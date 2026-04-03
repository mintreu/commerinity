# Address System - Complete Implementation Plan

**Date**: 2025-12-09
**Status**: 🚧 IN PROGRESS
**Priority**: CRITICAL

## Overview

Building a complete, enterprise-grade, battle-tested address system for Commerinity Pro with:
- ✅ Backend API (Laravel 12 + Filament v4)
- ✅ Frontend UI (Nuxt 4 + Nuxt UI v4)
- ✅ Comprehensive testing (Pest v4 + Browser tests)
- ✅ Complete documentation

---

## Implementation Strategy

### Incremental Development Approach
1. Build backend (migrations, models, factories, seeders)
2. Write brutal tests (unit + feature + browser)
3. Build API endpoints with validation
4. Build Filament admin resources
5. Build frontend UI components
6. Test full integration (backend + frontend)
7. Optimize and deploy

### Testing Philosophy
- **Test-Driven Development (TDD)**
- Every feature has tests BEFORE marking complete
- 100% test coverage for critical flows
- No bugs tolerated - fix immediately

---

## Part 1: Database Layer (Backend)

### Step 1.1: Complete All Migrations

**Files to Edit:**
```
apiserver/database/migrations/2025_12_09_082709_create_countries_table.php
apiserver/database/migrations/2025_12_09_082714_create_states_table.php
apiserver/database/migrations/2025_12_09_082720_create_blocks_table.php
apiserver/database/migrations/2025_12_09_082725_create_addresses_table.php
```

**Already Created:**
- ✅ app/Models/Geo/Country.php
- ✅ app/Models/Geo/State.php
- ✅ app/Models/Geo/Block.php
- ✅ app/Models/Address.php
- ✅ database/factories/Geo/CountryFactory.php
- ✅ database/factories/Geo/StateFactory.php
- ✅ database/factories/Geo/BlockFactory.php
- ✅ database/factories/AddressFactory.php
- ✅ database/seeders/CountrySeeder.php
- ✅ database/seeders/StateSeeder.php
- ✅ database/seeders/BlockSeeder.php
- ✅ database/seeders/AddressSeeder.php

**Next Actions:**
1. ⬜ Complete migration schemas (strict types, indexes, foreign keys)
2. ⬜ Run migrations: `php artisan migrate`
3. ⬜ Verify database schema: `php artisan db:show`

---

### Step 1.2: Complete All Models

**Model Structure:**

```
app/Models/
├── Geo/
│   ├── Country.php
│   ├── State.php
│   └── Block.php
├── Address.php
└── User.php (update with addresses relationship)
```

**Requirements for Each Model:**
- ✅ `declare(strict_types=1)` at top
- ✅ Proper namespace
- ✅ Type hints for all properties
- ✅ Explicit return types
- ✅ Relationships with return types
- ✅ Scopes for common queries
- ✅ Casts for attributes
- ✅ Boot logic (if needed)

---

### Step 1.3: Complete All Factories

**Factory Requirements:**
- Realistic fake data
- Multiple states (home, office, warehouse, etc.)
- Support for relationships
- Follow existing conventions

---

### Step 1.4: Complete All Seeders

**Seeder Plan:**

1. **CountrySeeder**: India + top 10 countries
2. **StateSeeder**: All Indian states (JSON data)
3. **BlockSeeder**: Top 100 blocks for testing
4. **AddressSeeder**: Sample addresses for testing

**India Geo Data JSON:**
- User has India JSON data ready
- Import and seed all states
- Import and seed major blocks

---

## Part 2: Testing Layer (Backend)

### Step 2.1: Unit Tests

**Create:**
```
tests/Unit/Models/Geo/CountryTest.php
tests/Unit/Models/Geo/StateTest.php
tests/Unit/Models/Geo/BlockTest.php
tests/Unit/Models/AddressTest.php
```

**Test Coverage:**
- Model relationships work correctly
- Casts work correctly
- Scopes work correctly
- Boot logic works correctly
- Validation rules
- Edge cases

---

### Step 2.2: Feature Tests

**Create:**
```
tests/Feature/UserAddressTest.php
tests/Feature/GeoHierarchyTest.php
tests/Feature/AddressIsolationTest.php
```

**Test Cases (from Popkult):**
```php
// UserAddressTest.php
test('user can have multiple addresses')
test('user addresses are properly polymorphic')
test('setting default user address updates other user addresses only')
test('setting default user address does not affect standalone addresses')
test('setting default user address does not affect other users addresses')
test('user can get default address')
test('user default address returns null when no default set')
test('user can have different address types')
test('user addresses can be deleted')
test('user addresses persist when user deleted')
test('address scopes work correctly')
test('mixed ownership addresses maintain proper isolation')
test('user can filter addresses by type')
test('user addresses maintain data integrity')

// GeoHierarchyTest.php
test('address belongs to correct country state block')
test('block provides latitude longitude to address')
test('state filtering by country works')
test('block filtering by state works')
test('address full_address attribute formats correctly')
test('india geo data seeds correctly')

// AddressIsolationTest.php
test('warehouse addresses isolated from user addresses')
test('store addresses isolated from user addresses')
test('default address logic maintains isolation')
```

