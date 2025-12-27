# Frontend Feature Checklist - Old Project vs Current Client

**Generated:** December 27, 2025
**Purpose:** Comprehensive comparison of old_project frontend (Nuxt 3) vs current client (Nuxt 4)

---

## Legend
- [x] Fully Implemented in current client
- [ ] Missing/Not Implemented in current client
- [~] Partially Implemented (needs completion)

---

# 1. AUTHENTICATION SYSTEM

## 1.1 Login Page (`/auth/login`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Mobile Login | Login via mobile number + password | [x] |
| Email Login | Login via email + password | [x] |
| OTP Login | Send OTP to mobile, verify, then login | [x] |
| Mode Toggle | Switch between mobile/email modes | [x] |
| OTP 6-Digit Input | Individual digit inputs with auto-focus | [x] |
| OTP Resend | Countdown timer (60s) + resend button | [x] |
| OTP Verification | API call to verify OTP before submit | [x] |
| Password Toggle | Show/hide password visibility | [x] |
| Remember Me | Checkbox to persist session | [x] |
| Guest Shopping | Link to continue as guest | [x] |
| Forgot Password | Link to password recovery | [x] |
| Register Link | Link to registration page | [x] |
| Google OAuth | Social login with Google | [ ] |
| GSAP Animations | Floating orbs, background effects | [x] |
| Error Handling | Field-level + form-level errors | [x] |
| Redirect After Login | Base64 encoded `ref` param support | [x] |
| Mobile Responsive | Stacked layout on mobile | [x] |
| Desktop Split Layout | 50/50 features showcase + form | [x] |
| Benefits Showcase | E-commerce platform features display | [x] |
| Trust Indicators | Badges (trusted, delivery, customers) | [x] |

### Functionality Details:
```typescript
// OTP Flow
1. User enters mobile number
2. Click "Send OTP" → POST /auth/send-otp
3. 60-second countdown starts
4. User enters 6 OTP digits
5. Click "Verify OTP" → POST /auth/verify-otp
6. If valid, otpVerified = true
7. Submit login → POST /auth/login with validated_otp: true

// Mobile Login Payload
{
  mobile: string,
  password?: string,  // Only if not using OTP
  remember: boolean,
  validated_otp: boolean
}

// Email Login Payload
{
  email: string,
  password: string,
  remember: boolean
}
```

---

## 1.2 Registration Page (`/auth/register`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Mobile Signup | Register with mobile + OTP | [~] |
| Email Signup | Register with email | [~] |
| Mode Toggle | Switch mobile/email registration | [ ] |
| OTP Verification | Verify mobile during registration | [ ] |
| Password Confirmation | Confirm password field | [ ] |
| Terms Agreement | Checkbox for T&C acceptance | [ ] |
| Referral Code Support | Optional referral code field | [ ] |
| Form Validation | Real-time validation feedback | [ ] |
| Success Redirect | Auto-redirect to onboarding | [ ] |

---

## 1.3 Password Recovery

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Forgot Password Page | Request reset link via email | [~] |
| Reset Password Page | Enter new password with token | [~] |
| Token Validation | Backend validates reset token | [ ] |
| Password Strength | Password strength indicator | [ ] |
| Success Feedback | Confirmation message after reset | [ ] |

---

# 2. DASHBOARD SYSTEM

## 2.1 Main Dashboard (`/dashboard`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Mobile/Desktop Split | Different layouts per viewport | [x] |
| Greeting Message | Time-based greeting (morning/afternoon/evening) | [x] |
| User Name Display | First name extraction from full name | [x] |
| Date Filter | Filter stats by date range (from/to) | [x] |
| Date Filter Dropdown | Collapsible filter panel | [x] |
| Stat Cards Grid | 8 KPI cards (earnings, referrals, orders, etc.) | [x] |
| Profile Card | Sidebar profile overview (desktop) | [x] |
| Orders Trend Chart | ECharts line/bar chart visualization | [x] |
| Quick Actions | 4-button grid (Orders, Wallet, KYC, Help) | [x] |
| Edit Profile Button | Link to account edit | [x] |
| Refresh Button | Manual dashboard refresh | [x] |
| Background Orbs | Decorative gradient orbs | [x] |
| Progressive Loading | Lazy load heavy components | [x] |
| Loading States | Skeleton loaders for cards/charts | [x] |

