<template>
  <GlobalLoader />
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>


<script setup>
import GlobalLoader from '~/components/GlobalLoader.vue'

const loading = useState('pageLoading', () => false)

const router = useRouter()

router.beforeEach(() => {
  loading.value = true
})

router.afterEach(() => {
  // Delay slightly for smooth transition
  setTimeout(() => {
    loading.value = false
  }, 500)
})
</script>
