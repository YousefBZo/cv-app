<script setup>
import { onMounted, watch, ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useCVStore } from '@/modules/cv/stores/cv'
import { useAuthStore } from '@/modules/auth/stores/auth'
import { usePagination } from '@/modules/cv/composables/usePagination'
import { useToastStore } from '@/shared/stores/toast'
import {
  validateProfile,
  validateExperience,
  validateEducation,
  validateProject,
  validateCertification,
  validateVolunteer,
  hasErrors,
} from '@/modules/cv/composables/useValidation'
import EditModal from '@/shared/components/EditModal.vue'
import LoadingSpinner from '@/shared/components/LoadingSpinner.vue'

const cvStore = useCVStore()
const authStore = useAuthStore()
const toast = useToastStore()
const router = useRouter()
const isVisible = ref(false)

onMounted(() => {
  cvStore.fetchCV()
})

watch(() => cvStore.profile, (p) => { if (p) isVisible.value = true })
setTimeout(() => { if (!isVisible.value) isVisible.value = true }, 10000)

// --- PAGINATION ---
const { displayed: displayedExperiences, hasMore: hasMoreExp, isExpanded: isExpandedExp, showAll: showAllExp, showLess: showLessExp } = usePagination(() => cvStore.experiences, 5)
const { displayed: displayedSkills, hasMore: hasMoreSkills, isExpanded: isExpandedSkills, showAll: showAllSkills, showLess: showLessSkills } = usePagination(() => cvStore.skills, 9)
const { displayed: displayedEducation, hasMore: hasMoreEdu, isExpanded: isExpandedEdu, showAll: showAllEdu, showLess: showLessEdu } = usePagination(() => cvStore.educations, 4)
const { displayed: displayedProjects, hasMore: hasMoreProjects, isExpanded: isExpandedProjects, showAll: showAllProjects, showLess: showLessProjects } = usePagination(() => cvStore.projects, 6)
const { displayed: displayedVolunteer, hasMore: hasMoreVol, isExpanded: isExpandedVol, showAll: showAllVol, showLess: showLessVol } = usePagination(() => cvStore.volunteerExperiences, 4)
const { displayed: displayedCertifications, hasMore: hasMoreCert, isExpanded: isExpandedCert, showAll: showAllCert, showLess: showLessCert } = usePagination(() => cvStore.certifications, 4)
const { displayed: displayedLanguages, hasMore: hasMoreLang, isExpanded: isExpandedLang, showAll: showAllLang, showLess: showLessLang } = usePagination(() => cvStore.languages, 6)

// --- EDIT MODAL STATE ---
const modalVisible = ref(false)
const modalTitle = ref('')
const modalSection = ref('')
const modalItemId = ref(null)
const modalLoading = ref(false)
const modalErrors = ref({})
const editForm = reactive({})

function openEditModal(section, item, title) {
  modalSection.value = section
  modalItemId.value = item.id
  modalTitle.value = title
  modalErrors.value = {}
  Object.keys(editForm).forEach(k => delete editForm[k])
  Object.assign(editForm, JSON.parse(JSON.stringify(item)))
  for (const key of ['start_date', 'end_date', 'issue_date', 'expiration_date']) {
    if (editForm[key]) {
      editForm[key] = editForm[key].substring(0, 10)
    }
  }
  modalVisible.value = true
}

function closeModal() {
  modalVisible.value = false
  modalErrors.value = {}
}

async function handleModalSave() {
  modalErrors.value = {}

  // Client-side validation based on section
  const section = modalSection.value
  let v = {}
  if (section === 'experience') v = validateExperience(editForm)
  else if (section === 'education') v = validateEducation(editForm)
  else if (section === 'project') v = validateProject(editForm)
  else if (section === 'certification') v = validateCertification(editForm)
  else if (section === 'volunteer') v = validateVolunteer(editForm)

  if (hasErrors(v)) {
    modalErrors.value = v
    return
  }

  modalLoading.value = true
  try {
    let payload
    if (section === 'skill' || section === 'language') {
      // For skill/language, only send the level
      payload = { level: editForm.level }
    } else {
      payload = { ...editForm }
      delete payload.id
      delete payload.profile_id
      delete payload.created_at
      delete payload.updated_at
      delete payload.pivot
      delete payload.photo
      delete payload.cover
    }
    await cvStore.updateItem(modalSection.value, modalItemId.value, payload)
    closeModal()
  } catch (err) {
    if (err.response?.status === 422) {
      modalErrors.value = err.response.data.errors || {}
    } else {
      modalErrors.value = { general: [err.response?.data?.message || 'Something went wrong. Please try again.'] }
    }
  } finally {
    modalLoading.value = false
  }
}

async function handleModalDelete() {
  if (!confirm('Are you sure you want to delete this item?')) return
  modalLoading.value = true
  try {
    await cvStore.deleteItem(modalSection.value, modalItemId.value)
    closeModal()
  } catch {
    // Error handled by store
  } finally {
    modalLoading.value = false
  }
}

// --- PROFILE EDIT MODAL ---
const profileModalVisible = ref(false)
const profileForm = reactive({ headline: '', summary: '', location: '' })
const profilePhoto = ref(null)
const profilePhotoPreview = ref(null)
const profileModalLoading = ref(false)
const profileModalErrors = ref({})

// User account fields
const userNameForm = reactive({ name: '' })
const userNameLoading = ref(false)
const userNameErrors = ref({})

const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' })
const passwordLoading = ref(false)
const passwordErrors = ref({})

// Which section is expanded in profile modal
const profileTab = ref('profile') // 'profile' | 'account' | 'password'