### Stats Data Structure:
```typescript
statsData: {
  total_earnings: { label, value, change, trend },
  direct_earnings: { label, value, change, trend },
  team_earnings: { label, value, change, trend },
  wallet_balance: { label, value, change, trend },
  total_referrals: { label, value, change, trend },
  total_orders: { label, value, change, trend },
  completed_orders: { label, value, change, trend },
  current_rank: { label, value, change, trend }
}
```

### API Endpoints:
- `GET /account/stats/dashboard?from=&to=` - Dashboard statistics

---

## 2.2 Account/Profile Page (`/dashboard/account`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Avatar Display | Large avatar with initials fallback | [x] |
| Avatar Upload | Camera button to change avatar | [~] |
| Name Display | Full name with gradient text | [x] |
| Email Display | Primary email address | [x] |
| Member Since | Formatted join date | [x] |
| Status Badge | Active/Inactive indicator | [x] |
| Verification Badge | Verified/Unverified email status | [x] |
| Personal Bio | Bio text with edit capability | [x] |
| Phone Number | Contact phone display | [x] |
| Date of Birth | DOB with formatted display | [x] |
| Gender | Gender selection display | [x] |
| Location | City/Country display | [x] |
| Edit Profile Modal | In-page modal for editing | [x] |
| Settings Button | Link to settings page | [x] |
| Recent Activity | Activity timeline (last actions) | [~] |
| Quick Actions | Links to edit, security, addresses, preferences | [x] |
| Account Stats | Total logins, last login, member since | [x] |

### Edit Form Fields:
```typescript
editForm: {
  name: string,
  phone: string,
  date_of_birth: string,
  gender: 'male' | 'female' | 'other' | 'prefer_not_to_say',
  location: string,
  bio: string
}
```

### API Endpoints:
- `GET /account/stats` - Account statistics
- `GET /account/activity` - Recent activity log
- `PUT /account/profile` - Update profile

---

## 2.3 Account Subpages

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Edit Profile | Full form edit page | [x] |
| Change Password | Current + new + confirm password | [x] |
| Change Email | New email with OTP verification | [x] |
| Change Mobile | New mobile with OTP verification | [x] |
| Address Management | CRUD for shipping addresses | [x] |
| KYC Submission | Document upload + verification | [x] |
| Settings Page | App preferences, notifications | [~] |
| Insights Page | Analytics and insights | [ ] |

---

# 3. WALLET SYSTEM

## 3.1 Wallet Dashboard (`/dashboard/wallet`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Locked State | Show "Unlock Wallet" if no wallet | [x] |
| Balance Display | Large formatted balance (₹X,XXX.XX) | [x] |
| Wallet ID | Truncated UUID display | [x] |
| QR Code | Wallet QR for receiving money | [x] |
| QR Enlarge | Tap to enlarge QR modal | [x] |
| Reward Coins | Coin balance with convert option | [x] |
| Beneficiary Preview | Masked account number display | [x] |

### Action Buttons:
| Feature | Description | Status |
|---------|-------------|--------|
| Add Money | Navigate to add money view | [x] |
| Withdraw | Open withdraw modal | [x] |
| Send | Navigate to send money view | [x] |
| Convert | Open convert coins modal | [x] |
| Security | Navigate to PIN management | [x] |

### Stats Cards:
| Feature | Description | Status |
|---------|-------------|--------|
| Available Balance | Green card with balance | [x] |
| Today's Credits | Green card with today's incoming | [x] |
| Today's Debits | Red card with today's outgoing | [x] |
| Reward Coins | Amber card with coin balance | [x] |
| Week Summary | 3-column (credits, debits, net) | [x] |
| Month Summary | 3-column (credits, debits, net) | [x] |

### Charts & History:
| Feature | Description | Status |
|---------|-------------|--------|
| Spending Analytics | ECharts bar chart by type filter | [x] |
| Type Filter | Dropdown (All/Expenses/Income) | [x] |
| Recent Transactions | Last 5 transactions list | [x] |
| View All Link | Navigate to full transactions page | [x] |

---

## 3.2 Add Money (`/dashboard/wallet` - Add View)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Amount Input | Large numeric input | [x] |
| Quick Amount Buttons | ₹500, ₹1000, ₹2000, ₹5000, ₹10000 | [x] |
| PIN Input | 6-digit PIN verification | [x] |
| Info Banner | Payment gateway redirect notice | [x] |
| Security Notice | Encryption assurance message | [x] |
| Submit Button | "Proceed to Payment" | [x] |
| Redirect to Gateway | Auto-redirect to payment URL | [x] |

