# Custom Packages - Old Commerinity

## Package Architecture

**Location**: `backend/packages/mintreu/`

**Total Packages**: 11 custom packages

**Development Setup**: Symlinked for local development

**Pattern**: Package-driven development for reusability across projects

## Package List

### 1. mintreu/toolkit
**Purpose**: Core utilities and shared functionality

**Key Features**:
- Unique ID generation (multiple strategies)
- Publishable status enum with Filament integration
- Factory resolution (dynamic factory loading)
- Common traits and helpers

**Classes**:
- `UniqueIdGenerator` - Multiple ID generation strategies
- `PublishableStatus` enum - Draft/Published/Archived with colors/icons
- `FactoryResolver` - Dynamic model factory resolution

**Usage**:
```php
use Mintreu\Toolkit\Traits\HasUnique;

class Model {
    use HasUnique;
    // Auto-generates UUID and custom codes
}
```

---

### 2. mintreu/laravel-money
**Purpose**: Precise monetary value handling

**Key Features**:
- Integer storage (cents) for precision
- LaravelMoney class for calculations
- LaravelMoneyCast for Eloquent
- Currency support
- Formatting helpers

**CRITICAL BUG**:
- `LaravelMoneyCast::set()` has conversion commented out
- Causes float storage → precision errors

**Classes**:
- `LaravelMoney` - Money value object
- `LaravelMoneyCast` - Eloquent attribute cast

**Usage**:
```php
class Product {
    protected function casts(): array {
        return [
            'price' => LaravelMoneyCast::class,
        ];
    }
}

// Access
$product->price; // LaravelMoney object
$product->price->amount; // Integer (cents)
$product->price->formatted(); // "$10.99"
```

---

### 3. mintreu/laravel-category
**Purpose**: Product categorization system

**Key Features**:
- Hierarchical categories (adjacency list)
- Category tree management
- Sluggable URLs
- Filament resource included

**Models**:
- `Category` - Main category model

**Traits**:
- `HasCategory` - Attach to categorizable models

**Usage**:
```php
use Mintreu\LaravelCategory\Traits\HasCategory;

class Product {
    use HasCategory;
}

// Usage
$product->category; // Belongs to category
$category->children; // Sub-categories
$category->ancestors; // Parent chain
```

---

### 4. mintreu/laravel-geokit
**Purpose**: Location and address management

**Key Features**:
- Address model
- Country, State, City (Block) hierarchy
- Geocoding support (planned)
- Multiple addresses per entity

**Models**:
- `Address`
- `Country`
- `State`
- `Block` (City)

**Traits**:
- `HasAddress` - Attach to addressable models (User, Order)

**Usage**:
```php
use Mintreu\LaravelGeokit\Traits\HasAddress;

class User {
    use HasAddress;
}

// Usage
$user->addresses; // Has many addresses
$user->primaryAddress; // Default address
$address->country->name; // "India"
```

---

### 5. mintreu/laravel-product-catalogue
**Purpose**: Comprehensive product management

**Key Features**:
- Multiple product types (simple, wholesale, configurable, downloadable, proxy)
- Product variants (parent-child hierarchy)
- Filters & filter options (color, size, etc.)
- Filter groups (variant configurations)
- Product tiers (bulk pricing)
- Product suppliers
- Tax configuration
- Media gallery integration

**Models**:
- `Product` - Main product model
- `Filter` - Filter definitions
- `FilterOption` - Filter values
- `FilterGroup` - Variant grouping
- `ProductTier` - Quantity-based pricing
- `ProductSupplier` - Supplier management

**Traits**:
- `HasProduct` - Basic product relationships
- `HasVariants` - Variant management
- `HasFilters` - Filter system

**Key Features**:
- SKU auto-generation (NOTE: potential issues with uniqueness)
- Hierarchical products (parent → variants)
- Polymorphic tenant support (multi-tenancy)
- Tax inclusive/exclusive/exempt handling

**Usage**:
```php
// Simple product
$product = Product::create([
    'name' => 'Widget',
    'sku' => 'WID-001',
    'price' => 1999, // $19.99 in cents
    'type' => 'simple',
]);

// Configurable with variants
$parent = Product::create(['type' => 'configurable', ...]);
$variant = Product::create([
    'parent_id' => $parent->id,
    'sku' => 'WID-RED-S',
    ...
]);
$variant->filterOptions()->attach($redOption, $smallOption);

// Bulk pricing
ProductTier::create([
    'product_id' => $product->id,
    'min_quantity' => 10,
    'max_quantity' => 50,
    'price' => 1799, // $17.99 each for 10-50 units
]);
```

