<script setup lang="ts">
/**
 * Notifications Page
 *
 * Full page view of all user notifications with pagination,
 * filtering, and push notification management.
 */

definePageMeta({
  middleware: ['sanctum:auth'],
  layout: 'dashboard'
})

const {
  unreadCount,
  notifications,
  isLoading,
  isPushSupported,
  isPushSubscribed,
  fetchNotifications,
  fetchUnreadCount,
  markAsRead,
  markAllAsRead,
  deleteNotification,
  subscribeToPush,
  unsubscribeFromPush,
  checkPushSubscription,
  success
} = useNotifications()

// Pagination
const currentPage = ref(1)
const perPage = ref(20)
const totalPages = ref(1)

// Filter
const filter = ref<'all' | 'unread'>('all')

// Fetch notifications on mount
onMounted(async () => {
  await Promise.all([
    loadNotifications(),
    fetchUnreadCount(),
    checkPushSubscription()
  ])
})

// Load notifications with pagination
async function loadNotifications() {
  const result = await fetchNotifications(currentPage.value, perPage.value)
  if (result) {
    totalPages.value = result.last_page || 1
  }
}

// Filtered notifications
const filteredNotifications = computed(() => {
  if (filter.value === 'unread') {
    return notifications.value.filter(n => !n.read_at)
  }
  return notifications.value
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
  if (diffMins < 60) return `${diffMins} minutes ago`
  if (diffHours < 24) return `${diffHours} hours ago`
  if (diffDays < 7) return `${diffDays} days ago`
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
  })
}

// Handle notification click
async function handleNotificationClick(notification: { id: string, read_at: string | null, data: { action_url?: string } }) {
  if (!notification.read_at) {
    await markAsRead(notification.id)
  }

  if (notification.data?.action_url) {
    navigateTo(notification.data.action_url)
  }
}

// Handle delete
async function handleDelete(notificationId: string) {
  await deleteNotification(notificationId)
  success('Notification Deleted', 'The notification has been removed.')
}

// Handle mark all as read
async function handleMarkAllAsRead() {
  await markAllAsRead()
  success('All Read', 'All notifications have been marked as read.')
}

// Toggle push subscription
async function togglePushSubscription() {
  if (isPushSubscribed.value) {
    await unsubscribeFromPush()
  } else {
    await subscribeToPush()
  }
}

// Watch filter changes
watch(filter, () => {
  currentPage.value = 1
})

// Watch page changes
watch(currentPage, () => {
  loadNotifications()
})
</script>

<template>
  <div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-bold text-highlighted">
          Notifications
        </h1>
        <p class="text-muted mt-1">
          {{ unreadCount }} unread notification{{ unreadCount !== 1 ? 's' : '' }}
        </p>
      </div>

      <div class="flex items-center gap-3">
        <!-- Push notification toggle -->
        <ClientOnly>
          <UButton
            v-if="isPushSupported"
            :color="isPushSubscribed ? 'success' : 'neutral'"
            variant="soft"
            :icon="isPushSubscribed ? 'i-lucide-bell-ring' : 'i-lucide-bell-off'"
            @click="togglePushSubscription"
          >
            {{ isPushSubscribed ? 'Push On' : 'Push Off' }}
          </UButton>
        </ClientOnly>

        <!-- Mark all as read -->
        <UButton
          v-if="unreadCount > 0"
          color="primary"
          variant="soft"
          icon="i-lucide-check-check"
          @click="handleMarkAllAsRead"
        >
          Mark all read
        </UButton>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex items-center gap-2 mb-6">
      <UButton
        :color="filter === 'all' ? 'primary' : 'neutral'"
        :variant="filter === 'all' ? 'solid' : 'ghost'"
        size="sm"
        @click="filter = 'all'"
      >
        All
      </UButton>
      <UButton
        :color="filter === 'unread' ? 'primary' : 'neutral'"
        :variant="filter === 'unread' ? 'solid' : 'ghost'"
        size="sm"
        @click="filter = 'unread'"
      >
        Unread
        <UBadge
          v-if="unreadCount > 0"
          color="error"
          size="xs"
          class="ml-1"
        >
          {{ unreadCount }}
        </UBadge>
      </UButton>
    </div>

    <!-- Loading state -->
    <div
      v-if="isLoading"
      class="flex items-center justify-center py-16"
    >
      <UIcon
        name="i-lucide-loader-2"
        class="h-8 w-8 animate-spin text-primary"
      />
    </div>

    <!-- Empty state -->
    <UCard
      v-else-if="filteredNotifications.length === 0"
      class="text-center py-12"
    >
      <div class="flex flex-col items-center">
        <UIcon
          name="i-lucide-inbox"
          class="h-16 w-16 text-muted mb-4"
        />
        <h3 class="text-lg font-medium text-highlighted mb-2">
          No notifications
        </h3>
        <p class="text-muted">
          {{ filter === 'unread' ? 'You have no unread notifications.' : 'You have no notifications yet.' }}
        </p>
      </div>
    </UCard>

    <!-- Notifications list -->
    <div
      v-else
      class="space-y-3"
    >
      <UCard
        v-for="notification in filteredNotifications"
        :key="notification.id"
        class="transition-all duration-200 hover:shadow-md cursor-pointer"
        :class="{ 'ring-2 ring-primary/20 bg-primary/5': !notification.read_at }"
        @click="handleNotificationClick(notification)"
      >
        <div class="flex gap-4">
          <!-- Icon -->
          <div class="shrink-0">
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center"
              :class="notification.read_at ? 'bg-muted/20' : 'bg-primary/20'"
            >
              <UIcon
                :name="notification.data?.icon ?? 'i-lucide-bell'"
                class="h-5 w-5"
                :class="notification.read_at ? 'text-muted' : 'text-primary'"
              />
            </div>
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1 min-w-0">
                <p
                  class="font-medium"
                  :class="notification.read_at ? 'text-muted' : 'text-highlighted'"
                >
                  {{ notification.data?.title ?? 'Notification' }}
                </p>
                <p
                  v-if="notification.data?.message || notification.data?.body"
                  class="text-sm text-muted mt-1"
                >
                  {{ notification.data?.message || notification.data?.body }}
                </p>
                <p class="text-xs text-muted/70 mt-2">
                  {{ formatTime(notification.created_at) }}
                </p>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-2 shrink-0">
                <!-- Unread indicator -->
                <span
                  v-if="!notification.read_at"
                  class="block h-2 w-2 rounded-full bg-primary"
                />

                <!-- Delete button -->
                <UButton
                  color="neutral"
                  variant="ghost"
                  icon="i-lucide-trash-2"
                  size="xs"
                  @click.stop="handleDelete(notification.id)"
                />
              </div>
            </div>
          </div>
        </div>
      </UCard>
    </div>

    <!-- Pagination -->
    <div
      v-if="totalPages > 1"
      class="flex justify-center mt-8"
    >
      <UPagination
        v-model="currentPage"
        :total="totalPages * perPage"
        :items-per-page="perPage"
      />
    </div>
  </div>
</template>
