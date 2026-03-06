<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/api/http'
import { useFormSubmit } from '@/modules/cv/composables/useFormSubmit'
import { useProfileGuard } from '@/modules/cv/composables/useProfileGuard'
import FormCard from '@/modules/cv/components/FormCard.vue'
import SectionHeader from '@/modules/cv/components/SectionHeader.vue'
import ErrorAlert from '@/shared/components/ErrorAlert.vue'
import LoadingSpinner from '@/shared/components/LoadingSpinner.vue'

const router = useRouter()
const { hasProfile, profileLoading } = useProfileGuard()

const availableLanguages = ref({})
const selectedLanguages = ref([])
const searchQuery = ref('')

const levels = [
  { value: 'beginner', label: 'Beginner' },
  { value: 'elementary', label: 'Elementary' },
  { value: 'intermediate', label: 'Intermediate' },
  { value: 'upper_intermediate', label: 'Upper Intermediate' },
  { value: 'advanced', label: 'Advanced' },
  { value: 'native', label: 'Native' },
]

const filteredLanguages = computed(() => {
  const entries = Object.entries(availableLanguages.value)
  const q = searchQuery.value.toLowerCase().trim()
  if (!q) return entries.slice(0, 20)
  return entries.filter(([code, name]) =>
    name.toLowerCase().includes(q)
  ).slice(0, 20)
})

const addLanguage = (code, name) => {
  if (selectedLanguages.value.find((l) => l.name === name)) return
  selectedLanguages.value.push({ name, level: 'beginner' })
  searchQuery.value = ''
}

const removeLanguage = (index) => {
  selectedLanguages.value.splice(index, 1)
}

const { errors, loading, submit } = useFormSubmit('/language', {
  onSuccess: () => router.push({ name: 'CV' }),
})

const handleSubmit = () => {
  if (selectedLanguages.value.length === 0) {
    errors.value = { general: ['Select at least one language.'] }
    return
  }
  submit({ languages: selectedLanguages.value })
}

onMounted(async () => {
  try {
    const res = await http.get('/language/available')
    availableLanguages.value = res.data.data
  } catch (e) {
    console.error('Failed to load languages', e)
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
      <h2 class="text-xl font-bold text-white">Profile Required</h2>
      <p class="text-slate-400 text-sm">You need to create your profile before adding languages.</p>
      <router-link to="/profile" class="inline-block px-6 py-2 rounded-xl text-sm font-semibold text-white bg-linear-to-r from-blue-500 to-indigo-600 hover:shadow-lg transition-all">
        Create Profile →
      </router-link>
    </div>

    <template v-else>
    <SectionHeader icon="🌍" title="Languages" subtitle="Add languages you speak"
      gradient="from-green-500 to-emerald-500" />

    <ErrorAlert :error="errors.general?.[0] ?? ''" />

    <!-- Search -->
    <div class="mb-5">
      <label class="label-dark">Search Language</label>
      <input type="text" v-model="searchQuery" placeholder="Type to search (e.g. English, French)..." class="input-dark" />

      <div v-if="searchQuery.trim()"
        class="mt-1 max-h-48 overflow-y-auto rounded-lg bg-slate-900 border border-white/10 shadow-xl">
        <button v-for="[code, name] in filteredLanguages" :key="code" type="button" @click="addLanguage(code, name)"
          class="block w-full text-left px-4 py-2.5 text-sm text-slate-300 hover:bg-white/5 hover:text-white transition-colors">
          {{ name }}
        </button>
        <p v-if="filteredLanguages.length === 0" class="px-4 py-3 text-xs text-slate-600">No results found</p>
      </div>
    </div>

    <!-- Selected languages -->
    <div v-if="selectedLanguages.length > 0" class="space-y-2 mb-6">
      <div v-for="(lang, index) in selectedLanguages" :key="index"
        class="flex items-center gap-3 bg-white/5 border border-white/10 rounded-xl p-3">
        <span class="text-sm text-white flex-1 font-medium">{{ lang.name }}</span>
        <select v-model="lang.level"
          class="bg-white/5 border border-white/10 rounded-lg px-2 py-1 text-xs text-slate-300 outline-none focus:border-blue-500">
          <option v-for="lvl in levels" :key="lvl.value" :value="lvl.value" class="bg-slate-900">{{ lvl.label }}</option>
        </select>
        <button type="button" @click="removeLanguage(index)"
          class="w-7 h-7 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 flex items-center justify-center text-xs transition-colors">
          ✕
        </button>
      </div>
    </div>

    <button @click="handleSubmit" :disabled="loading" class="btn-primary flex items-center justify-center gap-2">
      <LoadingSpinner v-if="loading" />
      {{ loading ? 'Saving...' : 'Save Languages' }}
    </button>
    </template>
  </FormCard>
</template>
