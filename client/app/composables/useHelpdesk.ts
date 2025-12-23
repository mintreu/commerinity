export const useHelpdesk = () => {
  const config = useRuntimeConfig()
  const loading = ref(false)

  const fetchTickets = async () => {
    loading.value = true
    try {
      const res = await useSanctumFetch(`${config.public.apiBase}/api/helpdesk/tickets`)
      return res.data || []
    } finally {
      loading.value = false
    }
  }

  const fetchTicket = async (uuid: string) => {
    loading.value = true
    try {
      const res = await useSanctumFetch(`${config.public.apiBase}/api/helpdesk/tickets/${uuid}`)
      return res.data
    } finally {
      loading.value = false
    }
  }

  const createTicket = async (data: FormData) => {
    return useSanctumFetch(`${config.public.apiBase}/api/helpdesk/tickets`, {
      method: 'POST',
      body: data
    })
  }

  const replyTicket = async (uuid: string, data: FormData) => {
    return useSanctumFetch(`${config.public.apiBase}/api/helpdesk/tickets/${uuid}/reply`, {
      method: 'POST',
      body: data
    })
  }

  const fetchTopics = async () => {
    const res = await useSanctumFetch(`${config.public.apiBase}/api/helpdesk/topics/ticket`)
    return res.data || []
  }

  return { loading, fetchTickets, fetchTicket, createTicket, replyTicket, fetchTopics }
}
