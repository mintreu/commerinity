<script setup lang="ts">
/**
 * Profile Settings Page
 * Account settings, preferences, and configuration
 */

definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const toast = useToast()
const config = useRuntimeConfig()
const user = useCurrentUser()

// Settings sections
const settingsSections = [
  {
    title: 'Account',
    items: [
      {
        icon: 'i-lucide-user',
        label: 'Edit Profile',
        description: 'Update your personal information',
        to: '/profile/edit',
        color: 'primary',
      },
      {
        icon: 'i-lucide-lock',
        label: 'Change Password',
        description: 'Update your account password',
        to: '/profile/change-password',
        color: 'amber',
      },
      {
        icon: 'i-lucide-shield-check',
        label: 'KYC Verification',
        description: 'Verify your identity',
        to: '/profile/kyc',
        color: 'green',
      },
    ]
  },
  {
    title: 'Wallet & Payments',
    items: [
      {
        icon: 'i-lucide-wallet',
        label: 'Wallet',
        description: 'View balance and transactions',
        to: '/wallet',
        color: 'purple',
      },
      {
        icon: 'i-lucide-building-2',
        label: 'Bank Accounts',
        description: 'Manage your withdrawal accounts',
        to: '/wallet/bank-accounts',
        color: 'indigo',
      },
      {
        icon: 'i-lucide-key',
        label: 'Wallet PIN',
        description: 'Manage your wallet security PIN',
        to: '/wallet/setup-pin',
        color: 'rose',
      },
    ]
  },
  {
    title: 'Location',
    items: [
      {
        icon: 'i-lucide-map-pin',
        label: 'Addresses',
        description: 'Manage your saved addresses',
        to: '/addresses',
        color: 'sky',
      },
    ]
  },
  {
    title: 'Notifications',
    items: [
      {
        icon: 'i-lucide-bell',
        label: 'Notifications',
        description: 'View your notifications',
        to: '/notifications',
        color: 'orange',
      },
    ]
  },
]

// Danger zone actions
const showDeleteModal = ref(false)
const deleteConfirmText = ref('')
const deleting = ref(false)

const canDelete = computed(() => {
  return deleteConfirmText.value === 'DELETE'
})

const deleteAccount = async () => {
  if (!canDelete.value) return

  deleting.value = true
  try {
    await useSanctumFetch(`${config.public.apiBase}/api/account`, {
      method: 'DELETE',
    })

    toast.add({
      title: 'Account Deleted',
      description: 'Your account has been permanently deleted.',
      color: 'success'
    })

    // Redirect to login
    navigateTo('/auth/login')
  } catch (e: any) {
    toast.add({
      title: 'Delete Failed',
      description: e.data?.message || 'Failed to delete account. Please try again.',
      color: 'error'
    })
  } finally {
    deleting.value = false
    showDeleteModal.value = false
  }
}
</script>

