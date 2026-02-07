/**
 * Cart Composable
 * Manages shopping cart state and provides "fly to cart" animation
 * Performance-optimized: Uses CSS transforms (GPU-accelerated)
 */

interface CartProduct {
  product_slug: string
  name: string
  sku: string
  quantity: number
  price: number
  price_formatted: string
  subtotal: number
  subtotal_formatted: string
  image?: string | null
  tax?: number
  tax_formatted?: string
  discount?: number
  bv?: number
  pv?: number
  reward_points?: number
}

interface CartSummary {
  items_count: number
  total_quantity: number
  subtotal: number
  subtotal_formatted: string
}

interface CartMetaSummary {
  sub_total: number
  original_sub_total?: number
  shipping_cost: number
  tax: number
  discount: number
  sale_discount?: number
  voucher_discount?: number
  total_discount?: number
  coins?: number
  coins_required?: number
  total: number
  quantity: number
  coupon_applied?: boolean
  coupon_code?: string | null
  formatted?: {
    sub_total?: string
    subtotal?: string
    shipping_cost?: string
    tax?: string
    discount?: string
    total?: string
  }
}

interface CartMeta {
  summary?: CartMetaSummary
  items?: any[]
  customer?: any
  tax_breakdown?: any[]
  gift_options?: Array<{ value: string, label: string }>
  voucher_details?: {
    name?: string
    code?: string
    action_type?: string
    applies_to_shipping?: boolean
    free_shipping?: boolean
  } | null
  voucher_validation?: { valid: boolean, message?: string }
}

interface CartState {
  items: CartProduct[]
  summary: CartSummary
  meta: CartMeta | null
  isGuest: boolean
  loading: boolean
}

// Global cart state
const cartState = reactive<CartState>({
  items: [],
  summary: {
    items_count: 0,
    total_quantity: 0,
    subtotal: 0,
    subtotal_formatted: '₹0.00'
  },
  meta: null,
  isGuest: true,
  loading: false
})

// Animation state
const flyingItems = ref<Array<{
  id: string
  x: number
  y: number
  targetX: number
  targetY: number
  image?: string
}>>([])