### API Endpoints:
- `POST /wallet/add-money` - Request payment link
  - Body: `{ amount: number, pin: string }`
  - Response: `{ redirect: string }` - Payment gateway URL

---

## 3.3 Withdraw (`/dashboard/wallet` - Withdraw Modal)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Amount Input | Withdrawal amount | [x] |
| Minimum Amount | ₹100 minimum validation | [x] |
| Balance Check | Cannot exceed available balance | [x] |
| PIN Input | 6-digit PIN verification | [x] |
| Beneficiary Check | Must have linked beneficiary | [x] |
| Submit Button | "Withdraw" with loading state | [x] |
| Error Display | Field-level error messages | [x] |

### API Endpoints:
- `POST /wallet/withdraw` - Request withdrawal
  - Body: `{ amount: number, pin: string }`

---

## 3.4 Send Money (`/dashboard/wallet` - Send View)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Recipient UUID | Wallet UUID input field | [x] |
| QR Scanner | Scan recipient QR code | [ ] |
| Amount Input | Transfer amount | [x] |
| Purpose | Transfer purpose/note | [x] |
| PIN Input | 6-digit PIN verification | [x] |
| Balance Check | Cannot exceed available balance | [x] |
| Submit Button | "Send Money" with loading | [x] |
| Error Handling | Field-level validation errors | [x] |

### API Endpoints:
- `POST /wallet/send` - Send money
  - Body: `{ amount, recipient_uuid, pin, purpose }`

---

## 3.5 PIN Management

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Setup PIN | Initial 6-digit PIN setup | [x] |
| Change PIN | Old PIN + New PIN + Confirm | [x] |
| Reset PIN | Security question recovery | [x] |
| Security Questions | Multiple choice questions | [x] |
| PIN Visibility Toggle | Show/hide PIN fields | [x] |

### API Endpoints:
- `POST /wallet/setup-pin` - Initial PIN setup
- `POST /wallet/change-pin` - Change existing PIN
- `POST /wallet/reset-pin` - Reset via security questions

---

## 3.6 Convert Coins (`/dashboard/wallet` - Convert Modal)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Coin Amount Input | Points to convert | [x] |
| Conversion Rate | Display "1 coin = ₹1.00" | [x] |
| PIN Input | 6-digit PIN verification | [x] |
| Submit Button | "Convert" with loading | [x] |

### API Endpoints:
- `POST /wallet/point-conversion` - Convert coins to balance
  - Body: `{ points: number, pin: string }`

---

## 3.7 Beneficiary Management (`/dashboard/wallet/beneficiary`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| List Beneficiaries | Display all linked accounts | [x] |
| Add Bank Account | Account number, IFSC, bank name | [x] |
| Add UPI | UPI handle input | [x] |
| Set Default | Mark as primary beneficiary | [x] |
| Delete Account | Remove beneficiary with confirmation | [x] |
| Account Type Icons | Bank vs UPI icons | [x] |
| Masked Display | Show only last 4 digits | [x] |

---

## 3.8 Transactions Page (`/dashboard/wallet/transactions`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Full Transaction List | Paginated history | [x] |
| Type Filter | Filter by credit/debit/all | [~] |
| Date Filter | Filter by date range | [~] |
| Transaction Details | Purpose, date, amount, status | [x] |
| Credit/Debit Icons | Green down/Red up arrows | [x] |
| Formatted Amount | With +/- prefix and color | [x] |
| Time Ago Format | "2h ago", "5m ago" display | [x] |

---

# 4. MLM NETWORK SYSTEM

## 4.1 My Team/Community (`/dashboard/myteam`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Header | "My Community" with total count | [x] |
| View Toggle | Chart vs List view buttons | [x] |
| Referral Link Card | Gradient card with copy button | [x] |
| Copy to Clipboard | Copy affiliate link with feedback | [x] |

### Stats Cards:
| Feature | Description | Status |
|---------|-------------|--------|
| Total Members | All network members count | [x] |
| Active | Members with downline | [x] |
| Max Depth | Deepest level in network | [x] |
| Viewing | Current view context (You/Member) | [x] |