---

### Step 2.3: API Tests

**Create:**
```
tests/Feature/Api/GeoControllerTest.php
tests/Feature/Api/AddressControllerTest.php
```

**Test All Endpoints:**
- GET /api/geo/countries
- GET /api/geo/states/{country_code}
- GET /api/geo/blocks/{state_code}
- GET /api/addresses
- POST /api/addresses
- GET /api/addresses/{id}
- PUT /api/addresses/{id}
- DELETE /api/addresses/{id}
- PATCH /api/addresses/{id}/set-default

---

## Part 3: API Layer (Backend)

### Step 3.1: Create Controllers

**Commands:**
```bash
php artisan make:controller Api/GeoController --api --no-interaction
php artisan make:controller Api/AddressController --api --no-interaction
```

**Requirements:**
- `declare(strict_types=1)`
- Dependency injection
- Return type declarations
- API Resources for responses
- Proper error handling

---

### Step 3.2: Create Form Requests

**Commands:**
```bash
php artisan make:request Api/StoreAddressRequest --no-interaction
php artisan make:request Api/UpdateAddressRequest --no-interaction
```

**Validation Rules:**
- Use Laravel validation
- Custom error messages
- Conditional validation
- Check sibling requests for conventions

---

### Step 3.3: Create API Resources

**Commands:**
```bash
php artisan make:resource Geo/CountryResource --no-interaction
php artisan make:resource Geo/StateResource --no-interaction
php artisan make:resource Geo/BlockResource --no-interaction
php artisan make:resource AddressResource --no-interaction
```

**Requirements:**
- Consistent response format
- Hide sensitive fields
- Include relationships
- Money formatting (if applicable)

---

### Step 3.4: Define Routes

**File:** `apiserver/routes/api.php`

```php
// Public geo endpoints
Route::prefix('geo')->group(function () {
    Route::get('/countries', [GeoController::class, 'countries']);
    Route::get('/states/{country_code}', [GeoController::class, 'states']);
    Route::get('/blocks/{state_code}', [GeoController::class, 'blocks']);
});

// Protected address endpoints
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('addresses', AddressController::class);
    Route::patch('/addresses/{address}/set-default', [AddressController::class, 'setDefault']);
});
```

---

## Part 4: Admin Panel (Filament)

### Step 4.1: Create Filament Resources

**Already Created:**
- ✅ app/Filament/Resources/Geo/Countries/CountryResource.php

**Commands:**
```bash
php artisan make:filament-resource Geo/State --generate --no-interaction
php artisan make:filament-resource Geo/Block --generate --no-interaction
php artisan make:filament-resource Address --generate --no-interaction
```

**Requirements:**
- Proper form fields
- Table columns
- Filters
- Actions (bulk actions)
- Relation managers (if needed)
- Permissions/Policies

---

### Step 4.2: Test Filament Resources

**Create:**
```
tests/Feature/Filament/CountryResourceTest.php
tests/Feature/Filament/StateResourceTest.php
tests/Feature/Filament/BlockResourceTest.php
tests/Feature/Filament/AddressResourceTest.php
```

---

## Part 5: Frontend Layer (Nuxt 4)

### Step 5.1: Create Composables

**Files to Create:**
```
client/composables/useGeo.ts
client/composables/useAddress.ts
```

**useGeo.ts:**
```typescript
export const useGeo = () => {
  const fetchCountries = async () => { ... }
  const fetchStates = async (countryCode: string) => { ... }
  const fetchBlocks = async (stateCode: string) => { ... }

  return {
    fetchCountries,
    fetchStates,
    fetchBlocks,
  }
}
```

---

### Step 5.2: Create Types

**File:** `client/types/address.ts`

```typescript
export interface Country {
  id: number
  name: string
  iso_code_2: string
  iso_code_3: string
  isd_code: number
  currency: string
  flag: string | null
  is_active: boolean
}

export interface State {
  id: number
  name: string
  code: string
  country_id: number
}

export interface Block {
  id: number
  name: string
  url: string
  district_name: string | null
  state_code: string
  latitude: number | null
  longitude: number | null
}

export interface Address {
  id: number
  uuid: string
  title: string | null
  person_name: string
  person_email: string | null
  person_mobile: string
  alternate_contact: string | null
  type: 'home' | 'office' | 'billing' | 'shipping'
  address_1: string
  address_2: string | null
  landmark: string | null
  city: string
  postal_code: string
  block_id: number | null
  state_code: string
  country_code: string
  latitude: number | null
  longitude: number | null
  default: boolean
  priority: number
  full_address: string
  created_at: string
  updated_at: string
}
```