export const useCart = () => {
  const config = useRuntimeConfig()
  const toast = useToast()
  const { formatCurrency } = useBranding()

  /**
   * Fetch cart from API
   */
  const fetchCart = async (options?: { shippingAddressId?: string | null }) => {
    cartState.loading = true
    try {
      const query = options?.shippingAddressId
        ? `?shipping_address_id=${encodeURIComponent(options.shippingAddressId)}`
        : ''
      const response = await useSanctumFetch<{
        success: boolean
        data: {
          items: CartProduct[]
          summary: CartSummary
          is_guest: boolean
          meta?: CartMeta
        }
      }>(`${config.public.apiBase}/api/cart${query}`)

      if (response.success && response.data) {
        cartState.meta = response.data.meta || null
        const metaItems = response.data.meta?.items || []

        cartState.items = metaItems.length > 0
          ? metaItems.map((item: any) => ({
              product_slug: item.product?.url || item.product_slug || '',
              name: item.product_name || item.product?.name || '',
              sku: item.product_sku || item.product?.sku || '',
              quantity: item.requested_quantity || item.quantity || 0,
              price: item.unit_price || 0,
              price_formatted: formatCurrency((item.unit_price || 0) / 100),
              subtotal: item.item_total || 0,
              subtotal_formatted: formatCurrency((item.item_total || 0) / 100),
              image: item.product?.thumbnail || item.image || null,
              tax: item.item_tax || 0,
              tax_formatted: formatCurrency((item.item_tax || 0) / 100),
              discount: item.summary?.discount || 0,
              bv: item.bv || 0,
              pv: item.pv || 0,
              reward_points: item.reward_points || 0
            }))
          : (response.data.items || [])

        cartState.summary = response.data.summary || {
          items_count: 0,
          total_quantity: 0,
          subtotal: 0,
          subtotal_formatted: '₹0.00'
        }
        if (response.data.meta?.summary) {
          const metaSummary = response.data.meta.summary
          cartState.summary.subtotal = metaSummary.sub_total || 0
          cartState.summary.subtotal_formatted = metaSummary.formatted?.sub_total
            || metaSummary.formatted?.subtotal
            || formatCurrency((metaSummary.sub_total || 0) / 100)
        }
        cartState.isGuest = response.data.is_guest ?? true
      }
    } catch {
      // Silent fail for guests
    } finally {
      cartState.loading = false
    }
  }

  /**
   * Add item to cart with optional fly animation
   */
  const addToCart = async (
    productSlug: string,
    quantity: number = 1,
    options?: {
      productName?: string
      productImage?: string
      sourceElement?: HTMLElement | null
      skipAnimation?: boolean
    }
  ): Promise<boolean> => {
    // Trigger fly animation if source element provided
    if (options?.sourceElement && !options?.skipAnimation) {
      triggerFlyAnimation(options.sourceElement, options.productImage)
    }

    try {
      const response = await useSanctumFetch<{
        success: boolean
        message: string
        data?: { items_count: number, total_quantity: number }
      }>(`${config.public.apiBase}/api/cart`, {
        method: 'POST',
        body: { product_slug: productSlug, quantity }
      })

      if (response.success) {
        // Update cart count immediately
        if (response.data) {
          cartState.summary.items_count = response.data.items_count
          cartState.summary.total_quantity = response.data.total_quantity
        }

        toast.add({
          title: 'Added to Cart',
          description: options?.productName
            ? `${options.productName} added to cart`
            : 'Item added to cart',
          color: 'success',
          icon: 'i-lucide-shopping-cart'
        })

        return true
      } else {
        toast.add({
          title: 'Error',
          description: response.message || 'Failed to add to cart',
          color: 'error',
          icon: 'i-lucide-alert-circle'
        })
        return false
      }
    } catch (error: unknown) {
      const message = error instanceof Error ? error.message : 'Failed to add to cart'
      toast.add({
        title: 'Error',
        description: message,
        color: 'error',
        icon: 'i-lucide-alert-circle'
      })
      return false
    }
  }

  /**
   * Update cart item quantity
   */
  const updateQuantity = async (
    productSlug: string,
    quantity: number,
    options?: { shippingAddressId?: string | null }
  ): Promise<boolean> => {
    try {
      const response = await useSanctumFetch<{
        success: boolean
        message?: string
        data?: { items_count: number, total_quantity: number }
      }>(`${config.public.apiBase}/api/cart/${productSlug}`, {
        method: 'PATCH',
        body: { quantity }
      })

      if (response.success) {
        // Update counts
        if (response.data) {
          cartState.summary.items_count = response.data.items_count
          cartState.summary.total_quantity = response.data.total_quantity
        }
        // Refetch to get updated items
        await fetchCart(options)
        return true
      }
      return false
    } catch {
      return false
    }
  }

  /**
   * Remove item from cart
   */
  const removeFromCart = async (
    productSlug: string,
    options?: { shippingAddressId?: string | null }
  ): Promise<boolean> => {
    try {
      const response = await useSanctumFetch<{
        success: boolean
        message?: string
        data?: { items_count: number, total_quantity: number }
      }>(`${config.public.apiBase}/api/cart/${productSlug}`, {
        method: 'DELETE'
      })

      if (response.success) {
        // Update counts
        if (response.data) {
          cartState.summary.items_count = response.data.items_count
          cartState.summary.total_quantity = response.data.total_quantity
        }
        // Refetch to get updated items
        await fetchCart(options)
        toast.add({
          title: 'Removed',
          description: 'Item removed from cart',
          color: 'neutral',
          icon: 'i-lucide-trash-2'
        })
        return true
      }
      return false
    } catch {
      return false
    }
  }

  /**
   * Trigger fly-to-cart animation
   * Uses CSS transforms for GPU acceleration (high Lighthouse score)
   */
  const triggerFlyAnimation = (sourceElement: HTMLElement, image?: string) => {
    if (!import.meta.client) return

    const sourceRect = sourceElement.getBoundingClientRect()

    // Find cart icon in navbar or bottom nav
    const cartIcon = document.querySelector('[data-cart-target]')
      || document.querySelector('[href*="/cart"]')
      || document.querySelector('.cart-icon')

    if (!cartIcon) {
      // No cart icon found, skip animation
      return
    }

    const targetRect = cartIcon.getBoundingClientRect()

    const flyItem = {
      id: `fly-${Date.now()}`,
      x: sourceRect.left + sourceRect.width / 2,
      y: sourceRect.top + sourceRect.height / 2,
      targetX: targetRect.left + targetRect.width / 2,
      targetY: targetRect.top + targetRect.height / 2,
      image
    }

    flyingItems.value.push(flyItem)

    // Remove after animation completes
    setTimeout(() => {
      flyingItems.value = flyingItems.value.filter(item => item.id !== flyItem.id)
    }, 800)
  }

  /**
   * Clear entire cart
   */
  const clearCart = async (): Promise<boolean> => {
    try {
      const response = await useSanctumFetch<{ success: boolean }>(`${config.public.apiBase}/api/cart/clear`, {
        method: 'POST'
      })

      if (response.success) {
        cartState.items = []
        cartState.summary = {
          items_count: 0,
          total_quantity: 0,
          subtotal: 0,
          subtotal_formatted: '₹0.00'
        }
        return true
      }
      return false
    } catch {
      return false
    }
  }

  const applyCoupon = async (code: string, options?: { shippingAddressId?: string | null }): Promise<boolean> => {
    try {
      const response = await useSanctumFetch<{
        success: boolean
        message?: string
        data?: { meta?: CartMeta }
      }>(`${config.public.apiBase}/api/cart/coupon`, {
        method: 'POST',
        body: { code, shipping_address_id: options?.shippingAddressId || null }
      })

      if (response.success) {
        if (response.data?.meta) {
          cartState.meta = response.data.meta
        }
        await fetchCart(options)
        toast.add({
          title: 'Coupon Applied',
          description: response.message || 'Coupon applied successfully',
          color: 'success'
        })
        return true
      }
      toast.add({
        title: 'Coupon Error',
        description: response.message || 'Unable to apply coupon',
        color: 'error'
      })
      return false
    } catch {
      toast.add({
        title: 'Coupon Error',
        description: 'Unable to apply coupon',
        color: 'error'
      })
      return false
    }
  }

  const removeCoupon = async (options?: { shippingAddressId?: string | null }): Promise<boolean> => {
    try {
      const response = await useSanctumFetch<{
        success: boolean
        message?: string
        data?: { meta?: CartMeta }
      }>(`${config.public.apiBase}/api/cart/coupon`, {
        method: 'DELETE',
        body: { shipping_address_id: options?.shippingAddressId || null }
      })

      if (response.success) {
        if (response.data?.meta) {
          cartState.meta = response.data.meta
        }
        await fetchCart(options)
        toast.add({
          title: 'Coupon Removed',
          description: response.message || 'Coupon removed',
          color: 'neutral'
        })
        return true
      }
      return false
    } catch {
      return false
    }
  }

  return {
    // State
    cart: readonly(cartState),
    cartCount: computed(() => cartState.summary.items_count),
    cartTotalQuantity: computed(() => cartState.summary.total_quantity),
    cartTotal: computed(() => cartState.summary.subtotal),
    cartTotalFormatted: computed(() => cartState.summary.subtotal_formatted),
    cartItems: computed(() => cartState.items),
    isCartLoading: computed(() => cartState.loading),
    isGuest: computed(() => cartState.isGuest),
    flyingItems: readonly(flyingItems),
    cartMeta: computed(() => cartState.meta),

    // Methods
    fetchCart,
    addToCart,
    updateQuantity,
    removeFromCart,
    applyCoupon,
    removeCoupon,
    clearCart,
    triggerFlyAnimation
  }
}
