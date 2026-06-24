import type { Ref } from 'vue'

export interface AdvisorEarningsSummary {
  total_sale_volume: number
  total_sale_volume_formatted: string
  total_earnings: number
  total_earnings_formatted: string
}

export const useAdvisorEarnings = () => {
  const config = useRuntimeConfig()

  const summary: Ref<AdvisorEarningsSummary | null> = ref(null)
  const isLoading = ref(false)
  const error: Ref<string | null> = ref(null)

  const fetchSummary = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<{ success: boolean, data: AdvisorEarningsSummary }>(
        `${config.public.apiBase}/api/advisor/earnings`
      )
      if (response?.success) {
        summary.value = response.data
      }
      return response
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch advisor earnings'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    summary,
    isLoading,
    error,
    fetchSummary
  }
}
