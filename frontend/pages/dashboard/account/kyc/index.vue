<template>
  <!-- Global Loader until data populated -->
  <GlobalLoader v-if="isLoading" />

  <!-- Page Wrapper -->
  <div v-else class="min-h-screen w-full bg-gradient-to-b from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-950">
    <div class="mx-auto w-full max-w-7xl px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-10">
      <!-- Card Shell -->
      <div class="w-full bg-white/95 dark:bg-gray-900/90 backdrop-blur rounded-xl md:rounded-2xl shadow-xl ring-1 ring-gray-200/70 dark:ring-gray-700/60 transition-all duration-300">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-5 md:p-6 lg:p-8 border-b border-gray-100 dark:border-gray-800">
          <div>
            <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">KYC</p>
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white">
              {{ hasExistingKyc ? 'Update KYC Record' : 'Create KYC Record' }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Aadhaar and PAN with clear images are required to complete verification. GST and Utilities are optional and can be added anytime.
            </p>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2">
            <button
                v-if="!isEditing"
                type="button"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white shadow hover:shadow-lg hover:bg-blue-700 active:scale-[0.98] transition"
                @click="enableEdit"
                aria-label="Edit KYC"
            >
              <span class="i-lucide-pencil w-4 h-4"></span>
              Edit
            </button>

            <template v-else>
              <button
                  type="button"
                  class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-700 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 shadow-sm hover:shadow transition"
                  @click="cancelEdit"
                  aria-label="Cancel editing"
                  :disabled="processing"
              >
                <span class="i-lucide-undo-2 w-4 h-4"></span>
                Cancel
              </button>
              <button
                  type="button"
                  class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white shadow hover:shadow-lg hover:bg-green-700 disabled:opacity-50 active:scale-[0.98] transition"
                  @click="submitIfValid"
                  :disabled="processing"
                  aria-label="Save KYC"
              >
                <span v-if="processing" class="i-lucide-loader-2 w-4 h-4 animate-spin"></span>
                <span v-else class="i-lucide-save w-4 h-4"></span>
                <span>{{ processing ? 'Saving...' : 'Save' }}</span>
              </button>
            </template>
          </div>
        </div>

        <!-- Single <form> containing all tabs and fields -->
        <form @submit.prevent="handleSubmit" class="px-5 md:px-6 lg:px-8 py-5 space-y-6" enctype="multipart/form-data">
          <!-- Tabs -->
          <div class="flex flex-wrap gap-2" role="tablist" aria-label="KYC sections">
            <button
                v-for="t in tabs"
                :key="t.key"
                role="tab"
                type="button"
                class="px-3 py-2 rounded-lg text-sm font-medium transition ring-1 ring-inset"
                :class="activeTab === t.key
                ? 'bg-blue-600 text-white ring-blue-600'
                : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 ring-gray-200 dark:ring-gray-700'"
                :aria-selected="activeTab === t.key"
                :aria-controls="`panel-${t.key}`"
                :id="`tab-${t.key}`"
                @click="activeTab = t.key"
            >
              {{ t.label }}
            </button>
          </div>

          <!-- Tab descriptions -->
          <p v-if="activeTab === 'aadhaar'" class="text-sm text-gray-600 dark:text-gray-400">
            Enter Aadhaar number and upload a clear image or PDF. Ensure the number has 12 digits without spaces.
          </p>
          <p v-else-if="activeTab === 'pan'" class="text-sm text-gray-600 dark:text-gray-400">
            Enter PAN in uppercase and upload a clear image or PDF. Make sure the text is legible.
          </p>
          <p v-else-if="activeTab === 'gst'" class="text-sm text-gray-600 dark:text-gray-400">
            Toggle GST. If enabled, provide the 15-character GSTIN and an optional GST document.
          </p>
          <p v-else-if="activeTab === 'utilities'" class="text-sm text-gray-600 dark:text-gray-400">
            Add one or more utility identifiers (no file needed). Each type can be added once.
          </p>

          <!-- Aadhaar Tab -->
          <section
              v-show="activeTab === 'aadhaar'"
              role="tabpanel"
              :id="'panel-aadhaar'"
              aria-labelledby="tab-aadhaar"
              class="section-card"
          >
            <header class="section-head">
              <div>
                <h2 class="section-title">Aadhaar</h2>
                <p class="section-meta">Required · Government-issued ID</p>
              </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
              <div>
                <label class="label mb-1">Aadhaar number</label>
                <input
                    v-model="form.aadhaar"
                    @input="clearError('aadhaar')"
                    maxlength="12"
                    inputmode="numeric"
                    placeholder="Enter 12-digit Aadhaar (e.g. 123412341234)"
                    :class="inputClass(errors.aadhaar)"
                    class="w-full"
                    :disabled="!isEditing"
                />
                <p class="mt-1 text-xs text-gray-500">Do not include spaces or dashes.</p>
                <p v-if="errors.aadhaar" class="mt-1 text-xs text-red-600">{{ errors.aadhaar }}</p>
              </div>

              <div>
                <label class="label mb-1">Aadhaar image (JPG/PNG/PDF)</label>

                <!-- Dropzone + Progress + Preview -->
                <div
                    class="uploader"
                    :class="{ 'uploader--drag': aadhaarDragOver }"
                    @dragover.prevent="onDragOver('aadhaar')"
                    @dragleave.prevent="onDragLeave('aadhaar')"
                    @drop.prevent="onDrop($event, 'aadhaar')"
                >
                  <input
                      class="sr-only"
                      id="aadhaar_file"
                      name="aadhaar_file"
                      type="file"
                      accept="image/*,application/pdf"
                      :disabled="!isEditing"
                      @change="onFileChange($event, 'aadhaar')"
                  />
                  <label for="aadhaar_file" class="uploader-label">
                    <span class="block text-sm">Drag & drop or click to browse</span>
                    <span class="block text-xs text-gray-500">JPG/PNG/PDF up to 10MB</span>
                  </label>

                  <!-- Progress -->
                  <div v-if="uploadProgress.aadhaar > 0" class="progress">
                    <div class="progress-bar" :style="{ width: uploadProgress.aadhaar + '%' }"></div>
                  </div>

                  <!-- Big Preview -->
                  <div class="preview-wide mt-3">
                    <img v-if="preview.aadhaar" :src="preview.aadhaar" class="preview-wide-img" />
                    <span v-else class="preview-wide-empty">No preview</span>
                  </div>
                </div>

                <p class="mt-1 text-xs text-gray-500">Tip: Use a bright, flat surface to avoid glare.</p>
              </div>
            </div>
          </section>

          <!-- PAN Tab -->
          <section
              v-show="activeTab === 'pan'"
              role="tabpanel"
              :id="'panel-pan'"
              aria-labelledby="tab-pan"
              class="section-card"
          >
            <header class="section-head">
              <div>
                <h2 class="section-title">PAN</h2>
                <p class="section-meta">Required · 10 characters (e.g. ABCDE1234F)</p>
              </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
              <div>
                <label class="label mb-1">PAN number</label>
                <input
                    v-model="form.pan"
                    @input="form.pan = form.pan.toUpperCase(); clearError('pan')"
                    maxlength="10"
                    placeholder="Enter PAN (e.g. ABCDE1234F)"
                    :class="inputClass(errors.pan)"
                    class="w-full"
                    :disabled="!isEditing"
                />
                <p class="mt-1 text-xs text-gray-500">Use uppercase letters. Ensure the name matches Aadhaar.</p>
                <p v-if="errors.pan" class="mt-1 text-xs text-red-600">{{ errors.pan }}</p>
              </div>

              <div>
                <label class="label mb-1">PAN image (JPG/PNG/PDF)</label>

                <div
                    class="uploader"
                    :class="{ 'uploader--drag': panDragOver }"
                    @dragover.prevent="onDragOver('pan')"
                    @dragleave.prevent="onDragLeave('pan')"
                    @drop.prevent="onDrop($event, 'pan')"
                >
                  <input
                      class="sr-only"
                      id="pan_file"
                      name="pan_file"
                      type="file"
                      accept="image/*,application/pdf"
                      :disabled="!isEditing"
                      @change="onFileChange($event, 'pan')"
                  />
                  <label for="pan_file" class="uploader-label">
                    <span class="block text-sm">Drag & drop or click to browse</span>
                    <span class="block text-xs text-gray-500">JPG/PNG/PDF up to 10MB</span>
                  </label>

                  <div v-if="uploadProgress.pan > 0" class="progress">
                    <div class="progress-bar" :style="{ width: uploadProgress.pan + '%' }"></div>
                  </div>

                  <div class="preview-wide mt-3">
                    <img v-if="preview.pan" :src="preview.pan" class="preview-wide-img" />
                    <span v-else class="preview-wide-empty">No preview</span>
                  </div>
                </div>

                <p class="mt-1 text-xs text-gray-500">Tip: Focus on the card; crop background if needed.</p>
              </div>
            </div>
          </section>

          <!-- GST Tab -->
          <section
              v-show="activeTab === 'gst'"
              role="tabpanel"
              :id="'panel-gst'"
              aria-labelledby="tab-gst"
              class="section-card"
          >
            <header class="section-head">
              <div>
                <h2 class="section-title">GST</h2>
                <p class="section-meta">Provide only if applicable to the business</p>
              </div>
            </header>

            <div class="space-y-4">
              <label class="inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="form.hasTax" class="mr-2 h-4 w-4 text-blue-600 border-gray-300 rounded" :disabled="!isEditing" />
                <span class="text-gray-700 dark:text-gray-200">This business has a GST number</span>
              </label>

              <div v-if="form.hasTax" class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div>
                  <label class="label mb-1">GST number</label>
                  <input
                      v-model="form.gst"
                      @input="form.gst = form.gst.toUpperCase(); clearError('gst')"
                      maxlength="15"
                      placeholder="Enter 15-character GSTIN"
                      :class="inputClass(errors.gst)"
                      class="w-full"
                      :disabled="!isEditing"
                  />
                  <p class="mt-1 text-xs text-gray-500">Alphanumeric, 15 characters.</p>
                  <p v-if="errors.gst" class="mt-1 text-xs text-red-600">{{ errors.gst }}</p>
                </div>

                <div>
                  <label class="label mb-1">GST document (JPG/PNG/PDF)</label>

                  <div
                      class="uploader"
                      :class="{ 'uploader--drag': gstDragOver }"
                      @dragover.prevent="onDragOver('gst')"
                      @dragleave.prevent="onDragLeave('gst')"
                      @drop.prevent="onDrop($event, 'gst')"
                  >
                    <input
                        class="sr-only"
                        id="gst_file"
                        name="gst_file"
                        type="file"
                        accept="image/*,application/pdf"
                        :disabled="!isEditing"
                        @change="onFileChange($event, 'gst')"
                    />
                    <label for="gst_file" class="uploader-label">
                      <span class="block text-sm">Drag & drop or click to browse</span>
                      <span class="block text-xs text-gray-500">JPG/PNG/PDF up to 10MB</span>
                    </label>

                    <div v-if="uploadProgress.gst > 0" class="progress">
                      <div class="progress-bar" :style="{ width: uploadProgress.gst + '%' }"></div>
                    </div>

                    <div class="preview-wide mt-3">
                      <img v-if="preview.gst" :src="preview.gst" class="preview-wide-img" />
                      <span v-else class="preview-wide-empty">No preview</span>
                    </div>
                  </div>

                  <p class="mt-1 text-xs text-gray-500">Ensure the GST doc matches business registration.</p>
                </div>
              </div>
            </div>
          </section>

          <!-- Utilities Tab -->
          <section
              v-show="activeTab === 'utilities'"
              role="tabpanel"
              :id="'panel-utilities'"
              aria-labelledby="tab-utilities"
              class="section-card"
          >
            <header class="section-head">
              <div>
                <h2 class="section-title">Utility Bills</h2>
                <p class="section-meta">Add identifiers for electricity, gas, telephone, water, etc.</p>
              </div>
            </header>

            <div class="space-y-3">
              <template v-for="(row, idx) in form.utility_bills" :key="row.id">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-2 items-center">
                  <div class="md:col-span-2">
                    <label class="label text-xs mb-1">Type</label>
                    <select v-model="row.type" @change="onUtilityTypeChange(idx)" :class="inputClass(errors[`utility_${idx}`])" class="w-full" :disabled="!isEditing">
                      <option value="">Select type</option>
                      <option v-for="opt in availableUtilityOptions(idx)" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                    </select>
                    <p class="mt-1 text-[11px] text-gray-500">Choose a unique bill type.</p>
                  </div>

                  <div class="md:col-span-3">
                    <label class="label text-xs mb-1">Identifier / account no.</label>
                    <input v-model="row.identifier" type="text" placeholder="Enter account or consumer number" :class="inputClass(errors[`utility_${idx}`])" :disabled="!isEditing" />
                    <p class="mt-1 text-[11px] text-gray-500">Example: 1234-5678-90</p>
                  </div>

                  <div class="md:col-span-1 flex gap-2">
                    <button type="button" @click="removeUtility(idx)" class="px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 shadow hover:shadow-lg transition" :disabled="!isEditing">
                      Remove
                    </button>
                  </div>
                </div>
              </template>

              <div>
                <button
                    type="button"
                    @click="addUtility"
                    :disabled="!isEditing || form.utility_bills.length >= utilityOptions.length"
                    class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 shadow hover:shadow-lg transition"
                >
                  Add Utility
                </button>
                <p v-if="utilityAddHint" class="text-xs text-gray-500 mt-2">{{ utilityAddHint }}</p>
              </div>
            </div>
          </section>

          <!-- Global Save inside the same <form> (submits entire payload) -->
          <div class="pt-2">
            <button
                type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-green-600 text-white shadow hover:shadow-lg hover:bg-green-700 disabled:opacity-50 active:scale-[0.98] transition"
                :disabled="processing || !isEditing"
            >
              <span v-if="processing" class="i-lucide-loader-2 w-4 h-4 animate-spin"></span>
              <span v-else class="i-lucide-save w-4 h-4"></span>
              <span>{{ processing ? 'Saving...' : 'Save changes' }}</span>
            </button>
            <p class="mt-2 text-xs text-gray-500">
              Aadhaar and PAN (including images) are required. GST and Utilities are optional and can be added later.
            </p>
          </div>
        </form>
      </div>
    </div>

    <!-- Create KYC Modal -->
    <transition enter-active-class="ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="createModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur">
        <div class="w-full max-w-lg rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-gray-800 shadow-2xl overflow-hidden">
          <div class="p-5 border-b border-gray-100 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Create KYC</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
              A new KYC record is required to proceed. Click “Start” to open the form and add details.
            </p>
          </div>
          <div class="p-5 flex items-center justify-end gap-2">
            <button
                type="button"
                class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-700 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 shadow-sm hover:shadow transition"
                @click="createModalOpen = false"
            >
              Close
            </button>
            <button
                type="button"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow hover:shadow-lg active:scale-[0.98] transition"
                @click="goToCreateForm"
            >
              Start
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
/**
 * Nuxt 3 – KYC Page
 * - Single <form> with accessible tabs; each tab contains its related fields.
 * - Aadhaar and PAN are required and include image upload with drag & drop, preview, and progress.
 * - GST: toggle first; fields appear only when toggled on.
 * - Utilities: identifiers only.
 * - Saving spinner and disabled states while submitting; PUT for update, POST for create.
 * - Tabs follow WAI-ARIA Authoring Practices (roles, ids, aria-controls). [web:108][web:114]
 */
import { ref, reactive, computed, onMounted, toRaw, onBeforeUnmount } from 'vue'
import { useSanctumFetch, useRuntimeConfig, useToast } from '#imports'
import { v4 as uuidv4 } from 'uuid'
import GlobalLoader from '~/components/GlobalLoader.vue'

definePageMeta({ layout: 'dashboard' }) // top-level macro [web:24]

/* Loading */
const isLoading = useState('pageLoading', () => true)

/* Core state */
const toast = useToast()
const config = useRuntimeConfig()
const processing = ref(false)
const isEditing = ref(false)
const hasExistingKyc = ref(false)
const createModalOpen = ref(false)

/* Tabs */
const tabs = [
  { key: 'aadhaar', label: 'Aadhaar' },
  { key: 'pan', label: 'PAN' },
  { key: 'gst', label: 'GST' },
  { key: 'utilities', label: 'Utilities' }
]
const activeTab = ref<'aadhaar'|'pan'|'gst'|'utilities'>('aadhaar')

/* Options */
const utilityOptions = [
  { value: 'electricity', label: 'Electricity Bill' },
  { value: 'gas', label: 'Gas Bill' },
  { value: 'telephone', label: 'Telephone / Landline' },
  { value: 'water', label: 'Water Bill' },
  { value: 'other', label: 'Other' }
]

/* Form */
const form = reactive({
  aadhaar: '',
  pan: '',
  aadhaarFile: /** @type {File|null} */ (null),
  panFile: /** @type {File|null} */ (null),
  hasTax: false,
  gst: '',
  gstFile: /** @type {File|null} */ (null),
  utility_bills: [] as Array<{ id: string, type: string, identifier: string }>,
})

/* Snapshot */
let initialSnapshot: any = null

/* Previews */
const preview = reactive({ aadhaar: '', pan: '', gst: '' })
function safeRevoke(url?: string) { try { if (url && url.startsWith('blob:')) URL.revokeObjectURL(url) } catch {} }
onBeforeUnmount(() => { safeRevoke(preview.aadhaar); safeRevoke(preview.pan); safeRevoke(preview.gst) })

/* Upload progress per field */
const uploadProgress = reactive({ aadhaar: 0, pan: 0, gst: 0 })
const aadhaarDragOver = ref(false)
const panDragOver = ref(false)
const gstDragOver = ref(false)

/* Drag & drop handlers (client-side only; preview + fake progress) */
function onDragOver(type: 'aadhaar'|'pan'|'gst') {
  if (!isEditing.value) return
  if (type === 'aadhaar') aadhaarDragOver.value = true
  if (type === 'pan') panDragOver.value = true
  if (type === 'gst') gstDragOver.value = true
}
function onDragLeave(type: 'aadhaar'|'pan'|'gst') {
  if (type === 'aadhaar') aadhaarDragOver.value = false
  if (type === 'pan') panDragOver.value = false
  if (type === 'gst') gstDragOver.value = false
}
function onDrop(e: DragEvent, type: 'aadhaar'|'pan'|'gst') {
  if (!isEditing.value) return
  const file = e.dataTransfer?.files?.[0] ?? null
  if (!file) return
  handleFileAssign(type, file)
  onDragLeave(type)
}

/* File selection */
function onFileChange(e: Event, type: 'aadhaar'|'pan'|'gst') {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0] ?? null
  if (!file) return
  handleFileAssign(type, file)
}

