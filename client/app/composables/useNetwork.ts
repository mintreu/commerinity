import type { Ref } from 'vue'

export interface AffiliateStats {
  genealogy: {
    uuid: string
    direct_count: number
    active_direct_count: number
    level_1_count: number
    level_2_count: number
    level_3_count: number
    level_4_count: number
    total_team_count: number
    active_team_count: number
    personal_pv: number
    team_pv: number
    personal_sales: number
    total_team_sales: number
    is_active: boolean
    current_stage: { name: string, slug: string } | null
    current_level: { name: string, level_number: number } | null
    highest_level: { name: string } | null
  } | null
  earnings: {
    total: number
    total_formatted: string
    pending: number
    pending_formatted: string
    this_month: number
    this_month_formatted: string
  }
  referral_link: string
}

export interface TeamMember {
  id: number
  uuid: string
  name: string
  email?: string
  type: string
  status: string
  created_at: string
}

export interface UplineMember {
  id: number
  uuid: string
  name: string
  type: string
  level: number | null
}

export const useNetwork = () => {
  const config = useRuntimeConfig()

  const stats: Ref<AffiliateStats | null> = ref(null)
  const team: Ref<TeamMember[]> = ref([])
  const teamMeta: Ref<{ current_page: number, last_page: number, per_page: number, total: number } | null> = ref(null)
  const upline: Ref<UplineMember[]> = ref([])
  const isLoading = ref(false)
  const error: Ref<string | null> = ref(null)

  const fetchStats = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<{ success: boolean, data: AffiliateStats }>(
        `${config.public.apiBase}/api/affiliate/stats`
      )
      if (response?.success) {
        stats.value = response.data
      }
      return response
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch Affiliate stats'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const fetchTeam = async (page = 1, perPage = 20) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<{
        success: boolean
        data: {
          data: TeamMember[]
          current_page: number
          last_page: number
          per_page: number
          total: number
        }
      }>(`${config.public.apiBase}/api/affiliate/children?page=${page}&per_page=${perPage}`)
      if (response?.success) {
        team.value = response.data.data
        teamMeta.value = {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          per_page: response.data.per_page,
          total: response.data.total
        }
      }
      return response
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch team'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const fetchUpline = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<{ success: boolean, data: UplineMember[] }>(
        `${config.public.apiBase}/api/affiliate/upline`
      )
      if (response?.success) {
        upline.value = response.data
      }
      return response
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch upline'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    stats,
    team,
    teamMeta,
    upline,
    isLoading,
    error,
    fetchStats,
    fetchTeam,
    fetchUpline
  }
}
