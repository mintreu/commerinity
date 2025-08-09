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
          />
          <button
              @click="fetchAddressFromPostalCode"
              class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-blue-600"
              title="Autofill Address"
          >
            🔍
          </button>
        </div>
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
        />
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
        >
          <option disabled value="">Select district</option>
          <option v-for="district in districtList" :key="district" :value="district">
            {{ district }}
          </option>
        </select>
      </div>


      <div>
        <label class="block font-medium text-gray-700 dark:text-gray-300">State</label>
        <select
            v-model="form.state_code"
            @change="fetchBlocks"
            class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:text-white"
        >
          <option disabled value="">Select your state</option>
          <option v-for="state in stateList" :key="state.code" :value="state.code">
            {{ state.name }}
          </option>
        </select>
      </div>

      <div>
        <label class="block font-medium text-gray-700 dark:text-gray-300">Block / Municipality</label>
        <select
            v-model="form.block_id"
            class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:text-white"
        >
          <option disabled value="">Select block</option>
          <option v-for="block in blockList" :key="block" :value="block">
            {{ block }}
          </option>
        </select>
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
      <button @click="$emit('back')" class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600">
        ← Back
      </button>
      <button @click="$emit('next')" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
        Next →
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useSanctumFetch, useRuntimeConfig } from '#imports'

const config = useRuntimeConfig()
const props = defineProps({
  modelValue: Object
})
const emit = defineEmits(['update:modelValue', 'next', 'back'])

const form = ref({ ...props.modelValue })
watch(form, () => emit('update:modelValue', form.value), { deep: true })

const country = ref<any>(null)
const stateList = ref<any[]>([])
const blockList = ref<string[]>([])
const districtList = ref<string[]>([])
const addressLoaded = ref(false)

onMounted(() => {
  fetchCountry()
  fetchStates()
})

// 🔹 Country + States
async function fetchCountry() {
  const res = await useSanctumFetch(`${config.public.apiBase}/geo/countries`)
  country.value = res.data.find((c: any) => c.iso_code_2 === 'IN')
}
async function fetchStates() {
  const res = await useSanctumFetch(`${config.public.apiBase}/geo/states/IN`)
  stateList.value = res.data
}

// 🔹 Blocks based on selected state
// async function fetchBlocks() {
//   if (!form.value.state_code) return
//   try {
//     const res = await useSanctumFetch(`${config.public.apiBase}/geo/state/${form.value.state_code}`)
//     blockList.value = res.data.blocks.map((b: any) => b.name)
//   } catch {
//     blockList.value = []
//   }
// }

async function fetchBlocks() {
  if (!form.value.state_code) return

  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/geo/state/${form.value.state_code}`)

    const blocks = res.data.blocks

    blockList.value = blocks.map((b: any) => b.name)

    // Extract unique district names
    const districts = [...new Set(blocks.map((b: any) => b.district))]
    districtList.value = districts

  } catch {
    blockList.value = []
    districtList.value = []
  }
}


// 🔹 Postal code → autofill
async function fetchAddressFromPostalCode() {
  const code = form.value.postal_code
  if (!code || code.length !== 6) return

  try {
    const res = await $fetch(`https://api.postalpincode.in/pincode/${code}`)
    const data = res[0]
    if (data.Status !== 'Success') return alert('Invalid postal code')

    const po = data.PostOffice[0]
    form.value.city = po.District
    form.value.state_code = getStateCodeByName(po.State)
    form.value.village = po.Name
    form.value.block_id = '' // Clear old block
    addressLoaded.value = true

    await fetchBlocks()

    // Attempt auto-select block
    const blocks = [...new Set(data.PostOffice.map(po => po.Block || 'Other'))]
    if (blocks.includes(po.Block)) form.value.block_id = po.Block
  } catch {
    alert('Failed to fetch address info')
  }
}

function getStateCodeByName(name: string): string {
  const state = stateList.value.find(s => s.name.toLowerCase() === name.toLowerCase())
  return state?.code || ''
}
</script>
