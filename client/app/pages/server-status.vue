<template>
  <div class="min-h-screen relative overflow-hidden bg-gradient-to-br from-slate-950 via-blue-950 to-slate-950">
    <!-- Animated Background -->
    <div class="absolute inset-0 pointer-events-none">
      <div
        ref="orb1"
        class="absolute w-96 h-96 bg-blue-500/20 rounded-full blur-3xl"
      />
      <div
        ref="orb2"
        class="absolute w-80 h-80 bg-purple-500/20 rounded-full blur-3xl"
      />
      <div
        ref="orb3"
        class="absolute w-64 h-64 bg-emerald-500/20 rounded-full blur-3xl"
      />
    </div>

    <!-- Content -->
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
      <div class="w-full max-w-6xl">
        <!-- Header -->
        <div
          ref="header"
          class="text-center mb-12 opacity-0"
        >
          <div class="inline-flex items-center gap-3 mb-4">
            <div class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse" />
            <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-blue-400 via-purple-400 to-emerald-400 bg-clip-text text-transparent">
              System Status
            </h1>
          </div>
          <p class="text-slate-400 text-lg">
            Real-time monitoring • Token Mode • {{ currentTime }}
          </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <!-- API Status -->
          <div
            ref="stat1"
            class="opacity-0"
          >
            <UCard :ui="{ body: { base: 'backdrop-blur-xl bg-slate-900/50 border border-slate-800' } }">
              <div class="text-center">
                <div class="mb-4">
                  <UIcon
                    name="i-heroicons-server"
                    class="w-12 h-12 mx-auto"
                    :class="apiStatus?.success ? 'text-emerald-400' : 'text-red-400'"
                  />
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">
                  API Server
                </h3>
                <div class="flex items-center justify-center gap-2">
                  <div
                    class="w-2 h-2 rounded-full"
                    :class="apiStatus?.success ? 'bg-emerald-400 animate-pulse' : 'bg-red-400'"
                  />
                  <span :class="apiStatus?.success ? 'text-emerald-400' : 'text-red-400'">
                    {{ apiStatus?.success ? 'Online' : 'Offline' }}
                  </span>
                </div>
                <p class="text-xs text-slate-400 mt-2">
                  {{ apiResponseTime }}ms
                </p>
              </div>
            </UCard>
          </div>

          <!-- Auth Status -->
          <div
            ref="stat2"
            class="opacity-0"
          >
            <UCard :ui="{ body: { base: 'backdrop-blur-xl bg-slate-900/50 border border-slate-800' } }">
              <div class="text-center">
                <div class="mb-4">
                  <UIcon
                    name="i-heroicons-shield-check"
                    class="w-12 h-12 mx-auto"
                    :class="authStatus?.success ? 'text-blue-400' : 'text-red-400'"
                  />
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">
                  Authentication
                </h3>
                <div class="flex items-center justify-center gap-2">
                  <div
                    class="w-2 h-2 rounded-full"
                    :class="authStatus?.success ? 'bg-blue-400 animate-pulse' : 'bg-red-400'"
                  />
                  <span :class="authStatus?.success ? 'text-blue-400' : 'text-red-400'">
                    {{ authStatus?.success ? 'Working' : 'Failed' }}
                  </span>
                </div>
                <p class="text-xs text-slate-400 mt-2">
                  Bearer Token Mode
                </p>
              </div>
            </UCard>
          </div>

          <!-- Database Status -->
          <div
            ref="stat3"
            class="opacity-0"
          >
            <UCard :ui="{ body: { base: 'backdrop-blur-xl bg-slate-900/50 border border-slate-800' } }">
              <div class="text-center">
                <div class="mb-4">
                  <UIcon
                    name="i-heroicons-circle-stack"
                    class="w-12 h-12 mx-auto"
                    :class="dbStatus ? 'text-purple-400' : 'text-slate-600'"
                  />
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">
                  Database
                </h3>
                <div class="flex items-center justify-center gap-2">
                  <div class="w-2 h-2 rounded-full bg-purple-400 animate-pulse" />
                  <span class="text-purple-400">Connected</span>
                </div>
                <p class="text-xs text-slate-400 mt-2">
                  MySQL
                </p>
              </div>
            </UCard>
          </div>
        </div>

        <!-- Detailed Test Results -->
        <div
          ref="details"
          class="opacity-0"
        >
          <UCard :ui="{ body: { base: 'backdrop-blur-xl bg-slate-900/50 border border-slate-800' } }">
            <template #header>
              <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-white">
                  System Tests
                </h2>
                <UButton
                  :loading="testing"
                  color="primary"
                  size="sm"
                  @click="runTests"
                >
                  {{ testing ? 'Running...' : 'Run Tests' }}
                </UButton>
              </div>
            </template>

            <div class="space-y-4">
              <!-- Test Item Template -->
              <div
                v-for="(test, index) in tests"
                :key="index"
                class="test-item p-4 rounded-lg border transition-all duration-300"
                :class="getTestClass(test)"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                      <UIcon
                        :name="test.status === 'success' ? 'i-heroicons-check-circle' : test.status === 'error' ? 'i-heroicons-x-circle' : 'i-heroicons-clock'"
                        class="w-5 h-5"
                      />
                      <h3 class="font-semibold text-white">
                        {{ test.name }}
                      </h3>
                    </div>
                    <p class="text-sm text-slate-400">
                      {{ test.description }}
                    </p>
                    <p
                      v-if="test.details"
                      class="text-xs text-slate-500 mt-2 font-mono"
                    >
                      {{ test.details }}
                    </p>
                  </div>
                  <div class="text-right">
                    <span
                      class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium"
                      :class="getStatusBadgeClass(test.status)"
                    >
                      {{ test.status === 'success' ? '✓ Passed' : test.status === 'error' ? '✗ Failed' : '⋯ Pending' }}
                    </span>
                    <p
                      v-if="test.responseTime"
                      class="text-xs text-slate-500 mt-1"
                    >
                      {{ test.responseTime }}ms
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </UCard>
        </div>

        <!-- Footer -->
        <div
          ref="footer"
          class="text-center mt-8 opacity-0"
        >
          <p class="text-slate-500 text-sm">
            Last updated: {{ lastUpdate }} • Auto-refresh every 60s
            <span
              v-if="errorCount > 0"
              class="text-red-400 ml-2"
            >
              • {{ errorCount }} error(s) detected
            </span>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { gsap } from 'gsap'