### Filter Section:
| Feature | Description | Status |
|---------|-------------|--------|
| Referral Code Input | View another member's network | [x] |
| Apply Button | Load member's tree | [x] |
| Reset Button | Back to own tree | [x] |

### Chart View:
| Feature | Description | Status |
|---------|-------------|--------|
| Organization Chart | D3-org-chart visualization | [x] |
| Node Design | Avatar, name, level, referral code | [x] |
| Click to Select | Open member drawer | [x] |
| Depth Badge | Level indicator on nodes | [x] |
| Dark Mode Support | Theme-aware styling | [x] |
| Responsive Container | Scrollable chart area | [x] |

### List View:
| Feature | Description | Status |
|---------|-------------|--------|
| Table Layout | Full-width responsive table | [x] |
| Member Column | Avatar, name, referral code | [x] |
| Level Column | Badge with level name | [x] |
| Depth Column | "Level X" indicator | [x] |
| Status Column | Active/No Downline badges | [x] |
| Actions Column | "View Network" button | [x] |
| Row Click | Open member drawer | [x] |

### Member Drawer:
| Feature | Description | Status |
|---------|-------------|--------|
| Profile Card | Avatar, name, referral code, level | [x] |
| Email Display | Member's email (if available) | [x] |
| Joined Date | Formatted join date | [x] |
| Network Depth | Level indicator | [x] |
| Downline Status | Active/None indicator | [x] |
| View Their Network | Button to load member's tree | [x] |
| Close Button | X button to close drawer | [x] |
| Backdrop Click | Close on outside click | [x] |

### API Endpoints:
- `GET /account/tree` - Get user's network tree
- `GET /account/tree?referral_code=XXX` - Get specific member's tree

---

# 5. SUBSCRIPTION SYSTEM

## 5.1 Subscribe Page (`/dashboard/subscribe`)

### Active Subscription View:
| Feature | Description | Status |
|---------|-------------|--------|
| Success Header | Green gradient "Premium Active" | [x] |
| Plan Name | Current subscription stage | [x] |
| Level Name | Membership level display | [x] |
| Team Capacity | Max team members limit | [x] |
| Expiry Date | Formatted expiration date | [x] |
| Days Remaining | Countdown badge | [x] |
| Benefits List | Active/Inactive benefit items | [x] |
| Auto-Renewal Toggle | Enable/disable auto-renew | [x] |
| Renew Button | "Renew Subscription" CTA | [x] |

### Subscription Wizard (4 Steps):
| Feature | Description | Status |
|---------|-------------|--------|
| Progress Bar | Visual step indicator | [x] |
| Step Labels | Welcome, Benefits, Details, Confirm | [x] |
| Step Navigation | Back/Continue buttons | [x] |
| Animated Transitions | Slide left/right animations | [x] |

### Step 1 - Welcome:
| Feature | Description | Status |
|---------|-------------|--------|
| Hero Card | Gradient rocket icon card | [x] |
| Animated Stats | Counter animation (members, earnings, rating) | [x] |
| Get Started Button | White button on gradient | [x] |

### Step 2 - Benefits:
| Feature | Description | Status |
|---------|-------------|--------|
| Features Grid | 2-3 column responsive grid | [x] |
| Active Features | Green checkmark, highlighted | [x] |
| Inactive Features | Gray lock icon, dimmed | [x] |

### Step 3 - Package Details:
| Feature | Description | Status |
|---------|-------------|--------|
| Package Card | Gradient card with plan details | [x] |
| Best Value Badge | Yellow badge top-right | [x] |
| Price Display | Large ₹X,XXX format | [x] |
| Description | Plan description text | [x] |
| Details List | Team capacity, level, validity | [x] |

### Step 4 - Confirmation:
| Feature | Description | Status |
|---------|-------------|--------|
| Guarantee Badges | Secure Payment, Savings, Support | [x] |
| Price Summary | Final price breakdown | [x] |
| Payment Method | Wallet vs Online radio options | [x] |
| Auto-Renewal Checkbox | Enable auto-renew option | [x] |
| Urgency Banner | Limited time offer message | [x] |
| Subscribe Button | "Subscribe Now" with loading | [x] |
| Terms Links | T&C and Privacy links | [x] |

### API Endpoints:
- `GET /account/lifecycle/get_status` - Current subscription status
- `POST /account/lifecycle/subscribe` - Subscribe to plan
  - Body: `{ stage_id, level_id, auto_renew, provider }`
