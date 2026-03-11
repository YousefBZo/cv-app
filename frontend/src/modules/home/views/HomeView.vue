<script setup>
/**
 * HomeView — Professional landing page with profile directory.
 *
 * DESIGN:
 *   - Hero section with compelling CTA for new visitors
 *   - "Explore Profiles" directory below the hero
 *   - Search/filter profiles by name, headline, location
 *   - Paginated profile cards grid with "Load More"
 *   - When authenticated: also show quick-access CV section cards
 *
 * This is the main entry point of the app, designed to serve both:
 *   1. New visitors / recruiters — browse profiles, view CVs
 *   2. Authenticated users — quick access to edit their own CV sections
 */
import { useCVStore } from '@/modules/cv/stores/cv'
import { useAuthStore } from '@/modules/auth/stores/auth'
import { useExploreStore } from '@/modules/explore/stores/explore'
import { useI18n } from 'vue-i18n'
import { onMounted, ref } from 'vue'
import ProfileCard from '@/modules/explore/components/ProfileCard.vue'
import ProfileCardSkeleton from '@/modules/explore/components/ProfileCardSkeleton.vue'
import SearchBar from '@/modules/explore/components/SearchBar.vue'

const cvStore = useCVStore()
const authStore = useAuthStore()
const explore = useExploreStore()
const { t } = useI18n()

const showSections = ref(false)

const sections = [
  { key: 'sidebar.profile', path: '/profile', icon: '👤', color: 'from-blue-500 to-cyan-500', descKey: 'home.profileCard' },
  { key: 'sidebar.education', path: '/education', icon: '🎓', color: 'from-indigo-500 to-blue-500', descKey: 'home.educationCard' },
  { key: 'sidebar.experience', path: '/experience', icon: '💼', color: 'from-purple-500 to-indigo-500', descKey: 'home.experienceCard' },
  { key: 'sidebar.projects', path: '/project', icon: '🚀', color: 'from-pink-500 to-purple-500', descKey: 'home.projectsCard' },
  { key: 'sidebar.skills', path: '/skill', icon: '⚡', color: 'from-amber-500 to-orange-500', descKey: 'home.skillsCard' },
  { key: 'sidebar.languages', path: '/language', icon: '🌍', color: 'from-green-500 to-emerald-500', descKey: 'home.languagesCard' },
  { key: 'sidebar.certifications', path: '/certification', icon: '🏅', color: 'from-yellow-500 to-amber-500', descKey: 'home.certificationsCard' },
  { key: 'sidebar.volunteer', path: '/volunteer', icon: '🤝', color: 'from-teal-500 to-cyan-500', descKey: 'home.volunteerCard' },
]

onMounted(() => {
  // Fetch public profiles for the directory
  explore.fetchProfiles()

  // Fetch available locations for the filter dropdown (cached — only hits API once)
  explore.fetchLocations()

  // If authenticated, also fetch their own CV
  if (authStore.isAuthenticated && !cvStore.hasProfile) {
    cvStore.fetchCV()
  }
})

function onSearch(query) {
  explore.searchProfiles(query)
}

function onSort(key) {
  explore.setSortBy(key)
}

function onFilterLocation(loc) {
  explore.setLocationFilter(loc)
}

function loadMore() {
  explore.fetchProfiles(true)
}
</script>

