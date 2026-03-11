import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import http from '@/api/http'
import { useAuthStore } from '@/modules/auth/stores/auth'

/**
 * Explore Store — Manages the public profiles directory.
 *
 * This store handles fetching, searching, sorting, filtering, and paginating
 * public profiles for the homepage "Explore Profiles" feature. It also manages
 * fetching a single public CV for the public CV viewer.
 *
 * DESIGN:
 *   - Separate from the CV store (which handles the authenticated user's own CV)
 *   - Pagination state is managed here so the UI can show "Load More"
 *   - Search is debounced on the UI side; the store just fires the API call
 *   - Sort & filter params are sent to the API — the backend does the work
 *   - Available locations are fetched once and cached in the store
 *
 * SORT OPTIONS:
 *   newest | oldest | name_asc | name_desc | most_skills | most_experience
 */
export const useExploreStore = defineStore('explore', () => {
  // ── Profile directory state ────────────────────────────────
  const profiles = ref([])
  const loading = ref(false)
  const error = ref(null)
  const search = ref('')

  // ── Sorting & Filtering ────────────────────────────────────
  const sortBy = ref('newest')            // current sort key
  const locationFilter = ref('')          // current location filter ('' = all)
  const availableLocations = ref([])      // cached list from API

  // ── Pagination ─────────────────────────────────────────────
  const currentPage = ref(1)
  const lastPage = ref(1)
  const total = ref(0)
  const perPage = ref(12)

  const hasMore = computed(() => currentPage.value < lastPage.value)

  /**
   * Fetch profiles for the directory listing.
   * @param {boolean} append — If true, append to existing list (load more).
   *                           If false, replace the list (new search or initial load).
   */
  async function fetchProfiles(append = false) {
    loading.value = true
    error.value = null

    try {
      const auth = useAuthStore()
      const page = append ? currentPage.value + 1 : 1
      const params = {
        page,
        per_page: perPage.value,
        sort_by: sortBy.value,
      }
      if (search.value.trim()) {
        params.search = search.value.trim()
      }
      if (locationFilter.value) {
        params.location = locationFilter.value
      }
      // Hide the authenticated user's own profile from the directory
      if (auth.user?.id) {
        params.exclude_user_id = auth.user.id
      }

      const { data } = await http.get('/public/profiles', { params })

      if (append) {
        profiles.value = [...profiles.value, ...data.data]
      } else {
        profiles.value = data.data
      }

      currentPage.value = data.pagination.current_page
      lastPage.value = data.pagination.last_page
      total.value = data.pagination.total
    } catch (err) {
      error.value = 'Failed to load profiles.'
      console.error('Explore fetch error:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Search profiles — resets pagination and fetches fresh results.
   */
  function searchProfiles(query) {
    search.value = query
    fetchProfiles(false)
  }

  /**
   * Change sort order — resets pagination and refetches.
   */
  function setSortBy(key) {
    sortBy.value = key
    fetchProfiles(false)
  }

  /**
   * Change location filter — resets pagination and refetches.
   */
  function setLocationFilter(loc) {
    locationFilter.value = loc
    fetchProfiles(false)
  }

  /**
   * Fetch the list of available locations for the filter dropdown.
   * Only called once; result is cached in `availableLocations`.
   */
  async function fetchLocations() {
    if (availableLocations.value.length > 0) return  // already cached
    try {
      const { data } = await http.get('/public/profiles/locations')
      availableLocations.value = data.data
    } catch (err) {
      console.error('Failed to fetch locations:', err)
    }
  }

  // ── Public CV state (single profile view) ──────────────────
  const publicCV = ref(null)
  const publicCVLoading = ref(false)
  const publicCVError = ref(null)

  /**
   * Fetch a single user's full CV for public viewing.
   * @param {string} slug — The user's URL slug (e.g., "yousef-bzo")
   */
  async function fetchPublicCV(slug) {
    publicCVLoading.value = true
    publicCVError.value = null
    publicCV.value = null

    try {
      const { data } = await http.get(`/public/profiles/${slug}`)
      publicCV.value = data.data
    } catch (err) {
      if (err.response?.status === 404) {
        publicCVError.value = 'Profile not found or not yet completed.'
      } else {
        publicCVError.value = 'Failed to load CV. Please try again.'
      }
    } finally {
      publicCVLoading.value = false
    }
  }

  /**
   * Reset the store state.
   */
  function $reset() {
    profiles.value = []
    loading.value = false
    error.value = null
    search.value = ''
    sortBy.value = 'newest'
    locationFilter.value = ''
    availableLocations.value = []
    currentPage.value = 1
    lastPage.value = 1
    total.value = 0
    publicCV.value = null
    publicCVLoading.value = false
    publicCVError.value = null
  }

  return {
    // directory
    profiles,
    loading,
    error,
    search,
    total,
    hasMore,
    currentPage,
    lastPage,
    fetchProfiles,
    searchProfiles,

    // sort & filter
    sortBy,
    locationFilter,
    availableLocations,
    setSortBy,
    setLocationFilter,
    fetchLocations,

    // public cv
    publicCV,
    publicCVLoading,
    publicCVError,
    fetchPublicCV,

    $reset,
  }
})
