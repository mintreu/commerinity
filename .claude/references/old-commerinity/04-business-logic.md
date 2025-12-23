# Business Logic & Features - Old Commerinity

## Core Business Models

### 1. Multi-Level Marketing (MLM) System

#### Referral & Recruitment
**Flow**:
```
1. User A registers with unique referral_code
2. User A shares referral link with potential recruits
3. User B registers using User A's referral_code
4. User B's parent_id = User A's user_id
5. User A becomes User B's originator
6. MLM tree structure established
```

**Key Points**:
- Referral codes: 8-character uppercase alphanumeric
- Hierarchical tree structure (adjacency list pattern)
- Supports binary, unilevel, or hybrid structures
- Tree visualization using D3.js on frontend

#### Lifecycle Progression System
**Concept**: Users progress through membership stages and levels

**Stages** (Examples):
- Bronze
- Silver
- Gold
- Platinum
- Diamond

**Levels within Stages**:
- Each stage has multiple levels (e.g., Bronze Level 1, 2, 3)
- Progression requires completing tasks and meeting team requirements

**Membership Flow**:
```
1. User selects stage/level
2. System calculates price (base_price - discount + tax)
3. User makes payment
4. Subscription created (user_subscriptions)
5. Level tasks assigned
6. User begins task completion
7. Progress tracked in user_level_task_progress
8. All tasks completed → eligible for next level
9. Level upgrade process initiated
10. New benefits unlocked
```

**Task System**:
- Tasks have types (sales target, recruitment, team building)
- Target scores for completion
- Rewards on achievement
- Progress tracking per user per task

#### Commission System (Incentives)

**Three Types** (Single Table Inheritance):

1. **Affiliate Incentives** (Direct Referral)
   - Triggered: When direct referree makes purchase
   - Calculation: Percentage of order value
   - Recipient: Direct upline (parent)
   - Example: User B buys product → User A gets 10% commission

2. **Team Incentives** (Downline Performance)
   - Triggered: When anyone in downline makes purchase
   - Calculation: Based on level depth and order value
   - Recipients: All upline members (with decreasing percentages)
   - Example:
     - Level 1 (direct): 5%
     - Level 2: 3%
     - Level 3: 2%

3. **Business Incentives** (Volume-Based)
   - Triggered: Monthly/quarterly volume targets met
   - Calculation: Based on total team sales volume
   - Criteria: Minimum team size, minimum volume
   - Example: Team reaches $10K volume → Leader gets $500 bonus

**Commission Flow**:
```
1. Order completed (status = completed)
2. Commission calculation service triggered
3. Calculate affiliate incentive (direct referrer)
4. Calculate team incentives (all upline)
5. Calculate business incentives (volume-based)
6. Create incentive records (status = pending)
7. Credit amounts to user wallets
8. Update transaction records
9. Mark incentives as paid
10. Notify recipients
```

**Payout Process**:
```
1. User has pending incentives in wallet
2. User adds beneficiary account (bank/UPI)
3. User requests withdrawal
4. KYC verification checked
5. Payout initiated via Razorpay Payouts
6. Transaction recorded
7. Wallet balance deducted
8. User notified of payout status
```

### 2. E-commerce System

#### Product Management

**Product Types**:
1. **Simple Product**: Single SKU, fixed price
2. **Wholesale Product**: Bulk pricing tiers
3. **Configurable Product**: Parent with variants (color, size)
4. **Downloadable Product**: Digital goods
5. **Proxy Product**: External product integration

**Variant System**:
```
Parent Product (T-Shirt)
├── Variant 1 (Red, Small) - SKU: TSHIRT-RED-S
├── Variant 2 (Red, Large) - SKU: TSHIRT-RED-L
└── Variant 3 (Blue, Small) - SKU: TSHIRT-BLUE-S

Implementation:
- Parent: parent_id = null
- Variants: parent_id = parent_product_id
- Filter options: product_filter_options pivot table
```