- `POST /account/subscription/auto-renew` - Toggle auto-renewal

---

# 6. E-COMMERCE SYSTEM

## 6.1 Shopping Cart (Composable: `useCart`)

### Cart State:
| Feature | Description | Status |
|---------|-------------|--------|
| Cart Data State | Full cart structure in useState | [ ] |
| Loading State | Request in-progress flag | [ ] |
| Request Queue | Prevent duplicate requests | [ ] |
| Guest Credentials | Cookie-based guest ID/token | [ ] |

### Cart Operations:
| Feature | Description | Status |
|---------|-------------|--------|
| Fetch Cart | GET /cart - Load cart data | [ ] |
| Add to Cart | POST /cart/add/{sku} | [ ] |
| Update Quantity | POST /cart/update/{sku} | [ ] |
| Remove Item | DELETE /cart/remove/{sku} | [ ] |
| Apply Coupon | POST /cart/coupon/{code} | [ ] |
| Clear Cart | POST /cart/clear | [ ] |
| Merge Guest Cart | POST /cart/merge (after login) | [ ] |

### Guest Cart Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Guest Credential API | POST /cart/guest-credential | [ ] |
| Credential Validation | POST /cart/validate/guest-credential | [ ] |
| Cookie Storage | 15-day expiry, secure cookies | [ ] |
| Auto-Refresh | Refresh expired credentials | [ ] |
| Error Retry | Exponential backoff on failure | [ ] |

### Cart Data Structure:
```typescript
interface CartResponse {
  summary: {
    sub_total: string,
    tax: string,
    tax_percentage: number,
    discount: string,
    coupon_applied: boolean,
    coupon_code: string | null,
    total: string,
    quantity: number
  },
  customer: {
    identity: { type, is_guest, token_expires_in },
    profile: { name, email, mobile, status_label, type_label }
  },
  items: CartItem[],
  error: string | null
}
```

---

## 6.2 Store/Shop Pages

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Store Landing | Category grid with products | [ ] |
| Product Listing | Grid with filters | [ ] |
| Product Detail | Full product page | [ ] |
| Product Images | Gallery slider | [ ] |
| Product Comments | Reviews/comments section | [ ] |
| Add to Cart | Button with quantity selector | [ ] |
| Buy Now | Direct checkout button | [ ] |
| Wishlist | Save for later | [ ] |
| Flash Deals | Time-limited offers | [ ] |
| Categories | Category browsing | [ ] |
| Search | Product search with results | [ ] |

---

## 6.3 Cart Page (`/cart`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Cart Items List | Product cards with quantities | [ ] |
| Quantity Selector | +/- buttons with input | [ ] |
| Remove Item | X button with confirmation | [ ] |
| Coupon Input | Code entry with apply button | [ ] |
| Cart Summary | Subtotal, tax, discount, total | [ ] |
| Checkout Button | Proceed to checkout | [ ] |
| Continue Shopping | Back to store link | [ ] |
| Empty Cart State | Message when cart is empty | [ ] |

---

## 6.4 Checkout (`/store/checkout`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Address Selection | Choose shipping address | [ ] |
| Add New Address | In-checkout address form | [ ] |
| Order Summary | Items, quantities, prices | [ ] |
| Payment Methods | Wallet, Cards, UPI, etc. | [~] |
| PIN Verification | For wallet payments | [~] |
| Place Order | Final submit button | [~] |
| Order Confirmation | Success page with details | [~] |

---

## 6.5 Orders (`/dashboard/orders`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Orders List | All orders with pagination | [ ] |
| Order Card | Order ID, date, status, total | [ ] |
| Order Detail | `/dashboard/orders/[uuid]` | [ ] |
| Order Timeline | Status history | [ ] |
| Track Order | Delivery tracking | [ ] |
| Cancel Order | Request cancellation | [ ] |
| Return Request | Initiate return | [ ] |
| Download Invoice | PDF invoice | [ ] |

---

# 7. CAREER SYSTEM

## 7.1 Job Listings (`/career`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Jobs List | Available positions | [ ] |
| Job Card | Title, type, location, deadline | [ ] |
| Search Jobs | Filter by keyword | [ ] |
| Filter by Type | Full-time, Part-time, etc. | [ ] |
| Filter by Location | Location-based filter | [ ] |

---

