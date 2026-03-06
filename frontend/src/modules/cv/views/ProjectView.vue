<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useFormSubmit } from '@/modules/cv/composables/useFormSubmit'
import { validateProject, hasErrors } from '@/modules/cv/composables/useValidation'
import { useProfileGuard } from '@/modules/cv/composables/useProfileGuard'
import FormCard from '@/modules/cv/components/FormCard.vue'
import SectionHeader from '@/modules/cv/components/SectionHeader.vue'
import ErrorAlert from '@/shared/components/ErrorAlert.vue'
import LoadingSpinner from '@/shared/components/LoadingSpinner.vue'

const router = useRouter()
const { hasProfile, profileLoading } = useProfileGuard()

const formData = ref({
  title: '',
  description: '',
  link: '',
  github_url: '',
  start_date: '',
  end_date: '',
})

const cover = ref(null)
const coverPreview = ref(null)

const { errors, loading, submit } = useFormSubmit('/project', {
  onSuccess: () => router.push({ name: 'CV' }),
})

const onCoverChange = (e) => {
  const file = e.target.files[0]
  cover.value = file
  if (file) coverPreview.value = URL.createObjectURL(file)
}

const handleSubmit = () => {
  // Frontend validation
  const v = validateProject(formData.value)
  if (hasErrors(v)) { errors.value = v; return }

  const data = new FormData()
  data.append('title', formData.value.title)
  data.append('description', formData.value.description)
  if (formData.value.link) data.append('link', formData.value.link)
  if (formData.value.github_url) data.append('github_url', formData.value.github_url)
  if (formData.value.start_date) data.append('start_date', formData.value.start_date)
  if (formData.value.end_date) data.append('end_date', formData.value.end_date)
  if (cover.value) data.append('cover', cover.value)
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
      <p class="text-slate-400 text-sm">You need to create your profile before adding projects.</p>
      <router-link to="/profile" class="inline-block px-6 py-2 rounded-xl text-sm font-semibold text-white bg-linear-to-r from-blue-500 to-indigo-600 hover:shadow-lg transition-all">
        Create Profile →
      </router-link>
    </div>

    <template v-else>
    <SectionHeader icon="🚀" title="Add Project" subtitle="Showcase your portfolio work"
      gradient="from-pink-500 to-purple-500" />

    <ErrorAlert :error="errors.general?.[0] ?? ''" />

    <form @submit.prevent="handleSubmit" class="space-y-5">
      <div>
        <label for="title" class="label-dark">Title</label>
        <input type="text" id="title" v-model="formData.title" placeholder="e.g. E-commerce Platform" class="input-dark" />
        <p v-if="errors.title" class="text-red-400 text-xs mt-1.5">{{ errors.title[0] }}</p>
      </div>

      <!-- Cover Photo with Preview -->
      <div>
        <label class="label-dark">Cover Photo</label>
        <div v-if="coverPreview" class="mb-3 rounded-xl overflow-hidden border border-white/10">
          <img :src="coverPreview" class="w-full h-36 object-cover" />
        </div>
        <input type="file" id="cover" accept="image/*" @change="onCoverChange" class="input-dark" />
        <p v-if="errors.cover" class="text-red-400 text-xs mt-1.5">{{ errors.cover[0] }}</p>
      </div>

      <div>
        <label for="description" class="label-dark">Description</label>
        <textarea id="description" v-model="formData.description" rows="3" placeholder="Describe your project..." class="input-dark resize-none"></textarea>
        <p v-if="errors.description" class="text-red-400 text-xs mt-1.5">{{ errors.description[0] }}</p>
      </div>

      <div>
        <label for="link" class="label-dark">Live Link</label>
        <input type="url" id="link" v-model="formData.link" placeholder="https://..." class="input-dark" />
        <p v-if="errors.link" class="text-red-400 text-xs mt-1.5">{{ errors.link[0] }}</p>
      </div>

      <div>
        <label for="github_url" class="label-dark">GitHub URL</label>
        <input type="url" id="github_url" v-model="formData.github_url" placeholder="https://github.com/..." class="input-dark" />
        <p v-if="errors.github_url" class="text-red-400 text-xs mt-1.5">{{ errors.github_url[0] }}</p>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label for="start_date" class="label-dark">Start Date</label>
          <input type="date" id="start_date" v-model="formData.start_date" class="input-dark" />
          <p v-if="errors.start_date" class="text-red-400 text-xs mt-1.5">{{ errors.start_date[0] }}</p>
        </div>
        <div>
          <label for="end_date" class="label-dark">End Date</label>
          <input type="date" id="end_date" v-model="formData.end_date" class="input-dark" />
          <p v-if="errors.end_date" class="text-red-400 text-xs mt-1.5">{{ errors.end_date[0] }}</p>
        </div>
      </div>

      <button type="submit" :disabled="loading" class="btn-primary flex items-center justify-center gap-2">
        <LoadingSpinner v-if="loading" />
        {{ loading ? 'Saving...' : 'Save Project' }}
      </button>
    </form>
    </template>
  </FormCard>
</template>
