<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import http from '@/api/http'
import { useAuthStore } from '@/modules/auth/stores/auth'
import { useFormSubmit } from '@/modules/cv/composables/useFormSubmit'
import { validateProfile, hasErrors } from '@/modules/cv/composables/useValidation'
import { useToastStore } from '@/shared/stores/toast'
import FormCard from '@/modules/cv/components/FormCard.vue'
import SectionHeader from '@/modules/cv/components/SectionHeader.vue'
import ErrorAlert from '@/shared/components/ErrorAlert.vue'
import LoadingSpinner from '@/shared/components/LoadingSpinner.vue'

const { t } = useI18n()
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
    toast.showSuccess(isEditMode.value ? t('toast.profileUpdated') : t('toast.profileCreated'))
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
    userNameErrors.value = { name: [t('validation.nameMin')] }
    return
  }
  userNameLoading.value = true
  try {
    await authStore.updateUserName(userName.value.trim())
    toast.showSuccess(t('toast.nameUpdated'))
  } catch (err) {
    if (err.response?.status === 422) {
      userNameErrors.value = err.response.data.errors || {}
    } else {
      userNameErrors.value = { name: [err.response?.data?.message || t('validation.genericError')] }
    }
  } finally {
    userNameLoading.value = false
  }
}

// ── Password handlers ──
const handlePasswordSave = async () => {
  passwordErrors.value = {}
  const v = {}
  if (!passwordForm.value.current_password) v.current_password = [t('validation.currentPasswordRequired')]
  if (!passwordForm.value.password || passwordForm.value.password.length < 8) v.password = [t('validation.passwordMin')]
  else if (passwordForm.value.password !== passwordForm.value.password_confirmation) v.password_confirmation = [t('validation.passwordMismatch')]
  if (Object.keys(v).length > 0) { passwordErrors.value = v; return }

  passwordLoading.value = true
  try {
    await authStore.updateUserPassword(
      passwordForm.value.current_password,
      passwordForm.value.password,
      passwordForm.value.password_confirmation
    )
    toast.showSuccess(t('toast.passwordUpdated'))
    passwordForm.value.current_password = ''
    passwordForm.value.password = ''
    passwordForm.value.password_confirmation = ''
  } catch (err) {
    if (err.response?.status === 422) {
      passwordErrors.value = err.response.data.errors || {}
    } else {
      passwordErrors.value = { current_password: [err.response?.data?.message || t('validation.genericError')] }
    }
  } finally {
    passwordLoading.value = false
  }
}

