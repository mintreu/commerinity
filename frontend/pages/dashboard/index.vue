<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <!-- Floating Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div ref="dashboardOrb1" class="absolute -top-32 -left-32 w-96 h-96 bg-gradient-to-r from-blue-400/10 to-purple-400/10 dark:from-blue-400/5 dark:to-purple-400/5 rounded-full blur-3xl opacity-60 animate-pulse"></div>
      <div ref="dashboardOrb2" class="absolute -bottom-32 -right-32 w-80 h-80 bg-gradient-to-r from-purple-400/10 to-pink-400/10 dark:from-purple-400/5 dark:to-pink-400/5 rounded-full blur-3xl opacity-70 animate-pulse"></div>

      <!-- Floating Particles -->
      <div v-for="i in 6" :key="i" class="absolute animate-bounce opacity-30" :style="getFloatingParticleStyle(i)">
        <div class="w-1 h-1 bg-blue-500/40 dark:bg-blue-400/30 rounded-full"></div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-8">

      <!-- Enhanced Page Header -->
      <div ref="pageHeader" class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl overflow-hidden relative">
        <!-- Header Background -->
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-purple-500/5 opacity-50"></div>

        <div class="relative z-10 p-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Header Text -->
            <div class="flex-1">
              <h1 class="text-3xl sm:text-4xl font-black bg-gradient-to-r from-slate-900 via-blue-600 to-purple-600 dark:from-white dark:via-blue-400 dark:to-purple-400 bg-clip-text text-transparent mb-2">
                Welcome Back, {{ user?.name?.split(' ')[0] || 'User' }}! 👋
              </h1>
              <p class="text-lg text-slate-600 dark:text-slate-300 leading-relaxed">
                {{ getGreetingMessage() }} Here's what's happening with your account today.
              </p>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-3 flex-shrink-0">
              <NuxtLink
                  to="/dashboard/account"
                  class="group inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 overflow-hidden"
              >
                <div class="flex items-center gap-2 relative z-10">
                  <Icon name="mdi:account-edit" class="w-4 h-4" />
                  <span>Edit Profile</span>
                </div>
                <!-- Shimmer Effect -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
              </NuxtLink>

              <button
                  @click="refreshDashboard"
                  :disabled="isRefreshing"
                  class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 disabled:opacity-50 text-slate-700 dark:text-slate-300 rounded-xl font-semibold transition-all duration-300 hover:-translate-y-0.5"
              >
                <Icon name="mdi:refresh" class="w-4 h-4" :class="{ 'animate-spin': isRefreshing }" />
                <span>{{ isRefreshing ? 'Refreshing...' : 'Refresh' }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Dashboard Grid -->
      <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">

        <!-- Profile Section -->
        <div class="xl:col-span-1">
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
        </div>

        <!-- Stats & Analytics Section -->
        <div class="xl:col-span-3 space-y-8">

          <!-- Quick Stats Grid -->
          <div ref="statsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- Earnings Card -->
            <div ref="earningsCard" class="group bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 overflow-hidden relative">
              <!-- Top Accent -->
              <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-500 to-emerald-600"></div>

              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                  <Icon name="mdi:currency-inr" class="w-6 h-6 text-white" />
                </div>
                <div class="flex-1">
                  <h3 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-1">Total Earnings</h3>
                  <div class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">
                    ₹<span class="animated-number" data-value="15200">0</span>
                  </div>
                  <div class="flex items-center gap-1 text-sm text-green-600 dark:text-green-400">
                    <Icon name="mdi:trending-up" class="w-4 h-4" />
                    <span>+12.5%</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Referrals Card -->
            <div ref="referralsCard" class="group bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 overflow-hidden relative">
              <!-- Top Accent -->
              <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-600"></div>

              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                  <Icon name="mdi:account-group" class="w-6 h-6 text-white" />
                </div>
                <div class="flex-1">
                  <h3 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-1">Referral Count</h3>
                  <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-2">
                    <span class="animated-number" data-value="38">0</span>
                  </div>
                  <div class="flex items-center gap-1 text-sm text-green-600 dark:text-green-400">
                    <Icon name="mdi:trending-up" class="w-4 h-4" />
                    <span>+8.3%</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Orders Card -->
            <div ref="ordersCard" class="group bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 overflow-hidden relative">
              <!-- Top Accent -->
              <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-pink-600"></div>

              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                  <Icon name="mdi:shopping" class="w-6 h-6 text-white" />
                </div>
                <div class="flex-1">
                  <h3 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-1">Total Orders</h3>
                  <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-2">
                    <span class="animated-number" data-value="129">0</span>
                  </div>
                  <div class="flex items-center gap-1 text-sm text-green-600 dark:text-green-400">
                    <Icon name="mdi:trending-up" class="w-4 h-4" />
                    <span>+15.7%</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Rank Card -->
            <div ref="rankCard" class="group bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 overflow-hidden relative">
              <!-- Top Accent -->
              <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-yellow-500 to-orange-600"></div>

              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                  <Icon name="mdi:trophy" class="w-6 h-6 text-white" />
                </div>
                <div class="flex-1">
                  <h3 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-1">Current Rank</h3>
                  <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mb-2">
                    Gold
                  </div>
                  <div class="flex items-center gap-1 text-sm text-slate-500 dark:text-slate-400">
                    <Icon name="mdi:minus" class="w-4 h-4" />
                    <span>No change</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- System Analytics Section -->
          <div class="space-y-6">
            <div class="flex items-center gap-3">
              <Icon name="mdi:chart-box" class="w-6 h-6 text-slate-600 dark:text-slate-400" />
              <h3 class="text-xl font-bold text-slate-900 dark:text-white">System Analytics</h3>
            </div>

            <div ref="systemStatsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              <DashboardStatCard
                  icon="mdi:account-group"
                  label="Total Users"
                  value="1,250"
                  change="+5.2%"
                  trend="up"
                  color="blue"
              />
              <DashboardStatCard
                  icon="mdi:currency-usd"
                  label="Monthly Sales"
                  value="$14,200"
                  change="+12.8%"
                  trend="up"
                  color="green"
              />
              <DashboardStatCard
                  icon="mdi:cart-outline"
                  label="Active Orders"
                  value="320"
                  change="-2.1%"
                  trend="down"
                  color="orange"
              />
              <DashboardStatCard
                  icon="mdi:chart-line"
                  label="Total Revenue"
                  value="$54,000"
                  change="+18.5%"
                  trend="up"
                  color="purple"
              />
            </div>
          </div>

          <!-- Charts Section -->
          <div class="space-y-6">
            <div class="flex items-center gap-3">
              <Icon name="mdi:chart-timeline-variant" class="w-6 h-6 text-slate-600 dark:text-slate-400" />
              <h3 class="text-xl font-bold text-slate-900 dark:text-white">Orders Trend Analysis</h3>
            </div>

            <div ref="chartsContainer" class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
              <!-- Chart Header -->
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-6 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    <Icon name="mdi:chart-line" class="w-6 h-6 text-white" />
                  </div>
                  <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">My Orders Trend</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Completed and confirmed orders over time</p>
                  </div>
                </div>
                <button class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-lg text-sm font-medium transition-colors">
                  <Icon name="mdi:download" class="w-4 h-4" />
                  <span>Export</span>
                </button>
              </div>

              <!-- Chart Body -->
              <div class="p-6">
                <OrdersTrendChart
                    title="Orders Analysis"
                    endpoint="order/insight"
                    type="line"
                    :show-markers="true"
                    value-label-formatter="{value}"
                    :status="['COMPLETED','CONFIRM']"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions Panel -->
      <div ref="quickActionsPanel" class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
        <!-- Panel Header -->
        <div class="flex items-center gap-4 p-6 border-b border-slate-200 dark:border-slate-700">
          <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg">
            <Icon name="mdi:lightning-bolt" class="w-6 h-6 text-white" />
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Quick Actions</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Frequently used features</p>
          </div>
        </div>

        <!-- Actions Grid -->
        <div class="p-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <NuxtLink to="/dashboard/orders" class="group flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                <Icon name="mdi:package-variant-closed" class="w-6 h-6 text-white" />
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-slate-900 dark:text-white text-sm mb-1">View Orders</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">Manage your orders</p>
              </div>
              <Icon name="mdi:arrow-right" class="w-4 h-4 text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 group-hover:translate-x-1 transition-all" />
            </NuxtLink>

            <NuxtLink to="/dashboard/wallet" class="group flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
              <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                <Icon name="mdi:wallet" class="w-6 h-6 text-white" />
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-slate-900 dark:text-white text-sm mb-1">My Wallet</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">Check balance</p>
              </div>
              <Icon name="mdi:arrow-right" class="w-4 h-4 text-slate-400 group-hover:text-green-600 dark:group-hover:text-green-400 group-hover:translate-x-1 transition-all" />
            </NuxtLink>

            <NuxtLink to="/dashboard/account/kyc" class="group flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
              <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                <Icon name="mdi:shield-check" class="w-6 h-6 text-white" />
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-slate-900 dark:text-white text-sm mb-1">KYC Status</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">Verify account</p>
              </div>
              <Icon name="mdi:arrow-right" class="w-4 h-4 text-slate-400 group-hover:text-purple-600 dark:group-hover:text-purple-400 group-hover:translate-x-1 transition-all" />
            </NuxtLink>

            <NuxtLink to="/dashboard/helpdesk" class="group flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
              <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                <Icon name="mdi:help-circle" class="w-6 h-6 text-white" />
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-slate-900 dark:text-white text-sm mb-1">Get Help</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">Support center</p>
              </div>
              <Icon name="mdi:arrow-right" class="w-4 h-4 text-slate-400 group-hover:text-orange-600 dark:group-hover:text-orange-400 group-hover:translate-x-1 transition-all" />
            </NuxtLink>

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useSanctum } from '#imports'
import OrdersTrendChart from "~/components/charts/OrdersTrendChart.vue"
import DashboardStatCard from "~/components/dashboard/cards/DashboardStatCard.vue"
import ProfileCard from "~/components/dashboard/cards/ProfileCard.vue"

