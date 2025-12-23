<script setup lang="ts">
/**
 * Bank Accounts Management Page
 * View, add, and manage beneficiary bank accounts for withdrawals
 */

definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

interface BeneficiaryAccount {
  uuid: string
  type: string
  type_label: string
  is_bank: boolean
  is_upi: boolean
  account_number_masked: string | null
  ifsc_code: string | null
  bank_name: string | null
  branch_name: string | null
  upi_id: string | null
  holder_name: string
  display_name: string
  status: string
  status_label: string
  status_color: string
  is_verified: boolean
  can_receive_payout: boolean
  is_default: boolean
  verified_at: string | null
  created_at: string
}

interface AccountType {
  value: string
  label: string
  is_bank: boolean
  is_upi: boolean
}

const toast = useToast()
const config = useRuntimeConfig()

const loading = ref(true)
const beneficiaries = ref<BeneficiaryAccount[]>([])
const accountTypes = ref<AccountType[]>([])
const showAddModal = ref(false)
const addLoading = ref(false)
const deleteLoading = ref<string | null>(null)
const verifyingIfsc = ref(false)

// Form data for adding new account
const formData = ref({
  type: 'savings',
  holder_name: '',
  account_number: '',
  confirm_account_number: '',
  ifsc_code: '',
  bank_name: '',
  branch_name: '',
  upi_id: ''
})

const formErrors = ref<Record<string, string>>({})

const isBank = computed(() => {
  const type = accountTypes.value.find(t => t.value === formData.value.type)
  return type?.is_bank ?? true
})

onMounted(async () => {
  await Promise.all([fetchBeneficiaries(), fetchAccountTypes()])
})

// Fetch beneficiary accounts
const fetchBeneficiaries = async () => {
  loading.value = true
  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/beneficiaries`)
    beneficiaries.value = response.data || []
  } catch (e) {
    console.error('Failed to fetch beneficiaries:', e)
    toast.add({
      title: 'Error',
      description: 'Failed to load bank accounts',
      color: 'error'
    })
  } finally {
    loading.value = false
  }
}

// Fetch account types
const fetchAccountTypes = async () => {
  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/beneficiaries/types`)
    if (response.success) {
      accountTypes.value = response.data.types
    }
  } catch (e) {
    console.error('Failed to fetch account types:', e)
  }
}

// Validate IFSC and fetch bank details
const validateIfsc = async () => {
  const ifsc = formData.value.ifsc_code.toUpperCase()
  formData.value.ifsc_code = ifsc
  if (ifsc.length !== 11) return

  verifyingIfsc.value = true
  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/beneficiaries/verify-ifsc`, {
      method: 'POST',
      body: { ifsc_code: ifsc }
    })
    if (response.success) {
      formData.value.bank_name = response.data.bank_name || ''
      formData.value.branch_name = response.data.branch_name || ''
      formErrors.value.ifsc_code = ''
    }
  } catch (e: any) {
    formErrors.value.ifsc_code = e.data?.message || 'Invalid IFSC code'
    formData.value.bank_name = ''
    formData.value.branch_name = ''
  } finally {
    verifyingIfsc.value = false
  }
}

// Validate form
const validateForm = () => {
  formErrors.value = {}

  if (!formData.value.holder_name.trim()) {
    formErrors.value.holder_name = 'Account holder name is required'
    return false
  }

  if (isBank.value) {
    if (!formData.value.account_number || formData.value.account_number.length < 9) {
      formErrors.value.account_number = 'Enter valid account number'
      return false
    }

    if (formData.value.account_number !== formData.value.confirm_account_number) {
      formErrors.value.confirm_account_number = 'Account numbers do not match'
      return false
    }

    if (!formData.value.ifsc_code || formData.value.ifsc_code.length !== 11) {
      formErrors.value.ifsc_code = 'Enter valid 11-character IFSC code'
      return false
    }

    if (!formData.value.bank_name) {
      formErrors.value.ifsc_code = 'Please verify IFSC code to get bank details'
      return false
    }
  } else {
    // UPI validation
    if (!formData.value.upi_id || !formData.value.upi_id.includes('@')) {
      formErrors.value.upi_id = 'Enter valid UPI ID (e.g., name@upi)'
      return false
    }
  }

  return true
}

// Add beneficiary
const handleAddBeneficiary = async () => {
  if (!validateForm()) return

  addLoading.value = true
  try {
    const payload = {
      type: formData.value.type,
      holder_name: formData.value.holder_name,
      ...(isBank.value ? {
        account_number: formData.value.account_number,
        confirm_account_number: formData.value.confirm_account_number,
        ifsc_code: formData.value.ifsc_code,
        bank_name: formData.value.bank_name,
        branch_name: formData.value.branch_name
      } : {
        upi_id: formData.value.upi_id
      })
    }

    const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/beneficiaries`, {
      method: 'POST',
      body: payload
    })

    toast.add({
      title: 'Success',
      description: response.message || 'Bank account added successfully',
      color: 'success'
    })

    showAddModal.value = false
    resetForm()
    await fetchBeneficiaries()
  } catch (e: any) {
    if (e.data?.errors) {
      formErrors.value = Object.fromEntries(
        Object.entries(e.data.errors).map(([key, value]) => [key, (value as string[])[0]])
      )
    } else {
      toast.add({
        title: 'Failed',
        description: e.data?.message || 'Failed to add bank account',
        color: 'error'
      })
    }
  } finally {
    addLoading.value = false
  }
}

