<template>
  <div class="address-form">
    <!-- Geolocation Permission Banner (Optional) -->
    <UCard
      v-if="showGeolocationPrompt && !geolocationDenied"
      class="mb-6"
      :ui="{ body: 'p-4' }"
    >
      <div class="flex items-start gap-4">
        <div class="flex-shrink-0">
          <div class="w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
            <UIcon
              name="i-lucide-map-pin"
              class="w-5 h-5 text-primary-600 dark:text-primary-400"
            />
          </div>
        </div>
        <div class="flex-1">
      <h4 class="font-semibold text-gray-900 dark:text-white mb-1">
        Enable Location Access
      </h4>
      <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
        Allow location access to automatically detect your coordinates for more accurate delivery.
        You can skip if you prefer to enter them manually.
      </p>
          <div class="flex gap-2">
            <UButton
              size="sm"
              color="primary"
              :loading="fetchingLocation"
              @click="requestGeolocation"
            >
              <UIcon
                name="i-lucide-navigation"
                class="w-4 h-4 mr-1.5"
              />
              Enable Location
            </UButton>
            <UButton
              size="sm"
              variant="ghost"
              color="neutral"
              @click="dismissGeolocationPrompt"
            >
              Skip
            </UButton>
          </div>
        </div>
        <button
          type="button"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
          @click="dismissGeolocationPrompt"
        >
          <UIcon
            name="i-lucide-x"
            class="w-5 h-5"
          />
        </button>
      </div>
    </UCard>

    <!-- Form Fields -->
    <UForm
      :state="formState"
      :schema="schema"
      class="space-y-6 w-full"
    >
      <!-- Full Name & Phone (2 columns on desktop) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
      <UFormField
        label="Full Name"
        name="person_name"
        required
        class="w-full"
      >
        <UInput
          v-model="formState.person_name"
          placeholder="Enter recipient name"
          size="lg"
          icon="i-lucide-user"
          class="w-full"
        />
        <template #hint>
          <span class="text-xs text-gray-500">Name of the person receiving the delivery.</span>
        </template>
      </UFormField>

      <UFormField
        label="Phone Number"
        name="person_mobile"
        required
        class="w-full"
      >
        <UInput
          v-model="formState.person_mobile"
          type="tel"
          placeholder="10-digit mobile number"
          size="lg"
          icon="i-lucide-phone"
          class="w-full"
        />
        <template #hint>
          <span class="text-xs text-gray-500">Enter the 10-digit mobile number.</span>
        </template>
      </UFormField>
      </div>

      <!-- Address Line 1 (Full width) -->
      <UFormField
        label="Address Line 1"
        name="address_1"
        required
        class="w-full"
      >
        <UInput
          v-model="formState.address_1"
          placeholder="House no., Building name, Street"
          size="lg"
          icon="i-lucide-home"
          class="w-full"
        />
        <template #hint>
          <span class="text-xs text-gray-500">Use the exact address shown on courier labels.</span>
        </template>
      </UFormField>

      <!-- Address Line 2 (Full width) -->
      <UFormField
        label="Address Line 2"
        name="address_2"
        class="w-full"
      >
        <UInput
          v-model="formState.address_2"
          placeholder="Area, Landmark"
          size="lg"
          icon="i-lucide-map"
          class="w-full"
        />
      </UFormField>

      <!-- Country & State (2 columns on desktop) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
      <UFormField
        label="Country"
        name="country_code"
        required
        class="w-full"
      >
        <USelectMenu
          v-model="formState.country_code"
          :options="countries"
          placeholder="Select country"
          size="lg"
          icon="i-lucide-globe"
          option-attribute="label"
          value-attribute="value"
          :loading="loadingCountries"
          searchable
          class="w-full"
          @update:model-value="handleCountryChange"
        />
        </UFormField>

      <UFormField
        label="State / Province"
        name="state_code"
        required
        class="w-full"
      >
        <USelectMenu
          v-model="formState.state_code"
          :options="states"
          placeholder="Select state"
          size="lg"
          icon="i-lucide-map-pinned"
          option-attribute="label"
          value-attribute="value"
          :loading="loadingStates"
          :disabled="!formState.country_code || loadingStates"
          searchable
          class="w-full"
          @update:model-value="handleStateChange"
        />
        </UFormField>
      </div>

      <!-- City & Block (2 columns on desktop) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
      <UFormField
        label="City / District"
        name="city"
        required
        class="w-full"
      >
        <UInput
          v-model="formState.city"
          placeholder="Enter city name"
          size="lg"
          icon="i-lucide-building-2"
          class="w-full"
        />
        </UFormField>

      <UFormField
        label="Block / Area"
        name="block_id"
        class="w-full"
      >
        <USelectMenu
          v-model="formState.block_id"
          :options="blocks"
          placeholder="Select block"
          size="lg"
          icon="i-lucide-map"
          option-attribute="label"
          value-attribute="value"
          :loading="loadingBlocks"
          :disabled="!formState.state_code || loadingBlocks"
          searchable
          class="w-full"
          @update:model-value="handleBlockChange"
        />
        </UFormField>
      </div>

      <!-- Postal Code (Full width) -->
      <UFormField
        label="Postal Code / ZIP"
        name="postal_code"
        required
        class="w-full"
      >
        <UInput
          v-model="formState.postal_code"
          placeholder="Enter postal code"
          size="lg"
          icon="i-lucide-hash"
          maxlength="10"
          class="w-full"
        />
        <template #hint>
          <span class="text-xs text-gray-500">Ensure it matches your delivery zone.</span>
        </template>
      </UFormField>

      <!-- Hidden lat/lng fields -->
      <input
        v-model="formState.latitude"
        type="hidden"
      >
      <input
        v-model="formState.longitude"
        type="hidden"
      >
    </UForm>

    <!-- Location Status (if coordinates are set) -->
    <div
      v-if="formState.latitude && formState.longitude"
      class="mt-4 flex items-center gap-2 text-sm text-green-600 dark:text-green-400"
    >
      <UIcon
        name="i-lucide-map-pin-check-inside"
        class="w-4 h-4"
      />
      <span>Location coordinates detected</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { z } from 'zod'
