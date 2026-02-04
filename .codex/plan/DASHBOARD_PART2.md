# Dashboard Part 2 (Advisor/Promoter/Mentor) — Execution Plan

## Scope
- Implement **appointments**, **challenges**, **programs** end-to-end (backend + frontend + tests + notifications).
- **Mentees** is on hold.
- Add **Advisor → Add TeamLeader** flow (create user + address + KYC + beneficiary + avatar).
- Avoid numeric IDs in client/API; use **uuid** for new models.
- Do **not** add new migrations; edit existing migrations (pre-deploy phase).

## Backend (Laravel)
1) **Migrations (edit existing)**
   - `appointments`, `challenges`, `programs` tables:
     - add `uuid` (unique), creator/user relations, status, timing fields, etc.
2) **Models**
   - Add `fillable`, `casts`, `getRouteKeyName()` to use `uuid`.
   - Add `booted()` to auto-generate uuid via `HasUnique`.
   - `Program` uses `HasAddress` (polymorphic addresses).
3) **Controllers + Routes**
   - `AppointmentController`:
     - `GET /api/appointments`, `POST /api/appointments`, `GET /api/appointments/{uuid}`
     - Enforce advisor originator rule on create.
   - `ChallengeController`:
     - `GET /api/challenges`, `GET /api/challenges/active`, `GET /api/challenges/{uuid}`
   - `ProgramController`:
     - `GET /api/programs`, `POST /api/programs`, `GET /api/programs/{uuid}`
   - `AdvisorTeamLeaderController`:
     - `POST /api/advisor/team-leaders` (create user + address + KYC + beneficiary + avatar).
4) **Resources**
   - `AppointmentResource`, `ChallengeResource`, `ProgramResource`
5) **Notifications**
   - Use `GeneralNotification` for:
     - appointment created → attendee + advisor/mentor
     - program created → target team if applicable (or creator + participants)
     - team leader created → new user (welcome) + advisor

## Frontend (Nuxt)
1) **Missing Pages**
   - `/appointments`, `/appointments/new`
   - `/challenges`, `/challenges/[uuid]`
   - `/programs`, `/programs/new`, `/programs/[uuid]`
2) **Dashboard Wiring**
   - Advisor: real appointments list + Add TeamLeader CTA + stats.
   - Promoter: real challenges list.
   - Mentor: real programs list.
3) **UX**
   - Loading + error states for all APIs.
   - Mobile-first drawer patterns.

## Tests
- **Pest**: API coverage for new endpoints (CRUD + rules).
- **Vitest**: component render + API state tests.
- **Playwright**: E2E flows (advisor creates team leader, schedules appointment, mentor program list, promoter challenges).

## Deliverables
- All endpoints live, no mock data.
- Dashboard pages use real API.
- Notifications delivered (database + optional push).
- Tests passing.
