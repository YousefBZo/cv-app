<script setup>
/**
 * CVView - thin orchestrator.
 * All business logic lives in composables (useEditModal, useProfileModal).
 * All section UI lives in dedicated Section components.
 */
import { onMounted, watch, ref } from 'vue'
import { useCVStore } from '@/modules/cv/stores/cv'
import { usePagination } from '@/modules/cv/composables/usePagination'
import { useEditModal } from '@/modules/cv/composables/useEditModal'
import { useProfileModal } from '@/modules/cv/composables/useProfileModal'

import CVSkeleton from '@/modules/cv/components/CVSkeleton.vue'
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

function onEditItem(section, item) {
  const titles = {
    project: 'Edit Project',
    experience: 'Edit Experience',
    skill: 'Skill: ' + item.name,
    education: 'Edit Education',
    volunteer: 'Edit Volunteer Experience',
    certification: 'Edit Certification',
    language: 'Language: ' + item.name,
  }
  editModal.open(section, item, titles[section] || 'Edit')
}
</script>

<template>
  <div class="min-h-screen text-slate-100 font-sans selection:bg-blue-500/30 overflow-x-hidden pb-20"
    style="background-image: linear-gradient(135deg, #000b18 0%, #00264d 100%);">

    <CVSkeleton v-if="cvStore.loading || !cvStore.hasFetched" />

    <div v-else-if="!cvStore.hasProfile" class="flex items-center justify-center min-h-[60vh]">
      <div class="text-center space-y-6 max-w-md mx-auto px-6">
        <div class="text-6xl">&#x1F464;</div>
        <h2 class="text-2xl font-bold text-white">No Profile Yet</h2>
        <p class="text-slate-400">You haven't created your profile yet. Create one to start building your CV!</p>
        <router-link to="/profile"
          class="inline-block px-8 py-3 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:shadow-lg hover:shadow-blue-500/25 transition-all">
          Create Profile →
        </router-link>
      </div>
    </div>

    <template v-else>
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
  </div>
</template>
