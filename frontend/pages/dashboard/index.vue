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
        <!-- Date Filter Dropdown (Mobile) -->
        <div v-if="SHOW_DATE_FILTER" class="relative">
          <button
              @click="showDateFilter = !showDateFilter"
              class="w-full flex items-center justify-between p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-medium shadow-sm hover:shadow-md transition-all"
          >
            <div class="flex items-center gap-2">
              <Icon name="mdi:calendar-filter" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
              <span class="text-slate-700 dark:text-slate-300">
                {{ dateFilterLabel }}
              </span>
            </div>
            <Icon
                :name="showDateFilter ? 'mdi:chevron-up' : 'mdi:chevron-down'"
                class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform duration-200"
                :class="{ 'rotate-180': showDateFilter }"
            />
          </button>

          <!-- Dropdown Content -->
          <Transition
              enter-active-class="transition ease-out duration-200"
              enter-from-class="opacity-0 scale-95"
              enter-to-class="opacity-100 scale-100"
              leave-active-class="transition ease-in duration-150"
              leave-from-class="opacity-100 scale-100"
              leave-to-class="opacity-0 scale-95"
          >
            <div v-if="showDateFilter" class="absolute z-50 w-full mt-2 p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl space-y-3">
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">From Date</label>
                <input
                    v-model="dateFrom"
                    type="date"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition-all"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">To Date</label>
                <input
                    v-model="dateTo"
                    type="date"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition-all"
                />
              </div>
              <div class="flex gap-2">
                <button
                    @click="applyDateFilter"
                    :disabled="isLoadingStats"
                    class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white rounded-lg text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm hover:shadow-md"
                >
                  {{ isLoadingStats ? 'Loading...' : 'Apply' }}
                </button>
                <button
                    @click="clearDateFilter"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-semibold transition-all"
                >
                  Clear
                </button>
              </div>
            </div>
          </Transition>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <DashboardStatCard
              icon="mdi:currency-inr"
              :label="statsData.total_earnings?.label || 'Total Earnings'"
              :value="statsData.total_earnings?.value || '₹0'"
              :change="statsData.total_earnings?.change"
              :trend="statsData.total_earnings?.trend || 'neutral'"
              color="green"
              :loading="isLoadingStats"
          />

          <DashboardStatCard
              icon="mdi:account-group"
              :label="statsData.total_referrals?.label || 'My Referrals'"
              :value="statsData.total_referrals?.value || '0'"
              :change="statsData.total_referrals?.change"
              :trend="statsData.total_referrals?.trend || 'neutral'"
              color="blue"
              :loading="isLoadingStats"
          />

          <DashboardStatCard
              icon="mdi:shopping"
              :label="statsData.total_orders?.label || 'My Orders'"
              :value="statsData.total_orders?.value || '0'"
              :change="statsData.total_orders?.change"
              :trend="statsData.total_orders?.trend || 'neutral'"
              color="purple"
              :loading="isLoadingStats"
          />

          <DashboardStatCard
              icon="mdi:trophy"
              :label="statsData.current_rank?.label || 'Current Rank'"
              :value="statsData.current_rank?.value || 'N/A'"
              :change="statsData.current_rank?.change"
              :trend="statsData.current_rank?.trend || 'neutral'"
              color="orange"
              :loading="isLoadingStats"
          />
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
              {{ greetingMessage }} Here's your personal performance overview.
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

          <!-- Date Filter Dropdown Section (Desktop) -->
          <section v-if="SHOW_DATE_FILTER" class="relative">
            <button
                @click="showDateFilter = !showDateFilter"
                class="w-full flex items-center justify-between p-4 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-md">
                  <Icon name="mdi:calendar-filter" class="w-5 h-5 text-white" />
                </div>
                <div class="text-left">
                  <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ dateFilterLabel }}</h3>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Click to filter statistics by date range</p>
                </div>
              </div>
              <Icon
                  :name="showDateFilter ? 'mdi:chevron-up' : 'mdi:chevron-down'"
                  class="w-6 h-6 text-slate-500 dark:text-slate-400 transition-transform duration-200"
                  :class="{ 'rotate-180': showDateFilter }"
              />
            </button>

            <!-- Dropdown Content -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-4"
            >
              <div v-if="showDateFilter" class="mt-4 p-6 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">From Date</label>
                    <input
                        v-model="dateFrom"
                        type="date"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition-all shadow-sm"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">To Date</label>
                    <input
                        v-model="dateTo"
                        type="date"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition-all shadow-sm"
                    />
                  </div>
                  <div class="flex items-end gap-2">
                    <button
                        @click="applyDateFilter"
                        :disabled="isLoadingStats"
                        class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md hover:shadow-lg"
                    >
                      {{ isLoadingStats ? 'Loading...' : 'Apply Filter' }}
                    </button>
                    <button
                        @click="clearDateFilter"
                        class="px-4 py-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-semibold transition-all shadow-sm"
                    >
                      Clear
                    </button>
                  </div>
                </div>
              </div>
            </Transition>
          </section>

          <!-- Stats Grid -->
          <section>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              <DashboardStatCard
                  icon="mdi:currency-inr"
                  :label="statsData.total_earnings?.label || 'Total Earnings'"
                  :value="statsData.total_earnings?.value || '₹0'"
                  :change="statsData.total_earnings?.change"
                  :trend="statsData.total_earnings?.trend || 'neutral'"
                  color="green"
                  :loading="isLoadingStats"
              />

              <DashboardStatCard
                  icon="mdi:cash-multiple"
                  :label="statsData.direct_earnings?.label || 'Direct Earnings'"
                  :value="statsData.direct_earnings?.value || '₹0'"
                  :change="statsData.direct_earnings?.change"
                  :trend="statsData.direct_earnings?.trend || 'neutral'"
                  color="purple"
                  :loading="isLoadingStats"
              />

              <DashboardStatCard
                  icon="mdi:account-network"
                  :label="statsData.team_earnings?.label || 'Team Earnings'"
                  :value="statsData.team_earnings?.value || '₹0'"
                  :change="statsData.team_earnings?.change"
                  :trend="statsData.team_earnings?.trend || 'neutral'"
                  color="orange"
                  :loading="isLoadingStats"
              />

              <DashboardStatCard
                  icon="mdi:wallet"
                  :label="statsData.wallet_balance?.label || 'Wallet Balance'"
                  :value="statsData.wallet_balance?.value || '₹0'"
                  :change="statsData.wallet_balance?.change"
                  :trend="statsData.wallet_balance?.trend || 'neutral'"
                  color="emerald"
                  :loading="isLoadingStats"
              />

              <DashboardStatCard
                  icon="mdi:account-group"
                  :label="statsData.total_referrals?.label || 'My Referrals'"
                  :value="statsData.total_referrals?.value || '0'"
                  :change="statsData.total_referrals?.change"
                  :trend="statsData.total_referrals?.trend || 'neutral'"
                  color="blue"
                  :loading="isLoadingStats"
              />

              <DashboardStatCard
                  icon="mdi:shopping"
                  :label="statsData.total_orders?.label || 'My Orders'"
                  :value="statsData.total_orders?.value || '0'"
                  :change="statsData.total_orders?.change"
                  :trend="statsData.total_orders?.trend || 'neutral'"
                  color="indigo"
                  :loading="isLoadingStats"
              />

              <DashboardStatCard
                  icon="mdi:cart-check"
                  :label="statsData.completed_orders?.label || 'Completed Orders'"
                  :value="statsData.completed_orders?.value || '0'"
                  :change="statsData.completed_orders?.change"
                  :trend="statsData.completed_orders?.trend || 'neutral'"
                  color="teal"
                  :loading="isLoadingStats"
              />

              <DashboardStatCard
                  icon="mdi:trophy"
                  :label="statsData.current_rank?.label || 'Current Rank'"
                  :value="statsData.current_rank?.value || 'N/A'"
                  :change="statsData.current_rank?.change"
                  :trend="statsData.current_rank?.trend || 'neutral'"
                  color="cyan"
                  :loading="isLoadingStats"
              />
            </div>
          </section>

          <!-- Charts -->
          <ClientOnly v-if="showCharts">
            <template #fallback>
              <div class="h-96 bg-slate-100 dark:bg-slate-800 rounded-2xl animate-pulse"></div>
            </template>
            <section>
              <div class="flex items-center gap-3 mb-6">
                <Icon name="mdi:chart-timeline-variant" class="w-6 h-6 text-slate-600 dark:text-slate-400" />
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">My Orders Trend Analysis</h3>
              </div>
              <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-lg overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                      <Icon name="mdi:chart-line" class="w-6 h-6 text-white" />
                    </div>
                    <div>
                      <h3 class="text-lg font-bold text-slate-900 dark:text-white">My Orders Trend</h3>
                      <p class="text-sm text-slate-500 dark:text-slate-400">Your completed and confirmed orders over time</p>
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
import { ref, computed, onMounted, reactive } from 'vue'
import { useSanctum, useSanctumFetch, useRuntimeConfig } from '#imports'

