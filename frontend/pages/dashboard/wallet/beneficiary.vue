<script setup lang="ts">
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRuntimeConfig, useToast, useSanctumFetch } from '#imports'
import GlobalLoader from '~/components/GlobalLoader.vue'

definePageMeta({ layout: 'dashboard' })

/* Core */
const config = useRuntimeConfig()
const toast = useToast()
const isLoading = useState('pageLoading', () => true)
const processing = ref(false)

/* Types */
type Beneficiary = {
  uuid: string
  type: 'savings' | 'current' | 'upi'
  bank_name?: string | null
  bank_branch?: string | null
  account_name?: string | null
  account_number?: string | null
  ifsc?: string | null
  upi_handle?: string | null
  default?: boolean
  status?: string
}

/* State */
const list = ref<Beneficiary[]>([])
const page = ref(1)
const hasMore = ref(true)
const perPage = ref(10)

/* UI State */
const showForm = ref(false)
/* Edit mode disabled globally */
const isEdit = ref(false)
const editingUuid = ref<string | null>(null)

const form = reactive<Partial<Beneficiary>>({
  type: 'savings',
  bank_name: '',
  bank_branch: '',
  account_name: '',
  account_number: '',
  ifsc: '',
  upi_handle: '',
  default: false,
})
const errors = reactive<Record<string, string>>({})
const confirmDeleteOpen = ref(false)
const deleteTarget = ref<Beneficiary | null>(null)

/* Helpers */
const typeOptions = [
  { value: 'savings', label: 'Savings (Bank)' },
  { value: 'current', label: 'Current (Bank)' },
  { value: 'upi', label: 'UPI' },
]
const isBank = computed(() => form.type === 'savings' || form.type === 'current')

function resetForm() {
  form.type = 'savings'
  form.bank_name = ''
  form.bank_branch = ''
  form.account_name = ''
  form.account_number = ''
  form.ifsc = ''
  form.upi_handle = ''
  form.default = false
  Object.keys(errors).forEach(k => delete errors[k])
  editingUuid.value = null
  isEdit.value = false
}

/* Open create form */
function openCreateForm() {
  resetForm()
  showForm.value = true
}

/* Parse API payload shape safely */
function parsePagePayload(res: any) {
  const data = res?.data
  if (Array.isArray(data)) return { items: data as Beneficiary[], next: null }
  const items = Array.isArray(data?.data) ? (data.data as Beneficiary[]) : (Array.isArray(data) ? (data as Beneficiary[]) : [])
  const next = data?.next_page_url ?? null
  return { items, next }
}

/* Loaders */
async function loadBeneficiaries(reset = false) {
  try {
    if (reset) {
      list.value = []
      page.value = 1
      hasMore.value = true
    }
    if (!hasMore.value || processing.value) return
    const url = new URL(`${config.public.apiBase}/beneficiaries`)
    url.searchParams.set('page', String(page.value))
    url.searchParams.set('per_page', String(perPage.value))
    processing.value = true
    const res = await useSanctumFetch(url.toString())
    const { items, next } = parsePagePayload(res)
    list.value.push(...items)
    hasMore.value = !!next
    page.value++
  } catch (e: any) {
    toast.error({ title: 'Beneficiaries', message: e?.data?.message || 'Failed to fetch beneficiaries.' })
  } finally {
    processing.value = false
  }
}

/* Actions: Create only (Edit disabled) */
function validate() {
  Object.keys(errors).forEach(k => delete errors[k])
  if (!form.type) errors.type = 'Type is required.'
  if (isBank.value) {
    if (!form.account_name) errors.account_name = 'Account holder name is required.'
    if (!form.account_number) errors.account_number = 'Account number is required.'
    if (!form.ifsc) errors.ifsc = 'IFSC is required.'
    if (!form.bank_name) errors.bank_name = 'Bank name is required.'
  } else {
    if (!form.upi_handle) errors.upi_handle = 'UPI handle is required.'
  }
  return Object.keys(errors).length === 0
}

async function submit() {
  if (!validate()) {
    toast.error({ title: 'Validation', message: 'Please fix highlighted errors.' })
    return
  }
  processing.value = true
  try {
    const payload: Record<string, any> = {
      type: form.type,
      default: !!form.default,
    }
    if (isBank.value) {
      payload.bank_name = form.bank_name
      payload.bank_branch = form.bank_branch
      payload.account_name = form.account_name
      payload.account_number = form.account_number
      payload.ifsc = form.ifsc
    } else {
      payload.upi_handle = form.upi_handle
    }

    // Always create, edit is disabled
    const res = await useSanctumFetch(`${config.public.apiBase}/beneficiaries`, {
      method: 'POST',
      body: payload,
    })
    const created = res?.data || null
    if (created) {
      list.value.unshift(created)
    } else {
      await loadBeneficiaries(true)
    }
    toast.success({ title: 'Beneficiaries', message: 'Beneficiary added.' })

    showForm.value = false
    resetForm()
  } catch (e: any) {
    const serverErrors = e?.data?.errors ?? e?.errors
    if (serverErrors && typeof serverErrors === 'object') {
      Object.entries(serverErrors).forEach(([k, v]) => {
        errors[k] = Array.isArray(v) ? (v as any).join(', ') : (v as any)
      })
    }
    toast.error({ title: 'Error', message: e?.data?.message || 'Operation failed.' })
  } finally {
    processing.value = false
  }
}

