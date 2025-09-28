<template>
  <div class="min-h-screen w-full bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-slate-900 dark:via-indigo-900 dark:to-purple-900">
    <RegisterForm redirectTo="/dashboard" />
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'default',           // Nuxt's standard layout property
  layoutConfig: {              // Our custom config property
    navbar: { show: false },
    footer: { show: false },
    bottomNav: { show: false },
    scrollTop: { show: false },
    background: { effects: false, particles: false }
  }
})

</script>
