<script setup lang="ts">
/**
 * Reusable Checkout Button with Modal
 * Fully configurable for job applications, subscriptions, team payments, etc.
 */

interface Props {
  // Button Display Configuration
  label: string
  icon?: string
  color?: 'primary' | 'success' | 'warning' | 'error' | 'gray'
  size?: 'xs' | 'sm' | 'md' | 'lg'
  variant?: 'solid' | 'outline' | 'ghost'
  disabled?: boolean
  loading?: boolean
  block?: boolean

  // Payment Configuration
  modalTitle: string
  amount: number
  amountFormatted: string
  description?: string
  checkoutEndpoint: string
  checkoutPayload?: Record<string, unknown>
}

const props = withDefaults(defineProps<Props>(), {
  color: 'primary',
  size: 'sm',
  variant: 'solid',
  disabled: false,
  loading: false,
  block: false,
  icon: 'i-lucide-wallet'
})

const emit = defineEmits(['success', 'click'])

const open = ref(false)

function handleClick() {
  emit('click')
  if (!props.disabled && !props.loading) {
    open.value = true
  }
}
</script>

<template>
  <div>
    <UButton
      :color="color"
      :size="size"
      :variant="variant"
      :disabled="disabled"
      :loading="loading"
      :block="block"
      @click="handleClick"
    >
      <UIcon v-if="icon" :name="icon" class="w-4 h-4 mr-1" />
      {{ label }}
    </UButton>

    <CheckoutModal
      v-model:open="open"
      :title="modalTitle"
      :amount="amount"
      :amount-formatted="amountFormatted"
      :description="description"
      :checkout-endpoint="checkoutEndpoint"
      :checkout-payload="checkoutPayload"
      @success="$emit('success')"
    />
  </div>
</template>
