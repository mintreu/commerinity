// composables/useCart.ts

import { useSanctum, useSanctumFetch, useRuntimeConfig, useCookie } from '#imports'

interface CartItem {
    product_id: number
    quantity: number
    product: {
        name: string
        url: string
        sku: string
        type: string
        min_quantity: number
        max_quantity: number
        price: string
        thumbnail: string | null
    }
    summary: {
        quantity: number
        sub_total: string
        discount: string
        tax: string
        total: string
    }
}

interface CartSummary {
    sub_total: string
    tax: string
    tax_percentage: number
    discount: string
    coupon_applied: boolean
    coupon_code: string | null
    total: string
    quantity: number
}

interface CustomerIdentity {
    type: 'authenticated' | 'guest'
    is_guest: boolean
    token_expires_in: number
}

interface CustomerProfile {
    name?: string
    email?: string
    mobile?: string
    status_label?: string
    type_label?: string
    class?: string
}

interface CartResponse {
    summary: CartSummary
    customer: {
        identity: CustomerIdentity
        profile: CustomerProfile
    }
    items: CartItem[]
    error: string | null
}

interface ValidationResponse {
    status: boolean
    error?: string
}

interface CredentialResponse {
    status: 'exist' | 'fresh'
    guest_id: string
    guest_token: string
    expires_at: string
}

