# Backend Models Documentation

This document provides a detailed overview of the Eloquent models used in the Laravel backend application. For each model, it outlines its purpose, key attributes, relationships, implemented features, and potential areas for improvement or future development.

## Table of Contents
- [Introduction](#introduction)
- [Model Details](#model-details)
  - [Admin](./models/Admin/README.md)
  - [Cart](#cart)
  - [Distributor](#distributor)
  - [Incentive](#incentive)
  - [Lifecycle Models](#lifecycle-models)
    - [Level](./models/Lifecycle/Level/README.md)
  - [Order Models](#order-models)
    - [Order](#order)
    - [OrderInvoice](#orderinvoice)
    - [OrderProduct](#orderproduct)
    - [OrderShipment](#ordershipment)
  - [Post](#post)
  - [Product](#product)
  - [ProductEngagement](#productengagement)
  - [ProductWishlist](#productwishlist)
  - [Staff](#staff)
  - [TaxCode](#taxcode)
  - [Transaction Models](#transaction-models)
    - [Kyc](#kyc)
  - [User](#user)

## Introduction

The Laravel backend leverages Eloquent ORM to interact with the database. Models serve as the foundation for data representation and business logic. This document aims to provide a comprehensive guide to each model, facilitating a deeper understanding of the application's data structure and functionalities.

## Model Details

Each model entry includes:
-   **Purpose:** A brief description of the model's role.
-   **Key Attributes:** Important columns and their types.
-   **Relationships:** How the model connects to other models.
-   **Traits:** Reusable functionalities integrated into the model.
-   **Implemented Features:** Specific methods or logic within the model.
-   **Usage:** How the model is typically utilized in the application.
-   **Pros/Cons/Suggestions:** Analysis of the current implementation and recommendations.
-   **Feature Checklist:** A checklist to track implemented and pending features.





### Lifecycle Models

This section details models related to user lifecycle management, including levels, stages, tasks, and subscriptions.


#### Stage

-   **File:** `backend/app/Models/Lifecycle/Stage.php`
-   **Purpose:** Represents a lifecycle stage, which can contain multiple user levels. It defines pricing, team capacities, and benefits.
-   **Key Attributes:**
    -   `name` (string)
    -   `url` (string, used as route key)
    -   `desc` (string)
    -   `base_price` (money, casted by `LaravelMoneyCast`)
    -   `discount` (money, casted by `LaravelMoneyCast`)
    -   `tax_percentage` (float)
    -   `tax_amount` (money, casted by `LaravelMoneyCast`)
    -   `price` (integer, but likely represents money)
    -   `max_team_members` (integer)
    -   `estimated_total_joining_points` (integer)
    -   `estimated_total_purchasing_points` (integer)
    -   `estimated_total_clan_points` (integer)
    -   `benefits` (array)
    -   `accessibility` (array)
    -   `status` (boolean)
    -   `min_per_order` (integer)
    -   `max_per_order` (integer)
-   **Relationships:**
    -   `levels()`: `hasMany` relationship to `Level`.
    -   `subscription()`: `hasMany` relationship to `UserSubscription`.
-   **Traits:** `HasFactory`.
-   **Implemented Features:** Defines stages with detailed pricing, team limits, and various point estimations. `getRouteKeyName()` uses `url` for route model binding.
-   **Usage:** Used to structure the user progression path and define the overall offerings of different membership tiers.
-   **Pros/Cons/Suggestions:**
    -   **Pros:** Comprehensive definition of stages with many configurable attributes.
    -   **Cons:** `price` is cast as an integer, but `base_price`, `discount`, `tax_amount` use `LaravelMoneyCast`. This inconsistency might lead to confusion or errors. `benefits` and `accessibility` are arrays, but their structures are not explicit.
    -   **Suggestions:** Standardize money-related fields to use `LaravelMoneyCast`. Document the expected structure of `benefits` and `accessibility` arrays. Clarify the purpose of various point estimation fields.
-   **Feature Checklist:**
    -   [x] Stage name, URL, description
    -   [x] Pricing details (base, discount, tax)
    -   [x] Max team members
    -   [x] Point estimations
    -   [x] Benefits and accessibility
    -   [x] Status (active/inactive)
    -   [x] Min/max per order
    -   [x] Related levels
    -   [x] Related subscriptions

#### UserLevelTaskProgress

-   **File:** `backend/app/Models/Lifecycle/UserLevelTaskProgress.php`
-   **Purpose:** Tracks a user's progress on specific `LevelTask`s.
-   **Key Attributes:**
    -   `score` (integer)
    -   `level_task_id` (integer)
    -   `player_id` (integer)
    -   `player_type` (string)
    -   `is_complete` (boolean)
-   **Relationships:**
    -   `levelTask()`: `belongsTo` relationship to `LevelTask`.
    -   `player()`: `morphTo` relationship to the entity performing the task (e.g., `User`).
-   **Traits:** `HasFactory`.
-   **Implemented Features:** Records the score and completion status for a user's attempt at a level task.
-   **Usage:** Essential for tracking user progression through the lifecycle system and determining eligibility for level-ups.
-   **Pros/Cons/Suggestions:**
    -   **Pros:** Clear tracking of individual task progress.
    -   **Cons:** None apparent.
    -   **Suggestions:** Consider adding a `started_at` and `completed_at` timestamp for more detailed progress tracking.
-   **Feature Checklist:**
    -   [x] Score tracking
    -   [x] Link to LevelTask
    -   [x] Polymorphic player entity
    -   [x] Completion status

#### UserSubscription

-   **File:** `backend/app/Models/Lifecycle/UserSubscription.php`
-   **Purpose:** Manages user subscriptions to lifecycle stages/levels.
-   **Key Attributes:**
    -   `uuid` (string)
    -   `amount` (money, but commented out `LaravelMoneyCast`)
    -   `is_paid` (boolean)
    -   `expire_at` (datetime)
    -   `checkout_expires_at` (datetime)
    -   `user_id` (integer)
    -   `level_id` (integer)
    -   `stage_id` (integer)
    -   `wallet_id` (integer)
-   **Relationships:**
    -   `user()`: `belongsTo` relationship to `User`.
    -   `customer()`: Alias for `user()`.
    -   `stage()`: `belongsTo` relationship to `Stage`.
    -   `level()`: `belongsTo` relationship to `Level`.
-   **Traits:**
    -   `HasUnique`: For generating unique UUIDs.
    -   `HasTransaction`: Custom trait for transaction integration.
-   **Implemented Features:** Manages subscription details, payment status, expiry, and links to user, stage, and level.
-   **Usage:** Central to the platform's membership and subscription system, tracking active and expired subscriptions.
-   **Pros/Cons/Suggestions:**
    -   **Pros:** Comprehensive subscription management, clear links to related entities.
    -   **Cons:** `amount` field has `LaravelMoneyCast` commented out, which might lead to inconsistent money handling. `wallet_id` is present but no explicit relationship to a `Wallet` model.
    -   **Suggestions:** Re-enable `LaravelMoneyCast` for `amount` or clarify why it's commented out. Add an explicit `belongsTo` relationship to `Wallet` for `wallet_id`.
-   **Feature Checklist:**
    -   [x] Unique UUID
    -   [x] Amount and payment status
    -   [x] Expiry dates
    -   [x] Associated user, level, stage
    -   [x] Wallet integration

---

### Order Models

This section details models related to order processing, including orders, invoices, products within orders, and shipments.

#### Order

-   **File:** `backend/app/Models/Order/Order.php`
-   **Purpose:** Represents a customer order, containing details about the order itself, its status, and associated customer and addresses.
-   **Key Attributes:**
    -   `uuid` (string)
    -   `amount` (money, but commented out `LaravelMoneyCast`)
    -   `subtotal` (money, but commented out `LaravelMoneyCast`)
    -   `discount` (money, but commented out `LaravelMoneyCast`)
    -   `tax` (money, but commented out `LaravelMoneyCast`)
    -   `total` (money, but commented out `LaravelMoneyCast`)
    -   `quantity` (integer)
    -   `voucher` (string)
    -   `is_cod` (boolean)
    -   `tracking_id` (string)
    -   `status` (string, casted by `OrderStatusCast`)
    -   `payment_success` (boolean)
    -   `expire_at` (datetime)
    -   `customerable_type` (string)
    -   `customerable_id` (integer)
    -   `shipping_is_billing` (boolean)
    -   `billing_address_id` (integer)
    -   `shipping_address_id` (integer)
    -   `has_guest` (boolean)
    -   `customer_name` (string)
    -   `customer_email` (string)
    -   `customer_mobile` (string)
-   **Relationships:**
    -   `customerable()`: `morphTo` relationship to the customer (e.g., `User`).
    -   `customer()`: Alias for `customerable()`.
    -   `orderProducts()`: `hasMany` relationship to `OrderProduct`.
    -   `invoices()`: `hasMany` relationship to `OrderInvoice`.
-   **Traits:**
    -   `HasFactory`
    -   `HasOrderAddresses`: Custom trait for managing order addresses.
    -   `HasTransaction`: Custom trait for transaction integration.
    -   `HasUnique`: For generating unique UUIDs.
-   **Implemented Features:** Comprehensive order management, including status tracking, payment details, and customer information (supporting both authenticated and guest users).
-   **Usage:** Central to the e-commerce functionality, tracking all customer purchases.
-   **Pros/Cons/Suggestions:**
    -   **Pros:** Robust order tracking, support for guest orders, detailed address management through trait.
    -   **Cons:** All money-related fields have `LaravelMoneyCast` commented out, leading to inconsistent money handling. `amount` and `total` are redundant if `subtotal`, `discount`, and `tax` are present.
    -   **Suggestions:** Re-enable `LaravelMoneyCast` for all money fields. Re-evaluate the necessity of `amount` and `total` if a detailed breakdown is already available. Document the possible values for `status` (from `OrderStatusCast`).
-   **Feature Checklist:**
    -   [x] Unique UUID
    -   [x] Detailed pricing (subtotal, discount, tax, total)
    -   [x] Quantity
    -   [x] Voucher support
    -   [x] COD support
    -   [x] Tracking ID
    -   [x] Status and payment success
    -   [x] Expiry date
    -   [x] Polymorphic customer
    -   [x] Guest customer details
    -   [x] Billing and shipping addresses
    -   [x] Related order products
    -   [x] Related invoices

#### OrderInvoice

-   **File:** `backend/app/Models/Order/OrderInvoice.php`
-   **Purpose:** Represents an invoice generated for an order or order product.
-   **Key Attributes:**
    -   `uuid` (string)
    -   `order_id` (integer)
    -   `order_product_id` (integer)
    -   `order_shipment_id` (integer)
-   **Relationships:**
    -   `order()`: `belongsTo` relationship to `Order`.
    -   `orderProduct()`: `belongsTo` relationship to `OrderProduct`.
    -   `shipment()`: `belongsTo` relationship to `OrderShipment`.
-   **Traits:**
    -   `HasFactory`
    -   `HasUnique`: For generating unique UUIDs.
-   **Implemented Features:** Stores links to the associated order, order product, and shipment.
-   **Usage:** Used to track and manage invoices generated for customer orders.
-   **Pros/Cons/Suggestions:**
    -   **Pros:** Clear links to related order entities.
    -   **Cons:** No explicit fields for invoice details (e.g., `invoice_number`, `amount`, `pdf_path`). It primarily acts as a linking table.
    -   **Suggestions:** Add fields for actual invoice data (e.g., `invoice_number`, `total_amount`, `currency`, `issue_date`, `due_date`, `pdf_storage_path`).
-   **Feature Checklist:**
    -   [x] Unique UUID
    -   [x] Link to Order
    -   [x] Link to OrderProduct
    -   [x] Link to OrderShipment
    -   [ ] Invoice number
    -   [ ] Invoice amount and dates
    -   [ ] PDF storage path

#### OrderProduct

-   **File:** `backend/app/Models/Order/OrderProduct.php`
-   **Purpose:** Represents a specific product within an order, capturing its state at the time of purchase.
-   **Key Attributes:**
    -   `quantity` (integer)
    -   `amount` (money, casted by `LaravelMoneyCast`)
    -   `discount` (money, casted by `LaravelMoneyCast`)
    -   `tax` (money, casted by `LaravelMoneyCast`)
    -   `total` (money, casted by `LaravelMoneyCast`)
    -   `has_tax` (boolean)
    -   `product_id` (integer)
    -   `status` (string, casted by `OrderStatusCast`)
    -   `status_feedback` (string)
-   **Relationships:**
    -   `product()`: `belongsTo` relationship to `Product`.
    -   `order()`: `belongsTo` relationship to `Order`.
-   **Traits:** `HasFactory`.
-   **Implemented Features:** Stores product-specific details for an order, including quantity, pricing breakdown, and individual status.
-   **Usage:** Provides a snapshot of a product's details and pricing within a particular order, allowing for individual tracking and management of each item.
-   **Pros/Cons/Suggestions:**
    -   **Pros:** Detailed pricing breakdown for each product in an order, individual status tracking.
    -   **Cons:** None apparent.
    -   **Suggestions:** Ensure `OrderStatusCast` is consistent with the main `Order` model. Consider adding a `snapshot` field to store a JSON representation of the product's details at the time of purchase, in case the original `Product` record changes.
-   **Feature Checklist:**
    -   [x] Quantity
    -   [x] Detailed pricing (amount, discount, tax, total)
    -   [x] Tax status
    -   [x] Link to Product
    -   [x] Link to Order
    -   [x] Individual status and feedback

#### OrderShipment

-   **File:** `backend/app/Models/Order/OrderShipment.php`
-   **Purpose:** Manages shipment details for an order, including tracking, status, and associated addresses.
-   **Key Attributes:**
    -   `total_quantity` (integer)
    -   `last_update` (array)
    -   `status` (string, with defined constants)
    -   `invoice_uid` (string)
    -   `cod` (boolean)
    -   `tracking_id` (string)
    -   `tracking_data` (array)
    -   `weight` (float)
    -   `length` (float)
    -   `breadth` (float)
    -   `height` (float)
    -   `charge` (money, casted by `LaravelMoneyCast`)
    -   `provider_payment_method` (string)
    -   `provider_channel_id` (string)
    -   `provider_order_id` (string)
    -   `shipment_id` (string)
    -   `shipment_track_activities` (array)
    -   `details` (array)
    -   `order_id` (integer)
    -   `pickup_address` (integer)
    -   `delivery_address` (integer)
    -   `shipping_provider_id` (integer)
    -   `return_order_id` (integer)
    -   `return_shipment_id` (integer)
-   **Relationships:**
    -   `orderProducts()`: `belongsToMany` relationship to `OrderProduct` (via `shipment_products` pivot table).
    -   `order()`: `belongsTo` relationship to `Order`.
    -   `integration()`: `belongsTo` relationship to `Integration`.
    -   `invoice()`: `belongsTo` relationship to `OrderInvoice`.
-   **Traits:**
    -   `HasFactory`
    -   `HasOrderAddresses`: Custom trait for managing order addresses.
-   **Implemented Features:** Comprehensive shipment tracking, status updates, and integration with shipping providers. Defines various status constants.
-   **Usage:** Used to manage the logistics and delivery aspects of customer orders.
-   **Pros/Cons/Suggestions:**
    -   **Pros:** Detailed tracking information, clear status definitions, integration with external providers.
    -   **Cons:** `pickup_address` and `delivery_address` are integers but commented out `belongsTo` relationships to `Address` suggest a potential issue or incomplete implementation. `last_update`, `tracking_data`, `shipment_track_activities`, `details` are generic arrays; their structures should be documented.
    -   **Suggestions:** Re-enable or clarify the `Address` relationships. Document the expected structure of the array fields. Consider using enums for `status` for better type safety.
-   **Feature Checklist:**
    -   [x] Total quantity
    -   [x] Status and last update
    -   [x] Invoice UID
    -   [x] COD support
    -   [x] Tracking ID and data
    -   [x] Dimensions (weight, length, breadth, height)
    -   [x] Shipping charge
    -   [x] Provider details
    -   [x] Shipment ID and activities
    -   [x] Link to Order
    -   [x] Pickup and delivery addresses
    -   [x] Shipping provider integration
    -   [x] Return order/shipment IDs
    -   [x] Related order products
    -   [x] Related invoice

---

### Post

-   **File:** `backend/app/Models/Post.php`
-   **Purpose:** Represents a blog post or article, including content, author, and categorization.
-   **Key Attributes:**
    -   `name` (string)
    -   `url` (string, used as route key)
    -   `description` (string)
    -   `category_id` (integer)
    -   `author_id` (integer)
    -   `author_type` (string)
    -   `status` (string, casted by `PublishableStatusCast`)
    -   `status_feedback` (string)
-   **Relationships:**
    -   `author()`: `morphTo` relationship to the author (e.g., `User`, `Admin`).
    -   `category()`: `belongsTo` relationship to `Category`.
-   **Traits:**
    -   `HasFactory`
    -   `HasUnique`: For generating unique identifiers.
    -   `InteractsWithMedia`: From Spatie Media Library for media attachments.
    -   `HasCategory`: Custom trait for category management.
-   **Implemented Features:** Manages blog post content, media (display and banner images), and categorization. `getRouteKeyName()` uses `url` for route model binding.
-   **Usage:** Used for the platform's blog or knowledge base section.
-   **Pros/Cons/Suggestions:**
    -   **Pros:** Flexible authoring with polymorphic relationship, media management, clear categorization.
    -   **Cons:** `description` is a string, but often rich text content is stored. Consider using a dedicated rich text field or a package for this.
    -   **Suggestions:** Document the possible values for `status` (from `PublishableStatusCast`). If `description` stores rich text, consider using a rich text editor and storing HTML or Markdown, and ensure proper sanitization on display.
-   **Feature Checklist:**
    -   [x] Name, URL, description
    -   [x] Category
    -   [x] Polymorphic author
    -   [x] Status and feedback
    -   [x] Media (display and banner images)

---

### Product

-   **File:** `backend/app/Models/Product.php`
-   **Purpose:** Extends the `Mintreu\LaravelProductCatalogue\Models\Product` model, adding cartable functionality.
-   **Key Attributes:** Inherits attributes from `Mintreu\LaravelProductCatalogue\Models\Product`.
-   **Relationships:** Inherits relationships from `Mintreu\LaravelProductCatalogue\Models\Product`.
-   **Traits:**
    -   `HasCartable`: Custom trait for making the product addable to a cart.
-   **Implemented Features:** Integrates with the cart system, allowing products to be added to a `Cart`.
-   **Usage:** Represents a product available for sale on the platform.
-   **Pros/Cons/Suggestions:**
    -   **Pros:** Extends a dedicated product catalogue package, promoting modularity. `HasCartable` trait simplifies cart integration.
    -   **Cons:** The full structure of `Mintreu\LaravelProductCatalogue\Models\Product` is not visible here, making a complete analysis difficult without inspecting the package.
    -   **Suggestions:** Document the key attributes and relationships inherited from the base `Product` model from the `mintreu/laravel-product-catalogue` package.
-   **Feature Checklist:**
    -   [x] Inherited product attributes
    -   [x] Cartable functionality

---

### ProductEngagement

-   **File:** `backend/app/Models/ProductEngagement.php`
-   **Purpose:** Represents user engagement with a product, such as ratings and reviews.
-   **Key Attributes:**
    -   `product_id` (integer)
    -   `authorable_id` (integer)
    -   `authorable_type` (string)
    -   `rating` (integer)
    -   `review` (string)
    -   `helpful_votes` (integer)
-   **Relationships:**
    -   `authorable()`: `morphTo` relationship to the entity making the engagement (e.g., `User`).
    -   `product()`: `belongsTo` relationship to `Product`.
-   **Traits:** `HasFactory`.
-   **Implemented Features:** Stores product ratings and reviews, including helpfulness votes. Provides scopes for `topLevel` (for parent comments/reviews) and `replies` (for replies to comments/reviews).
-   **Usage:** Used to capture and display user feedback on products.
-   **Pros/Cons/Suggestions:**
    -   **Pros:** Clear separation of engagement data, polymorphic authoring, helpfulness tracking.
    -   **Cons:** The `parent_id` field is implied by the `topLevel` and `replies` scopes but not explicitly defined as an attribute in the model, which is a **wrong implementation**. This could lead to unexpected behavior or data integrity issues.
    -   **Suggestions:** Explicitly add a `parent_id` (nullable integer) attribute to the `ProductEngagement` model to properly support nested engagements (comments/replies). Implement a `parent()` `belongsTo` and `children()` `hasMany` relationship for hierarchical structure.
-   **Feature Checklist:**
    -   [x] Product link
    -   [x] Polymorphic author
    -   [x] Rating
    -   [x] Review text
    -   [x] Helpfulness votes
    -   [ ] Hierarchical engagement (comments/replies) - **Wrong Implementation: `parent_id` missing**

---

### ProductWishlist

-   **File:** `backend/app/Models/ProductWishlist.php`
-   **Purpose:** Represents a product added to a user's wishlist.
-   **Key Attributes:**
    -   `product_id` (integer)
    -   `authorable_id` (integer)
    -   `authorable_type` (string)
-   **Relationships:**
    -   `authorable()`: `morphTo` relationship to the entity owning the wishlist (e.g., `User`).
    -   `product()`: `belongsTo` relationship to `Product`.
-   **Traits:** `HasFactory`.
-   **Implemented Features:** Stores a link between a product and the user who wishlisted it.
-   **Usage:** Used to manage user wishlists.
-   **Pros/Cons/Suggestions:**
    -   **Pros:** Simple and clear representation of a wishlist item, polymorphic authoring.
    -   **Cons:** None apparent.
    -   **Suggestions:** Consider adding a `notes` or `priority` field if users need to add personal notes or prioritize items in their wishlist.
-   **Feature Checklist:**
    -   [x] Product link
    -   [x] Polymorphic author

---

### Staff

-   **File:** `backend/app/Models/Staff.php`
-   **Purpose:** Represents staff users with access to the Filament admin panel and various platform functionalities. It extends `Authenticatable` and implements `FilamentUser`.
-   **Key Attributes:**
    -   `uuid` (string)
    -   `name` (string)
    -   `email` (string)
    -   `mobile` (string)
    -   `password` (string, hashed)
    -   `parent_id` (integer)
    -   `type` (string, casted by `AuthTypeCast`)
    -   `status` (string, casted by `AuthStatusCast`)
    -   `status_feedback` (string)
    -   `bio` (string)
    -   `gender` (string, casted by `GenderCast`)
    -   `dob` (date)
    -   `email_verified_at` (datetime)
    -   `mobile_verified_at` (datetime)
    -   `onboarded` (boolean)
-   **Relationships:** Inherits various polymorphic relationships through traits. Also has `HasRecursiveRelationships` for hierarchical structures.
-   **Traits:**
    -   `HasApiTokens`: For API authentication (Laravel Sanctum).
    -   `HasFactory`, `HasPushSubscriptions`, `Notifiable`, `InteractsWithMedia`.
    -   `HasRecursiveRelationships`: From `staudenmeir/laravel-adjacency-list` for hierarchical data (e.g., reporting structure).
    -   `HasAddress`, `HasCartOwner`, `HasKyc`, `HasUnique`, `HasLifecycle`, `HasOrder`, `HasFingerprint`, `HasJobApplications`, `HasSupportTicket`, `HasWallet`, `HasBeneficiary`, `HasVoucherAccess`, `HasProductEngagement`, `HasProductWishlist`.
    -   `Fingerprintable`: Custom interface.
    -   `EnjoyLifeCycle`: Custom interface.
-   **Implemented Features:** Similar to `Admin`, but specifically for staff. Includes comprehensive personal and authentication details, hierarchical relationships, and numerous platform functionalities through traits. `canAccessPanel()` allows Filament access. `registerMediaCollections()` defines `avatarImage` media collection.
-   **Usage:** Used for internal staff management, providing access to specific tools and data based on their roles and permissions.
-   **Pros/Cons/Suggestions:**
    -   **Pros:** Highly comprehensive model for staff, supporting complex hierarchical structures and a wide range of functionalities through traits. Filament integration.
    -   **Cons:** Very large number of traits and attributes makes the model potentially complex and heavy. The distinction between `Admin` and `Staff` might need clearer definition if their functionalities overlap significantly.
    -   **Suggestions:** Review the necessity of each trait for `Staff` users. Consider implementing role-based access control (RBAC) to manage permissions more granularly rather than relying solely on trait inclusion. Document the purpose of `parent_id` and how `HasRecursiveRelationships` is utilized.
-   **Feature Checklist:**
    -   [x] Authentication (API & Filament)
    -   [x] Personal details (name, email, mobile, gender, DOB, bio)
    -   [x] Status and type
    -   [x] Email/Mobile verification
    -   [x] Onboarding status
    -   [x] Hierarchical relationships (`parent_id`)
    -   [x] Media Management (Avatar)
    -   [x] Address Management
    -   [x] Cart Ownership
    -   [x] KYC Management
    -   [x] Lifecycle Management
    -   [x] Order Management
    -   [x] Fingerprinting
    -   [x] Job Applications
    -   [x] Support Ticket Management
    -   [x] Wallet Management
    -   [x] Beneficiary Management
    -   [x] Voucher Access
    -   [x] Product Engagement
    -   [x] Product Wishlist

---

### TaxCode

-   **File:** `backend/app/Models/TaxCode.php`
-   **Purpose:** Stores tax-related information, such as HSN codes and GST rates, for products or services.
-   **Key Attributes:**
    -   `code` (string, HSN code)
    -   `type` (string, casted by `TaxTypeCast`)
    -   `description` (string)
    -   `cgst_rate` (float)
    -   `sgst_rate` (float)
    -   `igst_rate` (float)
    -   `cess_rate` (float)
    -   `is_active` (boolean)
-   **Relationships:**
    -   `products()`: `hasMany` relationship to `Product`.
-   **Traits:** `HasFactory`.
-   **Implemented Features:** Calculates total GST rate. Provides a `scopeActive()` for filtering active tax codes. Helper methods `isGoods()` and `isService()`.
-   **Usage:** Used to apply correct tax rates to products and services.
-   **Pros/Cons/Suggestions:**
    -   **Pros:** Clear structure for tax data, useful helper methods and scopes.
    -   **Cons:** `type` is a string; possible values should be documented or enforced with an enum.
    -   **Suggestions:** Document the possible values for `type` (from `TaxTypeCast`). Consider adding validation rules for tax rates (e.g., non-negative).
-   **Feature Checklist:**
    -   [x] HSN code
    -   [x] Type and description
    -   [x] GST rates (CGST, SGST, IGST, Cess)
    -   [x] Active status
    -   [x] Total GST rate calculation
    -   [x] Goods/Service type check
    -   [x] Related products

---

### Transaction Models

This section details models related to financial transactions.

#### Kyc

-   **File:** `backend/app/Models/Transaction/Kyc.php`
-   **Purpose:** Stores Know Your Customer (KYC) verification details for various entities.
-   **Key Attributes:**
    -   `uuid` (string)
    -   `user_type` (string, casted by `KycTypeCast`)
    -   `company_name` (string)
    -   `company_type` (string)
    -   `has_tax` (boolean)
    -   `gst` (string)
    -   `pan` (string)
    -   `aadhaar` (string)
    -   `utility_bills` (array)
    -   `kycable_type` (string)
    -   `kycable_id` (integer)
-   **Relationships:**
    -   `kycable()`: `morphTo` relationship to the entity undergoing KYC (e.g., `User`, `Staff`).
-   **Traits:**
    -   `HasFactory`
    -   `InteractsWithMedia`: From Spatie Media Library for media attachments (Aadhaar, PAN, GST images).
    -   `HasUnique`: For generating unique UUIDs.
-   **Implemented Features:** Manages KYC documents and information, supporting different user types and company details. Defines media collections for Aadhaar, PAN, and GST images.
-   **Usage:** Used for identity verification and compliance within the platform.
-   **Pros/Cons/Suggestions:**
    -   **Pros:** Flexible polymorphic relationship, comprehensive fields for various KYC documents, media management for document uploads.
    -   **Cons:** `utility_bills` is a generic array; its structure should be documented. `company_type` is a string; possible values are not defined.
    -   **Suggestions:** Document the expected structure of `utility_bills` array. Define possible values for `user_type` (from `KycTypeCast`) and `company_type`.
-   **Feature Checklist:**
    -   [x] Unique UUID
    -   [x] User type and company details
    -   [x] Tax status (has_tax, GST)
    -   [x] Aadhaar and PAN details
    -   [x] Utility bills
    -   [x] Polymorphic kycable entity
    -   [x] Media management for documents

---

### User

-   **File:** `backend/app/Models/User.php`
-   **Purpose:** Represents a standard user of the application. It extends Laravel's `Authenticatable` and implements `MustVerifyEmail`, `FilamentUser`, `Fingerprintable`, and `EnjoyLifeCycle`.
-   **Key Attributes:**
    -   `uuid` (string)
    -   `name` (string)
    -   `email` (string)
    -   `mobile` (string)
    -   `password` (string, hashed)
    -   `referral_code` (string)
    -   `parent_id` (integer)
    -   `type` (string, casted by `AuthTypeCast`)
    -   `status` (string, casted by `AuthStatusCast`)
    -   `status_feedback` (string)
    -   `bio` (string)
    -   `gender` (string, casted by `GenderCast`)
    -   `dob` (date)
    -   `email_verified_at` (datetime)
    -   `mobile_verified_at` (datetime)
    -   `onboarded` (boolean)
-   **Relationships:**
    -   `level()`: `belongsTo` relationship to `Level`.
    -   `memberships()`: `hasMany` relationship to `UserSubscription`.
    -   `membership()`: `hasOne` relationship to the latest active `UserSubscription`.
    -   `originator()`: `morphTo` relationship (likely for who referred this user).
    -   `originatedUsers()`: `morphMany` relationship (for users referred by this user).
    -   Inherits various polymorphic relationships through traits.
-   **Traits:**
    -   `HasApiTokens`, `HasFactory`, `HasPushSubscriptions`, `Notifiable`, `InteractsWithMedia`.
    -   `HasRecursiveRelationships`: From `staudenmeir/laravel-adjacency-list` for hierarchical data (e.g., referral network).
    -   `HasAddress`, `HasCartOwner`, `HasKyc`, `HasUnique`, `HasLifecycle`, `HasOrder`, `HasFingerprint`, `HasJobApplications`, `HasSupportTicket`, `HasWallet`, `HasBeneficiary`, `HasVoucherAccess`, `HasProductEngagement`, `HasProductWishlist`.
    -   `Commenter`: From `kirschbaum-development/commentions` for commenting functionality.
-   **Implemented Features:** Comprehensive user management, including authentication, verification, personal details, referral system, and integration with almost all platform functionalities through numerous traits. `canAccessPanel()` allows Filament access. `registerMediaCollections()` defines `avatarImage` media collection. `setUniqueCodeUpper()` for `referral_code` and `setUniqueCode()` for `uuid` are used during creation.
-   **Usage:** The central model for all end-users of the platform, connecting them to various features and data.
-   **Pros/Cons/Suggestions:**
    -   **Pros:** Extremely comprehensive, integrating almost all platform features into a single user model. Supports complex referral networks and lifecycle management.
    -   **Cons:** The sheer number of traits and responsibilities makes this model very large and potentially difficult to manage or debug. It violates the Single Responsibility Principle. The `HasLifecycle` trait includes a `getNextLifecycleStage()` method that has a `try-catch` block with `report($t)`, which is generally not ideal within a model method and should be handled at a higher level (e.g., in a service or controller).
    -   **Suggestions:** Consider refactoring some functionalities into dedicated service classes or smaller, more focused models/traits if the complexity becomes unmanageable. Review the `getNextLifecycleStage()` method for error handling best practices. Document the purpose of `parent_id` and how `HasRecursiveRelationships` is utilized for the referral network.
-   **Feature Checklist:**
    -   [x] Authentication (API & Filament)
    -   [x] Email/Mobile verification
    -   [x] Personal details (name, email, mobile, gender, DOB, bio)
    -   [x] Referral system (`referral_code`, `parent_id`)
    -   [x] Status and type
    -   [x] Onboarding status
    -   [x] Media Management (Avatar)
    -   [x] Address Management
    -   [x] Cart Ownership
    -   [x] KYC Management
    -   [x] Lifecycle Management
    -   [x] Order Management
    -   [x] Fingerprinting
    -   [x] Job Applications
    -   [x] Support Ticket Management
    -   [x] Wallet Management
    -   [x] Beneficiary Management
    -   [x] Voucher Access
    -   [x] Product Engagement
    -   [x] Product Wishlist
    -   [x] Commenting functionality

---
