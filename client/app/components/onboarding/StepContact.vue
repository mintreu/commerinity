<template>
  <div class="step-contact">
    <div class="w-full">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <UIcon
            name="i-lucide-smartphone"
            class="w-8 h-8 text-green-600 dark:text-green-400"
          />
        </div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-2">
          {{ missingContact === 'mobile'
            ? 'Verify your mobile number'
            : missingContact === 'email'
              ? 'Add your email'
              : 'Contact details verified'
          }}
        </h2>
        <p class="text-gray-600 dark:text-gray-400 text-sm md:text-base">
          {{ missingContact === 'mobile'
            ? 'Mobile verification is required to continue'
            : missingContact === 'email'
              ? 'Add an email for account recovery and updates'
              : 'Your contact details are already verified.'
          }}
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
          We will never share your contact details with anyone.
        </p>
      </div>

      <!-- Mobile Section (REQUIRED if not verified) -->
      <div
        v-if="missingContact === 'mobile'"
        class="space-y-6"
      >
        <!-- Mobile Already Verified Badge -->
        <div
          v-if="mobileVerified"
          class="mb-6"
        >
          <div class="flex items-center justify-center gap-2 py-3 px-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
            <UIcon
              name="i-lucide-check-circle"
              class="w-5 h-5 text-green-600 dark:text-green-400"
            />
            <span class="text-green-700 dark:text-green-300 font-medium">
              Mobile verified successfully!
            </span>
          </div>
        </div>

        <!-- Mobile Form -->
        <UForm
          v-if="!mobileVerified"
          :state="mobileFormState"
          :schema="mobileSchema"
          class="space-y-6 w-full"
        >
          <UFormField
            label="Mobile Number"
            name="mobile"
            required
          >
            <UInput
              v-model="mobileFormState.mobile"
              type="tel"
              placeholder="10-digit mobile number"
              size="lg"
              icon="i-lucide-smartphone"
              class="w-full"
            />
            <template #hint>
              <span class="text-xs text-gray-500">Enter a 10-digit mobile number.</span>
            </template>
          </UFormField>

          <!-- OTP Section -->
          <div v-if="mobileFormState.mobile && !mobileVerified">
            <div
              v-if="!mobileOtpSent"
              class="mt-4"
            >
              <UButton
                :loading="sendingMobileOtp"
                :disabled="!isValidMobile"
                color="primary"
                variant="soft"
                size="lg"
                class="w-full"
                @click="sendMobileOtp"
              >
                <UIcon
                  name="i-lucide-send"
                  class="w-4 h-4 mr-2"
                />
                Send Verification Code
              </UButton>
            </div>

            <div
              v-else
              class="space-y-4 mt-4"
            >
              <p class="text-sm text-center text-gray-600 dark:text-gray-400">
                We sent a code to <strong>{{ mobileFormState.mobile }}</strong>
              </p>

              <UFormField
                label="Verification Code"
                name="otp"
              >
                <UInput
                  v-model="mobileFormState.otp"
                  placeholder="123456"
                  size="lg"
                  icon="i-lucide-key"
                  maxlength="6"
                  class="text-center tracking-widest font-mono"
                />
                <template #hint>
                  <span class="text-xs text-gray-500">Enter the 6-digit code</span>
                </template>
              </UFormField>

              <div class="flex items-center justify-between">
                <button
                  type="button"
                  class="text-sm text-primary-600 dark:text-primary-400 hover:underline"
                  :disabled="mobileResendCooldown > 0"
                  @click="resendMobileOtp"
                >
                  {{ mobileResendCooldown > 0 ? `Resend in ${mobileResendCooldown}s` : 'Resend code' }}
                </button>

                <UButton
                  :loading="verifyingMobileOtp"
                  :disabled="mobileFormState.otp.length !== 6"
                  color="primary"
                  size="sm"
                  @click="verifyMobileOtp"
                >
                  Verify
                </UButton>
              </div>
            </div>
          </div>
        </UForm>
      </div>

      <!-- Email Section (OPTIONAL - only shown after mobile is verified) -->
      <div
        v-else-if="missingContact === 'email'"
        class="space-y-5"
      >
        <!-- Mobile Already Verified Info -->
        <div class="flex items-center gap-2 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg mb-4">
          <UIcon
            name="i-lucide-check-circle"
            class="w-5 h-5 text-green-600"
          />
          <span class="text-green-700 dark:text-green-300 text-sm font-medium">
            Mobile: {{ props.userMobile }} (verified)
          </span>
        </div>

        <!-- Email Already Verified Badge -->
        <div
          v-if="hasEmailVerified"
          class="mb-6"
        >
          <div class="flex items-center justify-center gap-2 py-3 px-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
            <UIcon
              name="i-lucide-check-circle"
              class="w-5 h-5 text-green-600 dark:text-green-400"
            />
            <span class="text-green-700 dark:text-green-300 font-medium">
              Email already verified!
            </span>
          </div>
        </div>

        <!-- Email Form -->
        <UForm
          v-if="!hasEmailVerified && !emailVerified"
          :state="emailFormState"
          :schema="emailSchema"
          class="space-y-6 w-full"
        >
          <UFormField
            label="Email Address"
            name="email"
          >
            <UInput
              v-model="emailFormState.email"
              type="email"
              placeholder="you@example.com"
              size="lg"
              icon="i-lucide-mail"
              class="w-full"
            />
            <template #hint>
              <span class="text-xs text-gray-500">Used for recovery and account alerts.</span>
            </template>
          </UFormField>

          <!-- OTP Section -->
          <div v-if="emailFormState.email && !emailVerified">
            <div
              v-if="!emailOtpSent"
              class="mt-4"
            >
              <UButton
                :loading="sendingEmailOtp"
                :disabled="!isValidEmail"
                color="primary"
                variant="soft"
                size="lg"
                class="w-full"
                @click="sendEmailOtp"
              >
                <UIcon
                  name="i-lucide-send"
                  class="w-4 h-4 mr-2"
                />
                Send Verification Code
              </UButton>
            </div>

            <div
              v-else
              class="space-y-4 mt-4"
            >
              <p class="text-sm text-center text-gray-600 dark:text-gray-400">
                We sent a code to <strong>{{ emailFormState.email }}</strong>
              </p>

              <UFormField
                label="Verification Code"
                name="otp"
              >
                <UInput
                  v-model="emailFormState.otp"
                  placeholder="123456"
                  size="lg"
                  icon="i-lucide-key"
                  maxlength="6"
                  class="text-center tracking-widest font-mono"
                />
              </UFormField>

              <div class="flex items-center justify-between">
                <button
                  type="button"
                  class="text-sm text-primary-600 dark:text-primary-400 hover:underline"
                  :disabled="emailResendCooldown > 0"
                  @click="resendEmailOtp"
                >
                  {{ emailResendCooldown > 0 ? `Resend in ${emailResendCooldown}s` : 'Resend code' }}
                </button>

                <UButton
                  :loading="verifyingEmailOtp"
                  :disabled="emailFormState.otp.length !== 6"
                  color="primary"
                  size="sm"
                  @click="verifyEmailOtp"
                >
                  Verify
                </UButton>
              </div>
            </div>
          </div>
        </UForm>

        <!-- Email Verified Badge -->
        <div
          v-if="emailVerified"
          class="flex items-center gap-2 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg"
        >
          <UIcon
            name="i-lucide-check-circle"
            class="w-5 h-5 text-green-600"
          />
          <span class="text-green-700 dark:text-green-300 text-sm font-medium">Email verified successfully!</span>
        </div>

        <!-- Skip Email Note -->
        <p
          v-if="!hasEmailVerified && !emailVerified && !emailFormState.email"
          class="text-center text-sm text-gray-500 dark:text-gray-400"
        >
          You can skip this step and add email later from your profile settings.
        </p>

        <div class="flex justify-center">
          <UButton
            v-if="!hasEmailVerified && !emailVerified"
            variant="ghost"
            color="neutral"
            size="sm"
            @click="skipEmail"
          >
            Skip for now
          </UButton>
        </div>
      </div>

      <div
        v-else
        class="space-y-4"
      >
        <div class="flex items-center justify-center gap-2 py-3 px-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
          <UIcon
            name="i-lucide-check-circle"
            class="w-5 h-5 text-green-600 dark:text-green-400"
          />
          <span class="text-green-700 dark:text-green-300 font-medium">
            Contact details verified.
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { z } from 'zod'

