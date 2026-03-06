import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
import App from './App.vue'
import router from './router'
import { useAuthStore } from '@/modules/auth/stores/auth'

const app = createApp(App)
app.use(createPinia())

// Rehydrate auth state from saved token BEFORE installing router,
// so the router guard sees the correct isAuthenticated value.
const authStore = useAuthStore()
const init = authStore.token ? authStore.fetchUser() : Promise.resolve()

init.finally(() => {
  app.use(router)
  app.mount('#app')
})

