# Recruitment & Career

## Purpose
Lead capture and application pipeline with payment-enabled application journey.

## Primary Flow
- Public careers browse: `apiserver/routes/api.php:378-382`
- Auth apply/check/my applications: `:234-242`
- Controller: `RecruitmentController.php:37,53,73,89,136,169,196`

## Frontend
- `client/app/pages/career/index.vue`
- `client/app/pages/career/[slug]/index.vue`
- `client/app/pages/career/[slug]/apply.vue`
- `client/app/pages/career/applications/*`

## Tests
- `apiserver/tests/Feature/Api/RecruitmentTest.php`
- `apiserver/tests/Feature/Imports/*`
- `client/tests/career.api.test.ts`

