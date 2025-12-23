# Getting Started - Commerinity Pro

## Overview

Commerinity Pro is an enterprise MLM + E-commerce platform with:
- **Backend**: Laravel 12 + Sanctum (Token-based auth)
- **Frontend**: Nuxt 4 + Nuxt UI
- **Authentication**: Mobile-first OTP system with type-based dashboards

## Prerequisites

- PHP 8.3+
- Node.js 18+
- MySQL/MariaDB
- Composer
- npm/pnpm

## Project Structure

```
commerinity_pro/
├── apiserver/          # Laravel 12 API Backend
│   ├── app/
│   │   ├── Http/Controllers/Api/Auth/
│   │   ├── Http/Requests/
│   │   ├── Models/
│   │   └── Helpers/
│   ├── config/
│   ├── database/
│   ├── routes/
│   └── tests/
│
└── client/             # Nuxt 4 Frontend
    └── app/
        ├── components/
        ├── composables/
        ├── layouts/
        ├── pages/
        └── types/
```

## Setup Instructions

### 1. Laravel API Server

```bash
cd apiserver

# Install dependencies
composer install

# Configure environment
cp .env.example .env

# Important: Set these values in .env
APP_URL=http://localhost:8000
APP_CLIENT_URL=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS=  # Leave EMPTY for token mode
DB_DATABASE=commerinity
DB_USERNAME=root
DB_PASSWORD=your_password

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Start server
php artisan serve
# Server will run at http://localhost:8000
```

### 2. Nuxt Frontend

```bash
cd client

# Install dependencies
npm install

# Configure environment
echo "NUXT_PUBLIC_API_BASE=http://localhost:8000" > .env

# Start development server
npm run dev
# Server will run at http://localhost:3000
```

## Testing the Authentication Flow

### 1. Registration (Mobile + OTP)

**Navigate to**: `http://localhost:3000/auth/register`

**Steps**:
1. Enter Full Name: `John Doe`
2. Enter Mobile (with country code): `+919876543210`
3. Click "Send OTP"
4. Demo Mode: OTP will be displayed in a toast (123456)
5. Enter the OTP: `123456`
6. Optional: Enter email address
7. Create password (min 8 characters)
8. Confirm password
9. Optional: Enter referral code
10. Accept terms & conditions
11. Click "Create Account"

**Expected Result**:
- Account created successfully
- Automatically logged in
- Redirected to `/dashboard/regular` (default user type)

### 2. Login (Mobile + Password)

**Navigate to**: `http://localhost:3000/auth/login`

**Steps**:
1. Select "Mobile" tab
2. Enter mobile: `+919876543210`
3. Select "Password" method
4. Enter your password
5. Click "Sign In"

**Expected Result**:
- Logged in successfully
- Redirected to type-specific dashboard

### 3. Login (Mobile + OTP)

**Steps**:
1. Select "Mobile" tab
2. Enter mobile: `+919876543210`
3. Select "OTP" method
4. Click "Send OTP"
5. Demo Mode: OTP shown in toast (123456)
6. Enter OTP: `123456`
7. Click "Sign In"

### 4. Login (Email + Password)

**Steps**:
1. Select "Email" tab
2. Enter email address
3. Enter password
4. Click "Sign In"

## User Types & Dashboards

The system has 5 user types, each with a unique dashboard and navigation:

### 1. Regular Customer (`/dashboard/regular`)
**Default type for new registrations**

**Features**:
- Order tracking (Total, Delivered, Pending)
- Wishlist management
- Recent orders
- Upgrade to Member CTA

**Navigation**:
- Dashboard
- Profile
- Shop
- Orders

### 2. Member (`/dashboard/member`)
**MLM member with referral capabilities**

**Features**:
- Team members count
- Earnings tracking
- Referral code with copy functionality
- Network activity feed
- Recent earnings

**Navigation**:
- Dashboard
- Profile
- Shop
- Orders
- My Network
- Earnings

### 3. Promoter (`/dashboard/promoter`)
**Active promoter with marketing tools**

**Navigation**:
- All Member features +
- Promotions
- Marketing

### 4. Advisor (`/dashboard/advisor`)
**Team leader with management capabilities**

**Navigation**:
- All Promoter features +
- Team
- Reports
- Training

### 5. Mentor (`/dashboard/mentor`)
**Top-tier leadership role**

**Features**:
- Organization size metrics
- Team performance tracking
- Leadership tools (Team Overview, Analytics, Training)
- Advanced statistics
- Rank and performance metrics

