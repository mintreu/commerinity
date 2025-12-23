# Database Schema - Old Commerinity

## Overview
- **Total Migrations**: 65+
- **Database Engine**: SQLite (dev), MySQL/PostgreSQL (prod)
- **ORM**: Eloquent with recursive relationships

## Core Tables

### User Management

#### `users`
**Purpose**: Main user table with MLM hierarchy

**Key Columns**:
- `id` (bigint, PK)
- `uuid` (string, unique) - External identifier
- `name`, `email`, `mobile`
- `password`
- `referral_code` (string, unique, 8 chars uppercase)
- `parent_id` (bigint, FK to users) - MLM tree structure
- `originator_type`, `originator_id` - Polymorphic (who recruited)
- `type` - User type enum (regular/premium)
- `status` - Auth status enum (draft/active/suspended)
- `email_verified_at`, `mobile_verified_at`
- `onboarded_at`
- `gender`, `dob`, `bio`
- `level_id` (FK to levels) - Current membership level
- Timestamps

**Relationships**:
- Hierarchical: Self-referencing via `parent_id`
- Polymorphic: `originator` (who created/recruited)
- Belongs-to: `level`
- Has-many: addresses, orders, carts, wallets, KYC, subscriptions, incentives

#### `admins`
Admin users with Filament access

#### `staff`
Staff users (similar to admins)

#### `distributors`
Distribution partners

### Membership & Lifecycle

#### `stages`
**Purpose**: Membership tiers (Bronze, Silver, Gold, etc.)

**Key Columns**:
- `name`, `url`, `description`
- `base_price`, `discount`, `tax` (money fields)
- `team_capacity` - Max team size
- `joining_point_estimation`
- `purchasing_point_estimation`
- `clan_point_estimation`
- `benefits` (JSON) - Membership benefits
- `accessibility` (JSON) - Access permissions
- `status`

#### `levels`
**Purpose**: Sub-levels within stages

**Key Columns**:
- `name`, `price`
- `team_size` - Required team size
- `task_requirements` (JSON)
- `stage_id` (FK)
- `status`

#### `user_subscriptions`
**Purpose**: User membership tracking

**Key Columns**:
- `uuid`
- `user_id` (FK)
- `stage_id` (FK)
- `level_id` (FK)
- `payment_status`
- `expires_at`

#### `level_tasks`
Achievement tasks for levels

#### `user_level_task_progress`
User progress tracking for tasks

### Product Catalog

#### `products`
**Purpose**: Main product table

**Key Columns**:
- `id` (bigint, PK)
- `uuid` (string, unique)
- `name`, `sku`, `url`
- `type` - Product type (simple/wholesale/configurable)
- `price` (integer) - Stored in cents
- `tax_configuration` - Tax settings JSON
- `tax_inclusive` (boolean)
- `tax_exempted` (boolean)
- `tax_slab_id` (FK)
- `min_quantity`, `max_quantity`
- `returnable` (boolean)
- `parent_id` (FK to products) - For variants
- `downloadable` (boolean)
- `proxy` (boolean) - External product
- `width`, `height`, `length`, `weight`
- `tenant_type`, `tenant_id` - Polymorphic multi-tenancy
- `category_id` (FK)
- `filter_group_id` (FK)
- `status`
- `view_count`
- `meta_data` (JSON)
- Timestamps

**Relationships**:
- Hierarchical: Self-referencing `parent_id`
- Belongs-to: category, filter_group, tax_code, tenant
- Many-to-many: filter_options, sales, order_products
- One-to-many: product_tiers, engagements, wishlists

#### `product_tiers`
Quantity-based pricing (bulk discounts)

#### `product_suppliers`
Supplier management

#### `product_filter_options`
Product variant attributes (pivot table)

### Categories & Filters

#### `categories`
Hierarchical categories using adjacency list
- `parent_id` for tree structure

#### `filters`
Product filter definitions (Color, Size, Material, etc.)

#### `filter_options`
Filter values (Red, Blue, Small, Large, etc.)

#### `filter_groups`
Grouping filters for product variants

