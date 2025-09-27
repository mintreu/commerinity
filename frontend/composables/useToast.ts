import { ref } from 'vue'

interface ToastOptions {
    id?: string
    title?: string
    message: string
    type?: 'success' | 'error' | 'warning' | 'info'
    timeout?: number
    showProgress?: boolean
}

// Global toast state
const toastInstance = ref<any>(null)

export const useToast = () => {
    const setToastInstance = (instance: any) => {
        toastInstance.value = instance
    }

    const toast = {
        success: (options: string | Omit<ToastOptions, 'type'>) => {
            if (typeof options === 'string') {
                return toastInstance.value?.addToast({
                    message: options,
                    type: 'success'
                })
            }
            return toastInstance.value?.addToast({
                ...options,
                type: 'success'
            })
        },

        error: (options: string | Omit<ToastOptions, 'type'>) => {
            if (typeof options === 'string') {
                return toastInstance.value?.addToast({
                    message: options,
                    type: 'error'
                })
            }
            return toastInstance.value?.addToast({
                ...options,
                type: 'error'
            })
        },

        warning: (options: string | Omit<ToastOptions, 'type'>) => {
            if (typeof options === 'string') {
                return toastInstance.value?.addToast({
                    message: options,
                    type: 'warning'
                })
            }
            return toastInstance.value?.addToast({
                ...options,
                type: 'warning'
            })
        },

        info: (options: string | Omit<ToastOptions, 'type'>) => {
            if (typeof options === 'string') {
                return toastInstance.value?.addToast({
                    message: options,
                    type: 'info'
                })
            }
            return toastInstance.value?.addToast({
                ...options,
                type: 'info'
            })
        },

        remove: (id: string) => {
            return toastInstance.value?.removeToast(id)
        },

        clear: () => {
            return toastInstance.value?.clearAllToasts()
        }
    }

    return {
        toast,
        setToastInstance
    }
}
