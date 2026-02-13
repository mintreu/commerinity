/**
 * FAQ Composable
 * Handles FAQ data fetching and context-based personalization.
 */

export interface FaqTopic {
  id: number
  name: string
  slug: string
  description?: string | null
  icon?: string | null
  faqs_count?: number
}

export interface FaqItem {
  id: number
  url: string
  question: string
  answer: string
  tags?: string[] | null
  keywords?: string[] | null
  views?: number
  topic?: {
    name: string
    slug: string
    icon?: string | null
  }
}

export interface FaqSection {
  topic: FaqTopic
  faqs: FaqItem[]
}

interface TopicsResponse {
  success: boolean
  data: {
    topics: FaqTopic[]
  }
}

interface TopicFaqsResponse {
  success: boolean
  data: {
    topic: {
      name: string
      slug: string
      description?: string | null
      icon?: string | null
    }
    faqs: FaqItem[]
  }
}

interface FaqResponse {
  success: boolean
  data: {
    faq: FaqItem
  }
}

interface PopularResponse {
  success: boolean
  data: {
    faqs: FaqItem[]
  }
}

interface SearchResponse {
  success: boolean
  data: {
    query: string
    results: FaqItem[]
    count: number
  }
}

const FALLBACK_GENERAL_TOPIC_SLUGS = [
  'guest-faq',
  'getting-started',
  'account-profile',
  'login-security',
  'general-inquiry'
]

const DASHBOARD_TOPIC_PREFS: Record<string, string[]> = {
  guest: FALLBACK_GENERAL_TOPIC_SLUGS,
  regular: ['regular-user-faq', 'wallet-transactions', 'kyc-verification', 'account-profile'],
  member: ['member-faq', 'commission-earnings', 'membership-subscription', 'wallet-transactions'],
  promoter: ['promoter-faq', 'team-referrals', 'commission-earnings', 'wallet-transactions'],
  advisor: ['advisor-faq', 'team-referrals', 'commission-earnings', 'account-profile'],
  mentor: ['mentor-faq', 'team-referrals', 'commission-earnings', 'account-profile'],
  admin: ['admin-faq', 'general-inquiry', 'account-profile', 'login-security']
}

export const useFaq = () => {
  const config = useRuntimeConfig()

  const topics = ref<FaqTopic[]>([])
  const faqs = ref<FaqItem[]>([])
  const currentFaq = ref<FaqItem | null>(null)
  const popularFaqs = ref<FaqItem[]>([])
  const searchResults = ref<FaqItem[]>([])
  const sections = ref<FaqSection[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const topicMap = computed(() => {
    return new Map(topics.value.map(topic => [topic.slug, topic]))
  })

  const normalizeAudienceType = (audienceType?: string | null) => {
    const value = (audienceType || 'guest').toLowerCase()
    return value in DASHBOARD_TOPIC_PREFS ? value : 'guest'
  }

  const pickTopicSlugs = (preferred: string[]) => {
    const available = new Set(topics.value.map(topic => topic.slug))
    const matched = preferred.filter(slug => available.has(slug))

    if (matched.length > 0) {
      return matched
    }

    return topics.value.slice(0, 4).map(topic => topic.slug)
  }

  const readTopicFaqs = async (topicSlug: string): Promise<TopicFaqsResponse['data'] | null> => {
    try {
      const response = await $fetch<TopicFaqsResponse>(`${config.public.apiBase}/api/faq/${topicSlug}`)
      if (!response.success) return null
      return response.data
    } catch {
      return null
    }
  }

  const hydrateSections = async (topicSlugs: string[]) => {
    const results = await Promise.all(topicSlugs.map(readTopicFaqs))

    const builtSections: FaqSection[] = []
    for (const item of results) {
      if (!item || item.faqs.length === 0) continue
      const cachedTopic = topicMap.value.get(item.topic.slug)
      const topic: FaqTopic = cachedTopic || {
        id: 0,
        name: item.topic.name,
        slug: item.topic.slug,
        description: item.topic.description || null,
        icon: item.topic.icon || null
      }
      builtSections.push({ topic, faqs: item.faqs })
    }

    sections.value = builtSections
    faqs.value = builtSections.flatMap(section => section.faqs)
  }

  /**
   * Fetch all FAQ topics.
   */
  const fetchTopics = async () => {
    isLoading.value = true
    error.value = null

    try {
      const response = await $fetch<TopicsResponse>(`${config.public.apiBase}/api/faq/topics`)
      if (response.success) {
        topics.value = response.data.topics
      } else {
        topics.value = []
      }
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to load FAQ topics'
      topics.value = []
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Fetch FAQs by topic slug.
   */
  const fetchFaqsByTopic = async (topicSlug: string) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await $fetch<TopicFaqsResponse>(`${config.public.apiBase}/api/faq/${topicSlug}`)
      if (response.success) {
        faqs.value = response.data.faqs
        return response.data.topic
      }
      return null
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to load FAQs'
      return null
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Fetch a single FAQ by URL slug.
   */
  const fetchFaq = async (url: string) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await $fetch<FaqResponse>(`${config.public.apiBase}/api/faq/view/${url}`)
      if (response.success) {
        currentFaq.value = response.data.faq
      }
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to load FAQ'
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Fetch popular FAQs.
   */
  const fetchPopular = async () => {
    isLoading.value = true
    error.value = null

    try {
      const response = await $fetch<PopularResponse>(`${config.public.apiBase}/api/faq/popular`)
      popularFaqs.value = response.success ? response.data.faqs : []
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to load popular FAQs'
      popularFaqs.value = []
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Search FAQs.
   */
  const searchFaqs = async (query: string) => {
    if (!query || query.trim().length < 2) {
      searchResults.value = []
      return
    }

    isLoading.value = true
    error.value = null

    try {
      const response = await $fetch<SearchResponse>(`${config.public.apiBase}/api/faq/search`, {
        params: { q: query }
      })
      searchResults.value = response.success ? response.data.results : []
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to search FAQs'
      searchResults.value = []
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Footer/public FAQ bundle (guest/general).
   */
  const fetchGeneralFaqSections = async () => {
    isLoading.value = true
    error.value = null

    try {
      if (topics.value.length === 0) {
        await fetchTopics()
      }
      const slugs = pickTopicSlugs(FALLBACK_GENERAL_TOPIC_SLUGS)
      await hydrateSections(slugs)
      return sections.value
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to load general FAQs'
      sections.value = []
      faqs.value = []
      return []
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Dashboard FAQ bundle (user-type personalized on client side).
   */
  const fetchDashboardFaqSections = async (audienceType?: string | null) => {
    isLoading.value = true
    error.value = null

    try {
      if (topics.value.length === 0) {
        await fetchTopics()
      }

      const key = normalizeAudienceType(audienceType)
      const preferred = DASHBOARD_TOPIC_PREFS[key] || DASHBOARD_TOPIC_PREFS.guest
      const slugs = pickTopicSlugs(preferred)

      await hydrateSections(slugs)
      return sections.value
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to load personalized FAQs'
      sections.value = []
      faqs.value = []
      return []
    } finally {
      isLoading.value = false
    }
  }

  return {
    topics,
    faqs,
    sections,
    currentFaq,
    popularFaqs,
    searchResults,
    isLoading,
    error,
    fetchTopics,
    fetchFaqsByTopic,
    fetchFaq,
    fetchPopular,
    searchFaqs,
    fetchGeneralFaqSections,
    fetchDashboardFaqSections
  }
}
