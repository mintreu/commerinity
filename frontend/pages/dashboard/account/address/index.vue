<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useSanctumFetch, useRuntimeConfig,useRoute  } from '#imports'

definePageMeta({ layout: 'dashboard' })

const config = useRuntimeConfig()
const addresses = ref<any[]>([])
const isLoading = ref(false)

const showModal = ref(false)
const modalMode = ref<'create' | 'edit' | 'view'>('create')
const searchPincode = ref('')

const stateList = ref<any[]>([])
/**
 * blockList will hold objects: { name: string, url: string }
 * The select bind will use the `url` (primitive string) as the value.
 */
const blockList = ref<Array<{ name: string; url: string }>>([])
const districtList = ref<string[]>([])

// Default form state
const currentAddress = reactive<any>({
  uuid: '',
  title: '',
  type: 'home',
  person_name: '',
  person_email: '',
  person_mobile: '',
  address_1: '',
  landmark: '',
  city: '',
  state_code: '',
  postal_code: '',
  block_id: '', // will contain the block URL or fallback string
  village: '',
  country: 'IN', // Always default to India
})

// Validation errors
const errors = reactive<Record<string, string>>({})
const route = useRoute()

// List of required fields (as requested)
const requiredFields = [
  'title',
  'type',
  'address_1',
  'landmark',
  'person_name',
  'person_mobile',
  'state_code',
  'city',
  'postal_code',
  'block_id',
  'country',
]

// Helper: check validity without mutating `errors`
function isFieldValidSilent(field: string): boolean {
  const val = (currentAddress[field] ?? '').toString().trim()

  switch (field) {
    case 'title':
      if (!raw) errors.title = 'Title is required.'
      break
    case 'type':
    case 'address_1':
    case 'landmark':
    case 'person_name':
    case 'state_code':
    case 'city':
    case 'block_id':
    case 'country':
      return val.length > 0

    case 'person_mobile': {
      const digits = (currentAddress.person_mobile || '').toString().replace(/\D/g, '')
      return digits.length >= 10 // accept 10+ digits
    }

    case 'postal_code':
      return /^\d{6}$/.test(currentAddress.postal_code || '')

    default:
      return true
  }
}

// Validate single field (mutates `errors` with helpful messages)
function validateField(field: string) {
  const raw = (currentAddress[field] ?? '').toString().trim()

  // Clear previous error first
  delete errors[field]

  switch (field) {
    case 'type':
      if (!raw) errors.type = 'Please select an address type.'
      break

    case 'address_1':
      if (!raw) errors.address_1 = 'Street / house name is required.'
      else if (raw.length < 3) errors.address_1 = 'Please enter a valid street address.'
      break

    case 'landmark':
      if (!raw) errors.landmark = 'Landmark is required.'
      break

    case 'person_name':
      if (!raw) errors.person_name = 'Contact name is required.'
      break

    case 'person_mobile': {
      const digits = (currentAddress.person_mobile || '').toString().replace(/\D/g, '')
      if (!digits) errors.person_mobile = 'Mobile number is required.'
      else if (digits.length < 10) errors.person_mobile = 'Enter a valid mobile number (min 10 digits).'
      break
    }

    case 'state_code':
      if (!raw) errors.state_code = 'State is required.'
      break

    case 'city':
      if (!raw) errors.city = 'City / district is required.'
      break

    case 'postal_code':
      if (!raw) errors.postal_code = 'Pincode is required.'
      else if (!/^\d{6}$/.test(currentAddress.postal_code || '')) errors.postal_code = 'Pincode must be 6 digits.'
      break

    case 'block_id':
      if (!raw) errors.block_id = 'Block is required.'
      break

    case 'country':
      if (!raw) errors.country = 'Country is required.'
      break

    default:
      break
  }
}

// Validate all required fields and return boolean (true = valid)
function validateAll(): boolean {
  // clear all previous errors
  Object.keys(errors).forEach(k => delete errors[k])

  requiredFields.forEach(f => validateField(f))

  return Object.keys(errors).length === 0
}

// Small helpers
function clearErrors() {
  Object.keys(errors).forEach(k => delete errors[k])
}

