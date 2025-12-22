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

          <!-- Guest Email Input -->
          <div v-if="!user && !hasEnabledNotifications" class="mb-6">
            <input
                v-model="guestEmail"
                type="email"
                placeholder="Enter your email"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                :disabled="submitting"
            />
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
              We'll send notifications to this email
            </p>
          </div>

          <div class="space-y-3">
            <button
                @click="enablePushNotifications"
                :disabled="submitting || (!user && !isValidEmail(guestEmail))"
                class="w-full px-6 py-3.5 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 disabled:from-gray-400 disabled:to-gray-400 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95"
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
import { useRuntimeConfig, useSanctumFetch } from '#imports'
import { useSanctum } from '#imports'

const config = useRuntimeConfig()
const { user } = useSanctum()

const visible = ref(false)
const message = ref('')
const isError = ref(false)
const submitting = ref(false)
const splash = ref<HTMLElement | null>(null)
const swReady = ref(false)
const guestEmail = ref('')
const hasEnabledNotifications = ref(false)

const messageClass = computed(() =>
    isError.value
        ? 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800'
        : 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-800'
)

// ✅ Email validation
const isValidEmail = (email: string): boolean => {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return re.test(email)
}

const checkShouldShow = () => {
  if (!process.client) return false

  const hasSettings = localStorage.getItem('pushNotificationDismissed') ||
      localStorage.getItem('pushNotificationsEnabled')

  if (!hasSettings) {
    console.log('✅ First time - show splash')
    return true
  }

  const hasEnabled = localStorage.getItem('pushNotificationsEnabled')
  const hasDismissed = localStorage.getItem('pushNotificationDismissed')
  const lastShown = localStorage.getItem('pushNotificationLastShown')

  if (hasEnabled === 'true') {
    console.log('✅ Already enabled - skip')
    return false
  }

  if (hasDismissed === 'true' && lastShown) {
    const daysSince = (Date.now() - parseInt(lastShown)) / (1000 * 60 * 60 * 24)
    if (daysSince < 7) {
      console.log(`⏳ Dismissed ${daysSince.toFixed(1)} days ago - skip`)
      return false
    }
    console.log(`✅ 7+ days passed - show splash again`)
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
  if (!('serviceWorker' in navigator)) {
    console.warn('⚠️ Service Worker not supported')
    return null
  }

  try {
    console.log('📝 Registering Service Worker...')
    const registration = await navigator.serviceWorker.register('/sw.js', { scope: '/' })
    console.log('✅ Service Worker registered')

    return new Promise((resolve) => {
      if (registration.active) {
        console.log('✅ Service Worker already active')
        swReady.value = true
        resolve(registration)
        return
      }

      const listener = () => {
        if (registration.active) {
          console.log('✅ Service Worker activated (updatefound)')
          registration.removeEventListener('updatefound', listener)
          swReady.value = true
          resolve(registration)
        }
      }

      registration.addEventListener('updatefound', listener)
      const installingWorker = registration.installing

      if (installingWorker) {
        installingWorker.addEventListener('statechange', () => {
          if (installingWorker.state === 'activated') {
            console.log('✅ Service Worker state: activated')
            swReady.value = true
            resolve(registration)
          }
        })
      }

      // Timeout fallback (3 seconds)
      setTimeout(() => {
        console.log('⏱️ SW timeout - proceeding anyway')
        swReady.value = true
        resolve(registration)
      }, 3000)
    })
  } catch (error) {
    console.error('❌ Service Worker registration failed:', error)
    swReady.value = true
    return null
  }
}

const enablePushNotifications = async () => {
  // ✅ Validate guest email if not logged in
  if (!user.value && !isValidEmail(guestEmail.value)) {
    message.value = '❌ Please enter a valid email address'
    isError.value = true
    return
  }

  if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
    message.value = '❌ Push notifications not supported in this browser.'
    isError.value = true
    return
  }

  if (!swReady.value) {
    message.value = '⏳ Waiting for service worker... please try again'
    isError.value = true
    return
  }

  submitting.value = true
  message.value = ''
  isError.value = false

  try {
    console.log('🔔 Requesting notification permission...')
    const permission = await Notification.requestPermission()

    if (permission !== 'granted') {
      message.value = '❌ Please enable notifications in browser settings'
      isError.value = true
      submitting.value = false
      return
    }

    console.log('✅ Permission granted')
    console.log('📡 Subscribing to push notifications...')

    const registration = await navigator.serviceWorker.ready
    const subscription = await registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(config.public.vapidPublicKey)
    })

    console.log('✅ Push subscription created')
    console.log('📤 Sending subscription to server...')

    // ✅ Send with user email (logged in) or guest email
    const emailToSend = user.value?.email || guestEmail.value

    const res: any = await useSanctumFetch(`${config.public.apiBase}/push/subscribe`, {
      method: 'POST',
      body: {
        subscription: subscription.toJSON(),
        email: emailToSend
      }
    })

    if (res?.status) {
      message.value = '✅ Notifications enabled successfully!'
      isError.value = false
      hasEnabledNotifications.value = true

      if (process.client) {
        localStorage.setItem('pushNotificationsEnabled', 'true')
        localStorage.setItem('pushNotificationEmail', emailToSend)
        localStorage.removeItem('pushNotificationDismissed')
        localStorage.removeItem('pushNotificationLastShown')
      }

      setTimeout(() => visible.value = false, 2500)
    } else {
      throw new Error(res?.message || 'Subscription failed')
    }

  } catch (error: any) {
    console.error('❌ Push subscription error:', error)

    if (error?.data?.message?.includes('User not found')) {
      message.value = '❌ Email not registered. Please use a registered email or login.'
    } else if (error?.message?.includes('Subscription failed')) {
      message.value = '❌ Failed to enable notifications. Try again.'
    } else if (error?.message?.includes('no active Service Worker')) {
      message.value = '⏳ Service worker not ready. Refresh page and try again.'
    } else {
      message.value = error?.data?.message || '❌ Failed to enable notifications.'
    }

    isError.value = true
  } finally {
    submitting.value = false
  }
}

