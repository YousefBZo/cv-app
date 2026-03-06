<script setup>
/**
 * HeroSection — the profile hero banner at the top of the CV page.
 * Receives profile data via props, emits 'edit' when clicked.
 */
import { useCVStore } from '@/modules/cv/stores/cv'

defineProps({
  isVisible: { type: Boolean, default: false },
})

const emit = defineEmits(['edit'])
const cvStore = useCVStore()
</script>

<template>
  <section
    class="flex flex-col items-center justify-center py-24 px-6 transition-opacity duration-1000 cursor-pointer relative group"
    :class="isVisible ? 'opacity-100' : 'opacity-0'"
    @click="emit('edit')"
  >
    <div class="absolute top-28 right-8 opacity-0 group-hover:opacity-100 transition-opacity text-xs text-blue-400 font-medium">
      ✏️ Click to edit profile
    </div>
    <div class="relative mb-8">
      <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-cyan-400 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000"></div>
      <div class="relative bg-slate-900 rounded-full p-1 border border-white/10 shadow-2xl">
        <img v-if="cvStore.profilePhoto" :src="cvStore.profilePhoto" alt="Profile" class="rounded-full w-44 h-44 object-cover" />
        <div v-else class="rounded-full w-44 h-44 bg-slate-800 flex items-center justify-center text-slate-400 border border-dashed border-slate-600">
          <span class="text-sm font-medium uppercase tracking-widest text-center px-4">No Photo</span>
        </div>
      </div>
    </div>
    <div class="text-center max-w-2xl mx-auto space-y-4">
      <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-b from-white to-slate-400">
        {{ cvStore.headline }}
      </h1>
      <p class="text-lg md:text-xl text-slate-300 font-light leading-relaxed italic">"{{ cvStore.summary }}"</p>
      <div class="flex items-center justify-center gap-2 pt-4 text-sm font-medium text-blue-400 uppercase tracking-widest">
        <span class="w-8 h-[1px] bg-blue-500/50"></span>
        <span>📍 {{ cvStore.location }}</span>
        <span class="w-8 h-[1px] bg-blue-500/50"></span>
      </div>
    </div>
  </section>
</template>
