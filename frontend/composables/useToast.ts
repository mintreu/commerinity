import { ref, nextTick, onScopeDispose } from 'vue'
import { createVNode, render, type VNode } from 'vue'

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

// Global state with proper SSR handling
const toastInstance = ref<any>(null)
const toastContainer = ref<HTMLElement | null>(null)
const mountedVNode = ref<VNode | null>(null)
let isInitialized = false

// Initialize toast system with proper error handling
const initializeToast = async (): Promise<void> => {
    // Skip on server-side rendering
    if (!process.client || isInitialized) return

    try {
        // Ensure DOM is ready
        if (document.readyState === 'loading') {
            await new Promise(resolve => {
                document.addEventListener('DOMContentLoaded', resolve, { once: true })
            })
        }

        // Dynamically import Toast component (Nuxt 3/4 compatible)
        const toastModule = await import('~/components/Toast.vue')
        const Toast = toastModule.default || toastModule

        // Create container with proper cleanup
        if (!toastContainer.value) {
            toastContainer.value = document.createElement('div')
            toastContainer.value.id = 'toast-root-container'
            toastContainer.value.setAttribute('data-toast-container', 'true')
            document.body.appendChild(toastContainer.value)
        }

        // Create and mount Vue component
        const vnode = createVNode(Toast)
        render(vnode, toastContainer.value)
        mountedVNode.value = vnode

        // Get component instance (compatible with both Nuxt 3 and 4)
        toastInstance.value = vnode.component?.exposed || vnode.component?.setupState

        if (!toastInstance.value) {
            console.warn('Toast component instance not found. Toast functionality may be limited.')
        }

        isInitialized = true
    } catch (error) {
        console.error('Failed to initialize toast system:', error)
        throw error
    }
}

// Cleanup function
const destroyToast = (): void => {
    if (!process.client) return

    try {
        if (mountedVNode.value && toastContainer.value) {
            render(null, toastContainer.value)
            mountedVNode.value = null
        }

        if (toastContainer.value?.parentNode) {
            toastContainer.value.parentNode.removeChild(toastContainer.value)
        }

        toastContainer.value = null
        toastInstance.value = null
        isInitialized = false
    } catch (error) {
        console.error('Error during toast cleanup:', error)
    }
}

// Ensure initialization before toast operations
const ensureInitialized = async (): Promise<boolean> => {
    if (!process.client) {
        console.warn('Toast system not available on server-side')
        return false
    }

    if (!isInitialized) {
        await initializeToast()
    }

    if (!toastInstance.value) {
        console.error('Toast system initialization failed')
        return false
    }

    return true
}

// Create type-specific toast methods
const createToastMethod = (defaultType: ToastType) => {
    return async (options: string | Omit<ToastOptions, 'type'>) => {
        const initialized = await ensureInitialized()
        if (!initialized) return

        await nextTick()

        const toastOptions: ToastOptions = typeof options === 'string'
            ? { message: options, type: defaultType }
            : { ...options, type: defaultType }

        return toastInstance.value.addToast(toastOptions)
    }
}

// Toast methods
const success = createToastMethod('success')
const error = createToastMethod('error')
const warning = createToastMethod('warning')
const info = createToastMethod('info')

// Enhanced question method with promise support
const question = async (options: string | Omit<ToastOptions, 'type'>): Promise<boolean> => {
    const initialized = await ensureInitialized()
    if (!initialized) return false

    await nextTick()

    return new Promise((resolve) => {
        const questionOptions: ToastOptions = typeof options === 'string'
            ? {
                message: options,
                type: 'question',
                timeout: 0,
                actions: [
                    { label: 'Yes', style: 'primary', callback: () => resolve(true) },
                    { label: 'No', style: 'secondary', callback: () => resolve(false) }
                ]
            }
            : {
                ...options,
                type: 'question',
                timeout: options.timeout ?? 0,
                actions: options.actions || [
                    { label: 'Yes', style: 'primary', callback: () => resolve(true) },
                    { label: 'No', style: 'secondary', callback: () => resolve(false) }
                ]
            }

        toastInstance.value.addToast(questionOptions)
    })
}

// Utility methods
const remove = async (id: string): Promise<void> => {
    if (!await ensureInitialized()) return
    await nextTick()
    toastInstance.value?.removeToast(id)
}

const clear = async (): Promise<void> => {
    if (!await ensureInitialized()) return
    await nextTick()
    toastInstance.value?.clearAllToasts()
}

const custom = async (options: ToastOptions) => {
    const initialized = await ensureInitialized()
    if (!initialized) return

    await nextTick()
    return toastInstance.value.addToast(options)
}

// Auto-cleanup on scope dispose (important for Nuxt 4)
if (process.client) {
    onScopeDispose(() => {
        destroyToast()
    })
}

// Main composable interface
export const useToast = () => {
    return {
        success,
        error,
        warning,
        info,
        question,
        custom,
        remove,
        clear,
        destroy: destroyToast,
        // Status methods
        isInitialized: () => isInitialized,
        getInstance: () => toastInstance.value
    }
}

// Default export for auto-imports
export default useToast
