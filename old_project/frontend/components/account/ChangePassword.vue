<template>
  <!-- Floating Background Orbs -->
  <div class="fixed inset-0 pointer-events-none overflow-hidden">
    <div ref="orb1" class="absolute top-20 right-16 w-32 h-32 bg-gradient-to-r from-purple-400/10 to-pink-400/10 dark:from-purple-400/5 dark:to-pink-400/5 rounded-full blur-2xl opacity-60"></div>
    <div ref="orb2" class="absolute bottom-20 left-16 w-24 h-24 bg-gradient-to-r from-red-400/8 to-orange-400/8 dark:from-red-400/4 dark:to-orange-400/4 rounded-full blur-2xl opacity-40"></div>
  </div>

  <!-- Success Modal -->
  <Teleport to="body">
    <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="closeSuccessModal">
      <div class="w-full max-w-md mx-auto bg-white/95 dark:bg-slate-800/95 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-3xl">
        <!-- Success Header -->
        <div class="flex items-center gap-6 p-8 border-b border-green-200/50 dark:border-green-800/50">
          <div class="w-16 h-16 bg-gradient-to-br from-green-600 to-emerald-600 rounded-3xl flex items-center justify-center shadow-2xl">
            <Icon name="mdi:shield-check" class="w-8 h-8 text-white" />
          </div>
          <div>
            <h3 class="text-2xl font-bold text-green-600 dark:text-green-400">Password Updated!</h3>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Your account is now more secure</p>
          </div>
        </div>

        <!-- Success Body -->
        <div class="p-8">
          <p class="text-slate-600 dark:text-slate-400 mb-8 leading-relaxed">
            Your password has been successfully updated. Your account security has been enhanced.
          </p>

          <button
              @click="closeSuccessModal"
              class="w-full px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-2xl font-bold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
          >
            Continue
          </button>
        </div>
      </div>
    </div>
  </Teleport>

  <div class="relative z-10 bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
    <!-- Header Background Gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 via-pink-500/5 to-red-500/5 opacity-70"></div>

    <div class="relative z-10 grid grid-cols-1 md:grid-cols-[280px_1fr] divide-y md:divide-y-0 md:divide-x divide-slate-200/50 dark:divide-slate-700/50">

      <!-- Header Section -->
      <div class="p-8 bg-gradient-to-br from-purple-50/50 to-pink-50/50 dark:from-purple-900/20 dark:to-pink-900/20 backdrop-blur-sm">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg hover:scale-105 transition-transform duration-300">
            <Icon name="mdi:key-variant" class="w-6 h-6 text-white" />
          </div>
          <div>
            <h2 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Change Password</h2>
            <div class="w-16 h-1 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full mt-2"></div>
          </div>
        </div>
        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
          Use a strong and unique password to secure your account and protect your financial data.
        </p>

        <!-- Security Tips -->
        <div class="space-y-3">
          <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
            <Icon name="mdi:shield" class="w-4 h-4 text-purple-600 dark:text-purple-400" />
            Password Requirements
          </h4>
          <div class="space-y-2 text-xs text-slate-500 dark:text-slate-400">
            <div class="flex items-center gap-2" :class="{ 'text-green-600 dark:text-green-400': passwordValidation.minLength }">
              <Icon :name="passwordValidation.minLength ? 'mdi:check-circle' : 'mdi:circle-outline'" class="w-3 h-3" />
              <span>At least 8 characters</span>
            </div>
            <div class="flex items-center gap-2" :class="{ 'text-green-600 dark:text-green-400': passwordValidation.hasUpper }">
              <Icon :name="passwordValidation.hasUpper ? 'mdi:check-circle' : 'mdi:circle-outline'" class="w-3 h-3" />
              <span>One uppercase letter</span>
            </div>
            <div class="flex items-center gap-2" :class="{ 'text-green-600 dark:text-green-400': passwordValidation.hasLower }">
              <Icon :name="passwordValidation.hasLower ? 'mdi:check-circle' : 'mdi:circle-outline'" class="w-3 h-3" />
              <span>One lowercase letter</span>
            </div>
            <div class="flex items-center gap-2" :class="{ 'text-green-600 dark:text-green-400': passwordValidation.hasNumber }">
              <Icon :name="passwordValidation.hasNumber ? 'mdi:check-circle' : 'mdi:circle-outline'" class="w-3 h-3" />
              <span>One number</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Form Section -->
      <div class="p-8">
        <form @submit.prevent="submit" class="space-y-8">
          <div class="space-y-6">
            <PasswordField
                label="Current Password"
                v-model="form.current_password"
                field="current_password"
                :error="form.errors.current_password"
                autocomplete="current-password"
                placeholder="Enter your current password"
                @clearError="forgetError"
            />
            <PasswordField
                label="New Password"
                v-model="form.password"
                field="password"
                :error="form.errors.password"
                autocomplete="new-password"
                placeholder="Enter your new password"
                :show-strength="true"
                @clearError="forgetError"
            />
            <PasswordField
                label="Confirm New Password"
                v-model="form.password_confirmation"
                field="password_confirmation"
                :error="form.errors.password_confirmation"
                autocomplete="new-password"
                placeholder="Confirm your new password"
                @clearError="forgetError"
            />
          </div>

          <!-- Password Match Indicator -->
          <div v-if="form.password && form.password_confirmation" class="px-4 py-3 rounded-2xl border backdrop-blur-sm" :class="passwordsMatch ? 'bg-green-50/80 dark:bg-green-900/20 border-green-200/60 dark:border-green-800/60 text-green-600 dark:text-green-400' : 'bg-red-50/80 dark:bg-red-900/20 border-red-200/60 dark:border-red-800/60 text-red-600 dark:text-red-400'">
            <div class="flex items-center gap-2 text-sm font-medium">
              <Icon :name="passwordsMatch ? 'mdi:check-circle' : 'mdi:alert-circle'" class="w-5 h-5" />
              <span>{{ passwordsMatch ? 'Passwords match' : 'Passwords do not match' }}</span>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end pt-6 border-t border-slate-200/50 dark:border-slate-700/50">
            <button
                type="submit"
                class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-bold shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 group"
                :disabled="form.processing || !isFormValid"
            >
              <Icon name="mdi:loading" v-if="form.processing" class="w-6 h-6 animate-spin" />
              <Icon name="mdi:shield-key" v-else class="w-6 h-6 group-hover:scale-110 transition-transform duration-300" />
              <span>{{ form.processing ? 'Updating Password...' : 'Update Password' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRuntimeConfig, useSanctumForm, useSanctum } from '#imports'
import PasswordField from './PasswordField.vue'

// GSAP imports (client-side only)
let gsap: any = null
if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// Composables
const { $toast } = useNuxtApp()
const emit = defineEmits<{ success: [] }>()
const { refreshUser } = useSanctum()
const config = useRuntimeConfig()

// Refs for animations
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()

// State
const showSuccessModal = ref(false)

if (!config.public?.apiBase) {
  throw new Error('Missing runtime config: public.apiBase is required for API calls')
}

// Sanctum form
const form = useSanctumForm('put', `${config.public.apiBase}/account/password`, {
  current_password: '',
  password: '',
  password_confirmation: '',
})

// Computed properties
const passwordValidation = computed(() => {
  const password = form.password
  return {
    minLength: password.length >= 8,
    hasUpper: /[A-Z]/.test(password),
    hasLower: /[a-z]/.test(password),
    hasNumber: /\d/.test(password),
  }
})

const passwordStrength = computed(() => {
  const validations = Object.values(passwordValidation.value)
  const score = validations.filter(Boolean).length

  if (score <= 1) return { level: 'weak', color: 'red', percentage: 25 }
  if (score <= 2) return { level: 'fair', color: 'orange', percentage: 50 }
  if (score <= 3) return { level: 'good', color: 'yellow', percentage: 75 }
  return { level: 'strong', color: 'green', percentage: 100 }
})

const passwordsMatch = computed(() => {
  return form.password && form.password_confirmation && form.password === form.password_confirmation
})

const isFormValid = computed(() => {
  return (
      form.current_password.length >= 6 &&
      form.password.length >= 8 &&
      passwordsMatch.value &&
      Object.values(passwordValidation.value).every(Boolean)
  )
})

// Lifecycle
onMounted(() => {
  // Initialize GSAP animations
  if (process.client && gsap) {
    gsap.to([orb1.value, orb2.value], {
      rotation: 360,
      duration: 30,
      repeat: -1,
      ease: 'none'
    })
  }
})

// Methods
function forgetError(field: string) {
  if ((form as any).forgetError) {
    (form as any).forgetError(field)
  } else if (form.errors && field in form.errors) {
    delete form.errors[field]
  }
}

async function submit() {
  form.errors = {}

  // Enhanced client-side validation
  if (!form.current_password || form.current_password.length < 6) {
    form.errors.current_password = 'Please enter your current password (minimum 6 characters).'
    return
  }

  if (!Object.values(passwordValidation.value).every(Boolean)) {
    form.errors.password = 'Password must meet all security requirements.'
    return
  }

  if (!passwordsMatch.value) {
    form.errors.password_confirmation = 'Passwords do not match.'
    return
  }

  try {
    const response = await form.submit()

    // Handle successful response
    if (response.success) {
      await refreshUser?.()
      showSuccessModal.value = true
      form.reset?.()

      $toast.success({
        title: 'Password Updated Successfully!',
        message: response.message || 'Your password has been updated successfully.',
      })
    }
  } catch (err: any) {
    // Handle API errors with proper user feedback
    const apiMessage =
        err?.response?.data?.message ||
        err?.data?.message ||
        err?.response?.data?.errors?.current_password?.[0] ||
        'Failed to update password. Please try again.'

    $toast.error({
      title: 'Password Update Failed!',
      message: apiMessage,
    })

    // Set specific field error if it's about current password
    if (apiMessage.includes('current password') || apiMessage.includes('incorrect')) {
      form.errors.current_password = apiMessage
    }

    console.error('Password update failed:', err)
  }
}

function closeSuccessModal() {
  showSuccessModal.value = false
  emit('success')
}

// Watchers
watch(() => form.password, () => {
  // Clear password confirmation error when password changes
  if (form.errors.password_confirmation) {
    delete form.errors.password_confirmation
  }
})

watch(() => form.password_confirmation, () => {
  // Clear confirmation error when typing
  if (form.errors.password_confirmation) {
    delete form.errors.password_confirmation
  }
})
</script>
