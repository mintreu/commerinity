<script setup lang="ts">
/**
 * Subscription Management Page
 * Shows current subscription status, available plans, and subscription history
 * Premium Mintreu Design with glassmorphism and animations
 */

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const { plans, status, history, historyMeta, isLoading, fetchPlans, fetchStatus, fetchHistory, subscribe } = useSubscription()
const { wallet, fetchWallet } = useWallet()
const toast = useToast()

const activeTab = ref('plans')
const selectedPlan = ref<any>(null)
const pinInput = ref('')
const showPinModal = ref(false)
const subscribing = ref(false)

onMounted(async () => {
  await Promise.all([
    fetchPlans(),
    fetchStatus(),
    fetchWallet()
  ])
})

const loadHistory = async () => {
  if (history.value.length === 0) {
    await fetchHistory()
  }
}

const selectPlan = (plan: any) => {
  if (!status.value?.can_subscribe) {
    toast.add({
      title: 'Current Plan Active',
      description: 'You already have an active subscription. Management features coming soon.',
      color: 'info',
      icon: 'i-lucide-info'
    })
    return
  }
  selectedPlan.value = plan
  showPinModal.value = true
}

const handleSubscribe = async () => {
  if (!selectedPlan.value || !pinInput.value) return

  subscribing.value = true
  try {
    const response = await subscribe(selectedPlan.value.uuid, pinInput.value)
    if (response?.success) {
      toast.add({
        title: 'Subscription Active!',
        description: response.message || 'Welcome to your premium membership.',
        color: 'success',
        icon: 'i-lucide-party-popper'
      })
      showPinModal.value = false
      pinInput.value = ''
      selectedPlan.value = null
      await fetchStatus() // Refresh subscription status
      await fetchWallet() // Refresh wallet balance
    }
  }
  catch (err: any) {
    const errorMessage = err.data?.message || 'Failed to activate subscription.'

    if (err.data?.requires_pin_setup) {
      toast.add({
        title: 'PIN Required',
        description: 'Please set up your wallet PIN to proceed with payments.',
        color: 'warning',
        icon: 'i-lucide-shield-alert'
      })
      navigateTo('/wallet/setup-pin')
      return
    }

    toast.add({
      title: 'Subscription Failed',
      description: errorMessage,
      color: 'error',
      icon: 'i-lucide-alert-circle'
    })
  }
  finally {
    subscribing.value = false
  }
}

const formatDate = (dateString: string | null) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const getStatusColor = (statusStr: string) => {
  const colors: Record<string, string> = {
    active: 'success',
    pending: 'warning',
    expired: 'error',
    cancelled: 'error'
  }
  return colors[statusStr] || 'info'
}

const tabs = [
  { label: 'Available Plans', value: 'plans', icon: 'i-lucide-crown' },
  { label: 'My Status', value: 'status', icon: 'i-lucide-fingerprint' },
  { label: 'Transaction History', value: 'history', icon: 'i-lucide-scroll-text' }
]
</script>

