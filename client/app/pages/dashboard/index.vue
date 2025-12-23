<script setup lang="ts">
/**
 * Dashboard Index Page
 * Dynamically loads the appropriate dashboard component based on user type
 */

definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const { user, getDashboardComponent } = useUserType()

// Get the dashboard component name
const dashboardComponent = computed(() => {
  return getDashboardComponent()
})

// Loading state while user data is being fetched
const isLoading = computed(() => !user.value)
</script>

<template>
  <div>
    <!-- Loading State -->
    <div
      v-if="isLoading"
      class="flex h-[calc(100vh-200px)] items-center justify-center"
    >
      <div class="text-center">
        <UIcon
          name="i-lucide-loader-circle"
          class="h-10 w-10 animate-spin text-primary mx-auto mb-4"
        />
        <p class="text-sm text-slate-500 dark:text-slate-400">
          Loading your dashboard...
        </p>
      </div>
    </div>

    <!-- Dynamic Dashboard Component -->
    <component
      :is="resolveComponent(dashboardComponent)"
      v-else
    />
  </div>
</template>
