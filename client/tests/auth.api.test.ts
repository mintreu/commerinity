import { describe, it, expect, beforeAll } from 'vitest'

/**
 * Authentication API Tests (Legacy)
 *
 * These tests verify the auth API works correctly
 *
 * API Format:
 * - Login: POST /api/auth/login { email, password } => { token }
 * - User: GET /api/user => { data: { email, type, ... } }
 */

const apiBase = process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000'

describe('Authentication API Tests', () => {
  describe('Login Endpoint', () => {
    it('should reject login without credentials', async () => {
      const response = await fetch(`${apiBase}/api/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({})
      })

      expect(response.status).toBe(422) // Validation error
    })

    it('should reject login with invalid credentials', async () => {
      const response = await fetch(`${apiBase}/api/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          email: 'invalid@example.com',
          password: 'wrongpassword'
        })
      })

      expect([401, 422]).toContain(response.status)
    })

    it('should accept login with valid demo credentials', async () => {
      const response = await fetch(`${apiBase}/api/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          email: 'regular@demo.com',
          password: 'password'
        })
      })

      expect(response.ok).toBe(true)

      const data = await response.json()
      expect(data).toHaveProperty('token')
      expect(typeof data.token).toBe('string')
    })
  })

  describe('Protected Endpoint', () => {
    let authToken: string

    beforeAll(async () => {
      // Login to get token
      const loginResponse = await fetch(`${apiBase}/api/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          email: 'regular@demo.com',
          password: 'password'
        })
      })

      const data = await loginResponse.json()
      authToken = data.token
    })

    it('should reject access to /api/user without token', async () => {
      const response = await fetch(`${apiBase}/api/user`, {
        headers: {
          Accept: 'application/json'
        }
      })

      expect(response.status).toBe(401)
    })

    it('should allow access to /api/user with valid token', async () => {
      const response = await fetch(`${apiBase}/api/user`, {
        headers: {
          Accept: 'application/json',
          Authorization: `Bearer ${authToken}`
        }
      })

      expect(response.ok).toBe(true)

      const json = await response.json()
      // Laravel JsonResource wraps response in { data: ... }
      expect(json).toHaveProperty('data')
      expect(json.data).toHaveProperty('email', 'regular@demo.com')
      expect(json.data).toHaveProperty('name')
      expect(json.data).toHaveProperty('type')
    })
  })
})
