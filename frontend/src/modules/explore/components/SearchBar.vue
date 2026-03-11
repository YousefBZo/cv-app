<script setup>
/**
 * SearchBar — Search, sort & filter controls for the profile directory.
 *
 * Features:
 *   - Debounced text search (300ms) — avoids API spam on every keystroke
 *   - Sort dropdown (6 options)
 *   - Location filter dropdown (populated from API)
 *   - Clear button to reset search text
 *   - Result count badge
 *   - Active filter indicators (badges showing what's applied)
 *   - "Clear all filters" button when any filter is active
 */
import { ref, watch, computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const emit = defineEmits(['search', 'sort', 'filterLocation'])
const props = defineProps({
  modelValue: { type: String, default: '' },
  total: { type: Number, default: 0 },
  sortBy: { type: String, default: 'newest' },
  locationFilter: { type: String, default: '' },
  locations: { type: Array, default: () => [] },
})

// ── Search ───────────────────────────────────────────────────
const query = ref(props.modelValue)
let debounceTimer = null

watch(query, (val) => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    emit('search', val)
  }, 300)
})

function clear() {
  query.value = ''
  emit('search', '')
}

// ── Sort ─────────────────────────────────────────────────────
const sortOptions = [
  { value: 'newest',          labelKey: 'explore.sortNewest' },
  { value: 'oldest',          labelKey: 'explore.sortOldest' },
  { value: 'name_asc',        labelKey: 'explore.sortNameAZ' },
  { value: 'name_desc',       labelKey: 'explore.sortNameZA' },
  { value: 'most_skills',     labelKey: 'explore.sortMostSkills' },
  { value: 'most_experience', labelKey: 'explore.sortMostExp' },
]

function onSortChange(e) {
  emit('sort', e.target.value)
}

// ── Location filter ──────────────────────────────────────────
function onLocationChange(e) {
  emit('filterLocation', e.target.value)
}

// ── Active filters indicator ─────────────────────────────────
const hasActiveFilters = computed(() => {
  return query.value.trim() !== '' || props.sortBy !== 'newest' || props.locationFilter !== ''
})

function clearAll() {
  query.value = ''
  emit('search', '')
  emit('sort', 'newest')
  emit('filterLocation', '')
}
</script>

<template>
  <div class="space-y-3 max-w-3xl mx-auto">
    <!-- Row 1: Search input -->
    <div class="relative flex items-center">
      <!-- Search icon -->
      <div class="absolute start-4 text-slate-500 pointer-events-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
      </div>

      <input
        v-model="query"
        type="text"
        :placeholder="t('explore.searchPlaceholder')"
        class="w-full py-3 ps-11 pe-20 rounded-xl text-sm text-white placeholder-slate-500 bg-white/5 border border-white/10 focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none backdrop-blur-sm"
      />

      <!-- Clear button -->
      <button
        v-if="query"
        @click="clear"
        class="absolute end-14 p-1 rounded-md text-slate-500 hover:text-white hover:bg-white/10 transition-colors"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>

      <!-- Result count badge -->
      <div v-if="total > 0" class="absolute end-4 text-[10px] font-medium text-slate-500">
        {{ total }}
      </div>
    </div>

    <!-- Row 2: Sort + Location filter + Clear all -->
    <div class="flex flex-wrap items-center gap-2">
      <!-- Sort dropdown -->
      <div class="relative">
        <select
          :value="sortBy"
          @change="onSortChange"
          class="appearance-none py-2 ps-3 pe-8 rounded-lg text-xs font-medium text-slate-300 bg-white/5 border border-white/10 hover:border-white/20 focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none backdrop-blur-sm cursor-pointer"
        >
          <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value" class="bg-slate-900 text-slate-300">
            {{ t(opt.labelKey) }}
          </option>
        </select>
        <!-- Chevron icon -->
        <div class="absolute end-2 top-1/2 -translate-y-1/2 pointer-events-none text-slate-500">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </div>
      </div>

      <!-- Location filter dropdown -->
      <div v-if="locations.length > 0" class="relative">
        <select
          :value="locationFilter"
          @change="onLocationChange"
          class="appearance-none py-2 ps-3 pe-8 rounded-lg text-xs font-medium text-slate-300 bg-white/5 border border-white/10 hover:border-white/20 focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none backdrop-blur-sm cursor-pointer"
        >
          <option value="" class="bg-slate-900 text-slate-300">📍 {{ t('explore.allLocations') }}</option>
          <option v-for="loc in locations" :key="loc" :value="loc" class="bg-slate-900 text-slate-300">
            {{ loc }}
          </option>
        </select>
        <!-- Chevron icon -->
        <div class="absolute end-2 top-1/2 -translate-y-1/2 pointer-events-none text-slate-500">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </div>
      </div>

      <!-- Spacer -->
      <div class="flex-1"></div>

      <!-- Clear all filters -->
      <button
        v-if="hasActiveFilters"
        @click="clearAll"
        class="flex items-center gap-1 px-3 py-2 rounded-lg text-[11px] font-medium text-red-400/70 hover:text-red-400 hover:bg-red-400/5 border border-transparent hover:border-red-400/20 transition-all"
      >
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        {{ t('explore.clearFilters') }}
      </button>
    </div>
  </div>
</template>
