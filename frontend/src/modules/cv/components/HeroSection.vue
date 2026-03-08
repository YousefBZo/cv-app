<script setup>
import { useCVStore } from '@/modules/cv/stores/cv'
import { useI18n } from 'vue-i18n'

defineProps({
  isVisible: { type: Boolean, default: false },
})

const emit = defineEmits(['edit'])
const cvStore = useCVStore()
const { t } = useI18n()
</script>

<template>
  <section
    class="flex flex-col items-center justify-center py-14 sm:py-24 px-4 sm:px-6 transition-opacity duration-1000 cursor-pointer relative group"
    :class="isVisible ? 'opacity-100' : 'opacity-0'"
    @click="emit('edit')"
  >
    <div class="absolute top-16 right-4 sm:top-28 sm:right-8 opacity-0 group-hover:opacity-100 transition-opacity text-xs text-blue-400 font-medium">
      ✏️ {{ t('cv.clickToEdit') }}
    </div>
    <div class="relative mb-8">
      <div class="absolute -inset-1 bg-linear-to-r from-blue-600 to-cyan-400 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000"></div>
      <div class="relative bg-slate-900 rounded-full p-1 border border-white/10 shadow-2xl">
        <img v-if="cvStore.profilePhoto" :src="cvStore.profilePhoto" alt="Profile" class="rounded-full w-32 h-32 sm:w-44 sm:h-44 object-cover" />
        <div v-else class="rounded-full w-32 h-32 sm:w-44 sm:h-44 bg-slate-800 flex items-center justify-center text-slate-400 border border-dashed border-slate-600">
          <span class="text-sm font-medium uppercase tracking-widest text-center px-4">{{ t('cv.noPhoto') }}</span>
        </div>
      </div>
    </div>
    <div class="text-center max-w-2xl mx-auto space-y-4">
      <h1 class="text-3xl sm:text-5xl md:text-6xl font-extrabold tracking-tight bg-clip-text text-transparent bg-linear-to-b from-white to-slate-400">
        {{ cvStore.headline }}
      </h1>
      <p class="text-base sm:text-lg md:text-xl text-slate-300 font-light leading-relaxed italic">"{{ cvStore.summary }}"</p>
      <div class="flex items-center justify-center gap-2 pt-4 text-sm font-medium text-blue-400 uppercase tracking-widest">
        <span class="w-8 h-px bg-blue-500/50"></span>
        <span>📍 {{ cvStore.location }}</span>
        <span class="w-8 h-px bg-blue-500/50"></span>
      </div>
    </div>
  </section>
</template>