// ✅ Lazy components
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

const config = useRuntimeConfig()
const { user } = useSanctum()

// ✅ CONSTANT: Toggle date filter visibility
const SHOW_DATE_FILTER = true // Set to false to hide date filter

// State
const isRefreshing = ref(false)
const isLoadingStats = ref(true)
const showDateFilter = ref(false)
const showCharts = ref(false)
const showQuickActions = ref(false)
const showBackground = ref(false)

// Date Filter State
const dateFrom = ref('')
const dateTo = ref('')

// Stats Data
const statsData = reactive({
  total_earnings: { label: 'Total Earnings', value: '₹0', change: '+0%', trend: 'neutral' },
  direct_earnings: { label: 'Direct Earnings', value: '₹0', change: '+0%', trend: 'neutral' },
  team_earnings: { label: 'Team Earnings', value: '₹0', change: '+0%', trend: 'neutral' },
  wallet_balance: { label: 'Wallet Balance', value: '₹0', change: '+0%', trend: 'neutral' },
  total_referrals: { label: 'My Referrals', value: '0', change: '+0%', trend: 'neutral' },
  total_orders: { label: 'My Orders', value: '0', change: '+0%', trend: 'neutral' },
  completed_orders: { label: 'Completed Orders', value: '0', change: '+0%', trend: 'neutral' },
  current_rank: { label: 'Current Rank', value: 'N/A', change: null, trend: 'neutral' }
})

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