/* Edit disabled - keep function but no-op opening of edit (hidden in UI) */
function startEdit(_b: Beneficiary) {
  // Intentionally disabled
  toast.info?.({ title: 'Beneficiaries', message: 'Edit is temporarily disabled.' })
}

/* Delete */
function confirmDelete(b: Beneficiary) {
  deleteTarget.value = b
  confirmDeleteOpen.value = true
}
async function doDelete() {
  if (!deleteTarget.value) return
  processing.value = true
  try {
    await useSanctumFetch(`${config.public.apiBase}/beneficiaries/${deleteTarget.value.uuid}`, { method: 'DELETE' })
    list.value = list.value.filter(x => x.uuid !== deleteTarget.value!.uuid)
    toast.success({ title: 'Beneficiaries', message: 'Beneficiary deleted.' })
    confirmDeleteOpen.value = false
    deleteTarget.value = null
  } catch (e: any) {
    toast.error({ title: 'Error', message: e?.data?.message || 'Failed to delete.' })
  } finally {
    processing.value = false
  }
}

/* Make default */
async function makeDefault(b: Beneficiary) {
  processing.value = true
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/beneficiaries/${b.uuid}/default`, { method: 'POST' })
    const updated = res?.data || null
    list.value = list.value.map(item => ({
      ...item,
      default: item.uuid === b.uuid,
    }))
    if (updated) {
      const idx = list.value.findIndex(x => x.uuid === updated.uuid)
      if (idx !== -1) list.value[idx] = { ...list.value[idx], ...updated, default: true }
    }
    toast.success({ title: 'Beneficiaries', message: 'Default account set.' })
  } catch (e: any) {
    toast.error({ title: 'Error', message: e?.data?.message || 'Failed to set default.' })
  } finally {
    processing.value = false
  }
}

/* ESC to close modals */
function handleEsc(e: KeyboardEvent) {
  if (e.key === 'Escape') {
    if (showForm.value) showForm.value = false
    if (confirmDeleteOpen.value) confirmDeleteOpen.value = false
  }
}
onMounted(async () => {
  window.addEventListener('keydown', handleEsc)
  isLoading.value = true
  await loadBeneficiaries(true)
  isLoading.value = false
})
onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleEsc)
})

/* UI helpers */
function inputClass(err?: string) {
  return `w-full rounded-lg border px-3 py-2 dark:bg-gray-800 dark:border-gray-700 ${err ? 'border-red-600' : 'border-gray-300'}`
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 dark:from-gray-900 dark:to-gray-950 text-gray-800 dark:text-gray-100">
    <GlobalLoader v-if="isLoading" />

    <div v-else class="mx-auto w-full max-w-7xl px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-8 space-y-6">
      <!-- Header -->
      <div class="bg-white/95 dark:bg-gray-900/90 rounded-2xl p-5 md:p-6 ring-1 ring-gray-200/70 dark:ring-gray-800/70 shadow-xl flex items-center justify-between">
        <div>
          <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Wallet</p>
          <h1 class="text-2xl font-semibold">Beneficiaries</h1>
          <p class="text-sm text-gray-500">Add bank or UPI accounts and choose a default for withdrawals.</p>
        </div>
        <button
            class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow disabled:opacity-50"
            :disabled="processing"
            @click="openCreateForm"
        >
          Add Beneficiary
        </button>
      </div>

      <!-- List -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl ring-1 ring-gray-200/70 dark:ring-gray-800/70 shadow p-4 md:p-6">
        <div v-if="list.length === 0" class="text-sm text-gray-600 dark:text-gray-400 py-8 text-center">
          No beneficiaries added yet.
        </div>

        <div v-else class="space-y-3">
          <div
              v-for="b in list"
              :key="b.uuid"
              class="p-4 rounded-xl ring-1 ring-gray-200 dark:ring-gray-800 bg-white dark:bg-gray-900 shadow flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
          >
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span class="text-sm font-semibold capitalize">{{ b.type }}</span>
                <span v-if="b.default" class="text-[11px] px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Default</span>
              </div>
              <div v-if="b.type === 'upi'" class="text-sm text-gray-700 dark:text-gray-300">
                UPI: <span class="font-medium">{{ b.upi_handle }}</span>
              </div>
              <div v-else class="text-sm text-gray-700 dark:text-gray-300">
                <div>Bank: <span class="font-medium">{{ b.bank_name || '—' }}</span></div>
                <div>Account: <span class="font-medium">{{ b.account_name || '—' }}</span> • <span class="font-mono">{{ b.account_number ? `•••• ${String(b.account_number).slice(-4)}` : '—' }}</span></div>
                <div>IFSC: <span class="font-mono">{{ b.ifsc || '—' }}</span></div>
              </div>
            </div>

            <div class="flex flex-wrap gap-2">
              <!-- Edit button hidden as requested -->
              <!-- <button class="px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700"
                      :disabled="processing"
                      @click="startEdit(b)">
                Edit
              </button> -->
              <button class="px-3 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700"
                      :disabled="processing || !!b.default"
                      title="Set as default"
                      @click="makeDefault(b)">
                Set Default
              </button>
              <button class="px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700"
                      :disabled="processing || !!b.default"
                      title="Delete"
                      @click="confirmDelete(b)">
                Delete
              </button>
            </div>
          </div>

          <div v-if="hasMore" class="text-center pt-2">
            <button
                class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700"
                :disabled="processing"
                @click="loadBeneficiaries()"
            >
              Load more
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Drawer/Modal Form -->
    <teleport to="body">
      <transition enter-active-class="duration-150 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="duration-100 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="showForm" class="fixed inset-0 z-[9999] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4" @click.self="showForm = false">
          <div class="w-full sm:max-w-lg bg-white dark:bg-gray-900 dark:text-white rounded-t-2xl sm:rounded-2xl ring-1 ring-gray-200 dark:ring-gray-800 shadow-2xl p-5">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-lg font-semibold">Add Beneficiary</h3>
              <button class="text-gray-400 hover:text-gray-200" @click="showForm = false">✕</button>
            </div>

            <div class="grid grid-cols-1 gap-3">
              <div>
                <label class="block text-sm mb-1">Type</label>
                <select
                    v-model="form.type"
                    :class="inputClass(errors.type)"
                    class="w-full"
                >
                  <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
                <p v-if="errors.type" class="mt-1 text-xs text-red-600">{{ errors.type }}</p>
              </div>

              <!-- Bank fields -->
              <template v-if="isBank">
                <div>
                  <label class="block text-sm mb-1">Account Holder Name</label>
                  <input v-model="form.account_name" :class="inputClass(errors.account_name)" />
                  <p v-if="errors.account_name" class="mt-1 text-xs text-red-600">{{ errors.account_name }}</p>
                </div>
                <div>
                  <label class="block text-sm mb-1">Account Number</label>
                  <input v-model="form.account_number" :class="inputClass(errors.account_number)" />
                  <p v-if="errors.account_number" class="mt-1 text-xs text-red-600">{{ errors.account_number }}</p>
                </div>
                <div>
                  <label class="block text-sm mb-1">IFSC</label>
                  <input v-model="form.ifsc" :class="inputClass(errors.ifsc)" />
                  <p v-if="errors.ifsc" class="mt-1 text-xs text-red-600">{{ errors.ifsc }}</p>
                </div>
                <div>
                  <label class="block text-sm mb-1">Bank Name</label>
                  <input v-model="form.bank_name" :class="inputClass(errors.bank_name)" />
                  <p v-if="errors.bank_name" class="mt-1 text-xs text-red-600">{{ errors.bank_name }}</p>
                </div>
                <div>
                  <label class="block text-sm mb-1">Bank Branch (optional)</label>
                  <input v-model="form.bank_branch" :class="inputClass()" />
                </div>
              </template>

              <!-- UPI field -->
              <template v-else>
                <div>
                  <label class="block text-sm mb-1">UPI Handle</label>
                  <input v-model="form.upi_handle" placeholder="name@bank" :class="inputClass(errors.upi_handle)" />
                  <p v-if="errors.upi_handle" class="mt-1 text-xs text-red-600">{{ errors.upi_handle }}</p>
                </div>
              </template>

              <div class="flex items-center gap-2 mt-1">
                <input id="set-default" type="checkbox" v-model="form.default" class="h-4 w-4 rounded border-gray-300 text-indigo-600" />
                <label for="set-default" class="text-sm">Set as default</label>
              </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-2">
              <button class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700"
                      :disabled="processing"
                      @click="showForm = false">
                Cancel
              </button>
              <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow disabled:opacity-50"
                      :disabled="processing"
                      @click="submit">
                {{ processing ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </div>
        </div>
      </transition>
    </teleport>

    <!-- Delete Confirm -->
    <teleport to="body">
      <transition enter-active-class="duration-150 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="duration-100 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="confirmDeleteOpen" class="fixed inset-0 z-[9999] bg-black/40 backdrop-blur-sm grid place-items-center p-4" @click.self="confirmDeleteOpen = false">
          <div class="w-full max-w-sm bg-white dark:bg-gray-900 rounded-2xl p-5 ring-1 ring-gray-200 dark:ring-gray-800 shadow-2xl">
            <h3 class="text-lg font-semibold">Delete Beneficiary</h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">This action cannot be undone.</p>
            <div class="mt-4 flex items-center justify-end gap-2">
              <button class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700"
                      :disabled="processing"
                      @click="confirmDeleteOpen = false">
                Cancel
              </button>
              <button class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 disabled:opacity-50"
                      :disabled="processing || (deleteTarget && deleteTarget.default)"
                      @click="doDelete">
                {{ processing ? 'Deleting...' : 'Delete' }}
              </button>
            </div>
          </div>
        </div>
      </transition>
    </teleport>
  </div>
</template>
