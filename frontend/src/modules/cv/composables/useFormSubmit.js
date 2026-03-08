import { ref } from 'vue'
import http from '@/api/http'
import i18n from '@/i18n'

/**
 * Reusable form submission composable.
 * Handles request → error handling pattern.
 *
 * OPTIMIZATION #8 — Prevent Double Clicking
 *
 * WHY: Without a guard, if a user clicks "Save" twice quickly, two identical
 *      POST requests fire simultaneously. This can create duplicate records
 *      in the database (two identical experiences, certifications, etc.).
 *
 * HOW: We check `if (loading.value) return` at the top of submit().
 *      Since `loading` is a Vue ref, it's shared across the component.
 *      The first click sets loading=true, so the second click is blocked.
 *      The button in the template should use `:disabled="loading"` to give
 *      visual feedback that the request is in progress.
 *
 * @param {string} endpoint  - API path, e.g. "/experience"
 * @param {object} options   - { onSuccess(response), method }
 */
export function useFormSubmit(endpoint, options = {}) {
  const errors = ref({})
  const loading = ref(false)

  /**
   * Submit form data to the API endpoint.
   * Automatically detects FormData for file uploads.
   *
   * @param {object|FormData} payload - the data to send
   * @param {object} overrides - { endpoint, method } to override per call
   */
  async function submit(payload, overrides = {}) {
    // GUARD: Prevent double submission — if already loading, ignore the click
    if (loading.value) return

    errors.value = {}
    loading.value = true

    const targetEndpoint = overrides.endpoint || endpoint
    const method = overrides.method || options.method || 'post'

    try {
      const config = payload instanceof FormData
        ? { headers: { 'Content-Type': 'multipart/form-data' } }
        : {}

      const response = await http[method](targetEndpoint, payload, config)

      if (overrides.onSuccess) {
        overrides.onSuccess(response)
      } else if (options.onSuccess) {
        options.onSuccess(response)
      }

      return response
    } catch (error) {
      const t = i18n.global.t
      if (error.response?.status === 422) {
        errors.value = error.response.data.errors || {}
      } else if (error.response?.status === 409) {
        errors.value = { general: [t('validation.recordExists')] }
      } else if (error.response?.status === 404) {
        errors.value = { general: [error.response.data?.message || t('validation.notFoundError')] }
      } else if (error.response?.status === 403) {
        errors.value = { general: [error.response.data?.message || t('validation.permissionError')] }
      } else if (!error.response) {
        errors.value = { general: [t('validation.networkError')] }
      } else {
        errors.value = { general: [t('validation.genericError')] }
      }
      throw error
    } finally {
      loading.value = false
    }
  }

  return { errors, loading, submit }
}
