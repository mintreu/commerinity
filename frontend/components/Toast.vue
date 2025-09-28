<template>
  <Teleport to="body">
    <div
        v-for="(containerToasts, position) in groupedToasts"
        :key="position"
        class="toast-container fixed pointer-events-none z-[9999] space-y-3"
        :class="getContainerClasses(position as ToastPosition)"
    >
      <Transition
          v-for="toast in containerToasts"
          :key="toast.id"
          :enter-active-class="getEnterClass(position as ToastPosition)"
          :enter-from-class="getEnterFromClass(position as ToastPosition)"
          :enter-to-class="getEnterToClass(position as ToastPosition)"
          :leave-active-class="getLeaveClass(position as ToastPosition)"
          :leave-from-class="getLeaveFromClass(position as ToastPosition)"
          :leave-to-class="getLeaveToClass(position as ToastPosition)"
      >
        <div
            class="toast-item bg-white/95 dark:bg-gray-800/95 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/50 dark:border-gray-700/50 p-4 pointer-events-auto"
            :class="[getToastClasses(toast.type), getToastSizeClass(toast.size)]"
            role="alert"
            aria-live="polite"
            :aria-label="`${toast.type} notification: ${toast.title || ''} ${toast.message}`"
        >
          <div class="flex items-start gap-3">
            <!-- Icon -->
            <div
                v-if="!toast.hideIcon"
                class="toast-icon rounded-full flex items-center justify-center flex-shrink-0"
                :class="[getIconClasses(toast.type), getIconSizeClass(toast.size)]"
            >
              <Icon :name="toast.icon || getIconName(toast.type)" :class="getIconElementClass(toast.size)" />
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <h4
                  v-if="toast.title"
                  class="font-bold text-gray-900 dark:text-white mb-1"
                  :class="getTitleSizeClass(toast.size)"
              >
                {{ toast.title }}
              </h4>
              <p
                  class="text-gray-700 dark:text-gray-300 leading-relaxed"
                  :class="getMessageSizeClass(toast.size)"
              >
                {{ toast.message }}
              </p>

              <!-- Action Buttons (for question type) -->
              <div v-if="toast.type === 'question' && toast.actions" class="flex gap-2 mt-3">
                <button
                    v-for="action in toast.actions"
                    :key="action.label"
                    @click="handleAction(toast, action)"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200 hover:transform hover:scale-105"
                    :class="action.style === 'primary'
                    ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-md'
                    : 'bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300'"
                >
                  {{ action.label }}
                </button>
              </div>

              <!-- Progress Bar -->
              <div v-if="toast.showProgress && toast.timeout > 0" class="mt-3">
                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden" :class="getProgressBarSizeClass(toast.size)">
                  <div
                      class="progress-bar rounded-full transition-all duration-100 ease-linear h-full"
                      :class="getProgressClasses(toast.type)"
                      :style="{ width: `${toast.progress}%` }"
                  ></div>
                </div>
              </div>
            </div>

            <!-- Close Button -->
            <button
                v-if="!toast.hideClose"
                @click="removeToast(toast.id)"
                class="toast-close rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 flex-shrink-0 hover:transform hover:scale-110"
                :class="getCloseSizeClass(toast.size)"
                :aria-label="'Close notification'"
            >
              <Icon name="mdi:close" :class="getCloseIconSizeClass(toast.size)" />
            </button>
          </div>
        </div>
      </Transition>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, onUnmounted, onErrorCaptured } from 'vue'

export type ToastType = 'success' | 'error' | 'warning' | 'info' | 'question'
export type ToastPosition = 'topLeft' | 'topCenter' | 'topRight' | 'bottomLeft' | 'bottomCenter' | 'bottomRight' | 'center'
export type ToastSize = 'sm' | 'md' | 'lg'

export interface ToastAction {
  label: string
  style?: 'primary' | 'secondary'
  callback?: () => void | Promise<void>
}

