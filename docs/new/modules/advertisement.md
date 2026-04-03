# Advertisement

## Purpose
Placement-based ad serving with click tracking.

## Routes
- Group start: `apiserver/routes/api.php:486`
- Page aggregate: `:490`
- Placement: `:491`
- Placement+block: `:492`
- Click tracking: `:493`

## Controller
- `AdvertisementController.php:34` for placement
- `AdvertisementController.php:79` for placement+block
- `AdvertisementController.php:123` for page aggregation
- `AdvertisementController.php:204` click record

## Frontend
- `client/app/composables/useAdvertisements.ts`
- `client/app/components/ads/*`

