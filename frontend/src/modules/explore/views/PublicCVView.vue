<script setup>
/**
 * PublicCVView — Read-only public view of someone's CV.
 *
 * Accessible at /cv/:userId — no authentication required.
 * Allows recruiters and visitors to view a user's full CV,
 * share the link, and contact the user.
 *
 * DESIGN:
 *   - Reuses the same visual design as CVView but read-only (no edit buttons)
 *   - Share button to copy CV link to clipboard
 *   - Contact info visible (email, phone, LinkedIn, etc.)
 *   - Back button to return to the directory
 */
import { onMounted, computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useExploreStore } from '@/modules/explore/stores/explore'
import { useAuthStore } from '@/modules/auth/stores/auth'
import { useToastStore } from '@/shared/stores/toast'
import SkeletonPulse from '@/shared/components/SkeletonPulse.vue'
import http from '@/api/http'

const route = useRoute()
const router = useRouter()
const { t } = useI18n()
const explore = useExploreStore()
const authStore = useAuthStore()
const toast = useToastStore()

const slug = computed(() => route.params.slug)
const cv = computed(() => explore.publicCV)
const loading = computed(() => explore.publicCVLoading)
const error = computed(() => explore.publicCVError)

const copied = ref(false)
const generating = ref(false)

// ── Reaction state ───────────────────────────────────────────
const reactions = ref({ likes: 0, loves: 0, celebrates: 0, insightfuls: 0, curious: 0, total: 0, user_reaction: null })
const reactionLoading = ref(false)
const viewCount = ref(0)
const viewRecorded = ref(false)   // Guard: only record 1 view per page visit

onMounted(() => {
  explore.fetchPublicCV(slug.value)
})

// After CV loads, fetch reactions + record the view (once per mount)
watch(cv, async (newCV) => {
  if (newCV?.id) {
    fetchReactions(newCV.id)
    if (!viewRecorded.value) {
      viewRecorded.value = true
      recordView(newCV.id)
    }
  }
})

async function fetchReactions(profileId) {
  try {
    const { data } = await http.get(`/reactions/${profileId}`)
    reactions.value = data.data
  } catch {
    // Silent — reactions are non-critical
  }
}

async function recordView(profileId) {
  try {
    const { data } = await http.post(`/profile-views/${profileId}`)
    viewCount.value = data.data.view_count
  } catch {
    // Silent — view tracking is non-critical
  }
}

async function toggleReaction(type) {
  if (!authStore.isAuthenticated) {
    toast.showWarning(t('notifications.loginToReact'))
    return
  }
  if (reactionLoading.value) return
  reactionLoading.value = true
  try {
    const { data } = await http.post('/reactions/toggle', {
      profile_id: cv.value.id,
      type,
    })
    reactions.value = data.data.reactions
  } catch (err) {
    if (err.response?.status === 422) {
      toast.showWarning(t('notifications.cannotReactSelf'))
    }
  } finally {
    reactionLoading.value = false
  }
}

function shareLink() {
  const url = window.location.href
  navigator.clipboard.writeText(url).then(() => {
    copied.value = true
    toast.showSuccess(t('explore.linkCopied'))
    setTimeout(() => { copied.value = false }, 2000)
  })
}

/**
 * Download the public CV as PDF using html2pdf.js.
 *
 * OKLCH FIX (Tailwind CSS v4):
 *   html2canvas parses ALL document stylesheets. Tailwind v4 uses oklch()
 *   color functions that html2canvas cannot parse, crashing PDF generation.
 *
 *   The ONLY reliable fix is to never give html2canvas any Tailwind-styled
 *   DOM. Instead, we build a completely fresh DOM tree with 100% inline CSS
 *   from the cv.value data — exactly like CVPrintLayout.vue does.
 *   This way html2canvas never encounters oklch().
 *
 *   We also disable oklch-containing stylesheets as defence-in-depth.
 */

/** Convert an image URL to base64 data-URL via canvas. */
function imageToBase64(url) {
  return new Promise((resolve) => {
    const img = new Image()
    img.crossOrigin = 'anonymous'
    img.onload = () => {
      try {
        const c = document.createElement('canvas')
        c.width = img.naturalWidth; c.height = img.naturalHeight
        c.getContext('2d').drawImage(img, 0, 0)
        resolve(c.toDataURL('image/png'))
      } catch { resolve(url) }
    }
    img.onerror = () => resolve(url)
    img.src = url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now()
  })
}

/**
 * Build a completely self-contained PDF DOM from cv data.
 * Uses ONLY inline styles (no Tailwind, no classes).
 * Professional two-column layout: dark navy sidebar + clean white main area.
 * A4 at 96 DPI = 794 × 1123 px.
 */
