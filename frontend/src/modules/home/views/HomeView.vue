<script setup>
import { useCVStore } from '@/modules/cv/stores/cv'
import { useAuthStore } from '@/modules/auth/stores/auth'
import { onMounted } from 'vue'

const cvStore = useCVStore()
const authStore = useAuthStore()

onMounted(() => {
  if (authStore.isAuthenticated && !cvStore.hasProfile) {
    cvStore.fetchCV()
  }
})

const sections = [
  { name: 'Profile', path: '/profile', icon: '👤', color: 'from-blue-500 to-cyan-500', desc: 'Personal info, photo & headline' },
  { name: 'Education', path: '/education', icon: '🎓', color: 'from-indigo-500 to-blue-500', desc: 'Schools, degrees & fields' },
  { name: 'Experience', path: '/experience', icon: '💼', color: 'from-purple-500 to-indigo-500', desc: 'Work history & positions' },
  { name: 'Projects', path: '/project', icon: '🚀', color: 'from-pink-500 to-purple-500', desc: 'Portfolio & repositories' },
  { name: 'Skills', path: '/skill', icon: '⚡', color: 'from-amber-500 to-orange-500', desc: 'Technical abilities & expertise' },
  { name: 'Languages', path: '/language', icon: '🌍', color: 'from-green-500 to-emerald-500', desc: 'Languages you speak' },
  { name: 'Certifications', path: '/certification', icon: '🏅', color: 'from-yellow-500 to-amber-500', desc: 'Certificates & awards' },
  { name: 'Volunteer', path: '/volunteer', icon: '🤝', color: 'from-teal-500 to-cyan-500', desc: 'Community work & giving back' },
]
</script>

<template>
  <div class="min-h-[85vh] max-w-5xl mx-auto px-4 py-10 sm:py-16">
    <!-- Hero -->
    <div class="text-center mb-10 sm:mb-16">
      <div v-if="authStore.isAuthenticated" class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-semibold mb-6">
        <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
        Welcome back, {{ cvStore.userName }}
      </div>
      <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white mb-4 tracking-tight">
        Build Your <span class="bg-linear-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Professional CV</span>
      </h1>
      <p class="text-slate-400 text-base sm:text-lg max-w-xl mx-auto">
        Create a stunning, interactive curriculum vitae. Add your sections below and preview anytime.
      </p>

      <!-- CTA -->
      <div class="flex items-center justify-center gap-4 mt-8">
        <router-link v-if="authStore.isAuthenticated" to="/cv"
          class="px-6 py-3 rounded-xl text-sm font-semibold text-white bg-linear-to-r from-blue-500 to-indigo-600 hover:shadow-lg hover:shadow-blue-500/25 transition-all">
          👁 View My CV
        </router-link>
        <router-link v-else to="/register"
          class="px-6 py-3 rounded-xl text-sm font-semibold text-white bg-linear-to-r from-blue-500 to-indigo-600 hover:shadow-lg hover:shadow-blue-500/25 transition-all">
          🚀 Get Started Free
        </router-link>
      </div>
    </div>

    <!-- Section Cards Grid -->
    <div v-if="authStore.isAuthenticated">
      <div class="flex items-center gap-3 mb-8">
        <h2 class="text-xl font-bold text-white">CV Sections</h2>
        <div class="section-line"></div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <router-link v-for="s in sections" :key="s.name" :to="s.path"
          class="group glass-card p-5 hover:bg-white/6 hover:border-white/15 transition-all duration-300 cursor-pointer">
          <div class="flex items-start gap-3">
            <div :class="`w-10 h-10 rounded-xl bg-linear-to-br ${s.color} flex items-center justify-center text-lg shadow-lg group-hover:scale-110 transition-transform`">
              {{ s.icon }}
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="text-sm font-bold text-white group-hover:text-blue-400 transition-colors">{{ s.name }}</h3>
              <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">{{ s.desc }}</p>
            </div>
          </div>
          <div class="mt-3 flex items-center gap-1 text-[10px] font-semibold text-blue-400/60 uppercase tracking-widest group-hover:text-blue-400 transition-colors">
            <span>Add / Edit</span>
            <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </router-link>
      </div>
    </div>

    <!-- Not logged in -->
    <div v-else class="text-center mt-8">
      <div class="glass-card p-10 max-w-lg mx-auto">
        <div class="text-5xl mb-4">📝</div>
        <h3 class="text-lg font-bold text-white mb-2">Ready to get started?</h3>
        <p class="text-slate-500 text-sm mb-6">Create an account or sign in to build your professional CV.</p>
        <div class="flex items-center justify-center gap-3">
          <router-link to="/login" class="px-5 py-2 rounded-lg text-sm font-medium text-slate-300 border border-white/10 hover:border-white/20 transition-all">Sign In</router-link>
          <router-link to="/register" class="px-5 py-2 rounded-lg text-sm font-medium text-white bg-linear-to-r from-blue-500 to-indigo-600 transition-all">Sign Up</router-link>
        </div>
      </div>
    </div>
  </div>
</template>
