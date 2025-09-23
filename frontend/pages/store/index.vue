<template>
<!--  <div class="w-full text-black dark:text-white bg-white dark:bg-black">-->

<!--    &lt;!&ndash; Hero Section &ndash;&gt;-->
<!--    <section class="w-full h-[400px] bg-gradient-to-r from-blue-500 to-purple-600 text-white flex items-center justify-center text-center p-8">-->
<!--      <div>-->
<!--        <h1 class="text-4xl md:text-6xl font-bold mb-4">Welcome to ShopMaster</h1>-->
<!--        <p class="text-lg md:text-xl mb-6">Your one-stop shop for everything!</p>-->
<!--        <button class="px-6 py-3 bg-white text-black font-semibold rounded hover:bg-gray-200 transition-all">-->
<!--          Shop Now-->
<!--        </button>-->
<!--      </div>-->
<!--    </section>-->

<!--    &lt;!&ndash; Category Sections &ndash;&gt;-->
<!--    <section-->
<!--        v-for="(category, index) in categorySections"-->
<!--        :key="index"-->
<!--        class="py-10 px-4 md:px-12"-->
<!--    >-->
<!--      <div class="bg-gray-100 dark:bg-gray-900 rounded-xl shadow-lg p-6">-->
<!--        <h2 class="text-2xl font-bold mb-4 text-black dark:text-white">Best of {{ category.name }}</h2>-->

<!--        <Swiper-->
<!--            :slides-per-view="2"-->
<!--            :space-between="16"-->
<!--            :breakpoints="{-->
<!--            640: { slidesPerView: 3 },-->
<!--            768: { slidesPerView: 4 },-->
<!--            1024: { slidesPerView: 6 }-->
<!--          }"-->
<!--            class="my-4"-->
<!--        >-->
<!--          <SwiperSlide-->
<!--              v-for="(child, idx) in category.children"-->
<!--              :key="idx"-->
<!--          >-->
<!--            <NuxtLink-->
<!--                :to="`/category/${child.url}`"-->
<!--                class="block bg-white dark:bg-gray-800 rounded-lg p-4 shadow-md hover:shadow-lg transition-all h-full"-->
<!--            >-->
<!--              <img-->
<!--                  :src="child.image"-->
<!--                  :alt="child.name"-->
<!--                  class="w-full h-32 object-cover rounded-md mb-3"-->
<!--              />-->
<!--              <p class="font-medium text-sm text-center text-black dark:text-white mb-1">{{ child.name }}</p>-->
<!--              <p class="text-center text-blue-600 text-base font-semibold">From ₹{{ child.starting_from_price }}</p>-->
<!--            </NuxtLink>-->
<!--          </SwiperSlide>-->
<!--        </Swiper>-->
<!--      </div>-->
<!--    </section>-->

<!--    &lt;!&ndash; Promo Banner &ndash;&gt;-->
<!--    <section class="my-12 px-4 md:px-12">-->
<!--      <div class="w-full bg-yellow-400 text-black p-8 rounded flex flex-col md:flex-row justify-between items-center shadow-md">-->
<!--        <div class="mb-4 md:mb-0">-->
<!--          <h2 class="text-2xl font-bold">Mid-Year Sale is Live!</h2>-->
<!--          <p>Up to 70% off on selected items</p>-->
<!--        </div>-->
<!--        <button class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800 transition-all">Shop Deals</button>-->
<!--      </div>-->
<!--    </section>-->

<!--    &lt;!&ndash; Testimonials &ndash;&gt;-->
<!--    <section class="py-12 px-4 md:px-12 bg-gray-50 dark:bg-gray-900">-->
<!--      <h2 class="text-2xl font-bold mb-6">What Our Customers Say</h2>-->
<!--      <div class="grid md:grid-cols-3 gap-6">-->
<!--        <div-->
<!--            v-for="n in 3"-->
<!--            :key="n"-->
<!--            class="bg-white dark:bg-gray-800 p-6 rounded shadow"-->
<!--        >-->
<!--          <p class="italic text-sm">"Amazing service and quick delivery!"</p>-->
<!--          <div class="mt-4 flex items-center gap-3">-->
<!--            <div class="w-10 h-10 bg-gray-300 dark:bg-gray-600 rounded-full"></div>-->
<!--            <span class="font-medium">User {{ n }}</span>-->
<!--          </div>-->
<!--        </div>-->
<!--      </div>-->
<!--    </section>-->

<!--    &lt;!&ndash; Newsletter CTA &ndash;&gt;-->
<!--    <section class="py-12 px-4 md:px-12 bg-blue-50 dark:bg-blue-900 text-center">-->
<!--      <h2 class="text-xl md:text-2xl font-bold mb-4">Stay Updated</h2>-->
<!--      <p class="mb-6">Subscribe to get updates on latest deals and offers.</p>-->
<!--      <div class="flex justify-center gap-2 max-w-md mx-auto">-->
<!--        <input-->
<!--            type="email"-->
<!--            placeholder="Enter your email"-->
<!--            class="px-4 py-2 w-full rounded border border-gray-300 dark:border-gray-600"-->
<!--        />-->
<!--        <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Subscribe</button>-->
<!--      </div>-->
<!--    </section>-->
<!--  </div>-->




<div class="w-full h-full">
  <StoreLanding />
</div>




</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useFetch } from '#app'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
const config = useRuntimeConfig()
interface ChildCategory {
  name: string
  url: string
  image: string
  starting_from_price: number
}

interface ParentCategory {
  name: string
  url: string
  thumbnail: string
  children: ChildCategory[]
}

// Reactive parent + children data
const categorySections = ref<ParentCategory[]>([])

const { data, error } = await useFetch<ParentCategory[]>(`${config.public.apiBase}/categories/with-products`, {
  credentials: 'include',
})

if (data.value) {
  categorySections.value = data.value
}
</script>
