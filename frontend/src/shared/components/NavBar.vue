<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCVStore } from '@/modules/cv/stores/cv'
import { useAuthStore } from '@/modules/auth/stores/auth'
import { useLocale } from '@/shared/composables/useLocale'
import NotificationBell from '@/shared/components/NotificationBell.vue'

const cvStore = useCVStore()
const authStore = useAuthStore()
const router = useRouter()
const mobileOpen = ref(false)
const sidebarOpen = ref(false)
const { t, toggleLocale, locale, isRTL } = useLocale()

const navLinks = [
  { key: 'nav.home', path: '/', icon: '🏠' },
  { key: 'nav.explore', path: '/', hash: '#explore', icon: '🔍' },
  { key: 'nav.cv', path: '/cv', icon: '📄' },
]

const sidebarLinks = [
  { key: 'sidebar.profile', path: '/profile', icon: '👤', descKey: 'sidebar.profileDesc' },
  { key: 'sidebar.education', path: '/education', icon: '🎓', descKey: 'sidebar.educationDesc' },
  { key: 'sidebar.experience', path: '/experience', icon: '💼', descKey: 'sidebar.experienceDesc' },
  { key: 'sidebar.projects', path: '/project', icon: '🚀', descKey: 'sidebar.projectsDesc' },
  { key: 'sidebar.skills', path: '/skill', icon: '⚡', descKey: 'sidebar.skillsDesc' },
  { key: 'sidebar.languages', path: '/language', icon: '🌍', descKey: 'sidebar.languagesDesc' },
  { key: 'sidebar.certifications', path: '/certification', icon: '🏅', descKey: 'sidebar.certificationsDesc' },
  { key: 'sidebar.volunteer', path: '/volunteer', icon: '🤝', descKey: 'sidebar.volunteerDesc' },
]

const handleLogout = async () => {
  await authStore.logout()
  router.push({ name: 'Login' })
}

const goTo = (path) => {
  router.push(path)
  sidebarOpen.value = false
  mobileOpen.value = false
}
</script>

