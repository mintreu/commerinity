# Frontend Dashboard & User Journey Plan

## Research Summary

### Fintech UX Best Practices (2024-2025)
Based on research from [The Alien Design](https://www.thealien.design/insights/fintech-ux-design-trends), [Code Theorem](https://codetheorem.co/ui-ux-design/fintech-ux-design-trends/), and [Webstacks](https://www.webstacks.com/blog/fintech-ux-design):

1. **Gamification & Rewards** - Points, badges, challenges, leaderboards (CRED, Monobank)
2. **Data Visualization** - Interactive charts, spending patterns, goal tracking
3. **Personalization** - 72% customers expect personalized experiences
4. **Mobile-First** - 85% users access via smartphones
5. **Minimalist Design** - Clean interfaces, no distractions
6. **Trust Indicators** - Security badges, verification status
7. **Micro-interactions** - Fun, engaging elements to reduce cognitive load

### MLM Portal UX Insights
From [Prime MLM Software](https://primemlmsoftware.com/what-distributors-expect-in-their-mlm-user-portal-ux-insights/) and [Epixel MLM](https://www.epixelmlmsoftware.com/product-updates/customizable-mlm-dashboard):

1. **At-a-glance metrics** - PV/BV, rank status, commission tracking
2. **Smart dashboards** - Modular widgets, personalized views
3. **Real-time updates** - Live notifications, instant commission calculations
4. **Network visualization** - Genealogy trees, downline management
5. **Mobile responsiveness** - Touch-optimized, offline access
6. **Clear hierarchy** - Simplified navigation, quick access to earnings

### Nuxt 4 Best Practices
From [Nuxt Blog](https://nuxt.com/blog/v4) and [Nuxt Performance Guide](https://nuxt.com/docs/4.x/guide/best-practices/performance):

1. **New `app/` directory structure** - Better HMR, cleaner separation
2. **Single `tsconfig.json`** - Improved TypeScript experience
3. **Enhanced data fetching** - Auto-cleanup, reactive keys, cache control
4. **Skeleton loaders** - Prevent UI flashes during data fetching
5. **Lazy loading** - Load heavy components progressively

---

## Project Vision

**Commerinity Pro** is a fintech-style MLM + E-commerce platform that:
- Appears as a premium e-commerce site to regular users
- Unlocks MLM features progressively as users upgrade
- Guides users through their financial journey (not forcing, guiding)
- Uses gamification to encourage task completion
- Provides personalized dashboards based on user type and status

### User Type Hierarchy

```
Regular (Guest Shopper)
    ↓ Subscribe for membership
Member (MLM Participant)
    ↓ Pay for descendant's subscription
Promoter (Team Builder)
    ↓ Apply for job (future)
Advisor (Onboarder/Recruiter)
    ↓ Future expansion
Mentor (Leader/Trainer)
```

---

## Architecture

### Directory Structure (Nuxt 4 Standard)

```
client/
├── app/
│   ├── app.vue                     # Root component
│   ├── app.config.ts               # App configuration
│   │
│   ├── assets/
│   │   ├── css/
│   │   │   └── main.css            # Global styles
│   │   └── images/                 # Static images
│   │
│   ├── components/
│   │   ├── app/                    # App-wide components
│   │   │   ├── AppLogo.vue
│   │   │   ├── AppLoader.vue       # Global loader
│   │   │   └── AppBranding.vue     # Branding wrapper
│   │   │
│   │   ├── common/                 # Reusable UI components
│   │   │   ├── cards/
│   │   │   │   ├── StatCard.vue
│   │   │   │   ├── ProfileCard.vue
│   │   │   │   ├── ActionCard.vue
│   │   │   │   └── TaskCard.vue
│   │   │   ├── charts/
│   │   │   │   ├── TrendChart.vue
│   │   │   │   ├── PieChart.vue
│   │   │   │   └── BarChart.vue
│   │   │   ├── feedback/
│   │   │   │   ├── EmptyState.vue
│   │   │   │   ├── ErrorState.vue
│   │   │   │   └── SkeletonLoader.vue
│   │   │   └── forms/
│   │   │       └── DateRangeFilter.vue
│   │   │
│   │   ├── dashboard/              # Dashboard components
│   │   │   ├── DashboardHeader.vue
│   │   │   ├── DashboardStats.vue
│   │   │   ├── QuickActions.vue
│   │   │   ├── TaskProgress.vue
│   │   │   ├── RecentActivity.vue
│   │   │   └── UserJourneyCard.vue
│   │   │
│   │   ├── gamification/           # Gamification elements
│   │   │   ├── AchievementBadge.vue
│   │   │   ├── ProgressRing.vue
│   │   │   ├── LevelIndicator.vue
│   │   │   ├── StreakCounter.vue
│   │   │   └── RewardAnimation.vue
│   │   │
│   │   ├── navigation/             # Navigation components
│   │   │   ├── TopNavbar.vue
│   │   │   ├── AppSidebar.vue
│   │   │   ├── BottomNavBar.vue    # Mobile navigation
│   │   │   ├── UserDropdown.vue
│   │   │   └── NotificationBell.vue
│   │   │
│   │   └── user/                   # User-related components
│   │       ├── UserCard.vue
│   │       ├── UserTypeBadge.vue
│   │       ├── AvatarUploader.vue
│   │       └── VerificationStatus.vue
│   │
│   ├── composables/
│   │   ├── useApi.ts               # API helper
│   │   ├── useBranding.ts          # Branding config
│   │   ├── useDashboard.ts         # Dashboard data
│   │   ├── useGamification.ts      # Gamification state
│   │   ├── useNotifications.ts     # Notifications
│   │   ├── useOnboarding.ts        # Onboarding flow
│   │   ├── useTrends.ts            # Trend chart data
│   │   └── useUserType.ts          # User type helpers
│   │
│   ├── layouts/
│   │   ├── default.vue             # Main dashboard layout
│   │   ├── guest.vue               # Public pages layout
│   │   └── onboarding.vue          # Onboarding layout
│   │
│   ├── middleware/
│   │   ├── auth.ts                 # Auth check
│   │   └── onboarding.global.ts    # Onboarding redirect
│   │
│   ├── pages/
│   │   ├── index.vue               # Landing/redirect
│   │   │
│   │   ├── auth/
│   │   │   ├── login.vue
│   │   │   ├── register.vue
│   │   │   ├── forgot-password.vue
│   │   │   └── reset-password.vue
│   │   │
│   │   ├── onboarding/
│   │   │   └── index.vue
│   │   │
│   │   ├── dashboard/
│   │   │   ├── index.vue           # Router (redirects by type)
│   │   │   ├── regular.vue         # Regular user dashboard
│   │   │   ├── member.vue          # Member dashboard
│   │   │   ├── promoter.vue        # Promoter dashboard
│   │   │   ├── advisor.vue         # Advisor dashboard
│   │   │   └── mentor.vue          # Mentor dashboard
│   │   │
│   │   ├── profile/
│   │   │   ├── index.vue           # Profile overview with stats
│   │   │   ├── edit.vue            # Edit profile
│   │   │   └── change-password.vue
│   │   │
│   │   ├── wallet/
│   │   │   ├── index.vue           # Wallet overview
│   │   │   ├── transactions.vue    # Transaction history
│   │   │   ├── withdraw.vue        # Withdrawal request
│   │   │   └── beneficiaries.vue   # Bank accounts
│   │   │
│   │   ├── earnings/
│   │   │   ├── index.vue           # Earnings overview
│   │   │   └── commissions.vue     # Commission breakdown
│   │   │
│   │   ├── network/
│   │   │   ├── index.vue           # Network overview
│   │   │   ├── team.vue            # Direct team
│   │   │   └── genealogy.vue       # Tree view
│   │   │
│   │   ├── account/
│   │   │   ├── kyc.vue             # KYC verification
│   │   │   ├── addresses.vue       # Address management
│   │   │   └── settings.vue        # Account settings
│   │   │
│   │   └── notifications.vue       # Notifications page
│   │
│   ├── plugins/
│   │   ├── chart.client.ts         # Chart.js plugin
│   │   └── pwa.client.ts           # PWA plugin
│   │
│   ├── types/
│   │   ├── user.ts
│   │   ├── wallet.ts
│   │   ├── transaction.ts
│   │   ├── commission.ts
│   │   ├── notification.ts
│   │   └── api.ts
│   │
│   └── utils/
│       ├── formatters.ts           # Number/date formatters
│       ├── validators.ts           # Form validators
│       └── constants.ts            # App constants
│
├── public/
│   ├── favicon.ico
│   ├── favicon.svg
│   ├── apple-touch-icon.png
│   ├── favicon-96x96.png
│   ├── web-app-manifest-192x192.png
│   ├── web-app-manifest-512x512.png
│   ├── site.webmanifest
│   ├── logo.png
│   └── images/
│       ├── bg-login.jpeg
│       └── bg-registration.jpeg
│
├── nuxt.config.ts
├── package.json
└── tsconfig.json
```

---

## Dashboard Architecture

### Dynamic Dashboard Loading Strategy

```typescript
// pages/dashboard/index.vue - Smart Router
const dashboardComponents = {
  Regular: () => import('./regular.vue'),
  Member: () => import('./member.vue'),
  Promoter: () => import('./promoter.vue'),
  Advisor: () => import('./advisor.vue'),
  Mentor: () => import('./mentor.vue')
}

// Dynamically render based on user type
```

### Dashboard Components by User Type

#### Regular User Dashboard
```
┌─────────────────────────────────────────────────────────────┐
│ Welcome, {Name}! 👋                          [Notifications]│
│ Good morning! Here's what's new for you.                    │
├─────────────────────────────────────────────────────────────┤
│ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐         │
│ │ 🛒 Orders    │ │ 💰 Points    │ │ 🎁 Rewards   │         │
│ │    12       │ │    2,450    │ │    3 New    │         │
│ └──────────────┘ └──────────────┘ └──────────────┘         │
├─────────────────────────────────────────────────────────────┤
│ 🚀 UNLOCK PREMIUM BENEFITS                                  │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ Become a Member and get:                                │ │
│ │ ✓ 15% discount on all products                         │ │
│ │ ✓ Earn commissions by referring                        │ │
│ │ ✓ Exclusive member-only deals                          │ │
│ │                                   [Subscribe Now →]     │ │
│ └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│ 📦 Recent Orders                                            │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ Order #12345 - Processing      ₹2,499        [Track →] │ │
│ │ Order #12344 - Delivered       ₹1,299        [View →]  │ │
│ └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│ 🔥 Quick Actions                                            │
│ [Shop Now] [My Orders] [Support] [Profile]                  │
└─────────────────────────────────────────────────────────────┘
```

#### Member Dashboard
```
┌─────────────────────────────────────────────────────────────┐
│ Welcome, {Name}! 👋                    [Level: Bronze ⭐]    │
│ Good morning! Keep up the great work.                       │
├─────────────────────────────────────────────────────────────┤
│ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ │ 💰 Earnings  │ │ 👥 Referrals │ │ 🛒 Orders    │ │ 💳 Wallet   │
│ │   ₹12,500   │ │      8      │ │     24      │ │   ₹5,200   │
│ │   +15% ↑    │ │    +2 ↑     │ │   +5 ↑      │ │   +₹1.2K   │
│ └──────────────┘ └──────────────┘ └──────────────┘ └──────────────┘
├─────────────────────────────────────────────────────────────┤
│ 📊 Your Progress                                            │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ [▓▓▓▓▓▓▓░░░] 72% to Silver Level                       │ │
│ │ Need 3 more active referrals to level up               │ │
│ └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│ 📈 Earnings Trend           [This Month ▼] [Date Filter]    │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │            📊 Line Chart                                │ │
│ │                                                         │ │
│ └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│ ✅ Tasks to Complete                                        │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ ○ Complete KYC verification        [+100 points] [Do →]│ │
│ │ ● Refer your first friend          [Completed ✓]       │ │
│ │ ○ Make your first purchase         [+50 points] [Shop]│ │
│ └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│ 🔥 Quick Actions                                            │
│ [Share Referral] [View Network] [Withdraw] [Shop]           │
└─────────────────────────────────────────────────────────────┘
```

#### Promoter Dashboard
```
┌─────────────────────────────────────────────────────────────┐
│ Welcome, {Name}! 🌟                    [Level: Gold ⭐⭐⭐]   │
│ Great job! Your team is growing fast.                       │
├─────────────────────────────────────────────────────────────┤
│ [Overview] [Team] [Earnings] [Promotions] [Analytics]       │
├─────────────────────────────────────────────────────────────┤
│ 📊 Performance Summary           [This Month ▼]             │
│ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ │ 💰 Total     │ │ 👥 Team Size │ │ 📈 Active    │ │ 🎯 Target   │
│ │   ₹85,200   │ │     156     │ │     42      │ │   78%       │
│ │   +32% ↑    │ │   +12 ↑     │ │   +8 ↑      │ │   of goal   │
│ └──────────────┘ └──────────────┘ └──────────────┘ └──────────────┘
├─────────────────────────────────────────────────────────────┤
│ 📈 Team Growth & Earnings                                   │
│ ┌───────────────────────────┐ ┌───────────────────────────┐ │
│ │   Team Growth Chart      │ │  Earnings by Type         │ │
│ │        📊                │ │        🥧                │ │
│ └───────────────────────────┘ └───────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│ 🏆 Leaderboard Position: #12                                │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ You're 2,500 points away from Top 10!                  │ │
│ │ [View Leaderboard →]                                    │ │
│ └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│ 🎯 This Month's Challenges                                  │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ ⭐ Recruit 5 new members    [3/5] ▓▓▓▓▓▓░░░░  +₹5,000  │ │
│ │ ⭐ Team sales ₹1L           [₹72K] ▓▓▓▓▓▓▓░░░  +₹2,000  │ │
│ └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

#### Advisor Dashboard
```
┌─────────────────────────────────────────────────────────────┐
│ Welcome, {Name}! 💼                    [Advisor Badge]       │
│ You've helped 234 people start their journey.               │
├─────────────────────────────────────────────────────────────┤
│ [Dashboard] [Originated] [Earnings] [Reports] [Training]    │
├─────────────────────────────────────────────────────────────┤
│ 📊 Advisor Performance                   [This Month ▼]     │
│ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ │ 👤 Originated│ │ 💰 Comm.     │ │ 📈 Conversion│ │ ⭐ Rating   │
│ │     234     │ │   ₹1.2L     │ │    68%      │ │   4.8/5    │
│ │   +18 ↑     │ │  +₹15K ↑    │ │   +5% ↑     │ │   Excellent│
│ └──────────────┘ └──────────────┘ └──────────────┘ └──────────────┘
├─────────────────────────────────────────────────────────────┤
│ 📈 Originated Users Trend                                   │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │                    📊 Area Chart                        │ │
│ └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│ 🎓 Training Progress                                        │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ Module 1: Sales Techniques     [Completed ✓]           │ │
│ │ Module 2: Product Knowledge    [▓▓▓▓▓▓▓░░░] 70%       │ │
│ │ Module 3: Leadership           [Locked 🔒]             │ │
│ └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

---

## Gamification System

### Elements to Implement

1. **Points System**
   - Earn points for actions (referrals, purchases, KYC)
   - Display prominently on dashboard
   - Redeemable for rewards

2. **Badges & Achievements**
   - First Referral, First Sale, KYC Complete
   - Monthly Top Performer
   - Streak badges (7 days, 30 days)

3. **Progress Indicators**
   - Level progress bars
   - Task completion rings
   - Goal tracking

4. **Challenges & Missions**
   - Daily/Weekly/Monthly challenges
   - Team challenges
   - Limited-time events

5. **Leaderboards**
   - Weekly/Monthly rankings
   - Regional leaderboards
   - Category-specific (sales, referrals)

6. **Streaks & Milestones**
   - Daily login streak
   - Consecutive active days
   - Milestone celebrations

---

## API Requirements

### New Endpoints Needed

```php
// Dashboard Stats
GET /api/dashboard/stats?period=month&user_type=member

// Trend Data
GET /api/trends/wallet/{walletId}?period=month&type=balance_history
GET /api/trends/commissions?period=year&type=earnings
GET /api/trends/team?period=quarter&type=growth

// Gamification
GET /api/user/achievements
GET /api/user/progress
GET /api/challenges/active
GET /api/leaderboard?type=weekly&limit=10

// Tasks
GET /api/user/tasks
POST /api/user/tasks/{taskId}/complete
```

### Existing APIs to Use

```
- /api/user (profile)
- /api/profile/update
- /api/kyc/*
- /api/addresses/*
- /api/notifications/*
- /api/wallet/* (to be created)
- /api/transactions/* (to be created)
```

---

## Branding Configuration

### Environment Variables

```env
# Branding
NUXT_PUBLIC_APP_NAME="Commerinity Pro"
NUXT_PUBLIC_APP_SHORT_NAME="CMP"
NUXT_PUBLIC_COMPANY_NAME="Commerinity Pro"
NUXT_PUBLIC_COMPANY_LEGAL_NAME="Commerinity Pro Pvt Ltd"
NUXT_PUBLIC_COMPANY_SUPPORT_MAIL="support@commerinity.com"
NUXT_PUBLIC_COMPANY_SUPPORT_PHONE="+91 98765 43210"

# Theme Colors
NUXT_PUBLIC_PRIMARY_COLOR="#3b82f6"
NUXT_PUBLIC_SECONDARY_COLOR="#8b5cf6"
NUXT_PUBLIC_ACCENT_COLOR="#10b981"

# Feature Flags
NUXT_PUBLIC_ENABLE_PWA="true"
NUXT_PUBLIC_ENABLE_DARK_MODE="true"
NUXT_PUBLIC_ENABLE_GAMIFICATION="true"
```

---

## PWA Configuration

### Manifest (site.webmanifest)

```json
{
  "name": "Commerinity Pro",
  "short_name": "CMP",
  "description": "MLM & E-Commerce Platform",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#3b82f6",
  "icons": [
    {
      "src": "/web-app-manifest-192x192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "maskable"
    },
    {
      "src": "/web-app-manifest-512x512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "maskable"
    }
  ]
}
```

---

## Implementation Phases

### Phase 1: Foundation (Current)
1. ✅ Branding configuration with env
2. ✅ PWA setup with manifest
3. ✅ Copy assets from old project
4. ✅ Global loader component
5. ✅ Base chart components

### Phase 2: Dashboard Core
1. Dashboard component architecture
2. User type specific dashboards
3. Stat cards and quick actions
4. Date range filtering

### Phase 3: Trend Charts
1. Create trend API controllers
2. Integrate TrendServices with API
3. Build frontend chart components
4. Connect dashboards to APIs

### Phase 4: Gamification
1. Achievement system
2. Progress tracking
3. Challenges/missions
4. Notifications integration

### Phase 5: Polish
1. Responsive design verification
2. Dark mode consistency
3. Loading states everywhere
4. Error handling
5. Performance optimization

---

## Design Principles

1. **Premium Feel** - Glassmorphism, gradients, shadows
2. **Clean & Minimal** - No clutter, clear hierarchy
3. **Mobile First** - Touch-friendly, responsive
4. **Progressive Disclosure** - Show more as user progresses
5. **Encouraging** - Positive language, celebrate wins
6. **Fast** - Skeleton loaders, lazy loading, optimistic UI
7. **Accessible** - WCAG compliance, keyboard navigation

---

## Sources

- [Fintech UX Design Trends 2024](https://www.thealien.design/insights/fintech-ux-design-trends)
- [MLM Portal UX Insights](https://primemlmsoftware.com/what-distributors-expect-in-their-mlm-user-portal-ux-insights/)
- [Nuxt 4.0 Announcement](https://nuxt.com/blog/v4)
- [Nuxt Performance Best Practices](https://nuxt.com/docs/4.x/guide/best-practices/performance)
- [Epixel MLM Dashboard](https://www.epixelmlmsoftware.com/product-updates/customizable-mlm-dashboard)
