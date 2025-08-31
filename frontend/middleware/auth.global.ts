// middleware/auth.ts
// noinspection JSUnusedGlobalSymbols

import { defineNuxtRouteMiddleware, navigateTo } from 'nuxt/app'
import { useSanctum } from '#imports'

export default defineNuxtRouteMiddleware(async (to) => {
    const { isLoggedIn, fetchUser } = useSanctum()

    // Only apply auth check to routes that require it
    const requiresAuth = to.meta?.requiresAuth || to.meta?.layout === 'dashboard'

    if (!requiresAuth) return

    // Try to fetch user if not already
    if (!isLoggedIn.value) {
        try {
            await fetchUser()
        } catch (error) {
            return navigateTo('/auth/login')
        }
    }

    // Redirect if still not logged in
    if (!isLoggedIn.value) {
        return navigateTo('/auth/login')
    }
})
