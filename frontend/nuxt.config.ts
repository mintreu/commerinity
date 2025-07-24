// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-05-15',
  devtools: { enabled: true },
  ssr: false,
  css: ['~/assets/css/main.css'],
  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },

  modules: ['@nuxt/icon', '@qirolab/nuxt-sanctum-authentication'],
  laravelSanctum: {
    // Replace with your Laravel API URL
    apiUrl: 'http://localhost:8000',
    authMode: 'cookie',
    sanctumEndpoints: {
      csrf:"/sanctum/csrf-cookie",
      login:"/api/login",
      logout:"/api/logout",
    },

  },


  // App Information
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


  // Client Information
  runtimeConfig: {
    public: {
      apiBase: 'http://localhost:8000/api',
      registrationMode: 'mobile',  // or 'email'
      websiteName: 'Commernity',
      companyName: 'Commernity Inc.',
      supportEmail: 'support@commernity.com',
      phoneNumber: '+91 98765 43210',
      address: 'Commernity Inc., 7th Floor, Eco Space Business Park, New Town, Action Area II, Rajarhat, Kolkata, West Bengal 700156, India'
    }
  }



})