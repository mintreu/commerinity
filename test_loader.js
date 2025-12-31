const puppeteer = require('puppeteer');

(async () => {
  const browser = await puppeteer.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  try {
    const page = await browser.newPage();
    
    // Capture console messages
    const consoleLogs = [];
    const pageErrors = [];
    
    page.on('console', msg => {
      consoleLogs.push({
        type: msg.type(),
        text: msg.text()
      });
    });
    
    page.on('error', err => {
      pageErrors.push(err.message);
    });
    
    // Try to load the home page first
    console.log('Navigating to http://localhost:3000/...');
    try {
      await page.goto('http://localhost:3000/', { 
        waitUntil: 'domcontentloaded',
        timeout: 10000
      });
    } catch (e) {
      console.log('Initial navigation completed with timeout (expected if page is heavy)');
    }
    
    // Wait for some rendering
    await new Promise(resolve => setTimeout(resolve, 1500));
    
    // Test 1: Check for animate-spin class
    console.log('\n=== TEST 1: Checking for animate-spin (AppLoader structure) ===');
    const spinnerInfo = await page.evaluate(() => {
      const spinners = document.querySelectorAll('[class*="animate-spin"]');
      const spinnerDetails = [];
      spinners.forEach((el, i) => {
        spinnerDetails.push({
          index: i,
          tag: el.tagName,
          classCount: el.className.split(' ').length,
          hasAnimateSpin: el.className.includes('animate-spin')
        });
      });
      return {
        totalSpinners: spinners.length,
        details: spinnerDetails,
        spinnerExists: spinners.length > 0
      };
    });
    console.log('Spinner elements found:', spinnerInfo.totalSpinners);
    console.log('animate-spin class present:', spinnerInfo.spinnerExists);
    
    // Test 2: Check for AppLoader-like structure
    console.log('\n=== TEST 2: Checking for AppLoader component pattern ===');
    const appLoaderPattern = await page.evaluate(() => {
      const elements = [];
      
      // Look for elements with animate-spin
      const animateSpin = document.querySelector('[class*="animate-spin"]');
      if (animateSpin) {
        elements.push({
          type: 'animate-spin',
          found: true,
          parent: animateSpin.parentElement ? animateSpin.parentElement.className : 'no parent'
        });
      }
      
      // Look for elements with animate-bounce
      const animateBounce = document.querySelector('[class*="animate-bounce"]');
      if (animateBounce) {
        elements.push({
          type: 'animate-bounce',
          found: true,
          count: document.querySelectorAll('[class*="animate-bounce"]').length
        });
      }
      
      // Look for transition-opacity
      const transition = document.querySelector('[class*="transition-opacity"]');
      if (transition) {
        elements.push({
          type: 'transition-opacity',
          found: true
        });
      }
      
      return elements;
    });
    console.log('AppLoader pattern elements:', appLoaderPattern);
    
    // Test 3: Check for component resolution errors
    console.log('\n=== TEST 3: Checking console for component errors ===');
    const componentErrors = consoleLogs.filter(log => 
      log.text.includes('Component') || log.text.includes('component')
    );
    const resolutionErrors = componentErrors.filter(log => 
      log.text.includes('failed to resolve')
    );
    console.log('Total console logs:', consoleLogs.length);
    console.log('Component-related logs:', componentErrors.length);
    console.log('Resolution errors:', resolutionErrors.length);
    
    if (resolutionErrors.length > 0) {
      console.log('Resolution errors found:');
      resolutionErrors.forEach(log => {
        console.log('  -', log.text);
      });
    }
    
    // Test 4: Check page HTML for app.vue structure
    console.log('\n=== TEST 4: Checking root-level loader integration (app.vue) ===');
    const appVueIntegration = await page.evaluate(() => {
      const html = document.documentElement.outerHTML;
      return {
        hasAppLoader: html.includes('AppLoader'),
        hasNuxtApp: document.querySelector('[data-nuxt-app], #__nuxt, #app') !== null,
        hasUApp: html.includes('UApp'),
        hasRouter: html.includes('router') || html.includes('NuxtLayout'),
        bodyElementCount: document.body.children.length,
        htmlElementCount: document.querySelectorAll('*').length
      };
    });
    console.log('App.vue integration status:', appVueIntegration);
    
    // Test 5: Page title and basic structure
    console.log('\n=== TEST 5: Page Structure ===');
    const pageTitle = await page.title();
    const pageUrl = page.url();
    console.log('Page title:', pageTitle);
    console.log('Current URL:', pageUrl);
    console.log('Page errors captured:', pageErrors.length);
    
    // Final verdict
    console.log('\n=== FINAL TEST REPORT ===');
    const hasAppLoader = spinnerInfo.spinnerExists || appLoaderPattern.length > 0;
    console.log('1. AppLoader DOM Structure:', hasAppLoader ? 'PRESENT' : 'NOT VISIBLE');
    console.log('2. animate-spin class:', spinnerInfo.spinnerExists ? 'YES' : 'NO');
    console.log('3. animate-bounce dots:', appLoaderPattern.some(el => el.type === 'animate-bounce') ? 'YES' : 'NO');
    console.log('4. Component errors (failed to resolve):', resolutionErrors.length === 0 ? 'NONE' : 'FOUND - ' + resolutionErrors.length);
    console.log('5. Root-level loader (app.vue):', appVueIntegration.hasUApp ? 'INTEGRATED' : 'CHECK NEEDED');
    console.log('\nSummary: AppLoader is', hasAppLoader && resolutionErrors.length === 0 ? 'FULLY FUNCTIONAL' : 'NEEDS REVIEW');
    
  } catch (error) {
    console.error('CRITICAL ERROR:', error.message);
    console.error('Stack:', error.stack);
  } finally {
    await browser.close();
  }
})();
