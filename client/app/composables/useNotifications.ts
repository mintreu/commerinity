/**
 * Notifications Composable
 *
 * Provides unified notification system including:
 * - Toast notifications (via Nuxt UI)
 * - Push notification subscription management
 * - Database notification fetching
 */

import type { ToastProps } from '@nuxt/ui'

// Notification from API
export interface ApiNotification {
  id: string
  type: string
  data: {
    title?: string
    message?: string
    body?: string
    action_url?: string
    icon?: string
    [key: string]: unknown
  }
  read_at: string | null
  created_at: string
}

// Push subscription keys
interface PushSubscriptionKeys {
  p256dh: string
  auth: string
}

export function useNotifications() {
  const toast = useToast()
  const config = useRuntimeConfig()
  const { $sanctumFetch } = useNuxtApp()

  // State
  const unreadCount = ref(0)
  const notifications = ref<ApiNotification[]>([])
  const isLoading = ref(false)
  const isPushSupported = ref(false)
  const isPushSubscribed = ref(false)

  // Check push support on client side
  onMounted(() => {
    isPushSupported.value = 'serviceWorker' in navigator && 'PushManager' in window
  })

  // ========================================
  // Toast Notifications (UI feedback)
  // ========================================

  /**
   * Show success toast
   */
  function success(title: string, description?: string) {
    toast.add({
      title,
      description,
      icon: 'i-lucide-check-circle',
      color: 'success'
    })
  }

  /**
   * Show error toast
   */
  function error(title: string, description?: string) {
    toast.add({
      title,
      description,
      icon: 'i-lucide-x-circle',
      color: 'error'
    })
  }

  /**
   * Show warning toast
   */
  function warning(title: string, description?: string) {
    toast.add({
      title,
      description,
      icon: 'i-lucide-alert-triangle',
      color: 'warning'
    })
  }

  /**
   * Show info toast
   */
  function info(title: string, description?: string) {
    toast.add({
      title,
      description,
      icon: 'i-lucide-info',
      color: 'info'
    })
  }

  /**
   * Show custom toast
   */
  function show(options: Partial<ToastProps> & { title: string }) {
    toast.add(options)
  }

  /**
   * Show toast with action button
   */
  function showWithAction(
    title: string,
    description: string,
    actionLabel: string,
    actionCallback: () => void,
    color: ToastProps['color'] = 'primary'
  ) {
    toast.add({
      title,
      description,
      color,
      actions: [
        {
          label: actionLabel,
          color: 'neutral',
          variant: 'outline',
          onClick: (e) => {
            e?.stopPropagation()
            actionCallback()
          }
        }
      ]
    })
  }

  // ========================================
  // API Notifications (Database)
  // ========================================

  /**
   * Fetch notifications from API
   */
  async function fetchNotifications(page = 1, perPage = 20) {
    isLoading.value = true
    try {
      const response = await $sanctumFetch(`${config.public.apiBase}/api/notifications`, {
        params: { page, per_page: perPage }
      })

      if (response.success) {
        notifications.value = response.data.data || []
        return response.data
      }
      return null
    } catch (err) {
      console.error('Failed to fetch notifications:', err)
      return null
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Fetch unread count
   */
  async function fetchUnreadCount() {
    try {
      const response = await $sanctumFetch(`${config.public.apiBase}/api/notifications/unread-count`)

      if (response.success) {
        unreadCount.value = response.data.unread_count
        return response.data.unread_count
      }
      return 0
    } catch (err) {
      console.error('Failed to fetch unread count:', err)
      return 0
    }
  }

  /**
   * Mark notification as read
   */
  async function markAsRead(notificationId: string) {
    try {
      const response = await $sanctumFetch(
        `${config.public.apiBase}/api/notifications/${notificationId}/read`,
        { method: 'POST' }
      )

      if (response.success) {
        // Update local state
        const notification = notifications.value.find(n => n.id === notificationId)
        if (notification) {
          notification.read_at = new Date().toISOString()
        }
        unreadCount.value = Math.max(0, unreadCount.value - 1)
        return true
      }
      return false
    } catch (err) {
      console.error('Failed to mark notification as read:', err)
      return false
    }
  }

  /**
   * Mark all notifications as read
   */
  async function markAllAsRead() {
    try {
      const response = await $sanctumFetch(
        `${config.public.apiBase}/api/notifications/read-all`,
        { method: 'POST' }
      )

      if (response.success) {
        // Update local state
        notifications.value.forEach((n) => {
          n.read_at = new Date().toISOString()
        })
        unreadCount.value = 0
        return true
      }
      return false
    } catch (err) {
      console.error('Failed to mark all as read:', err)
      return false
    }
  }

  /**
   * Delete a notification
   */
  async function deleteNotification(notificationId: string) {
    try {
      const response = await $sanctumFetch(
        `${config.public.apiBase}/api/notifications/${notificationId}`,
        { method: 'DELETE' }
      )

      if (response.success) {
        // Remove from local state
        const index = notifications.value.findIndex(n => n.id === notificationId)
        if (index !== -1) {
          const wasUnread = !notifications.value[index].read_at
          notifications.value.splice(index, 1)
          if (wasUnread) {
            unreadCount.value = Math.max(0, unreadCount.value - 1)
          }
        }
        return true
      }
      return false
    } catch (err) {
      console.error('Failed to delete notification:', err)
      return false
    }
  }

  // ========================================
  // Push Notifications (WebPush/VAPID)
  // ========================================

  /**
   * Get VAPID public key from server
   */
  async function getVapidPublicKey(): Promise<string | null> {
    try {
      const response = await $sanctumFetch(`${config.public.apiBase}/api/push/vapid-key`)

      if (response.success) {
        return response.public_key
      }
      return null
    } catch (err) {
      console.error('Failed to get VAPID key:', err)
      return null
    }
  }

  /**
   * Subscribe to push notifications
   */
  async function subscribeToPush(): Promise<boolean> {
    if (!isPushSupported.value) {
      warning('Push Not Supported', 'Your browser does not support push notifications.')
      return false
    }

    try {
      // Request notification permission
      const permission = await Notification.requestPermission()
      if (permission !== 'granted') {
        warning('Permission Denied', 'Push notifications were not allowed.')
        return false
      }

      // Get VAPID public key
      const vapidPublicKey = await getVapidPublicKey()
      if (!vapidPublicKey) {
        error('Configuration Error', 'Push notifications are not configured.')
        return false
      }

      // Register service worker
      const registration = await navigator.serviceWorker.register('/sw.js')
      await navigator.serviceWorker.ready

      // Subscribe to push
      const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
      })

      // Send subscription to server
      const keys = subscription.toJSON().keys as PushSubscriptionKeys

      const response = await $sanctumFetch(`${config.public.apiBase}/api/push/subscribe`, {
        method: 'POST',
        body: {
          endpoint: subscription.endpoint,
          keys: {
            p256dh: keys.p256dh,
            auth: keys.auth
          }
        }
      })

      if (response.success) {
        isPushSubscribed.value = true
        success('Push Enabled', 'You will now receive push notifications.')
        return true
      }

      return false
    } catch (err) {
      console.error('Failed to subscribe to push:', err)
      error('Subscription Failed', 'Could not enable push notifications.')
      return false
    }
  }

  /**
   * Unsubscribe from push notifications
   */
  async function unsubscribeFromPush(): Promise<boolean> {
    if (!isPushSupported.value) return false

    try {
      const registration = await navigator.serviceWorker.ready
      const subscription = await registration.pushManager.getSubscription()

      if (subscription) {
        // Unsubscribe locally
        await subscription.unsubscribe()

        // Remove from server
        await $sanctumFetch(`${config.public.apiBase}/api/push/unsubscribe`, {
          method: 'POST',
          body: {
            endpoint: subscription.endpoint
          }
        })
      }

      isPushSubscribed.value = false
      info('Push Disabled', 'You will no longer receive push notifications.')
      return true
    } catch (err) {
      console.error('Failed to unsubscribe from push:', err)
      return false
    }
  }

  /**
   * Check if currently subscribed to push
   */
  async function checkPushSubscription(): Promise<boolean> {
    if (!isPushSupported.value) return false

    try {
      const registration = await navigator.serviceWorker.ready
      const subscription = await registration.pushManager.getSubscription()
      isPushSubscribed.value = !!subscription
      return !!subscription
    } catch {
      return false
    }
  }

  // ========================================
  // Utility Functions
  // ========================================

  /**
   * Convert base64 string to Uint8Array (for VAPID key)
   */
  function urlBase64ToUint8Array(base64String: string): Uint8Array {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4)
    const base64 = (base64String + padding)
      .replace(/-/g, '+')
      .replace(/_/g, '/')

    const rawData = window.atob(base64)
    const outputArray = new Uint8Array(rawData.length)

    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i)
    }
    return outputArray
  }

  return {
    // State
    unreadCount: readonly(unreadCount),
    notifications: readonly(notifications),
    isLoading: readonly(isLoading),
    isPushSupported: readonly(isPushSupported),
    isPushSubscribed: readonly(isPushSubscribed),

    // Toast methods
    success,
    error,
    warning,
    info,
    show,
    showWithAction,

    // API notification methods
    fetchNotifications,
    fetchUnreadCount,
    markAsRead,
    markAllAsRead,
    deleteNotification,

    // Push notification methods
    subscribeToPush,
    unsubscribeFromPush,
    checkPushSubscription
  }
}