<template>
  <div class="min-h-[85vh]">
    <!-- ═══════════════════════════════════════════════════════════
         HERO SECTION
         ═══════════════════════════════════════════════════════════ -->
    <section class="relative overflow-hidden">
      <!-- Background decorative elements -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-20 left-1/4 w-72 h-72 bg-blue-500/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-1/4 w-96 h-96 bg-indigo-500/5 rounded-full blur-3xl"></div>
      </div>

      <div class="relative max-w-5xl mx-auto px-4 py-12 sm:py-20 text-center">
        <!-- Welcome badge (authenticated users) -->
        <div v-if="authStore.isAuthenticated"
          class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-semibold mb-6">
          <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
          {{ t('home.welcomeBack', { name: cvStore.userName }) }}
        </div>

        <!-- Main heading -->
        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-white mb-5 tracking-tight leading-tight">
          {{ t('home.buildYour') }}
          <span class="bg-linear-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">{{ t('home.professionalCV') }}</span>
        </h1>

        <p class="text-slate-400 text-base sm:text-lg max-w-2xl mx-auto mb-8 leading-relaxed">
          {{ t('explore.heroDescription') }}
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-wrap items-center justify-center gap-3 mb-12">
          <router-link v-if="authStore.isAuthenticated" to="/cv"
            class="px-6 py-3 rounded-xl text-sm font-semibold text-white bg-linear-to-r from-blue-500 to-indigo-600 hover:shadow-lg hover:shadow-blue-500/25 transition-all hover:scale-105 active:scale-95">
            👁 {{ t('home.viewMyCV') }}
          </router-link>
          <router-link v-else to="/register"
            class="px-6 py-3 rounded-xl text-sm font-semibold text-white bg-linear-to-r from-blue-500 to-indigo-600 hover:shadow-lg hover:shadow-blue-500/25 transition-all hover:scale-105 active:scale-95">
            🚀 {{ t('home.getStarted') }}
          </router-link>
          <a href="#explore"
            class="px-6 py-3 rounded-xl text-sm font-semibold text-slate-300 border border-white/10 hover:border-white/20 hover:text-white transition-all">
            🔍 {{ t('explore.browseProfiles') }}
          </a>
        </div>

        <!-- Stats row -->
        <div class="flex flex-wrap items-center justify-center gap-8 sm:gap-12">
          <div class="text-center">
            <div class="text-2xl mb-1">👥</div>
            <div class="text-xl sm:text-2xl font-bold text-white">{{ explore.total }}</div>
            <div class="text-[11px] text-slate-500 uppercase tracking-widest">{{ t('explore.totalProfiles') }}</div>
          </div>
          <div class="text-center">
            <div class="text-2xl mb-1">🌍</div>
            <div class="text-xl sm:text-2xl font-bold text-white">∞</div>
            <div class="text-[11px] text-slate-500 uppercase tracking-widest">{{ t('explore.globalReach') }}</div>
          </div>
          <div class="text-center">
            <div class="text-2xl mb-1">🔒</div>
            <div class="text-xl sm:text-2xl font-bold text-white">100%</div>
            <div class="text-[11px] text-slate-500 uppercase tracking-widest">{{ t('explore.securePrivate') }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════
         MY CV SECTIONS (Authenticated Only)
         ═══════════════════════════════════════════════════════════ -->
    <section v-if="authStore.isAuthenticated" class="max-w-5xl mx-auto px-4 mb-16">
      <button @click="showSections = !showSections"
        class="flex items-center gap-3 mb-6 group cursor-pointer w-full">
        <h2 class="text-lg font-bold text-white">{{ t('home.cvSections') }}</h2>
        <div class="section-line"></div>
        <svg class="w-4 h-4 text-slate-500 group-hover:text-white transition-all duration-300"
          :class="showSections ? 'rotate-180' : ''"
          fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <Transition name="slide-down">
        <div v-if="showSections" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <router-link v-for="s in sections" :key="s.key" :to="s.path"
            class="group glass-card p-4 hover:bg-white/6 hover:border-white/15 transition-all duration-300 cursor-pointer">
            <div class="flex items-start gap-3">
              <div :class="`w-9 h-9 rounded-xl bg-linear-to-br ${s.color} flex items-center justify-center text-base shadow-lg group-hover:scale-110 transition-transform`">
                {{ s.icon }}
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="text-xs font-bold text-white group-hover:text-blue-400 transition-colors">{{ t(s.key) }}</h3>
                <p class="text-[10px] text-slate-500 mt-0.5 leading-relaxed">{{ t(s.descKey) }}</p>
              </div>
            </div>
          </router-link>
        </div>
      </Transition>
    </section>

    <!-- ═══════════════════════════════════════════════════════════
         EXPLORE PROFILES DIRECTORY
         ═══════════════════════════════════════════════════════════ -->
    <section id="explore" class="max-w-5xl mx-auto px-4 pb-16 scroll-mt-20">
      <!-- Section header -->
      <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-[11px] font-semibold uppercase tracking-widest mb-4">
          <span>🌐</span> {{ t('explore.directory') }}
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-white mb-3">
          {{ t('explore.discoverTalent') }}
        </h2>
        <p class="text-slate-400 text-sm max-w-lg mx-auto">
          {{ t('explore.directorySubtitle') }}
        </p>
      </div>

      <!-- Search bar + Sort + Filter -->
      <div class="mb-8">
        <SearchBar
          :total="explore.total"
          :sort-by="explore.sortBy"
          :location-filter="explore.locationFilter"
          :locations="explore.availableLocations"
          @search="onSearch"
          @sort="onSort"
          @filter-location="onFilterLocation"
        />
      </div>

      <!-- Profiles grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Skeletons while loading (initial load) -->
        <template v-if="explore.loading && explore.profiles.length === 0">
          <ProfileCardSkeleton v-for="i in 6" :key="'skeleton-' + i" />
        </template>

        <!-- Profile cards -->
        <ProfileCard
          v-for="profile in explore.profiles"
          :key="profile.id"
          :profile="profile"
        />
      </div>

      <!-- Empty state -->
      <div v-if="!explore.loading && explore.profiles.length === 0" class="text-center py-16">
        <div class="text-5xl mb-4">🔍</div>
        <h3 class="text-lg font-bold text-white mb-2">{{ t('explore.noProfiles') }}</h3>
        <p class="text-slate-500 text-sm">{{ t('explore.noProfilesSubtitle') }}</p>
      </div>

      <!-- Load More button -->
      <div v-if="explore.hasMore" class="flex justify-center mt-8">
        <button @click="loadMore" :disabled="explore.loading"
          class="px-6 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-white/10 hover:border-blue-400/30 hover:text-blue-400 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
          <span v-if="explore.loading" class="flex items-center gap-2">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            {{ t('explore.loading') }}
          </span>
          <span v-else>{{ t('explore.loadMore') }}</span>
        </button>
      </div>

      <!-- Total count -->
      <div v-if="explore.total > 0 && !explore.loading" class="text-center mt-6">
        <span class="text-xs text-slate-600">
          {{ t('explore.showingOf', { showing: explore.profiles.length, total: explore.total }) }}
        </span>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════
         CTA SECTION (Not logged in)
         ═══════════════════════════════════════════════════════════ -->
    <section v-if="!authStore.isAuthenticated" class="max-w-5xl mx-auto px-4 pb-16">
      <div class="glass-card p-10 text-center max-w-lg mx-auto">
        <div class="text-5xl mb-4">📝</div>
        <h3 class="text-lg font-bold text-white mb-2">{{ t('home.readyTitle') }}</h3>
        <p class="text-slate-500 text-sm mb-6">{{ t('home.readySubtitle') }}</p>
        <div class="flex items-center justify-center gap-3">
          <router-link to="/login" class="px-5 py-2 rounded-lg text-sm font-medium text-slate-300 border border-white/10 hover:border-white/20 transition-all">{{ t('nav.signIn') }}</router-link>
          <router-link to="/register" class="px-5 py-2 rounded-lg text-sm font-medium text-white bg-linear-to-r from-blue-500 to-indigo-600 transition-all">{{ t('nav.signUp') }}</router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}
.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  max-height: 0;
  transform: translateY(-10px);
}
.slide-down-enter-to,
.slide-down-leave-from {
  opacity: 1;
  max-height: 500px;
}
</style>
