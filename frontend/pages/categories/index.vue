<template>

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