**Navigation**:
- All Advisor features +
- Leadership
- Analytics

## API Endpoints

### Authentication Endpoints

#### Send OTP
```http
POST /api/auth/send-otp
Content-Type: application/json

{
  "type": "mobile",
  "value": "+919876543210"
}

Response (Demo Mode):
{
  "success": true,
  "message": "OTP sent successfully",
  "demo": true,
  "otp": "123456"
}
```

#### Register
```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "mobile": "+919876543210",
  "otp": "123456",
  "password": "Password123!",
  "password_confirmation": "Password123!",
  "email": "john@example.com",      // Optional
  "referral_code": "ABC12345"        // Optional
}

Response:
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 1,
      "uuid": "REG20251234567890AB",
      "name": "John Doe",
      "mobile": "+919876543210",
      "email": "john@example.com",
      "type": "regular",
      "status": "active",
      "referral_code": "USER1234"
    },
    "token": "1|abc123..."
  }
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

# Option 1: Mobile + Password
{
  "mobile": "+919876543210",
  "password": "Password123!"
}

# Option 2: Mobile + OTP
{
  "mobile": "+919876543210",
  "otp": "123456"
}

# Option 3: Email + Password
{
  "email": "john@example.com",
  "password": "Password123!"
}

Response:
{
  "success": true,
  "data": {
    "user": { ... },
    "token": "2|xyz789..."
  }
}
```

#### Get Current User
```http
GET /api/user
Authorization: Bearer {token}

Response:
{
  "id": 1,
  "uuid": "REG20251234567890AB",
  "name": "John Doe",
  "type": "regular",
  ...
}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}

Response:
{
  "success": true,
  "message": "Logged out successfully"
}
```

#### Logout All Devices
```http
POST /api/auth/logout-all
Authorization: Bearer {token}

Response:
{
  "success": true,
  "message": "Logged out from all devices"
}
```

## Testing with Pest

```bash
cd apiserver

# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Auth/RegistrationTest.php

# Run with filter
php artisan test --filter=testName
```

**Current Test Status**:
- OTP Tests: 27/27 passing ✅
- User Model Tests: 33/33 passing ✅
- Overall: 47/75 passing (63%)

## Demo Mode

**OTP Demo Mode** (Non-production environments):
- All OTP requests return `123456`
- OTP is displayed in API response and frontend toast
- No actual SMS sent

**To Test Production Mode**:
- Set `APP_ENV=production` in `.env`
- Configure SMS provider in OtpManager
- OTP will be sent via SMS (not shown in response)

## Troubleshooting

### CORS Issues

If you see CORS errors in browser console:

1. **Check Laravel .env**:
```bash
APP_URL=http://localhost:8000
APP_CLIENT_URL=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS=  # Should be EMPTY for token mode
```

2. **Verify CORS middleware** in `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->api(prepend: [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);
})
```

### Token Not Persisting

1. Check browser cookies - `commerinity_auth_token` should be present
2. Verify token storage in Nuxt config
3. Check API response format - must return `data.token`

### User Not Loading

1. Check `/api/user` endpoint returns 200
2. Verify Authorization header is sent
3. Check token is valid (not expired/revoked)

### Redirect Loops

1. Verify middleware configuration
2. Check user type matches dashboard route
3. Ensure authentication state is properly set

## Next Steps

1. **Test Authentication**: Register and login with different methods
2. **Test User Types**: Manually update user type in database and verify dashboard changes
3. **Password Reset**: Implement forgot password flow
4. **Profile Management**: Create profile edit page
5. **E-commerce Features**: Product catalog, cart, checkout
6. **MLM Features**: Team management, commission tracking

## Key Files Reference

### Frontend
- **Config**: `client/nuxt.config.ts`
- **Auth Pages**: `client/app/pages/auth/`
- **Dashboards**: `client/app/pages/dashboard/`
- **User Types**: `client/app/composables/useUserType.ts`
- **Type Definitions**: `client/app/types/user.ts`

### Backend
- **Controllers**: `apiserver/app/Http/Controllers/Api/Auth/`
- **Requests**: `apiserver/app/Http/Requests/`
- **Routes**: `apiserver/routes/api.php`
- **Tests**: `apiserver/tests/Feature/Auth/`

## Support

For issues or questions:
1. Check ACTIVITY_LOG.md for recent changes
2. Review NUXT_SANCTUM_AUTH_GUIDE.md for auth patterns
3. Check test files for expected behavior
4. Review Laravel and Nuxt logs
