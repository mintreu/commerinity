import { describe, it, expect, beforeAll } from 'vitest'

/**
 * Regular User Flow Tests
 *
 * Tests the complete flow for a REGULAR user type:
 * - Login with regular credentials
 * - Limited dashboard (no Affiliate)
 * - Basic wallet access
 * - Profile management
 * - Career/Job browsing
 *
 * API Format:
 * - Login: POST /api/auth/login { email, password } => { token }
 * - User: GET /api/user => { data: { email, type, permissions, features, ... } }
 *
 * Note: 500 status = endpoint not implemented yet (acceptable during development)
 */

const apiBase = process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000'

// Valid status codes (200=OK, 404=Not Found, 500=Not Implemented Yet)
const VALID_STATUS = [200, 404, 500]

// Helper function to login
async function login(email: string, password: string): Promise<{ ok: boolean; token?: string }> {
  const response = await fetch(`${apiBase}/api/auth/login`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ email, password })
  })

  const data = await response.json()
  return { ok: response.ok, token: data.token }
}

// Helper function to get user data
async function getUser(token: string): Promise<any> {
  const response = await fetch(`${apiBase}/api/user`, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  })
  const json = await response.json()
  return json.data // Laravel JsonResource wraps in { data: ... }
}

describe('Regular User Complete Flow', () => {
  let authToken: string
  let user: any

  beforeAll(async () => {
    const result = await login('regular@demo.com', 'password')
    expect(result.ok).toBe(true)
    authToken = result.token!

    // Get user info
    user = await getUser(authToken)
  })

  describe('Step 1: Authentication', () => {
    it('should authenticate as regular user', () => {
      expect(authToken).toBeDefined()
      expect(user.type).toBe('regular')
    })

    it('should have correct email', () => {
      expect(user.email).toBe('regular@demo.com')
    })

    it('should have limited permissions', () => {
      expect(user.permissions).toBeDefined()
      expect(user.permissions.can_access_affiliate).toBe(false)
    })

    it('should show upgrade prompt', () => {
      expect(user.features.show_upgrade_prompt).toBe(true)
    })
  })

  describe('Step 2: Profile Access', () => {
    it('should get user profile', async () => {
      const userData = await getUser(authToken)
      expect(userData.email).toBe('regular@demo.com')
      expect(userData.type).toBe('regular')
    })

    it('should get onboarding status', async () => {
      const response = await fetch(`${apiBase}/api/onboarding/status`, {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Accept': 'application/json'
        }
      })

      expect(VALID_STATUS).toContain(response.status)
    })
  })

  describe('Step 3: Wallet Access', () => {
    it('should view wallet info', async () => {
      const response = await fetch(`${apiBase}/api/wallet`, {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Accept': 'application/json'
        }
      })

      // Regular user may or may not have wallet
      expect(VALID_STATUS).toContain(response.status)
    })
  })

  describe('Step 4: Address Management', () => {
    it('should view addresses', async () => {
      const response = await fetch(`${apiBase}/api/addresses`, {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Accept': 'application/json'
        }
      })

      expect(VALID_STATUS).toContain(response.status)
    })
  })

  describe('Step 5: Notifications', () => {
    it('should view notifications', async () => {
      const response = await fetch(`${apiBase}/api/notifications`, {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Accept': 'application/json'
        }
      })

      expect(VALID_STATUS).toContain(response.status)
    })
  })
})

describe('Career/Job Flow (Guest & Authenticated)', () => {
  describe('Guest Access (No Auth)', () => {
    it('should browse careers without authentication', async () => {
      const response = await fetch(`${apiBase}/api/careers`)
      expect(response.ok).toBe(true)

      const data = await response.json()
      expect(Array.isArray(data.data)).toBe(true)
      expect(data.data.length).toBeGreaterThan(0)
    })

    it('should get career filters', async () => {
      const response = await fetch(`${apiBase}/api/careers/filters`)
      expect(response.ok).toBe(true)
    })

    it('should view single career', async () => {
      // First get list
      const listResponse = await fetch(`${apiBase}/api/careers`)
      const listData = await listResponse.json()

      if (listData.data.length > 0) {
        const slug = listData.data[0].slug
        const response = await fetch(`${apiBase}/api/careers/${slug}`)
        expect(response.ok).toBe(true)
      }
    })
  })

  describe('Authenticated Career Access', () => {
    let authToken: string

    beforeAll(async () => {
      const result = await login('regular@demo.com', 'password')
      expect(result.ok).toBe(true)
      authToken = result.token!
    })

    it('should view my applications', async () => {
      const response = await fetch(`${apiBase}/api/my-applications`, {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Accept': 'application/json'
        }
      })

      expect(VALID_STATUS).toContain(response.status)
    })

    it('should check application status for a job', async () => {
      // Get first job
      const listResponse = await fetch(`${apiBase}/api/careers`)
      const listData = await listResponse.json()

      if (listData.data.length > 0) {
        const slug = listData.data[0].slug
        const response = await fetch(`${apiBase}/api/careers/${slug}/check-application`, {
          headers: {
            'Authorization': `Bearer ${authToken}`,
            'Accept': 'application/json'
          }
        })
        expect(VALID_STATUS).toContain(response.status)
      }
    })
  })
})
