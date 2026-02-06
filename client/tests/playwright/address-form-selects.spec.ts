import { test, expect } from '@playwright/test'

test.describe('Address Form Select Dropdowns', () => {
  test('should display and populate country, state, and block select dropdowns', async ({ page }) => {
    // Enable console logging
    page.on('console', (msg) => {
      console.log(`BROWSER ${msg.type()}: ${msg.text()}`)
    })

    const apiBase = 'http://localhost:8000'
    const mobile = `+91990${Math.floor(1000000 + Math.random() * 8999999)}`

    // Register a new user
    const sendOtpResponse = await page.request.post(`${apiBase}/api/auth/send-otp`, {
      data: { type: 'mobile', value: mobile }
    })
    expect(sendOtpResponse.ok()).toBeTruthy()

    const registerResponse = await page.request.post(`${apiBase}/api/auth/register`, {
      data: {
        name: 'Address Form Test User',
        mobile,
        otp: '123456',
        password: 'TestPass@123',
        password_confirmation: 'TestPass@123'
      }
    })
    expect(registerResponse.ok()).toBeTruthy()

    // Login
    await page.goto('/auth/login', { waitUntil: 'networkidle' })
    await page.waitForLoadState('domcontentloaded')

    const mobileButton = page.getByRole('button', { name: /Mobile/i })
    await mobileButton.waitFor({ state: 'visible', timeout: 10000 })
    await mobileButton.click()

    const mobileInput = page.getByPlaceholder('10-digit mobile number')
    await mobileInput.waitFor({ state: 'visible', timeout: 5000 })
    await mobileInput.fill(mobile.replace('+91', ''))

    const passwordButton = page.getByRole('button', { name: /Password/i })
    await passwordButton.waitFor({ state: 'visible', timeout: 5000 })
    await passwordButton.click()

    const passwordInput = page.getByPlaceholder('Enter your password')
    await passwordInput.waitFor({ state: 'visible', timeout: 5000 })
    await passwordInput.fill('TestPass@123')

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

    // Navigate through onboarding steps to reach address form
    // Profile step
    const profileNameInput = page.getByPlaceholder('Enter your full name')
    await profileNameInput.waitFor({ state: 'visible', timeout: 30000 })
    await profileNameInput.fill('Address Form Test User')
    await page.getByLabel('Date of Birth').fill('1995-05-05')
    await page.getByText('Male', { exact: true }).click()
    await page.getByRole('button', { name: /Continue|Next/ }).click()

    // Contact step - skip if present
    await page.waitForTimeout(2000)
    const continueButton = page.getByRole('button', { name: /Continue|Next/ })
    if (await continueButton.isVisible().catch(() => false)) {
      await continueButton.click()
    }

    // Address step
    await page.waitForTimeout(2000)
    const addressForm = page.locator('.address-form')
    await addressForm.waitFor({ state: 'visible', timeout: 10000 })

    console.log('Address form visible, testing select dropdowns...')

    // Test Country Select
    const countrySelectButton = page.locator('[name="country_code"]').locator('button').first()
    await countrySelectButton.waitFor({ state: 'visible', timeout: 10000 })
    console.log('Country select button found, clicking...')
    await countrySelectButton.click()

    // Wait for dropdown menu
    await page.waitForTimeout(1000)
    const countryDropdown = page.locator('[role="listbox"]')
    await countryDropdown.waitFor({ state: 'visible', timeout: 5000 })
    console.log('Country dropdown visible')

    // Check if India option is visible
    const indiaOption = countryDropdown.locator('text=India')
    const indiaVisible = await indiaOption.isVisible({ timeout: 5000 }).catch(() => false)
    console.log(`India option visible: ${indiaVisible}`)
    expect(indiaVisible).toBeTruthy()

    // Select India
    await indiaOption.click()
    console.log('India selected')

    // Wait for states to load
    await page.waitForTimeout(2000)

    // Test State Select
    const stateSelectButton = page.locator('[name="state_code"]').locator('button').first()
    await stateSelectButton.waitFor({ state: 'visible', timeout: 10000 })
    await expect(stateSelectButton).toBeEnabled()
    console.log('State select enabled, clicking...')
    await stateSelectButton.click()

    // Wait for state dropdown
    await page.waitForTimeout(1000)
    const stateDropdown = page.locator('[role="listbox"]')
    await stateDropdown.waitFor({ state: 'visible', timeout: 5000 })
    console.log('State dropdown visible')

    // Check if Maharashtra option is visible
    const maharashtraOption = stateDropdown.locator('text=Maharashtra')
    const maharashtraVisible = await maharashtraOption.isVisible({ timeout: 5000 }).catch(() => false)
    console.log(`Maharashtra option visible: ${maharashtraVisible}`)
    expect(maharashtraVisible).toBeTruthy()

    // Select Maharashtra
    await maharashtraOption.click()
    console.log('Maharashtra selected')

    // Wait for blocks to potentially load
    await page.waitForTimeout(2000)

    // Test Block Select (should be enabled)
    const blockSelectButton = page.locator('[name="block_id"]').locator('button').first()
    await expect(blockSelectButton).toBeEnabled()
    console.log('Block select is enabled')

    console.log('All select dropdowns working correctly!')
  })
})
