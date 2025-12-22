<template>
  <div class="relative">
    <!-- ✅ Notification Button -->
    <button
        ref="buttonRef"
        @click="togglePanel"
        :class="buttonClasses"
        class="relative w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
        :aria-expanded="isOpen"
        aria-label="Notifications"
    >
      <Icon name="mdi:bell-outline" class="w-5 h-5 transition-transform duration-300" :class="{ 'scale-110': isHovered }" />

      <!-- Notification Badge -->
      <Transition name="badge">
        <div
            v-if="unreadCount > 0"
            class="absolute -top-1 -right-1 min-w-5 h-5 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold rounded-full border-2 border-white dark:border-gray-900 flex items-center justify-center shadow-lg animate-pulse px-1"
        >
          {{ unreadCount > 99 ? '99+' : unreadCount }}
        </div>
      </Transition>
    </button>

    <!-- ✅ Overlay -->
    <Transition name="overlay">
      <div
          v-if="isOpen"
          class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60]"
          @click="closePanel"
      ></div>
    </Transition>

    <!-- ✅ Right Sidebar Notification Panel -->
    <Transition name="slide-right">
      <aside
          v-if="isOpen"
          ref="panelRef"
          class="fixed top-0 right-0 h-full w-96 max-w-[90vw] bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl shadow-2xl z-[70] overflow-hidden border-l border-gray-200/50 dark:border-gray-700/50"
          @click.stop
      >
        <!-- ✅ Panel Header -->
        <header class="sticky top-0 z-10 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl border-b border-gray-200/50 dark:border-gray-700/50 p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center shadow-lg">
                <Icon name="mdi:bell" class="w-5 h-5 text-white" />
              </div>
              <div>
                <h2 class="text-xl font-black text-gray-900 dark:text-white">Notifications</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ totalCount }} total, {{ unreadCount }} unread</p>
              </div>
            </div>

            <button
                @click="closePanel"
                class="w-10 h-10 bg-gray-100 dark:bg-gray-800 hover:bg-red-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:rotate-90"
                aria-label="Close notifications"
            >
              <Icon name="mdi:close" class="w-5 h-5" />
            </button>
          </div>

          <!-- ✅ Filter Tabs -->
          <div class="flex items-center gap-2 mb-4 overflow-x-auto scrollbar-thin">
            <button
                v-for="filter in filterTabs"
                :key="filter.key"
                @click="setFilter(filter.key)"
                :class="[
                  'px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 whitespace-nowrap flex items-center gap-1',
                  currentFilter === filter.key
                    ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white shadow-md'
                    : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
                ]"
            >
              <Icon :name="filter.icon" class="w-4 h-4" />
              {{ filter.label }}
              <span v-if="filter.count !== undefined" class="ml-1 text-xs opacity-75">({{ filter.count }})</span>
            </button>
          </div>

          <!-- ✅ Action Buttons -->
          <div class="flex items-center gap-2">
            <button
                v-if="unreadCount > 0"
                @click="markAllAsRead"
                :disabled="isLoading"
                class="flex-1 px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 flex items-center justify-center gap-2"
            >
              <Icon name="mdi:check-all" class="w-4 h-4" />
              Mark All Read
            </button>

            <button
                v-if="notifications.length > 0"
                @click="confirmClearAll"
                :disabled="isLoading"
                class="px-4 py-2 bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50"
                title="Clear All"
            >
              <Icon name="mdi:delete-sweep" class="w-4 h-4" />
            </button>

            <button
                @click="refreshNotifications"
                :disabled="isLoading"
                class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-xl transition-all duration-200 disabled:opacity-50"
                title="Refresh"
            >
              <Icon name="mdi:refresh" class="w-4 h-4" :class="{ 'animate-spin': isLoading }" />
            </button>
          </div>
        </header>

        <!-- ✅ Notifications List -->
        <main class="flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-transparent">
          <!-- Loading State -->
          <div v-if="isLoading && notifications.length === 0" class="flex flex-col items-center justify-center py-16">
            <div class="w-12 h-12 border-2 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
            <p class="text-gray-600 dark:text-gray-400">Loading notifications...</p>
          </div>

          <!-- Notifications -->
          <div v-else-if="notifications.length > 0" class="divide-y divide-gray-100 dark:divide-gray-800">
            <TransitionGroup name="notification" tag="div">
              <article
                  v-for="notification in notifications"
                  :key="notification.id"
                  class="group p-4 hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-all duration-200 cursor-pointer"
                  :class="{
                    'bg-blue-50/30 dark:bg-blue-950/20 border-l-4 border-l-blue-500': !notification.read_at,
                    'opacity-75': notification.read_at
                  }"
                  @click="handleNotificationClick(notification)"
              >
                <div class="flex gap-3">
                  <!-- ✅ Notification Icon -->
                  <div
                      class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center shadow-sm transition-transform duration-200 group-hover:scale-110"
                      :class="getNotificationIconClasses(notification.type)"
                  >
                    <Icon :name="getNotificationIcon(notification.type)" class="w-6 h-6" />
                  </div>

                  <!-- ✅ Notification Content -->
                  <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2 mb-2">
                      <h3 class="font-bold text-gray-900 dark:text-white text-sm line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">
                        {{ notification.title }}
                      </h3>

                      <!-- ✅ Notification Menu -->
                      <div class="relative flex-shrink-0">
                        <button
                            @click.stop="toggleNotificationMenu(notification.id)"
                            class="w-8 h-8 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200"
                            :aria-expanded="activeMenuId === notification.id"
                        >
                          <Icon name="mdi:dots-vertical" class="w-4 h-4" />
                        </button>

                        <!-- Action Menu -->
                        <Transition name="menu">
                          <div
                              v-if="activeMenuId === notification.id"
                              class="absolute right-0 top-10 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl ring-1 ring-gray-200 dark:ring-gray-700 z-10 overflow-hidden"
                              @click.stop
                          >
                            <button
                                v-if="!notification.read_at"
                                @click="markAsRead(notification.id)"
                                class="w-full px-4 py-3 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors duration-200"
                            >
                              <Icon name="mdi:check" class="w-4 h-4 text-green-600" />
                              Mark as Read
                            </button>

                            <button
                                v-if="notification.read_at"
                                @click="markAsUnread(notification.id)"
                                class="w-full px-4 py-3 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors duration-200"
                            >
                              <Icon name="mdi:email-mark-as-unread" class="w-4 h-4 text-blue-600" />
                              Mark as Unread
                            </button>

                            <button
                                v-if="notification.url"
                                @click="viewNotification(notification)"
                                class="w-full px-4 py-3 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors duration-200"
                            >
                              <Icon name="mdi:open-in-new" class="w-4 h-4 text-blue-600" />
                              View Details
                            </button>

                            <button
                                @click="deleteNotification(notification.id)"
                                class="w-full px-4 py-3 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-3 transition-colors duration-200"
                            >
                              <Icon name="mdi:delete" class="w-4 h-4" />
                              Delete
                            </button>
                          </div>
                        </Transition>
                      </div>
                    </div>

                    <!-- ✅ Notification Body -->
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-3">
                      {{ notification.message }}
                    </p>

                    <!-- ✅ Notification Meta -->
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-500">
                        <span class="flex items-center gap-1">
                          <Icon name="mdi:clock-outline" class="w-3 h-3" />
                          {{ formatTime(notification.created_at) }}
                        </span>

                        <span
                            class="px-2 py-0.5 rounded-full font-medium"
                            :class="getNotificationTypeClasses(notification.type)"
                        >
                          {{ formatNotificationType(notification.type) }}
                        </span>
                      </div>

                      <!-- Unread Indicator -->
                      <div v-if="!notification.read_at" class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                    </div>

                    <!-- Action Button -->
                    <button
                        v-if="notification.url && !notification.read_at"
                        @click.stop="viewNotification(notification)"
                        class="mt-3 px-3 py-1.5 bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white text-xs font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105"
                    >
                      View Details →
                    </button>
                  </div>
                </div>
              </article>
            </TransitionGroup>
          </div>

          <!-- ✅ Empty State -->
          <div v-else class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 rounded-3xl flex items-center justify-center mx-auto mb-4">
              <Icon name="mdi:bell-off-outline" class="w-10 h-10 text-gray-400" />
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ getEmptyStateTitle() }}</h3>
            <p class="text-gray-600 dark:text-gray-400 text-sm max-w-sm">{{ getEmptyStateMessage() }}</p>
          </div>

          <!-- ✅ Load More Button -->
          <div v-if="hasMoreNotifications && notifications.length > 0" class="p-4">
            <button
                @click="loadMoreNotifications"
                :disabled="isLoading"
                class="w-full px-4 py-3 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 hover:from-blue-100 hover:to-purple-100 dark:hover:from-blue-900/30 dark:hover:to-purple-900/30 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 font-semibold rounded-xl border border-gray-200 dark:border-gray-700 transition-all duration-200 disabled:opacity-50 flex items-center justify-center gap-2"
            >
              <Icon v-if="isLoading" name="mdi:loading" class="w-4 h-4 animate-spin" />
              <Icon v-else name="mdi:chevron-down" class="w-4 h-4" />
              {{ isLoading ? 'Loading...' : 'Load More' }}
            </button>
          </div>
        </main>
      </aside>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onUnmounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useRuntimeConfig, useSanctumFetch, useSanctum } from '#imports'

