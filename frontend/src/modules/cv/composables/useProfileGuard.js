import { ref, onMounted } from 'vue'
import http from '@/api/http'

/**
 * Composable that checks if the user has a profile.
 * Used by all section views to block creation if no profile exists.
 */
export function useProfileGuard() {
  const hasProfile = ref(null) // null = loading, true/false = result
  const profileLoading = ref(true)

  onMounted(async () => {
    try {
      await http.get('/profile')
      hasProfile.value = true
    } catch (err) {
      if (err.response?.status === 404) {
        hasProfile.value = false
      } else {
        hasProfile.value = false
      }
    } finally {
      profileLoading.value = false
    }
  })

  return { hasProfile, profileLoading }
}

