# Onboarding System Documentation

## Overview

Professional onboarding flow built with Laravel 12 backend API and Nuxt 4 + Nuxt UI v4 frontend. The system guides new users through completing their profile after registration or first login.

## Architecture

### Backend (Laravel 12)

**Location:** `apiserver/app/Http/Controllers/Api/OnboardingController.php`

**Endpoints:**
- `GET /api/onboarding/status` - Get current onboarding progress
- `PUT /api/onboarding/profile` - Update profile information (Step 1)
- `POST /api/onboarding/complete` - Mark onboarding as complete

**Database:**
- `users.onboarded` - Boolean column tracking completion status
- Additional fields: `name`, `gender`, `dob`, `bio`

**Progress Calculation:**
1. Profile complete (name, dob, gender) - 25%
2. Email or mobile verified - 25%
3. Address added - 25%
4. KYC or skip KYC - 25%

**Minimum Requirements to Complete:**
- ✅ Profile filled (name, gender, dob)
- ✅ Email OR mobile verified

**Optional Steps:**
- Address (can be added later)
- KYC (can be completed later)

### Frontend (Nuxt 4 + Nuxt UI v4)

**Location:** `client/app/pages/onboarding/index.vue`

**Features:**
- ✅ Single-page multi-step form
- ✅ Real-time progress indicator (0-100%)
- ✅ Nuxt UI v4 components (UCard, UForm, UInput, UInputDate, URadioGroup, etc.)
- ✅ Form validation with Zod
- ✅ Client-side only rendering (`ssr: false`)
- ✅ Loading states and error handling
- ✅ Toast notifications for feedback

**Composable:** `client/app/composables/useOnboarding.ts`

**Middleware:** `client/app/middleware/onboarding.global.ts`
- Automatically redirects non-onboarded users to `/onboarding`
- Skips check for public pages (/, /about, /contact, /privacy, /terms)
- Skips check for auth pages and onboarding page itself
- Uses try/catch to handle cases where auth is not initialized

## User Flow

```
1. User registers → onboarded = false
2. After login → Middleware checks onboarded status
3. If onboarded = false → Redirect to /onboarding
4. User fills profile form
5. (Optional) User adds address
6. (Optional) User completes KYC
7. User clicks "Complete Onboarding"
8. Backend validates minimum requirements
9. onboarded = true
10. Redirect to /dashboard
```

## Components Used (Nuxt UI v4)

- `UCard` - Main container with header
- `UForm` - Form with validation
- `UFormField` - Form field wrapper
- `UInput` - Text input for name
- `UInputDate` - Date picker for DOB
- `URadioGroup` - Gender selection
- `UTextarea` - Bio input (optional)
- `UButton` - Action buttons
- `UBadge` - Progress badge
- `UProgress` - Progress bar
- `UIcon` - Icons for status indicators

## API Integration

All API calls use `useSanctumFetch` from `@qirolab/nuxt-sanctum-authentication` package.

**Pattern:**
```typescript
await useSanctumFetch(`${config.public.apiBase}/api/endpoint`, {
  method: 'POST',
  body: { data }
})
```

### Fetch Onboarding Status

```typescript
const { status, loading, fetchStatus } = useOnboarding()

await fetchStatus()
// Returns: { onboarded: false, progress: 50, steps: { profile: true, address: false, kyc: false } }
```

**Actual API call:**
```typescript
await useSanctumFetch(`${config.public.apiBase}/api/onboarding/status`)
```

### Update Profile

```typescript
const { updateProfile } = useOnboarding()

await updateProfile({
  name: 'John Doe',
  gender: 'male',
  dob: '1990-01-01',
  bio: 'Optional bio text'
})
```

**Actual API call:**
```typescript
await useSanctumFetch(`${config.public.apiBase}/api/onboarding/profile`, {
  method: 'PUT',
  body: data
})
```

### Complete Onboarding

```typescript
const { completeOnboarding } = useOnboarding()

await completeOnboarding()
// Sets onboarded = true if requirements met
```

**Actual API call:**
```typescript
await useSanctumFetch(`${config.public.apiBase}/api/onboarding/complete`, {
  method: 'POST'
})
```

### Important Notes

✅ **Always use `useSanctumFetch`** - Not `$fetch` or `$api`
✅ **Include full URL** - `${config.public.apiBase}/api/endpoint`
✅ **Sanctum handles auth** - Automatically includes auth token
✅ **Consistent pattern** - Matches all other API calls in the project

## Testing

### Manual Testing with Puppeteer

1. **Start frontend dev server:**
   ```bash
   cd client
   pnpm dev
   ```

