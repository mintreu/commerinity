# Biometric Authentication & App Lock Screen
## Enterprise Security Layer for Mobile Apps

**Date**: 2025-12-08
**Purpose**: Add biometric auth (fingerprint, Face ID) + app lock screen
**Platform**: iOS & Android (WebAuthn API for web fallback)

---

## 🔐 Biometric Authentication Architecture

### **Security Layers Extended**

```
┌─────────────────────────────────────────────────────────────┐
│            AUTHENTICATION & SECURITY LAYERS                  │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Layer 1: Primary Auth (REQUIRED)                           │
│  ├── Mobile + OTP (Registration)                            │
│  ├── Mobile/Email + Password (Login)                        │
│  └── Mobile + OTP (Passwordless Login)                      │
│                                                              │
│  Layer 2: Two-Factor Auth (OPTIONAL - User Enabled)         │
│  ├── OTP via Mobile (SMS)                                   │
│  ├── OTP via Email                                          │
│  ├── Authenticator App (TOTP)                               │
│  ├── 🆕 Biometric (Fingerprint, Face ID, Iris)            │
│  └── Backup Codes (Recovery)                                │
│                                                              │
│  Layer 3: App Lock Screen (OPTIONAL - Mobile Only)          │
│  ├── 🆕 Biometric Unlock (Primary)                        │
│  ├── 🆕 PIN Code (4-6 digits)                             │
│  ├── 🆕 Pattern Lock                                       │
│  └── 🆕 Auto-lock (30s, 1min, 5min, never)                │
│                                                              │
│  Layer 4: Session Management                                │
│  ├── Sanctum Bearer Tokens                                  │
│  ├── Multi-Device Sessions                                  │
│  ├── Trusted Devices (30 days)                              │
│  └── Suspicious Activity Detection                          │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎯 Biometric Authentication Methods

### **1. Fingerprint Authentication**
- **Platforms**: Android (most devices), iOS (Touch ID)
- **API**: WebAuthn / Credential Management API
- **Fallback**: PIN code or password

### **2. Face Recognition (Face ID)**
- **Platforms**: iOS (iPhone X+), Android (some flagship)
- **API**: WebAuthn
- **Fallback**: Fingerprint or PIN

### **3. Iris Scanning**
- **Platforms**: Select Samsung devices
- **API**: Device-specific APIs
- **Fallback**: Fingerprint or PIN

---

## 🏗️ Implementation Architecture

### **Database Schema Updates**

```php
// Already added to two_factor_auths table:
$table->boolean('biometric_enabled')->default(false);
$table->string('biometric_type')->nullable(); // fingerprint, face_id, iris
$table->text('biometric_public_key')->nullable(); // Device public key
$table->timestamp('biometric_registered_at')->nullable();
```

### **New Migration: App Lock Settings**

```php
// 2025_12_08_create_app_lock_settings_table.php

