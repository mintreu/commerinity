<template>
  <div class="relative z-10 flex items-center justify-center h-full px-4 max-w-full">
    <div class="w-full max-w-md bg-white dark:bg-gray-800 shadow-xl rounded-lg p-6 md:p-8">
      <!-- Branding -->
      <div class="flex flex-col items-center mb-6">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Create Your Account</h1>
      </div>

      <!-- Wizard Steps -->
      <form @submit.prevent="handleRegister" class="space-y-5">
        <!-- STEP 1: Contact & OTP -->
        <div v-if="step === 1" class="space-y-5">
          <!-- Contact input -->
          <label :for="registrationMode === 'mobile' ? 'mobile' : 'email'" class="form-label">
            {{ registrationMode === 'mobile' ? 'Mobile Number' : 'Email Address' }}
          </label>
          <div class="flex flex-col gap-2 items-start">
            <input
                v-if="registrationMode === 'mobile'"
                v-model="form.mobile"
                type="tel"
                id="mobile"
                placeholder="e.g. 9876543210"
                :disabled="otpSent"
                @blur="validateContact"
                class="input-field"
            />
            <input
                v-else
                v-model="form.email"
                type="email"
                id="email"
                placeholder="e.g. you@example.com"
                :disabled="otpSent"
                @blur="validateContact"
                class="input-field"
            />
            <span v-if="fieldError" class="text-sm text-red-500 mt-1 block">{{ fieldError }}</span>

            <!-- Send OTP -->
            <div class="flex justify-end w-full">
              <button
                  v-if="canShowOtpButton"
                  type="button"
                  @click="sendOtp"
                  :disabled="otpSending"
                  :class="['text-white px-3 py-2 rounded-md text-sm transition', otpSent ? 'bg-blue-600 hover:bg-blue-700' : 'bg-green-600 hover:bg-green-700']"
              >
                {{ otpSent ? `Resend OTP (${countdown}s)` : 'Send OTP' }}
              </button>
            </div>
          </div>
          <p class="text-xs mt-1 text-gray-500 dark:text-gray-400">We’ll send a 6-digit verification code.</p>

          <!-- OTP Fields -->
          <!-- OTP Input Area -->
          <div v-if="otpSent && !otpVerified">
            <label class="form-label mt-4 mb-1">OTP Code</label>
            <div class="flex gap-2 justify-between">
              <input
                  v-for="(digit, index) in otp"
                  :key="index"
                  maxlength="1"
                  type="text"
                  inputmode="numeric"
                  class="w-10 h-10 text-center rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-lg text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                  v-model="otp[index]"
                  @input="focusNext(index, $event)"
              />
            </div>
            <p class="text-xs mt-1 text-gray-500 dark:text-gray-400">Enter the code sent to your {{ registrationMode }}.</p>
            <p v-if="otpError" class="text-sm text-red-500 mt-2">{{ otpError }}</p>

            <button
                v-if="otp.join('').length === 6 && !otpVerified"
                type="button"
                @click="verifyOtp"
                class="btn-green mt-2"
            >
              {{ verifyingOtp ? 'Verifying...' : 'Verify OTP' }}
            </button>
          </div>

          <!-- OTP Verified Success Message -->
          <div v-else-if="otpVerified" class="text-center mt-4">
            <div class="text-green-600 font-semibold text-sm">✅ OTP Verified Successfully</div>
            <button type="button" @click="step++" class="btn-blue mt-3">Next</button>
          </div>

        </div>

        <!-- STEP 2: Personal Info -->
        <div v-if="step === 2" class="space-y-5">
          <div>
            <label for="name" class="form-label">Full Name</label>
            <input v-model="form.name" type="text" id="name" class="input-field" />
            <span v-if="!form.name" class="text-red-500 text-sm">Name is required.</span>
          </div>
          <div>
            <label for="gender" class="form-label">Gender</label>
            <select v-model="form.gender" id="gender" class="input-field">
              <option disabled value="">Select gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
            <span v-if="!form.gender" class="text-red-500 text-sm">Gender is required.</span>
          </div>
          <div>
            <label for="dob" class="form-label">Date of Birth</label>
            <input v-model="form.dob" type="date" id="dob" class="input-field" />
            <span v-if="!form.dob" class="text-red-500 text-sm">Date of birth is required.</span>
          </div>
          <button type="button" @click="validateStepTwo" class="btn-blue mt-4">Next</button>
        </div>

        <!-- STEP 3: Password -->
        <div v-if="step === 3" class="space-y-5">
          <div>
            <label for="password" class="form-label">Password</label>
            <div class="relative">
              <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  id="password"
                  class="input-field pr-10"
              />
              <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-2 text-gray-500 dark:text-gray-400"
              >
                <span v-if="showPassword">🙈</span>
                <span v-else>👁️</span>
              </button>
            </div>

          </div>
          <div>
            <label for="confirmPassword" class="form-label">Confirm Password</label>
            <div class="relative">
              <input
                  v-model="form.confirmPassword"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  id="confirmPassword"
                  class="input-field pr-10"
              />
              <button
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute right-3 top-2 text-gray-500 dark:text-gray-400"
              >
                <span v-if="showConfirmPassword">🙈</span>
                <span v-else>👁️</span>
              </button>
            </div>

          </div>
          <span v-if="passwordError" class="text-red-500 text-sm">{{ passwordError }}</span>
          <button type="submit" class="btn-blue mt-4">Create Account</button>
        </div>
      </form>

      <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
        Already have an account?
        <NuxtLink to="/auth/login" class="text-blue-600 hover:underline dark:text-blue-400">Login</NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, useRuntimeConfig } from '#imports'

