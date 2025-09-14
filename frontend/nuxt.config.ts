// https://nuxt.com/docs/api/configuration/nuxt-config

export default defineNuxtConfig({
  compatibilityDate: '2025-05-15',
  devtools: { enabled: false },
  ssr: false,
  css: ['~/assets/css/main.css'],
  modules: [
    '@nuxt/icon',
    '@qirolab/nuxt-sanctum-authentication',
    '@nuxtjs/google-fonts',
    '@nuxt/image',
    "@sentry/nuxt/module",
    '@vite-pwa/nuxt',
    'nuxt-toast',
    'nuxt-echarts'
  ],
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'https://api.production.in',
      webBase: process.env.NUXT_PUBLIC_WEB_BASE || 'https://api.production.in',
      nodeEnv: process.env.NUXT_PUBLIC_NODE_ENV || 'production',
      sentry: {
        dsn: process.env.SENTRY_DSN_PUBLIC || '', // load from env for security
      },
      registrationMode: 'mobile',  // or 'email'
      websiteName: 'Commernity',
      companyName: 'Commernity Inc.',
      supportEmail: 'support@commernity.com',
      phoneNumber: '+91 98765 43210',
      address: 'Commernity Inc., 7th Floor, Eco Space Business Park, New Town, Action Area II, Rajarhat, Kolkata, West Bengal 700156, India'
    }
  },
  app: {
    head: {
      title: 'Commernity - All in One Place',
      meta: [
        { name: 'description', content: 'Commernity is a unified platform for shopping, blogging, and more.' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { charset: 'utf-8' },
      ],
      link: [
        { rel: 'icon', type: 'image/png', href: '/logo.png' }, // ✅ Replace with your favicon file
      ]
    }
  },
  laravelSanctum: {
    // Replace with your Laravel API URL
    apiUrl: 'http://localhost:8000',
    authMode: 'cookie',
    userResponseWrapperKey: 'data',
    sanctumEndpoints: {
      csrf:"/sanctum/csrf-cookie",
      login:"/api/login",
      logout:"/api/logout",
    },

  },
  // googleFonts: {
  //   families: {
  //     Roboto: [400, 500, 700], // Use weights you actually need
  //   },
  //   base64: true,
  //   display: 'swap',
  //   preconnect: true,
  //   preload: true,
  //   useStylesheet: true,
  //   download: true,       // You can set to true to self-host
  //   inject: true,
  // },

  googleFonts: {
    families: { Roboto: [400, 700] }, // drop 500 if not needed
    download: true,
    inject: true,
    display: 'swap'
  },



  sentry: {
    sourceMapsUploadOptions: {
      org: 'your-org-slug',
      project: 'your-project-slug',
      authToken: process.env.SENTRY_AUTH_TOKEN,  // for source maps upload at build time
    },
  },
  sourcemap: { client: 'hidden' }, // for source maps generation on client side
  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },

  image: {
    domains: ['api.production.in', 'localhost'], // your CDN/API
    format: ['webp', 'avif','jpg','jpeg','png'],
  },

  build: {
    transpile: [], // keep empty unless needed
    analyze: true, // run `npm run build` → check bundle breakdown
  },

  pwa: {
    registerType: 'autoUpdate',
    manifest: {
      name: 'Vridhi Vikash India',
      short_name: 'VVIN',
      description: 'Official app for Vridhi Vikash India.',
      theme_color: '#ffffff',
      background_color: '#ffffff',
      display: 'standalone',
      orientation: 'portrait',
      start_url: '/',
      icons: [
        {
          src: '/web-app-manifest-192x192.png',
          sizes: '192x192',
          type: 'image/png',
          purpose: 'maskable',
        },
        {
          src: '/web-app-manifest-512x512.png',
          sizes: '512x512',
          type: 'image/png',
          purpose: 'maskable',
        },
      ],
    },
    workbox: {
      cleanupOutdatedCaches: true,
      navigateFallback: '/',
      globPatterns: ['**/*.{js,css,html,png,svg,ico,json}'],
    },
    client: {
      installPrompt: true,
    },
    devOptions: {
      enabled: false,
      type: 'module',
    },

  }




})