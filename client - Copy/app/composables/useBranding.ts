/**
 * Branding Composable
 * Provides centralized access to app branding configuration
 * Allows easy white-labeling by changing environment variables
 */
export const useBranding = () => {
  const config = useRuntimeConfig()

  // App Identity
  const appName = computed(() => config.public.appName)
  const appShortName = computed(() => config.public.appShortName)
  const companyName = computed(() => config.public.companyName)
  const companyLegalName = computed(() => config.public.companyLegalName)
  const tagline = computed(() => config.public.tagline)

  // Contact Information
  const supportEmail = computed(() => config.public.supportEmail)
  const supportPhone = computed(() => config.public.supportPhone)
  const companyAddress = computed(() => config.public.companyAddress)

  // Currency
  const currencyCode = computed(() => config.public.currencyCode || 'INR')
  const currencySymbol = computed(() => config.public.currencySymbol || '₹')

  // Theme Colors (for dynamic styling)
  const primaryColor = computed(() => config.public.primaryColor)
  const secondaryColor = computed(() => config.public.secondaryColor)

  // Feature Flags
  const enablePwa = computed(() => config.public.enablePwa)
  const enableDarkMode = computed(() => config.public.enableDarkMode)
  const enableAds = computed(() => config.public.enableAds)

  // Social Links
  const socialLinks = computed(() => ({
    facebook: config.public.socialFacebook || null,
    twitter: config.public.socialTwitter || null,
    instagram: config.public.socialInstagram || null,
    linkedin: config.public.socialLinkedin || null,
    youtube: config.public.socialYoutube || null
  }))

  const hasSocialLinks = computed(() => {
    return Object.values(socialLinks.value).some(link => !!link)
  })

  // API Base URL
  const apiBase = computed(() => config.public.apiBase)

  // Copyright Text
  const copyrightText = computed(() => {
    const year = new Date().getFullYear()
    return `${year} ${companyName.value}. All rights reserved.`
  })

  // Dynamic CSS Variables (for runtime theming)
  const cssVariables = computed(() => ({
    '--brand-primary': primaryColor.value,
    '--brand-secondary': secondaryColor.value
  }))

  // Greeting based on time of day
  const getGreeting = (name?: string): string => {
    const hour = new Date().getHours()
    let greeting = 'Hello'

    if (hour >= 5 && hour < 12) {
      greeting = 'Good morning'
    } else if (hour >= 12 && hour < 17) {
      greeting = 'Good afternoon'
    } else if (hour >= 17 && hour < 21) {
      greeting = 'Good evening'
    } else {
      greeting = 'Good night'
    }

    return name ? `${greeting}, ${name}!` : `${greeting}!`
  }

  // Format currency (INR by default)
  const formatCurrency = (amount: number, currency = 'INR'): string => {
    return new Intl.NumberFormat('en-IN', {
      style: 'currency',
      currency,
      minimumFractionDigits: 0,
      maximumFractionDigits: 2
    }).format(amount)
  }

  // Format number with Indian notation (lakhs, crores)
  const formatNumber = (num: number): string => {
    return new Intl.NumberFormat('en-IN').format(num)
  }

  // Format compact number (1K, 1L, 1Cr)
  const formatCompactNumber = (num: number): string => {
    if (num >= 10000000) {
      return `${(num / 10000000).toFixed(1)}Cr`
    }
    if (num >= 100000) {
      return `${(num / 100000).toFixed(1)}L`
    }
    if (num >= 1000) {
      return `${(num / 1000).toFixed(1)}K`
    }
    return num.toString()
  }

  // Format date
  const formatDate = (date: string | Date, format: 'short' | 'medium' | 'long' = 'medium'): string => {
    const d = typeof date === 'string' ? new Date(date) : date

    const options: Intl.DateTimeFormatOptions = {
      short: { day: 'numeric', month: 'short' },
      medium: { day: 'numeric', month: 'short', year: 'numeric' },
      long: { day: 'numeric', month: 'long', year: 'numeric', weekday: 'long' }
    }[format]

    return d.toLocaleDateString('en-IN', options)
  }

  // Format relative time (e.g., "2 hours ago")
  const formatRelativeTime = (date: string | Date): string => {
    const d = typeof date === 'string' ? new Date(date) : date
    const now = new Date()
    const diff = now.getTime() - d.getTime()

    const minutes = Math.floor(diff / 60000)
    const hours = Math.floor(diff / 3600000)
    const days = Math.floor(diff / 86400000)

    if (minutes < 1) return 'Just now'
    if (minutes < 60) return `${minutes}m ago`
    if (hours < 24) return `${hours}h ago`
    if (days < 7) return `${days}d ago`
    return formatDate(d, 'short')
  }

  return {
    // Identity
    appName,
    appShortName,
    companyName,
    companyLegalName,
    tagline,

    // Contact
    supportEmail,
    supportPhone,
    companyAddress,

    // Currency
    currencyCode,
    currencySymbol,

    // Theme
    primaryColor,
    secondaryColor,
    cssVariables,

    // Features
    enablePwa,
    enableDarkMode,
    enableAds,

    // Social
    socialLinks,
    hasSocialLinks,

    // API
    apiBase,

    // Computed
    copyrightText,

    // Helpers
    getGreeting,
    formatCurrency,
    formatNumber,
    formatCompactNumber,
    formatDate,
    formatRelativeTime
  }
}
