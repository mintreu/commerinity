<template>
  <div class="step-kyc">
    <div class="max-w-lg mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <UIcon
            name="i-lucide-shield-check"
            class="w-8 h-8 text-purple-600 dark:text-purple-400"
          />
        </div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-2">
          KYC Verification
        </h2>
        <p class="text-gray-600 dark:text-gray-400 text-sm md:text-base">
          Verify your identity to unlock all features.
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
          Your documents are encrypted and never shared with third parties.
        </p>
      </div>

      <!-- Skip Notice -->
      <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
        <div class="flex items-start gap-3">
          <UIcon
            name="i-lucide-info"
            class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5"
          />
          <div>
            <p class="text-sm text-blue-800 dark:text-blue-200">
              <strong>This step is optional.</strong> You can complete it later from your profile settings.
              However, some features like withdrawals require KYC verification.
            </p>
          </div>
        </div>
      </div>

      <!-- KYC Benefits -->
      <div class="mb-6 space-y-3">
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
          Benefits of KYC verification:
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div
            v-for="benefit in benefits"
            :key="benefit.title"
            class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200/60 dark:border-gray-700/60"
          >
            <UIcon
              :name="benefit.icon"
              class="w-5 h-5 text-primary-500 shrink-0"
            />
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ benefit.title }}</span>
          </div>
        </div>
      </div>

      <!-- Document Type Selection -->
      <div
        v-if="!skipping"
        class="space-y-5"
      >
        <UFormField
          label="Select Document Type"
          name="document_type"
          required
        >
          <USelect
            v-model="formState.document_type"
            :items="documentTypes"
            placeholder="Choose document type"
            size="lg"
            icon="i-lucide-file-text"
          />
        </UFormField>

        <!-- Document Number -->
        <UFormField
          v-if="formState.document_type"
          :label="selectedDocumentLabel + ' Number'"
          name="document_number"
          required
        >
          <UInput
            v-model="formState.document_number"
            :placeholder="documentPlaceholder"
            size="lg"
            icon="i-lucide-hash"
            :maxlength="documentMaxLength"
            class="uppercase"
          />
          <template #hint>
            <span class="text-xs text-gray-500">Enter exactly as shown on the document.</span>
          </template>
        </UFormField>

        <!-- Document Upload -->
        <div
          v-if="formState.document_type"
          class="space-y-4"
        >
          <!-- Front Side -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              {{ selectedDocumentLabel }} - Front Side
            </label>
            <div
              class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 text-center cursor-pointer hover:border-primary-500 transition-colors"
              @click="triggerUpload('front')"
              @drop.prevent="handleDrop($event, 'front')"
              @dragover.prevent
            >
              <input
                ref="frontInput"
                type="file"
                accept="image/*,.pdf"
                class="hidden"
                @change="handleFileChange($event, 'front')"
              >

              <div
                v-if="frontPreview"
                class="relative"
              >
                <img
                  :src="frontPreview"
                  alt="Front preview"
                  class="max-h-40 mx-auto rounded-lg"
                >
                <button
                  type="button"
                  class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center"
                  @click.stop="removeFile('front')"
                >
                  <UIcon
                    name="i-lucide-x"
                    class="w-4 h-4"
                  />
                </button>
              </div>

              <div v-else>
                <UIcon
                  name="i-lucide-upload"
                  class="w-10 h-10 text-gray-400 mx-auto mb-2"
                />
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  Click or drag to upload front side
                </p>
                <p class="text-xs text-gray-500 mt-1">
                  JPG, PNG or PDF (max 5MB)
                </p>
              </div>
            </div>
          </div>

          <!-- Back Side (optional for some docs) -->
          <div v-if="requiresBackSide">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              {{ selectedDocumentLabel }} - Back Side
            </label>
            <div
              class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 text-center cursor-pointer hover:border-primary-500 transition-colors"
              @click="triggerUpload('back')"
              @drop.prevent="handleDrop($event, 'back')"
              @dragover.prevent
            >
              <input
                ref="backInput"
                type="file"
                accept="image/*,.pdf"
                class="hidden"
                @change="handleFileChange($event, 'back')"
              >

              <div
                v-if="backPreview"
                class="relative"
              >
                <img
                  :src="backPreview"
                  alt="Back preview"
                  class="max-h-40 mx-auto rounded-lg"
                >
                <button
                  type="button"
                  class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center"
                  @click.stop="removeFile('back')"
                >
                  <UIcon
                    name="i-lucide-x"
                    class="w-4 h-4"
                  />
                </button>
              </div>

              <div v-else>
                <UIcon
                  name="i-lucide-upload"
                  class="w-10 h-10 text-gray-400 mx-auto mb-2"
                />
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  Click or drag to upload back side
                </p>
                <p class="text-xs text-gray-500 mt-1">
                  JPG, PNG or PDF (max 5MB)
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Skip Confirmation -->
      <div
        v-else
        class="text-center py-8"
      >
        <UIcon
          name="i-lucide-skip-forward"
          class="w-16 h-16 text-gray-400 mx-auto mb-4"
        />
        <p class="text-gray-600 dark:text-gray-400">
          You've chosen to skip KYC for now. You can complete it anytime from your profile.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface KycData {
  document_type: string
  document_number: string
  front_file: File | null
  back_file: File | null
  skipped: boolean
}

