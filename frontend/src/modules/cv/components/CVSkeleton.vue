<script setup>
/**
 * CVSkeleton — skeleton screen for the /cv page.
 *
 * This component renders the *exact layout shape* of CVView.vue but with
 * pulsing placeholder blocks instead of real content. It's shown instantly
 * (0ms) while the API call to GET /cv is in flight.
 *
 * DESIGN RULES:
 *   1. Match the real layout's widths, heights, and spacing exactly.
 *   2. Use the same section order as CVView (hero → projects → experience → …).
 *   3. Keep it lightweight — no logic, no props, no API calls.
 *
 * This is a PRESENTATION-ONLY component. The parent (CVView) controls
 * when to show this vs the real content via v-if/v-else.
 */
import SkeletonPulse from '@/shared/components/SkeletonPulse.vue'
</script>

<template>
  <div class="min-h-screen text-slate-100 font-sans overflow-x-hidden pb-20"
    style="background-image: linear-gradient(135deg, #000b18 0%, #00264d 100%);">

    <!-- 1. HERO SKELETON -->
    <section class="flex flex-col items-center justify-center py-14 sm:py-24 px-4 sm:px-6">
      <!-- Avatar -->
      <SkeletonPulse class="w-32 h-32 sm:w-44 sm:h-44 rounded-full mb-8" />
      <!-- Headline -->
      <SkeletonPulse class="h-10 sm:h-12 w-64 sm:w-80 max-w-full mb-4 rounded-xl" />
      <!-- Summary -->
      <SkeletonPulse class="h-5 w-96 max-w-full mb-2" />
      <SkeletonPulse class="h-5 w-72 max-w-full mb-6" />
      <!-- Location -->
      <SkeletonPulse class="h-4 w-40" />
    </section>

    <!-- 2. PROJECTS SKELETON -->
    <section class="max-w-4xl mx-auto py-8 sm:py-12 px-4 sm:px-6">
      <div class="flex items-center gap-4 mb-8 sm:mb-12">
        <SkeletonPulse class="h-8 w-56" />
        <div class="h-px flex-1 bg-linear-to-r from-white/5 to-transparent" />
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="i in 2" :key="i" class="bg-white/5 border border-white/10 rounded-3xl overflow-hidden">
          <SkeletonPulse class="w-full h-44 rounded-none" />
          <div class="p-6 space-y-3">
            <SkeletonPulse class="h-6 w-48" />
            <SkeletonPulse class="h-4 w-full" />
            <SkeletonPulse class="h-4 w-3/4" />
            <SkeletonPulse class="h-3 w-32" />
          </div>
        </div>
      </div>
    </section>

    <!-- 3. EXPERIENCE SKELETON -->
    <section class="max-w-4xl mx-auto py-8 sm:py-12 px-4 sm:px-6">
      <div class="flex items-center gap-4 mb-8 sm:mb-12">
        <SkeletonPulse class="h-8 w-40" />
        <div class="h-px flex-1 bg-linear-to-r from-white/5 to-transparent" />
      </div>
      <div class="relative border-l border-slate-700/50 ml-3 space-y-12">
        <div v-for="i in 3" :key="i" class="relative pl-8">
          <div class="absolute -left-2.25 top-2 w-4 h-4 rounded-full bg-slate-800 border-2 border-slate-600" />
          <div class="bg-white/5 border border-white/10 p-6 rounded-2xl space-y-3">
            <div class="flex justify-between items-start">
              <div class="space-y-2">
                <SkeletonPulse class="h-6 w-44" />
                <SkeletonPulse class="h-4 w-28" />
              </div>
              <SkeletonPulse class="h-6 w-36 rounded-full" />
            </div>
            <SkeletonPulse class="h-4 w-full" />
            <SkeletonPulse class="h-4 w-5/6" />
          </div>
        </div>
      </div>
    </section>

    <!-- 4. SKILLS SKELETON -->
    <section class="max-w-4xl mx-auto py-8 sm:py-12 px-4 sm:px-6">
      <div class="flex items-center gap-4 mb-8 sm:mb-12">
        <SkeletonPulse class="h-8 w-28" />
        <div class="h-px flex-1 bg-linear-to-r from-white/5 to-transparent" />
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="i in 6" :key="i" class="bg-white/5 p-5 rounded-2xl border border-white/10 space-y-3">
          <SkeletonPulse class="h-3 w-20" />
          <SkeletonPulse class="h-5 w-32" />
          <SkeletonPulse class="h-1.5 w-full rounded-full" />
        </div>
      </div>
    </section>

    <!-- 5. EDUCATION SKELETON -->
    <section class="max-w-4xl mx-auto py-8 sm:py-12 px-4 sm:px-6">
      <div class="flex items-center gap-4 mb-8 sm:mb-12">
        <SkeletonPulse class="h-8 w-36" />
        <div class="h-px flex-1 bg-linear-to-r from-white/5 to-transparent" />
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="i in 2" :key="i" class="bg-white/5 border border-white/10 p-6 rounded-2xl">
          <div class="flex items-start gap-4">
            <SkeletonPulse class="w-12 h-12 rounded-lg shrink-0" />
            <div class="space-y-2 flex-1">
              <SkeletonPulse class="h-3 w-32" />
              <SkeletonPulse class="h-6 w-44" />
              <SkeletonPulse class="h-4 w-36" />
              <SkeletonPulse class="h-3 w-28" />
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
