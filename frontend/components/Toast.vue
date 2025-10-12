<template>
  <Teleport to="body">
    <div
        v-for="(containerToasts, position) in groupedToasts"
        :key="position"
        class="toast-container fixed pointer-events-none z-[9999]"
        :class="getContainerClasses(position as ToastPosition)"
    >
      <TransitionGroup
          name="toast"
          tag="div"
          class="flex flex-col gap-2 sm:gap-3"
      >
        <div
            v-for="toast in containerToasts"
            :key="toast.id"
            class="toast-item pointer-events-auto"
            :class="[getToastClasses(toast.type), getToastSizeClass(toast.size)]"
            role="alert"
            aria-live="polite"
            :aria-label="`${toast.type} notification: ${toast.title || ''} ${toast.message}`"
        >
          <!-- Toast Content -->
          <div class="flex items-start gap-3">
            <!-- Icon -->
            <div
                v-if="!toast.hideIcon"
                class="toast-icon flex-shrink-0 flex items-center justify-center"
                :class="[getIconClasses(toast.type), getIconSizeClass(toast.size)]"
            >
              <component :is="getIconComponent(toast.type)" :class="getIconElementClass(toast.size)" />
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <h4
                  v-if="toast.title"
                  class="font-bold text-gray-900 dark:text-white mb-1 leading-tight"
                  :class="getTitleSizeClass(toast.size)"
              >
                {{ toast.title }}
              </h4>
              <p
                  class="text-gray-700 dark:text-gray-200 leading-relaxed"
                  :class="getMessageSizeClass(toast.size)"
              >
                {{ toast.message }}
              </p>

              <!-- Action Buttons -->
              <div v-if="toast.type === 'question' && toast.actions" class="flex gap-2 mt-3">
                <button
                    v-for="action in toast.actions"
                    :key="action.label"
                    @click="handleAction(toast, action)"
                    class="flex-1 sm:flex-none px-4 py-2 text-sm font-bold rounded-xl transition-all duration-200 active:scale-95"
                    :class="action.style === 'primary'
                    ? 'bg-purple-600 hover:bg-purple-700 text-white shadow-lg'
                    : 'bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200'"
                >
                  {{ action.label }}
                </button>
              </div>

              <!-- Progress Bar -->
              <div v-if="toast.showProgress && toast.timeout > 0" class="mt-3">
                <div class="w-full bg-gray-200/50 dark:bg-gray-700/50 rounded-full overflow-hidden" :class="getProgressBarSizeClass(toast.size)">
                  <div
                      class="progress-bar rounded-full h-full transition-all duration-100 ease-linear"
                      :class="getProgressClasses(toast.type)"
                      :style="{ width: `${toast.progress}%` }"
                  />
                </div>
              </div>
            </div>

            <!-- Close Button -->
            <button
                v-if="!toast.hideClose"
                @click="removeToast(toast.id)"
                class="toast-close flex-shrink-0 rounded-full flex items-center justify-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200 active:scale-90"
                :class="getCloseSizeClass(toast.size)"
                aria-label="Close notification"
            >
              <CloseIcon :class="getCloseIconSizeClass(toast.size)" />
            </button>
          </div>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, onUnmounted, h } from 'vue'

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

// SVG Icon Components (no dependency on Nuxt Icon)
const SuccessIcon = (props: any) => h('svg', {
  xmlns: 'http://www.w3.org/2000/svg',
  viewBox: '0 0 24 24',
  fill: 'currentColor',
  ...props
}, [
  h('path', { d: 'M12 2C6.5 2 2 6.5 2 12S6.5 22 12 22 22 17.5 22 12 17.5 2 12 2M10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z' })
])

const ErrorIcon = (props: any) => h('svg', {
  xmlns: 'http://www.w3.org/2000/svg',
  viewBox: '0 0 24 24',
  fill: 'currentColor',
  ...props
}, [
  h('path', { d: 'M13 13H11V7H13M13 17H11V15H13M12 2C6.48 2 2 6.48 2 12S6.48 22 12 22 22 17.52 22 12 17.52 2 12 2Z' })
])

const WarningIcon = (props: any) => h('svg', {
  xmlns: 'http://www.w3.org/2000/svg',
  viewBox: '0 0 24 24',
  fill: 'currentColor',
  ...props
}, [
  h('path', { d: 'M13 14H11V10H13M13 18H11V16H13M1 21H23L12 2L1 21Z' })
])

