import { defineEventHandler, setResponseHeader } from 'h3'
import { useRuntimeConfig } from '#imports'

type SitemapUrl = {
  path: string
  changefreq?: 'daily' | 'weekly' | 'monthly'
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
    console.error('[sitemap] failed to fetch', url, error)
    return []
  }
}

const buildLocation = (siteUrl: string, path: string) => {
  if (path === '/') {
    return siteUrl
  }

  const normalizedSite = siteUrl.replace(/\/+$/, '')
  const normalizedPath = path.replace(/^\/+/, '')
  return `${normalizedSite}/${normalizedPath}`
}

export default defineEventHandler(async event => {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase || 'https://panel.vvindia.in'
  const siteUrl = config.public.siteUrl || 'https://www.vvindia.in'

  const [categories, careers, products] = await Promise.all([
    safeFetch<{ slug?: string }>(`${apiBase}/api/catalog/categories`, { per_page: 200 }),
    safeFetch<{ slug?: string }>(`${apiBase}/api/careers`, { page: 1, per_page: 200 }),
    safeFetch<{ slug?: string }>(`${apiBase}/api/catalog/products`, { page: 1, per_page: 200 })
  ])

  const staticRoutes: SitemapUrl[] = [
    { path: '/shop', priority: 0.9, changefreq: 'daily' },
    { path: '/shop/deals', priority: 0.8, changefreq: 'weekly' },
    { path: '/shop/products', priority: 0.8, changefreq: 'weekly' },
    { path: '/categories', priority: 0.8, changefreq: 'weekly' },
    { path: '/career', priority: 0.8, changefreq: 'weekly' }
  ]

  const categoryRoutes = categories
    .map(category => category.slug)
    .filter((slug): slug is string => typeof slug === 'string')
    .map(slug => ({ path: `/category/${slug}`, changefreq: 'weekly', priority: 0.7 }))

  const careerRoutes = careers
    .map(career => career.slug)
    .filter((slug): slug is string => typeof slug === 'string')
    .map(slug => ({ path: `/career/${slug}`, changefreq: 'weekly', priority: 0.7 }))

  const productRoutes = products
    .map(product => product.slug)
    .filter((slug): slug is string => typeof slug === 'string')
    .map(slug => ({ path: `/shop/${slug}`, changefreq: 'daily', priority: 0.7 }))

  const allRoutes = [...staticRoutes, ...categoryRoutes, ...careerRoutes, ...productRoutes]

  const sitemapEntries = allRoutes
    .map(entry => {
      const parts = [
        '  <url>',
        `    <loc>${buildLocation(siteUrl, entry.path)}</loc>`,
        entry.changefreq ? `    <changefreq>${entry.changefreq}</changefreq>` : '',
        typeof entry.priority === 'number' ? `    <priority>${entry.priority.toFixed(1)}</priority>` : '',
        '  </url>'
      ]

      return parts.filter(Boolean).join('\n')
    })
    .join('\n')

  const xml = `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${sitemapEntries}\n</urlset>`

  setResponseHeader(event, 'content-type', 'application/xml')
  setResponseHeader(event, 'cache-control', 'public, max-age=86400')

  return xml
})
