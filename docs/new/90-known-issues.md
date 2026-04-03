# 90 Known Issues / Gaps / Confusions

## Security / Access
- <span style="color:red;font-size:1.25em;"><strong>Public checkout endpoints exist without auth (`apiserver/routes/api.php:396-399`). Business intent may be valid for payment links, but threat-model and signed-access constraints must be explicitly documented.</strong></span>

## SMS / Communications
- <span style="color:red;font-size:1.25em;"><strong>SMS template governance is sensitive: DLT-approved OTP formats must remain immutable where declared final. Any mismatch between template slug mapping and runtime use can break delivery/compliance.</strong></span>
- <span style="color:red;font-size:1.25em;"><strong>Provider mode drift risk (quick mode vs DLT mode) can create hidden production failures even when API calls succeed.</strong></span>

## Data / Multi-project DB contamination
- <span style="color:red;font-size:1.25em;"><strong>If same DB server/schema hosts multiple projects, raw table-list based tooling can show unrelated tables. Documentation and admin tools should prefer model-scoped mappings.</strong></span>

## Backup/Restore Feature Presence
- <span style="color:red;font-size:1.25em;"><strong>Current branch snapshot: full backup service/page files are not present in code tree (only `app/Services/Backup/Contracts`). If expected in production, verify branch/merge state immediately before release.</strong></span>

## Frontend API Chattiness (Ads)
- <span style="color:red;font-size:1.25em;"><strong>Ad-loading can become over-chatty if each placement requests separately per view segment. A centralized aggregated endpoint/caching strategy is recommended to reduce redundant calls.</strong></span>

## Test Environment Drift
- <span style="color:red;font-size:1.25em;"><strong>External provider dependencies (mail/sms/webhook) can make tests flaky unless provider transport is mocked/faked for test/seeding runs.</strong></span>

