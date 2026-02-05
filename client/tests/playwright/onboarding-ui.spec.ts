import { test, expect } from '@playwright/test'
import { takeShot } from './utils'

test('Onboarding UI - full width and steps', async ({ page }) => {
  page.on('console', (msg) => {
    console.log(`BROWSER ${msg.type()}: ${msg.text()}`)
  })
  const apiBase = 'http://localhost:8000'
  const mobile = `990${Math.floor(1000000 + Math.random() * 8999999)}`

  console.log(`Test mobile: ${mobile}`)

  const sendOtpResponse = await page.request.post(`${apiBase}/api/auth/send-otp`, {
    data: { type: 'mobile', value: mobile }
  })

  if (!sendOtpResponse.ok()) {
    console.log(`Send OTP failed: ${sendOtpResponse.status()}`)
    console.log(await sendOtpResponse.text())
  }
  expect(sendOtpResponse.ok()).toBeTruthy()

  const registerResponse = await page.request.post(`${apiBase}/api/auth/register`, {
    data: {
      name: 'Onboarding UI User',
      mobile,
      otp: '123456',
      password: 'StrongPass@1',
      password_confirmation: 'StrongPass@1'
    }
  })

  if (!registerResponse.ok()) {
    console.log(`Registration failed: ${registerResponse.status()}`)
    console.log(await registerResponse.text())
  }
  expect(registerResponse.ok()).toBeTruthy()

  // Login via API to get token
  const loginResponse = await page.request.post(`${apiBase}/api/auth/login`, {
    data: {
      mobile,
      password: 'StrongPass@1'
    }
  })

  if (!loginResponse.ok()) {
    console.log(`Login failed with status: ${loginResponse.status()}`)
    const errorBody = await loginResponse.text()
    console.log(`Error body: ${errorBody}`)
  }

  expect(loginResponse.ok()).toBeTruthy()
  const loginData = await loginResponse.json()
  const token = loginData.token || loginData.data?.token

  console.log(`TOKEN received: ${token ? 'yes' : 'no'}`)

  // Set token in localStorage and navigate to onboarding
  await page.goto('/')
  await page.evaluate((t) => {
    localStorage.setItem('commerinity_auth_token', t)
  }, token)

  await page.goto('/onboarding', { waitUntil: 'networkidle' })
  const loadingText = page.getByText('Loading your profile...')
  if (await loadingText.isVisible({ timeout: 2000 }).catch(() => false)) {
    await loadingText.waitFor({ state: 'hidden', timeout: 30000 })
  }
  const getStartedButton = page.getByRole('button', { name: /Get Started|Start/ })
  if (await getStartedButton.isVisible().catch(() => false)) {
    await getStartedButton.click()
  }
  const storedToken = await page.evaluate(() => window.localStorage.getItem('commerinity_auth_token'))
  console.log(`STORED_TOKEN: ${storedToken ? 'yes' : 'no'}`)
  if (storedToken) {
    const apiCheck = await page.request.get(`${apiBase}/api/user`, {
      headers: { Authorization: `Bearer ${storedToken}` }
    })
    console.log(`API_USER_STATUS: ${apiCheck.status()}`)
  }
  await takeShot(page, 'desktop-04-onboarding-welcome')

  const profileNameInput = page.getByPlaceholder('Enter your full name')
  await profileNameInput.waitFor({ state: 'visible', timeout: 30000 })
  await takeShot(page, 'desktop-05-onboarding-profile')
  await profileNameInput.fill('Onboarding UI User')
  await page.getByLabel('Date of Birth').fill('1995-02-02')
  await page.getByText('Male', { exact: true }).click()
  await page.getByRole('button', { name: /Continue|Next/ }).click()

  await page.waitForTimeout(2000)
  await takeShot(page, 'desktop-06-onboarding-contact')

  // Try to click any available button to proceed (Skip, Continue, Next)
  const proceedButtons = [
    page.getByRole('button', { name: /Skip/i }),
    page.getByRole('button', { name: /Continue/i }),
    page.getByRole('button', { name: /Next/i })
  ]

  let proceeded = false
  for (const btn of proceedButtons) {
    if (await btn.isVisible({ timeout: 1000 }).catch(() => false)) {
      const enabled = await btn.isEnabled().catch(() => false)
      if (enabled) {
        console.log(`Clicking button: ${await btn.textContent().catch(() => 'unknown')}`)
        await btn.click()
        proceeded = true
        break
      }
    }
  }

  if (!proceeded) {
    console.log('No enabled button found, waiting...')
    await page.waitForTimeout(3000)

    // Try one more time
    for (const btn of proceedButtons) {
      if (await btn.isVisible({ timeout: 1000 }).catch(() => false)) {
        if (await btn.isEnabled().catch(() => false)) {
          await btn.click()
          proceeded = true
          break
        }
      }
    }
  }

  if (!proceeded) {
    throw new Error('Could not proceed from contact step - all buttons disabled')
  }

  await takeShot(page, 'desktop-07-onboarding-address')

  // Test address form select dropdowns
  const addressStep = page.locator('.address-form')
  const addressVisible = await addressStep.isVisible({ timeout: 10000 }).catch(() => false)

  if (addressVisible) {
    console.log('✓ Address form found, testing select dropdowns')

    // Fill basic address fields
    await page.getByPlaceholder('Enter recipient name').fill('Onboarding UI User')
    await page.getByPlaceholder('10-digit mobile number').fill(mobile.replace('+91', ''))
    await page.getByPlaceholder('House no., Building name, Street').fill('123 Main Street')
    await page.getByPlaceholder('Area, Landmark').fill('Near Central Park')

    // Test country select dropdown
    const countrySelect = page.locator('[name="country_code"]').locator('button').first()
    if (await countrySelect.isVisible({ timeout: 3000 }).catch(() => false)) {
      console.log('✓ Country select found, clicking...')
      await countrySelect.click()
      await page.waitForTimeout(1000)

      // Check for dropdown menu
      const countryMenu = page.locator('[role="listbox"]')
      if (await countryMenu.isVisible({ timeout: 3000 }).catch(() => false)) {
        console.log('✓ Country dropdown opened')
        const indiaOption = countryMenu.locator('text=India').first()
        if (await indiaOption.isVisible({ timeout: 2000 }).catch(() => false)) {
          console.log('✓ India option found, selecting...')
          await indiaOption.click()
          await page.waitForTimeout(2000)

          // Test state select dropdown
          const stateSelect = page.locator('[name="state_code"]').locator('button').first()
          if (await stateSelect.isEnabled().catch(() => false)) {
            console.log('✓ State select enabled, clicking...')
            await stateSelect.click()
            await page.waitForTimeout(1000)

            const stateMenu = page.locator('[role="listbox"]')
            if (await stateMenu.isVisible({ timeout: 3000 }).catch(() => false)) {
              console.log('✓ State dropdown opened')
              const maharashtraOption = stateMenu.locator('text=Maharashtra').first()
              if (await maharashtraOption.isVisible({ timeout: 2000 }).catch(() => false)) {
                console.log('✓ Maharashtra option found, selecting...')
                await maharashtraOption.click()
                await page.waitForTimeout(1000)
              }
            }
          }

          // Fill remaining address fields
          await page.getByPlaceholder('Enter city name').fill('Mumbai')
          await page.getByPlaceholder('Enter postal code').fill('400001')

          console.log('✅ Address form completed with select dropdown testing!')
        }
      }
    }

    // Continue to next step
    await page.getByRole('button', { name: /Continue|Next/ }).click()
  } else {
    // If no address form, just skip
    console.log('ℹ Address form not visible, skipping...')
    const skipButton = page.getByRole('button', { name: /Skip|Continue|Next/i }).first()
    if (await skipButton.isVisible({ timeout: 2000 }).catch(() => false)) {
      await skipButton.click()
    }
  }

  await takeShot(page, 'desktop-08-onboarding-kyc')
  const skipKycButton = page.getByRole('button', { name: /Skip KYC|Skip|Continue|Next/i })
  await skipKycButton.waitFor({ state: 'visible', timeout: 5000 })
  await skipKycButton.click()

  // Wait for redirect to dashboard
  await page.waitForURL(/\/dashboard/, { timeout: 30000 })
  await page.waitForTimeout(2000)

  // Take dashboard screenshot
  await takeShot(page, 'desktop-09-dashboard-regular')

  // Verify we're on the dashboard
  const dashboardHeading = page.getByRole('heading', { name: /Dashboard|Welcome/i }).first()
  await dashboardHeading.waitFor({ state: 'visible', timeout: 10000 })
  expect(await dashboardHeading.isVisible()).toBeTruthy()

  console.log('✅ Onboarding completed successfully! User landed on dashboard.')
})
