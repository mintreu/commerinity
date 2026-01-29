# Project Status & Outstanding Work

This status page collects the known gaps, bugs, and next steps we have identified while working on the Nuxt + Laravel monorepo. It synthesizes the analyses already captured under `plans/frontend_analyze_report.md`, `plans/FRONTEND_CHECKLIST.md`, and the recent lint/typecheck runs so you can pick up the story in future sessions.

## 1. Completion snapshot
- **Frontend (client/):** Nuxt 4 SPA with public shop, authentication, wallet, helpdesk, network, and onboarding flows. Key UX areas (shop filters, product cards, reward ribbons) are wired up, and the Sanctum auth + MLN-based cart/checkout pipelines exist.
- **Backend (apiserver/):** Laravel 10 API with Sanctum, Filament admin, Money/payout services, catalog controllers, and product stock/price managers.
- **Quality tooling:** ESLint + TypeScript (npm run lint/typecheck) across `client/`, PHPStan/Pint/Insights expected for backend, and Vitest for flow tests.

## 2. Known issues (source: `plans/frontend_analyze_report.md`)
1. **Wallet PIN / reset flows** – payload mismatch, backend contract violations, and weak typing for security questions (missing string keys). These break lint/typecheck and risk runtime errors.
2. **Wallet & onboarding input handlers** – repeated `pinInputs.value[index]` guards. Need defensive checks so keyboard navigation doesn’t throw.
3. **Sanctum config** – Nuxt module defines `globalMiddleware`, which the current package doesn’t support, causing type errors.
4. **Flow tests & lint** – `npm run lint` reports ~200 existing violations (unused vars, `any`, style rules). These block automated validation and should be batched for a dedicated cleanup sprint.

## 3. Tasks to finish the project end-to-end
1. **Stabilize wallet/type safety**
   - Align `reset-pin` and `security question` flows with backend contracts (key names, payload shapes).
   - Guard all PIN input handlers against undefined inputs.
   - Replace broad `any` usage in wallet tests with typed fixtures.
2. **Clean up lint/type errors**
   - Run `npm run lint -- --rule` only on changed files to narrow scope, then gradually fix the top offenders listed in `plans/frontend_analyze_report.md` (wallet, network, onboarding pages).
   - Address stylistic issues flagged in `client/tests/*` flows; remove redundant quotes/comma mistakes.
3. **Finish mobile/desktop shop UX**
   - Ensure `<StoreProductFilters>` uses API data in both desktop sidebar and mobile drawer (already in-progress).
   - Polish price range slider + chips to show rupee formatted values (done) and remove old tag-based quick ranges.
   - Verify cart add/order flows work when authenticated and show `View Product` for guests.
4. **Validate backend-client contracts**
   - Re-run API endpoints (`/api/catalog/products`, `/api/catalog/filters`) to confirm expected payload shapes and schema alignments (enforce unique slug usage without exposing ID).
   - Add regression tests for `toRupee`/`priceRange` conversions if missing.
5. **QA checklist**
   - Execute `npm run test` (flows) once lint/type errors are cleaned.
   - Confirm backend Sanctum flow works using `composer test` or `php artisan test`.
   - Capture screenshots (mobile/desktop) for filters, cart, and checkout to detect layout regressions.

## 4. If you need to continue here
Copy this file into your `.codex` workspace note so future assistants can immediately know the project standing. Update it whenever a large module ships or a new set of blocking issues appears.
