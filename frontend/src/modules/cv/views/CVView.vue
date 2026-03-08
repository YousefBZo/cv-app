<script setup>
/**
 * CVView - thin orchestrator.
 * All business logic lives in composables (useEditModal, useProfileModal).
 * All section UI lives in dedicated Section components.
 */
import { onMounted, watch, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useCVStore } from '@/modules/cv/stores/cv'
import { usePagination } from '@/modules/cv/composables/usePagination'
import { useEditModal } from '@/modules/cv/composables/useEditModal'
import { useProfileModal } from '@/modules/cv/composables/useProfileModal'
import { useDownloadCV } from '@/modules/cv/composables/useDownloadCV'

import CVSkeleton from '@/modules/cv/components/CVSkeleton.vue'
import CVPrintLayout from '@/modules/cv/components/CVPrintLayout.vue'
import HeroSection from '@/modules/cv/components/HeroSection.vue'
import ProjectsSection from '@/modules/cv/components/ProjectsSection.vue'
import ExperienceSection from '@/modules/cv/components/ExperienceSection.vue'
import SkillsSection from '@/modules/cv/components/SkillsSection.vue'
import EducationSection from '@/modules/cv/components/EducationSection.vue'
import VolunteerSection from '@/modules/cv/components/VolunteerSection.vue'
import CertificationsSection from '@/modules/cv/components/CertificationsSection.vue'
import LanguagesSection from '@/modules/cv/components/LanguagesSection.vue'
import EditItemModal from '@/modules/cv/components/EditItemModal.vue'
import ProfileEditModal from '@/modules/cv/components/ProfileEditModal.vue'

const cvStore = useCVStore()
const { t } = useI18n()
const isVisible = ref(false)

onMounted(() => {
  cvStore.fetchCV()
})

watch(() => cvStore.profile, (p) => { if (p) isVisible.value = true }, { immediate: true })

const { displayed: displayedProjects, hasMore: hasMoreProjects, isExpanded: isExpandedProjects, showAll: showAllProjects, showLess: showLessProjects } = usePagination(() => cvStore.projects, 6)
const { displayed: displayedExperiences, hasMore: hasMoreExp, isExpanded: isExpandedExp, showAll: showAllExp, showLess: showLessExp } = usePagination(() => cvStore.experiences, 5)
const { displayed: displayedSkills, hasMore: hasMoreSkills, isExpanded: isExpandedSkills, showAll: showAllSkills, showLess: showLessSkills } = usePagination(() => cvStore.skills, 9)
const { displayed: displayedEducation, hasMore: hasMoreEdu, isExpanded: isExpandedEdu, showAll: showAllEdu, showLess: showLessEdu } = usePagination(() => cvStore.educations, 4)
const { displayed: displayedVolunteer, hasMore: hasMoreVol, isExpanded: isExpandedVol, showAll: showAllVol, showLess: showLessVol } = usePagination(() => cvStore.volunteerExperiences, 4)
const { displayed: displayedCertifications, hasMore: hasMoreCert, isExpanded: isExpandedCert, showAll: showAllCert, showLess: showLessCert } = usePagination(() => cvStore.certifications, 4)
const { displayed: displayedLanguages, hasMore: hasMoreLang, isExpanded: isExpandedLang, showAll: showAllLang, showLess: showLessLang } = usePagination(() => cvStore.languages, 6)

const editModal = useEditModal()
const profileModal = useProfileModal()
const { generating, downloadCV } = useDownloadCV()

function onEditItem(section, item) {
  const titles = {
    project: t('forms.saveChanges'),
    experience: t('forms.saveChanges'),
    skill: t('sidebar.skills') + ': ' + item.name,
    education: t('forms.saveChanges'),
    volunteer: t('forms.saveChanges'),
    certification: t('forms.saveChanges'),
    language: t('sidebar.languages') + ': ' + item.name,
  }
  editModal.open(section, item, titles[section] || t('forms.saveChanges'))
}
</script>

