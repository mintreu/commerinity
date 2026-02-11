import { test, expect } from '@playwright/test'
import { takeShot } from './utils'

test.describe('Address Form Select Dropdowns - Verification', () => {
  test('selects should show options and work correctly', async ({ page }) => {
    page.on('console', (msg) => {
      if (msg.type() === 'error') {
        console.log(`BROWSER ERROR: ${msg.text()}`)
      }
    })

    const email = 'e2e.address2@demo.com'
    const password = 'TestPass@123'

    await page.goto('/auth/login', { waitUntil: 'networkidle' })
    await page.waitForTimeout(2000)

    await page.getByRole('button', { name: /Email/i }).click()
    await page.getByPlaceholder('you@example.com').fill(email)
    await page.getByPlaceholder('Enter your password').fill(password)
    await page.getByRole('main').getByRole('button', { name: 'Sign In' }).click()

    await page.waitForURL(/\/onboarding/, { timeout: 30000 })
    await page.waitForTimeout(2000)

    const loadingText = page.getByText('Loading your profile...')
    if (await loadingText.isVisible({ timeout: 2000 }).catch(() => false)) {
      await loadingText.waitFor({ state: 'hidden', timeout: 30000 })
    }

    const getStartedButton = page.getByRole('button', { name: /Get Started|Start/ }).first()
    if (await getStartedButton.isVisible({ timeout: 3000 }).catch(() => false)) {
      await getStartedButton.click()
    }

    const addressForm = page.locator('.address-form')
    if (!await addressForm.isVisible({ timeout: 2000 }).catch(() => false)) {
      const profileNameInput = page.getByPlaceholder('Enter your full name')
      await profileNameInput.waitFor({ state: 'visible', timeout: 30000 })
      await profileNameInput.fill('Address Verify User')
      await page.locator('input[type="date"]').first().fill('1995-06-06')
      await page.getByText('Male', { exact: true }).click()
      await page.getByPlaceholder('Tell us a little about yourself...').fill('E2E onboarding profile bio text.')

      const profileContinue = page.getByRole('button', { name: /Continue|Next/ }).first()
      await expect(profileContinue).toBeEnabled({ timeout: 30000 })
      await profileContinue.click()

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

    await page.getByRole('heading', { name: /Add your delivery address/i }).first().waitFor({ state: 'visible', timeout: 30000 })
    await addressForm.waitFor({ state: 'visible', timeout: 10000 })
    await takeShot(page, 'address-form-01-initial')

    const getSelectButtonByIndex = async (index: number, timeout = 30000) => {
      const button = addressForm.getByRole('button', { name: 'Show popup' }).nth(index)
      await button.waitFor({ state: 'visible', timeout })
      return button
    }

    const countrySelect = await getSelectButtonByIndex(0)
    await countrySelect.waitFor({ state: 'visible' })
    await countrySelect.click()
    await page.waitForTimeout(1000)

    await takeShot(page, 'address-form-02-country-dropdown-open')

    const countryDropdown = page.locator('[role="listbox"]')
    await countryDropdown.waitFor({ state: 'visible', timeout: 5000 })

    const countryOptionsCount = await countryDropdown.locator('[role="option"]').count()
    expect(countryOptionsCount).toBeGreaterThan(0)

    const indiaOption = countryDropdown.locator('text=India')
    await indiaOption.waitFor({ state: 'visible' })
    await indiaOption.click()
    await page.waitForTimeout(2000)

    await takeShot(page, 'address-form-03-india-selected')

    const stateSelect = await getSelectButtonByIndex(1)
    await expect(stateSelect).toBeEnabled()

    await stateSelect.click()
    await page.waitForTimeout(1000)

    await takeShot(page, 'address-form-04-state-dropdown-open')

    const stateDropdown = page.locator('[role="listbox"]')
    await stateDropdown.waitFor({ state: 'visible', timeout: 5000 })

    const stateOptionsCount = await stateDropdown.locator('[role="option"]').count()
    expect(stateOptionsCount).toBeGreaterThan(0)

    const westBengalOption = stateDropdown.locator('text=West Bengal')
    await westBengalOption.waitFor({ state: 'visible' })
    await westBengalOption.click()
    await page.waitForTimeout(2000)

    await takeShot(page, 'address-form-05-state-selected')

    const districtSelect = await getSelectButtonByIndex(2)
    await expect(districtSelect).toBeEnabled()

    await districtSelect.click()
    await page.waitForTimeout(1000)

    const districtDropdown = page.locator('[role="listbox"]')
    await districtDropdown.waitFor({ state: 'visible', timeout: 5000 })
    const districtOptionsCount = await districtDropdown.locator('[role="option"]').count()
    expect(districtOptionsCount).toBeGreaterThan(0)

    const kolkataOption = districtDropdown.locator('text=Kolkata')
    await kolkataOption.waitFor({ state: 'visible' })
    await kolkataOption.click()
    await page.waitForTimeout(1000)

    await takeShot(page, 'address-form-06-district-selected')

    const blockSelect = await getSelectButtonByIndex(3)
    await expect(blockSelect).toBeEnabled()

    await blockSelect.click()
    await page.waitForTimeout(1000)

    await takeShot(page, 'address-form-07-block-select-clicked')
  })
})
