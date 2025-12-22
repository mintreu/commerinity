<template>
  <Transition
      enter-active-class="transition-opacity duration-200"
      leave-active-class="transition-opacity duration-150"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
  >
    <div v-if="isLoading" class="global-loader fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-gradient-to-br from-white/90 via-blue-50/90 to-purple-50/90 dark:from-gray-950/90 dark:via-blue-950/90 dark:to-purple-950/90 backdrop-blur-xl">

      <!-- Animated Background Orbs -->
      <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute -top-20 -left-20 w-80 h-80 bg-gradient-to-r from-blue-400/20 to-cyan-400/20 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute -bottom-20 -right-20 w-72 h-72 bg-gradient-to-r from-purple-400/20 to-pink-400/20 rounded-full blur-3xl animate-float-slower"></div>
        <div class="absolute top-1/2 left-1/3 w-64 h-64 bg-gradient-to-r from-emerald-400/20 to-teal-400/20 rounded-full blur-2xl animate-float-fast"></div>
      </div>

      <!-- Main Loader Container -->
      <div class="relative z-10 flex flex-col items-center">

        <!-- Logo/Brand Container -->
        <div class="mb-8">
          <div class="relative">
            <!-- Animated Brand Circle -->
            <div class="w-20 h-20 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center shadow-2xl shadow-blue-500/25 animate-pulse-slow relative overflow-hidden">
              <!-- Shimmer Effect -->
              <div class="shimmer absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -translate-x-full"></div>

              <!-- Brand Icon/Letter -->
              <div class="text-2xl font-black text-white z-10">{{ letter }}</div>
            </div>

            <!-- Rotating Ring -->
            <div class="absolute inset-0 w-20 h-20 border-4 border-transparent border-t-blue-500 border-r-purple-500 rounded-full animate-spin-slow"></div>
          </div>
        </div>

        <!-- Animated Spinner -->
        <div class="mb-6">
          <div class="relative w-16 h-16">
            <!-- Multiple Spinning Rings -->
            <div class="absolute inset-0 w-16 h-16 border-4 border-transparent border-t-blue-500 border-opacity-80 rounded-full animate-spin"></div>
            <div class="absolute inset-2 w-12 h-12 border-4 border-transparent border-t-purple-500 border-opacity-60 rounded-full animate-spin-reverse"></div>
            <div class="absolute inset-4 w-8 h-8 border-4 border-transparent border-t-pink-500 border-opacity-40 rounded-full animate-spin-slow"></div>

            <!-- Center Dot -->
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-3 h-3 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full animate-pulse"></div>
          </div>
        </div>

        <!-- Loading Text -->
        <div class="text-center">
          <h2 class="text-xl font-black bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mb-2 animate-pulse">
            {{ title }}
          </h2>

          <!-- Animated Dots -->
          <div class="flex items-center justify-center gap-1">
            <span class="text-sm font-semibold text-gray-600 dark:text-gray-300 mr-2">{{ msg }}</span>
            <div class="flex gap-1">
              <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce-dot" style="animation-delay: 0ms;"></div>
              <div class="w-2 h-2 bg-purple-500 rounded-full animate-bounce-dot" style="animation-delay: 150ms;"></div>
              <div class="w-2 h-2 bg-pink-500 rounded-full animate-bounce-dot" style="animation-delay: 300ms;"></div>
            </div>
          </div>
        </div>

        <!-- Progress Bar (Optional) -->
        <div v-if="showProg" class="mt-6 w-64">
          <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden shadow-inner">
            <div class="h-full bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-full transition-all duration-300 ease-out relative overflow-hidden" :style="{ width: prog + '%' }">
              <!-- Progress Bar Shimmer -->
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/40 to-transparent -translate-x-full animate-shimmer-fast"></div>
            </div>
          </div>
          <div class="text-center mt-2">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ Math.round(prog) }}% Complete</span>
          </div>
        </div>

      </div>

      <!-- Floating Particles (Optional) -->
      <div class="absolute inset-0 pointer-events-none hidden sm:block">
        <div v-for="p in parts" :key="p.id" class="particle absolute rounded-full opacity-40" :class="p.c" :style="p.s"></div>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
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
  brandLetter: 'V'
})

