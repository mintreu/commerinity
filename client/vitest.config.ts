import { defineConfig } from 'vitest/config'

export default defineConfig({
  test: {
    // Use node environment for API tests (simpler, faster)
    environment: 'node',
    // Include test files
    include: ['tests/**/*.test.ts'],
    // Timeout for async operations
    testTimeout: 30000,
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      exclude: [
        'node_modules/**',
        '.nuxt/**',
        'dist/**',
        '**/*.config.*',
        '**/*.d.ts'
      ]
    }
  }
})
