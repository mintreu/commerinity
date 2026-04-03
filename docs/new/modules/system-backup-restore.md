# System Backup & Restore

## Current State (Branch Snapshot)
- `apiserver/app/Services/Backup/Contracts` exists.
- Full backup service/page/commands are not found in this branch snapshot.

## Expected Business Need
- Full DB snapshot + media files
- Restore with safety checks and admin authorization
- Scheduled retention policy

## ? Action Required
- Verify merge/branch alignment if backup tool is expected in production.
- If missing, create service + command + admin page with test coverage.

