<script setup>
/**
 * SkillsSection — grid of skill cards with animated level bars.
 */
import { getSkillLevel } from '@/modules/cv/composables/useLevelHelpers'

defineProps({
  skills: { type: Array, default: () => [] },
  hasMore: { type: Boolean, default: false },
  isExpanded: { type: Boolean, default: false },
  isVisible: { type: Boolean, default: false },
})

const emit = defineEmits(['edit', 'showAll', 'showLess'])
</script>

<template>
  <section class="max-w-4xl mx-auto py-8 sm:py-12 px-4 sm:px-6">
    <div class="flex items-center gap-4 mb-8 sm:mb-12">
      <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Skills</h2>
      <div class="h-px flex-1 bg-linear-to-r from-blue-500/50 to-transparent"></div>
    </div>

    <div v-if="skills.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="(skill, idx) in skills" :key="idx"
        @click="emit('edit', skill)"
        class="bg-white/5 p-5 rounded-2xl border border-white/10 cursor-pointer hover:bg-white/8 hover:border-blue-500/30 transition-all">
        <div class="flex justify-between items-end mb-4">
          <div>
            <div class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1">{{ skill.level ?? 'Proficient' }}</div>
            <div class="text-lg font-bold text-white">{{ skill.name }}</div>
          </div>
        </div>
        <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
          <div class="h-full bg-linear-to-r from-blue-600 to-cyan-400 transition-all duration-1000"
            :style="{ width: isVisible ? getSkillLevel(skill.level) : '0%' }"></div>
        </div>
      </div>
    </div>

    <p v-else class="text-center text-slate-600 text-sm py-8">
      No skills added yet. <router-link to="/skill" class="text-blue-400 hover:underline">Add some →</router-link>
    </p>

    <div class="mt-8 flex justify-center gap-4">
      <button v-if="hasMore" @click="emit('showAll')" class="text-blue-400 text-xs font-bold uppercase tracking-widest">+ See All Skills</button>
      <button v-if="isExpanded" @click="emit('showLess')" class="text-slate-500 text-xs font-bold uppercase tracking-widest hover:underline">− Show Less</button>
    </div>
  </section>
</template>
