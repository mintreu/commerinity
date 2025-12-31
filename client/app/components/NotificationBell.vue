<script setup lang="ts">
/**
 * Notification Bell Component
 *
 * Displays notification count badge and dropdown with recent notifications.
 * Integrates with useNotifications composable.
 */

const {
  unreadCount,
  notifications,
  isLoading,
  fetchNotifications,
  fetchUnreadCount,
  markAsRead,
  markAllAsRead
} = useNotifications()

const isOpen = ref(false)

// Fetch unread count on mount
onMounted(async () => {
  await fetchUnreadCount()
})

// Fetch notifications when dropdown opens
watch(isOpen, async (open) => {
  if (open && notifications.value.length === 0) {
    await fetchNotifications()
  }
})

// Format relative time
function formatTime(dateString: string): string {
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  return date.toLocaleDateString()
}

// Handle notification click
async function handleNotificationClick(notification: { id: string; read_at: string | null; data: { action_url?: string } }) {
  if (!notification.read_at) {
    await markAsRead(notification.id)
  }

  if (notification.data?.action_url) {
    navigateTo(notification.data.action_url)
  }

  isOpen.value = false
}

// Handle mark all as read
async function handleMarkAllAsRead() {
  await markAllAsRead()
}
</script>

<template>
  <UPopover v-model:open="isOpen" :ui="{ content: 'w-80 max-h-96 overflow-hidden' }">
    <UButton
      color="neutral"
      variant="ghost"
      icon="i-lucide-bell"
      class="relative"
    >
      <!-- Unread badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </UButton>

    <template #content>
      <div class="flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-default px-4 py-3">
          <h3 class="font-semibold text-highlighted">
            Notifications
          </h3>
          <UButton
            v-if="unreadCount > 0"
            color="primary"
            variant="link"
            size="xs"
            @click="handleMarkAllAsRead"
          >
            Mark all read
          </UButton>
        </div>

        <!-- Notifications list -->
        <div class="max-h-72 overflow-y-auto">
          <!-- Loading state -->
          <div v-if="isLoading" class="flex items-center justify-center py-8">
            <UIcon name="i-lucide-loader-2" class="h-6 w-6 animate-spin text-muted" />
          </div>

          <!-- Empty state -->
          <div
            v-else-if="notifications.length === 0"
            class="flex flex-col items-center justify-center py-8 text-center"
          >
            <UIcon name="i-lucide-bell-off" class="h-10 w-10 text-muted mb-2" />
            <p class="text-sm text-muted">
              No notifications yet
            </p>
          </div>

          <!-- Notification items -->
          <div v-else>
            <button
              v-for="notification in notifications"
              :key="notification.id"
              class="w-full px-4 py-3 text-left hover:bg-elevated transition-colors border-b border-default last:border-b-0"
              :class="{ 'bg-primary/5': !notification.read_at }"
              @click="handleNotificationClick(notification)"
            >
              <div class="flex gap-3">
                <!-- Icon -->
                <div class="shrink-0">
                  <UIcon
                    :name="notification.data?.icon ?? 'i-lucide-bell'"
                    class="h-5 w-5"
                    :class="notification.read_at ? 'text-muted' : 'text-primary'"
                  />
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <p
                    class="text-sm font-medium truncate"
                    :class="notification.read_at ? 'text-muted' : 'text-highlighted'"
                  >
                    {{ notification.data?.title ?? 'Notification' }}
                  </p>
                  <p
                    v-if="notification.data?.message || notification.data?.body"
                    class="text-xs text-muted truncate mt-0.5"
                  >
                    {{ notification.data?.message || notification.data?.body }}
                  </p>
                  <p class="text-xs text-muted/70 mt-1">
                    {{ formatTime(notification.created_at) }}
                  </p>
                </div>

                <!-- Unread indicator -->
                <div v-if="!notification.read_at" class="shrink-0">
                  <span class="block h-2 w-2 rounded-full bg-primary" />
                </div>
              </div>
            </button>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-default px-4 py-2">
          <UButton
            to="/notifications"
            color="primary"
            variant="link"
            size="sm"
            class="w-full justify-center"
            @click="isOpen = false"
          >
            View all notifications
          </UButton>
        </div>
      </div>
    </template>
  </UPopover>
</template>
