<script setup lang="ts">
import { nextTick } from 'vue'
import { useGeoData } from '~/composables/useGeoData'

/**
 * Addresses Management Page
 * CRUD for user addresses with default toggler
 */

definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

interface Address {
  uuid: string
  title: string
  person_name: string
  person_mobile: string
  address_1: string
  address_2?: string
  landmark?: string
  city: string
  postal_code: string
  block_id?: number | null
  state_code: string
  country_code: string
  type: 'home' | 'work' | 'other'
  default: boolean
}

const config = useRuntimeConfig()

const {
  countries,
  states,
  blocks,
  loadingCountries,
  loadingStates,
  loadingBlocks,
  fetchCountries,
  fetchStates,
  fetchBlocks,
  resetStates,
  resetBlocks
} = useGeoData()

const addresses = ref<Address[]>([])
const loading = ref(true)
const showAddModal = ref(false)
const showEditModal = ref(false)
const editingAddress = ref<Address | null>(null)
const deletingId = ref<string | null>(null)

// Form state
const formData = ref({
  title: '',
  person_name: '',
  person_mobile: '',
  address_1: '',
  address_2: '',
  landmark: '',
  city: '',
  postal_code: '',
  state_code: '',
  country_code: 'IN',
  block_id: null as number | null,
  type: 'home' as const
})

const formErrors = ref<Record<string, string[] | string>>({})
const submitting = ref(false)
const editingFormLoading = ref(false)
const modalOpen = computed({
  get: () => showAddModal.value || showEditModal.value,
  set: (value: boolean) => {
    if (!value) {
      showAddModal.value = false
      showEditModal.value = false
    }
  }
})

const fieldError = (key: string) => {
  const error = formErrors.value[key]
  if (!error) return undefined
  return Array.isArray(error) ? error[0] : String(error)
}