const InfoIcon = (props: any) => h('svg', {
  xmlns: 'http://www.w3.org/2000/svg',
  viewBox: '0 0 24 24',
  fill: 'currentColor',
  ...props
}, [
  h('path', { d: 'M13 9H11V7H13M13 17H11V11H13M12 2C6.48 2 2 6.48 2 12S6.48 22 12 22 22 17.52 22 12 17.52 2 12 2Z' })
])

const QuestionIcon = (props: any) => h('svg', {
  xmlns: 'http://www.w3.org/2000/svg',
  viewBox: '0 0 24 24',
  fill: 'currentColor',
  ...props
}, [
  h('path', { d: 'M13.5 17H12V15.5H13.5M12 13H13.5C13.5 11.84 14.67 11.25 15.5 10.5C16.3 9.75 17 8.86 17 7.5C17 5.57 15.43 4 13.5 4S10 5.57 10 7.5H11.5C11.5 6.4 12.4 5.5 13.5 5.5S15.5 6.4 15.5 7.5C15.5 8.5 14.33 9.17 13.5 9.92C12.67 10.67 12 11.44 12 13M12 2C6.48 2 2 6.48 2 12S6.48 22 12 22 22 17.52 22 12 17.52 2 12 2Z' })
])

const CloseIcon = (props: any) => h('svg', {
  xmlns: 'http://www.w3.org/2000/svg',
  viewBox: '0 0 24 24',
  fill: 'currentColor',
  ...props
}, [
  h('path', { d: 'M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12L19 6.41Z' })
])

const toasts = ref<Toast[]>([])

const groupedToasts = computed(() => {
  return toasts.value.reduce((groups, toast) => {
    if (!groups[toast.position]) {
      groups[toast.position] = []
    }
    groups[toast.position].push(toast)
    return groups
  }, {} as Record<ToastPosition, Toast[]>)
})

// Get icon component
const getIconComponent = (type: ToastType) => {
  const icons = {
    success: SuccessIcon,
    error: ErrorIcon,
    warning: WarningIcon,
    info: InfoIcon,
    question: QuestionIcon
  }
  return icons[type]
}

// Container positioning
const getContainerClasses = (position: ToastPosition) => {
  const baseClasses = 'w-full sm:w-auto max-w-full sm:max-w-md'
  const positionClasses = {
    topLeft: 'top-0 sm:top-4 left-0 sm:left-4',
    topCenter: 'top-0 sm:top-4 left-0 sm:left-1/2 sm:-translate-x-1/2',
    topRight: 'top-0 sm:top-4 right-0 sm:right-4',
    bottomLeft: 'bottom-0 sm:bottom-4 left-0 sm:left-4',
    bottomCenter: 'bottom-0 sm:bottom-4 left-0 sm:left-1/2 sm:-translate-x-1/2',
    bottomRight: 'bottom-0 sm:bottom-4 right-0 sm:right-4',
    center: 'top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2'
  }
  return `${baseClasses} ${positionClasses[position]}`
}

// Size classes
const getToastSizeClass = (size: ToastSize) => {
  const classes = {
    sm: 'p-3',
    md: 'p-4',
    lg: 'p-5'
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
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg'
  }
  return classes[size]
}

const getMessageSizeClass = (size: ToastSize) => {
  const classes = {
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base'
  }
  return classes[size]
}

const getCloseSizeClass = (size: ToastSize) => {
  const classes = {
    sm: 'w-7 h-7',
    md: 'w-8 h-8',
    lg: 'w-9 h-9'
  }
  return classes[size]
}

const getCloseIconSizeClass = (size: ToastSize) => {
  const classes = {
    sm: 'w-4 h-4',
    md: 'w-5 h-5',
    lg: 'w-6 h-6'
  }
  return classes[size]
}

const getProgressBarSizeClass = (size: ToastSize) => {
  const classes = {
    sm: 'h-1',
    md: 'h-1',
    lg: 'h-1.5'
  }
  return classes[size]
}

