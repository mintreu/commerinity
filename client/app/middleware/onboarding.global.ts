import type { User } from '~/types/user'

export default defineNuxtRouteMiddleware(async (to) => {
  // Skip check for public pages
  const publicPages = ['/', '/about', '/contact', '/privacy', '/terms']
  if (publicPages.includes(to.path)) {
    return
  }

  // Skip check for auth pages
  if (to.path.startsWith('/auth/')) {
    return
  }

  // Skip check if already on onboarding page
  if (to.path.startsWith('/onboarding')) {
    return
  }

  // Skip for career pages (public)
  if (to.path.startsWith('/career')) {
    return
  }

  // Only check for authenticated users
  try {
    const { isLoggedIn, user, refreshUser } = useSanctum()

    // If not logged in, let auth middleware handle it
    if (!isLoggedIn.value) {
      return
    }

    // If user data not loaded yet, try to refresh it
    if (!user.value) {
      await refreshUser()
    }

    // Still no user data after refresh, skip check
    if (!user.value) {
      return
    }

    // Check if onboarding is complete
    const typedUser = user.value as User
    if (typedUser.onboarded === false) {
      return navigateTo('/onboarding')
    }
  } catch (error) {
    // If useSanctum fails (not initialized), skip check
    console.error('Onboarding middleware error:', error)
    return
  }
})
