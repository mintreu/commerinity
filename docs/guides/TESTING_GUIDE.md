# Testing Guide - Commerinity Pro

## 🎯 Quick Start Testing

### Step 1: Start Servers

**Terminal 1 - Laravel API**
```bash
cd apiserver
php artisan serve
```
Server: http://localhost:8000

**Terminal 2 - Nuxt Frontend**
```bash
cd client
npm run dev
```
Frontend: http://localhost:3000

### Step 2: Seed Demo Users

```bash
cd apiserver
php artisan db:seed --class=DemoUserSeeder
```

## 🧪 Test Scenarios

### Scenario 1: Registration Flow (New User)

**URL**: http://localhost:3000/auth/register

**Steps**:
1. Enter full name: `Test User`
2. Enter mobile: `+919999999999`
3. Click "Send OTP" button
4. Wait for toast notification showing demo OTP: `123456`
5. Enter OTP: `123456`
6. (Optional) Enter email: `test@example.com`
7. Enter password: `Password123!`
8. Confirm password: `Password123!`
9. (Optional) Enter referral code from another user
10. Check "I agree to Terms & Conditions"
11. Click "Create Account"

**Expected Result**:
- ✅ Success message
- ✅ Automatically logged in
- ✅ Redirected to `/dashboard/regular`
- ✅ User appears in sidebar
- ✅ Can see dashboard content

---

### Scenario 2: Login with Mobile + Password

**URL**: http://localhost:3000/auth/login

**Steps**:
1. Select "Mobile" tab (default)
2. Enter mobile: `+919876543210`
3. Select "Password" method (default)
4. Enter password: `password`
5. (Optional) Check "Remember me"
6. Click "Sign In"

**Expected Result**:
- ✅ Successfully logged in
- ✅ Redirected to `/dashboard/regular`
- ✅ User dropdown shows "Regular Customer"
- ✅ Navigation shows: Dashboard, Profile, Shop, Orders

---

### Scenario 3: Login with Mobile + OTP

**URL**: http://localhost:3000/auth/login

**Steps**:
1. Select "Mobile" tab
2. Enter mobile: `+919876543210`
3. Select "OTP" method
4. Click "Send OTP"
5. Wait for toast with demo OTP: `123456`
6. Enter OTP: `123456`
7. Click "Sign In"

**Expected Result**:
- ✅ Successfully logged in
- ✅ Redirected to appropriate dashboard

---

### Scenario 4: Login with Email + Password

**URL**: http://localhost:3000/auth/login

**Steps**:
1. Select "Email" tab
2. Enter email: `member@demo.com`
3. Enter password: `password`
4. Click "Sign In"

**Expected Result**:
- ✅ Successfully logged in
- ✅ Redirected to `/dashboard/member`
- ✅ User dropdown shows "Member"
- ✅ Navigation includes: My Network, Earnings

---

### Scenario 5: Test Different User Types

Login with each demo user to see different dashboards:

#### Regular Customer
```
Email: regular@demo.com
Password: password
→ Dashboard: /dashboard/regular
→ Navigation: 4 items
```

#### Member
```
Email: member@demo.com
Password: password
→ Dashboard: /dashboard/member
→ Navigation: 6 items (+ Network, Earnings)
→ Features: Referral code, Team stats
```

#### Promoter
```
Email: promoter@demo.com
Password: password
→ Dashboard: /dashboard/promoter
→ Navigation: 8 items (+ Promotions, Marketing)
→ Features: Campaign tools
```

#### Advisor
```
Email: advisor@demo.com
Password: password
→ Dashboard: /dashboard/advisor
→ Navigation: 9 items (+ Team, Reports, Training)
→ Features: Team management
```

#### Mentor
```
Email: mentor@demo.com
Password: password
→ Dashboard: /dashboard/mentor
→ Navigation: 11 items (+ Leadership, Analytics)
→ Features: Organization overview, Advanced analytics
```

---

### Scenario 6: User Dropdown Menu

**After logging in**:

**Steps**:
1. Click on user avatar/name in top-right
2. Dropdown should appear

**Expected in Dropdown**:
- ✅ User avatar with green online dot
- ✅ User name and mobile
- ✅ User type badge (colored)
- ✅ Quick stats (Orders, Wallet, Points)
- ✅ Menu items:
  - Profile Settings
  - My Orders
  - My Network (if not Regular)
