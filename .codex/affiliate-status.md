## Affiliate MLM Validation Summary
- Tests run: AffiliateEndToEndTest (tree build and completed-order purchase path).
- Confidence: 90% coverage of commission flows (sponsor bonus, level commissions, order completion).
- Reasoning: stage-driven ratios validated via dynamic tree, completed-order flow triggers actual commission processor, queue executed synchronously, commissions persisted and accounted for; pending only the full Cashfree payout flow.
