<template>
  <div class="store-page">
    <StoreLanding :categories="categorySections" />
  </div>
</template>

<script setup lang="ts">
interface ChildCategory {
  name: string
  url: string
  image: string
  starting_from_price: number
}

interface ParentCategory {
  name: string
  url: string
  thumbnail: string
  children: ChildCategory[]
}

// Configuration and API setup
const config = useRuntimeConfig()

// Fetch store data with optimizations
const { data: categorySections, error } = await useLazyFetch<ParentCategory[]>(
    `${config.public.apiBase}/categories/with-products`,
    {
      key: 'store-categories',
      credentials: 'include',
      default: () => [],
      server: true,
      transform: (data: ParentCategory[]) => {
        // Transform and optimize data if needed
        return data?.map(category => ({
          ...category,
          children: category.children?.slice(0, 12) || [] // Limit for performance
        })) || []
      }
    }
)

// Handle error state
if (error.value) {
  console.error('Failed to load store categories:', error.value)
}

// SEO Meta tags
useSeoMeta({
  title: 'Premium Online Store - Best Deals & Fast Delivery',
  description: 'Discover thousands of amazing products across multiple categories with unbeatable deals, lightning-fast delivery, and excellent customer service. Shop now for the best online shopping experience.',
  ogTitle: 'Premium Online Store - Best Deals & Fast Delivery',
  ogDescription: 'Discover thousands of amazing products across multiple categories with unbeatable deals, lightning-fast delivery, and excellent customer service.',
  ogType: 'website',
  ogUrl: `${config.public.siteUrl || ''}/store`,
  ogImage: `${config.public.siteUrl || ''}/images/store-banner.jpg`,
  twitterCard: 'summary_large_image',
  twitterTitle: 'Premium Online Store - Best Deals & Fast Delivery',
  twitterDescription: 'Discover thousands of amazing products across multiple categories with unbeatable deals, lightning-fast delivery, and excellent customer service.',
  robots: 'index,follow',
  keywords: 'online store, shopping, deals, fast delivery, premium products, categories, e-commerce'
})

// Add structured data using useHead instead
useHead({
  script: [
    {
      type: 'application/ld+json',
      children: JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'Store',
        name: 'Premium Online Store',
        description: 'Your one-stop shop for premium products with fast delivery',
        url: `${config.public.siteUrl || ''}/store`,
        image: `${config.public.siteUrl || ''}/images/store-logo.jpg`,
        telephone: '+1-800-STORE-01',
        address: {
          '@type': 'PostalAddress',
          streetAddress: '123 Shopping Street',
          addressLocality: 'Commerce City',
          postalCode: '12345',
          addressCountry: 'US'
        },
        geo: {
          '@type': 'GeoCoordinates',
          latitude: '40.7128',
          longitude: '-74.0060'
        },
        openingHoursSpecification: {
          '@type': 'OpeningHoursSpecification',
          dayOfWeek: [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
          ],
          opens: '00:00',
          closes: '23:59'
        },
        potentialAction: {
          '@type': 'SearchAction',
          target: `${config.public.siteUrl || ''}/search?q={search_term_string}`,
          'query-input': 'required name=search_term_string'
        },
        offers: {
          '@type': 'AggregateOffer',
          priceCurrency: 'INR',
          lowPrice: '99',
          highPrice: '99999',
          offerCount: categorySections.value?.length || 0
        }
      })
    }
  ]
})
</script>

<style scoped>
.store-page {
  @apply relative overflow-hidden;
}
</style>