/* Assign file, create preview URL, simulate upload progress bar */
function handleFileAssign(type: 'aadhaar'|'pan'|'gst', file: File) {
  // attach file
  if (type === 'aadhaar') form.aadhaarFile = file
  if (type === 'pan') form.panFile = file
  if (type === 'gst') form.gstFile = file

  // preview for images (PDF won't preview as img)
  const isImage = file.type.startsWith('image/')
  const url = isImage ? URL.createObjectURL(file) : ''
  // revoke previous
  safeRevoke(preview[type])
  preview[type] = url

  // simulate progress locally; real progress needs XHR or fetch with streams
  uploadProgress[type] = 0
  const step = () => {
    if (uploadProgress[type] >= 100) return
    uploadProgress[type] += 10
    setTimeout(step, 60)
  }
  step()
}

/* Errors */
const errors = reactive<Record<string, string>>({})
function clearError(field: string) { errors[field] = '' }

/* Utilities helpers */
function availableUtilityOptions(index: number) {
  const used = form.utility_bills.map((b, i) => (i === index ? null : b.type)).filter(Boolean) as string[]
  return utilityOptions.filter(o => !used.includes(o.value))
}
const utilityAddHint = computed(() => {
  const left = utilityOptions.length - form.utility_bills.length
  return left <= 0 ? 'All utility types added.' : `${left} types left to add.`
})
function addUtility() { if (form.utility_bills.length < utilityOptions.length && isEditing.value) form.utility_bills.push({ id: uuidv4(), type: '', identifier: '' }) }
function removeUtility(idx: number) { if (!isEditing.value) return; form.utility_bills.splice(idx, 1); delete errors[`utility_${idx}`] }
function onUtilityTypeChange(idx: number) {
  const seen = new Set<string>()
  for (let i = 0; i < form.utility_bills.length; i++) {
    const t = form.utility_bills[i].type
    if (!t) continue
    if (seen.has(t) && i !== idx) { form.utility_bills[i].type = ''; form.utility_bills[i].identifier = '' } else { seen.add(t) }
  }
  clearError(`utility_${idx}`)
}

