# Pending Implementation Notes (from user)

## FAQs (Nuxt client + admin)
- Personalized FAQ per user (client-side).
- Admin FAQ management.
- Public FAQ entry from footer CTA.

## SMS providers
- There is a sms_providers table/model and sms log/other sms model.
- Consider collapsing provider table into Integration model with a "sms" type constant + cast class (similar to existing casts), since max providers ~10.
- Decision pending: keep sms_providers vs reuse integrations.

## Client-side gaps
- Blogs and some pages still not ready in Nuxt.
- Order view page in Nuxt not fully working.

## Ecommerce testing gaps
- End-to-end tests for product -> order confirm -> return/refund not solid yet; needs ~1 week to stabilize.

## Business context
- Multi-warehouse stock (no multi-vendor).
- originator_id/type on User used for Advisor flows (track subscriptions/sales by advisor).
- Advisor can pay subscription for team member via wallet or payment link (Cashfree).
- Promotion to Promoter when a member pays for team subscription (wallet/online).
- Payout flow not fully ready; needs help.

## Cashfree MCP files present
- `apiserver/.mcp-servers/cashfree.json`
- `apiserver/.env.cashfree-mcp.example`
