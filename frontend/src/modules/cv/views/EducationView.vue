<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/api/http'
import { useFormSubmit } from '@/modules/cv/composables/useFormSubmit'
import { validateEducation, hasErrors } from '@/modules/cv/composables/useValidation'
import { useProfileGuard } from '@/modules/cv/composables/useProfileGuard'
import FormCard from '@/modules/cv/components/FormCard.vue'
import SectionHeader from '@/modules/cv/components/SectionHeader.vue'
import ErrorAlert from '@/shared/components/ErrorAlert.vue'
import LoadingSpinner from '@/shared/components/LoadingSpinner.vue'

const router = useRouter()
const { hasProfile, profileLoading } = useProfileGuard()

const formData = ref({
  institution: '',
  degree: '',
  field_of_study: '',
  start_date: '',
  end_date: '',
  description: '',
})

const availableFields = ref({})
const fieldSearch = ref('')
const showFieldDropdown = ref(false)

const flatFields = computed(() => {
  const all = []
  for (const [category, fields] of Object.entries(availableFields.value)) {
    for (const field of fields) {
      all.push({ name: field, category })
    }
  }
  return all
})

const filteredFields = computed(() => {
  const q = fieldSearch.value.toLowerCase().trim()
  if (!q) return flatFields.value.slice(0, 30)
  return flatFields.value.filter((f) => f.name.toLowerCase().includes(q)).slice(0, 30)
})

const selectField = (name) => {
  formData.value.field_of_study = name
  fieldSearch.value = name
  showFieldDropdown.value = false
}

const onFieldInput = () => {
  showFieldDropdown.value = true
  formData.value.field_of_study = fieldSearch.value
}

const { errors, loading, submit } = useFormSubmit('/education', {
  onSuccess: () => router.push({ name: 'CV' }),
})

const handleSubmit = () => {
  const v = validateEducation(formData.value)
  if (hasErrors(v)) { errors.value = v; return }
  submit(formData.value)
}

onMounted(async () => {
  try {
    const res = await http.get('/education/fields')
    availableFields.value = res.data.data
  } catch (e) {
    console.error('Failed to load fields of study', e)
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
      <p class="text-slate-400 text-sm">You need to create your profile before adding education.</p>
      <router-link to="/profile" class="inline-block px-6 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:shadow-lg transition-all">
        Create Profile →
      </router-link>
    </div>

    <template v-else>
      <SectionHeader icon="🎓" title="Add Education" subtitle="Schools, degrees & qualifications"
        gradient="from-indigo-500 to-blue-500" />

      <ErrorAlert :error="errors.general?.[0] ?? ''" />

      <form @submit.prevent="handleSubmit" class="space-y-5">
        <div>
          <label for="institution" class="label-dark">Institution <span class="text-red-400">*</span></label>
          <input type="text" id="institution" v-model="formData.institution" placeholder="e.g. MIT, Stanford..." class="input-dark" />
          <p v-if="errors.institution" class="text-red-400 text-xs mt-1.5">{{ errors.institution[0] }}</p>
        </div>

        <div>
          <label for="degree" class="label-dark">Degree <span class="text-red-400">*</span></label>
          <input type="text" id="degree" v-model="formData.degree" placeholder="e.g. Bachelor of Science" class="input-dark" />
          <p v-if="errors.degree" class="text-red-400 text-xs mt-1.5">{{ errors.degree[0] }}</p>
        </div>

        <div class="relative">
          <label for="field_of_study" class="label-dark">Field of Study</label>
          <input type="text" id="field_of_study" v-model="fieldSearch"
            @input="onFieldInput" @focus="showFieldDropdown = true"
            placeholder="Type to search (e.g. Computer Science)..." autocomplete="off" class="input-dark" />

          <div v-if="showFieldDropdown && fieldSearch"
            class="absolute z-10 w-full mt-1 max-h-48 overflow-y-auto rounded-lg bg-slate-900 border border-white/10 shadow-xl">
            <button v-for="field in filteredFields" :key="field.name" type="button" @click="selectField(field.name)"
              class="block w-full text-left px-4 py-2 text-sm text-slate-300 hover:bg-white/5 hover:text-white transition-colors">
              <span>{{ field.name }}</span>
              <span class="text-slate-600 ml-1 text-xs">({{ field.category }})</span>
            </button>
            <p v-if="filteredFields.length === 0" class="px-4 py-3 text-xs text-slate-600">No results found</p>
          </div>
          <p v-if="errors.field_of_study" class="text-red-400 text-xs mt-1.5">{{ errors.field_of_study[0] }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="start_date" class="label-dark">Start Date <span class="text-red-400">*</span></label>
            <input type="date" id="start_date" v-model="formData.start_date" class="input-dark" />
            <p v-if="errors.start_date" class="text-red-400 text-xs mt-1.5">{{ errors.start_date[0] }}</p>
          </div>
          <div>
            <label for="end_date" class="label-dark">End Date</label>
            <input type="date" id="end_date" v-model="formData.end_date" class="input-dark" />
            <p v-if="errors.end_date" class="text-red-400 text-xs mt-1.5">{{ errors.end_date[0] }}</p>
          </div>
        </div>

        <div>
          <label for="description" class="label-dark">Description</label>
          <textarea id="description" v-model="formData.description" rows="3" placeholder="Describe your studies..." class="input-dark resize-none"></textarea>
          <p v-if="errors.description" class="text-red-400 text-xs mt-1.5">{{ errors.description[0] }}</p>
        </div>

        <button type="submit" :disabled="loading" class="btn-primary flex items-center justify-center gap-2">
          <LoadingSpinner v-if="loading" />
          {{ loading ? 'Saving...' : 'Save Education' }}
        </button>
      </form>
    </template>
  </FormCard>
</template>
