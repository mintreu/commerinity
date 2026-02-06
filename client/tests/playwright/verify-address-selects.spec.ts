import { test, expect } from '@playwright/test'
import { takeShot, randomMobile } from './utils'

test.describe('Address Form Select Dropdowns - Verification', () => {
  test('selects should show options and work correctly', async ({ page }) => {
    // Enable console error logging
    page.on('console', (msg) => {
      if (msg.type() === 'error') {
        console.log(`BROWSER ERROR: ${msg.text()}`)
      }
    })

    const apiBase = 'http://localhost:8000'
    const mobile = randomMobile()

    // Register new user
    console.log(`Creating test user with mobile: ${mobile}`)
    const sendOtpResp = await page.request.post(`${apiBase}/api/auth/send-otp`, {
      data: { type: 'mobile', value: mobile }
    })
    expect(sendOtpResp.ok()).toBeTruthy()

    const registerResp = await page.request.post(`${apiBase}/api/auth/register`, {
      data: {
        name: 'Address Test User',
        mobile,
        otp: '123456',
        password: 'TestPass@123',
        password_confirmation: 'TestPass@123'
      }
    })
    expect(registerResp.ok()).toBeTruthy()

    // Login
    await page.goto('/auth/login', { waitUntil: 'networkidle' })
    await page.waitForTimeout(2000)

    await page.getByRole('button', { name: /Mobile/i }).click()
    await page.getByPlaceholder('10-digit mobile number').fill(mobile)
    await page.getByRole('button', { name: /Password/i }).click()
    await page.getByPlaceholder('Enter your password').fill('TestPass@123')
    await page.getByRole('main').getByRole('button', { name: 'Sign In' }).click()

    // Wait for onboarding
    await page.waitForURL(/\/onboarding/, { timeout: 30000 })
    await page.waitForTimeout(2000)

    // Skip to address form
    for (let i = 0; i < 5; i++) {
      const skipOrContinue = page.getByRole('button', { name: /Get Started|Continue|Next|Skip/i }).first()
      if (await skipOrContinue.isVisible({ timeout: 2000 }).catch(() => false)) {
        await skipOrContinue.click()
        await page.waitForTimeout(1500)
      }
    }

    // Find address form
    const addressForm = page.locator('.address-form')
    await addressForm.waitFor({ state: 'visible', timeout: 10000 })
    console.log('✓ Address form found')

    await takeShot(page, 'address-form-01-initial')

    // TEST 1: Country Select
    console.log('Testing country select...')
    const countrySelect = page.locator('[name="country_code"]').locator('button').first()
    await countrySelect.waitFor({ state: 'visible' })
    await countrySelect.click()
    await page.waitForTimeout(1000)

    await takeShot(page, 'address-form-02-country-dropdown-open')

    const countryDropdown = page.locator('[role="listbox"]')
    await countryDropdown.waitFor({ state: 'visible', timeout: 5000 })
    console.log('✓ Country dropdown opened')

    // Count options
    const countryOptionsCount = await countryDropdown.locator('[role="option"]').count()
    console.log(`Found ${countryOptionsCount} country options`)
    expect(countryOptionsCount).toBeGreaterThan(0)

    // Find and click India
    const indiaOption = countryDropdown.locator('text=India')
    await indiaOption.waitFor({ state: 'visible' })
    console.log('✓ India option visible')
    await indiaOption.click()
    await page.waitForTimeout(2000)

    await takeShot(page, 'address-form-03-india-selected')

    // TEST 2: State Select (should be enabled now)
    console.log('Testing state select...')
    const stateSelect = page.locator('[name="state_code"]').locator('button').first()
    await expect(stateSelect).toBeEnabled()
    console.log('✓ State select is enabled')

    await stateSelect.click()
    await page.waitForTimeout(1000)

    await takeShot(page, 'address-form-04-state-dropdown-open')

    const stateDropdown = page.locator('[role="listbox"]')
    await stateDropdown.waitFor({ state: 'visible', timeout: 5000 })
    console.log('✓ State dropdown opened')

    // Count state options
    const stateOptionsCount = await stateDropdown.locator('[role="option"]').count()
    console.log(`Found ${stateOptionsCount} state options`)
    expect(stateOptionsCount).toBeGreaterThan(0)

    // Select Maharashtra
    const maharashtraOption = stateDropdown.locator('text=Maharashtra')
    await maharashtraOption.waitFor({ state: 'visible' })
    console.log('✓ Maharashtra option visible')
    await maharashtraOption.click()
    await page.waitForTimeout(2000)

    await takeShot(page, 'address-form-05-maharashtra-selected')

    // TEST 3: Block Select (should be enabled)
    console.log('Testing block select...')
    const blockSelect = page.locator('[name="block_id"]').locator('button').first()
    await expect(blockSelect).toBeEnabled()
    console.log('✓ Block select is enabled')

    // Try opening block select (may or may not have options)
    await blockSelect.click()
    await page.waitForTimeout(1000)

    await takeShot(page, 'address-form-06-block-select-clicked')

    console.log('\n✅ ALL TESTS PASSED!')
    console.log('- Country select shows options ✓')
    console.log('- State select enabled after country selection ✓')
    console.log('- State options load correctly ✓')
    console.log('- Block select enabled after state selection ✓')
  })
})