---

### 6. mintreu/laravel-commerinity
**Purpose**: E-commerce core functionality

**Key Features**:
- Shopping cart (guest + authenticated)
- Vouchers & voucher codes
- Sales campaigns
- Order management (implied, or in app)
- Cart merging

**Models**:
- `Cart`
- `Voucher`
- `VoucherCode`
- `Sale`
- `SaleProduct` (pivot)

**Traits**:
- `HasCartOwner` - Polymorphic cart ownership
- `HasCartable` - Products in cart
- `HasVoucherAccess` - Voucher usage

**Key Features**:
- Guest cart via UUID
- Voucher validation (usage limits, dates, min order value)
- Sales with product associations
- Cart calculations (subtotal, discount, tax, total)

**Usage**:
```php
use Mintreu\LaravelCommerinity\Traits\HasCartOwner;

class User {
    use HasCartOwner;
}

// Cart operations
$cart = Cart::forOwner($user);
$cart->add($product, $quantity);
$cart->remove($product);
$cart->applyVoucher($voucherCode);
$cart->total(); // Calculate total
```

---

### 7. mintreu/laravel-transaction
**Purpose**: Payment gateway integration

**Key Features**:
- Multi-gateway support (Razorpay, Cashfree, Paytm)
- Transaction tracking
- Webhook handling
- Payment status management
- Razorpay Payouts integration

**Models**:
- `Transaction`

**Services**:
- `RazorpayService`
- `CashfreeService`
- `PaytmService`
- `PayoutService`

**Key Features**:
- Gateway abstraction layer
- Webhook signature verification
- Transaction state machine
- Polymorphic transactable (Order, WalletTopUp, etc.)

**Usage**:
```php
use Mintreu\LaravelTransaction\Services\RazorpayService;

// Create payment
$razorpay = new RazorpayService();
$order = $razorpay->createOrder($amount, $currency, $receiptId);

// Verify payment
$transaction = Transaction::findByUuid($uuid);
$razorpay->verifyPayment($transaction, $paymentId, $signature);

// Payout
$payout = $razorpay->createPayout($beneficiary, $amount);
```

**Configuration**:
```php
// config/laravel-transaction.php
return [
    'default_gateway' => 'razorpay',
    'gateways' => [
        'razorpay' => [
            'key' => env('RAZORPAY_KEY'),
            'secret' => env('RAZORPAY_SECRET'),
            'webhook_secret' => env('RAZORPAY_WEBHOOK'),
        ],
    ],
];
```

---

### 8. mintreu/laravel-recruitment
**Purpose**: Job posting and application system

**Key Features**:
- Job postings management
- Application submissions
- Resume uploads
- Application tracking

**Models**:
- `Recruitment` (Job posting)
- `JobApplication`

**Traits**:
- `HasJobApplications` - User applications

**Usage**:
```php
use Mintreu\LaravelRecruitment\Traits\HasJobApplications;

class User {
    use HasJobApplications;
}

// User applies for job
$user->jobApplications()->create([
    'recruitment_id' => $job->id,
    'cover_letter' => '...',
    'resume' => $resumeFile,
]);
```

---

### 9. mintreu/laravel-helpdesk
**Purpose**: Support ticket system

**Key Features**:
- Ticket management
- Topic/category system
- Conversation threads
- File attachments
- FAQ management

**Models**:
- `Helpdesk` (Ticket)
- `HelpdeskTopic` (Category)
- `HelpdeskConversation` (Messages)
- `HelpdeskFaq`

**Traits**:
- `HasSupportTicket` - User tickets

**Usage**:
```php
use Mintreu\LaravelHelpdesk\Traits\HasSupportTicket;

class User {
    use HasSupportTicket;
}

// Create ticket
$ticket = $user->supportTickets()->create([
    'topic_id' => $topic->id,
    'subject' => 'Issue with order',
    'description' => '...',
    'priority' => 'high',
]);

// Add reply
$ticket->conversations()->create([
    'user_id' => $admin->id,
    'message' => 'Response...',
]);
```

---

### 10. mintreu/laravel-penpress
**Purpose**: Blog and content management

**Key Features**:
- Blog post management
- Categories
- Polymorphic authors
- Rich text content
- SEO optimization