- ✅ Logout button

---

### Scenario 7: Navigation Items

**Regular User Navigation**:
- Dashboard
- Profile
- Shop
- Orders

**Member+ Navigation** (Additional):
- My Network
- Earnings

**Promoter+ Navigation** (Additional):
- Promotions
- Marketing

**Advisor+ Navigation** (Additional):
- Team
- Reports
- Training

**Mentor Navigation** (Additional):
- Leadership
- Analytics

---

### Scenario 8: Logout

**Steps**:
1. Click user dropdown
2. Click "Logout" button

**Expected Result**:
- ✅ Logged out successfully
- ✅ Redirected to `/auth/login`
- ✅ Token removed from cookies
- ✅ Cannot access protected pages

---

### Scenario 9: Theme Toggle

**Steps**:
1. Click sun/moon icon in topbar
2. Theme should toggle

**Expected Result**:
- ✅ Colors switch between light/dark
- ✅ Preference saved to localStorage
- ✅ All pages support dark mode
- ✅ Gradients adapt to theme

---

## 🐛 Common Issues & Solutions

### Issue: "Invalid credentials"
**Solution**: Check that demo users are seeded
```bash
cd apiserver
php artisan db:seed --class=DemoUserSeeder
```

### Issue: "Failed to send OTP"
**Solution**: Check Laravel API is running at http://localhost:8000

### Issue: CORS errors
**Solution**:
1. Check `.env` has `APP_CLIENT_URL=http://localhost:3000`
2. Verify CORS middleware in `bootstrap/app.php`

### Issue: Token not persisting
**Solution**: Check browser cookies for `commerinity_auth_token`

### Issue: Can't access dashboard
**Solution**:
1. Verify you're logged in
2. Check token is valid
3. Try logout and login again

### Issue: Navigation items not showing
**Solution**: Check user type in database and refresh page

---

## ✅ Backend Test Coverage

```bash
cd apiserver
php artisan test
```

**Current Status**: 47/75 passing (63%)

**Passing Tests**:
- ✅ OTP Manager: 27/27
- ✅ User Model: 33/33
- ✅ Registration: 10/12
- ✅ Login: 10/20
- ⚠️ Password Reset: Needs type field fixes

---

## 🎨 UI/UX Checklist

### Auth Pages
- [x] Gradient backgrounds (blue→indigo→purple)
- [x] Floating animated orbs
- [x] Glassmorphism cards
- [x] Split-screen layout (desktop)
- [x] Feature showcases
- [x] Mobile responsive
- [x] Dark mode support

### Dashboard
- [x] Gradient background with orbs
- [x] Glassmorphism sidebar
- [x] User profile in sidebar
- [x] Type-based navigation
- [x] Stat cards with gradients
- [x] Icon boxes with gradients
- [x] Custom scrollbar
- [x] Dark mode support

### Components
- [x] User dropdown with stats
- [x] Topbar with search/notifications
- [x] Theme toggle
- [x] Gradient buttons
- [x] Form inputs with icons
- [x] Toast notifications
- [x] Loading states
- [x] Error handling

---

## 📱 Mobile Testing

Test on mobile by accessing:
- http://localhost:3000 from mobile device on same network
- Or use browser dev tools mobile emulation

**Mobile Features**:
- Stacked auth layout
- Hamburger menu
- Bottom navigation (to be implemented)
- Touch-friendly buttons
- Responsive stat cards

---

## 🔍 Browser DevTools Checklist

### Console
- [ ] No JavaScript errors
- [ ] No CORS errors
- [ ] API calls returning 200

### Network Tab
- [ ] `/api/auth/login` returns token
- [ ] `/api/user` returns user data
- [ ] Authorization header present

### Application Tab (Cookies)
- [ ] `commerinity_auth_token` cookie exists
- [ ] Token value matches API response

### Lighthouse Score
- [ ] Performance > 90
- [ ] Accessibility > 90
- [ ] Best Practices > 90
- [ ] SEO > 90

---

## 🚀 Next Steps After Testing

1. **Complete Password Reset** - Add forgot password pages
2. **Profile Management** - Add profile edit functionality
3. **E-Commerce** - Add product catalog
4. **MLM Features** - Add team management
5. **Mobile App** - React Native/Flutter integration
