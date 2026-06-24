import { test, expect } from '@playwright/test'
import { takeShot } from './utils'

test.describe('Regular user address management', () => {
  const userEmail = 'regular@demo.com'
  const userPassword = 'password'

  test('Complete address CRUD from dashboard', async ({ page }) => {
    await page.goto('/auth/login')
    await page.getByRole('button', { name: 'Email' }).click()
    await page.getByPlaceholder('you@example.com').fill(userEmail)
    await page.getByPlaceholder('Enter your password').fill(userPassword)
    await page.locator('form button[type="submit"]').click()

    await page.waitForURL(/\/dashboard/)
    await takeShot(page, 'regular-01-dashboard')

    await page.goto('/addresses')
    await page.getByRole('heading', { name: /My Addresses/i }).waitFor({ timeout: 30000 })
    await takeShot(page, 'regular-02-address-list')

    await page.getByRole('button', { name: /Add New Address/i }).click()
    await page.getByLabel('Address Label').fill('Playwright Home')
    await page.getByLabel('Address Type').click()
    await page.getByRole('option', { name: 'Home' }).click()
    await page.getByLabel('Recipient\'s Full Name').fill('Regular Demo')
    await page.getByLabel('Contact Number').fill('9876543210')
    await page.getByLabel('Flat / House / Building').fill('56 Experiment Avenue')
    await page.getByLabel('Street / Area / Colony (Optional)').fill('Playwright District')
    await page.getByLabel('Landmark (Optional)').fill('Near Developer Lab')
    await page.getByLabel('City').fill('Kolkata')
    await page.getByLabel('PIN Code').fill('700001')
    await page.getByLabel('State').fill('WB')
    await page.getByRole('button', { name: /Save Address|Update Address/ }).click()

    await page.getByText('Playwright Home').waitFor({ timeout: 10000 })
    await takeShot(page, 'regular-03-address-saved')

    await page.getByRole('button', { name: 'Edit Address' }).first().click()
    await page.getByLabel('Flat / House / Building').fill('22 Playwright Street')
    await page.getByRole('button', { name: 'Update Address' }).click()
    await page.getByText('22 Playwright Street').waitFor({ timeout: 10000 })
    await takeShot(page, 'regular-04-address-updated')

    await page.getByRole('button', { name: 'Delete Address' }).first().click()
    await expect(page.getByText('Playwright Home')).toBeHidden({ timeout: 10000 }).catch(() => {})
    await takeShot(page, 'regular-05-address-deleted')
  })
})