// Badge colors mapping (kept as-is)
const typeBadges: Record<string, { label: string; color: string }> = {
  home: { label: 'HOME', color: 'bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200' },
  work: { label: 'WORK', color: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-800 dark:text-emerald-200' },
  delivery: { label: 'DELIVERY', color: 'bg-amber-100 text-amber-700 dark:bg-amber-800 dark:text-amber-200' },
  other: { label: 'OTHER', color: 'bg-sky-100 text-sky-700 dark:bg-sky-800 dark:text-sky-200' },
}

// Load states
async function fetchStates() {
  const res = await useSanctumFetch(`${config.public.apiBase}/geo/states/IN`)
  // keep stateList as returned (array of objects)
  // ensure each state's value is `code` for binding (we use state.code in template)
  stateList.value = res.data
}

// Load blocks + districts based on state
async function fetchBlocks() {
  if (!currentAddress.state_code) {
    blockList.value = []
    districtList.value = []
    return
  }
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/geo/state/${currentAddress.state_code}`)
    const blocks = res.data.blocks || []

    // Normalize block objects to { name, url }.
    // Use common fallbacks if the API doesn't provide `url` exactly.
    blockList.value = blocks.map((b: any) => {
      const name = b.name || b.block_name || b.title || ''
      // common fallback chain for unique identifier/url:
      const url = b.url || b.href || (b._links && b._links.self && b._links.self.href) || b.slug || b.id || name
      return { name, url: String(url) }
    })

    // district list (unique)
    districtList.value = [...new Set(blocks.map((b: any) => b.district).filter(Boolean))]
  } catch {
    blockList.value = []
    districtList.value = []
  }
}

// Autofill from postal code
async function fetchAddressFromPostalCode() {
  const code = currentAddress.postal_code
  if (!code || code.length !== 6) return
  try {
    const res = await $fetch(`https://api.postalpincode.in/pincode/${code}`)
    const data = res[0]
    if (data.Status !== 'Success') return alert('Invalid postal code')

    const po = data.PostOffice[0]
    currentAddress.city = po.District
    currentAddress.state_code = getStateCodeByName(po.State)
    currentAddress.village = po.Name
    currentAddress.block_id = ''

    // load blocks for the state (so we can map block name -> url)
    await fetchBlocks()

    // postal response might return Block names — try to map to block url if available
    const poBlockName = (po.Block || '').trim()
    if (poBlockName && blockList.value && blockList.value.length) {
      const found = blockList.value.find(b => b.name.toLowerCase() === poBlockName.toLowerCase())
      if (found) currentAddress.block_id = found.url
      else currentAddress.block_id = poBlockName // fallback to plain name
    } else {
      // If blockList empty, try to set name from postal data (fallback)
      currentAddress.block_id = poBlockName || ''
    }
  } catch {
    alert('Failed to fetch address info')
  }
}

function getStateCodeByName(name: string): string {
  const state = stateList.value.find((s) => s.name.toLowerCase() === name.toLowerCase())
  return state?.code || ''
}