const urlBase64ToUint8Array = (base64String: string) => {
  if (!base64String) {
    throw new Error('VAPID public key not configured')
  }

  const padding = '='.repeat((4 - base64String.length % 4) % 4)
  const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/')
  const rawData = window.atob(base64)
  const outputArray = new Uint8Array(rawData.length)
  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i)
  }
  return outputArray
}

onMounted(async () => {
  if (!process.client) return

  console.log('🚀 NewsletterSplash mounted')

  if (checkShouldShow()) {
    console.log('💬 Showing notification splash (first load)')
    visible.value = true
    setTimeout(() => splash.value?.focus(), 300)
  }

  registerServiceWorker()
})
</script>

<style scoped>
.backdrop-fade-enter-active, .backdrop-fade-leave-active {
  transition: opacity 0.3s ease;
}

.backdrop-fade-enter-from, .backdrop-fade-leave-to {
  opacity: 0;
}

.slide-in-enter-active {
  animation: slide-in 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.slide-in-leave-active {
  animation: slide-out 0.3s ease-out;
}

@keyframes slide-in {
  from {
    transform: translateY(100%) translateX(-20%);
    opacity: 0;
  }
  to {
    transform: translateY(0) translateX(0);
    opacity: 1;
  }
}

@keyframes slide-out {
  to {
    transform: translateY(20%) translateX(-10%) scale(0.95);
    opacity: 0;
  }
}

.message-fade-enter-active, .message-fade-leave-active {
  transition: all 0.3s ease;
}

.message-fade-enter-from, .message-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

@keyframes pulse-soft {
  0%, 100% {
    transform: scale(1);
    opacity: 0.1;
  }
  50% {
    transform: scale(1.05);
    opacity: 0.15;
  }
}

.animate-pulse-soft {
  animation: pulse-soft 3s ease-in-out infinite;
}

.shadow-premium {
  box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.25), 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}

.dark .shadow-premium {
  box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.5), 0 10px 25px -5px rgba(0, 0, 0, 0.3);
}

@media (max-width: 640px) {
  @keyframes slide-in {
    from {
      transform: translateY(100%);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }
}
</style>
