<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import http from '@/api/http'
import { useFormSubmit } from '@/modules/cv/composables/useFormSubmit'
import { useProfileGuard } from '@/modules/cv/composables/useProfileGuard'
import FormCard from '@/modules/cv/components/FormCard.vue'
import SectionHeader from '@/modules/cv/components/SectionHeader.vue'
import ErrorAlert from '@/shared/components/ErrorAlert.vue'
import LoadingSpinner from '@/shared/components/LoadingSpinner.vue'

const { t } = useI18n()
const router = useRouter()
const { hasProfile, profileLoading } = useProfileGuard()

const availableSkills = ref({})
const selectedSkills = ref([])
const searchQuery = ref('')
const searchInput = ref(null)
const inputFocused = ref(false)

const levels = computed(() => [
  { value: 'beginner', label: t('levels.beginner') },
  { value: 'intermediate', label: t('levels.intermediate') },
  { value: 'advanced', label: t('levels.advanced') },
  { value: 'expert', label: t('levels.expert') },
])

const flatSkills = computed(() => {
  const all = []
  for (const [category, skills] of Object.entries(availableSkills.value)) {
    for (const skill of skills) {
      all.push({ name: skill, category })
    }
  }
  return all
})

const filteredSkills = computed(() => {
  const selectedNames = new Set(selectedSkills.value.map((s) => s.name))
  const pool = flatSkills.value.filter((s) => !selectedNames.has(s.name))
  const q = searchQuery.value.toLowerCase().trim()
  if (!q) return pool.slice(0, 30)
  return pool.filter((s) => s.name.toLowerCase().includes(q)).slice(0, 30)
})

const addSkill = (name) => {
  if (selectedSkills.value.find((s) => s.name === name)) return
  selectedSkills.value.push({ name, level: 'beginner' })
  searchQuery.value = ''
  nextTick(() => {
    searchInput.value?.focus()
    inputFocused.value = true
  })
}

const removeSkill = (index) => {
  selectedSkills.value.splice(index, 1)
}

const handleBlur = () => {
  setTimeout(() => { inputFocused.value = false }, 200)
}

const { errors, loading, submit } = useFormSubmit('/skill', {
  onSuccess: () => router.push({ name: 'CV' }),
})

const handleSubmit = () => {
  if (selectedSkills.value.length === 0) {
    errors.value = { general: [t('validation.selectOneSkill')] }
    return
  }
  submit({ skills: selectedSkills.value })
}

onMounted(async () => {
  try {
    const res = await http.get('/skill/available')
    availableSkills.value = res.data.data
  } catch (e) {
    console.error('Failed to load skills', e)
  }
})
</script>

<template>
  <FormCard>
    <div v-if="profileLoading" class="flex justify-center py-12">
      <LoadingSpinner class="w-8 h-8 text-blue-400" />
    </div>

    <div v-else-if="hasProfile === false" class="text-center py-12 space-y-4">
      <div class="text-5xl">⚠️</div>
      <h2 class="text-xl font-bold text-white">{{ t('forms.profileRequired') }}</h2>
      <p class="text-slate-400 text-sm">{{ t('forms.profileRequiredText', { section: t('sidebar.skills').toLowerCase() }) }}</p>
      <router-link to="/profile" class="inline-block px-6 py-2 rounded-xl text-sm font-semibold text-white bg-linear-to-r from-blue-500 to-indigo-600 hover:shadow-lg transition-all">
        {{ t('forms.createProfileLink') }}
      </router-link>
    </div>

    <template v-else>
    <SectionHeader icon="⚡" :title="t('forms.skillsTitle')" :subtitle="t('forms.skillsSubtitle')"
      gradient="from-amber-500 to-orange-500" />

    <ErrorAlert :error="errors.general?.[0] ?? ''" />

    <!-- Search -->
    <div class="mb-5">
      <label class="label-dark">{{ t('forms.searchSkill') }}</label>
      <input type="text" ref="searchInput" v-model="searchQuery"
        @focus="inputFocused = true" @blur="handleBlur"
        :placeholder="t('forms.searchSkillPlaceholder')" class="input-dark" />

      <div v-if="searchQuery"
        class="mt-1 max-h-48 overflow-y-auto rounded-lg bg-slate-900 border border-white/10 shadow-xl">
        <button v-for="skill in filteredSkills" :key="skill.name" type="button" @click="addSkill(skill.name)"
          class="block w-full text-left px-4 py-2.5 text-sm text-slate-300 hover:bg-white/5 hover:text-white transition-colors">
          <span>{{ skill.name }}</span>
          <span class="text-slate-600 ml-1 text-xs">({{ skill.category }})</span>
        </button>
        <p v-if="filteredSkills.length === 0" class="px-4 py-3 text-xs text-slate-600">{{ t('forms.noResults') }}</p>
      </div>
    </div>

    <!-- Selected skills -->
    <div v-if="selectedSkills.length > 0" class="space-y-2 mb-6">
      <div v-for="(skill, index) in selectedSkills" :key="index"
        class="flex items-center gap-3 bg-white/5 border border-white/10 rounded-xl p-3">
        <span class="text-sm text-white flex-1 font-medium">{{ skill.name }}</span>
        <select v-model="skill.level"
          class="bg-white/5 border border-white/10 rounded-lg px-2 py-1 text-xs text-slate-300 outline-none focus:border-blue-500">
          <option v-for="lvl in levels" :key="lvl.value" :value="lvl.value" class="bg-slate-900">{{ lvl.label }}</option>
        </select>
        <button type="button" @click="removeSkill(index)"
          class="w-7 h-7 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 flex items-center justify-center text-xs transition-colors">
          ✕
        </button>
      </div>
    </div>

    <button @click="handleSubmit" :disabled="loading" class="btn-primary flex items-center justify-center gap-2">
      <LoadingSpinner v-if="loading" />
      {{ loading ? t('forms.saving') : t('forms.saveSkills') }}
    </button>
    </template>
  </FormCard>
</template>
