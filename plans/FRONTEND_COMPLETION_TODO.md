# Frontend Completion Plan (Nuxt Client)

This document provides a comprehensive to-do list for completing the Nuxt frontend application located in the `/client` directory. The goal is to move from the current state (mock data in components) to a fully functional, API-driven application.

---

## Phase 1: Core Infrastructure & Setup

This phase focuses on building the foundational pieces required for a robust and scalable frontend.

-   [ ] **1.1: Centralized API Client Setup**
    -   **Info:** Create a dedicated utility for handling API requests (e.g., using Nuxt's built-in `ofetch`).
    -   **Action:** Configure a base client in `client/utils/api.ts` or similar. It should automatically include the API base URL (`/api`), handle authentication tokens (if any), and standardize error handling.

-   [ ] **1.2: Implement State Management (Pinia)**
    -   **Info:** The dashboards will handle a lot of shared data (user info, stats, lists). Pinia is the official state management library for Vue and is ideal for this.
    -   **Action:** Install Pinia (`npm install pinia @pinia/nuxt`). Create stores for `auth` (to manage user data) and for each dashboard module (`advisor`, `promoter`, `mentor`).

-   [ ] **1.3: Authentication Flow**
    -   **Info:** The application currently lacks login/logout functionality. This is a top priority.
    -   **Action:**
        -   Create `LoginPage.vue` and `RegisterPage.vue`.
        -   Implement the API calls in the `auth` store to handle login and registration.
        -   Create Nuxt middleware (e.g., `client/middleware/auth.global.ts`) to protect dashboard routes and redirect unauthenticated users to the login page.

---

## Phase 2: Implement Dashboard Modules (from Mock to Live)

This phase involves activating the existing dashboard components with live data and building out the missing pages.

-   [ ] **2.1: Advisor Module (`DashboardAdvisor.vue`)**
    -   **Fix:** Replace hardcoded appointment data.
    -   **Action:** In the `advisor` store, create actions to fetch stats and appointments from the backend (`GET /api/dashboard/advisor` or `GET /api/appointments`).
    -   **Build:**
        -   Create `client/pages/appointments/index.vue`: A page to list all appointments with search and filter capabilities.
        -   Create `client/pages/appointments/new.vue`: A form to create a new appointment (`POST /api/appointments`).

-   [ ] **2.2: Promoter Module (`DashboardPromoter.vue`)**
    -   **Fix:** Replace hardcoded challenge data.
    -   **Action:** In the `promoter` store, create an action to fetch active challenges from `GET /api/challenges/active`.
    -   **Build:**
        -   Create `client/pages/challenges/index.vue`: A page showing all available, active, and completed challenges.
        -   Create `client/pages/challenges/[id].vue`: A detail page for a single challenge.

-   [ ] **2.3: Mentor Module (`DashboardMentor.vue`)**
    -   **Fix:** Replace hardcoded program and mentee data.
    -   **Action:** In the `mentor` store, create actions to fetch stats, active programs, and top mentees from endpoints like `GET /api/dashboard/mentor`.
    -   **Build:**
        -   Create `client/pages/programs/index.vue`: List all programs created by the mentor.
        -   Create `client/pages/programs/new.vue`: A form for mentors to create a new program (`POST /api/programs`).
        -   Create `client/pages/programs/[id].vue`: A detail page for a program, showing its curriculum and enrolled mentees.
        -   Create `client/pages/mentees/index.vue`: A page to list all mentees under the current mentor.

---

## Phase 3: UI/UX Refinements & General Fixes

This phase is about improving the user experience and code quality.

-   [ ] **3.1: Create Reusable UI Components**
    -   **Info:** The dashboards share common UI elements (stat cards, progress bars, lists).
    -   **Action:** Identify and extract these into reusable components in `client/components/common/` to ensure consistency and reduce code duplication.

-   [ ] **3.2: Implement Loading & Error States**
    -   **Fix:** The UI currently doesn't handle API loading or error states gracefully.
    -   **Action:** For every API call, implement a loading state (e.g., show a skeleton loader or spinner). If an error occurs, display a user-friendly message (e.g., "Could not load challenges. Please try again.").

-   [ ] **3.3: Ensure Responsiveness**
    -   **Fix:** The current components may not be fully responsive.
    -   **Action:** Test and fix all dashboard components and newly created pages on various screen sizes (mobile, tablet, desktop).

---

## Phase 4: Testing & Finalization

-   [ ] **4.1: Component & Unit Testing**
    -   **Info:** Add tests to ensure components render correctly and functions work as expected.
    -   **Action:** Use `vitest` to write tests for critical UI components and state management logic (Pinia stores).

-   [ ] **4.2: End-to-End (E2E) Testing (Optional but Recommended)**
    -   **Info:** Simulate real user flows to catch integration issues.
    -   **Action:** Use a tool like Cypress or Playwright to test key user journeys (e.g., login -> create program -> view mentees).

-   [ ] **4.3: Final Build & Review**
    -   **Action:** Run `npm run build` to ensure the project builds without errors. Perform a final round of manual testing in the production-like environment.
    -   **Action:** Review and remove all remaining mock data and `// TODO` comments from the codebase.
