<template>
  <aside class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 h-full">
    <h2 class="text-xl font-semibold mb-6 text-gray-900 dark:text-gray-100">Progress</h2>
    <ul class="space-y-4">
      <li
          v-for="(step, index) in steps"
          :key="index"
          class="flex items-center gap-3 group transition-all"
      >
        <div
            class="w-10 h-10 rounded-full flex items-center justify-center border transition-all"
            :class="[
            index === currentStep
              ? 'bg-blue-600 text-white border-blue-600 shadow-md'
              : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600'
          ]"
        >
          <Icon :name="step.icon" class="text-lg" />
        </div>
        <span
            class="text-gray-900 dark:text-gray-100 font-medium transition"
            :class="index === currentStep ? 'text-blue-600 dark:text-blue-400' : ''"
        >
          {{ step.name }}
        </span>
      </li>
    </ul>
  </aside>
</template>

<script setup lang="ts">
defineProps<{
  steps: { name: string, icon: string }[]
  currentStep: number
}>()
</script>
