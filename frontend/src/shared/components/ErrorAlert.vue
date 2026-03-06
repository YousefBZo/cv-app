<script setup>
defineProps({
  error: { type: String, default: '' },
})
</script>

<template>
  <p v-if="error"
    class="text-red-400 text-sm text-center mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20">
    {{ error }}
  </p>
</template>
