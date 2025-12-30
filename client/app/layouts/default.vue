<script setup lang="ts">
import Dashboard from "./dashboard.vue";
import Public from "./public.vue";

/**
 * Smart Default Layout
 * Auto-switches between public and dashboard based on auth status
 * - Guest/Public routes: Use public.vue (no sidebar)
 * - Authenticated dashboard routes: Use dashboard.vue (with sidebar)
 */
const { isLoggedIn } = useSanctum()
const route = useRoute()

// Public routes (no auth required, should use public layout)
const publicRoutes = ['/shop', '/category', '/product', '/about', '/contact', '/']

const isPublicRoute = computed(() => {
  return publicRoutes.some(pub => route.path === pub || route.path.startsWith(pub + '/'))
})

// Use public layout for public routes OR when not logged in
// Use dashboard layout for dashboard routes when logged in
const layoutToUse = computed(() => {
  // if (isPublicRoute.value || !isLoggedIn.value) {
  //   return resolveComponent('LayoutsPublic')
  // }

  if (!isLoggedIn.value) {
    return resolveComponent('LayoutsPublic')
  }
  return resolveComponent('LayoutsDashboard')
})
</script>

<template>

  <NuxtLayout v-if="isLoggedIn" name="dashboard">
    <slot />
  </NuxtLayout>

  <!-- Guest View: Landing page with carousels -->
  <NuxtLayout v-else name="public">
    <slot />
  </NuxtLayout>



<!--  <component :is="layoutToUse">-->
<!--    <slot />-->
<!--  </component>-->
</template>