// ── Delete account ──
const handleDeleteAccount = async () => {
  if (!confirm(t('forms.deleteConfirm1'))) return
  if (!confirm(t('forms.deleteConfirm2'))) return
  try {
    await http.delete('/profile')
    toast.showSuccess(t('toast.accountDeleted'))
    localStorage.removeItem('auth_token')
    router.push({ name: 'Login' })
    setTimeout(() => window.location.reload(), 100)
  } catch (err) {
    toast.showError(t('toast.deleteFailed'))
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
        :title="isEditMode ? t('forms.editProfile') : t('forms.createProfile')"
        :subtitle="isEditMode ? t('forms.editProfileSubtitle') : t('forms.createProfileSubtitle')"
        gradient="from-blue-500 to-cyan-500" />

      <!-- ── Tab Navigation (only in edit mode) ── -->
      <div v-if="isEditMode" class="flex gap-1 mb-6 bg-white/5 rounded-xl p-1">
        <button @click="activeTab = 'profile'"
          :class="activeTab === 'profile' ? 'bg-blue-500/20 text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
          class="flex-1 py-2.5 px-2 sm:px-3 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
          <span class="hidden sm:inline">👤 </span>{{ t('forms.profileTab') }}
        </button>
        <button @click="activeTab = 'account'"
          :class="activeTab === 'account' ? 'bg-blue-500/20 text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
          class="flex-1 py-2.5 px-2 sm:px-3 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
          <span class="hidden sm:inline">⚙️ </span>{{ t('forms.accountTab') }}
        </button>
        <button @click="activeTab = 'password'"
          :class="activeTab === 'password' ? 'bg-blue-500/20 text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
          class="flex-1 py-2.5 px-2 sm:px-3 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
          <span class="hidden sm:inline">🔒 </span>{{ t('forms.passwordTab') }}
        </button>
      </div>

      <!-- ═══════ PROFILE TAB ═══════ -->
      <div v-if="activeTab === 'profile' || !isEditMode">
        <ErrorAlert :error="errors.general?.[0] ?? ''" />

        <form @submit.prevent="handleSubmit" class="space-y-5">
          <!-- Photo -->
          <div>
            <label class="label-dark">{{ t('forms.profilePhoto') }}</label>
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
            <label for="headline" class="label-dark">{{ t('forms.headline') }} <span class="text-red-400">*</span></label>
            <input type="text" id="headline" v-model="formData.headline" :placeholder="t('forms.headlinePlaceholder')" class="input-dark" />
            <p v-if="errors.headline" class="text-red-400 text-xs mt-1.5">{{ errors.headline[0] }}</p>
          </div>

          <div>
            <label for="summary" class="label-dark">{{ t('forms.summary') }} <span class="text-red-400">*</span></label>
            <textarea id="summary" v-model="formData.summary" rows="4" :placeholder="t('forms.summaryPlaceholder')" class="input-dark resize-none"></textarea>
            <p v-if="errors.summary" class="text-red-400 text-xs mt-1.5">{{ errors.summary[0] }}</p>
          </div>

          <div>
            <label for="location" class="label-dark">{{ t('forms.location') }} <span class="text-red-400">*</span></label>
            <input type="text" id="location" v-model="formData.location" :placeholder="t('forms.locationPlaceholder')" class="input-dark" />
            <p v-if="errors.location" class="text-red-400 text-xs mt-1.5">{{ errors.location[0] }}</p>
          </div>

          <button type="submit" :disabled="loading" class="btn-primary flex items-center justify-center gap-2">
            <LoadingSpinner v-if="loading" />
            {{ loading ? t('forms.saving') : (isEditMode ? t('forms.updateProfile') : t('forms.createProfile')) }}
          </button>
        </form>
      </div>

      <!-- ═══════ ACCOUNT TAB ═══════ -->
      <div v-else-if="activeTab === 'account'">
        <div class="text-center mb-6">
          <p class="text-sm text-slate-400">{{ t('forms.updateNameDesc') }}</p>
        </div>

        <form @submit.prevent="handleUserNameSave" class="space-y-5">
          <div>
            <label class="label-dark">{{ t('forms.displayName') }}</label>
            <input v-model="userName" class="input-dark" :placeholder="t('forms.displayNamePlaceholder')" />
            <p v-if="userNameErrors.name" class="text-red-400 text-xs mt-1.5">{{ userNameErrors.name[0] }}</p>
          </div>

          <div>
            <label class="label-dark">{{ t('forms.email') }}</label>
            <input :value="authStore.user?.email" class="input-dark opacity-50 cursor-not-allowed" disabled />
            <p class="text-slate-600 text-xs mt-1">{{ t('forms.emailCannotChange') }}</p>
          </div>

          <button type="submit" :disabled="userNameLoading" class="btn-primary flex items-center justify-center gap-2">
            <LoadingSpinner v-if="userNameLoading" />
            {{ userNameLoading ? t('forms.saving') : t('forms.updateName') }}
          </button>
        </form>
      </div>

      <!-- ═══════ PASSWORD TAB ═══════ -->
      <div v-else-if="activeTab === 'password'">
        <div class="text-center mb-6">
          <p class="text-sm text-slate-400">{{ t('forms.changePasswordDesc') }}</p>
        </div>

        <form @submit.prevent="handlePasswordSave" class="space-y-5">
          <div>
            <label class="label-dark">{{ t('forms.currentPassword') }}</label>
            <input type="password" v-model="passwordForm.current_password" class="input-dark" :placeholder="t('forms.currentPasswordPlaceholder')" />
            <p v-if="passwordErrors.current_password" class="text-red-400 text-xs mt-1.5">{{ passwordErrors.current_password[0] }}</p>
          </div>

          <div>
            <label class="label-dark">{{ t('forms.newPassword') }}</label>
            <input type="password" v-model="passwordForm.password" class="input-dark" :placeholder="t('forms.newPasswordPlaceholder')" />
            <p v-if="passwordErrors.password" class="text-red-400 text-xs mt-1.5">{{ passwordErrors.password[0] }}</p>
          </div>

          <div>
            <label class="label-dark">{{ t('forms.confirmNewPassword') }}</label>
            <input type="password" v-model="passwordForm.password_confirmation" class="input-dark" :placeholder="t('forms.confirmNewPasswordPlaceholder')" />
            <p v-if="passwordErrors.password_confirmation" class="text-red-400 text-xs mt-1.5">{{ passwordErrors.password_confirmation[0] }}</p>
          </div>

          <button type="submit" :disabled="passwordLoading" class="btn-primary flex items-center justify-center gap-2">
            <LoadingSpinner v-if="passwordLoading" />
            {{ passwordLoading ? t('forms.updating') : t('forms.changePassword') }}
          </button>
        </form>
      </div>

      <!-- Delete Account (only in edit mode) -->
      <div v-if="isEditMode" class="mt-8 pt-6 border-t border-white/5">
        <div class="text-center">
          <p class="text-xs text-slate-600 mb-3">{{ t('forms.deleteAccountTitle') }}</p>
          <button @click="handleDeleteAccount"
            class="px-6 py-2 rounded-lg text-xs font-semibold text-red-400 border border-red-500/20 hover:bg-red-500/10 transition-all">
            🗑 {{ t('forms.deleteAccountBtn') }}
          </button>
        </div>
      </div>
    </template>
  </FormCard>
</template>
