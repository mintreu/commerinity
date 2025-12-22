<script setup lang="ts">
definePageMeta({
  layout: 'native',
  title: 'Home'
})
</script>

<template>
  <section class="space-y-4">
    <div class="rounded-xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
      <h2 class="text-base font-semibold">Welcome</h2>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
        This is a native‑inspired mobile‑first dashboard layout tuned for Android/iOS with safe‑area support.
      </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <article class="rounded-xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
        <p class="text-sm font-medium">Quick Action</p>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Create something new from the FAB on mobile.</p>
      </article>
      <article class="rounded-xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
        <p class="text-sm font-medium">Orders</p>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Track and manage recent orders seamlessly.</p>
      </article>
      <article class="rounded-xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
        <p class="text-sm font-medium">Profile</p>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Access account and security settings quickly.</p>
      </article>
    </div>

    <div class="rounded-xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
      <NuxtLink to="/sample/back" class="inline-flex items-center gap-2 text-indigo-600 hover:underline dark:text-indigo-400">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        Open detail with back affordance
      </NuxtLink>
    </div>
  </section>
</template>
