import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import http from '@/api/http'
import { useAuthStore } from '@/modules/auth/stores/auth'
import { useToastStore } from '@/shared/stores/toast'
import i18n from '@/i18n'

/**
 * OPTIMIZATION #9 — Cache CV Data & Avoid Redundant Fetches
 *
 * WHY: Without caching, every time the user navigates to any CV section
 *      (education, experience, projects...), fetchCV() fires a full GET /cv
 *      request. If the user navigates between 5 sections in 10 seconds,
 *      that's 5 identical API calls. The data hasn't changed!
 *
 * HOW: We track `lastFetchedAt` (timestamp of last successful fetch) and
 *      skip the API call if data is less than 30 seconds old. Any mutation
 *      (create/update/delete) forces a fresh fetch by passing `force=true`.
 *
 * IMPACT: Reduces API calls by ~80% during normal browsing.
 */
export const useCVStore = defineStore('cv', () => {
  const authStore = useAuthStore()

  // ── User info (from auth store) ────────────────────────────
  const userName = computed(() => authStore.userName)
  const isAuthenticated = computed(() => authStore.isAuthenticated)

  // ── CV state ───────────────────────────────────────────────
  const profile = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // ── Cache control ──────────────────────────────────────────
  const lastFetchedAt = ref(null)
  const STALE_AFTER_MS = 30_000 // 30 seconds

  /**
   * hasFetched — true after the very first API call resolves.
   * Before the first fetch, we don't know if the user has a profile or not,
   * so the template should show a skeleton rather than "No Profile" banner.
   */
  const hasFetched = ref(false)

  // ── Computed helpers for each CV section ───────────────────
  const profilePhoto = computed(() => profile.value?.photo ?? null)
  const headline = computed(() => profile.value?.headline ?? '')
  const summary = computed(() => profile.value?.summary ?? '')
  const location = computed(() => profile.value?.location ?? '')
  const educations = computed(() => profile.value?.educations ?? [])
  const experiences = computed(() => profile.value?.experiences ?? [])
  const volunteerExperiences = computed(() => profile.value?.volunteer_experiences ?? [])
  const skills = computed(() => profile.value?.skills ?? [])
  const projects = computed(() => profile.value?.projects ?? [])
  const certifications = computed(() => profile.value?.certifications ?? [])
  const languages = computed(() => profile.value?.languages ?? [])
  const hasProfile = computed(() => profile.value !== null)

  /**
   * Fetch the full CV (profile + all nested relations) from GET /api/v1/cv.
   * Skips the request if data was fetched within the last 30 seconds,
   * unless `force` is true (used after mutations).
   */
  async function fetchCV(force = false) {
    // Skip fetch if data is still fresh and not forced
    if (
      !force &&
      lastFetchedAt.value &&
      Date.now() - lastFetchedAt.value < STALE_AFTER_MS
    ) {
      return
    }

    loading.value = true
    error.value = null

    try {
      const { data } = await http.get('/cv')
      profile.value = data.data
      lastFetchedAt.value = Date.now()
    } catch (err) {
      if (err.response?.status === 404) {
        profile.value = null
        error.value = 'Profile not found. Please create your profile first.'
      } else {
        error.value = 'Failed to load CV. Please try again.'
      }
    } finally {
      loading.value = false
      hasFetched.value = true
    }
  }

  /**
   * Update a specific item in a section.
   * Forces a fresh fetch after mutation to keep data in sync.
   */
  async function updateItem(section, id, payload, isFormData = false) {
    const toast = useToastStore()
    try {
      const config = isFormData ? { headers: { 'Content-Type': 'multipart/form-data' } } : {}
      const url = id ? `/${section}/${id}` : `/${section}`
      const method = isFormData ? 'post' : 'put'
      await http[method](url, payload, config)
      toast.showSuccess(i18n.global.t('toast.updateSuccess', { section: section.charAt(0).toUpperCase() + section.slice(1) }))
      await fetchCV(true) // force=true after mutation
    } catch (err) {
      if (err.response?.status === 422) {
        throw err
      }
      toast.showError(err.response?.data?.message || i18n.global.t('toast.updateFailed'))
      throw err
    }
  }

  /**
   * Delete a specific item from a section.
   * Forces a fresh fetch after mutation.
   */
  async function deleteItem(section, id) {
    const toast = useToastStore()
    try {
      await http.delete(`/${section}/${id}`)
      toast.showSuccess(i18n.global.t('toast.deleteSuccess'))
      await fetchCV(true) // force=true after mutation
    } catch (err) {
      toast.showError(err.response?.data?.message || i18n.global.t('toast.deleteFailed2'))
      throw err
    }
  }

  /**
   * Update profile. Forces fresh fetch.
   */
  async function updateProfile(payload) {
    const toast = useToastStore()
    try {
      const config = payload instanceof FormData
        ? { headers: { 'Content-Type': 'multipart/form-data' } }
        : {}
      await http.post('/profile', payload, config)
      toast.showSuccess(i18n.global.t('toast.profileUpdated'))
      await fetchCV(true) // force=true after mutation
    } catch (err) {
      if (err.response?.status === 422) throw err
      toast.showError(err.response?.data?.message || i18n.global.t('toast.profileUpdateFailed'))
      throw err
    }
  }

  /**
   * Delete profile and account.
   */
  async function deleteProfile() {
    const toast = useToastStore()
    try {
      await http.delete('/profile')
      toast.showSuccess(i18n.global.t('toast.accountDeleted'))
      profile.value = null
      lastFetchedAt.value = null
      authStore.logout()
    } catch (err) {
      toast.showError(err.response?.data?.message || i18n.global.t('toast.deleteFailed'))
      throw err
    }
  }

  /**
   * Clear all CV data (e.g. on logout).
   */
  function $reset() {
    profile.value = null
    loading.value = false
    error.value = null
    lastFetchedAt.value = null
    hasFetched.value = false
  }

  return {
    // auth
    userName,
    isAuthenticated,

    // cv state
    profile,
    loading,
    error,
    hasProfile,
    hasFetched,

    // cv sections
    profilePhoto,
    headline,
    summary,
    location,
    educations,
    experiences,
    volunteerExperiences,
    skills,
    projects,
    certifications,
    languages,

    // actions
    fetchCV,
    updateItem,
    deleteItem,
    updateProfile,
    deleteProfile,
    $reset,
  }
})