### Cart & Orders

#### `carts`
**Purpose**: Shopping carts

**Key Columns**:
- `uuid`
- `owner_type`, `owner_id` - Polymorphic (user/guest)
- `tenant_type`, `tenant_id` - Multi-tenancy
- Timestamps

#### `orders`
**Purpose**: Customer orders

**Key Columns**:
- `uuid` (unique identifier for external use)
- `subtotal` (integer) - Amount before discounts
- `discount` (integer)
- `tax` (integer)
- `shipping_cost` (integer)
- `total` (integer)
- `quantity` - Total items
- `voucher_code`
- `status` - Order status enum (pending/processing/completed/cancelled)
- `payment_status`
- `expires_at` - Payment expiry
- `customer_type`, `customer_id` - Polymorphic
- `billing_address_id` (FK to addresses)
- `shipping_address_id` (FK to addresses)
- Timestamps

**Relationships**:
- Polymorphic: customer
- Belongs-to: billing_address, shipping_address
- One-to-many: order_products, invoices, shipments
- Has-one: transaction

#### `order_products`
**Purpose**: Order line items

**Key Columns**:
- `order_id` (FK)
- `product_id` (FK)
- `product_tier_id` (FK) - If bulk pricing used
- Product snapshot (name, sku, price at purchase)
- `quantity`
- `price` (integer)
- `tax` (integer)
- `total` (integer)
- `status` - Individual item status

#### `order_invoices`
Invoice records linking orders, products, and shipments

#### `order_shipments`
**Purpose**: Shipping details

**Key Columns**:
- `tracking_number`
- `dimensions`, `weight`
- `shipping_provider`
- `status`
- `shipment_activities` (JSON) - Tracking history
- Timestamps

### Promotions

#### `sales`
Sale campaigns with start/end dates

#### `sale_products`
Products included in sales (pivot table)

#### `vouchers`
Discount voucher definitions

#### `voucher_codes`
Individual voucher codes with usage tracking

### Financial

#### `wallets`
**Purpose**: Digital wallets for users

**Key Columns**:
- `uuid`
- `user_id` (FK)
- `balance` (integer) - Stored in cents
- `pin` (encrypted)
- `status`

#### `transactions`
**Purpose**: Payment transactions

**Key Columns**:
- `uuid`
- `transactable_type`, `transactable_id` - Polymorphic (order/wallet top-up)
- `amount` (integer)
- `gateway` - Payment gateway used
- `gateway_transaction_id`
- `status`
- `meta` (JSON) - Gateway response data
- Timestamps

#### `beneficiary_accounts`
Bank/UPI details for payouts

**Key Columns**:
- `user_id` (FK)
- `account_type` (bank/upi)
- `account_number`
- `ifsc_code`
- `upi_id`
- `is_default` (boolean)

#### `kycs`
**Purpose**: KYC verification records

**Key Columns**:
- `kycable_type`, `kycable_id` - Polymorphic
- `company_name`, `company_type`
- `pan_number`
- `aadhaar_number`
- `gst_number`
- `status` (pending/approved/rejected)
- Uses Spatie Media Library for document uploads

### Incentives (MLM Commission System)

#### `incentives`
Base incentive records

**Key Columns**:
- `uuid`
- `type` - Incentive type (affiliate/team/business)
- `user_id` (FK) - Recipient
- `amount` (integer)
- `order_id` (FK) - Source order
- `status` (pending/paid)
- `paid_at`

#### `affiliate_incentives`
Direct referral commissions (Single Table Inheritance)

#### `team_incentives`
Team-based bonuses (downline performance)

#### `business_incentives`
Business volume rewards

### Content & Community

#### `posts`
**Purpose**: Blog posts

**Key Columns**:
- `name`, `url`, `description`
- `author_type`, `author_id` - Polymorphic (user/admin/staff)
- `category_id` (FK)
- `status` (draft/published)
- `published_at`
- Uses Spatie Media Library for featured images

#### `product_engagements`
**Purpose**: Product reviews/ratings

