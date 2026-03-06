<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCVStore } from '@/modules/cv/stores/cv'
import { useAuthStore } from '@/modules/auth/stores/auth'

const cvStore = useCVStore()
const authStore = useAuthStore()
const router = useRouter()
const mobileOpen = ref(false)
const sidebarOpen = ref(false)

const navLinks = [
  { name: 'Home', path: '/', icon: '🏠' },
  { name: 'CV', path: '/cv', icon: '📄' },
]

const sidebarLinks = [
  { name: 'Profile', path: '/profile', icon: '👤', desc: 'Personal info & photo' },
  { name: 'Education', path: '/education', icon: '🎓', desc: 'Schools & degrees' },
  { name: 'Experience', path: '/experience', icon: '💼', desc: 'Work history' },
  { name: 'Projects', path: '/project', icon: '🚀', desc: 'Your portfolio' },
  { name: 'Skills', path: '/skill', icon: '⚡', desc: 'Technical abilities' },
  { name: 'Languages', path: '/language', icon: '🌍', desc: 'Languages spoken' },
  { name: 'Certifications', path: '/certification', icon: '🏅', desc: 'Certificates & awards' },
  { name: 'Volunteer', path: '/volunteer', icon: '🤝', desc: 'Community work' },
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
            {{ cvStore.userName || 'CV Builder' }}
          </span>
        </router-link>
      </div>

      <!-- Center: Nav links (desktop) -->
      <ul class="hidden md:flex items-center gap-1">
        <li v-for="link in navLinks" :key="link.name">
          <router-link :to="link.path"
            class="px-4 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition-all">
            {{ link.name }}
          </router-link>
        </li>
      </ul>

      <!-- Right: Auth actions -->
      <div class="flex items-center gap-2">
        <template v-if="authStore.isAuthenticated">

          <button @click="handleLogout"
            class="hidden md:inline-flex px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-red-400 border border-white/10 hover:border-red-400/30 transition-all">
            Logout
          </button>
        </template>
        <template v-else>
          <router-link to="/login"
            class="hidden md:inline-flex px-4 py-1.5 rounded-lg text-xs font-medium text-slate-300 hover:text-white border border-white/10 hover:border-white/20 transition-all">
            Sign In
          </router-link>
          <router-link to="/register"
            class="hidden md:inline-flex px-4 py-1.5 rounded-lg text-xs font-medium text-white bg-linear-to-r from-blue-500 to-indigo-600 hover:shadow-lg hover:shadow-blue-500/25 transition-all">
            Sign Up
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
      <router-link v-for="link in navLinks" :key="link.name" :to="link.path" @click="mobileOpen = false"
        class="block px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-white/5 transition-all">
        {{ link.icon }} {{ link.name }}
      </router-link>
      <!-- Auth actions on mobile -->
      <div class="border-t border-white/5 pt-2 mt-2 space-y-1">
        <template v-if="authStore.isAuthenticated">
          <button @click="handleLogout(); mobileOpen = false"
            class="w-full text-left px-3 py-2 rounded-lg text-sm text-red-400 hover:bg-white/5 transition-all">
            🚪 Logout
          </button>
        </template>
        <template v-else>
          <router-link to="/login" @click="mobileOpen = false"
            class="block px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-white/5 transition-all">
            🔑 Sign In
          </router-link>
          <router-link to="/register" @click="mobileOpen = false"
            class="block px-3 py-2 rounded-lg text-sm text-blue-400 hover:text-blue-300 hover:bg-white/5 transition-all">
            🚀 Sign Up
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
  <Transition name="slide">
    <aside v-if="sidebarOpen"
      class="fixed top-0 left-0 z-50 h-full w-72 bg-slate-950/95 backdrop-blur-xl border-r border-white/5 shadow-2xl flex flex-col">
      <!-- Sidebar header -->
      <div class="flex items-center justify-between p-5 border-b border-white/5">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-linear-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs font-black">
            {{ cvStore.userName?.charAt(0) || '?' }}
          </div>
          <div>
            <div class="text-sm font-bold text-white">{{ cvStore.userName }}</div>
            <div class="text-[10px] text-slate-500 uppercase tracking-widest">CV Builder</div>
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
        <p class="text-[10px] font-bold text-slate-600 uppercase tracking-widest px-3 mb-2">CV Sections</p>
        <button v-for="link in sidebarLinks" :key="link.name" @click="goTo(link.path)"
          class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left hover:bg-white/5 transition-all group">
          <span class="text-lg">{{ link.icon }}</span>
          <div>
            <div class="text-sm font-medium text-slate-300 group-hover:text-white transition-colors">{{ link.name }}</div>
            <div class="text-[10px] text-slate-600">{{ link.desc }}</div>
          </div>
        </button>
      </div>

      <!-- Sidebar footer -->
      <div class="p-4 border-t border-white/5">
        <button @click="goTo('/cv')"
          class="w-full py-2.5 rounded-xl text-sm font-semibold text-white bg-linear-to-r from-blue-500 to-indigo-600 hover:shadow-lg hover:shadow-blue-500/25 transition-all text-center">
          👁 View My CV
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
</style>
