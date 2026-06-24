import type { User } from '~/types/user'

export default defineNuxtRouteMiddleware(async (to) => {
  const publicPrefixes = ['/', '/about', '/contact', '/privacy', '/terms', '/shop', '/category', '/categories', '/product', '/products', '/career']
  if (publicPrefixes.some(prefix => to.path === prefix || to.path.startsWith(prefix + '/'))) return

  if (to.path.startsWith('/auth/')) return

  if (to.path.startsWith('/onboarding')) return

  if (to.path.startsWith('/subscription')) return

  try {
    const { isLoggedIn, user, refreshUser } = useSanctum()

    if (!isLoggedIn.value) return

    if (!user.value) {
      await refreshUser()
    }

    if (!user.value) return

    const typedUser = user.value as User
    if (typedUser.onboarded !== true) {
      return navigateTo('/onboarding')
    }
  } catch (error) {
    console.error('Onboarding middleware error:', error)
    return
  }
})