Schema::create('app_lock_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    // Lock Screen Settings
    $table->boolean('lock_enabled')->default(false);
    $table->string('lock_method')->default('biometric'); // biometric, pin, pattern
    $table->integer('auto_lock_timeout')->default(30); // seconds (0 = never)

    // PIN Settings
    $table->string('pin_hash')->nullable(); // Hashed PIN (4-6 digits)
    $table->integer('pin_length')->default(4); // 4 or 6

    // Pattern Lock
    $table->text('pattern_hash')->nullable(); // Hashed pattern sequence

    // Biometric Settings
    $table->boolean('biometric_fallback_enabled')->default(true);
    $table->string('fallback_method')->default('pin'); // pin, password

    // Security
    $table->integer('failed_unlock_attempts')->default(0);
    $table->timestamp('locked_until')->nullable();
    $table->integer('max_failed_attempts')->default(5);

    $table->timestamps();

    $table->unique('user_id');
    $table->index(['user_id', 'lock_enabled']);
});
```

---

## 📱 Biometric Flow (Mobile App)

### **Registration Flow**

```
1. User completes primary auth (mobile + OTP + password)
2. App shows "Secure your account" prompt
3. User enables biometric authentication
4. Device prompts for biometric enrollment
5. Device generates key pair (public/private)
6. App sends public key to API
7. API stores biometric_public_key
8. User can now login with biometrics
```

### **Login Flow with Biometrics**

```
┌──────────────────────────────────────────────┐
│  User opens app                              │
│  ↓                                            │
│  Check if biometric enabled                  │
│  ↓                                            │
│  YES → Show biometric prompt                 │
│  │                                            │
│  │  ├─ Biometric Success                     │
│  │  │  ├─ Send challenge to API              │
│  │  │  ├─ API verifies with stored public key│
│  │  │  ├─ Issue Sanctum token                │
│  │  │  └─ User logged in ✅                  │
│  │  │                                         │
│  │  └─ Biometric Failed                      │
│  │     ├─ Retry (max 3 attempts)             │
│  │     └─ Fallback to PIN/Password           │
│  │                                            │
│  NO → Show login form                        │
│      └─ Mobile + Password                    │
└──────────────────────────────────────────────┘
```

### **App Lock Screen Flow**

```
┌──────────────────────────────────────────────┐
│  User puts app in background                 │
│  ↓                                            │
│  Start auto-lock timer (30s, 1min, 5min)     │
│  ↓                                            │
│  Timer expires OR app returns to foreground  │
│  ↓                                            │
│  Show lock screen overlay                    │
│  ↓                                            │
│  User unlocks:                               │
│  ├─ Biometric (fingerprint/face)             │
│  ├─ PIN code (4-6 digits)                    │
│  └─ Pattern (9-dot grid)                     │
│  ↓                                            │
│  Success → Remove overlay, continue session  │
│  Failure → Increment attempts, lock after 5  │
└──────────────────────────────────────────────┘
```

---

## 🌐 WebAuthn API Integration

### **Frontend (Nuxt/Mobile)**

```typescript
// composables/useBiometric.ts

export const useBiometric = () => {
  const { $api } = useNuxtApp()

  /**
   * Check if biometric auth is available on device
   */
  async function isAvailable(): Promise<boolean> {
    if (!window.PublicKeyCredential) {
      return false
    }

    const available = await PublicKeyCredential
      .isUserVerifyingPlatformAuthenticatorAvailable()

    return available
  }

  /**
   * Register biometric authentication
   */
  async function register() {
    // 1. Get challenge from API
    const { challenge, user } = await $api('/auth/biometric/challenge', {
      method: 'POST'
    })

    // 2. Create credential
    const credential = await navigator.credentials.create({
      publicKey: {
        challenge: Uint8Array.from(atob(challenge), c => c.charCodeAt(0)),
        rp: {
          name: "Commerinity Pro",
          id: window.location.hostname
        },
        user: {
          id: Uint8Array.from(user.id.toString(), c => c.charCodeAt(0)),
          name: user.mobile,
          displayName: user.name
        },
        pubKeyCredParams: [
          { alg: -7, type: "public-key" },  // ES256
          { alg: -257, type: "public-key" } // RS256
        ],
        authenticatorSelection: {
          authenticatorAttachment: "platform", // Built-in (Touch ID, Face ID)
          userVerification: "required"
        },
        timeout: 60000
      }
    })

    // 3. Send credential to API
    const response = await $api('/auth/biometric/register', {
      method: 'POST',
      body: {
        credential: {
          id: credential.id,
          rawId: arrayBufferToBase64(credential.rawId),
          response: {
            clientDataJSON: arrayBufferToBase64(credential.response.clientDataJSON),
            attestationObject: arrayBufferToBase64(credential.response.attestationObject)
          },
          type: credential.type
        }
      }
    })

    return response
  }

  /**
   * Authenticate with biometric
   */
  async function authenticate() {
    // 1. Get challenge
    const { challenge } = await $api('/auth/biometric/challenge', {
      method: 'POST'
    })

    // 2. Get credential
    const credential = await navigator.credentials.get({
      publicKey: {
        challenge: Uint8Array.from(atob(challenge), c => c.charCodeAt(0)),
        timeout: 60000,
        userVerification: "required"
      }
    })

    // 3. Verify with API
    const response = await $api('/auth/biometric/verify', {
      method: 'POST',
      body: {
        credential: {
          id: credential.id,
          rawId: arrayBufferToBase64(credential.rawId),
          response: {
            clientDataJSON: arrayBufferToBase64(credential.response.clientDataJSON),
            authenticatorData: arrayBufferToBase64(credential.response.authenticatorData),
            signature: arrayBufferToBase64(credential.response.signature),
            userHandle: credential.response.userHandle
              ? arrayBufferToBase64(credential.response.userHandle)
              : null
          },
          type: credential.type
        }
      }
    })

    return response // { success: true, token }
  }

  return {
    isAvailable,
    register,
    authenticate
  }
}
```

---

## 🔒 App Lock Screen Implementation

### **Frontend Store**

```typescript
// stores/appLock.ts
import { defineStore } from 'pinia'

