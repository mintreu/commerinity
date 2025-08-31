<template>
  <div class="min-h-screen p-4 flex justify-center bg-gray-100 dark:bg-gray-900">
    <div class="w-full max-w-3xl bg-white dark:bg-gray-800 rounded-lg shadow p-6">
      <h1 class="text-2xl font-semibold mb-4 text-gray-800 dark:text-white text-center">Complete KYC</h1>

      <form @submit.prevent="handleSubmit" class="space-y-6">

        <!-- Aadhaar -->
        <section class="bg-gray-50 dark:bg-gray-900 p-4 rounded space-y-3">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">Aadhaar</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">12 digits</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Aadhaar number</label>
              <input
                  v-model="form.aadhaar"
                  @input="clearError('aadhaar')"
                  maxlength="12"
                  inputmode="numeric"
                  placeholder="123412341234"
                  :class="inputClass(errors.aadhaar)"
                  class="w-full"
              />
              <p v-if="errors.aadhaar" class="mt-1 text-xs text-red-600">{{ errors.aadhaar }}</p>
            </div>

            <div class="flex flex-col gap-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload Aadhaar (jpg/png/pdf)</label>
              <input
                  type="file"
                  accept="image/*,application/pdf"
                  @change="onFileChange($event, 'aadhaar')"
                  class="block w-full text-sm text-gray-600 dark:text-gray-400"
              />
            </div>
          </div>

          <div class="mt-2 flex items-center gap-3">
            <div class="w-28 h-28 border rounded overflow-hidden bg-gray-50 dark:bg-gray-800 flex items-center justify-center">
              <img v-if="preview.aadhaar" :src="preview.aadhaar" class="max-h-full max-w-full object-contain" />
              <span v-else class="text-xs text-gray-400">No preview</span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300">We accept JPG/PNG/PDF. PDFs will be stored as-is.</p>
          </div>
        </section>

        <!-- PAN -->
        <section class="bg-gray-50 dark:bg-gray-900 p-4 rounded space-y-3">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">PAN</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">10 chars, e.g. ABCDE1234F</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">PAN number</label>
              <input
                  v-model="form.pan"
                  @input="form.pan = form.pan.toUpperCase(); clearError('pan')"
                  maxlength="10"
                  placeholder="ABCDE1234F"
                  :class="inputClass(errors.pan)"
                  class="w-full"
              />
              <p v-if="errors.pan" class="mt-1 text-xs text-red-600">{{ errors.pan }}</p>
            </div>

            <div class="flex flex-col gap-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload PAN (jpg/png/pdf)</label>
              <input
                  type="file"
                  accept="image/*,application/pdf"
                  @change="onFileChange($event, 'pan')"
                  class="block w-full text-sm text-gray-600 dark:text-gray-400"
              />
            </div>
          </div>

          <div class="mt-2 flex items-center gap-3">
            <div class="w-28 h-28 border rounded overflow-hidden bg-gray-50 dark:bg-gray-800 flex items-center justify-center">
              <img v-if="preview.pan" :src="preview.pan" class="max-h-full max-w-full object-contain" />
              <span v-else class="text-xs text-gray-400">No preview</span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300">Clear, legible images help fast verification.</p>
          </div>
        </section>

        <!-- GST -->
        <section class="bg-gray-50 dark:bg-gray-900 p-4 rounded space-y-3">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">GST (optional)</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">Only required if you have GST</p>
          </div>

          <div class="flex items-center gap-4">
            <label class="inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="form.hasTax" class="mr-2 h-4 w-4 text-blue-600 border-gray-300 rounded" />
              <span class="text-gray-700 dark:text-gray-200">I have GST</span>
            </label>
          </div>

          <div v-if="form.hasTax" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">GST number</label>
              <input
                  v-model="form.gst"
                  @input="form.gst = form.gst.toUpperCase(); clearError('gst')"
                  maxlength="15"
                  placeholder="15-character GSTIN"
                  :class="inputClass(errors.gst)"
                  class="w-full"
              />
              <p v-if="errors.gst" class="mt-1 text-xs text-red-600">{{ errors.gst }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload GST (jpg/png/pdf)</label>
              <input
                  type="file"
                  accept="image/*,application/pdf"
                  @change="onFileChange($event, 'gst')"
                  class="block w-full text-sm text-gray-600 dark:text-gray-400"
              />
            </div>
          </div>
        </section>

        <!-- Utility bills (optional) -->
        <section class="bg-gray-50 dark:bg-gray-900 p-4 rounded space-y-3">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">Utility Bills (optional)</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">Provide one or more — no files required</p>
          </div>

          <div class="space-y-3">
            <template v-for="(row, idx) in form.utility_bills" :key="row.id">
              <div class="grid grid-cols-1 md:grid-cols-6 gap-2 items-center">
                <div class="md:col-span-2">
                  <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Type</label>
                  <select v-model="row.type" @change="onUtilityTypeChange(idx)" :class="inputClass(errors[`utility_${idx}`])" class="w-full">
                    <option value="">Select type</option>
                    <option v-for="opt in availableUtilityOptions(idx)" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                  </select>
                </div>

                <div class="md:col-span-3">
                  <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Identifier / account no.</label>
                  <input v-model="row.identifier" type="text" placeholder="Enter bill / account number" :class="inputClass(errors[`utility_${idx}`])" />
                </div>

                <div class="md:col-span-1 flex gap-2">
                  <button type="button" @click="removeUtility(idx)" class="px-3 py-2 rounded bg-red-600 text-white hover:bg-red-700">Remove</button>
                </div>
              </div>
            </template>

            <div>
              <button type="button" @click="addUtility" :disabled="form.utility_bills.length >= utilityOptions.length" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                Add Utility
              </button>
              <p v-if="utilityAddHint" class="text-xs text-gray-500 mt-2">{{ utilityAddHint }}</p>
            </div>
          </div>
        </section>

        <!-- Submit -->
        <div class="text-center">
          <button type="submit" :disabled="processing" class="px-6 py-2 rounded bg-green-600 text-white hover:bg-green-700 disabled:opacity-50">
            <span v-if="processing">Submitting...</span>
            <span v-else>Submit KYC</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useSanctumFetch, useRuntimeConfig, useToast } from '#imports'