**Pricing Tiers** (Bulk Pricing):
```
Product: Widget
- 1-10 units: $10 each
- 11-50 units: $9 each
- 51+ units: $8 each

Table: product_tiers
- product_id, min_quantity, max_quantity, price
```

**Tax Handling**:
- Tax inclusive vs. exclusive
- Tax exempted products
- Tax slabs (5%, 12%, 18%, 28% GST)
- Calculated at cart level

#### Shopping Cart System

**Guest Cart**:
```
1. Guest visits site
2. Frontend requests guest UUID (POST /api/cart/guest-credential)
3. UUID stored in localStorage
4. Cart operations use guest UUID
5. Cart persists across sessions (same device)
```

**Authenticated Cart**:
```
1. User logs in
2. Cart associated with user_id
3. Persists across devices
4. On login from device with guest cart:
   - Merge guest cart to user cart (POST /api/cart/merge)
   - Combine quantities for duplicate items
   - Remove guest cart
```

**Cart Operations**:
- Add product (with quantity, tier, variant options)
- Update quantity
- Remove product
- Apply voucher code
- Calculate totals (subtotal, discount, tax, shipping, total)

**Voucher Application**:
```
1. User enters voucher code
2. Validate voucher:
   - Code exists and active
   - Usage limit not exceeded
   - User hasn't used (if single-use)
   - Valid date range
   - Minimum order value met
3. Calculate discount (percentage or fixed)
4. Apply to cart
5. Store voucher_code in cart/order
```

#### Checkout & Order Processing

**Checkout Flow**:
```
1. User reviews cart
2. Select/add billing address
3. Select/add shipping address (or same as billing)
4. Choose payment method:
   - Online payment (Razorpay/Cashfree/Paytm)
   - Wallet balance
   - COD (if enabled)
5. Review order summary
6. Place order (POST /api/orders/order/place)
7. Order created (status = pending)
8. Payment gateway redirect
9. User completes payment
10. Webhook received → Order updated (status = processing)
11. Invoice generated
12. Admin processes order
13. Shipment created
14. Tracking details added
15. Order status = completed
16. Commission calculation triggered
```

**Order States**:
- `pending` - Order placed, awaiting payment
- `processing` - Payment received, being prepared
- `completed` - Order fulfilled
- `cancelled` - Order cancelled
- `returned` - Return requested/processed
- `refunded` - Refund issued

**Guest Checkout**:
- Allowed (customer_type = Guest)
- Email provided for order updates
- Address saved temporarily
- Can create account later with order lookup

#### Shipping Integration

**Shiprocket Integration**:
```
1. Order ready to ship
2. Admin creates shipment in Filament
3. API call to Shiprocket:
   - Create order
   - Generate AWB (tracking number)
   - Generate shipping label
4. Shipment record created:
   - tracking_number
   - shipping_provider = 'shiprocket'
   - dimensions, weight
5. Tracking updates via webhook:
   - shipment_activities (JSON)
6. Customer can track order
7. Delivery confirmation → Order completed
```

**Shipment States**:
- Booked
- Picked up
- In transit
- Out for delivery
- Delivered
- RTO (Return to Origin)

### 3. Financial System

#### Digital Wallet

**Wallet Creation**:
```
1. User registers
2. Wallet auto-created on first access
3. Initial balance = 0
4. PIN set by user (encrypted)
5. UUID generated for QR code
```

**Wallet Operations**:

1. **Add Money**:
   ```
   1. User initiates top-up
   2. Amount entered
   3. Payment gateway redirect
   4. Payment success
   5. Wallet credited
   6. Transaction recorded
   ```

2. **Withdraw to Bank**:
   ```
   1. User has balance
   2. Beneficiary account added (if not exists)
   3. KYC verified (required)
   4. Withdrawal amount entered
   5. PIN verification
   6. Payout API called (Razorpay Payouts)
   7. Wallet debited
   8. Transaction recorded
   9. Payout status tracked
   ```

3. **P2P Transfer**:
   ```
   1. User A initiates send
   2. User B identified (mobile/email/wallet ID)
   3. Amount entered
   4. PIN verification
   5. User A wallet debited
   6. User B wallet credited
   7. Both notified
   8. Transactions recorded
   ```

