# Combined Backend & Frontend Completion Plan

## Overall Goal
Complete the entire platform (backend + frontend) efficiently, with a focus on enabling frontend features, ensuring robust testing, and delivering a fully functional e-commerce and MLM experience.

## Strategy
The plan is structured in phases, prioritizing backend development that directly unlocks critical frontend functionalities. Each phase outlines the necessary backend tasks, the frontend features they enable, and the associated testing.

---

### Phase 1: Core MLM Backend Logic & Frontend Display (High Priority)

**Objective:** Implement the escrowed commission system on the backend and enable its display on the frontend dashboard.

#### Backend Tasks:

1.  **Implement `JoiningCommissionService`:**
    *   **Purpose:** Calculates the "Joining Commission" for upline members when a new user subscribes.
    *   **Logic:** `stages.price * upline_member->level->joining_bonus %`.
    *   **Action:** Creates `Incentive` records with `status = 'PENDING'`.
2.  **Implement `LevelCompletionService`:**
    *   **Purpose:** Checks if an upline member has met their `levels.team_member_limit` (downline requirement).
    *   **Action:** If complete, calls `PayoutBonusService`.
3.  **Implement `PayoutBonusService`:**
    *   **Purpose:** Handles the "Payout Phase" of the escrowed commissions.
    *   **Logic:** Sums all `PENDING` incentives for the user, credits their `Wallet`, creates a `Transaction`, updates `Incentive` statuses to `PAID_TO_WALLET`, and promotes the user's `level_id`.
4.  **Modify `NetworkService->addToNetwork()`:**
    *   **Purpose:** Orchestrates the calls to the new commission services.
    *   **Action:** After fixing the user's parent, it will call `JoiningCommissionService->recordPendingCommissions($this->record)` and `LevelCompletionService->checkUplineForCompletion($this->record)`.
5.  **Implement `downline_count` Cache:**
    *   **Purpose:** Optimize performance for checking level completion and displaying progress.
    *   **Action:** Add a `downline_count` column to the `users` table. Implement logic to increment this count for all upline members whenever a new user joins their downline.
6.  **Create Backend API Endpoints (for Frontend Display):**
    *   Endpoint to retrieve the logged-in user's current `level_id`, `level->team_member_limit`, and `downline_count`.
    *   Endpoint to retrieve the sum of `PENDING` incentives for the logged-in user.
    *   Endpoint to retrieve a detailed list of all `PENDING` and `PAID` incentives for the user.
    *   Endpoint to retrieve the user's unique referral link.

#### Frontend Features Enabled:

*   **Dashboard Display:**
    *   Display the user's current level and progress towards the next level (`downline_count` vs. `team_member_limit`).
    *   Show the total sum of `PENDING` incentives (potential Level Completion Bonus).
    *   Display the user's unique referral link.
*   **Commission History Page:** A dedicated page to view all `PAID` and `PENDING` incentives with details.
*   **MLM Tree Visualization:** Basic display of direct downline members.

#### Testing:

*   **Backend:** Unit tests for `JoiningCommissionService`, `LevelCompletionService`, `PayoutBonusService`. Feature tests for the `NetworkService` integration and the new API endpoints.
*   **Frontend:** Unit tests for new composables. E2E tests for displaying dashboard stats and commission history.

---

### Phase 2: E-commerce Backend & Frontend Refinement (Medium Priority)

**Objective:** Enhance the core e-commerce experience with a robust checkout flow.

#### Backend Tasks:

1.  **Checkout Process APIs:** Ensure all necessary APIs for a multi-step checkout are robust and well-tested (e.g., applying coupons, calculating shipping, processing payment).

#### Frontend Features Enabled:

*   **Dedicated Checkout Flow:** Implement a clear, multi-step checkout process (e.g., shipping, payment, review).
*   **Order Confirmation/Success Page:** A dedicated page to display order details after a successful purchase.

#### Testing:

*   **Backend:** Feature tests for the entire checkout API flow.
*   **Frontend:** E2E tests for the complete checkout process.

---

### Phase 3: Remaining Features & Polish (Lower Priority)

**Objective:** Address any remaining features, UI/UX polish, and performance optimizations.

#### Backend Tasks:

*   Implement any remaining backend logic not directly tied to immediate frontend needs (e.g., advanced reporting, admin tools).

#### Frontend Tasks:

*   Advanced MLM tree visualization tools.
*   Ongoing UI/UX refinements, accessibility improvements.
*   Continuous performance monitoring and optimization.
*   Cross-browser and device responsiveness testing.

#### Testing:

*   Comprehensive E2E testing for all major user flows.
*   Performance testing.

---
