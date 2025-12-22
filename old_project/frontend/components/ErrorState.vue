<template>
  <!-- Minimal Mode -->
  <div v-if="minimal" class="py-6 sm:py-8 px-4">
    <div class="max-w-sm mx-auto text-center">
      <!-- Simple Icon -->
      <div class="mb-4 flex justify-center">
        <div class="w-16 h-16 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center">
          <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
          </svg>
        </div>
      </div>

      <!-- Minimal Text -->
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
        Connection Not Established
      </h3>
      <p class="text-sm text-gray-600 dark:text-gray-400">
        Please check your internet connection
      </p>
    </div>
  </div>

  <!-- Full Mode -->
  <div v-else class="py-16 sm:py-20 px-4">
    <div class="max-w-md mx-auto">
      <!-- Premium Fintech Error Icon Animation -->
      <div class="mb-8 flex justify-center">
        <div class="relative w-32 h-32">
          <!-- Outer Ring Animation -->
          <div class="absolute inset-0 rounded-full bg-gradient-to-br from-red-500/10 to-orange-500/10 animate-pulse"></div>

          <!-- Inner Circle with Gradient -->
          <div class="absolute inset-2 rounded-full bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 border border-red-200/50 dark:border-red-800/50 flex items-center justify-center">
            <!-- SVG Icon - Premium Style -->
            <svg class="w-16 h-16" fill="none" viewBox="0 0 64 64" stroke="currentColor">
              <defs>
                <linearGradient id="errorGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" style="stop-color: #ef4444; stop-opacity: 1" />
                  <stop offset="100%" style="stop-color: #f97316; stop-opacity: 1" />
                </linearGradient>
              </defs>

              <!-- Connection Lines -->
              <circle cx="32" cy="32" r="24" stroke="url(#errorGradient)" stroke-width="1.5" opacity="0.3" />

              <!-- Animated Dots -->
              <circle cx="32" cy="10" r="2.5" fill="url(#errorGradient)" class="animate-pulse" />
              <circle cx="50" cy="32" r="2.5" fill="url(#errorGradient)" opacity="0.5" />
              <circle cx="32" cy="54" r="2.5" fill="url(#errorGradient)" opacity="0.3" />
              <circle cx="14" cy="32" r="2.5" fill="url(#errorGradient)" opacity="0.7" class="animate-pulse" style="animation-delay: 0.2s" />

              <!-- Error Symbol -->
              <g stroke="url(#errorGradient)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="28" y1="28" x2="36" y2="36" />
                <line x1="36" y1="28" x2="28" y2="36" />
              </g>
            </svg>
          </div>

          <!-- Floating Particle Effect -->
          <div class="absolute top-0 right-0 w-1 h-1 bg-red-500 rounded-full animate-float" style="animation-duration: 3s"></div>
          <div class="absolute bottom-2 left-2 w-1 h-1 bg-orange-500 rounded-full animate-float" style="animation-duration: 4s; animation-delay: 1s"></div>
        </div>
      </div>

      <!-- Premium Typography -->
      <div class="text-center mb-6">
        <h3 class="text-2xl font-bold bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 dark:from-white dark:via-gray-100 dark:to-white bg-clip-text text-transparent mb-3">
          Connection Failed
        </h3>
        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed mb-4">
          We're unable to establish a connection. This could be due to:
        </p>

        <!-- Helpful Tips -->
        <ul class="text-left bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 mb-6 space-y-2">
          <li class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 flex items-start gap-2">
            <span class="text-blue-600 dark:text-blue-400 font-bold">•</span>
            <span>Check your internet connection</span>
          </li>
          <li class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 flex items-start gap-2">
            <span class="text-blue-600 dark:text-blue-400 font-bold">•</span>
            <span>Clear your browser cache and cookies</span>
          </li>
          <li class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 flex items-start gap-2">
            <span class="text-blue-600 dark:text-blue-400 font-bold">•</span>
            <span>Try again in a few moments</span>
          </li>
        </ul>
      </div>

      <!-- Premium Button -->
      <button
          @click="handleReload"
          class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-3 group"
      >
        <svg class="w-5 h-5 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <span>Refresh Page</span>
      </button>

      <!-- Secondary Text -->
      <p class="text-xs text-gray-500 dark:text-gray-500 text-center mt-6">
        If the problem persists, please contact support
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  minimal?: boolean
}

withDefaults(defineProps<Props>(), {
  minimal: false
})

const handleReload = () => {
  if (process.client) {
    window.location.reload()
  }
}
</script>

<style scoped>
@keyframes float {
  0%, 100% {
    transform: translateY(0px) translateX(0px);
    opacity: 0;
  }
  50% {
    opacity: 1;
  }
  100% {
    transform: translateY(-30px) translateX(20px);
    opacity: 0;
  }
}

.animate-float {
  animation: float 4s ease-in-out infinite;
}
</style>
