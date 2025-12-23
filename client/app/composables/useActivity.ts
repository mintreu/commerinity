/**
 * Activity tracking composable
 * Tracks user activities from client side and sends to backend for analytics
 * Activities are stored for admin only - not visible to users
 */

interface ActivityProperties {
  page_path?: string
  page_title?: string
  referrer?: string
  action?: string
  target?: string
  data?: Record<string, unknown>
  [key: string]: unknown
}

interface LocationData {
  latitude?: number
  longitude?: number
  city?: string
  country?: string
}

interface ScreenData {
  width: number
  height: number
  viewport_width: number
  viewport_height: number
}

interface ActivityPayload {
  event: string
  description?: string
  properties?: ActivityProperties
  location?: LocationData
  screen?: ScreenData
}

interface BatchActivity {
  event: string
  description?: string
  properties?: ActivityProperties
  timestamp?: string
}

export function useActivity() {
  const config = useRuntimeConfig()
  const { isAuthenticated } = useSanctum()

  // Queue for offline/batching
  const activityQueue = ref<BatchActivity[]>([])
  const isTracking = ref(false)

  /**
   * Get current screen info
   */
  const getScreenData = (): ScreenData => ({
    width: window.screen.width,
    height: window.screen.height,
    viewport_width: window.innerWidth,
    viewport_height: window.innerHeight,
  })

  /**
   * Track a generic activity
   */
  const track = async (
    event: string,
    description?: string,
    properties?: ActivityProperties
  ): Promise<void> => {
    if (!isAuthenticated.value || isTracking.value) return

    try {
      isTracking.value = true
      const payload: ActivityPayload = {
        event,
        description,
        properties,
        screen: getScreenData(),
      }

      await useSanctumFetch(`${config.public.apiBase}/api/activity/track`, {
        method: 'POST',
        body: payload,
      })
    } catch (error) {
      // Silently fail - activity tracking shouldn't block user
      console.debug('Activity tracking failed:', error)
    } finally {
      isTracking.value = false
    }
  }

  /**
   * Track a page view
   */
  const trackPageView = async (
    pagePath: string,
    pageTitle: string,
    referrer?: string
  ): Promise<void> => {
    if (!isAuthenticated.value) return

    try {
      await useSanctumFetch(`${config.public.apiBase}/api/activity/page-view`, {
        method: 'POST',
        body: {
          page_path: pagePath,
          page_title: pageTitle,
          referrer,
          screen: getScreenData(),
        },
      })
    } catch (error) {
      console.debug('Page view tracking failed:', error)
    }
  }

  /**
   * Track a user action (click, submit, etc.)
   */
  const trackAction = async (
    action: string,
    target: string,
    data?: Record<string, unknown>
  ): Promise<void> => {
    if (!isAuthenticated.value) return

    try {
      await useSanctumFetch(`${config.public.apiBase}/api/activity/action`, {
        method: 'POST',
        body: {
          action,
          target,
          data,
          screen: getScreenData(),
        },
      })
    } catch (error) {
      console.debug('Action tracking failed:', error)
    }
  }

  /**
   * Add activity to queue for batch sending
   */
  const queueActivity = (
    event: string,
    description?: string,
    properties?: ActivityProperties
  ): void => {
    activityQueue.value.push({
      event,
      description,
      properties,
      timestamp: new Date().toISOString(),
    })
  }

  /**
   * Send queued activities in batch
   */
  const flushQueue = async (): Promise<void> => {
    if (!isAuthenticated.value || activityQueue.value.length === 0) return

    try {
      const activities = [...activityQueue.value]
      activityQueue.value = []

      await useSanctumFetch(`${config.public.apiBase}/api/activity/batch`, {
        method: 'POST',
        body: { activities },
      })
    } catch (error) {
      console.debug('Batch activity tracking failed:', error)
    }
  }

  /**
   * Track login event
   */
  const trackLogin = (method: string = 'password'): Promise<void> => {
    return track('login', `User logged in via ${method}`, { method })
  }

  /**
   * Track logout event
   */
  const trackLogout = (): Promise<void> => {
    return track('logout', 'User logged out')
  }

  /**
   * Track referral share
   */
  const trackShare = (platform: string): Promise<void> => {
    return track('referral_share', `Shared referral link via ${platform}`, {
      platform,
    })
  }

  /**
   * Track subscription action
   */
  const trackSubscription = (action: string, planName?: string): Promise<void> => {
    return track(
      'subscription',
      `Subscription ${action}${planName ? ` for plan: ${planName}` : ''}`,
      { action, plan: planName }
    )
  }

  /**
   * Track wallet view
   */
  const trackWalletView = (): Promise<void> => {
    return track('wallet_view', 'User viewed wallet')
  }

  /**
   * Track profile update
   */
  const trackProfileUpdate = (changedFields: string[]): Promise<void> => {
    return track('profile_update', 'User updated profile', {
      changed_fields: changedFields,
    })
  }

  /**
   * Track network/MLM tree view
   */
  const trackNetworkView = (): Promise<void> => {
    return track('network_view', 'User viewed network/MLM tree')
  }

  /**
   * Track KYC submission
   */
  const trackKycSubmit = (): Promise<void> => {
    return track('kyc_submit', 'User submitted KYC documents')
  }

  return {
    // Core tracking
    track,
    trackPageView,
    trackAction,

    // Batch/queue
    queueActivity,
    flushQueue,
    activityQueue: readonly(activityQueue),

    // Convenience methods
    trackLogin,
    trackLogout,
    trackShare,
    trackSubscription,
    trackWalletView,
    trackProfileUpdate,
    trackNetworkView,
    trackKycSubmit,
  }
}
