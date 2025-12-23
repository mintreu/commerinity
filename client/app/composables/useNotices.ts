import type { Ref } from 'vue'

export interface Notice {
  uuid: string
  title: string
  content: string
  type: 'info' | 'warning' | 'success' | 'promo'
  type_color: string
  type_icon: string
  cta_text?: string
  cta_link?: string
  icon?: string
  color?: string
  image_url?: string
  priority: number
}

export const useNotices = () => {
  const config = useRuntimeConfig()
  const { useSanctumFetch } = useSanctum()

  const notices: Ref<Notice[]> = ref([])
  const isLoading = ref(false)
  const error: Ref<string | null> = ref(null)

  const fetchNotices = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<{
        success: boolean
        data: Notice[]
      }>(`${config.public.apiBase}/api/notices`)

      if (response?.success) {
        notices.value = response.data
      }
      return response
    }
    catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch notices'
      throw err
    }
    finally {
      isLoading.value = false
    }
  }

  const dismissNotice = async (uuid: string) => {
    try {
      await useSanctumFetch(`${config.public.apiBase}/api/notices/${uuid}/dismiss`, {
        method: 'POST'
      })

      // Remove from local state
      notices.value = notices.value.filter(n => n.uuid !== uuid)

      return true
    }
    catch {
      return false
    }
  }

  const recordClick = async (uuid: string) => {
    try {
      const response = await useSanctumFetch<{
        success: boolean
        data: { cta_link: string }
      }>(`${config.public.apiBase}/api/notices/${uuid}/click`, {
        method: 'POST'
      })

      return response?.data?.cta_link
    }
    catch {
      // Silent fail
      return null
    }
  }

  return {
    notices,
    isLoading,
    error,
    fetchNotices,
    dismissNotice,
    recordClick
  }
}