<template>
  <div class="min-h-screen text-slate-100 font-sans selection:bg-blue-500/30 overflow-x-hidden pb-20"
    style="background-image: linear-gradient(135deg, #000b18 0%, #00264d 100%);">

    <CVSkeleton v-if="cvStore.loading || !cvStore.hasFetched" />

    <div v-else-if="!cvStore.hasProfile" class="flex items-center justify-center min-h-[60vh]">
      <div class="text-center space-y-6 max-w-md mx-auto px-6">
        <div class="text-6xl">&#x1F464;</div>
        <h2 class="text-2xl font-bold text-white">{{ t('cv.noProfileTitle') }}</h2>
        <p class="text-slate-400">{{ t('cv.noProfileText') }}</p>
        <router-link to="/profile"
          class="inline-block px-8 py-3 rounded-xl text-sm font-semibold text-white bg-linear-to-r from-blue-500 to-indigo-600 hover:shadow-lg hover:shadow-blue-500/25 transition-all">
          {{ t('cv.createProfileLink') }}
        </router-link>
      </div>
    </div>

    <template v-else>
      <!-- Download CV floating button -->
      <div class="fixed bottom-6 right-4 sm:bottom-8 sm:right-8 z-50">
        <button
          @click="downloadCV(cvStore.userName)"
          :disabled="generating"
          class="group flex items-center justify-center gap-2 sm:gap-2.5 p-3.5 sm:pl-5 sm:pr-6 sm:py-3.5 rounded-full text-sm font-semibold text-white shadow-xl transition-all duration-300"
          :class="generating
            ? 'bg-slate-700 cursor-wait'
            : 'bg-linear-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 hover:shadow-blue-500/30 hover:scale-105 active:scale-95'"
          title="Download CV as PDF"
        >
          <!-- Spinner when generating -->
          <svg v-if="generating" class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
          </svg>
          <!-- Download icon -->
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a2 2 0 002 2h14a2 2 0 002-2v-3" />
          </svg>
          <span class="hidden sm:inline">{{ generating ? t('cv.generating') : t('cv.downloadCV') }}</span>
        </button>
      </div>

      <HeroSection :isVisible="isVisible" @edit="profileModal.open()" />

      <ProjectsSection v-if="isVisible"
        :projects="displayedProjects" :hasMore="hasMoreProjects" :isExpanded="isExpandedProjects"
        @edit="(p) => onEditItem('project', p)" @showAll="showAllProjects" @showLess="showLessProjects" />

      <ExperienceSection v-if="isVisible"
        :experiences="displayedExperiences" :hasMore="hasMoreExp" :isExpanded="isExpandedExp"
        @edit="(e) => onEditItem('experience', e)" @showAll="showAllExp" @showLess="showLessExp" />

      <SkillsSection v-if="isVisible"
        :skills="displayedSkills" :hasMore="hasMoreSkills" :isExpanded="isExpandedSkills" :isVisible="isVisible"
        @edit="(s) => onEditItem('skill', s)" @showAll="showAllSkills" @showLess="showLessSkills" />

      <EducationSection v-if="isVisible"
        :educations="displayedEducation" :hasMore="hasMoreEdu" :isExpanded="isExpandedEdu"
        @edit="(e) => onEditItem('education', e)" @showAll="showAllEdu" @showLess="showLessEdu" />

      <VolunteerSection v-if="isVisible"
        :volunteers="displayedVolunteer" :hasMore="hasMoreVol" :isExpanded="isExpandedVol"
        @edit="(v) => onEditItem('volunteer', v)" @showAll="showAllVol" @showLess="showLessVol" />

      <CertificationsSection v-if="isVisible"
        :certifications="displayedCertifications" :hasMore="hasMoreCert" :isExpanded="isExpandedCert"
        @edit="(c) => onEditItem('certification', c)" @showAll="showAllCert" @showLess="showLessCert" />

      <LanguagesSection v-if="isVisible"
        :languages="displayedLanguages" :hasMore="hasMoreLang" :isExpanded="isExpandedLang" :isVisible="isVisible"
        @edit="(l) => onEditItem('language', l)" @showAll="showAllLang" @showLess="showLessLang" />
    </template>

    <EditItemModal
      :visible="editModal.visible.value"
      :title="editModal.title.value"
      :section="editModal.section.value"
      :form="editModal.form"
      :loading="editModal.loading.value"
      :errors="editModal.errors.value"
      @close="editModal.close"
      @save="editModal.save"
      @delete="editModal.remove"
    />

    <ProfileEditModal
      :visible="profileModal.visible.value"
      :tab="profileModal.tab.value"
      :profileForm="profileModal.profileForm"
      :profilePhotoPreview="profileModal.profilePhotoPreview.value"
      :profileLoading="profileModal.profileLoading.value"
      :profileErrors="profileModal.profileErrors.value"
      :userNameForm="profileModal.userNameForm"
      :userNameLoading="profileModal.userNameLoading.value"
      :userNameErrors="profileModal.userNameErrors.value"
      :passwordForm="profileModal.passwordForm"
      :passwordLoading="profileModal.passwordLoading.value"
      :passwordErrors="profileModal.passwordErrors.value"
      @close="profileModal.close"
      @update:tab="(t) => profileModal.tab.value = t"
      @saveProfile="profileModal.saveProfile"
      @saveUserName="profileModal.saveUserName"
      @savePassword="profileModal.savePassword"
      @deleteAccount="profileModal.deleteAccount"
      @photoChange="profileModal.onPhotoChange"
    />

    <!-- Hidden print-optimized layout for PDF generation -->
    <CVPrintLayout />
  </div>
</template>
