import { describe, it, expect } from 'vitest'
import { setup, createPage, url, waitForHydration } from '@nuxt/test-utils/e2e'

interface ContentPost {
  slug: string
}

interface ContentApiResponse {
  data?: ContentPost[]
}

const apiBase = process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000'

const fetchFirstSlug = async (type: 'blogs' | 'news'): Promise<string | null> => {
  const response = await fetch(`${apiBase}/api/${type}?per_page=3`)
  if (!response.ok) return null

  const payload = (await response.json()) as ContentApiResponse
  const first = payload.data?.[0]
  return first?.slug || null
}

describe('Content pages', async () => {
  await setup({
    server: true,
    browser: true
  })

  it('renders blogs list and detail page without API failures', async () => {
    const slug = await fetchFirstSlug('blogs')
    expect(slug).toBeTruthy()
    if (!slug) return

    const page = await createPage()
    const responses: Array<{ url: string, status: number }> = []

    page.on('response', (response) => {
      if (response.url().includes('/api/blogs')) {
        responses.push({ url: response.url(), status: response.status() })
      }
    })

    await page.goto(url('/blogs'))
    await waitForHydration(page)
    await page.waitForFunction(() => {
      return Boolean(
        document.body.textContent?.includes('No posts found')
        || document.querySelector('a[href^="/blogs/"]')
      )
    }, { timeout: 60000 })

    const listFailure = responses.find(item => item.status >= 400)
    if (listFailure) {
      throw new Error(`Blogs list API failed: ${listFailure.status} for ${listFailure.url}`)
    }

    await page.goto(url(`/blogs/${slug}`))
    await waitForHydration(page)
    await page.waitForFunction(() => {
      return Boolean(document.body.textContent?.includes('Related') || document.querySelector('h1'))
    }, { timeout: 60000 })

    const detailFailure = responses.find(item => item.url.includes(`/api/blogs/${slug}`) && item.status >= 400)
    if (detailFailure) {
      throw new Error(`Blog detail API failed: ${detailFailure.status} for ${detailFailure.url}`)
    }
  }, 120000)

  it('renders news list and detail page without API failures', async () => {
    const slug = await fetchFirstSlug('news')
    expect(slug).toBeTruthy()
    if (!slug) return

    const page = await createPage()
    const responses: Array<{ url: string, status: number }> = []

    page.on('response', (response) => {
      if (response.url().includes('/api/news')) {
        responses.push({ url: response.url(), status: response.status() })
      }
    })

    await page.goto(url('/news'))
    await waitForHydration(page)
    await page.waitForFunction(() => {
      return Boolean(
        document.body.textContent?.includes('No posts found')
        || document.querySelector('a[href^="/news/"]')
      )
    }, { timeout: 60000 })

    const listFailure = responses.find(item => item.status >= 400)
    if (listFailure) {
      throw new Error(`News list API failed: ${listFailure.status} for ${listFailure.url}`)
    }

    await page.goto(url(`/news/${slug}`))
    await waitForHydration(page)
    await page.waitForFunction(() => {
      return Boolean(document.body.textContent?.includes('Related') || document.querySelector('h1'))
    }, { timeout: 60000 })

    const detailFailure = responses.find(item => item.url.includes(`/api/news/${slug}`) && item.status >= 400)
    if (detailFailure) {
      throw new Error(`News detail API failed: ${detailFailure.status} for ${detailFailure.url}`)
    }
  }, 120000)
})
