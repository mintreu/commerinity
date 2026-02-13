import { describe, it, expect } from 'vitest'
import { setup, createPage, url, waitForHydration } from '@nuxt/test-utils/e2e'

process.env.NUXT_PUBLIC_API_BASE = 'http://localhost:8000'

describe('Shop products page', async () => {
  await setup({
    server: true,
    browser: true
  })

  it('renders product grid with items', async () => {
    const page = await createPage()
    const responses: Array<{ url: string, status: number }> = []
    const errors: string[] = []
    page.on('response', (response) => {
      if (response.url().includes('/api/catalog/products')) {
        responses.push({ url: response.url(), status: response.status() })
      }
    })
    page.on('pageerror', (error) => {
      errors.push(error.message)
    })
    page.on('console', (message) => {
      if (message.type() === 'error') {
        errors.push(message.text())
      }
    })
    await page.goto(url('/shop/products'))
    await waitForHydration(page)

    await page.waitForFunction(() => {
      return Boolean(
        document.querySelector('[data-testid="product-grid"]')
        || document.body.textContent?.includes('No products found')
      )
    }, { timeout: 60000 })

    if (responses.length === 0) {
      const errorDetails = errors.length ? ` Browser errors: ${errors.join(' | ')}` : ''
      throw new Error(`No /api/catalog/products request was observed in the browser.${errorDetails}`)
    }
    const failed = responses.find(item => item.status >= 400)
    if (failed) {
      throw new Error(`Catalog API failed: ${failed.status} for ${failed.url}`)
    }

    const gridVisible = await page.locator('[data-testid="product-grid"]').isVisible().catch(() => false)
    if (!gridVisible) {
      throw new Error('Product grid not rendered. UI showed empty state.')
    }

    const count = await page.locator('[data-testid="product-card"]').count()
    expect(count).toBeGreaterThan(0)
  }, 120000)

  it('shows prices sorted ascending when using price_asc', async () => {
    const page = await createPage()
    await page.goto(url('/shop/products?sort=price_asc'))
    await waitForHydration(page)

    await page.waitForFunction(() => {
      return Boolean(
        document.querySelector('[data-testid="product-price"]')
        || document.body.textContent?.includes('No products found')
      )
    }, { timeout: 60000 })

    const priceVisible = await page.locator('[data-testid="product-price"]').first().isVisible().catch(() => false)
    if (!priceVisible) {
      throw new Error('No product prices rendered. UI showed empty state.')
    }

    await page.waitForFunction(() => {
      return document.querySelectorAll('[data-testid="product-price"]').length >= 2
    }, { timeout: 60000 })
    const prices = await page.$$eval('[data-testid="product-price"]', (nodes) => {
      return nodes.slice(0, 6).map((node) => {
        const text = node.textContent || ''
        const numeric = Number(text.replace(/[^0-9.]/g, ''))
        return Number.isFinite(numeric) ? numeric : null
      }).filter((value): value is number => value !== null)
    })

    expect(prices.length).toBeGreaterThan(1)
    for (let i = 1; i < prices.length; i++) {
      expect(prices[i]).toBeGreaterThanOrEqual(prices[i - 1])
    }
  }, 120000)
})
