# Content, Search, FAQ, Helpdesk

## Purpose
Organic acquisition + support lifecycle.

## Routes
- Contact inquiry: `apiserver/routes/api.php:387-390`
- FAQ: `:425-431`
- Global search: `:436`
- Blogs/news: `:441-453`
- Helpdesk (auth): `:458-464`

## Frontend
- `client/app/pages/faq.vue`, `search.vue`, `contact.vue`
- `client/app/pages/blogs/*`, `news/*`
- `client/app/pages/helpdesk/*`

## Tests
- `apiserver/tests/Feature/Api/GlobalSearchControllerTest.php`
- `apiserver/tests/Feature/Api/HelpdeskModelTest.php`
- `client/tests/e2e/content-pages.e2e.test.ts`