const emit = defineEmits<{
  'update:data': [data: KycData]
  'valid': [isValid: boolean]
  'skip': []
}>()

const benefits = [
  { title: 'Higher withdrawal limits', icon: 'i-lucide-trending-up' },
  { title: 'Faster transactions', icon: 'i-lucide-zap' },
  { title: 'Account security', icon: 'i-lucide-shield' },
  { title: 'Access to all features', icon: 'i-lucide-star' }
]

const documentTypes = [
  { label: 'Aadhaar Card', value: 'aadhaar' },
  { label: 'PAN Card', value: 'pan' },
  { label: 'Driving License', value: 'driving_license' },
  { label: 'Passport', value: 'passport' },
  { label: 'Voter ID', value: 'voter_id' }
]

const formState = reactive<KycData>({
  document_type: '',
  document_number: '',
  front_file: null,
  back_file: null,
  skipped: false
})

const skipping = ref(false)
const frontInput = ref<HTMLInputElement>()
const backInput = ref<HTMLInputElement>()
const frontPreview = ref<string | null>(null)
const backPreview = ref<string | null>(null)

// Computed properties
const selectedDocumentLabel = computed(() => {
  const doc = documentTypes.find(d => d.value === formState.document_type)
  return doc?.label || 'Document'
})

const documentPlaceholder = computed(() => {
  switch (formState.document_type) {
    case 'aadhaar': return 'XXXX XXXX XXXX'
    case 'pan': return 'ABCDE1234F'
    case 'passport': return 'A1234567'
    default: return 'Enter document number'
  }
})

const documentMaxLength = computed(() => {
  switch (formState.document_type) {
    case 'aadhaar': return 14 // with spaces
    case 'pan': return 10
    case 'passport': return 8
    default: return 20
  }
})

const requiresBackSide = computed(() => {
  return ['aadhaar', 'driving_license', 'voter_id'].includes(formState.document_type)
})

// Validation
const isValid = computed(() => {
  if (skipping.value || formState.skipped) return true

  if (!formState.document_type) return false
  if (!formState.document_number || formState.document_number.length < 4) return false
  if (!formState.front_file) return false
  if (requiresBackSide.value && !formState.back_file) return false

  return true
})

// Watch and emit
watch(
  () => ({ ...formState, skipped: skipping.value }),
  (newData) => {
    formState.skipped = skipping.value
    emit('update:data', { ...newData, skipped: skipping.value })
    emit('valid', isValid.value)
  },
  { deep: true, immediate: true }
)

const triggerUpload = (side: 'front' | 'back') => {
  if (side === 'front') {
    frontInput.value?.click()
  } else {
    backInput.value?.click()
  }
}

const handleFileChange = (event: Event, side: 'front' | 'back') => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    processFile(file, side)
  }
}

const handleDrop = (event: DragEvent, side: 'front' | 'back') => {
  const file = event.dataTransfer?.files?.[0]
  if (file) {
    processFile(file, side)
  }
}

const processFile = (file: File, side: 'front' | 'back') => {
  // Validate file size (5MB)
  if (file.size > 5 * 1024 * 1024) {
    useToast().add({
      title: 'File too large',
      description: 'Please select a file smaller than 5MB',
      color: 'error'
    })
    return
  }

  if (side === 'front') {
    formState.front_file = file
    createPreview(file, 'front')
  } else {
    formState.back_file = file
    createPreview(file, 'back')
  }
}

const createPreview = (file: File, side: 'front' | 'back') => {
  if (file.type.startsWith('image/')) {
    const reader = new FileReader()
    reader.onload = (e) => {
      if (side === 'front') {
        frontPreview.value = e.target?.result as string
      } else {
        backPreview.value = e.target?.result as string
      }
    }
    reader.readAsDataURL(file)
  } else {
    // PDF - show icon instead
    if (side === 'front') {
      frontPreview.value = null // Will show PDF icon
    } else {
      backPreview.value = null
    }
  }
}

const removeFile = (side: 'front' | 'back') => {
  if (side === 'front') {
    formState.front_file = null
    frontPreview.value = null
    if (frontInput.value) frontInput.value.value = ''
  } else {
    formState.back_file = null
    backPreview.value = null
    if (backInput.value) backInput.value.value = ''
  }
}

// Expose methods
const skip = () => {
  skipping.value = true
  formState.skipped = true
  emit('skip')
}

const validate = (): boolean => isValid.value

const getData = (): KycData => ({
  ...formState,
  skipped: skipping.value
})

defineExpose({
  validate,
  getData,
  skip
})
</script>
