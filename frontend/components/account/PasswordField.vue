<template>
  <div>
    <label :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ label }}</label>
    <div class="relative">
      <input
          :id="id"
          :type="show ? 'text' : 'password'"
          :autocomplete="autocomplete"
          :value="modelValue"
          @input="onInput($event)"
          class="form-input pr-10"
          :aria-invalid="Boolean(error) ? 'true' : 'false'"
      />
      <button
          type="button"
          class="absolute right-3 top-2.5 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
          @click="toggleShow"
          aria-label="Toggle password visibility"
      >
        <Icon :name="show ? 'mdi:eye-off' : 'mdi:eye'" />
      </button>
    </div>
    <p v-if="error" class="form-error">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const props = defineProps({
  label: { type: String, required: true },
  modelValue: { type: String, required: true },
  field: { type: String, required: true },
  error: { type: [String, null], default: null },
  autocomplete: { type: String, default: 'new-password' },
})

const emit = defineEmits(['update:modelValue', 'clearError'])

const show = ref(false)
const id = computed(() => `password-${props.field}`)

function toggleShow() {
  show.value = !show.value
}

function onInput(e: Event) {
  const v = (e.target as HTMLInputElement).value
  emit('update:modelValue', v)
  emit('clearError', props.field)
}
</script>

<style scoped>
.form-input {
  @apply w-full mt-1 px-4 py-2 bg-gray-50 dark:bg-gray-700 border rounded-md
  text-gray-900 dark:text-white border-gray-300 dark:border-gray-600;
}
.form-error {
  @apply text-sm text-red-600 dark:text-red-400 mt-1 block;
}
</style>