// State
const isLoading = useState('pageLoading', () => false)
const prog = ref(0)

// Optimized props
const letter = props.brandLetter
const title = props.title
const msg = props.message
const showProg = props.showProgress

// ✅ OPTIMIZED: Reduced particles from 5 to 3 (mobile hidden)
const parts = [
  { id: 1, c: 'w-2 h-2 bg-blue-400', s: 'top: 20%; left: 10%; animation: float-particle 4s ease-in-out infinite;' },
  { id: 2, c: 'w-3 h-3 bg-purple-400', s: 'top: 60%; right: 15%; animation: float-particle 5s ease-in-out infinite; animation-delay: 1s;' },
  { id: 3, c: 'w-2 h-2 bg-pink-400', s: 'bottom: 30%; left: 20%; animation: float-particle 6s ease-in-out infinite; animation-delay: 2s;' }
]

// ✅ OPTIMIZED: Progress interval increased from 200ms to 400ms
let timer: NodeJS.Timeout | null = null

const startProg = () => {
  if (!showProg) return
  prog.value = 0
  timer = setInterval(() => {
    if (prog.value < 90) {
      prog.value += Math.random() * 15
    } else if (prog.value < 95) {
      prog.value += 2
    }
    if (prog.value >= 100) {
      prog.value = 100
      if (timer) {
        clearInterval(timer)
        timer = null
      }
    }
  }, 400) // ✅ Changed from 200ms to 400ms
}

// Watch
watch(isLoading, (val) => {
  if (val && showProg) {
    startProg()
  } else if (!val && timer) {
    clearInterval(timer)
    timer = null
    prog.value = 0
  }
})

// Cleanup
onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>

<style scoped>
/* ✅ OPTIMIZED: Reduced animation complexity */
@keyframes float-slow {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-20px); }
}

@keyframes float-slower {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(15px); }
}

@keyframes float-fast {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-30px); }
}

@keyframes spin-slow {
  to { transform: rotate(360deg); }
}

@keyframes spin-reverse {
  to { transform: rotate(-360deg); }
}

@keyframes pulse-slow {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

@keyframes shimmer {
  to { transform: translateX(200%); }
}

@keyframes shimmer-fast {
  to { transform: translateX(200%); }
}

@keyframes bounce-dot {
  0%, 20%, 53%, 80%, 100% { transform: translateY(0); }
  40%, 43% { transform: translateY(-8px); }
}

@keyframes float-particle {
  0%, 100% { transform: translateY(0) scale(1); opacity: 0.4; }
  50% { transform: translateY(-15px) scale(1.2); opacity: 0.8; }
}

/* Animation Classes */
.animate-float-slow { animation: float-slow 8s ease-in-out infinite; }
.animate-float-slower { animation: float-slower 10s ease-in-out infinite; }
.animate-float-fast { animation: float-fast 6s ease-in-out infinite; }
.animate-spin-slow { animation: spin-slow 3s linear infinite; }
.animate-spin-reverse { animation: spin-reverse 2s linear infinite; }
.animate-pulse-slow { animation: pulse-slow 2s ease-in-out infinite; }
.animate-shimmer { animation: shimmer 2s ease-in-out infinite; }
.animate-shimmer-fast { animation: shimmer-fast 1.5s ease-in-out infinite; }
.animate-bounce-dot { animation: bounce-dot 1.4s ease-in-out infinite both; }

/* ✅ OPTIMIZED: GPU acceleration */
.global-loader,
.animate-float-slow,
.animate-float-slower,
.animate-float-fast,
.animate-spin-slow,
.animate-spin-reverse,
.shimmer {
  will-change: transform;
  transform: translateZ(0);
}

/* Glass morphism */
.global-loader {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

/* ✅ OPTIMIZED: Simplified shadows */
.shadow-2xl {
  box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.3);
}

/* Responsive */
@media (max-width: 640px) {
  .w-80, .h-80 { width: 12rem; height: 12rem; }
  .w-72, .h-72 { width: 12rem; height: 12rem; }
  .w-64, .h-64 { width: 8rem; height: 8rem; }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
  * { animation: none !important; }
}
</style>
