# Demo User Credentials - Commerinity Pro

## Test Users

All demo users have the same password: **`password`**

### 1. Regular Customer
- **Mobile**: `+919876543210`
- **Email**: `regular@demo.com`
- **Password**: `password`
- **Type**: Regular
- **Dashboard**: `/dashboard/regular`
- **Features**: Shopping, Orders, Wishlist

### 2. Member
- **Mobile**: `+919876543211`
- **Email**: `member@demo.com`
- **Password**: `password`
- **Type**: Member
- **Dashboard**: `/dashboard/member`
- **Features**: Shopping, Orders, Network, Earnings, Referrals

### 3. Promoter
- **Mobile**: `+919876543212`
- **Email**: `promoter@demo.com`
- **Password**: `password`
- **Type**: Promoter
- **Dashboard**: `/dashboard/promoter`
- **Features**: Shopping, Orders, Network, Earnings, Promotions, Marketing

### 4. Advisor
- **Mobile**: `+919876543213`
- **Email**: `advisor@demo.com`
- **Password**: `password`
- **Type**: Advisor
- **Dashboard**: `/dashboard/advisor`
- **Features**: Shopping, Orders, Network, Earnings, Team Management, Reports, Training

### 5. Mentor
- **Mobile**: `+919876543214`
- **Email**: `mentor@demo.com`
- **Password**: `password`
- **Type**: Mentor
- **Dashboard**: `/dashboard/mentor`
- **Features**: All features + Leadership, Analytics, Organization Management

## How to Test

### Login Options

#### Option 1: Mobile + Password
1. Go to http://localhost:3000/auth/login
2. Select "Mobile" tab
3. Enter mobile: `+919876543210`
4. Select "Password"
5. Enter password: `password`
6. Click "Sign In"

#### Option 2: Email + Password
1. Go to http://localhost:3000/auth/login
2. Select "Email" tab
3. Enter email: `regular@demo.com`
4. Enter password: `password`
5. Click "Sign In"

#### Option 3: Mobile + OTP (Demo Mode)
1. Go to http://localhost:3000/auth/login
2. Select "Mobile" tab
3. Enter mobile: `+919876543210`
4. Select "OTP"
5. Click "Send OTP"
6. Enter demo OTP: `123456`
7. Click "Sign In"

### Test Different User Types

To test different dashboards:
1. Logout from current account
2. Login with different user credentials above
3. You'll be redirected to the type-specific dashboard

### Registration Test

1. Go to http://localhost:3000/auth/register
2. Enter new mobile (e.g., `+919999999999`)
3. Click "Send OTP"
4. Enter demo OTP: `123456`
5. Fill in name, password
6. Optional: Add email and referral code
7. Accept terms
8. Click "Create Account"
9. New user will be created as "Regular" type

## Resetting Demo Data

To reset and recreate demo users:

```bash
cd apiserver
php artisan migrate:fresh
php artisan db:seed --class=DemoUserSeeder
```

## Notes

- All users are already verified (mobile_verified_at and email_verified_at set)
- All users are onboarded (onboarded = true)
- All users have active status
- In demo mode (non-production), OTP is always `123456`
- Password reset tokens expire after 60 minutes
- OTPs expire after 10 minutes
- Rate limiting: 3 OTP requests per 15 minutes
