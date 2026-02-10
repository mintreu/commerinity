// Helpers for producing user-facing API error messages and keeping the original details for logging
export interface FriendlyApiErrorMeta {
  message: string
  status?: number
  originalMessage?: string
  kind?: 'network' | 'server' | 'authorization' | 'validation' | 'general'
}

export type ApiErrorContext =
  | 'categories'
  | 'products'
  | 'product_details'
  | 'login'
  | 'register'
  | 'cart'
  | 'wishlist'
  | 'orders'
  | 'profile'
  | 'wallet'
  | 'onboarding'
  | 'helpdesk'
  | 'contact'
  | 'messages'
  | 'appointments'
  | 'careers'
  | 'subscriptions'
  | 'network'
  | 'general'

type ContextMessages = Partial<Record<NonNullable<FriendlyApiErrorMeta['kind']>, string>>

const NETWORK_INDICATORS = [
  'failed to fetch',
  'networkerror',
  'connection refused',
  'econnrefused',
  'enotfound',
  'eai_again',
  'getaddrinfo',
  'no connection',
  'net::err_name_not_resolved'
]

const FALLBACK_MESSAGES = {
  network: 'Unable to reach our servers. Check your internet connection and try again.',
  server: 'We are experiencing an issue on our side. Please try again in a few minutes.',
  validation: 'Some data is incorrect. Please review and try again.',
  general: 'Something went wrong. Please try again.',
  authorization: 'Your session expired. Please log in again to continue.'
}

const CONTEXT_MESSAGES: Record<ApiErrorContext, ContextMessages> = {
  categories: {
    network: 'Categories are unavailable right now. Please check your connection.',
    server: 'We could not load categories at the moment.',
    general: 'Unable to load categories right now.'
  },
  products: {
    network: 'Products cannot be loaded right now. Please check your connection.',
    server: 'Products are temporarily unavailable.',
    general: 'Unable to load products right now.'
  },
  product_details: {
    network: 'We could not load this product. Please check your connection.',
    server: 'Product details are temporarily unavailable.',
    general: 'Unable to load product details right now.'
  },
  login: {
    network: 'Unable to reach the login server. Please check your connection.',
    server: 'Login service is temporarily unavailable.',
    authorization: 'Login failed. Please check your credentials.',
    general: 'Login failed. Please try again.'
  },
  register: {
    network: 'Unable to reach the signup server. Please check your connection.',
    server: 'Signup service is temporarily unavailable.',
    validation: 'Please check the form details and try again.',
    general: 'Signup failed. Please try again.'
  },
  cart: {
    network: 'We could not update your cart. Please check your connection.',
    server: 'Cart service is temporarily unavailable.',
    general: 'Unable to update your cart right now.'
  },
  wishlist: {
    network: 'We could not update your wishlist. Please check your connection.',
    server: 'Wishlist service is temporarily unavailable.',
    general: 'Unable to update your wishlist right now.'
  },
  orders: {
    network: 'We could not load your orders. Please check your connection.',
    server: 'Orders are temporarily unavailable.',
    general: 'Unable to load orders right now.'
  },
  profile: {
    network: 'We could not update your profile. Please check your connection.',
    server: 'Profile service is temporarily unavailable.',
    general: 'Unable to update your profile right now.'
  },
  wallet: {
    network: 'Wallet service is unavailable. Please check your connection.',
    server: 'Wallet service is temporarily unavailable.',
    general: 'Unable to process wallet request right now.'
  },
  onboarding: {
    network: 'Unable to continue onboarding. Please check your connection.',
    server: 'Onboarding service is temporarily unavailable.',
    general: 'Unable to continue onboarding right now.'
  },
  helpdesk: {
    network: 'Unable to reach support. Please check your connection.',
    server: 'Helpdesk is temporarily unavailable.',
    general: 'Unable to contact support right now.'
  },
  contact: {
    network: 'Unable to send your message. Please check your connection.',
    server: 'Contact service is temporarily unavailable.',
    general: 'Unable to send your message right now.'
  },
  messages: {
    network: 'Unable to load messages. Please check your connection.',
    server: 'Messaging service is temporarily unavailable.',
    general: 'Unable to load messages right now.'
  },
  appointments: {
    network: 'Unable to load appointments. Please check your connection.',
    server: 'Appointments are temporarily unavailable.',
    general: 'Unable to load appointments right now.'
  },
  careers: {
    network: 'Unable to load career information. Please check your connection.',
    server: 'Careers are temporarily unavailable.',
    general: 'Unable to load career information right now.'
  },
  subscriptions: {
    network: 'Unable to load subscriptions. Please check your connection.',
    server: 'Subscriptions are temporarily unavailable.',
    general: 'Unable to load subscriptions right now.'
  },
  network: {
    network: 'Network service is unavailable. Please check your connection.',
    server: 'Network service is temporarily unavailable.',
    general: 'Unable to load network data right now.'
  },
  general: {}
}

