<script setup lang="ts">
import { Swiper, SwiperSlide } from "swiper/vue";
import AddToCartButton from "~/components/cart/AddToCartButton.vue";
import 'swiper/css'
import { onMounted, ref, watch } from "vue";
import { useRuntimeConfig, useSanctumFetch } from "#imports";

const props = defineProps<{
  label: string,
  url: string,
  cta?: { label: string, url?: string, action?: () => void } // optional CTA
}>()

const config = useRuntimeConfig()
const items = ref<any[]>([])
const loading = ref(false)

async function fetchProducts() {
  try {
    loading.value = true
    const res = await useSanctumFetch(`${config.public.apiBase}${props.url}`)
    items.value = res.data || []
  } catch (e: any) {
    console.error('Product fetch error', e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchProducts)

// if `url` prop changes dynamically
watch(() => props.url, fetchProducts)
</script>

<template>
  <section v-if="items.length" class="mb-16">
    <div class="flex items-center justify-between mb-6 px-4">
      <h2 class="text-2xl font-bold">{{ props.label }}</h2>

      <!-- Optional CTA -->
      <button
          v-if="props.cta?.action"
          @click="props.cta.action"
          class="text-sm font-medium text-blue-600 hover:underline"
      >
        {{ props.cta.label }}
      </button>

      <NuxtLink
          v-else-if="props.cta?.url"
          :to="props.cta.url"
          class="text-sm font-medium text-blue-600 hover:underline"
      >
        {{ props.cta.label }}
      </NuxtLink>
    </div>

    <Swiper
        :slides-per-view="1"
        :space-between="20"
        :breakpoints="{
        640: { slidesPerView: 2 },
        768: { slidesPerView: 3 },
        1024: { slidesPerView: 4 },
        1280: { slidesPerView: 6 }
      }"
        class="my-6"
    >
      <SwiperSlide v-for="product in items" :key="product.url">
        <div class="border border-gray-200 dark:border-gray-700 rounded p-4 hover:shadow-lg transition">
          <NuxtLink :to="`/product/${product.url}`" class="block">
            <img
                loading="lazy"
                :src="product.thumbnail"
                class="h-[250px] w-full object-contain rounded mb-4"
            />
            <h3 class="font-semibold mb-1">{{ product.name }}</h3>
            <p class="text-sm text-gray-500 mb-1">${{ product.price }}</p>
            <p class="text-xs text-gray-400 mb-2">{{ product.short_description }}</p>
          </NuxtLink>

          <AddToCartButton
              :sku="product.sku"
              :quantity="1"
              class="mt-2 w-full"
          />
        </div>
      </SwiperSlide>
    </Swiper>
  </section>
</template>


