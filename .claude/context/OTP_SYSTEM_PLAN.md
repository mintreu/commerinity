# OTP System Implementation
## Based on Old Commerinity (Cache-based, Simple, Tested)

---

## 🎯 **Winner: Old Commerinity OTP System** ✅

**Why Old Commerinity is Better**:
- ✅ **Cache-based** (no database table needed)
- ✅ **Flexible** (mobile OR email)
- ✅ **Demo mode** (for testing)
- ✅ **Simple** (single Helper class)
- ✅ **Secure** (hashed OTP storage)
- ✅ **Auto-expiry** (10 minutes via cache TTL)

**Popkult**: ❌ No OTP system (doesn't need it)

---

## 📊 **How It Works**

### **1. Send OTP**
```
POST /api/auth/send-otp
Body: { type: 'mobile', value: '9876543210' }

Backend:
1. Generate 6-digit OTP (random_int(100000, 999999))
2. Hash OTP (Hash::make($otp))
3. Store in cache: Cache::put('otp_md5(mobile)', $hashedOtp, 10 minutes)
4. Send via SMS (Fast2SMS) or Email
5. Return success

Demo Mode:
- Fixed OTP: 123456
- Return OTP in response for testing
```

### **2. Verify OTP**
```
POST /api/auth/verify-otp
Body: { type: 'mobile', value: '9876543210', otp: '123456' }

Backend:
1. Get cached OTP: Cache::get('otp_md5(mobile)')
2. Verify: Hash::check($inputOtp, $cachedOtp)
3. If valid: return success
4. If invalid/expired: return error
```

### **3. Register with OTP**
```
POST /api/auth/register
Body: {
  mobile: '9876543210',
  otp: '123456',
  name: 'John Doe',
  password: 'password123',
  referral: 'ABC12345' (optional)
}

Backend:
1. Validate OTP first
2. Create user
3. Mark mobile as verified
4. Auto-login (Sanctum token)
5. Destroy OTP from cache
```

---

## 🏗️ **Implementation**

### **File Structure**:
```
app/
├── Helpers/
│   └── OtpManager.php           # Cache-based OTP (from old)
├── Http/Controllers/Api/Auth/
│   ├── RegisterController.php   # Registration with OTP
│   ├── LoginController.php      # Login (mobile OR email)
│   └── OtpController.php        # Send/Verify OTP
└── Notifications/
    └── OtpNotification.php      # Email OTP notification
```

### **OtpManager.php** (Copy from old, clean up):
```php
// app/Helpers/OtpManager.php

class OtpManager
{
    const MOBILE = 'mobile';
    const EMAIL = 'email';

    protected bool $demoMode = true; // Dev: true, Prod: false

    public static function make(): static
    {
        return new static();
    }

    // Generate & send OTP
    public function sendOtp(string $credential, string $type): int|bool
    {
        if ($this->demoMode) {
            return $this->generateDemoOtp($credential);
        }

        $otp = random_int(100000, 999999);
        $hashedOtp = Hash::make($otp);

        Cache::put($this->getCacheKey($credential), $hashedOtp, now()->addMinutes(10));

        // Send via SMS or Email
        if ($type === self::MOBILE) {
            $this->sendViaSms($credential, $otp);
        } else {
            $this->sendViaEmail($credential, $otp);
        }

        return true;
    }

    // Validate OTP
    public function validateOtp(string $credential, string $otp): array
    {
        $key = $this->demoMode ? 'demo_' . $credential : $credential;
        $cachedOtp = Cache::get($this->getCacheKey($key));

        if (!$cachedOtp) {
            return ['status' => false, 'msg' => 'OTP expired!'];
        }

        $valid = Hash::check($otp, $cachedOtp);

        return $valid
            ? ['status' => true, 'msg' => 'OTP verified successfully']
            : ['status' => false, 'msg' => 'Invalid OTP'];
    }

    // Demo mode (for testing)
    private function generateDemoOtp(string $credential): int
    {
        $otp = 123456; // Fixed for easy testing
        $hashedOtp = Hash::make($otp);

        Cache::put($this->getCacheKey('demo_' . $credential), $hashedOtp, now()->addMinutes(10));

        return $otp; // Return plain OTP for testing
    }

    private function getCacheKey(string $credential): string
    {
        return 'otp_' . md5($credential);
    }

    public function destroyOtp(string $credential): bool
    {
        return Cache::forget($this->getCacheKey($credential));
    }
}
```

---

## 🔌 **API Routes**

```php
// routes/api.php

// OTP endpoints
Route::post('/auth/send-otp', [OtpController::class, 'send']);
Route::post('/auth/verify-otp', [OtpController::class, 'verify']);

// Auth endpoints
Route::post('/auth/register', [RegisterController::class, 'register']);
Route::post('/auth/login', [LoginController::class, 'login']);

// Protected
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => response()->json(['data' => $request->user()]));
    Route::post('/auth/logout', [LoginController::class, 'logout']);
});
```

---

## ✅ **Pest Tests**

```php
// tests/Feature/Auth/OtpTest.php

test('can send OTP to mobile', function () {
    $response = $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => '9876543210',
    ]);

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'status',
        'message',
        'demo', // Demo mode returns OTP
        'otp',
    ]);
});

test('can verify correct OTP', function () {
    // Send OTP first
    $sendResponse = $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => '9876543210',
    ]);

    $otp = $sendResponse->json('otp'); // Get demo OTP

    // Verify OTP
    $response = $this->postJson('/api/auth/verify-otp', [
        'type' => 'mobile',
        'value' => '9876543210',
        'otp' => $otp,
    ]);

    $response->assertSuccessful();
    $response->assertJson(['data' => ['valid' => true]]);
});

test('cannot verify incorrect OTP', function () {
    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => '9876543210',
    ]);

    $response = $this->postJson('/api/auth/verify-otp', [
        'type' => 'mobile',
        'value' => '9876543210',
        'otp' => '000000', // Wrong OTP
    ]);

    $response->assertStatus(422);
    $response->assertJson(['valid' => false]);
});

test('can register with verified OTP', function () {
    // Send OTP
    $sendResponse = $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => '9876543210',
    ]);

    $otp = $sendResponse->json('otp');

    // Register
    $response = $this->postJson('/api/auth/register', [
        'mobile' => '9876543210',
        'otp' => $otp,
        'name' => 'Test User',
        'gender' => 'male',
        'dob' => '1990-01-01',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('users', [
        'mobile' => '9876543210',
        'name' => 'Test User',
    ]);
});
```

---

## 🎯 **Summary**

**Use Old Commerinity's OTP System**:
- ✅ Cache-based (simple, no DB table)
- ✅ Demo mode built-in (easy testing)
- ✅ Flexible (mobile OR email)
- ✅ Secure (hashed storage)
- ✅ Auto-expiry (cache TTL)
- ✅ Production-ready (SMS via Fast2SMS)

**Popkult**: Doesn't have OTP system

**Decision**: Copy Old Commerinity's OtpManager.php ✅

---

**Next**: Create the actual implementation files
