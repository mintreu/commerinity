export interface AffiliateLedgerEntry {
  uuid: string
  status: string
  status_label?: string
  bv: number
  pv: number
  depth: number
  order_id?: number | null
  order_item_id?: number | null
  eligible_at?: string | null
  confirmed_at?: string | null
  reversed_at?: string | null
  created_at?: string | null
}

export function useAffiliateLedger() {
  const config = useRuntimeConfig()
  const entries = ref<AffiliateLedgerEntry[]>([])
  const meta = ref<{ current_page: number, last_page: number, per_page: number, total: number } | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchLedger = async (params: { status?: string, page?: number, per_page?: number } = {}) => {
    loading.value = true
    error.value = null

    try {
      const query = new URLSearchParams()
      if (params.status) query.set('status', params.status)
      if (params.page) query.set('page', String(params.page))
      if (params.per_page) query.set('per_page', String(params.per_page))

      const response = await useSanctumFetch<any>(
        `${config.public.apiBase}/api/affiliate/ledger?${query.toString()}`
      )

      if (response?.success) {
        entries.value = response.data
        meta.value = response.meta
      }
    } catch (e: any) {
      error.value = e?.message || 'Failed to load affiliate ledger'
    } finally {
      loading.value = false
    }
  }

  return {
    entries,
    meta,
    loading,
    error,
    fetchLedger
  }
}