/* Load existing */
async function loadExisting() {
  try {
    isLoading.value = true
    const res = await useSanctumFetch(`${config.public.apiBase}/account/kyc`)
    const k = res?.data ?? null
    hasExistingKyc.value = !!k
    if (k) {
      form.aadhaar = (k.aadhaar || '').replace(/\D/g, '')
      form.pan = (k.pan || '').toUpperCase()
      form.hasTax = !!k.has_tax
      form.gst = (k.gst || '').toUpperCase()
      if (k.aadhaarImage) { safeRevoke(preview.aadhaar); preview.aadhaar = k.aadhaarImage }
      if (k.panImage) { safeRevoke(preview.pan); preview.pan = k.panImage }
      if (k.gstImage) { safeRevoke(preview.gst); preview.gst = k.gstImage }
      if (Array.isArray(k.utility_bills)) form.utility_bills = k.utility_bills.map((b: any) => ({ id: uuidv4(), type: b.type || '', identifier: b.value || '' }))
      initialSnapshot = deepCloneForm()
    } else {
      createModalOpen.value = true
    }
  } catch (e) {
    console.error('loadExisting KYC error', e)
  } finally {
    isLoading.value = false
  }
}
onMounted(loadExisting)

/* Masking */
const maskedAadhaar = computed(() => { const v = (form.aadhaar || '').replace(/\D/g, ''); return v.length === 12 ? `${v.slice(0,4)}-${'•'.repeat(4)}-${v.slice(8)}` : (v || '—') })
const maskedPan = computed(() => { const v = (form.pan || '').toUpperCase().trim(); return v.length === 10 ? `${v.slice(0,3)}${'•'.repeat(4)}${v.slice(7)}` : (v || '—') })
const maskedGst = computed(() => { const v = (form.gst || '').toUpperCase().trim(); return v.length === 15 ? `${v.slice(0,4)}${'•'.repeat(7)}${v.slice(11)}` : (v || '—') })