export const useAppLockStore = defineStore('appLock', () => {
  const locked = ref(false)
  const settings = ref({
    enabled: false,
    method: 'biometric', // biometric, pin, pattern
    autoLockTimeout: 30, // seconds
    biometricFallback: true
  })

  let lockTimer: NodeJS.Timeout | null = null

  /**
   * Start auto-lock timer
   */
  function startLockTimer() {
    if (!settings.value.enabled || settings.value.autoLockTimeout === 0) {
      return
    }

    clearLockTimer()
    lockTimer = setTimeout(() => {
      lock()
    }, settings.value.autoLockTimeout * 1000)
  }

  /**
   * Clear lock timer
   */
  function clearLockTimer() {
    if (lockTimer) {
      clearTimeout(lockTimer)
      lockTimer = null
    }
  }

  /**
   * Lock the app
   */
  function lock() {
    locked.value = true
    clearLockTimer()
  }

  /**
   * Unlock the app
   */
  async function unlock(credential: any) {
    // Verify unlock credential (biometric, PIN, pattern)
    const valid = await verifyUnlockCredential(credential)

    if (valid) {
      locked.value = false
      startLockTimer() // Restart timer
      return true
    }

    return false
  }

  /**
   * Fetch lock settings from API
   */
  async function fetchSettings() {
    const { $api } = useNuxtApp()
    const data = await $api('/auth/lock-settings')
    settings.value = data
  }

  /**
   * Update lock settings
   */
  async function updateSettings(newSettings: any) {
    const { $api } = useNuxtApp()
    const data = await $api('/auth/lock-settings', {
      method: 'PUT',
      body: newSettings
    })
    settings.value = data
  }

  // Lifecycle hooks
  if (process.client) {
    // Lock on app visibility change
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        startLockTimer()
      } else {
        clearLockTimer()
        if (settings.value.enabled && settings.value.autoLockTimeout === 0) {
          // Immediate lock when app becomes visible
          lock()
        }
      }
    })
  }

  return {
    locked,
    settings,
    lock,
    unlock,
    startLockTimer,
    clearLockTimer,
    fetchSettings,
    updateSettings
  }
})
```

### **Lock Screen Component**

```vue
<!-- components/auth/AppLockScreen.vue -->
<template>
  <div v-if="appLockStore.locked" class="fixed inset-0 z-50 bg-gray-900/95 backdrop-blur-lg flex items-center justify-center">
    <div class="max-w-md w-full p-8 text-center">
      <!-- User Avatar -->
      <img :src="authStore.user?.avatar" class="w-24 h-24 rounded-full mx-auto mb-4" />
      <h2 class="text-2xl font-bold text-white mb-2">{{ authStore.user?.name }}</h2>
      <p class="text-gray-400 mb-8">App is locked</p>

      <!-- Biometric Unlock -->
      <template v-if="settings.method === 'biometric'">
        <button @click="unlockWithBiometric" class="btn btn-primary mb-4">
          <Icon name="heroicons:finger-print" class="w-12 h-12 mx-auto mb-2" />
          <span>Unlock with {{ biometricType }}</span>
        </button>

        <button v-if="settings.biometricFallback" @click="showPinInput = true" class="text-sm text-gray-400">
          Use PIN instead
        </button>
      </template>

      <!-- PIN Unlock -->
      <template v-else-if="settings.method === 'pin' || showPinInput">
        <PinInput
          v-model="pin"
          :length="settings.pinLength"
          @complete="unlockWithPin"
          :error="error"
        />

        <button v-if="settings.method === 'biometric'" @click="showPinInput = false" class="text-sm text-gray-400 mt-4">
          Use biometric instead
        </button>
      </template>

      <!-- Pattern Unlock -->
      <template v-else-if="settings.method === 'pattern'">
        <PatternLock
          @complete="unlockWithPattern"
          :error="error"
        />
      </template>

      <!-- Failed Attempts -->
      <div v-if="failedAttempts > 0" class="mt-4 text-red-400">
        {{ 5 - failedAttempts }} attempts remaining
      </div>

      <!-- Logout Option -->
      <button @click="logout" class="mt-8 text-sm text-gray-500 hover:text-white">
        Logout
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
const appLockStore = useAppLockStore()
const authStore = useAuthStore()
const { authenticate: biometricAuth } = useBiometric()