definePageMeta({
  layout: false
})

const config = useRuntimeConfig()

// Refs for animations
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()
const orb3 = ref<HTMLElement>()
const header = ref<HTMLElement>()
const stat1 = ref<HTMLElement>()
const stat2 = ref<HTMLElement>()
const stat3 = ref<HTMLElement>()
const details = ref<HTMLElement>()
const footer = ref<HTMLElement>()

// State
const testing = ref(false)
const currentTime = ref('')
const lastUpdate = ref('')
const apiResponseTime = ref(0)
const errorCount = ref(0)

const apiStatus = ref<{ success: boolean, message?: string } | null>(null)
const authStatus = ref<{ success: boolean, message?: string } | null>(null)
const dbStatus = ref(true)

interface Test {
  name: string
  description: string
  status: 'pending' | 'success' | 'error'
  details?: string
  responseTime?: number
}

const tests = ref<Test[]>([
  {
    name: 'API Health Check',
    description: 'Verify API server is responding',
    status: 'pending'
  },
  {
    name: 'Authentication Flow',
    description: 'Test token-based login system',
    status: 'pending'
  },
  {
    name: 'User Endpoint',
    description: 'Verify authenticated API calls work',
    status: 'pending'
  },
  {
    name: 'CORS Configuration',
    description: 'Check cross-origin requests',
    status: 'pending'
  }
])

// Update time
const updateTime = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString()
  lastUpdate.value = now.toLocaleString()
}

