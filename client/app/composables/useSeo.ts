/**
 * Comprehensive SEO Composable
 * Handles full SEO with OG tags, Twitter cards, and Schema.org
 */

interface SeoOptions {
  title: string
  description?: string
  keywords?: string | string[]
  image?: string
  imageAlt?: string
  url?: string
  type?: 'website' | 'article' | 'product' | 'profile'
  article?: {
    publishedTime?: string
    modifiedTime?: string
    author?: string
    section?: string
    tags?: string[]
  }
  product?: {
    price?: number | string
    currency?: string
    availability?: 'in stock' | 'out of stock' | 'preorder'
    brand?: string
    condition?: 'new' | 'refurbished' | 'used'
  }
  twitter?: {
    card?: 'summary' | 'summary_large_image' | 'app' | 'player'
    site?: string
    creator?: string
  }
  noindex?: boolean
  nofollow?: boolean
  canonical?: string
}

export function useComprehensiveSeo(options: SeoOptions) {
  const config = useRuntimeConfig()
  const route = useRoute()

  const siteName = config.public.companyName || config.public.appName || 'VVIndia'
  const siteUrl = String(config.public.siteUrl || 'https://www.vvindia.in').replace(/\/$/, '')
  const defaultImage = `${siteUrl}/og-image.png`

  const toAbsoluteUrl = (value?: string) => {
    if (!value) return undefined
    if (/^https?:\/\//i.test(value)) return value
    return `${siteUrl}${value.startsWith('/') ? '' : '/'}${value}`
  }

  // Build full URL
  const fullUrl = toAbsoluteUrl(options.url) || `${siteUrl}${route.fullPath}`

  // Build title with site name
  const fullTitle = `${options.title} | ${siteName}`

  // Build meta tags
  const meta: Record<string, string>[] = []

  // Basic meta
  if (options.description) {
    meta.push({ name: 'description', content: options.description })
  }

  const keywords = Array.isArray(options.keywords)
    ? options.keywords
    : (options.keywords ? [options.keywords] : [])
  if (keywords.length > 0) {
    meta.push({ name: 'keywords', content: keywords.join(', ') })
  }

  // Robots
  if (options.noindex || options.nofollow) {
    const robots = [
      options.noindex ? 'noindex' : 'index',
      options.nofollow ? 'nofollow' : 'follow'
    ].join(', ')
    meta.push({ name: 'robots', content: robots })
  }

  // Open Graph
  meta.push(
    { property: 'og:site_name', content: siteName },
    { property: 'og:title', content: options.title },
    { property: 'og:type', content: options.type || 'website' },
    { property: 'og:url', content: fullUrl }
  )

  if (options.description) {
    meta.push({ property: 'og:description', content: options.description })
  }

  const imageUrl = toAbsoluteUrl(options.image) || defaultImage

  meta.push({
    property: 'og:image',
    content: imageUrl
  })

  if (options.imageAlt) {
    meta.push({ property: 'og:image:alt', content: options.imageAlt })
  }

  // Article-specific OG tags
  if (options.type === 'article' && options.article) {
    if (options.article.publishedTime) {
      meta.push({ property: 'article:published_time', content: options.article.publishedTime })
    }
    if (options.article.modifiedTime) {
      meta.push({ property: 'article:modified_time', content: options.article.modifiedTime })
    }
    if (options.article.author) {
      meta.push({ property: 'article:author', content: options.article.author })
    }
    if (options.article.section) {
      meta.push({ property: 'article:section', content: options.article.section })
    }
    if (options.article.tags) {
      options.article.tags.forEach((tag) => {
        meta.push({ property: 'article:tag', content: tag })
      })
    }
  }

  // Product-specific OG tags
  if (options.type === 'product' && options.product) {
    if (options.product.price) {
      meta.push({
        property: 'product:price:amount',
        content: String(options.product.price)
      })
    }
    if (options.product.currency) {
      meta.push({
        property: 'product:price:currency',
        content: options.product.currency
      })
    }
    if (options.product.availability) {
      meta.push({
        property: 'product:availability',
        content: options.product.availability
      })
    }
    if (options.product.brand) {
      meta.push({
        property: 'product:brand',
        content: options.product.brand
      })
    }
    if (options.product.condition) {
      meta.push({
        property: 'product:condition',
        content: options.product.condition
      })
    }
  }

  // Twitter Card
  const twitterCard = options.twitter?.card || (options.image ? 'summary_large_image' : 'summary')
  meta.push(
    { name: 'twitter:card', content: twitterCard },
    { name: 'twitter:title', content: options.title }
  )

  if (options.description) {
    meta.push({ name: 'twitter:description', content: options.description })
  }

  meta.push({
    name: 'twitter:image',
    content: imageUrl
  })

  if (options.imageAlt) {
    meta.push({ name: 'twitter:image:alt', content: options.imageAlt })
  }

  if (options.twitter?.site) {
    meta.push({ name: 'twitter:site', content: options.twitter.site })
  }

  if (options.twitter?.creator) {
    meta.push({ name: 'twitter:creator', content: options.twitter.creator })
  }

  // Build link tags
  const link: Record<string, string>[] = [
    { rel: 'canonical', href: options.canonical || fullUrl }
  ]

  // Apply meta tags
  useHead({
    title: fullTitle,
    meta,
    link
  })

  // Also use useSeoMeta for better compatibility
  useSeoMeta({
    title: fullTitle,
    description: options.description,
    ogTitle: options.title,
    ogDescription: options.description,
    ogImage: imageUrl,
    ogUrl: fullUrl,
    twitterTitle: options.title,
    twitterDescription: options.description,
    twitterImage: imageUrl,
    twitterCard: twitterCard
  })
}
