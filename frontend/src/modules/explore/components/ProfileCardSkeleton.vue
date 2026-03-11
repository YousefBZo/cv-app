<script setup>
/**
 * ProfileCardSkeleton — Loading placeholder for ProfileCard.
 * Matches the ProfileCard layout so the user sees the shape
 * of content while profiles are loading.
 */
</script>

<template>
  <div class="glass-card overflow-hidden flex flex-col">
    <div class="p-5 pb-4 flex items-start gap-4">
      <!-- Avatar skeleton -->
      <div class="w-14 h-14 rounded-full bg-white/6 animate-pulse shrink-0"></div>
      <!-- Text skeletons -->
      <div class="flex-1 space-y-2 pt-1">
        <div class="h-3.5 w-28 bg-white/6 animate-pulse rounded"></div>
        <div class="h-3 w-40 bg-white/6 animate-pulse rounded"></div>
        <div class="h-2.5 w-20 bg-white/6 animate-pulse rounded"></div>
      </div>
    </div>
    <!-- Summary skeleton -->
    <div class="px-5 pb-3 space-y-1.5">
      <div class="h-2 w-full bg-white/6 animate-pulse rounded"></div>
      <div class="h-2 w-3/4 bg-white/6 animate-pulse rounded"></div>
    </div>
    <!-- Badges skeleton -->
    <div class="px-5 pb-4 flex gap-1.5 mt-auto">
      <div class="h-5 w-16 bg-white/6 animate-pulse rounded-full"></div>
      <div class="h-5 w-14 bg-white/6 animate-pulse rounded-full"></div>
      <div class="h-5 w-18 bg-white/6 animate-pulse rounded-full"></div>
    </div>
    <!-- Footer skeleton -->
    <div class="border-t border-white/5 px-5 py-3">
      <div class="h-2.5 w-16 bg-white/6 animate-pulse rounded"></div>
    </div>
  </div>
</template>
