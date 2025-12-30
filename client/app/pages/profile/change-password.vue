<template>
  <div class="space-y-6">
    <div class="glass-card p-8">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-3xl font-bold gradient-text-primary">
            Change Password
          </h2>
          <p class="text-slate-600 dark:text-slate-400 mt-1">
            Update your password to keep your account secure
          </p>
        </div>
        <NuxtLink to="/profile">
          <UButton
            color="gray"
            variant="ghost"
          >
            <UIcon
              name="i-lucide-x"
              class="w-4 h-4"
            />
            Cancel
          </UButton>
        </NuxtLink>
      </div>

      <!-- Success Message -->
      <div
        v-if="success"
        class="mb-6"
      >
        <div class="flex items-start gap-3 px-4 py-4 bg-green-50 dark:bg-green-900/20 border border-green-200/60 dark:border-green-800/60 rounded-xl text-green-700 dark:text-green-400">
          <UIcon
            name="i-lucide-check-circle"
            class="w-5 h-5 flex-shrink-0 mt-0.5"
          />
          <div>
            <p class="font-semibold mb-1">
              Password Changed Successfully!
            </p>
            <p class="text-sm">
              Your password has been updated. {{ logoutMessage }}
            </p>
          </div>
        </div>
      </div>

      <!-- Change Password Form -->
      <form
        v-if="!success"
        class="space-y-6"
        @submit.prevent="handleChangePassword"
      >
        <!-- Current Password -->
        <div class="space-y-2">
          <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
            <UIcon
              name="i-lucide-lock"
              class="w-4 h-4"
            />
            <span>Current Password</span>
            <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <input
              v-model="form.current_password"
              :type="showCurrentPassword ? 'text' : 'password'"
              required
              placeholder="Enter your current password"
              class="w-full px-4 py-3 pr-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            >
            <button
              type="button"
              class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors"
              @click="showCurrentPassword = !showCurrentPassword"
            >
              <UIcon
                :name="showCurrentPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                class="w-4 h-4"
              />
            </button>
          </div>
        </div>

        <!-- New Password -->
        <div class="space-y-2">
          <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
            <UIcon
              name="i-lucide-key"
              class="w-4 h-4"
            />
            <span>New Password</span>
            <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <input
              v-model="form.password"
              :type="showNewPassword ? 'text' : 'password'"
              required
              placeholder="At least 8 characters"
              class="w-full px-4 py-3 pr-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            >
            <button
              type="button"
              class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors"
              @click="showNewPassword = !showNewPassword"
            >
              <UIcon
                :name="showNewPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                class="w-4 h-4"
              />
            </button>
          </div>
        </div>

        <!-- Confirm New Password -->
        <div class="space-y-2">
          <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
            <UIcon
              name="i-lucide-shield-check"
              class="w-4 h-4"
            />
            <span>Confirm New Password</span>
            <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <input
              v-model="form.password_confirmation"
              :type="showConfirmPassword ? 'text' : 'password'"
              required
              placeholder="Re-enter your new password"
              class="w-full px-4 py-3 pr-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            >
            <button
              type="button"
              class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors"
              @click="showConfirmPassword = !showConfirmPassword"
            >
              <UIcon
                :name="showConfirmPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                class="w-4 h-4"
              />
            </button>
          </div>
        </div>

        <!-- Password Strength Indicator -->
        <div
          v-if="form.password"
          class="space-y-2"
        >
          <div class="flex items-center justify-between text-xs">
            <span class="text-slate-600 dark:text-slate-400">Password Strength:</span>
            <span
              :class="passwordStrengthColor"
              class="font-semibold"
            >{{ passwordStrengthText }}</span>
          </div>
          <div class="h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
            <div
              :class="passwordStrengthColor"
              :style="{ width: `${passwordStrength}%` }"
              class="h-full transition-all duration-300"
            />
          </div>
        </div>

        <!-- Logout Other Devices Option -->
        <div class="flex items-start gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/60 rounded-xl">
          <input
            id="logout-devices"
            v-model="form.logout_other_devices"
            type="checkbox"
            class="mt-1 w-4 h-4 text-blue-600 rounded border-slate-300 dark:border-slate-600 focus:ring-blue-500"
          >
          <label
            for="logout-devices"
            class="flex-1 cursor-pointer"
          >
            <div class="font-semibold text-slate-900 dark:text-white text-sm mb-1">
              Logout from all other devices
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-400">
              For security, sign out all devices except this one after changing password
            </p>
          </label>
        </div>

        <!-- Error Alert -->
        <div
          v-if="error"
          class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200/60 dark:border-red-800/60 rounded-xl text-red-600 dark:text-red-400 text-sm"
        >
          <UIcon
            name="i-lucide-alert-circle"
            class="w-5 h-5 flex-shrink-0"
          />
          <p>{{ error }}</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-4 pt-4">
          <button
            type="submit"
            :disabled="loading || form.password !== form.password_confirmation"
            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-300"
          >
            <UIcon
              :name="loading ? 'i-lucide-loader-2' : 'i-lucide-check'"
              :class="{ 'animate-spin': loading }"
              class="w-5 h-5"
            />
            <span>{{ loading ? 'Changing...' : 'Change Password' }}</span>
          </button>
          <NuxtLink
            to="/profile"
            class="flex-shrink-0"
          >
            <UButton
              color="gray"
              variant="soft"
              size="lg"
            >
              Cancel
            </UButton>
          </NuxtLink>
        </div>
      </form>

      <!-- Back to Profile Button (after success) -->
      <div
        v-if="success"
        class="mt-6"
      >
        <NuxtLink
          to="/profile"
          class="block"
        >
          <UButton
            color="primary"
            size="lg"
            block
          >
            <UIcon
              name="i-lucide-arrow-left"
              class="w-4 h-4"
            />
            Back to Profile
          </UButton>
        </NuxtLink>
      </div>
    </div>

    <!-- Security Tips -->
    <div class="glass-card p-6">
      <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
        <UIcon
          name="i-lucide-lightbulb"
          class="w-5 h-5 text-amber-500"
        />
        Password Security Tips
      </h3>
      <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-400">
        <li class="flex items-start gap-2">
          <UIcon
            name="i-lucide-check"
            class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0"
          />
          <span>Use at least 8 characters with a mix of uppercase, lowercase, numbers, and symbols</span>
        </li>
        <li class="flex items-start gap-2">
          <UIcon
            name="i-lucide-check"
            class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0"
          />
          <span>Avoid using personal information like your name, birthdate, or common words</span>
        </li>
        <li class="flex items-start gap-2">
          <UIcon
            name="i-lucide-check"
            class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0"
          />
          <span>Don't reuse passwords from other websites or services</span>
        </li>
        <li class="flex items-start gap-2">
          <UIcon
            name="i-lucide-check"
            class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0"
          />
          <span>Consider using a password manager to generate and store strong passwords</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: '$auth',
  title: 'Change Password',
  layout: 'dashboard'
})

