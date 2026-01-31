# SMS & Notifications

## Current Thought
- There is sms_providers table/model and sms logs.
- Possible refactor: use Integration model with sms type constant + cast.
- Decision pending.

## Notifications
- Target system: SMS + Email + Push + Toast.

## Files to inspect
- `apiserver/app/Models/*Sms*`
- `apiserver/app/Services/IntegrationServices/*`
- `apiserver/app/Notifications/*`