type EndpointMessageRule = {
  pattern: RegExp
  messages: ContextMessages
}

// Endpoint-specific overrides. Keep these concise and user-facing.
const ENDPOINT_MESSAGES: EndpointMessageRule[] = [
  {
    pattern: /\/api\/catalog\/categories/i,
    messages: {
      network: 'Unable to load categories right now. Please check your connection.',
      server: 'Categories are temporarily unavailable.',
      general: 'Unable to load categories right now.'
    }
  },
  {
    pattern: /\/api\/catalog\/products/i,
    messages: {
      network: 'Unable to load products right now. Please check your connection.',
      server: 'Products are temporarily unavailable.',
      general: 'Unable to load products right now.'
    }
  }
]

const getStatusFromError = (error: Record<string, unknown>) =>
  (error.response as Record<string, unknown> | undefined)?.status
  ?? (error.response as Record<string, unknown> | undefined)?._data?.status
  ?? (error.status as number | undefined)
  ?? (error.statusCode as number | undefined)

const extractMessageFromPayload = (payload: unknown): string | undefined => {
  if (typeof payload === 'string' && payload.trim().length > 0) {
    return payload
  }
  if (typeof payload === 'object' && payload !== null) {
    const message = (payload as Record<string, unknown>).message
    if (typeof message === 'string' && message.trim().length > 0) {
      return message
    }
  }
  return undefined
}

const getServerMessage = (error: Record<string, unknown>): string | undefined => {
  const response = error.response as Record<string, unknown> | undefined
  const responseBody = response?._data ?? response?.data ?? error.data
  return (
    extractMessageFromPayload(responseBody)
    ?? extractMessageFromPayload(response?._data)
    ?? extractMessageFromPayload(error.data)
    ?? extractMessageFromPayload(error.message)
  )
}

const isNetworkError = (error: Record<string, unknown>) => {
  const message = typeof error.message === 'string' ? error.message.toLowerCase() : ''
  const code = typeof error.code === 'string' ? error.code.toLowerCase() : ''
  if (message && NETWORK_INDICATORS.some((token) => message.includes(token))) {
    return true
  }
  if (code && NETWORK_INDICATORS.some((token) => code.includes(token))) {
    return true
  }
  return false
}

export const getFriendlyApiError = (error: unknown): FriendlyApiErrorMeta => {
  const normalized = typeof error === 'object' && error !== null ? (error as Record<string, unknown>) : {}
  const status = getStatusFromError(normalized)
  const serverMessage = getServerMessage(normalized)
  const originalMessage = typeof error === 'string'
    ? error
    : typeof normalized.message === 'string'
      ? normalized.message
      : undefined

  if (!status) {
    if (normalized.name === 'AbortError') {
      return {
        message: 'Request was cancelled. Please try again.',
        originalMessage,
        kind: 'general'
      }
    }
    if (isNetworkError(normalized)) {
      return {
        message: FALLBACK_MESSAGES.network,
        originalMessage,
        kind: 'network'
      }
    }
    return {
      message: FALLBACK_MESSAGES.general,
      originalMessage,
      kind: 'general'
    }
  }

  if (status >= 500) {
    return {
      message: FALLBACK_MESSAGES.server,
      status,
      originalMessage,
      kind: 'server'
    }
  }

  if (status === 401) {
    return {
      message: FALLBACK_MESSAGES.authorization,
      status,
      originalMessage,
      kind: 'authorization'
    }
  }

  if (status === 403) {
    return {
      message: serverMessage ?? 'You are not allowed to perform this action.',
      status,
      originalMessage,
      kind: 'authorization'
    }
  }

  if (status === 404) {
    return {
      message: serverMessage ?? 'The requested resource could not be found.',
      status,
      originalMessage,
      kind: 'general'
    }
  }

  if (status === 429) {
    return {
      message: 'You are making requests too quickly. Please wait a moment and try again.',
      status,
      originalMessage,
      kind: 'validation'
    }
  }

  if (serverMessage) {
    return {
      message: serverMessage,
      status,
      originalMessage,
      kind: 'validation'
    }
  }

  return {
    message: FALLBACK_MESSAGES.validation,
    status,
    originalMessage,
    kind: 'validation'
  }
}

