<template>
  <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-5 flex items-center space-x-4">
    <Icon :name="icon" class="text-3xl text-primary-600 dark:text-primary-400" />
    <div>
      <div class="text-sm text-gray-500 dark:text-gray-400">{{ label }}</div>
      <div class="text-xl font-semibold text-gray-800 dark:text-white">{{ value }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  icon: string
  label: string
  value: string | number
}>()
</script>