const getIconClasses = (type: ToastType) => {
  const classes = {
    success: 'bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-lg',
    error: 'bg-gradient-to-br from-red-500 to-pink-600 text-white shadow-lg',
    warning: 'bg-gradient-to-br from-yellow-500 to-orange-600 text-white shadow-lg',
    info: 'bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg',
    question: 'bg-gradient-to-br from-purple-500 to-indigo-600 text-white shadow-lg'
  }
  return `${classes[type]} rounded-xl sm:rounded-2xl`
}

const getToastClasses = (type: ToastType) => {
  const baseClasses = 'bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl shadow-2xl border-l-4 rounded-none sm:rounded-2xl'
  const borderClasses = {
    success: 'border-green-500',
    error: 'border-red-500',
    warning: 'border-yellow-500',
    info: 'border-blue-500',
    question: 'border-purple-500'
  }
  return `${baseClasses} ${borderClasses[type]}`
}

const getProgressClasses = (type: ToastType) => {
  const classes = {
    success: 'bg-gradient-to-r from-green-500 to-emerald-600',
    error: 'bg-gradient-to-r from-red-500 to-pink-600',
    warning: 'bg-gradient-to-r from-yellow-500 to-orange-600',
    info: 'bg-gradient-to-r from-blue-500 to-indigo-600',
    question: 'bg-gradient-to-r from-purple-500 to-indigo-600'
  }
  return classes[type]
}

const addToast = (options: ToastOptions): string | undefined => {
  if (!process.client) return undefined

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

    if (toast.showProgress && toast.timeout > 0) {
      const startTime = Date.now()
      toast.progressTimer = setInterval(() => {
        const elapsed = Date.now() - startTime
        const remaining = Math.max(0, toast.timeout - elapsed)
        toast.progress = (remaining / toast.timeout) * 100

        if (remaining <= 0 && toast.progressTimer) {
          clearInterval(toast.progressTimer)
          toast.progressTimer = undefined
        }
      }, 50)
    }

    if (toast.timeout > 0) {
      toast.timer = setTimeout(() => {
        removeToast(id)
      }, toast.timeout)
    }

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
  if (!process.client) return

  try {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index > -1) {
      const toast = toasts.value[index]
      if (toast.timer) clearTimeout(toast.timer)
      if (toast.progressTimer) clearInterval(toast.progressTimer)
      toasts.value.splice(index, 1)
    }
  } catch (error) {
    console.error('Error removing toast:', error)
  }
}

const handleAction = async (toast: Toast, action: ToastAction): Promise<void> => {
  try {
    if (action.callback) await action.callback()
  } catch (error) {
    console.error('Toast action error:', error)
  } finally {
    removeToast(toast.id)
  }
}

const clearAllToasts = (): void => {
  if (!process.client) return

  toasts.value.forEach(toast => {
    if (toast.timer) clearTimeout(toast.timer)
    if (toast.progressTimer) clearInterval(toast.progressTimer)
  })
  toasts.value = []
}

onUnmounted(() => {
  clearAllToasts()
})

defineExpose({
  addToast,
  removeToast,
  clearAllToasts,
  getToasts: () => toasts.value,
  getToastCount: () => toasts.value.length,
  hasToasts: () => toasts.value.length > 0
})
</script>

<style scoped>
/* Toast animations */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-enter-from {
  opacity: 0;
  transform: translateY(-100%) scale(0.95);
}

.toast-enter-to {
  opacity: 1;
  transform: translateY(0) scale(1);
}

.toast-leave-from {
  opacity: 1;
  transform: translateY(0) scale(1);
}

.toast-leave-to {
  opacity: 0;
  transform: translateY(-20px) scale(0.95);
}

.toast-move {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Mobile-first: Native notification style */
@media (max-width: 639px) {
  .toast-container {
    left: 0 !important;
    right: 0 !important;
    transform: none !important;
  }

  .toast-item {
    border-radius: 0 !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  }
}

/* Desktop: Rounded card style */
@media (min-width: 640px) {
  .toast-item {
    box-shadow:
        0 20px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04),
        0 0 0 1px rgba(0, 0, 0, 0.05);
  }

  .toast-item:hover {
    transform: translateY(-2px);
    box-shadow:
        0 25px 30px -5px rgba(0, 0, 0, 0.15),
        0 15px 15px -5px rgba(0, 0, 0, 0.08);
  }
}

/* Progress bar animation */
.progress-bar {
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
}
</style>
