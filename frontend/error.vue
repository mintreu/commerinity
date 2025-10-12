<script setup lang="ts">
// Nuxt 3 global error page
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useRuntimeConfig } from '#app'
import { gsap } from 'gsap'

// Type for incoming error prop
import type { NuxtError } from '#app'
const props = defineProps<{ error?: NuxtError | any }>()

const config = useRuntimeConfig()
const mode = (config.public?.nodeEnv as string) || (import.meta.env?.MODE as string) || (process.env.NODE_ENV as string) || 'production'
const isDev = mode === 'development' || mode === 'dev'

const route = useRoute()
const router = useRouter()
const isDashboard = computed(() => route.path.includes('/dashboard'))

const copied = ref(false)
const detailOpen = ref(isDev)
const animRoot = ref<HTMLElement | null>(null)

const statusCode = computed(() => props.error?.statusCode ?? props.error?.status ?? '—')
const message = computed(() => props.error?.message ?? props.error?.statusMessage ?? 'An unexpected error occurred.')
const stack = computed(() => {
  if (!props.error) return ''
  return props.error?.stack ?? props.error?.meta?.stack ?? (typeof props.error === 'string' ? props.error : '')
})

// ✅ Send error to backend API
async function logErrorToBackend(include404 = false) {
  if (statusCode.value === 404 && !include404) return

  // Only log in production
  if (process.env.NODE_ENV !== 'production') {
    console.warn('Error logging disabled in dev mode')
    return
  }

  try {
    await $fetch(`${config.public.apiBase}/log-error`, {
      method: 'POST',
      body: {
        statusCode: statusCode.value,
        message: message.value,
        stack: stack.value,
        url: window.location.href,
        route: route.fullPath,
        userAgent: navigator.userAgent,
        timestamp: new Date().toISOString(),
      }
    })
  } catch (error) {
    console.error('Failed to log error to backend:', error)
  }
}

onMounted(() => {
  if (!animRoot.value) return
  gsap.from(animRoot.value.querySelectorAll('.card-elem'), {
    opacity: 0,
    y: 20,
    stagger: 0.06,
    duration: 0.5,
    ease: 'power2.out'
  })

  // ✅ Auto-log error on page load (except 404s)
  if (!isDev) {
    logErrorToBackend(false)
  }
})

function goToHome() { router.push('/') }
function goToDashboard() { router.push('/dashboard') }
function tryBack() {
  if (window.history.length > 1) window.history.back()
  else router.push('/')
}

async function copyError() {
  try {
    const payload = JSON.stringify({
      statusCode: statusCode.value,
      message: message.value,
      stack: stack.value
    }, null, 2)
    await navigator.clipboard.writeText(payload)
    copied.value = true
    setTimeout(() => (copied.value = false), 2000)
  } catch {
    copied.value = false
  }
}

