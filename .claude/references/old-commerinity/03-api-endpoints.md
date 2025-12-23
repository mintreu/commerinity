# API Endpoints - Old Commerinity

## Authentication Strategy
- **Laravel Sanctum** - Cookie-based authentication for SPA
- **CSRF Protection** - Enabled for web routes
- **Guest Support** - Cart and order placement available for guests
- **Middleware**: `auth:sanctum` for protected routes

## Base URL
`/api/` - All API routes prefixed

## Authentication & User Management

### Authentication (`/api/`)
```
POST   /register                 - User registration
POST   /login                    - User login
POST   /logout                   - User logout (auth)
POST   /forgot-password          - Request password reset
POST   /reset-password           - Reset password with token
POST   /verify-email             - Email verification
POST   /verify-mobile            - Mobile verification
GET    /user                     - Get authenticated user (auth:sanctum)
```

### Account Management (`/api/account/`)
**All routes protected by auth:sanctum**
```
GET    /profile                  - Get user profile
PUT    /profile                  - Update user profile
POST   /avatar                   - Upload avatar image
GET    /addresses                - List user addresses
POST   /addresses                - Create new address
PUT    /addresses/{id}           - Update address
DELETE /addresses/{id}           - Delete address
```

## Wallet & Financial

### Wallet Operations (`/api/wallet/`)
**All routes protected by auth:sanctum**
```
GET    /wallet                   - Get wallet details
POST   /wallet/create            - Create wallet
GET    /wallet/qr                - Get wallet QR code
POST   /wallet/add-money         - Top up wallet
POST   /wallet/withdraw          - Withdraw to bank
POST   /wallet/send              - P2P wallet transfer
POST   /wallet/change-pin        - Update wallet PIN
POST   /wallet/point-conversion  - Convert points to balance
```

### Beneficiary Accounts (`/api/beneficiaries/`)
**All routes protected by auth:sanctum**
```
GET    /                         - List beneficiary accounts
POST   /                         - Add beneficiary account
GET    /{uuid}                   - Get beneficiary details
PUT    /{uuid}                   - Update beneficiary
DELETE /{uuid}                   - Delete beneficiary
POST   /{uuid}/default           - Set as default account
```

## Product Catalog

### Products (`/api/`)
```
GET    /products                 - List products (pagination, filters)
                                   Query params: category, price_min, price_max, sort
GET    /products/{url}           - Get product details
GET    /products/{url}/variants  - Get product variants
GET    /products/{url}/tiers     - Get bulk pricing tiers
POST   /products                 - Create product (admin only)
PUT    /products/{id}            - Update product (admin only)
```

### Categories (`/api/categories/`)
```
GET    /                         - List all categories
GET    /with-products            - Categories with product counts
GET    /{url}                    - Get category with products
```

### Product Engagement (`/api/product/`)
```
GET    /engagements/{product_url}       - Get product reviews
POST   /engagement/{product_url}        - Submit review (auth)
PUT    /engagement/{id}                 - Update review (auth)
DELETE /engagement/{id}                 - Delete review (auth)
POST   /engagement/{id}/helpful         - Mark review helpful (auth)
```

### Wishlist (`/api/product/`)
**Protected by auth:sanctum**
```
POST   /wishlist/{product_url}   - Add to wishlist
DELETE /wishlist/{product_url}   - Remove from wishlist
GET    /wishlists                - Get user wishlists (implied)
```

## Shopping Cart

### Cart Management (`/api/cart/`)
**Supports both guest and authenticated users**
```
POST   /guest-credential         - Generate guest cart UUID
POST   /validate/guest-credential - Validate guest UUID
GET    /                         - Get cart contents
POST   /add/{sku}                - Add product to cart
                                   Body: { quantity, tier_id?, options? }
POST   /update/{sku}             - Update cart item quantity
DELETE /remove/{sku}             - Remove item from cart
POST   /coupon/{code}            - Apply voucher code
POST   /clear                    - Clear entire cart
POST   /merge                    - Merge guest cart to user cart (auth)
```

**Guest Cart Flow**:
1. Frontend requests guest credential (UUID)
2. UUID stored in localStorage
3. Cart operations use guest UUID
4. On login, merge guest cart to user cart

## Orders & Transactions

### Order Management (`/api/orders/`)
```
POST   /order/place              - Place order (guest/auth)
                                   Body: {
                                     billing_address,
                                     shipping_address,
                                     payment_method,
                                     voucher_code?
                                   }
GET    /order/insight            - Order statistics (auth)
GET    /                         - List user orders (auth)
GET    /{uuid}                   - Get order details (auth)
POST   /{uuid}/canceled          - Cancel order (auth)
POST   /{uuid}/return            - Request return (auth)
POST   /{uuid}/refund            - Request refund (auth)
GET    /{uuid}/invoice           - Download invoice PDF (auth)
```

### Transactions (`/api/transactions/`)
**Protected by auth:sanctum**
```
GET    /                         - List user transactions
GET    /{uuid}                   - Get transaction details
GET    /{uuid}/request_pdf       - Download transaction receipt
```

### Transaction Callbacks (`/api/_transaction/`)
**Public endpoints for payment gateway redirects**
```
GET    /validate/{uuid}          - Payment success callback
GET    /failed/{uuid}            - Payment failure callback
```

### Payment Webhooks (`/api/webhooks/`)
**Public endpoints for gateway webhooks (verified via signature)**
```
POST   /razorpay                 - Razorpay webhook
POST   /cashfree                 - Cashfree webhook
POST   /paytm                    - Paytm webhook
```

