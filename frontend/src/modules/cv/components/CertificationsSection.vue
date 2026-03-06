<script setup>
/**
 * CertificationsSection — grid of certification cards.
 */
defineProps({
  certifications: { type: Array, default: () => [] },
  hasMore: { type: Boolean, default: false },
  isExpanded: { type: Boolean, default: false },
})

const emit = defineEmits(['edit', 'showAll', 'showLess'])
</script>

<template>
  <section class="max-w-4xl mx-auto py-8 sm:py-12 px-4 sm:px-6">
    <div class="flex items-center gap-4 mb-8 sm:mb-12">
      <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Certifications</h2>
      <div class="h-px flex-1 bg-linear-to-r from-blue-500/50 to-transparent"></div>
    </div>

    <div v-if="certifications.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="cert in certifications" :key="cert.id"
        @click="emit('edit', cert)"
        class="bg-white/5 border border-white/10 p-4 sm:p-6 rounded-2xl shadow-lg hover:bg-white/8 hover:border-amber-500/30 transition-all duration-300 cursor-pointer">
        <div class="flex items-start gap-4">
          <div class="p-3 rounded-lg bg-amber-500/10 text-amber-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
            </svg>
          </div>
          <div class="flex-1">
            <div class="text-amber-400 text-xs font-bold mb-1">
              {{ cert.issue_date?.substring(0, 10) }}
              <span v-if="cert.expiration_date"> — {{ cert.expiration_date?.substring(0, 10) }}</span>
            </div>
            <h3 class="text-lg sm:text-xl font-bold text-white mb-1">{{ cert.name }}</h3>
            <p class="text-slate-300 font-medium">{{ cert.organization }}</p>
            <div v-if="cert.photo" class="mt-3">
              <img :src="cert.photo" :alt="cert.name" class="h-16 rounded-lg object-contain border border-white/10" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <p v-else class="text-center text-slate-600 text-sm py-8">
      No certifications added yet. <router-link to="/certification" class="text-amber-400 hover:underline">Add one →</router-link>
    </p>

    <div class="mt-12 flex justify-center gap-4">
      <button v-if="hasMore" @click="emit('showAll')" class="px-5 sm:px-8 py-2 sm:py-3 rounded-full border border-slate-700 text-slate-400 hover:text-amber-400 hover:border-amber-500/30 transition-all text-xs font-bold uppercase tracking-widest">View More Certifications</button>
      <button v-if="isExpanded" @click="emit('showLess')" class="px-5 sm:px-8 py-2 sm:py-3 rounded-full border border-slate-700 text-slate-500 hover:text-amber-400 transition-all text-xs font-bold uppercase tracking-widest">− Show Less</button>
    </div>
  </section>
</template>
