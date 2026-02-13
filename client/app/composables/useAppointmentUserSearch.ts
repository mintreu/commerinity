export const useAppointmentUserSearch = () => {
  const config = useRuntimeConfig()
  const search = async (query: string, type = 'user', scope = 'all') => {
    if (!query) {
      return []
    }
    const response = await useSanctumFetch<{ data: Array<{ uuid: string, label: string, details: string, type: string }> }>(
      `${config.public.apiBase}/api/dashboard/appointments/search-users`,
      {
        query: { q: query, type, scope }
      }
    )
    return response.data
  }

  const fetchAttendeeTypes = async () => {
    const response = await useSanctumFetch<{ data: Array<{ value: string, label: string }> }>(
      `${config.public.apiBase}/api/dashboard/appointments/attendee-types`
    )
    return response.data
  }

  return { search, fetchAttendeeTypes }
}
