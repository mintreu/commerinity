import { describe, it, expect, beforeAll } from 'vitest'

/**
 * Authentication API Tests
 *
 * Test all auth endpoints with real seeded data
 * API expects: { email, password } and returns { token } on success
 * User endpoint returns: { data: { email, type, ... } } (Laravel JsonResource format)
 */

const apiBase = process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000'

// Demo user credentials from DemoUserSeeder
const demoUsers = {
  regular: { email: 'regular@demo.com', password: 'password' },
  member: { email: 'member@demo.com', password: 'password' },
  promoter: { email: 'promoter@demo.com', password: 'password' },
  advisor: { email: 'advisor@demo.com', password: 'password' },
  mentor: { email: 'mentor@demo.com', password: 'password' }
}

// Helper to login and get token
async function login(email: string, password: string): Promise<{ ok: boolean, token?: string, status: number }> {
  const response = await fetch(`${apiBase}/api/auth/login`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ email, password })
  })

  const data = await response.json()
  return {
    ok: response.ok,
    token: data.token,
    status: response.status
  }
}

// Helper to get user data (unwraps Laravel JsonResource's data wrapper)
async function getUser(token: string): Promise<{ ok: boolean, user?: any }> {
  const response = await fetch(`${apiBase}/api/user`, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json'
    }
  })

  if (!response.ok) {
    return { ok: false }
  }

  const json = await response.json()
  // Laravel JsonResource wraps response in { data: ... }
  return { ok: true, user: json.data }
}

describe('Authentication API', () => {
  describe('Login Endpoint', () => {
    it('should login with valid credentials', async () => {
      const response = await fetch(`${apiBase}/api/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          email: demoUsers.member.email,
          password: demoUsers.member.password
        })
      })

      expect(response.ok).toBe(true)

      const data = await response.json()
      expect(data.token).toBeDefined()
      expect(typeof data.token).toBe('string')
      expect(data.token.length).toBeGreaterThan(10)
    })

    it('should reject invalid credentials', async () => {
      const response = await fetch(`${apiBase}/api/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          email: 'invalid@email.com',
          password: 'wrongpassword'
        })
      })

      expect(response.ok).toBe(false)
      // Could be 401 (invalid creds) or 422 (validation failed)
      expect([401, 422]).toContain(response.status)
    })

    it('should validate required fields', async () => {
      const response = await fetch(`${apiBase}/api/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({})
      })

      expect(response.ok).toBe(false)
      expect(response.status).toBe(422)
    })

    it('should reject missing password', async () => {
      const response = await fetch(`${apiBase}/api/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          email: demoUsers.member.email
        })
      })

      expect(response.ok).toBe(false)
      expect(response.status).toBe(422)
    })
  })

  describe('User Endpoint (Protected)', () => {
    let authToken: string

    beforeAll(async () => {
      const result = await login(demoUsers.member.email, demoUsers.member.password)
      expect(result.ok).toBe(true)
      authToken = result.token!
    })

    it('should return user data with valid token', async () => {
      const result = await getUser(authToken)
      expect(result.ok).toBe(true)
      expect(result.user.email).toBe(demoUsers.member.email)
      expect(result.user.type).toBe('member')
    })

    it('should reject without token', async () => {
      const response = await fetch(`${apiBase}/api/user`, {
        headers: { Accept: 'application/json' }
      })

      expect(response.ok).toBe(false)
      expect(response.status).toBe(401)
    })

    it('should reject with invalid token', async () => {
      const response = await fetch(`${apiBase}/api/user`, {
        headers: {
          Authorization: 'Bearer invalid-token',
          Accept: 'application/json'
        }
      })

      expect(response.ok).toBe(false)
      expect(response.status).toBe(401)
    })
  })

  describe('Logout Endpoint', () => {
    it('should logout and invalidate token', async () => {
      // First login
      const loginResult = await login(demoUsers.regular.email, demoUsers.regular.password)
      expect(loginResult.ok).toBe(true)
      const token = loginResult.token!

      // Logout
      const logoutResponse = await fetch(`${apiBase}/api/auth/logout`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json'
        }
      })

      expect(logoutResponse.ok).toBe(true)

      // Try to use the token again - should fail
      const userResponse = await fetch(`${apiBase}/api/user`, {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json'
        }
      })

      expect(userResponse.status).toBe(401)
    })
  })
})

describe('All User Types - Authentication', () => {
  for (const [userType, credentials] of Object.entries(demoUsers)) {
    describe(`${userType.toUpperCase()} User`, () => {
      let token: string

      it('should authenticate successfully', async () => {
        const result = await login(credentials.email, credentials.password)
        expect(result.ok).toBe(true)
        expect(result.token).toBeDefined()
        token = result.token!
      })

      it('should return correct user type from /api/user', async () => {
        if (!token) {
          // Login if previous test was skipped
          const result = await login(credentials.email, credentials.password)
          token = result.token!
        }

        const result = await getUser(token)
        expect(result.ok).toBe(true)
        expect(result.user.type).toBe(userType)
        expect(result.user.email).toBe(credentials.email)
      })
    })
  }
})
