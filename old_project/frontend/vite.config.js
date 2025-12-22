// vite.config.js
import { defineConfig } from 'vite'

export default defineConfig({
    optimizeDeps: {
        include: ['lodash-es']
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('lodash-es')) {
                        return 'vendor-lodash'
                    }
                }
            }
        }
    }
})