// Load addresses
async function loadAddresses() {
  isLoading.value = true
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/account/addresses`)
    addresses.value = res?.data || []
  } finally {
    isLoading.value = false
  }
}

// Search filter
const filteredAddresses = computed(() => {
  if (!searchPincode.value) return addresses.value
  return addresses.value.filter(addr =>
      addr.postal_code?.toString().includes(searchPincode.value.trim())
  )
})

// Modal open
function openModal(mode: 'create' | 'edit' | 'view', address: any | null = null) {
  modalMode.value = mode
  clearErrors()

  if (address) {
    Object.assign(currentAddress, {
      ...address,
      country: 'IN',
      state_code: address.state?.code || address.state_code || '',
      city: address.city || '',
      block_id: address.block?.url || address.block_id || address.block || '',
    })
  } else {
    Object.assign(currentAddress, {
      // uuid: '',
      title: '',
      type: '',  // keep blank initially
      person_name: '',
      person_email: '',
      person_mobile: '',
      address_1: '',
      landmark: '',
      city: '',
      state_code: '',
      postal_code: '',
      block_id: '',
      village: '',
      country: 'IN',
    })

    // ✅ Auto-fill from query string only in create mode
    const queryType = (route.query.type || '').toString().toLowerCase()
    if (queryType && typeBadges[queryType]) {
      currentAddress.type = queryType
    }
  }

  showModal.value = true
}

// Save
async function saveAddress() {
  // Validate required fields
  if (!validateAll()) {
    // Keep inline errors — do not submit when invalid
    return
  }

  // payload uses primitives only (state_code is code string, block_id is url or string)
  const payload = { ...currentAddress }
  if (modalMode.value === 'edit') {
    await useSanctumFetch(`${config.public.apiBase}/account/addresses/${payload.uuid}`, {
      method: 'PUT',
      body: payload,
    })
  } else {
    await useSanctumFetch(`${config.public.apiBase}/account/addresses`, {
      method: 'POST',
      body: payload,
    })
  }
  showModal.value = false
  await loadAddresses()
}

// Delete
async function deleteAddress(uuid: string) {
  if (confirm('Are you sure you want to delete this address?')) {
    await useSanctumFetch(`${config.public.apiBase}/account/addresses/${uuid}`, { method: 'DELETE' })
    await loadAddresses()
  }
}

onMounted(() => {
  fetchStates()
  loadAddresses()
})
</script>

<template>
  <div class="p-6 mt-8 space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div class="flex items-start gap-4">
        <div class="rounded-lg p-3 bg-gradient-to-r from-white via-slate-50 to-white dark:from-gray-800 dark:via-gray-800 dark:to-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-0">
          <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
            <span class="text-2xl">📍</span>
            <span>My Addresses</span>
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your saved shipping & billing addresses.</p>
        </div>

        <div class="hidden md:flex flex-col justify-center">
          <div class="text-sm text-gray-500 dark:text-gray-400">Total</div>
          <div class="mt-1 inline-flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white text-sm font-semibold shadow-md">
              {{ addresses.length }}
            </span>
            <span class="text-sm text-gray-700 dark:text-gray-300">addresses</span>
          </div>
        </div>
      </div>

      <div class="w-full md:w-auto flex items-center gap-3">
        <div class="relative w-full md:w-72">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
            🔎
          </span>
          <input
              v-model="searchPincode"
              type="text"
              placeholder="Search by pincode"
              class="w-full pl-10 pr-12 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800"
          />
          <button v-if="searchPincode" @click="searchPincode = ''" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            ✖
          </button>
        </div>

        <button
            @click="openModal('create')"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gradient-to-br from-blue-600 to-blue-500 shadow-lg text-white hover:from-blue-700 hover:to-blue-600"
        >
          <Icon name="mdi:plus" /> Add Address
        </button>
      </div>
    </div>

    <!-- Content area -->
    <div>
      <div v-if="isLoading" class="rounded-lg border border-dashed border-gray-200 dark:border-gray-700 p-8 text-center text-gray-500">
        Loading addresses...
      </div>

      <div v-else>
        <div v-if="!addresses.length" class="rounded-lg p-6 bg-white dark:bg-gray-800 ring-1 ring-gray-100 dark:ring-0 text-center">
          <p class="text-gray-600 dark:text-gray-300 mb-2">No addresses yet</p>
          <button @click="openModal('create')" class="px-4 py-2 rounded-lg bg-blue-600 text-white shadow">Create your first address</button>
        </div>

        <div v-else class="grid gap-4">
          <!-- Desktop: a polished table, Mobile: cards -->
          <div class="hidden md:block">
            <div class="overflow-hidden rounded-lg shadow">
              <table class="min-w-full divide-y divide-gray-200 bg-white dark:bg-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <tr v-for="address in filteredAddresses" :key="address.uuid" class="hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ address.title }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ address.person_name }} • <span class="text-gray-400">{{ address.person_mobile }}</span></div>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="['inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold', typeBadges[address.type?.toLowerCase()]?.color]">
                        {{ typeBadges[address.type?.toLowerCase()]?.label || address.type }}
                      </span>
                  </td>

                  <td class="px-6 py-4 whitespace-normal text-sm text-gray-700 dark:text-gray-300">
                    {{ address.address_1 }}, {{ address.city }}, {{ address.state?.name || address.state }}, {{ address.postal_code }}, India
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-2">
                    <button @click="openModal('view', address)" title="View" class="px-3 py-1 rounded-lg bg-gray-50 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200">
                      <Icon name="mdi:eye" /> View
                    </button>
                    <button @click="openModal('edit', address)" title="Edit" class="px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow">
                      <Icon name="mdi:pencil" /> Edit
                    </button>
                    <button @click="deleteAddress(address.uuid)" title="Delete" class="px-3 py-1 rounded-lg bg-red-600 hover:bg-red-700 text-white">
                      <Icon name="mdi:delete" /> Delete
                    </button>
                  </td>
                </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Mobile cards -->
          <div class="md:hidden space-y-3">
            <div v-for="address in filteredAddresses" :key="address.uuid" class="bg-white dark:bg-gray-800 ring-1 ring-gray-100 dark:ring-0 rounded-lg p-4 shadow-sm">
              <div class="flex justify-between items-start">
                <div>
                  <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ address.title }}</div>
                  <div class="text-xs text-gray-500 mt-1">{{ address.person_name }} • <span class="text-gray-400">{{ address.person_mobile }}</span></div>
                </div>
                <div class="flex flex-col items-end gap-2">
                  <div :class="['inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold', typeBadges[address.type?.toLowerCase()]?.color]">
                    {{ typeBadges[address.type?.toLowerCase()]?.label || address.type }}
                  </div>
                  <div class="flex gap-2">
                    <button @click="openModal('view', address)" class="text-gray-600 hover:text-gray-800"><Icon name="mdi:eye" /></button>
                    <button @click="openModal('edit', address)" class="text-blue-600 hover:text-blue-800"><Icon name="mdi:pencil" /></button>
                    <button @click="deleteAddress(address.uuid)" class="text-red-600 hover:text-red-800"><Icon name="mdi:delete" /></button>
                  </div>
                </div>
              </div>
              <div class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                {{ address.address_1 }}, {{ address.city }}, {{ address.state?.name || address.state }}, {{ address.postal_code }}, India
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <transition name="fade">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40" @click="showModal = false"></div>

        <div class="relative w-full max-w-3xl mx-4">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl overflow-hidden ring-1 ring-gray-100 dark:ring-0">
            <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-700">
              <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                  {{ modalMode === 'create' ? 'Add New Address' : modalMode === 'edit' ? 'Edit Address' : 'View Address' }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Keep your addresses up-to-date for faster checkout.</p>
              </div>
              <div class="flex items-center gap-2">
                <button @click="showModal = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-300" title="Close">
                  ✖
                </button>
              </div>
            </div>

            <div class="p-6">
              <!-- Form Mode -->
              <form v-if="modalMode !== 'view'" class="grid grid-cols-1 md:grid-cols-2 gap-4" @submit.prevent="saveAddress">
                <!-- Left column -->
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Title
                    </label>
                    <input v-model="currentAddress.title" @input="delete errors.title" @blur="validateField('title')" type="text" placeholder="My Home / Office" class="input mt-1" />
                    <span v-if="errors.title" class="text-red-600 text-xs mt-1">{{ errors.title }}</span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Type <span class="text-rose-600">*</span>
                    </label>
                    <select v-model="currentAddress.type" @change="validateField('type')" class="input mt-1" required>
                      <option value="">Select type</option>
                      <option v-for="(badge, key) in typeBadges" :key="key" :value="key">{{ badge.label }}</option>
                    </select>
                    <span v-if="errors.type" class="text-red-600 text-xs mt-1">{{ errors.type }}</span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Street Address <span class="text-rose-600">*</span></label>
                    <input v-model="currentAddress.address_1" @input="delete errors.address_1" @blur="validateField('address_1')" type="text" placeholder="House number, building, road" class="input mt-1" />
                    <span v-if="errors.address_1" class="text-red-600 text-xs mt-1">{{ errors.address_1 }}</span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Landmark <span class="text-rose-600">*</span></label>
                    <input v-model="currentAddress.landmark" @input="delete errors.landmark" @blur="validateField('landmark')" type="text" placeholder="e.g. near hospital" class="input mt-1" />
                    <span v-if="errors.landmark" class="text-red-600 text-xs mt-1">{{ errors.landmark }}</span>
                  </div>
                </div>

                <!-- Right column -->
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Name <span class="text-rose-600">*</span></label>
                    <input v-model="currentAddress.person_name" @input="delete errors.person_name" @blur="validateField('person_name')" type="text" placeholder="Full name" class="input mt-1" />
                    <span v-if="errors.person_name" class="text-red-600 text-xs mt-1">{{ errors.person_name }}</span>
                  </div>

                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                      <input v-model="currentAddress.person_email" type="email" class="input mt-1" placeholder="you@example.com" />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mobile <span class="text-rose-600">*</span></label>
                      <input v-model="currentAddress.person_mobile" @input="delete errors.person_mobile" @blur="validateField('person_mobile')" type="text" class="input mt-1" placeholder="+91 99999 99999" />
                      <span v-if="errors.person_mobile" class="text-red-600 text-xs mt-1">{{ errors.person_mobile }}</span>
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">State <span class="text-rose-600">*</span></label>
                    <select v-model="currentAddress.state_code" @change="() => { fetchBlocks(); validateField('state_code') }" class="input mt-1">
                      <option disabled value="">Select your state</option>
                      <option v-for="state in stateList" :key="state.code" :value="state.code">{{ state.name }}</option>
                    </select>
                    <span v-if="errors.state_code" class="text-red-600 text-xs mt-1">{{ errors.state_code }}</span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">District / City <span class="text-rose-600">*</span></label>
                    <select v-model="currentAddress.city" @change="validateField('city')" class="input mt-1">
                      <option disabled value="">Select district</option>
                      <option v-for="district in districtList" :key="district" :value="district">{{ district }}</option>
                    </select>
                    <span v-if="errors.city" class="text-red-600 text-xs mt-1">{{ errors.city }}</span>
                  </div>

                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pincode <span class="text-rose-600">*</span></label>
                      <div class="relative">
                        <input
                            v-model="currentAddress.postal_code"
                            maxlength="6"
                            minlength="6"
                            @input="delete errors.postal_code"
                            @blur="validateField('postal_code')"
                            class="input mt-1 pr-10"
                            placeholder="6-digit pincode"
                        />
                        <button @click.prevent="fetchAddressFromPostalCode" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-blue-600">🔍</button>
                      </div>
                      <span v-if="errors.postal_code" class="text-red-600 text-xs mt-1">{{ errors.postal_code }}</span>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Block <span class="text-rose-600">*</span></label>

                      <!-- blockList contains objects; option value uses primitive `url` string -->
                      <template v-if="blockList && blockList.length">
                        <select v-model="currentAddress.block_id" @change="validateField('block_id')" class="input mt-1">
                          <option disabled value="">Select block</option>
                          <option v-for="b in blockList" :key="b.url" :value="b.url">{{ b.name }}</option>
                        </select>
                      </template>

                      <template v-else>
                        <input v-model="currentAddress.block_id" @input="delete errors.block_id" @blur="validateField('block_id')" type="text" class="input mt-1" placeholder="Enter block" />
                      </template>

                      <span v-if="errors.block_id" class="text-red-600 text-xs mt-1">{{ errors.block_id }}</span>
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country <span class="text-rose-600">*</span></label>
                    <input type="text" value="India" disabled class="input mt-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-200 cursor-not-allowed" />
                    <span v-if="errors.country" class="text-red-600 text-xs mt-1">{{ errors.country }}</span>
                  </div>
                </div>

                <!-- Buttons spanning full width -->
                <div class="md:col-span-2 flex justify-end gap-2 mt-2">
                  <button type="button" @click="showModal = false" class="px-4 py-2 rounded-md border hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</button>
                  <button type="submit" class="px-4 py-2 rounded-md bg-gradient-to-br from-blue-600 to-blue-500 text-white shadow">
                    <Icon name="mdi:content-save" /> Save address
                  </button>
                </div>
              </form>

              <!-- View Mode -->
              <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <div class="text-sm text-gray-500">Title</div>
                  <div class="font-semibold text-gray-800 dark:text-gray-100">{{ currentAddress.title }}</div>
                </div>
                <div>
                  <div class="text-sm text-gray-500">Type</div>
                  <div class="font-semibold text-gray-800 dark:text-gray-100">{{ typeBadges[currentAddress.type]?.label }}</div>
                </div>

                <div>
                  <div class="text-sm text-gray-500">Contact</div>
                  <div class="font-semibold text-gray-800 dark:text-gray-100">{{ currentAddress.person_name }} • <span class="text-gray-500">{{ currentAddress.person_mobile }}</span></div>
                </div>

                <div>
                  <div class="text-sm text-gray-500">Email</div>
                  <div class="font-semibold text-gray-800 dark:text-gray-100">{{ currentAddress.person_email }}</div>
                </div>

                <div class="md:col-span-2">
                  <div class="text-sm text-gray-500">Address</div>
                  <div class="font-semibold text-gray-800 dark:text-gray-100">
                    {{ currentAddress.address_1 }}, {{ currentAddress.landmark ? currentAddress.landmark + ', ' : '' }}{{ currentAddress.city }}, {{ currentAddress.state_code }}, {{ currentAddress.postal_code }}, India
                  </div>
                </div>

                <div class="md:col-span-2 flex justify-end gap-2 mt-3">
                  <button type="button" @click="showModal = false" class="px-4 py-2 rounded-md border hover:bg-gray-50 dark:hover:bg-gray-700">Close</button>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
.input {
  @apply w-full border rounded-md px-3 py-2 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200;
}

/* subtle fade for modal */
.fade-enter-active, .fade-leave-active {
  transition: opacity .2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