<template>
  <div class="max-w-2xl mx-auto">
    <div class="glass-card overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-6 text-white">
        <div class="flex items-center gap-4">
          <NuxtLink
            to="/profile"
            class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center hover:bg-white/30 transition-colors"
          >
            <UIcon
              name="i-lucide-arrow-left"
              class="w-5 h-5"
            />
          </NuxtLink>
          <div>
            <h1 class="text-xl font-bold">
              Settings
            </h1>
            <p class="text-slate-300 text-sm">
              Manage your account preferences
            </p>
          </div>
        </div>
      </div>

      <div class="p-6 space-y-8">
        <!-- Settings Sections -->
        <div
          v-for="section in settingsSections"
          :key="section.title"
          class="space-y-3"
        >
          <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
            {{ section.title }}
          </h2>
          <div class="space-y-2">
            <NuxtLink
              v-for="item in section.items"
              :key="item.label"
              :to="item.to"
              class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            >
              <div
                :class="[
                  'w-10 h-10 rounded-xl flex items-center justify-center',
                  item.color === 'primary' ? 'bg-blue-100 dark:bg-blue-900/30' : '',
                  item.color === 'amber' ? 'bg-amber-100 dark:bg-amber-900/30' : '',
                  item.color === 'green' ? 'bg-green-100 dark:bg-green-900/30' : '',
                  item.color === 'purple' ? 'bg-purple-100 dark:bg-purple-900/30' : '',
                  item.color === 'indigo' ? 'bg-indigo-100 dark:bg-indigo-900/30' : '',
                  item.color === 'rose' ? 'bg-rose-100 dark:bg-rose-900/30' : '',
                  item.color === 'sky' ? 'bg-sky-100 dark:bg-sky-900/30' : '',
                  item.color === 'orange' ? 'bg-orange-100 dark:bg-orange-900/30' : '',
                ]"
              >
                <UIcon
                  :name="item.icon"
                  :class="[
                    'w-5 h-5',
                    item.color === 'primary' ? 'text-blue-600 dark:text-blue-400' : '',
                    item.color === 'amber' ? 'text-amber-600 dark:text-amber-400' : '',
                    item.color === 'green' ? 'text-green-600 dark:text-green-400' : '',
                    item.color === 'purple' ? 'text-purple-600 dark:text-purple-400' : '',
                    item.color === 'indigo' ? 'text-indigo-600 dark:text-indigo-400' : '',
                    item.color === 'rose' ? 'text-rose-600 dark:text-rose-400' : '',
                    item.color === 'sky' ? 'text-sky-600 dark:text-sky-400' : '',
                    item.color === 'orange' ? 'text-orange-600 dark:text-orange-400' : '',
                  ]"
                />
              </div>
              <div class="flex-1">
                <p class="font-medium text-slate-900 dark:text-white">
                  {{ item.label }}
                </p>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  {{ item.description }}
                </p>
              </div>
              <UIcon
                name="i-lucide-chevron-right"
                class="w-5 h-5 text-slate-400"
              />
            </NuxtLink>
          </div>
        </div>

        <!-- App Info -->
        <div class="space-y-3">
          <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
            About
          </h2>
          <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 space-y-2">
            <div class="flex justify-between">
              <span class="text-slate-600 dark:text-slate-400">App Version</span>
              <span class="font-medium text-slate-900 dark:text-white">1.0.0</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-600 dark:text-slate-400">User ID</span>
              <span class="font-mono text-sm text-slate-900 dark:text-white">
                {{ user?.uuid?.slice(0, 8) }}...
              </span>
            </div>
          </div>
        </div>

        <!-- Danger Zone -->
        <div class="space-y-3 pt-4 border-t border-slate-200 dark:border-slate-700">
          <h2 class="text-sm font-semibold text-red-500 uppercase tracking-wide">
            Danger Zone
          </h2>
          <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
            <div class="flex items-start gap-4">
              <UIcon
                name="i-lucide-alert-triangle"
                class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5"
              />
              <div class="flex-1">
                <p class="font-medium text-red-800 dark:text-red-300">
                  Delete Account
                </p>
                <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                  Permanently delete your account and all associated data.
                  This action cannot be undone.
                </p>
                <UButton
                  color="error"
                  variant="outline"
                  size="sm"
                  class="mt-3"
                  @click="showDeleteModal = true"
                >
                  Delete My Account
                </UButton>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Account Modal -->
    <UModal v-model:open="showDeleteModal">
      <template #content>
        <div class="p-6 space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
              <UIcon
                name="i-lucide-alert-triangle"
                class="w-6 h-6 text-red-600"
              />
            </div>
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                Delete Account
              </h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">
                This action is permanent and irreversible
              </p>
            </div>
          </div>

          <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 text-sm text-red-700 dark:text-red-300">
            <p>
              Deleting your account will:
            </p>
            <ul class="list-disc list-inside mt-2 space-y-1">
              <li>Remove all your personal data</li>
              <li>Cancel any active subscriptions</li>
              <li>Forfeit any wallet balance</li>
              <li>Remove your network connections</li>
            </ul>
          </div>

          <UFormField label="Type DELETE to confirm">
            <UInput
              v-model="deleteConfirmText"
              placeholder="DELETE"
            />
          </UFormField>

          <div class="flex gap-3">
            <UButton
              variant="outline"
              color="neutral"
              class="flex-1"
              @click="showDeleteModal = false"
            >
              Cancel
            </UButton>
            <UButton
              color="error"
              class="flex-1"
              :disabled="!canDelete"
              :loading="deleting"
              @click="deleteAccount"
            >
              Delete Account
            </UButton>
          </div>
        </div>
      </template>
    </UModal>
  </div>
</template>
