<template>
  <div class="step-address">
    <div class="max-w-3xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-6">
        <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <UIcon name="i-lucide-map-pin" class="w-8 h-8 text-orange-600 dark:text-orange-400" />
        </div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-2">
          Add your delivery address
        </h2>
        <p class="text-gray-600 dark:text-gray-400 text-sm md:text-base">
          This will be your default home address for deliveries
        </p>
      </div>

      <!-- Reusable Address Form -->
      <FormsAddressForm
        ref="addressForm"
        :initial-data="addressInitialData"
        :show-geolocation="true"
        @update:data="handleAddressUpdate"
        @valid="handleValidUpdate"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
interface AddressData {
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
  initialData?: Partial<AddressData>
  userName?: string
  userPhone?: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:data': [data: AddressData]
  'valid': [isValid: boolean]
}>()

const addressForm = ref()

// Prepare initial data with defaults
const addressInitialData = computed(() => ({
  person_name: props.initialData?.person_name || props.userName || '',
  person_mobile: props.initialData?.person_mobile || props.userPhone || '',
  address_1: props.initialData?.address_1 || '',
  address_2: props.initialData?.address_2 || '',
  city: props.initialData?.city || '',
  postal_code: props.initialData?.postal_code || '',
  block_id: props.initialData?.block_id || null,
  state_code: props.initialData?.state_code || '',
  country_code: props.initialData?.country_code || 'IN',
  latitude: props.initialData?.latitude || null,
  longitude: props.initialData?.longitude || null,
}))

// Handle data updates from the form
const handleAddressUpdate = (data: AddressData) => {
  emit('update:data', data)
}

// Handle validity updates
const handleValidUpdate = (valid: boolean) => {
  emit('valid', valid)
}

// Expose validation methods
const validate = (): boolean => {
  return addressForm.value?.validate() || false
}

const getData = (): AddressData => {
  return addressForm.value?.getData() || addressInitialData.value
}

defineExpose({
  validate,
  getData,
})
</script>