function openProfileModal() {
  if (!cvStore.hasProfile) {
    router.push({ name: 'Profile' })
    return
  }
  profileModalErrors.value = {}
  userNameErrors.value = {}
  passwordErrors.value = {}
  profileForm.headline = cvStore.headline
  profileForm.summary = cvStore.summary
  profileForm.location = cvStore.location
  profilePhoto.value = null
  profilePhotoPreview.value = cvStore.profilePhoto
  userNameForm.name = authStore.userName
  passwordForm.current_password = ''
  passwordForm.password = ''
  passwordForm.password_confirmation = ''
  profileTab.value = 'profile'
  profileModalVisible.value = true
}

async function handleProfileSave() {
  profileModalErrors.value = {}

  const v = validateProfile(profileForm)
  if (hasErrors(v)) {
    profileModalErrors.value = v
    return
  }

  profileModalLoading.value = true
  try {
    const data = new FormData()
    data.append('headline', profileForm.headline)
    data.append('summary', profileForm.summary)
    data.append('location', profileForm.location)
    if (profilePhoto.value) {
      data.append('photo', profilePhoto.value)
    }
    data.append('_method', 'PUT')
    await cvStore.updateItem('profile', '', data, true)
    profileModalVisible.value = false
  } catch (err) {
    if (err.response?.status === 422) {
      profileModalErrors.value = err.response.data.errors || {}
    }
  } finally {
    profileModalLoading.value = false
  }
}

async function handleDeleteAccount() {
  if (!confirm('⚠️ Are you sure you want to delete your ENTIRE account? This cannot be undone!')) return
  if (!confirm('This will delete your profile, all CV data, and your account. Continue?')) return
  profileModalLoading.value = true
  try {
    await cvStore.deleteProfile()
    router.push({ name: 'Login' })
  } catch {
    // handled by store
  } finally {
    profileModalLoading.value = false
  }
}

function onProfilePhotoChange(e) {
  const file = e.target.files[0]
  profilePhoto.value = file
  if (file) {
    profilePhotoPreview.value = URL.createObjectURL(file)
  }
}

async function handleUserNameSave() {
  userNameErrors.value = {}
  if (!userNameForm.name || userNameForm.name.trim().length < 2) {
    userNameErrors.value = { name: ['Name must be at least 2 characters.'] }
    return
  }
  userNameLoading.value = true
  try {
    await authStore.updateUserName(userNameForm.name.trim())
    toast.showSuccess('Name updated successfully!')
  } catch (err) {
    if (err.response?.status === 422) {
      userNameErrors.value = err.response.data.errors || {}
    } else {
      userNameErrors.value = { name: [err.response?.data?.message || 'Failed to update name.'] }
    }
  } finally {
    userNameLoading.value = false
  }
}

async function handlePasswordSave() {
  passwordErrors.value = {}
  const v = {}
  if (!passwordForm.current_password) v.current_password = ['Current password is required.']
  if (!passwordForm.password || passwordForm.password.length < 8) v.password = ['New password must be at least 8 characters.']
  else if (passwordForm.password !== passwordForm.password_confirmation) v.password_confirmation = ['Passwords do not match.']
  if (Object.keys(v).length > 0) { passwordErrors.value = v; return }

  passwordLoading.value = true
  try {
    await authStore.updateUserPassword(
      passwordForm.current_password,
      passwordForm.password,
      passwordForm.password_confirmation
    )
    toast.showSuccess('Password updated successfully!')
    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
  } catch (err) {
    if (err.response?.status === 422) {
      passwordErrors.value = err.response.data.errors || {}
    } else {
      passwordErrors.value = { current_password: [err.response?.data?.message || 'Failed to update password.'] }
    }
  } finally {
    passwordLoading.value = false
  }
}

// Helpers
const getLangLevel = (level) => {
  if (!level) return { width: '50%', label: 'Conversational', color: 'from-slate-500 to-slate-400' }
  const l = String(level).toLowerCase()
  if (l.includes('native') || l.includes('fluent')) return { width: '100%', label: level, color: 'from-emerald-500 to-teal-400' }
  if (l.includes('advanced') || l.includes('proficient')) return { width: '80%', label: level, color: 'from-blue-500 to-cyan-400' }
  if (l.includes('intermediate') || l.includes('conversational')) return { width: '55%', label: level, color: 'from-amber-500 to-yellow-400' }
  if (l.includes('beginner') || l.includes('basic') || l.includes('elementary')) return { width: '30%', label: level, color: 'from-rose-500 to-pink-400' }
  return { width: '50%', label: level, color: 'from-slate-500 to-slate-400' }
}

const getSkillLevel = (level) => {
  if (!level) return '70%'
  const l = String(level).toLowerCase()
  if (l.includes('expert') || l.includes('senior')) return '95%'
  if (l.includes('advanced') || l.includes('intermediate')) return '75%'
  if (l.includes('beginner') || l.includes('junior')) return '35%'
  return '65%'
}
</script>