4. **Point Conversion**:
   ```
   1. User earns points (purchases, referrals, tasks)
   2. Conversion rate defined (e.g., 100 points = $1)
   3. User requests conversion
   4. Points deducted
   5. Wallet credited
   6. Transaction recorded
   ```

**Wallet as Payment Method**:
- During checkout, user can pay via wallet
- If balance insufficient:
  - Pay partial via wallet
  - Remaining via payment gateway
- Wallet debited on order placement

#### Payment Gateway Integration

**Supported Gateways**:
1. Razorpay (Primary)
2. Cashfree
3. Paytm

**Payment Flow**:
```
1. User places order
2. Transaction record created (status = pending)
3. Gateway checkout page rendered
4. User completes payment
5. Two verification methods:
   a) Success redirect → /api/_transaction/validate/{uuid}
   b) Webhook → /api/webhooks/razorpay (verified via signature)
6. Transaction updated (status = success)
7. Order updated (payment_status = paid)
8. Order processing begins
9. Commission calculation triggered
```

**Webhook Handling**:
```
1. Gateway sends webhook POST
2. Verify signature (gateway secret)
3. Extract transaction ID, status
4. Find transaction by gateway_transaction_id
5. Update transaction and order
6. Return 200 OK to gateway
7. Trigger post-payment actions
```

**Payment Failure**:
```
1. User redirected to failure URL
2. Transaction marked as failed
3. Order remains pending
4. User can retry payment
5. After expiry time, order auto-cancelled
```

#### KYC Verification

**Required For**:
- Wallet withdrawals
- High-value orders
- Commission payouts
- Becoming distributor

**KYC Process**:
```
1. User submits KYC:
   - Company details (if applicable)
   - PAN number
   - Aadhaar number
   - GST number (if applicable)
   - Supporting documents (uploaded via media library)
2. KYC record created (status = pending)
3. Admin reviews in Filament
4. Verification:
   - Check document validity
   - Match details
5. Approve or reject with reason
6. User notified
7. KYC status updated
8. User can proceed with restricted actions
```

### 4. Content Management

#### Blog System

**Post Management**:
- Polymorphic authors (User, Admin, Staff)
- Categories
- Rich text content (TipTap editor)
- Featured images via media library
- SEO-friendly URLs
- Status workflow (draft → published)
- Scheduled publishing (published_at)

**Frontend Display**:
- Blog listing with pagination
- Category filtering
- Search functionality
- Post detail with author info
- Related posts
- Social sharing

#### CMS Pages

**Static Pages**:
- About Us
- Privacy Policy
- Terms of Service
- Return & Refund Policy
- Shipping Policy
- Contact

**Management**:
- Filament admin panel
- Rich text editor
- Custom meta tags for SEO
- URL customization

### 5. Product Discovery & Engagement

#### Search System

**Global Search**:
- Products (by name, SKU, description)
- Posts (by title, content)
- Categories
- Faceted search results

**Filters**:
- Price range
- Categories
- Product attributes (color, size, etc.)
- Brands (if implemented)
- Availability

#### Product Reviews & Ratings

**Review System**:
```
1. User purchases product
2. Order completed
3. User can submit review:
   - Star rating (1-5)
   - Review text
   - Photos (optional)
4. Review published (status = active)
5. Other users can:
   - Mark as helpful
   - Reply to review (nested comments)
6. Average rating calculated
7. Displayed on product page
```

**Review Moderation**:
- Admin can approve/reject
- Flag inappropriate content
- Delete spam reviews

#### Wishlist

**Functionality**:
- Add/remove products
- Persistent across devices (auth users)
- Quick add to cart from wishlist
- Price drop notifications (if implemented)

### 6. Support & Help Desk

**Ticket System**:
```
1. User creates ticket:
   - Select topic (category)
   - Subject, description
   - Attachments (optional)
2. Ticket created (status = open, uuid generated)
3. Admin receives notification
4. Admin views ticket in Filament
5. Admin responds (conversation thread)
6. User receives notification
7. User can reply
8. Back-and-forth until resolution
9. Admin marks as resolved
10. User can reopen if needed
11. Auto-close after inactivity period
```