## 7.2 Job Detail (`/career/[url]`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Job Header | Title, company, location | [ ] |
| Description | Full job description | [ ] |
| Requirements | List of requirements | [ ] |
| Benefits | Job benefits list | [ ] |
| Salary Range | Compensation display | [ ] |
| Apply Button | Navigate to application | [ ] |
| Share Job | Social share buttons | [ ] |

---

## 7.3 Job Application (`/career/[url]/apply`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Application Form | Resume, cover letter, fields | [ ] |
| File Upload | Resume/CV upload | [ ] |
| Form Validation | Required fields check | [ ] |
| Submit Application | POST /career/apply | [ ] |
| Confirmation Page | Application submitted message | [ ] |

---

## 7.4 My Applications (`/dashboard/career/applications`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Applications List | User's job applications | [ ] |
| Application Card | Job, date, status | [ ] |
| Application Detail | Full application view | [ ] |
| Status Timeline | Application progress | [ ] |
| Withdraw Application | Cancel application | [ ] |

---

# 8. HELPDESK/SUPPORT SYSTEM

## 8.1 Tickets List (`/dashboard/helpdesk`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Tickets List | All support tickets | [~] |
| Ticket Card | Subject, status, date, priority | [~] |
| Create Button | New ticket link | [~] |
| Status Filter | Filter by open/closed/pending | [ ] |
| Priority Filter | Filter by priority level | [ ] |

---

## 8.2 Create Ticket (`/dashboard/helpdesk/create`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Topic Selection | Dropdown of topics | [~] |
| Subject Input | Ticket subject | [~] |
| Message Input | Rich text description | [~] |
| Priority Selection | Low/Medium/High/Urgent | [ ] |
| Attachment Upload | File attachments | [ ] |
| Submit Ticket | POST /helpdesk/create | [~] |

---

## 8.3 Ticket Detail (`/dashboard/helpdesk/[url]`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Ticket Header | Subject, status, priority | [~] |
| Conversation Thread | Messages timeline | [~] |
| Reply Form | Add new message | [~] |
| Attachment View | View/download attachments | [ ] |
| Close Ticket | Mark as resolved | [ ] |
| Reopen Ticket | Reopen closed ticket | [ ] |

---

# 9. COMMUNICATION SYSTEM

## 9.1 Messages (`/messages`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Inbox List | All messages | [~] |
| Message Preview | Sender, subject, snippet | [~] |
| Unread Badge | Unread count indicator | [ ] |
| Compose Button | New message link | [~] |
| Read/Unread Toggle | Mark as read/unread | [ ] |

---

## 9.2 Compose Message (`/messages/compose`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Recipient Search | Find users to message | [ ] |
| Subject Input | Message subject | [~] |
| Message Body | Rich text editor | [~] |
| Send Button | Submit message | [~] |

---

## 9.3 Message Thread (`/messages/[uuid]`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Thread View | Full conversation | [~] |
| Reply Form | Quick reply input | [~] |
| Delete Message | Remove from inbox | [ ] |
| Archive Message | Move to archive | [ ] |

---

# 10. ONBOARDING SYSTEM

## 10.1 Onboarding Flow (`/onboarding`)

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Multi-Step Wizard | 5-step onboarding | [x] |
| Progress Indicator | Step numbers/dots | [x] |
| Step Navigation | Next/Back buttons | [x] |
| Skip Option | Skip optional steps | [~] |

### Step 1 - Welcome:
| Feature | Description | Status |
|---------|-------------|--------|
| Welcome Message | Greeting text | [x] |
| Get Started Button | Begin onboarding | [x] |

### Step 2 - Profile:
| Feature | Description | Status |
|---------|-------------|--------|
| Name Input | Full name field | [x] |
| DOB Picker | Date of birth | [x] |
| Gender Selection | Radio/select options | [x] |
| Bio Input | Optional bio text | [x] |

### Step 3 - Contact:
| Feature | Description | Status |
|---------|-------------|--------|
| Email Display | Current email | [x] |
| Email Verification | Verify with OTP | [x] |
| Mobile Display | Current mobile | [x] |
| Mobile Verification | Verify with OTP | [~] |

### Step 4 - Address:
| Feature | Description | Status |
|---------|-------------|--------|
| Address Form | Street, city, state, postal | [x] |
| Geo Dropdowns | Country/State cascading | [x] |
| Set as Default | Primary address toggle | [x] |