const config = useRuntimeConfig()
const router = useRouter()

const loading = ref(false)
const success = ref(false)
const error = ref<string | null>(null)
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const form = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
  logout_other_devices: true
})

const passwordStrength = computed(() => {
  const password = form.password
  if (!password) return 0

  let strength = 0
  if (password.length >= 8) strength += 25
  if (password.length >= 12) strength += 25
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25
  if (/\d/.test(password)) strength += 12.5
  if (/[^a-zA-Z0-9]/.test(password)) strength += 12.5

  return Math.min(100, strength)
})

const passwordStrengthText = computed(() => {
  const strength = passwordStrength.value
  if (strength < 25) return 'Weak'
  if (strength < 50) return 'Fair'
  if (strength < 75) return 'Good'
  return 'Strong'
})

const passwordStrengthColor = computed(() => {
  const strength = passwordStrength.value
  if (strength < 25) return 'text-red-500 bg-red-500'
  if (strength < 50) return 'text-amber-500 bg-amber-500'
  if (strength < 75) return 'text-blue-500 bg-blue-500'
  return 'text-green-500 bg-green-500'
})

const logoutMessage = computed(() => {
  return form.logout_other_devices
    ? 'All other devices have been signed out.'
    : 'You remain signed in on all devices.'
})

const handleChangePassword = async () => {
  if (form.password !== form.password_confirmation) {
    error.value = 'Passwords do not match'
    return
  }

  if (form.password.length < 8) {
    error.value = 'Password must be at least 8 characters'
    return
  }

  loading.value = true
  success.value = false
  error.value = null

  try {
    await useSanctumFetch(`${config.public.apiBase}/api/user/password`, {
      method: 'PUT',
      body: {
        current_password: form.current_password,
        password: form.password,
        password_confirmation: form.password_confirmation,
        logout_other_devices: form.logout_other_devices
      }
    })

    success.value = true

    // Redirect after showing success message
    setTimeout(() => {
      router.push('/profile')
    }, 3000)
  } catch (err: unknown) {
    const fetchError = err as { data?: { message?: string, errors?: Record<string, string[]> } }
    if (fetchError.data?.errors) {
      const errors = Object.values(fetchError.data.errors).flat()
      error.value = errors[0] as string
    } else {
      error.value = fetchError.data?.message || 'Failed to change password'
    }
  } finally {
    loading.value = false
  }
}
</script>
