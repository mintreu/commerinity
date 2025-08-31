<template>
  <div class="bg-white dark:bg-gray-800 shadow rounded-xl mt-10">
    <div
        class="grid grid-cols-1 md:grid-cols-[250px_1fr] divide-y md:divide-y-0 md:divide-x divide-gray-200 dark:divide-gray-700"
    >
      <!-- Header -->
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
          Change Password
        </h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Use a strong and unique password to secure your account.
        </p>
      </div>

      <!-- Form -->
      <div class="p-6 space-y-6">
        <div class="space-y-4">
          <PasswordField
              label="Current Password"
              v-model="form.current_password"
              field="current_password"
              :error="form.errors.current_password"
              autocomplete="current-password"
              @clearError="forgetError"
          />
          <PasswordField
              label="New Password"
              v-model="form.password"
              field="password"
              :error="form.errors.password"
              autocomplete="new-password"
              @clearError="forgetError"
          />
          <PasswordField
              label="Confirm Password"
              v-model="form.password_confirmation"
              field="password_confirmation"
              :error="form.errors.password_confirmation"
              autocomplete="new-password"
              @clearError="forgetError"
          />
        </div>

        <!-- Actions -->
        <div class="flex flex-row">
          <div class="grow hidden md:block"></div>
          <button
              type="button"
              class="btn-primary"
              :disabled="form.processing"
              @click="submit"
          >
            {{ form.processing ? 'Updating...' : 'Update Password' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import PasswordField from './PasswordField.vue'
import { useRuntimeConfig, useSanctumForm, useSanctum } from '#imports'
import { useToast } from '#imports'

const toast = useToast()
const emit = defineEmits<{ (e: 'success'): void }>()

const { refreshUser } = useSanctum()
const config = useRuntimeConfig()

if (!config.public?.apiBase) {
  throw new Error('Missing runtime config: public.apiBase is required for API calls')
}

// Sanctum form
const form = useSanctumForm('put', `${config.public.apiBase}/account/password`, {
  current_password: '',
  password: '',
  password_confirmation: '',
})

/**
 * Clear a single error
 */
function forgetError(field: string) {
  if ((form as any).forgetError) {
    (form as any).forgetError(field)
  } else if (form.errors && field in form.errors) {
    delete form.errors[field]
  }
}

/**
 * Submit handler
 */
async function submit() {
  form.errors = {}

  // Client-side validation
  if (!form.current_password || form.current_password.length < 6) {
    form.errors.current_password = 'Please enter your current password (min 6 chars).'
    return
  }
  if (!form.password || form.password.length < 8) {
    form.errors.password = 'New password must be at least 8 characters.'
    return
  }
  if (form.password !== form.password_confirmation) {
    form.errors.password_confirmation = 'Passwords do not match.'
    return
  }

  try {
    await form.submit()
    await refreshUser?.()
    emit('success')
    form.reset?.()
    toast.success({
      title: 'Success!',
      message: 'Your password has been updated successfully.',
    })
  } catch (err: any) {
    // Only show API-provided message, avoid raw HTTP errors
    const apiMessage =
        err?.response?.data?.message ||
        err?.response?.data?.errors?.current_password?.[0] ||
        'Failed to update password. Please try again.'
    toast.error({
      title: 'Error!',
      message: apiMessage,
    })
    console.log('Password update failed:', apiMessage)
  }
}
</script>

<style scoped>
.btn-primary {
  @apply px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition disabled:opacity-50;
}
</style>
