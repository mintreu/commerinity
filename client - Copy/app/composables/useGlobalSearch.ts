export interface GlobalSearchItem {
  id: number
  type: 'product' | 'blog' | 'news'
  title: string
  slug: string
  excerpt?: string | null
  thumbnail?: string | null
  url: string
  sku?: string | null
  price_formatted?: string | null
  published_at?: string | null
}

export interface GlobalSearchResult {
  products: GlobalSearchItem[]
  blogs: GlobalSearchItem[]
  news: GlobalSearchItem[]
}

interface GlobalSearchResponse {
  success: boolean
  data: {
    query: string
    results: GlobalSearchResult
    totals: {
      products: number
      blogs: number
      news: number
      all: number
    }
  }
}

export const useGlobalSearch = () => {
  const config = useRuntimeConfig()

  const search = async (query: string, perType = 6) => {
    const q = query.trim()

    if (q.length < 2) {
      return {
        query: q,
        results: {
          products: [],
          blogs: [],
          news: []
        } as GlobalSearchResult,
        totals: {
          products: 0,
          blogs: 0,
          news: 0,
          all: 0
        }
      }
    }

    const response = await $fetch<GlobalSearchResponse>(`${config.public.apiBase}/api/search/global`, {
      params: {
        q,
        per_type: perType
      }
    })

    return response.data
  }

  const buildSuggestions = (results: GlobalSearchResult, limit = 8): GlobalSearchItem[] => {
    const merged = [
      ...results.products,
      ...results.blogs,
      ...results.news
    ]

    return merged.slice(0, limit)
  }

  return {
    search,
    buildSuggestions
  }
}
