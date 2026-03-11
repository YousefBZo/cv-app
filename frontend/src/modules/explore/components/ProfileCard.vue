<script setup>
/**
 * ProfileCard — A single profile card for the homepage directory.
 *
 * Displays:
 *   - User photo (or initials fallback)
 *   - Name & headline
 *   - Location
 *   - Summary snippet
 *   - Skill/experience/project/education count badges
 *   - Click to open full CV
 */
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

const { t } = useI18n()

const props = defineProps({
  profile: { type: Object, required: true },
})

const initials = computed(() => {
  const name = props.profile.user_name || ''
  return name.split(' ').map(w => w.charAt(0)).join('').toUpperCase().slice(0, 2)
})

const badges = computed(() => {
  const b = []
  if (props.profile.skills_count > 0) {
    b.push({ label: t('explore.skills', { count: props.profile.skills_count }), icon: '⚡', color: 'text-amber-400 bg-amber-400/10 border-amber-400/20' })
  }
  if (props.profile.experiences_count > 0) {
    b.push({ label: t('explore.experiences', { count: props.profile.experiences_count }), icon: '💼', color: 'text-purple-400 bg-purple-400/10 border-purple-400/20' })
  }
  if (props.profile.projects_count > 0) {
    b.push({ label: t('explore.projects', { count: props.profile.projects_count }), icon: '🚀', color: 'text-pink-400 bg-pink-400/10 border-pink-400/20' })
  }
  if (props.profile.educations_count > 0) {
    b.push({ label: t('explore.educations', { count: props.profile.educations_count }), icon: '🎓', color: 'text-blue-400 bg-blue-400/10 border-blue-400/20' })
  }
  return b
})
</script>

<template>
  <router-link
    :to="{ name: 'PublicCV', params: { slug: profile.user_slug } }"
    class="group glass-card overflow-hidden hover:bg-white/6 hover:border-white/15 transition-all duration-300 cursor-pointer flex flex-col"
  >
    <!-- Card Top: Photo + Info -->
    <div class="p-5 pb-4 flex items-start gap-4">
      <!-- Avatar -->
      <div class="relative shrink-0">
        <div class="absolute -inset-0.5 bg-linear-to-r from-blue-500 to-indigo-500 rounded-full opacity-0 group-hover:opacity-40 blur transition-opacity duration-300"></div>
        <div class="relative">
          <img
            v-if="profile.photo"
            :src="profile.photo"
            :alt="profile.user_name"
            class="w-14 h-14 rounded-full object-cover border-2 border-white/10 group-hover:border-blue-400/30 transition-colors"
          />
          <div
            v-else
            class="w-14 h-14 rounded-full bg-linear-to-br from-blue-500/20 to-indigo-500/20 border-2 border-white/10 group-hover:border-blue-400/30 flex items-center justify-center text-blue-400 font-bold text-sm transition-colors"
          >
            {{ initials }}
          </div>
        </div>
      </div>

      <!-- Name + Headline -->
      <div class="flex-1 min-w-0">
        <h3 class="text-sm font-bold text-white group-hover:text-blue-400 transition-colors truncate">
          {{ profile.user_name }}
        </h3>
        <p class="text-xs text-slate-400 mt-0.5 line-clamp-1">
          {{ profile.headline }}
        </p>
        <div v-if="profile.location" class="flex items-center gap-1 mt-1.5 text-[11px] text-slate-500">
          <span>📍</span>
          <span class="truncate">{{ profile.location }}</span>
        </div>
      </div>
    </div>

    <!-- Summary -->
    <div v-if="profile.summary" class="px-5 pb-3">
      <p class="text-[11px] text-slate-500 leading-relaxed line-clamp-2">
        {{ profile.summary }}
      </p>
    </div>

    <!-- Badges -->
    <div v-if="badges.length" class="px-5 pb-4 mt-auto">
      <div class="flex flex-wrap gap-1.5">
        <span
          v-for="badge in badges"
          :key="badge.label"
          :class="badge.color"
          class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium border"
        >
          <span>{{ badge.icon }}</span>
          {{ badge.label }}
        </span>
      </div>
    </div>

    <!-- View CV footer -->
    <div class="mt-auto border-t border-white/5 px-5 py-3 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="text-[10px] font-semibold text-blue-400/60 uppercase tracking-widest group-hover:text-blue-400 transition-colors">
          {{ t('explore.viewCV') }}
        </span>
        <!-- Micro stats -->
        <div class="flex items-center gap-2 text-[10px] text-slate-600">
          <span v-if="profile.reactions_count > 0" class="flex items-center gap-0.5">❤️ {{ profile.reactions_count }}</span>
          <span v-if="profile.views_count > 0" class="flex items-center gap-0.5">👁️ {{ profile.views_count }}</span>
        </div>
      </div>
      <svg class="w-3.5 h-3.5 text-blue-400/60 group-hover:text-blue-400 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
    </div>
  </router-link>
</template>
