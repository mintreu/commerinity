<template>
  <div class="space-y-6 container">
    <!-- Page Title -->
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>


    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Profile Card -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 space-y-4">
        <div class="flex flex-col items-center text-center">
          <img
              :src="user?.avatar || '/default-avatar.png'"
              alt="Avatar"
              class="w-32 h-32 rounded-full border-4 border-blue-600 dark:border-blue-400 object-cover shadow"
          />
          <h2 class="mt-4 text-xl font-semibold text-gray-900 dark:text-white">{{ user?.name }}</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ user?.email }}</p>
          <div class="flex flex-row gap-2">
            <span class="bg-green-600 px-3 py-1 rounded-2xl text-white">{{ user?.type}}</span>
            <span class="bg-green-600 px-3 py-1 rounded-2xl text-white">{{ user?.status}}</span>
          </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-2 text-sm text-gray-700 dark:text-gray-300">
          <p><strong>Mobile:</strong> {{ user?.mobile || 'Not set' }}</p>
          <p><strong>Gender:</strong> {{ user?.gender || 'Not set' }}</p>
          <p><strong>DOB:</strong> {{ user?.dob || 'Not set' }}</p>
          <p><strong>Bio:</strong> <span class="italic">{{ user?.bio || 'No bio yet' }}</span></p>
        </div>
      </div>

      <!-- Stats & Future Sections -->
      <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Example Stat Box -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 text-center">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Total Earnings</h3>
          <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">₹ 15,200</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 text-center">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Referral Count</h3>
          <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">38</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 text-center">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Total Orders</h3>
          <p class="mt-2 text-2xl font-bold text-purple-600 dark:text-purple-400">129</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 text-center">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Rank</h3>
          <p class="mt-2 text-2xl font-bold text-yellow-500 dark:text-yellow-300">Gold</p>
        </div>
      </div>
    </div>




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




      <OrdersTrendChart
          title="My Orders Trend"
          endpoint="order/insight"
          type="line"
          :show-markers="true"
          value-label-formatter="{value}"
          :status="['COMPLETED','CONFIRM']"
      />











    </div>
  </div>
</template>

<script setup lang="ts">
import { useSanctum } from '#imports'
import OrdersTrendChart from "~/components/charts/OrdersTrendChart.vue";

const { user } = useSanctum()



definePageMeta({
  layout: 'dashboard'
})
</script>
