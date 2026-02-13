import { describe, it, expect } from 'vitest'
import { setup, createPage, url, waitForHydration } from '@nuxt/test-utils/e2e'

interface CatalogProduct {
  slug: string
  category: { slug: string, name: string } | null
}

interface ProductsResponse {
  data: CatalogProduct[]
}

const apiBase = process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000'

const findProductWithRelated = async (): Promise<{ productSlug: string, categorySlug: string } | null> => {
  const response = await fetch(`${apiBase}/api/catalog/products?per_page=50`)
  if (!response.ok) return null
  const payload = (await response.json()) as ProductsResponse
  const grouped: Record<string, CatalogProduct[]> = {}
  for (const product of payload.data ?? []) {
    if (!product.category?.slug) continue
    grouped[product.category.slug] = grouped[product.category.slug] || []
    grouped[product.category.slug].push(product)
  }

  const match = Object.entries(grouped).find(([, items]) => items.length >= 2)
  if (!match) return null

  return {
    categorySlug: match[0],
    productSlug: match[1][0].slug
  }
}

describe('Product detail page', async () => {
  await setup({
    server: true,
    browser: true
  })

  it('shows related products carousel when category has more items', async () => {
    const candidate = await findProductWithRelated()
    expect(candidate).toBeTruthy()
    if (!candidate) return

    const page = await createPage()
    await page.goto(url(`/shop/product/${candidate.productSlug}`))
    await waitForHydration(page)

    await page.waitForSelector('[data-testid="related-products"]')
    const isVisible = await page.locator('[data-testid="related-products"]').isVisible()
    expect(isVisible).toBe(true)
  })

  it('redirects legacy /shop/:slug to /shop/product/:slug', async () => {
    const candidate = await findProductWithRelated()
    expect(candidate).toBeTruthy()
    if (!candidate) return

    const page = await createPage()
    await page.goto(url(`/shop/${candidate.productSlug}`))
    await waitForHydration(page)

    const currentUrl = page.url()
    expect(currentUrl).toContain(`/shop/product/${candidate.productSlug}`)
  })
})
