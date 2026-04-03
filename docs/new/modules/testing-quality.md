# Testing & Quality

## Backend Tests
- Feature: `apiserver/tests/Feature/*`
- Unit: `apiserver/tests/Unit/*`

## Frontend Tests
- API contract style tests: `client/tests/api/*`
- User-type journey flows: `client/tests/flows/*`
- Browser automation: `client/tests/playwright/*`

## Quality Strategy
- Keep payment/sms/mail providers mocked in tests.
- Preserve role-based access assertions across user types.
- Maintain regression tests around checkout, wallet, affiliate commissions.

## Known Gaps
- See `docs/new/91-feature-gaps-trace.md` for TODO/incomplete logic references.

