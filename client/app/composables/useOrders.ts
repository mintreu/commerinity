/**
 * Orders Composable
 * Handles order state and API interactions for e-commerce orders
 */

export interface OrderItem {
  id: string
  product_name: string
  product_slug: string
  quantity: number
  unit_price: number
  unit_price_formatted: string
  subtotal: number
  subtotal_formatted: string
  image?: string
}

export interface Order {
  uuid: string
  order_number: string
  status: string
  status_label: string
  status_color: string
  subtotal: number
  subtotal_formatted: string
  shipping_cost: number
  shipping_cost_formatted: string
  tax: number
  tax_formatted: string
  discount: number
  discount_formatted: string
  total: number
  total_formatted: string
  quantity: number
  payment_success: boolean
  payment_status: string
  tracking_id?: string
  shipped_at?: string
  delivered_at?: string
  created_at: string
  created_at_formatted: string
  items: OrderItem[]
}

export interface OrdersResponse {
  success: boolean
  data: Order[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export const useOrders = () => {
  const config = useRuntimeConfig()

  const orders = ref<Order[]>([])
  const currentOrder = ref<Order | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  const meta = ref<OrdersResponse['meta'] | null>(null)

  // Fetch all orders for the current user
  const fetchOrders = async (page = 1, perPage = 10) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await useSanctumFetch<OrdersResponse>(
        `${config.public.apiBase}/api/orders?page=${page}&per_page=${perPage}`
      )

      if (response.success) {
        orders.value = response.data
        meta.value = response.meta
      }

      return response
    } catch (e: unknown) {
      error.value = e instanceof Error ? e.message : 'Failed to fetch orders'
      return null
    } finally {
      isLoading.value = false
    }
  }

  // Fetch single order details
  const fetchOrder = async (uuid: string) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await useSanctumFetch<{ success: boolean, data: Order }>(
        `${config.public.apiBase}/api/orders/${uuid}`
      )

      if (response.success) {
        currentOrder.value = response.data
      }

      return response
    } catch (e: unknown) {
      error.value = e instanceof Error ? e.message : 'Failed to fetch order'
      return null
    } finally {
      isLoading.value = false
    }
  }

  // Get recent orders (limit 5) for dashboard
  const fetchRecentOrders = async (limit = 5) => {
    try {
      const response = await fetchOrders(1, limit)
      return response?.data || []
    } catch {
      return []
    }
  }

  // Get order count by status
  const getOrderStats = async () => {
    try {
      const response = await useSanctumFetch<{
        success: boolean
        data: {
          total: number
          pending: number
          processing: number
          shipped: number
          delivered: number
          completed: number
          cancelled: number
        }
      }>(`${config.public.apiBase}/api/orders/stats`)

      if (response.success) {
        return response.data
      }

      return {
        total: 0,
        pending: 0,
        processing: 0,
        shipped: 0,
        delivered: 0,
        completed: 0,
        cancelled: 0
      }
    } catch {
      return {
        total: 0,
        pending: 0,
        processing: 0,
        shipped: 0,
        delivered: 0,
        completed: 0,
        cancelled: 0
      }
    }
  }

  // Helper to format status colors
  const getStatusColor = (status: string): 'success' | 'warning' | 'info' | 'error' | 'neutral' => {
    const colors: Record<string, 'success' | 'warning' | 'info' | 'error' | 'neutral'> = {
      pending: 'warning',
      confirmed: 'info',
      processing: 'info',
      shipped: 'info',
      delivered: 'success',
      completed: 'success',
      cancelled: 'error',
      refunded: 'error'
    }
    return colors[status] || 'neutral'
  }

  // Helper to format status icons
  const getStatusIcon = (status: string): string => {
    const icons: Record<string, string> = {
      pending: 'i-lucide-hourglass',
      confirmed: 'i-lucide-check-circle',
      processing: 'i-lucide-loader-circle',
      shipped: 'i-lucide-truck',
      delivered: 'i-lucide-package-check',
      completed: 'i-lucide-trophy',
      cancelled: 'i-lucide-x-circle',
      refunded: 'i-lucide-rotate-ccw'
    }
    return icons[status] || 'i-lucide-circle'
  }

  return {
    // State
    orders: readonly(orders),
    currentOrder: readonly(currentOrder),
    isLoading: readonly(isLoading),
    error: readonly(error),
    meta: readonly(meta),

    // Methods
    fetchOrders,
    fetchOrder,
    fetchRecentOrders,
    getOrderStats,
    getStatusColor,
    getStatusIcon
  }
}
