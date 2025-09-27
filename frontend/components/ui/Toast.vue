<template>
  <Teleport to="body">
    <div class="toast-container fixed top-4 right-4 z-[9999] space-y-3 pointer-events-none">
      <Transition
          v-for="toast in toasts"
          :key="toast.id"
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="opacity-0 transform translate-x-full scale-95"
          enter-to-class="opacity-100 transform translate-x-0 scale-100"
          leave-active-class="transition-all duration-300 ease-in"
          leave-from-class="opacity-100 transform translate-x-0 scale-100"
          leave-to-class="opacity-0 transform translate-x-full scale-95"
      >
        <div
            class="toast-item bg-white/95 dark:bg-gray-800/95 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/50 dark:border-gray-700/50 p-4 max-w-sm pointer-events-auto"
            :class="getToastClasses(toast.type)"
        >
          <div class="flex items-start gap-3">
            <!-- Icon -->
            <div class="toast-icon w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" :class="getIconClasses(toast.type)">
              <Icon :name="getIconName(toast.type)" class="w-5 h-5" />
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <h4 v-if="toast.title" class="font-bold text-sm text-gray-900 dark:text-white mb-1">
                {{ toast.title }}
              </h4>
              <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                {{ toast.message }}
              </p>

              <!-- Progress Bar -->
              <div v-if="toast.showProgress" class="mt-3">
                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-1 overflow-hidden">
                  <div
                      class="progress-bar h-full rounded-full transition-all duration-100 ease-linear"
                      :class="getProgressClasses(toast.type)"
                      :style="{ width: `${toast.progress}%` }"
                  ></div>
                </div>
              </div>
            </div>

            <!-- Close Button -->
            <button
                @click="removeToast(toast.id)"
                class="toast-close w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 flex-shrink-0"
            >
              <Icon name="mdi:close" class="w-4 h-4" />
            </button>
          </div>
        </div>
      </Transition>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

export interface ToastOptions {
  id?: string
  title?: string
  message: string
  type?: 'success' | 'error' | 'warning' | 'info'
  timeout?: number
  showProgress?: boolean
}

interface Toast extends Required<Omit<ToastOptions, 'title'>> {
  title?: string
  progress: number
  timer?: NodeJS.Timeout
  progressTimer?: NodeJS.Timeout
}

const toasts = ref<Toast[]>([])

const getIconName = (type: string) => {
  const icons = {
    success: 'mdi:check-circle',
    error: 'mdi:alert-circle',
    warning: 'mdi:alert',
    info: 'mdi:information'
  }
  return icons[type as keyof typeof icons] || icons.info
}

const getIconClasses = (type: string) => {
  const classes = {
    success: 'bg-gradient-to-br from-green-500 to-emerald-500 text-white',
    error: 'bg-gradient-to-br from-red-500 to-pink-500 text-white',
    warning: 'bg-gradient-to-br from-yellow-500 to-orange-500 text-white',
    info: 'bg-gradient-to-br from-blue-500 to-indigo-500 text-white'
  }
  return classes[type as keyof typeof classes] || classes.info
}

const getToastClasses = (type: string) => {
  const classes = {
    success: 'border-l-4 border-green-500',
    error: 'border-l-4 border-red-500',
    warning: 'border-l-4 border-yellow-500',
    info: 'border-l-4 border-blue-500'
  }
  return classes[type as keyof typeof classes] || classes.info
}

const getProgressClasses = (type: string) => {
  const classes = {
    success: 'bg-gradient-to-r from-green-500 to-emerald-500',
    error: 'bg-gradient-to-r from-red-500 to-pink-500',
    warning: 'bg-gradient-to-r from-yellow-500 to-orange-500',
    info: 'bg-gradient-to-r from-blue-500 to-indigo-500'
  }
  return classes[type as keyof typeof classes] || classes.info
}

const addToast = (options: ToastOptions) => {
  const id = options.id || `toast-${Date.now()}-${Math.random()}`
  const toast: Toast = {
    id,
    title: options.title,
    message: options.message,
    type: options.type || 'info',
    timeout: options.timeout || 5000,
    showProgress: options.showProgress !== false,
    progress: 100
  }

  toasts.value.unshift(toast)

  if (toast.showProgress && toast.timeout > 0) {
    const startTime = Date.now()
    toast.progressTimer = setInterval(() => {
      const elapsed = Date.now() - startTime
      const remaining = Math.max(0, toast.timeout - elapsed)
      toast.progress = (remaining / toast.timeout) * 100

      if (remaining <= 0) {
        clearInterval(toast.progressTimer)
      }
    }, 50)
  }

  if (toast.timeout > 0) {
    toast.timer = setTimeout(() => {
      removeToast(id)
    }, toast.timeout)
  }

  // Limit to 5 toasts
  if (toasts.value.length > 5) {
    const oldToast = toasts.value.pop()
    if (oldToast?.timer) clearTimeout(oldToast.timer)
    if (oldToast?.progressTimer) clearInterval(oldToast.progressTimer)
  }

  return id
}

const removeToast = (id: string) => {
  const index = toasts.value.findIndex(t => t.id === id)
  if (index > -1) {
    const toast = toasts.value[index]
    if (toast.timer) clearTimeout(toast.timer)
    if (toast.progressTimer) clearInterval(toast.progressTimer)
    toasts.value.splice(index, 1)
  }
}

const clearAllToasts = () => {
  toasts.value.forEach(toast => {
    if (toast.timer) clearTimeout(toast.timer)
    if (toast.progressTimer) clearInterval(toast.progressTimer)
  })
  toasts.value = []
}

onUnmounted(() => {
  clearAllToasts()
})

// Expose methods for global usage
defineExpose({
  addToast,
  removeToast,
  clearAllToasts
})
</script>

<style scoped>
.toast-container {
  max-width: calc(100vw - 2rem);
}

@media (max-width: 640px) {
  .toast-container {
    left: 1rem;
    right: 1rem;
    top: 1rem;
  }

  .toast-item {
    max-width: none;
  }
}

/* Custom progress bar animation */
.progress-bar {
  animation: progress-shrink linear;
}

@keyframes progress-shrink {
  from { width: 100%; }
  to { width: 0%; }
}
</style>
