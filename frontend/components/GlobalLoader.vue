<template>
  <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 backdrop-blur-0"
      enter-to-class="opacity-100 backdrop-blur-md"
      leave-active-class="transition-all duration-300 ease-in"
      leave-from-class="opacity-100 backdrop-blur-md"
      leave-to-class="opacity-0 backdrop-blur-0"
  >
    <div
        v-if="isLoading"
        class="global-loader fixed inset-0 z-[9999] flex flex-col items-center justify-center
             bg-gradient-to-br from-white/90 via-blue-50/90 to-purple-50/90
             dark:from-gray-950/90 dark:via-blue-950/90 dark:to-purple-950/90
             backdrop-blur-xl transition-colors duration-300"
    >
      <!-- Animated Background Orbs -->
      <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div
            class="loader-orb-1 absolute -top-20 -left-20 w-80 h-80
                 bg-gradient-to-r from-blue-400/20 to-cyan-400/20
                 rounded-full blur-3xl animate-float-slow"
        ></div>
        <div
            class="loader-orb-2 absolute -bottom-20 -right-20 w-72 h-72
                 bg-gradient-to-r from-purple-400/20 to-pink-400/20
                 rounded-full blur-3xl animate-float-slower"
        ></div>
        <div
            class="loader-orb-3 absolute top-1/2 left-1/3 w-64 h-64
                 bg-gradient-to-r from-emerald-400/20 to-teal-400/20
                 rounded-full blur-2xl animate-float-fast"
        ></div>
      </div>

      <!-- Main Loader Container -->
      <div class="loader-container relative z-10 flex flex-col items-center">

        <!-- Logo/Brand Container -->
        <div class="loader-brand mb-8">
          <div class="brand-container relative">
            <!-- Animated Brand Circle -->
            <div class="brand-circle w-20 h-20 rounded-full bg-gradient-to-r from-blue-500 to-purple-600
                        flex items-center justify-center shadow-2xl shadow-blue-500/25
                        animate-pulse-slow relative overflow-hidden">
              <!-- Shimmer Effect -->
              <div class="shimmer absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent
                          -translate-x-full animate-shimmer"></div>

              <!-- Brand Icon/Letter -->
              <div class="brand-icon text-2xl font-black text-white z-10">
                {{ brandLetter }}
              </div>
            </div>

            <!-- Rotating Ring -->
            <div class="rotating-ring absolute inset-0 w-20 h-20 border-4 border-transparent
                        border-t-blue-500 border-r-purple-500 rounded-full animate-spin-slow"></div>
          </div>
        </div>

        <!-- Animated Spinner -->
        <div class="loader-spinner mb-6">
          <div class="spinner-container relative w-16 h-16">
            <!-- Multiple Spinning Rings -->
            <div class="spinner-ring-1 absolute inset-0 w-16 h-16 border-4 border-transparent
                        border-t-blue-500 border-opacity-80 rounded-full animate-spin"></div>
            <div class="spinner-ring-2 absolute inset-2 w-12 h-12 border-4 border-transparent
                        border-t-purple-500 border-opacity-60 rounded-full animate-spin-reverse"></div>
            <div class="spinner-ring-3 absolute inset-4 w-8 h-8 border-4 border-transparent
                        border-t-pink-500 border-opacity-40 rounded-full animate-spin-slow"></div>

            <!-- Center Dot -->
            <div class="center-dot absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2
                        w-3 h-3 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full animate-pulse"></div>
          </div>
        </div>

        <!-- Loading Text -->
        <div class="loader-text text-center">
          <h2 class="loading-title text-xl font-black bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600
                     bg-clip-text text-transparent mb-2 animate-pulse">
            {{ loadingTitle }}
          </h2>

          <!-- Animated Dots -->
          <div class="loading-dots flex items-center justify-center gap-1">
            <span class="loading-message text-sm font-semibold text-gray-600 dark:text-gray-300 mr-2">
              {{ loadingMessage }}
            </span>
            <div class="dots flex gap-1">
              <div class="dot w-2 h-2 bg-blue-500 rounded-full animate-bounce-dot" style="animation-delay: 0ms;"></div>
              <div class="dot w-2 h-2 bg-purple-500 rounded-full animate-bounce-dot" style="animation-delay: 150ms;"></div>
              <div class="dot w-2 h-2 bg-pink-500 rounded-full animate-bounce-dot" style="animation-delay: 300ms;"></div>
            </div>
          </div>
        </div>

        <!-- Progress Bar (Optional) -->
        <div v-if="showProgress" class="loader-progress mt-6 w-64">
          <div class="progress-container h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden shadow-inner">
            <div class="progress-bar h-full bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500
                        rounded-full transition-all duration-300 ease-out relative overflow-hidden"
                 :style="{ width: `${progress}%` }">
              <!-- Progress Bar Shimmer -->
              <div class="progress-shimmer absolute inset-0 bg-gradient-to-r from-transparent via-white/40 to-transparent
                          -translate-x-full animate-shimmer-fast"></div>
            </div>
          </div>
          <div class="progress-text text-center mt-2">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
              {{ Math.round(progress) }}% Complete
            </span>
          </div>
        </div>

      </div>

      <!-- Floating Particles (Optional) -->
      <div class="floating-particles absolute inset-0 pointer-events-none">
        <div v-for="(particle, index) in particles" :key="`loader-particle-${index}`"
             class="particle absolute rounded-full opacity-40"
             :class="particle.class"
             :style="particle.style">
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

