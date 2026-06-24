const { chromium } = require('playwright')

async function testFilamentContent() {
  console.log('Launching browser...')
  const browser = await chromium.launch({
    headless: true
  })

  try {
    const context = await browser.newContext({
      viewport: { width: 1280, height: 900 }
    })
    const page = await context.newPage()

    console.log('Navigating to product page...')
    await page.goto('http://localhost:3000/shop/product/black-pepper', {
      waitUntil: 'networkidle',
      timeout: 30000
    })

    // Wait for content to load
    await page.waitForSelector('.filament-content', { timeout: 10000 })

    // Scroll to description section
    await page.evaluate(() => {
      const descSection = document.querySelector('.filament-content')
      if (descSection) {
        descSection.scrollIntoView({ behavior: 'instant', block: 'start' })
      }
    })
    await page.waitForTimeout(500)

    // Screenshot light mode
    console.log('Taking light mode screenshot...')
    await page.screenshot({
      path: 'screenshot-light.png',
      fullPage: true
    })

    // Switch to dark mode
    console.log('Switching to dark mode...')
    await page.evaluate(() => {
      document.documentElement.classList.add('dark')
      document.body.classList.add('dark')
    })

    await page.waitForTimeout(500)

    // Screenshot dark mode
    console.log('Taking dark mode screenshot...')
    await page.screenshot({
      path: 'screenshot-dark.png',
      fullPage: true
    })

    // Analyze the content
    const contentAnalysis = await page.evaluate(() => {
      const content = document.querySelector('.filament-content')
      if (!content) return { error: 'FilamentContent not found' }

      const html = content.innerHTML
      const hasHeadings = content.querySelectorAll('h1, h2, h3, h4, h5, h6').length
      const hasParagraphs = content.querySelectorAll('p').length
      const hasLists = content.querySelectorAll('ul, ol').length
      const hasListItems = content.querySelectorAll('li').length
      const hasImages = content.querySelectorAll('img').length
      const hasTables = content.querySelectorAll('table').length
      const hasBold = content.querySelectorAll('strong, b').length
      const hasItalic = content.querySelectorAll('em, i').length

      return {
        htmlPreview: html.substring(0, 800),
        stats: {
          headings: hasHeadings,
          paragraphs: hasParagraphs,
          lists: hasLists,
          listItems: hasListItems,
          images: hasImages,
          tables: hasTables,
          bold: hasBold,
          italic: hasItalic
        }
      }
    })

    console.log('\n--- Content Analysis ---')
    console.log('HTML Preview:', contentAnalysis.htmlPreview || contentAnalysis.error)
    console.log('\nElement Stats:')
    console.log(JSON.stringify(contentAnalysis.stats, null, 2))

    // Check styling in light mode
    await page.evaluate(() => {
      document.documentElement.classList.remove('dark')
      document.body.classList.remove('dark')
    })
    await page.waitForTimeout(200)

    const lightStyles = await page.evaluate(() => {
      const content = document.querySelector('.filament-content')
      if (!content) return null

      const computedStyle = window.getComputedStyle(content)
      const h2 = content.querySelector('h2')
      const ul = content.querySelector('ul')
      const strong = content.querySelector('strong, b')

      return {
        baseColor: computedStyle.color,
        fontSize: computedStyle.fontSize,
        h2Color: h2 ? window.getComputedStyle(h2).color : 'N/A',
        listPadding: ul ? window.getComputedStyle(ul).paddingInlineStart : 'N/A',
        strongColor: strong ? window.getComputedStyle(strong).color : 'N/A'
      }
    })

    console.log('\n--- Light Mode Styles ---')
    console.log(JSON.stringify(lightStyles, null, 2))

    // Check styling in dark mode
    await page.evaluate(() => {
      document.documentElement.classList.add('dark')
      document.body.classList.add('dark')
    })
    await page.waitForTimeout(200)

    const darkStyles = await page.evaluate(() => {
      const content = document.querySelector('.filament-content')
      if (!content) return null

      const computedStyle = window.getComputedStyle(content)
      const h2 = content.querySelector('h2')
      const strong = content.querySelector('strong, b')

      return {
        baseColor: computedStyle.color,
        h2Color: h2 ? window.getComputedStyle(h2).color : 'N/A',
        strongColor: strong ? window.getComputedStyle(strong).color : 'N/A'
      }
    })

    console.log('\n--- Dark Mode Styles ---')
    console.log(JSON.stringify(darkStyles, null, 2))

    console.log('\n=== Screenshots saved ===')
    console.log('- screenshot-light.png')
    console.log('- screenshot-dark.png')
    console.log('\nOpen these files to verify the rendering.')
  } catch (error) {
    console.error('Error:', error.message)
  } finally {
    await browser.close()
    console.log('\nBrowser closed.')
  }
}

testFilamentContent()
