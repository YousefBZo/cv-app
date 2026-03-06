import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import http from '@/api/http'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token'))
  const loading = ref(false)

  const isAuthenticated = computed(() => !!user.value)
  const userName = computed(() => user.value?.name ?? '')

  /**
   * Login with email & password.
   * Uses Fortify's POST /login endpoint (prefixed at /api/v1/login).
   * Fortify returns a Sanctum token because we customised LoginResponse.
   */
  async function login(email, password) {
    const { data } = await http.post('/login', { email, password })

    token.value = data.token
    localStorage.setItem('auth_token', data.token)

    await fetchUser()
  }

  /**
   * Register a new account.
   * Uses Fortify's POST /register endpoint (prefixed at /api/v1/register).
   */
  async function register(name, email, password, password_confirmation) {
    await http.post('/register', { name, email, password, password_confirmation })

    await fetchUser()
  }

  /**
   * Fetch the authenticated user from GET /api/v1/user.
   * Protected by Sanctum's auth:sanctum middleware.
   */
  async function fetchUser() {
    try {
      loading.value = true
      const { data } = await http.get('/user')
      // Laravel UserResource wraps the response in a `data` key
      user.value = data.data ?? data
    } catch {
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
    } finally {
      loading.value = false
    }
  }

  /**
   * Logout — clear token locally and revoke it on the server.
   */
  async function logout() {
    try {
      await http.post('/logout')
    } catch {
      // ignore errors during logout
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
    }
  }

  /**
   * Update the authenticated user's display name.
   */
  async function updateUserName(name) {
    const { data } = await http.put('/user/name', { name })
    user.value = data.data ?? data
    return data
  }

  /**
   * Update the authenticated user's password.
   */
  async function updateUserPassword(current_password, password, password_confirmation) {
    const { data } = await http.put('/user/password', {
      current_password,
      password,
      password_confirmation,
    })
    return data
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    userName,
    login,
    register,
    fetchUser,
    logout,
    updateUserName,
    updateUserPassword,
  }
})
