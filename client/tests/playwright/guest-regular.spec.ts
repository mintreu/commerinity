import { test, expect } from '@playwright/test'
import { randomEmail, randomMobile, takeShot } from './utils'

test.describe('Guest to Regular Onboarding - Desktop', () => {
  const completeOnboarding = async (page: any, options: { verifyEmail: boolean }) => {
    const mobile = randomMobile()
    const email = randomEmail()

    await page.goto('/auth/register')
    await expect(page).toHaveURL(/\/auth\/register/)
    await takeShot(page, 'desktop-01-register')

    await page.getByPlaceholder('10-digit mobile number').fill(mobile)
    const sendOtpButton = page.getByRole('button', { name: 'Send OTP' })
    await expect(sendOtpButton).toBeEnabled({ timeout: 30000 })
    await sendOtpButton.click()

    const otpInput = page.getByPlaceholder('123456')
    if (!await otpInput.isVisible().catch(() => false)) {
      await sendOtpButton.click()
    }
    await expect(otpInput).toBeVisible({ timeout: 60000 })
    await otpInput.fill('123456')
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
    const loadingText = page.getByText('Loading your profile...')
    if (await loadingText.isVisible({ timeout: 2000 }).catch(() => false)) {
      await loadingText.waitFor({ state: 'hidden', timeout: 30000 })
    }
    await takeShot(page, 'desktop-04-onboarding-welcome')

    const getStartedButton = page.getByRole('button', { name: /Get Started|Start/ })
    if (await getStartedButton.isVisible().catch(() => false)) {
      await getStartedButton.click()
    }

    await page.getByPlaceholder('Enter your full name').fill('Demo Regular User')
    const dobInput = page.locator('input[type="date"]').first()
    await dobInput.waitFor({ state: 'visible', timeout: 30000 })
    for (let attempt = 0; attempt < 3; attempt += 1) {
      await dobInput.fill('1995-02-02')
      if ((await dobInput.inputValue()) === '1995-02-02') break
      await dobInput.evaluate((el, value) => {
        const input = el as HTMLInputElement
        input.value = value as string
        input.dispatchEvent(new Event('input', { bubbles: true }))
        input.dispatchEvent(new Event('change', { bubbles: true }))
      }, '1995-02-02')
      if ((await dobInput.inputValue()) === '1995-02-02') break
      await page.waitForTimeout(200)
    }
    await expect(dobInput).toHaveValue('1995-02-02')

    const maleRadio = page.locator('button[role="radio"][aria-label="Male"]').first()
    await maleRadio.click()
    if ((await maleRadio.getAttribute('aria-checked')) !== 'true') {
      await page.getByText('Male', { exact: true }).click()
    }
    await expect(maleRadio).toHaveAttribute('aria-checked', 'true')
    await page.getByPlaceholder('Tell us a little about yourself...').fill('Demo bio for onboarding flow.')
    await takeShot(page, 'desktop-05-onboarding-profile')
    const profileContinue = page.getByRole('button', { name: /Continue|Next/ })
    await expect(profileContinue).toBeEnabled()
    await profileContinue.click()

    const contactHeading = page.getByRole('heading', { name: /Add your email|Verify your mobile number|Contact details verified/i })
    const addressHeading = page.getByRole('heading', { name: /Add your delivery address/i })

    if (await contactHeading.isVisible().catch(() => false)) {
      await takeShot(page, 'desktop-06-onboarding-contact')
      if (await page.getByRole('heading', { name: /Add your email/i }).isVisible().catch(() => false)) {
        if (options.verifyEmail) {
          const sendCodeButton = page.getByRole('button', { name: /Send Verification Code/i })
          if (await sendCodeButton.isVisible().catch(() => false)) {
            await sendCodeButton.click()
          }
          const otpInput = page.getByPlaceholder('123456')
          await otpInput.waitFor({ state: 'visible', timeout: 30000 })
          await otpInput.fill('123456')
          await page.getByRole('button', { name: /Verify/i }).click()
          await page.getByText(/Email verified/i).waitFor({ timeout: 30000 }).catch(() => {})
        } else {
          const skipEmailButton = page.getByRole('button', { name: /Skip for now/i })
          if (await skipEmailButton.isVisible().catch(() => false)) {
            await skipEmailButton.click()
          }
        }
      }
      const contactContinue = page.getByRole('button', { name: /Continue|Next/ })
      await expect(contactContinue).toBeEnabled({ timeout: 30000 })
      await contactContinue.click()
      const addressHeadingAfterContact = page.getByRole('heading', { name: /Add your delivery address/i })
      if (!await addressHeadingAfterContact.isVisible().catch(() => false)) {
        await contactContinue.click()
      }
    }

    const addressTitle = page.getByRole('heading', { name: /Add your delivery address/i }).first()
    await addressTitle.waitFor({ state: 'visible', timeout: 30000 })
    await addressTitle.scrollIntoViewIfNeeded()
    await expect(contactHeading).toBeHidden({ timeout: 30000 }).catch(() => {})

    const geoSkip = page.getByRole('button', { name: 'Skip' })
    if (await geoSkip.isVisible().catch(() => false)) {
      await geoSkip.click()
    }

    await page.getByPlaceholder('Enter recipient name').waitFor({ state: 'visible', timeout: 30000 })
    await page.getByPlaceholder('Enter recipient name').fill('Demo Regular User')
    await page.getByPlaceholder('10-digit mobile number').fill(mobile)
    await page.getByPlaceholder('House no., Building name, Street').fill('221B Baker Street')
    await page.getByPlaceholder('Area, Landmark').fill('Near Demo Market')
    await page.getByPlaceholder('Enter city name').fill('Kolkata')
    await page.getByPlaceholder('Enter postal code').fill('700001')

    // Find country select by field name
    const countrySelect = page.locator('[name="country_code"]').locator('button').first()
    await countrySelect.waitFor({ state: 'visible', timeout: 10000 })
    await countrySelect.scrollIntoViewIfNeeded()
    await countrySelect.click()

    // Wait for dropdown and select India
    await page.waitForTimeout(1000)
    const countryDropdown = page.locator('[role="listbox"]')
    await countryDropdown.waitFor({ state: 'visible', timeout: 5000 })
    const indiaOption = page.getByRole('option', { name: /India/i })
    await indiaOption.waitFor({ timeout: 30000 })
    await indiaOption.click()

    // Wait for states to load
    await page.waitForTimeout(2000)

    // Find state select by field name
    const stateSelect = page.locator('[name="state_code"]').locator('button').first()
    await stateSelect.waitFor({ state: 'visible', timeout: 10000 })
    await expect(stateSelect).toBeEnabled()
    await stateSelect.scrollIntoViewIfNeeded()
    await stateSelect.click()

    // Wait for state dropdown and select West Bengal
    await page.waitForTimeout(1000)
    const stateDropdown = page.locator('[role="listbox"]')
    await stateDropdown.waitFor({ state: 'visible', timeout: 5000 })
    const stateOption = page.getByRole('option', { name: /West Bengal/i })
    await stateOption.waitFor({ timeout: 30000 })
    const optionCount = await page.getByRole('option').count()
    expect(optionCount).toBeGreaterThan(0)
    await stateOption.click()

    // Verify block select is enabled after state selection
    await page.waitForTimeout(1000)
    const blockSelect = page.locator('[name="block_id"]').locator('button').first()
    if (await blockSelect.isVisible({ timeout: 3000 }).catch(() => false)) {
      await expect(blockSelect).toBeEnabled()
      console.log('✓ Block select enabled after state selection')
      await blockSelect.scrollIntoViewIfNeeded()
      await blockSelect.click()
      await page.waitForTimeout(500)
      const blockOptions = await page.getByRole('option').count()
      expect(blockOptions).toBeGreaterThan(0)
      await page.getByRole('option').first().click()
    }

    await takeShot(page, 'desktop-07-onboarding-address')
    await page.getByRole('button', { name: /Continue|Next/ }).click()

    await page.getByRole('button', { name: /Skip KYC/i }).click()
    await takeShot(page, 'desktop-08-onboarding-kyc-skip')

    await page.getByRole('button', { name: /Complete Setup|Finish|Continue/ }).click()
    await page.waitForURL(/\/dashboard/)
    await takeShot(page, 'desktop-09-dashboard-regular')
  }

  test('Desktop registration + onboarding (email skip)', async ({ page }) => {
    await completeOnboarding(page, { verifyEmail: false })
  })

  test('Desktop registration + onboarding (email verify)', async ({ page }) => {
    await completeOnboarding(page, { verifyEmail: true })
  })

})
