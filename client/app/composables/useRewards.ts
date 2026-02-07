export interface RewardEarning {
  uuid: string
  reward_type: string
  reward_type_label?: string
  coins?: number
  voucher_code?: string | null
  status?: string
  status_label?: string
  is_used?: boolean
  claimed_at?: string | null
  expires_at?: string | null
  created_at?: string | null
}

export function useRewards() {
  const config = useRuntimeConfig()
  const rewards = ref<RewardEarning[]>([])
  const meta = ref<{ current_page: number; last_page: number; per_page: number; total: number } | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchRewards = async (params: { status?: string; reward_type?: string; used?: boolean; page?: number; per_page?: number } = {}) => {
    loading.value = true
    error.value = null
    try {
      const query = new URLSearchParams()
      if (params.status) query.set('status', params.status)
      if (params.reward_type) query.set('reward_type', params.reward_type)
      if (params.used !== undefined) query.set('used', String(params.used))
      if (params.page) query.set('page', String(params.page))
      if (params.per_page) query.set('per_page', String(params.per_page))
      const response = await useSanctumFetch<any>(
        `${config.public.apiBase}/api/rewards?${query.toString()}`
      )
      if (response?.success) {
        rewards.value = response.data
        meta.value = response.meta
      }
    } catch (e: any) {
      error.value = e?.message || 'Failed to load rewards'
    } finally {
      loading.value = false
    }
  }

  const markUsed = async (uuid: string) => {
    loading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<any>(
        `${config.public.apiBase}/api/rewards/${uuid}/use`,
        { method: 'POST' }
      )
      if (response?.success) {
        const updated = response.data
        rewards.value = rewards.value.map((r) => (r.uuid === uuid ? updated : r))
      }
      return response
    } catch (e: any) {
      error.value = e?.message || 'Failed to mark reward used'
      return null
    } finally {
      loading.value = false
    }
  }

  return {
    rewards,
    meta,
    loading,
    error,
    fetchRewards,
    markUsed
  }
}
