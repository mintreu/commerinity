<template>
  <div class="flex justify-center gap-2">
    <input
      v-for="(digit, index) in otp"
      :key="index"
      ref="inputs"
      v-model="otp[index]"
      type="text"
      inputmode="numeric"
      maxlength="1"
      class="w-12 h-14 text-center text-2xl font-bold border-2 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:bg-gray-800 dark:border-gray-600 transition-all"
      @input="handleInput($event, index)"
      @keydown="handleKeydown($event, index)"
      @paste="handlePaste"
    >
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  modelValue: string
  length?: number
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'complete': [value: string]
}>()

const otp = ref(Array(props.length || 6).fill(''))
const inputs = ref<HTMLInputElement[]>([])

const handleInput = (e: Event, index: number) => {
  const target = e.target as HTMLInputElement
  const value = target.value.replace(/\D/g, '')

  otp.value[index] = value

  if (value && index < otp.value.length - 1) {
    inputs.value[index + 1]?.focus()
  }

  updateModelValue()
}

const handleKeydown = (e: KeyboardEvent, index: number) => {
  if (e.key === 'Backspace' && !otp.value[index] && index > 0) {
    inputs.value[index - 1]?.focus()
  }
}

const handlePaste = (e: ClipboardEvent) => {
  e.preventDefault()
  const pastedData = e.clipboardData?.getData('text').replace(/\D/g, '') || ''

  for (let i = 0; i < Math.min(pastedData.length, otp.value.length); i++) {
    otp.value[i] = pastedData[i]
  }

  updateModelValue()

  const nextIndex = Math.min(pastedData.length, otp.value.length - 1)
  inputs.value[nextIndex]?.focus()
}

const updateModelValue = () => {
  const value = otp.value.join('')
  emit('update:modelValue', value)

  if (value.length === otp.value.length) {
    emit('complete', value)
  }
}

watch(() => props.modelValue, (newValue) => {
  if (newValue === '') {
    otp.value = Array(props.length || 6).fill('')
  }
})
</script>
