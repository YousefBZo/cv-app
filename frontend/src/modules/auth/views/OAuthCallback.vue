<script setup>
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/modules/auth/stores/auth'
import LoadingSpinner from '@/shared/components/LoadingSpinner.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

onMounted(() => {
  const token = route.query.token

  if (token) {
    // Save token using the auth store action
    authStore.setToken(token)

    // Redirect to home or wherever needed
    router.replace({ name: 'HomeView' })
  } else {
    // If no token was found
    router.replace({ name: 'LoginView' })
  }
})
</script>

<template>
  <div class="min-h-[85vh] flex items-center justify-center">
    <div class="text-center">
      <LoadingSpinner class="w-12 h-12 mx-auto mb-4 text-blue-500" />
      <h2 class="text-xl font-bold text-slate-800 dark:text-white">Authenticating with Google...</h2>
      <p class="text-slate-500 mt-2">Please wait while we log you in.</p>
    </div>
  </div>
</template>