const showPinInput = ref(false)
const pin = ref('')
const error = ref('')
const failedAttempts = ref(0)

const biometricType = computed(() => {
  const type = appLockStore.settings.biometricType
  return type === 'face_id' ? 'Face ID' : 'Fingerprint'
})

async function unlockWithBiometric() {
  try {
    const result = await biometricAuth()
    if (result.success) {
      await appLockStore.unlock({ type: 'biometric' })
      failedAttempts.value = 0
    }
  } catch (e) {
    error.value = 'Biometric authentication failed'
    failedAttempts.value++
  }
}

async function unlockWithPin(pinCode: string) {
  const result = await appLockStore.unlock({ type: 'pin', value: pinCode })
  if (result) {
    failedAttempts.value = 0
    pin.value = ''
  } else {
    error.value = 'Incorrect PIN'
    failedAttempts.value++
    pin.value = ''
  }
}

async function unlockWithPattern(pattern: number[]) {
  const result = await appLockStore.unlock({ type: 'pattern', value: pattern })
  if (!result) {
    error.value = 'Incorrect pattern'
    failedAttempts.value++
  }
}

function logout() {
  authStore.logout()
  navigateTo('/login')
}
</script>
```

---

## 🔌 API Endpoints for Biometric & Lock

### **Biometric Authentication**

```
POST   /api/v1/auth/biometric/challenge      Get WebAuthn challenge
POST   /api/v1/auth/biometric/register       Register biometric credential
POST   /api/v1/auth/biometric/verify         Verify biometric authentication
DELETE /api/v1/auth/biometric/revoke         Revoke biometric auth
GET    /api/v1/auth/biometric/status         Check if enabled
```

### **App Lock Screen**

```
GET    /api/v1/auth/lock-settings            Get lock settings
PUT    /api/v1/auth/lock-settings            Update lock settings
POST   /api/v1/auth/lock/enable              Enable app lock
POST   /api/v1/auth/lock/disable             Disable app lock
POST   /api/v1/auth/lock/set-pin             Set/Change PIN
POST   /api/v1/auth/lock/set-pattern         Set/Change pattern
POST   /api/v1/auth/lock/verify              Verify unlock attempt
```

---

## 🎨 User Settings UI Flow

### **Security Settings Page**

```vue
<!-- pages/account/security.vue -->
<template>
  <div class="space-y-8">
    <!-- Password Section -->
    <SettingsCard title="Password">
      <Button @click="showChangePassword = true">Change Password</Button>
    </SettingsCard>

    <!-- Two-Factor Authentication -->
    <SettingsCard title="Two-Factor Authentication">
      <Toggle v-model="twoFactorEnabled" @change="toggle2FA" />
      <p class="text-sm text-gray-600">Add extra security layer</p>

      <template v-if="twoFactorEnabled">
        <RadioGroup v-model="twoFactorMethod">
          <Radio value="sms">SMS to {{ user.mobile }}</Radio>
          <Radio value="email">Email to {{ user.email }}</Radio>
          <Radio value="totp">Authenticator App</Radio>
          <Radio value="biometric">🆕 Biometric ({{ biometricType }})</Radio>
        </RadioGroup>

        <Button v-if="twoFactorMethod === 'biometric'" @click="registerBiometric">
          Setup Biometric Auth
        </Button>
      </template>
    </SettingsCard>

    <!-- App Lock Screen (Mobile Only) -->
    <SettingsCard v-if="isMobile" title="App Lock Screen">
      <Toggle v-model="appLockEnabled" />
      <p class="text-sm text-gray-600">Lock app when in background</p>

      <template v-if="appLockEnabled">
        <Select v-model="lockMethod" label="Lock Method">
          <option value="biometric">🆕 Biometric</option>
          <option value="pin">PIN Code</option>
          <option value="pattern">Pattern</option>
        </Select>

        <Select v-model="autoLockTimeout" label="Auto-Lock After">
          <option :value="0">Immediately</option>
          <option :value="30">30 seconds</option>
          <option :value="60">1 minute</option>
          <option :value="300">5 minutes</option>
          <option :value="-1">Never</option>
        </Select>

        <template v-if="lockMethod === 'pin'">
          <Button @click="showSetPin = true">Set PIN Code</Button>
        </template>

        <template v-if="lockMethod === 'pattern'">
          <Button @click="showSetPattern = true">Set Pattern</Button>
        </template>
      </template>
    </SettingsCard>

    <!-- Trusted Devices -->
    <SettingsCard title="Trusted Devices">
      <div v-for="device in trustedDevices" :key="device.id" class="flex items-center justify-between py-2">
        <div>
          <p class="font-medium">{{ device.device_name }}</p>
          <p class="text-sm text-gray-600">{{ device.ip_address }} • {{ device.city }}</p>
          <p class="text-xs text-gray-500">Last used: {{ formatDate(device.last_used_at) }}</p>
        </div>
        <Button @click="revokeDevice(device.id)" variant="danger" size="sm">
          Revoke
        </Button>
      </div>
    </SettingsCard>

    <!-- Active Sessions -->
    <SettingsCard title="Active Sessions">
      <Button @click="logoutAllDevices" variant="danger">
        Logout All Devices
      </Button>
    </SettingsCard>
  </div>
