const lighthouse = require('lighthouse');
const chromeLauncher = require('chrome-launcher');

async function runLighthouse(url, formFactor) {
  const chrome = await chromeLauncher.launch({chromeFlags: ['--headless']});
  const options = {
    logLevel: 'error',
    output: 'json',
    onlyCategories: ['performance', 'accessibility', 'best-practices', 'seo'],
    port: chrome.port,
    formFactor: formFactor,
    screenEmulation: formFactor === 'mobile' ? {
      mobile: true,
      width: 375,
      height: 667,
      deviceScaleFactor: 2
    } : {
      mobile: false,
      width: 1350,
      height: 940,
      deviceScaleFactor: 1
    }
  };

  const runnerResult = await lighthouse(url, options);
  await chrome.kill();

  const scores = runnerResult.lhr.categories;
  return {
    performance: Math.round(scores.performance.score * 100),
    accessibility: Math.round(scores.accessibility.score * 100),
    bestPractices: Math.round(scores['best-practices'].score * 100),
    seo: Math.round(scores.seo.score * 100)
  };
}

(async () => {
  console.log('Testing: http://localhost:3000');
  const mobile = await runLighthouse('http://localhost:3000', 'mobile');
  console.log('\nMOBILE SCORES:');
  console.log(`Performance: ${mobile.performance}`);
  console.log(`Accessibility: ${mobile.accessibility}`);
  console.log(`Best Practices: ${mobile.bestPractices}`);
  console.log(`SEO: ${mobile.seo}`);
  
  const desktop = await runLighthouse('http://localhost:3000', 'desktop');
  console.log('\nDESKTOP SCORES:');
  console.log(`Performance: ${desktop.performance}`);
  console.log(`Accessibility: ${desktop.accessibility}`);
  console.log(`Best Practices: ${desktop.bestPractices}`);
  console.log(`SEO: ${desktop.seo}`);
})();