/* ——— Types ——— */
interface Notification {
  id: string
  type: 'info' | 'success' | 'warning' | 'error' | 'order' | 'payment' | 'system'
  title: string
  message: string
  url?: string
  read_at?: string | null
  created_at: string
  level: string
  record_type?: string
  record_id?: string
}

interface NotificationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  has_more: boolean
  unread_count: number
}

/* ——— Composables ——— */
const config = useRuntimeConfig()
const router = useRouter()
const toast = useToast()
const { isLoggedIn } = useSanctum()

/* ——— State ——— */
const isOpen = ref(false)
const isLoading = ref(false)
const isHovered = ref(false)
const notifications = ref<Notification[]>([])
const activeMenuId = ref<string | null>(null)
const currentFilter = ref('all')
const currentPage = ref(1)
const meta = ref<NotificationMeta>({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  has_more: false,
  unread_count: 0
})

/* ——— Refs ——— */
const buttonRef = ref<HTMLElement>()
const panelRef = ref<HTMLElement>()

/* ——— Computed ——— */
const unreadCount = computed(() => meta.value.unread_count)
const totalCount = computed(() => meta.value.total)
const hasMoreNotifications = computed(() => meta.value.has_more)

const buttonClasses = computed(() => ({
  'hover:bg-gradient-to-r hover:from-orange-500 hover:to-red-500 text-gray-600 dark:text-gray-400 hover:text-white': !isOpen.value,
  'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg': isOpen.value
}))