**Key Columns**:
- `product_id` (FK)
- `user_id` (FK)
- `rating` (1-5)
- `review` (text)
- `parent_id` (FK to product_engagements) - For nested replies
- `helpful_count` - Upvotes
- `status`

**NOTE**: Missing `parent_id` attribute in model (documented issue)

#### `product_wishlists`
User wishlists (pivot table)

### Support & HR

#### `helpdesk_topics`
Support ticket categories

#### `helpdesks`
**Purpose**: Support tickets

**Key Columns**:
- `uuid`
- `user_id` (FK)
- `topic_id` (FK)
- `subject`, `description`
- `priority` (low/medium/high)
- `status` (open/in_progress/resolved/closed)

#### `helpdesk_conversations`
Ticket message threads

#### `helpdesk_faqs`
FAQ content management

#### `recruitments`
Job postings

#### `job_applications`
Job applications with resume uploads

### System & Infrastructure

#### `addresses`
Physical addresses

**Key Columns**:
- `addressable_type`, `addressable_id` - Polymorphic
- `type` (home/office/billing/shipping)
- `address_line_1`, `address_line_2`
- `landmark`
- `pincode`
- `country_id`, `state_id`, `block_id` (city) - FKs
- `is_default` (boolean)

#### `countries`, `states`, `blocks`
Location hierarchy

#### `integrations`
Third-party service configurations

#### `pages`
CMS pages (About, Privacy, Terms, etc.)

#### `inquiries`
Contact form submissions

#### `notifications`
User notifications (database channel)

#### `push_subscriptions`
Web push notification subscriptions (VAPID)

#### `media`
Spatie Media Library table

#### `personal_access_tokens`
Laravel Sanctum API tokens

#### `cache`, `cache_locks`
Database-backed caching

#### `jobs`, `failed_jobs`, `job_batches`
Queue system tables

#### `sessions`
Database-backed sessions

## Key Relationship Patterns

### Polymorphic Relations
- **customer**: Order can belong to User or Distributor
- **owner**: Cart can belong to User or Guest
- **originator**: Who recruited the user
- **author**: Posts can be authored by User, Admin, or Staff
- **tenant**: Multi-tenancy support
- **kycable**: KYC can belong to User or Distributor
- **addressable**: Addresses for various entities
- **transactable**: Transactions for Orders or Wallet operations

### Hierarchical Relations (Adjacency List)
- **users.parent_id** - MLM tree structure
- **categories.parent_id** - Category hierarchy
- **products.parent_id** - Product variants

### Pivot Tables
- `product_filter_options` - Product variants
- `filter_group_mapping` - Filter configurations
- `sale_products` - Products in sales
- `shipment_products` - Products in shipments
- `product_wishlists` - User wishlists

## Money Handling Strategy

**CRITICAL ISSUE**: Monetary values should be stored as integers (cents) for precision

**Fields Affected**:
- All `price`, `amount`, `total`, `subtotal`, `discount`, `tax` fields
- `wallet.balance`
- `incentive.amount`

**Implementation**:
- Should use `LaravelMoneyCast` consistently
- Convert: dollars * 100 = cents (integer storage)
- Display: cents / 100 = dollars

**Current Bug**: `LaravelMoneyCast::set()` has conversion commented out in some models

## Indexing Strategy

### Recommended Indexes
- `users.referral_code` - UNIQUE
- `users.parent_id` - For MLM tree queries
- `users.email` - UNIQUE
- `products.sku` - UNIQUE
- `products.url` - UNIQUE
- `orders.uuid` - UNIQUE
- `orders.customer_type, customer_id` - Polymorphic index
- `transactions.uuid` - UNIQUE
- `addresses.addressable_type, addressable_id` - Polymorphic index

## Data Integrity Considerations

1. **UUIDs**: Used for external references (API, URLs)
2. **Soft Deletes**: Minimal use (should be expanded)
3. **Timestamps**: Always included
4. **Status Enums**: Type-safe status management
5. **Foreign Key Constraints**: Should be enforced
6. **Unique Constraints**: Required for codes, SKUs, emails
