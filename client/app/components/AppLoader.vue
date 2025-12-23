<script setup lang="ts">
/**
 * AppLoader - Global loading component with branding
 * Shows animated logo and progress indicator during page loads
 */
defineProps<{
  show?: boolean
  message?: string
  fullScreen?: boolean
}>()

const { appName } = useBranding()
</script>

<template>
  <Transition
    enter-active-class="transition-opacity duration-300"
    leave-active-class="transition-opacity duration-300"
    enter-from-class="opacity-0"
    leave-to-class="opacity-0"
  >
    <div
      v-if="show"
      :class="[
        'flex flex-col items-center justify-center gap-6 z-50',
        fullScreen
          ? 'fixed inset-0 bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-blue-950 dark:to-purple-950'
          : 'py-12'
      ]"
    >
      <!-- Background Effects (full screen only) -->
      <div
        v-if="fullScreen"
        class="absolute inset-0 pointer-events-none overflow-hidden"
      >
        <div class="absolute -top-20 -left-20 w-96 h-96 bg-gradient-to-r from-blue-400/10 to-purple-400/10 rounded-full blur-3xl opacity-60 animate-pulse" />
        <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-gradient-to-r from-purple-400/10 to-pink-400/10 rounded-full blur-3xl opacity-70 animate-pulse" />
      </div>

      <!-- Loader Content -->
      <div class="relative z-10 flex flex-col items-center gap-6">
        <!-- Animated Logo -->
        <div class="relative">
          <!-- Spinning Ring -->
          <div class="absolute inset-0 animate-spin rounded-full border-4 border-transparent border-t-blue-600 border-r-indigo-600" />

          <!-- Logo Container -->
          <div class="w-20 h-20 rounded-2xl bg-white dark:bg-slate-800 shadow-2xl flex items-center justify-center p-2 m-1">
            <img
              src="/logo.png"
              alt="Logo"
              class="w-full h-full object-contain"
            >
          </div>
        </div>

        <!-- App Name -->
        <h1 class="text-2xl font-bold gradient-text-primary">
          {{ appName }}
        </h1>

        <!-- Loading Message -->
        <div class="flex items-center gap-3">
          <!-- Dots Animation -->
          <div class="flex gap-1">
            <span class="w-2 h-2 bg-blue-600 rounded-full animate-bounce [animation-delay:-0.3s]" />
            <span class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce [animation-delay:-0.15s]" />
            <span class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" />
          </div>

          <span class="text-sm text-slate-600 dark:text-slate-400">
            {{ message || 'Loading...' }}
          </span>
        </div>
      </div>
    </div>
  </Transition>
</template>
