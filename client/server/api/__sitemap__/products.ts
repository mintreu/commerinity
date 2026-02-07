import { defineSitemapEventHandler } from '#imports'
import { useRuntimeConfig } from '#imports'

type SitemapUrl = {
  loc: string
  lastmod?: string | Date
  changefreq?: 'always' | 'hourly' | 'daily' | 'weekly' | 'monthly' | 'yearly' | 'never'
  priority?: number
}

type ApiResponse<T> = {
  data?: T
  items?: T[]
  [key: string]: unknown
}

const safeFetch = async <T>(url: string, query?: Record<string, string | number>) => {
  try {
    const params = query ? new URLSearchParams(Object.entries(query).map(([key, value]) => [key, String(value)])) : undefined
    const fullUrl = `${url}${params && params.toString() ? `?${params.toString()}` : ''}`
    const response = await fetch(fullUrl, {
      method: 'GET',
      headers: {
        accept: 'application/json'
      }
    })

    if (!response.ok) {
      return []
    }

    const body = (await response.json()) as ApiResponse<T>

    if (Array.isArray(body.data)) {
      return body.data
    }

    if (Array.isArray(body.items)) {
      return body.items
    }

    if (Array.isArray(body)) {
      return body as T[]
    }

    return []
  } catch (error) {
    console.error('[sitemap-products] failed to fetch', url, error)
    return []
  }
}

export default defineSitemapEventHandler(async () => {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase || 'https://panel.vvindia.in'

  // Fetch product slugs from your API
  const products = await safeFetch<{ slug?: string }>(`${apiBase}/api/catalog/products`, { per_page: 1000 }) // Fetch up to 1000 products

  const productSitemapUrls: SitemapUrl[] = products
    .map(product => product.slug)
    .filter((slug): slug is string => typeof slug === 'string')
    .map(slug => ({
      loc: `/shop/product/${slug}`,
      changefreq: 'daily',
      priority: 0.7
    }))

  return productSitemapUrls
})