import { v4 as uuidv4 } from 'uuid' // small unique id for repeat rows

// page meta
definePageMeta({ layout: 'dashboard' })

const toast = useToast()
const config = useRuntimeConfig()
const processing = ref(false)

// utility options master list
const utilityOptions = [
  { value: 'electricity', label: 'Electricity Bill' },
  { value: 'gas', label: 'Gas Bill' },
  { value: 'telephone', label: 'Telephone / Landline' },
  { value: 'water', label: 'Water Bill' },
  { value: 'other', label: 'Other' }
]

// reactive form
const form = reactive({
  aadhaar: '',
  pan: '',
  aadhaarFile: /** @type {File|null} */ (null),
  panFile: /** @type {File|null} */ (null),
  aadhaarImageUrl: '',
  panImageUrl: '',
  hasTax: false,
  gst: '',
  gstFile: /** @type {File|null} */ (null),
  utility_bills: [] as Array<{ id: string, type: string, identifier: string }>,
})

// local preview URLs for images
const preview = reactive({
  aadhaar: '',
  pan: '',
  gst: ''
})

// inline errors object
const errors = reactive<Record<string, string>>({})

// helper: clear error for a field
function clearError(field: string) {
  errors[field] = ''
}

// available utility options for a specific row (prevents duplicates)
function availableUtilityOptions(index: number) {
  const used = form.utility_bills.map((b, i) => (i === index ? null : b.type)).filter(Boolean)
  return utilityOptions.filter(o => !used.includes(o.value))
}

// hint text for add button
const utilityAddHint = computed(() => {
  const left = utilityOptions.length - form.utility_bills.length
  if (left <= 0) return 'All utility types added.'
  return `${left} types left to add.`
})

// add / remove utility rows
function addUtility() {
  // don't add duplicates
  if (form.utility_bills.length >= utilityOptions.length) return
  form.utility_bills.push({ id: uuidv4(), type: '', identifier: '' })
}

function removeUtility(idx: number) {
  form.utility_bills.splice(idx, 1)
  // clear related error
  delete errors[`utility_${idx}`]
}

// called when a type changes to ensure uniqueness
function onUtilityTypeChange(idx: number) {
  // if duplicate slipped in, remove duplicate selection
  const seen = new Set<string>()
  for (let i = 0; i < form.utility_bills.length; i++) {
    const t = form.utility_bills[i].type
    if (!t) continue
    if (seen.has(t) && i !== idx) {
      form.utility_bills[i].type = ''
      form.utility_bills[i].identifier = ''
    } else {
      seen.add(t)
    }
  }
  clearError(`utility_${idx}`)
}

/* ---------- file handling & previews ---------- */
function onFileChange(e: Event, type: 'aadhaar' | 'pan' | 'gst') {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0] ?? null
  if (type === 'aadhaar') {
    form.aadhaarFile = file
    preview.aadhaar = file && file.type.startsWith('image/') ? URL.createObjectURL(file) : (file ? '' : '')
  } else if (type === 'pan') {
    form.panFile = file
    preview.pan = file && file.type.startsWith('image/') ? URL.createObjectURL(file) : (file ? '' : '')
  } else {
    form.gstFile = file
    preview.gst = file && file.type.startsWith('image/') ? URL.createObjectURL(file) : (file ? '' : '')
  }
  // clear possible server/form errors
  clearError(type)
}

