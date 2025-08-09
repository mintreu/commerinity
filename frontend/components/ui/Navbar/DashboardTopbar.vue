<template>
  <header class="h-16 px-6 flex items-center justify-end gap-4 border-b bg-white dark:bg-gray-900 dark:border-gray-800">
    <DarkModeToggle />
    <UserDropdown />
  </header>
</template>

<script setup lang="ts">
import DarkModeToggle from "~/components/ui/DarkModeToggle.vue"
import UserDropdown from "~/components/ui/UserDropdown.vue"
</script>
