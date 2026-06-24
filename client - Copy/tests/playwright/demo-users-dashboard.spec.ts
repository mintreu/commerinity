import { test, expect } from '@playwright/test'
import { takeShot } from './utils'

const loginWithEmail = async (page: any, email: string, password: string) => {
  await page.goto('/auth/login')
  await expect(page).toHaveURL(/\/auth\/login/)

  const emailTab = page.getByRole('button', { name: /^Email$/i })
  if (await emailTab.isVisible().catch(() => false)) {
    await emailTab.click()
  }

  await page.getByPlaceholder('you@example.com').fill(email)
  await page.getByPlaceholder('Enter your password').fill(password)

  await page.getByRole('button', { name: /Sign In/i }).click()
  await page.waitForURL(/\/dashboard/, { timeout: 60000 })
}

const demoUsers = [
  { label: 'regular', email: 'regular@demo.com', password: 'password' },
  { label: 'member', email: 'member@demo.com', password: 'password' },
  { label: 'promoter', email: 'promoter@demo.com', password: 'password' },
  { label: 'advisor', email: 'advisor@demo.com', password: 'password' },
  { label: 'mentor', email: 'mentor@demo.com', password: 'password' }
]

test.describe('Demo User Dashboards - Desktop', () => {
  for (const user of demoUsers) {
    test(`Dashboard access for ${user.label}`, async ({ page }) => {
      await loginWithEmail(page, user.email, user.password)
      await takeShot(page, `desktop-dashboard-${user.label}`)
    })
  }
})