// ✅ OPTIMIZED: Static filter tabs
const filterTabs = computed(() => [
  { key: 'all', label: 'All', icon: 'mdi:bell', count: totalCount.value },
  { key: 'unread', label: 'Unread', icon: 'mdi:bell-badge', count: unreadCount.value },
  { key: 'today', label: 'Today', icon: 'mdi:today' },
  { key: 'week', label: 'This Week', icon: 'mdi:calendar-week' }
])

/* ——— ✅ OPTIMIZED: Memoized Helper Functions ——— */
const iconMap: Record<string, string> = {
  info: 'mdi:information',
  success: 'mdi:check-circle',
  warning: 'mdi:alert',
  error: 'mdi:alert-circle',
  order: 'mdi:shopping',
  payment: 'mdi:credit-card',
  system: 'mdi:cog'
}

const iconClassMap: Record<string, string> = {
  info: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
  success: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
  warning: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
  error: 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
  order: 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
  payment: 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400',
  system: 'bg-gray-100 dark:bg-gray-900/30 text-gray-600 dark:text-gray-400'
}

const typeClassMap: Record<string, string> = {
  info: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
  success: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
  warning: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
  error: 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
  order: 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300',
  payment: 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300',
  system: 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300'
}

const typeLabelMap: Record<string, string> = {
  info: 'Info',
  success: 'Success',
  warning: 'Warning',
  error: 'Error',
  order: 'Order',
  payment: 'Payment',
  system: 'System'
}

