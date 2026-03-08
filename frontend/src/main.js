import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
import App from './App.vue'
import router from './router'
import i18n from './i18n'
import { useAuthStore } from '@/modules/auth/stores/auth'

const app = createApp(App)
app.use(createPinia())
app.use(i18n)

// Apply initial direction (RTL/LTR) based on saved locale
const savedLocale = localStorage.getItem('locale') || 'en'
document.documentElement.setAttribute('dir', savedLocale === 'ar' ? 'rtl' : 'ltr')
document.documentElement.setAttribute('lang', savedLocale)

const authStore = useAuthStore()

/**
 * WHY we still block on auth before installing the router:
 *
 * The router guard checks `auth.isAuthenticated` on EVERY navigation.
 * If we mount the app and install the router before the GET /user call
 * finishes, the guard sees isAuthenticated=false and redirects to /login —
 * even if the user has a valid token. The user would briefly see the login
 * page, then get redirected back when auth resolves. That's worse UX than
 * a brief wait.
 *
 * HOWEVER: the real bottleneck is that GET /user runs BEFORE the CV page
 * can even begin loading. We solve that in the CV store by using a skeleton
 * UI — the page renders its shape instantly while data loads.
 *
 * FUTURE OPTIMIZATION: If this becomes a problem, add an `authReady` flag
 * and make the router guard await it, so the app can mount immediately
 * and show a global skeleton while auth resolves in the background.
 */
const init = authStore.token ? authStore.fetchUser() : Promise.resolve()

init.finally(() => {
  app.use(router)
  app.mount('#app')
})

