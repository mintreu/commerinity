# Backend Build Script - Phase 1
## Execute These Steps in Order

✅ MoneyPHP installed
⏳ WebPush installing in background

---

## STEP 1: Install Remaining Packages

```bash
cd apiserver

# Wait for webpush to finish, then:
composer require laravel-notification-channels/webpush --no-interaction
composer require staudenmeir/laravel-adjacency-list --no-interaction
```

---

## STEP 2: Create Database

```bash
# In MySQL/MariaDB
mysql -u root -e "CREATE DATABASE IF NOT EXISTS commerinity_;"
```

Note: Database name is `commerinity_` (without 'pro') as per your .env

---

## STEP 3: Run Initial Migrations

```bash
php artisan migrate
```

This will create default Laravel tables (users, sessions, cache, jobs, etc.)

---

## STEP 4: Create User Migration (Enhanced)

```bash
php artisan make:migration enhance_users_table --no-interaction
```

Add these fields to the migration:

```php
// database/migrations/xxxx_enhance_users_table.php

public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Mobile & verification
        $table->string('mobile', 15)->nullable()->unique()->after('email');
        $table->timestamp('mobile_verified_at')->nullable()->after('email_verified_at');

        // MLM fields
        $table->string('referral_code', 8)->unique()->after('mobile_verified_at');
        $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete()->after('referral_code');

        // Status & type
        $table->string('status')->default('draft')->after('parent_id'); // draft, active, suspended
        $table->string('type')->default('regular')->after('status'); // regular, premium

        // Timestamps
        $table->timestamp('onboarded_at')->nullable()->after('remember_token');

        // Additional
        $table->string('gender')->nullable();
        $table->date('dob')->nullable();
        $table->text('bio')->nullable();
    });
}
```

---

## STEP 5: Create OTP System

```bash
php artisan make:model Models/User/Otp -m --no-interaction
```

Migration:
```php
Schema::create('otps', function (Blueprint $table) {
    $table->id();
    $table->string('mobile', 15);
    $table->string('code', 6);
    $table->timestamp('expires_at');
    $table->boolean('verified')->default(false);
    $table->timestamps();

    $table->index(['mobile', 'code']);
});
```

Model:
```php
// app/Models/User/Otp.php
namespace App\Models\User;

class Otp extends Model
{
    protected $fillable = ['mobile', 'code', 'expires_at', 'verified'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified' => 'boolean',
        ];
    }

    public function isValid(): bool
    {
        return !$this->verified && $this->expires_at->isFuture();
    }
}
```

---

## STEP 6: Update User Model

```php
// app/Models/User.php

protected $fillable = [
    'name', 'email', 'mobile', 'password',
    'referral_code', 'parent_id', 'status', 'type',
    'email_verified_at', 'mobile_verified_at', 'onboarded_at',
    'gender', 'dob', 'bio',
];

protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'mobile_verified_at' => 'datetime',
        'onboarded_at' => 'datetime',
        'password' => 'hashed',
        'dob' => 'date',
    ];
}

// Relationships
public function parent(): BelongsTo
{
    return $this->belongsTo(User::class, 'parent_id');
}

public function children(): HasMany
{
    return $this->hasMany(User::class, 'parent_id');
}

// Auto-generate referral code
protected static function booted(): void
{
    static::creating(function (User $user) {
        if (!$user->referral_code) {
            $user->referral_code = strtoupper(Str::random(8));
        }
    });
}
```

---

## STEP 7: Create Auth Controllers

Use Artisan:
```bash
php artisan make:controller Http/Controllers/Api/Auth/RegisterController --no-interaction
php artisan make:controller Http/Controllers/Api/Auth/LoginController --no-interaction
php artisan make:controller Http/Controllers/Api/Auth/OtpController --no-interaction
```

Implement based on old commerinity patterns (mobile OTP registration, flexible login)

---

## STEP 8: Create Form Requests

```bash
php artisan make:request Http/Requests/Auth/RegisterRequest --no-interaction
php artisan make:request Http/Requests/Auth/LoginRequest --no-interaction
php artisan make:request Http/Requests/Auth/SendOtpRequest --no-interaction
php artisan make:request Http/Requests/Auth/VerifyOtpRequest --no-interaction
```

---

## STEP 9: Create API Routes

Add to `routes/api.php`:

```php
// Public routes
Route::post('/auth/send-otp', [OtpController::class, 'send']);
Route::post('/auth/verify-otp', [OtpController::class, 'verify']);
Route::post('/auth/register', [RegisterController::class, 'register']);
Route::post('/auth/login', [LoginController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
    Route::post('/auth/logout', [LoginController::class, 'logout']);
});
```

---

## STEP 10: Create app:reset Command

```bash
php artisan make:command ResetCommand --no-interaction
```

Implementation:
```php
// app/Console/Commands/ResetCommand.php

protected $signature = 'app:reset';
protected $description = 'Reset application (fresh migrations, cache clear)';

public function handle(): int
{
    $this->info('Resetting application...');

    $this->call('migrate:fresh');
    $this->call('db:seed');
    $this->call('cache:clear');
    $this->call('config:clear');
    $this->call('route:clear');
    $this->call('view:clear');

    $this->info('✅ Application reset complete!');

    return self::SUCCESS;
}
```

---

## STEP 11: Write Pest Tests

```bash
php artisan make:test --pest Feature/Auth/RegisterTest --no-interaction
php artisan make:test --pest Feature/Auth/LoginTest --no-interaction
php artisan make:test --pest Feature/Auth/OtpTest --no-interaction
```

Example test:
```php
// tests/Feature/Auth/RegisterTest.php

test('user can register with mobile and OTP', function () {
    // Send OTP
    $response = $this->postJson('/api/auth/send-otp', [
        'mobile' => '9876543210'
    ]);

    $response->assertSuccessful();

    // Get OTP from database (in production, sent via SMS)
    $otp = Otp::where('mobile', '9876543210')->latest()->first();

    // Verify OTP and register
    $response = $this->postJson('/api/auth/register', [
        'mobile' => '9876543210',
        'otp' => $otp->code,
        'name' => 'Test User',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('users', ['mobile' => '9876543210']);
});
```

---

## ✅ **After Completion**

Run:
```bash
php artisan app:reset
php artisan test
php artisan serve
```

Backend will be ready at: http://localhost:8000

---

**Estimated Time**: 2-3 hours to implement all backend auth
**Next**: Build frontend (Nuxt 4 with old commerinity design)
