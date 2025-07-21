<template>
  <div class="space-y-6">
    <!-- Page Title -->
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>

    <!-- Stats Cards -->
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      <DashboardStatCard
          icon="mdi:account-group"
          label="Users"
          value="1,250"
      />
      <DashboardStatCard
          icon="mdi:currency-usd"
          label="Sales"
          value="$14,200"
      />
      <DashboardStatCard
          icon="mdi:cart-outline"
          label="Orders"
          value="320"
      />
      <DashboardStatCard
          icon="mdi:chart-line"
          label="Revenue"
          value="$54,000"
      />
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'dashboard'
})
</script>
