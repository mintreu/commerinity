/**
 * Composable for managing redirect URL after login.
 *
 * Stores the intended destination URL when a user is redirected to login,
 * so they can be redirected back after successful authentication.
 */
export const useRedirectUrl = () => {
  const STORAGE_KEY = 'auth_redirect_url'

  /**
   * Save the intended URL before redirecting to login.
   * Only saves if the URL is not an auth-related page.
   */
  const saveRedirectUrl = (url?: string): void => {
    if (import.meta.server) return

    const targetUrl = url || window.location.pathname + window.location.search

    // Don't save auth-related or onboarding URLs
    const excludedPaths = ['/auth/', '/onboarding']
    const shouldExclude = excludedPaths.some(path => targetUrl.startsWith(path))

    if (!shouldExclude && targetUrl !== '/') {
      sessionStorage.setItem(STORAGE_KEY, targetUrl)
    }
  }

  /**
   * Get the saved redirect URL and clear it from storage.
   * Returns null if no URL was saved.
   */
  const getAndClearRedirectUrl = (): string | null => {
    if (import.meta.server) return null

    const url = sessionStorage.getItem(STORAGE_KEY)
    if (url) {
      sessionStorage.removeItem(STORAGE_KEY)
    }
    return url
  }

  /**
   * Get the saved redirect URL without clearing it.
   */
  const getRedirectUrl = (): string | null => {
    if (import.meta.server) return null
    return sessionStorage.getItem(STORAGE_KEY)
  }

  /**
   * Clear the saved redirect URL.
   */
  const clearRedirectUrl = (): void => {
    if (import.meta.server) return
    sessionStorage.removeItem(STORAGE_KEY)
  }

  /**
   * Check if there's a saved redirect URL.
   */
  const hasRedirectUrl = (): boolean => {
    if (import.meta.server) return false
    return sessionStorage.getItem(STORAGE_KEY) !== null
  }

  return {
    saveRedirectUrl,
    getAndClearRedirectUrl,
    getRedirectUrl,
    clearRedirectUrl,
    hasRedirectUrl
  }
}
