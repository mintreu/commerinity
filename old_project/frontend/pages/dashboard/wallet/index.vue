<template>
  <div class="min-h-screen w-full bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-950 dark:to-slate-900 overflow-x-hidden">

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center min-h-screen">
      <div class="flex flex-col items-center gap-4 bg-white/90 dark:bg-slate-800/90 backdrop-blur rounded-2xl p-8 shadow-2xl">
        <div class="w-16 h-16 border-4 border-slate-200 dark:border-slate-700 border-t-emerald-600 dark:border-t-emerald-400 rounded-full animate-spin"></div>
        <p class="text-slate-600 dark:text-slate-400 font-semibold">Loading wallet...</p>
      </div>
    </div>

    <!-- Locked State -->
    <div v-else-if="!wallet" class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 md:p-12 shadow-2xl max-w-md w-full text-center">
        <div class="relative inline-block mb-8">
          <Icon name="mdi:wallet-outline" class="w-24 h-24 text-slate-300 dark:text-slate-600" />
          <div class="absolute -bottom-2 -right-2 w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
            <Icon name="mdi:lock" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
          </div>
        </div>
        <h2 class="text-3xl font-black text-slate-900 dark:text-white mb-4">Wallet Locked</h2>
        <p class="text-slate-600 dark:text-slate-400 mb-8 leading-relaxed">Unlock secure wallet to manage funds, payments, and transactions.</p>
        <button @click="unlockWallet" :disabled="processing" class="inline-flex items-center justify-center gap-3 w-full px-8 py-4 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 disabled:opacity-50 text-white font-bold rounded-xl shadow-xl hover:shadow-2xl transition-all">
          <Icon :name="processing ? 'mdi:loading' : 'mdi:lock-open'" class="w-5 h-5" :class="{ 'animate-spin': processing }" />
          <span>{{ processing ? 'Creating...' : 'Unlock Wallet' }}</span>
        </button>
      </div>
    </div>

    <!-- Main Wallet -->
    <div v-else class="max-w-7xl mx-auto p-4 md:p-6 lg:p-8">

      <!-- Home View -->
      <div v-if="currentView === 'home'" class="space-y-6">

        <!-- Wallet Card -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden">
          <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 p-6 md:p-8">
            <div class="flex-1">
              <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 block">My Wallet</span>
              <div class="mb-6">
                <div class="text-5xl font-black text-slate-900 dark:text-white mb-2">{{ wallet.balance }}</div>
                <div class="flex items-center gap-2 text-emerald-500 text-sm font-semibold">
                  <Icon name="mdi:trending-up" class="w-4 h-4" />
                  <span>Available Balance</span>
                </div>
              </div>
              <div class="space-y-3 text-sm">
                <div class="flex items-center gap-2">
                  <span class="text-slate-500 dark:text-slate-400">Wallet ID:</span>
                  <span class="font-mono font-bold text-slate-900 dark:text-white">{{ formatWalletId(wallet.uuid) }}</span>
                </div>
                <div v-if="wallet.coins > 0" class="flex items-center gap-2">
                  <span class="text-slate-500 dark:text-slate-400">Reward Coins:</span>
                  <span class="font-bold text-amber-600 dark:text-amber-400">{{ wallet.coins.toLocaleString() }}</span>
                  <button @click="openConvertModal" class="text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">Convert</button>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-slate-500 dark:text-slate-400">Account:</span>
                  <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ maskedAccount }}</span>
                  <NuxtLink to="/dashboard/wallet/beneficiary" class="inline-flex items-center gap-1 ml-2 text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                    <Icon name="mdi:pencil" class="w-3 h-3" />
                    <span>Manage</span>
                  </NuxtLink>
                </div>
              </div>
            </div>
            <div v-if="walletQr" class="flex-shrink-0 text-center cursor-pointer group" @click="showQrModal = true">
              <img :src="walletQr" alt="QR" class="w-28 h-28 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-2xl p-2 object-contain transition-transform group-hover:scale-105" />
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Tap to enlarge</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 px-6 pb-6 md:px-8 md:pb-8">
            <button @click="go('add')" class="flex flex-col items-center gap-2 p-4 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all hover:-translate-y-1">
              <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                <Icon name="mdi:plus-circle" class="w-6 h-6 text-white" />
              </div>
              <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Add Money</span>
            </button>
            <button @click="openWithdraw" class="flex flex-col items-center gap-2 p-4 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all hover:-translate-y-1">
              <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                <Icon name="mdi:bank-transfer-out" class="w-6 h-6 text-white" />
              </div>
              <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Withdraw</span>
            </button>
            <button @click="go('send')" class="flex flex-col items-center gap-2 p-4 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all hover:-translate-y-1">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                <Icon name="mdi:send-circle" class="w-6 h-6 text-white" />
              </div>
              <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Send</span>
            </button>
            <button @click="openConvertModal" class="flex flex-col items-center gap-2 p-4 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all hover:-translate-y-1">
              <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg">
                <Icon name="mdi:swap-horizontal-circle" class="w-6 h-6 text-white" />
              </div>
              <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Convert</span>
            </button>
            <button @click="go('pin')" class="flex flex-col items-center gap-2 p-4 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all hover:-translate-y-1">
              <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                <Icon name="mdi:shield-key" class="w-6 h-6 text-white" />
              </div>
              <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Security</span>
            </button>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Available Balance -->
          <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-xl border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                <Icon name="mdi:wallet" class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
              </div>
              <div class="flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                <Icon name="mdi:trending-up" class="w-3 h-3" />
                <span>Active</span>
              </div>
            </div>
            <h3 class="text-sm font-bold text-slate-600 dark:text-slate-400 mb-2">Available Balance</h3>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ wallet.balance }}</div>
          </div>

          <!-- Today's Credits -->
          <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-xl border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                <Icon name="mdi:arrow-down-circle" class="w-6 h-6 text-green-600 dark:text-green-400" />
              </div>
              <div class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Today</div>
            </div>
            <h3 class="text-sm font-bold text-slate-600 dark:text-slate-400 mb-2">Credits</h3>
            <div class="text-2xl font-black text-green-600 dark:text-green-400">{{ formatMoney(wallet.stats?.today?.credits) }}</div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ wallet.stats?.counts?.credits || 0 }} transactions</p>
          </div>

          <!-- Today's Debits -->
          <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-xl border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
                <Icon name="mdi:arrow-up-circle" class="w-6 h-6 text-red-600 dark:text-red-400" />
              </div>
              <div class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Today</div>
            </div>
            <h3 class="text-sm font-bold text-slate-600 dark:text-slate-400 mb-2">Debits</h3>
            <div class="text-2xl font-black text-red-600 dark:text-red-400">{{ formatMoney(wallet.stats?.today?.debits) }}</div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ wallet.stats?.counts?.debits || 0 }} transactions</p>
          </div>

          <!-- Reward Coins -->
          <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-xl border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                <Icon name="mdi:coin" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
              </div>
              <button v-if="wallet.coins > 0" @click="openConvertModal" class="text-xs font-bold text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 transition-colors">CONVERT</button>
            </div>
            <h3 class="text-sm font-bold text-slate-600 dark:text-slate-400 mb-2">Reward Coins</h3>
            <div class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ wallet.coins?.toLocaleString() || 0 }}</div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">1 coin = ₹1.00</p>
          </div>
        </div>

        <!-- Week & Month Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Week Summary -->
          <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-2xl p-6 border border-blue-200 dark:border-blue-800">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-10 h-10 bg-blue-600 dark:bg-blue-500 rounded-lg flex items-center justify-center">
                <Icon name="mdi:calendar-week" class="w-5 h-5 text-white" />
              </div>
              <div>
                <h3 class="text-lg font-black text-blue-900 dark:text-blue-100">This Week</h3>
                <p class="text-xs text-blue-700 dark:text-blue-300">Last 7 days activity</p>
              </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
              <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur rounded-lg p-3">
                <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">Credits</p>
                <p class="text-sm font-black text-green-600 dark:text-green-400">{{ formatMoney(wallet.stats?.week?.credits) }}</p>
              </div>
              <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur rounded-lg p-3">
                <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">Debits</p>
                <p class="text-sm font-black text-red-600 dark:text-red-400">{{ formatMoney(wallet.stats?.week?.debits) }}</p>
              </div>
              <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur rounded-lg p-3">
                <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">Net</p>
                <p class="text-sm font-black text-blue-600 dark:text-blue-400">{{ formatMoney(wallet.stats?.week?.net_activity) }}</p>
              </div>
            </div>
          </div>

          <!-- Month Summary -->
          <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-2xl p-6 border border-purple-200 dark:border-purple-800">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-10 h-10 bg-purple-600 dark:bg-purple-500 rounded-lg flex items-center justify-center">
                <Icon name="mdi:calendar-month" class="w-5 h-5 text-white" />
              </div>
              <div>
                <h3 class="text-lg font-black text-purple-900 dark:text-purple-100">This Month</h3>
                <p class="text-xs text-purple-700 dark:text-purple-300">Last 30 days activity</p>
              </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
              <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur rounded-lg p-3">
                <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">Credits</p>
                <p class="text-sm font-black text-green-600 dark:text-green-400">{{ formatMoney(wallet.stats?.month?.credits) }}</p>
              </div>
              <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur rounded-lg p-3">
                <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">Debits</p>
                <p class="text-sm font-black text-red-600 dark:text-red-400">{{ formatMoney(wallet.stats?.month?.debits) }}</p>
              </div>
              <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur rounded-lg p-3">
                <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">Net</p>
                <p class="text-sm font-black text-purple-600 dark:text-purple-400">{{ formatMoney(wallet.stats?.month?.net_activity) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Dashboard -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

          <!-- Analytics -->
          <div class="xl:col-span-2 bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                  <Icon name="mdi:chart-line" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-lg font-black text-slate-900 dark:text-white">Spending Analytics</h3>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Track financial patterns</p>
                </div>
              </div>
              <select v-model="filters.type" @change="loadAnalytics" class="px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white">
                <option value="all">All Types</option>
                <option value="Debited">Expenses</option>
                <option value="Credited">Income</option>
              </select>
            </div>
            <div class="p-6 h-64">
              <div v-if="analyticsData && analyticsData.length > 0">
                <client-only>
                  <VChart :option="chartOption" autoresize />
                </client-only>
              </div>
              <div v-else class="flex flex-col items-center justify-center h-full text-center">
                <Icon name="mdi:chart-line-variant" class="w-16 h-16 text-slate-300 dark:text-slate-600 mb-4" />
                <p class="text-slate-500 dark:text-slate-400 text-sm font-semibold">No analytics data available</p>
                <p class="text-slate-400 dark:text-slate-500 text-xs mt-1">Start making transactions to see patterns</p>
              </div>
            </div>
          </div>

          <!-- Recent Transactions (Last 5) -->
          <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                  <Icon name="mdi:history" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-lg font-black text-slate-900 dark:text-white">Recent Activity</h3>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Latest 5 transactions</p>
                </div>
              </div>
              <NuxtLink to="/dashboard/wallet/transactions" class="flex items-center gap-1 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-bold transition-colors">
                <span>View All</span>
                <Icon name="mdi:arrow-right" class="w-4 h-4" />
              </NuxtLink>
            </div>
            <div class="p-6 space-y-4 max-h-80 overflow-y-auto">
              <div v-for="tx in (wallet.transactions || []).slice(0, 5)" :key="tx.uuid" class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" :class="tx.type === 'Credited' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'">
                  <Icon :name="tx.type === 'Credited' ? 'mdi:arrow-down-circle' : 'mdi:arrow-up-circle'" class="w-4 h-4" />
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="font-bold text-slate-900 dark:text-white text-sm truncate">{{ tx.purpose || (tx.type === 'Credited' ? 'Money Received' : 'Money Sent') }}</h4>
                  <p class="text-xs text-slate-500 dark:text-slate-400">{{ formatTransactionDate(tx.created_at) }}</p>
                </div>
                <div class="font-black text-sm" :class="tx.type === 'Credited' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                  {{ tx.type === 'Credited' ? '+' : '-' }}{{ tx.amount }}
                </div>
              </div>
              <div v-if="!wallet.transactions || wallet.transactions.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
                <Icon name="mdi:receipt-text-outline" class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-2" />
                <p class="text-slate-500 dark:text-slate-400 text-sm">No transactions yet</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Money View -->
      <div v-else-if="currentView === 'add'" class="max-w-2xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-6">
          <button @click="backHome" class="flex items-center gap-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
            <Icon name="mdi:arrow-left" class="w-5 h-5" />
            <span class="font-semibold">Back</span>
          </button>
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
              <Icon name="mdi:plus-circle" class="w-6 h-6 text-white" />
            </div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">Add Money</h2>
          </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 md:p-8 shadow-2xl space-y-6">
          <div class="space-y-2">
            <label class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300">
              <Icon name="mdi:currency-inr" class="w-4 h-4" />
              <span>Amount to Add</span>
            </label>
            <input v-model="addForm.amount" type="number" min="1" step="0.01" placeholder="0.00" class="w-full px-4 py-3 text-xl font-black bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all" />
            <div class="flex flex-wrap gap-2 mt-4">
              <button v-for="amt in [500, 1000, 2000, 5000, 10000]" :key="amt" @click="addForm.amount = amt.toString()" class="px-4 py-2 rounded-lg border-2 transition-all font-bold" :class="addForm.amount === amt.toString() ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400' : 'border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white hover:border-emerald-300 dark:hover:border-emerald-600'">
                ₹{{ amt }}
              </button>
            </div>
          </div>
          <div class="space-y-2">
            <label class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300">
              <Icon name="mdi:shield-key" class="w-4 h-4" />
              <span>Transaction PIN (6 digits)</span>
              <span class="text-red-500">*</span>
            </label>
            <input v-model="addForm.pin" type="password" inputmode="numeric" minlength="6" maxlength="6" placeholder="Enter 6-digit PIN" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all tracking-widest text-center text-2xl font-black" />
          </div>
          <div class="space-y-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
            <div class="flex items-center gap-2 text-sm text-blue-700 dark:text-blue-300">
              <Icon name="mdi:information" class="w-4 h-4 text-blue-500" />
              <span>Redirected to secure payment gateway</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-blue-700 dark:text-blue-300">
              <Icon name="mdi:shield-check" class="w-4 h-4 text-emerald-500" />
              <span>All transactions encrypted and secure</span>
            </div>
          </div>
          <button @click="addMoney" :disabled="processing || !addForm.amount || Number(addForm.amount) <= 0 || addForm.pin.length !== 6" class="w-full flex items-center justify-center gap-3 py-4 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black rounded-xl shadow-xl hover:shadow-2xl transition-all">
            <Icon :name="processing ? 'mdi:loading' : 'mdi:credit-card'" class="w-5 h-5" :class="{ 'animate-spin': processing }" />
            <span>{{ processing ? 'Processing...' : 'Proceed to Payment' }}</span>
          </button>
        </div>
      </div>

      <!-- Send Money, PIN, Other Views - Continue in next part -->
      <!-- Keep exactly as previous code... -->

    </div>

    <!-- All Modals - Continue in next part -->
    <!-- Keep exactly as previous code... -->

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useSanctumFetch, useRuntimeConfig, useToast } from '#imports'
import VChart from 'vue-echarts'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { BarChart } from 'echarts/charts'
import { TitleComponent, TooltipComponent, GridComponent } from 'echarts/components'

use([CanvasRenderer, BarChart, TitleComponent, TooltipComponent, GridComponent])

definePageMeta({ layout: 'dashboard' })

const config = useRuntimeConfig()
const toast = useToast()
const isLoading = useState('pageLoading', () => true)
const wallet = ref<any>(null)
const defaultBeneficiary = ref<any>(null)
const processing = ref(false)
type ViewKey = 'home' | 'add' | 'send' | 'pin'
const currentView = ref<ViewKey>('home')
const showQrModal = ref(false)
const withdrawOpen = ref(false)
const scannerOpen = ref(false)
const showConvertModal = ref(false)
const addForm = ref({ amount: '', pin: '' })
const withdrawForm = ref({ amount: '', pin: '' })
const sendForm = ref({ amount: '', recipient_uuid: '', pin: '', purpose: '' })
const pinForm = ref({ oldPin: '', pin: '', confirmPin: '' })
const convertForm = ref({ points: '', pin: '' })
const showPin = ref(false)
const showOldPin = ref(false)
const showConfirmPin = ref(false)
const filters = ref({ type: 'all' })
const withdrawErrors = ref<Record<string, string>>({})
const sendErrors = ref<Record<string, string>>({})
const MIN_WITHDRAW = 100
const analyticsData = ref<any[]>([])

function formatWalletId(uuid: string) {
  if (!uuid) return 'N/A'
  return `${uuid.substring(0, 8)}...${uuid.slice(-8)}`
}

function formatMoney(value: any) {
  if (!value || value === 0) return '₹0.00'
  if (typeof value === 'string') return value
  return `₹${(value / 100).toFixed(2)}`
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

const walletQr = computed<string | null>(() => wallet.value?.qr || null)

const maskedAccount = computed(() => {
  const n = defaultBeneficiary.value?.account_number || ''
  return n ? `•••• ${String(n).slice(-4)}` : (defaultBeneficiary.value?.upi_handle || 'Not linked')
})

const hasBeneficiary = computed(() => !!defaultBeneficiary.value)

const canSubmitWithdraw = computed(() => {
  return Number(withdrawForm.value.amount) >= MIN_WITHDRAW &&
      Number(withdrawForm.value.amount) <= parseFloat(String(wallet.value?.balance).replace(/[^\d.-]/g, '')) &&
      withdrawForm.value.pin.length === 6 &&
      hasBeneficiary.value
})

const isValidSendForm = computed(() => {
  return Number(sendForm.value.amount) > 0 &&
      sendForm.value.recipient_uuid.trim().length > 0 &&
      sendForm.value.purpose.trim().length > 0 &&
      sendForm.value.pin.length === 6
})

const chartOption = computed(() => ({
  tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
  grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
  xAxis: {
    type: 'category',
    data: analyticsData.value.map((d: any) => d.date || d.label),
    axisLine: { lineStyle: { color: '#94a3b8' } }
  },
  yAxis: {
    type: 'value',
    axisLine: { lineStyle: { color: '#94a3b8' } }
  },
  series: [{
    name: 'Amount',
    type: 'bar',
    data: analyticsData.value.map((d: any) => (d.amount || 0) / 100),
    itemStyle: {
      color: {
        type: 'linear',
        x: 0,
        y: 0,
        x2: 0,
        y2: 1,
        colorStops: [
          { offset: 0, color: '#8b5cf6' },
          { offset: 1, color: '#6366f1' }
        ]
      }
    }
  }]
}))

async function loadWallet() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/wallet`)
    const data = res?.data?.data || res?.data
    wallet.value = data
    defaultBeneficiary.value = data?.beneficiary || null
  } catch {
    wallet.value = null
    defaultBeneficiary.value = null
  }
}

async function loadAnalytics() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/wallet/analytics?type=${filters.value.type}`)
    analyticsData.value = res?.data?.data || res?.data || []
  } catch {
    analyticsData.value = []
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
  if (addForm.value.pin.length !== 6) {
    toast.error({ title: 'Add Money', message: '6-digit PIN is required.' })
    return
  }
  try {
    processing.value = true
    const res = await useSanctumFetch(`${config.public.apiBase}/wallet/add-money`, {
      method: 'POST',
      body: { amount, pin: addForm.value.pin }
    })

    // Check for redirect URL and auto-redirect
    const redirectUrl = res?.data?.redirect || res?.redirect
    if (redirectUrl && typeof redirectUrl === 'string' && /^https?:\/\//i.test(redirectUrl)) {
      toast.success({ title: 'Redirecting', message: 'Taking you to payment gateway...' })
      window.location.href = redirectUrl
      return
    }

    toast.success({ title: 'Add Money', message: res?.data?.message || 'Request processed.' })
    await loadWallet()
    addForm.value = { amount: '', pin: '' }
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
  const balance = parseFloat(String(wallet.value?.balance).replace(/[^\d.-]/g, ''))
  if (amount > balance) {
    withdrawErrors.value.amount = 'Amount exceeds wallet balance.'
    return
  }
  if (withdrawForm.value.pin.length !== 6) {
    withdrawErrors.value.pin = '6-digit PIN is required.'
    return
  }
  try {
    processing.value = true
    await useSanctumFetch(`${config.public.apiBase}/wallet/withdraw`, {
      method: 'POST',
      body: { amount, pin: withdrawForm.value.pin }
    })
    toast.success({ title: 'Withdraw', message: 'Withdrawal request placed successfully!' })
    await loadWallet() // Refresh wallet data
    closeWithdraw()
    withdrawForm.value = { amount: '', pin: '' }
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
  if (sendForm.value.pin.length !== 6) {
    sendErrors.value.pin = '6-digit PIN is required.'
  }
  if (!sendForm.value.purpose) {
    sendErrors.value.purpose = 'Purpose is required.'
  }
  if (Object.keys(sendErrors.value).length > 0) return
  const balance = parseFloat(String(wallet.value?.balance).replace(/[^\d.-]/g, ''))
  if (amount > balance) {
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
    toast.success({ title: 'Send', message: 'Money sent successfully!' })
    await loadWallet() // Refresh wallet data
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
  if (pinForm.value.oldPin.length !== 6) {
    toast.error({ title: 'PIN', message: '6-digit old PIN is required.' })
    return
  }
  if (pinForm.value.pin.length !== 6 || pinForm.value.pin !== pinForm.value.confirmPin) {
    toast.error({ title: 'PIN', message: 'New PINs must be 6 digits and match.' })
    return
  }
  try {
    processing.value = true
    await useSanctumFetch(`${config.public.apiBase}/wallet/change-pin`, {
      method: 'POST',
      body: {
        old_pin: pinForm.value.oldPin,
        pin: pinForm.value.pin,
        confirm_pin: pinForm.value.confirmPin
      }
    })
    toast.success({ title: 'PIN', message: 'PIN changed successfully!' })
    pinForm.value = { oldPin: '', pin: '', confirmPin: '' }
    currentView.value = 'home'
  } catch (e: any) {
    toast.error({ title: 'PIN', message: e?.data?.message || 'Failed to change PIN.' })
  } finally {
    processing.value = false
  }
}

function openScanner() {
  scannerOpen.value = true
}

function closeScanner() {
  scannerOpen.value = false
}

async function shareQR() {
  if (!process.client || !wallet.value) return
  try {
    if (navigator.share) {
      await navigator.share({
        title: 'My Wallet QR',
        text: `Pay me using this wallet ID: ${wallet.value.uuid}`,
        url: window.location.href
      })
    } else {
      await navigator.clipboard.writeText(wallet.value.uuid)
      toast.success({ title: 'QR', message: 'Wallet ID copied to clipboard!' })
    }
  } catch {}
}

function openConvertModal() {
  convertForm.value = { points: '', pin: '' }
  showConvertModal.value = true
}

async function convertPoints() {
  const points = Number(convertForm.value.points || 0)
  if (!points || points <= 0) {
    toast.error({ title: 'Convert', message: 'Enter valid coins.' })
    return
  }
  if (convertForm.value.pin.length !== 6) {
    toast.error({ title: 'Convert', message: '6-digit PIN is required.' })
    return
  }
  try {
    processing.value = true
    await useSanctumFetch(`${config.public.apiBase}/wallet/point-conversion`, {
      method: 'POST',
      body: { points, pin: convertForm.value.pin }
    })
    toast.success({ title: 'Convert', message: 'Coins converted successfully!' })
    await loadWallet() // Refresh wallet data
    showConvertModal.value = false
    convertForm.value = { points: '', pin: '' }
  } catch (e: any) {
    toast.error({ title: 'Convert', message: e?.data?.message || 'Failed to convert coins.' })
  } finally {
    processing.value = false
  }
}

function go(view: ViewKey) {
  currentView.value = view
}

function backHome() {
  currentView.value = 'home'
}

onMounted(async () => {
  isLoading.value = true
  await Promise.all([loadWallet(), loadAnalytics()])
  isLoading.value = false
})
</script>
