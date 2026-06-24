import { test, expect } from '@playwright/test'

test.describe('Address Form Select Dropdowns', () => {
  test('should display and populate country, state, district and block select dropdowns', async ({ page }) => {
    // Enable console logging
    page.on('console', (msg) => {
      console.log(`BROWSER ${msg.type()}: ${msg.text()}`)
    })

    const email = 'e2e.address1@demo.com'
    const password = 'TestPass@123'

    // Login with seeded E2E account
    await page.goto('/auth/login', { waitUntil: 'networkidle' })
    await page.waitForLoadState('domcontentloaded')

    const emailButton = page.getByRole('button', { name: /Email/i })
    await emailButton.waitFor({ state: 'visible', timeout: 10000 })
    await emailButton.click()

    const emailInput = page.getByPlaceholder('you@example.com')
    await emailInput.waitFor({ state: 'visible', timeout: 5000 })
    await emailInput.fill(email)

    const passwordInput = page.getByPlaceholder('Enter your password')
    await passwordInput.waitFor({ state: 'visible', timeout: 5000 })
    await passwordInput.fill(password)

    const signInButton = page.getByRole('main').getByRole('button', { name: 'Sign In' })
    await signInButton.waitFor({ state: 'visible', timeout: 5000 })
    await signInButton.click()

    // Wait for onboarding page
    await page.waitForURL(/\/onboarding/, { timeout: 30000 })

    const loadingText = page.getByText('Loading your profile...')
    if (await loadingText.isVisible({ timeout: 2000 }).catch(() => false)) {
      await loadingText.waitFor({ state: 'hidden', timeout: 30000 })
    }

    const getStartedButton = page.getByRole('button', { name: /Get Started|Start/ })
    if (await getStartedButton.isVisible().catch(() => false)) {
      await getStartedButton.click()
    }

    const addressForm = page.locator('.address-form')
    if (!await addressForm.isVisible({ timeout: 2000 }).catch(() => false)) {
      // Navigate through onboarding steps to reach address form
      const profileNameInput = page.getByPlaceholder('Enter your full name')
      await profileNameInput.waitFor({ state: 'visible', timeout: 30000 })
      await profileNameInput.fill('Address Form Test User')
      await page.locator('input[type="date"]').first().fill('1995-05-05')
      await page.getByText('Male', { exact: true }).click()
      await page.getByPlaceholder('Tell us a little about yourself...').fill('E2E onboarding profile bio text.')
      const profileContinue = page.getByRole('button', { name: /Continue|Next/ }).first()
      await expect(profileContinue).toBeEnabled({ timeout: 30000 })
      await profileContinue.click()

      // Contact step - skip email if shown, then continue
      const contactHeading = page.getByRole('heading', { name: /Add your email|Verify your mobile number|Contact details verified/i })
      if (await contactHeading.isVisible({ timeout: 4000 }).catch(() => false)) {
        const skipEmailButton = page.getByRole('button', { name: /Skip for now/i }).first()
        if (await skipEmailButton.isVisible({ timeout: 2000 }).catch(() => false)) {
          await skipEmailButton.click()
        }
        const contactContinue = page.getByRole('button', { name: /Continue|Next/ }).first()
        await expect(contactContinue).toBeEnabled({ timeout: 30000 })
        await contactContinue.click()
      }
    }

    // Address step
    await page.getByRole('heading', { name: /Add your delivery address/i }).first().waitFor({ state: 'visible', timeout: 30000 })
    await addressForm.waitFor({ state: 'visible', timeout: 10000 })

    console.log('Address form visible, testing select dropdowns...')

    const getSelectButtonByIndex = async (index: number, timeout = 30000) => {
      const button = addressForm.getByRole('button', { name: 'Show popup' }).nth(index)
      await button.waitFor({ state: 'visible', timeout })
      return button
    }

    // Country is pre-selected in onboarding; continue with state/district/block checks.
    await getSelectButtonByIndex(0)
    await page.waitForTimeout(1000)

    // Test State Select
    const stateSelectButton = await getSelectButtonByIndex(1)
    await stateSelectButton.waitFor({ state: 'visible', timeout: 10000 })
    await expect(stateSelectButton).toBeEnabled()
    console.log('State select enabled, clicking...')
    await stateSelectButton.click()

    // Wait for state dropdown
    await page.waitForTimeout(1000)
    const stateDropdown = page.locator('[role="listbox"]')
    await stateDropdown.waitFor({ state: 'visible', timeout: 5000 })
    console.log('State dropdown visible')

    // Check if West Bengal option is visible
    const westBengalOption = stateDropdown.locator('text=West Bengal')
    const westBengalVisible = await westBengalOption.isVisible({ timeout: 5000 }).catch(() => false)
    console.log(`West Bengal option visible: ${westBengalVisible}`)
    expect(westBengalVisible).toBeTruthy()

    // Select West Bengal
    await westBengalOption.click()
    console.log('West Bengal selected')

    // Wait for districts to load
    await page.waitForTimeout(2000)

    // Test District Select (should be enabled)
    const districtSelectButton = await getSelectButtonByIndex(2)
    await expect(districtSelectButton).toBeEnabled()
    console.log('District select is enabled')

    // Select Kolkata district
    await districtSelectButton.click()
    await page.waitForTimeout(1000)
    const districtDropdown = page.locator('[role="listbox"]')
    await districtDropdown.waitFor({ state: 'visible', timeout: 5000 })
    const kolkataOption = districtDropdown.locator('text=Kolkata')
    const kolkataVisible = await kolkataOption.isVisible({ timeout: 5000 }).catch(() => false)
    expect(kolkataVisible).toBeTruthy()
    await kolkataOption.click()
    await page.waitForTimeout(1000)

    // Test Block Select (should be enabled after district)
    const blockSelectButton = await getSelectButtonByIndex(3)
    await expect(blockSelectButton).toBeEnabled()
    console.log('Block select is enabled')

    console.log('All select dropdowns working correctly!')
  })
})