const getEndpointOverride = (url: string | undefined | null, kind: FriendlyApiErrorMeta['kind']) => {
  if (!url || !kind) return undefined
  for (const rule of ENDPOINT_MESSAGES) {
    if (rule.pattern.test(url)) {
      return rule.messages[kind]
    }
  }
  return undefined
}

export const getContextualApiError = (
  error: unknown,
  context: ApiErrorContext = 'general',
  url?: string | null
): FriendlyApiErrorMeta => {
  const base = getFriendlyApiError(error)
  const endpointOverride = getEndpointOverride(url, base.kind)
  if (endpointOverride) {
    return {
      ...base,
      message: endpointOverride
    }
  }
  const contextMessage = CONTEXT_MESSAGES[context]?.[base.kind ?? 'general']
  if (contextMessage) {
    return {
      ...base,
      message: contextMessage
    }
  }
  return base
}

const CONTEXT_ROUTE_MAP: Array<{ pattern: RegExp; context: ApiErrorContext }> = [
  { pattern: /\/api\/catalog\/categories/i, context: 'categories' },
  { pattern: /\/api\/catalog\/products/i, context: 'products' },
  { pattern: /\/api\/catalog\/featured/i, context: 'products' },
  { pattern: /\/api\/products\//i, context: 'product_details' },
  { pattern: /\/api\/auth\/login/i, context: 'login' },
  { pattern: /\/api\/auth\/register/i, context: 'register' },
  { pattern: /\/api\/auth\/send-otp/i, context: 'login' },
  { pattern: /\/api\/cart/i, context: 'cart' },
  { pattern: /\/api\/wishlist/i, context: 'wishlist' },
  { pattern: /\/api\/orders/i, context: 'orders' },
  { pattern: /\/api\/user/i, context: 'profile' },
  { pattern: /\/api\/account/i, context: 'profile' },
  { pattern: /\/api\/addresses/i, context: 'profile' },
  { pattern: /\/api\/kyc/i, context: 'profile' },
  { pattern: /\/api\/wallet/i, context: 'wallet' },
  { pattern: /\/api\/onboarding/i, context: 'onboarding' },
  { pattern: /\/api\/helpdesk/i, context: 'helpdesk' },
  { pattern: /\/api\/contact/i, context: 'contact' },
  { pattern: /\/api\/messages/i, context: 'messages' },
  { pattern: /\/api\/appointments/i, context: 'appointments' },
  { pattern: /\/api\/careers/i, context: 'careers' },
  { pattern: /\/api\/subscription/i, context: 'subscriptions' },
  { pattern: /\/api\/affiliate/i, context: 'network' },
  { pattern: /\/api\/commissions/i, context: 'network' },
  { pattern: /\/api\/notices/i, context: 'network' }
]

export const getContextFromUrl = (url: string | undefined | null): ApiErrorContext => {
  if (!url) return 'general'
  const normalized = url.toString()
  for (const entry of CONTEXT_ROUTE_MAP) {
    if (entry.pattern.test(normalized)) {
      return entry.context
    }
  }
  return 'general'
}

const EMPTY_STATE_MESSAGES: Partial<Record<ApiErrorContext, string>> = {
  categories: 'Oops, no categories found.',
  products: 'No products matched your filters.',
  orders: 'No orders found yet.',
  wishlist: 'Your wishlist is empty.',
  messages: 'No messages yet.',
  careers: 'No openings available right now.'
}

export const getEmptyStateMessage = (context: ApiErrorContext, fallback = 'No records found.') =>
  EMPTY_STATE_MESSAGES[context] ?? fallback
