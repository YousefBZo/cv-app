<script setup>
defineProps({
  icon: { type: String, required: true },
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  gradient: { type: String, default: 'from-blue-500 to-indigo-500' },
})
</script>

<template>
  <div class="flex items-center gap-3 mb-8">
    <div :class="`w-10 h-10 rounded-xl bg-gradient-to-br ${gradient} flex items-center justify-center text-lg`">
      {{ icon }}
    </div>
    <div>
      <h1 class="text-xl font-bold text-white">{{ title }}</h1>
      <p v-if="subtitle" class="text-xs text-slate-500">{{ subtitle }}</p>
    </div>
  </div>
</template>
