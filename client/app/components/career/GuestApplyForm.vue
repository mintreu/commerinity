<template>
  <div class="guest-apply-form space-y-6">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
      Apply as Guest
    </h2>
    <p class="text-sm text-gray-600 dark:text-gray-400">
      Create an account and apply for this position in one go.
    </p>
    <UForm
      :state="formState"
      :schema="schema"
      class="space-y-6"
      @submit="handleSubmit"
    >
      <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 border-b pb-2">
        Your Information
      </h3>

      <UFormGroup
        label="Full Name"
        name="name"
        required
      >
        <UInput
          v-model="formState.name"
          placeholder="John Doe"
          size="lg"
          icon="i-heroicons-user"
        />
      </UFormGroup>

      <UFormGroup
        label="Email Address"
        name="email"
        required
      >
        <UInput
          v-model="formState.email"
          type="email"
          placeholder="you@example.com"
          size="lg"
          icon="i-heroicons-envelope"
        />
      </UFormGroup>

      <UFormGroup
        label="Mobile Number"
        name="mobile"
        required
      >
        <UInput
          v-model="formState.mobile"
          type="tel"
          placeholder="+919876543210"
          size="lg"
          icon="i-heroicons-phone"
        />
      </UFormGroup>

      <UFormGroup
        label="Date of Birth"
        name="dob"
        required
      >
        <UInput
          v-model="formState.dob"
          type="date"
          size="lg"
        />
      </UFormGroup>

      <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 border-b pb-2 pt-4">
        Your Address
      </h3>
      <ClientOnly>
        <StepAddress
          @update:data="handleAddressUpdate"
          @valid="addressValid = $event"
        />
      </ClientOnly>

      <div class="flex justify-end gap-4 pt-4">
        <UButton
          type="button"
          color="gray"
          @click="$emit('cancel')"
        >
          Cancel
        </UButton>
        <UButton
          type="submit"
          :loading="submitting"
        >
          Submit Application
        </UButton>
      </div>
    </UForm>
  </div>
</template>

<script setup lang="ts">
import { z } from 'zod'
import StepAddress from '~/components/onboarding/StepAddress.vue'

const emit = defineEmits(['cancel', 'submit'])

const formState = reactive({
  name: '',
  email: '',
  mobile: '',
  dob: '',
  address: {}
})

const addressValid = ref(false)
const submitting = ref(false)

const schema = z.object({
  name: z.string().min(2, 'Name is required'),
  email: z.string().email('Invalid email address'),
  mobile: z.string().min(10, 'Invalid mobile number'),
  dob: z.string().min(1, 'Date of birth is required')
})

const handleAddressUpdate = (data: any) => {
  formState.address = data
}

const handleSubmit = async () => {
  if (!addressValid.value) {
    // Maybe show a toast notification
    console.error('Address form is invalid')
    return
  }
  submitting.value = true
  try {
    emit('submit', formState)
  } catch (error) {
    console.error('Failed to submit application:', error)
  } finally {
    submitting.value = false
  }
}
</script>
