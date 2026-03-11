import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import http from '@/api/http'

/**
 * Notification Store — Manages in-app notifications with polling.
 *
 * DESIGN:
 *   - Polls for unread count every 30 seconds when authenticated.
 *   - Full notification list is fetched on-demand (when dropdown opens).
 *   - Supports mark-as-read (single + all) with optimistic UI updates.
 *   - Polling is started/stopped via startPolling()/stopPolling() — called
 *     from the NavBar on mount/unmount.
 */
export const useNotificationStore = defineStore('notifications', () => {
  // ── State ──────────────────────────────────────────────────
  const notifications = ref([])
  const unreadCount = ref(0)
  const loading = ref(false)
  const pagination = ref({ currentPage: 1, lastPage: 1, total: 0 })

  let pollInterval = null

  // ── Computed ───────────────────────────────────────────────
  const hasUnread = computed(() => unreadCount.value > 0)

  // ── Actions ────────────────────────────────────────────────

  /**
   * Fetch unread count (lightweight — just a number for the badge).
   */
  async function fetchUnreadCount() {
    try {
      const { data } = await http.get('/notifications/unread-count')
      unreadCount.value = data.data.count
    } catch {
      // Silent fail — polling shouldn't show errors
    }
  }

  /**
   * Fetch full notification list (paginated).
   */
  async function fetchNotifications(append = false) {
    loading.value = true
    try {
      const page = append ? pagination.value.currentPage + 1 : 1
      const { data } = await http.get('/notifications', { params: { page, per_page: 20 } })

      if (append) {
        notifications.value = [...notifications.value, ...data.data]
      } else {
        notifications.value = data.data
      }

      pagination.value = {
        currentPage: data.pagination.current_page,
        lastPage: data.pagination.last_page,
        total: data.pagination.total,
      }
    } catch (err) {
      console.error('Failed to fetch notifications:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Mark a single notification as read (optimistic update).
   */
  async function markAsRead(id) {
    // Optimistic: update UI immediately
    const notif = notifications.value.find(n => n.id === id)
    if (notif && !notif.is_read) {
      notif.is_read = true
      notif.read_at = new Date().toISOString()
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    }

    try {
      await http.put(`/notifications/${id}/read`)
    } catch {
      // Revert on failure
      if (notif) {
        notif.is_read = false
        notif.read_at = null
        unreadCount.value += 1
      }
    }
  }

  /**
   * Mark all notifications as read.
   */
  async function markAllAsRead() {
    const previousUnread = unreadCount.value

    // Optimistic
    notifications.value.forEach(n => {
      n.is_read = true
      n.read_at = n.read_at || new Date().toISOString()
    })
    unreadCount.value = 0

    try {
      await http.put('/notifications/read-all')
    } catch {
      // Revert
      unreadCount.value = previousUnread
      await fetchNotifications()
    }
  }

  /**
   * Start polling for unread count every 15 seconds.
   * Also immediately fetches the count on start.
   */
  function startPolling() {
    stopPolling()
    fetchUnreadCount()
    pollInterval = setInterval(fetchUnreadCount, 15000)
  }

  /**
   * Stop polling.
   */
  function stopPolling() {
    if (pollInterval) {
      clearInterval(pollInterval)
      pollInterval = null
    }
  }

  /**
   * Reset state (on logout).
   */
  function $reset() {
    stopPolling()
    notifications.value = []
    unreadCount.value = 0
    loading.value = false
    pagination.value = { currentPage: 1, lastPage: 1, total: 0 }
  }

  return {
    notifications,
    unreadCount,
    loading,
    pagination,
    hasUnread,
    fetchUnreadCount,
    fetchNotifications,
    markAsRead,
    markAllAsRead,
    startPolling,
    stopPolling,
    $reset,
  }
})
