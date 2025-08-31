import * as Sentry from '@sentry/nuxt'
import { useRuntimeConfig } from '#app'

export default defineNuxtPlugin(() => {
    const config = useRuntimeConfig()

    Sentry.init({
        dsn: config.public.sentry.dsn,
        integrations: [
            Sentry.browserTracingIntegration(),
            Sentry.replayIntegration(),
        ],
        tracesSampleRate: 1.0, // Adjust in production
        replaysSessionSampleRate: 0.1,
        replaysOnErrorSampleRate: 1.0,
        sendDefaultPii: true
    })

    console.log('✅ Sentry initialized with DSN:', config.public.sentry.dsn)
})
