<template>
  <form @submit.prevent="handleSubmit">
    <!-- Subscription Section -->
    <div class="mb-6">
      <h2 class="text-lg font-semibold mb-2 text-gray-800 dark:text-white">Subscription</h2>
      <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
        Subscription gives you full access, while trial offers limited access for a short period.
      </p>

      <div class="space-y-2">
        <label class="flex items-center space-x-2">
          <input
              type="radio"
              value="subscribe"
              v-model="localData.subscription_type"
              class="text-blue-600 focus:ring-blue-500"
          />
          <span class="text-sm text-gray-800 dark:text-gray-200">
            <strong>Subscribe Now</strong> — Get immediate access to all premium features.
          </span>
        </label>

        <label class="flex items-center space-x-2">
          <input
              type="radio"
              value="trial"
              v-model="localData.subscription_type"
              class="text-blue-600 focus:ring-blue-500"
          />
          <span class="text-sm text-gray-800 dark:text-gray-200">
            <strong>Start Trial</strong> — Experience premium features for a limited time before committing.
          </span>
        </label>
        <span v-if="errors.subscription_type" class="text-red-600 text-sm mt-1 block">{{ errors.subscription_type }}</span>
      </div>
    </div>

    <!-- Terms & Conditions Section -->
    <div class="mb-6 border-t pt-6">
      <h2 class="text-lg font-semibold mb-2 text-gray-800 dark:text-white">Complete Onboarding</h2>
      <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
        Please take a moment to review our
        <a href="/terms" class="text-blue-600 hover:underline" target="_blank">Terms and Conditions</a>.
      </p>

      <label class="flex items-center space-x-2">
        <input
            type="checkbox"
            v-model="localData.tnc"
            class="text-blue-600 focus:ring-blue-500"
        />
        <span class="text-sm text-gray-800 dark:text-gray-200">
          I agree to the Terms & Conditions
        </span>
      </label>
      <span v-if="errors.tnc" class="text-red-600 text-sm mt-2 block">{{ errors.tnc }}</span>
    </div>

    <!-- Actions -->
    <div class="flex justify-between mt-8">
      <button
          type="button"
          class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded hover:bg-gray-300 dark:hover:bg-gray-600"
          @click="$emit('back')"
      >
        Back
      </button>

    </div>
  </form>
</template>

<script setup lang="ts">
import { reactive, watch, computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue', 'next', 'back'])

const localData = reactive({
  subscription_type: props.modelValue.subscription_type || '',
  tnc: props.modelValue.tnc ?? false
})

const errors = reactive({
  subscription_type: '',
  tnc: ''
})

// Sync back to main form on change
watch(localData, () => {
  emit('update:modelValue', { ...props.modelValue, ...localData })
}, { deep: true })

function validate() {
  errors.subscription_type = localData.subscription_type ? '' : 'Please select a subscription type.'
  errors.tnc = localData.tnc ? '' : 'You must agree to proceed.'

  return !errors.subscription_type && !errors.tnc
}

const isFormValid = computed(() => validate())

function handleSubmit() {
  if (validate()) {
    emit('update:modelValue', { ...props.modelValue, ...localData })
    emit('next')
  }
}
</script>
