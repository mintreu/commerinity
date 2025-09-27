<template>
  <div class="space-y-3">
    <label :for="id" class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
      <Icon name="mdi:key" class="w-4 h-4" />
      <span>{{ label }}</span>
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="relative group">
      <!-- Password Input -->
      <div class="relative">
        <Icon name="mdi:lock" class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-purple-500 transition-colors duration-300" />
        <input
            :id="id"
            :type="show ? 'text' : 'password'"
            :autocomplete="autocomplete"
            :value="modelValue"
            :placeholder="placeholder"
            @input="onInput($event)"
            @focus="focused = true"
            @blur="focused = false"
            class="w-full pl-12 pr-16 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-300 font-medium"
            :class="{
              'border-red-500 bg-red-50 dark:bg-red-900/20': error,
              'border-green-300 bg-green-50 dark:bg-green-900/10': !error && modelValue && isValid,
              'focus:scale-[1.02]': focused
            }"
            :aria-invalid="Boolean(error) ? 'true' : 'false'"
        />

        <!-- Toggle Password Visibility -->
        <button
            type="button"
            class="absolute right-4 top-1/2 transform -translate-y-1/2 w-10 h-10 flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-600 transition-all duration-300 group"
            @click="toggleShow"
            :aria-label="show ? 'Hide password' : 'Show password'"
        >
          <Icon
              :name="show ? 'mdi:eye-off' : 'mdi:eye'"
              class="w-5 h-5 group-hover:scale-110 transition-transform duration-300"
          />
        </button>
      </div>

      <!-- Password Strength Indicator -->
      <div v-if="showStrength && modelValue && focused" class="mt-3">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Password Strength</span>
          <span class="text-xs font-bold capitalize" :class="strengthColorClass">{{ strengthLevel }}</span>
        </div>

        <div class="w-full bg-slate-200 dark:bg-slate-600 rounded-full h-2 overflow-hidden">
          <div
              class="h-full rounded-full transition-all duration-500 ease-out"
              :class="strengthBgClass"
              :style="{ width: strengthPercentage + '%' }"
          ></div>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="flex items-center gap-2 text-sm text-red-500 bg-red-50/80 dark:bg-red-900/20 px-4 py-3 rounded-xl border border-red-200/60 dark:border-red-800/60 backdrop-blur-sm">
      <Icon name="mdi:alert-circle" class="w-5 h-5 flex-shrink-0" />
      <span class="font-medium">{{ error }}</span>
    </div>

    <!-- Success Message -->
    <div v-else-if="!error && modelValue && isValid && showSuccess" class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400 bg-green-50/80 dark:bg-green-900/20 px-4 py-3 rounded-xl border border-green-200/60 dark:border-green-800/60 backdrop-blur-sm">
      <Icon name="mdi:check-circle" class="w-5 h-5 flex-shrink-0" />
      <span class="font-medium">Password meets security requirements</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

interface Props {
  label: string
  modelValue: string
  field: string
  error?: string | null
  autocomplete?: string
  placeholder?: string
  required?: boolean
  showStrength?: boolean
  showSuccess?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  autocomplete: 'new-password',
  placeholder: '',
  required: true,
  showStrength: false,
  showSuccess: true,
  error: null
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'clearError': [field: string]
}>()

// State
const show = ref(false)
const focused = ref(false)

// Computed
const id = computed(() => `password-${props.field}`)

const passwordValidation = computed(() => {
  const password = props.modelValue
  return {
    minLength: password.length >= 8,
    hasUpper: /[A-Z]/.test(password),
    hasLower: /[a-z]/.test(password),
    hasNumber: /\d/.test(password),
    hasSpecial: /[!@#$%^&*(),.?":{}|<>]/.test(password)
  }
})

const strengthScore = computed(() => {
  if (!props.modelValue) return 0
  const validations = Object.values(passwordValidation.value)
  return validations.filter(Boolean).length
})

const strengthLevel = computed(() => {
  const score = strengthScore.value
  if (score <= 1) return 'weak'
  if (score <= 2) return 'fair'
  if (score <= 3) return 'good'
  if (score <= 4) return 'strong'
  return 'excellent'
})

const strengthPercentage = computed(() => {
  return Math.min((strengthScore.value / 5) * 100, 100)
})

const strengthColorClass = computed(() => {
  switch (strengthLevel.value) {
    case 'weak': return 'text-red-500 dark:text-red-400'
    case 'fair': return 'text-orange-500 dark:text-orange-400'
    case 'good': return 'text-yellow-500 dark:text-yellow-400'
    case 'strong': return 'text-green-500 dark:text-green-400'
    case 'excellent': return 'text-emerald-500 dark:text-emerald-400'
    default: return 'text-slate-500 dark:text-slate-400'
  }
})

const strengthBgClass = computed(() => {
  switch (strengthLevel.value) {
    case 'weak': return 'bg-gradient-to-r from-red-500 to-red-600'
    case 'fair': return 'bg-gradient-to-r from-orange-500 to-orange-600'
    case 'good': return 'bg-gradient-to-r from-yellow-500 to-yellow-600'
    case 'strong': return 'bg-gradient-to-r from-green-500 to-green-600'
    case 'excellent': return 'bg-gradient-to-r from-emerald-500 to-emerald-600'
    default: return 'bg-slate-300 dark:bg-slate-600'
  }
})

const isValid = computed(() => {
  if (!props.modelValue) return false
  const required = ['minLength', 'hasUpper', 'hasLower', 'hasNumber']
  return required.every(key => passwordValidation.value[key])
})

// Methods
function toggleShow() {
  show.value = !show.value
}

function onInput(e: Event) {
  const value = (e.target as HTMLInputElement).value
  emit('update:modelValue', value)
  emit('clearError', props.field)
}
</script>
