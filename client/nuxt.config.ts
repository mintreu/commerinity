// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({

  modules: [
    '@nuxt/eslint',
    '@nuxt/fonts',
    '@nuxt/ui',
    '@qirolab/nuxt-sanctum-authentication'
  ],
  ssr: false,

  devtools: {
    enabled: true
  },

  // Nuxt Fonts Configuration
  fonts: {
    families: [
      {
        name: 'Plus Jakarta Sans',
        provider: 'google',
        weights: [300, 400, 500, 600, 700, 800]
      },
      {
        name: 'Inter',
        provider: 'google',
        weights: [300, 400, 500, 600, 700]
      }
    ],
    defaults: {
      weights: [400, 500, 600, 700],
      styles: ['normal']
    }
  },

  app: {
    head: {
      title: 'VVIndia - Affiliate & E-Commerce Platform',
      titleTemplate: '%s | VVIndia',
      meta: [
        { name: 'description', content: 'VVIndia - Premium Affiliate & E-Commerce Platform. Shop smart, earn more, grow your network.' },
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1, maximum-scale=5' },
        { name: 'theme-color', content: '#a855f7' },
        // PWA Meta Tags
        { name: 'mobile-web-app-capable', content: 'yes' },
        { name: 'apple-mobile-web-app-capable', content: 'yes' },
        { name: 'apple-mobile-web-app-status-bar-style', content: 'black-translucent' },
        { name: 'apple-mobile-web-app-title', content: 'CMP' },
        { name: 'application-name', content: 'Commerinity Pro' },
        { name: 'msapplication-TileColor', content: '#a855f7' },
        // Open Graph
        { property: 'og:type', content: 'website' },
        { property: 'og:site_name', content: 'VVIndia' }
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'icon', type: 'image/svg+xml', href: '/favicon.svg' },
        { rel: 'apple-touch-icon', sizes: '180x180', href: '/apple-touch-icon.png' },
        { rel: 'manifest', href: '/site.webmanifest' }
      ]
    }
  },

  css: ['~/assets/css/main.css'],

  runtimeConfig: {
    public: {
      // API Configuration
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'https://vvindia.in',

      // Branding (can be overridden via .env)
      appName: process.env.NUXT_PUBLIC_APP_NAME || 'VVIndia',
      appShortName: process.env.NUXT_PUBLIC_APP_SHORT_NAME || 'VVIN',
      companyName: process.env.NUXT_PUBLIC_COMPANY_NAME || 'VVIndia',
      companyLegalName: process.env.NUXT_PUBLIC_COMPANY_LEGAL_NAME || 'VVIndia ',
      tagline: process.env.NUXT_PUBLIC_TAGLINE || 'Shop Smart. Earn More. Grow Together.',

      // Contact Information
      supportEmail: process.env.NUXT_PUBLIC_SUPPORT_EMAIL || 'support@vvindia.in',
      supportPhone: process.env.NUXT_PUBLIC_SUPPORT_PHONE || '+91 98765 43210',
      companyAddress: process.env.NUXT_PUBLIC_COMPANY_ADDRESS || '123 Business Park, Tech Hub, City - 700001, India',

      // Currency
      currencyCode: process.env.NUXT_PUBLIC_CURRENCY_CODE || 'INR',
      currencySymbol: process.env.NUXT_PUBLIC_CURRENCY_SYMBOL || '₹',

      // Theme Colors (for dynamic branding)
      primaryColor: process.env.NUXT_PUBLIC_PRIMARY_COLOR || '#a855f7',
      secondaryColor: process.env.NUXT_PUBLIC_SECONDARY_COLOR || '#d946ef',

      // Feature Flags
      enablePwa: process.env.NUXT_PUBLIC_ENABLE_PWA !== 'false',
      enableDarkMode: process.env.NUXT_PUBLIC_ENABLE_DARK_MODE !== 'false',
      enableAds: process.env.NUXT_PUBLIC_ENABLE_ADS === 'true',

      // Auth Configuration
      // signupMode: 'mobile' | 'email' - default signup method
      signupMode: process.env.NUXT_PUBLIC_SIGNUP_MODE || 'mobile',

      // Social Links (optional)
      socialFacebook: process.env.NUXT_PUBLIC_SOCIAL_FACEBOOK || '',
      socialTwitter: process.env.NUXT_PUBLIC_SOCIAL_TWITTER || '',
      socialInstagram: process.env.NUXT_PUBLIC_SOCIAL_INSTAGRAM || '',
      socialLinkedin: process.env.NUXT_PUBLIC_SOCIAL_LINKEDIN || '',
      socialYoutube: process.env.NUXT_PUBLIC_SOCIAL_YOUTUBE || ''
    }
  },

  routeRules: {
    '/': { prerender: false }
  },

  compatibilityDate: '2025-01-15',

  eslint: {
    config: {
      stylistic: {
        commaDangle: 'never',
        braceStyle: '1tbs'
      }
    }
  },

  laravelSanctum: {
    apiUrl: process.env.NUXT_PUBLIC_API_BASE || 'https://vvindia.in',
    authMode: 'token',
    userResponseWrapperKey: 'data', // Laravel UserResource wraps response in { data: {...} }
    token: {
      storageKey: 'commerinity_auth_token',
      provider: 'cookie',
      responseKey: 'token'
    },
    sanctumEndpoints: {
      login: '/api/auth/login',
      logout: '/api/auth/logout',
      user: '/api/user'
    },
    redirect: {
      enableIntendedRedirect: true,
      loginPath: '/auth/login',
      guestOnlyRedirect: '/dashboard',
      redirectToAfterLogin: '/dashboard',
      redirectToAfterLogout: '/'
    },
    globalMiddleware: {
      enabled: false
    }
  }
})