---

### Step 5.3: Create Components

**Files to Create:**
```
client/components/address/AddressForm.vue
client/components/address/AddressList.vue
client/components/address/AddressCard.vue
client/components/geo/CountrySelect.vue
client/components/geo/StateSelect.vue
client/components/geo/BlockSelect.vue
```

**Requirements:**
- Use Nuxt UI v4 components
- Proper TypeScript types
- Form validation
- Loading states
- Error handling
- Accessibility

---

### Step 5.4: Create Pages

**Files to Create:**
```
client/pages/dashboard/addresses/index.vue
client/pages/dashboard/addresses/create.vue
client/pages/dashboard/addresses/[id]/edit.vue
client/pages/onboarding/address.vue
```

**Requirements:**
- SEO meta tags
- Loading states
- Error states
- Empty states
- Responsive design

---

### Step 5.5: User Flow Documentation

**File:** `client/docs/user-flows/address-management.md`

**Document:**
- User journey map
- Screenshots/wireframes
- Decision points
- Success/error paths
- A/B test variations

---

## Part 6: Integration Testing

### Step 6.1: E2E Tests (Pest Browser)

**Create:**
```
tests/Browser/AddressManagementTest.php
```

**Test Flows:**
```php
test('user can create address through UI')
test('user can edit address through UI')
test('user can delete address through UI')
test('user can set default address through UI')
test('cascading dropdowns work correctly')
test('form validation works correctly')
test('error messages display correctly')
```

---

### Step 6.2: Full Stack Test

**Verify:**
1. Backend API responds correctly
2. Frontend fetches data correctly
3. Form submissions work
4. Validation works end-to-end
5. Error handling works
6. Loading states work
7. Success notifications work

---

## Part 7: Optimization & Deployment

### Step 7.1: Performance Optimization

**Backend:**
- Add database indexes
- Eager load relationships
- Cache geo data
- Optimize queries

**Frontend:**
- Code splitting
- Lazy loading
- Image optimization
- Bundle size optimization

---

### Step 7.2: Security Audit

**Checklist:**
- [ ] Input validation on all endpoints
- [ ] Authorization policies implemented
- [ ] Rate limiting configured
- [ ] CORS configured properly
- [ ] XSS prevention
- [ ] SQL injection prevention
- [ ] CSRF protection
- [ ] Sanitize all user inputs

---

### Step 7.3: Documentation

**Create:**
1. API documentation (Postman/Swagger)
2. User guide
3. Developer guide
4. Deployment guide

---

## Success Criteria

### Technical
- ✅ All migrations run successfully
- ✅ All models have relationships
- ✅ All tests pass (100%)
- ✅ API endpoints work correctly
- ✅ Filament admin works
- ✅ Frontend UI works
- ✅ No N+1 queries
- ✅ No security vulnerabilities

### User Experience
- ✅ Intuitive UI
- ✅ Fast response times (< 200ms)
- ✅ Clear error messages
- ✅ Mobile responsive
- ✅ Accessible (WCAG 2.1)

### Business
- ✅ Production-ready
- ✅ Scalable
- ✅ Maintainable
- ✅ Documented

---

## Current Progress Tracker

### Phase 1: Database ⏳ IN PROGRESS
- [x] Create models
- [x] Create migrations
- [x] Create factories
- [x] Create seeders
- [ ] Complete migration schemas
- [ ] Complete model relationships
- [ ] Complete factories
- [ ] Complete seeders
- [ ] Run migrations
- [ ] Verify database

### Phase 2: Testing ⏸️ PENDING
- [ ] Write unit tests
- [ ] Write feature tests
- [ ] Write API tests
- [ ] All tests pass

### Phase 3: API ⏸️ PENDING
- [ ] Create controllers
- [ ] Create form requests
- [ ] Create API resources
- [ ] Define routes
- [ ] Test all endpoints

### Phase 4: Admin ⏸️ PENDING
- [ ] Create Filament resources
- [ ] Test Filament resources

### Phase 5: Frontend ⏸️ PENDING
- [ ] Create composables
- [ ] Create types
- [ ] Create components
- [ ] Create pages
- [ ] Document user flows

### Phase 6: Integration ⏸️ PENDING
- [ ] E2E tests
- [ ] Full stack test

### Phase 7: Deployment ⏸️ PENDING
- [ ] Optimize performance
- [ ] Security audit
- [ ] Documentation
- [ ] Deploy

---

## Next Immediate Actions

1. ✅ Complete migrations (countries, states, blocks, addresses)
2. ✅ Complete models with relationships
3. ✅ Complete factories
4. ✅ Complete seeders (India geo data)
5. ✅ Run migrations and seed
6. ✅ Write brutal comprehensive tests
7. ✅ Ensure 100% test pass rate

---

**Last Updated**: 2025-12-09
**Status**: Ready to build migrations
