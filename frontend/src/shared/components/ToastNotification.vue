<script setup>
import { useToastStore } from '@/shared/stores/toast'

const toastStore = useToastStore()

const typeClasses = {
  error: 'bg-red-500/15 border-red-500/30 text-red-400',
  success: 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400',
  warning: 'bg-amber-500/15 border-amber-500/30 text-amber-400',
}

const typeIcons = {
  error: '✕',
  success: '✓',
  warning: '⚠',
}
</script>

<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 rtl:right-auto rtl:left-4 z-100 flex flex-col gap-3 max-w-sm w-full pointer-events-none">
      <TransitionGroup name="toast">
        <div v-for="toast in toastStore.toasts" :key="toast.id"
          class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-xl border backdrop-blur-xl shadow-2xl transition-all duration-300"
          :class="typeClasses[toast.type] || typeClasses.error">
          <span class="text-sm font-bold mt-0.5">{{ typeIcons[toast.type] }}</span>
          <p class="text-sm flex-1 font-medium">{{ toast.message }}</p>
          <button @click="toastStore.removeToast(toast.id)"
            class="text-xs opacity-60 hover:opacity-100 transition-opacity mt-0.5">✕</button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-enter-active { transition: all 0.3s ease-out; }
.toast-leave-active { transition: all 0.2s ease-in; }
.toast-enter-from { opacity: 0; transform: translateX(100px); }
.toast-leave-to { opacity: 0; transform: translateX(100px); }
.toast-move { transition: transform 0.3s ease; }

/* RTL: slide from left instead of right */
[dir="rtl"] .toast-enter-from { transform: translateX(-100px); }
[dir="rtl"] .toast-leave-to { transform: translateX(-100px); }
</style>

