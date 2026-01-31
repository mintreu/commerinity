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
}

interface CartSummary {
  items_count: number
  total_quantity: number
  subtotal: number
  subtotal_formatted: string
}

interface CartState {
  items: CartProduct[]
  summary: CartSummary
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
        }
      }>(`${config.public.apiBase}/api/cart${query}`)

      if (response.success && response.data) {
        cartState.items = response.data.items || []
        cartState.summary = response.data.summary || {
          items_count: 0,
          total_quantity: 0,
          subtotal: 0,
          subtotal_formatted: '₹0.00'
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

    // Methods
    fetchCart,
    addToCart,
    updateQuantity,
    removeFromCart,
    clearCart,
    triggerFlyAnimation
  }
}
