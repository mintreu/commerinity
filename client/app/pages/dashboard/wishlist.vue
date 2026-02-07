<script setup lang="ts">
definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const config = useRuntimeConfig()
const toast = useToast()

type WishlistItem = {
  id: number
  added_at?: string
  product?: {
    name: string
    slug: string
    sku?: string
    price: number
    price_formatted: string
    image?: { url?: string } | null
    in_stock?: boolean
    category?: { name?: string } | null
  }
}

const items = ref<WishlistItem[]>([])
const pagination = ref<{ current_page: number; last_page: number; per_page: number; total: number; has_more: boolean } | null>(null)
const loading = ref(false)

const fetchWishlist = async (page = 1) => {
  loading.value = true
  try {
    const response = await useSanctumFetch<any>(`${config.public.apiBase}/api/wishlist?page=${page}`)
    if (response?.success) {
      items.value = response.data.items || []
      pagination.value = response.data.pagination || null
    }
  } catch {
    toast.add({
      title: 'Error',
      description: 'Failed to load wishlist',
      color: 'error'
    })
  } finally {
    loading.value = false
  }
}

const removeItem = async (slug?: string) => {
  if (!slug) return
  try {
    const response = await useSanctumFetch<any>(`${config.public.apiBase}/api/wishlist/${slug}`, { method: 'DELETE' })
    if (response?.success) {
      toast.add({ title: 'Removed', description: 'Item removed from wishlist', color: 'success' })
      await fetchWishlist(pagination.value?.current_page || 1)
    }
  } catch {
    toast.add({ title: 'Error', description: 'Could not remove item', color: 'error' })
  }
}

onMounted(async () => {
  await fetchWishlist()
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8">
    <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center shadow-lg shadow-rose-500/30">
          <UIcon name="i-lucide-heart" class="w-6 h-6 sm:w-8 sm:h-8 text-white" />
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white">
            My Wishlist
          </h1>
          <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">
            Saved products you want to revisit
          </p>
        </div>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-10">
      <UIcon name="i-lucide-loader-2" class="w-7 h-7 animate-spin text-primary-500" />
    </div>

    <div v-else-if="items.length === 0" class="glass-card p-6 sm:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 text-center">
      <p class="text-sm text-slate-500">Your wishlist is empty.</p>
      <NuxtLink to="/shop" class="inline-block mt-4">
        <UButton color="primary" variant="soft">Browse Products</UButton>
      </NuxtLink>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
      <div
        v-for="item in items"
        :key="item.id"
        class="glass-card p-4 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10"
      >
        <NuxtLink :to="`/shop/product/${item.product?.slug}`" class="block">
          <div class="aspect-[4/3] bg-slate-100 dark:bg-slate-800 rounded-xl overflow-hidden mb-3">
            <img
              v-if="item.product?.image?.url"
              :src="item.product.image.url"
              :alt="item.product?.name || 'Product'"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-slate-400 text-sm">
              No image
            </div>
          </div>
          <h3 class="text-sm font-semibold text-slate-900 dark:text-white line-clamp-2">
            {{ item.product?.name || 'Product' }}
          </h3>
        </NuxtLink>

        <div class="mt-2 flex items-center justify-between">
          <div>
            <div class="text-base font-bold text-slate-900 dark:text-white">
              {{ item.product?.price_formatted || '₹0.00' }}
            </div>
            <div class="text-xs text-slate-500">
              {{ item.product?.category?.name || '' }}
            </div>
          </div>
          <UBadge :color="item.product?.in_stock ? 'success' : 'error'" size="xs">
            {{ item.product?.in_stock ? 'In Stock' : 'Out of Stock' }}
          </UBadge>
        </div>

        <div class="mt-4 flex gap-2">
          <NuxtLink :to="`/shop/product/${item.product?.slug}`" class="flex-1">
            <UButton block color="primary" variant="soft">View</UButton>
          </NuxtLink>
          <UButton
            color="error"
            variant="ghost"
            icon="i-lucide-trash-2"
            @click="removeItem(item.product?.slug)"
          />
        </div>
      </div>
    </div>

    <div v-if="pagination && pagination.last_page > 1" class="flex justify-center pt-2">
      <UPagination
        :model-value="pagination.current_page"
        :total="pagination.total"
        :page-count="pagination.per_page"
        @update:model-value="fetchWishlist"
      />
    </div>
  </div>
</template>
