<template>
  <button
      @click="toggleDark"
      class="transition hover:text-yellow-500 dark:hover:text-yellow-300"
      :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
      aria-label="Toggle Dark Mode"
  >
    <Icon v-if="!isDark" name="bi:moon-fill" width="20" height="20" />
    <Icon v-else name="bi:brightness-high-fill" width="20" height="20" />
  </button>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

const isDark = ref(false)

function toggleDark() {
  isDark.value = !isDark.value
  const html = document.documentElement
  html.classList.toggle('dark', isDark.value)
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

onMounted(() => {
  const saved = localStorage.getItem('theme')
  if (saved === 'dark') {
    isDark.value = true
    document.documentElement.classList.add('dark')
  } else if (saved === 'light') {
    isDark.value = false
    document.documentElement.classList.remove('dark')
  } else {
    isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
    document.documentElement.classList.toggle('dark', isDark.value)
  }
})
</script>
