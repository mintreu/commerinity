<script setup lang="ts">
/**
 * Settings - Premium VRIDDHI VIKASH Design
 * Enhanced visual hierarchy with glassmorphic cards and polished typography
 */

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const toast = useToast()
const config = useRuntimeConfig()
const user = useCurrentUser()

const settingsSections = [
  {
    title: 'Identity & Access',
    items: [
      { icon: 'i-lucide-user-pen', label: 'Modify Profile', desc: 'Update name, bio and primary contact', to: '/profile/edit', color: 'primary' },
      { icon: 'i-lucide-fingerprint', label: 'Identity Verification', desc: 'Manage your KYC compliance status', to: '/profile/kyc', color: 'emerald' },
      { icon: 'i-lucide-shield-check', label: 'Security Vault', desc: 'Manage password and transaction keys', to: '/profile/change-password', color: 'amber' }
    ]
  },
  {
    title: 'Financial Ecosystem',
    items: [
      { icon: 'i-lucide-wallet', label: 'Wallet Hub', desc: 'Manage balance and payment methods', to: '/wallet', color: 'purple' },
      { icon: 'i-lucide-building-2', label: 'Settlement Accounts', desc: 'Linked bank accounts for withdrawals', to: '/wallet/bank-accounts', color: 'indigo' },
      { icon: 'i-lucide-key-round', label: 'Transaction PIN', desc: 'Highly secure 6-digit payment code', to: '/wallet/setup-pin', color: 'rose' }
    ]
  },
  {
    title: 'Preferences',
    items: [
      { icon: 'i-lucide-map-pin', label: 'Saved Addresses', desc: 'Home, work and other locations', to: '/addresses', color: 'sky' },
      { icon: 'i-lucide-bell-ring', label: 'Alert Protocol', desc: 'Notification and email preferences', to: '/notifications', color: 'orange' }
    ]
  }
]

const showDeleteModal = ref(false)
const deleteConfirmText = ref('')
const deleting = ref(false)

const canDelete = computed(() => deleteConfirmText.value === 'DELETE')

