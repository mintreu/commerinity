import { defineEventHandler, getRequestHeader, getRequestURL, setResponseHeader } from 'h3'

interface OgPayload {
  title: string
  description: string
  image?: string
  url: string
  type?: 'website' | 'article' | 'product'
  siteName: string
}

interface ProductMeta {
  name?: string
  description?: string | null
  short_description?: string | null
  gallery?: Array<{
    src?: string | null
    thumbnail?: string | null
  }>
}

interface PostMeta {
  title?: string
  seo_title?: string | null
  excerpt?: string | null
  seo_description?: string | null
  cover_image?: string | null
}

interface CategoryMeta {
  name?: string
  description?: string | null
  banner?: string | null
  thumbnail?: string | null
  seo_meta?: {
    title?: string | null
    description?: string | null
  } | null
}

interface ProductResponse {
  success?: boolean
  data?: ProductMeta
}

interface PostResponse {
  data?: PostMeta
}

interface CategoryResponse {
  category?: CategoryMeta
}

const BOT_USER_AGENT_PATTERN = /(facebookexternalhit|facebot|twitterbot|linkedinbot|slackbot|discordbot|whatsapp|telegrambot|pinterest|skypeuripreview|googlebot|bingbot|crawler|spider)/i

const toAbsoluteUrl = (baseUrl: string, value?: string | null) => {
  if (!value) return undefined
  if (/^https?:\/\//i.test(value)) return value
  return `${baseUrl}${value.startsWith('/') ? '' : '/'}${value}`
}

const escapeHtml = (value: string) => value
  .replace(/&/g, '&amp;')
  .replace(/</g, '&lt;')
  .replace(/>/g, '&gt;')
  .replace(/"/g, '&quot;')
  .replace(/'/g, '&#39;')

const renderOgHtml = (payload: OgPayload) => {
  const title = escapeHtml(payload.title)
  const description = escapeHtml(payload.description)
  const canonicalUrl = escapeHtml(payload.url)
  const image = payload.image ? escapeHtml(payload.image) : ''
  const type = payload.type || 'website'
  const siteName = escapeHtml(payload.siteName)

  return `<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>${title}</title>
    <meta name="description" content="${description}">
    <meta property="og:type" content="${type}">
    <meta property="og:site_name" content="${siteName}">
    <meta property="og:title" content="${title}">
    <meta property="og:description" content="${description}">
    <meta property="og:url" content="${canonicalUrl}">
    ${image ? `<meta property="og:image" content="${image}">` : ''}
    ${image ? `<meta name="twitter:image" content="${image}">` : ''}
    <meta name="twitter:card" content="${image ? 'summary_large_image' : 'summary'}">
    <meta name="twitter:title" content="${title}">
    <meta name="twitter:description" content="${description}">
    <link rel="canonical" href="${canonicalUrl}">
  </head>
  <body>
    <p>${siteName}</p>
  </body>
</html>`
}

export default defineEventHandler(async (event) => {
  const userAgent = getRequestHeader(event, 'user-agent') || ''
  if (!BOT_USER_AGENT_PATTERN.test(userAgent)) {
    return
  }

  const config = useRuntimeConfig()
  const apiBase = String(config.public.apiBase || '').replace(/\/$/, '')
  const siteUrl = String(config.public.siteUrl || '').replace(/\/$/, '')
  const siteName = String(config.public.companyName || config.public.appName || 'VVIndia')
  const pathname = getRequestURL(event).pathname

  const productMatch = pathname.match(/^\/shop\/product\/([^/?#]+)/)
  if (productMatch) {
    const slug = decodeURIComponent(productMatch[1])
    try {
      const response = await $fetch<ProductResponse>(`${apiBase}/api/catalog/products/${encodeURIComponent(slug)}`)
      const product = response?.data
      if (!product) return

      const payload: OgPayload = {
        title: product.name || siteName,
        description: product.short_description || String(product.description || '').slice(0, 160) || `Buy ${product.name} online.`,
        image: toAbsoluteUrl(siteUrl, product.gallery?.[0]?.src || product.gallery?.[0]?.thumbnail),
        url: `${siteUrl}/shop/product/${encodeURIComponent(slug)}`,
        type: 'product',
        siteName
      }

      setResponseHeader(event, 'content-type', 'text/html; charset=utf-8')
      return renderOgHtml(payload)
    } catch {
      return
    }
  }

  const blogMatch = pathname.match(/^\/blogs\/([^/?#]+)/)
  if (blogMatch) {
    const slug = decodeURIComponent(blogMatch[1])
    try {
      const response = await $fetch<PostResponse>(`${apiBase}/api/blogs/${encodeURIComponent(slug)}`)
      const post = response?.data
      if (!post) return

      const payload: OgPayload = {
        title: post.seo_title || post.title || siteName,
        description: post.seo_description || post.excerpt || 'Read latest blog updates.',
        image: toAbsoluteUrl(siteUrl, post.cover_image),
        url: `${siteUrl}/blogs/${encodeURIComponent(slug)}`,
        type: 'article',
        siteName
      }

      setResponseHeader(event, 'content-type', 'text/html; charset=utf-8')
      return renderOgHtml(payload)
    } catch {
      return
    }
  }

  const newsMatch = pathname.match(/^\/news\/([^/?#]+)/)
  if (newsMatch) {
    const slug = decodeURIComponent(newsMatch[1])
    try {
      const response = await $fetch<PostResponse>(`${apiBase}/api/news/${encodeURIComponent(slug)}`)
      const post = response?.data
      if (!post) return

      const payload: OgPayload = {
        title: post.seo_title || post.title || siteName,
        description: post.seo_description || post.excerpt || 'Read latest news updates.',
        image: toAbsoluteUrl(siteUrl, post.cover_image),
        url: `${siteUrl}/news/${encodeURIComponent(slug)}`,
        type: 'article',
        siteName
      }

      setResponseHeader(event, 'content-type', 'text/html; charset=utf-8')
      return renderOgHtml(payload)
    } catch {
      return
    }
  }

  const categoryMatch = pathname.match(/^\/category\/([^/?#]+)/)
  if (categoryMatch) {
    const slug = decodeURIComponent(categoryMatch[1])
    try {
      const response = await $fetch<CategoryResponse>(`${apiBase}/api/catalog/category/${encodeURIComponent(slug)}`)
      const category = response?.category
      if (!category) return

      const payload: OgPayload = {
        title: category?.seo_meta?.title || category.name || siteName,
        description: category?.seo_meta?.description || category.description || `Browse ${category.name} products.`,
        image: toAbsoluteUrl(siteUrl, category.banner || category.thumbnail),
        url: `${siteUrl}/category/${encodeURIComponent(slug)}`,
        type: 'website',
        siteName
      }

      setResponseHeader(event, 'content-type', 'text/html; charset=utf-8')
      return renderOgHtml(payload)
    } catch {
      return
    }
  }
})
