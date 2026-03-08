<script setup>
/**
 * CVPrintLayout — Professional CV template for PDF export.
 *
 * Layout: Dark navy sidebar (left 33%) + Clean white main area (right 67%).
 * Uses pixel units (794×1123 = A4 at 96 DPI) for pixel-perfect PDF output.
 * All styles are inline so html2canvas captures everything reliably.
 */
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useCVStore } from '@/modules/cv/stores/cv'
import { getSkillLevel, getLangLevel } from '@/modules/cv/composables/useLevelHelpers'

const { t, locale } = useI18n()
const cvStore = useCVStore()

const isRTL = computed(() => locale.value === 'ar')
const dir = computed(() => isRTL.value ? 'rtl' : 'ltr')
const fontFamily = computed(() =>
  isRTL.value
    ? "'Noto Sans Arabic','Segoe UI','Helvetica Neue',Arial,sans-serif"
    : "'Segoe UI','Helvetica Neue',Arial,sans-serif"
)

function fmt(d) {
  if (!d) return t('cv.present')
  const date = new Date(d)
  return date.toLocaleDateString(locale.value === 'ar' ? 'ar-SA' : 'en-US', { month: 'short', year: 'numeric' })
}

function skillPct(level) {
  return parseInt(getSkillLevel(level)) || 65
}

function langPct(level) {
  return parseInt(getLangLevel(level).width) || 50
}

/** Translate a raw level value (from DB) using the levels.* keys */
function translateLevel(level) {
  if (!level) return t('levels.intermediate')
  const key = String(level).toLowerCase().replace(/\s+/g, '_')
  return t(`levels.${key}`, level)
}

/**
 * Arabic-safe heading styles.
 * letter-spacing and text-transform:uppercase BREAK Arabic text in html2canvas —
 * Arabic has no uppercase, and letter-spacing disconnects ligatures (حروف متصلة).
 */
const sidebarHeadingStyle = computed(() => ({
  fontSize: '9px',
  fontWeight: '800',
  textTransform: isRTL.value ? 'none' : 'uppercase',
  letterSpacing: isRTL.value ? '0' : '2.5px',
  color: '#60a5fa',
  paddingBottom: '7px',
  marginBottom: '10px',
  borderBottom: '1px solid #1e3a5f',
  textAlign: isRTL.value ? 'right' : 'left',
}))

const mainHeadingStyle = computed(() => ({
  fontSize: '14px',
  fontWeight: '800',
  textTransform: isRTL.value ? 'none' : 'uppercase',
  letterSpacing: isRTL.value ? '0' : '2px',
  color: '#0f172a',
}))

/** Date badge style — consistent pill with centered text */
const dateBadgeStyle = {
  fontSize: '9px',
  color: '#64748b',
  background: '#f8fafc',
  padding: '4px 12px',
  borderRadius: '12px',
  fontWeight: '600',
  whiteSpace: 'nowrap',
  border: '1px solid #e2e8f0',
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  lineHeight: '1.4',
}
</script>

