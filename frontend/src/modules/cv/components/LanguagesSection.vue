<script setup>
import { useI18n } from 'vue-i18n'
import { getLangLevel } from '@/modules/cv/composables/useLevelHelpers'

defineProps({
  languages: { type: Array, default: () => [] },
  hasMore: { type: Boolean, default: false },
  isExpanded: { type: Boolean, default: false },
  isVisible: { type: Boolean, default: false },
})

const emit = defineEmits(['edit', 'showAll', 'showLess'])
const { t } = useI18n()

/** Translate a raw level value (from DB) using the levels.* keys */
function translateLevel(level) {
  if (!level) return t('levels.intermediate')
  const key = String(level).toLowerCase().replace(/\s+/g, '_')
  return t(`levels.${key}`, level) // fallback to raw value if key not found
}
</script>

<template>
  <section class="max-w-4xl mx-auto py-8 sm:py-12 px-4 sm:px-6">
    <div class="flex items-center gap-4 mb-8 sm:mb-12">
      <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">{{ t('cv.languagesTitle') }}</h2>
      <div class="h-px flex-1 bg-linear-to-r from-blue-500/50 to-transparent"></div>
    </div>

    <div v-if="languages.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="lang in languages" :key="lang.id"
        @click="emit('edit', lang)"
        class="bg-white/5 border border-white/10 p-5 rounded-2xl hover:bg-white/8 hover:border-purple-500/30 transition-all duration-300 cursor-pointer">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-white">{{ lang.name }}</h3>
            <p class="text-xs font-semibold uppercase tracking-wider" :class="{
              'text-emerald-400': getLangLevel(lang.level).color.includes('emerald'),
              'text-blue-400': getLangLevel(lang.level).color.includes('blue'),
              'text-amber-400': getLangLevel(lang.level).color.includes('amber'),
              'text-rose-400': getLangLevel(lang.level).color.includes('rose'),
              'text-slate-400': getLangLevel(lang.level).color.includes('slate'),
            }">{{ translateLevel(lang.level) }}</p>
          </div>
        </div>
        <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
          <div class="h-full rounded-full bg-linear-to-r transition-all duration-1000"
            :class="getLangLevel(lang.level).color"
            :style="{ width: isVisible ? getLangLevel(lang.level).width : '0%' }"></div>
        </div>
      </div>
    </div>

    <p v-else class="text-center text-slate-600 text-sm py-8">
      {{ t('cv.noLanguages') }} <router-link to="/language" class="text-purple-400 hover:underline">{{ t('cv.addLanguages') }}</router-link>
    </p>

    <div class="mt-8 flex justify-center gap-4">
      <button v-if="hasMore" @click="emit('showAll')" class="text-purple-400 text-xs font-bold uppercase tracking-widest hover:underline">{{ t('cv.seeAllLanguages') }}</button>
      <button v-if="isExpanded" @click="emit('showLess')" class="text-slate-500 text-xs font-bold uppercase tracking-widest hover:underline">{{ t('cv.showLess') }}</button>
    </div>
  </section>
</template>
