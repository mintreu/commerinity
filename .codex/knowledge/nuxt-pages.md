# Nuxt Client Pages (Public Site)

## Pricing Display Requirements
- Product price shown on client must come from **correct stock** (location-aware) and match backend cart/order pricing.
- If sale applicable, show **MRP**, **sale price**, **current price**, and **discount** (Flipkart-style breakdown).
- Multiple applicable sales should be resolved consistently and displayed with proper breakdown.
- Cart -> order must keep **exact same price** used in product listing/detail.
- Coupon/Voucher should apply on cart totals (already implemented server-side; verify client mirrors it).

## Location & FIFO
- Location-based stock price selection must be visible in UI (nearest warehouse first for authenticated users).
- FIFO rules apply after location resolution.
- Different warehouse locations may have different prices; UI should reflect the selected stock price.

## India-first, Global-ready
- Pricing, tax, and shipment flows should follow India standards now, but remain adaptable for future global expansion.

## Priority
- Must finish public-facing flows for user types:
  - Guest, Regular, Member, Promoter, Advisor, Mentor
- Wallet withdrawal flow needs complete UX
- Order view page incomplete
- Blogs + some pages still pending

## Files
- `client/app/pages/*`
- `client/app/components/*`
