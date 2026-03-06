<script setup>
import LoadingSpinner from '@/shared/components/LoadingSpinner.vue'

defineProps({
  visible: { type: Boolean, default: false },
  title: { type: String, default: 'Edit' },
  loading: { type: Boolean, default: false },
  showDelete: { type: Boolean, default: true },
})

const emit = defineEmits(['close', 'delete'])
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="visible" class="fixed inset-0 z-[90] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="emit('close')"></div>

        <!-- Modal -->
        <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto bg-slate-900 border border-white/10 rounded-2xl shadow-2xl z-10">
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
            <h2 class="text-lg font-bold text-white">{{ title }}</h2>
            <button @click="emit('close')"
              class="p-1.5 rounded-lg hover:bg-white/5 text-slate-500 hover:text-white transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Body (slot) -->
          <div class="px-6 py-5">
            <slot />
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-between px-6 py-4 border-t border-white/5">
            <button v-if="showDelete" @click="emit('delete')" :disabled="loading"
              class="px-4 py-2 rounded-lg text-xs font-semibold text-red-400 border border-red-500/20 hover:bg-red-500/10 transition-all disabled:opacity-50">
              <span v-if="loading"><LoadingSpinner /></span>
              <span v-else>🗑 Delete</span>
            </button>
            <div v-else></div>
            <button @click="emit('close')"
              class="px-4 py-2 rounded-lg text-xs font-medium text-slate-400 border border-white/10 hover:bg-white/5 transition-all">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active { transition: all 0.3s ease-out; }
.modal-leave-active { transition: all 0.2s ease-in; }
.modal-enter-from { opacity: 0; }
.modal-enter-from .relative { transform: scale(0.95) translateY(10px); }
.modal-leave-to { opacity: 0; }
</style>

