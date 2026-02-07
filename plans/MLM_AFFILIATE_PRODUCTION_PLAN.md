# MLM / Affiliate Production Plan (Commerinity)

## Goal
Ship a production-grade affiliate system (Vestige/RCM style) with BV/PV, commissions, pending/confirmed lifecycle, wallet payouts, optional funds split, and full test coverage.

## Current Implementation Snapshot (What Exists)
- **Commission Orchestrator**: `apiserver/app/Services/Affiliate/CommissionProcessorService.php` with calculators (sponsor bonus, level commission, originator joining/recurring, task completion).
- **Genealogy Tracking**: `apiserver/app/Models/Affiliate/AffiliateGenealogy.php` tracks personal/team **PV** and sales volumes; propagates PV up the upline in `addSales`.
- **Order BV/PV**: `apiserver/app/Models/Ecommerce/Order.php` has `total_bv`, `total_pv`; `OrderItem` has `bv`, `pv`. Cart calculates BV/PV for member/promoter only.
- **Events**: `PaymentCompleted` and affiliate commission events already wired for subscription/order flow.
- **Tests**: Core MLM journey tests exist, but ecommerce purchase BV/PV + payout pipeline lacks end‑to‑end tests and production-grade assertions.

## Gaps To Close
1. **BV tracking** in genealogy (only PV exists). BV should mirror PV’s pipeline or be its own tracked volume.
2. **Pending vs confirmed volumes**: PV/BV should be pending until return window completes (or explicit admin confirmation).
3. **PV/BV → wallet payout**: clarify conversion formula and scheduling (weekly/monthly) and handle TDS.
4. **Fund allocations**: optional split of payout (wallet vs fund buckets).
5. **E2E tests**: multi-user, multi-level, order completion/return/refund, payout scheduling.

## Proposed Data Model Adjustments
### A) Volume ledger (recommended)
Add a dedicated ledger to track BV/PV flow and status:
```
affiliate_volumes (
  id,
  user_id,
  source_type, source_id,
  bv, pv,
  status: pending|confirmed|reversed,
  eligible_at (return window end),
  created_at, updated_at
)
```
Then maintain rolling counters in genealogy for fast read:
- `pending_personal_pv`, `pending_team_pv`, `confirmed_personal_pv`, `confirmed_team_pv`
- same for BV

### B) Minimal change (if no new table)
Add BV fields + pending fields on genealogy:
- `personal_bv`, `team_bv`, `pending_personal_bv`, `pending_team_bv`, `pending_personal_pv`, `pending_team_pv`
Then add a job to move pending → confirmed after return window.

## PV/BV Lifecycle
1. Order paid → PV/BV logged as **pending** for buyer + upline.
2. On return window expiry (or explicit admin confirm):
   - Pending → confirmed
   - Trigger commission eligibility / payout pipeline.
3. If refund/return occurs before eligibility:
   - Reverse pending volumes and related commissions (if any).

## Payout Strategy (Vestige/RCM style)
### Weekly/Monthly payout
- Summarize **confirmed** BV/PV for period.
- Apply conversion rate (config-driven).
- Deduct TDS (if applicable).
- Credit wallet or fund buckets.

### Optional Fund Split
Config-based split example:
```
pv_to_wallet_percent = 70
pv_to_fund_percent = 30
fund_buckets = ["housing", "travel", "welfare"]
```
Funds stored in a simple table:
```
affiliate_funds (user_id, type, balance)
```
Keep split on payout only, not at volume tracking time.

## Conversion Rules
- **Config** in `config/affiliate.php`:
  - `pv_to_paisa_rate`, `bv_to_paisa_rate` (int, per 100 PV/BV).
  - `payout_frequency` (weekly/monthly).
  - `payout_minimum` (threshold).
  - `fund_split` (percentages).

## Required Services
1. `AffiliateVolumeService`
   - recordPendingVolumes(order)
   - confirmEligibleVolumes()
   - reverseVolumes(source)
2. `AffiliatePayoutService`
   - summarizeConfirmedVolumes(period)
   - computePayout(user)
   - createWalletTransaction(user, amount)
   - applyFundSplit(user, amount)
3. `AffiliateRefundService`
   - reverse pending volumes
   - reverse commissions if already paid (creates negative commission records)

## API Surface (Backend)
- `GET /api/v1/affiliate/volumes` (summary: pending/confirmed BV/PV)
- `POST /api/v1/affiliate/convert` (manual conversion request)
- `GET /api/v1/affiliate/payouts` (history)
- Admin endpoints to approve/reject payouts and handle disputes

## Testing Plan (Must Pass)
### Core
- Multi-user genealogy (depth 4) + commission distribution.
- Order flow:
  - BV/PV pending creation on payment
  - pending → confirmed after return window
  - refund before confirm reverses volumes + commission
  - refund after confirm creates reversal commission

### Conversion
- PV/BV conversion to wallet using config rate.
- Fund split accuracy.
- Minimum payout threshold.
- TDS deduction (if enabled).

### API
- Eligibility checks
- Permission enforcement
- Idempotency and double‑payout protection

## Implementation Phases
1. **Model updates + ledger** (BV tracking + pending/confirmed).
2. **Volume service + events** (order paid/return/confirm).
3. **Payout service + config** (wallet/fund split + scheduler).
4. **APIs + Filament admin** (payout approval).
5. **Tests** (feature + service + refund reversal).

## Definition of Done
- All volume transitions are deterministic and auditable.
- Full e2e tests cover multi-level BV/PV with returns/refunds.
- Wallet balances reconcile with confirmed volumes.
- Payout schedule and TDS logic correct and verified.

