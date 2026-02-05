import { test, expect } from '@playwright/test'
import { randomEmail, randomMobile, takeShot } from './utils'

test.describe('Guest to Regular Onboarding - Desktop', () => {
  test('Desktop registration + onboarding', async ({ page }) => {
    const mobile = randomMobile()
    const email = randomEmail()

    await page.goto('/auth/register')
    await expect(page).toHaveURL(/\/auth\/register/)
    await takeShot(page, 'desktop-01-register')

    await page.getByPlaceholder('+91 9XXXXXXXXX').fill(mobile)
    await page.getByRole('button', { name: 'Send OTP' }).click()

    await page.getByPlaceholder('123456').fill('123456')
    await page.getByRole('button', { name: 'Continue' }).click()

    await page.getByPlaceholder('John Doe').fill('Demo Regular User')
    await page.getByPlaceholder('you@example.com').fill(email)
    await takeShot(page, 'desktop-02-register-details')
    await page.getByRole('button', { name: 'Continue' }).click()

    await page.getByPlaceholder('At least 8 characters').fill('StrongPass@1')
    await page.getByPlaceholder('Re-enter your password').fill('StrongPass@1')
    await page.getByRole('checkbox').check()
    await takeShot(page, 'desktop-03-register-password')
    await page.getByRole('button', { name: 'Create Account' }).click()

    await page.waitForURL(/\/onboarding/)
    await takeShot(page, 'desktop-04-onboarding-welcome')

    await page.getByRole('button', { name: /Get Started|Start/ }).click()

    await page.getByPlaceholder('Enter your full name').fill('Demo Regular User')
    await page.getByLabel('Date of Birth').fill('1995-02-02')
    await page.getByText('Male', { exact: true }).click()
    await takeShot(page, 'desktop-05-onboarding-profile')
    await page.getByRole('button', { name: /Continue|Next/ }).click()

    await expect(page.getByText(/Verify your mobile number|Add your email/i)).toBeVisible()
    await takeShot(page, 'desktop-06-onboarding-contact')
    await page.getByRole('button', { name: /Continue|Next/ }).click()

    await page.getByRole('button', { name: 'Skip' }).click().catch(() => {})

    await page.getByPlaceholder('Enter recipient name').fill('Demo Regular User')
    await page.getByPlaceholder('+91 9876543210').fill(mobile)
    await page.getByPlaceholder('House no., Building name, Street').fill('221B Baker Street')
    await page.getByPlaceholder('Area, Landmark').fill('Near Demo Market')
    await page.getByPlaceholder('Enter city name').fill('Kolkata')
    await page.getByPlaceholder('Enter postal code').fill('700001')

    await page.getByRole('button', { name: /Select country/i }).click()
    await page.getByRole('option', { name: /India/i }).click()

    await page.getByRole('button', { name: /Select state/i }).click()
    await page.getByRole('option', { name: /West Bengal/i }).click()

    await takeShot(page, 'desktop-07-onboarding-address')
    await page.getByRole('button', { name: /Continue|Next/ }).click()

    await page.getByRole('button', { name: /Skip KYC/i }).click()
    await takeShot(page, 'desktop-08-onboarding-kyc-skip')

    await page.getByRole('button', { name: /Complete Setup|Finish|Continue/ }).click()
    await page.waitForURL(/\/dashboard/)
    await takeShot(page, 'desktop-09-dashboard-regular')
  })

})
