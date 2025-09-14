<script setup lang="ts">
import { reactive, watch, ref, onMounted } from "vue"
import { useSanctumFetch, useRuntimeConfig } from "#imports"

const props = defineProps<{ modelValue?: Record<string, any> }>()
const emit = defineEmits<{ (e: "update:modelValue", value: Record<string, any>): void }>()

const config = useRuntimeConfig()

// Form state
const guestForm = reactive({
  customer_name: "",
  customer_email: "",
  customer_mobile: "",
  address_1: "",
  landmark: "",
  state: "",
  city: "",
  district: "",
  postal_code: "",
  block_id: "",
})

// Lists
const stateList = ref<any[]>([])
const districtList = ref<string[]>([])
const blockList = ref<Array<{ name: string; url: string }>>([])

// Prefill
if (props.modelValue) {
  Object.assign(guestForm, props.modelValue)
}

// Emit changes
watch(guestForm, (val) => {
  emit("update:modelValue", { ...val })
}, { deep: true, immediate: true })

// Fetch states
async function fetchStates() {
  const res = await useSanctumFetch(`${config.public.apiBase}/geo/states/IN`)
  stateList.value = res.data
}

// Fetch blocks & districts
async function fetchBlocks() {
  if (!guestForm.state) return
  const res = await useSanctumFetch(`${config.public.apiBase}/geo/state/${guestForm.state}`)
  const blocks = res.data.blocks || []

  blockList.value = blocks.map((b: any) => {
    const name = b.name || b.block_name || ""
    const url = b.url || b.id || name
    return { name, url: String(url) }
  })

  districtList.value = [...new Set(blocks.map((b: any) => b.district).filter(Boolean))]
}

onMounted(() => {
  fetchStates()
})
</script>

<template>
  <div class="w-full flex flex-col gap-4 text-sm">
    <!-- Customer Info -->
    <div class="space-y-2">
      <input v-model="guestForm.customer_name" type="text" placeholder="Full Name" class="input w-full" required />
      <input v-model="guestForm.customer_email" type="email" placeholder="Email" class="input w-full" required />
      <input v-model="guestForm.customer_mobile" type="tel" placeholder="Mobile" class="input w-full" required />
    </div>

    <!-- Delivery Address -->
    <div class="space-y-2">
      <h2 class="font-semibold text-base">Delivery Address</h2>

      <input v-model="guestForm.address_1" type="text" placeholder="Address Line" class="input w-full" required />
      <input v-model="guestForm.landmark" type="text" placeholder="Landmark" class="input w-full" />

      <!-- State -->
      <select v-model="guestForm.state" @change="fetchBlocks" class="input w-full" required>
        <option disabled value="">Select State</option>
        <option v-for="state in stateList" :key="state.code" :value="state.code">{{ state.name }}</option>
      </select>

      <!-- District -->
      <select v-model="guestForm.city" class="input w-full" required>
        <option disabled value="">Select District</option>
        <option v-for="d in districtList" :key="d" :value="d">{{ d }}</option>
      </select>

      <!-- Block -->
      <select v-model="guestForm.block_id" class="input w-full">
        <option disabled value="">Select Block</option>
        <option v-for="block in blockList" :key="block.url" :value="block.url">{{ block.name }}</option>
      </select>

      <input v-model="guestForm.postal_code" type="text" placeholder="Postal Code" class="input w-full" required />
    </div>
  </div>
</template>

<style scoped>
.input {
  @apply border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100;
}
</style>
