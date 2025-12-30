<script setup lang="ts">
/**
 * Subscription Management Page
 * Shows current subscription status, available plans, and subscription history
 */

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const { plans, status, history, historyMeta, isLoading, fetchPlans, fetchStatus, fetchHistory, subscribe } = useSubscription()
const { wallet, fetchWallet } = useWallet()
const toast = useToast()

const activeTab = ref('plans')
const selectedPlan = ref<string | null>(null)
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

const selectPlan = (planUuid: string) => {
  if (!status.value?.can_subscribe) {
    toast.add({
      title: 'Already Subscribed',
      description: 'You already have an active subscription. Upgrade feature coming soon.',
      color: 'warning'
    })
    return
  }
  selectedPlan.value = planUuid
  showPinModal.value = true
}

const handleSubscribe = async () => {
  if (!selectedPlan.value || !pinInput.value) return

  subscribing.value = true
  try {
    const response = await subscribe(selectedPlan.value, pinInput.value)
    if (response?.success) {
      toast.add({
        title: 'Success!',
        description: response.message || 'Subscription activated successfully!',
        color: 'success'
      })
      showPinModal.value = false
      pinInput.value = ''
      selectedPlan.value = null
      await fetchWallet()
    }
  }
  catch (err: unknown) {
    const error = err as { data?: { message?: string; requires_pin_setup?: boolean } }
    if (error.data?.requires_pin_setup) {
      toast.add({
        title: 'PIN Required',
        description: 'Please set up your wallet PIN first.',
        color: 'warning'
      })
      navigateTo('/wallet/pin-setup')
      return
    }
    toast.add({
      title: 'Error',
      description: error.data?.message || 'Failed to subscribe. Please try again.',
      color: 'error'
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
  const colors: Record<string, 'success' | 'warning' | 'error' | 'info'> = {
    active: 'success',
    pending: 'warning',
    expired: 'error',
    cancelled: 'error'
  }
  return colors[statusStr] || 'info'
}

const tabs = [
  { label: 'Plans', value: 'plans', icon: 'i-lucide-crown' },
  { label: 'My Subscription', value: 'status', icon: 'i-lucide-user-check' },
  { label: 'History', value: 'history', icon: 'i-lucide-history' }
]
</script>

<template>
  <div class="max-w-6xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Membership Plans</h1>
        <p class="text-gray-500 dark:text-gray-400">Choose a plan to unlock premium features and earn commissions</p>
      </div>

      <!-- Wallet Balance -->
      <div class="glass-card p-4 min-w-[200px]">
        <div class="text-sm text-gray-500 dark:text-gray-400">Wallet Balance</div>
        <div class="text-xl font-bold text-gray-900 dark:text-white">
          {{ wallet?.available_balance_formatted || '₹0.00' }}
        </div>
      </div>
    </div>

    <!-- Current Subscription Banner -->
    <div
      v-if="status?.has_subscription && status.subscription"
      class="glass-card p-6 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 border border-primary-200 dark:border-primary-800"
    >
      <div class="flex flex-col md:flex-row md:items-center gap-4">
        <div class="w-16 h-16 bg-primary-500 rounded-2xl flex items-center justify-center">
          <UIcon name="i-lucide-crown" class="w-8 h-8 text-white" />
        </div>
        <div class="flex-1">
          <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
              {{ status.subscription.stage?.name || 'Active Plan' }}
            </h2>
            <UBadge :color="getStatusColor(status.subscription.status)" size="sm">
              {{ status.subscription.status }}
            </UBadge>
          </div>
          <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
              <span class="text-gray-500 dark:text-gray-400">Expires</span>
              <p class="font-medium text-gray-900 dark:text-white">{{ formatDate(status.subscription.expires_at) }}</p>
            </div>
            <div>
              <span class="text-gray-500 dark:text-gray-400">Days Left</span>
              <p class="font-medium text-gray-900 dark:text-white">{{ status.subscription.days_remaining ?? 'N/A' }}</p>
            </div>
            <div>
              <span class="text-gray-500 dark:text-gray-400">Commission Earned</span>
              <p class="font-medium text-green-600 dark:text-green-400">{{ status.subscription.total_commission_formatted }}</p>
            </div>
            <div>
              <span class="text-gray-500 dark:text-gray-400">Personal PV</span>
              <p class="font-medium text-gray-900 dark:text-white">{{ status.subscription.personal_pv }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <UTabs
      v-model="activeTab"
      :items="tabs"
      class="w-full"
      @update:model-value="(val) => val === 'history' && loadHistory()"
    />

    <!-- Plans Tab -->
    <div v-if="activeTab === 'plans'" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
      <div v-if="isLoading" class="col-span-full flex justify-center py-12">
        <UIcon name="i-lucide-loader-2" class="w-8 h-8 animate-spin text-primary-500" />
      </div>

      <template v-else>
        <div
          v-for="plan in plans"
          :key="plan.uuid"
          class="glass-card overflow-hidden transition-all hover:shadow-lg"
          :class="plan.is_default ? 'ring-2 ring-primary-500' : ''"
        >
          <!-- Plan Header -->
          <div class="p-6 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ plan.name }}</h3>
              <UBadge v-if="plan.is_default" color="primary" size="sm">Popular</UBadge>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ plan.description || 'Premium membership plan' }}</p>
          </div>

          <!-- Pricing -->
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-baseline gap-1">
              <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ plan.price_formatted }}</span>
            </div>
            <div v-if="plan.discount > 0" class="mt-1 text-sm">
              <span class="line-through text-gray-400">{{ plan.base_price_formatted }}</span>
              <span class="ml-2 text-green-600 dark:text-green-400">Save {{ plan.discount_formatted }}</span>
            </div>
            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              + {{ plan.tax_amount_formatted }} GST
            </div>
          </div>

          <!-- Benefits -->
          <div class="p-6 space-y-3">
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
              <UIcon name="i-lucide-zap" class="w-4 h-4 text-primary-500" />
              <span>{{ plan.pv }} PV Points</span>
            </div>
            <div v-if="plan.max_team_members" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
              <UIcon name="i-lucide-users" class="w-4 h-4 text-primary-500" />
              <span>Up to {{ plan.max_team_members }} team members</span>
            </div>
            <div v-for="benefit in plan.benefits.slice(0, 4)" :key="benefit" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
              <UIcon name="i-lucide-check" class="w-4 h-4 text-green-500" />
              <span>{{ benefit }}</span>
            </div>
          </div>

          <!-- Action -->
          <div class="p-6 pt-0">
            <UButton
              block
              :color="!status?.can_subscribe ? 'neutral' : 'primary'"
              :disabled="!status?.can_subscribe"
              @click="selectPlan(plan.uuid)"
            >
              {{ !status?.can_subscribe ? 'Already Subscribed' : 'Subscribe Now' }}
            </UButton>
          </div>
        </div>
      </template>

      <div v-if="!isLoading && plans.length === 0" class="col-span-full text-center py-12">
        <UIcon name="i-lucide-package-x" class="w-12 h-12 mx-auto text-gray-400 mb-4" />
        <p class="text-gray-500 dark:text-gray-400">No plans available at the moment.</p>
      </div>
    </div>

    <!-- Status Tab -->
    <div v-if="activeTab === 'status'" class="space-y-6">
      <div v-if="isLoading" class="flex justify-center py-12">
        <UIcon name="i-lucide-loader-2" class="w-8 h-8 animate-spin text-primary-500" />
      </div>

      <template v-else-if="status?.has_subscription && status.subscription">
        <div class="grid gap-6 md:grid-cols-2">
          <!-- Subscription Details -->
          <div class="glass-card p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Subscription Details</h3>
            <dl class="space-y-4">
              <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Plan</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ status.subscription.stage?.name }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                <dd>
                  <UBadge :color="getStatusColor(status.subscription.status)">{{ status.subscription.status }}</UBadge>
                </dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Amount Paid</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ status.subscription.amount_formatted }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Started</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ formatDate(status.subscription.starts_at) }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Expires</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ formatDate(status.subscription.expires_at) }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Renewals</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ status.subscription.renewal_count }}</dd>
              </div>
            </dl>
          </div>

          <!-- Performance Stats -->
          <div class="glass-card p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Performance</h3>
            <div class="grid grid-cols-2 gap-4">
              <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
                <div class="text-sm text-green-600 dark:text-green-400">Commission Earned</div>
                <div class="text-xl font-bold text-green-700 dark:text-green-300">{{ status.subscription.total_commission_formatted }}</div>
              </div>
              <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                <div class="text-sm text-blue-600 dark:text-blue-400">Personal PV</div>
                <div class="text-xl font-bold text-blue-700 dark:text-blue-300">{{ status.subscription.personal_pv }}</div>
              </div>
              <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                <div class="text-sm text-purple-600 dark:text-purple-400">Team PV</div>
                <div class="text-xl font-bold text-purple-700 dark:text-purple-300">{{ status.subscription.team_pv }}</div>
              </div>
              <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl">
                <div class="text-sm text-amber-600 dark:text-amber-400">Current Level</div>
                <div class="text-xl font-bold text-amber-700 dark:text-amber-300">{{ status.subscription.current_level?.name || 'N/A' }}</div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <div v-else class="glass-card p-12 text-center">
        <UIcon name="i-lucide-package-open" class="w-16 h-16 mx-auto text-gray-400 mb-4" />
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Active Subscription</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6">Subscribe to a plan to start earning commissions and unlock premium features.</p>
        <UButton color="primary" @click="activeTab = 'plans'">View Plans</UButton>
      </div>
    </div>

    <!-- History Tab -->
    <div v-if="activeTab === 'history'" class="space-y-4">
      <div v-if="isLoading" class="flex justify-center py-12">
        <UIcon name="i-lucide-loader-2" class="w-8 h-8 animate-spin text-primary-500" />
      </div>

      <template v-else-if="history.length > 0">
        <div
          v-for="sub in history"
          :key="sub.uuid"
          class="glass-card p-4"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                <UIcon name="i-lucide-crown" class="w-6 h-6 text-primary-600 dark:text-primary-400" />
              </div>
              <div>
                <h4 class="font-medium text-gray-900 dark:text-white">{{ sub.stage?.name || 'Subscription' }}</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(sub.created_at) }}</p>
              </div>
            </div>
            <div class="text-right">
              <div class="font-medium text-gray-900 dark:text-white">{{ sub.amount_formatted }}</div>
              <UBadge :color="getStatusColor(sub.status)" size="sm">{{ sub.status }}</UBadge>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="historyMeta && historyMeta.last_page > 1" class="flex justify-center pt-4">
          <UPagination
            :model-value="historyMeta.current_page"
            :total="historyMeta.total"
            :page-count="historyMeta.per_page"
            @update:model-value="fetchHistory"
          />
        </div>
      </template>

      <div v-else class="glass-card p-12 text-center">
        <UIcon name="i-lucide-history" class="w-12 h-12 mx-auto text-gray-400 mb-4" />
        <p class="text-gray-500 dark:text-gray-400">No subscription history yet.</p>
      </div>
    </div>

    <!-- PIN Modal -->
    <UModal v-model:open="showPinModal">
      <template #content>
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Enter Wallet PIN</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Enter your 6-digit wallet PIN to confirm subscription.</p>

          <UFormField label="Wallet PIN">
            <UInput
              v-model="pinInput"
              type="password"
              placeholder="Enter 6-digit PIN"
              maxlength="6"
              pattern="[0-9]*"
              inputmode="numeric"
              class="text-center tracking-widest"
            />
          </UFormField>

          <div class="flex gap-3 mt-6">
            <UButton
              block
              color="neutral"
              variant="outline"
              :disabled="subscribing"
              @click="showPinModal = false; pinInput = ''"
            >
              Cancel
            </UButton>
            <UButton
              block
              color="primary"
              :loading="subscribing"
              :disabled="pinInput.length !== 6"
              @click="handleSubscribe"
            >
              Confirm & Subscribe
            </UButton>
          </div>
        </div>
      </template>
    </UModal>
  </div>
</template>