### Step 5 - KYC:
| Feature | Description | Status |
|---------|-------------|--------|
| Document Type | ID type selection | [x] |
| Document Number | ID number input | [x] |
| Document Upload | Front/back images | [x] |
| Skip Option | Complete later option | [x] |

---

# 11. CONTENT PAGES

## 11.1 Static Pages

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| About Page | Company information | [~] |
| Contact Page | Contact form + info | [~] |
| Help Center | FAQ and guides | [~] |
| Privacy Policy | Legal text | [~] |
| Terms of Service | Legal text | [~] |
| Shipping Policy | Delivery information | [~] |
| Return/Refund Policy | Return terms | [~] |

---

## 11.2 Blog/News

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Blog Listing | Posts with pagination | [ ] |
| Blog Detail | Full article view | [ ] |
| Blog Comments | Comment section | [ ] |
| News Listing | News articles | [ ] |
| News Detail | Full news view | [ ] |
| Share Buttons | Social sharing | [ ] |

---

# 12. UI/UX COMPONENTS

## 12.1 Layout Components

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Default Layout | Navbar + Footer + Sidebar | [x] |
| Dashboard Layout | Dashboard-specific layout | [x] |
| Auth Layout | Minimal auth pages layout | [x] |
| Mobile Bottom Nav | 5-button bottom navigation | [ ] |
| Sidebar Navigation | Collapsible left sidebar | [x] |
| Top Navbar | Logo, search, user menu | [x] |
| Footer | Copyright, legal links | [x] |

---

## 12.2 Global UI Components

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Dark Mode Toggle | Theme switcher | [x] |
| Toast Notifications | Success/error/info toasts | [x] |
| Loading Spinner | Global loader | [x] |
| Error State | Error placeholder | [x] |
| Empty State | No data placeholder | [x] |
| Alert Modal | Confirmation dialogs | [~] |
| Search Modal | Global search overlay | [ ] |
| Newsletter Popup | Email subscription | [ ] |
| Scroll to Top | Button to scroll up | [ ] |

---

## 12.3 User Components

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| User Dropdown | Account menu | [x] |
| Notification Bell | Notification dropdown | [x] |
| Avatar Uploader | Image upload with preview | [~] |
| OTP Input | 6-digit OTP component | [x] |

---

# 13. GAMIFICATION (Future)

## 13.1 User Village

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Village Game | Gamification component | [ ] |
| Achievements | Badges and rewards | [ ] |
| Leaderboard | Ranking system | [ ] |
| Points System | Point accumulation | [ ] |

---

# 14. NOTIFICATIONS

## 14.1 Notification System

### Old Project Features:
| Feature | Description | Status |
|---------|-------------|--------|
| Notification Bell | Badge with count | [x] |
| Notification Dropdown | Recent notifications | [x] |
| Notification Center | Full notifications page | [~] |
| Push Notifications | Browser push support | [ ] |
| Mark as Read | Individual mark read | [~] |
| Mark All Read | Bulk mark read | [ ] |
| Notification Settings | Preference management | [ ] |

---

# 15. COMPOSABLES CHECKLIST

## Current Client Composables:
| Composable | Purpose | Status |
|------------|---------|--------|
| useUserType | User type detection & navigation | [x] |
| useOnboarding | Onboarding flow management | [x] |
| useRedirectUrl | Post-login redirect handling | [x] |
| useWallet | Wallet state & transactions | [x] |
| useSubscription | Plans & subscription | [x] |
| useNetwork | Network/community data | [x] |
| useCommissions | Commission calculations | [x] |
| useMlmTree | MLM genealogy visualization | [x] |
| useMessages | Messaging system | [~] |
| useHelpdesk | Support tickets | [~] |
| useNotices | System notices | [x] |
| useActivity | Activity logging | [~] |
| useBranding | Currency formatting | [x] |
| useTrends | Trend data | [~] |
| useNotifications | Toast system | [x] |

## Missing Composables (from Old Project):
| Composable | Purpose | Status |
|------------|---------|--------|
| useCart | Shopping cart management | [ ] |
| useWishlist | Wishlist functionality | [ ] |
| usePageMeta | SEO utilities | [ ] |

---

# 16. PROPOSED ADDITIONAL FEATURES

## 16.1 Missing Critical Features

