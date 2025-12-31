import type { Ref } from 'vue'

export interface SubscriptionPlan {
  uuid: string
  name: string
  slug: string
  description: string | null
  price: number
  price_formatted: string
  base_price: number
  base_price_formatted: string
  discount: number
  discount_formatted: string
  tax_amount: number
  tax_amount_formatted: string
  pv: number
  benefits: string[]
  max_team_members: number | null
  is_default: boolean
}

export interface SubscriptionStage {
  name: string
  slug: string
}

export interface SubscriptionLevel {
  name: string
  level_number?: number
}

export interface Subscription {
  uuid: string
  stage: SubscriptionStage | null
  current_level: SubscriptionLevel | null
  highest_level: SubscriptionLevel | null
  amount: number
  amount_formatted: string
  status: string
  is_active: boolean
  starts_at: string | null
  expires_at: string | null
  days_remaining: number | null
  total_commission_earned: number
  total_commission_formatted: string
  personal_pv: number
  team_pv: number
  renewal_count: number
  paid_at: string | null
  created_at: string
}

export interface SubscriptionStatus {
  has_subscription: boolean
  subscription: Subscription | null
  can_subscribe: boolean
  can_upgrade?: boolean
}

export const useSubscription = () => {
  const config = useRuntimeConfig()

  const plans: Ref<SubscriptionPlan[]> = ref([])
  const status: Ref<SubscriptionStatus | null> = ref(null)
  const history: Ref<Subscription[]> = ref([])
  const historyMeta: Ref<{ current_page: number; last_page: number; per_page: number; total: number } | null> = ref(null)
  const isLoading = ref(false)
  const error: Ref<string | null> = ref(null)

  const fetchPlans = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<{ success: boolean; data: { plans: SubscriptionPlan[] } }>(
        `${config.public.apiBase}/api/subscription/plans`
      )
      if (response?.success) {
        plans.value = response.data.plans
      }
      return response
    }
    catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch plans'
      throw err
    }
    finally {
      isLoading.value = false
    }
  }

  const fetchStatus = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<{ success: boolean; data: SubscriptionStatus }>(
        `${config.public.apiBase}/api/subscription/status`
      )
      if (response?.success) {
        status.value = response.data
      }
      return response
    }
    catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch status'
      throw err
    }
    finally {
      isLoading.value = false
    }
  }

  const subscribe = async (planUuid: string, pin: string) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<{
        success: boolean
        message: string
        data?: {
          subscription: Subscription
          transaction_reference: string
          new_balance_formatted: string
        }
        requires_pin_setup?: boolean
      }>(`${config.public.apiBase}/api/subscription/subscribe`, {
        method: 'POST',
        body: {
          plan_uuid: planUuid,
          pin,
        },
      })
      if (response?.success) {
        await fetchStatus()
      }
      return response
    }
    catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to subscribe'
      throw err
    }
    finally {
      isLoading.value = false
    }
  }

  const fetchHistory = async (page = 1) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<{
        success: boolean
        data: Subscription[]
        meta: { current_page: number; last_page: number; per_page: number; total: number }
      }>(`${config.public.apiBase}/api/subscription/history?page=${page}`)
      if (response?.success) {
        history.value = response.data
        historyMeta.value = response.meta
      }
      return response
    }
    catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch history'
      throw err
    }
    finally {
      isLoading.value = false
    }
  }

  return {
    plans,
    status,
    history,
    historyMeta,
    isLoading,
    error,
    fetchPlans,
    fetchStatus,
    subscribe,
    fetchHistory,
  }
}
