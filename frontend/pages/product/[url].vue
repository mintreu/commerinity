<template>
  <div class="w-full h-full mt-16 px-4 lg:px-20">

    <!-- Loader -->
    <GlobalLoader v-if="isLoading"/>

    <div v-else>
      <div class="mt-14 mr-5">
        <CartCounter/>
      </div>

      <div v-if="product" class="max-w-7xl mx-auto px-5 py-8 h-full flex flex-col gap-4 bg-gray-200 dark:bg-gray-800 rounded-2xl">
        <!-- Product View Body-->

        <div class="flex flex-col md:flex-row gap-4">
          <!-- Media Preview-->
          <div class="w-full md:w-2/5 px-3 ">
            <ProductMediaSlider :media="product.banner"/>
          </div>

          <div class="grow">
            <!-- Product Information -->
            <h1 class="text-2xl md:text-6xl font-roboto font-semibold">{{ product.name }}</h1>
            <small>SKU : {{ product.sku }}</small>
            <!-- Category Details -->
            <div v-if="product.categories" class="md:flex flex-row gap-2 my-1 hidden  ">
              <span>Categories:</span>
              <div v-for="category in product.categories" :key="category.url" class="flex flex-row gap-1">
                <NuxtLink :to="`/category/${category.url}`">
                  <span class="px-3 py-0.5 bg-green-300 hover:bg-blue-600 hover:text-white rounded-xl shadow-md ">{{ category.name }}</span>
                </NuxtLink>
              </div>
            </div>
            <hr>
            <!-- Pricing Information -->
            <h2 class="text-xl font-semibold my-3 flex flex-row items-center gap-2">Price : <span class="text-3xl">{{ product.price }}</span></h2>

              <!-- Variants -->
            <div v-if="product.parent" class="w-full">
              <div v-if="product.parent.variants" class="w-full gap-3 flex flex-col ">
                <h2 class="text-lg font-semibold ">Available Options :</h2>
                  <div v-for="variant in product.parent.variants" :key="variant.url" class="w-full flex flex-row gap-3">
                      <NuxtLink :to="`/product/${variant.url}`">
                        <div>
                          <NuxtImg
                              loading="lazy"
                              :src="variant.thumbnail"
                              class="h-14 w-full object-contain rounded mb-4"
                          />
                        </div>
                      </NuxtLink>
                  </div>
              </div>
            </div>

            <!-- Available Offers Sales -->
            <div v-if="product.sales?.length" class="mt-6">
              <h3 class="text-base font-semibold mb-3 text-gray-900 dark:text-gray-100">
                Available Offers
              </h3>

              <ul class="space-y-3">
                <li
                    v-for="offer in product.sales"
                    :key="offer.sale.uuid"
                    class="flex items-start text-sm text-gray-700 dark:text-gray-300"
                >
                  <!-- Icon -->
                  <Icon
                      name="mdi:tag-outline"
                      class="flex-shrink-0 w-5 h-5 text-green-600 dark:text-green-400 mt-0.5"
                  />

                  <!-- Offer text -->
                  <span class="ml-2">
        <span class="font-medium text-green-600 dark:text-green-400">
          {{ formatOffer(offer) }}
        </span>
        <span class="ml-1 text-blue-600 dark:text-blue-400 cursor-pointer hover:underline">
          T&amp;C
        </span>
      </span>
                </li>
              </ul>
            </div>






            <!-- Add To Cart Cta-->
<!--            <AddToCartButton-->
<!--                :sku="product.sku"-->
<!--                :quantity="1"-->
<!--                class="mt-2 w-96"-->
<!--            />-->



            <AddToCartWithQuantitySelector
                :sku="product.sku"
                :min-quantity="product.min_quantity"
                :max-quantity="product.max_quantity"
                class="mt-2 w-96"
            />



            <!-- Product Short Description -->
            <div v-if="product.short_description" class="w-full mt-2">
              <h2 class="font-semibold text-xl">Overview :</h2>
              <p v-html="product.short_description"></p>
            </div>

            <!-- ./Product Information -->

          </div>
        </div>

        <div class="w-full">
          <h2 class="font-semibold text-2xl">Description</h2>
          <FilamentTipTapContent :text="product.description"/>
        </div>

        <!-- ./Product View Body-->

      </div>

    </div>

  </div>
</template>

<script setup>
import CartCounter from "~/components/cart/CartCounter.vue";
import GlobalLoader from "~/components/GlobalLoader.vue"
import {onMounted, ref} from "vue";
import {useRoute, useRuntimeConfig, useSanctumFetch} from "#imports";
import ProductMediaSlider from "~/components/sliders/ProductMediaSlider.vue";
import FilamentTipTapContent from "~/components/FilamentTipTapContent.vue";
import {SwiperSlide} from "swiper/vue";
import AddToCartButton from "~/components/cart/AddToCartButton.vue";
import AddToCartWithQuantitySelector from "~/components/store/buttons/AddToCartWithQuantitySelector.vue";

const route = useRoute()
const config = useRuntimeConfig()
const isLoading = useState('pageLoading', () => false)
const product = ref(null)

async function fetchProductDetail() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/products/${route.params.url}`, {
      method: 'GET'
    })
    product.value = res?.data ?? []
  } catch (error) {
    console.error('[✘] Failed to load suggestion products', error)
  }
}




onMounted(async () => {

  try {

    await fetchProductDetail()
  } catch (e) {
    console.error('Api fetch failed!', e)
  } finally {
    isLoading.value = false
  }
})


// Function to format the offer based on discount_type
const formatOffer = (offer) => {
  if (!offer) return ""

  // Determine discount amount to show
  let discountAmount = offer.discount ?? ""

  // Append % if discount type is percent
  if (offer.discount_type === "by_percent" || offer.discount_type === "to_percent") {
    discountAmount += "%"
  }

  // Build final offer string
  return `${offer.sale.name} Offer ${discountAmount} off ${offer.sale.description}`
}




</script>

<style scoped>
/* Add custom styles if needed */
</style>