interface Props {
  signupMethod: 'mobile' | 'email'
  userEmail?: string | null
  userMobile?: string | null
  emailVerifiedAt?: string | null
  mobileVerifiedAt?: string | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:data': [data: { email?: string, mobile?: string }]
  'valid': [isValid: boolean]
  'verified': []
}>()

const config = useRuntimeConfig()
const toast = useToast()

// Check verification status from props.
// If user signed up with a contact method, assume it was verified during registration.
const hasMobileVerified = computed(() => {
  if (props.signupMethod === 'mobile' && props.userMobile) {
    return true
  }
  return !!props.mobileVerifiedAt
})
const hasEmailVerified = computed(() => {
  if (props.signupMethod === 'email' && props.userEmail) {
    return true
  }
  return !!props.emailVerifiedAt
})

const missingContact = computed<'mobile' | 'email' | 'none'>(() => {
  if (!hasMobileVerified.value && hasEmailVerified.value) return 'mobile'
  if (!hasEmailVerified.value && hasMobileVerified.value) return 'email'
  if (!hasMobileVerified.value && !hasEmailVerified.value) {
    return props.signupMethod === 'mobile' ? 'email' : 'mobile'
  }
  return 'none'
})

// Mobile form state
const mobileFormState = reactive({
  mobile: props.userMobile || '',
  otp: ''
})