export interface ToastOptions {
  id?: string
  title?: string
  message: string
  type?: ToastType
  position?: ToastPosition
  timeout?: number
  showProgress?: boolean
  hideIcon?: boolean
  hideClose?: boolean
  icon?: string
  size?: ToastSize
  actions?: ToastAction[]
}

interface Toast extends Required<Omit<ToastOptions, 'title' | 'actions'>> {
  title?: string
  actions?: ToastAction[]
  progress: number
  timer?: NodeJS.Timeout
  progressTimer?: NodeJS.Timeout
}

const toasts = ref<Toast[]>([])

// Error handling for better Nuxt 3/4 compatibility
onErrorCaptured((error, instance, errorInfo) => {
  console.error('Toast component error:', error, errorInfo)
  // Prevent error propagation
  return false
})

// Group toasts by position
const groupedToasts = computed(() => {
  return toasts.value.reduce((groups, toast) => {
    if (!groups[toast.position]) {
      groups[toast.position] = []
    }
    groups[toast.position].push(toast)
    return groups
  }, {} as Record<ToastPosition, Toast[]>)
})

// Position classes
const getContainerClasses = (position: ToastPosition) => {
  const classes = {
    topLeft: 'top-4 left-4',
    topCenter: 'top-4 left-1/2 -translate-x-1/2',
    topRight: 'top-4 right-4',
    bottomLeft: 'bottom-4 left-4',
    bottomCenter: 'bottom-4 left-1/2 -translate-x-1/2',
    bottomRight: 'bottom-4 right-4',
    center: 'top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2'
  }
  return classes[position]
}

// Animation classes based on position
const getEnterClass = (position: ToastPosition) => 'transition-all duration-300 ease-out'
const getLeaveClass = (position: ToastPosition) => 'transition-all duration-300 ease-in'

const getEnterFromClass = (position: ToastPosition) => {
  const classes = {
    topLeft: 'opacity-0 transform -translate-x-full scale-95',
    topCenter: 'opacity-0 transform -translate-y-full scale-95',
    topRight: 'opacity-0 transform translate-x-full scale-95',
    bottomLeft: 'opacity-0 transform -translate-x-full scale-95',
    bottomCenter: 'opacity-0 transform translate-y-full scale-95',
    bottomRight: 'opacity-0 transform translate-x-full scale-95',
    center: 'opacity-0 transform scale-95'
  }
  return classes[position]
}

const getEnterToClass = (position: ToastPosition) => 'opacity-100 transform translate-x-0 translate-y-0 scale-100'

const getLeaveFromClass = (position: ToastPosition) => 'opacity-100 transform translate-x-0 translate-y-0 scale-100'

const getLeaveToClass = (position: ToastPosition) => {
  const classes = {
    topLeft: 'opacity-0 transform -translate-x-full scale-95',
    topCenter: 'opacity-0 transform -translate-y-full scale-95',
    topRight: 'opacity-0 transform translate-x-full scale-95',
    bottomLeft: 'opacity-0 transform -translate-x-full scale-95',
    bottomCenter: 'opacity-0 transform translate-y-full scale-95',
    bottomRight: 'opacity-0 transform translate-x-full scale-95',
    center: 'opacity-0 transform scale-95'
  }
  return classes[position]
}

// Size classes
const getToastSizeClass = (size: ToastSize) => {
  const classes = {
    sm: 'max-w-xs',
    md: 'max-w-sm',
    lg: 'max-w-md'
  }
  return classes[size]
}

const getIconSizeClass = (size: ToastSize) => {
  const classes = {
    sm: 'w-8 h-8',
    md: 'w-10 h-10',
    lg: 'w-12 h-12'
  }
  return classes[size]
}

const getIconElementClass = (size: ToastSize) => {
  const classes = {
    sm: 'w-4 h-4',
    md: 'w-5 h-5',
    lg: 'w-6 h-6'
  }
  return classes[size]
}

const getTitleSizeClass = (size: ToastSize) => {
  const classes = {
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base'
  }
  return classes[size]
}

const getMessageSizeClass = (size: ToastSize) => {
  const classes = {
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-sm'
  }
  return classes[size]
}

