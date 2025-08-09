<template>
  <div
    v-if="loading"
    class="fixed inset-0 z-50 flex items-center justify-center
           bg-white/80 dark:bg-black/80 backdrop-blur-md transition-colors duration-300"
  >
    <div class="h-12 w-12 animate-spin rounded-full border-4 border-blue-500 border-t-transparent dark:border-blue-400 dark:border-t-transparent"></div>
  </div>
</template>

<script setup>
const loading = useState('pageLoading', () => false)
</script>