## Membership & Lifecycle

### Lifecycle Management (`/api/lifecycle/`)
```
GET    /timeline                 - Get lifecycle progression timeline (auth)
GET    /stages                   - List all membership stages
GET    /stage/{url}              - Get stage details
GET    /level/{url}              - Get level details
GET    /subscribable             - Get available subscriptions (auth)
POST   /subscribe                - Subscribe to stage/level (auth, implied)
```

**Membership Flow**:
1. User views available stages
2. Selects stage/level
3. Proceeds to payment
4. Subscription activated
5. Level tasks assigned

## Sales & Promotions

### Sales Campaigns (`/api/sales/`)
```
GET    /                         - List active sales
GET    /{url}                    - Get sale details (implied)
```

### Flash Deals (`/api/flash-deals/`)
```
GET    /                         - List active flash deals
GET    /stats                    - Flash deal statistics
GET    /categories               - Flash deal categories
```

## Support & Help

### Help Desk (`/api/helpdesk/`)
```
GET    /topics/ticket            - Get ticket topics (categories)
GET    /topics/faq               - Get FAQ categories
GET    /tickets                  - List user tickets (auth)
POST   /tickets                  - Create ticket (auth)
                                   Body: {
                                     topic_id,
                                     subject,
                                     description
                                   }
GET    /tickets/{uuid}           - Get ticket details (auth)
POST   /tickets/{uuid}/reply     - Reply to ticket (auth)
POST   /tickets/{uuid}/attachments - Upload attachment (auth)
```

## Recruitment & Careers

### Job Postings (`/api/recruitment/`)
```
GET    /                         - List job postings
GET    /{url}                    - Get job details
POST   /{url}/apply              - Submit application (auth)
                                   Body: {
                                     cover_letter,
                                     resume (file upload)
                                   }
```

## Content Management

### Pages (`/api/`)
```
GET    /pages                    - List CMS pages
GET    /pages/{url}              - Get page content (implied)
```

### Blog (`/api/blogs/`)
```
GET    /                         - List blog posts
GET    /{url}                    - Get post details
```

## Search & Discovery

### Global Search (`/api/`)
```
GET    /search                   - Global search
                                   Query params: q (query), type (products|posts|all)
```

### Statistics (`/api/stats/`)
**Various analytics endpoints**
```
GET    /stats/dashboard          - Dashboard overview (auth)
GET    /stats/orders             - Order statistics (auth)
GET    /stats/referrals          - Referral statistics (auth)
GET    /stats/team               - Team statistics (auth)
GET    /stats/incentives         - Commission statistics (auth)
```

## Push Notifications

### Web Push (`/api/push/`)
```
GET    /vapid-public-key         - Get VAPID public key for subscription
POST   /subscribe                - Subscribe to push notifications
                                   Body: { subscription object }
POST   /unsubscribe              - Unsubscribe (auth)
POST   /send-to-user             - Send notification to user (admin)
POST   /send-to-all              - Broadcast notification (admin)
POST   /send-to-level            - Send to membership level (admin)
```

## Contact & Inquiries

### Contact Forms (`/api/contact/`)
```
POST   /user                     - User inquiry form
                                   Body: { name, email, mobile, message }
POST   /business                 - Business inquiry form
                                   Body: { company, email, mobile, message }
```

## Location Services

### Geo Data (`/api/geo/`)
```
GET    /countries                - List countries
GET    /states/{country}         - List states in country
GET    /blocks/{state}           - List cities in state
```

## Integrations

### Third-Party Services (`/api/integration/`)
```
GET    /payment                  - Get payment gateway info (minimal)
```

## API Response Format

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

### Pagination Response
```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  },
  "links": {
    "first": "url",
    "last": "url",
    "prev": null,
    "next": "url"
  }
}
```

## API Versioning
**Current**: No explicit versioning (v1 implied)
**Recommendation**: Implement `/api/v1/` prefix for future compatibility

## Rate Limiting
**Current**: Not explicitly implemented
**Recommendation**: Implement throttling middleware

## Authentication Flow

### SPA Authentication (Sanctum)
```
1. GET /sanctum/csrf-cookie       - Get CSRF token
2. POST /api/login                - Login with credentials
3. Subsequent requests include:
   - Cookie: laravel_session
   - Header: X-XSRF-TOKEN
4. POST /api/logout               - Logout
```

### Guest Cart Flow
```
1. POST /api/cart/guest-credential  - Get guest UUID
2. Store UUID in localStorage
3. Include UUID in cart requests
4. On login: POST /api/cart/merge   - Merge to user cart
```

## Security Considerations

1. **CSRF Protection**: Required for state-changing operations
2. **Sanctum Cookies**: HTTPOnly, Secure in production
3. **Input Validation**: Form Request classes
4. **Authorization**: Policies for resource access
5. **Webhook Verification**: Signature validation for payment webhooks
6. **Rate Limiting**: Should be implemented
7. **API Versioning**: Should be implemented

## Missing/Recommended Endpoints

1. **Incentive Details** (`/api/incentives/{uuid}`)
2. **Team Tree** (`/api/team/tree`) - MLM genealogy
3. **Level Progress** (`/api/lifecycle/progress`) - Task completion
4. **Wallet History** (`/api/wallet/transactions`)
5. **KYC Submission** (`/api/kyc/submit`)
6. **KYC Status** (`/api/kyc/status`)
7. **Product Reviews Pagination** (if not implemented)
8. **Order Tracking** (`/api/orders/{uuid}/tracking`)
