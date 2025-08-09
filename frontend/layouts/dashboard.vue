<template>
  <div class="min-h-screen flex bg-gray-100 dark:bg-gray-950 text-gray-900 dark:text-gray-100">
    <!-- Sidebar -->
    <DashboardSidebar />
    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen">
      <DashboardTopbar />
      <!-- Page content slot -->
      <main class="p-6 container mx-auto flex-1 overflow-y-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import DashboardSidebar from "~/components/ui/Navbar/DashboardSidebar.vue";
import DashboardTopbar from "~/components/ui/Navbar/DashboardTopbar.vue";
</script>
