<script setup>
/**
 * VolunteerSection — grid of volunteer experience cards.
 */
defineProps({
  volunteers: { type: Array, default: () => [] },
  hasMore: { type: Boolean, default: false },
  isExpanded: { type: Boolean, default: false },
})

const emit = defineEmits(['edit', 'showAll', 'showLess'])
</script>

<template>
  <section class="max-w-4xl mx-auto py-8 sm:py-12 px-4 sm:px-6">
    <div class="flex items-center gap-4 mb-8 sm:mb-12">
      <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Volunteer Experience</h2>
      <div class="h-px flex-1 bg-linear-to-r from-blue-500/50 to-transparent"></div>
    </div>

    <div v-if="volunteers.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="vol in volunteers" :key="vol.id"
        @click="emit('edit', vol)"
        class="bg-white/5 border border-white/10 p-4 sm:p-6 rounded-2xl shadow-lg hover:bg-white/8 hover:border-teal-500/30 transition-all duration-300 cursor-pointer">
        <div class="flex items-start gap-4">
          <div class="p-3 rounded-lg bg-teal-500/10 text-teal-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
          </div>
          <div>
            <div class="text-teal-400 text-xs font-bold mb-1">{{ vol.start_date?.substring(0, 10) }} — {{ vol.end_date?.substring(0, 10) || 'Present' }}</div>
            <h3 class="text-lg sm:text-xl font-bold text-white mb-1">{{ vol.role }}</h3>
            <p class="text-slate-300 font-medium">{{ vol.organization }}</p>
            <p v-if="vol.description" class="text-slate-500 text-sm mt-2 italic">{{ vol.description }}</p>
          </div>
        </div>
      </div>
    </div>

    <p v-else class="text-center text-slate-600 text-sm py-8">
      No volunteer experience added yet. <router-link to="/volunteer" class="text-teal-400 hover:underline">Add one →</router-link>
    </p>

    <div class="mt-12 flex justify-center gap-4">
      <button v-if="hasMore" @click="emit('showAll')" class="px-5 sm:px-8 py-2 sm:py-3 rounded-full border border-slate-700 text-slate-400 hover:text-teal-400 hover:border-teal-500/30 transition-all text-xs font-bold uppercase tracking-widest">View More</button>
      <button v-if="isExpanded" @click="emit('showLess')" class="px-5 sm:px-8 py-2 sm:py-3 rounded-full border border-slate-700 text-slate-500 hover:text-teal-400 transition-all text-xs font-bold uppercase tracking-widest">− Show Less</button>
    </div>
  </section>
</template>
