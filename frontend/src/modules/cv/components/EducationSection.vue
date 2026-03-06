<script setup>
/**
 * EducationSection — grid of education cards.
 */
defineProps({
  educations: { type: Array, default: () => [] },
  hasMore: { type: Boolean, default: false },
  isExpanded: { type: Boolean, default: false },
})

const emit = defineEmits(['edit', 'showAll', 'showLess'])
</script>

<template>
  <section class="max-w-4xl mx-auto py-8 sm:py-12 px-4 sm:px-6">
    <div class="flex items-center gap-4 mb-8 sm:mb-12">
      <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Education</h2>
      <div class="h-px flex-1 bg-linear-to-r from-blue-500/50 to-transparent"></div>
    </div>

    <div v-if="educations.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="edu in educations" :key="edu.id"
        @click="emit('edit', edu)"
        class="bg-white/5 border border-white/10 p-4 sm:p-6 rounded-2xl shadow-lg cursor-pointer hover:bg-white/8 hover:border-blue-500/30 transition-all">
        <div class="flex items-start gap-4">
          <div class="p-3 rounded-lg bg-blue-500/10 text-blue-400">
            <svg xmlns="http://www.w3.org" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 14l9-5-9-5-9 5 9 5z" /><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /></svg>
          </div>
          <div>
            <div class="text-blue-400 text-xs font-bold mb-1">{{ edu.start_date?.substring(0, 10) }} — {{ edu.end_date?.substring(0, 10) || 'Present' }}</div>
            <h3 class="text-lg sm:text-xl font-bold text-white mb-1">{{ edu.degree }}</h3>
            <p class="text-slate-300 font-medium">{{ edu.institution }}</p>
            <p v-if="edu.field_of_study" class="text-slate-500 text-sm mt-2 italic">{{ edu.field_of_study }}</p>
          </div>
        </div>
      </div>
    </div>

    <p v-else class="text-center text-slate-600 text-sm py-8">
      No education added yet. <router-link to="/education" class="text-blue-400 hover:underline">Add one →</router-link>
    </p>

    <div class="mt-12 flex justify-center gap-4">
      <button v-if="hasMore" @click="emit('showAll')" class="px-5 sm:px-8 py-2 sm:py-3 rounded-full border border-slate-700 text-slate-400 hover:text-blue-400 transition-all text-xs font-bold uppercase tracking-widest">View More Education</button>
      <button v-if="isExpanded" @click="emit('showLess')" class="px-5 sm:px-8 py-2 sm:py-3 rounded-full border border-slate-700 text-slate-500 hover:text-blue-400 transition-all text-xs font-bold uppercase tracking-widest">− Show Less</button>
    </div>
  </section>
</template>
