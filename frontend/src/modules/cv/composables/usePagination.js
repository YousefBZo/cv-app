import { ref, computed, toValue } from 'vue'

/**
 * Reusable pagination composable.
 * Extracts the show-more / show-less pattern used in CV.vue.
 *
 * @param {import('vue').Ref<Array> | (() => Array)} items - reactive ref or getter returning an array
 * @param {number} initialLimit - number of items shown initially
 */
export function usePagination(items, initialLimit = 5) {
  const limit = ref(initialLimit)

  const displayed = computed(() =>
    (toValue(items) || []).slice(0, limit.value)
  )

  const hasMore = computed(() =>
    (toValue(items)?.length || 0) > limit.value
  )

  const isExpanded = computed(() =>
    limit.value > initialLimit
  )

  function showAll() {
    limit.value = toValue(items)?.length || initialLimit
  }

  function showLess() {
    limit.value = initialLimit
  }

  return { limit, displayed, hasMore, isExpanded, showAll, showLess }
}