function getNotificationIcon(type: string): string {
  return iconMap[type] || 'mdi:bell'
}

function getNotificationIconClasses(type: string): string {
  return iconClassMap[type] || iconClassMap.info
}

function getNotificationTypeClasses(type: string): string {
  return typeClassMap[type] || typeClassMap.info
}

function formatNotificationType(type: string): string {
  return typeLabelMap[type] || 'Info'
}

function formatTime(dateString: string): string {
  const now = new Date()
  const date = new Date(dateString)
  const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60))

  if (diffInMinutes < 1) return 'Just now'
  if (diffInMinutes < 60) return `${diffInMinutes}m ago`
  if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`
  if (diffInMinutes < 10080) return `${Math.floor(diffInMinutes / 1440)}d ago`

  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
  })
}

function getEmptyStateTitle(): string {
  const titleMap: Record<string, string> = {
    all: 'No notifications',
    unread: 'No unread notifications',
    today: 'No notifications today',
    week: 'No notifications this week'
  }
  return titleMap[currentFilter.value] || 'No notifications'
}

function getEmptyStateMessage(): string {
  const messageMap: Record<string, string> = {
    all: 'You\'re all caught up! New notifications will appear here.',
    unread: 'All notifications have been read.',
    today: 'No notifications received today.',
    week: 'No notifications received this week.'
  }
  return messageMap[currentFilter.value] || 'Check back later for updates.'
}

/* ——— Panel Management ——— */
function togglePanel() {
  if (isOpen.value) {
    closePanel()
  } else {
    openPanel()
  }
}

async function openPanel() {
  if (!isLoggedIn.value) {
    toast.warning('Login Required', 'Please login to view notifications')
    router.push('/auth/login')
    return
  }

  isOpen.value = true
  await fetchNotifications(true)

  nextTick(() => {
    document.addEventListener('click', handleClickOutside)
    document.addEventListener('keydown', handleEscapeKey)
  })
}

function closePanel() {
  isOpen.value = false
  activeMenuId.value = null
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleEscapeKey)
}

function handleClickOutside(event: MouseEvent) {
  if (panelRef.value && !panelRef.value.contains(event.target as Node) &&
      buttonRef.value && !buttonRef.value.contains(event.target as Node)) {
    closePanel()
  }
}

function handleEscapeKey(event: KeyboardEvent) {
  if (event.key === 'Escape') {
    closePanel()
  }
}

/* ——— API Functions ——— */
const fetchNotifications = async (reset = false) => {
  if (!isLoggedIn.value) return

  try {
    isLoading.value = true

    if (reset) {
      currentPage.value = 1
      notifications.value = []
    }

    const params = new URLSearchParams({
      page: currentPage.value.toString(),
      per_page: '15',
      include_read: 'true',
      filter: currentFilter.value
    })

    const res = await useSanctumFetch(`${config.public.apiBase}/account/notifications?${params}`, {
      method: 'GET'
    })

    if (res?.data) {
      if (reset) {
        notifications.value = res.data
      } else {
        notifications.value.push(...res.data)
      }

      if (res.meta) {
        meta.value = {
          current_page: res.meta.current_page || 1,
          last_page: res.meta.last_page || 1,
          per_page: res.meta.per_page || 15,
          total: res.meta.total || 0,
          has_more: res.meta.current_page < res.meta.last_page,
          unread_count: res.meta.unread_count || 0
        }
      } else {
        meta.value.total = res.data.length
        meta.value.unread_count = res.data.filter((n: Notification) => !n.read_at).length
      }
    }
  } catch (error: any) {
    console.error('[✘] Failed to fetch notifications', error)

    if (error?.statusCode !== 401) {
      toast.error({ title: 'Error', message: 'Failed to load notifications' })
    }
  } finally {
    isLoading.value = false
  }
}

const loadMoreNotifications = async () => {
  if (!hasMoreNotifications.value || isLoading.value || !isLoggedIn.value) return

  currentPage.value += 1
  await fetchNotifications(false)
}

const refreshNotifications = async () => {
  if (!isLoggedIn.value) return

  await fetchNotifications(true)
  toast.success({ title: 'Refreshed', message: 'Notifications updated' })
}

const markAsRead = async (notificationId: string) => {
  if (!isLoggedIn.value) return

  try {
    const res = await useSanctumFetch(`/api/account/notifications/${notificationId}/read`, {
      method: 'POST'
    })

    if (res?.success) {
      const notification = notifications.value.find(n => n.id === notificationId)
      if (notification) {
        notification.read_at = new Date().toISOString()
        meta.value.unread_count = Math.max(0, meta.value.unread_count - 1)
      }

      activeMenuId.value = null
      toast.success({ title: 'Success', message: 'Notification marked as read' })
    }
  } catch (error) {
    console.error('[✘] Failed to mark notification as read', error)
    toast.error({ title: 'Error', message: 'Failed to mark notification as read' })
  }
}

const markAsUnread = async (notificationId: string) => {
  if (!isLoggedIn.value) return

  try {
    const res = await useSanctumFetch(`/api/account/notifications/${notificationId}/unread`, {
      method: 'POST'
    })

    if (res?.success) {
      const notification = notifications.value.find(n => n.id === notificationId)
      if (notification) {
        notification.read_at = null
        meta.value.unread_count += 1
      }

      activeMenuId.value = null
      toast.success({ title: 'Success', message: 'Notification marked as unread' })
    }
  } catch (error) {
    console.error('[✘] Failed to mark notification as unread', error)
    toast.error({ title: 'Error', message: 'Failed to mark notification as unread' })
  }
}

const markAllAsRead = async () => {
  if (!isLoggedIn.value) return

  try {
    isLoading.value = true
    const res = await useSanctumFetch(`/api/account/notifications/mark-all-read`, {
      method: 'POST'
    })

    if (res?.success) {
      notifications.value.forEach(notification => {
        if (!notification.read_at) {
          notification.read_at = new Date().toISOString()
        }
      })

      meta.value.unread_count = 0
      toast.success({ title: 'Success', message: 'All notifications marked as read' })
    }
  } catch (error) {
    console.error('[✘] Failed to mark all notifications as read', error)
    toast.error({ title: 'Error', message: 'Failed to mark all notifications as read' })
  } finally {
    isLoading.value = false
  }
}

const deleteNotification = async (notificationId: string) => {
  if (!isLoggedIn.value) return

  try {
    const res = await useSanctumFetch(`/api/account/notifications/${notificationId}`, {
      method: 'DELETE'
    })

    if (res?.success) {
      const notificationIndex = notifications.value.findIndex(n => n.id === notificationId)
      if (notificationIndex !== -1) {
        const notification = notifications.value[notificationIndex]
        notifications.value.splice(notificationIndex, 1)

        meta.value.total -= 1
        if (!notification.read_at) {
          meta.value.unread_count = Math.max(0, meta.value.unread_count - 1)
        }
      }

      activeMenuId.value = null
      toast.success({ title: 'Success', message: 'Notification deleted' })
    }
  } catch (error) {
    console.error('[✘] Failed to delete notification', error)
    toast.error({ title: 'Error', message: 'Failed to delete notification' })
  }
}

const confirmClearAll = async () => {
  if (!isLoggedIn.value) return

  if (!confirm('Are you sure you want to clear all notifications? This action cannot be undone.')) {
    return
  }

  try {
    isLoading.value = true
    const res = await useSanctumFetch(`/api/account/notifications/clear-all`, {
      method: 'DELETE'
    })

    if (res?.success) {
      notifications.value = []
      meta.value.total = 0
      meta.value.unread_count = 0

      toast.success({ title: 'Success', message: 'All notifications cleared' })
    }
  } catch (error) {
    console.error('[✘] Failed to clear all notifications', error)
    toast.error({ title: 'Error', message: 'Failed to clear all notifications' })
  } finally {
    isLoading.value = false
  }
}

/* ——— Notification Actions ——— */
function handleNotificationClick(notification: Notification) {
  if (notification.url) {
    viewNotification(notification)
  } else if (!notification.read_at) {
    markAsRead(notification.id)
  }
}

function viewNotification(notification: Notification) {
  if (notification.url) {
    if (!notification.read_at) {
      markAsRead(notification.id)
    }

    closePanel()

    if (notification.url.startsWith('http')) {
      window.open(notification.url, '_blank')
    } else {
      router.push(notification.url)
    }
  }
}

function toggleNotificationMenu(notificationId: string) {
  activeMenuId.value = activeMenuId.value === notificationId ? null : notificationId
}

/* ——— Filter Management ——— */
function setFilter(filter: string) {
  if (currentFilter.value !== filter) {
    currentFilter.value = filter
    fetchNotifications(true)
  }
}

/* ——— Lifecycle ——— */
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleEscapeKey)
})

// Watch for login status changes
watch(isLoggedIn, (newValue) => {
  if (!newValue) {
    notifications.value = []
    meta.value.unread_count = 0
    closePanel()
  }
})
</script>

<style scoped>
/* ✅ Custom Scrollbar */
.scrollbar-thin {
  scrollbar-width: thin;
}

.scrollbar-thumb-gray-300 {
  scrollbar-color: rgb(209 213 219) transparent;
}

.dark .scrollbar-thumb-gray-600 {
  scrollbar-color: rgb(75 85 99) transparent;
}

.scrollbar-track-transparent {
  scrollbar-color: transparent transparent;
}

::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  @apply bg-gray-300 dark:bg-gray-600 rounded-full;
}

::-webkit-scrollbar-thumb:hover {
  @apply bg-gray-400 dark:bg-gray-500;
}

/* ✅ Transitions */
.overlay-enter-active,
.overlay-leave-active {
  transition: opacity 0.3s ease;
}

.overlay-enter-from,
.overlay-leave-to {
  opacity: 0;
}

.slide-right-enter-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-right-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 1, 1);
}

.slide-right-enter-from,
.slide-right-leave-to {
  transform: translateX(100%);
}

.badge-enter-active,
.badge-leave-active {
  transition: all 0.3s ease;
}

.badge-enter-from,
.badge-leave-to {
  opacity: 0;
  transform: scale(0);
}

.notification-enter-active {
  transition: all 0.3s ease;
}

.notification-leave-active {
  transition: all 0.2s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(20px);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

.notification-move {
  transition: transform 0.3s ease;
}

.menu-enter-active {
  transition: all 0.2s ease;
}

.menu-leave-active {
  transition: all 0.15s ease;
}

.menu-enter-from,
.menu-leave-to {
  opacity: 0;
  transform: translateY(-5px) scale(0.95);
}

/* ✅ Line clamp utilities */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* ✅ Focus states */
button:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* ✅ Mobile optimizations */
@media (max-width: 768px) {
  .notification-panel {
    width: 100vw;
    max-width: none;
  }
}

/* ✅ Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  *,
  ::before,
  ::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