const dateFilterLabel = computed(() => {
  if (dateFrom.value && dateTo.value) {
    return `Filtered: ${formatDate(dateFrom.value)} - ${formatDate(dateTo.value)}`
  } else if (dateFrom.value) {
    return `From: ${formatDate(dateFrom.value)}`
  } else if (dateTo.value) {
    return `Until: ${formatDate(dateTo.value)}`
  }
  return 'Filter Statistics by Date'
})

// Methods
function formatDate(dateStr: string): string {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

async function fetchDashboardStats() {
  try {
    isLoadingStats.value = true

    const url = `${config.public.apiBase}/account/stats/dashboard`
    const params: any = {}

    if (dateFrom.value) params.from = dateFrom.value
    if (dateTo.value) params.to = dateTo.value

    const response = await useSanctumFetch(url, {
      method: 'GET',
      params
    })

    if (response?.data) {
      const data = response.data

      // Map API response to statsData
      Object.keys(statsData).forEach(key => {
        if (data[key]) {
          statsData[key] = {
            label: data[key].label || statsData[key].label,
            value: data[key].value || statsData[key].value,
            change: data[key].change || null,
            trend: data[key].trend || 'neutral'
          }
        }
      })
    }
  } catch (error) {
    console.error('Failed to fetch dashboard stats:', error)
  } finally {
    isLoadingStats.value = false
  }
}

async function applyDateFilter() {
  showDateFilter.value = false
  await fetchDashboardStats()
}

function clearDateFilter() {
  dateFrom.value = ''
  dateTo.value = ''
  showDateFilter.value = false
  fetchDashboardStats()
}

async function refreshDashboard() {
  isRefreshing.value = true
  await fetchDashboardStats()
  isRefreshing.value = false
}

// ✅ Progressive loading
async function loadProgressively() {
  await nextTick()

  // Fetch stats
  await fetchDashboardStats()

  setTimeout(() => { showQuickActions.value = true }, 300)

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
