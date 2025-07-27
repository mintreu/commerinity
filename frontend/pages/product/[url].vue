<template>
  <div>
    <div v-if="pending" class="p-10 text-center text-gray-500 dark:text-gray-400">
      Loading product…
    </div>
    <div v-else-if="error" class="p-10 text-center text-red-600 dark:text-red-400">
      Failed to load product.
    </div>
    <div v-else-if="!product" class="p-10 text-center text-gray-500 dark:text-gray-400">
      Product not found.
    </div>

    <div v-else class="container mx-auto px-4 py-10">



      <div class="flex flex-col md:flex-row gap-12">
        <!-- Image Gallery -->
        <div class="w-full md:w-1/3">
          <div v-if="images.length" class="overflow-hidden rounded-xl shadow-lg">
            <Swiper :slides-per-view="1" :space-between="10" navigation pagination>
              <SwiperSlide v-for="(img, i) in images" :key="i">
                <img :src="img" class="w-full h-60 object-cover" alt="Product Image" />
              </SwiperSlide>
            </Swiper>
          </div>
        </div>

        <!-- Info + Filter Section -->
        <div class="w-full md:w-2/3">
          <h2 class="text-3xl font-bold mb-4">{{ product.name }}</h2>

          <!-- Product Variant Options Section -->
          <!-- Variant Thumbnails Grid -->
          <section v-if="(product.hasParent && product.parent?.variants) || (product.type === 'Configurable' && product.variants)" class="mb-6">
            <div class="flex flex-wrap gap-4 justify-start">
              <NuxtLink
                  v-for="variant in product.hasParent ? product.parent.variants : product.variants"
                  :key="variant.url"
                  :to="'/products/' + variant.url"
                  class="relative border rounded-lg overflow-hidden w-24 h-24 flex items-center justify-center transition-all"
                  :class="{
        'ring-2 ring-blue-500': product.url === variant.url,
        'hover:ring-1 hover:ring-gray-400': product.url !== variant.url
      }"
              >
                <img
                    :src="variant.thumbnail || 'https://via.placeholder.com/100'"
                    :alt="variant.name"
                    class="object-cover w-full h-full"
                />
              </NuxtLink>
            </div>
          </section>



<!--          Product Filter Options With Variant-->

          <!-- Variant Product: child variant, pull variants from parent -->
          <section v-if="product.hasParent" class="p-4">
            <div class="grid grid-cols-2 gap-4">
              <template
                  v-for="filterName in [...new Set(
        product.parent?.variants?.flatMap(v =>
          v.filter_option?.map(opt => opt.filter.name)
        ) || []
      )]"
                  :key="'parent-' + filterName"
              >
                <!-- Filter Name -->
                <div class="font-semibold border p-2 bg-gray-100">
                  {{ filterName }}
                </div>

                <!-- Filter Options -->
                <div class="border p-2 flex flex-wrap gap-2">
                  <div
                      v-for="option in [...new Set(
            product.parent?.variants?.flatMap(v =>
              v.filter_option
                ?.filter(opt => opt.filter.name === filterName)
                .map(opt => opt.value)
            ) || []
          )]"
                      :key="'parent-' + filterName + '-' + option"
                      class="cursor-pointer px-3 py-1 border rounded text-sm hover:bg-blue-100"
                      :class="{
            'bg-blue-500 text-white': isActiveOption(filterName, option)
          }"
                      @click="handleOptionClick(filterName, option)"
                  >
                    {{ option }}
                  </div>
                </div>
              </template>
            </div>
          </section>


          <!-- Configurable Product -->
          <section v-else-if="product.type === 'Configurable'" class="p-4">

            <!--          Currently Work For Only Configurable Products-->
<!--            <section v-if="product.variants" class="p-4">-->
              <div class="grid grid-cols-2 gap-4">
                <template
                    v-for="filterName in [...new Set(product.variants.flatMap(v => v.filter_option.map(opt => opt.filter.name)))]"
                    :key="filterName"
                >
                  <!-- Filter Name -->
                  <div class="font-semibold border p-2 bg-gray-100">
                    {{ filterName }}
                  </div>

                  <!-- Unique Clickable Options -->
                  <div class="border p-2 flex flex-wrap gap-2">
                    <div
                        v-for="option in [...new Set(product.variants.flatMap(v =>
            v.filter_option
              .filter(opt => opt.filter.name === filterName)
              .map(opt => opt.value)
          ))]"
                        :key="filterName + '-' + option"
                        class="cursor-pointer px-3 py-1 border rounded hover:bg-blue-100 text-sm"
                        @click="handleOptionClick(filterName, option)"
                    >
                      {{ option }}
                    </div>
                  </div>
                </template>
              </div>
<!--            </section>-->

          </section>

          <!-- Simple Product -->
          <section v-else-if="product.type === 'Simple'" class="p-4">
            <div class="grid grid-cols-2 gap-4">
              <template
                  v-for="filterName in [...new Set(
        product.filter_option?.map(opt => opt.filter.name) || []
      )]"
                  :key="'simple-' + filterName"
              >
                <div class="font-semibold border p-2 bg-gray-100">{{ filterName }}</div>
                <div class="border p-2 flex flex-wrap gap-2">
                  <div
                      v-for="option in [...new Set(
            product.filter_option
              ?.filter(opt => opt.filter.name === filterName)
              .map(opt => opt.value) || []
          )]"
                      :key="'simple-' + filterName + '-' + option"
                      class="cursor-pointer px-3 py-1 border rounded hover:bg-blue-100 text-sm"
                      @click="handleOptionClick(filterName, option)"
                  >
                    {{ option }}
                  </div>
                </div>
              </template>
            </div>
          </section>


          <!--         ./ Product Filter Options With Variant-->















        </div>
      </div>

      <!-- Description -->
      <div class="mt-10">
        <h3 class="text-lg font-bold mb-2">Description</h3>
        <div v-html="product.description" class="prose prose-sm text-gray-700 dark:text-gray-300"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute, useRuntimeConfig, useFetch } from '#imports'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'

const route = useRoute()
const apiUrl = useRuntimeConfig().public.apiBase

const product = ref(null)
const { data, pending, error } = await useFetch(`${apiUrl}/products/${route.params.url}`)

watch(data, (val) => console.log('Fetched data:', val), { immediate: true })
watch(product, (val) => console.log('Product ref:', val), { immediate: true })

if (data.value && data.value.data) {
  product.value = data.value.data
}

// Computed images array
const images = computed(() => {
  const p = product.value
  if (!p) return []
  console.log('Computed product.images from banner:', p.banner)
  const bannerArr = Array.isArray(p.banner) ? p.banner : p.banner ? [p.banner] : []
  const result = [p.thumbnail, ...bannerArr].filter(Boolean)
  console.log('Computed images:', result)
  return result
})

watch(images, (imgs) => console.log('Images array:', imgs), { immediate: true })


// Handle user clicking on a variant option
function handleOptionClick(filterName, option) {
  console.log('Clicked:', filterName, option)
  // TODO: Add logic if needed to change variant or navigate
}

// Check if current variant has this option active
function isActiveOption(filterName, option) {
  return product.value?.filter_option?.some(
      opt => opt.filter.name === filterName && opt.value === option
  )
}


</script>

<style scoped>
/* Add custom styles if needed */
</style>
