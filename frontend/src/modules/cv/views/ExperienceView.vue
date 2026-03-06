<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useFormSubmit } from '@/modules/cv/composables/useFormSubmit'
import { validateExperience, hasErrors } from '@/modules/cv/composables/useValidation'
import { useProfileGuard } from '@/modules/cv/composables/useProfileGuard'
import FormCard from '@/modules/cv/components/FormCard.vue'
import SectionHeader from '@/modules/cv/components/SectionHeader.vue'
import ErrorAlert from '@/shared/components/ErrorAlert.vue'
import LoadingSpinner from '@/shared/components/LoadingSpinner.vue'

const router = useRouter()
const { hasProfile, profileLoading } = useProfileGuard()

const formData = ref({
  company: '',
  position: '',
  start_date: '',
  end_date: '',
  description: '',
})

const { errors, loading, submit } = useFormSubmit('/experience', {
  onSuccess: () => router.push({ name: 'CV' }),
})

const handleSubmit = () => {
  const v = validateExperience(formData.value)
  if (hasErrors(v)) { errors.value = v; return }
  submit(formData.value)
}
</script>

<template>
  <FormCard>
    <div v-if="profileLoading" class="flex justify-center py-12">
      <LoadingSpinner class="w-8 h-8 text-blue-400" />
    </div>

    <!-- No Profile Guard -->
    <div v-else-if="hasProfile === false" class="text-center py-12 space-y-4">
      <div class="text-5xl">⚠️</div>
      <h2 class="text-xl font-bold text-white">Profile Required</h2>
      <p class="text-slate-400 text-sm">You need to create your profile before adding experience.</p>
      <router-link to="/profile" class="inline-block px-6 py-2 rounded-xl text-sm font-semibold text-white bg-linear-to-r from-blue-500 to-indigo-600 hover:shadow-lg transition-all">
        Create Profile →
      </router-link>
    </div>

    <template v-else>
      <SectionHeader icon="💼" title="Add Experience" subtitle="Work history & positions"
        gradient="from-purple-500 to-indigo-500" />

      <ErrorAlert :error="errors.general?.[0] ?? ''" />

      <form @submit.prevent="handleSubmit" class="space-y-5">
        <div>
          <label for="company" class="label-dark">Company <span class="text-red-400">*</span></label>
          <input type="text" id="company" v-model="formData.company" placeholder="e.g. Google, Microsoft..." class="input-dark" />
          <p v-if="errors.company" class="text-red-400 text-xs mt-1.5">{{ errors.company[0] }}</p>
        </div>

        <div>
          <label for="position" class="label-dark">Position <span class="text-red-400">*</span></label>
          <input type="text" id="position" v-model="formData.position" placeholder="e.g. Software Engineer" class="input-dark" />
          <p v-if="errors.position" class="text-red-400 text-xs mt-1.5">{{ errors.position[0] }}</p>
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
          <textarea id="description" v-model="formData.description" rows="3" placeholder="Describe your responsibilities..." class="input-dark resize-none"></textarea>
          <p v-if="errors.description" class="text-red-400 text-xs mt-1.5">{{ errors.description[0] }}</p>
        </div>

        <button type="submit" :disabled="loading" class="btn-primary flex items-center justify-center gap-2">
          <LoadingSpinner v-if="loading" />
          {{ loading ? 'Saving...' : 'Save Experience' }}
        </button>
      </form>
    </template>
  </FormCard>
</template>
