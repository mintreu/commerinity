<template>
  <transition name="backdrop-fade">
    <div v-if="visible" class="fixed inset-0 z-[9999] bg-black/40 backdrop-blur-md" @click="closeSplash" />
  </transition>

  <transition name="slide-in">
    <div
        v-if="visible"
        class="fixed z-[10000] bottom-6 left-6 right-6 sm:left-6 sm:right-auto w-full max-w-md"
        @keydown.esc="closeSplash"
        tabindex="0"
        ref="splash"
    >
      <div class="notification-card bg-white dark:bg-gray-900 rounded-2xl shadow-premium overflow-hidden relative">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5 pointer-events-none" />
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600" />

        <div class="relative p-8">
          <button
              @click="closeSplash"
              aria-label="Close notification"
              class="absolute top-4 right-4 w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-red-500 hover:text-white transition-all duration-200 hover:rotate-180"
          >
            <Icon name="mdi:close" class="w-5 h-5" />
          </button>

          <div class="relative w-16 h-16 mx-auto mb-5">
            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-blue-600 to-purple-600 opacity-10 animate-pulse-soft" />
            <div class="relative w-full h-full rounded-2xl bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 flex items-center justify-center shadow-lg">
              <Icon name="mdi:bell-ring" class="w-8 h-8 text-white" />
            </div>
          </div>

          <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 text-center">
            Enable Push Notifications 🔔
          </h2>
          <p class="text-gray-600 dark:text-gray-300 text-sm mb-6 leading-relaxed text-center">
            Never miss important updates, offers, and product launches. Stay connected!
          </p>

          <div class="space-y-3">
            <button
                @click="enablePushNotifications"
                :disabled="submitting"
                class="w-full px-6 py-3.5 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95"
            >
              <span class="flex items-center justify-center gap-2">
                <Icon v-if="!submitting" name="mdi:bell-check" class="w-5 h-5" />
                <Icon v-else name="mdi:loading" class="w-5 h-5 animate-spin" />
                {{ submitting ? 'Enabling...' : 'Enable Notifications' }}
              </span>
            </button>
            <button
                @click="closeSplash"
                class="w-full px-6 py-3 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-all"
            >
              Maybe Later
            </button>
          </div>

          <transition name="message-fade">
            <div v-if="message" class="mt-4 p-3 rounded-xl flex items-center gap-3" :class="messageClass">
              <Icon :name="isError ? 'mdi:alert-circle' : 'mdi:check-circle'" class="w-5 h-5 shrink-0" />
              <p class="font-medium text-sm">{{ message }}</p>
            </div>
          </transition>

          <div class="mt-5 pt-4 border-t border-gray-200 dark:border-gray-800">
            <p class="flex items-center justify-center gap-2 text-xs text-gray-500">
              <Icon name="mdi:shield-check" class="w-4 h-4 text-green-600" />
              <span>Your privacy is protected. Unsubscribe anytime.</span>
            </p>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRuntimeConfig } from '#imports'
import { useSanctum } from '#imports'

const config = useRuntimeConfig()
const {user} = useSanctum()

const visible = ref(false)
const message = ref('')
const isError = ref(false)
const submitting = ref(false)
const splash = ref<HTMLElement | null>(null)

const messageClass = computed(() =>
    isError.value
        ? 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800'
        : 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-800'
)

const checkShouldShow = () => {
  if (!process.client) return false
  const hasEnabled = localStorage.getItem('pushNotificationsEnabled')
  const hasDismissed = localStorage.getItem('pushNotificationDismissed')
  const lastShown = localStorage.getItem('pushNotificationLastShown')

  if (hasEnabled === 'true') return false
  if (hasDismissed === 'true' && lastShown) {
    const daysSince = (Date.now() - parseInt(lastShown)) / (1000 * 60 * 60 * 24)
    if (daysSince < 7) return false
  }
  return true
}

const closeSplash = () => {
  visible.value = false
  if (process.client) {
    localStorage.setItem('pushNotificationDismissed', 'true')
    localStorage.setItem('pushNotificationLastShown', Date.now().toString())
  }
}

const registerServiceWorker = async () => {
  if (!('serviceWorker' in navigator)) return null

  try {
    const registration = await navigator.serviceWorker.register('/sw.js', { scope: '/' })
    console.log('✅ Service Worker registered')
    return registration
  } catch (error) {
    console.error('❌ Service Worker registration failed:', error)
    return null
  }
}

const enablePushNotifications = async () => {
  if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
    message.value = '❌ Push notifications not supported in this browser.'
    isError.value = true
    return
  }

  submitting.value = true
  message.value = ''
  isError.value = false

  try {
    const permission = await Notification.requestPermission()
    if (permission !== 'granted') {
      message.value = '❌ Notification permission denied.'
      isError.value = true
      submitting.value = false
      return
    }

    const registration = await navigator.serviceWorker.ready
    const subscription = await registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(config.public.vapidPublicKey)
    })

    const res: any = await $fetch(`${config.public.apiBase}/push/subscribe`, {
      method: 'POST',
      body: {
        subscription: subscription.toJSON(),
        email: user.value?.email || null
      },
      headers: { 'Content-Type': 'application/json' }
    })

    if (res.status) {
      message.value = '✅ Push notifications enabled successfully!'
      isError.value = false

      if (process.client) {
        localStorage.setItem('pushNotificationsEnabled', 'true')
        localStorage.removeItem('pushNotificationDismissed')
      }

      setTimeout(() => visible.value = false, 2500)
    }

  } catch (error: any) {
    console.error('Push subscription error:', error)
    message.value = error?.data?.message || '❌ Failed to enable notifications.'
    isError.value = true
  } finally {
    submitting.value = false
  }
}

const urlBase64ToUint8Array = (base64String: string) => {
  const padding = '='.repeat((4 - base64String.length % 4) % 4)
  const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/')
  const rawData = window.atob(base64)
  const outputArray = new Uint8Array(rawData.length)
  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i)
  }
  return outputArray
}

onMounted(() => {
  if (!process.client) return

  registerServiceWorker()

  setTimeout(() => {
    if (checkShouldShow()) {
      visible.value = true
      setTimeout(() => splash.value?.focus(), 300)
    }
  }, 5000)
})
</script>

<style scoped>
.backdrop-fade-enter-active, .backdrop-fade-leave-active { transition: opacity 0.3s ease; }
.backdrop-fade-enter-from, .backdrop-fade-leave-to { opacity: 0; }
.slide-in-enter-active { animation: slide-in 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); }
.slide-in-leave-active { animation: slide-out 0.3s ease-out; }
@keyframes slide-in {
  from { transform: translateY(100%) translateX(-20%); opacity: 0; }
  to { transform: translateY(0) translateX(0); opacity: 1; }
}
@keyframes slide-out {
  to { transform: translateY(20%) translateX(-10%) scale(0.95); opacity: 0; }
}
.message-fade-enter-active, .message-fade-leave-active { transition: all 0.3s ease; }
.message-fade-enter-from, .message-fade-leave-to { opacity: 0; transform: translateY(-10px); }
@keyframes pulse-soft {
  0%, 100% { transform: scale(1); opacity: 0.1; }
  50% { transform: scale(1.05); opacity: 0.15; }
}
.animate-pulse-soft { animation: pulse-soft 3s ease-in-out infinite; }
.shadow-premium {
  box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.25), 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}
.dark .shadow-premium {
  box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.5), 0 10px 25px -5px rgba(0, 0, 0, 0.3);
}
@media (max-width: 640px) {
  @keyframes slide-in {
    from { transform: translateY(100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }
}
</style>