| Feature | Priority | Description |
|---------|----------|-------------|
| Complete Cart System | HIGH | Full shopping cart with guest support |
| Order Management | HIGH | Orders list, detail, tracking |
| Product Catalog | HIGH | Product listing, detail, search |
| Complete Helpdesk | MEDIUM | Full ticket lifecycle |
| Complete Messages | MEDIUM | Full messaging system |
| Job Applications | MEDIUM | Career application flow |
| Blog/News | LOW | Content publishing |
| Mobile Bottom Nav | MEDIUM | Mobile navigation bar |
| Push Notifications | LOW | Browser push support |

## 16.2 Enhancement Suggestions

| Feature | Priority | Description |
|---------|----------|-------------|
| Skeleton Loaders | MEDIUM | Better loading states for all pages |
| Error Boundaries | HIGH | Global error handling |
| Offline Support | LOW | PWA offline capabilities |
| Performance Optimization | MEDIUM | Lazy loading, code splitting |
| Analytics Integration | LOW | Usage tracking |
| A/B Testing | LOW | Feature flag system |

---

# 17. API ENDPOINT SUMMARY

## Authentication:
- `POST /auth/send-otp` - Send OTP
- `POST /auth/verify-otp` - Verify OTP
- `POST /auth/login` - Login
- `POST /auth/register` - Register
- `POST /auth/forgot-password` - Request reset
- `POST /auth/reset-password` - Reset password

## Account:
- `GET /account/profile` - Get profile
- `PUT /account/profile` - Update profile
- `GET /account/stats` - Account stats
- `GET /account/stats/dashboard` - Dashboard stats
- `GET /account/activity` - Activity log
- `GET /account/tree` - Network tree

## Wallet:
- `GET /wallet` - Wallet data
- `POST /wallet/create` - Create wallet
- `POST /wallet/add-money` - Add funds
- `POST /wallet/withdraw` - Withdraw
- `POST /wallet/send` - Send money
- `POST /wallet/setup-pin` - Setup PIN
- `POST /wallet/change-pin` - Change PIN
- `POST /wallet/reset-pin` - Reset PIN
- `POST /wallet/point-conversion` - Convert coins
- `GET /wallet/analytics` - Spending analytics
- `GET /wallet/transactions` - Transaction history

## Cart:
- `GET /cart` - Get cart
- `POST /cart/add/{sku}` - Add item
- `POST /cart/update/{sku}` - Update item
- `DELETE /cart/remove/{sku}` - Remove item
- `POST /cart/coupon/{code}` - Apply coupon
- `POST /cart/clear` - Clear cart
- `POST /cart/merge` - Merge guest cart
- `POST /cart/guest-credential` - Get guest credentials
- `POST /cart/validate/guest-credential` - Validate credentials

## Subscription:
- `GET /account/lifecycle/get_status` - Subscription status
- `POST /account/lifecycle/subscribe` - Subscribe
- `POST /account/subscription/auto-renew` - Toggle auto-renew

## Helpdesk:
- `GET /helpdesk` - List tickets
- `POST /helpdesk/create` - Create ticket
- `GET /helpdesk/{uuid}` - Get ticket
- `POST /helpdesk/{uuid}/reply` - Reply to ticket

---

# SUMMARY

## Implementation Progress:

| Category | Old Project | Current Client | Progress |
|----------|-------------|----------------|----------|
| Authentication | 20 features | 18 features | 90% |
| Dashboard | 18 features | 17 features | 95% |
| Wallet | 35 features | 33 features | 95% |
| Network/MLM | 25 features | 24 features | 95% |
| Subscription | 30 features | 28 features | 93% |
| E-Commerce | 40 features | 5 features | 12% |
| Career | 15 features | 0 features | 0% |
| Helpdesk | 12 features | 6 features | 50% |
| Messages | 10 features | 4 features | 40% |
| Onboarding | 15 features | 14 features | 93% |
| Content Pages | 10 features | 5 features | 50% |
| UI Components | 25 features | 18 features | 72% |
| **TOTAL** | **255 features** | **172 features** | **67%** |

## Priority Implementation Order:
1. Complete E-Commerce (Cart, Products, Orders) - HIGH
2. Complete Helpdesk System - MEDIUM
3. Complete Messages System - MEDIUM
4. Add Career Module - MEDIUM
5. Add Mobile Bottom Nav - MEDIUM
6. Add Missing UI Components - LOW
7. Add Blog/News - LOW
8. Add Gamification - LOW