function buildPdfDom(data) {
  const d = data
  const fontFamily = "'Segoe UI','Helvetica Neue',Arial,sans-serif"

  function fmt(dateStr) {
    if (!dateStr) return t('cv.present')
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
  }

  function skillPct(level) {
    if (!level) return '70%'
    const l = String(level).toLowerCase()
    if (l.includes('expert') || l.includes('senior')) return '95%'
    if (l.includes('advanced')) return '85%'
    if (l.includes('intermediate')) return '65%'
    if (l.includes('beginner') || l.includes('junior')) return '35%'
    return '65%'
  }

  function langPct(level) {
    if (!level) return '50%'
    const l = String(level).toLowerCase()
    if (l.includes('native') || l.includes('fluent')) return '100%'
    if (l.includes('advanced') || l.includes('proficient')) return '80%'
    if (l.includes('intermediate') || l.includes('conversational')) return '55%'
    if (l.includes('beginner') || l.includes('basic') || l.includes('elementary')) return '30%'
    return '50%'
  }

  /** Create an element with inline styles + optional text content */
  function el(tag, styles, text) {
    const e = document.createElement(tag)
    if (styles) Object.assign(e.style, styles)
    if (text !== undefined) e.textContent = text
    return e
  }
  /** el but with innerHTML (for emojis, html entities) */
  function elH(tag, styles, html) {
    const e = document.createElement(tag)
    if (styles) Object.assign(e.style, styles)
    if (html !== undefined) e.innerHTML = html
    return e
  }

  // ─── Reusable style objects ───
  const SIDEBAR_HEADING = {
    fontSize: '8.5px', fontWeight: '800', textTransform: 'uppercase',
    letterSpacing: '2.5px', color: '#60a5fa', paddingBottom: '8px',
    marginBottom: '12px', borderBottom: '1px solid rgba(96,165,250,0.2)',
    fontFamily,
  }
  const MAIN_HEADING = {
    fontSize: '13px', fontWeight: '800', textTransform: 'uppercase',
    letterSpacing: '1.8px', color: '#1e293b', fontFamily,
  }
  const DATE_BADGE = {
    fontSize: '8px', color: '#64748b', background: '#f8fafc',
    padding: '3px 10px', borderRadius: '10px', fontWeight: '600',
    whiteSpace: 'nowrap', border: '1px solid #e2e8f0',
    display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
    lineHeight: '1.4', fontFamily,
  }

  // ═══════════════════════════════════════════════════════════════
  //  ROOT — A4 container
  // ═══════════════════════════════════════════════════════════════
  const root = el('div', {
    width: '794px', minHeight: '1123px', fontFamily,
    background: '#ffffff', display: 'flex', overflow: 'hidden',
    color: '#1e293b', lineHeight: '1.5', boxSizing: 'border-box',
  })

  // ═══════════════════════════════════════════════════════════════
  //  SIDEBAR  (280px — premium dark navy)
  // ═══════════════════════════════════════════════════════════════
  const sidebar = el('div', {
    width: '280px', minWidth: '280px', background: '#0c1222',
    display: 'flex', flexDirection: 'column',
  })

  // ── Photo + Identity header ──
  const identity = el('div', {
    padding: '40px 24px 30px', textAlign: 'center',
    background: 'linear-gradient(180deg, #0f1d35 0%, #0c1222 100%)',
  })

  if (d.photo) {
    const photoOuter = el('div', {
      width: '120px', height: '120px', margin: '0 auto 16px', borderRadius: '50%',
      background: 'linear-gradient(135deg, #3b82f6, #8b5cf6)',
      padding: '3px', boxSizing: 'border-box',
    })
    const photoInner = el('div', {
      width: '100%', height: '100%', borderRadius: '50%', overflow: 'hidden',
      background: '#1e293b',
    })
    const img = document.createElement('img')
    img.setAttribute('crossorigin', 'anonymous')
    img.setAttribute('data-src', d.photo)
    img.src = d.photo
    Object.assign(img.style, { width: '100%', height: '100%', objectFit: 'cover', display: 'block' })
    photoInner.appendChild(img)
    photoOuter.appendChild(photoInner)
    identity.appendChild(photoOuter)
  } else {
    const initials = (d.user_name || '').split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
    const avatarOuter = el('div', {
      width: '120px', height: '120px', margin: '0 auto 16px', borderRadius: '50%',
      background: 'linear-gradient(135deg, #3b82f6, #8b5cf6)',
      padding: '3px', boxSizing: 'border-box',
    })
    const avatarInner = el('div', {
      width: '100%', height: '100%', borderRadius: '50%', overflow: 'hidden',
      background: '#1e293b', display: 'flex', alignItems: 'center', justifyContent: 'center',
    })
    avatarInner.appendChild(el('span', {
      fontSize: '38px', color: '#94a3b8', fontWeight: '700', fontFamily,
    }, initials))
    avatarOuter.appendChild(avatarInner)
    identity.appendChild(avatarOuter)
  }

  // Name
  identity.appendChild(el('div', {
    fontSize: '18px', fontWeight: '800', color: '#ffffff',
    letterSpacing: '0.3px', padding: '0 16px', textAlign: 'center',
    lineHeight: '1.3', fontFamily,
  }, d.user_name || ''))

  // Headline
  if (d.headline) {
    identity.appendChild(el('div', {
      fontSize: '10px', color: '#60a5fa', marginTop: '6px',
      fontWeight: '600', letterSpacing: '0.5px', padding: '0 16px',
      textAlign: 'center', lineHeight: '1.4', fontFamily,
    }, d.headline))
  }

  // Thin accent line under identity
  identity.appendChild(el('div', {
    width: '40px', height: '2px', margin: '18px auto 0',
    background: 'linear-gradient(90deg, #3b82f6, #8b5cf6)', borderRadius: '1px',
  }))

  sidebar.appendChild(identity)

  // ── Sidebar body ──
  const sidebarBody = el('div', {
    padding: '22px 22px 28px', flex: '1', display: 'flex',
    flexDirection: 'column', gap: '20px',
  })

  // ─── CONTACT ───
  const contactFields = [
    { icon: '📍', val: d.location },
    { icon: '📞', val: d.phone },
    { icon: '✉️', val: d.contact_email, small: true },
    { icon: '🌐', val: d.website ? d.website.replace(/^https?:\/\//, '') : null, blue: true, small: true },
    { icon: '💼', val: d.linkedin ? d.linkedin.replace(/^https?:\/\/(www\.)?/, '') : null, blue: true, small: true },
    { icon: '🐙', val: d.github ? d.github.replace(/^https?:\/\/(www\.)?/, '') : null, blue: true, small: true },
  ].filter(f => f.val)

  if (contactFields.length) {
    const sec = el('div', {})
    sec.appendChild(el('div', { ...SIDEBAR_HEADING }, t('print.contact').toUpperCase()))
    for (const f of contactFields) {
      const row = el('div', { display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '7px' })
      const iconCircle = el('div', {
        width: '24px', height: '24px', borderRadius: '50%',
        background: 'rgba(96,165,250,0.1)', display: 'flex',
        alignItems: 'center', justifyContent: 'center', flexShrink: '0',
      })
      iconCircle.appendChild(elH('span', { fontSize: '10px' }, f.icon))
      row.appendChild(iconCircle)
      row.appendChild(el('span', {
        fontSize: f.small ? '8.5px' : '9.5px',
        color: f.blue ? '#60a5fa' : '#cbd5e1',
        wordBreak: 'break-all', lineHeight: '1.4', fontFamily,
      }, f.val))
      sec.appendChild(row)
    }
    sidebarBody.appendChild(sec)
  }

  // ─── SKILLS ───
  if (d.skills?.length) {
    const sec = el('div', {})
    sec.appendChild(el('div', { ...SIDEBAR_HEADING }, t('print.skills').toUpperCase()))
    for (const skill of d.skills) {
      const item = el('div', { marginBottom: '8px' })
      const row = el('div', { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '4px' })
      row.appendChild(el('span', { fontSize: '9.5px', fontWeight: '600', color: '#e2e8f0', fontFamily }, skill.name))
      if (skill.level) {
        const levelKey = String(skill.level).toLowerCase().replace(/\s+/g, '_')
        row.appendChild(el('span', { fontSize: '7.5px', color: '#64748b', fontFamily }, t(`levels.${levelKey}`, skill.level)))
      }
      item.appendChild(row)
      const track = el('div', {
        width: '100%', height: '3px', background: 'rgba(255,255,255,0.06)',
        borderRadius: '2px', overflow: 'hidden',
      })
      track.appendChild(el('div', {
        width: skillPct(skill.level), height: '100%', borderRadius: '2px',
        background: 'linear-gradient(90deg, #3b82f6, #60a5fa)',
      }))
      item.appendChild(track)
      sec.appendChild(item)
    }
    sidebarBody.appendChild(sec)
  }

  // ─── LANGUAGES ───
  if (d.languages?.length) {
    const sec = el('div', {})
    sec.appendChild(el('div', { ...SIDEBAR_HEADING }, t('print.languages').toUpperCase()))
    for (const lang of d.languages) {
      const item = el('div', { marginBottom: '10px' })
      const row = el('div', { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '4px' })
      row.appendChild(el('span', { fontSize: '9.5px', fontWeight: '600', color: '#e2e8f0', fontFamily }, lang.name))
      if (lang.level) {
        const levelKey = String(lang.level).toLowerCase().replace(/\s+/g, '_')
        row.appendChild(el('span', { fontSize: '7.5px', color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '0.5px', fontFamily }, t(`levels.${levelKey}`, lang.level)))
      }
      item.appendChild(row)
      const track = el('div', {
        width: '100%', height: '3px', background: 'rgba(255,255,255,0.06)',
        borderRadius: '2px', overflow: 'hidden',
      })
      track.appendChild(el('div', {
        width: langPct(lang.level), height: '100%', borderRadius: '2px',
        background: 'linear-gradient(90deg, #8b5cf6, #a78bfa)',
      }))
      item.appendChild(track)
      sec.appendChild(item)
    }
    sidebarBody.appendChild(sec)
  }

  // ─── CERTIFICATIONS in sidebar (text-only, when none have photos) ───
  const certsInSidebar = d.certifications?.length && !d.certifications.some(c => c.photo)
  if (certsInSidebar) {
    const sec = el('div', {})
    sec.appendChild(el('div', { ...SIDEBAR_HEADING }, t('print.certifications').toUpperCase()))
    for (const cert of d.certifications) {
      const item = el('div', { marginBottom: '10px', pageBreakInside: 'avoid' })
      item.appendChild(el('div', { fontSize: '9.5px', fontWeight: '700', color: '#f1f5f9', lineHeight: '1.3', fontFamily }, cert.name))
      item.appendChild(el('div', { fontSize: '8.5px', color: '#94a3b8', marginTop: '2px', fontFamily }, cert.organization || ''))
      const dateStr = fmt(cert.issue_date) + (cert.expiration_date ? ' — ' + fmt(cert.expiration_date) : '')
      item.appendChild(el('div', { fontSize: '7.5px', color: '#64748b', marginTop: '2px', fontFamily }, dateStr))
      sec.appendChild(item)
    }
    sidebarBody.appendChild(sec)
  }

  sidebar.appendChild(sidebarBody)
  root.appendChild(sidebar)

  // ═══════════════════════════════════════════════════════════════
  //  MAIN CONTENT AREA (clean white)
  // ═══════════════════════════════════════════════════════════════
  const main = el('div', {
    flex: '1', padding: '36px 32px 32px 36px', background: '#ffffff',
    display: 'flex', flexDirection: 'column', gap: '22px',
  })

  // ── Section heading with accent bar ──
  function sectionHeading(emoji, title, accentColor) {
    const wrap = el('div', {
      display: 'flex', alignItems: 'center', gap: '10px',
      marginBottom: '12px', paddingBottom: '8px',
      borderBottom: `2px solid ${accentColor || '#3b82f6'}`,
    })
    wrap.appendChild(elH('span', { fontSize: '14px', lineHeight: '1' }, emoji))
    wrap.appendChild(el('span', { ...MAIN_HEADING }, title))
    return wrap
  }

  // ── Item separator ──
  function itemSep(i, total) {
    return {
      paddingBottom: i < total - 1 ? '12px' : '0',
      marginBottom: i < total - 1 ? '12px' : '0',
      borderBottom: i < total - 1 ? '1px solid #f1f5f9' : 'none',
      pageBreakInside: 'avoid',
    }
  }

  // ── ABOUT / SUMMARY ──
  if (d.summary) {
    const sec = el('div', {})
    sec.appendChild(sectionHeading('💼', t('print.aboutMe')))
    const summaryBox = el('div', {
      margin: '0', padding: '10px 14px', fontSize: '10px', color: '#475569',
      lineHeight: '1.9', background: '#f8fafc', borderRadius: '6px',
      borderLeft: '3px solid #3b82f6', fontFamily,
    })
    summaryBox.textContent = d.summary
    sec.appendChild(summaryBox)
    main.appendChild(sec)
  }

  // ── EXPERIENCE ──
  if (d.experiences?.length) {
    const sec = el('div', {})
    sec.appendChild(sectionHeading('🏢', t('print.experience')))
    d.experiences.forEach((exp, i) => {
      const item = el('div', itemSep(i, d.experiences.length))
      const topRow = el('div', { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: '8px' })
      const left = el('div', { flex: '1' })
      left.appendChild(el('div', { fontSize: '11.5px', fontWeight: '700', color: '#0f172a', lineHeight: '1.3', fontFamily }, exp.position))
      left.appendChild(el('div', { fontSize: '10px', color: '#3b82f6', fontWeight: '600', marginTop: '2px', fontFamily }, exp.company))
      topRow.appendChild(left)
      topRow.appendChild(el('div', { ...DATE_BADGE }, `${fmt(exp.start_date)} — ${fmt(exp.end_date)}`))
      item.appendChild(topRow)
      if (exp.description) {
        item.appendChild(el('p', {
          margin: '6px 0 0', fontSize: '9.5px', color: '#64748b',
          lineHeight: '1.75', whiteSpace: 'pre-line', fontFamily,
        }, exp.description))
      }
      sec.appendChild(item)
    })
    main.appendChild(sec)
  }

  // ── EDUCATION ──
  if (d.educations?.length) {
    const sec = el('div', {})
    sec.appendChild(sectionHeading('🎓', t('print.education')))
    d.educations.forEach((edu, i) => {
      const item = el('div', itemSep(i, d.educations.length))
      const topRow = el('div', { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: '8px' })
      const left = el('div', { flex: '1' })
      left.appendChild(el('div', { fontSize: '11.5px', fontWeight: '700', color: '#0f172a', lineHeight: '1.3', fontFamily }, edu.degree))
      left.appendChild(el('div', { fontSize: '10px', color: '#3b82f6', fontWeight: '600', marginTop: '2px', fontFamily }, edu.institution))
      if (edu.field_of_study) {
        left.appendChild(el('div', { fontSize: '9px', color: '#94a3b8', fontStyle: 'italic', marginTop: '2px', fontFamily }, edu.field_of_study))
      }
      topRow.appendChild(left)
      topRow.appendChild(el('div', { ...DATE_BADGE }, `${fmt(edu.start_date)} — ${fmt(edu.end_date)}`))
      item.appendChild(topRow)
      sec.appendChild(item)
    })
    main.appendChild(sec)
  }

  // ── PROJECTS ──
  if (d.projects?.length) {
    const sec = el('div', {})
    sec.appendChild(sectionHeading('🚀', t('print.projects'), '#8b5cf6'))
    d.projects.forEach((proj, i) => {
      const item = el('div', itemSep(i, d.projects.length))
      const row = el('div', { display: 'flex', gap: '12px', alignItems: 'flex-start' })

      if (proj.cover) {
        const coverWrap = el('div', {
          width: '80px', minWidth: '80px', height: '56px', borderRadius: '6px',
          overflow: 'hidden', border: '1px solid #e2e8f0', flexShrink: '0',
        })
        const coverImg = document.createElement('img')
        coverImg.setAttribute('crossorigin', 'anonymous')
        coverImg.setAttribute('data-src', proj.cover)
        coverImg.src = proj.cover
        Object.assign(coverImg.style, { width: '100%', height: '100%', objectFit: 'cover', display: 'block' })
        coverWrap.appendChild(coverImg)
        row.appendChild(coverWrap)
      }

      const body = el('div', { flex: '1', minWidth: '0' })
      const topRow = el('div', { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: '8px' })
      topRow.appendChild(el('div', { fontSize: '11.5px', fontWeight: '700', color: '#0f172a', lineHeight: '1.3', fontFamily }, proj.title))
      if (proj.start_date) {
        topRow.appendChild(el('div', { ...DATE_BADGE }, `${fmt(proj.start_date)} — ${fmt(proj.end_date)}`))
      }
      body.appendChild(topRow)

      if (proj.github_url || proj.link) {
        const links = el('div', { display: 'flex', gap: '10px', marginTop: '3px' })
        if (proj.github_url) links.appendChild(el('span', { fontSize: '8px', color: '#8b5cf6', fontWeight: '700', fontFamily }, '● GitHub'))
        if (proj.link) links.appendChild(el('span', { fontSize: '8px', color: '#3b82f6', fontWeight: '700', fontFamily }, '● Live Demo'))
        body.appendChild(links)
      }

      if (proj.description) {
        body.appendChild(el('p', {
          margin: '4px 0 0', fontSize: '9.5px', color: '#64748b', lineHeight: '1.7', fontFamily,
        }, proj.description))
      }
      row.appendChild(body)
      item.appendChild(row)
      sec.appendChild(item)
    })
    main.appendChild(sec)
  }

  // ── CERTIFICATIONS with photos (main area) ──
  const certsInMain = d.certifications?.length && d.certifications.some(c => c.photo)
  if (certsInMain) {
    const sec = el('div', {})
    sec.appendChild(sectionHeading('🏅', t('print.certCredentials'), '#f59e0b'))
    const grid = el('div', { display: 'flex', flexWrap: 'wrap', gap: '10px' })
    for (const cert of d.certifications) {
      const card = el('div', { width: 'calc(50% - 5px)', pageBreakInside: 'avoid' })
      const inner = el('div', {
        border: '1px solid #e2e8f0', borderRadius: '8px', overflow: 'hidden',
        background: '#fafafa',
      })
      if (cert.photo) {
        const imgWrap = el('div', { width: '100%', height: '90px', overflow: 'hidden', background: '#f1f5f9' })
        const certImg = document.createElement('img')
        certImg.setAttribute('crossorigin', 'anonymous')
        certImg.setAttribute('data-src', cert.photo)
        certImg.src = cert.photo
        Object.assign(certImg.style, { width: '100%', height: '100%', objectFit: 'cover', display: 'block' })
        imgWrap.appendChild(certImg)
        inner.appendChild(imgWrap)
      }
      const info = el('div', { padding: '7px 10px' })
      info.appendChild(el('div', { fontSize: '9.5px', fontWeight: '700', color: '#0f172a', lineHeight: '1.3', fontFamily }, cert.name))
      info.appendChild(el('div', { fontSize: '8.5px', color: '#3b82f6', fontWeight: '600', marginTop: '2px', fontFamily }, cert.organization || ''))
      const dateStr = fmt(cert.issue_date) + (cert.expiration_date ? ' — ' + fmt(cert.expiration_date) : '')
      info.appendChild(el('div', { fontSize: '7.5px', color: '#94a3b8', marginTop: '2px', fontFamily }, dateStr))
      inner.appendChild(info)
      card.appendChild(inner)
      grid.appendChild(card)
    }
    sec.appendChild(grid)
    main.appendChild(sec)
  }

  // ── VOLUNTEER ──
  if (d.volunteer_experiences?.length) {
    const sec = el('div', {})
    sec.appendChild(sectionHeading('💚', t('print.volunteer'), '#10b981'))
    d.volunteer_experiences.forEach((vol, i) => {
      const item = el('div', itemSep(i, d.volunteer_experiences.length))
      const topRow = el('div', { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: '8px' })
      const left = el('div', { flex: '1' })
      left.appendChild(el('div', { fontSize: '11.5px', fontWeight: '700', color: '#0f172a', lineHeight: '1.3', fontFamily }, vol.role))
      left.appendChild(el('div', { fontSize: '10px', color: '#10b981', fontWeight: '600', marginTop: '2px', fontFamily }, vol.organization))
      topRow.appendChild(left)
      topRow.appendChild(el('div', { ...DATE_BADGE }, `${fmt(vol.start_date)} — ${fmt(vol.end_date)}`))
      item.appendChild(topRow)
      if (vol.description) {
        item.appendChild(el('p', {
          margin: '6px 0 0', fontSize: '9.5px', color: '#64748b',
          lineHeight: '1.75', fontFamily,
        }, vol.description))
      }
      sec.appendChild(item)
    })
    main.appendChild(sec)
  }

  root.appendChild(main)
  return root
}

async function downloadPDF() {
  if (generating.value) return
  generating.value = true

  let iframe = null
  try {
    const html2pdf = (await import('html2pdf.js')).default

    // Build a completely fresh DOM with 100% inline styles — no Tailwind
    const pdfDom = buildPdfDom(cv.value)

    // ── Use a hidden iframe so html2canvas can't see main-page stylesheets ──
    // This prevents the visible page from flashing unstyled when we render the PDF.
    // The iframe has NO stylesheets at all — only our inline-styled DOM.
    iframe = document.createElement('iframe')
    iframe.style.cssText = 'position:fixed;left:-9999px;top:0;width:794px;height:1123px;border:none;opacity:0;pointer-events:none;'
    document.body.appendChild(iframe)

    await new Promise((r) => requestAnimationFrame(() => requestAnimationFrame(r)))

    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document
    iframeDoc.open()
    iframeDoc.write('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body style="margin:0;padding:0;background:#fff;"></body></html>')
    iframeDoc.close()

    iframeDoc.body.appendChild(pdfDom)

    // Convert all images to base64 to avoid CORS issues
    const images = pdfDom.querySelectorAll('img[src]')
    await Promise.all(
      Array.from(images).map(async (img) => {
        const src = img.getAttribute('data-src') || img.getAttribute('src')
        if (src && !src.startsWith('data:')) {
          try {
            img.src = await imageToBase64(src)
          } catch { /* keep original */ }
        }
      })
    )

    await new Promise((r) => setTimeout(r, 300))

    const filename = `${(cv.value?.user_name || 'CV').replace(/\s+/g, '_')}_CV.pdf`

    await html2pdf()
      .set({
        margin:      [0, 0, 0, 0],
        filename,
        image:       { type: 'jpeg', quality: 0.98 },
        html2canvas: {
          scale:           2,
          useCORS:         true,
          allowTaint:      false,
          letterRendering: true,
          logging:         false,
          backgroundColor: '#ffffff',
          width:           794,
          windowWidth:     794,
        },
        jsPDF:       { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak:   { mode: ['avoid-all', 'css', 'legacy'] },
      })
      .from(pdfDom)
      .save()
  } catch (err) {
    console.error('PDF generation failed:', err)
  } finally {
    if (iframe?.parentNode) iframe.parentNode.removeChild(iframe)
    generating.value = false
  }
}

function goBack() {
  router.push({ name: 'HomeView' })
}

// Helper to format dates
function formatDate(dateStr) {
  if (!dateStr) return t('cv.present')
  const d = new Date(dateStr)
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short' })
}
</script>

<template>
  <div class="min-h-screen text-slate-100 font-sans selection:bg-blue-500/30 overflow-x-hidden pb-20"
    style="background-image: linear-gradient(135deg, #000b18 0%, #00264d 100%);">

    <!-- Loading Skeleton -->
    <div v-if="loading" class="max-w-4xl mx-auto px-4 py-16">
      <div class="flex flex-col items-center gap-6">
        <SkeletonPulse class="w-36 h-36 rounded-full" />
        <SkeletonPulse class="h-8 w-64" />
        <SkeletonPulse class="h-4 w-96" />
        <SkeletonPulse class="h-4 w-48" />
        <div class="grid grid-cols-3 gap-3 mt-8 w-full max-w-md">
          <SkeletonPulse class="h-20 rounded-xl" />
          <SkeletonPulse class="h-20 rounded-xl" />
          <SkeletonPulse class="h-20 rounded-xl" />
        </div>
      </div>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="flex items-center justify-center min-h-[60vh]">
      <div class="text-center space-y-6 max-w-md mx-auto px-6">
        <div class="text-6xl">😔</div>
        <h2 class="text-2xl font-bold text-white">{{ t('explore.profileNotFound') }}</h2>
        <p class="text-slate-400">{{ error }}</p>
        <button @click="goBack"
          class="inline-block px-8 py-3 rounded-xl text-sm font-semibold text-white bg-linear-to-r from-blue-500 to-indigo-600 hover:shadow-lg hover:shadow-blue-500/25 transition-all">
          ← {{ t('explore.backToDirectory') }}
        </button>
      </div>
    </div>

    <!-- CV Content -->
    <template v-else-if="cv">
      <!-- Top action bar -->
      <div class="sticky top-14 z-30 bg-black/40 backdrop-blur-xl border-b border-white/5">
        <div class="max-w-4xl mx-auto px-4 py-2.5 flex items-center justify-between">
          <button @click="goBack"
            class="flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ t('explore.backToDirectory') }}
          </button>
          <button @click="shareLink"
            class="flex items-center gap-2 px-4 py-1.5 rounded-lg text-xs font-medium border transition-all"
            :class="copied ? 'text-green-400 border-green-400/30 bg-green-400/10' : 'text-slate-300 border-white/10 hover:border-blue-400/30 hover:text-blue-400'">
            <svg v-if="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
            <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ copied ? t('explore.copied') : t('explore.shareCV') }}
          </button>
          <button @click="downloadPDF"
            :disabled="generating"
            class="flex items-center gap-2 px-4 py-1.5 rounded-lg text-xs font-medium border transition-all"
            :class="generating
              ? 'text-slate-500 border-white/5 cursor-wait'
              : 'text-slate-300 border-white/10 hover:border-emerald-400/30 hover:text-emerald-400'">
            <svg v-if="generating" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a2 2 0 002 2h14a2 2 0 002-2v-3" />
            </svg>
            {{ generating ? t('explore.generating') : t('explore.downloadCV') }}
          </button>
        </div>
      </div>

      <!-- Hero Section -->
      <div id="public-cv-content">
      <section class="flex flex-col items-center justify-center py-14 sm:py-24 px-4 sm:px-6">
        <div class="relative mb-8">
          <div class="absolute -inset-1 bg-linear-to-r from-blue-600 to-cyan-400 rounded-full blur opacity-25"></div>
          <div class="relative bg-slate-900 rounded-full p-1 border border-white/10 shadow-2xl">
            <img v-if="cv.photo" :src="cv.photo" :alt="cv.user_name" class="rounded-full w-32 h-32 sm:w-44 sm:h-44 object-cover" />
            <div v-else class="rounded-full w-32 h-32 sm:w-44 sm:h-44 bg-slate-800 flex items-center justify-center text-slate-400 border border-dashed border-slate-600">
              <span class="text-3xl font-bold">{{ cv.user_name?.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2) }}</span>
            </div>
          </div>
        </div>
        <div class="text-center max-w-2xl mx-auto space-y-4">
          <h1 class="text-3xl sm:text-5xl md:text-6xl font-extrabold tracking-tight bg-clip-text text-transparent bg-linear-to-b from-white to-slate-400">
            {{ cv.user_name }}
          </h1>
          <p class="text-lg sm:text-xl text-blue-400 font-medium">{{ cv.headline }}</p>
          <p v-if="cv.summary" class="text-base sm:text-lg text-slate-300 font-light leading-relaxed italic">"{{ cv.summary }}"</p>
          <div v-if="cv.location" class="flex items-center justify-center gap-2 pt-2 text-sm font-medium text-blue-400 uppercase tracking-widest">
            <span class="w-8 h-px bg-blue-500/50"></span>
            <span>📍 {{ cv.location }}</span>
            <span class="w-8 h-px bg-blue-500/50"></span>
          </div>

          <!-- Contact Info Pills -->
          <div class="flex flex-wrap items-center justify-center gap-2 pt-5">
            <a v-if="cv.phone" :href="'tel:' + cv.phone"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium text-slate-300 bg-white/5 border border-white/10 hover:border-blue-400/30 hover:text-blue-400 transition-all">
              <span>📞</span> {{ cv.phone }}
            </a>
            <a v-if="cv.contact_email" :href="'mailto:' + cv.contact_email"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium text-slate-300 bg-white/5 border border-white/10 hover:border-blue-400/30 hover:text-blue-400 transition-all">
              <span>✉️</span> {{ cv.contact_email }}
            </a>
            <a v-if="cv.website" :href="cv.website" target="_blank" rel="noopener"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium text-slate-300 bg-white/5 border border-white/10 hover:border-cyan-400/30 hover:text-cyan-400 transition-all">
              <span>🌐</span> {{ t('forms.website') }}
            </a>
            <a v-if="cv.linkedin" :href="cv.linkedin" target="_blank" rel="noopener"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium text-slate-300 bg-white/5 border border-white/10 hover:border-blue-500/30 hover:text-blue-500 transition-all">
              <span>💼</span> LinkedIn
            </a>
            <a v-if="cv.github" :href="cv.github" target="_blank" rel="noopener"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium text-slate-300 bg-white/5 border border-white/10 hover:border-purple-400/30 hover:text-purple-400 transition-all">
              <span>🐙</span> GitHub
            </a>
          </div>
        </div>
      </section>

      <!-- Reaction Bar + View Count -->
      <section class="max-w-4xl mx-auto px-4 sm:px-6 mb-10">
        <div class="glass-card p-3 sm:p-5">

          <!-- Reaction Buttons — grid on mobile, flex row on sm+ -->
          <div class="grid grid-cols-5 gap-1.5 sm:flex sm:flex-wrap sm:items-center sm:gap-3">
            <!-- Like -->
            <button
              @click="toggleReaction('like')"
              :disabled="reactionLoading"
              class="group flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1.5 px-1 sm:px-4 py-2 sm:py-2 rounded-xl text-sm font-medium border transition-all duration-300"
              :class="reactions.user_reaction === 'like'
                ? 'bg-blue-500/15 border-blue-400/40 text-blue-400 shadow-lg shadow-blue-500/10'
                : 'bg-white/5 border-white/10 text-slate-400 hover:border-blue-400/30 hover:text-blue-400 hover:bg-blue-500/5'"
            >
              <span class="text-base sm:text-lg transition-transform duration-300" :class="{ 'scale-125': reactions.user_reaction === 'like' }">👍</span>
              <span class="text-[10px] sm:text-sm leading-tight hidden sm:inline">{{ t('notifications.like') }}</span>
              <span v-if="reactions.likes > 0"
                class="px-1 sm:px-1.5 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold"
                :class="reactions.user_reaction === 'like' ? 'bg-blue-400/20 text-blue-300' : 'bg-white/5 text-slate-500'">
                {{ reactions.likes }}
              </span>
            </button>

            <!-- Love -->
            <button
              @click="toggleReaction('love')"
              :disabled="reactionLoading"
              class="group flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1.5 px-1 sm:px-4 py-2 sm:py-2 rounded-xl text-sm font-medium border transition-all duration-300"
              :class="reactions.user_reaction === 'love'
                ? 'bg-pink-500/15 border-pink-400/40 text-pink-400 shadow-lg shadow-pink-500/10'
                : 'bg-white/5 border-white/10 text-slate-400 hover:border-pink-400/30 hover:text-pink-400 hover:bg-pink-500/5'"
            >
              <span class="text-base sm:text-lg transition-transform duration-300" :class="{ 'scale-125': reactions.user_reaction === 'love' }">❤️</span>
              <span class="text-[10px] sm:text-sm leading-tight hidden sm:inline">{{ t('notifications.love') }}</span>
              <span v-if="reactions.loves > 0"
                class="px-1 sm:px-1.5 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold"
                :class="reactions.user_reaction === 'love' ? 'bg-pink-400/20 text-pink-300' : 'bg-white/5 text-slate-500'">
                {{ reactions.loves }}
              </span>
            </button>

            <!-- Celebrate -->
            <button
              @click="toggleReaction('celebrate')"
              :disabled="reactionLoading"
              class="group flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1.5 px-1 sm:px-4 py-2 sm:py-2 rounded-xl text-sm font-medium border transition-all duration-300"
              :class="reactions.user_reaction === 'celebrate'
                ? 'bg-amber-500/15 border-amber-400/40 text-amber-400 shadow-lg shadow-amber-500/10'
                : 'bg-white/5 border-white/10 text-slate-400 hover:border-amber-400/30 hover:text-amber-400 hover:bg-amber-500/5'"
            >
              <span class="text-base sm:text-lg transition-transform duration-300" :class="{ 'scale-125': reactions.user_reaction === 'celebrate' }">🎉</span>
              <span class="text-[10px] sm:text-sm leading-tight hidden sm:inline">{{ t('notifications.celebrate') }}</span>
              <span v-if="reactions.celebrates > 0"
                class="px-1 sm:px-1.5 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold"
                :class="reactions.user_reaction === 'celebrate' ? 'bg-amber-400/20 text-amber-300' : 'bg-white/5 text-slate-500'">
                {{ reactions.celebrates }}
              </span>
            </button>

            <!-- Insightful -->
            <button
              @click="toggleReaction('insightful')"
              :disabled="reactionLoading"
              class="group flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1.5 px-1 sm:px-4 py-2 sm:py-2 rounded-xl text-sm font-medium border transition-all duration-300"
              :class="reactions.user_reaction === 'insightful'
                ? 'bg-emerald-500/15 border-emerald-400/40 text-emerald-400 shadow-lg shadow-emerald-500/10'
                : 'bg-white/5 border-white/10 text-slate-400 hover:border-emerald-400/30 hover:text-emerald-400 hover:bg-emerald-500/5'"
            >
              <span class="text-base sm:text-lg transition-transform duration-300" :class="{ 'scale-125': reactions.user_reaction === 'insightful' }">💡</span>
              <span class="text-[10px] sm:text-sm leading-tight hidden sm:inline">{{ t('notifications.insightful') }}</span>
              <span v-if="reactions.insightfuls > 0"
                class="px-1 sm:px-1.5 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold"
                :class="reactions.user_reaction === 'insightful' ? 'bg-emerald-400/20 text-emerald-300' : 'bg-white/5 text-slate-500'">
                {{ reactions.insightfuls }}
              </span>
            </button>

            <!-- Curious -->
            <button
              @click="toggleReaction('curious')"
              :disabled="reactionLoading"
              class="group flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1.5 px-1 sm:px-4 py-2 sm:py-2 rounded-xl text-sm font-medium border transition-all duration-300"
              :class="reactions.user_reaction === 'curious'
                ? 'bg-purple-500/15 border-purple-400/40 text-purple-400 shadow-lg shadow-purple-500/10'
                : 'bg-white/5 border-white/10 text-slate-400 hover:border-purple-400/30 hover:text-purple-400 hover:bg-purple-500/5'"
            >
              <span class="text-base sm:text-lg transition-transform duration-300" :class="{ 'scale-125': reactions.user_reaction === 'curious' }">🤔</span>
              <span class="text-[10px] sm:text-sm leading-tight hidden sm:inline">{{ t('notifications.curious') }}</span>
              <span v-if="reactions.curious > 0"
                class="px-1 sm:px-1.5 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold"
                :class="reactions.user_reaction === 'curious' ? 'bg-purple-400/20 text-purple-300' : 'bg-white/5 text-slate-500'">
                {{ reactions.curious }}
              </span>
            </button>
          </div>

          <!-- Separator + Stats -->
          <div class="mt-3 pt-3 border-t border-white/5 flex flex-col sm:flex-row sm:flex-wrap items-start sm:items-center justify-between gap-2 sm:gap-3">
            <div class="flex items-center gap-3 sm:gap-4 text-[11px] sm:text-xs text-slate-500">
              <div v-if="reactions.total > 0" class="flex items-center gap-1.5">
                <span>🎉</span>
                <span>{{ t('notifications.totalReactions', { count: reactions.total }) }}</span>
              </div>
              <div v-if="viewCount > 0" class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>{{ t('notifications.views', { count: viewCount }) }}</span>
              </div>
            </div>

            <!-- Login prompt for guests -->
            <p v-if="!authStore.isAuthenticated" class="text-[10px] sm:text-[11px] text-slate-600 italic">
              {{ t('notifications.loginToReact') }}
            </p>
          </div>
        </div>
      </section>

      <!-- Experience Section -->
      <section v-if="cv.experiences?.length" class="max-w-4xl mx-auto px-4 sm:px-6 mb-16">
        <div class="flex items-center gap-3 mb-8">
          <h2 class="text-xl font-bold text-white flex items-center gap-2">💼 {{ t('cv.experience') }}</h2>
          <div class="section-line"></div>
        </div>
        <div class="space-y-4">
          <div v-for="exp in cv.experiences" :key="exp.id" class="glass-card p-5">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h3 class="text-sm font-bold text-white">{{ exp.position }}</h3>
                <p class="text-xs text-blue-400 mt-0.5">{{ exp.company }}</p>
              </div>
              <span class="text-[11px] text-slate-500 whitespace-nowrap">
                {{ formatDate(exp.start_date) }} — {{ formatDate(exp.end_date) }}
              </span>
            </div>
            <p v-if="exp.description" class="text-xs text-slate-400 mt-3 leading-relaxed">{{ exp.description }}</p>
          </div>
        </div>
      </section>

      <!-- Education Section -->
      <section v-if="cv.educations?.length" class="max-w-4xl mx-auto px-4 sm:px-6 mb-16">
        <div class="flex items-center gap-3 mb-8">
          <h2 class="text-xl font-bold text-white flex items-center gap-2">🎓 {{ t('cv.educationTitle') }}</h2>
          <div class="section-line"></div>
        </div>
        <div class="space-y-4">
          <div v-for="edu in cv.educations" :key="edu.id" class="glass-card p-5">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h3 class="text-sm font-bold text-white">{{ edu.institution }}</h3>
                <p class="text-xs text-blue-400 mt-0.5">{{ edu.degree }} {{ edu.field_of_study ? '— ' + edu.field_of_study : '' }}</p>
              </div>
              <span class="text-[11px] text-slate-500 whitespace-nowrap">
                {{ formatDate(edu.start_date) }} — {{ formatDate(edu.end_date) }}
              </span>
            </div>
            <p v-if="edu.description" class="text-xs text-slate-400 mt-3 leading-relaxed">{{ edu.description }}</p>
          </div>
        </div>
      </section>

      <!-- Projects Section -->
      <section v-if="cv.projects?.length" class="max-w-4xl mx-auto px-4 sm:px-6 mb-16">
        <div class="flex items-center gap-3 mb-8">
          <h2 class="text-xl font-bold text-white flex items-center gap-2">🚀 {{ t('cv.featuredProjects') }}</h2>
          <div class="section-line"></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="project in cv.projects" :key="project.id" class="glass-card overflow-hidden group">
            <div v-if="project.cover" class="aspect-video overflow-hidden">
              <img :src="project.cover" :alt="project.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
            </div>
            <div class="p-5">
              <h3 class="text-sm font-bold text-white">{{ project.title }}</h3>
              <p v-if="project.description" class="text-xs text-slate-400 mt-1.5 leading-relaxed line-clamp-3">{{ project.description }}</p>
              <div class="flex items-center gap-3 mt-3">
                <a v-if="project.github_url" :href="project.github_url" target="_blank" rel="noopener"
                  class="text-[11px] text-purple-400 hover:text-purple-300 font-medium transition-colors">
                  🐙 {{ t('cv.github') }}
                </a>
                <a v-if="project.link" :href="project.link" target="_blank" rel="noopener"
                  class="text-[11px] text-cyan-400 hover:text-cyan-300 font-medium transition-colors">
                  🌐 {{ t('cv.liveDemo') }}
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Skills Section -->
      <section v-if="cv.skills?.length" class="max-w-4xl mx-auto px-4 sm:px-6 mb-16">
        <div class="flex items-center gap-3 mb-8">
          <h2 class="text-xl font-bold text-white flex items-center gap-2">⚡ {{ t('cv.skills') }}</h2>
          <div class="section-line"></div>
        </div>
        <div class="flex flex-wrap gap-2">
          <span v-for="skill in cv.skills" :key="skill.id"
            class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
            :class="{
              'text-emerald-400 bg-emerald-400/10 border-emerald-400/20': skill.level === 'expert',
              'text-blue-400 bg-blue-400/10 border-blue-400/20': skill.level === 'advanced',
              'text-amber-400 bg-amber-400/10 border-amber-400/20': skill.level === 'intermediate',
              'text-slate-400 bg-white/5 border-white/10': !skill.level || skill.level === 'beginner',
            }">
            {{ skill.name }}
            <span v-if="skill.level" class="text-[10px] opacity-60 ms-1">• {{ t('levels.' + skill.level) }}</span>
          </span>
        </div>
      </section>

      <!-- Languages Section -->
      <section v-if="cv.languages?.length" class="max-w-4xl mx-auto px-4 sm:px-6 mb-16">
        <div class="flex items-center gap-3 mb-8">
          <h2 class="text-xl font-bold text-white flex items-center gap-2">🌍 {{ t('cv.languagesTitle') }}</h2>
          <div class="section-line"></div>
        </div>
        <div class="flex flex-wrap gap-2">
          <span v-for="lang in cv.languages" :key="lang.id"
            class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-300 bg-white/5 border border-white/10">
            {{ lang.name }}
            <span v-if="lang.level" class="text-[10px] text-slate-500 ms-1">• {{ t('levels.' + lang.level) }}</span>
          </span>
        </div>
      </section>

      <!-- Certifications Section -->
      <section v-if="cv.certifications?.length" class="max-w-4xl mx-auto px-4 sm:px-6 mb-16">
        <div class="flex items-center gap-3 mb-8">
          <h2 class="text-xl font-bold text-white flex items-center gap-2">🏅 {{ t('cv.certificationsTitle') }}</h2>
          <div class="section-line"></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="cert in cv.certifications" :key="cert.id" class="glass-card p-5 flex items-start gap-4">
            <img v-if="cert.photo" :src="cert.photo" :alt="cert.name" class="w-12 h-12 rounded-lg object-cover border border-white/10" />
            <div class="w-12 h-12 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-lg" v-else>🏅</div>
            <div class="flex-1 min-w-0">
              <h3 class="text-sm font-bold text-white">{{ cert.name }}</h3>
              <p class="text-xs text-slate-400 mt-0.5">{{ cert.organization }}</p>
              <p class="text-[11px] text-slate-500 mt-1">
                {{ formatDate(cert.issue_date) }}
                <span v-if="cert.expiration_date"> — {{ formatDate(cert.expiration_date) }}</span>
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Volunteer Section -->
      <section v-if="cv.volunteer_experiences?.length" class="max-w-4xl mx-auto px-4 sm:px-6 mb-16">
        <div class="flex items-center gap-3 mb-8">
          <h2 class="text-xl font-bold text-white flex items-center gap-2">🤝 {{ t('cv.volunteerTitle') }}</h2>
          <div class="section-line"></div>
        </div>
        <div class="space-y-4">
          <div v-for="vol in cv.volunteer_experiences" :key="vol.id" class="glass-card p-5">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h3 class="text-sm font-bold text-white">{{ vol.role }}</h3>
                <p class="text-xs text-blue-400 mt-0.5">{{ vol.organization }}</p>
              </div>
              <span class="text-[11px] text-slate-500 whitespace-nowrap">
                {{ formatDate(vol.start_date) }} — {{ formatDate(vol.end_date) }}
              </span>
            </div>
            <p v-if="vol.description" class="text-xs text-slate-400 mt-3 leading-relaxed">{{ vol.description }}</p>
          </div>
        </div>
      </section>
      </div><!-- /public-cv-content -->
    </template>
  </div>
</template>
