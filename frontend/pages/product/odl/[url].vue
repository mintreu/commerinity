<template>
  <div class="w-full h-full">
    <!-- Place Cart Button -->
    <div class="mt-14 mr-5">
      <CartCounter />
    </div>

    <h2 class="text-5xl text-red-500"> Product View Page</h2>

    <!-- Loader -->
    <GlobalLoader v-if="isLoading" />

    <div v-else class="w-full h-full">


        <!-- Product View -->
      <div v-if="product" class="container mx-auto px-4 py-10 flex flex-col gap-3">

        <div class="flex flex-row gap-4">
          <div class="w-full md:w-1/3">
            <NuxtImg
                loading="lazy"
                :src="product.banner"
                class="h-full md:h-[650px] w-full object-cover rounded mb-4"
            />
          </div>

          <div class="grow flex flex-col gap-4">
            <h1 class="text-2xl md:text-6xl font-roboto font-semibold">{{product.name}}</h1>
          </div>


        </div>



      </div>
        <!-- Product View -->

      <div v-else class="flex flex-col justify-center items-center ">
        <h1 class="text-xl my-8 text-blue-500">Fetching data...</h1>
      </div>
    </div>



  </div>
</template>

<script setup>

import {ref, computed, watch, onMounted} from 'vue'
import {useRoute, useRuntimeConfig, useFetch, useToast, useSanctumFetch} from '#imports'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import AddToCartButton from "~/components/cart/AddToCartButton.vue";
import CartCounter from "~/components/cart/CartCounter.vue";
import GlobalLoader from "~/components/GlobalLoader.vue"

const route = useRoute()
const config = useRuntimeConfig()
const isLoading = useState('pageLoading', () => false)
const toast = useToast()
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




//
// const { data, pending, error } = await useFetch(`${config.public.apiBase}/products/${route.params.url}`)
//
// // watch(data, (val) => console.log('Fetched data:', val), { immediate: true })
// // watch(product, (val) => console.log('Product ref:', val), { immediate: true })
//
// if (data.value && data.value.data) {
//   product.value = data.value.data
// }

// Computed images array
// const images = computed(() => {
//   const p = product.value
//   if (!p) return []
//   console.log('Computed product.images from banner:', p.banner)
//   const bannerArr = Array.isArray(p.banner) ? p.banner : p.banner ? [p.banner] : []
//   const result = [p.thumbnail, ...bannerArr].filter(Boolean)
//   console.log('Computed images:', result)
//   return result
// })

//watch(images, (imgs) => console.log('Images array:', imgs), { immediate: true })


// Handle user clicking on a variant option
// function handleOptionClick(filterName, option) {
//   console.log('Clicked:', filterName, option)
//   // TODO: Add logic if needed to change variant or navigate
// }
//
// // Check if current variant has this option active
// function isActiveOption(filterName, option) {
//   return product.value?.filter_option?.some(
//       opt => opt.filter.name === filterName && opt.value === option
//   )
// }



</script>

<style scoped>
/* Add custom styles if needed */
</style>