// Delete beneficiary
const handleDelete = async (uuid: string) => {
  if (!confirm('Are you sure you want to remove this bank account?')) return

  deleteLoading.value = uuid
  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/beneficiaries/${uuid}`, {
      method: 'DELETE'
    })

    toast.add({
      title: 'Removed',
      description: response.message || 'Bank account removed successfully',
      color: 'success'
    })

    await fetchBeneficiaries()
  } catch (e: any) {
    toast.add({
      title: 'Failed',
      description: e.data?.message || 'Failed to remove bank account',
      color: 'error'
    })
  } finally {
    deleteLoading.value = null
  }
}

// Set as default
const handleSetDefault = async (uuid: string) => {
  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/beneficiaries/${uuid}/default`, {
      method: 'POST'
    })

    toast.add({
      title: 'Updated',
      description: response.message || 'Default account updated',
      color: 'success'
    })

    await fetchBeneficiaries()
  } catch (e: any) {
    toast.add({
      title: 'Failed',
      description: e.data?.message || 'Failed to update default account',
      color: 'error'
    })
  }
}

// Verify beneficiary (demo mode)
const handleVerify = async (uuid: string) => {
  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/beneficiaries/${uuid}/verify`, {
      method: 'POST'
    })

    toast.add({
      title: 'Verified',
      description: response.message || 'Account verified successfully',
      color: 'success'
    })

    await fetchBeneficiaries()
  } catch (e: any) {
    toast.add({
      title: 'Info',
      description: e.data?.message || 'Verification pending',
      color: 'warning'
    })
  }
}

// Reset form
const resetForm = () => {
  formData.value = {
    type: 'savings',
    holder_name: '',
    account_number: '',
    confirm_account_number: '',
    ifsc_code: '',
    bank_name: '',
    branch_name: '',
    upi_id: ''
  }
  formErrors.value = {}
}

// Open add modal
const openAddModal = () => {
  resetForm()
  showAddModal.value = true
}

// Get badge color for status
const getStatusColor = (beneficiary: BeneficiaryAccount) => {
  if (beneficiary.is_verified) return 'success'
  if (beneficiary.status === 'rejected') return 'error'
  return 'warning'
}
</script>

<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <NuxtLink
          to="/wallet"
          class="w-10 h-10 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
        >
          <UIcon
            name="i-lucide-arrow-left"
            class="w-5 h-5 text-slate-600 dark:text-slate-400"
          />
        </NuxtLink>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
            Bank Accounts
          </h1>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            Manage your withdrawal bank accounts
          </p>
        </div>
      </div>
      <UButton
        color="primary"
        @click="openAddModal"
      >
        <UIcon
          name="i-lucide-plus"
          class="w-4 h-4 mr-1"
        />
        Add Account
      </UButton>
    </div>

    <!-- Info Banner -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
      <div class="flex gap-3">
        <UIcon
          name="i-lucide-info"
          class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0"
        />
        <div class="text-sm text-blue-700 dark:text-blue-300">
          <p class="font-medium mb-1">
            Verification Required
          </p>
          <p>
            New bank accounts may require verification. You can only withdraw to verified accounts.
          </p>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="space-y-4"
    >
      <div
        v-for="i in 2"
        :key="i"
        class="glass-card p-5 animate-pulse"
      >
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 bg-slate-200 dark:bg-slate-700 rounded-xl" />
          <div class="flex-1">
            <div class="h-5 w-40 bg-slate-200 dark:bg-slate-700 rounded mb-2" />
            <div class="h-4 w-56 bg-slate-200 dark:bg-slate-700 rounded mb-2" />
            <div class="h-3 w-32 bg-slate-200 dark:bg-slate-700 rounded" />
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="beneficiaries.length === 0"
      class="glass-card p-12 text-center"
    >
      <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <UIcon
          name="i-lucide-building-2"
          class="w-10 h-10 text-slate-400 dark:text-slate-500"
        />
      </div>
      <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
        No Bank Accounts
      </h3>
      <p class="text-slate-500 dark:text-slate-400 mb-6">
        Add a bank account to withdraw money from your wallet
      </p>
      <UButton
        color="primary"
        @click="openAddModal"
      >
        <UIcon
          name="i-lucide-plus"
          class="w-4 h-4 mr-1"
        />
        Add Your First Account
      </UButton>
    </div>

    <!-- Beneficiary List -->
    <div
      v-else
      class="space-y-4"
    >
      <div
        v-for="beneficiary in beneficiaries"
        :key="beneficiary.uuid"
        class="glass-card p-5"
        :class="{ 'ring-2 ring-primary-500': beneficiary.is_default }"
      >
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" :class="beneficiary.is_upi ? 'bg-purple-100 dark:bg-purple-900/30' : 'bg-slate-100 dark:bg-slate-800'">
            <UIcon
              :name="beneficiary.is_upi ? 'i-lucide-qr-code' : 'i-lucide-building-2'"
              class="w-6 h-6"
              :class="beneficiary.is_upi ? 'text-purple-600 dark:text-purple-400' : 'text-slate-600 dark:text-slate-400'"
            />
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-4">
              <div>
                <div class="flex items-center gap-2">
                  <h3 class="font-semibold text-slate-900 dark:text-white">
                    {{ beneficiary.holder_name }}
                  </h3>
                  <UBadge
                    v-if="beneficiary.is_default"
                    color="primary"
                    variant="subtle"
                    size="xs"
                  >
                    Default
                  </UBadge>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                  {{ beneficiary.is_upi ? 'UPI' : beneficiary.bank_name }}
                </p>
                <p class="text-sm text-slate-500 dark:text-slate-500 font-mono">
                  {{ beneficiary.is_upi ? beneficiary.upi_id : beneficiary.account_number_masked }}
                </p>
              </div>
              <div class="flex items-center gap-2">
                <UBadge
                  :color="getStatusColor(beneficiary)"
                  variant="subtle"
                >
                  {{ beneficiary.status_label }}
                </UBadge>
                <UDropdown
                  :items="[
                    [
                      { label: 'Set as Default', icon: 'i-lucide-star', click: () => handleSetDefault(beneficiary.uuid), disabled: beneficiary.is_default },
                      { label: 'Verify (Demo)', icon: 'i-lucide-check-circle', click: () => handleVerify(beneficiary.uuid), disabled: beneficiary.is_verified }
                    ],
                    [
                      { label: 'Remove', icon: 'i-lucide-trash-2', click: () => handleDelete(beneficiary.uuid), color: 'error' as const }
                    ]
                  ]"
                >
                  <UButton
                    variant="ghost"
                    color="neutral"
                    size="sm"
                    :loading="deleteLoading === beneficiary.uuid"
                  >
                    <UIcon
                      name="i-lucide-more-vertical"
                      class="w-4 h-4"
                    />
                  </UButton>
                </UDropdown>
              </div>
            </div>
            <div
              v-if="!beneficiary.is_upi"
              class="flex items-center gap-4 mt-2 text-xs text-slate-500 dark:text-slate-400"
            >
              <span>IFSC: {{ beneficiary.ifsc_code }}</span>
              <span v-if="beneficiary.branch_name">{{ beneficiary.branch_name }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Account Modal -->
    <UModal v-model:open="showAddModal">
      <template #content>
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
              Add Bank Account
            </h2>
            <UButton
              variant="ghost"
              color="neutral"
              size="sm"
              @click="showAddModal = false"
            >
              <UIcon
                name="i-lucide-x"
                class="w-5 h-5"
              />
            </UButton>
          </div>

          <div class="space-y-4">
            <!-- Account Type Selection -->
            <UFormField
              label="Account Type"
              :error="formErrors.type"
            >
              <USelect
                v-model="formData.type"
                :items="accountTypes.map(t => ({ label: t.label, value: t.value }))"
                placeholder="Select account type"
              />
            </UFormField>

            <UFormField
              label="Account Holder Name"
              :error="formErrors.holder_name"
            >
              <UInput
                v-model="formData.holder_name"
                placeholder="Name as per bank records"
              />
            </UFormField>

            <!-- Bank Account Fields -->
            <template v-if="isBank">
              <UFormField
                label="Account Number"
                :error="formErrors.account_number"
              >
                <UInput
                  v-model="formData.account_number"
                  placeholder="Enter account number"
                  inputmode="numeric"
                />
              </UFormField>

              <UFormField
                label="Confirm Account Number"
                :error="formErrors.confirm_account_number"
              >
                <UInput
                  v-model="formData.confirm_account_number"
                  placeholder="Re-enter account number"
                  inputmode="numeric"
                />
              </UFormField>

              <UFormField
                label="IFSC Code"
                :error="formErrors.ifsc_code"
              >
                <UInput
                  v-model="formData.ifsc_code"
                  placeholder="11-character IFSC code"
                  maxlength="11"
                  class="uppercase"
                  :loading="verifyingIfsc"
                  @blur="validateIfsc"
                />
              </UFormField>

              <div
                v-if="formData.bank_name"
                class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-3"
              >
                <div class="flex items-center gap-2 text-green-700 dark:text-green-300">
                  <UIcon
                    name="i-lucide-check-circle"
                    class="w-4 h-4"
                  />
                  <span class="text-sm font-medium">
                    {{ formData.bank_name }}
                  </span>
                </div>
                <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                  {{ formData.branch_name }}
                </p>
              </div>
            </template>

            <!-- UPI Fields -->
            <template v-else>
              <UFormField
                label="UPI ID"
                :error="formErrors.upi_id"
              >
                <UInput
                  v-model="formData.upi_id"
                  placeholder="yourname@upi"
                />
              </UFormField>
            </template>

            <div class="flex gap-3 pt-4">
              <UButton
                variant="outline"
                color="neutral"
                class="flex-1"
                @click="showAddModal = false"
              >
                Cancel
              </UButton>
              <UButton
                color="primary"
                class="flex-1"
                :loading="addLoading"
                @click="handleAddBeneficiary"
              >
                Add Account
              </UButton>
            </div>
          </div>
        </div>
      </template>
    </UModal>
  </div>
</template>
