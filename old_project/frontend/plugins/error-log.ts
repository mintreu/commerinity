export default defineNuxtPlugin((nuxtApp) => {
    const config = useRuntimeConfig()

    // Only enable in production
    if (process.env.NODE_ENV !== 'production') {
        return
    }

    // Global error handler
    nuxtApp.hook('vue:error', (error, instance, info) => {
        // Send to your backend API
        $fetch(`${config.public.apiBase}/log-error`, {
            method: 'POST',
            body: {
                message: error.message,
                stack: error.stack,
                componentName: instance?.$options?.name,
                info: info,
                url: window.location.href,
                userAgent: navigator.userAgent,
                timestamp: new Date().toISOString(),
            }
        }).catch(() => {
            // Silent fail - don't break app if logging fails
            console.error('Failed to log error:', error)
        })
    })

    // Catch unhandled promise rejections
    window.addEventListener('unhandledrejection', (event) => {
        $fetch(`${config.public.apiBase}/log-error`, {
            method: 'POST',
            body: {
                message: event.reason?.message || 'Unhandled Promise Rejection',
                stack: event.reason?.stack,
                type: 'unhandledrejection',
                url: window.location.href,
                timestamp: new Date().toISOString(),
            }
        }).catch(() => {})
    })
})
