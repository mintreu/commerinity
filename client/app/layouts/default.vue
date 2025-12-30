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

  <dashboard
    v-if="isLoggedIn"
  >
    <slot />
  </dashboard>

  <!-- Guest View: Landing page with carousels -->
  <public
    v-else
  >
    <slot />
  </public>



<!--  <component :is="layoutToUse">-->
<!--    <slot />-->
<!--  </component>-->
</template>
