<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/api/http'
import { useAuthStore } from '@/modules/auth/stores/auth'
import { useFormSubmit } from '@/modules/cv/composables/useFormSubmit'
import { validateProfile, hasErrors } from '@/modules/cv/composables/useValidation'
import { useToastStore } from '@/shared/stores/toast'
import FormCard from '@/modules/cv/components/FormCard.vue'
import SectionHeader from '@/modules/cv/components/SectionHeader.vue'
import ErrorAlert from '@/shared/components/ErrorAlert.vue'
import LoadingSpinner from '@/shared/components/LoadingSpinner.vue'

const router = useRouter()
const toast = useToastStore()
const authStore = useAuthStore()

const isEditMode = ref(false)
const pageLoading = ref(true)
const activeTab = ref('profile')

// ── Profile tab state ──
const formData = ref({
  headline: '',
  summary: '',
  location: '',
})
const photo = ref(null)
const photoPreview = ref(null)

const { errors, loading, submit } = useFormSubmit('/profile', {
  onSuccess: () => {
    toast.showSuccess(isEditMode.value ? 'Profile updated!' : 'Profile created!')
    router.push({ name: 'CV' })
  },
})

// ── Account tab state ──
const userName = ref('')
const userNameLoading = ref(false)
const userNameErrors = ref({})

// ── Password tab state ──
const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})
const passwordLoading = ref(false)
const passwordErrors = ref({})

// ── Init ──
onMounted(async () => {
  try {
    const { data } = await http.get('/profile')
    const profile = data.data
    if (profile) {
      isEditMode.value = true
      formData.value.headline = profile.headline || ''
      formData.value.summary = profile.summary || ''
      formData.value.location = profile.location || ''
      if (profile.photo) {
        photoPreview.value = profile.photo
      }
    }
  } catch (err) {
    if (err.response?.status === 404) {
      isEditMode.value = false
    }
  } finally {
    pageLoading.value = false
    userName.value = authStore.userName
  }
})

// ── Profile handlers ──
const onFileChange = (e) => {
  const file = e.target.files[0]
  photo.value = file
  if (file) {
    photoPreview.value = URL.createObjectURL(file)
  }
}

const handleSubmit = () => {
  const validationErrors = validateProfile(formData.value)
  if (hasErrors(validationErrors)) {
    errors.value = validationErrors
    return
  }

  const data = new FormData()
  data.append('headline', formData.value.headline)
  data.append('summary', formData.value.summary)
  data.append('location', formData.value.location)
  if (photo.value) {
    data.append('photo', photo.value)
  }
  if (isEditMode.value) {
    data.append('_method', 'PUT')
  }
  submit(data)
}

