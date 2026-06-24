import type { DashboardChallenge } from '~/types/dashboard'

export const useDashboardChallenges = () => {
  const config = useRuntimeConfig()
  const baseUrl = `${config.public.apiBase}/api/dashboard/challenges`

  const fetchAll = async (params: Record<string, unknown> = {}) => {
    return useSanctumFetch<{
      data: DashboardChallenge[]
    }>(baseUrl, {
      query: params
    })
  }

  const fetchActive = async () => {
    return useSanctumFetch<{
      data: DashboardChallenge[]
    }>(`${baseUrl}/active`)
  }

  const show = async (uuid: string) => {
    return useSanctumFetch<{ data: DashboardChallenge }>(`${baseUrl}/${uuid}`)
  }

  return {
    fetchAll,
    fetchActive,
    show
  }
}
