# .codex workspace

Purpose: keep a concise, factual project memory and session state to prevent hallucinations and over‑engineering.

## Files
- `PROJECT_SNAPSHOT.md` : Current, high‑level understanding of the system (facts only).
- `LAST_SESSION.md` : What was done in the most recent session (date‑stamped).
- `ACTIVITY_LOG.md` : Append‑only running log of work items and decisions.
- `OPEN_QUESTIONS.md` : Unknowns that should be clarified before changes.
- `ASSUMPTIONS.md` : Explicit assumptions that must be verified.
- `SOURCES.md` : Files and locations used to build the snapshot.
- `MEMORY.md` : Consolidated memory used by the assistant (derived from the above).

## Update rules
- Add only verified facts from files, code, or user statements.
- When unsure, move it to `OPEN_QUESTIONS.md` or `ASSUMPTIONS.md`.
- Keep notes short; avoid speculative design or future plans unless asked.