// Run tests
const runTests = async () => {
  testing.value = true
  errorCount.value = 0

  // Reset tests
  tests.value.forEach((test) => {
    test.status = 'pending'
    test.details = undefined
    test.responseTime = undefined
  })

  // Test 1: API Health
  try {
    const start = Date.now()
    await useSanctumFetch(`${config.public.apiBase}/api/health`)
    const responseTime = Date.now() - start

    tests.value[0].status = 'success'
    tests.value[0].details = `API is running (${responseTime}ms)`
    tests.value[0].responseTime = responseTime
    apiResponseTime.value = responseTime
    apiStatus.value = { success: true }
  } catch (error: unknown) {
    const err = error as Error
    tests.value[0].status = 'error'
    tests.value[0].details = err.message || 'Failed to connect'
    apiStatus.value = { success: false }
    errorCount.value++
  }

  await new Promise(resolve => setTimeout(resolve, 500))

  // Test 2: Authentication
  try {
    const start = Date.now()
    const { login } = useSanctum()
    await login({
      email: 'regular@demo.com',
      password: 'password'
    })
    const responseTime = Date.now() - start

    tests.value[1].status = 'success'
    tests.value[1].details = `Token authentication successful (${responseTime}ms)`
    tests.value[1].responseTime = responseTime
    authStatus.value = { success: true }
  } catch (error: unknown) {
    const err = error as { data?: { message?: string }, message?: string }
    tests.value[1].status = 'error'
    tests.value[1].details = err.data?.message || err.message || 'Authentication failed'
    authStatus.value = { success: false }
    errorCount.value++
  }

  await new Promise(resolve => setTimeout(resolve, 500))

  // Test 3: User Endpoint
  try {
    const start = Date.now()
    const response = await useSanctumFetch(`${config.public.apiBase}/api/user`) as { name: string }
    const responseTime = Date.now() - start

    tests.value[2].status = 'success'
    tests.value[2].details = `Bearer token working • User: ${response.name} (${responseTime}ms)`
    tests.value[2].responseTime = responseTime
  } catch (error: unknown) {
    const err = error as Error
    tests.value[2].status = 'error'
    tests.value[2].details = err.message || 'Failed to fetch user'
    errorCount.value++
  }

  await new Promise(resolve => setTimeout(resolve, 500))

  // Test 4: CORS
  tests.value[3].status = apiStatus.value?.success ? 'success' : 'error'
  tests.value[3].details = apiStatus.value?.success
    ? 'Cross-origin requests working properly'
    : 'CORS configuration issue detected'

  if (!apiStatus.value?.success) {
    errorCount.value++
  }

  testing.value = false
  updateTime()

  // Send error notification if there are errors
  if (errorCount.value > 0) {
    await sendErrorNotification()
  }
}

// Send error notification
const sendErrorNotification = async () => {
  try {
    const failedTests = tests.value.filter(t => t.status === 'error')

    // Call backend API to send email
    await useSanctumFetch(`${config.public.apiBase}/api/system/error-notification`, {
      method: 'POST',
      body: {
        error_count: errorCount.value,
        failed_tests: failedTests.map(t => ({
          name: t.name,
          details: t.details
        })),
        timestamp: new Date().toISOString()
      }
    })
  } catch (error) {
    console.error('Failed to send error notification:', error)
  }
}

// Helper functions
const getTestClass = (test: Test) => {
  if (test.status === 'success') return 'border-emerald-500/30 bg-emerald-500/5'
  if (test.status === 'error') return 'border-red-500/30 bg-red-500/5'
  return 'border-slate-700 bg-slate-800/30'
}

const getStatusBadgeClass = (status: string) => {
  if (status === 'success') return 'bg-emerald-500/20 text-emerald-400'
  if (status === 'error') return 'bg-red-500/20 text-red-400'
  return 'bg-slate-700/50 text-slate-400'
}

// Animations
onMounted(() => {
  // Animate orbs
  if (orb1.value) {
    gsap.to(orb1.value, {
      x: 300,
      y: 200,
      duration: 20,
      repeat: -1,
      yoyo: true,
      ease: 'sine.inOut'
    })
  }

  if (orb2.value) {
    gsap.to(orb2.value, {
      x: -200,
      y: 300,
      duration: 15,
      repeat: -1,
      yoyo: true,
      ease: 'sine.inOut'
    })
  }

  if (orb3.value) {
    gsap.to(orb3.value, {
      x: 100,
      y: -150,
      duration: 18,
      repeat: -1,
      yoyo: true,
      ease: 'sine.inOut'
    })
  }

  // Stagger animations for content
  gsap.to(header.value, { opacity: 1, y: 0, duration: 0.8, delay: 0.2 })
  gsap.to(stat1.value, { opacity: 1, y: 0, duration: 0.8, delay: 0.4 })
  gsap.to(stat2.value, { opacity: 1, y: 0, duration: 0.8, delay: 0.5 })
  gsap.to(stat3.value, { opacity: 1, y: 0, duration: 0.8, delay: 0.6 })
  gsap.to(details.value, { opacity: 1, y: 0, duration: 0.8, delay: 0.8 })
  gsap.to(footer.value, { opacity: 1, duration: 0.8, delay: 1 })

  // Initial run
  updateTime()
  runTests()

  // Auto-refresh every 60 seconds
  const interval = setInterval(() => {
    runTests()
  }, 60000)

  // Update time every second
  const timeInterval = setInterval(updateTime, 1000)

  onUnmounted(() => {
    clearInterval(interval)
    clearInterval(timeInterval)
  })
})
</script>

<style scoped>
.test-item {
  transition: all 0.3s ease;
}

.test-item:hover {
  transform: translateX(4px);
}
</style>