/* ---------- load existing data ---------- */
async function loadExisting() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/account/kyc`)
    const k = res?.data ?? {}
    // Aadhaar may include dashes; strip them
    form.aadhaar = (k.aadhaar || '').replace(/\D/g, '')
    form.pan = (k.pan || '').toUpperCase()
    if (k.aadhaarImage) preview.aadhaar = k.aadhaarImage
    if (k.panImage) preview.pan = k.panImage
    form.hasTax = !!k.has_tax
    form.gst = (k.gst || '').toUpperCase()
    if (Array.isArray(k.utility_bills)) {
      form.utility_bills = k.utility_bills.map((b: any) => ({ id: uuidv4(), type: b.type || '', identifier: b.value || '' }))
    }
  } catch (err) {
    // non-fatal
    console.error('loadExisting KYC error', err)
  }
}
onMounted(loadExisting)

/* ---------- validations ---------- */
function validateAadhaar() {
  const v = (form.aadhaar || '').trim()
  if (!v) {
    errors.aadhaar = 'Aadhaar is required.'
    return false
  }
  if (!/^\d{12}$/.test(v)) {
    errors.aadhaar = 'Aadhaar must be 12 digits.'
    return false
  }
  errors.aadhaar = ''
  return true
}

function validatePan() {
  const v = (form.pan || '').toUpperCase().trim()
  if (!v) {
    errors.pan = 'PAN is required.'
    return false
  }
  if (!/^[A-Z]{5}[0-9]{4}[A-Z]$/.test(v)) {
    errors.pan = 'PAN format invalid (e.g. ABCDE1234F).'
    return false
  }
  errors.pan = ''
  return true
}

function validateGst() {
  if (!form.hasTax) {
    errors.gst = ''
    return true
  }
  const v = (form.gst || '').toUpperCase().trim()
  if (!v) {
    errors.gst = 'GST number is required when you have GST.'
    return false
  }
  if (!/^[0-9A-Z]{15}$/.test(v)) {
    errors.gst = 'GST must be 15 alphanumeric characters.'
    return false
  }
  errors.gst = ''
  return true
}

function validateUtilities() {
  let ok = true
  // ensure no duplicate types and each row has type + identifier if present
  const used = new Set<string>()
  form.utility_bills.forEach((row, i) => {
    if (!row.type && !row.identifier) {
      // if both empty skip (allow empty row?)
      errors[`utility_${i}`] = ''
      return
    }
    if (!row.type) {
      errors[`utility_${i}`] = 'Select utility type or remove this row.'
      ok = false
      return
    }
    if (!row.identifier || !row.identifier.trim()) {
      errors[`utility_${i}`] = 'Enter identifier / account number.'
      ok = false
      return
    }
    if (used.has(row.type)) {
      errors[`utility_${i}`] = 'This type is already chosen.'
      ok = false
      return
    }
    used.add(row.type)
    errors[`utility_${i}`] = ''
  })
  return ok
}

function validateAll() {
  const a = validateAadhaar()
  const p = validatePan()
  const g = validateGst()
  const u = validateUtilities()
  return a && p && g && u
}

/* ---------- UI helpers ---------- */
function inputClass(err = '') {
  return `w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 ${err ? 'border-red-600 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'} dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600`
}

/* ---------- submit ---------- */
async function handleSubmit() {
  // client-side validation
  if (!validateAll()) {
    toast.error({ title: 'Validation', message: 'Please fix highlighted errors.' })
    return
  }

  processing.value = true
  try {
    const payload = new FormData()
    // basic fields
    payload.append('aadhaar', form.aadhaar)
    payload.append('pan', form.pan)
    payload.append('has_tax', form.hasTax ? '1' : '0')
    if (form.hasTax) payload.append('gst', form.gst)

    // files (if present)
    if (form.aadhaarFile) payload.append('aadhaar_file', form.aadhaarFile)
    if (form.panFile) payload.append('pan_file', form.panFile)
    if (form.gstFile) payload.append('gst_file', form.gstFile)

    // utility bills — send as JSON string (array of {type, identifier})
    const utilityPayload = form.utility_bills
        .filter(b => b.type && b.identifier && b.identifier.trim())
        .map(b => ({ type: b.type, value: b.identifier.trim() }))

    payload.append('utility_bills', JSON.stringify(utilityPayload))

    // send via useSanctumFetch, multipart/form-data handled by browser
    const url = `${config.public.apiBase}/user/kyc`
    const res = await useSanctumFetch(url, { method: 'POST', body: payload })

    // expected server response pattern: { success: true, ... }
    if (res?.data?.success || res?.success) {
      toast.success({ title: 'Submitted', message: 'KYC submitted successfully.' })
      // optionally refresh data
      await loadExisting()
    } else {
      // server may return validation errors; map them
      if (res?.data?.errors) {
        Object.entries(res.data.errors).forEach(([k, v]) => {
          // map nested names
          errors[k] = Array.isArray(v) ? (v as any).join(', ') : (v as any)
        })
        toast.error({ title: 'Error', message: 'Server validation failed. See highlighted fields.' })
      } else {
        toast.error({ title: 'Error', message: 'Submission failed. Please try again.' })
      }
    }
  } catch (err: any) {
    console.error('submit error', err)
    toast.error({ title: 'Error', message: err?.message || 'Submission error' })
  } finally {
    processing.value = false
  }
}
</script>

<style scoped>
/* small niceties */
@media (max-width: 640px) {
  .max-w-3xl { max-width: 100%; }
}
</style>
