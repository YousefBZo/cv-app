<script setup>
/**
 * NotificationBell — Notification bell icon with dropdown panel.
 *
 * Shows unread count badge, opens a dropdown with notification list,
 * supports mark-as-read and mark-all-as-read.
 *
 * Placed in NavBar for authenticated users.
 */
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useNotificationStore } from '@/shared/stores/notification'
import { useAuthStore } from '@/modules/auth/stores/auth'

const { t } = useI18n()
const router = useRouter()
const notifStore = useNotificationStore()
const authStore = useAuthStore()

const open = ref(false)
const panelRef = ref(null)

// Start polling when component mounts (if authenticated)
onMounted(() => {
  if (authStore.isAuthenticated) {
    notifStore.startPolling()
    // Eagerly fetch the full notification list so the dropdown is pre-loaded
    notifStore.fetchNotifications()
  }
})

onUnmounted(() => {
  notifStore.stopPolling()
})

// Watch for auth changes
watch(() => authStore.isAuthenticated, (isAuth) => {
  if (isAuth) {
    notifStore.startPolling()
  } else {
    notifStore.$reset()
  }
})

function togglePanel() {
  open.value = !open.value
  if (open.value) {
    notifStore.fetchNotifications()
  }
}

function closePanel() {
  open.value = false
}

function handleClickOutside(e) {
  if (panelRef.value && !panelRef.value.contains(e.target)) {
    closePanel()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside, true)
})
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside, true)
})

function getNotificationIcon(type, data) {
  if (type === 'reaction') {
    const icons = { like: '👍', love: '❤️', celebrate: '🎉', insightful: '💡', curious: '🤔' }
    return icons[data?.reaction_type] || '👍'
  }
  if (type === 'profile_view') {
    return '👁️'
  }
  return '🔔'
}

function getNotificationText(notif) {
  if (notif.type === 'reaction') {
    const name = notif.data?.reactor_name || t('notifications.someone')
    const reactionKey = `notifications.reacted_${notif.data?.reaction_type || 'like'}`
    const reactionVerb = t(reactionKey, t('notifications.liked'))
    return t('notifications.reactionText', { name, reaction: reactionVerb })
  }
  if (notif.type === 'profile_view') {
    const name = notif.data?.viewer_name || t('notifications.someone')
    return t('notifications.viewText', { name })
  }
  return t('notifications.genericText')
}

function handleNotifClick(notif) {
  if (!notif.is_read) {
    notifStore.markAsRead(notif.id)
  }

  // Navigate to the reactor/viewer's profile
  const slug = notif.data?.reactor_slug || notif.data?.viewer_slug
  if (slug) {
    router.push({ name: 'PublicCV', params: { slug } })
    closePanel()
  }
}

function handleMarkAllRead() {
  notifStore.markAllAsRead()
}

function loadMore() {
  notifStore.fetchNotifications(true)
}
</script>

<template>
  <div ref="panelRef" class="relative">
    <!-- Bell Button -->
    <button
      @click="togglePanel"
      class="relative p-1.5 rounded-lg hover:bg-white/5 transition-colors text-slate-400 hover:text-white"
      :title="t('notifications.title')"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>

      <!-- Unread badge -->
      <Transition name="badge">
        <span v-if="notifStore.hasUnread"
          class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-4.5 h-4.5 px-1 rounded-full text-[10px] font-bold text-white bg-red-500 shadow-lg shadow-red-500/30 animate-pulse">
          {{ notifStore.unreadCount > 99 ? '99+' : notifStore.unreadCount }}
        </span>
      </Transition>
    </button>

    <!-- Dropdown Panel -->
    <Transition name="dropdown">
      <div v-if="open"
        class="fixed inset-x-3 top-14 sm:absolute sm:inset-x-auto sm:top-full sm:mt-2 w-auto sm:w-96 bg-slate-950/95 backdrop-blur-xl border border-white/10 rounded-2xl shadow-2xl overflow-hidden z-50"
        :class="$i18n.locale === 'ar' ? 'sm:left-0' : 'sm:right-0'">

        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-white/5">
          <h3 class="text-sm font-bold text-white">{{ t('notifications.title') }}</h3>
          <div class="flex items-center gap-3">
            <button v-if="notifStore.hasUnread"
              @click="handleMarkAllRead"
              class="text-[11px] text-blue-400 hover:text-blue-300 font-medium transition-colors">
              {{ t('notifications.markAllRead') }}
            </button>
            <!-- Close button (mobile) -->
            <button @click="closePanel" class="sm:hidden p-1 -m-1 rounded-lg hover:bg-white/5 text-slate-500 hover:text-white transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Notification List -->
        <div class="max-h-[60vh] sm:max-h-80 overflow-y-auto overscroll-contain">
          <!-- Loading -->
          <div v-if="notifStore.loading && !notifStore.notifications.length" class="p-6 text-center">
            <div class="inline-block w-5 h-5 border-2 border-blue-400/30 border-t-blue-400 rounded-full animate-spin"></div>
          </div>

          <!-- Empty state -->
          <div v-else-if="!notifStore.notifications.length" class="p-8 text-center">
            <div class="text-3xl mb-2">🔔</div>
            <p class="text-xs text-slate-500">{{ t('notifications.empty') }}</p>
          </div>

          <!-- Items -->
          <button
            v-for="notif in notifStore.notifications"
            :key="notif.id"
            @click="handleNotifClick(notif)"
            class="w-full flex items-start gap-3 px-4 py-3 text-start transition-colors hover:bg-white/5 active:bg-white/10"
            :class="{ 'bg-blue-500/5': !notif.is_read }"
          >
            <!-- Icon -->
            <div class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-lg"
              :class="notif.is_read ? 'bg-white/5' : 'bg-blue-500/10'">
              {{ getNotificationIcon(notif.type, notif.data) }}
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <p class="text-xs leading-relaxed"
                :class="notif.is_read ? 'text-slate-500' : 'text-slate-300'">
                {{ getNotificationText(notif) }}
              </p>
              <p class="text-[10px] mt-0.5"
                :class="notif.is_read ? 'text-slate-600' : 'text-blue-400/60'">
                {{ notif.time_ago }}
              </p>
            </div>

            <!-- Unread dot -->
            <div v-if="!notif.is_read" class="shrink-0 mt-2">
              <div class="w-2 h-2 rounded-full bg-blue-400"></div>
            </div>
          </button>
        </div>

        <!-- Load more -->
        <div v-if="notifStore.pagination.currentPage < notifStore.pagination.lastPage"
          class="border-t border-white/5 p-2">
          <button @click="loadMore"
            class="w-full py-2 text-xs text-blue-400 hover:text-blue-300 font-medium transition-colors rounded-lg hover:bg-white/5">
            {{ t('notifications.loadMore') }}
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.badge-enter-active { transition: all 0.3s ease; }
.badge-leave-active { transition: all 0.2s ease; }
.badge-enter-from { transform: scale(0); opacity: 0; }
.badge-leave-to { transform: scale(0); opacity: 0; }

.dropdown-enter-active { transition: all 0.2s ease-out; }
.dropdown-leave-active { transition: all 0.15s ease-in; }
.dropdown-enter-from { opacity: 0; transform: translateY(-8px) scale(0.95); }
.dropdown-leave-to { opacity: 0; transform: translateY(-8px) scale(0.95); }
</style>
