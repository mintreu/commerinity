# Frontend Completion Plan: Nuxt.js Client

## Overall Goal
Complete the Nuxt.js client with a fully working backend, prioritizing efficiency, testability, and a smooth user experience for both e-commerce and MLM functionalities.

## Current State Summary
The Nuxt.js frontend is in a very strong and substantially complete state for core e-commerce functionalities, user authentication, and a comprehensive user dashboard. It provides an excellent and well-organized foundation for further development.

## Key Areas for Development & Refinement

### 1. MLM & Dashboard Features (High Priority)

This section focuses on integrating and displaying the escrowed commission and level progression logic.

*   **Completed/Existing Components & Pages:**
    *   `pages/dashboard/index.vue` (Dashboard homepage)
    *   `pages/dashboard/myteam.vue` (Team/Network view)
    *   `pages/dashboard/members/index.vue` (Members list)
    *   `pages/dashboard/wallet/index.vue` (Wallet overview)
    *   `pages/dashboard/wallet/transactions.vue` (Wallet transaction history)
    *   `pages/dashboard/subscribe.vue` (Subscription page)
    *   `components/insights/member/MemberInsight.vue` (User-specific insights)
    *   `components/dashboard/cards/DashboardStatCard.vue`, `StatsCounter.vue` (Displaying key statistics)
    *   `components/timelines/MemberTimeline.vue` (User activity timeline)

*   **To Do/Implement:**
    *   **Display Pending Commissions:** Show the total sum of `PENDING` incentives for the logged-in user.
    *   **Display Level Completion Progress:** Clearly show the user's current downline count versus the `team_member_limit` for their current level.
    *   **Display Potential Level Completion Bonus:** Show the total sum of `PENDING` incentives that will be paid out upon completing the current level.
    *   **Commission History Page:** A dedicated page/section to view all `PAID` and `PENDING` incentives with details (source, amount, date).
    *   **Referral Link/Sharing UI:** A section where users can easily find and share their unique affiliate link.
    *   **MLM Tree Visualization:** A more interactive and detailed visualization of the user's downline tree.

*   **Implementation Plan:**
    1.  **Backend API Endpoints:** Create new backend API endpoints to expose the necessary data:
        *   User's current level, `team_member_limit`, and `downline_count`.
        *   Sum of `PENDING` incentives for the user.
        *   Detailed list of all `PENDING` and `PAID` incentives.
        *   User's unique referral link.
    2.  **Nuxt.js Pages/Components:**
        *   Modify `pages/dashboard/index.vue` or create new dashboard components to display pending commissions, level progress, and potential bonus.
        *   Create `pages/dashboard/commissions/index.vue` for the detailed commission history.
        *   Modify `pages/dashboard/myteam.vue` or create new components for enhanced MLM tree visualization and referral link sharing.
    3.  **Nuxt.js Composables:** Develop new composables (e.g., `useMlmStats.ts`, `useCommissions.ts`) for fetching, caching, and managing this data from the backend APIs.

### 2. E-commerce Features (Medium Priority)

*   **Completed/Existing Components & Pages:**
    *   Product listings, product detail pages, cart functionality are largely in place.
    *   `useCart.ts` composable.

*   **To Do/Implement:**
    *   **Dedicated Checkout Flow:** Implement a clear, multi-step checkout process (e.g., shipping, payment, review).
    *   **Order Confirmation/Success Page:** A dedicated page to display order details after a successful purchase.

*   **Implementation Plan:**
    1.  **Backend API Endpoints:** Ensure robust backend APIs for the checkout process (order creation, payment processing, address management).
    2.  **Nuxt.js Pages/Components:** Create `pages/checkout/index.vue` and sub-pages for each step of the checkout process. Implement `pages/order/success.vue` and `pages/order/failure.vue`.
    3.  **Nuxt.js Composables:** Develop composables for managing checkout state and interacting with checkout APIs.

### 3. User Management & Authentication (Low Priority - Mostly Complete)

*   **Completed/Existing Components & Pages:**
    *   Login, Register, Forgot Password, Account Profile, Settings, Onboarding are well-covered.

*   **To Do/Fix:**
    *   Ongoing minor UI/UX refinements or bug fixes as identified during testing.

### 4. General UI/UX & Performance (Ongoing)

*   **Completed/Existing Components & Pages:**
    *   A rich component library and utility composables are already present.

*   **To Do/Fix:**
    *   Continuous performance monitoring and optimization (e.g., lazy loading, image optimization).
    *   Thorough cross-browser and device responsiveness testing.
    *   Accessibility (A11y) audits and improvements.

## Backend Integration Strategy
*   **API-First Approach:** All frontend data requirements will drive the creation or modification of backend API endpoints.
*   **Performance Optimization:** Implement caching strategies (e.g., `downline_count` cache on `users` table) on the backend to ensure frontend responsiveness, especially for MLM-related stats.

## Testing Strategy
*   **Unit Tests:** Implement unit tests for all new and modified Nuxt.js composables and complex components.
*   **End-to-End (E2E) Tests:** Develop E2E tests for critical user flows, including:
    *   User registration and login.
    *   Subscription process (both wallet and external payment).
    *   Product browsing, adding to cart, and checkout.
    *   Display of MLM dashboard stats and commission history.
    *   Referral link sharing.

---
