<template>
  <div
    v-if="isLoading"
    class="fixed inset-0 z-50 flex flex-col items-center justify-center
           bg-white/80 dark:bg-black/80 backdrop-blur-md transition-colors duration-300"
  >
    <div class="w-20 h-16">
      <svg class="w-full object-cover" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><circle fill="#76F2FF" stroke="#76F2FF" stroke-width="16" r="15" cx="40" cy="65"><animate attributeName="cy" calcMode="spline" dur="0.7" values="65;135;65;" keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="-.4"></animate></circle><circle fill="#76F2FF" stroke="#76F2FF" stroke-width="16" r="15" cx="100" cy="65"><animate attributeName="cy" calcMode="spline" dur="0.7" values="65;135;65;" keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="-.2"></animate></circle><circle fill="#76F2FF" stroke="#76F2FF" stroke-width="16" r="15" cx="160" cy="65"><animate attributeName="cy" calcMode="spline" dur="0.7" values="65;135;65;" keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="0"></animate></circle></svg>
    </div>
    <h2 class="text-md font-semibold py-2 animate-bounce dark:text-white">Please wait...</h2>

  </div>
</template>

<script setup>
const isLoading = useState('pageLoading', () => false)
</script>
