import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useCVStore } from '@/modules/cv/stores/cv'
import { useAuthStore } from '@/modules/auth/stores/auth'
import { useToastStore } from '@/shared/stores/toast'
import { validateProfile, hasErrors } from '@/modules/cv/composables/useValidation'

/**
 * Composable — encapsulates all state & logic for the profile / account
 * / password edit modal. Keeps the view layer thin.
 */
export function useProfileModal() {
  const cvStore = useCVStore()
  const authStore = useAuthStore()
  const toast = useToastStore()
  const router = useRouter()

  // ── Visibility ──────────────────────────────────────────────
  const visible = ref(false)
  const tab = ref('profile') // 'profile' | 'account' | 'password'

  // ── Profile tab ─────────────────────────────────────────────
  const profileForm = reactive({ headline: '', summary: '', location: '' })
  const profilePhoto = ref(null)
  const profilePhotoPreview = ref(null)
  const profileLoading = ref(false)
  const profileErrors = ref({})

  // ── Account tab ─────────────────────────────────────────────
  const userNameForm = reactive({ name: '' })
  const userNameLoading = ref(false)
  const userNameErrors = ref({})

  // ── Password tab ────────────────────────────────────────────
  const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' })
  const passwordLoading = ref(false)
  const passwordErrors = ref({})

  /** Open the modal — redirects to /profile if no profile exists yet */
  function open() {
    if (!cvStore.hasProfile) {
      router.push({ name: 'Profile' })
      return
    }
    profileErrors.value = {}
    userNameErrors.value = {}
    passwordErrors.value = {}
    profileForm.headline = cvStore.headline
    profileForm.summary = cvStore.summary
    profileForm.location = cvStore.location
    profilePhoto.value = null
    profilePhotoPreview.value = cvStore.profilePhoto
    userNameForm.name = authStore.userName
    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
    tab.value = 'profile'
    visible.value = true
  }

  function close() {
    visible.value = false
  }

  function onPhotoChange(e) {
    const file = e.target.files[0]
    profilePhoto.value = file
    if (file) profilePhotoPreview.value = URL.createObjectURL(file)
  }

  // ── Save handlers ───────────────────────────────────────────

  async function saveProfile() {
    profileErrors.value = {}
    const v = validateProfile(profileForm)
    if (hasErrors(v)) { profileErrors.value = v; return }

    profileLoading.value = true
    try {
      const data = new FormData()
      data.append('headline', profileForm.headline)
      data.append('summary', profileForm.summary)
      data.append('location', profileForm.location)
      if (profilePhoto.value) data.append('photo', profilePhoto.value)
      data.append('_method', 'PUT')
      await cvStore.updateItem('profile', '', data, true)
      visible.value = false
    } catch (err) {
      if (err.response?.status === 422) {
        profileErrors.value = err.response.data.errors || {}
      }
    } finally {
      profileLoading.value = false
    }
  }

  async function saveUserName() {
    userNameErrors.value = {}
    if (!userNameForm.name || userNameForm.name.trim().length < 2) {
      userNameErrors.value = { name: ['Name must be at least 2 characters.'] }
      return
    }
    userNameLoading.value = true
    try {
      await authStore.updateUserName(userNameForm.name.trim())
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

  async function savePassword() {
    passwordErrors.value = {}
    const v = {}
    if (!passwordForm.current_password) v.current_password = ['Current password is required.']
    if (!passwordForm.password || passwordForm.password.length < 8) v.password = ['New password must be at least 8 characters.']
    else if (passwordForm.password !== passwordForm.password_confirmation) v.password_confirmation = ['Passwords do not match.']
    if (Object.keys(v).length > 0) { passwordErrors.value = v; return }

    passwordLoading.value = true
    try {
      await authStore.updateUserPassword(
        passwordForm.current_password,
        passwordForm.password,
        passwordForm.password_confirmation,
      )
      toast.showSuccess('Password updated successfully!')
      passwordForm.current_password = ''
      passwordForm.password = ''
      passwordForm.password_confirmation = ''
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

  async function deleteAccount() {
    if (!confirm('⚠️ Are you sure you want to delete your ENTIRE account? This cannot be undone!')) return
    if (!confirm('This will delete your profile, all CV data, and your account. Continue?')) return
    profileLoading.value = true
    try {
      await cvStore.deleteProfile()
      router.push({ name: 'Login' })
    } catch {
      // handled by store
    } finally {
      profileLoading.value = false
    }
  }

  return {
    // State
    visible,
    tab,
    profileForm,
    profilePhoto,
    profilePhotoPreview,
    profileLoading,
    profileErrors,
    userNameForm,
    userNameLoading,
    userNameErrors,
    passwordForm,
    passwordLoading,
    passwordErrors,
    // Methods
    open,
    close,
    onPhotoChange,
    saveProfile,
    saveUserName,
    savePassword,
    deleteAccount,
  }
}
