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
        price: string // Already formatted from backend
        thumbnail: string | null
    }
    summary: {
        quantity: number
        sub_total: string // Already formatted
        discount: string // Already formatted
        tax: string // Already formatted
        total: string // Already formatted
    }
}

interface CartSummary {
    sub_total: string // Already formatted
    tax: string // Already formatted
    tax_percentage: number
    discount: string // Already formatted
    coupon_applied: boolean
    coupon_code: string | null
    total: string // Already formatted
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

export const useCart = () => {
    const { isLoggedIn } = useSanctum()
    const config = useRuntimeConfig()

    // ✅ Global state matching backend structure
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
        maxAge: 60 * 60 * 24 * 15, // 15 days to match backend
        httpOnly: false
    })

    const guestTokenExpires = useCookie('guest_token_expires', {
        secure: true,
        sameSite: 'lax',
        maxAge: 60 * 60 * 24 * 15,
        httpOnly: false
    })

    // ✅ Headers matching backend expectations
    const getHeaders = (): HeadersInit => {
        const headers: HeadersInit = {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }

        // Backend expects these specific header names (configurable)
        if (!isLoggedIn.value && guestId.value && guestToken.value) {
            headers['X-Guest-ID'] = guestId.value as string
            headers['X-Guest-Token'] = guestToken.value as string
        }

        return headers
    }

    // ✅ Guest credential validation matching backend logic
    async function ensureGuestCredentials(retryCount = 0): Promise<boolean> {
        // If logged in, no guest credentials needed
        if (isLoggedIn.value) return true

        // Check if current credentials are valid
        const hasValidCredentials = guestId.value &&
            guestToken.value &&
            guestTokenExpires.value &&
            new Date(guestTokenExpires.value).getTime() > Date.now()

        if (hasValidCredentials) return true

        // Prevent concurrent requests
        const credentialKey = 'guest-credential'
        if (requestQueue.value.has(credentialKey)) {
            await new Promise(resolve => setTimeout(resolve, 500))
            return ensureGuestCredentials(Math.min(retryCount + 1, 2))
        }

        if (retryCount >= 2) {
            console.error('[Cart] Max retries reached for guest credentials')
            return false
        }

        try {
            requestQueue.value.add(credentialKey)

            const response = await useSanctumFetch(
                `${config.public.apiBase}/cart/guest-credential`,
                {
                    method: 'POST',
                    headers: getHeaders(),
                    retry: 1,
                    retryDelay: 1000
                }
            )

            // Backend returns { data: { guest_id, guest_token, expires_at } }
            const data = response?.data

            if (data?.guest_id && data?.guest_token && data?.expires_at) {
                guestId.value = data.guest_id
                guestToken.value = data.guest_token
                guestTokenExpires.value = data.expires_at
                return true
            }

            console.error('[Cart] Invalid guest credential response:', response)
            return false

        } catch (e: any) {
            console.error('[Cart] Failed to get guest credentials:', e)

            // Exponential backoff for retries
            if (retryCount < 2) {
                await new Promise(resolve => setTimeout(resolve, Math.pow(2, retryCount) * 1000))
                return ensureGuestCredentials(retryCount + 1)
            }

            return false
        } finally {
            requestQueue.value.delete(credentialKey)
        }
    }

    // ✅ Fetch cart matching backend response structure
    async function fetchCart() {
        if (loading.value) return

        const fetchKey = 'fetch-cart'
        if (requestQueue.value.has(fetchKey)) return

        loading.value = true
        requestQueue.value.add(fetchKey)

        try {
            const hasCredentials = await ensureGuestCredentials()
            if (!hasCredentials && !isLoggedIn.value) {
                console.warn('[Cart] No valid credentials, skipping fetch')
                return
            }

            const response = await useSanctumFetch(`${config.public.apiBase}/cart`, {
                method: 'GET',
                headers: getHeaders(),
                retry: 2,
                retryDelay: 500
            })

            // Backend returns CartResource::make() which wraps data
            if (response?.data) {
                cartData.value = response.data
            }

        } catch (e: any) {
            console.error('[Cart] Fetch error:', e)

            // Handle auth/credential errors
            if (e.status === 419 || e.status === 403 || e.status === 401) {
                console.log('[Cart] Auth error, clearing credentials')
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
        } finally {
            loading.value = false
            requestQueue.value.delete(fetchKey)
        }
    }

    // ✅ Add to cart matching backend expectations
    async function addToCart(sku: string, quantity = 1) {
        if (!sku?.trim()) throw new Error('SKU is required')

        const addKey = `add-${sku}-${quantity}`
        if (requestQueue.value.has(addKey)) {
            console.log(`[Cart] Add request for ${sku} already in progress`)
            return
        }

        try {
            requestQueue.value.add(addKey)

            const hasCredentials = await ensureGuestCredentials()
            if (!hasCredentials && !isLoggedIn.value) {
                throw new Error('Unable to establish cart session')
            }

            const response = await useSanctumFetch(
                `${config.public.apiBase}/cart/add/${encodeURIComponent(sku)}`,
                {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({ quantity }),
                    retry: 1,
                    retryDelay: 500
                }
            )

            if (response?.data) {
                cartData.value = response.data
            }

        } catch (e: any) {
            console.error('[Cart] Add error:', e)

            // Clear credentials on auth errors and let user retry
            if (e.status === 419 || e.status === 403 || e.status === 401) {
                guestId.value = null
                guestToken.value = null
                guestTokenExpires.value = null
            }

            throw e
        } finally {
            requestQueue.value.delete(addKey)
        }
    }

    // ✅ Update cart item
    async function updateCartItem(sku: string, quantity: number) {
        if (!sku?.trim()) throw new Error('SKU is required')
        if (quantity < 0) throw new Error('Quantity must be positive')

        const updateKey = `update-${sku}-${quantity}`
        if (requestQueue.value.has(updateKey)) return

        try {
            requestQueue.value.add(updateKey)

            const hasCredentials = await ensureGuestCredentials()
            if (!hasCredentials && !isLoggedIn.value) {
                throw new Error('Unable to establish cart session')
            }

            const response = await useSanctumFetch(
                `${config.public.apiBase}/cart/update/${encodeURIComponent(sku)}`,
                {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({ quantity }),
                    retry: 1,
                    retryDelay: 500
                }
            )

            if (response?.data) {
                cartData.value = response.data
            }

        } catch (e: any) {
            console.error('[Cart] Update error:', e)

            if (e.status === 419 || e.status === 403 || e.status === 401) {
                guestId.value = null
                guestToken.value = null
                guestTokenExpires.value = null
            }

            throw e
        } finally {
            requestQueue.value.delete(updateKey)
        }
    }

    // ✅ Remove item
    async function removeItem(sku: string) {
        if (!sku?.trim()) throw new Error('SKU is required')

        const removeKey = `remove-${sku}`
        if (requestQueue.value.has(removeKey)) return

        try {
            requestQueue.value.add(removeKey)

            const hasCredentials = await ensureGuestCredentials()
            if (!hasCredentials && !isLoggedIn.value) {
                throw new Error('Unable to establish cart session')
            }

            const response = await useSanctumFetch(
                `${config.public.apiBase}/cart/remove/${encodeURIComponent(sku)}`,
                {
                    method: 'DELETE',
                    headers: getHeaders(),
                    retry: 1,
                    retryDelay: 500
                }
            )

            if (response?.data) {
                cartData.value = response.data
            }

        } catch (e: any) {
            console.error('[Cart] Remove error:', e)

            if (e.status === 419 || e.status === 403 || e.status === 401) {
                guestId.value = null
                guestToken.value = null
                guestTokenExpires.value = null
            }

            throw e
        } finally {
            requestQueue.value.delete(removeKey)
        }
    }

    // ✅ Apply coupon
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

            const hasCredentials = await ensureGuestCredentials()
            if (!hasCredentials && !isLoggedIn.value) {
                return { success: false, errors: 'Unable to establish session' }
            }

            const safeCode = encodeURIComponent(code.trim())
            const response = await useSanctumFetch(
                `${config.public.apiBase}/cart/coupon/${safeCode}`,
                {
                    method: 'POST',
                    headers: getHeaders(),
                    retry: 1,
                    retryDelay: 500
                }
            )

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

            if (e.status === 419 || e.status === 403 || e.status === 401) {
                guestId.value = null
                guestToken.value = null
                guestTokenExpires.value = null
            }

            return { success: false, errors: 'Failed to apply coupon. Please try again.' }
        } finally {
            requestQueue.value.delete(couponKey)
        }
    }

    // ✅ Computed properties matching backend structure
    const itemCount = computed(() => cartData.value.summary?.quantity || 0)
    const items = computed(() => cartData.value.items || [])
    const summary = computed(() => cartData.value.summary)
    const customer = computed(() => cartData.value.customer)
    const hasError = computed(() => !!cartData.value.error)
    const errorMessage = computed(() => cartData.value.error)

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

        // Methods
        fetchCart,
        addToCart,
        updateCartItem,
        removeItem,
        applyCoupon,
        ensureGuestCredentials
    }
}
