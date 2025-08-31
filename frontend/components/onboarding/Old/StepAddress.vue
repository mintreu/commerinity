<template>
  <div class="space-y-6">
    <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Address Details</h2>

    <!-- Postal Code -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block font-medium text-gray-700 dark:text-gray-300">Postal Code</label>
        <div class="relative mt-1">
          <input
              v-model="form.postal_code"
              maxlength="6"
              minlength="6"
              class="w-full border rounded px-3 py-2 pr-10 bg-white dark:bg-gray-800 dark:text-white focus:ring"
              placeholder="Enter 6-digit postal code"
              @input="validateField('postal_code')"
          />
          <button
              @click.prevent="fetchAddressFromPostalCode"
              class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-blue-600"
              title="Autofill Address"
          >
            🔍
          </button>
        </div>
        <span v-if="errors.postal_code" class="text-red-600 text-sm mt-1 block">{{ errors.postal_code }}</span>
        <p class="text-sm text-gray-500 mt-1">Postal code helps auto-fill city and state details.</p>
      </div>
    </div>

    <!-- Address Inputs -->
    <div v-if="form.postal_code && addressLoaded" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <label class="block font-medium text-gray-700 dark:text-gray-300">Street Address</label>
        <input
            v-model="form.address_1"
            type="text"
            placeholder="Enter your full address"
            class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:text-white"
            @input="validateField('address_1')"
        />
        <span v-if="errors.address_1" class="text-red-600 text-sm mt-1 block">{{ errors.address_1 }}</span>
      </div>

      <div>
        <label class="block font-medium text-gray-700 dark:text-gray-300">Landmark</label>
        <input
            v-model="form.landmark"
            type="text"
            placeholder="e.g. near hospital"
            class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:text-white"
        />
      </div>

      <div>
        <label class="block font-medium text-gray-700 dark:text-gray-300">Village/Town</label>
        <input
            v-model="form.village"
            type="text"
            placeholder="Enter village or town name"
            class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:text-white"
        />
      </div>

      <div>
        <label class="block font-medium text-gray-700 dark:text-gray-300">District / City</label>
        <select
            v-model="form.city"
            class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:text-white"
            @change="validateField('city')"
        >
          <option disabled value="">Select district</option>
          <option v-for="district in districtList" :key="district" :value="district">
            {{ district }}
          </option>
        </select>
        <span v-if="errors.city" class="text-red-600 text-sm mt-1 block">{{ errors.city }}</span>
      </div>

      <div>
        <label class="block font-medium text-gray-700 dark:text-gray-300">State</label>
        <select
            v-model="form.state_code"
            @change="() => { fetchBlocks(); validateField('state_code') }"
            class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:text-white"
        >
          <option disabled value="">Select your state</option>
          <option v-for="state in stateList" :key="state.code" :value="state.code">
            {{ state.name }}
          </option>
        </select>
        <span v-if="errors.state_code" class="text-red-600 text-sm mt-1 block">{{ errors.state_code }}</span>
      </div>

      <div>
        <label class="block font-medium text-gray-700 dark:text-gray-300">Block / Municipality</label>
        <select
            v-model="form.block_id"
            @change="validateField('block_id')"
            class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:text-white"
        >
          <option disabled value="">Select block</option>
          <option v-for="block in blockList" :key="block" :value="block">
            {{ block }}
          </option>
        </select>
        <span v-if="errors.block_id" class="text-red-600 text-sm mt-1 block">{{ errors.block_id }}</span>
      </div>

      <div>
        <label class="block font-medium text-gray-700 dark:text-gray-300">Country</label>
        <select
            class="w-full border rounded px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-500 cursor-not-allowed"
            disabled
        >
          <option v-if="country" :value="country.iso_code_2">{{ country.name }}</option>
        </select>
      </div>
    </div>

    <!-- Navigation -->
    <div class="flex justify-between mt-8">
      <button
          @click="$emit('back')"
          class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600"
      >
        ← Back
      </button>
      <button
          @click="handleNext"
          :disabled="!isFormValid"
          class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
      >
        Next →
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, reactive, computed } from 'vue'
import { useSanctumFetch, useRuntimeConfig } from '#imports'

const config = useRuntimeConfig()
const props = defineProps({
  modelValue: Object
})
const emit = defineEmits(['update:modelValue', 'next', 'back'])

const form = reactive({ ...props.modelValue })

watch(
    form,
    () => emit('update:modelValue', { ...form }),
    { deep: true }
)

const country = ref<any>(null)
const stateList = ref<any[]>([])
const blockList = ref<string[]>([])
const districtList = ref<string[]>([])
const addressLoaded = ref(false)

onMounted(() => {
  fetchCountry()
  fetchStates()
})

async function fetchCountry() {
  const res = await useSanctumFetch(`${config.public.apiBase}/geo/countries`)
  country.value = res.data.find((c: any) => c.iso_code_2 === 'IN')
}

async function fetchStates() {
  const res = await useSanctumFetch(`${config.public.apiBase}/geo/states/IN`)
  stateList.value = res.data
}

async function fetchBlocks() {
  if (!form.state_code) return

  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/geo/state/${form.state_code}`)
    const blocks = res.data.blocks

    blockList.value = blocks.map((b: any) => b.name)
    const districts = [...new Set(blocks.map((b: any) => b.district))]
    districtList.value = districts
  } catch {
    blockList.value = []
    districtList.value = []
  }
}

async function fetchAddressFromPostalCode() {
  const code = form.postal_code
  if (!code || code.length !== 6) return

  try {
    const res = await $fetch(`https://api.postalpincode.in/pincode/${code}`)
    const data = res[0]
    if (data.Status !== 'Success') return alert('Invalid postal code')

    const po = data.PostOffice[0]
    form.city = po.District
    form.state_code = getStateCodeByName(po.State)
    form.village = po.Name
    form.block_id = '' // Clear old block
    addressLoaded.value = true

    await fetchBlocks()

    const blocks = [...new Set(data.PostOffice.map((po: any) => po.Block || 'Other'))]
    if (blocks.includes(po.Block)) form.block_id = po.Block
  } catch {
    alert('Failed to fetch address info')
  }
}

function getStateCodeByName(name: string): string {
  const state = stateList.value.find((s) => s.name.toLowerCase() === name.toLowerCase())
  return state?.code || ''
}

// Validation errors
const errors = reactive({
  postal_code: '',
  address_1: '',
  city: '',
  state_code: '',
  block_id: ''
})

function validateField(field: keyof typeof errors) {
  switch (field) {
    case 'postal_code':
      errors.postal_code = /^\d{6}$/.test(form.postal_code || '') ? '' : 'Postal code must be exactly 6 digits.'
      break
    case 'address_1':
      errors.address_1 = form.address_1?.trim() ? '' : 'Street address is required.'
      break
    case 'city':
      errors.city = form.city ? '' : 'Please select a district/city.'
      break
    case 'state_code':
      errors.state_code = form.state_code ? '' : 'Please select a state.'
      break
    case 'block_id':
      errors.block_id = form.block_id ? '' : 'Please select a block/municipality.'
      break
  }
}

function validateAll() {
  validateField('postal_code')
  validateField('address_1')
  validateField('city')
  validateField('state_code')
  validateField('block_id')

  return Object.values(errors).every((e) => e === '')
}

const isFormValid = computed(() => validateAll())

function handleNext() {
  if (validateAll()) {
    emit('next')
  }
}
</script>
