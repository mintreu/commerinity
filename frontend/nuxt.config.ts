// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
    compatibilityDate: '2024-04-03',
    devtools: { enabled: process.env.NODE_ENV === 'development' },
    ssr: false,

    css: ['~/assets/css/main.css'],

    vite: {
        optimizeDeps: {
            include: ['lodash-es']
        }
    },
    // ✅ PostCSS Configuration
    postcss: {
        plugins: {
            tailwindcss: {},
            autoprefixer: {},

        },
    },

    // ✅ Removed @nuxt/image module
    modules: ['@nuxt/icon', '@qirolab/nuxt-sanctum-authentication', '@nuxtjs/google-fonts', '@vite-pwa/nuxt', 'nuxt-echarts', '@nuxtjs/robots','@nuxtjs/sitemap'],

    runtimeConfig: {
        // Private keys (only available on server-side)
        //sentryAuthToken: process.env.SENTRY_AUTH_TOKEN,

        // Public keys (exposed to client-side)
        public: {
            apiBase: process.env.NUXT_PUBLIC_API_BASE || 'https://panel.vvindia.in/api',
            webBase: process.env.NUXT_PUBLIC_WEB_BASE || 'https://panel.vvindia.in',
            siteUrl: process.env.NUXT_PUBLIC_SITE_URL || 'https://vvindia.com',
            nodeEnv: process.env.NODE_ENV || 'production',
            vapidPublicKey: process.env.NUXT_PUBLIC_VAPID_PUBLIC_KEY,
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
                // { name: 'viewport', content: 'width=device-width, initial-scale=1' },
                { charset: 'utf-8' },
                { name: 'format-detection', content: 'telephone=no' },
                { name: 'theme-color', content: '#ffffff' },
                { name: 'mobile-web-app-capable', content: 'yes' },
                { name: 'apple-mobile-web-app-status-bar-style', content: 'default' },
                { name: 'viewport', content: 'width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes' },
            ],
            link: [
                { rel: 'icon', type: 'image/png', href: '/logo.png' },
                { rel: 'apple-touch-icon', href: '/apple-touch-icon.png' },
                { rel: 'canonical', href: process.env.NUXT_PUBLIC_SITE_URL || 'https://vvindia.com' },
                // Preconnect to API
                { rel: 'preconnect', href: 'http://localhost:8000' },
                { rel: 'dns-prefetch', href: 'http://localhost:8000' }
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

    sourcemap: {
        server: false,
        client: process.env.NODE_ENV === 'development' ? true : 'hidden'
    },

    // ✅ Sitemap Configuration



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
    },






    // ✅ Site Configuration (for canonical URLs)
    site: {
        url: process.env.NUXT_PUBLIC_SITE_URL || 'https://vvindia.com',
        name: process.env.NUXT_PUBLIC_APP_NAME || 'Vriddhi Vikash',
        description: 'VVIndia is a unified platform for shopping, blogging, and more.',
        defaultLocale: 'en-IN',
    },

    // ✅ Robots Configuration
    robots: {
        disallow: [
            '/dashboard',
            '/dashboard/**',
            '/admin',
            '/admin/**',
            '/auth/**',
            '/backups/**',
            '/sample/**',
            '/test',
            '/cart', // Usually no-index for cart
            '/*?*', // Exclude all query parameters
        ],
        allow: [
            '/auth/login', // Allow only login if you want it indexed
            '/auth/register',
        ],
    },

    // ✅ Production-Ready Sitemap Configuration
    sitemap: {
        // Hostname (will use canonical URLs in production)
        //hostname: process.env.NUXT_PUBLIC_SITE_URL || 'https://vvindia.com',
        xsl: false,
        // Disable XSL stylesheet tips in production
        xslTips: process.env.NODE_ENV === 'development',

        // Customize columns shown in sitemap UI
        xslColumns: [
            { label: 'URL', width: '50%' },
            { label: 'Last Modified', select: 'sitemap:lastmod', width: '25%' },
            { label: 'Priority', select: 'sitemap:priority', width: '12.5%' },
            { label: 'Change Freq', select: 'sitemap:changefreq', width: '12.5%' },
        ],

        // Exclude routes from sitemap
        exclude: [
            '/dashboard',
            '/dashboard/**',
            '/admin/**',
            '/auth/**', // All auth routes
            '/backups/**', // All backup routes
            '/sample/**', // All sample routes
            '/test', // Test page
            '/cart', // Shopping cart
            '/*?*', // All routes with query parameters
        ],

        // Set default values for all URLs
        defaults: {
            changefreq: 'daily',
            priority: 0.5,
            lastmod: new Date().toISOString(),
        },

        // Custom URL configuration with priorities
        urls: async () => {
            return [
                // High priority pages
                {
                    loc: '/',
                    priority: 1.0,
                    changefreq: 'daily',
                    lastmod: new Date().toISOString(),
                },
                {
                    loc: '/shop',
                    priority: 0.9,
                    changefreq: 'hourly',
                    lastmod: new Date().toISOString(),
                },
                {
                    loc: '/categories',
                    priority: 0.9,
                    changefreq: 'daily',
                    lastmod: new Date().toISOString(),
                },
                {
                    loc: '/store',
                    priority: 0.9,
                    changefreq: 'hourly',
                    lastmod: new Date().toISOString(),
                },
                {
                    loc: '/blog',
                    priority: 0.8,
                    changefreq: 'daily',
                    lastmod: new Date().toISOString(),
                },

                // Medium priority pages
                {
                    loc: '/about',
                    priority: 0.7,
                    changefreq: 'monthly',
                    lastmod: new Date().toISOString(),
                },
                {
                    loc: '/contact',
                    priority: 0.7,
                    changefreq: 'monthly',
                    lastmod: new Date().toISOString(),
                },
                {
                    loc: '/career',
                    priority: 0.6,
                    changefreq: 'weekly',
                    lastmod: new Date().toISOString(),
                },

                // Lower priority pages
                {
                    loc: '/help',
                    priority: 0.5,
                    changefreq: 'monthly',
                    lastmod: new Date().toISOString(),
                },
                {
                    loc: '/privacy',
                    priority: 0.4,
                    changefreq: 'yearly',
                    lastmod: new Date().toISOString(),
                },
                {
                    loc: '/terms',
                    priority: 0.4,
                    changefreq: 'yearly',
                    lastmod: new Date().toISOString(),
                },
                {
                    loc: '/return-refund',
                    priority: 0.4,
                    changefreq: 'yearly',
                    lastmod: new Date().toISOString(),
                },
                {
                    loc: '/shipping',
                    priority: 0.4,
                    changefreq: 'yearly',
                    lastmod: new Date().toISOString(),
                },
            ]
        },

        // Enable gzip compression
        //gzip: true,

        // Exclude routes based on robots rules
        discoverImages: true,

        // Better caching
        cacheMaxAgeSeconds: 600, // 10 minutes
    },

    // Route rules for additional control
    routeRules: {
        // Exclude from search engines completely
        '/dashboard/**': { robots: false, index: false },
        '/admin/**': { robots: false, index: false },
        '/auth/**': { robots: false, index: false },
        '/backups/**': { robots: false, index: false },
        '/sample/**': { robots: false, index: false },
        '/test': { robots: false, index: false },
        '/cart': { robots: false, index: false },

        // Allow but with specific rules
        '/auth/login': { robots: true, index: true },
        '/auth/register': { robots: true, index: true },

        // Prerender important pages
        '/': { prerender: true },
        '/shop': { prerender: true },
        '/categories': { prerender: true },
        '/blog': { prerender: true },
        '/about': { prerender: true },
        '/contact': { prerender: true },
    },







})