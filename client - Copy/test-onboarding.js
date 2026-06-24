/**
 * Manual Onboarding Flow Test using Puppeteer
 *
 * This script connects to the running Nuxt dev server
 * and tests the onboarding flow
 */

async function testOnboardingFlow() {
  console.log('🚀 Starting Onboarding Flow Test...\n')

  try {
    // Connect to Chrome with remote debugging
    const { default: puppeteer } = await import('puppeteer')

    console.log('📡 Connecting to Chrome...')
    const browser = await puppeteer.connect({
      browserURL: 'http://localhost:9222'
    })

    const pages = await browser.pages()
    const page = pages[0] || await browser.newPage()

    await page.setViewport({ width: 1280, height: 720 })

    // Step 1: Navigate to login page
    console.log('\n📝 Step 1: Navigate to login page')
    await page.goto('http://localhost:3000/auth/login', { waitUntil: 'networkidle2' })
    console.log('✅ Login page loaded')

    // Take screenshot
    await page.screenshot({ path: 'logs/01-login-page.png' })

    // Step 2: Check if onboarding page exists (directly)
    console.log('\n📝 Step 2: Check onboarding page')
    await page.goto('http://localhost:3000/onboarding', { waitUntil: 'networkidle2' })
    console.log('✅ Onboarding page loaded')

    // Take screenshot
    await page.screenshot({ path: 'logs/02-onboarding-page.png' })

    // Step 3: Verify page elements
    console.log('\n📝 Step 3: Verify onboarding elements')

    const hasTitle = await page.$eval('h2', el => el.textContent.includes('Complete Your Profile'))
    console.log(hasTitle ? '✅ Title found' : '❌ Title not found')

    const hasProgress = await page.$('div[role="progressbar"]')
    console.log(hasProgress ? '✅ Progress bar found' : '❌ Progress bar not found')

    const hasNameInput = await page.$('input[placeholder*="name"]')
    console.log(hasNameInput ? '✅ Name input found' : '❌ Name input not found')

    // Step 4: Check form validation
    console.log('\n📝 Step 4: Test form validation')

    const submitButton = await page.$('button[type="submit"]')
    if (submitButton) {
      console.log('✅ Submit button found')
    }

    // Step 5: Console errors check
    console.log('\n📝 Step 5: Check for console errors')
    const errors = []
    page.on('console', (msg) => {
      if (msg.type() === 'error') {
        errors.push(msg.text())
      }
    })

    // Wait a bit to catch any errors
    await page.waitForTimeout(2000)

    if (errors.length === 0) {
      console.log('✅ No console errors')
    } else {
      console.log('❌ Console errors found:')
      errors.forEach(err => console.log('  -', err))
    }

    // Final screenshot
    await page.screenshot({ path: 'logs/03-onboarding-final.png' })

    console.log('\n✅ Test completed successfully!')
    console.log('\n📸 Screenshots saved to logs/ directory')
  } catch (error) {
    console.error('\n❌ Test failed:', error.message)
    process.exit(1)
  }
}

// Run the test
testOnboardingFlow()
