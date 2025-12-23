/**
 * Trends Composable
 * Handles fetching trend/chart data from the API
 */

export type Period = 'today' | 'yesterday' | 'week' | 'last_week' | 'month' | 'last_month' | 'quarter' | 'year' | 'last_year' | 'custom'
export type Interval = 'hour' | 'day' | 'week' | 'month' | 'year'

export interface ChartData {
  labels: string[]
  datasets: Array<{
    label: string
    data: number[]
    backgroundColor?: string
    borderColor?: string
  }>
}

export interface TrendResponse<T = ChartData> {
  success: boolean
  data: T & {
    summary?: Record<string, any>
  }
  meta: {
    period: string
    interval?: string
    generated_at: string
  }
}

export interface ComparisonData {
  current: {
    credits: number
    debits: number
    net: number
    count: number
  }
  previous: {
    credits: number
    debits: number
    net: number
    count: number
  }
  changes: {
    credits_change: number
    debits_change: number
    count_change: number
  }
}

export interface DashboardSummary {
  wallet: ComparisonData
  commissions: {
    current: Record<string, number>
    previous: Record<string, number>
    changes: Record<string, number>
  }
  team: {
    total_members: number
    active_members: number
    direct_referrals: number
    levels: Record<string, number>
  }
}

export interface TrendParams {
  period?: Period
  interval?: Interval
  start_date?: string
  end_date?: string
}

export const useTrends = () => {
  const config = useRuntimeConfig()
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Helper to build query string
  const buildQueryString = (params: TrendParams): string => {
    const searchParams = new URLSearchParams()
    if (params.period) searchParams.append('period', params.period)
    if (params.interval) searchParams.append('interval', params.interval)
    if (params.start_date) searchParams.append('start_date', params.start_date)
    if (params.end_date) searchParams.append('end_date', params.end_date)
    const queryString = searchParams.toString()
    return queryString ? `?${queryString}` : ''
  }

  // Generic fetch function
  const fetchTrend = async <T = ChartData>(endpoint: string, params: TrendParams = {}): Promise<TrendResponse<T> | null> => {
    loading.value = true
    error.value = null
    try {
      const queryString = buildQueryString(params)
      const response = await useSanctumFetch<TrendResponse<T>>(
        `${config.public.apiBase}/api/trends/${endpoint}${queryString}`
      )
      return response
    } catch (e: any) {
      error.value = e.data?.message || 'Failed to fetch trend data'
      console.error(`Failed to fetch ${endpoint}:`, e)
      return null
    } finally {
      loading.value = false
    }
  }

  // ========================================
  // Dashboard Summary
  // ========================================

  const fetchDashboardSummary = async (period: Period = 'month') => {
    return fetchTrend<DashboardSummary>('dashboard', { period })
  }

  // ========================================
  // Wallet Trends
  // ========================================

  const fetchWalletBalance = async (params: TrendParams = {}) => {
    return fetchTrend('wallet/balance', params)
  }

  const fetchWalletCreditDebit = async (params: TrendParams = {}) => {
    return fetchTrend('wallet/credit-debit', params)
  }

  const fetchWalletActivity = async (params: TrendParams = {}) => {
    return fetchTrend('wallet/activity', params)
  }

  const fetchWalletComparison = async (period: Period = 'month') => {
    return fetchTrend<ComparisonData>('wallet/comparison', { period })
  }

  // ========================================
  // Commission Trends
  // ========================================

  const fetchCommissionEarnings = async (params: TrendParams = {}) => {
    return fetchTrend('commissions/earnings', params)
  }

  const fetchCommissionByType = async (params: TrendParams = {}) => {
    return fetchTrend('commissions/by-type', params)
  }

  const fetchCommissionComparison = async (period: Period = 'month') => {
    return fetchTrend('commissions/comparison', { period })
  }

  // ========================================
  // Team Trends
  // ========================================

  const fetchTeamGrowth = async (params: TrendParams = {}) => {
    return fetchTrend('team/growth', params)
  }

  const fetchTeamLevels = async () => {
    return fetchTrend('team/levels', {})
  }

  const fetchTeamActivity = async (params: TrendParams = {}) => {
    return fetchTrend('team/activity', params)
  }

  // ========================================
  // Chart.js Helper Functions
  // ========================================

  /**
   * Convert API response to Chart.js compatible format
   */
  const toChartJsData = (response: TrendResponse | null) => {
    if (!response?.success || !response.data) {
      return {
        labels: [],
        datasets: []
      }
    }

    return {
      labels: response.data.labels || [],
      datasets: (response.data.datasets || []).map(dataset => ({
        ...dataset,
        fill: false,
        tension: 0.4,
        borderWidth: 2,
        pointRadius: 3,
        pointHoverRadius: 5,
      }))
    }
  }

  /**
   * Get default chart options for line charts
   */
  const getLineChartOptions = (title?: string) => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: true,
        position: 'top' as const,
      },
      title: {
        display: !!title,
        text: title,
      },
      tooltip: {
        mode: 'index' as const,
        intersect: false,
      },
    },
    scales: {
      x: {
        grid: {
          display: false,
        },
      },
      y: {
        beginAtZero: true,
        grid: {
          color: 'rgba(0, 0, 0, 0.1)',
        },
      },
    },
    interaction: {
      mode: 'nearest' as const,
      axis: 'x' as const,
      intersect: false,
    },
  })

  /**
   * Get default chart options for bar charts
   */
  const getBarChartOptions = (title?: string) => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: true,
        position: 'top' as const,
      },
      title: {
        display: !!title,
        text: title,
      },
    },
    scales: {
      x: {
        grid: {
          display: false,
        },
      },
      y: {
        beginAtZero: true,
      },
    },
  })

  /**
   * Get default chart options for doughnut/pie charts
   */
  const getDoughnutChartOptions = (title?: string) => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: true,
        position: 'right' as const,
      },
      title: {
        display: !!title,
        text: title,
      },
    },
  })

  /**
   * Format percentage change with arrow indicator
   */
  const formatPercentageChange = (value: number): { text: string; color: string; icon: string } => {
    if (value > 0) {
      return {
        text: `+${value.toFixed(1)}%`,
        color: 'text-green-600',
        icon: 'i-lucide-trending-up',
      }
    } else if (value < 0) {
      return {
        text: `${value.toFixed(1)}%`,
        color: 'text-red-600',
        icon: 'i-lucide-trending-down',
      }
    }
    return {
      text: '0%',
      color: 'text-gray-500',
      icon: 'i-lucide-minus',
    }
  }

  /**
   * Format currency value
   */
  const formatCurrency = (value: number, currency: string = 'INR'): string => {
    return new Intl.NumberFormat('en-IN', {
      style: 'currency',
      currency,
      maximumFractionDigits: 0,
    }).format(value)
  }

  return {
    // State
    loading,
    error,

    // Dashboard
    fetchDashboardSummary,

    // Wallet
    fetchWalletBalance,
    fetchWalletCreditDebit,
    fetchWalletActivity,
    fetchWalletComparison,

    // Commissions
    fetchCommissionEarnings,
    fetchCommissionByType,
    fetchCommissionComparison,

    // Team
    fetchTeamGrowth,
    fetchTeamLevels,
    fetchTeamActivity,

    // Helpers
    toChartJsData,
    getLineChartOptions,
    getBarChartOptions,
    getDoughnutChartOptions,
    formatPercentageChange,
    formatCurrency,
  }
}
