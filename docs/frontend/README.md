# Frontend Documentation

This document provides an overview of the frontend application, built with Nuxt.js 3.

## Table of Contents
- [Project Overview](#project-overview)
- [Technologies Used](#technologies-used)
- [Installation](#installation)
- [Scripts](#scripts)
- [Directory Structure](#directory-structure)
- [Key Features](#key-features)
- [Authentication](#authentication)
- [State Management](#state-management)
- [UI Components](#ui-components)
- [Pages and Routing](#pages-and-routing)
- [API Integration](#api-integration)

## Project Overview

The frontend is a Nuxt.js 3 application designed to provide a rich and interactive user experience for the Commerinity platform. It leverages server-side rendering (SSR) for improved performance and SEO, and is built with a component-based architecture.

## Technologies Used

- **Nuxt.js 3:** The progressive Vue.js framework for building modern web applications.
- **Vue.js 3:** The core JavaScript framework.
- **Tailwind CSS:** A utility-first CSS framework for rapid UI development.
- **GSAP:** GreenSock Animation Platform for high-performance animations.
- **D3.js & Echarts:** For data visualization and charting.
- **Swiper:** Modern touch slider.
- **@qirolab/nuxt-sanctum-authentication:** For handling authentication with Laravel Sanctum.
- **@vite-pwa/nuxt:** For Progressive Web App (PWA) capabilities.
- **@nuxtjs/sitemap & @nuxtjs/robots:** For SEO optimization.

## Installation

To set up the frontend application, follow these steps:

1.  Navigate to the `frontend` directory:
    ```bash
    cd frontend
    ```
2.  Install the dependencies:
    ```bash
    npm install
    ```

## Scripts

The following npm scripts are available:

- `npm run build`: Builds the application for production deployment.
- `npm run dev`: Starts the development server with hot-reloading.
- `npm run generate`: Generates static files for the application.
- `npm run preview`: Locally previews your production build.
- `npm run postinstall`: Prepares the Nuxt application (usually runs after `npm install`).

## Directory Structure

```
frontend/
├── assets/             # Static assets (images, fonts, etc.)
├── components/         # Vue.js components
├── composables/        # Reusable Vue.js composition functions
├── layouts/            # Application layouts
├── middleware/         # Nuxt.js middleware
├── pages/              # Application pages and routing
├── plugins/            # Nuxt.js plugins
├── public/             # Statically served files
├── server/             # Server-side API routes and middleware
├── types/              # TypeScript type definitions
├── utils/              # Utility functions
├── app.config.ts       # Application configuration
├── app.vue             # Main application component
├── nuxt.config.ts      # Nuxt.js configuration
├── package.json        # Project dependencies and scripts
├── tailwind.config.js  # Tailwind CSS configuration
└── tsconfig.json       # TypeScript configuration
```

## Key Features

- **User Authentication:** Secure authentication powered by Laravel Sanctum.
- **E-commerce Functionality:** Cart management, product display, order processing.
- **Data Visualization:** Interactive charts and graphs using D3.js and Echarts.
- **Responsive Design:** Built with Tailwind CSS for a mobile-first approach.
- **PWA Support:** Offline capabilities and installability.
- **SEO Friendly:** Optimized for search engines with sitemap and robots.txt generation.

## Authentication

The application uses `@qirolab/nuxt-sanctum-authentication` for handling user authentication. This module simplifies the integration with Laravel Sanctum, providing features like login, registration, and session management.

## State Management

The application leverages Vue's reactivity system and Nuxt's `useState` for global state management, particularly for features like the shopping cart. API interactions are central to these composables.

-   **`useCart.ts`**: This composable provides comprehensive state management and API interactions for the shopping cart. It handles:
    -   Guest cart functionality, including generating and validating guest credentials via cookies via API calls like `POST /cart/guest-credential` and `POST /cart/validate/guest-credential`.
    -   Authenticated user cart management.
    -   Operations such as adding, updating, and removing products through API endpoints like `POST /cart/add/{sku}`, `POST /cart/update/{sku}`, `DELETE /cart/remove/{sku}`.
    -   Applying and managing discount coupons via `POST /cart/coupon/{code}`.
    -   Clearing the cart via `POST /cart/clear`.
    -   Merging guest carts into a user's cart upon authentication via `POST /cart/merge`.
    -   Manages loading states and request queues to prevent duplicate API calls.
-   **`useWishlist.ts`**: Manages the user's product wishlist, allowing authenticated users to add, remove, and toggle products in their wishlist through API calls like `POST /product/wishlist/{productUrl}` and `DELETE /product/wishlist/{productUrl}`.
-   **`buildingConfig.ts`**: While not a traditional state management composable, it defines the static configuration and state for interactive "buildings" within the application, including their status, levels, and routes.

## Utility Composables

-   **`usePageMeta.ts`**: A utility for dynamically setting page metadata (title, description, Open Graph tags, etc.) to enhance SEO and social media sharing.
-   **`useToast.ts`**: Implements a reactive and dynamic toast notification system for displaying various types of messages (success, error, warning, info, question) to the user. It supports custom actions, timeouts, and different display positions.

## UI Components

The application utilizes a modular and organized component structure, with components categorized into logical directories. This promotes reusability and maintainability. Many components interact with the backend API to fetch or submit data.

**Key Component Categories:**

-   **`account/`**: Components related to user account management, profiles, and settings. Examples include `AvatarUploader.vue` (for `PUT /account/avatar`), `ChangeEmail.vue` (for `PUT /account/contact`), `ChangeMobile.vue` (for `PUT /account/contact`), `ChangePassword.vue` (for `PUT /account/password`), `DeleteAccount.vue` (for `DELETE /account/delete`), and `ExportData.vue` (for `POST /account/export-data`).
-   **`blog/`**: Components for displaying blog posts, categories, and related content, such as `BlogList.vue` which fetches blog posts and category information.
-   **`card/`**: Generic card components, possibly for displaying products, articles, or other content in a card-like format.
-   **`career/`**: Components for job listings, application forms, and career-related content.
-   **`cart/`**: Components for displaying cart items, managing quantities, and checkout processes. These heavily rely on the `useCart` composable for API interactions.
-   **`charts/`**: Reusable components for data visualization using charting libraries (e.g., Echarts, D3.js). These often consume data fetched from backend APIs.
-   **`dashboard/`**: Components specific to the user dashboard, including widgets, statistics, and navigation. `DashboardStatCard.vue` is an example that displays data fetched from APIs.
-   **`games/`**: Components related to interactive game elements or features.
-   **`home/`**: Components specific to the homepage layout and content.
-   **`insights/`**: Components for displaying analytical data and business insights. These are currently static but are candidates for API integration.
-   **`onboarding/`**: Components guiding users through initial setup or feature introductions, interacting with APIs like `POST /account/onboarding`.
-   **`product/`**: Components for displaying product details, listings, variations, and reviews.
-   **`sliders/`**: Reusable slider components, likely utilizing Swiper.js.
-   **`store/`**: Components related to the e-commerce store, such as product grids, filters, and categories.
-   **`subscription/`**: Components for managing user subscriptions and plans, interacting with APIs like `POST /account/lifecycle/subscribe`.
-   **`timelines/`**: Components for displaying chronological events or progress.
-   **`ui/`**: Generic UI elements like buttons, inputs, modals, and other foundational design system components.

**Standalone Components:**

-   **`AlertModal.vue`**: A general-purpose modal component for displaying alerts or confirmations.
-   **`DashboardStatCard.vue`**: A card component specifically designed to display statistics on a dashboard, consuming data from APIs like `GET /account/stats/dashboard`.
-   **`FilamentTipTapContent.vue`**: Likely a component for rendering rich text content generated by the Filament TipTap editor (from the backend).
-   **`GlobalLoader.vue`**: A component to indicate global loading states across the application.
-   **`RegisterForm.vue` / `RegisterFormComponent.vue`**: Components for user registration forms, interacting with authentication APIs like `POST /register`, `POST /auth/send-otp`, etc.
-   **`Toast.vue`**: The core component for displaying toast notifications, as managed by `useToast` composable.

## Pages and Routing

Nuxt.js utilizes a file-system based routing approach, where `.vue` files within the `pages` directory automatically generate routes. This structure provides a clear and intuitive way to manage the application's navigation. Many pages are responsible for fetching and displaying data from the backend API.

**Key Page Categories and Examples:**

-   **Static Pages:**
    -   `index.vue`: The application's homepage.
    -   `about.vue`: Information about the company or platform. (Currently uses static data, candidate for API integration).
    -   `contact.vue`: Contact us page, interacts with `POST /contact/user` and `POST /contact/business`.
    -   `help.vue`: Help or FAQ section.
    -   `privacy.vue`: Privacy policy. (Currently uses static data, candidate for API integration).
    -   `return-refund.vue`: Return and refund policy. (Currently uses static data, candidate for API integration).
    -   `search.vue`: Global search results page, interacts with `GET /search`.
    -   `shipping.vue`: Shipping information. (Currently uses static data, candidate for API integration).
    -   `terms.vue`: Terms and conditions. (Currently uses static data, candidate for API integration).
    -   `test.vue`: A temporary page likely used for testing purposes.

-   **Authentication (`auth/`):** Contains pages related to user authentication, such as login (`login.vue`), registration (`register.vue` which uses `RegisterForm`), and password reset (`forgot-password.vue`). These pages interact with various authentication APIs like `POST /login`, `POST /register`, `POST /auth/send-otp`, `POST /auth/verify-otp`, `POST /auth/reset-password`, and social login redirects.
-   **Blogs (`blogs/`):** Pages for displaying blog posts, categories, and individual blog articles. `index.vue` uses `BlogList` to fetch posts via `GET /blogs` and categories via `GET /categories/{categorySlug}`. `[url].vue` fetches individual posts via `GET /blogs/{url}`.
-   **Career (`career/`):** Pages for job listings and career opportunities. `index.vue` fetches job listings via `GET /recruitment`. `[url].vue` fetches individual job details via `GET /recruitment/{url}`.
-   **Cart (`cart/`):** Pages for viewing and managing the shopping cart. `index.vue` heavily relies on the `useCart` composable and interacts with APIs like `GET /products/suggestions/cart`, `GET /account/addresses`, `GET /integration/payment`, and `POST /order/place`.
-   **Categories (`categories/` & `category/`):** Pages for browsing product categories and individual category details. `index.vue` fetches categories with products via `GET /categories/with-products`. `[url].vue` fetches category details and products via `GET /categories/{categoryUrl}` and also fetches product sorting and filtering options.
-   **Dashboard (`dashboard/`):** User-specific dashboard pages, potentially with sub-routes for different sections (e.g., dashboard/profile, dashboard/orders).
    -   `dashboard/index.vue`: Fetches dashboard statistics via `GET /account/stats/dashboard`.
    -   `dashboard/account/index.vue`: Fetches user profile and stats via `GET /account/stats` and `GET /account/activity`, and updates profile via `PUT /account/profile`.
    -   `dashboard/account/address/index.vue`: Manages user addresses via `GET /account/addresses`, `POST /account/addresses`, `PUT /account/addresses/{address:uuid}`, `DELETE /account/addresses/{address:uuid}`, and `POST /account/addresses/{account:uuid}/default`.
    -   `dashboard/account/kyc/index.vue`: Manages KYC information via `GET /account/kyc`, `POST /account/kyc`, `PUT /account/kyc`.
    -   `dashboard/helpdesk/index.vue`: Fetches helpdesk tickets via `GET /helpdesk/tickets`.
    -   `dashboard/helpdesk/create.vue`: Creates new helpdesk tickets via `POST /helpdesk/tickets` and fetches topics via `GET /helpdesk/topics/ticket`.
    -   `dashboard/helpdesk/[url]/index.vue`: Fetches individual ticket details via `GET /helpdesk/tickets/{uuid}` and sends replies via `POST /helpdesk/tickets/{uuid}/reply`.
    -   `dashboard/members/index.vue`: Fetches referral tree data via `GET /account/tree`.
    -   `dashboard/orders/index.vue`: Fetches user orders via `GET /orders`.
    -   `dashboard/orders/[uuid]/index.vue`: Fetches individual order details via `GET /orders/{uuid}` and downloads invoices via `GET /orders/{uuid}/invoice`.
    -   `dashboard/wallet/index.vue`: Manages wallet operations via `GET /wallet`, `POST /wallet/create`, `POST /wallet/add-money`, `POST /wallet/withdraw`, `POST /wallet/send`, `POST /wallet/change-pin`, `POST /wallet/point-conversion`, and fetches analytics via `GET /wallet/analytics`.
    -   `dashboard/wallet/beneficiary.vue`: Manages beneficiaries via `GET /beneficiaries`, `POST /beneficiaries`, `PUT /beneficiaries/{account:uuid}`, `DELETE /beneficiaries/{account:uuid}`, and `POST /beneficiaries/{account:uuid}/default`.
    -   `dashboard/wallet/transactions.vue`: Fetches transaction history via `GET /transactions` and individual transaction details via `GET /transactions/{uuid}`.
-   **News (`news/`):** Pages for news articles or announcements.
-   **Product (`product/`):** Pages for displaying individual product details.
-   **Shop (`shop/`):** Pages related to the overall shopping experience, perhaps product listings or filtered views.
-   **Store (`store/`):** Another directory potentially related to the e-commerce store, possibly for specific store sections or seller profiles.

Nuxt.js dynamic routing is likely used within these directories (e.g., `product/[slug].vue` or `category/[id].vue`) to handle specific items or entities.

## API Integration

The frontend application interacts extensively with the Laravel backend API to fetch and manipulate data. All API calls are handled securely, primarily leveraging the authentication provided by Laravel Sanctum and the `useSanctumFetch` composable.

For a detailed breakdown of each frontend API call, its corresponding backend route, and the controller/method handling it, please refer to the dedicated API integration document:

-   [Frontend API Integration Details](API_INTEGRATION.md)

This document provides a comprehensive mapping of API endpoints, helping developers understand the data flow and interaction points between the frontend and backend.

## Future Enhancements / Static Data Conversion

During the API integration analysis, several areas were identified where static, hardcoded data in the frontend could be replaced with dynamic data fetched from backend APIs. This would significantly improve the application's flexibility, maintainability, and content management capabilities.

Key areas for static data to API conversion include:

-   **Insight Components:** `frontend/components/insights/applicant/ApplicantInsight.vue`, `frontend/components/insights/member/MemberInsight.vue`, `frontend/components/insights/organizer/OrganizerInsight.vue`, `frontend/components/insights/regular/RegularInsight.vue`.
-   **Static Content Pages:** `frontend/pages/about.vue`, `frontend/pages/privacy.vue`, `frontend/pages/return-refund.vue`, `frontend/pages/shipping.vue`, `frontend/pages/terms.vue`, `frontend/pages/dashboard/faq.vue`.
-   **Home Page Sections:** `frontend/components/home/AffiliateBenefitsSection.vue`, `frontend/components/home/EnhancedFeaturesSection.vue`.
-   **Career Page Content:** `frontend/pages/career/index.vue` (for `heroHighlights`, `enhancedValues`, `enhancedLifeAtCompany`).

Implementing dedicated backend API endpoints for these static data sources would enable content to be managed dynamically, for example, through an admin panel, without requiring frontend code changes.

For more details on specific suggestions, refer to the "Static Data to API Conversion" section in the [Frontend API Integration Details](API_INTEGRATION.md) document.