function reportIssue() {
  // ✅ Log error (including 404s when manually reported)
  logErrorToBackend(true)

  const subject = encodeURIComponent(`Error report: ${statusCode.value} - ${message.value}`)
  const body = encodeURIComponent(
      `URL: ${location.href}\nStatus: ${statusCode.value}\n\nMessage:\n${message.value}\n\nStack:\n${stack.value}`
  )
  window.location.href = `mailto:${config.public.supportEmail}?subject=${subject}&body=${body}`
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 flex items-center justify-center p-6">
    <div ref="animRoot" class="max-w-4xl w-full bg-white dark:bg-slate-900 rounded-2xl shadow-xl ring-1 ring-slate-200 dark:ring-slate-700 overflow-hidden md:flex">
      <!-- Left visual / icon -->
      <section class="card-elem flex-shrink-0 bg-gradient-to-br from-indigo-600 to-violet-600 text-white p-8 md:w-1/3 flex flex-col items-center justify-center">
        <div class="w-28 h-28 rounded-full bg-white/10 flex items-center justify-center mb-4">
          <Icon name="mdi:alert-circle-outline" width="48" height="48" aria-hidden />
        </div>

        <h3 class="text-xl font-semibold">Oops — something broke</h3>
        <p class="text-sm mt-2 text-white/80 text-center">We hit an unexpected condition.</p>

        <div class="mt-6 text-xs text-white/80">Status: <span class="font-medium">{{ statusCode }}</span></div>
      </section>

      <!-- Right content -->
      <section class="card-elem p-6 md:flex-1">
        <div class="flex items-start justify-between">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ message }}</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">We logged the issue. Try one of the options below.</p>
          </div>

          <div class="ml-4 flex items-center gap-3">
            <button
                @click="tryBack"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-md bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 ring-1 ring-slate-100 dark:ring-slate-600"
            >
              <Icon name="mdi:arrow-left" width="16" height="16" />
              Back
            </button>

            <button
                v-if="isDashboard"
                @click="goToDashboard"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-md bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm"
            >
              <Icon name="mdi:home-outline" width="16" height="16" />
              Dashboard Home
            </button>

            <button
                v-else
                @click="goToHome"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-md bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm"
            >
              <Icon name="mdi:home-outline" width="16" height="16" />
              Home
            </button>
          </div>
        </div>

        <div class="my-6 border-t border-slate-100 dark:border-slate-700"></div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div class="flex items-center gap-3">
            <button
                @click="detailOpen = !detailOpen"
                class="flex items-center gap-2 px-3 py-2 rounded-md bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 ring-1 ring-slate-100 dark:ring-slate-600 text-sm"
            >
              <Icon name="mdi:file-document-outline" width="16" height="16" />
              {{ detailOpen ? 'Hide' : 'Show' }} details
            </button>

            <button
                @click="copyError"
                class="ml-1 px-3 py-2 text-sm rounded-md bg-amber-50 dark:bg-amber-900 hover:bg-amber-100 dark:hover:bg-amber-800 text-amber-700 dark:text-amber-300 ring-1 ring-amber-50 dark:ring-amber-900"
            >
              <Icon name="mdi:clipboard-outline" width="14" height="14" />
              {{ copied ? 'Copied' : 'Copy error' }}
            </button>

            <button
                @click="reportIssue"
                class="ml-1 px-3 py-2 text-sm rounded-md bg-rose-50 dark:bg-rose-900 hover:bg-rose-100 dark:hover:bg-rose-800 text-rose-700 dark:text-rose-300 ring-1 ring-rose-50 dark:ring-rose-900"
            >
              <Icon name="mdi:email-outline" width="14" height="14" />
              Report
            </button>
          </div>

          <div class="text-sm text-slate-500 dark:text-slate-400">
            <span v-if="isDev" class="font-medium text-amber-600 dark:text-amber-400">Development mode</span>
            <span v-else>Production</span>
          </div>
        </div>

        <transition name="fade">
          <div
              v-show="detailOpen"
              class="mt-4 bg-slate-50 dark:bg-slate-800 p-4 rounded-md border border-slate-100 dark:border-slate-700 text-sm text-slate-800 dark:text-slate-200 overflow-y-auto max-h-64"
          >
            <div class="mb-2">
              <strong class="text-slate-700 dark:text-slate-300">Message:</strong>
              <div class="mt-1 font-mono text-xs text-slate-600 dark:text-slate-400 break-words">{{ message }}</div>
            </div>

            <div v-if="isDev" class="mt-2">
              <strong class="text-slate-700 dark:text-slate-300">Stack trace:</strong>
              <pre
                  class="mt-1 whitespace-pre-wrap text-xs font-mono text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-900 border rounded p-3 overflow-auto max-h-48"
              >{{ stack }}</pre>
            </div>

            <div v-else class="mt-2 text-xs text-slate-500 dark:text-slate-400">
              For more details please contact support.
            </div>
          </div>
        </transition>

        <div class="mt-6 text-sm text-slate-500 dark:text-slate-400 flex items-center justify-between">
          <div>
            <span>Possible actions:</span>
            <ul class="inline-block ml-3 list-disc">
              <li class="inline-block ml-2">Reload the page</li>
              <li class="inline-block ml-2">Go to a safe page</li>
              <li class="inline-block ml-2">Contact support</li>
            </ul>
          </div>

          <div class="hidden md:block text-xs text-slate-400 dark:text-slate-600">Error UI • Commernity</div>
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

pre {
  scrollbar-width: thin;
}
</style>
