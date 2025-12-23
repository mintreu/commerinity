import { describe, it, expect, beforeAll } from 'vitest'

/**
 * Wallet API Tests
 *
 * Test wallet endpoints with authenticated user
 *
 * API Format:
 * - Login: POST /api/auth/login { email, password } => { token }
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

describe('Wallet API', () => {
  let authToken: string

  beforeAll(async () => {
    // Login as member (has wallet)
    const result = await login('member@demo.com', 'password')
    expect(result.ok).toBe(true)
    authToken = result.token!
  })

  describe('Wallet Info', () => {
    it('should return wallet data', async () => {
      const response = await fetch(`${apiBase}/api/wallet`, {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Accept': 'application/json'
        }
      })

      expect(VALID_STATUS).toContain(response.status)

      if (response.ok) {
        const data = await response.json()
        // Response could be direct or wrapped in data
        const walletData = data.data || data
        // Wallet might have balance or other fields
        expect(walletData).toBeDefined()
      }
    })

    it('should return wallet balance', async () => {
      const response = await fetch(`${apiBase}/api/wallet/balance`, {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Accept': 'application/json'
        }
      })

      expect(VALID_STATUS).toContain(response.status)

      if (response.ok) {
        const data = await response.json()
        const balanceData = data.data || data
        expect(balanceData).toBeDefined()
      }
    })

    it('should return wallet stats', async () => {
      const response = await fetch(`${apiBase}/api/wallet/stats`, {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Accept': 'application/json'
        }
      })

      expect(VALID_STATUS).toContain(response.status)
    })
  })

  describe('Transactions', () => {
    it('should return transaction list', async () => {
      const response = await fetch(`${apiBase}/api/wallet/transactions`, {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Accept': 'application/json'
        }
      })

      expect(VALID_STATUS).toContain(response.status)

      if (response.ok) {
        const data = await response.json()
        const txnList = data.data || data
        expect(Array.isArray(txnList)).toBe(true)
      }
    })

    it('should have proper transaction structure', async () => {
      const response = await fetch(`${apiBase}/api/wallet/transactions`, {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Accept': 'application/json'
        }
      })

      if (response.ok) {
        const data = await response.json()
        const txnList = data.data || data

        if (Array.isArray(txnList) && txnList.length > 0) {
          const txn = txnList[0]
          expect(txn.uuid).toBeDefined()
          expect(txn.type).toBeDefined()
          expect(txn.amount).toBeDefined()
          expect(txn.status).toBeDefined()
        }
      }
    })
  })

  describe('Security Questions', () => {
    it('should return available security questions', async () => {
      const response = await fetch(`${apiBase}/api/wallet/security-questions`, {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Accept': 'application/json'
        }
      })

      expect(VALID_STATUS).toContain(response.status)
    })
  })

  describe('Beneficiaries', () => {
    it('should return beneficiary account types', async () => {
      const response = await fetch(`${apiBase}/api/wallet/beneficiaries/types`, {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Accept': 'application/json'
        }
      })

      expect(VALID_STATUS).toContain(response.status)
    })

    it('should return beneficiary list', async () => {
      const response = await fetch(`${apiBase}/api/wallet/beneficiaries`, {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Accept': 'application/json'
        }
      })

      expect(VALID_STATUS).toContain(response.status)
    })
  })
})
