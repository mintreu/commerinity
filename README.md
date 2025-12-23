# Commerinity Pro - Enterprise MLM & E-Commerce Platform

A modern, full-stack MLM (Multi-Level Marketing) and E-commerce platform built with Laravel 12 and Nuxt 4.

## 🚀 Tech Stack

### Backend
- **Laravel 12** - PHP Framework
- **Laravel Sanctum 4** - Token-based Authentication
- **Pest 4** - Testing Framework
- **MySQL** - Database

### Frontend
- **Nuxt 4** - Vue.js Framework
- **Nuxt UI** - Component Library
- **Tailwind CSS 4** - Styling
- **TypeScript** - Type Safety

## ✨ Features

### Authentication System
- **Mobile-First** - Mobile number as primary identifier
- **Dual Authentication** - Password OR OTP login
- **Multi-Method** - Login via Mobile/Email
- **Token-Based** - Sanctum bearer token authentication
- **Secure** - OTP verification, rate limiting, password hashing

### User Type System
Five distinct user types with unique dashboards:
1. **Regular** - Standard customer (shopping only)
2. **Member** - MLM member (shopping + referrals + earnings)
3. **Promoter** - Active promoter (+ marketing tools)
4. **Advisor** - Team leader (+ team management + reports)
5. **Mentor** - Top-tier leadership (+ analytics + organization tools)

### MLM Features
- Referral system with unique codes
- Parent-child hierarchy (upline/downline)
- Originator tracking (agent recruitment)
- Commission structure (ready for implementation)
- Team management
- Performance tracking

### E-Commerce Features (Ready for Implementation)
- Product catalog
- Shopping cart
- Order management
- Wishlist
- Multi-vendor support

## 📋 Quick Start

### Prerequisites
- PHP 8.3+
- Node.js 18+
- MySQL
- Composer
- npm

### Installation

#### 1. Clone and Setup Backend

```bash
cd apiserver

# Install dependencies
composer install

# Configure environment
cp .env.example .env

# Update .env with your settings
# DB_DATABASE=commerinity
# DB_USERNAME=root
# DB_PASSWORD=your_password

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed demo users
php artisan db:seed --class=DemoUserSeeder

# Start server
php artisan serve
```

Backend runs at: **http://localhost:8000**

#### 2. Setup Frontend

```bash
cd client

# Install dependencies
npm install

# Start dev server
npm run dev
```

Frontend runs at: **http://localhost:3000**

## 🧪 Testing

### Demo User Credentials

All demo users use password: **`password`**

| Type | Mobile | Email | Dashboard |
|------|--------|-------|-----------|
| Regular | +919876543210 | regular@demo.com | /dashboard/regular |
| Member | +919876543211 | member@demo.com | /dashboard/member |
| Promoter | +919876543212 | promoter@demo.com | /dashboard/promoter |
| Advisor | +919876543213 | advisor@demo.com | /dashboard/advisor |
| Mentor | +919876543214 | mentor@demo.com | /dashboard/mentor |

### Login Methods

**Option 1: Email + Password**
```
Email: regular@demo.com
Password: password
```

**Option 2: Mobile + Password**
```
Mobile: +919876543210
Password: password
```

**Option 3: Mobile + OTP (Demo Mode)**
```
Mobile: +919876543210
OTP: 123456 (always in demo mode)
```

### Registration Test

1. Navigate to http://localhost:3000/auth/register
2. Enter mobile: `+919999999999`
3. Click "Send OTP"
4. Enter demo OTP: `123456`
5. Fill in details
6. Click "Create Account"

### Run Backend Tests

```bash
cd apiserver

# Run all tests
php artisan test

# Run specific suite
php artisan test tests/Feature/Auth/

# Current status: 47/75 passing
```

## 📁 Project Structure

