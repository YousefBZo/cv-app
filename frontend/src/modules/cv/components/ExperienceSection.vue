<script setup>
/**
 * ExperienceSection — timeline of work experiences.
 */
defineProps({
  experiences: { type: Array, default: () => [] },
  hasMore: { type: Boolean, default: false },
  isExpanded: { type: Boolean, default: false },
})

const emit = defineEmits(['edit', 'showAll', 'showLess'])
</script>

<template>
  <section class="max-w-4xl mx-auto py-12 px-6">
    <div class="flex items-center gap-4 mb-12">
      <h2 class="text-3xl font-bold tracking-tight text-white">Experience</h2>
      <div class="h-px flex-1 bg-gradient-to-r from-blue-500/50 to-transparent"></div>
    </div>

    <div v-if="experiences.length" class="relative border-l border-slate-700 ml-3 space-y-12">
      <div v-for="exp in experiences" :key="exp.id" class="relative pl-8 group cursor-pointer"
        @click="emit('edit', exp)">
        <div class="absolute -left-[9px] top-2 w-4 h-4 rounded-full bg-slate-900 border-2 border-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.5)]"></div>
        <div class="bg-white/5 border border-white/10 p-6 rounded-2xl shadow-xl hover:bg-white/[0.08] hover:border-blue-500/30 transition-all">
          <div class="flex flex-wrap justify-between items-start gap-2 mb-4">
            <div>
              <h3 class="text-xl font-bold text-white">{{ exp.position }}</h3>
              <p class="text-blue-400 font-medium text-sm uppercase">{{ exp.company }}</p>
            </div>
            <div class="px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-300 text-xs font-semibold">
              {{ exp.start_date?.substring(0, 10) }} — {{ exp.end_date?.substring(0, 10) || 'Present' }}
            </div>
          </div>
          <p class="text-slate-400 leading-relaxed text-sm whitespace-pre-line">{{ exp.description }}</p>
        </div>
      </div>
    </div>

    <p v-else class="text-center text-slate-600 text-sm py-8">
      No experience added yet. <router-link to="/experience" class="text-blue-400 hover:underline">Add one →</router-link>
    </p>

    <div class="mt-12 flex justify-center gap-4">
      <button v-if="hasMore" @click="emit('showAll')" class="text-blue-400 font-bold uppercase text-xs tracking-widest">+ View All Experience</button>
      <button v-if="isExpanded" @click="emit('showLess')" class="text-slate-500 font-bold uppercase text-xs tracking-widest hover:underline">− Show Less</button>
    </div>
  </section>
</template>
