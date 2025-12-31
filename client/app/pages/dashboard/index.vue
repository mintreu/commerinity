<script setup lang="ts">
/**
 * Dashboard Index Page
 * Dynamically loads the appropriate dashboard component based on user type
 */
import DashboardRegular from '~/components/dashboard/DashboardRegular.vue'
import DashboardMember from '~/components/dashboard/DashboardMember.vue'
import DashboardPromoter from '~/components/dashboard/DashboardPromoter.vue'
import DashboardAdvisor from '~/components/dashboard/DashboardAdvisor.vue'
import DashboardMentor from '~/components/dashboard/DashboardMentor.vue'

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const { user, getDashboardComponent } = useUserType()
const { startLoading, stopLoading } = useLoading()

// Map component names to actual component objects
const componentMap: Record<string, any> = {
  DashboardRegular,
  DashboardMember,
  DashboardPromoter,
  DashboardAdvisor,
  DashboardMentor
}

// Get the actual dashboard component object
const dashboardComponent = computed(() => {
  const name = getDashboardComponent()
  return componentMap[name] || DashboardRegular
})

// Loading state while user data is being fetched
const isUserLoading = computed(() => !user.value)

// Sync with global loader
watch(isUserLoading, (loading) => {
  if (loading) {
    startLoading('Finalizing your dashboard...')
  } else {
    // Small delay to ensure component is ready
    setTimeout(() => {
      stopLoading()
    }, 500)
  }
}, { immediate: true })
</script>

<template>
  <div class="h-full w-full">
    <!-- Main Dashboard Section -->
    <template v-if="!isUserLoading">
      <Suspense>
        <!-- The correctly resolved component object -->
        <component :is="dashboardComponent" />

        <!-- Loading fallback for specific dashboard component imports -->
        <template #fallback>
          <div class="flex h-[calc(100vh-200px)] items-center justify-center">
            <UIcon
              name="i-lucide-loader-circle"
              class="h-10 w-10 animate-spin text-primary"
            />
          </div>
        </template>
      </Suspense>
    </template>

    <!-- Initial Loading State -->
    <div
      v-else
      class="flex h-[calc(100vh-200px)] items-center justify-center"
    >
      <div class="text-center">
        <UIcon
          name="i-lucide-loader-circle"
          class="h-10 w-10 animate-spin text-primary mx-auto mb-4"
        />
        <p class="text-sm text-slate-500 dark:text-slate-400">
          Preparing your dashboard...
        </p>
      </div>
    </div>
  </div>
</template>
