<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/modules/auth/stores/auth'
import PasswordInput from '@/modules/auth/components/PasswordInput.vue'
import LoadingSpinner from '@/shared/components/LoadingSpinner.vue'

const router = useRouter()
const authStore = useAuthStore()

const formData = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const errors = ref({})
const loading = ref(false)

const handleRegister = async () => {
  errors.value = {}
  loading.value = true

  try {
    await authStore.register(
      formData.value.name,
      formData.value.email,
      formData.value.password,
      formData.value.password_confirmation,
    )
    router.push({ name: 'HomeView' })
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors
    } else {
      console.error('Registration failed:', err)
      errors.value.general = ['Something went wrong. Please try again.']
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-[85vh] flex items-center justify-center px-4">
    <!-- Decorative blobs -->
    <div class="fixed top-20 right-20 w-72 h-72 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed bottom-10 left-10 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative">
      <div class="glass-card p-8 shadow-2xl shadow-black/20">
        <!-- Icon -->
        <div class="flex justify-center mb-6">
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-purple-500/25">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
            </svg>
          </div>
        </div>

        <h1 class="text-2xl font-bold text-center text-white mb-1">Create Account</h1>
        <p class="text-sm text-slate-500 text-center mb-8">Start building your professional CV today</p>

        <p v-if="errors.general"
          class="text-red-400 text-sm text-center mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20">
          {{ errors.general[0] }}
        </p>

        <form @submit.prevent="handleRegister" class="space-y-4">
          <div>
            <label for="name" class="label-dark">Full Name</label>
            <input type="text" v-model="formData.name" id="name" required placeholder="John Doe" class="input-dark" />
            <p v-if="errors.name" class="text-red-400 text-xs mt-1.5">{{ errors.name[0] }}</p>
          </div>

          <div>
            <label for="email" class="label-dark">Email Address</label>
            <input type="email" v-model="formData.email" id="email" required placeholder="you@example.com" class="input-dark" />
            <p v-if="errors.email" class="text-red-400 text-xs mt-1.5">{{ errors.email[0] }}</p>
          </div>

          <div>
            <label for="password" class="label-dark">Password</label>
            <PasswordInput
              v-model="formData.password"
              :error="errors.password?.[0] ?? ''"
            />
          </div>

          <div>
            <label for="password_confirmation" class="label-dark">Confirm Password</label>
            <input type="password" v-model="formData.password_confirmation" id="password_confirmation"
              required placeholder="••••••••" class="input-dark" />
            <p v-if="errors.password_confirmation" class="text-red-400 text-xs mt-1.5">{{ errors.password_confirmation[0] }}</p>
          </div>

          <button type="submit" :disabled="loading" class="btn-primary flex items-center justify-center gap-2 mt-2">
            <LoadingSpinner v-if="loading" />
            {{ loading ? 'Creating account...' : 'Create Account' }}
          </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
          Already have an account?
          <router-link to="/login" class="text-blue-400 hover:text-blue-300 font-medium transition-colors">Sign in</router-link>
        </p>
      </div>
    </div>
  </div>
</template>