<template>
  <div class="min-h-screen text-slate-100 font-sans selection:bg-blue-500/30 overflow-x-hidden pb-20"
    style="background-image: linear-gradient(135deg, #000b18 0%, #00264d 100%);">

    <!-- Loading State -->
    <div v-if="cvStore.loading" class="flex items-center justify-center min-h-[60vh]">
      <div class="text-center space-y-4">
        <LoadingSpinner class="w-8 h-8 mx-auto text-blue-400" />
        <p class="text-slate-500 text-sm">Loading your CV...</p>
      </div>
    </div>

    <!-- No Profile Banner -->
    <div v-else-if="!cvStore.hasProfile && !cvStore.loading" class="flex items-center justify-center min-h-[60vh]">
      <div class="text-center space-y-6 max-w-md mx-auto px-6">
        <div class="text-6xl">👤</div>
        <h2 class="text-2xl font-bold text-white">No Profile Yet</h2>
        <p class="text-slate-400">You haven't created your profile yet. Create one to start building your CV!</p>
        <router-link to="/profile"
          class="inline-block px-8 py-3 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:shadow-lg hover:shadow-blue-500/25 transition-all">
          Create Profile →
        </router-link>
      </div>
    </div>

    <!-- CV Content -->
    <template v-else>
      <!-- 1. HERO SECTION -->
      <section class="flex flex-col items-center justify-center py-24 px-6 transition-opacity duration-1000 cursor-pointer relative group"
        :class="isVisible ? 'opacity-100' : 'opacity-0'" @click="openProfileModal">
        <div class="absolute top-28 right-8 opacity-0 group-hover:opacity-100 transition-opacity text-xs text-blue-400 font-medium">
          ✏️ Click to edit profile
        </div>
        <div class="relative mb-8">
          <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-cyan-400 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000"></div>
          <div class="relative bg-slate-900 rounded-full p-1 border border-white/10 shadow-2xl">
            <img v-if="cvStore.profilePhoto" :src="cvStore.profilePhoto" alt="Profile" class="rounded-full w-44 h-44 object-cover" />
            <div v-else class="rounded-full w-44 h-44 bg-slate-800 flex items-center justify-center text-slate-400 border border-dashed border-slate-600">
              <span class="text-sm font-medium uppercase tracking-widest text-center px-4">No Photo</span>
            </div>
          </div>
        </div>
        <div class="text-center max-w-2xl mx-auto space-y-4">
          <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-b from-white to-slate-400">
            {{ cvStore.headline }}
          </h1>
          <p class="text-lg md:text-xl text-slate-300 font-light leading-relaxed italic">"{{ cvStore.summary }}"</p>
          <div class="flex items-center justify-center gap-2 pt-4 text-sm font-medium text-blue-400 uppercase tracking-widest">
            <span class="w-8 h-[1px] bg-blue-500/50"></span>
            <span>📍 {{ cvStore.location }}</span>
            <span class="w-8 h-[1px] bg-blue-500/50"></span>
          </div>
        </div>
      </section>

      <!-- 2. PROJECTS SECTION -->
      <section v-if="isVisible" class="max-w-4xl mx-auto py-12 px-6">
        <div class="flex items-center gap-4 mb-12">
          <h2 class="text-3xl font-bold tracking-tight">Featured Projects</h2>
          <div class="h-px flex-1 bg-gradient-to-r from-blue-500/50 to-transparent"></div>
        </div>
        <div v-if="displayedProjects.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-for="project in displayedProjects" :key="project.id"
            @click="openEditModal('project', project, 'Edit Project')"
            class="group bg-white/5 border border-white/10 rounded-3xl overflow-hidden hover:bg-white/[0.08] hover:border-blue-500/40 transition-all duration-500 cursor-pointer">
            <div v-if="project.cover" class="w-full h-44 overflow-hidden">
              <img :src="project.cover" :alt="project.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
            </div>
            <div v-else class="w-full h-44 bg-slate-800/50 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
              </svg>
            </div>
            <div class="p-6">
              <div class="flex justify-between items-start mb-4">
                <h3 class="text-xl font-bold text-white group-hover:text-blue-400 transition-colors">{{ project.title }}</h3>
                <div class="flex gap-2" @click.stop>
                  <a v-if="project.github_url" :href="project.github_url" target="_blank" class="text-slate-500 hover:text-white transition-colors" title="GitHub">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                    </svg>
                  </a>
                  <a v-if="project.link" :href="project.link" target="_blank" class="text-slate-500 hover:text-white transition-colors" title="Live Demo">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </a>
                </div>
              </div>
              <p class="text-slate-400 text-sm leading-relaxed mb-4">{{ project.description }}</p>
              <div class="text-xs text-blue-400 font-semibold">
                {{ project.start_date?.substring(0, 10) ?? '' }}
                <span v-if="project.end_date"> — {{ project.end_date?.substring(0, 10) }}</span>
                <span v-else-if="project.start_date"> — Present</span>
              </div>
            </div>
          </div>
        </div>
        <p v-else class="text-center text-slate-600 text-sm py-8">No projects added yet. <router-link to="/project" class="text-blue-400 hover:underline">Add one →</router-link></p>
        <div class="mt-8 flex justify-center gap-4">
          <button v-if="hasMoreProjects" @click="showAllProjects" class="text-blue-400 font-bold uppercase text-xs tracking-widest hover:underline">+ More Projects</button>
          <button v-if="isExpandedProjects" @click="showLessProjects" class="text-slate-500 font-bold uppercase text-xs tracking-widest hover:underline">− Show Less</button>
        </div>
      </section>

      <!-- 3. EXPERIENCE SECTION -->
      <section v-if="isVisible" class="max-w-4xl mx-auto py-12 px-6">
        <div class="flex items-center gap-4 mb-12">
          <h2 class="text-3xl font-bold tracking-tight text-white">Experience</h2>
          <div class="h-px flex-1 bg-gradient-to-r from-blue-500/50 to-transparent"></div>
        </div>
        <div v-if="displayedExperiences.length" class="relative border-l border-slate-700 ml-3 space-y-12">
          <div v-for="exp in displayedExperiences" :key="exp.id" class="relative pl-8 group cursor-pointer"
            @click="openEditModal('experience', exp, 'Edit Experience')">
            <div class="absolute -left-[9px] top-2 w-4 h-4 rounded-full bg-slate-900 border-2 border-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.5)]"></div>
            <div class="bg-white/5 border border-white/10 p-6 rounded-2xl shadow-xl hover:bg-white/[0.08] hover:border-blue-500/30 transition-all">
              <div class="flex flex-wrap justify-between items-start gap-2 mb-4">
                <div>
                  <h3 class="text-xl font-bold text-white">{{ exp.position }}</h3>
                  <p class="text-blue-400 font-medium text-sm uppercase">{{ exp.company }}</p>
                </div>
                <div class="px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-300 text-xs font-semibold">
                  {{ exp.start_date?.substring(0, 10) }} — {{ exp.end_date?.substring(0, 10) || 'Present' }}
                </div>
              </div>
              <p class="text-slate-400 leading-relaxed text-sm whitespace-pre-line">{{ exp.description }}</p>
            </div>
          </div>
        </div>
        <p v-else class="text-center text-slate-600 text-sm py-8">No experience added yet. <router-link to="/experience" class="text-blue-400 hover:underline">Add one →</router-link></p>
        <div class="mt-12 flex justify-center gap-4">
          <button v-if="hasMoreExp" @click="showAllExp" class="text-blue-400 font-bold uppercase text-xs tracking-widest">+ View All Experience</button>
          <button v-if="isExpandedExp" @click="showLessExp" class="text-slate-500 font-bold uppercase text-xs tracking-widest hover:underline">− Show Less</button>
        </div>
      </section>

      <!-- 4. SKILLS SECTION -->
      <section v-if="isVisible" class="max-w-4xl mx-auto py-12 px-6">
        <div class="flex items-center gap-4 mb-12">
          <h2 class="text-3xl font-bold tracking-tight text-white">Skills</h2>
          <div class="h-px flex-1 bg-gradient-to-r from-blue-500/50 to-transparent"></div>
        </div>
        <div v-if="displayedSkills.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="(skill, idx) in displayedSkills" :key="idx"
            @click="openEditModal('skill', skill, 'Skill: ' + skill.name)"
            class="bg-white/5 p-5 rounded-2xl border border-white/10 cursor-pointer hover:bg-white/[0.08] hover:border-blue-500/30 transition-all">
            <div class="flex justify-between items-end mb-4">
              <div>
                <div class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1">{{ skill.level ?? 'Proficient' }}</div>
                <div class="text-lg font-bold text-white">{{ skill.name }}</div>
              </div>
            </div>
            <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-blue-600 to-cyan-400 transition-all duration-1000"
                :style="{ width: isVisible ? getSkillLevel(skill.level) : '0%' }"></div>
            </div>
          </div>
        </div>
        <p v-else class="text-center text-slate-600 text-sm py-8">No skills added yet. <router-link to="/skill" class="text-blue-400 hover:underline">Add some →</router-link></p>
        <div class="mt-8 flex justify-center gap-4">
          <button v-if="hasMoreSkills" @click="showAllSkills" class="text-blue-400 text-xs font-bold uppercase tracking-widest">+ See All Skills</button>
          <button v-if="isExpandedSkills" @click="showLessSkills" class="text-slate-500 text-xs font-bold uppercase tracking-widest hover:underline">− Show Less</button>
        </div>
      </section>

      <!-- 5. EDUCATION SECTION -->
      <section v-if="isVisible" class="max-w-4xl mx-auto py-12 px-6">
        <div class="flex items-center gap-4 mb-12">
          <h2 class="text-3xl font-bold tracking-tight text-white">Education</h2>
          <div class="h-px flex-1 bg-gradient-to-r from-blue-500/50 to-transparent"></div>
        </div>
        <div v-if="displayedEducation.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-for="edu in displayedEducation" :key="edu.id"
            @click="openEditModal('education', edu, 'Edit Education')"
            class="bg-white/5 border border-white/10 p-6 rounded-2xl shadow-lg cursor-pointer hover:bg-white/[0.08] hover:border-blue-500/30 transition-all">
            <div class="flex items-start gap-4">
              <div class="p-3 rounded-lg bg-blue-500/10 text-blue-400">
                <svg xmlns="http://www.w3.org" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 14l9-5-9-5-9 5 9 5z" /><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /></svg>
              </div>
              <div>
                <div class="text-blue-400 text-xs font-bold mb-1">{{ edu.start_date?.substring(0, 10) }} — {{ edu.end_date?.substring(0, 10) || 'Present' }}</div>
                <h3 class="text-xl font-bold text-white mb-1">{{ edu.degree }}</h3>
                <p class="text-slate-300 font-medium">{{ edu.institution }}</p>
                <p v-if="edu.field_of_study" class="text-slate-500 text-sm mt-2 italic">{{ edu.field_of_study }}</p>
              </div>
            </div>
          </div>
        </div>
        <p v-else class="text-center text-slate-600 text-sm py-8">No education added yet. <router-link to="/education" class="text-blue-400 hover:underline">Add one →</router-link></p>
        <div class="mt-12 flex justify-center gap-4">
          <button v-if="hasMoreEdu" @click="showAllEdu" class="px-8 py-3 rounded-full border border-slate-700 text-slate-400 hover:text-blue-400 transition-all text-xs font-bold uppercase tracking-widest">View More Education</button>
          <button v-if="isExpandedEdu" @click="showLessEdu" class="px-8 py-3 rounded-full border border-slate-700 text-slate-500 hover:text-blue-400 transition-all text-xs font-bold uppercase tracking-widest">− Show Less</button>
        </div>
      </section>

      <!-- 6. VOLUNTEER SECTION -->
      <section v-if="isVisible" class="max-w-4xl mx-auto py-12 px-6">
        <div class="flex items-center gap-4 mb-12">
          <h2 class="text-3xl font-bold tracking-tight text-white">Volunteer Experience</h2>
          <div class="h-px flex-1 bg-gradient-to-r from-blue-500/50 to-transparent"></div>
        </div>
        <div v-if="displayedVolunteer.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-for="vol in displayedVolunteer" :key="vol.id"
            @click="openEditModal('volunteer', vol, 'Edit Volunteer Experience')"
            class="bg-white/5 border border-white/10 p-6 rounded-2xl shadow-lg hover:bg-white/[0.08] hover:border-teal-500/30 transition-all duration-300 cursor-pointer">
            <div class="flex items-start gap-4">
              <div class="p-3 rounded-lg bg-teal-500/10 text-teal-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
              </div>
              <div>
                <div class="text-teal-400 text-xs font-bold mb-1">{{ vol.start_date?.substring(0, 10) }} — {{ vol.end_date?.substring(0, 10) || 'Present' }}</div>
                <h3 class="text-xl font-bold text-white mb-1">{{ vol.role }}</h3>
                <p class="text-slate-300 font-medium">{{ vol.organization }}</p>
                <p v-if="vol.description" class="text-slate-500 text-sm mt-2 italic">{{ vol.description }}</p>
              </div>
            </div>
          </div>
        </div>
        <p v-else class="text-center text-slate-600 text-sm py-8">No volunteer experience added yet. <router-link to="/volunteer" class="text-teal-400 hover:underline">Add one →</router-link></p>
        <div class="mt-12 flex justify-center gap-4">
          <button v-if="hasMoreVol" @click="showAllVol" class="px-8 py-3 rounded-full border border-slate-700 text-slate-400 hover:text-teal-400 hover:border-teal-500/30 transition-all text-xs font-bold uppercase tracking-widest">View More</button>
          <button v-if="isExpandedVol" @click="showLessVol" class="px-8 py-3 rounded-full border border-slate-700 text-slate-500 hover:text-teal-400 transition-all text-xs font-bold uppercase tracking-widest">− Show Less</button>
        </div>
      </section>

      <!-- 7. CERTIFICATIONS SECTION -->
      <section v-if="isVisible" class="max-w-4xl mx-auto py-12 px-6">
        <div class="flex items-center gap-4 mb-12">
          <h2 class="text-3xl font-bold tracking-tight text-white">Certifications</h2>
          <div class="h-px flex-1 bg-gradient-to-r from-blue-500/50 to-transparent"></div>
        </div>
        <div v-if="displayedCertifications.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-for="cert in displayedCertifications" :key="cert.id"
            @click="openEditModal('certification', cert, 'Edit Certification')"
            class="bg-white/5 border border-white/10 p-6 rounded-2xl shadow-lg hover:bg-white/[0.08] hover:border-amber-500/30 transition-all duration-300 cursor-pointer">
            <div class="flex items-start gap-4">
              <div class="p-3 rounded-lg bg-amber-500/10 text-amber-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
              </div>
              <div class="flex-1">
                <div class="text-amber-400 text-xs font-bold mb-1">
                  {{ cert.issue_date?.substring(0, 10) }}
                  <span v-if="cert.expiration_date"> — {{ cert.expiration_date?.substring(0, 10) }}</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-1">{{ cert.name }}</h3>
                <p class="text-slate-300 font-medium">{{ cert.organization }}</p>
                <div v-if="cert.photo" class="mt-3">
                  <img :src="cert.photo" :alt="cert.name" class="h-16 rounded-lg object-contain border border-white/10" />
                </div>
              </div>
            </div>
          </div>
        </div>
        <p v-else class="text-center text-slate-600 text-sm py-8">No certifications added yet. <router-link to="/certification" class="text-amber-400 hover:underline">Add one →</router-link></p>
        <div class="mt-12 flex justify-center gap-4">
          <button v-if="hasMoreCert" @click="showAllCert" class="px-8 py-3 rounded-full border border-slate-700 text-slate-400 hover:text-amber-400 hover:border-amber-500/30 transition-all text-xs font-bold uppercase tracking-widest">View More Certifications</button>
          <button v-if="isExpandedCert" @click="showLessCert" class="px-8 py-3 rounded-full border border-slate-700 text-slate-500 hover:text-amber-400 transition-all text-xs font-bold uppercase tracking-widest">− Show Less</button>
        </div>
      </section>

      <!-- 8. LANGUAGES SECTION -->
      <section v-if="isVisible" class="max-w-4xl mx-auto py-12 px-6">
        <div class="flex items-center gap-4 mb-12">
          <h2 class="text-3xl font-bold tracking-tight text-white">Languages</h2>
          <div class="h-px flex-1 bg-gradient-to-r from-blue-500/50 to-transparent"></div>
        </div>
        <div v-if="displayedLanguages.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="lang in displayedLanguages" :key="lang.id"
            @click="openEditModal('language', lang, 'Language: ' + lang.name)"
            class="bg-white/5 border border-white/10 p-5 rounded-2xl hover:bg-white/[0.08] hover:border-purple-500/30 transition-all duration-300 cursor-pointer">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-bold text-white">{{ lang.name }}</h3>
                <p class="text-xs font-semibold uppercase tracking-wider" :class="{
                  'text-emerald-400': getLangLevel(lang.level).color.includes('emerald'),
                  'text-blue-400': getLangLevel(lang.level).color.includes('blue'),
                  'text-amber-400': getLangLevel(lang.level).color.includes('amber'),
                  'text-rose-400': getLangLevel(lang.level).color.includes('rose'),
                  'text-slate-400': getLangLevel(lang.level).color.includes('slate'),
                }">{{ getLangLevel(lang.level).label }}</p>
              </div>
            </div>
            <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
              <div class="h-full rounded-full bg-gradient-to-r transition-all duration-1000"
                :class="getLangLevel(lang.level).color"
                :style="{ width: isVisible ? getLangLevel(lang.level).width : '0%' }"></div>
            </div>
          </div>
        </div>
        <p v-else class="text-center text-slate-600 text-sm py-8">No languages added yet. <router-link to="/language" class="text-purple-400 hover:underline">Add some →</router-link></p>
        <div class="mt-8 flex justify-center gap-4">
          <button v-if="hasMoreLang" @click="showAllLang" class="text-purple-400 text-xs font-bold uppercase tracking-widest hover:underline">+ See All Languages</button>
          <button v-if="isExpandedLang" @click="showLessLang" class="text-slate-500 text-xs font-bold uppercase tracking-widest hover:underline">− Show Less</button>
        </div>
      </section>
    </template>

    <!-- ====== EDIT MODAL ====== -->
    <EditModal :visible="modalVisible" :title="modalTitle" :loading="modalLoading"
      show-delete
      @close="closeModal" @delete="handleModalDelete">

      <p v-if="modalErrors.general" class="text-red-400 text-sm mb-4 bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-2">{{ modalErrors.general[0] }}</p>

      <!-- EXPERIENCE FORM -->
      <form v-if="modalSection === 'experience'" @submit.prevent="handleModalSave" class="space-y-4">
        <div>
          <label class="label-dark">Company</label>
          <input v-model="editForm.company" class="input-dark" />
          <p v-if="modalErrors.company" class="text-red-400 text-xs mt-1">{{ modalErrors.company[0] }}</p>
        </div>
        <div>
          <label class="label-dark">Position</label>
          <input v-model="editForm.position" class="input-dark" />
          <p v-if="modalErrors.position" class="text-red-400 text-xs mt-1">{{ modalErrors.position[0] }}</p>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label-dark">Start Date</label>
            <input type="date" v-model="editForm.start_date" class="input-dark" />
            <p v-if="modalErrors.start_date" class="text-red-400 text-xs mt-1">{{ modalErrors.start_date[0] }}</p>
          </div>
          <div>
            <label class="label-dark">End Date</label>
            <input type="date" v-model="editForm.end_date" class="input-dark" />
            <p v-if="modalErrors.end_date" class="text-red-400 text-xs mt-1">{{ modalErrors.end_date[0] }}</p>
          </div>
        </div>
        <div>
          <label class="label-dark">Description</label>
          <textarea v-model="editForm.description" rows="3" class="input-dark resize-none"></textarea>
          <p v-if="modalErrors.description" class="text-red-400 text-xs mt-1">{{ modalErrors.description[0] }}</p>
        </div>
        <button type="submit" :disabled="modalLoading" class="btn-primary w-full flex items-center justify-center gap-2">
          <LoadingSpinner v-if="modalLoading" /> {{ modalLoading ? 'Saving...' : 'Save Changes' }}
        </button>
      </form>

      <!-- EDUCATION FORM -->
      <form v-else-if="modalSection === 'education'" @submit.prevent="handleModalSave" class="space-y-4">
        <div>
          <label class="label-dark">Institution</label>
          <input v-model="editForm.institution" class="input-dark" />
          <p v-if="modalErrors.institution" class="text-red-400 text-xs mt-1">{{ modalErrors.institution[0] }}</p>
        </div>
        <div>
          <label class="label-dark">Degree</label>
          <input v-model="editForm.degree" class="input-dark" />
          <p v-if="modalErrors.degree" class="text-red-400 text-xs mt-1">{{ modalErrors.degree[0] }}</p>
        </div>
        <div>
          <label class="label-dark">Field of Study</label>
          <input v-model="editForm.field_of_study" class="input-dark" />
          <p v-if="modalErrors.field_of_study" class="text-red-400 text-xs mt-1">{{ modalErrors.field_of_study[0] }}</p>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label-dark">Start Date</label>
            <input type="date" v-model="editForm.start_date" class="input-dark" />
            <p v-if="modalErrors.start_date" class="text-red-400 text-xs mt-1">{{ modalErrors.start_date[0] }}</p>
          </div>
          <div>
            <label class="label-dark">End Date</label>
            <input type="date" v-model="editForm.end_date" class="input-dark" />
            <p v-if="modalErrors.end_date" class="text-red-400 text-xs mt-1">{{ modalErrors.end_date[0] }}</p>
          </div>
        </div>
        <div>
          <label class="label-dark">Description</label>
          <textarea v-model="editForm.description" rows="3" class="input-dark resize-none"></textarea>
          <p v-if="modalErrors.description" class="text-red-400 text-xs mt-1">{{ modalErrors.description[0] }}</p>
        </div>
        <button type="submit" :disabled="modalLoading" class="btn-primary w-full flex items-center justify-center gap-2">
          <LoadingSpinner v-if="modalLoading" /> {{ modalLoading ? 'Saving...' : 'Save Changes' }}
        </button>
      </form>

      <!-- PROJECT FORM -->
      <form v-else-if="modalSection === 'project'" @submit.prevent="handleModalSave" class="space-y-4">
        <div>
          <label class="label-dark">Title</label>
          <input v-model="editForm.title" class="input-dark" />
          <p v-if="modalErrors.title" class="text-red-400 text-xs mt-1">{{ modalErrors.title[0] }}</p>
        </div>
        <div>
          <label class="label-dark">Description</label>
          <textarea v-model="editForm.description" rows="3" class="input-dark resize-none"></textarea>
          <p v-if="modalErrors.description" class="text-red-400 text-xs mt-1">{{ modalErrors.description[0] }}</p>
        </div>
        <div>
          <label class="label-dark">Live Link</label>
          <input v-model="editForm.link" class="input-dark" placeholder="https://..." />
          <p v-if="modalErrors.link" class="text-red-400 text-xs mt-1">{{ modalErrors.link[0] }}</p>
        </div>
        <div>
          <label class="label-dark">GitHub URL</label>
          <input v-model="editForm.github_url" class="input-dark" placeholder="https://github.com/..." />
          <p v-if="modalErrors.github_url" class="text-red-400 text-xs mt-1">{{ modalErrors.github_url[0] }}</p>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label-dark">Start Date</label>
            <input type="date" v-model="editForm.start_date" class="input-dark" />
            <p v-if="modalErrors.start_date" class="text-red-400 text-xs mt-1">{{ modalErrors.start_date[0] }}</p>
          </div>
          <div>
            <label class="label-dark">End Date</label>
            <input type="date" v-model="editForm.end_date" class="input-dark" />
            <p v-if="modalErrors.end_date" class="text-red-400 text-xs mt-1">{{ modalErrors.end_date[0] }}</p>
          </div>
        </div>
        <button type="submit" :disabled="modalLoading" class="btn-primary w-full flex items-center justify-center gap-2">
          <LoadingSpinner v-if="modalLoading" /> {{ modalLoading ? 'Saving...' : 'Save Changes' }}
        </button>
      </form>

      <!-- CERTIFICATION FORM -->
      <form v-else-if="modalSection === 'certification'" @submit.prevent="handleModalSave" class="space-y-4">
        <div>
          <label class="label-dark">Certificate Name</label>
          <input v-model="editForm.name" class="input-dark" />
          <p v-if="modalErrors.name" class="text-red-400 text-xs mt-1">{{ modalErrors.name[0] }}</p>
        </div>
        <div>
          <label class="label-dark">Organization</label>
          <input v-model="editForm.organization" class="input-dark" />
          <p v-if="modalErrors.organization" class="text-red-400 text-xs mt-1">{{ modalErrors.organization[0] }}</p>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label-dark">Issue Date</label>
            <input type="date" v-model="editForm.issue_date" class="input-dark" />
            <p v-if="modalErrors.issue_date" class="text-red-400 text-xs mt-1">{{ modalErrors.issue_date[0] }}</p>
          </div>
          <div>
            <label class="label-dark">Expiration Date</label>
            <input type="date" v-model="editForm.expiration_date" class="input-dark" />
            <p v-if="modalErrors.expiration_date" class="text-red-400 text-xs mt-1">{{ modalErrors.expiration_date[0] }}</p>
          </div>
        </div>
        <button type="submit" :disabled="modalLoading" class="btn-primary w-full flex items-center justify-center gap-2">
          <LoadingSpinner v-if="modalLoading" /> {{ modalLoading ? 'Saving...' : 'Save Changes' }}
        </button>
      </form>

      <!-- VOLUNTEER FORM -->
      <form v-else-if="modalSection === 'volunteer'" @submit.prevent="handleModalSave" class="space-y-4">
        <div>
          <label class="label-dark">Organization</label>
          <input v-model="editForm.organization" class="input-dark" />
          <p v-if="modalErrors.organization" class="text-red-400 text-xs mt-1">{{ modalErrors.organization[0] }}</p>
        </div>
        <div>
          <label class="label-dark">Role</label>
          <input v-model="editForm.role" class="input-dark" />
          <p v-if="modalErrors.role" class="text-red-400 text-xs mt-1">{{ modalErrors.role[0] }}</p>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label-dark">Start Date</label>
            <input type="date" v-model="editForm.start_date" class="input-dark" />
            <p v-if="modalErrors.start_date" class="text-red-400 text-xs mt-1">{{ modalErrors.start_date[0] }}</p>
          </div>
          <div>
            <label class="label-dark">End Date</label>
            <input type="date" v-model="editForm.end_date" class="input-dark" />
            <p v-if="modalErrors.end_date" class="text-red-400 text-xs mt-1">{{ modalErrors.end_date[0] }}</p>
          </div>
        </div>
        <div>
          <label class="label-dark">Description</label>
          <textarea v-model="editForm.description" rows="3" class="input-dark resize-none"></textarea>
          <p v-if="modalErrors.description" class="text-red-400 text-xs mt-1">{{ modalErrors.description[0] }}</p>
        </div>
        <button type="submit" :disabled="modalLoading" class="btn-primary w-full flex items-center justify-center gap-2">
          <LoadingSpinner v-if="modalLoading" /> {{ modalLoading ? 'Saving...' : 'Save Changes' }}
        </button>
      </form>

      <!-- SKILL (edit level + delete) -->
      <div v-else-if="modalSection === 'skill'" class="space-y-5">
        <div class="text-center">
          <span class="text-white font-bold text-lg">{{ editForm.name }}</span>
        </div>
        <form @submit.prevent="handleModalSave" class="space-y-4">
          <div>
            <label class="label-dark">Proficiency Level</label>
            <select v-model="editForm.level" class="input-dark">
              <option value="beginner">Beginner</option>
              <option value="intermediate">Intermediate</option>
              <option value="advanced">Advanced</option>
              <option value="expert">Expert</option>
            </select>
            <p v-if="modalErrors.level" class="text-red-400 text-xs mt-1">{{ modalErrors.level[0] }}</p>
          </div>
          <button type="submit" :disabled="modalLoading" class="btn-primary w-full flex items-center justify-center gap-2">
            <LoadingSpinner v-if="modalLoading" /> {{ modalLoading ? 'Saving...' : 'Update Level' }}
          </button>
        </form>
      </div>

      <!-- LANGUAGE (edit level + delete) -->
      <div v-else-if="modalSection === 'language'" class="space-y-5">
        <div class="text-center">
          <span class="text-white font-bold text-lg">{{ editForm.name }}</span>
        </div>
        <form @submit.prevent="handleModalSave" class="space-y-4">
          <div>
            <label class="label-dark">Proficiency Level</label>
            <select v-model="editForm.level" class="input-dark">
              <option value="beginner">Beginner</option>
              <option value="elementary">Elementary</option>
              <option value="intermediate">Intermediate</option>
              <option value="upper_intermediate">Upper Intermediate</option>
              <option value="advanced">Advanced</option>
              <option value="native">Native</option>
            </select>
            <p v-if="modalErrors.level" class="text-red-400 text-xs mt-1">{{ modalErrors.level[0] }}</p>
          </div>
          <button type="submit" :disabled="modalLoading" class="btn-primary w-full flex items-center justify-center gap-2">
            <LoadingSpinner v-if="modalLoading" /> {{ modalLoading ? 'Saving...' : 'Update Level' }}
          </button>
        </form>
      </div>
    </EditModal>

    <!-- ====== PROFILE EDIT MODAL ====== -->
    <EditModal :visible="profileModalVisible" title="Edit Profile & Account" :loading="profileModalLoading"
      show-delete @close="profileModalVisible = false" @delete="handleDeleteAccount">

      <!-- Tab Navigation -->
      <div class="flex gap-1 mb-6 bg-white/5 rounded-xl p-1">
        <button @click="profileTab = 'profile'"
          :class="profileTab === 'profile' ? 'bg-blue-500/20 text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
          class="flex-1 py-2 px-3 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
          Profile
        </button>
        <button @click="profileTab = 'account'"
          :class="profileTab === 'account' ? 'bg-blue-500/20 text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
          class="flex-1 py-2 px-3 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
          Account
        </button>
        <button @click="profileTab = 'password'"
          :class="profileTab === 'password' ? 'bg-blue-500/20 text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
          class="flex-1 py-2 px-3 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
          Password
        </button>
      </div>

      <!-- ── PROFILE TAB ── -->
      <form v-if="profileTab === 'profile'" @submit.prevent="handleProfileSave" class="space-y-4">
        <!-- Photo Upload -->
        <div>
          <label class="label-dark">Profile Photo</label>
          <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-xl bg-white/5 border border-white/10 overflow-hidden flex items-center justify-center shrink-0">
              <img v-if="profilePhotoPreview" :src="profilePhotoPreview" alt="Preview" class="w-full h-full object-cover" />
              <span v-else class="text-2xl">📷</span>
            </div>
            <input type="file" accept="image/*" @change="onProfilePhotoChange" class="input-dark flex-1" />
          </div>
          <p v-if="profileModalErrors.photo" class="text-red-400 text-xs mt-1">{{ profileModalErrors.photo[0] }}</p>
        </div>
        <div>
          <label class="label-dark">Headline</label>
          <input v-model="profileForm.headline" class="input-dark" placeholder="e.g. Full Stack Developer" />
          <p v-if="profileModalErrors.headline" class="text-red-400 text-xs mt-1">{{ profileModalErrors.headline[0] }}</p>
        </div>
        <div>
          <label class="label-dark">Summary</label>
          <textarea v-model="profileForm.summary" rows="3" class="input-dark resize-none" placeholder="Write about yourself..."></textarea>
          <p v-if="profileModalErrors.summary" class="text-red-400 text-xs mt-1">{{ profileModalErrors.summary[0] }}</p>
        </div>
        <div>
          <label class="label-dark">Location</label>
          <input v-model="profileForm.location" class="input-dark" placeholder="e.g. New York, USA" />
          <p v-if="profileModalErrors.location" class="text-red-400 text-xs mt-1">{{ profileModalErrors.location[0] }}</p>
        </div>
        <button type="submit" :disabled="profileModalLoading" class="btn-primary w-full flex items-center justify-center gap-2">
          <LoadingSpinner v-if="profileModalLoading" /> {{ profileModalLoading ? 'Saving...' : 'Update Profile' }}
        </button>
      </form>

      <!-- ── ACCOUNT TAB (Username) ── -->
      <form v-else-if="profileTab === 'account'" @submit.prevent="handleUserNameSave" class="space-y-4">
        <div class="text-center mb-2">
          <p class="text-sm text-slate-400">Update your display name. Your email cannot be changed.</p>
        </div>
        <div>
          <label class="label-dark">Display Name</label>
          <input v-model="userNameForm.name" class="input-dark" placeholder="Your name" />
          <p v-if="userNameErrors.name" class="text-red-400 text-xs mt-1">{{ userNameErrors.name[0] }}</p>
        </div>
        <div>
          <label class="label-dark">Email</label>
          <input :value="authStore.user?.email" class="input-dark opacity-50 cursor-not-allowed" disabled />
          <p class="text-slate-600 text-xs mt-1">Email cannot be changed.</p>
        </div>
        <button type="submit" :disabled="userNameLoading" class="btn-primary w-full flex items-center justify-center gap-2">
          <LoadingSpinner v-if="userNameLoading" /> {{ userNameLoading ? 'Saving...' : 'Update Name' }}
        </button>
      </form>

      <!-- ── PASSWORD TAB ── -->
      <form v-else-if="profileTab === 'password'" @submit.prevent="handlePasswordSave" class="space-y-4">
        <div class="text-center mb-2">
          <p class="text-sm text-slate-400">Change your account password.</p>
        </div>
        <div>
          <label class="label-dark">Current Password</label>
          <input type="password" v-model="passwordForm.current_password" class="input-dark" placeholder="Enter current password" />
          <p v-if="passwordErrors.current_password" class="text-red-400 text-xs mt-1">{{ passwordErrors.current_password[0] }}</p>
        </div>
        <div>
          <label class="label-dark">New Password</label>
          <input type="password" v-model="passwordForm.password" class="input-dark" placeholder="Min 8 characters" />
          <p v-if="passwordErrors.password" class="text-red-400 text-xs mt-1">{{ passwordErrors.password[0] }}</p>
        </div>
        <div>
          <label class="label-dark">Confirm New Password</label>
          <input type="password" v-model="passwordForm.password_confirmation" class="input-dark" placeholder="Repeat new password" />
          <p v-if="passwordErrors.password_confirmation" class="text-red-400 text-xs mt-1">{{ passwordErrors.password_confirmation[0] }}</p>
        </div>
        <button type="submit" :disabled="passwordLoading" class="btn-primary w-full flex items-center justify-center gap-2">
          <LoadingSpinner v-if="passwordLoading" /> {{ passwordLoading ? 'Updating...' : 'Change Password' }}
        </button>
      </form>

      <div class="mt-4 pt-4 border-t border-white/5">
        <p class="text-xs text-slate-600 text-center">The Delete button below will permanently delete your entire account.</p>
      </div>
    </EditModal>
  </div>
</template>