<template>
  <div class="max-w-7xl mx-auto space-y-8 pb-12">
    <!-- Premium Header Section -->
    <div class="relative overflow-hidden group">
      <!-- Background Ambient Glow -->
      <div class="absolute -top-24 -right-24 w-96 h-96 bg-primary-500/10 blur-3xl rounded-full" />
      <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-purple-500/10 blur-3xl rounded-full" />

      <div class="relative flex flex-col md:flex-row items-center justify-between gap-8 p-8 glass-card border-none bg-white/40 dark:bg-slate-900/40 backdrop-blur-2xl">
        <div class="space-y-2">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-500/10 border border-primary-500/20 text-primary-600 dark:text-primary-400 text-xs font-bold uppercase tracking-wider mb-2">
            <UIcon name="i-lucide-sparkles" class="w-4 h-4" />
            Premium Membership
          </div>
          <h1 class="text-3xl md:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
            Elevate Your <span class="bg-gradient-to-r from-primary-600 to-indigo-600 bg-clip-text text-transparent">Experience</span>
          </h1>
          <p class="text-slate-600 dark:text-slate-400 max-w-lg text-lg">
            Choose a plan that fits your ambition. Unlock team commissions, exclusive products, and advanced marketing tools.
          </p>
        </div>

        <!-- Wallet Context Card -->
        <div class="w-full md:w-auto min-w-[280px] p-6 rounded-3xl bg-gradient-to-br from-slate-900 to-slate-800 dark:from-slate-800 dark:to-black shadow-2xl shadow-primary-500/10 border border-slate-700/50">
          <div class="flex items-center justify-between mb-4">
            <span class="text-slate-400 text-xs font-bold uppercase tracking-widest">Wallet Credits</span>
            <div class="p-2 bg-primary-500/20 rounded-xl">
              <UIcon name="i-lucide-wallet" class="w-5 h-5 text-primary-500" />
            </div>
          </div>
          <div class="space-y-1">
            <div class="text-3xl font-black text-white">
              {{ wallet?.available_balance_formatted || '₹0.00' }}
            </div>
            <p class="text-emerald-400 text-xs font-medium flex items-center gap-1">
              <UIcon name="i-lucide-shield-check" class="w-3 h-3" />
              Available for instant activation
            </p>
          </div>
          <UButton
            to="/wallet/add"
            variant="ghost"
            color="primary"
            class="mt-4 p-0 hover:bg-transparent text-sm font-bold"
          >
            Add credits <UIcon name="i-lucide-arrow-right" class="w-4 h-4" />
          </UButton>
        </div>
      </div>
    </div>

    <!-- Active Subscription Highlight -->
    <div
      v-if="status?.has_subscription && status.subscription"
      class="animate-in fade-in slide-in-from-top-4 duration-700"
    >
      <div class="glass-card relative overflow-hidden p-6 border-l-4 border-l-primary-500 bg-gradient-to-r from-primary-500/5 to-transparent">
        <div class="flex flex-col lg:flex-row lg:items-center gap-8">
          <div class="flex items-center gap-6">
            <div class="relative">
              <div class="w-20 h-20 bg-gradient-to-br from-primary-500 to-indigo-600 rounded-3xl flex items-center justify-center shadow-xl shadow-primary-500/20 rotate-3 group-hover:rotate-0 transition-transform">
                <UIcon name="i-lucide-crown" class="w-10 h-10 text-white" />
              </div>
              <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-emerald-500 rounded-full border-4 border-white dark:border-slate-900 flex items-center justify-center">
                <UIcon name="i-lucide-check" class="w-4 h-4 text-white" />
              </div>
            </div>
            <div>
              <div class="flex items-center gap-3">
                <h2 class="text-2xl font-black text-slate-900 dark:text-white">
                  {{ status.subscription.stage?.name || 'Active Member' }}
                </h2>
                <UBadge :color="getStatusColor(status.subscription.status)" variant="soft" size="lg" class="rounded-full px-4">
                  {{ status.subscription.status.toUpperCase() }}
                </UBadge>
              </div>
              <p class="text-slate-500 dark:text-slate-400 mt-1 font-medium italic">
                Membership valid until {{ formatDate(status.subscription.expires_at) }}
              </p>
            </div>
          </div>

          <!-- Dynamic Progress Stats -->
          <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-6 p-4 rounded-2xl bg-white/50 dark:bg-slate-800/50 backdrop-blur-md border border-white/50 dark:border-slate-700/50">
            <div class="space-y-1">
              <span class="text-[10px] text-slate-400 uppercase font-black tracking-widest">Personal Volume</span>
              <p class="text-xl font-black text-slate-900 dark:text-white">{{ status.subscription.personal_pv }} <span class="text-xs font-normal text-slate-500">PV</span></p>
            </div>
            <div class="space-y-1">
              <span class="text-[10px] text-slate-400 uppercase font-black tracking-widest">Commissions</span>
              <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 leading-none">{{ status.subscription.total_commission_formatted }}</p>
            </div>
            <div class="space-y-1">
              <span class="text-[10px] text-slate-400 uppercase font-black tracking-widest">Time Remaining</span>
              <p class="text-xl font-black text-slate-900 dark:text-white">{{ status.subscription.days_remaining }} <span class="text-xs font-normal text-slate-500">Days</span></p>
            </div>
            <div class="space-y-1">
              <span class="text-[10px] text-slate-400 uppercase font-black tracking-widest">Active Level</span>
              <p class="text-xl font-black text-primary-600 dark:text-primary-400 truncate">{{ status.subscription.current_level?.name || 'Starter' }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Interface Tabs -->
    <div class="flex flex-col space-y-8">
      <div class="flex justify-center">
        <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
          <button
            v-for="tab in tabs"
            :key="tab.value"
            @click="activeTab = tab.value; tab.value === 'history' && loadHistory()"
            :class="[
              'flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold transition-all duration-300',
              activeTab === tab.value
                ? 'bg-white dark:bg-slate-700 text-primary-600 dark:text-white shadow-sm ring-1 ring-slate-200 dark:ring-slate-600'
                : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'
            ]"
          >
            <UIcon :name="tab.icon" class="w-5 h-5" />
            {{ tab.label }}
          </button>
        </div>
      </div>

      <!-- Content Area -->
      <transition
        mode="out-in"
        enter-active-class="animate-in fade-in slide-in-from-bottom-4 duration-300"
        leave-active-class="animate-out fade-out slide-out-to-top-4 duration-200"
      >
        <!-- Plans Grid -->
        <div v-if="activeTab === 'plans'" key="plans-content" class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
          <div v-if="isLoading" class="col-span-full h-96 flex items-center justify-center">
            <div class="relative">
              <div class="w-16 h-16 border-4 border-primary-500/20 border-t-primary-500 rounded-full animate-spin" />
              <UIcon name="i-lucide-crown" class="absolute inset-0 m-auto w-6 h-6 text-primary-500" />
            </div>
          </div>

          <template v-else>
            <div
              v-for="plan in plans"
              :key="plan.uuid"
              class="group relative flex flex-col glass-card p-0 overflow-hidden hover:scale-[1.02] transition-all duration-500"
              :class="plan.is_default ? 'ring-2 ring-primary-500 shadow-2xl shadow-primary-500/10' : 'hover:border-slate-300 dark:hover:border-slate-600'"
            >
              <!-- Plan Visual Header -->
              <div class="h-24 bg-gradient-to-br from-slate-50 to-slate-200 dark:from-slate-800 dark:to-slate-900 p-6 flex justify-between items-start">
                <div class="p-3 bg-white dark:bg-slate-700 rounded-2xl shadow-lg shadow-black/5">
                  <UIcon :name="plan.pv > 500 ? 'i-lucide-gem' : 'i-lucide-zap'" class="w-8 h-8 text-primary-500" />
                </div>
                <UBadge v-if="plan.is_default" color="primary" variant="solid" class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest">
                  Best Value
                </UBadge>
              </div>

              <!-- Content -->
              <div class="flex-1 p-8 pt-0 -mt-8">
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-xl shadow-black/5 border border-slate-100 dark:border-slate-700/50">
                  <h3 class="text-xl font-black text-slate-900 dark:text-white mb-1">{{ plan.name }}</h3>
                  <div class="flex items-baseline gap-1 mb-4">
                    <span class="text-3xl font-black text-slate-900 dark:text-white">{{ plan.price_formatted }}</span>
                    <span class="text-slate-400 text-sm font-medium">/ year</span>
                  </div>

                  <div class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-700">
                    <div class="flex items-center justify-between text-sm">
                      <span class="text-slate-500 font-medium">Personal BP</span>
                      <span class="font-black text-primary-600 dark:text-primary-400">{{ plan.pv }} PV</span>
                    </div>
                    <div v-if="plan.max_team_members" class="flex items-center justify-between text-sm">
                      <span class="text-slate-500 font-medium">Net Capacity</span>
                      <span class="font-black text-slate-900 dark:text-white">{{ plan.max_team_members }} Members</span>
                    </div>
                  </div>
                </div>

                <div class="mt-8 space-y-4">
                  <h4 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Features & Benefits</h4>
                  <ul class="space-y-3">
                    <li v-for="benefit in plan.benefits" :key="benefit" class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-400 group/item">
                      <div class="mt-1 w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center group-hover/item:scale-110 transition-transform">
                        <UIcon name="i-lucide-check" class="w-3 h-3" />
                      </div>
                      <span class="flex-1">{{ benefit }}</span>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="p-8 pt-0">
                <UButton
                  block
                  size="xl"
                  :color="!status?.can_subscribe ? 'neutral' : 'primary'"
                  class="rounded-2xl font-black py-4 shadow-lg active:scale-95 transition-all"
                  :disabled="!status?.can_subscribe"
                  @click="selectPlan(plan)"
                >
                  <template #leading v-if="!status?.can_subscribe">
                    <UIcon name="i-lucide-lock" />
                  </template>
                  {{ !status?.can_subscribe ? 'Current Selection' : 'Activate Membership' }}
                </UButton>
                <p class="text-[10px] text-center text-slate-400 mt-4 uppercase font-bold tracking-widest">
                  Secure checkout with wallet credits
                </p>
              </div>
            </div>
          </template>
        </div>

        <!-- Status Details -->
        <div v-else-if="activeTab === 'status'" key="status-content" class="max-w-4xl mx-auto space-y-8">
          <div v-if="!status?.has_subscription" class="glass-card p-16 text-center">
            <div class="w-24 h-24 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6">
              <UIcon name="i-lucide-package-search" class="w-12 h-12 text-slate-400" />
            </div>
            <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-2">No Active Membership</h3>
            <p class="text-slate-500 dark:text-slate-400 mb-8 max-w-sm mx-auto">
              Unlock the full potential of Mintreu by choosing a membership plan today.
            </p>
            <UButton color="primary" size="lg" class="px-12 rounded-2xl font-bold" @click="activeTab = 'plans'">
              Browse Plans
            </UButton>
          </div>

          <template v-else-if="status.subscription">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="glass-card p-8">
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                  <UIcon name="i-lucide-shield-info" class="text-primary-500" />
                  Agreement Details
                </h3>
                <dl class="space-y-6">
                  <div v-for="item in [
                    { label: 'STAGE NAME', value: status.subscription.stage?.name },
                    { label: 'SUBSCRIPTION ID', value: status.subscription.uuid.split('-')[0].toUpperCase() },
                    { label: 'PURCHASE AMOUNT', value: status.subscription.amount_formatted },
                    { label: 'ACTIVATION DATE', value: formatDate(status.subscription.starts_at) },
                    { label: 'RENEWAL DATE', value: formatDate(status.subscription.expires_at) },
                    { label: 'LIFETIME RENEWALS', value: status.subscription.renewal_count }
                  ]" :key="item.label" class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-4 last:border-0 last:pb-0">
                    <dt class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ item.label }}</dt>
                    <dd class="text-sm font-bold text-slate-900 dark:text-white">{{ item.value || 'N/A' }}</dd>
                  </div>
                </dl>
              </div>

              <div class="space-y-8">
                <div class="glass-card p-8 bg-gradient-to-br from-primary-600 to-indigo-700 text-white border-none shadow-2xl shadow-primary-500/20">
                  <h3 class="text-lg font-black mb-6 flex items-center gap-2">
                    <UIcon name="i-lucide-bar-chart-3" />
                     Real-time Impact
                  </h3>
                  <div class="grid grid-cols-2 gap-8">
                    <div>
                      <span class="text-[10px] font-black text-white/60 uppercase tracking-widest">Total Earnings</span>
                      <p class="text-2xl font-black">{{ status.subscription.total_commission_formatted }}</p>
                    </div>
                    <div>
                      <span class="text-[10px] font-black text-white/60 uppercase tracking-widest">Active Level</span>
                      <p class="text-2xl font-black">{{ status.subscription.current_level?.name || 'Starter' }}</p>
                    </div>
                    <div>
                      <span class="text-[10px] font-black text-white/60 uppercase tracking-widest">Personal PV</span>
                      <p class="text-2xl font-black">{{ status.subscription.personal_pv }}</p>
                    </div>
                    <div>
                      <span class="text-[10px] font-black text-white/60 uppercase tracking-widest">Team PV</span>
                      <p class="text-2xl font-black">{{ status.subscription.team_pv }}</p>
                    </div>
                  </div>
                </div>

                <div class="glass-card p-8 border-dashed border-2">
                  <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">
                      <UIcon name="i-lucide-rotate-cw" class="w-6 h-6" />
                    </div>
                    <div>
                      <h4 class="font-bold text-slate-900 dark:text-white">Auto-Renewal</h4>
                      <p class="text-xs text-slate-500">Enable to ensure uninterrupted service</p>
                    </div>
                    <div class="ml-auto">
                      <UToggle :model-value="true" disabled />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>

        <!-- History Area -->
        <div v-else-if="activeTab === 'history'" key="history-content" class="max-w-4xl mx-auto space-y-4">
          <div v-if="isLoading" class="flex justify-center py-24">
            <UIcon name="i-lucide-loader-2" class="w-10 h-10 animate-spin text-primary-500" />
          </div>

          <template v-else-if="history.length > 0">
            <div
              v-for="sub in history"
              :key="sub.uuid"
              class="glass-card p-5 group hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-5">
                  <div class="w-14 h-14 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center text-slate-500 group-hover:bg-primary-500/10 group-hover:text-primary-500 transition-all">
                    <UIcon name="i-lucide-crown" class="w-7 h-7" />
                  </div>
                  <div>
                    <h4 class="font-black text-slate-900 dark:text-white">{{ sub.stage?.name || 'Generic Plan' }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Activated {{ formatDate(sub.created_at) }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-lg font-black text-slate-900 dark:text-white leading-none mb-2">{{ sub.amount_formatted }}</div>
                  <UBadge
                    :color="getStatusColor(sub.status)"
                    variant="soft"
                    size="sm"
                    class="rounded-full px-4 text-[10px] font-black uppercase tracking-widest"
                  >
                    {{ sub.status }}
                  </UBadge>
                </div>
              </div>
            </div>

            <!-- Page Meta -->
            <div v-if="historyMeta && historyMeta.last_page > 1" class="flex justify-center pt-8">
              <UPagination
                :model-value="historyMeta.current_page"
                :total="historyMeta.total"
                :page-count="historyMeta.per_page"
                class="rounded-2xl"
                @update:model-value="fetchHistory"
              />
            </div>
          </template>

          <div v-else class="glass-card h-64 flex flex-col items-center justify-center text-center p-12">
            <UIcon name="i-lucide-layers-3" class="w-12 h-12 text-slate-300 mb-4" />
            <p class="text-slate-500 font-bold uppercase tracking-widest text-xs">No records found</p>
          </div>
        </div>
      </transition>
    </div>

    <!-- Security Authentication Modal -->
    <UModal
      v-model:open="showPinModal"
      :ui="{
        content: 'bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-[40px]',
        container: 'flex items-center justify-center p-4'
      }"
    >
      <div class="p-8 sm:p-12 text-center">
        <div class="w-20 h-20 bg-primary-500/10 rounded-[30px] flex items-center justify-center mx-auto mb-8 text-primary-500">
          <UIcon name="i-lucide-shield-check" class="w-10 h-10" />
        </div>

        <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Authorize Security</h3>
        <p class="text-slate-500 dark:text-slate-400 mb-8">
          Enter your 6-digit transaction PIN to confirm the activation of <span class="text-primary-600 font-bold">{{ selectedPlan?.name }}</span>.
        </p>

        <UFormField>
          <UInput
            v-model="pinInput"
            type="password"
            placeholder="······"
            maxlength="6"
            pattern="[0-9]*"
            inputmode="numeric"
            class="text-3xl text-center tracking-[1rem] font-black h-20 rounded-3xl bg-slate-50 dark:bg-slate-800/50 border-none ring-offset-bg"
            :ui="{
              base: 'text-center pl-4 pr-0',
              input: 'placeholder:text-slate-300 dark:placeholder:text-slate-600'
            }"
            autofocus
          />
        </UFormField>

        <div class="grid grid-cols-2 gap-4 mt-10">
          <UButton
            size="xl"
            color="neutral"
            variant="ghost"
            class="rounded-2xl font-bold py-4 hover:bg-slate-100 dark:hover:bg-slate-800"
            :disabled="subscribing"
            @click="showPinModal = false; pinInput = ''"
          >
            Go Back
          </UButton>
          <UButton
            size="xl"
            color="primary"
            class="rounded-2xl font-black py-4 shadow-xl shadow-primary-500/20 active:scale-95"
            :loading="subscribing"
            :disabled="pinInput.length !== 6"
            @click="handleSubscribe"
          >
            Confirm
          </UButton>
        </div>
        <p class="text-[10px] text-slate-400 mt-6 uppercase font-black tracking-widest">
          Secured by Mintreu Vault System
        </p>
      </div>
    </UModal>
  </div>
</template>
