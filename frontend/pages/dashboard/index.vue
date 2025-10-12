<!-- pages/dashboard/index.vue -->
<template>
  <div>

    <!-- ✅ MOBILE: Simple, Fast Layout (hidden on desktop) -->
    <div class="lg:hidden">

      <!-- Mobile Header (LCP Element) -->
      <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 p-4">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
          Welcome, {{ user?.name?.split(' ')[0] || 'User' }}! 👋
        </h1>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
          {{ greetingMessage }}
        </p>
      </header>

      <!-- Mobile Stats Grid -->
      <main class="p-4 space-y-4">
        <div class="grid grid-cols-2 gap-3">

          <div class="bg-white dark:bg-slate-800 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-8 h-8 rounded bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                <Icon name="mdi:currency-inr" class="w-5 h-5 text-green-600" />
              </div>
            </div>
            <p class="text-xs text-slate-500">Earnings</p>
            <p class="text-lg font-bold text-green-600">₹<span class="animated-number" data-value="15200">0</span></p>
          </div>

          <div class="bg-white dark:bg-slate-800 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-8 h-8 rounded bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                <Icon name="mdi:account-group" class="w-5 h-5 text-blue-600" />
              </div>
            </div>
            <p class="text-xs text-slate-500">Referrals</p>
            <p class="text-lg font-bold text-blue-600"><span class="animated-number" data-value="38">0</span></p>
          </div>

          <div class="bg-white dark:bg-slate-800 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-8 h-8 rounded bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                <Icon name="mdi:shopping" class="w-5 h-5 text-purple-600" />
              </div>
            </div>
            <p class="text-xs text-slate-500">Orders</p>
            <p class="text-lg font-bold text-purple-600"><span class="animated-number" data-value="129">0</span></p>
          </div>

          <div class="bg-white dark:bg-slate-800 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-8 h-8 rounded bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                <Icon name="mdi:trophy" class="w-5 h-5 text-yellow-600" />
              </div>
            </div>
            <p class="text-xs text-slate-500">Rank</p>
            <p class="text-lg font-bold text-yellow-600">Gold</p>
          </div>
        </div>

        <!-- Mobile Quick Actions -->
        <div v-if="showQuickActions" class="grid grid-cols-2 gap-3 mt-4">
          <NuxtLink to="/dashboard/orders" class="flex flex-col items-center gap-2 p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <Icon name="mdi:package-variant-closed" class="w-5 h-5 text-blue-600" />
            </div>
            <span class="text-xs font-medium text-slate-900 dark:text-white">Orders</span>
          </NuxtLink>

          <NuxtLink to="/dashboard/wallet" class="flex flex-col items-center gap-2 p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
            <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
              <Icon name="mdi:wallet" class="w-5 h-5 text-green-600" />
            </div>
            <span class="text-xs font-medium text-slate-900 dark:text-white">Wallet</span>
          </NuxtLink>

          <NuxtLink to="/dashboard/account/kyc" class="flex flex-col items-center gap-2 p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
            <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
              <Icon name="mdi:shield-check" class="w-5 h-5 text-purple-600" />
            </div>
            <span class="text-xs font-medium text-slate-900 dark:text-white">KYC</span>
          </NuxtLink>

          <NuxtLink to="/dashboard/helpdesk" class="flex flex-col items-center gap-2 p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
            <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
              <Icon name="mdi:help-circle" class="w-5 h-5 text-orange-600" />
            </div>
            <span class="text-xs font-medium text-slate-900 dark:text-white">Help</span>
          </NuxtLink>
        </div>
      </main>
    </div>

    <!-- ✅ DESKTOP: Rich Layout (hidden on mobile) -->
    <div class="hidden lg:block min-h-screen">

      <!-- Desktop Header (LCP Element) -->
      <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
          <div class="flex-1">
            <h1 class="text-3xl sm:text-4xl font-extrabold bg-gradient-to-r from-slate-900 via-blue-600 to-purple-600 dark:from-white dark:via-blue-400 dark:to-purple-400 bg-clip-text text-transparent mb-2">
              Welcome Back, {{ user?.name?.split(' ')[0] || 'User' }}! 👋
            </h1>
            <p class="text-lg text-slate-600 dark:text-slate-300">
              {{ greetingMessage }} Here's what's happening with your account today.
            </p>
          </div>

          <div class="flex items-center gap-3 flex-shrink-0">
            <NuxtLink to="/dashboard/account" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
              <Icon name="mdi:account-edit" class="w-4 h-4" />
              <span>Edit Profile</span>
            </NuxtLink>

            <button @click="refreshDashboard" :disabled="isRefreshing" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl font-semibold hover:bg-slate-200 dark:hover:bg-slate-600 transition-all duration-300 disabled:opacity-50">
              <Icon name="mdi:refresh" class="w-4 h-4" :class="{ 'animate-spin': isRefreshing }" />
              <span>{{ isRefreshing ? 'Refreshing...' : 'Refresh' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Desktop Main Grid -->
      <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 xl:grid-cols-[320px_1fr] gap-8">

        <!-- Sidebar: Profile -->
        <aside>
          <ClientOnly>
            <template #fallback>
              <div class="h-80 bg-slate-100 dark:bg-slate-800 rounded-2xl animate-pulse"></div>
            </template>
            <ProfileCard
                :user="user"
                header-title="Profile Overview"
                header-subtitle="Your account details"
                :show-edit-button="false"
                :show-verification-badges="false"
                :show-referral-section="false"
                :show-extended-details="true"
                :show-social-share="false"
                :use-avatar-uploader="false"
            />
          </ClientOnly>
        </aside>

        <!-- Main Content -->
        <main class="space-y-8">

          <!-- Stats Grid -->
          <section>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

              <div class="group relative bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-500 to-emerald-500 rounded-t-2xl"></div>
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <Icon name="mdi:currency-inr" class="w-6 h-6" />
                  </div>
                  <div class="flex-1">
                    <h3 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-1">Total Earnings</h3>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">
                      ₹<span class="animated-number tabular-nums" data-value="15200">0</span>
                    </div>
                    <div class="flex items-center gap-1 text-xs font-semibold text-green-600 dark:text-green-400">
                      <Icon name="mdi:trending-up" class="w-3 h-3" />
                      <span>+12.5%</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="group relative bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-t-2xl"></div>
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <Icon name="mdi:account-group" class="w-6 h-6" />
                  </div>
                  <div class="flex-1">
                    <h3 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-1">Referral Count</h3>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-2">
                      <span class="animated-number tabular-nums" data-value="38">0</span>
                    </div>
                    <div class="flex items-center gap-1 text-xs font-semibold text-green-600 dark:text-green-400">
                      <Icon name="mdi:trending-up" class="w-3 h-3" />
                      <span>+8.3%</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="group relative bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-pink-500 rounded-t-2xl"></div>
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <Icon name="mdi:shopping" class="w-6 h-6" />
                  </div>
                  <div class="flex-1">
                    <h3 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-1">Total Orders</h3>
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-2">
                      <span class="animated-number tabular-nums" data-value="129">0</span>
                    </div>
                    <div class="flex items-center gap-1 text-xs font-semibold text-green-600 dark:text-green-400">
                      <Icon name="mdi:trending-up" class="w-3 h-3" />
                      <span>+15.7%</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="group relative bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-t-2xl"></div>
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-500 to-orange-600 flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <Icon name="mdi:trophy" class="w-6 h-6" />
                  </div>
                  <div class="flex-1">
                    <h3 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-1">Current Rank</h3>
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mb-2">Gold</div>
                    <div class="flex items-center gap-1 text-xs font-semibold text-slate-500">
                      <Icon name="mdi:minus" class="w-3 h-3" />
                      <span>No change</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- System Stats -->
          <ClientOnly v-if="showSystemStats">
            <section>
              <div class="flex items-center gap-3 mb-6">
                <Icon name="mdi:chart-box" class="w-6 h-6 text-slate-600 dark:text-slate-400" />
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">System Analytics</h3>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <DashboardStatCard icon="mdi:account-group" label="Total Users" value="1,250" change="+5.2%" trend="up" color="blue" />
                <DashboardStatCard icon="mdi:currency-usd" label="Monthly Sales" value="$14,200" change="+12.8%" trend="up" color="green" />
                <DashboardStatCard icon="mdi:cart-outline" label="Active Orders" value="320" change="-2.1%" trend="down" color="orange" />
                <DashboardStatCard icon="mdi:chart-line" label="Total Revenue" value="$54,000" change="+18.5%" trend="up" color="purple" />
              </div>
            </section>
          </ClientOnly>

          <!-- Charts -->
          <ClientOnly v-if="showCharts">
            <template #fallback>
              <div class="h-96 bg-slate-100 dark:bg-slate-800 rounded-2xl animate-pulse"></div>
            </template>
            <section>
              <div class="flex items-center gap-3 mb-6">
                <Icon name="mdi:chart-timeline-variant" class="w-6 h-6 text-slate-600 dark:text-slate-400" />
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Orders Trend Analysis</h3>
              </div>
              <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-lg overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                      <Icon name="mdi:chart-line" class="w-6 h-6 text-white" />
                    </div>
                    <div>
                      <h3 class="text-lg font-bold text-slate-900 dark:text-white">My Orders Trend</h3>
                      <p class="text-sm text-slate-500 dark:text-slate-400">Completed and confirmed orders over time</p>
                    </div>
                  </div>
                  <button class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    <Icon name="mdi:download" class="w-4 h-4" />
                    <span>Export</span>
                  </button>
                </div>
                <div class="p-6">
                  <LazyOrdersTrendChart
                      title="Orders Analysis"
                      endpoint="order/insight"
                      type="line"
                      :show-markers="true"
                      value-label-formatter="{value}"
                      :status="['COMPLETED','CONFIRM']"
                  />
                </div>
              </div>
            </section>
          </ClientOnly>

          <!-- Quick Actions -->
          <ClientOnly v-if="showQuickActions">
            <section>
              <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg">
                  <Icon name="mdi:lightning-bolt" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-xl font-bold text-slate-900 dark:text-white">Quick Actions</h3>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Frequently used features</p>
                </div>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <NuxtLink to="/dashboard/orders" class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                  <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg">
                    <Icon name="mdi:package-variant-closed" class="w-6 h-6" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-slate-900 dark:text-white text-sm mb-1">View Orders</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Manage your orders</p>
                  </div>
                  <Icon name="mdi:arrow-right" class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition-transform" />
                </NuxtLink>

                <NuxtLink to="/dashboard/wallet" class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl hover:bg-green-50 dark:hover:bg-green-900/20 hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                  <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white shadow-lg">
                    <Icon name="mdi:wallet" class="w-6 h-6" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-slate-900 dark:text-white text-sm mb-1">My Wallet</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Check balance</p>
                  </div>
                  <Icon name="mdi:arrow-right" class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition-transform" />
                </NuxtLink>

                <NuxtLink to="/dashboard/account/kyc" class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                  <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white shadow-lg">
                    <Icon name="mdi:shield-check" class="w-6 h-6" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-slate-900 dark:text-white text-sm mb-1">KYC Status</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Verify account</p>
                  </div>
                  <Icon name="mdi:arrow-right" class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition-transform" />
                </NuxtLink>

                <NuxtLink to="/dashboard/helpdesk" class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                  <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center text-white shadow-lg">
                    <Icon name="mdi:help-circle" class="w-6 h-6" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-slate-900 dark:text-white text-sm mb-1">Get Help</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Support center</p>
                  </div>
                  <Icon name="mdi:arrow-right" class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition-transform" />
                </NuxtLink>
              </div>
            </section>
          </ClientOnly>
        </main>
      </div>

      <!-- Background Orbs -->
      <div v-if="showBackground" class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
        <div ref="orb1" class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-gradient-to-r from-blue-400/10 to-purple-400/10 dark:from-blue-400/5 dark:to-purple-400/5 blur-3xl opacity-60"></div>
        <div ref="orb2" class="absolute -bottom-32 -right-32 w-80 h-80 rounded-full bg-gradient-to-r from-purple-400/10 to-pink-400/10 dark:from-purple-400/5 dark:to-pink-400/5 blur-3xl opacity-70"></div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useSanctum } from '#imports'

// ✅ Lazy components (NO LODASH IMPORTS!)
const LazyOrdersTrendChart = defineAsyncComponent(() =>
    import('~/components/charts/OrdersTrendChart.vue')
)

const DashboardStatCard = defineAsyncComponent(() =>
    import('~/components/dashboard/cards/DashboardStatCard.vue')
)

const ProfileCard = defineAsyncComponent(() =>
    import('~/components/dashboard/cards/ProfileCard.vue')
)

definePageMeta({ layout: 'dashboard' })

// ✅ CRITICAL: Inline CSS for LCP
useHead({
  style: [{
    children: `header{background:#fff}@media (prefers-color-scheme:dark){header{background:#0f172a}}h1{font-size:1.5rem;font-weight:700;color:#0f172a}@media (prefers-color-scheme:dark){h1{color:#fff}}@media (min-width:1024px){h1{font-size:1.875rem}.lg\\:hidden{display:none}.hidden.lg\\:block{display:block}}`,
    type: 'text/css'
  }]
})

const { user } = useSanctum()

// State
const isRefreshing = ref(false)
const showSystemStats = ref(false)
const showCharts = ref(false)
const showQuickActions = ref(false)
const showBackground = ref(false)

// Refs
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()

// Computed
const greetingMessage = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12) return "Good morning!"
  if (hour < 18) return "Good afternoon!"
  return "Good evening!"
})

// Methods
async function refreshDashboard() {
  isRefreshing.value = true
  await new Promise(resolve => setTimeout(resolve, 1500))
  animateNumbers()
  isRefreshing.value = false
}

function animateNumbers() {
  if (!process.client) return

  const numbers = document.querySelectorAll('.animated-number')
  numbers.forEach((el) => {
    const target = parseInt(el.getAttribute('data-value') || '0')
    let current = 0
    const increment = target / 30

    const interval = setInterval(() => {
      current += increment
      if (current >= target) {
        current = target
        clearInterval(interval)
      }
      el.textContent = Math.round(current).toLocaleString()
    }, 30)
  })
}

// ✅ Progressive loading
async function loadProgressively() {
  await nextTick()
  animateNumbers()

  setTimeout(() => { showQuickActions.value = true }, 300)
  setTimeout(() => { showSystemStats.value = true }, 800)

  // Only load heavy components on desktop
  if (process.client && window.innerWidth >= 1024) {
    setTimeout(() => { showCharts.value = true }, 1500)
    setTimeout(() => { showBackground.value = true }, 2000)
  }
}

onMounted(() => {
  loadProgressively()
})
</script>
