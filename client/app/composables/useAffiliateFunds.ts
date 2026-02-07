export interface AffiliateFundAccount {
  id: number
  fund_type: string
  balance: number
  balance_formatted?: string
  total_credited: number
  total_credited_formatted?: string
  total_debited: number
  total_debited_formatted?: string
  is_active: boolean
}

export interface AffiliateFundTransaction {
  id: number
  type: string
  type_label?: string
  amount: number
  amount_formatted?: string
  balance_after?: number | null
  balance_after_formatted?: string
  notes?: string | null
  created_at?: string | null
}

export function useAffiliateFunds() {
  const config = useRuntimeConfig()
  const accounts = ref<AffiliateFundAccount[]>([])
  const transactions = ref<AffiliateFundTransaction[]>([])
  const transactionsMeta = ref<{ current_page: number; last_page: number; per_page: number; total: number } | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchAccounts = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<any>(`${config.public.apiBase}/api/affiliate/funds`)
      if (response?.success) {
        accounts.value = response.data
      }
    } catch (e: any) {
      error.value = e?.message || 'Failed to load fund accounts'
    } finally {
      loading.value = false
    }
  }

  const fetchTransactions = async (fundType: string, params: { page?: number; per_page?: number } = {}) => {
    loading.value = true
    error.value = null
    try {
      const query = new URLSearchParams()
      if (params.page) query.set('page', String(params.page))
      if (params.per_page) query.set('per_page', String(params.per_page))
      const response = await useSanctumFetch<any>(
        `${config.public.apiBase}/api/affiliate/funds/${fundType}/transactions?${query.toString()}`
      )
      if (response?.success) {
        transactions.value = response.data
        transactionsMeta.value = response.meta
      }
    } catch (e: any) {
      error.value = e?.message || 'Failed to load fund transactions'
    } finally {
      loading.value = false
    }
  }

  return {
    accounts,
    transactions,
    transactionsMeta,
    loading,
    error,
    fetchAccounts,
    fetchTransactions
  }
}
