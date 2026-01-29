# Architectural Breaks / Deviations

Last updated: 2026-01-29 00:12

## API contract mismatches
- Cart endpoints mismatch between frontend and backend (PATCH vs PUT; POST /clear vs DELETE /cart).

## Security and environment concerns
- Public debug auth flow route present in production routes.
- Public checkout route without auth or signature guard (commented as “bad thing, not secured”).
- Transaction actions missing signature verification.

## Plan vs implementation
- `docs/README.md` and `plans/*` present a multi‑phase “fresh rebuild” plan; actual codebase contains partial implementations across many domains, with test coverage far below the target 80%+.