const getCloseSizeClass = (size: ToastSize) => {
  const classes = {
    sm: 'w-6 h-6',
    md: 'w-8 h-8',
    lg: 'w-10 h-10'
  }
  return classes[size]
}

const getCloseIconSizeClass = (size: ToastSize) => {
  const classes = {
    sm: 'w-3 h-3',
    md: 'w-4 h-4',
    lg: 'w-5 h-5'
  }
  return classes[size]
}

const getProgressBarSizeClass = (size: ToastSize) => {
  const classes = {
    sm: 'h-0.5',
    md: 'h-1',
    lg: 'h-1.5'
  }
  return classes[size]
}

// Icon and color classes
const getIconName = (type: ToastType) => {
  const icons = {
    success: 'mdi:check-circle',
    error: 'mdi:alert-circle',
    warning: 'mdi:alert',
    info: 'mdi:information',
    question: 'mdi:help-circle'
  }
  return icons[type]
}

const getIconClasses = (type: ToastType) => {
  const classes = {
    success: 'bg-gradient-to-br from-green-500 to-emerald-500 text-white shadow-lg',
    error: 'bg-gradient-to-br from-red-500 to-pink-500 text-white shadow-lg',
    warning: 'bg-gradient-to-br from-yellow-500 to-orange-500 text-white shadow-lg',
    info: 'bg-gradient-to-br from-blue-500 to-indigo-500 text-white shadow-lg',
    question: 'bg-gradient-to-br from-purple-500 to-indigo-500 text-white shadow-lg'
  }
  return classes[type]
}

const getToastClasses = (type: ToastType) => {
  const classes = {
    success: 'border-l-4 border-green-500',
    error: 'border-l-4 border-red-500',
    warning: 'border-l-4 border-yellow-500',
    info: 'border-l-4 border-blue-500',
    question: 'border-l-4 border-purple-500'
  }
  return classes[type]
}

const getProgressClasses = (type: ToastType) => {
  const classes = {
    success: 'bg-gradient-to-r from-green-500 to-emerald-500',
    error: 'bg-gradient-to-r from-red-500 to-pink-500',
    warning: 'bg-gradient-to-r from-yellow-500 to-orange-500',
    info: 'bg-gradient-to-r from-blue-500 to-indigo-500',
    question: 'bg-gradient-to-r from-purple-500 to-indigo-500'
  }
  return classes[type]
}

// ✅ SINGLE addToast method with proper error handling and complete logic
const addToast = (options: ToastOptions): string | undefined => {
  try {
    const id = options.id || `toast-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`

    const toast: Toast = {
      id,
      title: options.title,
      message: options.message,
      type: options.type || 'info',
      position: options.position || 'topRight',
      timeout: options.timeout === 0 ? 0 : (options.timeout || 5000),
      showProgress: options.showProgress !== false,
      hideIcon: options.hideIcon || false,
      hideClose: options.hideClose || false,
      icon: options.icon,
      size: options.size || 'md',
      actions: options.actions,
      progress: 100
    }

    toasts.value.unshift(toast)

    // Setup progress bar with proper cleanup
    if (toast.showProgress && toast.timeout > 0) {
      const startTime = Date.now()
      toast.progressTimer = setInterval(() => {
        const elapsed = Date.now() - startTime
        const remaining = Math.max(0, toast.timeout - elapsed)
        toast.progress = (remaining / toast.timeout) * 100

        if (remaining <= 0) {
          if (toast.progressTimer) {
            clearInterval(toast.progressTimer)
            toast.progressTimer = undefined
          }
        }
      }, 50)
    }

    // Setup auto-dismiss timer
    if (toast.timeout > 0) {
      toast.timer = setTimeout(() => {
        removeToast(id)
      }, toast.timeout)
    }

    // Limit toasts per position to prevent overflow
    const positionToasts = toasts.value.filter(t => t.position === toast.position)
    if (positionToasts.length > 5) {
      const oldToast = positionToasts[positionToasts.length - 1]
      removeToast(oldToast.id)
    }

    return id
  } catch (error) {
    console.error('Error adding toast:', error)
    return undefined
  }
}

