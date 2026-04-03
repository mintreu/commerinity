# API Calling Pattern - Sanctum Authentication

## Correct Pattern

✅ **ALWAYS use this pattern:**

```typescript
const config = useRuntimeConfig()

await useSanctumFetch(`${config.public.apiBase}/api/endpoint`, {
  method: 'POST',
  body: { data }
})
```

## Examples

### GET Request
```typescript
const config = useRuntimeConfig()

const data = await useSanctumFetch(`${config.public.apiBase}/api/user`)
```

### POST Request
```typescript
const config = useRuntimeConfig()

await useSanctumFetch(`${config.public.apiBase}/api/auth/send-otp`, {
  method: 'POST',
  body: {
    type: 'mobile',
    value: mobile.trim()
  }
})
```

### PUT Request
```typescript
const config = useRuntimeConfig()

await useSanctumFetch(`${config.public.apiBase}/api/onboarding/profile`, {
  method: 'PUT',
  body: {
    name: 'John Doe',
    gender: 'male',
    dob: '1990-01-01'
  }
})
```

### DELETE Request
```typescript
const config = useRuntimeConfig()

await useSanctumFetch(`${config.public.apiBase}/api/addresses/${uuid}`, {
  method: 'DELETE'
})
```

## Why This Pattern?

### ✅ Correct: `useSanctumFetch`
- Automatically includes Sanctum auth token
- Handles CSRF protection
- Consistent with project conventions
- Works with `@qirolab/nuxt-sanctum-authentication`

### ❌ Wrong: `$fetch`
```typescript
// DON'T USE THIS
await $fetch(`${config.public.apiBase}/api/endpoint`)
```
- No auth token automatically included
- Manual token handling required
- Not consistent with project

### ❌ Wrong: `$api`
```typescript
// DON'T USE THIS
const { $api } = useNuxtApp()
await $api('/api/endpoint')
```
- `$api` is not defined in this project
- Will cause runtime errors

## In Composables

```typescript
export const useMyFeature = () => {
  const config = useRuntimeConfig()
  const toast = useToast()

  const loading = ref(false)

  const fetchData = async () => {
    loading.value = true
    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/my-endpoint`)
      return response
    } catch (err: any) {
      toast.add({
        title: 'Error',
        description: err.message || 'Failed to fetch data',
        color: 'red'
      })
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    fetchData
  }
}
```

## In Components

```typescript
<script setup lang="ts">
const config = useRuntimeConfig()

const handleSubmit = async () => {
  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/api/endpoint`, {
      method: 'POST',
      body: formData
    })

    // Handle success
  } catch (error) {
    // Handle error
  }
}
</script>
```

## API Base URL

Configured in `nuxt.config.ts`:

```typescript
runtimeConfig: {
  public: {
    apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000'
  }
}
```

Default: `http://localhost:8000`

## Authentication Flow

1. **Login** → Sanctum returns token
2. **Token stored** → In cookie by `@qirolab/nuxt-sanctum-authentication`
3. **All requests** → `useSanctumFetch` automatically includes token
4. **Logout** → Token cleared

## Error Handling

```typescript
try {
  const response = await useSanctumFetch(`${config.public.apiBase}/api/endpoint`, {
    method: 'POST',
    body: data
  })
  // Success
} catch (err: any) {
  // Error handling
  console.error(err.message)
  toast.add({
    title: 'Error',
    description: err.message || 'Something went wrong',
    color: 'red'
  })
}
```

## Common Endpoints

### Auth
- `POST /api/auth/send-otp` - Send OTP
- `POST /api/auth/verify-otp` - Verify OTP
- `POST /api/auth/register` - Register user
- `POST /api/auth/login` - Login
- `POST /api/auth/logout` - Logout
- `POST /api/auth/forgot-password` - Forgot password
- `POST /api/auth/reset-password` - Reset password

### User
- `GET /api/user` - Get current user
- `PUT /api/user/profile` - Update profile
- `PUT /api/user/password` - Change password

### Onboarding
- `GET /api/onboarding/status` - Get status
- `PUT /api/onboarding/profile` - Update profile
- `POST /api/onboarding/complete` - Complete onboarding

### Addresses
- `GET /api/addresses` - List addresses
- `POST /api/addresses` - Create address
- `PUT /api/addresses/{uuid}` - Update address
- `DELETE /api/addresses/{uuid}` - Delete address
- `POST /api/addresses/{uuid}/default` - Set default

### KYC
- `GET /api/kyc/status` - Get KYC status
- `POST /api/kyc/submit` - Submit KYC
- `POST /api/kyc/{id}/resubmit` - Resubmit KYC

## Checklist

Before making any API call:

- [ ] Using `useSanctumFetch`?
- [ ] Using `${config.public.apiBase}` prefix?
- [ ] Correct HTTP method?
- [ ] Body data trimmed/validated?
- [ ] Error handling in place?
- [ ] Loading state managed?
- [ ] Toast notification on error?

## Reference Files

- `client/app/pages/auth/register.vue` - Complete registration example
- `client/app/pages/auth/login.vue` - Complete login example
- `client/app/composables/useOnboarding.ts` - Composable example
- `client/nuxt.config.ts` - Configuration

---

**Remember:** Always use `useSanctumFetch` with full URL including `${config.public.apiBase}`
