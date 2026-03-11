import { ref } from 'vue'
import i18n from '@/i18n'

/**
 * useDownloadCV — generates a professional PDF from the hidden CVPrintLayout.
 *
 * Image fix: We load each image into an <img> element (same-origin or
 * CORS-friendly), paint it onto a <canvas>, then extract a data-URL.
 * This bypasses html2canvas's CORS limitations entirely.
 *
 * Arabic/RTL fix: html2canvas has a known bug with Arabic text — it doesn't
 * handle RTL ligature shaping correctly (letters appear disconnected/reversed).
 * We fix this by:
 *   1. Fetching the Noto Sans Arabic font and embedding it as base64 @font-face
 *   2. Setting dir="rtl" on the off-screen wrapper
 *   3. Adding unicode-bidi:embed to force correct bidi rendering
 */
export function useDownloadCV() {
  const generating = ref(false)
  let cachedFontBase64 = null

  /**
   * Fetch the Noto Sans Arabic WOFF2 font and convert to base64 data-URL.
   * Uses the local copy in /public for reliability (works offline).
   * Cached after first load so subsequent PDF exports are instant.
   */
  async function getArabicFontBase64() {
    if (cachedFontBase64) return cachedFontBase64
    try {
      // Local file first (reliable, works offline), fallback to Google Fonts CDN
      const urls = [
        '/NotoSansArabic-Regular.woff2',
        'https://fonts.gstatic.com/s/notosansarabic/v33/nwpCtLGrOAZMl5nJ_wfgRg3DrWFZWsnVBJ_sS6tlqHHFlj4wv4r4xA.woff2',
      ]
      let blob = null
      for (const url of urls) {
        try {
          const resp = await fetch(url)
          if (resp.ok) { blob = await resp.blob(); break }
        } catch { /* try next URL */ }
      }
      if (!blob) return null
      return new Promise((resolve) => {
        const reader = new FileReader()
        reader.onloadend = () => { cachedFontBase64 = reader.result; resolve(reader.result) }
        reader.readAsDataURL(blob)
      })
    } catch {
      return null
    }
  }

  /**
   * Load an image URL and convert it to a base64 data-URL via canvas.
   * Works for same-origin images and images with proper CORS headers.
   * Falls back to the original URL if conversion fails.
   */
  function imageToBase64(url) {
    return new Promise((resolve) => {
      const img = new Image()
      img.crossOrigin = 'anonymous'
      img.onload = () => {
        try {
          const canvas = document.createElement('canvas')
          canvas.width = img.naturalWidth
          canvas.height = img.naturalHeight
          const ctx = canvas.getContext('2d')
          ctx.drawImage(img, 0, 0)
          resolve(canvas.toDataURL('image/png'))
        } catch {
          resolve(url)
        }
      }
      img.onerror = () => resolve(url)
      // Bust cache to force CORS reload
      img.src = url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now()
    })
  }

  /**
   * Replace every <img> src in a container with an inlined base64 data-URL.
   */
  async function inlineAllImages(container) {
    const images = container.querySelectorAll('img[src]')
    await Promise.all(
      Array.from(images).map(async (img) => {
        if (img.src && !img.src.startsWith('data:')) {
          const base64 = await imageToBase64(img.getAttribute('src'))
          img.setAttribute('src', base64)
        }
      })
    )
  }

  async function downloadCV(userName = 'CV') {
    if (generating.value) return
    generating.value = true

    let wrapper = null
    const isArabic = i18n.global.locale.value === 'ar'
    const disabledSheets = []

    try {
      const html2pdf = (await import('html2pdf.js')).default

      const source = document.getElementById('cv-print-layout')
      if (!source) {
        console.error('Print layout element not found')
        return
      }

      // 1. Clone the hidden element
      const clone = source.cloneNode(true)
      clone.removeAttribute('id')

      // 2. Off-screen wrapper with real layout
      wrapper = document.createElement('div')
      wrapper.style.cssText = 'position:absolute;left:-9999px;top:0;z-index:-1;width:794px;overflow:visible;background:#fff;'

      // RTL: propagate direction to wrapper so html2canvas respects it
      if (isArabic) {
        wrapper.setAttribute('dir', 'rtl')
        wrapper.setAttribute('lang', 'ar')
      }

      // Make clone visible (it was display:none)
      clone.style.display = 'block'
      const inner = clone.firstElementChild
      if (inner) inner.style.display = 'flex'

      // 3. Arabic font fix: embed Noto Sans Arabic as base64 @font-face
      //    This forces html2canvas to use the correct Arabic font with
      //    proper ligature shaping instead of a fallback that breaks glyphs.
      if (isArabic) {
        const fontDataUrl = await getArabicFontBase64()
        if (fontDataUrl) {
          const styleEl = document.createElement('style')
          styleEl.textContent = `
            @font-face {
              font-family: 'Noto Sans Arabic';
              src: url('${fontDataUrl}') format('woff2');
              font-weight: 100 900;
              font-style: normal;
              font-display: block;
            }
          `
          clone.prepend(styleEl)
        }

        // Add unicode-bidi and direction to all text-bearing elements
        // This ensures html2canvas renders Arabic glyphs in the correct
        // joined form and right-to-left order
        clone.querySelectorAll('div, span, p, h1, h2, h3, h4, h5, h6').forEach((el) => {
          el.style.unicodeBidi = 'embed'
          el.style.direction = 'rtl'
          el.style.textAlign = el.style.textAlign || 'right'
        })
      }

      wrapper.appendChild(clone)
      document.body.appendChild(wrapper)

      // 4. Convert all images to base64
      await inlineAllImages(wrapper)

      // 5. Let the browser finish layout + font loading
      if (isArabic) {
        // Wait for embedded font to be ready
        try { await document.fonts.ready } catch { /* fallback: just wait */ }
      }
      await new Promise((r) => setTimeout(r, isArabic ? 600 : 300))

      const filename = `${userName.replace(/\s+/g, '_')}_CV.pdf`

      // 6. OKLCH FIX: Disable stylesheets containing oklch() before html2canvas.
      //    html2canvas parses ALL document.styleSheets. Tailwind CSS v4 emits
      //    oklch() color functions that html2canvas can't parse, causing a crash.
      //    CVPrintLayout uses only inline styles, so disabling sheets is safe.
      for (const sheet of document.styleSheets) {
        if (sheet.disabled) continue
        try {
          const rules = sheet.cssRules || sheet.rules
          if (!rules) continue
          for (const rule of rules) {
            if (rule.cssText && rule.cssText.includes('oklch')) {
              sheet.disabled = true
              disabledSheets.push(sheet)
              break
            }
          }
        } catch {
          // CORS sheets — can't read rules, skip
        }
      }

      // 7. Generate PDF
      await html2pdf()
        .set({
          margin:      0,
          filename,
          image:       { type: 'jpeg', quality: 1 },
          html2canvas: {
            scale:           2,
            useCORS:         true,
            allowTaint:      false,
            letterRendering: true,
            logging:         false,
            width:           794,
            windowWidth:     794,
          },
          jsPDF:       { unit: 'mm', format: 'a4', orientation: 'portrait' },
          pagebreak:   { mode: ['avoid-all', 'css', 'legacy'] },
        })
        .from(clone)
        .save()
    } catch (err) {
      console.error('PDF generation failed:', err)
    } finally {
      // Re-enable all stylesheets we disabled
      for (const sheet of disabledSheets) {
        sheet.disabled = false
      }
      if (wrapper?.parentNode) wrapper.parentNode.removeChild(wrapper)
      generating.value = false
    }
  }

  return { generating, downloadCV }
}
