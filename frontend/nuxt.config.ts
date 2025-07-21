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

  // Client Information
  runtimeConfig: {
    public: {
      websiteName: 'Commernity',
      companyName: 'Commernity Inc.',
      supportEmail: 'support@commernity.com',
      phoneNumber: '+91 98765 43210',
      address: 'Commernity Inc., 7th Floor, Eco Space Business Park, New Town, Action Area II, Rajarhat, Kolkata, West Bengal 700156, India'
    }
  }



})