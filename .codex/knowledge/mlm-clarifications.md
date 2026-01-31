# MLM/Affiliate Clarifications (updated)

## User types
- Guest
- Regular (general customer)
- Member
- Promoter
- Advisor (company staff)
- Mentor (company staff)
- Future: Distributor (after Promoter)

## Eligibility & earnings
- MLM commissions apply **only to Member + Promoter**.
- **Active subscription required** to earn BV/PV/Rewards.
  - If no active subscription, BV/PV/Rewards value goes to **company fund / unclaimed balance**.
- Regular/Advisor/Mentor can earn **rewards only** (future game-based rewards/coupons).

## Advisor & Mentor roles
- Advisor + Mentor are **company commissioned/salaried staff**.
- Advisor can:
  - Create team heads (parent_id = null) and help onboard their children.
  - Complete KYC/address and subscribe users on behalf.
  - Set parent via referral code / parent_id.
- Advisor income from **originator tracking** on total confirmed sales of teams they created (not MLM tree commission).
- Mentor is trainer; advisor can be mentor; admin can create advisor/mentor directly.

## Distributor (future)
- Distributor type after Promoter.
- Admin can appoint; advisor can recommend.
- May use separate Filament panel/model.
- Wholesale columns exist in ProductStock but distributor system not planned yet.

## Client priorities (live)
- Nuxt public site must be completed for user types: guest/regular/member/promoter/advisor/mentor.
- Wallet withdraw flow must be finished (salary/commission/refund -> wallet -> bank withdrawal).

