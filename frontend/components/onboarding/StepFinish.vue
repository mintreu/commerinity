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
              required
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
              required
          />
          <span class="text-sm text-gray-800 dark:text-gray-200">
            <strong>Start Trial</strong> — Experience premium features for a limited time before committing.
          </span>
        </label>
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
            required
        />
        <span class="text-sm text-gray-800 dark:text-gray-200">
          I agree to the Terms & Conditions
        </span>
      </label>

      <p v-if="showError" class="text-red-600 text-sm mt-2">You must agree to proceed.</p>
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

      <button
          type="submit"
          class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
      >
        Finish
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { reactive, watch, ref } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue', 'next', 'back'])

const localData = reactive({
  subscription_type: props.modelValue.subscription_type || '',
  tnc: props.modelValue.tnc ?? true
})

const showError = ref(false)

// Sync back to main form
watch(localData, () => {
  emit('update:modelValue', { ...props.modelValue, ...localData })
}, { deep: true })

function handleSubmit() {
  if (!localData.tnc) {
    showError.value = true
    return
  }

  emit('update:modelValue', { ...props.modelValue, ...localData })
  emit('next')
}
</script>