// GSAP imports (client-side only)
let gsap: any = null
if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// Meta
definePageMeta({
  layout: 'dashboard'
})

// Composables
const { user } = useSanctum()

// State
const isRefreshing = ref(false)

// Refs for animations
const dashboardOrb1 = ref<HTMLElement>()
const dashboardOrb2 = ref<HTMLElement>()
const pageHeader = ref<HTMLElement>()
const statsGrid = ref<HTMLElement>()
const systemStatsGrid = ref<HTMLElement>()
const chartsContainer = ref<HTMLElement>()
const quickActionsPanel = ref<HTMLElement>()
const earningsCard = ref<HTMLElement>()
const referralsCard = ref<HTMLElement>()
const ordersCard = ref<HTMLElement>()
const rankCard = ref<HTMLElement>()

let gsapContext: any = null

// Methods
function getGreetingMessage() {
  const hour = new Date().getHours()
  if (hour < 12) return "Good morning!"
  if (hour < 18) return "Good afternoon!"
  return "Good evening!"
}

function getFloatingParticleStyle(index: number) {
  const delay = index * 1.2
  const duration = 8 + (index % 4) * 3

  return {
    left: `${10 + (index * 15) % 80}%`,
    top: `${15 + (index * 18) % 70}%`,
    animationDelay: `${delay}s`,
    animationDuration: `${duration}s`
  }
}

