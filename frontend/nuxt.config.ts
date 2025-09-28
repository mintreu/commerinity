// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
    compatibilityDate: '2024-04-03',
    devtools: { enabled: process.env.NODE_ENV === 'development' },
    ssr: false,

    css: ['~/assets/css/main.css'],

    // ✅ Removed @nuxt/image module
    modules: [
        '@nuxt/icon',
        '@qirolab/nuxt-sanctum-authentication',
        '@nuxtjs/google-fonts',
        '@sentry/nuxt/module',
        '@vite-pwa/nuxt',
        'nuxt-echarts'
    ],

    runtimeConfig: {
        // Private keys (only available on server-side)
        sentryAuthToken: process.env.SENTRY_AUTH_TOKEN,

        // Public keys (exposed to client-side)
        public: {
            apiBase: process.env.NUXT_PUBLIC_API_BASE || 'https://panel.vvindia.in/api',
            webBase: process.env.NUXT_PUBLIC_WEB_BASE || 'https://panel.vvindia.in',
            siteUrl: process.env.NUXT_PUBLIC_SITE_URL || 'https://vvindia.com',
            nodeEnv: process.env.NODE_ENV || 'production',
            sentry: {
                dsn: process.env.SENTRY_DSN_PUBLIC || '',
            },
            registrationMode: 'mobile',
            websiteName: process.env.NUXT_PUBLIC_APP_NAME || 'Vriddhi Vikash',
            companyName: process.env.NUXT_PUBLIC_COMPANY_NAME || 'Vriddhi Vikash',
            supportEmail: process.env.NUXT_PUBLIC_COMPANY_SUPPORT_MAIL || 'support@vvindia.com',
            phoneNumber: process.env.NUXT_PUBLIC_COMPANY_SUPPORT_PHONE || '+91 98765 43210',
            address: process.env.NUXT_PUBLIC_COMPANY_ADDRESS || 'Vriddhi Vikash., 7th Floor, Eco Space Business Park, New Town, Action Area II, Rajarhat, Kolkata, West Bengal 700156, India'
        }
    },

    app: {
        head: {
            title: 'VVIndia - All in One Place',
            titleTemplate: '%s | VVIndia',
            meta: [
                { name: 'description', content: 'VVIndia is a unified platform for shopping, blogging, and more.' },
                { name: 'viewport', content: 'width=device-width, initial-scale=1' },
                { charset: 'utf-8' },
                { name: 'format-detection', content: 'telephone=no' },
                { name: 'theme-color', content: '#ffffff' },
                { name: 'apple-mobile-web-app-capable', content: 'yes' },
                { name: 'apple-mobile-web-app-status-bar-style', content: 'default' }
            ],
            link: [
                { rel: 'icon', type: 'image/png', href: '/logo.png' },
                { rel: 'apple-touch-icon', href: '/apple-touch-icon.png' },
                { rel: 'canonical', href: process.env.NUXT_PUBLIC_SITE_URL || 'https://vvindia.com' }
            ]
        }
    },

    // ✅ Laravel Sanctum Configuration
    laravelSanctum: {
        apiUrl: process.env.NUXT_PUBLIC_WEB_BASE || 'https://panel.vvindia.in',
        authMode: 'cookie',
        userResponseWrapperKey: 'data',
        sanctumEndpoints: {
            csrf: '/sanctum/csrf-cookie',
            login: '/api/login',
            logout: '/api/logout',
        },
    },

    // ✅ Auto-imports (minimal needed for toast system)
    imports: {
        dirs: ['composables']
    },

    // ✅ Optimized Google Fonts
    googleFonts: {
        families: {
            Roboto: [400, 700]
        },
        download: true,
        inject: true,
        display: 'swap',
        preconnect: true,
        preload: true
    },

    // ✅ Sentry Configuration
    sentry: {
        sourceMapsUploadOptions: {
            org: process.env.SENTRY_ORG || 'your-org-slug',
            project: process.env.SENTRY_PROJECT || 'your-project-slug',
            authToken: process.env.SENTRY_AUTH_TOKEN,
        },
    },
    sourcemap: {
        server: false,
        client: process.env.NODE_ENV === 'development' ? true : 'hidden'
    },

    // ✅ PostCSS Configuration
    postcss: {
        plugins: {
            tailwindcss: {},
            autoprefixer: {},
        },
    },

    // ✅ Build Configuration
    build: {
        transpile: ['vue-echarts', 'echarts'],
        analyze: process.env.ANALYZE === 'true'
    },

    // ✅ Enhanced PWA Configuration
    pwa: {
        registerType: 'autoUpdate',
        workbox: {
            cleanupOutdatedCaches: true,
            navigateFallback: '/',
            globPatterns: ['**/*.{js,css,html,png,svg,ico,json,webp}'],
            runtimeCaching: [
                {
                    urlPattern: /^https:\/\/panel\.vvindia\.in\/api\/.*/,
                    handler: 'NetworkFirst',
                    options: {
                        cacheName: 'api-cache',
                        expiration: {
                            maxEntries: 100,
                            maxAgeSeconds: 60 * 60 * 24 // 1 day
                        }
                    }
                },
                {
                    urlPattern: /\.(png|jpg|jpeg|svg|webp|avif)$/,
                    handler: 'CacheFirst',
                    options: {
                        cacheName: 'images-cache',
                        expiration: {
                            maxEntries: 200,
                            maxAgeSeconds: 60 * 60 * 24 * 30 // 30 days
                        }
                    }
                }
            ]
        },
        manifest: {
            name: 'Vriddhi Vikash India',
            short_name: 'VVIndia',
            description: 'Official app for Vriddhi Vikash India - All in One Platform',
            theme_color: '#ffffff',
            background_color: '#ffffff',
            display: 'standalone',
            orientation: 'portrait-primary',
            start_url: '/',
            scope: '/',
            lang: 'en-IN',
            categories: ['shopping', 'business', 'productivity'],
            icons: [
                {
                    src: '/web-app-manifest-192x192.png',
                    sizes: '192x192',
                    type: 'image/png',
                    purpose: 'any maskable',
                },
                {
                    src: '/web-app-manifest-512x512.png',
                    sizes: '512x512',
                    type: 'image/png',
                    purpose: 'any maskable',
                },
            ],
        },
        client: {
            installPrompt: true,
            periodicSyncForUpdates: 20
        },
        devOptions: {
            enabled: false,
            type: 'module',
        },
    },

    // ✅ Performance Optimizations
    experimental: {
        payloadExtraction: false,
        viewTransition: true
    },

    // ✅ Nitro Configuration
    nitro: {
        compressPublicAssets: true,
        minify: true
    },

    // ✅ Vue Configuration
    vue: {
        propsDestructure: true
    }
})