// Email form state
const emailFormState = reactive({
  email: props.userEmail || '',
  otp: ''
})

// OTP states
const mobileOtpSent = ref(false)
const emailOtpSent = ref(false)
const sendingMobileOtp = ref(false)
const sendingEmailOtp = ref(false)
const verifyingMobileOtp = ref(false)
const verifyingEmailOtp = ref(false)
const mobileVerified = ref(!!props.mobileVerifiedAt)
const emailVerified = ref(!!props.emailVerifiedAt)
const mobileResendCooldown = ref(0)
const emailResendCooldown = ref(0)

// Validation
const isValidMobile = computed(() => {
  const mobileRegex = /^\d{10}$/
  return mobileRegex.test(mobileFormState.mobile.replace(/[\s-]/g, ''))
})

const isValidEmail = computed(() => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(emailFormState.email)
})

// Schemas
const mobileSchema = z.object({
  mobile: z.string().regex(/^\d{10}$/, 'Please enter a valid 10-digit mobile number'),
  otp: z.string().optional()
})

const emailSchema = z.object({
  email: z.string().email('Please enter a valid email').optional().or(z.literal('')),
  otp: z.string().optional()
})

// Step is valid when required contact is verified; email is optional
const isStepValid = computed(() => {
  if (missingContact.value === 'mobile') {
    return hasMobileVerified.value || mobileVerified.value
  }
  return true
})

// Watch and emit validity
watch(
  isStepValid,
  (isValid) => {
    emit('valid', isValid)
  },
  { immediate: true }
)

// Emit data changes
watch(
  () => ({ email: emailFormState.email, mobile: mobileFormState.mobile }),
  data => emit('update:data', data),
  { immediate: true }
)

// Mobile OTP functions
const sendMobileOtp = async () => {
  sendingMobileOtp.value = true

  try {
    // Clean mobile number
    const cleanMobile = mobileFormState.mobile.replace(/[\s-]/g, '')

    const response = await $fetch<{ demo?: boolean, otp?: string }>(`${config.public.apiBase}/api/auth/send-otp`, {
      method: 'POST',
      body: {
        type: 'mobile',
        value: cleanMobile
      }
    })

    mobileOtpSent.value = true
    startMobileResendCooldown()

    toast.add({
      title: 'Code Sent',
      description: 'Verification code sent to your mobile',
      color: 'success'
    })
  } catch (error: unknown) {
    const err = error as { data?: { message?: string } }
    toast.add({
      title: 'Error',
      description: err.data?.message || 'Failed to send verification code',
      color: 'error'
    })
  } finally {
    sendingMobileOtp.value = false
  }
}

