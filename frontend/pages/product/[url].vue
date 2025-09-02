<template>
  <div class="w-full h-full mt-16 px-4 lg:px-20">

    <!-- Loader -->
    <GlobalLoader v-if="isLoading"/>

    <div v-else>
      <div class="mt-14 mr-5">
        <CartCounter/>
      </div>

      <div v-if="product" class="max-w-7xl mx-auto px-5 py-8 h-full flex flex-col gap-4 bg-gray-400 dark:bg-gray-800 rounded-2xl">
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
            <div v-if="product.categories" class="md:flex flex-row gap-3 my-1 hidden  ">
              <span>Categories:</span>
              <div v-for="category in product.categories" :key="category.url" class="flex flex-row gap-2">
                <NuxtLink :to="`/category/${category.url}`">
                  <span class="px-3 py-0.5 bg-gray-600 hover:bg-blue-600 rounded-2xl">{{ category.name }}</span>
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

async function fetchSuggestionProducts() {
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

    await fetchSuggestionProducts()
  } catch (e) {
    console.error('Api fetch failed!', e)
  } finally {
    isLoading.value = false
  }
})


</script>

<style scoped>
/* Add custom styles if needed */
</style>