export const useCart = () => {
    const { isLoggedIn } = useSanctum()
    const config = useRuntimeConfig()

    // Global state matching backend structure
    const cartData = useState<CartResponse>('cartData', () => ({
        summary: {
            sub_total: '₹0.00',
            tax: '₹0.00',
            tax_percentage: 0,
            discount: '₹0.00',
            coupon_applied: false,
            coupon_code: null,
            total: '₹0.00',
            quantity: 0
        },
        customer: {
            identity: {
                type: 'guest',
                is_guest: true,
                token_expires_in: 0
            },
            profile: {}
        },
        items: [],
        error: null
    }))

    const loading = useState('cartLoading', () => false)
    const requestQueue = useState('cartRequestQueue', () => new Set<string>())
    const isValidating = useState('cartIsValidating', () => false)

    // Guest credentials - matching backend headers
    const guestId = useCookie('guest_id', {
        secure: true,
        sameSite: 'lax',
        maxAge: 60 * 60 * 24 * 15, // 15 days to match backend
        httpOnly: false
    })

    const guestToken = useCookie('guest_token', {
        secure: true,
        sameSite: 'lax',
        maxAge: 60 * 60 * 24 * 15,
        httpOnly: false
    })

    const guestTokenExpires = useCookie('guest_token_expires', {
        secure: true,
        sameSite: 'lax',
        maxAge: 60 * 60 * 24 * 15,
        httpOnly: false
    })

    // Headers matching backend expectations
    const getHeaders = (): HeadersInit => {
        const headers: HeadersInit = {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }

        // Backend expects these specific header names for guest users only
        if (!isLoggedIn.value && guestId.value && guestToken.value) {
            headers['X-Guest-ID'] = guestId.value as string
            headers['X-Guest-Token'] = guestToken.value as string
        }

        return headers
    }

    // ✅ FIXED: Validate guest credentials with backend using POST method
    async function validateGuestCredential(): Promise<boolean> {
        // Skip validation for authenticated users
        if (isLoggedIn.value) return true

        // Skip if no credentials exist yet
        if (!guestId.value || !guestToken.value) {
            return false
        }

        // Prevent concurrent validation requests
        if (isValidating.value) {
            await new Promise(resolve => setTimeout(resolve, 300))
            return validateGuestCredential()
        }

        try {
            isValidating.value = true

            // ✅ FIXED: Changed from GET to POST
            const response = await useSanctumFetch(
                `${config.public.apiBase}/cart/validate/guest-credential`,
                {
                    method: 'POST', // Backend expects POST
                    headers: getHeaders(),
                    retry: 0 // No retry for validation
                }
            )

            const validationData = response?.data as ValidationResponse

            if (validationData?.status === true) {
                return true
            }

            // Validation failed
            console.warn('[Cart] Guest credential validation failed:', validationData?.error)
            return false

        } catch (e: any) {
            console.error('[Cart] Validation request error:', e)
            return false
        } finally {
            isValidating.value = false
        }
    }

    // ✅ ENHANCED: Fetch/refresh guest credentials with existing guest_id support
    async function ensureGuestCredentials(retryCount = 0, forceRefresh = false): Promise<boolean> {
        // If logged in, no guest credentials needed
        if (isLoggedIn.value) {
            console.log('[Cart] User authenticated, skipping guest credentials')
            return true
        }

        // Check if current credentials are valid (unless forcing refresh)
        if (!forceRefresh) {
            const hasValidCredentials = guestId.value &&
                guestToken.value &&
                guestTokenExpires.value &&
                new Date(guestTokenExpires.value as string).getTime() > Date.now()

            if (hasValidCredentials) {
                // Validate with backend before trusting local credentials
                const isValid = await validateGuestCredential()
                if (isValid) {
                    console.log('[Cart] Guest credentials exist:', guestId.value)
                    return true
                }
            }
        }

        // Prevent concurrent requests
        const credentialKey = 'guest-credential'
        if (requestQueue.value.has(credentialKey)) {
            await new Promise(resolve => setTimeout(resolve, 500))
            return ensureGuestCredentials(Math.min(retryCount + 1, 2), forceRefresh)
        }

        if (retryCount >= 2) {
            console.error('[Cart] Max retries reached for guest credentials')
            return false
        }

        try {
            requestQueue.value.add(credentialKey)

            // ✅ Pass existing guest_id and guest_token if available
            const response = await useSanctumFetch(
                `${config.public.apiBase}/cart/guest-credential`,
                {
                    method: 'POST',
                    headers: getHeaders(), // Will include existing credentials if present
                    retry: 1,
                    retryDelay: 1000
                }
            )

            const data = response?.data as CredentialResponse

            if (data?.guest_id && data?.guest_token && data?.expires_at) {
                // Clear old cookies before setting new ones if guest_id changed
                if (guestId.value && guestId.value !== data.guest_id) {
                    console.log('[Cart] Guest ID changed, clearing old credentials')
                    guestId.value = null
                    guestToken.value = null
                    guestTokenExpires.value = null
                }

                // Set new credentials
                guestId.value = data.guest_id
                guestToken.value = data.guest_token
                guestTokenExpires.value = data.expires_at

                console.log(`[Cart] Guest credentials ${data.status}: ${data.guest_id}`)
                return true
            }

            console.error('[Cart] Invalid guest credential response:', response)
            return false

        } catch (e: any) {
            console.error('[Cart] Failed to get guest credentials:', e)

            // Exponential backoff for retries
            if (retryCount < 2) {
                await new Promise(resolve => setTimeout(resolve, Math.pow(2, retryCount) * 1000))
                return ensureGuestCredentials(retryCount + 1, forceRefresh)
            }

            return false
        } finally {
            requestQueue.value.delete(credentialKey)
        }
    }

    // ✅ ENHANCED: Handle cart response errors and retry with fresh credentials
    async function handleCartResponseError(
        response: any,
        operation: () => Promise<any>,
        retryCount = 0
    ): Promise<any> {
        // Check for credential validation error in response
        const hasCredentialError = response?.data?.error === 'cart credential not validated!'

        if (hasCredentialError && retryCount < 1 && !isLoggedIn.value) {
            console.warn('[Cart] Cart credential not validated - refreshing credentials')

            // Clear existing invalid credentials
            guestId.value = null
            guestToken.value = null
            guestTokenExpires.value = null

            // Force refresh credentials
            const refreshed = await ensureGuestCredentials(0, true)

            if (refreshed) {
                // Retry the original operation with new credentials
                console.log('[Cart] Retrying operation with fresh credentials')
                await new Promise(resolve => setTimeout(resolve, 300))
                return await operation()
            }
        }

        // Return original response if no retry or retry failed
        return response
    }

    // ✅ ENHANCED: Fetch cart with validation and retry
    async function fetchCart() {
        if (loading.value) return

        const fetchKey = 'fetch-cart'
        if (requestQueue.value.has(fetchKey)) return

        loading.value = true
        requestQueue.value.add(fetchKey)

        try {
            // For guest users, ensure credentials are valid
            if (!isLoggedIn.value) {
                const hasCredentials = await ensureGuestCredentials()
                if (!hasCredentials) {
                    console.warn('[Cart] No valid guest credentials, skipping fetch')
                    return
                }
            }

            const performFetch = async () => {
                return await useSanctumFetch(`${config.public.apiBase}/cart`, {
                    method: 'GET',
                    headers: getHeaders(),
                    retry: 1,
                    retryDelay: 500
                })
            }

            let response = await performFetch()

            // Handle credential validation errors (for guest users only)
            if (!isLoggedIn.value) {
                response = await handleCartResponseError(response, performFetch)
            }

            // Update cart data
            if (response?.data) {
                cartData.value = response.data
            }

        } catch (e: any) {
            console.error('[Cart] Fetch error:', e)

            // Handle auth/credential errors for guest users
            if (e.status === 419 || e.status === 403 || e.status === 401) {
                if (!isLoggedIn.value) {
                    console.log('[Cart] Auth error, clearing guest credentials')
                    guestId.value = null
                    guestToken.value = null
                    guestTokenExpires.value = null

                    // Retry once with new credentials
                    if (!requestQueue.value.has('retry-fetch')) {
                        requestQueue.value.add('retry-fetch')
                        setTimeout(() => {
                            requestQueue.value.delete('retry-fetch')
                            fetchCart()
                        }, 1000)
                    }
                }
            }
        } finally {
            loading.value = false
            requestQueue.value.delete(fetchKey)
        }
    }

    // ✅ ENHANCED: Add to cart with validation and retry
    async function addToCart(sku: string, quantity = 1) {
        if (!sku?.trim()) throw new Error('SKU is required')

        const addKey = `add-${sku}-${quantity}`
        if (requestQueue.value.has(addKey)) {
            console.log(`[Cart] Add request for ${sku} already in progress`)
            return
        }

        try {
            requestQueue.value.add(addKey)

            // For guest users, ensure credentials are valid
            if (!isLoggedIn.value) {
                const hasCredentials = await ensureGuestCredentials()
                if (!hasCredentials) {
                    throw new Error('Unable to establish cart session')
                }
            }

            const performAdd = async () => {
                return await useSanctumFetch(
                    `${config.public.apiBase}/cart/add/${encodeURIComponent(sku)}`,
                    {
                        method: 'POST',
                        headers: getHeaders(),
                        body: JSON.stringify({ quantity }),
                        retry: 1,
                        retryDelay: 500
                    }
                )
            }

            let response = await performAdd()

            // Handle credential validation errors (for guest users only)
            if (!isLoggedIn.value) {
                response = await handleCartResponseError(response, performAdd)
            }

            if (response?.data) {
                cartData.value = response.data
            }

        } catch (e: any) {
            console.error('[Cart] Add error:', e)

            // Clear credentials on auth errors (guest users only)
            if ((e.status === 419 || e.status === 403 || e.status === 401) && !isLoggedIn.value) {
                guestId.value = null
                guestToken.value = null
                guestTokenExpires.value = null
            }

            throw e
        } finally {
            requestQueue.value.delete(addKey)
        }
    }

    // ✅ ENHANCED: Update cart item with validation and retry
    async function updateCartItem(sku: string, quantity: number) {
        if (!sku?.trim()) throw new Error('SKU is required')
        if (quantity < 0) throw new Error('Quantity must be positive')

        const updateKey = `update-${sku}-${quantity}`
        if (requestQueue.value.has(updateKey)) return

        try {
            requestQueue.value.add(updateKey)

            // For guest users, ensure credentials are valid
            if (!isLoggedIn.value) {
                const hasCredentials = await ensureGuestCredentials()
                if (!hasCredentials) {
                    throw new Error('Unable to establish cart session')
                }
            }

            const performUpdate = async () => {
                return await useSanctumFetch(
                    `${config.public.apiBase}/cart/update/${encodeURIComponent(sku)}`,
                    {
                        method: 'POST',
                        headers: getHeaders(),
                        body: JSON.stringify({ quantity }),
                        retry: 1,
                        retryDelay: 500
                    }
                )
            }

            let response = await performUpdate()

            // Handle credential validation errors (for guest users only)
            if (!isLoggedIn.value) {
                response = await handleCartResponseError(response, performUpdate)
            }

            if (response?.data) {
                cartData.value = response.data
            }

        } catch (e: any) {
            console.error('[Cart] Update error:', e)

            if ((e.status === 419 || e.status === 403 || e.status === 401) && !isLoggedIn.value) {
                guestId.value = null
                guestToken.value = null
                guestTokenExpires.value = null
            }

            throw e
        } finally {
            requestQueue.value.delete(updateKey)
        }
    }

    // ✅ ENHANCED: Remove item with validation and retry
    async function removeItem(sku: string) {
        if (!sku?.trim()) throw new Error('SKU is required')

        const removeKey = `remove-${sku}`
        if (requestQueue.value.has(removeKey)) return

        try {
            requestQueue.value.add(removeKey)

            // For guest users, ensure credentials are valid
            if (!isLoggedIn.value) {
                const hasCredentials = await ensureGuestCredentials()
                if (!hasCredentials) {
                    throw new Error('Unable to establish cart session')
                }
            }

            const performRemove = async () => {
                return await useSanctumFetch(
                    `${config.public.apiBase}/cart/remove/${encodeURIComponent(sku)}`,
                    {
                        method: 'DELETE',
                        headers: getHeaders(),
                        retry: 1,
                        retryDelay: 500
                    }
                )
            }

            let response = await performRemove()

            // Handle credential validation errors (for guest users only)
            if (!isLoggedIn.value) {
                response = await handleCartResponseError(response, performRemove)
            }

            if (response?.data) {
                cartData.value = response.data
            }

        } catch (e: any) {
            console.error('[Cart] Remove error:', e)

            if ((e.status === 419 || e.status === 403 || e.status === 401) && !isLoggedIn.value) {
                guestId.value = null
                guestToken.value = null
                guestTokenExpires.value = null
            }

            throw e
        } finally {
            requestQueue.value.delete(removeKey)
        }
    }

    // ✅ ENHANCED: Apply coupon with validation and retry
    async function applyCoupon(code: string) {
        if (!code?.trim()) {
            return { success: false, errors: 'Coupon code is required' }
        }

        const couponKey = `coupon-${code}`
        if (requestQueue.value.has(couponKey)) {
            return { success: false, errors: 'Coupon request in progress' }
        }

        try {
            requestQueue.value.add(couponKey)

            // For guest users, ensure credentials are valid
            if (!isLoggedIn.value) {
                const hasCredentials = await ensureGuestCredentials()
                if (!hasCredentials) {
                    return { success: false, errors: 'Unable to establish session' }
                }
            }

            const safeCode = encodeURIComponent(code.trim())

            const performCoupon = async () => {
                return await useSanctumFetch(
                    `${config.public.apiBase}/cart/coupon/${safeCode}`,
                    {
                        method: 'POST',
                        headers: getHeaders(),
                        retry: 1,
                        retryDelay: 500
                    }
                )
            }

            let response = await performCoupon()

            // Handle credential validation errors (for guest users only)
            if (!isLoggedIn.value) {
                response = await handleCartResponseError(response, performCoupon)
            }

            // Backend returns { success: boolean, errors: string|null }
            if (response?.success) {
                await fetchCart() // Refresh cart to get updated totals
                return { success: true }
            }

            return {
                success: false,
                errors: response?.errors || 'Invalid or expired coupon'
            }

        } catch (e: any) {
            console.error('[Cart] Coupon error:', e)

            if ((e.status === 419 || e.status === 403 || e.status === 401) && !isLoggedIn.value) {
                guestId.value = null
                guestToken.value = null
                guestTokenExpires.value = null
            }

            return { success: false, errors: 'Failed to apply coupon. Please try again.' }
        } finally {
            requestQueue.value.delete(couponKey)
        }
    }

    // ✅ NEW: Clear cart
    async function clearCart() {
        try {
            // For guest users, ensure credentials are valid
            if (!isLoggedIn.value) {
                const hasCredentials = await ensureGuestCredentials()
                if (!hasCredentials) {
                    throw new Error('Unable to establish cart session')
                }
            }

            const response = await useSanctumFetch(
                `${config.public.apiBase}/cart/clear`,
                {
                    method: 'POST',
                    headers: getHeaders(),
                    retry: 1,
                    retryDelay: 500
                }
            )

            if (response?.data) {
                cartData.value = response.data
            }

        } catch (e: any) {
            console.error('[Cart] Clear error:', e)
            throw e
        }
    }

    // ✅ NEW: Merge guest cart (for authenticated users only)
    async function mergeGuestCart() {
        if (!isLoggedIn.value) {
            console.warn('[Cart] User must be authenticated to merge cart')
            return { success: false, message: 'User not authenticated' }
        }

        try {
            const response = await useSanctumFetch(
                `${config.public.apiBase}/cart/merge`,
                {
                    method: 'POST',
                    headers: getHeaders(),
                    retry: 1,
                    retryDelay: 500
                }
            )

            // Clear guest credentials after successful merge
            guestId.value = null
            guestToken.value = null
            guestTokenExpires.value = null

            // Refresh cart to get merged data
            await fetchCart()

            return { success: true, message: 'Cart merged successfully' }

        } catch (e: any) {
            console.error('[Cart] Merge error:', e)
            return { success: false, message: 'Failed to merge cart' }
        }
    }

    // Computed properties matching backend structure
    const itemCount = computed(() => cartData.value.summary?.quantity || 0)
    const items = computed(() => cartData.value.items || [])
    const summary = computed(() => cartData.value.summary)
    const customer = computed(() => cartData.value.customer)
    const hasError = computed(() => !!cartData.value.error)
    const errorMessage = computed(() => cartData.value.error)
    const isGuest = computed(() => customer.value?.identity?.is_guest ?? true)

    return {
        // State
        cartData,
        loading,

        // Computed
        itemCount,
        items,
        summary,
        customer,
        hasError,
        errorMessage,
        isGuest,

        // Methods
        fetchCart,
        addToCart,
        updateCartItem,
        removeItem,
        applyCoupon,
        clearCart,
        mergeGuestCart,
        ensureGuestCredentials,
        validateGuestCredential
    }
}
