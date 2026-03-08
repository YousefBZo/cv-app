<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/modules/auth/stores/auth'
import PasswordInput from '@/modules/auth/components/PasswordInput.vue'
import LoadingSpinner from '@/shared/components/LoadingSpinner.vue'

const { t } = useI18n()
const router = useRouter()
const authStore = useAuthStore()

const errors = ref({})
const loading = ref(false)

const formData = ref({
  email: '',
  password: '',
})

const handleLogin = async () => {
  errors.value = {}
  loading.value = true

  try {
    await authStore.login(formData.value.email, formData.value.password)
    router.push({ name: 'HomeView' })
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors
    } else {
      console.error('Login failed:', err)
      errors.value.general = [t('auth.somethingWrong')]
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-[85vh] flex items-center justify-center px-4">
    <!-- Decorative blobs -->
    <div class="fixed top-20 left-10 w-72 h-72 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed bottom-20 right-10 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative">
      <div class="glass-card p-8 shadow-2xl shadow-black/20">
        <!-- Icon -->
        <div class="flex justify-center mb-6">
          <div class="w-16 h-16 rounded-2xl bg-linear-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/25">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
          </div>
        </div>

        <h1 class="text-2xl font-bold text-center text-white mb-1">{{ t('auth.welcomeBack') }}</h1>
        <p class="text-sm text-slate-500 text-center mb-8">{{ t('auth.signInSubtitle') }}</p>

        <p v-if="errors.general"
          class="text-red-400 text-sm text-center mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20">
          {{ errors.general[0] }}
        </p>

        <form @submit.prevent="handleLogin" class="space-y-5">
          <div>
            <label for="email" class="label-dark">{{ t('auth.emailLabel') }}</label>
            <input type="email" id="email" v-model="formData.email" required
              :placeholder="t('auth.emailPlaceholder')" class="input-dark" />
            <p v-if="errors.email" class="text-red-400 text-xs mt-1.5">{{ errors.email[0] }}</p>
          </div>

          <div>
            <label for="password" class="label-dark">{{ t('auth.passwordLabel') }}</label>
            <PasswordInput
              v-model="formData.password"
              :error="errors.password?.[0] ?? ''"
            />
          </div>

          <button type="submit" :disabled="loading" class="btn-primary flex items-center justify-center gap-2">
            <LoadingSpinner v-if="loading" />
            {{ loading ? t('auth.signingIn') : t('auth.signInBtn') }}
          </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
          {{ t('auth.noAccount') }}
          <router-link to="/register" class="text-blue-400 hover:text-blue-300 font-medium transition-colors">
            {{ t('auth.createOne') }}
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>
