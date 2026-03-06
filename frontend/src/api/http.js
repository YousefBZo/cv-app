import axios from 'axios'

export const BASE_URL = 'http://localhost:8082'
export const API_URL = `${BASE_URL}/api/v1`

const http = axios.create({
  baseURL: API_URL,
  headers: {
    Accept: 'application/json',
  },
})

// Request interceptor: attach bearer token if available
http.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Response interceptor: handle errors globally
http.interceptors.response.use(
  (response) => response,
  (error) => {
    // Lazy-import to avoid circular dependency issues at module load time
    const showGlobalToast = async (msg, type = 'error') => {
      try {
        const { useToastStore } = await import('@/shared/stores/toast')
        const toast = useToastStore()
        if (type === 'error') toast.showError(msg)
        else if (type === 'warning') toast.showWarning(msg)
      } catch {
        // Toast store not ready yet — silent fail
      }
    }

    // Network error (server down, no connection)
    if (!error.response) {
      showGlobalToast('Network error — please check your connection or the server may be down.')
      return Promise.reject(error)
    }

    const status = error.response.status
    const message = error.response.data?.message || ''

    switch (status) {
      case 401:
        localStorage.removeItem('auth_token')
        showGlobalToast('Session expired. Please log in again.', 'warning')
        break
      case 403:
        showGlobalToast(message || 'You do not have permission to perform this action.')
        break
      case 404:
        // Don't toast 404s for profile check — handled by views
        if (!message.includes('Profile not found')) {
          showGlobalToast(message || 'The requested resource was not found.')
        }
        break
      case 409:
        showGlobalToast(message || 'This record already exists.')
        break
      case 422:
        // Validation errors — handled by form components, no global toast
        break
      case 429:
        showGlobalToast('Too many requests. Please slow down.')
        break
      case 500:
        showGlobalToast(message || 'Server error. Please try again later.')
        break
      default:
        if (status >= 500) {
          showGlobalToast('Server error. Please try again later.')
        }
    }

    return Promise.reject(error)
  }
)

export default http
