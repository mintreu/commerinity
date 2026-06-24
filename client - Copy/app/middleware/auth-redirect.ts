/**
 * Auth middleware that saves the intended URL before redirecting to login.
 *
 * Use this middleware on protected pages instead of $auth to enable
 * "redirect back after login" functionality.
 *
 * Usage: definePageMeta({ middleware: ['auth-redirect'] })
 */
export default defineNuxtRouteMiddleware((to) => {
  const { isLoggedIn } = useSanctum()

  if (!isLoggedIn.value) {
    // Save the intended URL before redirecting to login
    const { saveRedirectUrl } = useRedirectUrl()
    saveRedirectUrl(to.fullPath)

    return navigateTo('/auth/login')
  }
})