const deleteAccount = async () => {
  if (!canDelete.value) return
  deleting.value = true
  try {
    await useSanctumFetch(`${config.public.apiBase}/api/account`, { method: 'DELETE' })
    toast.add({ title: 'Account Expunged', description: 'Your footprint has been removed.', color: 'success' })
    navigateTo('/auth/login')
  } catch (e: any) {
    toast.add({ title: 'Operation Failed', description: e.data?.message || 'Failed to delete account.', color: 'error' })
  } finally {
    deleting.value = false
    showDeleteModal.value = false
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-12 pb-20">
    <!-- Header -->
    <div class="space-y-2">
      <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
        Account <span class="bg-gradient-to-r from-primary-600 to-indigo-600 bg-clip-text text-transparent">Nexus</span>
      </h1>
      <p class="text-slate-500 dark:text-slate-400 font-medium">
        Configure your digital footprint and financial security parameters.
      </p>
    </div>

    <!-- Sections Grid -->
    <div class="grid gap-12">
      <div
        v-for="section in settingsSections"
        :key="section.title"
        class="space-y-6"
      >
        <h2 class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 pl-4 border-l-4 border-primary-500/20">
          {{ section.title }}
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <NuxtLink
            v-for="item in section.items"
            :key="item.label"
            :to="item.to"
            class="glass-card group p-6 border-none ring-1 ring-slate-200 dark:ring-slate-800 hover:ring-primary-500/50 hover:bg-white dark:hover:bg-slate-800 transition-all duration-300"
          >
            <div class="flex items-center gap-5">
              <div :class="`w-14 h-14 rounded-2xl flex items-center justify-center bg-${item.color}-500/10 text-${item.color}-500 group-hover:scale-110 transition-transform duration-500`">
                <UIcon
                  :name="item.icon"
                  class="w-7 h-7"
                />
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-black text-slate-900 dark:text-white truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                  {{ item.label }}
                </h3>
                <p class="text-xs text-slate-500 font-medium truncate mt-0.5">
                  {{ item.desc }}
                </p>
              </div>
              <UIcon
                name="i-lucide-chevron-right"
                class="w-5 h-5 text-slate-300 group-hover:text-primary-500 transition-colors"
              />
            </div>
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- System Info & Danger Zone -->
    <div class="pt-12 border-t border-slate-100 dark:border-slate-800 grid md:grid-cols-2 gap-8">
      <!-- App Metadata -->
      <div class="glass-card p-8 border-none bg-slate-50/50 dark:bg-slate-900/30">
        <div class="flex items-center gap-3 mb-6">
          <UIcon
            name="i-lucide-info"
            class="text-slate-400"
          />
          <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">
            Node Information
          </h3>
        </div>
        <div class="space-y-4">
          <div class="flex justify-between items-center text-xs">
            <span class="font-bold text-slate-400 uppercase">Core Version</span>
            <span class="font-black text-slate-900 dark:text-white">v4.2.1-stable</span>
          </div>
          <div class="flex justify-between items-center text-xs">
            <span class="font-bold text-slate-400 uppercase">Identity Hash</span>
            <span class="font-mono text-slate-900 dark:text-white bg-white dark:bg-slate-800 px-2 py-1 rounded">
              {{ user?.uuid?.slice(0, 16).toUpperCase() }}...
            </span>
          </div>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="glass-card p-8 border-none bg-red-500/5 ring-1 ring-red-500/20">
        <div class="flex items-center gap-3 mb-4 text-red-500">
          <UIcon
            name="i-lucide-shield-alert"
            class="w-6 h-6"
          />
          <h3 class="text-sm font-black uppercase tracking-widest">
            Termination Zone
          </h3>
        </div>
        <p class="text-xs text-red-600/70 font-medium mb-6">
          Deleting your account will purge all transaction history, active subscriptions, and affiliate status. This cannot be recovered.
        </p>
        <UButton
          color="error"
          variant="soft"
          size="lg"
          block
          class="rounded-xl font-black py-4 active:scale-95 transition-transform"
          @click="showDeleteModal = true"
        >
          Purge Account Data
        </UButton>
      </div>
    </div>

    <!-- Termination Confirmation Modal -->
    <UModal
      v-model:open="showDeleteModal"
      :ui="{
        content: 'bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl border border-red-500/20 rounded-[40px]',
        container: 'flex items-center justify-center p-4'
      }"
    >
      <div class="p-10 text-center">
        <div class="w-20 h-20 bg-red-500/10 text-red-600 rounded-[30px] flex items-center justify-center mx-auto mb-6">
          <UIcon
            name="i-lucide-alert-octagon"
            class="w-10 h-10"
          />
        </div>

        <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-2">
          Final Confirmation
        </h3>
        <p class="text-slate-500 dark:text-slate-400 text-sm mb-8">
          This process is final. Type <span class="bg-red-500/10 text-red-600 font-black px-2 py-0.5 rounded">DELETE</span> below to authorize termination.
        </p>

        <UFormField>
          <UInput
            v-model="deleteConfirmText"
            placeholder="TYPE HERE"
            class="text-center font-black h-16 rounded-2xl bg-white dark:bg-slate-800 shadow-inner border-none ring-1 ring-red-500/20 focus:ring-red-500"
            variant="none"
          />
        </UFormField>

        <div class="grid grid-cols-2 gap-4 mt-8">
          <UButton
            size="xl"
            color="neutral"
            variant="ghost"
            class="rounded-xl font-bold py-4 hover:bg-slate-100"
            @click="showDeleteModal = false; deleteConfirmText=''"
          >
            Cancel
          </UButton>
          <UButton
            size="xl"
            color="error"
            class="rounded-xl font-black py-4 shadow-xl shadow-red-500/20"
            :loading="deleting"
            :disabled="!canDelete"
            @click="deleteAccount"
          >
            Authorize
          </UButton>
        </div>
      </div>
    </UModal>
  </div>
</template>