```
commerinity_pro/
├── apiserver/              # Laravel 12 API
│   ├── app/
│   │   ├── Casts/         # Enum casts (UserType, UserStatus)
│   │   ├── Helpers/       # OtpManager
│   │   ├── Http/
│   │   │   ├── Controllers/Api/Auth/
│   │   │   └── Requests/  # Form Requests
│   │   └── Models/        # Eloquent Models
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   │   └── api.php
│   └── tests/
│       └── Feature/Auth/
│
└── client/                 # Nuxt 4 Frontend
    └── app/
        ├── components/    # Vue Components
        ├── composables/   # useUserType
        ├── layouts/       # guest, default
        ├── pages/         # Routes
        │   ├── auth/      # Login, Register
        │   └── dashboard/ # Type-specific dashboards
        └── types/         # TypeScript definitions
```

## 🎨 Design System

### Color Palette
- **Primary**: Blue (#3b82f6)
- **Secondary**: Indigo (#6366f1)
- **Accent**: Purple (#a855f7)
- **Success**: Green/Emerald
- **Warning**: Amber
- **Danger**: Red/Pink

### Design Features
- Glassmorphism effects (backdrop-blur)
- Floating animated orbs
- Gradient text and buttons
- Smooth transitions
- Dark mode support
- Mobile responsive

### Reusable CSS Classes
```css
.glass-card         - Glassmorphism card
.feature-card       - Feature showcase card
.stat-card          - Statistics card
.icon-box-*         - Gradient icon containers
.gradient-text-*    - Gradient text effects
.btn-*              - Button styles
```

## 🔒 Security Features

- CSRF protection
- Rate limiting (OTP: 3/15min, Verification: 5 attempts)
- Password hashing (bcrypt)
- OTP hashing (bcrypt with xxh3 keys)
- Token expiration
- Multi-device token management
- Email/Mobile verification

## 📚 Documentation

- **NUXT_SANCTUM_AUTH_GUIDE.md** - Complete authentication guide
- **ACTIVITY_LOG.md** - Development history
- **DEMO_CREDENTIALS.md** - Test user credentials
- **GETTING_STARTED.md** - Setup instructions
- **OLD_COMMERINITY_DESIGN.md** - Design specifications

## 🛠️ Development

### Backend Commands

```bash
# Run tests
php artisan test

# Code formatting
vendor/bin/pint

# Fresh migration with demo data
php artisan migrate:fresh --seed
```

### Frontend Commands

```bash
# Development
npm run dev

# Build
npm run build

# Type check
npm run typecheck

# Lint
npm run lint
```

## 🌐 API Endpoints

### Public Endpoints
- `POST /api/auth/send-otp` - Send OTP
- `POST /api/auth/verify-otp` - Verify OTP
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - Login
- `POST /api/auth/forgot-password` - Request password reset
- `POST /api/auth/reset-password` - Reset password

### Protected Endpoints
- `GET /api/user` - Get current user
- `POST /api/auth/logout` - Logout current device
- `POST /api/auth/logout-all` - Logout all devices

## 📊 Database Schema

### Users Table
- `mobile` (required, unique) - E.164 format
- `email` (optional, unique)
- `type` - regular/member/promoter/advisor/mentor
- `status` - draft/active/inactive/suspended/banned
- `parent_id` - MLM upline
- `originator_id` - Agent who recruited
- `referral_code` - 8-char unique code
- UUID generation: REG2025 + 12 random chars

## 🚧 Roadmap

### Phase 1: Foundation (✅ COMPLETED)
- [x] Authentication system
- [x] User type system
- [x] Type-based dashboards
- [x] Navigation system
- [x] UI matching old commerinity

### Phase 2: E-Commerce (Next)
- [ ] Product catalog
- [ ] Shopping cart
- [ ] Checkout system
- [ ] Order management
- [ ] Payment integration

### Phase 3: MLM Features
- [ ] Commission calculation
- [ ] Team tree visualization
- [ ] Earnings dashboard
- [ ] Payout system
- [ ] Reports and analytics

### Phase 4: Advanced Features
- [ ] Notifications system
- [ ] Profile management
- [ ] Onboarding flow
- [ ] Admin panel
- [ ] Mobile app

## 📝 License

Proprietary - All rights reserved

## 👥 Support

For issues or questions, contact: support@commerinity.com