2. **Start Chrome with remote debugging:**
   ```bash
   # Windows
   "C:\Program Files\Google\Chrome\Application\chrome.exe" --remote-debugging-port=9222 --user-data-dir="C:\chrome-debug"

   # Mac
   /Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome --remote-debugging-port=9222 --user-data-dir=/tmp/chrome-debug

   # Linux
   google-chrome --remote-debugging-port=9222 --user-data-dir=/tmp/chrome-debug
   ```

3. **Run the test:**
   ```bash
   cd client
   node test-onboarding.js
   ```

4. **Check screenshots:**
   - `logs/01-login-page.png`
   - `logs/02-onboarding-page.png`
   - `logs/03-onboarding-final.png`

### Using MCP Puppeteer Tools

```bash
# Connect to active Chrome tab
mcp__puppeteer__puppeteer_connect_active_tab

# Navigate to onboarding
mcp__puppeteer__puppeteer_navigate --url http://localhost:3000/onboarding

# Take screenshot
mcp__puppeteer__puppeteer_screenshot --name onboarding-page

# Fill form
mcp__puppeteer__puppeteer_fill --selector 'input[placeholder*="name"]' --value "Test User"

# Click button
mcp__puppeteer__puppeteer_click --selector 'button[type="submit"]'
```

## Validation Rules

**Backend (OnboardingProfileRequest):**
```php
'name' => ['required', 'string', 'max:255', 'min:3'],
'gender' => ['required', Rule::in(['male', 'female', 'other'])],
'dob' => ['required', 'date', 'before:today', 'after:1900-01-01'],
'bio' => ['nullable', 'string', 'max:500']
```

**Frontend (Zod Schema):**
```typescript
z.object({
  name: z.string().min(3, 'Name must be at least 3 characters'),
  gender: z.enum(['male', 'female', 'other']),
  dob: z.string().min(1, 'Date of birth is required'),
  bio: z.string().optional()
})
```

## Error Handling

- ✅ API errors displayed via toast notifications
- ✅ Form validation errors shown inline
- ✅ Loading states prevent duplicate submissions
- ✅ Missing requirements displayed when trying to complete

## Customization

### Add More Steps

1. Add step to backend `OnboardingController::calculateProgress()`
2. Add step to frontend form
3. Update validation schemas
4. Adjust progress calculation

### Modify Requirements

Edit `OnboardingController::canCompleteOnboarding()`:

```php
private function canCompleteOnboarding($user): bool
{
    return $this->isProfileComplete($user)
        && ($user->hasVerifiedEmail() || $user->hasVerifiedMobile())
        && $user->addresses()->exists(); // Make address required
}
```

## Security Considerations

- ✅ All endpoints protected by `auth:sanctum` middleware
- ✅ Form validation on both frontend and backend
- ✅ CSRF protection via Sanctum
- ✅ Rate limiting applied (Laravel default)
- ✅ Input sanitization automatic via Laravel

## Performance

- ✅ Client-side rendering (`ssr: false`)
- ✅ Minimal API calls (status cached in composable)
- ✅ Progressive disclosure (optional steps)
- ✅ Optimistic UI updates

## Accessibility

- ✅ Keyboard navigation supported
- ✅ Screen reader friendly (Nuxt UI components)
- ✅ Focus management
- ✅ ARIA labels on form fields

## Browser Support

- ✅ Chrome/Edge (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Mobile browsers

## Future Enhancements

- [ ] Multi-step wizard with separate pages
- [ ] Profile picture upload
- [ ] Social media links
- [ ] Skills/interests selection
- [ ] Welcome video/tutorial
- [ ] Email confirmation after onboarding

## Troubleshooting

### User stuck in onboarding loop

Check database:
```sql
SELECT id, name, email, onboarded, dob, gender FROM users WHERE id = ?;
```

Manually complete:
```sql
UPDATE users SET onboarded = 1 WHERE id = ?;
```

### Form not submitting

1. Check browser console for errors
2. Verify API is running: `http://localhost:8000/api/onboarding/status`
3. Check authentication token exists
4. Verify CORS settings

### Middleware not redirecting

1. Check user object has `onboarded` field
2. Verify middleware is registered globally (`.global.ts` suffix)
3. Check route exclusions in middleware
4. Add your public pages to the `publicPages` array if needed

### "useSanctumAuth is not defined" error

This means the middleware is running on a public page. Fix:
1. Add the page path to `publicPages` array in `onboarding.global.ts`
2. Ensure try/catch block is wrapping `useSanctumAuth()` call

## Support

For issues or questions:
1. Check logs: `apiserver/storage/logs/laravel.log`
2. Check browser console
3. Verify all dependencies installed
4. Restart dev servers

---

**Built with:**
- Laravel 12
- Nuxt 4
- Nuxt UI v4
- TypeScript
- Zod
- Tailwind CSS v4
