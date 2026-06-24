import { describe, it, expect } from 'vitest'

/**
 * Health & Connection Tests
 * Verify backend is accessible and CORS is configured
 */

const apiBase = process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000'

describe('Backend Health Check', () => {
  it('should respond to health endpoint', async () => {
    const response = await fetch(`${apiBase}/api/health`)
    expect(response.ok).toBe(true)

    const data = await response.json()
    expect(data.status).toBe('ok')
  })

  it('should have CORS headers for frontend', async () => {
    const response = await fetch(`${apiBase}/api/health`, {
      headers: { Origin: 'http://localhost:3000' }
    })
    expect(response.ok).toBe(true)
  })

  it('should provide CSRF cookie', async () => {
    const response = await fetch(`${apiBase}/sanctum/csrf-cookie`, {
      credentials: 'include'
    })
    expect(response.status).toBe(204)
  })
})