// ── Account handlers ──
const handleUserNameSave = async () => {
  userNameErrors.value = {}
  if (!userName.value || userName.value.trim().length < 2) {
    userNameErrors.value = { name: ['Name must be at least 2 characters.'] }
    return
  }
  userNameLoading.value = true
  try {
    await authStore.updateUserName(userName.value.trim())
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

// ── Password handlers ──
const handlePasswordSave = async () => {
  passwordErrors.value = {}
  const v = {}
  if (!passwordForm.value.current_password) v.current_password = ['Current password is required.']
  if (!passwordForm.value.password || passwordForm.value.password.length < 8) v.password = ['New password must be at least 8 characters.']
  else if (passwordForm.value.password !== passwordForm.value.password_confirmation) v.password_confirmation = ['Passwords do not match.']
  if (Object.keys(v).length > 0) { passwordErrors.value = v; return }

  passwordLoading.value = true
  try {
    await authStore.updateUserPassword(
      passwordForm.value.current_password,
      passwordForm.value.password,
      passwordForm.value.password_confirmation
    )
    toast.showSuccess('Password updated successfully!')
    passwordForm.value.current_password = ''
    passwordForm.value.password = ''
    passwordForm.value.password_confirmation = ''
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

// ── Delete account ──
const handleDeleteAccount = async () => {
  if (!confirm('⚠️ Are you sure you want to delete your ENTIRE account? This cannot be undone!')) return
  if (!confirm('This will delete your profile, all CV data, and your account. Continue?')) return
  try {
    await http.delete('/profile')
    toast.showSuccess('Account deleted successfully.')
    localStorage.removeItem('auth_token')
    router.push({ name: 'Login' })
    setTimeout(() => window.location.reload(), 100)
  } catch (err) {
    toast.showError('Failed to delete account.')
  }
}
</script>

<template>
  <FormCard>
    <!-- Loading state -->
    <div v-if="pageLoading" class="flex justify-center py-12">
      <LoadingSpinner class="w-8 h-8 text-blue-400" />
    </div>

    <template v-else>
      <SectionHeader :icon="isEditMode ? '✏️' : '👤'"
        :title="isEditMode ? 'Edit Profile' : 'Create Profile'"
        :subtitle="isEditMode ? 'Manage your profile, account & password' : 'Set up your personal info & headline'"
        gradient="from-blue-500 to-cyan-500" />

      <!-- ── Tab Navigation (only in edit mode) ── -->
      <div v-if="isEditMode" class="flex gap-1 mb-6 bg-white/5 rounded-xl p-1">
        <button @click="activeTab = 'profile'"
          :class="activeTab === 'profile' ? 'bg-blue-500/20 text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
          class="flex-1 py-2.5 px-2 sm:px-3 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
          <span class="hidden sm:inline">👤 </span>Profile
        </button>
        <button @click="activeTab = 'account'"
          :class="activeTab === 'account' ? 'bg-blue-500/20 text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
          class="flex-1 py-2.5 px-2 sm:px-3 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
          <span class="hidden sm:inline">⚙️ </span>Account
        </button>
        <button @click="activeTab = 'password'"
          :class="activeTab === 'password' ? 'bg-blue-500/20 text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
          class="flex-1 py-2.5 px-2 sm:px-3 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
          <span class="hidden sm:inline">🔒 </span>Password
        </button>
      </div>

      <!-- ═══════ PROFILE TAB ═══════ -->
      <div v-if="activeTab === 'profile' || !isEditMode">
        <ErrorAlert :error="errors.general?.[0] ?? ''" />

        <form @submit.prevent="handleSubmit" class="space-y-5">
          <!-- Photo -->
          <div>
            <label class="label-dark">Profile Photo</label>
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 rounded-xl bg-white/5 border border-white/10 overflow-hidden flex items-center justify-center shrink-0">
                <img v-if="photoPreview" :src="photoPreview" alt="Profile photo preview" class="w-full h-full object-cover" />
                <span v-else class="text-2xl">📷</span>
              </div>
              <input type="file" id="photo" accept="image/*" @change="onFileChange" class="input-dark flex-1" />
            </div>
            <p v-if="errors.photo" class="text-red-400 text-xs mt-1.5">{{ errors.photo[0] }}</p>
          </div>

          <div>
            <label for="headline" class="label-dark">Headline <span class="text-red-400">*</span></label>
            <input type="text" id="headline" v-model="formData.headline" placeholder="e.g. Full Stack Developer" class="input-dark" />
            <p v-if="errors.headline" class="text-red-400 text-xs mt-1.5">{{ errors.headline[0] }}</p>
          </div>

          <div>
            <label for="summary" class="label-dark">Summary <span class="text-red-400">*</span></label>
            <textarea id="summary" v-model="formData.summary" rows="4" placeholder="Write a brief summary about yourself..." class="input-dark resize-none"></textarea>
            <p v-if="errors.summary" class="text-red-400 text-xs mt-1.5">{{ errors.summary[0] }}</p>
          </div>

          <div>
            <label for="location" class="label-dark">Location <span class="text-red-400">*</span></label>
            <input type="text" id="location" v-model="formData.location" placeholder="e.g. New York, USA" class="input-dark" />
            <p v-if="errors.location" class="text-red-400 text-xs mt-1.5">{{ errors.location[0] }}</p>
          </div>

          <button type="submit" :disabled="loading" class="btn-primary flex items-center justify-center gap-2">
            <LoadingSpinner v-if="loading" />
            {{ loading ? 'Saving...' : (isEditMode ? 'Update Profile' : 'Create Profile') }}
          </button>
        </form>
      </div>

      <!-- ═══════ ACCOUNT TAB ═══════ -->
      <div v-else-if="activeTab === 'account'">
        <div class="text-center mb-6">
          <p class="text-sm text-slate-400">Update your display name. Your email cannot be changed.</p>
        </div>

        <form @submit.prevent="handleUserNameSave" class="space-y-5">
          <div>
            <label class="label-dark">Display Name</label>
            <input v-model="userName" class="input-dark" placeholder="Your name" />
            <p v-if="userNameErrors.name" class="text-red-400 text-xs mt-1.5">{{ userNameErrors.name[0] }}</p>
          </div>

          <div>
            <label class="label-dark">Email</label>
            <input :value="authStore.user?.email" class="input-dark opacity-50 cursor-not-allowed" disabled />
            <p class="text-slate-600 text-xs mt-1">Email cannot be changed.</p>
          </div>

          <button type="submit" :disabled="userNameLoading" class="btn-primary flex items-center justify-center gap-2">
            <LoadingSpinner v-if="userNameLoading" />
            {{ userNameLoading ? 'Saving...' : 'Update Name' }}
          </button>
        </form>
      </div>

      <!-- ═══════ PASSWORD TAB ═══════ -->
      <div v-else-if="activeTab === 'password'">
        <div class="text-center mb-6">
          <p class="text-sm text-slate-400">Change your account password.</p>
        </div>

        <form @submit.prevent="handlePasswordSave" class="space-y-5">
          <div>
            <label class="label-dark">Current Password</label>
            <input type="password" v-model="passwordForm.current_password" class="input-dark" placeholder="Enter current password" />
            <p v-if="passwordErrors.current_password" class="text-red-400 text-xs mt-1.5">{{ passwordErrors.current_password[0] }}</p>
          </div>

          <div>
            <label class="label-dark">New Password</label>
            <input type="password" v-model="passwordForm.password" class="input-dark" placeholder="Min 8 characters" />
            <p v-if="passwordErrors.password" class="text-red-400 text-xs mt-1.5">{{ passwordErrors.password[0] }}</p>
          </div>

          <div>
            <label class="label-dark">Confirm New Password</label>
            <input type="password" v-model="passwordForm.password_confirmation" class="input-dark" placeholder="Repeat new password" />
            <p v-if="passwordErrors.password_confirmation" class="text-red-400 text-xs mt-1.5">{{ passwordErrors.password_confirmation[0] }}</p>
          </div>

          <button type="submit" :disabled="passwordLoading" class="btn-primary flex items-center justify-center gap-2">
            <LoadingSpinner v-if="passwordLoading" />
            {{ passwordLoading ? 'Updating...' : 'Change Password' }}
          </button>
        </form>
      </div>

      <!-- Delete Account (only in edit mode) -->
      <div v-if="isEditMode" class="mt-8 pt-6 border-t border-white/5">
        <div class="text-center">
          <p class="text-xs text-slate-600 mb-3">Want to delete your entire account and all CV data?</p>
          <button @click="handleDeleteAccount"
            class="px-6 py-2 rounded-lg text-xs font-semibold text-red-400 border border-red-500/20 hover:bg-red-500/10 transition-all">
            🗑 Delete Account
          </button>
        </div>
      </div>
    </template>
  </FormCard>
</template>