const props = defineProps({
  redirectTo: {
    type: String,
    default: '/dashboard'
  }
})

const { login } = useSanctum()
const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const showPassword = ref(false)
const showConfirmPassword = ref(false)


const registrationMode = config.public.registrationMode || 'mobile'

const form = ref({
  mobile: '',
  email: '',
  name: '',
  gender: '',
  dob: '',
  password: '',
  confirmPassword: '',
  referral: ''
})

const otp = ref(['', '', '', '', '', ''])
const otpSent = ref(false)
const otpVerified = ref(false)
const otpSending = ref(false)
const verifyingOtp = ref(false)
const otpError = ref('')
const fieldError = ref('')
const passwordError = ref('')
const countdown = ref(60)
let countdownInterval = null

const step = ref(1)
const canShowOtpButton = ref(false)

onMounted(() => {
  const refCode = route.query.referral || ''
  if (refCode) form.value.referral = refCode
})

async function validateContact() {
  const type = registrationMode
  const value = (type === 'mobile' ? form.value.mobile : form.value.email).trim()
  const mobileRegex = /^[6-9]\d{9}$/
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  if ((type === 'mobile' && !mobileRegex.test(value)) || (type === 'email' && !emailRegex.test(value))) {
    fieldError.value = type === 'mobile' ? 'Enter a valid 10-digit Indian mobile' : 'Enter a valid email address'
    canShowOtpButton.value = false
    return
  }

  fieldError.value = ''
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/auth/has_contact`, {
      method: 'POST',
      body: { type, value },
    })

    const exists = res.data?.exists
    if (exists) {
      fieldError.value = `${type === 'mobile' ? 'Mobile number' : 'Email'} already in use`
      canShowOtpButton.value = false
    } else {
      canShowOtpButton.value = true
    }
  } catch {
    fieldError.value = 'Failed to check contact'
    canShowOtpButton.value = false
  }
}

async function sendOtp() {
  otpError.value = ''
  otpVerified.value = false
  otpSending.value = true
  try {
    await useSanctumFetch(`${config.public.apiBase}/auth/send-otp`, {
      method: 'POST',
      body: { type: registrationMode, value: form.value[registrationMode].trim() }
    })
    otpSent.value = true
    canShowOtpButton.value = false
    countdown.value = 60
    countdownInterval = setInterval(() => {
      countdown.value--
      if (countdown.value <= 0) {
        clearInterval(countdownInterval)
        canShowOtpButton.value = true
        otpSending.value = false
      }
    }, 1000)
  } catch {
    otpError.value = 'Failed to send OTP'
    otpSending.value = false
  }
}

function focusNext(index, event) {
  if (event.inputType !== 'deleteContentBackward') {
    const next = event.target.nextElementSibling
    if (next) next.focus()
  }
}

async function verifyOtp() {
  const code = otp.value.join('')
  if (code.length !== 6) {
    otpError.value = 'Enter a 6-digit OTP'
    return
  }

  verifyingOtp.value = true
  otpError.value = ''
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/auth/verify-otp`, {
      method: 'POST',
      body: { type: registrationMode, value: form.value[registrationMode].trim(), otp: code }
    })

    otpVerified.value = res?.data?.valid === true
    if (!otpVerified.value) otpError.value = 'Invalid OTP, please try again or resend'
  } catch (err) {
    otpError.value = err?.data?.message || 'OTP verification failed'
  } finally {
    verifyingOtp.value = false
  }
}

function validateStepTwo() {
  if (!form.value.name || !form.value.gender || !form.value.dob) return
  step.value++
}

async function handleRegister() {
  passwordError.value = ''
  if (form.value.password !== form.value.confirmPassword) {
    passwordError.value = 'Passwords do not match'
    return
  }
  if (form.value.password.length < 6) {
    passwordError.value = 'Password must be 6+ characters'
    return
  }

  const payload = {
    name: form.value.name,
    gender: form.value.gender,
    dob: form.value.dob,
    password: form.value.password,
    confirmPassword: form.value.confirmPassword,
    referral: form.value.referral,
    type: registrationMode,
    otp: otp.value.join(''),
    ...(registrationMode === 'mobile' ? { mobile: form.value.mobile } : { email: form.value.email }),
  }

  try {
    await useSanctumFetch(`${config.public.apiBase}/register`, { method: 'POST', body: payload })
    await login({
      [registrationMode === 'mobile' ? 'mobile' : 'email']: form.value[registrationMode],
      password: form.value.password,
      remember: true
    })
    router.push(props.redirectTo)
  } catch (err) {
    passwordError.value = err?.data?.message || 'Registration failed'
  }
}
</script>

<style scoped>
.input-field {
  @apply mt-1 w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:outline-none;
}
.form-label {
  @apply block text-sm font-medium text-gray-700 dark:text-gray-300;
}
.btn-blue {
  @apply w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition;
}
.btn-green {
  @apply w-full bg-green-600 text-white py-2 rounded-md hover:bg-green-700 transition;
}
</style>
