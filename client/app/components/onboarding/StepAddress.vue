<template>
  <div class="step-address">
    <div class="max-w-lg mx-auto">
      <!-- Header -->
      <div class="text-center mb-6">
        <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <UIcon name="i-lucide-map-pin" class="w-8 h-8 text-orange-600 dark:text-orange-400" />
        </div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-2">
          Add your address
        </h2>
        <p class="text-gray-600 dark:text-gray-400 text-sm md:text-base">
          This will be your default delivery address
        </p>
      </div>

      <!-- Form -->
      <UForm :state="formState" :schema="schema" class="space-y-5" @submit="handleSubmit">
        <!-- Address Label -->
        <UFormField label="Address Label" name="label" required>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="option in labelOptions"
              :key="option.value"
              type="button"
              :class="[
                'px-4 py-2 rounded-lg font-medium text-sm transition-all',
                formState.label === option.value
                  ? 'bg-primary-500 text-white shadow-md'
                  : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'
              ]"
              @click="formState.label = option.value"
            >
              <UIcon :name="option.icon" class="w-4 h-4 mr-1.5 inline" />
              {{ option.label }}
            </button>
          </div>
        </UFormField>

        <!-- Full Name -->
        <UFormField label="Full Name" name="name" required>
          <UInput
            v-model="formState.name"
            placeholder="Enter recipient name"
            size="lg"
            icon="i-lucide-user"
          />
        </UFormField>

        <!-- Phone -->
        <UFormField label="Phone Number" name="phone" required>
          <UInput
            v-model="formState.phone"
            type="tel"
            placeholder="+91 9876543210"
            size="lg"
            icon="i-lucide-phone"
          />
        </UFormField>

        <!-- Address Line 1 -->
        <UFormField label="Address Line 1" name="address_line_1" required>
          <UInput
            v-model="formState.address_line_1"
            placeholder="House no., Building name, Street"
            size="lg"
            icon="i-lucide-home"
          />
        </UFormField>

        <!-- Address Line 2 -->
        <UFormField label="Address Line 2" name="address_line_2" hint="Optional">
          <UInput
            v-model="formState.address_line_2"
            placeholder="Area, Landmark"
            size="lg"
            icon="i-lucide-map"
          />
        </UFormField>

        <!-- City & State Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <UFormField label="City" name="city" required>
            <UInput
              v-model="formState.city"
              placeholder="City"
              size="lg"
              icon="i-lucide-building-2"
            />
          </UFormField>

          <UFormField label="State" name="state" required>
            <UInput
              v-model="formState.state"
              placeholder="State"
              size="lg"
              icon="i-lucide-map-pinned"
            />
          </UFormField>
        </div>

        <!-- Postal Code & Country Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <UFormField label="Postal Code" name="postal_code" required>
            <UInput
              v-model="formState.postal_code"
              placeholder="123456"
              size="lg"
              icon="i-lucide-hash"
              maxlength="10"
            />
          </UFormField>

          <UFormField label="Country" name="country" required>
            <USelect
              v-model="formState.country"
              :items="countryOptions"
              placeholder="Select country"
              size="lg"
              icon="i-lucide-globe"
            />
          </UFormField>
        </div>
      </UForm>
    </div>
  </div>
</template>

<script setup lang="ts">
import { z } from 'zod'

interface AddressData {
  label: string
  name: string
  phone: string
  address_line_1: string
  address_line_2: string
  city: string
  state: string
  postal_code: string
  country: string
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

const labelOptions = [
  { label: 'Home', value: 'home', icon: 'i-lucide-home' },
  { label: 'Work', value: 'work', icon: 'i-lucide-briefcase' },
  { label: 'Other', value: 'other', icon: 'i-lucide-map-pin' }
]

const countryOptions = [
  { label: 'India', value: 'IN' },
  { label: 'United States', value: 'US' },
  { label: 'United Kingdom', value: 'GB' },
  { label: 'Canada', value: 'CA' },
  { label: 'Australia', value: 'AU' },
  { label: 'Singapore', value: 'SG' },
  { label: 'United Arab Emirates', value: 'AE' }
]

const formState = reactive<AddressData>({
  label: props.initialData?.label || 'home',
  name: props.initialData?.name || props.userName || '',
  phone: props.initialData?.phone || props.userPhone || '',
  address_line_1: props.initialData?.address_line_1 || '',
  address_line_2: props.initialData?.address_line_2 || '',
  city: props.initialData?.city || '',
  state: props.initialData?.state || '',
  postal_code: props.initialData?.postal_code || '',
  country: props.initialData?.country || 'IN'
})

const schema = z.object({
  label: z.string().min(1, 'Please select an address label'),
  name: z.string().min(2, 'Name must be at least 2 characters'),
  phone: z.string().min(10, 'Please enter a valid phone number'),
  address_line_1: z.string().min(5, 'Please enter your address'),
  address_line_2: z.string().optional(),
  city: z.string().min(2, 'Please enter your city'),
  state: z.string().min(2, 'Please enter your state'),
  postal_code: z.string().min(4, 'Please enter a valid postal code'),
  country: z.string().min(2, 'Please select your country')
})

// Watch and emit
watch(
  () => ({ ...formState }),
  (newData) => {
    emit('update:data', newData)
    
    const result = schema.safeParse(formState)
    emit('valid', result.success)
  },
  { deep: true, immediate: true }
)

const handleSubmit = () => {
  // Parent handles navigation
}

// Expose validation
const validate = (): boolean => {
  const result = schema.safeParse(formState)
  return result.success
}

const getData = (): AddressData => ({ ...formState })

defineExpose({
  validate,
  getData
})
</script>
