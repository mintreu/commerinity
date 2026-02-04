import type { DashboardProgram, PaginatedResponse } from '~/types/dashboard'

export const useDashboardPrograms = () => {
  const config = useRuntimeConfig()
  const baseUrl = `${config.public.apiBase}/api/dashboard/programs`

  const fetchList = async (params: Record<string, unknown> = {}): Promise<PaginatedResponse<DashboardProgram>> => {
    const response = await useSanctumFetch<PaginatedResponse<DashboardProgram>>(baseUrl, {
      query: params
    })

    return {
      items: response.data,
      meta: response.meta
    }
  }

  const create = async (payload: FormData | Record<string, unknown>) => {
    return useSanctumFetch<{ data: DashboardProgram }>(baseUrl, {
      method: 'POST',
      body: payload
    })
  }

  const show = async (uuid: string) => {
    return useSanctumFetch<{ data: DashboardProgram }>(`${baseUrl}/${uuid}`)
  }

  return {
    fetchList,
    create,
    show
  }
}