/* Validation (required: Aadhaar and PAN and images) */
function validateAadhaar() {
  const v = (form.aadhaar || '').trim()
  if (!v) { errors.aadhaar = 'Aadhaar is required.'; return false }
  if (!/^\d{12}$/.test(v)) { errors.aadhaar = 'Aadhaar must be 12 digits.'; return false }
  errors.aadhaar = ''; return true
}
function validatePan() {
  const v = (form.pan || '').toUpperCase().trim()
  if (!v) { errors.pan = 'PAN is required.'; return false }
  if (!/^[A-Z]{5}[0-9]{4}[A-Z]$/.test(v)) { errors.pan = 'PAN format invalid (e.g. ABCDE1234F).'; return false }
  errors.pan = ''; return true
}
function validateGst() {
  if (!form.hasTax) { errors.gst = ''; return true }
  const v = (form.gst || '').toUpperCase().trim()
  if (!v) { errors.gst = 'GST number is required when GST is enabled.'; return false }
  if (!/^[0-9A-Z]{15}$/.test(v)) { errors.gst = 'GST must be 15 alphanumeric characters.'; return false }
  errors.gst = ''; return true
}
function validateUtilities() {
  let ok = true
  const used = new Set<string>()
  form.utility_bills.forEach((row, i) => {
    if (!row.type && !row.identifier) { errors[`utility_${i}`] = ''; return }
    if (!row.type) { errors[`utility_${i}`] = 'Select utility type or remove this row.'; ok = false; return }
    if (!row.identifier || !row.identifier.trim()) { errors[`utility_${i}`] = 'Enter identifier / account number.'; ok = false; return }
    if (used.has(row.type)) { errors[`utility_${i}`] = 'This type is already chosen.'; ok = false; return }
    used.add(row.type); errors[`utility_${i}`] = ''
  })
  return ok
}
function validateImages() {
  // Require images only for Aadhaar and PAN
  if (!form.aadhaarFile && !preview.aadhaar) { errors.aadhaar = (errors.aadhaar || ''); errors.aadhaar = errors.aadhaar || 'Aadhaar image is required.'; return false }
  if (!form.panFile && !preview.pan) { errors.pan = (errors.pan || ''); errors.pan = errors.pan || 'PAN image is required.'; return false }
  return true
}
function validateAll() {
  const a = validateAadhaar()
  const p = validatePan()
  const g = validateGst()
  const u = validateUtilities()
  const i = validateImages()
  return a && p && g && u && i
}

