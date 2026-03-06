import { ref } from 'vue'

/**
 * useDownloadCV — generates a professional PDF from the hidden CVPrintLayout.
 *
 * Image fix: We load each image into an <img> element (same-origin or
 * CORS-friendly), paint it onto a <canvas>, then extract a data-URL.
 * This bypasses html2canvas's CORS limitations entirely.
 */
export function useDownloadCV() {
  const generating = ref(false)

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

      // Make clone visible (it was display:none)
      clone.style.display = 'block'
      const inner = clone.firstElementChild
      if (inner) inner.style.display = 'flex'

      wrapper.appendChild(clone)
      document.body.appendChild(wrapper)

      // 3. Convert all images to base64
      await inlineAllImages(wrapper)

      // 4. Let the browser finish layout
      await new Promise((r) => setTimeout(r, 300))

      const filename = `${userName.replace(/\s+/g, '_')}_CV.pdf`

      // 5. Generate PDF
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
      if (wrapper?.parentNode) wrapper.parentNode.removeChild(wrapper)
      generating.value = false
    }
  }

  return { generating, downloadCV }
}
