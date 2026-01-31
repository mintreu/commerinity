# Affiliate / MLM (Current Rules)

## Eligible Users
- MLM commissions apply only to **Member** and **Promoter**.
- Active subscription required to earn BV/PV/Rewards.
- If no active subscription: BV/PV/Rewards go to company fund/unclaimed.
- Regular/Advisor/Mentor can only earn **Rewards** (future game system).

## Advisor & Mentor
- Advisor + Mentor are company staff (commission/salaried).
- Advisor can:
  - Create team heads (parent_id = null)
  - Onboard users (KYC/address) and subscribe on their behalf
  - Set parent via referral code / parent_id
- **Advisor income**: monthly total confirmed sales volume of teams they created (originator tracking, not MLM tree commission).
- Mentor: training/event based (not implemented now).

## Commission Engine
- CommissionProcessorService orchestrates calculators.
- Calculators: sponsor bonus, level commission, originator joining, originator recurring, task completion.
- Order commissions use BV as base amount.

## Gaps
- Order commission context lacks stage/level.
- Purchase commissions may not be persisted due to async calc only.

## Files
- `apiserver/app/Services/Affiliate/*`
- `apiserver/app/Models/Affiliate/*`
- `apiserver/app/Services/Membership/SubscriptionService.php`
- `apiserver/app/Models/Ecommerce/Order.php`
