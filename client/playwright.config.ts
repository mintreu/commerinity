import { defineConfig } from '@playwright/test'

export default defineConfig({
  testDir: './tests/playwright',
  timeout: 180_000,
  expect: {
    timeout: 15_000
  },
  use: {
    baseURL: 'http://localhost:3000',
    headless: false,
    viewport: { width: 1440, height: 900 },
    screenshot: 'on',
    trace: 'on'
  }
})
