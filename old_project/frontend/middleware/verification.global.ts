// middleware/verification.global.ts
// noinspection JSUnusedGlobalSymbols

import { defineNuxtRouteMiddleware, navigateTo } from 'nuxt/app'
import { useSanctum } from '#imports'

export default defineNuxtRouteMiddleware(async (to) => {
    const { user } = useSanctum()
    const toast = useToast()
    const SHOW_WARNING_INTERVAL_DAYS = 3

    if (!to.path.startsWith('/dashboard')) return
    if (!user.value) return

    // ✅ Prevent infinite redirect: skip if already on verify-mobile page
    const verifyMobilePath = '/dashboard/auth/my-mobile'
    if (!user.value.mobile || !user.value.mobile_verified) {
        if (to.path !== verifyMobilePath) {
            return navigateTo(verifyMobilePath)
        }
        return // already on verify page, do nothing
    }

    // Email warning logic
    if (user.value.email && !user.value.email_verified) {
        const now = new Date()
        const lastShown = localStorage.getItem('email-warning-shown')

        if (!lastShown || now.getTime() - new Date(lastShown).getTime() >
            SHOW_WARNING_INTERVAL_DAYS * 24 * 60 * 60 * 1000
        ) {
            toast.warning({
                title: 'Email not verified',
                message: 'Please verify your email to unlock all features.'
            })
            localStorage.setItem('email-warning-shown', now.toISOString())
        }
    }
})
