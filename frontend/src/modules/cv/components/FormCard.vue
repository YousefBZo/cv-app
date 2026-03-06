<script setup>
defineProps({
  maxWidth: { type: String, default: 'max-w-lg' },
})
</script>

<template>
  <div class="min-h-[85vh] flex items-start justify-center px-4 py-12">
    <div :class="`w-full ${maxWidth}`">
      <div class="glass-card p-8">
        <slot />
      </div>
    </div>
  </div>
</template>
