<script setup lang="ts">
definePageMeta({
  layout: 'dashboard-native',
  title: 'Dashboard'
})
</script>

<template>
  <section class="space-y-4">
    <!-- Overview cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <article class="rounded-xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
        <p class="text-sm text-gray-600 dark:text-gray-300">Revenue</p>
        <p class="mt-1 text-2xl font-semibold">$34,200</p>
        <p class="mt-1 text-xs text-emerald-600">+8.2% vs last week</p>
      </article>
      <article class="rounded-xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
        <p class="text-sm text-gray-600 dark:text-gray-300">Orders</p>
        <p class="mt-1 text-2xl font-semibold">1,284</p>
        <p class="mt-1 text-xs text-emerald-600">+3.4% vs last week</p>
      </article>
      <article class="rounded-xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
        <p class="text-sm text-gray-600 dark:text-gray-300">Customers</p>
        <p class="mt-1 text-2xl font-semibold">8,913</p>
        <p class="mt-1 text-xs text-emerald-600">+1.1% vs last week</p>
      </article>
      <article class="rounded-xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
        <p class="text-sm text-gray-600 dark:text-gray-300">Refunds</p>
        <p class="mt-1 text-2xl font-semibold">42</p>
        <p class="mt-1 text-xs text-rose-600">-0.5% vs last week</p>
      </article>
    </div>

    <!-- Activity + Recent orders -->
    <div class="grid gap-4 lg:grid-cols-3">
      <section class="rounded-xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5 lg:col-span-2">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-semibold">Activity</h2>
          <button class="text-xs text-indigo-600 hover:underline dark:text-indigo-400">View all</button>
        </div>
        <div class="mt-3 h-56 rounded-lg bg-gradient-to-tr from-indigo-50 to-emerald-50 dark:from-indigo-400/10 dark:to-emerald-400/10" />
      </section>

      <section class="rounded-xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
        <h2 class="text-base font-semibold">Recent Orders</h2>
        <ul class="mt-3 space-y-3">
          <li class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium">#ORD-1293</p>
              <p class="text-xs text-gray-600 dark:text-gray-300">Today • 2 items</p>
            </div>
            <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">Paid</span>
          </li>
          <li class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium">#ORD-1292</p>
              <p class="text-xs text-gray-600 dark:text-gray-300">Today • 1 item</p>
            </div>
            <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800 dark:bg-yellow-400/10 dark:text-yellow-300">Pending</span>
          </li>
          <li class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium">#ORD-1291</p>
              <p class="text-xs text-gray-600 dark:text-gray-300">Yesterday • 3 items</p>
            </div>
            <span class="rounded-full bg-rose-100 px-2 py-1 text-xs font-medium text-rose-700 dark:bg-rose-400/10 dark:text-rose-300">Refunded</span>
          </li>
        </ul>
      </section>
    </div>

    <!-- Quick links -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <NuxtLink to="/dashboard/orders" class="rounded-xl border border-gray-200/70 bg-white p-4 shadow-sm transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10">
        <p class="text-sm font-medium">Manage Orders</p>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Review, fulfill, refund.</p>
      </NuxtLink>
      <NuxtLink to="/dashboard/wallet" class="rounded-xl border border-gray-200/70 bg-white p-4 shadow-sm transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10">
        <p class="text-sm font-medium">Wallet</p>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Balance and payouts.</p>
      </NuxtLink>
      <NuxtLink to="/dashboard/account" class="rounded-xl border border-gray-200/70 bg-white p-4 shadow-sm transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10">
        <p class="text-sm font-medium">Account</p>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Profile & security.</p>
      </NuxtLink>
      <NuxtLink to="/dashboard/helpdesk" class="rounded-xl border border-gray-200/70 bg-white p-4 shadow-sm transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10">
        <p class="text-sm font-medium">Helpdesk</p>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Support & docs.</p>
      </NuxtLink>
    </div>
  </section>
</template>
