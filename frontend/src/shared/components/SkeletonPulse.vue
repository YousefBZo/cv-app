<script setup>
/**
 * SkeletonPulse — a reusable placeholder shimmer block.
 *
 * WHY: Instead of showing a spinner and hiding the entire page, skeleton
 *      screens show the *shape* of the content while data loads. The user's
 *      brain processes layout before text, so by the time real data arrives,
 *      the page already "feels" loaded.
 *
 * USAGE:
 *   <SkeletonPulse class="h-6 w-48" />        → text line
 *   <SkeletonPulse class="h-44 w-44 rounded-full" /> → avatar
 *   <SkeletonPulse class="h-40 rounded-2xl" /> → card
 *
 * The component is intentionally minimal — just a pulsing div. All sizing
 * is controlled by the parent via Tailwind classes.
 */
</script>

<template>
  <div class="animate-pulse rounded-lg bg-white/6" />
</template>