**Priority Levels**:
- Low
- Medium
- High
- Urgent

**FAQ Management**:
- Topic-based FAQs
- Search functionality
- Reduce ticket volume

### 7. Recruitment Module

**Job Posting Flow**:
```
1. Admin creates job posting:
   - Title, description
   - Requirements
   - Location
   - Salary range
   - Application deadline
2. Published to careers page
3. Users browse postings
4. User applies:
   - Cover letter
   - Resume upload (PDF)
5. Application submitted
6. Admin reviews in Filament
7. Shortlist or reject
8. Email sent to applicant
9. Schedule interview (manual)
```

### 8. Notifications System

**Channels**:
1. Database - In-app notifications
2. Email - via configured mail driver
3. Web Push - VAPID protocol

**Notification Types**:
- Order updates
- Payment confirmations
- Commission earnings
- Level upgrades
- Task completions
- Ticket responses
- Marketing announcements

**Web Push Flow**:
```
1. User visits site
2. Prompt for notification permission
3. User accepts
4. Frontend sends subscription object to backend
5. Stored in push_subscriptions table
6. Admin can send:
   - To specific user
   - To all users
   - To membership level
7. Push notification delivered via VAPID
```

## Business Rules & Constraints

### Order Processing Rules
1. Minimum order value (if defined)
2. Maximum quantity per product
3. Stock availability check
4. Address validation (pincode serviceable)
5. Payment timeout (30 minutes default)
6. Auto-cancel if payment not completed

### Commission Rules
1. Commission calculated only on completed orders
2. Returns/refunds deduct commissions
3. Minimum payout threshold
4. KYC required for payouts
5. Commission caps per level

### Membership Rules
1. One active subscription at a time
2. Can upgrade but not downgrade (configurable)
3. Tasks must be completed for level progression
4. Subscription expiry (monthly/annual)
5. Auto-renewal (if enabled)

### Wallet Rules
1. Minimum withdrawal amount
2. Daily withdrawal limit
3. Transaction fees (if applicable)
4. PIN required for sensitive operations
5. Balance cannot go negative

### Product Rules
1. SKU must be unique
2. Price > 0
3. Variants share category/base attributes
4. At least one image required
5. Tax configuration required

## Critical Business Logic Issues

### 1. Money Precision
**Issue**: Float storage causes rounding errors
**Impact**: Financial discrepancies in orders, commissions
**Solution**: Use integer storage (cents) with `LaravelMoneyCast`

### 2. Commission Recalculation
**Issue**: No mechanism to recalculate commissions on order returns
**Impact**: Overpaid commissions
**Solution**: Implement commission reversal on returns/refunds

### 3. Stock Management
**Issue**: No inventory tracking implemented
**Impact**: Overselling, order fulfillment issues
**Solution**: Add stock quantity field, decrement on order, reserve on cart

### 4. Concurrent Operations
**Issue**: No locking for wallet operations
**Impact**: Race conditions in P2P transfers
**Solution**: Use database transactions with row locking

### 5. Orphaned Records
**Issue**: Soft deletes not used consistently
**Impact**: Data integrity issues on user/product deletion
**Solution**: Implement soft deletes, cascade rules, or prevent deletion

## Recommended Improvements

1. **Add Queue Jobs** for:
   - Commission calculations
   - Email notifications
   - Large data exports
   - Report generation

2. **Implement Caching** for:
   - Product listings
   - Category trees
   - User permissions
   - Configuration values

3. **Add Rate Limiting** for:
   - API endpoints
   - Login attempts
   - Password resets
   - OTP requests

4. **Enhance Security**:
   - Two-factor authentication
   - IP whitelisting for admin panel
   - API request signing
   - Sensitive data encryption

5. **Improve Monitoring**:
   - Failed payment tracking
   - Commission accuracy audits
   - Stock level alerts
   - Performance metrics