const removeToast = (id: string): void => {
  try {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index > -1) {
      const toast = toasts.value[index]

      // Clear timers
      if (toast.timer) {
        clearTimeout(toast.timer)
        toast.timer = undefined
      }
      if (toast.progressTimer) {
        clearInterval(toast.progressTimer)
        toast.progressTimer = undefined
      }

      toasts.value.splice(index, 1)
    }
  } catch (error) {
    console.error('Error removing toast:', error)
  }
}

const handleAction = async (toast: Toast, action: ToastAction): Promise<void> => {
  try {
    if (action.callback) {
      await action.callback()
    }
  } catch (error) {
    console.error('Toast action callback error:', error)
    // Show error toast for failed actions
    addToast({
      type: 'error',
      title: 'Action Failed',
      message: 'There was an error processing your request.',
      timeout: 3000
    })
  } finally {
    // Always remove the question toast after action
    removeToast(toast.id)
  }
}

const clearAllToasts = (): void => {
  try {
    toasts.value.forEach(toast => {
      if (toast.timer) {
        clearTimeout(toast.timer)
      }
      if (toast.progressTimer) {
        clearInterval(toast.progressTimer)
      }
    })
    toasts.value = []
  } catch (error) {
    console.error('Error clearing toasts:', error)
  }
}

// Cleanup on component unmount
onUnmounted(() => {
  clearAllToasts()
})

// Expose methods for global usage
defineExpose({
  addToast,
  removeToast,
  clearAllToasts,
  // Additional utility methods
  getToasts: () => toasts.value,
  getToastCount: () => toasts.value.length,
  hasToasts: () => toasts.value.length > 0
})
</script>

<style scoped>
.toast-container {
  max-width: calc(100vw - 2rem);
  z-index: 9999;
}

.toast-item {
  /* Enhanced shadow and backdrop effects */
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  box-shadow:
      0 20px 25px -5px rgba(0, 0, 0, 0.1),
      0 10px 10px -5px rgba(0, 0, 0, 0.04),
      0 0 0 1px rgba(255, 255, 255, 0.1);

  /* Smooth transitions */
  transition: all 0.3s ease;
}

.toast-item:hover {
  transform: translateY(-2px);
  box-shadow:
      0 25px 30px -5px rgba(0, 0, 0, 0.15),
      0 15px 15px -5px rgba(0, 0, 0, 0.08),
      0 0 0 1px rgba(255, 255, 255, 0.1);
}

/* Responsive design for mobile */
@media (max-width: 640px) {
  .toast-container {
    left: 1rem !important;
    right: 1rem !important;
    top: 1rem !important;
    bottom: 1rem !important;
    transform: none !important;
    max-width: calc(100vw - 2rem);
  }

  .toast-item {
    max-width: none;
    width: 100%;
  }
}

/* Enhanced progress bar animation */
.progress-bar {
  animation: progress-shrink linear;
  border-radius: inherit;
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
}

@keyframes progress-shrink {
  from {
    width: 100%;
    opacity: 1;
  }
  to {
    width: 0%;
    opacity: 0.8;
  }
}

/* Enhanced button interactions */
.toast-item button {
  transition: all 0.2s ease;
}

.toast-item button:hover {
  transform: scale(1.05);
}

.toast-item button:active {
  transform: scale(0.95);
}

/* Icon animations */
.toast-icon {
  transition: all 0.3s ease;
}

.toast-item:hover .toast-icon {
  transform: scale(1.1);
}

/* Improved accessibility */
@media (prefers-reduced-motion: reduce) {
  .toast-item,
  .toast-icon,
  .toast-item button,
  .progress-bar {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}

/* Focus styles for accessibility */
.toast-item:focus-within {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .toast-item {
    border: 2px solid currentColor;
    background: white;
    color: black;
  }

  .dark .toast-item {
    background: black;
    color: white;
  }
}
</style>