/* UI helpers */
function inputClass(err = '') {
  return `w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 transition ${err ? 'border-red-600 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'} dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700`
}

/* Permissions stub */
function canUpdateKYC(): boolean { return true }

/* Edit controls */
function enableEdit() { if (!canUpdateKYC()) return; isEditing.value = true; Object.keys(errors).forEach(k => delete errors[k]) }
function cancelEdit() { if (initialSnapshot) applySnapshot(initialSnapshot); form.aadhaarFile=null; form.panFile=null; form.gstFile=null; isEditing.value=false }
function submitIfValid() { handleSubmit() }

/* Submit (entire form) */
async function handleSubmit() {
  if (!isEditing.value) return
  if (!validateAll()) { toast.error({ title: 'Validation', message: 'Please fix highlighted errors.' }); return }
  processing.value = true
  try {
    const payload = new FormData()
    payload.append('aadhaar', form.aadhaar || '')
    payload.append('pan', form.pan || '')
    payload.append('has_tax', form.hasTax ? '1' : '0')
    if (form.hasTax && form.gst) payload.append('gst', form.gst)
    if (form.aadhaarFile instanceof File) payload.append('aadhaar_file', form.aadhaarFile)
    if (form.panFile instanceof File) payload.append('pan_file', form.panFile)
    if (form.gstFile instanceof File) payload.append('gst_file', form.gstFile)
    const utilityPayload = form.utility_bills.filter(b => b.type && b.identifier && b.identifier.trim()).map(b => ({ type: b.type, value: b.identifier.trim() }))
    payload.append('utility_bills', JSON.stringify(utilityPayload))

    const method = hasExistingKyc.value ? 'PUT' : 'POST'
    const url = `${config.public.apiBase}/account/kyc`
    const res = await useSanctumFetch(url, { method, body: payload, headers: { Accept: 'application/json' } })
    const ok = res?.data?.success || res?.success || (res?.status === 'ok')
    if (ok) {
      toast.success({ title: 'Submitted', message: `KYC ${hasExistingKyc.value ? 'updated' : 'created'} successfully.` })
      await loadExisting()
      initialSnapshot = deepCloneForm()
      isEditing.value = false
      hasExistingKyc.value = true
      createModalOpen.value = false
    } else {
      const serverErrors = res?.data?.errors ?? res?.errors ?? null
      if (serverErrors && typeof serverErrors === 'object') {
        Object.entries(serverErrors).forEach(([k, v]) => { // @ts-ignore
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

/* Modal controls */
function openCreateModal() { createModalOpen.value = true }
function goToCreateForm() { createModalOpen.value = false; enableEdit() }

/* Snapshot helpers */
function deepCloneForm() {
  return {
    aadhaar: form.aadhaar,
    pan: form.pan,
    hasTax: form.hasTax,
    gst: form.gst,
    utility_bills: form.utility_bills.map(b => ({ id: b.id, type: b.type, identifier: b.identifier })),
    preview: { ...toRaw(preview) }
  }
}
function applySnapshot(s: any) {
  form.aadhaar = s.aadhaar || ''
  form.pan = s.pan || ''
  form.hasTax = !!s.hasTax
  form.gst = s.gst || ''
  form.utility_bills = Array.isArray(s.utility_bills) ? s.utility_bills.map((b: any) => ({ id: b.id || uuidv4(), type: b.type || '', identifier: b.identifier || '' })) : []
  preview.aadhaar = s.preview?.aadhaar || ''
  preview.pan = s.preview?.pan || ''
  preview.gst = s.preview?.gst || ''
}
</script>

<style scoped>
/* Premium section styling */
.section-card { @apply bg-white/90 dark:bg-gray-900/90 rounded-xl ring-1 ring-gray-100 dark:ring-gray-800 shadow-sm transition-all duration-200 p-4 sm:p-5; }
.section-card:hover { @apply shadow-md; }
.section-head { @apply flex items-center justify-between mb-3; }
.section-title { @apply text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100; }
.section-meta { @apply text-xs text-gray-500 dark:text-gray-400; }
.label { @apply text-sm text-gray-600 dark:text-gray-300; }
.value { @apply mt-1 font-medium text-gray-900 dark:text-gray-100; }

/* Big rectangular previewer */
.preview-wide { @apply w-full h-48 sm:h-56 md:h-64 border rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-800 flex items-center justify-center ring-1 ring-gray-100 dark:ring-gray-800 shadow-sm; }
.preview-wide-img { @apply w-full h-full object-contain; }
.preview-wide-empty { @apply text-sm text-gray-400; }

/* Uploader with drag & drop */
.uploader { @apply w-full border-2 border-dashed rounded-xl p-4 text-center bg-gray-50/60 dark:bg-gray-800/60 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 transition; }
.uploader--drag { @apply bg-blue-50 dark:bg-blue-900/30 border-blue-400; }
.uploader-label { @apply cursor-pointer inline-flex flex-col items-center justify-center gap-1 text-gray-700 dark:text-gray-200; }

/* Progress bar */
.progress { @apply mt-3 w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden; }
.progress-bar { @apply h-full bg-blue-600 transition-all duration-150; }

/* Icons placeholder */
.i-lucide-pencil,.i-lucide-undo-2,.i-lucide-save,.i-lucide-file-plus,.i-lucide-loader-2{ display:inline-block; }
</style>
