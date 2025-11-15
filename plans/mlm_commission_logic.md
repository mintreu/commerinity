# MLM Commission and Level Progression Logic

This document outlines the business logic for the MLM commission system.

## 1. System Type: Escrowed Commissions with Milestone Payout

The system is a hybrid model that combines per-transaction commission calculation with milestone-based payouts.

1.  **Recording Phase (Per-Transaction):** When a new user subscribes, a "Joining Commission" is calculated for all eligible members in the upline. This commission is **not** paid immediately but is recorded as a `PENDING` incentive.
2.  **Payout Phase (Milestone):** When a user completes a level (by meeting their downline requirement), the system sums up all their `PENDING` incentives. This total sum constitutes their "Level Completion Bonus," which is then paid out to their wallet.

This means the `completion_bonus` column is **not needed**. The bonus is the dynamic sum of accumulated pending commissions.

## 2. Key Concepts

- **Structural Levels (ID 1-12):** A user's permanent rank within the 3 Stages / 12 Levels system. All rules are based on this.
- **Achievement Ranks (e.g., Bronze, Silver):** Separate, temporary (e.g., monthly) performance ranks. Not used in this core logic.
- **Depth:** The number of steps in the referral tree between two users, calculated by traversing `parent_id` relationships.

## 3. Level Progression Logic

- A user progresses to the next level by recruiting a specific number of subscribed members into their downline.
- The requirement for each level is stored in the `team_member_limit` column of the `levels` table (e.g., 5, 25, 125).

## 4. Commission & Payout Logic

### Recording Pending "Joining Commissions"
- **Trigger:** A new user successfully subscribes.
- **Calculation:** For each upline member, the commission is `stages.price * upline_member->level->joining_bonus %`.
- **Action:** An `Incentive` record is created for each upline member with the calculated `amount` and a `status` of `'PENDING'`.

### Paying Out the "Level Completion Bonus"
- **Trigger:** An existing user's downline count meets or exceeds their `level->team_member_limit`.
- **Calculation:** The system finds all `Incentive` records for that user with `status = 'PENDING'` and calculates the `sum('amount')`.
- **Action:**
    1. The total sum is transferred to the user's `Wallet`, and a corresponding `Transaction` is created.
    2. The status of all those pending `Incentive` records is updated to `'PAID_TO_WALLET'`.
    3. The user is promoted to the next `level_id`.

## 5. Event & Service Architecture

The entire process is orchestrated through a clean, event-driven flow:

1.  **Initial Event:** `Mintreu\LaravelTransaction\Events\TransactionConfirmed` is fired when any payment is verified.
2.  **Routing Listener:** The existing `HandleTransactionConfirmed` listener catches this event. If the payment is for a `UserSubscription`, it calls the `MembershipSubscriptionService->validate()` method.
3.  **Domain Event Dispatch:** Inside `MembershipSubscriptionService->validate()`, after the subscription is marked as paid and the user's level is set, a new, specific domain event is dispatched: `App\Events\SubscriptionPaid`.
4.  **Commission Listeners:** The `SubscriptionPaid` event is caught by two separate, independent listeners:
    - **`RecordPendingCommissions` (Listener):** Calls a `JoiningCommissionService` to execute the "Recording Phase" logic for the new user's upline.
    - **`CheckForLevelCompletion` (Listener):** Calls a `LevelCompletionService` to execute the "Payout Phase" logic by checking if anyone in the new user's upline has now met their level completion goals.

## 6. User-Facing Display (Dashboard)

To keep users motivated, their dashboard should display:
- **Current Level:** The name of their current level.
- **The Goal:** The value from `levels.team_member_limit`.
- **Current Progress:** A real-time count of their active downline members.
- **Potential Reward:** The sum of all their `Incentive` records where the `status` is `'PENDING'`.
