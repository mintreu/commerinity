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

export interface ToastComposable {
    success: (options: string | Omit<ToastOptions, 'type'>) => Promise<string | undefined>
    error: (options: string | Omit<ToastOptions, 'type'>) => Promise<string | undefined>
    warning: (options: string | Omit<ToastOptions, 'type'>) => Promise<string | undefined>
    info: (options: string | Omit<ToastOptions, 'type'>) => Promise<string | undefined>
    question: (options: string | Omit<ToastOptions, 'type'>) => Promise<boolean>
    custom: (options: ToastOptions) => Promise<string | undefined>
    remove: (id: string) => Promise<void>
    clear: () => Promise<void>
    destroy: () => void
    isInitialized: () => boolean
    getInstance: () => any
}

// Nuxt 3/4 compatible module declarations
declare module '#app' {
    interface NuxtApp {
        $toast: ToastComposable
    }
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        $toast: ToastComposable
    }
}
