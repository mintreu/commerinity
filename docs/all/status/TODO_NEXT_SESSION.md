# TODO - Next Session

## Priority: Fix Auth Pages API Fetch Methods

### Issue
Login and register pages are failing because they're using incorrect API fetch patterns.

### Pages to Fix

1. **`client/app/pages/auth/login.vue`**
   - Check all `$fetch` calls
   - Replace with `useSanctumFetch`
   - Ensure full URL with `${config.public.apiBase}`

2. **`client/app/pages/auth/register.vue`**
   - Check all `$fetch` calls
   - Replace with `useSanctumFetch`
   - Ensure full URL with `${config.public.apiBase}`

3. **`client/app/pages/auth/forgot-password.vue`**
   - Check all `$fetch` calls
   - Replace with `useSanctumFetch`
   - Ensure full URL with `${config.public.apiBase}`

4. **`client/app/pages/auth/reset-password.vue`**
   - Check all `$fetch` calls
   - Replace with `useSanctumFetch`
   - Ensure full URL with `${config.public.apiBase}`

### Correct Pattern to Apply

**Wrong:**
```typescript
await $fetch(`${config.public.apiBase}/api/auth/send-otp`, {
  method: 'POST',
  body: { type: 'mobile', value: mobile }
})
```

**Correct:**
```typescript
await useSanctumFetch(`${config.public.apiBase}/api/auth/send-otp`, {
  method: 'POST',
  body: { type: 'mobile', value: mobile.trim() }
})
```

### Search for Issues

```bash
# Find all $fetch usage in auth pages
grep -n "\$fetch" client/app/pages/auth/*.vue

# Find all $api usage (should be none)
grep -n "\$api" client/app/pages/auth/*.vue
```

### Verification Steps

1. Fix all API calls to use `useSanctumFetch`
2. Test register flow
3. Test login flow
4. Test forgot password flow
5. Test reset password flow
6. Verify OTP sending/verification
7. Check error handling works
8. Check toast notifications display

### Reference Files

- `API_PATTERN.md` - Complete API calling guide
- `client/app/composables/useOnboarding.ts` - Fixed example
- Existing working auth pages (if any) for pattern reference

### Additional Checks

- Ensure all data is trimmed before sending
- Validate form data properly
- Check loading states are managed
- Verify error handling catches all cases
- Confirm success redirects work

## What Was Completed This Session

✅ Built complete onboarding system (backend already existed)
✅ Created onboarding composable with API integration
✅ Built onboarding page with Nuxt UI v4 components
✅ Created global onboarding middleware
✅ Fixed middleware naming (`guest` → `$guest`, `auth` → `$auth`)
✅ Fixed public page access issues
✅ Fixed API pattern in onboarding composable
✅ Created comprehensive documentation:
   - `ONBOARDING.md` - Complete onboarding docs
   - `API_PATTERN.md` - API calling pattern guide
   - `MIDDLEWARE_FIX.md` - Middleware fix documentation
   - `TODO_NEXT_SESSION.md` - This file

## Current State

**Working:**
- ✅ Homepage (public)
- ✅ Onboarding page structure (needs testing with working auth)
- ✅ Middleware system
- ✅ Backend APIs (all working)

**Needs Fix:**
- ❌ Login page (API fetch issues)
- ❌ Register page (API fetch issues)
- ❌ Forgot password (API fetch issues)
- ❌ Reset password (API fetch issues)

**Once Auth Fixed:**
- Test complete registration → onboarding → dashboard flow
- Test login → onboarding check → dashboard flow
- Use Puppeteer test script in `client/test-onboarding.js`

## Notes for Next Session

- All documentation is up to date
- Pattern is clearly defined in `API_PATTERN.md`
- CLAUDE.md has been updated with critical patterns
- Search through auth pages systematically
- Apply pattern consistently
- Test each page after fixing

---

**Last Updated:** 2025-12-10
**Status:** Auth pages need API fetch method fixes
