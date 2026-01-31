# Order Flow (Create -> Confirm -> Return/Refund)

## Current Flow
- CartService validates + allocates stock FIFO.
- OrderService creates order + order_items + consumes stock.
- PaymentCompleted listener routes to OrderValidationService.
- **Return window**: order should be marked successful only after return window ends.

## Commission Timing (desired)
- Payment complete -> order confirmed
- After delivery + return window end -> order completed -> commissions applied

## Known Gaps
- OrderValidationService triggers commission calculation but does NOT persist (persistImmediately=false).
- Inconsistent commission timing between OrderValidationService and OrderService.
- Return/refund + reverse commission needs full test coverage.

## Target State
- Single consistent commission trigger point (COMPLETED after return period).
- Return/refund should reverse commission safely.
- E2E tests: create -> pay -> confirm -> deliver -> complete -> refund.

## Files to align
- `apiserver/app/Services/Ecommerce/OrderService.php`
- `apiserver/app/Services/Ecommerce/OrderService/OrderValidationService.php`
- `apiserver/app/Listeners/Payment/HandlePaymentCompleted.php`
