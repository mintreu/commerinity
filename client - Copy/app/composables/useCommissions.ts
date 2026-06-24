import type { Ref } from 'vue'

export interface CommissionSummary {
  total_earnings: number
  total_earnings_formatted: string
  pending_earnings: number
  pending_earnings_formatted: string
  this_month_earnings: number
  this_month_earnings_formatted: string
  last_month_earnings: number
  last_month_earnings_formatted: string
  growth_percent: number
  total_commissions: number
}

export interface Commission {
  uuid: string
  type: string
  type_label: string
  level: number | null
  status: string
  status_label: string
  gross_amount: number
  gross_amount_formatted: string
  tds_amount: number
  tds_amount_formatted: string
  admin_fee: number
  admin_fee_formatted: string
  net_amount: number
  net_amount_formatted: string
  description: string | null
  from_user: { uuid: string, name: string } | null
  commission_date: string | null
  period_key: string | null
  paid_at: string | null
  created_at: string
  rate_percent?: number
  base_amount?: number
  base_amount_formatted?: string
  metadata?: Record<string, unknown>
  approved_at?: string
  transaction?: { uuid: string, reference_number: string }
}

export interface CommissionByType {
  type: string
  type_label: string
  total: number
  total_formatted: string
  count: number
}

export interface MonthlyEarning {
  period: string
  total: number
  total_formatted: string
  count: number
}

export const useCommissions = () => {
  const config = useRuntimeConfig()

  const summary: Ref<CommissionSummary | null> = ref(null)
  const commissions: Ref<Commission[]> = ref([])
  const commissionsMeta: Ref<{ current_page: number, last_page: number, per_page: number, total: number } | null> = ref(null)
  const byType: Ref<CommissionByType[]> = ref([])
  const monthly: Ref<MonthlyEarning[]> = ref([])
  const isLoading = ref(false)
  const error: Ref<string | null> = ref(null)

  const fetchSummary = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<{ success: boolean, data: CommissionSummary }>(
        `${config.public.apiBase}/api/commissions/summary`
      )
      if (response?.success) {
        summary.value = response.data
      }
      return response
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch summary'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const fetchCommissions = async (params: {
    page?: number
    status?: string
    type?: string
    period?: string
    per_page?: number
  } = {}) => {
    isLoading.value = true
    error.value = null
    try {
      const queryParams = new URLSearchParams()
      if (params.page) queryParams.append('page', params.page.toString())
      if (params.status) queryParams.append('status', params.status)
      if (params.type) queryParams.append('type', params.type)
      if (params.period) queryParams.append('period', params.period)
      if (params.per_page) queryParams.append('per_page', params.per_page.toString())

      const response = await useSanctumFetch<{
        success: boolean
        data: Commission[]
        meta: { current_page: number, last_page: number, per_page: number, total: number }
      }>(`${config.public.apiBase}/api/commissions?${queryParams.toString()}`)
      if (response?.success) {
        commissions.value = response.data
        commissionsMeta.value = response.meta
      }
      return response
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch commissions'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const fetchCommission = async (uuid: string) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<{ success: boolean, data: Commission }>(
        `${config.public.apiBase}/api/commissions/${uuid}`
      )
      return response
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch commission'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const fetchByType = async (period?: string) => {
    isLoading.value = true
    error.value = null
    try {
      const url = period
        ? `${config.public.apiBase}/api/commissions/by-type?period=${period}`
        : `${config.public.apiBase}/api/commissions/by-type`
      const response = await useSanctumFetch<{ success: boolean, data: CommissionByType[] }>(url)
      if (response?.success) {
        byType.value = response.data
      }
      return response
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch breakdown'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const fetchMonthly = async (months = 12) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<{ success: boolean, data: MonthlyEarning[] }>(
        `${config.public.apiBase}/api/commissions/monthly?months=${months}`
      )
      if (response?.success) {
        monthly.value = response.data
      }
      return response
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch monthly'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    summary,
    commissions,
    commissionsMeta,
    byType,
    monthly,
    isLoading,
    error,
    fetchSummary,
    fetchCommissions,
    fetchCommission,
    fetchByType,
    fetchMonthly
  }
}
