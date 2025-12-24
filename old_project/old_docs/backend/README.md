# Backend Documentation

This document provides an overview of the backend application, built with Laravel.

## Table of Contents
- [Project Overview](#project-overview)
- [Model Details](#model-details)
- [Technologies Used](#technologies-used)
- [Installation](#installation)
- [Scripts](#scripts)
- [Directory Structure](#directory-structure)
- [Key Features](#key-features)
- [API Endpoints](#api-endpoints)
- [Database Schema](#database-schema)
- [Custom Packages](#custom-packages)

## Project Overview

The backend is a Laravel 12 application that serves as the API for the Commerinity platform. It handles data storage, business logic, authentication, and provides various functionalities for e-commerce, user management, and other platform features.

## Technologies Used

-   **Laravel 12:** The PHP framework for web artisans.
-   **PHP 8.3:** The scripting language.
-   **Filament:** A TALL stack admin panel for Laravel.
-   **Laravel Sanctum:** For API authentication.
-   **Laravel Telescope:** For debugging and monitoring.
-   **Custom Mintreu Packages:** A suite of custom Laravel packages for specific functionalities like `laravel-category`, `laravel-commerinity`, `laravel-geokit`, `laravel-helpdesk`, `laravel-money`, `laravel-penpress`, `laravel-product-catalogue`, `laravel-recruitment`, `laravel-transaction`, and `toolkit`.

## Installation

To set up the backend application, follow these steps:

1.  Navigate to the `backend` directory:
    ```bash
    cd backend
    ```
2.  Install PHP dependencies:
    ```bash
    composer install
    ```
3.  Copy the `.env.example` file to `.env` and configure your database and other environment variables:
    ```bash
    cp .env.example .env
    ```
4.  Generate an application key:
    ```bash
    php artisan key:generate
    ```
5.  Run database migrations:
    ```bash
    php artisan migrate
    ```
6.  (Optional) Seed the database:
    ```bash
    php artisan db:seed
    ```

## Scripts

The `composer.json` defines several scripts:

-   `post-autoload-dump`: Runs after composer autoload dump, including package discovery and Filament upgrade.
-   `post-update-cmd`: Publishes Laravel assets.
-   `post-root-package-install`: Copies `.env.example` to `.env` if `.env` doesn't exist.
-   `post-create-project-cmd`: Generates app key, creates sqlite database (if not exists), and runs migrations.
-   `dev`: Starts development servers (Laravel, queue, pail, vite) concurrently.
-   `test`: Clears config and runs tests.

## Directory Structure

```
backend/
├── app/                # Core application code (Models, Http, Providers, etc.)
├── bootstrap/          # Framework bootstrapping
├── config/             # Configuration files
├── database/           # Database migrations, seeders, factories
├── public/             # Publicly accessible files
├── resources/          # Views, language files, uncompiled assets
├── routes/             # API and web routes
├── storage/            # Compiled templates, file uploads, logs
├── tests/              # Automated tests
├── vendor/             # Composer dependencies
├── artisan             # Laravel's command-line interface
├── composer.json       # Composer dependencies and scripts
├── package.json        # Node.js dependencies and scripts
├── phpunit.xml         # PHPUnit configuration
├── vite.config.js      # Vite configuration
└── ...
```

## Key Features

-   **User Authentication & Authorization:** Secure API authentication using Laravel Sanctum.
-   **E-commerce Functionality:** Comprehensive support for products, categories, carts, orders, and transactions.
-   **Wallet Management:** User wallet functionalities including adding money, withdrawals, and P2P transfers.
-   **Helpdesk System:** Ticketing system for customer support.
-   **Recruitment Module:** Management of job postings and applications.
-   **Content Management:** Blog posts and static pages.
-   **Push Notifications:** Web push notification capabilities.
-   **Admin Panel:** Powered by Filament for easy management of application data.

## API Endpoints

The API endpoints are defined in `routes/api.php` and its included files (`routes/apis/user/auth.php`, `routes/apis/user/account.php`, `routes/apis/geo-location.php`, `routes/apis/products.php`). Key endpoint categories include:

-   **Authentication:** Register, login, logout, password management, and authenticated user details.
-   **Account:** User profile and address management.
-   **Wallet:** Wallet operations, beneficiaries, and point conversion.
-   **Pages:** Retrieval of static pages.
-   **Geo Location:** Location-based services.
-   **Search:** Global search functionality.
-   **Categories:** Listing and viewing product categories.
-   **Products:** Product engagements (comments, wishlists).
-   **Flash Deals:** Information on flash sales.
-   **Cart:** Guest and authenticated cart management (add, update, remove, apply coupon, clear, merge).
-   **Orders:** Placing orders, viewing order details, cancellation, return, and refund.
-   **Transactions:** Transaction validation, failure, and display.
-   **Integration:** Payment integrations.
-   **Recruitment:** Job listings.
-   **Lifecycle:** User lifecycle stages and levels.
-   **Sales:** Sales and promotions.
-   **Helpdesk:** Ticket management and FAQs.
-   **Contact:** User and business inquiry forms.
-   **Blogs:** Listing and viewing blog posts.
-   **Push Notifications:** VAPID public key, subscribe, unsubscribe, and sending notifications.

## Database Schema

The database schema is designed to support a comprehensive e-commerce and community platform. It includes tables for user management, product catalog, order processing, financial transactions, and various other modules. The schema is built using Laravel migrations, and relationships are defined within the Eloquent models.

**Key Tables and Relationships:**

-   **`users`**: Stores user information. It has relationships with `wallets`, `addresses`, `orders`, `product_engagements`, `product_wishlists`, `kycs`, `helpdesks`, `job_applications`, and `user_subscriptions`.
    -   `uuid`: Unique identifier for the user.
    -   `name`, `email`, `mobile`, `password`.
    -   `referral_code`, `parent_id` (for hierarchical relationships).
    -   `type`, `status`, `gender`, `dob`.
    -   `email_verified_at`, `mobile_verified_at`.
-   **`admins`**: Stores admin user information, similar to `users` but for administrative access. It also implements `FilamentUser`.
-   **`staff`**: Stores staff user information, similar to `users` but for staff access. It also implements `FilamentUser`.
-   **`products`**: Stores product details. It has relationships with `categories`, `product_engagements`, `product_wishlists`, `carts`, and `order_products`.
    -   Inherits many fields from `Mintreu\LaravelProductCatalogue\Models\Product`.
    -   `tax_code_id`.
-   **`categories`**: Organizes products into categories.
-   **`carts`**: Stores items added to the shopping cart, supporting both guest and authenticated users.
    -   `quantity`, `discount`.
    -   `cartable_id`, `cartable_type` (polymorphic to `products`).
    -   `ownerable_id`, `ownerable_type` (polymorphic to `users` or `guests`).
    -   `guest_id`, `guest_token`, `is_guest`.
-   **`orders`**: Records customer orders.
    -   `uuid`, `amount`, `subtotal`, `discount`, `tax`, `total`, `quantity`.
    -   `status`, `payment_success`, `expire_at`.
    -   `customerable_id`, `customerable_type` (polymorphic to `users` or `guests`).
    -   `billing_address_id`, `shipping_address_id`.
-   **`order_products`**: Details of products within an order.
    -   `order_id`, `product_id`, `quantity`, `amount`, `discount`, `tax`, `total`.
    -   `status`, `status_feedback`.
-   **`order_shipments`**: Manages shipment details for orders.
    -   `order_id`, `total_quantity`, `status`, `tracking_id`.
    -   `pickup_address`, `delivery_address`.
-   **`order_invoices`**: Stores invoice details for orders.
    -   `uuid`, `order_id`, `order_product_id`, `order_shipment_id`.
-   **`transactions`**: Records all financial transactions.
-   **`wallets`**: Manages user wallets.
-   **`beneficiary_accounts`**: Stores beneficiary details for transfers.
-   **`kycs`**: Stores Know Your Customer (KYC) verification details.
    -   `uuid`, `user_type`, `company_name`, `gst`, `pan`, `aadhaar`.
    -   `kycable_id`, `kycable_type` (polymorphic to `users`, `staff`, etc.).
-   **`product_engagements`**: Stores product reviews and ratings.
    -   `product_id`, `authorable_id`, `authorable_type` (polymorphic to `users`, `staff`, etc.).
    -   `rating`, `review`, `helpful_votes`.
-   **`product_wishlists`**: Stores user wishlisted products.
    -   `product_id`, `authorable_id`, `authorable_type`.
-   **`posts`**: Stores blog posts or articles.
    -   `name`, `url`, `description`, `category_id`.
    -   `author_id`, `author_type` (polymorphic).
    -   `status`.
-   **`pages`**: Stores static content pages.
-   **`helpdesks`**: Manages support tickets.
-   **`helpdesk_topics`**: Categories for helpdesk tickets.
-   **`helpdesk_conversations`**: Stores conversations within helpdesk tickets.
-   **`helpdesk_faqs`**: Stores frequently asked questions.
-   **`recruitments`**: Stores job postings.
-   **`job_applications`**: Stores job applications.
-   **`stages`**: Defines lifecycle stages.
-   **`levels`**: Defines levels within lifecycle stages.
-   **`level_tasks`**: Tasks associated with each level.
-   **`user_level_task_progress`**: Tracks user progress on level tasks.
-   **`user_subscriptions`**: Manages user subscriptions to lifecycle stages/levels.
-   **`tax_codes`**: Stores tax-related information (HSN codes, GST rates).
-   **`inquiries`**: Stores user and business inquiries.
-   **`integrations`**: Stores information about third-party integrations (e.g., payment gateways).
-   **`sales`**: Manages sales and promotions.
-   **`vouchers`**: Stores voucher details.
-   **`voucher_codes`**: Stores individual voucher codes.
-   **`media`**: Spatie Media Library table for file attachments.
-   **`personal_access_tokens`**: For API authentication with Laravel Sanctum.
-   **`push_subscriptions`**: For web push notifications.
-   **`telescope_entries`**: For Laravel Telescope debugging.
-   **`cache`, `jobs`, `notifications`**: Standard Laravel tables.
-   **`countries`, `states`, `blocks`, `addresses`**: For geographical data and user addresses.

This detailed schema supports the complex functionalities of the Commerinity platform, with clear relationships between different entities.

## Model Details

For a comprehensive overview of all Eloquent models, their attributes, relationships, implemented features, and suggestions for improvement, please refer to the dedicated models documentation:

-   [Backend Models Documentation](MODELS.md)

This document provides a detailed breakdown of each model, facilitating a deeper understanding of the application's data structure and functionalities.

The application heavily utilizes custom packages developed by Mintreu. These packages extend Laravel's functionality and provide domain-specific features:

-   `mintreu/laravel-category`
-   `mintreu/laravel-commerinity`
-   `mintreu/laravel-geokit`
-   `mintreu/laravel-helpdesk`
-   `mintreu/laravel-money`
-   `mintreu/laravel-penpress`
-   `mintreu/laravel-product-catalogue`
-   `mintreu/laravel-recruitment`
-   `mintreu/laravel-transaction`
-   `mintreu/toolkit`

Each of these packages likely comes with its own set of migrations, models, and business logic, contributing to the overall database schema and application features.