</template>
```

---

## 🔐 Backend Implementation

### **TwoFactorManager Helper**

```php
// app/Helpers/TwoFactorManager.php

final class TwoFactorManager
{
    public function __construct(
        private readonly CacheContract $cache
    ) {}

    /**
     * Generate TOTP secret for authenticator app
     */
    public function generateTotpSecret(): string
    {
        return Base32::encodeUpper(random_bytes(20));
    }

    /**
     * Generate QR code for TOTP setup
     */
    public function getTotpQrCode(User $user, string $secret): string
    {
        $issuer = config('app.name');
        $label = $user->mobile ?? $user->email;

        $url = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            urlencode($issuer),
            urlencode($label),
            $secret,
            urlencode($issuer)
        );

        // Generate QR code (use simplesoftwareio/simple-qrcode)
        return QrCode::size(200)->generate($url);
    }

    /**
     * Verify TOTP code
     */
    public function verifyTotp(string $secret, string $code): bool
    {
        $totp = new TOTP($secret);
        return $totp->verify($code, null, 1); // Allow 1 window tolerance
    }

    /**
     * Generate backup codes
     */
    public function generateBackupCodes(int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(4).'-'.Str::random(4));
        }
        return $codes;
    }

    /**
     * Verify backup code
     */
    public function verifyBackupCode(TwoFactorAuth $twoFactor, string $code): bool
    {
        $codes = $twoFactor->getDecryptedBackupCodes();

        foreach ($codes as $index => $storedCode) {
            if (hash_equals($storedCode, $code)) {
                // Remove used code
                unset($codes[$index]);
                $twoFactor->setEncryptedBackupCodes(array_values($codes));
                $twoFactor->increment('backup_codes_used');
                return true;
            }
        }

        return false;
    }
}
```

---

## 📊 Complete API Response Examples

### **Enable Biometric Response**

```json
{
  "success": true,
  "message": "Biometric authentication enabled",
  "data": {
    "biometric_type": "face_id",
    "registered_at": "2025-12-08T17:30:00Z",
    "backup_codes": [
      "ABCD-1234",
      "EFGH-5678",
      "..."
    ]
  }
}
```

### **Biometric Login Response**

```json
{
  "success": true,
  "message": "Authenticated successfully",
  "data": {
    "user": {...},
    "token": "1|sanctum_token_here",
    "auth_method": "biometric"
  }
}
```

### **Lock Settings Response**

```json
{
  "success": true,
  "data": {
    "enabled": true,
    "method": "biometric",
    "auto_lock_timeout": 30,
    "biometric_fallback": true,
    "biometric_type": "fingerprint"
  }
}
```

---

## 🎯 Mobile-Specific Features

### **1. Biometric Types Supported**

```typescript
type BiometricType =
  | 'fingerprint'     // Android + iOS (Touch ID)
  | 'face_id'         // iOS (iPhone X+)
  | 'iris'            // Samsung select models
  | 'voice'           // Future