async function refreshDashboard() {
  isRefreshing.value = true

  // Simulate API refresh
  await new Promise(resolve => setTimeout(resolve, 1500))

  // Animate numbers refresh
  animateNumbers()

  isRefreshing.value = false
}

function animateNumbers() {
  if (!process.client || !gsap) return

  const numberElements = document.querySelectorAll('.animated-number')
  numberElements.forEach((element) => {
    const target = parseInt(element.getAttribute('data-value') || '0')
    const obj = { value: 0 }

    gsap.to(obj, {
      value: target,
      duration: 2,
      ease: 'power2.out',
      onUpdate: () => {
        element.textContent = Math.round(obj.value).toLocaleString()
      }
    })
  })
}

function initializeAnimations() {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
    // Floating orbs animation
    if (dashboardOrb1.value && dashboardOrb2.value) {
      gsap.to(dashboardOrb1.value, {
        x: 100,
        y: 50,
        rotation: 180,
        duration: 20,
        repeat: -1,
        yoyo: true,
        ease: 'none'
      })

      gsap.to(dashboardOrb2.value, {
        x: -80,
        y: -60,
        rotation: -120,
        duration: 25,
        repeat: -1,
        yoyo: true,
        ease: 'none'
      })
    }

    // Page entrance animations
    const tl = gsap.timeline()

    // Header animation
    if (pageHeader.value) {
      tl.fromTo(pageHeader.value,
          { y: -50, opacity: 0 },
          { y: 0, opacity: 1, duration: 0.8, ease: 'back.out(1.7)' }
      )
    }

    // Stats cards animation
    const statCards = [earningsCard.value, referralsCard.value, ordersCard.value, rankCard.value].filter(Boolean)
    if (statCards.length > 0) {
      tl.fromTo(statCards,
          { y: 50, opacity: 0, scale: 0.9 },
          {
            y: 0,
            opacity: 1,
            scale: 1,
            duration: 0.6,
            stagger: 0.1,
            ease: 'back.out(1.7)'
          },
          '-=0.6'
      )
    }

    // System stats animation
    if (systemStatsGrid.value) {
      tl.fromTo(systemStatsGrid.value.children,
          { y: 30, opacity: 0 },
          {
            y: 0,
            opacity: 1,
            duration: 0.5,
            stagger: 0.1,
            ease: 'power2.out'
          },
          '-=0.4'
      )
    }

    // Charts container animation
    if (chartsContainer.value) {
      tl.fromTo(chartsContainer.value,
          { y: 50, opacity: 0 },
          { y: 0, opacity: 1, duration: 0.8, ease: 'power2.out' },
          '-=0.3'
      )
    }

    // Quick actions panel
    if (quickActionsPanel.value) {
      tl.fromTo(quickActionsPanel.value,
          { y: 30, opacity: 0 },
          { y: 0, opacity: 1, duration: 0.6, ease: 'power2.out' },
          '-=0.2'
      )
    }

    // Animate numbers after all elements are visible
    tl.call(() => {
      setTimeout(animateNumbers, 500)
    })
  })
}

// Lifecycle
onMounted(() => {
  nextTick(() => {
    setTimeout(initializeAnimations, 100)
  })
})

onUnmounted(() => {
  if (gsapContext) {
    gsapContext.kill()
  }
})
</script>