<template>
  <div id="cv-print-layout" style="display: none;">
    <div :dir="dir" :style="{ width:'794px', minHeight:'1123px', fontFamily: fontFamily, background:'#fff', display:'flex', flexDirection: isRTL ? 'row-reverse' : 'row', overflow:'hidden', color:'#1e293b', lineHeight:'1.5' }">

      <!-- ====== SIDEBAR ====== -->
      <div style="width:264px;min-width:264px;background:#0f172a;display:flex;flex-direction:column;">

        <!-- Photo + Identity -->
        <div style="padding:44px 0 28px;text-align:center;background:linear-gradient(180deg,#162d50 0%,#0f172a 100%);">
          <!-- Profile Photo -->
          <div v-if="cvStore.profilePhoto" style="width:130px;height:130px;margin:0 auto 18px;border-radius:50%;overflow:hidden;border:4px solid rgba(96,165,250,0.45);box-shadow:0 6px 24px rgba(0,0,0,0.5);">
            <img :src="cvStore.profilePhoto" alt="" crossorigin="anonymous" style="width:100%;height:100%;object-fit:cover;display:block;" />
          </div>
          <div v-else style="width:130px;height:130px;margin:0 auto 18px;border-radius:50%;background:#1e293b;border:4px solid rgba(96,165,250,0.25);display:flex;align-items:center;justify-content:center;">
            <span style="font-size:48px;color:#334155;">👤</span>
          </div>
          <!-- Name -->
          <div :style="{ fontSize:'20px', fontWeight:'800', color:'#fff', letterSpacing: isRTL ? '0' : '0.3px', padding:'0 20px', textAlign:'center' }">{{ cvStore.userName }}</div>
          <!-- Headline -->
          <div v-if="cvStore.headline" :style="{ fontSize:'11px', color:'#60a5fa', marginTop:'6px', fontWeight:'600', letterSpacing: isRTL ? '0' : '0.5px', padding:'0 20px', textAlign:'center' }">{{ cvStore.headline }}</div>
        </div>

        <!-- Sidebar Sections -->
        <div style="padding:24px 22px;flex:1;display:flex;flex-direction:column;gap:22px;">

          <!-- Contact Info -->
          <div v-if="cvStore.location || cvStore.phone || cvStore.contactEmail || cvStore.website || cvStore.linkedin || cvStore.github">
            <div :style="sidebarHeadingStyle">{{ t('print.contact') }}</div>

            <!-- Location -->
            <div v-if="cvStore.location" style="display:flex;align-items:center;gap:7px;margin-bottom:8px;">
              <div style="width:22px;height:22px;border-radius:50%;background:rgba(96,165,250,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="font-size:10px;">📍</span>
              </div>
              <span style="font-size:10px;color:#cbd5e1;">{{ cvStore.location }}</span>
            </div>

            <!-- Phone -->
            <div v-if="cvStore.phone" style="display:flex;align-items:center;gap:7px;margin-bottom:8px;">
              <div style="width:22px;height:22px;border-radius:50%;background:rgba(96,165,250,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="font-size:10px;">📞</span>
              </div>
              <span style="font-size:10px;color:#cbd5e1;">{{ cvStore.phone }}</span>
            </div>

            <!-- Email -->
            <div v-if="cvStore.contactEmail" style="display:flex;align-items:center;gap:7px;margin-bottom:8px;">
              <div style="width:22px;height:22px;border-radius:50%;background:rgba(96,165,250,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="font-size:10px;">✉️</span>
              </div>
              <span style="font-size:9px;color:#cbd5e1;word-break:break-all;">{{ cvStore.contactEmail }}</span>
            </div>

            <!-- Website -->
            <div v-if="cvStore.website" style="display:flex;align-items:center;gap:7px;margin-bottom:8px;">
              <div style="width:22px;height:22px;border-radius:50%;background:rgba(96,165,250,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="font-size:10px;">🌐</span>
              </div>
              <span style="font-size:9px;color:#60a5fa;word-break:break-all;">{{ cvStore.website.replace(/^https?:\/\//, '') }}</span>
            </div>

            <!-- LinkedIn -->
            <div v-if="cvStore.linkedin" style="display:flex;align-items:center;gap:7px;margin-bottom:8px;">
              <div style="width:22px;height:22px;border-radius:50%;background:rgba(96,165,250,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="font-size:10px;">💼</span>
              </div>
              <span style="font-size:9px;color:#60a5fa;word-break:break-all;">{{ cvStore.linkedin.replace(/^https?:\/\/(www\.)?/, '') }}</span>
            </div>

            <!-- GitHub -->
            <div v-if="cvStore.github" style="display:flex;align-items:center;gap:7px;margin-bottom:8px;">
              <div style="width:22px;height:22px;border-radius:50%;background:rgba(96,165,250,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="font-size:10px;">🐙</span>
              </div>
              <span style="font-size:9px;color:#60a5fa;word-break:break-all;">{{ cvStore.github.replace(/^https?:\/\/(www\.)?/, '') }}</span>
            </div>
          </div>

          <!-- Skills -->
          <div v-if="cvStore.skills.length">
            <div :style="sidebarHeadingStyle">{{ t('print.skills') }}</div>
            <div v-for="skill in cvStore.skills" :key="skill.id" style="margin-bottom:9px;">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                <span style="font-size:10px;font-weight:600;color:#e2e8f0;">{{ skill.name }}</span>
              </div>
              <div style="width:100%;height:4px;background:#1e293b;border-radius:2px;overflow:hidden;">
                <div :style="{ width: getSkillLevel(skill.level), height:'100%', borderRadius:'2px', background:'linear-gradient(90deg,#3b82f6,#60a5fa)' }"></div>
              </div>
            </div>
          </div>

          <!-- Languages -->
          <div v-if="cvStore.languages.length">
            <div :style="sidebarHeadingStyle">{{ t('print.languages') }}</div>
            <div v-for="lang in cvStore.languages" :key="lang.id" style="margin-bottom:12px;">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                <span style="font-size:10px;font-weight:600;color:#e2e8f0;">{{ lang.name }}</span>
                <span :style="{ fontSize:'8px', color:'#94a3b8', textTransform: isRTL ? 'none' : 'uppercase', letterSpacing: isRTL ? '0' : '0.5px' }">{{ translateLevel(lang.level) }}</span>
              </div>
              <div style="width:100%;height:4px;background:#1e293b;border-radius:2px;overflow:hidden;">
                <div :style="{ width: getLangLevel(lang.level).width, height:'100%', borderRadius:'2px', background:'linear-gradient(90deg,#8b5cf6,#a78bfa)' }"></div>
              </div>
            </div>
          </div>

          <!-- Certifications (sidebar — text only, ONLY if no cert has a photo; otherwise main area handles it) -->
          <div v-if="cvStore.certifications.length && !cvStore.certifications.some(c => c.photo)">
            <div :style="sidebarHeadingStyle">{{ t('print.certifications') }}</div>
            <div v-for="cert in cvStore.certifications" :key="cert.id" style="margin-bottom:10px;page-break-inside:avoid;">
              <div style="font-size:10px;font-weight:700;color:#f1f5f9;">{{ cert.name }}</div>
              <div style="font-size:9px;color:#94a3b8;margin-top:1px;">{{ cert.organization }}</div>
              <div style="font-size:8px;color:#64748b;margin-top:2px;">
                {{ fmt(cert.issue_date) }}<span v-if="cert.expiration_date"> — {{ fmt(cert.expiration_date) }}</span>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ====== MAIN CONTENT ====== -->
      <div :style="{ flex:'1', padding: isRTL ? '40px 40px 36px 36px' : '40px 36px 36px 40px', background:'#fff', display:'flex', flexDirection:'column', gap:'24px' }">

        <!-- ABOUT / SUMMARY -->
        <div v-if="cvStore.summary">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div style="width:28px;height:28px;border-radius:6px;background:#eff6ff;display:flex;align-items:center;justify-content:center;">
              <span style="font-size:13px;">💼</span>
            </div>
            <span :style="mainHeadingStyle">{{ t('print.aboutMe') }}</span>
          </div>
          <p :style="{ margin:'0', fontSize:'10.5px', color:'#475569', lineHeight:'1.85', borderLeft: isRTL ? 'none' : '3px solid #3b82f6', borderRight: isRTL ? '3px solid #3b82f6' : 'none', paddingLeft: isRTL ? '0' : '14px', paddingRight: isRTL ? '14px' : '0' }">{{ cvStore.summary }}</p>
        </div>

        <!-- EXPERIENCE -->
        <div v-if="cvStore.experiences.length">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <div style="width:28px;height:28px;border-radius:6px;background:#eff6ff;display:flex;align-items:center;justify-content:center;">
              <span style="font-size:13px;">🏢</span>
            </div>
            <span :style="mainHeadingStyle">{{ t('print.experience') }}</span>
          </div>

          <div v-for="(exp, i) in cvStore.experiences" :key="exp.id" style="page-break-inside:avoid;">
            <div :style="{ paddingBottom: i < cvStore.experiences.length - 1 ? '14px' : '0', marginBottom: i < cvStore.experiences.length - 1 ? '14px' : '0', borderBottom: i < cvStore.experiences.length - 1 ? '1px solid #f1f5f9' : 'none' }">
              <div style="display:flex;justify-content:space-between;align-items:center;">
                <div style="font-size:12.5px;font-weight:700;color:#0f172a;">{{ exp.position }}</div>
                <div :style="dateBadgeStyle">
                  {{ fmt(exp.start_date) }} — {{ fmt(exp.end_date) }}
                </div>
              </div>
              <div style="font-size:10.5px;color:#3b82f6;font-weight:700;margin:4px 0 6px;">{{ exp.company }}</div>
              <p v-if="exp.description" style="margin:0;font-size:10px;color:#64748b;line-height:1.75;white-space:pre-line;">{{ exp.description }}</p>
            </div>
          </div>
        </div>

        <!-- EDUCATION -->
        <div v-if="cvStore.educations.length">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <div style="width:28px;height:28px;border-radius:6px;background:#eff6ff;display:flex;align-items:center;justify-content:center;">
              <span style="font-size:13px;">🎓</span>
            </div>
            <span :style="mainHeadingStyle">{{ t('print.education') }}</span>
          </div>

          <div v-for="(edu, i) in cvStore.educations" :key="edu.id" style="page-break-inside:avoid;">
            <div :style="{ paddingBottom: i < cvStore.educations.length - 1 ? '14px' : '0', marginBottom: i < cvStore.educations.length - 1 ? '14px' : '0', borderBottom: i < cvStore.educations.length - 1 ? '1px solid #f1f5f9' : 'none' }">
              <div style="display:flex;justify-content:space-between;align-items:center;">
                <div style="font-size:12.5px;font-weight:700;color:#0f172a;">{{ edu.degree }}</div>
                <div :style="dateBadgeStyle">
                  {{ fmt(edu.start_date) }} — {{ fmt(edu.end_date) }}
                </div>
              </div>
              <div style="font-size:10.5px;color:#3b82f6;font-weight:600;margin-top:4px;">{{ edu.institution }}</div>
              <div v-if="edu.field_of_study" style="font-size:9.5px;color:#94a3b8;font-style:italic;margin-top:3px;">{{ edu.field_of_study }}</div>
            </div>
          </div>
        </div>

        <!-- PROJECTS -->
        <div v-if="cvStore.projects.length">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <div style="width:28px;height:28px;border-radius:6px;background:#eff6ff;display:flex;align-items:center;justify-content:center;">
              <span style="font-size:13px;">🚀</span>
            </div>
            <span :style="mainHeadingStyle">{{ t('print.projects') }}</span>
          </div>

          <div v-for="(proj, i) in cvStore.projects" :key="proj.id" style="page-break-inside:avoid;">
            <div :style="{ paddingBottom: i < cvStore.projects.length - 1 ? '14px' : '0', marginBottom: i < cvStore.projects.length - 1 ? '14px' : '0', borderBottom: i < cvStore.projects.length - 1 ? '1px solid #f1f5f9' : 'none' }">
              <div style="display:flex;gap:12px;align-items:flex-start;">
                <!-- Project cover thumbnail (compact card) -->
                <div v-if="proj.cover" style="width:90px;min-width:90px;height:64px;border-radius:6px;overflow:hidden;border:1px solid #e2e8f0;flex-shrink:0;">
                  <img :src="proj.cover" :alt="proj.title" crossorigin="anonymous" style="width:100%;height:100%;object-fit:cover;display:block;" />
                </div>
                <div style="flex:1;min-width:0;">
                  <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div style="font-size:12.5px;font-weight:700;color:#0f172a;">{{ proj.title }}</div>
                    <div v-if="proj.start_date" :style="dateBadgeStyle">
                      {{ fmt(proj.start_date) }} — {{ fmt(proj.end_date) }}
                    </div>
                  </div>
                  <!-- Links -->
                  <div v-if="proj.github_url || proj.link" style="display:flex;gap:12px;margin-top:3px;">
                    <span v-if="proj.github_url" style="font-size:9px;color:#3b82f6;font-weight:700;">● {{ t('cv.github') }}</span>
                    <span v-if="proj.link" style="font-size:9px;color:#3b82f6;font-weight:700;">● {{ t('cv.liveDemo') }}</span>
                  </div>
                  <p v-if="proj.description" style="margin:4px 0 0;font-size:10px;color:#64748b;line-height:1.7;">{{ proj.description }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- CERTIFICATIONS (with images — main area) -->
        <div v-if="cvStore.certifications.length">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <div style="width:28px;height:28px;border-radius:6px;background:#fffbeb;display:flex;align-items:center;justify-content:center;">
              <span style="font-size:13px;">🏅</span>
            </div>
            <span :style="mainHeadingStyle">{{ t('print.certCredentials') }}</span>
          </div>

          <div style="display:flex;flex-wrap:wrap;gap:12px;">
            <div v-for="cert in cvStore.certifications" :key="cert.id" style="width:calc(50% - 6px);page-break-inside:avoid;">
              <div style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;background:#fafafa;">
                <div v-if="cert.photo" style="width:100%;height:100px;overflow:hidden;background:#f1f5f9;">
                  <img :src="cert.photo" :alt="cert.name" crossorigin="anonymous" style="width:100%;height:100%;object-fit:cover;display:block;" />
                </div>
                <div style="padding:8px 10px;">
                  <div style="font-size:10px;font-weight:700;color:#0f172a;line-height:1.4;">{{ cert.name }}</div>
                  <div style="font-size:9px;color:#3b82f6;font-weight:600;margin-top:2px;">{{ cert.organization }}</div>
                  <div style="font-size:8px;color:#94a3b8;margin-top:3px;">
                    {{ fmt(cert.issue_date) }}<span v-if="cert.expiration_date"> — {{ fmt(cert.expiration_date) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- VOLUNTEER -->
        <div v-if="cvStore.volunteerExperiences.length">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <div style="width:28px;height:28px;border-radius:6px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;">
              <span style="font-size:13px;">💚</span>
            </div>
            <span :style="mainHeadingStyle">{{ t('print.volunteer') }}</span>
          </div>

          <div v-for="(vol, i) in cvStore.volunteerExperiences" :key="vol.id" style="page-break-inside:avoid;">
            <div :style="{ paddingBottom: i < cvStore.volunteerExperiences.length - 1 ? '14px' : '0', marginBottom: i < cvStore.volunteerExperiences.length - 1 ? '14px' : '0', borderBottom: i < cvStore.volunteerExperiences.length - 1 ? '1px solid #f1f5f9' : 'none' }">
              <div style="display:flex;justify-content:space-between;align-items:center;">
                <div style="font-size:12.5px;font-weight:700;color:#0f172a;">{{ vol.role }}</div>
                <div :style="dateBadgeStyle">
                  {{ fmt(vol.start_date) }} — {{ fmt(vol.end_date) }}
                </div>
              </div>
              <div style="font-size:10.5px;color:#0d9488;font-weight:700;margin:4px 0 6px;">{{ vol.organization }}</div>
              <p v-if="vol.description" style="margin:0;font-size:10px;color:#64748b;line-height:1.75;">{{ vol.description }}</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>
