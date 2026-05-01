// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({

  modules: [
    '@nuxt/eslint',
    '@nuxt/fonts',
    '@nuxt/ui',
    '@qirolab/nuxt-sanctum-authentication',
    '@nuxtjs/sitemap'
  ],

  ssr: false,

  devtools: {
    enabled: true
  },

  app: {
    head: {
      title: 'VRIDDHI VIKASH – Your Online Shopping Destination',
      titleTemplate: '%s | VRIDDHI VIKASH',
      meta: [
        { name: 'description', content: 'VRIDDHI VIKASH – Your trusted online marketplace for quality products, smart deals, and rewarding shopping experiences.' },
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1, maximum-scale=5' },
        { name: 'theme-color', content: '#a855f7' },
        // PWA Meta Tags
        { name: 'mobile-web-app-capable', content: 'yes' },
        { name: 'apple-mobile-web-app-capable', content: 'yes' },
        { name: 'apple-mobile-web-app-status-bar-style', content: 'black-translucent' },
        { name: 'apple-mobile-web-app-title', content: 'CMP' },
        { name: 'application-name', content: 'VRIDDHI VIKASH' },
        { name: 'msapplication-TileColor', content: '#a855f7' },
        // Open Graph
        { property: 'og:type', content: 'website' },
        { property: 'og:site_name', content: 'VRIDDHI VIKASH' }
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'apple-touch-icon', href: '/apple-touch-icon.png' },
        { rel: 'manifest', href: '/site.webmanifest' }
      ]
    }
  },

  css: ['~/assets/css/main.css'], site: {
    url: process.env.NUXT_PUBLIC_SITE_URL || 'https://www.vvindia.in'
  },

  runtimeConfig: {
    public: {
      // API Configuration
      // Development
      //  apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000',
      //  siteUrl: process.env.NUXT_PUBLIC_SITE_URL || 'http://localhost:3000',
      // Production
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'https://panel.vvindia.in',
      siteUrl: process.env.NUXT_PUBLIC_SITE_URL || 'https://www.vvindia.in',

      // Branding (can be overridden via .env)
      appName: process.env.NUXT_PUBLIC_APP_NAME || 'VRIDDHI VIKASH',
      appShortName: process.env.NUXT_PUBLIC_APP_SHORT_NAME || 'VVIN',
      companyName: process.env.NUXT_PUBLIC_COMPANY_NAME || 'VRIDDHI VIKASH',
      companyLegalName: process.env.NUXT_PUBLIC_COMPANY_LEGAL_NAME || 'VRIDDHI VIKASH',
      tagline: process.env.NUXT_PUBLIC_TAGLINE || 'India’s Smart Shopping Network.',

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
      // Theme switcher: "default" keeps current violet/fuchsia, "amber" enables amber/orange
      themeName: process.env.NUXT_PUBLIC_THEME || 'default',

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

  laravelSanctum: {
    // Development
     apiUrl: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000',
    // Production
    //apiUrl: process.env.NUXT_PUBLIC_API_BASE || 'https://panel.vvindia.in',
    authMode: 'token',
    userResponseWrapperKey: 'data', // Laravel UserResource wraps response in { data: {...} }
    token: {
      storageKey: 'commerinity_auth_token',
      provider: process.env.NUXT_PUBLIC_TOKEN_PROVIDER || 'localStorage',
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
  },

  sitemap: {
    xsl: false,
    exclude: [
      '/dashboard/**',
      '/auth/login',
      '/auth/forgot-password',
      '/auth/reset-password',
      '/blogs',
      '/news'
    ],
    sources: [
      '/api/__sitemap__/products'
    ]
  }
})
