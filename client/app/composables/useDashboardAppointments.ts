import type { DashboardAppointment, PaginatedResponse } from '~/types/dashboard'

export const useDashboardAppointments = () => {
  const config = useRuntimeConfig()
  const baseUrl = `${config.public.apiBase}/api/dashboard/appointments`

  const fetchList = async (params: Record<string, unknown> = {}): Promise<PaginatedResponse<DashboardAppointment>> => {
    const response = await useSanctumFetch<PaginatedResponse<DashboardAppointment>>(baseUrl, {
      query: params
    })

    return {
      items: response.data,
      meta: response.meta
    }
  }

  const create = async (payload: Record<string, unknown>) => {
    return useSanctumFetch<{ data: DashboardAppointment }>(baseUrl, {
      method: 'POST',
      body: payload
    })
  }

  const show = async (uuid: string) => {
    return useSanctumFetch<{ data: DashboardAppointment }>(`${baseUrl}/${uuid}`)
  }

  return {
    fetchList,
    create,
    show
  }
}
