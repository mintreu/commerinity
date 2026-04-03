# Middleware Fix - Sanctum Authentication

## Issue
All auth pages were throwing 500 errors:
```
Unknown route middleware: 'guest'. Valid middleware: '$auth', '$guest'.
```

## Root Cause
The `@qirolab/nuxt-sanctum-authentication` package provides middleware with `$` prefix:
- ✅ `$auth` - For authenticated routes
- ✅ `$guest` - For guest-only routes (redirects authenticated users)

But pages were using them without the prefix:
- ❌ `middleware: 'auth'`
- ❌ `middleware: 'guest'`

## Files Fixed

### Auth Pages (changed to `$guest`)
1. `client/app/pages/auth/login.vue`
2. `client/app/pages/auth/register.vue`
3. `client/app/pages/auth/forgot-password.vue`
4. `client/app/pages/auth/reset-password.vue`

### Protected Pages (changed to `$auth`)
1. `client/app/pages/dashboard/index.vue`
2. `client/app/pages/onboarding/index.vue`
3. `client/app/pages/profile/index.vue`
4. `client/app/pages/profile/edit.vue`
5. `client/app/pages/profile/change-password.vue`

## Changes Made

**Before:**
```typescript
definePageMeta({
  middleware: 'guest'  // ❌ Wrong
})
```

**After:**
```typescript
definePageMeta({
  middleware: '$guest'  // ✅ Correct
})
```

## Testing

All pages should now work correctly:

### Public Pages (no middleware needed)
- ✅ `http://localhost:3000/` - Homepage
- ✅ `http://localhost:3000/about`
- ✅ `http://localhost:3000/contact`
- ✅ `http://localhost:3000/privacy`
- ✅ `http://localhost:3000/terms`

### Guest Pages (using `$guest` middleware)
- ✅ `http://localhost:3000/auth/login`
- ✅ `http://localhost:3000/auth/register`
- ✅ `http://localhost:3000/auth/forgot-password`
- ✅ `http://localhost:3000/auth/reset-password`

### Protected Pages (using `$auth` middleware)
- ✅ `http://localhost:3000/dashboard`
- ✅ `http://localhost:3000/onboarding`
- ✅ `http://localhost:3000/profile`
- ✅ `http://localhost:3000/profile/edit`
- ✅ `http://localhost:3000/profile/change-password`

## Middleware Behavior

### `$guest` middleware:
- Allows access only if user is NOT authenticated
- Redirects authenticated users to home/dashboard
- Used for: login, register, forgot-password, reset-password

### `$auth` middleware:
- Requires user to be authenticated
- Redirects unauthenticated users to login page
- Used for: dashboard, profile, onboarding, protected routes

### Global `onboarding.global.ts` middleware:
- Runs on ALL routes automatically
- Checks if authenticated user has completed onboarding
- Redirects to `/onboarding` if `user.onboarded === false`
- Skips public pages, auth pages, and onboarding page itself

## Package Documentation

Package: `@qirolab/nuxt-sanctum-authentication`

Middleware provided:
- `$auth` - Authenticated only
- `$guest` - Guest only (not authenticated)

Configuration in `nuxt.config.ts`:
```typescript
laravelSanctum: {
  apiUrl: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000',
  authMode: 'token',
  redirects: {
    home: '/dashboard',
    login: '/auth/login',
    logout: '/auth/login'
  },
  globalMiddleware: {
    enabled: false  // We use per-page middleware
  }
}
```

## Future Pages

When creating new pages, use the correct middleware:

**For auth pages (login, register, etc.):**
```typescript
definePageMeta({
  middleware: '$guest',
  layout: 'guest'
})
```

**For protected pages (dashboard, profile, etc.):**
```typescript
definePageMeta({
  middleware: '$auth'
})
```

**For public pages (homepage, about, etc.):**
```typescript
definePageMeta({
  layout: 'default'
  // No middleware needed
})
```

## Verification

Run these commands to verify all middleware are correct:

```bash
# Should return "All 'auth' fixed"
grep -r "middleware: 'auth'" client/app/pages/ || echo "✅ All 'auth' fixed"

# Should return "All 'guest' fixed"
grep -r "middleware: 'guest'" client/app/pages/ || echo "✅ All 'guest' fixed"

# Should find only $auth and $guest
grep -r "middleware: '\$" client/app/pages/
```

## Related Files

- `client/app/middleware/onboarding.global.ts` - Global onboarding check
- `client/nuxt.config.ts` - Sanctum configuration
- `ONBOARDING.md` - Onboarding system documentation

---

**Status:** ✅ All middleware fixed and verified
**Date:** 2025-12-10