// Props (optional for customization)
interface Props {
  title?: string
  message?: string
  showProgress?: boolean
  brandLetter?: string
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Loading',
  message: 'Please wait',
  showProgress: false,
  brandLetter: 'V' // First letter of your brand
})

// State
const isLoading = useState('pageLoading', () => false)
const progress = ref(0)

// Computed
const loadingTitle = computed(() => props.title)
const loadingMessage = computed(() => props.message)
const brandLetter = computed(() => props.brandLetter)
const showProgress = computed(() => props.showProgress)

// Floating particles for extra visual effect
const particles = [
  { class: 'w-2 h-2 bg-blue-400', style: 'top: 20%; left: 10%; animation: float-particle 4s ease-in-out infinite; animation-delay: 0s;' },
  { class: 'w-3 h-3 bg-purple-400', style: 'top: 60%; right: 15%; animation: float-particle 5s ease-in-out infinite; animation-delay: 1s;' },
  { class: 'w-2 h-2 bg-pink-400', style: 'bottom: 30%; left: 20%; animation: float-particle 6s ease-in-out infinite; animation-delay: 2s;' },
  { class: 'w-2 h-2 bg-emerald-400', style: 'top: 40%; right: 25%; animation: float-particle 4.5s ease-in-out infinite; animation-delay: 3s;' },
  { class: 'w-1 h-1 bg-cyan-400', style: 'top: 80%; left: 70%; animation: float-particle 3s ease-in-out infinite; animation-delay: 1.5s;' }
]

// Progress simulation (if showProgress is true)
let progressInterval: NodeJS.Timeout | null = null

const simulateProgress = () => {
  if (!showProgress.value) return

  progress.value = 0
  progressInterval = setInterval(() => {
    if (progress.value < 90) {
      progress.value += Math.random() * 10
    } else if (progress.value < 95) {
      progress.value += 1
    }

    if (progress.value >= 100) {
      progress.value = 100
      if (progressInterval) {
        clearInterval(progressInterval)
        progressInterval = null
      }
    }
  }, 200)
}

// Watch for loading state changes
watch(isLoading, (newValue) => {
  if (newValue && showProgress.value) {
    simulateProgress()
  } else if (!newValue && progressInterval) {
    clearInterval(progressInterval)
    progressInterval = null
    progress.value = 0
  }
})

// Cleanup
onUnmounted(() => {
  if (progressInterval) {
    clearInterval(progressInterval)
  }
})
</script>

<style scoped>
/* Custom Animations */
@keyframes float-slow {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(180deg); }
}

@keyframes float-slower {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(15px) rotate(-90deg); }
}

@keyframes float-fast {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-30px) rotate(270deg); }
}

@keyframes spin-slow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes spin-reverse {
  from { transform: rotate(360deg); }
  to { transform: rotate(0deg); }
}

@keyframes pulse-slow {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.1); opacity: 0.8; }
}

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(200%); }
}

@keyframes shimmer-fast {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(200%); }
}

@keyframes bounce-dot {
  0%, 20%, 53%, 80%, 100% { transform: translateY(0px); }
  40%, 43% { transform: translateY(-8px); }
}

@keyframes float-particle {
  0%, 100% { transform: translateY(0px) scale(1); opacity: 0.4; }
  50% { transform: translateY(-15px) scale(1.2); opacity: 0.8; }
}

/* Animation Classes */
.animate-float-slow {
  animation: float-slow 8s ease-in-out infinite;
}

.animate-float-slower {
  animation: float-slower 10s ease-in-out infinite;
}

.animate-float-fast {
  animation: float-fast 6s ease-in-out infinite;
}

.animate-spin-slow {
  animation: spin-slow 3s linear infinite;
}

.animate-spin-reverse {
  animation: spin-reverse 2s linear infinite;
}

.animate-pulse-slow {
  animation: pulse-slow 2s ease-in-out infinite;
}

.animate-shimmer {
  animation: shimmer 2s ease-in-out infinite;
}

.animate-shimmer-fast {
  animation: shimmer-fast 1.5s ease-in-out infinite;
}

.animate-bounce-dot {
  animation: bounce-dot 1.4s ease-in-out infinite both;
}

/* Glass morphism effects */
.global-loader {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

.brand-circle {
  box-shadow:
      0 20px 25px -5px rgba(59, 130, 246, 0.3),
      0 10px 10px -5px rgba(147, 51, 234, 0.2),
      inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.spinner-ring-1,
.spinner-ring-2,
.spinner-ring-3 {
  filter: drop-shadow(0 4px 8px rgba(59, 130, 246, 0.3));
}

.progress-container {
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Responsive Design */
@media (max-width: 640px) {
  .loader-orb-1,
  .loader-orb-2 {
    width: 12rem;
    height: 12rem;
  }

  .loader-orb-3 {
    width: 8rem;
    height: 8rem;
  }

  .floating-particles {
    display: none;
  }

  .loading-title {
    font-size: 1.125rem;
  }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .brand-circle {
    box-shadow:
        0 20px 25px -5px rgba(59, 130, 246, 0.4),
        0 10px 10px -5px rgba(147, 51, 234, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
  }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
  .animate-float-slow,
  .animate-float-slower,
  .animate-float-fast,
  .animate-spin-slow,
  .animate-spin-reverse,
  .animate-pulse-slow,
  .animate-shimmer,
  .animate-shimmer-fast,
  .animate-bounce-dot,
  .particle {
    animation: none !important;
  }
}
</style>