// Fetch addresses
const fetchAddresses = async () => {
  loading.value = true
  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/api/addresses`)
    addresses.value = response.data || []
  } catch (error) {
    console.error('Failed to fetch addresses:', error)
  } finally {
    loading.value = false
  }
}

const loadInitialGeo = async () => {
  await fetchCountries()
  if (formData.value.country_code) {
    await fetchStates(formData.value.country_code)
  }
}

const stateItems = computed(() => states.value)
const blockItems = computed(() => blocks.value)

const handleStateChange = async (value: string | null) => {
  formData.value.state_code = value || ''
  formData.value.block_id = null
  resetBlocks()
  if (value) {
    await fetchBlocks(value)
  }
}

const handleBlockChange = (value: number | string | null) => {
  formData.value.block_id = value ? Number(value) : null
}

const hydrateStateAndBlock = async (countryCode: string, stateCode?: string | null, blockId?: number | null) => {
  await fetchStates(countryCode)
  await nextTick()

  if (stateCode) {
    formData.value.state_code = stateCode
    await fetchBlocks(stateCode)
    await nextTick()
    formData.value.block_id = blockId ?? null
  } else {
    formData.value.state_code = ''
    formData.value.block_id = null
  }
}

// Create address
const sanitizeMobile = (value: string, countryCode = 'IN') => {
  if (!value) return ''
  if (value.startsWith('+')) return value
  const digits = value.replace(/\D/g, '')
  if (countryCode === 'IN' && digits.length === 10) {
    return `+91${digits}`
  }
  return digits ? `+${digits}` : ''
}

const createAddress = async () => {
  submitting.value = true
  formErrors.value = {}

  try {
    await useSanctumFetch(`${config.public.apiBase}/api/addresses`, {
      method: 'POST',
      body: {
        ...formData.value,
        block_id: formData.value.block_id ?? null,
        person_mobile: sanitizeMobile(formData.value.person_mobile, formData.value.country_code || 'IN')
      }
    })

    showAddModal.value = false
    resetForm()
    await fetchAddresses()
  } catch (error: any) {
    if (error.data?.errors) {
      formErrors.value = error.data.errors
    }
  } finally {
    submitting.value = false
  }
}

// Update address
const updateAddress = async () => {
  if (!editingAddress.value) return

  submitting.value = true
  formErrors.value = {}

  try {
    await useSanctumFetch(`${config.public.apiBase}/api/addresses/${editingAddress.value.uuid}`, {
      method: 'PUT',
      body: {
        ...formData.value,
        block_id: formData.value.block_id ?? null,
        person_mobile: sanitizeMobile(formData.value.person_mobile, formData.value.country_code || 'IN')
      }
    })

    showEditModal.value = false
    editingAddress.value = null
    resetForm()
    await fetchAddresses()
  } catch (error: any) {
    if (error.data?.errors) {
      formErrors.value = error.data.errors
    }
  } finally {
    submitting.value = false
  }
}

// Delete address
const deleteAddress = async (uuid: string) => {
  deletingId.value = uuid
  try {
    await useSanctumFetch(`${config.public.apiBase}/api/addresses/${uuid}`, {
      method: 'DELETE'
    })
    await fetchAddresses()
  } catch (error) {
    console.error('Failed to delete address:', error)
  } finally {
    deletingId.value = null
  }
}

// Set default address
const setDefaultAddress = async (uuid: string) => {
  try {
    await useSanctumFetch(`${config.public.apiBase}/api/addresses/${uuid}/default`, {
      method: 'POST'
    })
    await fetchAddresses()
  } catch (error) {
    console.error('Failed to set default address:', error)
  }
}

// Open edit modal
const openEditModal = async (address: Address) => {
  editingFormLoading.value = true
  editingAddress.value = address
  formData.value = {
    title: address.title,
    person_name: address.person_name,
    person_mobile: address.person_mobile,
    address_1: address.address_1,
    address_2: address.address_2 || '',
    landmark: address.landmark || '',
    city: address.city,
    postal_code: address.postal_code,
    state_code: address.state_code,
    country_code: address.country_code || 'IN',
    block_id: address.block_id ?? null,
    type: address.type
  }
  const countryCode = address.country_code || 'IN'
  await hydrateStateAndBlock(countryCode, address.state_code, address.block_id ?? null)
  editingFormLoading.value = false
  showEditModal.value = true
}

// Reset form
const resetForm = () => {
  formData.value = {
    title: '',
    person_name: '',
    person_mobile: '',
    address_1: '',
    address_2: '',
    landmark: '',
    city: '',
    postal_code: '',
    state_code: '',
    country_code: 'IN',
    type: 'home'
  }
  formErrors.value = {}
}

const openAddModal = async () => {
  editingFormLoading.value = true
  resetForm()
  await hydrateStateAndBlock('IN')
  editingFormLoading.value = false
  showAddModal.value = true
}

const addressTypeOptions = [
  { label: 'Home', value: 'home' },
  { label: 'Work', value: 'work' },
  { label: 'Other', value: 'other' }
]

const getTypeIcon = (type: string) => {
  switch (type) {
    case 'home': return 'i-lucide-home'
    case 'work': return 'i-lucide-building-2'
    default: return 'i-lucide-map-pin'
  }
}

const getTypeColor = (type: string) => {
  switch (type) {
    case 'home': return 'primary'
    case 'work': return 'success'
    default: return 'neutral'
  }
}

onMounted(async () => {
  await loadInitialGeo()
  await fetchAddresses()
})
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
          My Addresses
        </h1>
        <p class="text-base text-slate-500 dark:text-slate-400 mt-1">
          Manage your delivery and billing addresses for a faster checkout.
        </p>
      </div>
      <UButton
        icon="i-lucide-plus"
        color="primary"
        size="lg"
        class="rounded-xl shadow-lg shadow-primary-500/20"
        @click="openAddModal"
      >
        Add New Address
      </UButton>
    </div>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="grid grid-cols-1 md:grid-cols-2 gap-6"
    >
      <div
        v-for="i in 2"
        :key="i"
        class="glass-card p-6 animate-pulse"
      >
        <div class="flex items-start gap-4">
          <div class="w-14 h-14 bg-slate-200 dark:bg-slate-700 rounded-2xl" />
          <div class="flex-1 space-y-4">
            <div class="h-5 w-32 bg-slate-200 dark:bg-slate-700 rounded" />
            <div class="h-3 w-full bg-slate-200 dark:bg-slate-700 rounded" />
            <div class="h-3 w-3/4 bg-slate-200 dark:bg-slate-700 rounded" />
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="addresses.length === 0"
      class="glass-card p-16 text-center"
    >
      <CommonEmptyState
        icon="i-lucide-map-pin"
        title="No saved addresses"
        description="Looks like you haven't added any addresses yet. Add one now to start shopping."
        action-label="Add Your First Address"
        @action="openAddModal"
      />
    </div>

    <!-- Addresses Grid -->
    <div
      v-else
      class="grid grid-cols-1 md:grid-cols-2 gap-6"
    >
      <div
        v-for="address in addresses"
        :key="address.uuid"
        :class="[
          'glass-card p-6 relative transition-all duration-300 hover:shadow-xl group',
          address.default ? 'ring-2 ring-primary-500 dark:ring-primary-400 bg-primary-50/10' : 'hover:border-primary-500/50'
        ]"
      >
        <!-- Default Badge -->
        <div
          v-if="address.default"
          class="absolute top-4 right-4"
        >
          <UBadge
            color="primary"
            variant="solid"
            size="sm"
            class="rounded-lg px-2.5"
          >
            Default
          </UBadge>
        </div>

        <div class="flex items-start gap-5">
          <!-- Type Icon -->
          <div
            :class="[
              'w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-sm transition-transform duration-300 group-hover:scale-110',
              address.type === 'home' ? 'bg-primary-100 dark:bg-primary-900/30'
              : address.type === 'work' ? 'bg-success-100 dark:bg-success-900/30'
                : 'bg-slate-100 dark:bg-slate-800'
            ]"
          >
            <UIcon
              :name="getTypeIcon(address.type)"
              :class="[
                'w-7 h-7',
                address.type === 'home' ? 'text-primary-600 dark:text-primary-400'
                : address.type === 'work' ? 'text-success-600 dark:text-success-400'
                  : 'text-slate-600 dark:text-slate-400'
              ]"
            />
          </div>

          <!-- Address Details -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-2">
              <h3 class="text-lg font-bold text-slate-900 dark:text-white truncate">
                {{ address.title || address.type }}
              </h3>
              <UBadge
                :color="getTypeColor(address.type) as any"
                variant="subtle"
                size="xs"
                class="capitalize rounded-md"
              >
                {{ address.type }}
              </UBadge>
            </div>

            <div class="space-y-1">
              <p class="text-sm text-slate-800 dark:text-slate-200 font-semibold flex items-center gap-2">
                <UIcon
                  name="i-lucide-user"
                  class="w-3.5 h-3.5 text-slate-400"
                />
                {{ address.person_name }}
              </p>
              <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                {{ address.address_1 }}
                <span v-if="address.address_2">, {{ address.address_2 }}</span>
              </p>
              <p
                v-if="address.landmark"
                class="text-sm text-slate-500 dark:text-slate-500 italic"
              >
                Landmark: {{ address.landmark }}
              </p>
              <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">
                {{ address.city }}, {{ address.state_code }} - {{ address.postal_code }}
              </p>
              <p class="text-sm text-primary-600 dark:text-primary-400 font-bold mt-2 flex items-center gap-2">
                <UIcon
                  name="i-lucide-phone"
                  class="w-3.5 h-3.5"
                />
                {{ address.person_mobile }}
              </p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 mt-6 pt-5 border-t border-slate-100 dark:border-slate-800">
          <UButton
            v-if="!address.default"
            variant="ghost"
            color="primary"
            size="sm"
            class="font-semibold"
            @click="setDefaultAddress(address.uuid)"
          >
            Mark as Default
          </UButton>
          <div class="flex-1" />
          <UTooltip text="Edit Address">
            <UButton
              icon="i-lucide-pencil"
              variant="subtle"
              color="neutral"
              size="sm"
              class="rounded-lg"
              @click="openEditModal(address)"
            />
          </UTooltip>
          <UTooltip text="Delete Address">
            <UButton
              icon="i-lucide-trash-2"
              variant="subtle"
              color="error"
              size="sm"
              class="rounded-lg"
              :loading="deletingId === address.uuid"
              @click="deleteAddress(address.uuid)"
            />
          </UTooltip>
        </div>
      </div>
    </div>

    <!-- Address Form Modal (Shared for Add/Edit) -->
    <UModal
      v-model:open="modalOpen"
      :ui="{ width: 'sm:max-w-2xl' }"
    >
      <template #content>
        <UCard :ui="{ body: { padding: 'p-8' }, header: { padding: 'px-8 py-6' }, footer: { padding: 'px-8 py-6' } }">
          <template #header>
            <div class="flex items-center gap-4">
              <div
                :class="[
                  'w-12 h-12 rounded-2xl flex items-center justify-center shadow-inner',
                  showEditModal ? 'bg-amber-100 dark:bg-amber-900/30' : 'bg-primary-100 dark:bg-primary-900/30'
                ]"
              >
                <UIcon
                  :name="showEditModal ? 'i-lucide-pencil' : 'i-lucide-map-pin-plus'"
                  :class="['w-6 h-6', showEditModal ? 'text-amber-600 dark:text-amber-400' : 'text-primary-600 dark:text-primary-400']"
                />
              </div>
              <div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                  {{ showEditModal ? 'Edit Address' : 'Add New Address' }}
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  {{ showEditModal ? 'Update your existing address information' : 'Enter the details for your new delivery location' }}
                </p>
              </div>
            </div>
          </template>

          <div class="space-y-6 relative">
            <div
              v-if="editingFormLoading"
              class="absolute inset-0 bg-white/90 dark:bg-slate-900/80 flex items-center justify-center z-10 rounded-lg"
            >
              <UIcon name="i-lucide-loader-2" class="w-16 h-16 animate-spin text-primary-500" />
            </div>
            <!-- Row 1: Title & Type -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <UFormField
                label="Address Label"
                description="e.g. Home, Office, Parents"
                :error="formErrors.title"
                required
              >
                <UInput
                  v-model="formData.title"
                  placeholder="Give this address a name"
                  size="lg"
                  class="w-full"
                />
              </UFormField>
              <UFormField
                label="Address Type"
                :error="formErrors.type"
                required
              >
                <USelect
                  v-model="formData.type"
                  :items="addressTypeOptions"
                  size="lg"
                  class="w-full"
                />
              </UFormField>
            </div>

            <!-- Row 2: Name & Mobile -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <UFormField
                label="Recipient's Full Name"
                :error="formErrors.person_name"
                required
              >
                <UInput
                  v-model="formData.person_name"
                  placeholder="Who's receiving the package?"
                  size="lg"
                  icon="i-lucide-user"
                  class="w-full"
                />
              </UFormField>
              <UFormField
                label="Contact Number"
                :error="formErrors.person_mobile"
                required
              >
                <UInput
                  v-model="formData.person_mobile"
                  placeholder="10-digit mobile number"
                  size="lg"
                  icon="i-lucide-phone"
                  class="w-full"
                />
              </UFormField>
            </div>

            <!-- Address Lines -->
            <UFormField
              label="Flat / House / Building"
              :error="formErrors.address_1"
              required
            >
              <UInput
                v-model="formData.address_1"
                placeholder="Complete house address"
                size="lg"
                class="w-full"
              />
            </UFormField>

            <UFormField
              label="Street / Area / Colony (Optional)"
              :error="formErrors.address_2"
            >
              <UInput
                v-model="formData.address_2"
                placeholder="Area details"
                size="lg"
                class="w-full"
              />
            </UFormField>

            <UFormField
              label="Landmark (Optional)"
              :error="formErrors.landmark"
            >
              <UInput
                v-model="formData.landmark"
                placeholder="Any famous nearby place"
                size="lg"
                icon="i-lucide-flag"
                class="w-full"
              />
            </UFormField>

            <!-- Row 3: City, PIN, State / Block -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <UFormField
                label="City"
                :error="fieldError('city')"
                required
              >
                <UInput
                  v-model="formData.city"
                  placeholder="City"
                  size="lg"
                  class="w-full"
                />
              </UFormField>
              <UFormField
                label="PIN Code"
                :error="fieldError('postal_code')"
                required
              >
                <UInput
                  v-model="formData.postal_code"
                  placeholder="6-digit PIN"
                  size="lg"
                  class="w-full"
                />
              </UFormField>
              <UFormField
                label="State"
                :error="fieldError('state_code')"
                required
              >
                <USelectMenu
                  v-model="formData.state_code"
                  :items="stateItems"
                  placeholder="Select state"
                  size="lg"
                  class="w-full"
                  :loading="loadingStates"
                  :disabled="loadingStates || editingFormLoading"
                  value-key="value"
                  label-key="label"
                  searchable
                  @update:model-value="handleStateChange"
                />
              </UFormField>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <UFormField
                label="Block / Area"
                :error="fieldError('block_id')"
              >
                <USelectMenu
                  v-model="formData.block_id"
                  :items="blockItems"
                  placeholder="Select block"
                  size="lg"
                  class="w-full"
                  :loading="loadingBlocks"
                  :disabled="loadingBlocks || !formData.state_code || editingFormLoading"
                  value-key="value"
                  label-key="label"
                  searchable
                  @update:model-value="handleBlockChange"
                />
              </UFormField>
            </div>
          </div>

          <template #footer>
            <div class="flex flex-col sm:flex-row justify-end gap-4">
              <UButton
                variant="ghost"
                color="neutral"
                size="lg"
                class="sm:px-8"
                @click="() => { showAddModal = false; showEditModal = false; }"
              >
                Cancel
              </UButton>
              <UButton
                color="primary"
                size="lg"
                class="sm:px-12 rounded-xl shadow-lg shadow-primary-500/20"
                :loading="submitting"
                @click="showEditModal ? updateAddress() : createAddress()"
              >
                {{ showEditModal ? 'Update Address' : 'Save Address' }}
              </UButton>
            </div>
          </template>
        </UCard>
      </template>
    </UModal>
  </div>
</template>