const verifyMobileOtp = async () => {
  verifyingMobileOtp.value = true

  try {
    const cleanMobile = mobileFormState.mobile.replace(/[\s-]/g, '')

    await useSanctumFetch(`${config.public.apiBase}/api/onboarding/verify-contact`, {
      method: 'POST',
      body: {
        type: 'mobile',
        value: cleanMobile,
        otp: mobileFormState.otp
      }
    })

    mobileVerified.value = true
    emit('verified')
    toast.add({
      title: 'Verified!',
      description: 'Your mobile has been verified',
      color: 'success'
    })
  } catch (error: unknown) {
    const err = error as { data?: { message?: string } }
    toast.add({
      title: 'Verification Failed',
      description: err.data?.message || 'Invalid or expired code',
      color: 'error'
    })
  } finally {
    verifyingMobileOtp.value = false
  }
}

const resendMobileOtp = () => {
  mobileFormState.otp = ''
  sendMobileOtp()
}

const startMobileResendCooldown = () => {
  mobileResendCooldown.value = 60
  const interval = setInterval(() => {
    mobileResendCooldown.value--
    if (mobileResendCooldown.value <= 0) {
      clearInterval(interval)
    }
  }, 1000)
}

// Email OTP functions
const sendEmailOtp = async () => {
  sendingEmailOtp.value = true

  try {
    const response = await $fetch<{ demo?: boolean, otp?: string }>(`${config.public.apiBase}/api/auth/send-otp`, {
      method: 'POST',
      body: {
        type: 'email',
        value: emailFormState.email
      }
    })

    emailOtpSent.value = true
    startEmailResendCooldown()

    toast.add({
      title: 'Code Sent',
      description: 'Verification code sent to your email',
      color: 'success'
    })
  } catch (error: unknown) {
    const err = error as { data?: { message?: string } }
    toast.add({
      title: 'Error',
      description: err.data?.message || 'Failed to send verification code',
      color: 'error'
    })
  } finally {
    sendingEmailOtp.value = false
  }
}

const verifyEmailOtp = async () => {
  verifyingEmailOtp.value = true

  try {
    await useSanctumFetch(`${config.public.apiBase}/api/onboarding/verify-contact`, {
      method: 'POST',
      body: {
        type: 'email',
        value: emailFormState.email,
        otp: emailFormState.otp
      }
    })

    emailVerified.value = true
    emit('verified')
    toast.add({
      title: 'Verified!',
      description: 'Your email has been verified',
      color: 'success'
    })
  } catch (error: unknown) {
    const err = error as { data?: { message?: string } }
    toast.add({
      title: 'Verification Failed',
      description: err.data?.message || 'Invalid or expired code',
      color: 'error'
    })
  } finally {
    verifyingEmailOtp.value = false
  }
}

const resendEmailOtp = () => {
  emailFormState.otp = ''
  sendEmailOtp()
}

const skipEmail = () => {
  emailFormState.email = ''
  emailFormState.otp = ''
  emailOtpSent.value = false
  emailVerified.value = false
}

const startEmailResendCooldown = () => {
  emailResendCooldown.value = 60
  const interval = setInterval(() => {
    emailResendCooldown.value--
    if (emailResendCooldown.value <= 0) {
      clearInterval(interval)
    }
  }, 1000)
}

// Expose validation
const validate = (): boolean => {
  return hasMobileVerified.value || mobileVerified.value
}

const getData = () => ({
  email: emailFormState.email || undefined,
  mobile: mobileFormState.mobile || undefined
})

defineExpose({
  validate,
  getData
})
</script>