interface BiometricCapabilities {
  available: boolean
  types: BiometricType[]
  strongAuth: boolean  // Hardware-backed
}
```

### **2. Auto-Lock Scenarios**

```
Trigger Lock When:
├── App sent to background
├── Device locked
├── Timeout elapsed (30s, 1min, 5min)
└── Manual lock (security button)

Do NOT Lock When:
├── Auto-lock disabled (timeout = -1)
├── User in critical flow (checkout, payment)
└── Already authenticated via biometric (same session)
```

### **3. Fallback Mechanisms**

```
Primary: Biometric (fingerprint/face)
↓ (if fails 3x)
Fallback 1: PIN Code (4-6 digits)
↓ (if fails 5x)
Fallback 2: Full Login (mobile + password)
↓ (if fails 3x)
Lock Account: 30 minutes cooldown
```

---

## 🚀 Implementation Packages

### **Backend**

```bash
# TOTP Implementation
composer require spomky-labs/otphp

# QR Code Generation
composer require simplesoftwareio/simple-qrcode

# Already installed ✅
composer require laravel-notification-channels/webpush
```

### **Frontend (Nuxt)**

```bash
# WebAuthn (Biometric)
npm install @simplewebauthn/browser

# Pattern Lock
npm install vue-pattern-lock

# QR Code Scanner (for TOTP setup)
npm install qrcode-reader-vue3
```

---

## 📋 Implementation Checklist

### **Backend**
- [x] Two-factor auth table migration
- [x] Trusted devices table migration
- [ ] App lock settings table migration
- [x] TwoFactorAuth model
- [x] TrustedDevice model
- [ ] AppLockSetting model
- [ ] TwoFactorManager helper
- [ ] BiometricAuthController
- [ ] AppLockController
- [ ] Middleware: RequireTwoFactor
- [ ] Middleware: CheckDeviceTrust
- [ ] Tests for 2FA flow
- [ ] Tests for biometric flow
- [ ] Tests for app lock

### **Frontend**
- [ ] useBiometric composable
- [ ] use2FA composable
- [ ] useAppLock composable
- [ ] AppLockScreen component
- [ ] PinInput component
- [ ] PatternLock component
- [ ] BiometricSetup component
- [ ] Security settings page
- [ ] Trusted devices management

---

## 🎯 User Experience Flow

### **First-Time Setup (New User)**

```
1. Register → Mobile + OTP ✅
2. Set Password ✅
3. (Optional) Enable 2FA
   ├─ Choose method: SMS / Email / Authenticator / 🆕 Biometric
   └─ Setup chosen method
4. (Optional - Mobile) Enable App Lock
   ├─ Choose method: 🆕 Biometric / PIN / Pattern
   ├─ Set auto-lock timeout
   └─ Enable biometric fallback
5. Done! Account secured 🔒
```

### **Daily Usage**

```
Mobile App:
1. Open app
2. IF app lock enabled:
   ├─ Show biometric prompt
   ├─ Scan fingerprint/face
   └─ Unlock → Continue
3. ELSE:
   └─ Continue (already logged in)

Sensitive Actions (transfer money, change password):
1. IF 2FA enabled:
   ├─ Request 2FA challenge
   ├─ User verifies (OTP/Biometric)
   └─ Action allowed
2. ELSE:
   └─ Action allowed
```

---

**Last Updated**: 2025-12-08 17:30 PM
**Status**: Architecture complete, ready for implementation
**Next**: Create AppLockSetting migration and TwoFactorManager helper
