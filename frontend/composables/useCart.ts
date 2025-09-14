// composables/useCart.ts
import { useSanctum, useSanctumFetch, useRuntimeConfig, useCookie } from '#imports'

interface CartItem {
    product_id: number
    product: {
        sku: string
        name: string
        price: string
        thumbnail: string
        min_quantity: number
        max_quantity: number
    }
    quantity: number
    total: string
}

interface CartSummary {
    sub_total: string
    tax: string
    discount: string
    total: string
    quantity: number
    coupon_applied: boolean
}

interface CartResponse {
    items: CartItem[]
    summary: CartSummary
    customer: any
}

export const useCart = () => {
    const { isLoggedIn } = useSanctum()
    const config = useRuntimeConfig()

    // ✅ shared global state
    const cartData = useState<CartResponse>('cartData', () => ({
        items: [],
        summary: {
            sub_total: '₹0.00',
            tax: '₹0.00',
            discount: '₹0.00',
            total: '₹0.00',
            quantity: 0,
            coupon_applied: false
        },
        customer: {}
    }))

    const loading = useState<boolean>('cartLoading', () => false)

    // guest credentials
    const guestId = useCookie('guest_id')
    const guestToken = useCookie('guest_token')
    const guestTokenExpires = useCookie('guest_token_expires')

    // ✅ always returns proper HeadersInit
    const getGuestHeaders = (): HeadersInit => {
        if (!isLoggedIn.value && guestId.value && guestToken.value) {
            return {
                'x-guest-id': guestId.value as string,
                'x-guest-token': guestToken.value as string
            }
        }
        return {}
    }

    async function validateOrInitGuest() {
        const expired =
            guestTokenExpires.value &&
            Date.now() > new Date(guestTokenExpires.value).getTime()
        const missing = !guestId.value || !guestToken.value

        if (isLoggedIn.value) return

        if (expired || missing) {
            try {
                const res = await useSanctumFetch(
                    `${config.public.apiBase}/cart/guest-credential`,
                    { method: 'POST' }
                )
                const data = res?.data || {}
                guestId.value = data.guest_id
                guestToken.value = data.guest_token
                guestTokenExpires.value = data.expires_at
            } catch (e) {
                console.error('[Cart] Failed to init guest credentials', e)
            }
        }
    }

    async function fetchCart() {
        if (loading.value) return
        loading.value = true
        try {
            await validateOrInitGuest()
            const res = await useSanctumFetch(`${config.public.apiBase}/cart`, {
                method: 'GET',
                headers: getGuestHeaders()
            })
            cartData.value = res?.data || cartData.value
        } catch (e) {
            console.error('[Cart] Fetch error', e)
        } finally {
            loading.value = false
        }
    }

    async function addToCart(sku: string, quantity = 1) {
        try {
            const res = await useSanctumFetch(
                `${config.public.apiBase}/cart/add/${sku}`,
                {
                    method: 'POST',
                    headers: getGuestHeaders(),
                    body: { quantity }
                }
            )
            cartData.value = res?.data || cartData.value
        } catch (e) {
            console.error('[Cart] Add error', e)
        }
    }

    async function updateCartItem(sku: string, quantity: number) {
        try {
            const res = await useSanctumFetch(
                `${config.public.apiBase}/cart/update/${sku}`,
                {
                    method: 'POST',
                    headers: getGuestHeaders(),
                    body: { quantity }
                }
            )
            cartData.value = res?.data || cartData.value
        } catch (e) {
            console.error('[Cart] Update error', e)
        }
    }

    async function removeItem(sku: string) {
        try {
            const res = await useSanctumFetch(
                `${config.public.apiBase}/cart/remove/${sku}`,
                {
                    method: 'DELETE',
                    headers: getGuestHeaders()
                }
            )
            cartData.value = res?.data || cartData.value
        } catch (e) {
            console.error('[Cart] Remove error', e)
        }
    }

    async function applyCoupon(code: string) {
        try {
            const res = await useSanctumFetch(
                `${config.public.apiBase}/cart/coupon/${code}`,
                {
                    method: 'POST',
                    headers: getGuestHeaders()
                }
            )
            cartData.value = res?.data || cartData.value
        } catch (e) {
            console.error('[Cart] Coupon error', e)
        }
    }

    // handy computed
    const itemCount = computed(() => cartData.value.summary?.quantity || 0)
    const items = computed(() => cartData.value.items || [])

    return {
        cartData,
        itemCount,
        items,
        fetchCart,
        addToCart,
        updateCartItem,
        removeItem,
        applyCoupon,
        loading
    }
}
