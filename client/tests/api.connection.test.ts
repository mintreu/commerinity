import { describe, it, expect } from 'vitest'
import { setup } from '@nuxt/test-utils/e2e'

describe('API Connection Tests', async () => {
  await setup({
    // Use test environment
    server: true,
    browser: false
  })

  const apiBase = process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000'

  describe('Health Check', () => {
    it('should connect to API health endpoint', async () => {
      const response = await fetch(`${apiBase}/api/health`)
      expect(response.ok).toBe(true)

      const data = await response.json()
      expect(data).toHaveProperty('status', 'ok')
      expect(data).toHaveProperty('message')
      expect(data).toHaveProperty('timestamp')
    })
  })

  describe('CORS Configuration', () => {
    it('should allow requests from Nuxt dev server', async () => {
      const response = await fetch(`${apiBase}/api/health`, {
        headers: {
          Origin: 'http://localhost:3000'
        }
      })

      expect(response.ok).toBe(true)
      // Check if CORS headers are present
      const headers = response.headers
      expect(headers.get('access-control-allow-origin')).toBeTruthy()
    })

    it('should allow credentials', async () => {
      const response = await fetch(`${apiBase}/api/health`, {
        headers: {
          Origin: 'http://localhost:3000'
        },
        credentials: 'include'
      })

      expect(response.ok).toBe(true)
      expect(response.headers.get('access-control-allow-credentials')).toBe('true')
    })
  })

  describe('CSRF Cookie', () => {
    it('should retrieve CSRF cookie', async () => {
      const response = await fetch(`${apiBase}/sanctum/csrf-cookie`, {
        headers: {
          Origin: 'http://localhost:3000',
          Accept: 'application/json'
        },
        credentials: 'include'
      })

      expect(response.status).toBe(204)

      // Check if Set-Cookie header is present
      const setCookie = response.headers.get('set-cookie')
      expect(setCookie).toBeTruthy()
      if (setCookie) {
        expect(setCookie).toContain('XSRF-TOKEN')
      }
    })
  })
})
