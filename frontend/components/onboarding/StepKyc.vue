<template>
  <form @submit.prevent="emit('next')">
    <div class="grid gap-4 sm:grid-cols-2">
      <!-- Aadhaar Number -->
      <div>
        <label class="block font-medium text-gray-700 dark:text-gray-300">Aadhaar Number</label>
        <p class="text-sm text-gray-500 mb-1">Must be a 12-digit number without spaces.</p>
        <input
            v-model="modelValue.aadhaar"
            type="text"
            maxlength="12"
            placeholder="Enter 12-digit Aadhaar number"
            class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:text-white"
        />
        <p v-if="modelValue.aadhaar && !isValidAadhaar" class="text-red-500 text-sm mt-1">
          Invalid Aadhaar number.
        </p>
      </div>

      <!-- PAN Number -->
      <div>
        <label class="block font-medium text-gray-700 dark:text-gray-300">PAN Number</label>
        <p class="text-sm text-gray-500 mb-1">Format: 5 uppercase letters, 4 digits, and 1 uppercase letter (ABCDE1234F).</p>
        <input
            v-model="modelValue.pan"
            type="text"
            maxlength="10"
            placeholder="Enter PAN (ABCDE1234F)"
            class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:text-white uppercase"
        />
        <p v-if="modelValue.pan && !isValidPan" class="text-red-500 text-sm mt-1">
          Invalid PAN format.
        </p>
      </div>

      <!-- Aadhaar Upload -->
      <div>
        <label class="block font-medium text-gray-700 dark:text-gray-300">Upload Aadhaar Image</label>
        <p class="text-sm text-gray-500 mb-1">Accepted formats: JPEG, PNG, or PDF. Max size: 2MB.</p>
        <input
            type="file"
            accept="image/*,.pdf"
            @change="handleFileChange($event, 'aadhaar_file')"
            class="w-full text-sm mt-1"
        />
      </div>

      <!-- PAN Upload -->
      <div>
        <label class="block font-medium text-gray-700 dark:text-gray-300">Upload PAN Image</label>
        <p class="text-sm text-gray-500 mb-1">Accepted formats: JPEG, PNG, or PDF. Max size: 2MB.</p>
        <input
            type="file"
            accept="image/*,.pdf"
            @change="handleFileChange($event, 'pan_file')"
            class="w-full text-sm mt-1"
        />
      </div>
    </div>

    <p class="text-sm mt-4 text-yellow-500 dark:text-yellow-400">
      ⚠️ If any detail is found invalid or fake, your account may be <strong>banned</strong> permanently.
    </p>

    <!-- Navigation Buttons -->
    <div class="flex justify-between mt-6">
      <button
          type="button"
          @click="emit('back')"
          class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600"
      >
        Back
      </button>
      <button
          type="submit"
          :disabled="!isFormValid"
          class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
      >
        Next
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
const emit = defineEmits(['next', 'back'])

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
})

const modelValue = props.modelValue

function handleFileChange(e: Event, key: 'aadhaar_file' | 'pan_file') {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    modelValue[key] = target.files[0]
  }
}

// Validation Rules
const isValidAadhaar = computed(() => /^[0-9]{12}$/.test(modelValue.aadhaar || ''))
const isValidPan = computed(() =>
    /^[A-Z]{5}[0-9]{4}[A-Z]$/.test((modelValue.pan || '').toUpperCase())
)

// Optional: Add file validations here if needed (file type/size etc)
const isFormValid = computed(() => {
  return isValidAadhaar.value && isValidPan.value
})
</script>
