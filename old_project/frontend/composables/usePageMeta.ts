import type { MetaObject } from 'nuxt/schema'

export function usePageMeta(metaData: {
    title?: string
    description?: string
    image?: string
    type?: string
    price?: number
    currency?: string
    keywords?: string[]
}) {
    const meta: MetaObject['meta'] = []

    if (metaData.description) {
        meta.push({ name: 'description', content: metaData.description })
        meta.push({ property: 'og:description', content: metaData.description })
    }

    if (metaData.keywords) {
        meta.push({ name: 'keywords', content: metaData.keywords.join(', ') })
    }

    if (metaData.title) {
        meta.push({ property: 'og:title', content: metaData.title })
    }

    if (metaData.image) {
        meta.push({ property: 'og:image', content: metaData.image })
    }

    meta.push({ property: 'og:type', content: metaData.type || 'website' })

    if (metaData.price) {
        meta.push({ property: 'product:price:amount', content: metaData.price.toString() })
    }

    if (metaData.currency) {
        meta.push({ property: 'product:price:currency', content: metaData.currency })
    }

    useHead({
        title: metaData.title ?? 'Commernity',
        meta
    })
}
