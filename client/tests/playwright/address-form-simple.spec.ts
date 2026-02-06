import { test, expect } from '@playwright/test'

test.describe('Address Form Dropdowns - Simple Test', () => {
  test('verify select dropdowns show options', async ({ page }) => {
    page.on('console', (msg) => {
      if (msg.type() === 'error' || msg.text().includes('Failed') || msg.text().includes('undefined')) {
        console.log(`BROWSER ${msg.type()}: ${msg.text()}`)
      }
    })

    // Login with demo user API call
    const apiBase = 'http://localhost:8000'
    const loginResponse = await page.request.post(`${apiBase}/api/auth/login`, {
      data: {
        mobile: '9901000001',
        password: 'password'
      }
    })

    expect(loginResponse.ok()).toBeTruthy()
    const loginData = await loginResponse.json()
    const token = loginData.token || loginData.data?.token

    // Set token in localStorage
    await page.goto('/')
    await page.evaluate((t) => {
      localStorage.setItem('commerinity_auth_token', t)
    }, token)

    // Navigate to onboarding address step directly
    await page.goto('/onboarding', { waitUntil: 'networkidle' })

    // Wait for page load
    await page.waitForTimeout(3000)

    // Try to get past initial steps quickly
    let continueBtn = page.getByRole('button', { name: /Get Started|Continue|Next|Skip/i })
    let attempts = 0
    while (attempts < 10) {
      if (await continueBtn.first().isVisible({ timeout: 2000 }).catch(() => false)) {
        await continueBtn.first().click()
        await page.waitForTimeout(1500)
        attempts++
      } else {
        break
      }
    }

    // Look for address form
    const addressForm = page.locator('.address-form')
    const isAddressVisible = await addressForm.isVisible({ timeout: 5000 }).catch(() => false)

    if (!isAddressVisible) {
      console.log('Address form not yet visible, may need more navigation')
      // Take screenshot for debugging
      await page.screenshot({ path: 'test-results/address-form-not-found.png', fullPage: true })
    }

    expect(isAddressVisible).toBeTruthy()

    console.log('✓ Address form found')

    // Test Country Select
    await page.waitForTimeout(1000)
    const countrySelect = page.locator('[name="country_code"]')
    await countrySelect.waitFor({ state: 'visible', timeout: 10000 })

    // Click the select button (USelectMenu creates a button element)
    const countryButton = countrySelect.locator('button').first()
    await countryButton.click()
    await page.waitForTimeout(1000)

    // Check if dropdown appeared
    const dropdown = page.locator('[role="listbox"]')
    const dropdownVisible = await dropdown.isVisible({ timeout: 3000 }).catch(() => false)

    if (!dropdownVisible) {
      console.log('Dropdown not visible, checking page state...')
      await page.screenshot({ path: 'test-results/dropdown-not-visible.png', fullPage: true })
      const html = await page.content()
      console.log('Has listbox role:', html.includes('listbox'))
    }

    expect(dropdownVisible).toBeTruthy()
    console.log('✓ Country dropdown opened')

    // Check for India option
    const indiaOption = dropdown.locator('text=India')
    const indiaVisible = await indiaOption.isVisible({ timeout: 3000 }).catch(() => false)

    if (!indiaVisible) {
      const options = await dropdown.locator('[role="option"]').count()
      console.log(`Found ${options} options in dropdown`)
      if (options > 0) {
        const firstOptionText = await dropdown.locator('[role="option"]').first().textContent()
        console.log(`First option: ${firstOptionText}`)
      }
    }

    expect(indiaVisible).toBeTruthy()
    console.log('✓ India option found in dropdown')

    // Select India
    await indiaOption.click()
    await page.waitForTimeout(2000)

    console.log('✓ India selected, waiting for states to load')

    // Test State Select
    const stateSelect = page.locator('[name="state_code"]')
    const stateButton = stateSelect.locator('button').first()

    // Check if state select is enabled
    const stateEnabled = await stateButton.isEnabled()
    expect(stateEnabled).toBeTruthy()
    console.log('✓ State select is enabled')

    // Click state select
    await stateButton.click()
    await page.waitForTimeout(1000)

    // Check for state dropdown
    const stateDropdown = page.locator('[role="listbox"]')
    const stateDropdownVisible = await stateDropdown.isVisible({ timeout: 3000 }).catch(() => false)
    expect(stateDropdownVisible).toBeTruthy()
    console.log('✓ State dropdown opened')

    // Check for Maharashtra
    const maharashtraOption = stateDropdown.locator('text=Maharashtra')
    const maharashtraVisible = await maharashtraOption.isVisible({ timeout: 3000 }).catch(() => false)

    if (!maharashtraVisible) {
      const stateOptions = await stateDropdown.locator('[role="option"]').count()
      console.log(`Found ${stateOptions} state options`)
    }

    expect(maharashtraVisible).toBeTruthy()
    console.log('✓ Maharashtra option found')

    // Select Maharashtra
    await maharashtraOption.click()
    await page.waitForTimeout(2000)

    console.log('✓ Maharashtra selected')

    // Check Block select is enabled
    const blockSelect = page.locator('[name="block_id"]')
    const blockButton = blockSelect.locator('button').first()
    const blockEnabled = await blockButton.isEnabled()
    expect(blockEnabled).toBeTruthy()
    console.log('✓ Block select is enabled')

    console.log('\n✅ All select dropdowns working correctly!')
  })
})
