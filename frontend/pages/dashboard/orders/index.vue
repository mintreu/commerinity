

<template>

  <div class="p-12">
    <h1 class="text-4xl font-semibold">My Orders</h1>
  </div>


</template>




<script setup lang="ts">
import { useSanctum } from '#imports'

const { user } = useSanctum()



definePageMeta({
  layout: 'dashboard'
})
</script>


<style scoped>

</style>