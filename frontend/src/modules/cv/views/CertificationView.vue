<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useFormSubmit } from '@/modules/cv/composables/useFormSubmit'
import { validateCertification, hasErrors } from '@/modules/cv/composables/useValidation'
import { useProfileGuard } from '@/modules/cv/composables/useProfileGuard'
import FormCard from '@/modules/cv/components/FormCard.vue'
import SectionHeader from '@/modules/cv/components/SectionHeader.vue'
import ErrorAlert from '@/shared/components/ErrorAlert.vue'
import LoadingSpinner from '@/shared/components/LoadingSpinner.vue'

const router = useRouter()
const { hasProfile, profileLoading } = useProfileGuard()

const formData = ref({
  name: '',
  organization: '',
  issue_date: '',
  expiration_date: '',
})

const photo = ref(null)

const { errors, loading, submit } = useFormSubmit('/certification', {
  onSuccess: () => router.push({ name: 'CV' }),
})

const onFileChange = (e) => {
  photo.value = e.target.files[0]
}

const handleSubmit = () => {
  // Frontend validation
  const v = validateCertification(formData.value)
  if (hasErrors(v)) { errors.value = v; return }

  const data = new FormData()
  data.append('name', formData.value.name)
  data.append('organization', formData.value.organization)
  data.append('issue_date', formData.value.issue_date)
  if (formData.value.expiration_date) data.append('expiration_date', formData.value.expiration_date)
  if (photo.value) {
    data.append('photo', photo.value)
  }
  submit(data)
}
</script>

<template>
  <FormCard>
    <div v-if="profileLoading" class="flex justify-center py-12">
      <LoadingSpinner class="w-8 h-8 text-blue-400" />
    </div>

    <div v-else-if="hasProfile === false" class="text-center py-12 space-y-4">
      <div class="text-5xl">⚠️</div>
      <h2 class="text-xl font-bold text-white">Profile Required</h2>
      <p class="text-slate-400 text-sm">You need to create your profile before adding certifications.</p>
      <router-link to="/profile" class="inline-block px-6 py-2 rounded-xl text-sm font-semibold text-white bg-linear-to-r from-blue-500 to-indigo-600 hover:shadow-lg transition-all">
        Create Profile →
      </router-link>
    </div>

    <template v-else>
    <SectionHeader icon="🏅" title="Add Certification" subtitle="Certificates, awards & credentials"
      gradient="from-yellow-500 to-amber-500" />

    <ErrorAlert :error="errors.general?.[0] ?? ''" />

    <form @submit.prevent="handleSubmit" class="space-y-5">
      <div>
        <label class="label-dark">Certificate Photo</label>
        <input type="file" id="photo" accept="image/*" @change="onFileChange" class="input-dark" />
        <p v-if="errors.photo" class="text-red-400 text-xs mt-1.5">{{ errors.photo[0] }}</p>
      </div>

      <div>
        <label for="name" class="label-dark">Certificate Name</label>
        <input type="text" id="name" v-model="formData.name" placeholder="e.g. AWS Solutions Architect" class="input-dark" />
        <p v-if="errors.name" class="text-red-400 text-xs mt-1.5">{{ errors.name[0] }}</p>
      </div>

      <div>
        <label for="organization" class="label-dark">Organization</label>
        <input type="text" id="organization" v-model="formData.organization" placeholder="e.g. Amazon Web Services" class="input-dark" />
        <p v-if="errors.organization" class="text-red-400 text-xs mt-1.5">{{ errors.organization[0] }}</p>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label for="issue_date" class="label-dark">Issue Date</label>
          <input type="date" id="issue_date" v-model="formData.issue_date" class="input-dark" />
          <p v-if="errors.issue_date" class="text-red-400 text-xs mt-1.5">{{ errors.issue_date[0] }}</p>
        </div>
        <div>
          <label for="expiration_date" class="label-dark">Expiration Date</label>
          <input type="date" id="expiration_date" v-model="formData.expiration_date" class="input-dark" />
          <p v-if="errors.expiration_date" class="text-red-400 text-xs mt-1.5">{{ errors.expiration_date[0] }}</p>
        </div>
      </div>

      <button type="submit" :disabled="loading" class="btn-primary flex items-center justify-center gap-2">
        <LoadingSpinner v-if="loading" />
        {{ loading ? 'Saving...' : 'Save Certification' }}
      </button>
    </form>
    </template>
  </FormCard>
</template>