<template>
  <!-- NAVBAR -->
  <nav class="sticky top-0 z-50 w-full border-b border-white/5 bg-black/40 backdrop-blur-xl">
    <div class="max-w-6xl mx-auto flex items-center justify-between px-4 py-3">
      <!-- Left: Brand -->
      <div class="flex items-center gap-3">
        <!-- Sidebar toggle -->
        <button v-if="authStore.isAuthenticated" @click="sidebarOpen = !sidebarOpen"
          class="p-1.5 rounded-lg hover:bg-white/5 transition-colors text-slate-400 hover:text-white">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>

        <router-link to="/" class="group flex items-center gap-2">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-linear-to-br from-blue-500 to-indigo-600 text-white font-black text-xs shadow-lg shadow-blue-500/20 group-hover:shadow-blue-500/40 transition-shadow">
            {{ cvStore.userName?.charAt(0) || '?' }}
          </div>
          <span class="hidden sm:inline text-sm font-bold text-white">
            {{ cvStore.userName || t('sidebar.cvBuilder') }}
          </span>
        </router-link>
      </div>

      <!-- Center: Nav links (desktop) -->
      <ul class="hidden md:flex items-center gap-1">
        <li v-for="link in navLinks" :key="link.key">
          <router-link :to="{ path: link.path, hash: link.hash }"
            class="px-4 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition-all">
            {{ t(link.key) }}
          </router-link>
        </li>
      </ul>

      <!-- Right: Lang toggle + Auth actions -->
      <div class="flex items-center gap-2">
        <!-- Language Toggle Button -->
        <button @click="toggleLocale"
          class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 border border-white/10 hover:border-white/20 hover:text-white transition-all"
          :title="isRTL ? 'Switch to English' : 'التبديل إلى العربية'">
          {{ isRTL ? 'EN' : 'ع' }}
        </button>

        <template v-if="authStore.isAuthenticated">
          <!-- Notification Bell -->
          <NotificationBell />

          <button @click="handleLogout"
            class="hidden md:inline-flex px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-red-400 border border-white/10 hover:border-red-400/30 transition-all">
            {{ t('nav.logout') }}
          </button>
        </template>
        <template v-else>
          <router-link to="/login"
            class="hidden md:inline-flex px-4 py-1.5 rounded-lg text-xs font-medium text-slate-300 hover:text-white border border-white/10 hover:border-white/20 transition-all">
            {{ t('nav.signIn') }}
          </router-link>
          <router-link to="/register"
            class="hidden md:inline-flex px-4 py-1.5 rounded-lg text-xs font-medium text-white bg-linear-to-r from-blue-500 to-indigo-600 hover:shadow-lg hover:shadow-blue-500/25 transition-all">
            {{ t('nav.signUp') }}
          </router-link>
        </template>

        <!-- Mobile menu toggle -->
        <button @click="mobileOpen = !mobileOpen" class="md:hidden p-1.5 rounded-lg hover:bg-white/5 text-slate-400">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile dropdown -->
    <div v-if="mobileOpen" class="md:hidden border-t border-white/5 bg-black/60 backdrop-blur-xl px-4 py-3 space-y-1">
      <router-link v-for="link in navLinks" :key="link.key" :to="{ path: link.path, hash: link.hash }" @click="mobileOpen = false"
        class="block px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-white/5 transition-all">
        {{ link.icon }} {{ t(link.key) }}
      </router-link>
      <!-- Auth actions on mobile -->
      <div class="border-t border-white/5 pt-2 mt-2 space-y-1">
        <template v-if="authStore.isAuthenticated">
          <button @click="handleLogout(); mobileOpen = false"
            class="w-full text-start px-3 py-2 rounded-lg text-sm text-red-400 hover:bg-white/5 transition-all">
            🚪 {{ t('nav.logout') }}
          </button>
        </template>
        <template v-else>
          <router-link to="/login" @click="mobileOpen = false"
            class="block px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-white/5 transition-all">
            🔑 {{ t('nav.signIn') }}
          </router-link>
          <router-link to="/register" @click="mobileOpen = false"
            class="block px-3 py-2 rounded-lg text-sm text-blue-400 hover:text-blue-300 hover:bg-white/5 transition-all">
            🚀 {{ t('nav.signUp') }}
          </router-link>
        </template>
      </div>
    </div>
  </nav>

  <!-- SIDEBAR OVERLAY -->
  <Transition name="fade">
    <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm" @click="sidebarOpen = false"></div>
  </Transition>

  <!-- SIDEBAR -->
  <Transition :name="isRTL ? 'slide-rtl' : 'slide'">
    <aside v-if="sidebarOpen"
      class="fixed top-0 z-50 h-full w-72 bg-slate-950/95 backdrop-blur-xl border-white/5 shadow-2xl flex flex-col"
      :class="isRTL ? 'right-0 border-l' : 'left-0 border-r'">
      <!-- Sidebar header -->
      <div class="flex items-center justify-between p-5 border-b border-white/5">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-linear-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs font-black">
            {{ cvStore.userName?.charAt(0) || '?' }}
          </div>
          <div>
            <div class="text-sm font-bold text-white">{{ cvStore.userName }}</div>
            <div class="text-[10px] text-slate-500 uppercase tracking-widest">{{ t('sidebar.cvBuilder') }}</div>
          </div>
        </div>
        <button @click="sidebarOpen = false" class="p-1 rounded-lg hover:bg-white/5 text-slate-500 hover:text-white transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Sidebar links -->
      <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        <p class="text-[10px] font-bold text-slate-600 uppercase tracking-widest px-3 mb-2">{{ t('sidebar.cvSections') }}</p>
        <button v-for="link in sidebarLinks" :key="link.key" @click="goTo(link.path)"
          class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-start hover:bg-white/5 transition-all group">
          <span class="text-lg">{{ link.icon }}</span>
          <div>
            <div class="text-sm font-medium text-slate-300 group-hover:text-white transition-colors">{{ t(link.key) }}</div>
            <div class="text-[10px] text-slate-600">{{ t(link.descKey) }}</div>
          </div>
        </button>
      </div>

      <!-- Sidebar footer -->
      <div class="p-4 border-t border-white/5">
        <button @click="goTo('/cv')"
          class="w-full py-2.5 rounded-xl text-sm font-semibold text-white bg-linear-to-r from-blue-500 to-indigo-600 hover:shadow-lg hover:shadow-blue-500/25 transition-all text-center">
          👁 {{ t('sidebar.viewMyCV') }}
        </button>
      </div>
    </aside>
  </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-enter-active, .slide-leave-active { transition: transform 0.3s ease; }
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }

.slide-rtl-enter-active, .slide-rtl-leave-active { transition: transform 0.3s ease; }
.slide-rtl-enter-from, .slide-rtl-leave-to { transform: translateX(100%); }
</style>
