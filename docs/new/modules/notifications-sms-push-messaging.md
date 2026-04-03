# Notifications, SMS, Push, Messaging

## Purpose
Keep users informed (transactional notifications), enable engagement (push/messages), and compliance messaging (SMS).

## Primary Flow
- Notification routes: `apiserver/routes/api.php:126-132`
- Push routes: `:135-138`, VAPID public key at `:373`
- Message routes: `:300-310`
- Notification controller: `NotificationController.php:18,118,133,158,171`
- Push controller: `PushSubscriptionController.php:16,42,62`

## SMS Stack
- `SmsService.php:30,42,205`
- `Fast2SmsProvider.php:30,34`
- Template seeding: `database/seeders/SmsTemplateSeeder.php`

## Frontend
- `client/app/pages/notifications.vue`
- `client/app/pages/messages/*`
- push initialization in app shell/plugins

## Tests
- `apiserver/tests/Feature/Notifications/*`
- `apiserver/tests/Feature/Services/SmsServiceTest.php`