**Models**:
- `Post`
- `PostCategory`

**Key Features**:
- Sluggable URLs
- Status workflow (draft/published/archived)
- Scheduled publishing
- Author attribution (User/Admin/Staff)

**Usage**:
```php
$post = Post::create([
    'name' => 'Blog Title',
    'url' => 'blog-title',
    'description' => 'Rich text content...',
    'author_type' => User::class,
    'author_id' => $user->id,
    'category_id' => $category->id,
    'status' => 'published',
    'published_at' => now(),
]);
```

---

### 11. mintreu/laravel-integration
**Purpose**: Third-party service integrations

**Key Features**:
- SMS (Fast2SMS)
- Shipping (Shiprocket)
- Social login (partial, mainly via Socialite)
- Integration configuration storage

**Models**:
- `Integration` - Service configurations

**Services**:
- `Fast2SMSService` - SMS sending
- `ShiprocketService` - Shipping management

**Usage**:
```php
use Mintreu\LaravelIntegration\Services\Fast2SMSService;

// Send SMS
$sms = new Fast2SMSService();
$sms->send($mobile, $message);

// Shiprocket
$shiprocket = new ShiprocketService();
$shipment = $shiprocket->createOrder($orderDetails);
$tracking = $shiprocket->trackShipment($awb);
```

---

## Common Package Patterns

### 1. Trait-Based Composition
All packages provide traits for easy integration:
```php
use Mintreu\PackageName\Traits\HasFeature;

class Model {
    use HasFeature;
}
```

### 2. Filament Integration
Most packages include Filament resources for admin management

### 3. Configuration Files
Packages publish configuration files to `config/` directory

### 4. Migration Publishing
Packages include migrations that can be published

### 5. Factory Resolution
Toolkit provides dynamic factory resolution for testing

## Package Issues Identified

### Critical Issues

1. **mintreu/laravel-money**:
   - Money cast conversion commented out
   - Causes financial precision errors
   - **Priority**: CRITICAL

2. **Lack of Tests**:
   - Most packages have no automated tests
   - High risk for regressions
   - **Priority**: CRITICAL

3. **Documentation**:
   - Package READMEs are templates
   - No usage examples
   - No API documentation
   - **Priority**: HIGH

### Medium Issues

4. **mintreu/laravel-product-catalogue**:
   - SKU generation may not be unique
   - Potential for overly long SKUs
   - **Priority**: MEDIUM

5. **Package Dependencies**:
   - Inter-package dependencies not clearly documented
   - Coupling between packages unclear
   - **Priority**: MEDIUM

6. **Version Management**:
   - No clear versioning strategy
   - Breaking changes not documented
   - **Priority**: MEDIUM

## Recommendations for Refactoring

### 1. Fix Critical Bugs
- Uncomment money cast conversion
- Add validation for SKU uniqueness
- Implement proper error handling

### 2. Add Comprehensive Tests
- Unit tests for all services
- Feature tests for API endpoints
- Package-specific test suites
- Minimum 80% code coverage

### 3. Improve Documentation
- Complete package READMEs with examples
- API documentation for each package
- Inter-package dependency map
- Migration guides between versions

### 4. Standardize Patterns
- Consistent trait naming
- Uniform service class patterns
- Standard configuration structure
- Common exception handling

### 5. Version Management
- Semantic versioning for all packages
- CHANGELOG.md for each package
- Deprecation notices for breaking changes
- Migration guides

### 6. Reduce Coupling
- Review inter-package dependencies
- Extract common functionality to toolkit
- Use interfaces for loose coupling
- Dependency injection over facades

### 7. Add Package Tests to CI/CD
- Automated testing on each commit
- Code coverage reports
- PHPStan/Larastan static analysis
- Laravel Pint code style enforcement

### 8. Consider Publishing to Packagist
- If packages are stable and well-tested
- Allows version management via Composer
- Facilitates reuse across projects
- Community contributions possible

## Package Usage in Main Application

### Heavy Users
- **User model**: Uses 8+ package traits
- **Product model**: Extends package model
- **Order system**: Integrates cart, transaction, product packages

### Integration Points
- Cart system: commerinity + product-catalogue
- Payment flow: transaction + commerinity (orders)
- MLM system: Custom app code + toolkit utilities
- Support system: helpdesk package
- Content: penpress package
- Location: geokit package

### Package Configuration
All packages configured via published config files in `config/` directory
