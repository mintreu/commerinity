<template>
  <div class="address-form">
    <!-- Geolocation Permission Banner -->
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
            :items="countries"
            placeholder="Select country"
            size="lg"
            icon="i-lucide-globe"
            value-key="value"
            label-key="label"
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
            :items="states"
            placeholder="Select state"
            size="lg"
            icon="i-lucide-map-pinned"
            value-key="value"
            label-key="label"
            :loading="loadingStates"
            :disabled="!formState.country_code || loadingStates"
            searchable
            class="w-full"
            @update:model-value="handleStateChange"
          />
        </UFormField>
      </div>

      <!-- District & Block (2 columns on desktop) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
        <UFormField
          label="District"
          name="district_id"
          class="w-full"
        >
          <USelectMenu
            v-model="formState.district_id"
            :items="districts"
            placeholder="Select district"
            size="lg"
            icon="i-lucide-map"
            value-key="value"
            label-key="label"
            :loading="loadingDistricts"
            :disabled="!formState.state_code || loadingDistricts"
            searchable
            class="w-full"
            @update:model-value="handleDistrictChange"
          />
        </UFormField>

        <UFormField
          label="Block / Area"
          name="block_id"
          class="w-full"
        >
          <USelectMenu
            v-model="formState.block_id"
            :items="blocks"
            placeholder="Select block"
            size="lg"
            icon="i-lucide-map"
            value-key="value"
            label-key="label"
            :loading="loadingBlocks"
            :disabled="!formState.district_id || loadingBlocks"
            searchable
            class="w-full"
            @update:model-value="handleBlockChange"
          />
        </UFormField>
      </div>

      <!-- City & Postal (2 columns on desktop) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
        <UFormField
          label="City"
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
      </div>

      <!-- Hidden lat/lng fields -->
      <input
        :value="formState.latitude ?? ''"
        type="hidden"
      >
      <input
        :value="formState.longitude ?? ''"
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
import type { GeoOption } from '~/composables/useGeoData'

interface AddressFormData {
  person_name: string
  person_mobile: string
  address_1: string
  address_2: string
  city: string
  postal_code: string
  district_id: number | null
  block_id: number | null
  state_code: string
  country_code: string
  latitude: number | null
  longitude: number | null
}

type SelectValue = string | number | { value?: string | number } | null | undefined

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
  districts,
  blocks,
  loadingCountries,
  loadingStates,
  loadingDistricts,
  loadingBlocks,
  fetchCountries,
  fetchStates,
  fetchDistricts,
  fetchBlocks,
  resetStates,
  resetDistricts,
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
  district_id: props.initialData?.district_id || null,
  block_id: props.initialData?.block_id || null,
  state_code: props.initialData?.state_code || '',
  country_code: props.initialData?.country_code || props.defaultCountry,
  latitude: props.initialData?.latitude || null,
  longitude: props.initialData?.longitude || null
})

const normalizeSelectValue = (value: SelectValue): string | number | null => {
  if (value === null || value === undefined || value === '') return null
  if (typeof value === 'object') {
    const raw = value.value
    if (raw === null || raw === undefined || raw === '') return null
    return raw
  }
  return value
}

const normalizeNumericValue = (value: SelectValue): number | null => {
  const raw = normalizeSelectValue(value)
  if (raw === null) return null
  const parsed = Number(raw)
  return Number.isFinite(parsed) ? parsed : null
}

const normalizeAddressData = (data: AddressFormData): AddressFormData => ({
  ...data,
  country_code: String(normalizeSelectValue(data.country_code) || ''),
  state_code: String(normalizeSelectValue(data.state_code) || ''),
  district_id: normalizeNumericValue(data.district_id),
  block_id: normalizeNumericValue(data.block_id),
  latitude: normalizeNumericValue(data.latitude),
  longitude: normalizeNumericValue(data.longitude)
})

const normalizeMobile = (value: string): string => value.replace(/\D/g, '').slice(-10)

const isProgressionValid = (data: AddressFormData): boolean => {
  const normalized = normalizeAddressData(data)
  return normalized.person_name.trim().length >= 2
    && normalizeMobile(normalized.person_mobile).length === 10
    && normalized.address_1.trim().length >= 5
    && normalized.city.trim().length >= 2
    && normalized.postal_code.trim().length >= 4
    && normalized.country_code.trim().length >= 2
    && normalized.state_code.trim().length >= 1
}

// Watch for changes and emit
watch(
  () => ({ ...formState }),
  (newData) => {
    const normalized = normalizeAddressData(newData as AddressFormData)
    emit('update:data', normalized)
    emit('valid', isProgressionValid(normalized))
  },
  { deep: true, immediate: true }
)

// Handle country change
const handleCountryChange = async (countryCode: SelectValue) => {
  const normalizedCountry = normalizeSelectValue(countryCode)
  if (!normalizedCountry) return
  formState.country_code = String(normalizedCountry)
  formState.state_code = ''
  formState.district_id = null
  formState.block_id = null
  resetStates()

  await fetchStates(formState.country_code)
}

// Handle state change
const handleStateChange = async (stateCode: SelectValue) => {
  const normalizedState = normalizeSelectValue(stateCode)
  formState.state_code = normalizedState ? String(normalizedState) : ''
  formState.district_id = null
  formState.block_id = null
  resetDistricts()

  if (formState.state_code) {
    await fetchDistricts(formState.state_code)
  }
}

// Handle district change
const handleDistrictChange = async (districtId: SelectValue) => {
  formState.district_id = normalizeNumericValue(districtId)
  formState.block_id = null
  resetBlocks()

  if (formState.state_code && formState.district_id) {
    await fetchBlocks(formState.state_code, formState.district_id)
  }
}

// Handle block change (optional - may have coordinates)
const handleBlockChange = (blockId: SelectValue) => {
  formState.block_id = normalizeNumericValue(blockId)
  if (!formState.block_id) return

  const block = blocks.value.find((b: GeoOption) => Number(b.value) === formState.block_id)
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
  return isProgressionValid(formState)
}

const getData = (): AddressFormData => normalizeAddressData(formState)

defineExpose({
  validate,
  getData
})

// Load countries on mount
onMounted(async () => {
  // Temporarily clear country_code to avoid mismatch
  const tempCountry = formState.country_code
  formState.country_code = ''

  await fetchCountries()
  console.log('Countries loaded:', countries.value.length, countries.value)

  // Restore and validate country_code
  if (tempCountry && countries.value.some(c => c.value === tempCountry)) {
    formState.country_code = tempCountry
    console.log('Loading states for preselected country:', tempCountry)
    await fetchStates(tempCountry)
    console.log('States loaded:', states.value.length, states.value)
  } else if (tempCountry) {
    // Country not found, use default
    console.warn(`Country ${tempCountry} not found, setting default`)
    formState.country_code = countries.value[0]?.value || ''
  }

  // Load districts and blocks if state/district are pre-selected
  if (formState.state_code) {
    console.log('Loading districts for preselected state:', formState.state_code)
    await fetchDistricts(formState.state_code)
    console.log('Districts loaded:', districts.value.length)

    if (formState.district_id) {
      console.log('Loading blocks for preselected district:', formState.district_id)
      await fetchBlocks(formState.state_code, formState.district_id)
      console.log('Blocks loaded:', blocks.value.length)
    }
  }

  geoReady.value = true
  console.log('Geo data ready, final country_code:', formState.country_code)
})
</script>
