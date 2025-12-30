# Architecture Design
## Enterprise-Grade Affiliate + E-commerce Platform

---

## 🎯 **System Overview**

**Type**: Decoupled full-stack application
**Backend**: Laravel 12 REST API + Filament 4 Admin
**Frontend**: Nuxt 4 SPA with SSR
**Communication**: Laravel Sanctum (cookie-based auth)

---

## 🏗️ **Architecture Pattern**

### **Layered Architecture** (Clean, Standard Laravel)

```
┌─────────────────────────────────────────────┐
│         FRONTEND (Nuxt 4 + Nuxt UI)         │
│   Pages → Components → Composables → API    │
└─────────────────────────────────────────────┘
                    ↓ HTTP/JSON ↓
┌─────────────────────────────────────────────┐
│              API LAYER (Routes)              │
│         Controllers → Requests               │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│         SERVICE LAYER (Business Logic)       │
│   Services → Actions → Policies              │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│         DATA LAYER (Eloquent Models)         │
│   Models → Relationships → Observers         │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│            DATABASE (MySQL)                  │
└─────────────────────────────────────────────┘
```

---

## 📁 **Backend Structure**

### **Standard Laravel with Feature Grouping**

```
apiserver/
└── app/
    ├── Models/
    │   ├── Catalogue/          # Products, Categories, Filters
    │   ├── Order/              # Orders, OrderItems, Invoices
    │   ├── Cart/               # Cart, CartItems
    │   ├── Payment/            # Payments, Transactions
    │   ├── Wallet/             # Wallet, WalletTransactions
    │   ├── Commission/         # Commissions (Affiliate)
    │   ├── Membership/         # Stages, Levels, Subscriptions
    │   ├── Shipping/           # Shipments
    │   ├── Content/            # Posts, Pages
    │   ├── Support/            # Tickets, FAQ
    │   ├── Recruitment/        # Jobs, Applications
    │   ├── User/               # Users, Customers, Admins
    │   └── Shared/             # Address, Media
    │
    ├── Services/
    │   ├── Catalogue/          # ProductService, StockService
    │   ├── Cart/               # CartService
    │   ├── Order/              # OrderService, InvoiceService
    │   ├── Payment/            # PaymentService + Providers
    │   ├── Wallet/             # WalletService
    │   ├── Commission/         # CommissionService, Calculator
    │   ├── Membership/         # MembershipService
    │   ├── Shipping/           # ShippingService + Providers
    │   └── Shared/             # MoneyService, GeoService
    │
    ├── Actions/
    │   ├── Cart/               # AddToCartAction, etc.
    │   ├── Order/              # CreateOrderAction, etc.
    │   ├── Commission/         # CalculateCommissionAction
    │   └── Wallet/             # TransferAction, WithdrawAction
    │
    ├── Enums/
    │   ├── Catalogue/          # ProductStatus, ProductType
    │   ├── Order/              # OrderStatus, PaymentStatus
    │   ├── Commission/         # CommissionType
    │   └── Shared/             # GstTaxRate, Currency
    │
    ├── Http/
    │   ├── Controllers/Api/
    │   │   ├── Catalogue/
    │   │   ├── Cart/
    │   │   ├── Order/
    │   │   ├── Payment/
    │   │   ├── Wallet/
    │   │   └── User/
    │   │
    │   ├── Requests/           # Form validation
    │   │   ├── Cart/
    │   │   ├── Order/
    │   │   └── Product/
    │   │
    │   └── Resources/          # API transformers
    │       ├── Catalogue/
    │       ├── Cart/
    │       └── Order/
    │
    ├── Filament/
    │   └── Resources/
    │       ├── Catalogue/
    │       │   ├── ProductResource.php
    │       │   └── Pages/
    │       │       ├── ManageVariants.php
    │       │       └── ManageProductStocks.php
    │       ├── Order/
    │       └── User/
    │
    ├── Observers/              # Model lifecycle hooks
    ├── Policies/               # Authorization
    ├── Scopes/                 # Query scopes
    ├── Casts/                  # Attribute casts
    ├── Traits/                 # Reusable behaviors
    └── Support/                # Helpers
```

---

## 🎨 **Frontend Structure**

```
client/
└── app/
    ├── pages/                  # File-based routing
    │   ├── index.vue           # Home
    │   ├── shop/               # E-commerce
    │   ├── dashboard/          # User dashboard
    │   └── admin/              # Admin pages (optional)
    │
    ├── components/
    │   ├── ui/                 # Base components (Nuxt UI wrapped)
    │   ├── catalogue/          # Product cards, filters
    │   ├── cart/               # Cart components
    │   ├── order/              # Order tracking
    │   ├── dashboard/          # Dashboard widgets
    │   └── affiliate/                # Genealogy tree, commission charts
    │
    ├── composables/
    │   ├── useCart.ts          # Cart state
    │   ├── useAuth.ts          # Auth helpers (wraps useSanctum)
    │   ├── useWallet.ts        # Wallet state
    │   └── useToast.ts         # Notifications
    │
    ├── layouts/
    │   ├── default.vue         # Main layout
    │   ├── dashboard.vue       # Dashboard layout
    │   └── auth.vue            # Auth pages layout
    │
    ├── middleware/
    │   ├── auth.ts             # Auth guard
    │   └── guest.ts            # Guest only
    │
    └── assets/
        └── css/
            └── main.css        # Tailwind + custom utilities
```

