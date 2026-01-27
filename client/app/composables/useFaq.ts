/**
 * FAQ Composable
 * Handles FAQ data fetching and state management
 */

export const useFaq = () => {
  const config = useRuntimeConfig()

  const topics = ref<any[]>([])
  const faqs = ref<any[]>([])
  const currentFaq = ref<any>(null)
  const popularFaqs = ref<any[]>([])
  const searchResults = ref<any[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  /**
   * Fetch all FAQ topics
   */
  const fetchTopics = async () => {
    isLoading.value = true
    error.value = null

    try {
      const response = await $fetch<any>(`${config.public.apiBase}/api/faq/topics`)

      if (response.success) {
        topics.value = response.data.topics
      }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to load FAQ topics'
      console.error('Error fetching FAQ topics:', err)
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Fetch FAQs by topic slug
   */
  const fetchFaqsByTopic = async (topicSlug: string) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await $fetch<any>(`${config.public.apiBase}/api/faq/${topicSlug}`)

      if (response.success) {
        faqs.value = response.data.faqs
        return response.data.topic
      }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to load FAQs'
      console.error('Error fetching FAQs:', err)
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Fetch a single FAQ by URL slug
   */
  const fetchFaq = async (url: string) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await $fetch<any>(`${config.public.apiBase}/api/faq/view/${url}`)

      if (response.success) {
        currentFaq.value = response.data.faq
      }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to load FAQ'
      console.error('Error fetching FAQ:', err)
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Fetch popular FAQs
   */
  const fetchPopular = async () => {
    isLoading.value = true
    error.value = null

    try {
      const response = await $fetch<any>(`${config.public.apiBase}/api/faq/popular`)

      if (response.success) {
        popularFaqs.value = response.data.faqs
      }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to load popular FAQs'
      console.error('Error fetching popular FAQs:', err)
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Search FAQs
   */
  const searchFaqs = async (query: string) => {
    if (!query || query.trim().length < 2) {
      searchResults.value = []
      return
    }

    isLoading.value = true
    error.value = null

    try {
      const response = await $fetch<any>(`${config.public.apiBase}/api/faq/search`, {
        params: { q: query }
      })

      if (response.success) {
        searchResults.value = response.data.results
      }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to search FAQs'
      console.error('Error searching FAQs:', err)
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Mark FAQ as helpful
   */
  const markHelpful = async (url: string) => {
    try {
      const response = await $fetch<any>(`${config.public.apiBase}/api/faq/${url}/helpful`, {
        method: 'POST'
      })

      return response.success
    } catch (err: any) {
      console.error('Error marking FAQ as helpful:', err)
      return false
    }
  }

  /**
   * Mark FAQ as not helpful
   */
  const markNotHelpful = async (url: string) => {
    try {
      const response = await $fetch<any>(`${config.public.apiBase}/api/faq/${url}/not-helpful`, {
        method: 'POST'
      })

      return response.success
    } catch (err: any) {
      console.error('Error marking FAQ as not helpful:', err)
      return false
    }
  }

  return {
    // State
    topics,
    faqs,
    currentFaq,
    popularFaqs,
    searchResults,
    isLoading,
    error,

    // Methods
    fetchTopics,
    fetchFaqsByTopic,
    fetchFaq,
    fetchPopular,
    searchFaqs,
    markHelpful,
    markNotHelpful
  }
}
