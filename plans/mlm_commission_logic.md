# MLM Commission and Level Progression Logic

This document outlines the business logic for the MLM commission system, which is based on a "Level Completion Bonus" model.

## 1. System Type

The commission system is **not** a standard per-transaction MLM model. It is a **Level Completion Bonus Model**. This means that commissions are not paid for every new subscription or purchase. Instead, a lump-sum bonus is paid to a user only when they complete the requirements for their current structural level.

## 2. Key Concepts

- **Structural Levels (ID 1-12):** This is a user's permanent rank within the system's 3 Stages and 12 Levels. All commission and progression rules are based on this structural level.
- **Achievement Ranks (e.g., Bronze, Silver):** These are separate, temporary (e.g., monthly) ranks based on performance. They are not directly used for the core level completion logic.
- **Depth:** This is the number of steps in the referral tree between two users (e.g., a direct referral is `depth: 1`). It is calculated by traversing the `parent_id` relationships, not by subtracting level IDs.

## 3. Level Progression Logic

- A user progresses from one level to the next by recruiting a specific number of subscribed members into their downline.
- The requirement for each level is stored in the `team_member_limit` column of the `levels` table.
- The progression follows a `5^n` pattern. For example:
    - To complete Level 1, a user needs 5 members.
    - To complete Level 2, a user needs 25 members.
    - To complete Level 3, a user needs 125 members.

## 4. Commission & Bonus Logic

- The core reward is a **lump-sum bonus** paid to a user upon completing their current level's `team_member_limit`.
- This bonus amount will be stored in a new `completion_bonus` column on the `levels` table.
- When a user's downline count meets or exceeds the `team_member_limit` for their current level, the system will:
    1. Create a single `Incentive` record for the `completion_bonus` amount.
    2. Promote the user to the next `level_id`.

## 5. User-Facing Display (Dashboard)

To keep users motivated, their dashboard should display their progress towards their next level completion bonus. This requires showing:
- **Current Level:** The name of their current level (e.g., "Level 1").
- **The Goal:** The value from `levels.team_member_limit`.
- **Current Progress:** A real-time count of their active downline members.
- **Potential Reward:** The value from the new `levels.completion_bonus` column.
