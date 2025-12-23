<script setup lang="ts">
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
  state_code: string
  country_code: string
  type: 'home' | 'work' | 'other'
  default: boolean
}

const config = useRuntimeConfig()
const { formatCurrency } = useBranding()

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
  type: 'home' as const
})

const formErrors = ref<Record<string, string>>({})
const submitting = ref(false)

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

// Create address
const createAddress = async () => {
  submitting.value = true
  formErrors.value = {}

  try {
    await useSanctumFetch(`${config.public.apiBase}/api/addresses`, {
      method: 'POST',
      body: formData.value
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
      body: formData.value
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
const openEditModal = (address: Address) => {
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
    country_code: address.country_code,
    type: address.type
  }
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

// Open add modal
const openAddModal = () => {
  resetForm()
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

onMounted(() => {
  fetchAddresses()
})
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
          My Addresses
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
          Manage your delivery addresses
        </p>
      </div>
      <UButton
        icon="i-lucide-plus"
        color="primary"
        @click="openAddModal"
      >
        Add Address
      </UButton>
    </div>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="grid grid-cols-1 md:grid-cols-2 gap-4"
    >
      <div
        v-for="i in 2"
        :key="i"
        class="glass-card p-6 animate-pulse"
      >
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 bg-slate-200 dark:bg-slate-700 rounded-xl" />
          <div class="flex-1 space-y-3">
            <div class="h-4 w-24 bg-slate-200 dark:bg-slate-700 rounded" />
            <div class="h-3 w-full bg-slate-200 dark:bg-slate-700 rounded" />
            <div class="h-3 w-3/4 bg-slate-200 dark:bg-slate-700 rounded" />
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="addresses.length === 0"
      class="glass-card p-12"
    >
      <CommonEmptyState
        icon="i-lucide-map-pin"
        title="No addresses yet"
        description="Add your first delivery address to make checkout faster"
        action-label="Add Address"
        @action="openAddModal"
      />
    </div>

    <!-- Addresses Grid -->
    <div
      v-else
      class="grid grid-cols-1 md:grid-cols-2 gap-4"
    >
      <div
        v-for="address in addresses"
        :key="address.uuid"
        :class="[
          'glass-card p-5 relative transition-all',
          address.default ? 'ring-2 ring-blue-500 dark:ring-blue-400' : ''
        ]"
      >
        <!-- Default Badge -->
        <div
          v-if="address.default"
          class="absolute top-3 right-3"
        >
          <UBadge
            color="primary"
            variant="solid"
            size="xs"
          >
            Default
          </UBadge>
        </div>

        <div class="flex items-start gap-4">
          <!-- Type Icon -->
          <div
            :class="[
              'w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0',
              address.type === 'home' ? 'bg-blue-100 dark:bg-blue-900/30' :
              address.type === 'work' ? 'bg-green-100 dark:bg-green-900/30' :
              'bg-slate-100 dark:bg-slate-800'
            ]"
          >
            <UIcon
              :name="getTypeIcon(address.type)"
              :class="[
                'w-6 h-6',
                address.type === 'home' ? 'text-blue-600 dark:text-blue-400' :
                address.type === 'work' ? 'text-green-600 dark:text-green-400' :
                'text-slate-600 dark:text-slate-400'
              ]"
            />
          </div>

          <!-- Address Details -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <h3 class="font-semibold text-slate-900 dark:text-white truncate">
                {{ address.title || address.type }}
              </h3>
              <UBadge
                :color="getTypeColor(address.type) as any"
                variant="soft"
                size="xs"
              >
                {{ address.type }}
              </UBadge>
            </div>

            <p class="text-sm text-slate-700 dark:text-slate-300 font-medium">
              {{ address.person_name }}
            </p>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              {{ address.address_1 }}
            </p>
            <p
              v-if="address.address_2"
              class="text-sm text-slate-600 dark:text-slate-400"
            >
              {{ address.address_2 }}
            </p>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              {{ address.city }}, {{ address.state_code }} - {{ address.postal_code }}
            </p>
            <p class="text-sm text-slate-500 dark:text-slate-500 mt-1">
              <UIcon
                name="i-lucide-phone"
                class="w-3 h-3 inline mr-1"
              />
              {{ address.person_mobile }}
            </p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2 mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
          <UButton
            v-if="!address.default"
            variant="soft"
            color="primary"
            size="xs"
            @click="setDefaultAddress(address.uuid)"
          >
            Set as Default
          </UButton>
          <div class="flex-1" />
          <UButton
            icon="i-lucide-pencil"
            variant="ghost"
            color="neutral"
            size="xs"
            @click="openEditModal(address)"
          />
          <UButton
            icon="i-lucide-trash-2"
            variant="ghost"
            color="error"
            size="xs"
            :loading="deletingId === address.uuid"
            @click="deleteAddress(address.uuid)"
          />
        </div>
      </div>
    </div>

    <!-- Add Address Modal -->
    <UModal v-model:open="showAddModal">
      <template #content>
        <UCard>
          <template #header>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                <UIcon
                  name="i-lucide-map-pin-plus"
                  class="w-5 h-5 text-blue-600 dark:text-blue-400"
                />
              </div>
              <div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                  Add New Address
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Enter your delivery address details
                </p>
              </div>
            </div>
          </template>

          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <UFormField
                label="Address Title"
                :error="formErrors.title"
              >
                <UInput
                  v-model="formData.title"
                  placeholder="e.g. Home, Office"
                />
              </UFormField>
              <UFormField
                label="Type"
                :error="formErrors.type"
              >
                <USelect
                  v-model="formData.type"
                  :items="addressTypeOptions"
                />
              </UFormField>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <UFormField
                label="Full Name"
                :error="formErrors.person_name"
              >
                <UInput
                  v-model="formData.person_name"
                  placeholder="Recipient name"
                />
              </UFormField>
              <UFormField
                label="Mobile Number"
                :error="formErrors.person_mobile"
              >
                <UInput
                  v-model="formData.person_mobile"
                  placeholder="10-digit mobile"
                />
              </UFormField>
            </div>

            <UFormField
              label="Address Line 1"
              :error="formErrors.address_1"
            >
              <UInput
                v-model="formData.address_1"
                placeholder="House/Flat No., Building Name"
              />
            </UFormField>

            <UFormField
              label="Address Line 2 (Optional)"
              :error="formErrors.address_2"
            >
              <UInput
                v-model="formData.address_2"
                placeholder="Street, Area"
              />
            </UFormField>

            <UFormField
              label="Landmark (Optional)"
              :error="formErrors.landmark"
            >
              <UInput
                v-model="formData.landmark"
                placeholder="Near..."
              />
            </UFormField>

            <div class="grid grid-cols-3 gap-4">
              <UFormField
                label="City"
                :error="formErrors.city"
              >
                <UInput
                  v-model="formData.city"
                  placeholder="City"
                />
              </UFormField>
              <UFormField
                label="PIN Code"
                :error="formErrors.postal_code"
              >
                <UInput
                  v-model="formData.postal_code"
                  placeholder="6-digit PIN"
                />
              </UFormField>
              <UFormField
                label="State Code"
                :error="formErrors.state_code"
              >
                <UInput
                  v-model="formData.state_code"
                  placeholder="e.g. WB"
                />
              </UFormField>
            </div>
          </div>

          <template #footer>
            <div class="flex justify-end gap-3">
              <UButton
                variant="ghost"
                color="neutral"
                @click="showAddModal = false"
              >
                Cancel
              </UButton>
              <UButton
                color="primary"
                :loading="submitting"
                @click="createAddress"
              >
                Save Address
              </UButton>
            </div>
          </template>
        </UCard>
      </template>
    </UModal>

    <!-- Edit Address Modal -->
    <UModal v-model:open="showEditModal">
      <template #content>
        <UCard>
          <template #header>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                <UIcon
                  name="i-lucide-pencil"
                  class="w-5 h-5 text-amber-600 dark:text-amber-400"
                />
              </div>
              <div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                  Edit Address
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Update your address details
                </p>
              </div>
            </div>
          </template>

          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <UFormField
                label="Address Title"
                :error="formErrors.title"
              >
                <UInput
                  v-model="formData.title"
                  placeholder="e.g. Home, Office"
                />
              </UFormField>
              <UFormField
                label="Type"
                :error="formErrors.type"
              >
                <USelect
                  v-model="formData.type"
                  :items="addressTypeOptions"
                />
              </UFormField>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <UFormField
                label="Full Name"
                :error="formErrors.person_name"
              >
                <UInput
                  v-model="formData.person_name"
                  placeholder="Recipient name"
                />
              </UFormField>
              <UFormField
                label="Mobile Number"
                :error="formErrors.person_mobile"
              >
                <UInput
                  v-model="formData.person_mobile"
                  placeholder="10-digit mobile"
                />
              </UFormField>
            </div>

            <UFormField
              label="Address Line 1"
              :error="formErrors.address_1"
            >
              <UInput
                v-model="formData.address_1"
                placeholder="House/Flat No., Building Name"
              />
            </UFormField>

            <UFormField
              label="Address Line 2 (Optional)"
              :error="formErrors.address_2"
            >
              <UInput
                v-model="formData.address_2"
                placeholder="Street, Area"
              />
            </UFormField>

            <UFormField
              label="Landmark (Optional)"
              :error="formErrors.landmark"
            >
              <UInput
                v-model="formData.landmark"
                placeholder="Near..."
              />
            </UFormField>

            <div class="grid grid-cols-3 gap-4">
              <UFormField
                label="City"
                :error="formErrors.city"
              >
                <UInput
                  v-model="formData.city"
                  placeholder="City"
                />
              </UFormField>
              <UFormField
                label="PIN Code"
                :error="formErrors.postal_code"
              >
                <UInput
                  v-model="formData.postal_code"
                  placeholder="6-digit PIN"
                />
              </UFormField>
              <UFormField
                label="State Code"
                :error="formErrors.state_code"
              >
                <UInput
                  v-model="formData.state_code"
                  placeholder="e.g. WB"
                />
              </UFormField>
            </div>
          </div>

          <template #footer>
            <div class="flex justify-end gap-3">
              <UButton
                variant="ghost"
                color="neutral"
                @click="showEditModal = false"
              >
                Cancel
              </UButton>
              <UButton
                color="primary"
                :loading="submitting"
                @click="updateAddress"
              >
                Update Address
              </UButton>
            </div>
          </template>
        </UCard>
      </template>
    </UModal>
  </div>
</template>
