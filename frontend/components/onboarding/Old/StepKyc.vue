<template>
  <form @submit.prevent="onSubmit" class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2">
      <!-- Aadhaar Number -->
      <div>
        <label
            for="aadhaar"
            class="block font-medium text-gray-700 dark:text-gray-300"
        >Aadhaar Number</label>
        <p class="text-sm text-gray-500 mb-1">
          Must be a 12-digit number without spaces.
        </p>
        <input
            id="aadhaar"
            v-model="model.aadhaar"
            type="text"
            maxlength="12"
            placeholder="Enter 12-digit Aadhaar number"
            class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:text-white"
            @input="validateField('aadhaar')"
        />
        <span v-if="errors.aadhaar" class="text-red-600 text-sm mt-1 block"
        >{{ errors.aadhaar }}</span
        >
      </div>

      <!-- PAN Number -->
      <div>
        <label
            for="pan"
            class="block font-medium text-gray-700 dark:text-gray-300"
        >PAN Number</label>
        <p class="text-sm text-gray-500 mb-1">
          Format: 5 uppercase letters, 4 digits, and 1 uppercase letter
          (ABCDE1234F).
        </p>
        <input
            id="pan"
            v-model="model.pan"
            type="text"
            maxlength="10"
            placeholder="Enter PAN (ABCDE1234F)"
            class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:text-white uppercase"
            @input="validateField('pan')"
        />
        <span v-if="errors.pan" class="text-red-600 text-sm mt-1 block"
        >{{ errors.pan }}</span
        >
      </div>

      <!-- Aadhaar Upload -->
      <div>
        <label
            for="aadhaar_file"
            class="block font-medium text-gray-700 dark:text-gray-300"
        >Upload Aadhaar Image</label
        >
        <div v-if="model.aadhaarImageUrl" class="mb-2">
          <img
              :src="model.aadhaarImageUrl"
              alt="Aadhaar Preview"
              class="max-h-32 object-contain"
          />
        </div>
        <input
            id="aadhaar_file"
            type="file"
            accept="image/jpeg,image/png,application/pdf"
            @change="handleFileChange($event, 'aadhaar_file')"
            class="w-full text-sm mt-1"
        />
        <span
            v-if="errors.aadhaar_file"
            class="text-red-600 text-sm mt-1 block"
        >{{ errors.aadhaar_file }}</span
        >
      </div>

      <!-- PAN Upload -->
      <div>
        <label
            for="pan_file"
            class="block font-medium text-gray-700 dark:text-gray-300"
        >Upload PAN Image</label
        >
        <p class="text-sm text-gray-500 mb-1">
          Accepted formats: JPEG, PNG, or PDF. Max size: 2MB.
        </p>
        <input
            id="pan_file"
            type="file"
            accept="image/jpeg,image/png,application/pdf"
            @change="handleFileChange($event, 'pan_file')"
            class="w-full text-sm mt-1"
        />
        <span
            v-if="errors.pan_file"
            class="text-red-600 text-sm mt-1 block"
        >{{ errors.pan_file }}</span
        >
      </div>
    </div>

    <p class="text-sm mt-4 text-yellow-500 dark:text-yellow-400">
      ⚠️ If any detail is found invalid or fake, your account may be
      <strong>banned</strong> permanently.
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
import { reactive, computed, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
})

console.log(props);

const emit = defineEmits(['update:modelValue', 'next', 'back'])

// Use the modelValue directly (kyc object)
const model = reactive({ ...props.modelValue })

// Sync local model changes to parent
watch(
    model,
    (val) => {
      emit('update:modelValue', { ...val })
    },
    { deep: true }
)

const errors = reactive({
  aadhaar: '',
  pan: '',
  aadhaar_file: '',
  pan_file: '',
})

function validateField(field: 'aadhaar' | 'pan' | 'aadhaar_file' | 'pan_file') {
  switch (field) {
    case 'aadhaar':
      errors.aadhaar = /^\d{12}$/.test(model.aadhaar)
          ? ''
          : 'Aadhaar must be exactly 12 digits.'
      break
    case 'pan':
      errors.pan = /^[A-Z]{5}[0-9]{4}[A-Z]$/.test(model.pan.toUpperCase())
          ? ''
          : 'PAN format is invalid.'
      break
    case 'aadhaar_file':
      errors.aadhaar_file = validateFile(model.aadhaar_file)
      break
    case 'pan_file':
      errors.pan_file = validateFile(model.pan_file)
      break
  }
}

function validateFile(file: File | null) {
  if (!file) return ''
  const validTypes = ['image/jpeg', 'image/png', 'application/pdf']
  if (!validTypes.includes(file.type))
    return 'Invalid file type. Must be JPEG, PNG or PDF.'
  if (file.size > 2 * 1024 * 1024) return 'File size must be less than 2MB.'
  return ''
}

function validate() {
  validateField('aadhaar')
  validateField('pan')
  validateField('aadhaar_file')
  validateField('pan_file')

  return (
      !errors.aadhaar &&
      !errors.pan &&
      !errors.aadhaar_file &&
      !errors.pan_file &&
      model.aadhaar !== '' &&
      model.pan !== ''
  )
}

const isFormValid = computed(() => validate())

function handleFileChange(e: Event, key: 'aadhaar_file' | 'pan_file') {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    model[key] = target.files[0]
  } else {
    model[key] = null
  }
  validateField(key)
}

function onSubmit() {
  if (validate()) {
    emit('next')
  }
}

defineExpose({ validate })
</script>

<style scoped>
/* Add your styles here or keep empty */
</style>
