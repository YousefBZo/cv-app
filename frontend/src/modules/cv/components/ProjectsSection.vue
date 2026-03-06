<script setup>
/**
 * ProjectsSection — grid of project cards.
 * Receives pagination data via props, emits 'edit' with the project item.
 */
defineProps({
  projects: { type: Array, default: () => [] },
  hasMore: { type: Boolean, default: false },
  isExpanded: { type: Boolean, default: false },
})

const emit = defineEmits(['edit', 'showAll', 'showLess'])
</script>

<template>
  <section class="max-w-4xl mx-auto py-8 sm:py-12 px-4 sm:px-6">
    <div class="flex items-center gap-4 mb-8 sm:mb-12">
      <h2 class="text-2xl sm:text-3xl font-bold tracking-tight">Featured Projects</h2>
      <div class="h-px flex-1 bg-linear-to-r from-blue-500/50 to-transparent"></div>
    </div>

    <div v-if="projects.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="project in projects" :key="project.id"
        @click="emit('edit', project)"
        class="group bg-white/5 border border-white/10 rounded-3xl overflow-hidden hover:bg-white/8 hover:border-blue-500/40 transition-all duration-500 cursor-pointer">
        <div v-if="project.cover" class="w-full h-32 sm:h-44 overflow-hidden">
          <img :src="project.cover" :alt="project.title"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
        </div>
        <div v-else class="w-full h-32 sm:h-44 bg-slate-800/50 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
          </svg>
        </div>
        <div class="p-4 sm:p-6">
          <div class="flex flex-wrap justify-between items-start gap-2 mb-3 sm:mb-4">
            <h3 class="text-lg sm:text-xl font-bold text-white group-hover:text-blue-400 transition-colors">{{ project.title }}</h3>
            <div class="flex gap-2" @click.stop>
              <a v-if="project.github_url" :href="project.github_url" target="_blank" class="text-slate-500 hover:text-white transition-colors" title="GitHub">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                </svg>
              </a>
              <a v-if="project.link" :href="project.link" target="_blank" class="text-slate-500 hover:text-white transition-colors" title="Live Demo">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
              </a>
            </div>
          </div>
          <p class="text-slate-400 text-sm leading-relaxed mb-4">{{ project.description }}</p>
          <div class="text-xs text-blue-400 font-semibold">
            {{ project.start_date?.substring(0, 10) ?? '' }}
            <span v-if="project.end_date"> — {{ project.end_date?.substring(0, 10) }}</span>
            <span v-else-if="project.start_date"> — Present</span>
          </div>
        </div>
      </div>
    </div>

    <p v-else class="text-center text-slate-600 text-sm py-8">
      No projects added yet. <router-link to="/project" class="text-blue-400 hover:underline">Add one →</router-link>
    </p>

    <div class="mt-8 flex justify-center gap-4">
      <button v-if="hasMore" @click="emit('showAll')" class="text-blue-400 font-bold uppercase text-xs tracking-widest hover:underline">+ More Projects</button>
      <button v-if="isExpanded" @click="emit('showLess')" class="text-slate-500 font-bold uppercase text-xs tracking-widest hover:underline">− Show Less</button>
    </div>
  </section>
</template>
