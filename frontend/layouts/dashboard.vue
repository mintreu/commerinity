<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import DashboardSidebar from '~/components/ui/Navbar/DashboardSidebar.vue'
import DashboardTopbar from '~/components/ui/Navbar/DashboardTopbar.vue'
import BottomNavBar from '~/components/ui/BottomNavBar.vue'

const collapsed = ref(false)
const mobileOpen = ref(false)
const isMobile = ref(window.innerWidth < 768)

function handleResize() { isMobile.value = window.innerWidth < 768 }

function toggleSidebar() {
  if (isMobile.value) mobileOpen.value = !mobileOpen.value
  else collapsed.value = !collapsed.value

  try { localStorage.setItem('sidebar-collapsed', collapsed.value.toString()) } catch {}
}

onMounted(() => window.addEventListener('resize', handleResize))
onUnmounted(() => window.removeEventListener('resize', handleResize))
</script>

<template>
  <div class="min-h-screen flex flex-col md:flex-row bg-gray-100 dark:bg-gray-950">
    <div class="flex-1 flex flex-col min-h-screen">
      <DashboardTopbar :collapsed="collapsed" @toggle-sidebar="toggleSidebar" class="md:flex" />

      <OnboardingBanner />

      <div class="flex flex-row w-full h-full gap-3">
        <!-- Desktop Sidebar -->
        <div class="hidden md:flex">
          <DashboardSidebar
              class="hidden md:flex"
              :collapsed="collapsed"
              :isMobile="false"
              :mobileOpen="false"
          />
        </div>

        <!-- Mobile Sidebar Drawer -->
        <div class="md:hidden">
          <DashboardSidebar
              class="md:hidden"
              :collapsed="collapsed"
              :isMobile="true"
              :mobileOpen="mobileOpen"
              @close-mobile-sidebar="mobileOpen = false"
          />
        </div>

        <main class="flex-1 overflow-y-auto p-4 md:p-8 container mx-auto">
          <slot />
        </main>
      </div>
    </div>

    <!-- Mobile Bottom Nav -->
    <BottomNavBar class="md:hidden" />
  </div>
</template>
