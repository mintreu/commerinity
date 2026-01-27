<script setup lang="ts">
/**
 * Wallet PIN Setup Page - Mobile OTP based
 */

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const router = useRouter()
const toast = useToast()
const { wallet, fetchWallet, setupPin } = useWallet()

const loading = ref(false)
const formData = ref({ pin: '', confirm_pin: '' })
const pinInputs = ref<HTMLInputElement[]>([])
const confirmPinInputs = ref<HTMLInputElement[]>([])

onMounted(async () => {
  await fetchWallet()

  if (wallet.value && !wallet.value.requires_pin_setup) {
    toast.add({ title: 'PIN Already Set', color: 'primary' })
    router.push('/wallet')
  }
})

const handlePinInput = (index: number, event: Event, isConfirm = false) => {
  const target = event.target as HTMLInputElement
  const value = target.value.replace(/\D/g, '')
  target.value = value

  const inputs = isConfirm ? confirmPinInputs.value : pinInputs.value
  if (value && index < 5) inputs[index + 1]?.focus()

  const pin = inputs.map(input => input.value).join('')
  if (isConfirm) formData.value.confirm_pin = pin
  else formData.value.pin = pin
}

const handleKeydown = (index: number, event: KeyboardEvent, isConfirm = false) => {
  const inputs = isConfirm ? confirmPinInputs.value : pinInputs.value
  if (event.key === 'Backspace' && !inputs[index].value && index > 0) {
    inputs[index - 1]?.focus()
  }
}

const handleSubmit = async () => {
  if (formData.value.pin.length !== 6) {
    toast.add({ title: 'Invalid PIN', description: 'PIN must be 6 digits', color: 'error' })
    return
  }

  if (formData.value.pin !== formData.value.confirm_pin) {
    toast.add({ title: 'PIN Mismatch', description: 'PINs do not match', color: 'error' })
    return
  }

  const weakPins = ['123456', '654321', '111111', '000000', '123123']
  if (weakPins.includes(formData.value.pin)) {
    toast.add({ title: 'Weak PIN', description: 'Choose a stronger PIN', color: 'error' })
    return
  }

  loading.value = true
  const result = await setupPin(formData.value)
  loading.value = false

  if (result.success) {
    toast.add({ title: 'PIN Set Successfully', color: 'success' })
    router.push('/wallet')
  } else {
    toast.add({ title: 'Failed', description: result.message, color: 'error' })
  }
}
</script>

<template>
  <div class="max-w-lg mx-auto">
    <div class="glass-card overflow-hidden">
      <div class="bg-gradient-to-r from-amber-600 to-amber-500 p-6 text-white">
        <h1 class="text-xl font-bold">
          Set Wallet PIN
        </h1>
        <p class="text-amber-100 text-sm">
          Secure your wallet with a 6-digit PIN
        </p>
      </div>

      <div class="p-6 space-y-6">
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Enter 6-digit PIN
          </label>
          <div class="flex justify-center gap-2">
            <input
              v-for="i in 6"
              :key="'pin-' + i"
              :ref="el => pinInputs[i-1] = el as HTMLInputElement"
              type="password"
              maxlength="1"
              inputmode="numeric"
              class="w-12 h-14 text-center text-2xl font-bold rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all"
              @input="handlePinInput(i-1, $event)"
              @keydown="handleKeydown(i-1, $event, false)"
            >
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Confirm PIN
          </label>
          <div class="flex justify-center gap-2">
            <input
              v-for="i in 6"
              :key="'confirm-' + i"
              :ref="el => confirmPinInputs[i-1] = el as HTMLInputElement"
              type="password"
              maxlength="1"
              inputmode="numeric"
              class="w-12 h-14 text-center text-2xl font-bold rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all"
              @input="handlePinInput(i-1, $event, true)"
              @keydown="handleKeydown(i-1, $event, true)"
            >
          </div>
        </div>

        <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4">
          <h4 class="text-sm font-medium text-slate-900 dark:text-white mb-2">
            PIN Guidelines
          </h4>
          <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-1">
            <li class="flex items-center gap-2">
              <UIcon
                name="i-lucide-check"
                class="w-3 h-3 text-green-500"
              />
              Must be exactly 6 digits
            </li>
            <li class="flex items-center gap-2">
              <UIcon
                name="i-lucide-x"
                class="w-3 h-3 text-red-500"
              />
              Avoid sequential numbers (123456)
            </li>
            <li class="flex items-center gap-2">
              <UIcon
                name="i-lucide-x"
                class="w-3 h-3 text-red-500"
              />
              Avoid repeated digits (111111)
            </li>
          </ul>
        </div>

        <UButton
          color="primary"
          size="lg"
          block
          :loading="loading"
          @click="handleSubmit"
        >
          Set PIN
        </UButton>
      </div>
    </div>
  </div>
</template>
