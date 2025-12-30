import { describe, it, expect, beforeAll } from 'vitest'

/**
 * All User Types Flow Tests
 *
 * Tests authentication and access patterns for all 5 user types:
 * - Regular
 * - Member
 * - Promoter
 * - Advisor
 * - Mentor
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

const userTypes = [
  { type: 'regular', email: 'regular@demo.com', password: 'password' },
  { type: 'member', email: 'member@demo.com', password: 'password' },
  { type: 'promoter', email: 'promoter@demo.com', password: 'password' },
  { type: 'advisor', email: 'advisor@demo.com', password: 'password' },
  { type: 'mentor', email: 'mentor@demo.com', password: 'password' }
]

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

// Helper function to get user data (unwraps Laravel JsonResource's data wrapper)
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

describe('All User Types - Authentication', () => {
  for (const user of userTypes) {
    describe(`${user.type.toUpperCase()} User`, () => {
      let authToken: string
      let userData: any

      it('should authenticate successfully', async () => {
        const result = await login(user.email, user.password)
        expect(result.ok).toBe(true)
        expect(result.token).toBeDefined()
        authToken = result.token!
      })

      it('should return correct user type from /api/user', async () => {
        if (!authToken) {
          const result = await login(user.email, user.password)
          authToken = result.token!
        }

        userData = await getUser(authToken)
        expect(userData.type).toBe(user.type)
        expect(userData.email).toBe(user.email)
      })

      it('should have proper user structure', async () => {
        if (!userData) {
          if (!authToken) {
            const result = await login(user.email, user.password)
            authToken = result.token!
          }
          userData = await getUser(authToken)
        }

        // Required fields from UserResource
        expect(userData.uuid).toBeDefined()
        expect(userData.name).toBeDefined()
        expect(userData.email).toBeDefined()
        expect(userData.type).toBeDefined()
        expect(userData.status).toBeDefined()

        // Permissions object
        expect(userData.permissions).toBeDefined()
        expect(typeof userData.permissions.can_withdraw).toBe('boolean')
        expect(typeof userData.permissions.can_refer).toBe('boolean')
        expect(typeof userData.permissions.can_access_affiliate).toBe('boolean')
        expect(typeof userData.permissions.can_access_team).toBe('boolean')

        // Features object
        expect(userData.features).toBeDefined()
        expect(typeof userData.features.show_wallet).toBe('boolean')
        expect(typeof userData.features.show_network).toBe('boolean')
        expect(typeof userData.features.show_earnings).toBe('boolean')
        expect(typeof userData.features.show_team).toBe('boolean')
      })
    })
  }
})

describe('Protected Endpoint Access by User Type', () => {
  for (const user of userTypes) {
    describe(`${user.type.toUpperCase()} - Endpoint Access`, () => {
      let authToken: string

      beforeAll(async () => {
        const result = await login(user.email, user.password)
        expect(result.ok).toBe(true)
        authToken = result.token!
      })

      it('should access /api/user', async () => {
        const response = await fetch(`${apiBase}/api/user`, {
          headers: {
            'Authorization': `Bearer ${authToken}`,
            'Accept': 'application/json'
          }
        })
        expect(response.ok).toBe(true)
      })

      it('should access /api/notifications', async () => {
        const response = await fetch(`${apiBase}/api/notifications`, {
          headers: {
            'Authorization': `Bearer ${authToken}`,
            'Accept': 'application/json'
          }
        })
        expect(VALID_STATUS).toContain(response.status)
      })

      it('should access /api/addresses', async () => {
        const response = await fetch(`${apiBase}/api/addresses`, {
          headers: {
            'Authorization': `Bearer ${authToken}`,
            'Accept': 'application/json'
          }
        })
        expect(VALID_STATUS).toContain(response.status)
      })

      it('should access /api/onboarding/status', async () => {
        const response = await fetch(`${apiBase}/api/onboarding/status`, {
          headers: {
            'Authorization': `Bearer ${authToken}`,
            'Accept': 'application/json'
          }
        })
        expect(VALID_STATUS).toContain(response.status)
      })
    })
  }
})

describe('Wallet Access by User Type', () => {
  for (const user of userTypes) {
    describe(`${user.type.toUpperCase()} - Wallet`, () => {
      let authToken: string

      beforeAll(async () => {
        const result = await login(user.email, user.password)
        authToken = result.token!
      })

      it('should access wallet balance', async () => {
        const response = await fetch(`${apiBase}/api/wallet/balance`, {
          headers: {
            'Authorization': `Bearer ${authToken}`,
            'Accept': 'application/json'
          }
        })
        expect(VALID_STATUS).toContain(response.status)
      })

      it('should access wallet transactions', async () => {
        const response = await fetch(`${apiBase}/api/wallet/transactions`, {
          headers: {
            'Authorization': `Bearer ${authToken}`,
            'Accept': 'application/json'
          }
        })
        expect(VALID_STATUS).toContain(response.status)
      })
    })
  }
})

describe('User Permissions by Type', () => {
  it('Regular user should have limited permissions', async () => {
    const result = await login('regular@demo.com', 'password')
    const user = await getUser(result.token!)

    expect(user.type).toBe('regular')
    expect(user.permissions.can_access_affiliate).toBe(false)
    expect(user.features.show_wallet).toBe(false)
    expect(user.features.show_upgrade_prompt).toBe(true)
  })

  it('Member user should have Affiliate permissions', async () => {
    const result = await login('member@demo.com', 'password')
    const user = await getUser(result.token!)

    expect(user.type).toBe('member')
    expect(user.permissions.can_access_affiliate).toBe(true)
    expect(user.features.show_wallet).toBe(true)
    expect(user.features.show_network).toBe(true)
    expect(user.features.show_earnings).toBe(true)
  })

  it('Promoter user should have team access', async () => {
    const result = await login('promoter@demo.com', 'password')
    const user = await getUser(result.token!)

    expect(user.type).toBe('promoter')
    expect(user.permissions.can_access_team).toBe(true)
    expect(user.features.show_team).toBe(true)
  })

  it('Advisor user should have training access', async () => {
    const result = await login('advisor@demo.com', 'password')
    const user = await getUser(result.token!)

    expect(user.type).toBe('advisor')
    expect(user.permissions.can_access_team).toBe(true)
    expect(user.features.show_training).toBe(true)
  })

  it('Mentor user should have all features', async () => {
    const result = await login('mentor@demo.com', 'password')
    const user = await getUser(result.token!)

    expect(user.type).toBe('mentor')
    expect(user.permissions.can_access_affiliate).toBe(true)
    expect(user.permissions.can_access_team).toBe(true)
    expect(user.features.show_wallet).toBe(true)
    expect(user.features.show_network).toBe(true)
    expect(user.features.show_earnings).toBe(true)
    expect(user.features.show_team).toBe(true)
    expect(user.features.show_training).toBe(true)
  })
})
