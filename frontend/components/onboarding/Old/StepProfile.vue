<template>
  <form class="space-y-6" @submit.prevent="onSubmit">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="col-span-full">
        <label class="form-label" for="name">Full Name</label>
        <input
            id="name"
            v-model="model.name"
            type="text"
            class="form-input"
            placeholder="Your name"
            @input="validateField('name')"
        />
        <span v-if="errors.name" class="text-red-600 text-sm mt-1 block">{{ errors.name }}</span>
      </div>

      <div>
        <label class="form-label" for="mobile">Mobile</label>
        <input
            id="mobile"
            v-model="model.mobile"
            type="text"
            class="form-input"
            placeholder="Your mobile"
            maxlength="10"
            @input="validateField('mobile')"
        />
        <span v-if="errors.mobile" class="text-red-600 text-sm mt-1 block">{{ errors.mobile }}</span>
      </div>

      <div>
        <label class="form-label" for="email">Email</label>
        <input
            id="email"
            v-model="model.email"
            type="email"
            class="form-input"
            placeholder="Your email"
            @input="validateField('email')"
        />
        <span v-if="errors.email" class="text-red-600 text-sm mt-1 block">{{ errors.email }}</span>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="form-label" for="dob">Date of Birth</label>
        <input id="dob" v-model="model.dob" type="date" class="form-input" />
      </div>
      <div>
        <label class="form-label" for="gender">Gender</label>
        <select id="gender" v-model="model.gender" class="form-input">
          <option disabled value="">Select Gender</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
          <option value="other">Other</option>
        </select>
      </div>
    </div>

    <div class="flex justify-between pt-4">
      <button type="button" class="btn-secondary" @click="emit('back')">
        Back
      </button>
      <button type="submit" class="btn-primary" :disabled="!isValid">
        Continue
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { reactive, computed, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue', 'next', 'back'])

// Two-way binding with v-model using reactive proxy
const model = reactive({ ...props.modelValue })

// Sync model back to parent when local model changes
watch(
    model,
    (val) => {
      emit('update:modelValue', { ...val })
    },
    { deep: true }
)

// Validation error messages
const errors = reactive({
  name: '',
  email: '',
  mobile: ''
})

// Field validation functions
function validateField(field: 'name' | 'email' | 'mobile') {
  switch (field) {
    case 'name':
      errors.name = model.name && model.name.trim() !== '' ? '' : 'Name is required.'
      break
    case 'email':
      errors.email = /^\S+@\S+\.\S+$/.test(model.email) ? '' : 'Valid email is required.'
      break
    case 'mobile':
      // Only digits, exactly 10 digits
      const mobileClean = model.mobile.replace(/\D/g, '') // remove non-digit chars
      errors.mobile =
          mobileClean.length === 10 ? '' : 'Mobile number must be exactly 10 digits.'
      break
  }
}

// Validate all fields and return overall validity
function validate() {
  validateField('name')
  validateField('email')
  validateField('mobile')
  return !errors.name && !errors.email && !errors.mobile
}

// Computed property for overall form validity
const isValid = computed(() => {
  return validate()
})

function onSubmit() {
  if (validate()) {
    emit('next')
  }
}

defineExpose({ validate })
</script>

<style scoped>
.form-label {
  @apply block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300;
}
.form-input {
  @apply w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition;
}
.btn-primary {
  @apply bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition font-medium disabled:opacity-50;
}
.btn-secondary {
  @apply bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white px-4 py-2 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition font-medium;
}
</style>