---

## 🔑 **Key Architectural Principles**

### 1. **Separation of Concerns**
- Models: Data structure only
- Services: Business logic
- Actions: Single operations
- Controllers: HTTP handling only
- Resources: Response transformation

### 2. **Single Responsibility**
- Each class does ONE thing well
- No god objects (fat models/controllers)
- Extract complex logic to services

### 3. **Dependency Injection**
```php
class OrderService
{
    public function __construct(
        protected CartService $cartService,
        protected CommissionService $commissionService,
        protected MoneyService $moneyService,
    ) {}
}
```

### 4. **Interface-Based Providers**
```php
interface PaymentProviderInterface
{
    public function createOrder(Order $order): array;
    public function verifyPayment(Payment $payment, array $data): bool;
}

// Implementations
- RazorpayProvider
- CodProvider
- WalletProvider
```

### 5. **Type Safety**
- PHP 8.3+ type hints everywhere
- Enums for constants
- Strict types enabled
- Return type declarations

### 6. **Money Precision**
- ALL prices stored as integers (paise)
- MoneyPHP for calculations
- ZERO float arithmetic
- API returns: `{ paise: 49900, rupees: "499.00", formatted: "₹499.00" }`

---

## 🔐 **Authentication & Authorization**

### Backend (Laravel Sanctum)
```php
// API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/orders', [OrderController::class, 'index']);
});

// Policies
Gate::define('update-order', [OrderPolicy::class, 'update']);
```

### Frontend (Nuxt + Sanctum)
```typescript
// @qirolab/nuxt-sanctum-authentication
const { user, isLoggedIn, login, logout } = useSanctum()

// Middleware
definePageMeta({
  middleware: 'auth'
})
```

---

## 💾 **Data Flow Example: Product Purchase**

```
1. Frontend: User clicks "Buy Now"
   ↓
2. Nuxt: useSanctumFetch('/api/cart/add', { product_id, quantity })
   ↓
3. Laravel Route: POST /api/cart/add
   ↓
4. CartController: Validates request (AddToCartRequest)
   ↓
5. CartService: Executes business logic
   - Check stock availability (StockService)
   - Calculate price (MoneyService)
   - Add to cart
   ↓
6. Response: CartResource (API transformer)
   ↓
7. Frontend: Update cart state, show toast
```

---

## 🎯 **Commission Calculation Flow**

```
Order Completed
   ↓
CommissionService->calculate(Order)
   ↓
1. Get order items with products
2. For each product:
   - Get commission rates
   - Find user's upline (parent, grandparent, etc.)
   - Calculate affiliate commission (direct parent)
   - Calculate team commissions (depth-based)
   - Create commission records
   ↓
3. Credit to wallets (WalletService)
   ↓
4. Send notifications
   ↓
5. Return commission records
```

---

## 🚀 **Technology Choices**

### External Packages (Battle-Tested)
- **moneyphp/money** - Currency precision
- **filament/filament** - Admin panel
- **staudenmeir/laravel-adjacency-list** - Affiliate tree
- **awcodes/filament-curator** - Media management
- **awcodes/filament-tiptap-editor** - Rich text
- **barryvdh/laravel-dompdf** - PDF generation
- **@qirolab/nuxt-sanctum-authentication** - Nuxt auth

### Internal Implementation
- Cart logic
- Order processing
- Commission calculation
- Wallet operations
- Stock management

---

## 📊 **Performance Considerations**

### Query Optimization
- Eager loading (prevent N+1)
- Query scopes (reusable)
- Computed columns (stock)
- Database indexes

### Caching Strategy
- Redis for frequently accessed data
- Cache categories, products, config
- Cache invalidation on updates

### Queue Jobs
- Commission calculations
- Email notifications
- PDF generation
- Report generation

---

## 🔒 **Security Architecture**

### Input Validation
- Form Requests for all inputs
- Enum validation
- Type hints everywhere

### SQL Injection
- Eloquent ORM (parameterized queries)
- No raw queries without bindings

### XSS Prevention
- Vue auto-escapes output
- API returns JSON only

### CSRF Protection
- Sanctum CSRF cookies
- SameSite cookies

### Rate Limiting
- 60 requests/minute (general)
- 5 attempts/minute (login)
- 3 attempts/hour (password reset)

---

## 📈 **Scalability Plan**

### Database
- Indexes on all foreign keys
- Partitioning for large tables (future)
- Read replicas (future)

### Application
- Horizontal scaling (stateless API)
- Queue workers (multiple instances)
- Redis cluster (caching + sessions)

### Frontend
- CDN for static assets
- Image optimization
- Code splitting
- Lazy loading

---

**Status**: ✅ Architecture finalized
**Next**: Database schema design