import type { GeoOption } from '~/composables/useGeoData'

interface AddressFormData {
  person_name: string
  person_mobile: string
  address_1: string
  address_2: string
  city: string
  postal_code: string
  block_id: number | null
  state_code: string
  country_code: string
  latitude: number | null
  longitude: number | null
}

interface Props {
  initialData?: Partial<AddressFormData>
  defaultCountry?: string
  showGeolocation?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  defaultCountry: 'IN',
  showGeolocation: true
})

const emit = defineEmits<{
  'update:data': [data: AddressFormData]
  'valid': [isValid: boolean]
}>()

// Geo data composable
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

// Geolocation state
const geoReady = ref(false)
const showGeolocationPrompt = ref(props.showGeolocation)
const geolocationDenied = ref(false)
const fetchingLocation = ref(false)

// Form state
const formState = reactive<AddressFormData>({
  person_name: props.initialData?.person_name || '',
  person_mobile: props.initialData?.person_mobile || '',
  address_1: props.initialData?.address_1 || '',
  address_2: props.initialData?.address_2 || '',
  city: props.initialData?.city || '',
  postal_code: props.initialData?.postal_code || '',
  block_id: props.initialData?.block_id || null,
  state_code: props.initialData?.state_code || '',
  country_code: props.initialData?.country_code || props.defaultCountry,
  latitude: props.initialData?.latitude || null,
  longitude: props.initialData?.longitude || null
})

// Validation schema
const schema = z.object({
  person_name: z.string().min(2, 'Name must be at least 2 characters'),
  person_mobile: z.string().min(10, 'Please enter a valid phone number'),
  address_1: z.string().min(5, 'Please enter your address'),
  address_2: z.string().optional(),
  city: z.string().min(2, 'Please enter your city'),
  postal_code: z.string().min(4, 'Please enter a valid postal code'),
  state_code: z.string().min(1, 'Please select your state'),
  country_code: z.string().min(2, 'Please select your country'),
  block_id: z.number().nullable().optional(),
  latitude: z.number().nullable().optional(),
  longitude: z.number().nullable().optional()
})

// Watch for changes and emit
watch(
  () => ({ ...formState }),
  (newData) => {
    emit('update:data', newData)

    const result = schema.safeParse(formState)
    emit('valid', result.success)
  },
  { deep: true, immediate: true }
)

// Handle country change
const handleCountryChange = async (countryCode: string | number | null) => {
  if (!countryCode) return
  formState.state_code = ''
  formState.block_id = null
  resetStates()

  await fetchStates(String(countryCode))
}

// Handle state change
const handleStateChange = async (stateCode: string | number | null) => {
  formState.block_id = null
  resetBlocks()

  if (stateCode) {
    await fetchBlocks(String(stateCode))
  }
}

// Handle block change (optional - may have coordinates)
const handleBlockChange = (blockId: string | number | null) => {
  if (!blockId) return
  const block = blocks.value.find((b: GeoOption) => b.value === blockId)
  if (block?.coordinates) {
    formState.latitude = block.coordinates.lat
    formState.longitude = block.coordinates.lng
  }
}

// Geolocation methods
const requestGeolocation = async () => {
  if (!navigator.geolocation) {
    geolocationDenied.value = true
    showGeolocationPrompt.value = false
    return
  }

  fetchingLocation.value = true

  try {
    const position = await new Promise<GeolocationPosition>((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      })
    })

    formState.latitude = position.coords.latitude
    formState.longitude = position.coords.longitude
    showGeolocationPrompt.value = false

    // Success feedback
    const toast = useToast()
    toast.add({
      title: 'Location Detected',
      description: 'Your coordinates have been captured successfully',
      color: 'success',
      icon: 'i-lucide-map-pin-check-inside'
    })
  } catch (error) {
    console.error('Geolocation error:', error)
    geolocationDenied.value = true
    showGeolocationPrompt.value = false
  } finally {
    fetchingLocation.value = false
  }
}

const dismissGeolocationPrompt = () => {
  showGeolocationPrompt.value = false
}

// Expose methods
const validate = (): boolean => {
  const result = schema.safeParse(formState)
  return result.success
}

const getData = (): AddressFormData => ({ ...formState })

defineExpose({
  validate,
  getData
})

// Load countries on mount
onMounted(async () => {
  await fetchCountries()
  console.log('Countries loaded:', countries.value.length, countries.value)

  // Load states if country is pre-selected
  if (formState.country_code) {
    console.log('Loading states for preselected country:', formState.country_code)
    await fetchStates(formState.country_code)
    console.log('States loaded:', states.value.length, states.value)
  }

  // Load blocks if state is pre-selected
  if (formState.state_code) {
    console.log('Loading blocks for preselected state:', formState.state_code)
    await fetchBlocks(formState.state_code)
    console.log('Blocks loaded:', blocks.value.length)
  }

  geoReady.value = true
  console.log('Geo data ready')
})
</script>
