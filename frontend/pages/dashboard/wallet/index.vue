<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <!-- Floating Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div ref="orb1" class="absolute -top-32 -right-32 w-96 h-96 bg-green-400/10 dark:bg-green-400/5 rounded-full blur-3xl opacity-60 animate-pulse"></div>
      <div ref="orb2" class="absolute -bottom-32 -left-32 w-80 h-80 bg-blue-400/10 dark:bg-blue-400/5 rounded-full blur-3xl opacity-50 animate-pulse"></div>

      <!-- Floating Elements -->
      <div v-for="i in 8" :key="i" class="absolute animate-bounce opacity-30" :style="getFloatingElementStyle(i)">
        <div class="w-2 h-2 bg-blue-500/20 dark:bg-blue-400/10 rounded-full"></div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center min-h-screen relative z-10">
      <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-8 shadow-xl">
        <div class="flex flex-col items-center gap-4">
          <div class="w-16 h-16 border-4 border-green-200 dark:border-green-800 border-t-green-600 dark:border-t-green-400 rounded-full animate-spin"></div>
          <p class="text-slate-600 dark:text-slate-400 font-semibold">Loading your wallet...</p>
        </div>
      </div>
    </div>

    <!-- Wallet Locked State -->
    <div v-else-if="!wallet" class="flex items-center justify-center min-h-screen relative z-10">
      <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-12 shadow-xl max-w-md mx-auto text-center">
        <!-- Lock Animation -->
        <div class="relative mb-8 inline-block">
          <Icon name="mdi:wallet-outline" class="w-24 h-24 text-slate-300 dark:text-slate-600" />
          <div class="absolute -bottom-2 -right-2 w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
            <Icon name="mdi:lock" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
          </div>
        </div>

        <!-- Content -->
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">Wallet Locked</h2>
        <p class="text-slate-600 dark:text-slate-400 mb-8 leading-relaxed">
          Unlock your secure wallet to start managing your funds, making payments, and tracking transactions.
        </p>

        <!-- Unlock Button -->
        <button
            @click="unlockWallet"
            :disabled="processing"
            class="group relative inline-flex items-center justify-center gap-3 px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 disabled:opacity-50 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden"
        >
          <div class="flex items-center gap-3 relative z-10">
            <Icon
                :name="processing ? 'mdi:loading' : 'mdi:lock-open'"
                class="w-5 h-5 transition-transform duration-300"
                :class="{ 'animate-spin': processing }"
            />
            <span>{{ processing ? 'Creating Wallet...' : 'Unlock Wallet' }}</span>
          </div>

          <!-- Shimmer Effect -->
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
        </button>
      </div>
    </div>

    <!-- Main Wallet Interface -->
    <div v-else class="relative z-10 max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-8">

      <!-- Wallet Home View -->
      <div v-if="currentView === 'home'" class="space-y-8">

        <!-- Enhanced Wallet Card -->
        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl overflow-hidden">
          <!-- Card Background -->
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-green-500/10 dark:from-blue-400/5 dark:to-green-400/5 opacity-50"></div>

          <div class="relative z-10 p-6 sm:p-8">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-8">
              <!-- Wallet Info -->
              <div class="flex-1">
                <div class="mb-2 text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">My Wallet</div>

                <!-- Balance -->
                <div class="mb-6">
                  <div class="text-4xl sm:text-5xl font-black text-slate-900 dark:text-white mb-2">
                    ₹{{ balance.toLocaleString('en-IN', { maximumFractionDigits: 2 }) }}
                  </div>
                  <div class="flex items-center gap-2">
                    <Icon name="mdi:trending-up" class="w-4 h-4 text-green-500" />
                    <span class="text-green-500 text-sm font-medium">Available Balance</span>
                  </div>
                </div>

                <!-- Wallet Details -->
                <div class="space-y-3">
                  <div class="flex items-center gap-2 text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Wallet ID:</span>
                    <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ formatWalletId(wallet.uuid) }}</span>
                  </div>
                  <div class="flex items-center gap-2 text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Linked Account:</span>
                    <span class="font-semibold text-slate-900 dark:text-white">{{ maskedAccount }}</span>
                    <NuxtLink to="/dashboard/wallet/beneficiary" class="inline-flex items-center gap-1 ml-2 text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                      <Icon name="mdi:pencil" class="w-3 h-3" />
                      <span>Manage</span>
                    </NuxtLink>
                  </div>
                </div>
              </div>

              <!-- QR Code -->
              <div v-if="walletQr" class="flex-shrink-0 text-center">
                <div
                    @click="showQrModal = true"
                    class="group relative w-28 h-28 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-2xl cursor-pointer transition-all duration-300 hover:scale-105 hover:shadow-lg overflow-hidden"
                >
                  <img :src="walletQr" alt="Wallet QR" class="w-full h-full object-contain p-2" />
                  <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <Icon name="mdi:magnify-plus" class="w-6 h-6 text-white" />
                  </div>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Tap to enlarge</p>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-8">
              <button @click="go('add')" class="flex flex-col items-center gap-3 p-4 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all hover:-translate-y-1">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                  <Icon name="mdi:plus-circle" class="w-6 h-6 text-white" />
                </div>
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Add Money</span>
              </button>

              <button @click="openWithdraw" class="flex flex-col items-center gap-3 p-4 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all hover:-translate-y-1">
                <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                  <Icon name="mdi:bank-transfer-out" class="w-6 h-6 text-white" />
                </div>
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Withdraw</span>
              </button>

              <button @click="go('send')" class="flex flex-col items-center gap-3 p-4 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all hover:-translate-y-1">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                  <Icon name="mdi:send-circle" class="w-6 h-6 text-white" />
                </div>
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Send Money</span>
              </button>

              <button @click="go('pin')" class="flex flex-col items-center gap-3 p-4 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all hover:-translate-y-1">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                  <Icon name="mdi:shield-key" class="w-6 h-6 text-white" />
                </div>
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Security</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
          <!-- Available Balance -->
          <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                <Icon name="mdi:wallet" class="w-6 h-6 text-green-600 dark:text-green-400" />
              </div>
              <div class="flex items-center gap-1 text-xs font-semibold text-green-600 dark:text-green-400">
                <Icon name="mdi:trending-up" class="w-3 h-3" />
                <span>+5.2%</span>
              </div>
            </div>
            <h3 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-2">Available Balance</h3>
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">₹{{ balance.toLocaleString('en-IN') }}</div>
          </div>

          <!-- Today's Activity -->
          <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                <Icon name="mdi:calendar-today" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
              </div>
              <div class="flex items-center gap-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <Icon name="mdi:minus" class="w-3 h-3" />
                <span>Today</span>
              </div>
            </div>
            <h3 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-2">Today's Activity</h3>
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">₹{{ (todayActivity || 0).toLocaleString('en-IN') }}</div>
          </div>

          <!-- Total Transactions -->
          <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                <Icon name="mdi:swap-horizontal" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
              </div>
              <div class="flex items-center gap-1 text-xs font-semibold text-purple-600 dark:text-purple-400">
                <Icon name="mdi:trending-up" class="w-3 h-3" />
                <span>+12%</span>
              </div>
            </div>
            <h3 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-2">Total Transactions</h3>
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ transactions.length }}</div>
          </div>

          <!-- Last Transaction -->
          <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center">
                <Icon name="mdi:clock-outline" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
              </div>
              <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Recent</div>
            </div>
            <h3 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-2">Last Transaction</h3>
            <div class="text-sm font-bold text-orange-600 dark:text-orange-400">{{ lastTransaction || 'No transactions' }}</div>
          </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

          <!-- Spending Analytics -->
          <div class="xl:col-span-2 bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                  <Icon name="mdi:chart-line" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-lg font-bold text-slate-900 dark:text-white">Spending Analytics</h3>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Track your financial patterns</p>
                </div>
              </div>
              <select v-model="filters.type" @change="loadTransactions(true)" class="px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm">
                <option value="all">All Types</option>
                <option value="debit">Expenses</option>
                <option value="credit">Income</option>
              </select>
            </div>

            <div class="p-6 h-64 flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-700/50 dark:to-slate-800/50">
              <div class="text-center">
                <Icon name="mdi:chart-areaspline" class="w-16 h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" />
                <p class="text-slate-500 dark:text-slate-400">Analytics chart will be integrated here</p>

                <!-- Mock Chart -->
                <div class="flex items-end justify-center gap-2 mt-6 h-16">
                  <div v-for="i in 5" :key="i" class="w-6 bg-gradient-to-t from-blue-600 to-indigo-500 rounded-t animate-pulse" :style="{ height: `${20 + Math.random() * 60}%`, animationDelay: `${i * 0.2}s` }"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Transactions -->
          <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                  <Icon name="mdi:history" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-lg font-bold text-slate-900 dark:text-white">Recent Activity</h3>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Latest transactions</p>
                </div>
              </div>
              <button @click="go('transactions')" class="flex items-center gap-1 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-semibold">
                <span>View All</span>
                <Icon name="mdi:arrow-right" class="w-4 h-4" />
              </button>
            </div>

            <div class="p-6">
              <div class="space-y-4 max-h-80 overflow-y-auto">
                <div
                    v-for="tx in transactions.slice(0, 5)"
                    :key="tx.uuid"
                    class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                >
                  <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="getTransactionIconClass(tx.type)">
                    <Icon :name="getTransactionIcon(tx.type)" class="w-4 h-4" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-slate-900 dark:text-white text-sm">{{ tx.purpose || getTransactionLabel(tx.type) }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ formatTransactionDate(tx.created_at) }}</p>
                  </div>
                  <div class="font-bold text-sm" :class="getAmountClass(tx.type)">
                    {{ tx.type === 'credit' ? '+' : '-' }}₹{{ Number(tx.amount).toLocaleString('en-IN') }}
                  </div>
                </div>

                <div v-if="transactions.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
                  <Icon name="mdi:receipt-text-outline" class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-2" />
                  <p class="text-slate-500 dark:text-slate-400">No transactions yet</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Money View -->
      <div v-else-if="currentView === 'add'" class="max-w-2xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
          <button @click="backHome" class="flex items-center gap-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
            <Icon name="mdi:arrow-left" class="w-5 h-5" />
            <span>Back to Wallet</span>
          </button>
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
              <Icon name="mdi:plus-circle" class="w-6 h-6 text-white" />
            </div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Add Money</h2>
          </div>
        </div>

        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-8 shadow-xl space-y-6">
          <!-- Amount Input -->
          <div>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
              <Icon name="mdi:currency-inr" class="w-4 h-4" />
              <span>Amount to Add</span>
            </label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-semibold pointer-events-none">₹</span>
              <input
                  v-model="addForm.amount"
                  type="number"
                  min="1"
                  step="0.01"
                  placeholder="0.00"
                  class="w-full pl-10 pr-4 py-4 text-xl font-semibold bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
              />
            </div>

            <!-- Quick Amounts -->
            <div class="flex flex-wrap gap-2 mt-4">
              <button
                  v-for="amount in quickAmounts"
                  :key="amount"
                  @click="addForm.amount = amount.toString()"
                  class="px-4 py-2 rounded-lg border-2 transition-all"
                  :class="addForm.amount === amount.toString()
                  ? 'border-green-500 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400'
                  : 'border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 hover:border-green-300 dark:hover:border-green-600'"
              >
                ₹{{ amount }}
              </button>
            </div>
          </div>

          <!-- Info -->
          <div class="space-y-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
            <div class="flex items-center gap-2 text-sm">
              <Icon name="mdi:information" class="w-4 h-4 text-blue-500" />
              <span class="text-blue-700 dark:text-blue-300">You'll be redirected to secure payment gateway</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <Icon name="mdi:shield-check" class="w-4 h-4 text-green-500" />
              <span class="text-green-700 dark:text-green-300">All transactions are encrypted and secure</span>
            </div>
          </div>

          <!-- Submit Button -->
          <button
              @click="addMoney"
              :disabled="processing || !addForm.amount || Number(addForm.amount) <= 0"
              class="w-full flex items-center justify-center gap-3 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
          >
            <Icon
                :name="processing ? 'mdi:loading' : 'mdi:credit-card'"
                class="w-5 h-5"
                :class="{ 'animate-spin': processing }"
            />
            <span>{{ processing ? 'Processing...' : 'Proceed to Payment' }}</span>
          </button>
        </div>
      </div>

      <!-- Send Money View -->
      <div v-else-if="currentView === 'send'" class="max-w-2xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
          <button @click="backHome" class="flex items-center gap-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
            <Icon name="mdi:arrow-left" class="w-5 h-5" />
            <span>Back to Wallet</span>
          </button>
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
              <Icon name="mdi:send-circle" class="w-6 h-6 text-white" />
            </div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Send Money</h2>
          </div>
        </div>

        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-8 shadow-xl space-y-6">
          <!-- Balance Display -->
          <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
            <span class="text-blue-700 dark:text-blue-300 font-medium">Available Balance:</span>
            <span class="text-blue-800 dark:text-blue-200 font-bold text-lg">₹{{ balance.toLocaleString('en-IN') }}</span>
          </div>

          <!-- Amount -->
          <div>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
              <Icon name="mdi:currency-inr" class="w-4 h-4" />
              <span>Amount</span>
              <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-semibold pointer-events-none">₹</span>
              <input
                  v-model="sendForm.amount"
                  type="number"
                  min="1"
                  step="0.01"
                  placeholder="0.00"
                  class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': sendErrors.amount }"
              />
            </div>
            <p v-if="sendErrors.amount" class="flex items-center gap-1 mt-1 text-sm text-red-500">
              <Icon name="mdi:alert-circle" class="w-4 h-4" />
              <span>{{ sendErrors.amount }}</span>
            </p>
          </div>

          <!-- Recipient -->
          <div>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
              <Icon name="mdi:account-outline" class="w-4 h-4" />
              <span>Recipient Wallet ID</span>
              <span class="text-red-500">*</span>
            </label>
            <div class="flex gap-2">
              <input
                  v-model="sendForm.recipient_uuid"
                  type="text"
                  placeholder="Enter wallet UUID or scan QR"
                  class="flex-1 px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': sendErrors.recipient_uuid }"
              />
              <button @click="openScanner" class="px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-colors">
                <Icon name="mdi:qrcode-scan" class="w-5 h-5" />
              </button>
            </div>
            <p v-if="sendErrors.recipient_uuid" class="flex items-center gap-1 mt-1 text-sm text-red-500">
              <Icon name="mdi:alert-circle" class="w-4 h-4" />
              <span>{{ sendErrors.recipient_uuid }}</span>
            </p>
          </div>

          <!-- Purpose -->
          <div>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
              <Icon name="mdi:message-text" class="w-4 h-4" />
              <span>Purpose</span>
              <span class="text-red-500">*</span>
            </label>
            <textarea
                v-model="sendForm.purpose"
                rows="3"
                placeholder="Add a note about this transaction"
                maxlength="200"
                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
                :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': sendErrors.purpose }"
            ></textarea>
            <div class="flex items-center justify-between mt-1">
              <p v-if="sendErrors.purpose" class="flex items-center gap-1 text-sm text-red-500">
                <Icon name="mdi:alert-circle" class="w-4 h-4" />
                <span>{{ sendErrors.purpose }}</span>
              </p>
              <div class="text-xs text-slate-500 dark:text-slate-400">{{ sendForm.purpose.length }}/200</div>
            </div>
          </div>

          <!-- PIN -->
          <div>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
              <Icon name="mdi:shield-key" class="w-4 h-4" />
              <span>Transaction PIN</span>
              <span class="text-red-500">*</span>
            </label>
            <input
                v-model="sendForm.pin"
                type="password"
                inputmode="numeric"
                minlength="4"
                maxlength="6"
                placeholder="Enter your wallet PIN"
                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all tracking-widest"
                :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': sendErrors.pin }"
            />
            <p v-if="sendErrors.pin" class="flex items-center gap-1 mt-1 text-sm text-red-500">
              <Icon name="mdi:alert-circle" class="w-4 h-4" />
              <span>{{ sendErrors.pin }}</span>
            </p>
          </div>

          <!-- Submit Button -->
          <button
              @click="sendMoney"
              :disabled="processing || !isValidSendForm"
              class="w-full flex items-center justify-center gap-3 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
          >
            <Icon
                :name="processing ? 'mdi:loading' : 'mdi:send'"
                class="w-5 h-5"
                :class="{ 'animate-spin': processing }"
            />
            <span>{{ processing ? 'Sending...' : 'Send Money' }}</span>
          </button>
        </div>
      </div>

      <!-- Change PIN View -->
      <div v-else-if="currentView === 'pin'" class="max-w-2xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
          <button @click="backHome" class="flex items-center gap-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
            <Icon name="mdi:arrow-left" class="w-5 h-5" />
            <span>Back to Wallet</span>
          </button>
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
              <Icon name="mdi:shield-key" class="w-6 h-6 text-white" />
            </div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Change PIN</h2>
          </div>
        </div>

        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-8 shadow-xl space-y-6">
          <!-- Security Info -->
          <div class="space-y-3 p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl">
            <div class="flex items-center gap-2 text-sm">
              <Icon name="mdi:information" class="w-4 h-4 text-purple-500" />
              <span class="text-purple-700 dark:text-purple-300">Your PIN secures all wallet transactions</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <Icon name="mdi:shield-check" class="w-4 h-4 text-green-500" />
              <span class="text-green-700 dark:text-green-300">Choose a strong 4-6 digit PIN</span>
            </div>
          </div>

          <!-- New PIN -->
          <div>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
              <Icon name="mdi:key" class="w-4 h-4" />
              <span>New PIN</span>
              <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input
                  v-model="pinForm.pin"
                  :type="showPin ? 'text' : 'password'"
                  inputmode="numeric"
                  minlength="4"
                  maxlength="6"
                  placeholder="Enter new PIN"
                  class="w-full px-4 py-3 pr-12 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all tracking-widest"
              />
              <button
                  type="button"
                  @click="showPin = !showPin"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
              >
                <Icon :name="showPin ? 'mdi:eye-off' : 'mdi:eye'" class="w-5 h-5" />
              </button>
            </div>
          </div>

          <!-- Confirm PIN -->
          <div>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
              <Icon name="mdi:key-plus" class="w-4 h-4" />
              <span>Confirm PIN</span>
              <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input
                  v-model="pinForm.confirmPin"
                  :type="showConfirmPin ? 'text' : 'password'"
                  inputmode="numeric"
                  minlength="4"
                  maxlength="6"
                  placeholder="Confirm new PIN"
                  class="w-full px-4 py-3 pr-12 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all tracking-widest"
                  :class="{ 'border-red-500': pinForm.pin && pinForm.confirmPin && pinForm.pin !== pinForm.confirmPin }"
              />
              <button
                  type="button"
                  @click="showConfirmPin = !showConfirmPin"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
              >
                <Icon :name="showConfirmPin ? 'mdi:eye-off' : 'mdi:eye'" class="w-5 h-5" />
              </button>
            </div>
            <p v-if="pinForm.pin && pinForm.confirmPin && pinForm.pin !== pinForm.confirmPin" class="flex items-center gap-1 mt-1 text-sm text-red-500">
              <Icon name="mdi:alert-circle" class="w-4 h-4" />
              <span>PINs do not match</span>
            </p>
          </div>

          <!-- Submit Button -->
          <button
              @click="changePin"
              :disabled="processing || !pinForm.pin || pinForm.pin !== pinForm.confirmPin"
              class="w-full flex items-center justify-center gap-3 py-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
          >
            <Icon
                :name="processing ? 'mdi:loading' : 'mdi:shield-check'"
                class="w-5 h-5"
                :class="{ 'animate-spin': processing }"
            />
            <span>{{ processing ? 'Updating...' : 'Update PIN' }}</span>
          </button>
        </div>
      </div>

      <!-- Transactions View -->
      <div v-else-if="currentView === 'transactions'" class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div class="flex items-center gap-4">
            <button @click="backHome" class="flex items-center gap-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
              <Icon name="mdi:arrow-left" class="w-5 h-5" />
              <span>Back</span>
            </button>
            <div>
              <h2 class="text-2xl font-bold text-slate-900 dark:text-white">All Transactions</h2>
              <p class="text-slate-500 dark:text-slate-400">Complete transaction history</p>
            </div>
          </div>

          <!-- Filters -->
          <div class="flex gap-2 flex-wrap">
            <select v-model="filters.type" @change="loadTransactions(true)" class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm">
              <option value="all">All Types</option>
              <option value="credit">Credits</option>
              <option value="debit">Debits</option>
            </select>
            <select v-model="filters.status" @change="loadTransactions(true)" class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm">
              <option value="all">All Status</option>
              <option value="success">Success</option>
              <option value="pending">Pending</option>
              <option value="failed">Failed</option>
            </select>
            <select v-model="sort" @change="loadTransactions(true)" class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm">
              <option value="desc">Newest First</option>
              <option value="asc">Oldest First</option>
            </select>
          </div>
        </div>

        <!-- Transactions List -->
        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl overflow-hidden">
          <!-- Desktop Table -->
          <div class="hidden lg:block">
            <div class="grid grid-cols-5 gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-400">
              <div class="flex items-center gap-2">
                <Icon name="mdi:swap-horizontal" class="w-4 h-4" />
                <span>Type</span>
              </div>
              <div class="flex items-center gap-2">
                <Icon name="mdi:message-text" class="w-4 h-4" />
                <span>Purpose</span>
              </div>
              <div class="flex items-center gap-2">
                <Icon name="mdi:currency-inr" class="w-4 h-4" />
                <span>Amount</span>
              </div>
              <div class="flex items-center gap-2">
                <Icon name="mdi:check-circle" class="w-4 h-4" />
                <span>Status</span>
              </div>
              <div class="flex items-center gap-2">
                <Icon name="mdi:calendar" class="w-4 h-4" />
                <span>Date</span>
              </div>
            </div>

            <div class="divide-y divide-slate-200 dark:divide-slate-700">
              <div
                  v-for="tx in transactions"
                  :key="tx.uuid"
                  class="grid grid-cols-5 gap-4 p-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
              >
                <div class="flex items-center gap-2">
                  <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="getTransactionTypeClass(tx.type)">
                    <Icon :name="getTransactionIcon(tx.type)" class="w-3 h-3 mr-1" />
                    {{ tx.type }}
                  </span>
                </div>
                <div class="font-medium text-slate-900 dark:text-white">{{ tx.purpose || 'N/A' }}</div>
                <div class="font-bold" :class="getAmountClass(tx.type)">
                  {{ tx.type === 'credit' ? '+' : '-' }}₹{{ Number(tx.amount).toLocaleString('en-IN') }}
                </div>
                <div>
                  <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="getStatusClass(tx.status)">
                    <Icon :name="getStatusIcon(tx.status)" class="w-3 h-3 mr-1" />
                    {{ tx.status }}
                  </span>
                </div>
                <div class="text-sm text-slate-500 dark:text-slate-400">{{ formatTransactionDate(tx.created_at) }}</div>
              </div>
            </div>
          </div>

          <!-- Mobile Cards -->
          <div class="lg:hidden p-4 space-y-4">
            <div
                v-for="tx in transactions"
                :key="tx.uuid"
                class="p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
            >
              <div class="flex items-center justify-between mb-3">
                <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="getTransactionTypeClass(tx.type)">
                  <Icon :name="getTransactionIcon(tx.type)" class="w-3 h-3 mr-1" />
                  {{ tx.type }}
                </span>
                <div class="font-bold" :class="getAmountClass(tx.type)">
                  {{ tx.type === 'credit' ? '+' : '-' }}₹{{ Number(tx.amount).toLocaleString('en-IN') }}
                </div>
              </div>
              <p class="font-medium text-slate-900 dark:text-white mb-2">{{ tx.purpose || 'No description' }}</p>
              <div class="flex items-center justify-between text-sm">
                <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="getStatusClass(tx.status)">
                  <Icon :name="getStatusIcon(tx.status)" class="w-3 h-3 mr-1" />
                  {{ tx.status }}
                </span>
                <span class="text-slate-500 dark:text-slate-400">{{ formatTransactionDate(tx.created_at) }}</span>
              </div>
            </div>
          </div>

          <!-- Load More -->
          <div v-if="hasMore" class="p-4 text-center border-t border-slate-200 dark:border-slate-700">
            <button @click="loadTransactions()" class="flex items-center gap-2 mx-auto px-4 py-2 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-lg transition-colors">
              <Icon name="mdi:refresh" class="w-4 h-4" />
              <span>Load More</span>
            </button>
          </div>

          <!-- Empty State -->
          <div v-if="transactions.length === 0" class="p-12 text-center">
            <Icon name="mdi:receipt-text-outline" class="w-16 h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" />
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">No transactions found</h3>
            <p class="text-slate-500 dark:text-slate-400">Your transaction history will appear here</p>
          </div>
        </div>
      </div>
    </div>

    <!-- QR Modal -->
    <div v-if="showQrModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 max-w-md w-full" @click.stop>
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
              <Icon name="mdi:qrcode" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-slate-900 dark:text-white">My Wallet QR</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">Share this QR to receive payments</p>
            </div>
          </div>
          <button @click="showQrModal = false" class="w-8 h-8 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
            <Icon name="mdi:close" class="w-5 h-5" />
          </button>
        </div>

        <div class="bg-slate-50 dark:bg-slate-700 rounded-xl p-8 mb-4 text-center">
          <img :src="walletQr as string" alt="Wallet QR" class="w-48 h-48 mx-auto rounded-lg" />
        </div>

        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
          <div class="text-xs font-semibold text-blue-600 dark:text-blue-400 mb-1">Wallet ID:</div>
          <div class="font-mono text-sm text-slate-900 dark:text-white break-all">{{ wallet.uuid }}</div>
        </div>
      </div>
    </div>

    <!-- Withdraw Modal -->
    <div v-if="withdrawOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 max-w-lg w-full" @click.stop>
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center">
              <Icon name="mdi:bank-transfer-out" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-slate-900 dark:text-white">Withdraw Money</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">Transfer to your linked account</p>
            </div>
          </div>
          <button @click="closeWithdraw" class="w-8 h-8 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
            <Icon name="mdi:close" class="w-5 h-5" />
          </button>
        </div>

        <!-- Beneficiary Info -->
        <div class="mb-6 p-4 bg-slate-50 dark:bg-slate-700 rounded-xl">
          <h4 class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
            <Icon name="mdi:bank" class="w-4 h-4" />
            <span>Linked Account</span>
          </h4>

          <div class="relative p-3 bg-white dark:bg-slate-800 rounded-lg border">
            <div v-if="isUpiBeneficiary">
              <div class="flex items-center gap-2 text-sm font-medium text-purple-600 dark:text-purple-400 mb-1">
                <Icon name="mdi:cellphone" class="w-4 h-4" />
                <span>UPI Account</span>
              </div>
              <p class="text-slate-900 dark:text-white">{{ defaultBeneficiary?.upi_handle }}</p>
            </div>
            <div v-else>
              <div class="flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 mb-1">
                <Icon name="mdi:bank" class="w-4 h-4" />
                <span>Bank Account</span>
              </div>
              <div class="space-y-1">
                <p class="font-medium text-slate-900 dark:text-white">{{ defaultBeneficiary?.account_name || '—' }}</p>
                <p class="text-sm text-slate-600 dark:text-slate-400">{{ defaultBeneficiary?.bank_name || '—' }}</p>
                <p class="text-sm font-mono">{{ maskedAccount }}</p>
                <p class="text-xs text-slate-500">IFSC: {{ defaultBeneficiary?.ifsc || '—' }}</p>
              </div>
            </div>

            <NuxtLink to="/dashboard/wallet/beneficiary" class="absolute top-3 right-3 flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
              <Icon name="mdi:pencil" class="w-3 h-3" />
              <span>Change</span>
            </NuxtLink>
          </div>
        </div>

        <div class="space-y-4">
          <!-- Amount -->
          <div>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
              <Icon name="mdi:currency-inr" class="w-4 h-4" />
              <span>Withdrawal Amount</span>
              <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-semibold pointer-events-none">₹</span>
              <input
                  v-model="withdrawForm.amount"
                  type="number"
                  :min="MIN_WITHDRAW"
                  step="0.01"
                  placeholder="0.00"
                  class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                  :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': withdrawErrors.amount }"
              />
            </div>
            <div class="flex items-center justify-between mt-1 text-xs text-slate-500 dark:text-slate-400">
              <span>Minimum: ₹{{ MIN_WITHDRAW }}</span>
              <span>Available: ₹{{ balance.toLocaleString('en-IN') }}</span>
            </div>
            <p v-if="withdrawErrors.amount" class="flex items-center gap-1 mt-1 text-sm text-red-500">
              <Icon name="mdi:alert-circle" class="w-4 h-4" />
              <span>{{ withdrawErrors.amount }}</span>
            </p>
          </div>

          <!-- PIN -->
          <div>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
              <Icon name="mdi:shield-key" class="w-4 h-4" />
              <span>Transaction PIN</span>
              <span class="text-red-500">*</span>
            </label>
            <input
                v-model="withdrawForm.pin"
                type="password"
                inputmode="numeric"
                minlength="4"
                maxlength="6"
                placeholder="Enter your wallet PIN"
                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all tracking-widest"
                :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': withdrawErrors.pin }"
            />
            <p v-if="withdrawErrors.pin" class="flex items-center gap-1 mt-1 text-sm text-red-500">
              <Icon name="mdi:alert-circle" class="w-4 h-4" />
              <span>{{ withdrawErrors.pin }}</span>
            </p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 mt-6">
          <button @click="closeWithdraw" class="flex-1 px-4 py-3 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
            Cancel
          </button>
          <button
              @click="submitWithdraw"
              :disabled="processing || !canSubmitWithdraw"
              class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 disabled:opacity-50 text-white rounded-xl transition-colors"
          >
            <Icon :name="processing ? 'mdi:loading' : 'mdi:bank-transfer-out'" class="w-4 h-4" :class="{ 'animate-spin': processing }" />
            <span>{{ processing ? 'Processing...' : 'Withdraw' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Scanner Modal -->
    <div v-if="scannerOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 max-w-md w-full" @click.stop>
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
              <Icon name="mdi:qrcode-scan" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-slate-900 dark:text-white">Scan QR Code</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">Point camera at recipient's wallet QR</p>
            </div>
          </div>
          <button @click="closeScanner" class="w-8 h-8 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
            <Icon name="mdi:close" class="w-5 h-5" />
          </button>
        </div>

        <div class="relative aspect-square bg-slate-100 dark:bg-slate-700 rounded-xl overflow-hidden mb-4">
          <client-only>
            <QrcodeStream @detect="onDetect" @error="onScanError" @ready="scanning = true" class="w-full h-full" />
          </client-only>
          <div class="absolute inset-4 border-2 border-blue-500 rounded-xl pointer-events-none"></div>
        </div>

        <div class="text-center">
          <div v-if="scanning" class="flex items-center justify-center gap-2 text-blue-600 dark:text-blue-400">
            <div class="w-2 h-2 bg-blue-600 dark:bg-blue-400 rounded-full animate-pulse"></div>
            <span class="text-sm font-medium">Scanning for QR code...</span>
          </div>
          <div v-else class="flex items-center justify-center gap-2 text-slate-500 dark:text-slate-400">
            <Icon name="mdi:camera" class="w-4 h-4" />
            <span class="text-sm">Allow camera permission to scan</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useSanctumFetch, useRuntimeConfig, useToast, navigateTo } from '#imports'

// GSAP imports (client-side only)
let gsap: any = null
if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

definePageMeta({ layout: 'dashboard' })

/* Core */
const config = useRuntimeConfig()
const toast = useToast()

/* State */
const isLoading = useState('pageLoading', () => true)
const wallet = ref<any>(null)
const defaultBeneficiary = ref<any>(null)
const transactions = ref<any[]>([])
const page = ref(1)
const hasMore = ref(true)
const processing = ref(false)

// UI State
type ViewKey = 'home' | 'add' | 'send' | 'transactions' | 'pin'
const currentView = ref<ViewKey>('home')

// Modal States
const showQrModal = ref(false)
const withdrawOpen = ref(false)
const scannerOpen = ref(false)
const scanning = ref(false)

// Form States
const addForm = ref({ amount: '' })
const withdrawForm = ref({ amount: '', pin: '' })
const sendForm = ref({ amount: '', recipient_uuid: '', pin: '', purpose: '' })
const pinForm = ref({ pin: '', confirmPin: '' })

// Form Visibility
const showPin = ref(false)
const showConfirmPin = ref(false)

// Filters
const filters = ref({ type: 'all', status: 'all' })
const sort = ref<'desc' | 'asc'>('desc')

// Error States
const withdrawErrors = ref<Record<string, string>>({})
const sendErrors = ref<Record<string, string>>({})

// Refs
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()

// Constants
const MIN_WITHDRAW = 100
const quickAmounts = [500, 1000, 2000, 5000, 10000]

/* Helper Functions */
function getFloatingElementStyle(index: number) {
  const size = 4 + (index % 3) * 2
  const delay = index * 0.5
  const duration = 8 + (index % 4) * 2

  return {
    left: `${10 + (index * 12) % 80}%`,
    top: `${15 + (index * 8) % 70}%`,
    animationDelay: `${delay}s`,
    animationDuration: `${duration}s`
  }
}

function formatWalletId(uuid: string) {
  if (!uuid) return 'N/A'
  return `${uuid.substring(0, 8)}...${uuid.slice(-8)}`
}

function formatTransactionDate(dateString: string) {
  const date = new Date(dateString)
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  const hours = Math.floor(diff / (1000 * 60 * 60))

  if (hours < 24) {
    if (hours < 1) {
      const minutes = Math.floor(diff / (1000 * 60))
      return minutes < 1 ? 'Just now' : `${minutes}m ago`
    }
    return `${hours}h ago`
  }

  return date.toLocaleDateString()
}

function getTransactionIcon(type: string) {
  const icons = {
    credit: 'mdi:arrow-down-circle',
    debit: 'mdi:arrow-up-circle'
  }
  return icons[type as keyof typeof icons] || 'mdi:swap-horizontal'
}

function getTransactionIconClass(type: string) {
  const classes = {
    credit: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
    debit: 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'
  }
  return classes[type as keyof typeof classes] || 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400'
}

function getTransactionTypeClass(type: string) {
  const classes = {
    credit: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    debit: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'
  }
  return classes[type as keyof typeof classes] || 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-400'
}

function getTransactionLabel(type: string) {
  const labels = {
    credit: 'Money Received',
    debit: 'Money Sent'
  }
  return labels[type as keyof typeof labels] || 'Transaction'
}

function getAmountClass(type: string) {
  const classes = {
    credit: 'text-green-600 dark:text-green-400',
    debit: 'text-red-600 dark:text-red-400'
  }
  return classes[type as keyof typeof classes] || 'text-slate-600 dark:text-slate-400'
}

function getStatusClass(status: string) {
  const classes = {
    success: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    pending: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    failed: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'
  }
  return classes[status as keyof typeof classes] || 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-400'
}

function getStatusIcon(status: string) {
  const icons = {
    success: 'mdi:check-circle',
    pending: 'mdi:clock',
    failed: 'mdi:close-circle'
  }
  return icons[status as keyof typeof icons] || 'mdi:help-circle'
}

/* Computed */
const balance = computed(() => {
  const raw = wallet.value?.balance_numeric ?? wallet.value?.balance ?? 0
  const str = String(raw)
  const cleaned = str.replace(/[^\d.-]/g, '')
  const num = Number(cleaned)
  return Number.isFinite(num) ? num : 0
})

const walletQr = computed<string | null>(() => wallet.value?.qr || null)

const maskedAccount = computed(() => {
  const n = defaultBeneficiary.value?.account_number || ''
  return n ? `•••• ${String(n).slice(-4)}` : (defaultBeneficiary.value?.upi_handle || 'Not linked')
})

const isUpiBeneficiary = computed(() => {
  const ben = defaultBeneficiary.value
  if (!ben) return false
  const t = (ben.type || '').toString().toLowerCase()
  return !!ben.upi_handle || t.includes('upi')
})

const hasBeneficiary = computed(() => !!defaultBeneficiary.value)

const canWithdraw = computed(() => balance.value >= MIN_WITHDRAW && hasBeneficiary.value)

const canSubmitWithdraw = computed(() => {
  return Number(withdrawForm.value.amount) >= MIN_WITHDRAW &&
      Number(withdrawForm.value.amount) <= balance.value &&
      withdrawForm.value.pin.length >= 4 &&
      hasBeneficiary.value
})

const isValidSendForm = computed(() => {
  return Number(sendForm.value.amount) > 0 &&
      sendForm.value.recipient_uuid.trim().length > 0 &&
      sendForm.value.purpose.trim().length > 0 &&
      sendForm.value.pin.length >= 4
})

const todayActivity = computed(() => {
  const today = new Date().toDateString()
  return transactions.value
      .filter(tx => new Date(tx.created_at).toDateString() === today)
      .reduce((sum, tx) => sum + Number(tx.amount), 0)
})

const lastTransaction = computed(() => {
  const latest = transactions.value[0]
  return latest ? latest.purpose || getTransactionLabel(latest.type) : null
})

/* API Functions */
function normalizeWalletResponse(res: any) {
  const root = res?.data ?? {}
  const hasNestedWalletField = Object.prototype.hasOwnProperty.call(root, 'wallet')
  const walletObj = hasNestedWalletField ? root.wallet : root
  const finalWallet = walletObj && typeof walletObj === 'object' && (walletObj.uuid || walletObj.qr || walletObj.balance !== undefined)
      ? walletObj
      : null
  const txs = Array.isArray(root.transactions) ? root.transactions : []
  const ben = root.beneficiary ?? null
  return { wallet: finalWallet, transactions: txs, beneficiary: ben }
}

async function loadWallet() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/wallet`)
    const { wallet: w, transactions: txs, beneficiary: ben } = normalizeWalletResponse(res)
    wallet.value = w
    defaultBeneficiary.value = ben
    if (Array.isArray(txs) && txs.length > 0) {
      transactions.value = txs
      hasMore.value = false
    }
  } catch {
    wallet.value = null
    defaultBeneficiary.value = null
    console.log('Failed to fetch wallet.')
  }
}

async function loadTransactions(reset = false) {
  try {
    if (reset) { transactions.value = []; page.value = 1; hasMore.value = true }
    if (!hasMore.value) return

    const q = new URLSearchParams({
      page: String(page.value),
      type: filters.value.type,
      status: filters.value.status,
      sort: sort.value
    }).toString()

    const res = await useSanctumFetch(`${config.public.apiBase}/wallet/transactions?${q}`)
    const list = res?.data?.data ?? (Array.isArray(res?.data) ? res?.data : [])
    transactions.value.push(...list)
    hasMore.value = !!res?.data?.next_page_url
    page.value++
  } catch {
    console.log('Failed to fetch transactions.')
  }
}

async function unlockWallet() {
  try {
    processing.value = true
    await useSanctumFetch(`${config.public.apiBase}/wallet/create`, { method: 'POST' })
    await loadWallet()
    toast.success({ title: 'Wallet', message: 'Wallet unlocked successfully!' })
  } catch {
    toast.error({ title: 'Wallet', message: 'Failed to create wallet.' })
  } finally {
    processing.value = false
  }
}

async function addMoney() {
  const amount = Number(addForm.value.amount || 0)
  if (!amount || amount <= 0) {
    toast.error({ title: 'Add Money', message: 'Enter a valid amount.' })
    return
  }

  try {
    processing.value = true
    const res = await useSanctumFetch(`${config.public.apiBase}/wallet/add-money`, {
      method: 'POST',
      body: { amount }
    })

    const success = !!(res?.data?.success ?? res?.success)
    const redirectUrl = res?.data?.redirect ?? res?.data?.redirect_url ?? res?.redirect ?? res?.redirect_url

    if (success && redirectUrl && typeof redirectUrl === 'string') {
      if (/^https?:\/\//i.test(redirectUrl)) {
        window.location.assign(redirectUrl)
      } else {
        await navigateTo(redirectUrl)
      }
      return
    }

    toast.success({ title: 'Add Money', message: res?.data?.message || 'Proceed to checkout.' })
    await loadWallet()
    addForm.value = { amount: '' }
    currentView.value = 'home'
  } catch (e: any) {
    toast.error({ title: 'Add Money', message: e?.data?.message || 'Failed to add money.' })
  } finally {
    processing.value = false
  }
}

function openWithdraw() {
  withdrawErrors.value = {}
  withdrawForm.value = { amount: '', pin: '' }
  withdrawOpen.value = true
}

function closeWithdraw() {
  withdrawOpen.value = false
}

async function submitWithdraw() {
  const amount = Number(withdrawForm.value.amount || 0)
  withdrawErrors.value = {}

  if (!hasBeneficiary.value) {
    toast.error({ title: 'Withdraw', message: 'Add a beneficiary to withdraw.' })
    return
  }

  if (!amount || amount < MIN_WITHDRAW) {
    withdrawErrors.value.amount = `Minimum withdrawal is ₹${MIN_WITHDRAW}.`
    return
  }

  if (amount > balance.value) {
    withdrawErrors.value.amount = 'Amount exceeds wallet balance.'
    return
  }

  if (!withdrawForm.value.pin) {
    withdrawErrors.value.pin = 'PIN is required.'
    return
  }

  try {
    processing.value = true
    await useSanctumFetch(`${config.public.apiBase}/wallet/withdraw`, {
      method: 'POST',
      body: { amount, pin: withdrawForm.value.pin }
    })

    toast.success({ title: 'Withdraw', message: 'Withdrawal request placed.' })
    await loadWallet()
    closeWithdraw()
    currentView.value = 'home'
  } catch (e: any) {
    const errs = e?.data?.errors ?? e?.errors
    if (errs && typeof errs === 'object') {
      Object.entries(errs).forEach(([k, v]) => {
        withdrawErrors.value[k] = Array.isArray(v) ? (v as any).join(', ') : (v as any)
      })
    }
    toast.error({ title: 'Withdraw', message: e?.data?.message || 'Failed to withdraw.' })
  } finally {
    processing.value = false
  }
}

async function sendMoney() {
  sendErrors.value = {}
  const amount = Number(sendForm.value.amount || 0)

  if (!amount || amount <= 0) {
    sendErrors.value.amount = 'Enter a valid amount.'
  }
  if (!sendForm.value.recipient_uuid) {
    sendErrors.value.recipient_uuid = 'Recipient wallet UUID is required.'
  }
  if (!sendForm.value.pin) {
    sendErrors.value.pin = 'PIN is required.'
  }
  if (!sendForm.value.purpose) {
    sendErrors.value.purpose = 'Purpose is required.'
  }

  if (Object.keys(sendErrors.value).length > 0) return

  if (amount > balance.value) {
    sendErrors.value.amount = 'Amount exceeds wallet balance.'
    return
  }

  try {
    processing.value = true
    await useSanctumFetch(`${config.public.apiBase}/wallet/send`, {
      method: 'POST',
      body: {
        amount,
        recipient_uuid: sendForm.value.recipient_uuid,
        pin: sendForm.value.pin,
        purpose: sendForm.value.purpose
      }
    })

    toast.success({ title: 'Send', message: 'Money sent successfully.' })
    await loadWallet()
    sendForm.value = { amount: '', recipient_uuid: '', pin: '', purpose: '' }
    currentView.value = 'home'
  } catch (e: any) {
    const errs = e?.data?.errors ?? e?.errors
    if (errs && typeof errs === 'object') {
      Object.entries(errs).forEach(([k, v]) => {
        sendErrors.value[k] = Array.isArray(v) ? (v as any).join(', ') : (v as any)
      })
    }
    toast.error({ title: 'Send', message: e?.data?.message || 'Failed to send money.' })
  } finally {
    processing.value = false
  }
}

async function changePin() {
  if (!pinForm.value.pin || pinForm.value.pin !== pinForm.value.confirmPin) {
    toast.error({ title: 'PIN', message: 'PINs do not match.' })
    return
  }

  try {
    processing.value = true
    await useSanctumFetch(`${config.public.apiBase}/wallet/change-pin`, {
      method: 'POST',
      body: { pin: pinForm.value.pin, confirmPin: pinForm.value.confirmPin }
    })

    toast.success({ title: 'PIN', message: 'PIN changed successfully.' })
    pinForm.value = { pin: '', confirmPin: '' }
    currentView.value = 'home'
  } catch (e: any) {
    toast.error({ title: 'PIN', message: e?.data?.message || 'Failed to change PIN.' })
  } finally {
    processing.value = false
  }
}

// Scanner Methods
function openScanner() {
  scannerOpen.value = true
  scanning.value = false
}

function closeScanner() {
  scannerOpen.value = false
  scanning.value = false
}

type DetectedBarcode = { rawValue: string }

function onDetect(detected: DetectedBarcode[]) {
  const first = Array.isArray(detected) && detected.length ? detected[0] : null
  const val = first?.rawValue?.trim() || ''

  if (val) {
    sendForm.value.recipient_uuid = val
    toast.success({ title: 'QR', message: 'Recipient detected from QR.' })
  } else {
    toast.error({ title: 'QR', message: 'Invalid QR content.' })
  }

  closeScanner()
}

function onScanError(err: Error) {
  console.error('QR scan error', err)
  toast.error({ title: 'QR', message: err?.message || 'Camera or scanning error.' })
  closeScanner()
}

// Navigation
function go(view: ViewKey) {
  currentView.value = view
  if (view === 'transactions') loadTransactions(true)
}

function backHome() {
  currentView.value = 'home'
}

// Lifecycle
onMounted(async () => {
  isLoading.value = true
  await Promise.all([loadWallet(), loadTransactions(true)])
  isLoading.value = false

  if (process.client && gsap) {
    gsap.to([orb1.value, orb2.value], {
      rotation: 360,
      duration: 20,
      repeat: -1,
      ease: 'none'
    })
  }
})
</script>
