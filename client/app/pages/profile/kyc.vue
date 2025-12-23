<script setup lang="ts">
/**
 * KYC (Know Your Customer) Page
 * Allows users to submit and view KYC verification status
 */

definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const toast = useToast()
const config = useRuntimeConfig()

// State
const loading = ref(true)
const submitting = ref(false)
const kycStatus = ref<any>(null)
const hasKyc = ref(false)

// Form data
const kycType = ref<'personal' | 'business'>('personal')
const formData = ref({
  pan_number: '',
  aadhaar_number: '',
  company_name: '',
  company_type: '',
  gst_number: '',
})
const documents = ref<File[]>([])

// Company type options
const companyTypes = [
  { value: 'sole_proprietor', label: 'Sole Proprietor' },
  { value: 'partnership', label: 'Partnership' },
  { value: 'llp', label: 'LLP (Limited Liability Partnership)' },
  { value: 'private_limited', label: 'Private Limited' },
  { value: 'public_limited', label: 'Public Limited' },
  { value: 'huf', label: 'HUF (Hindu Undivided Family)' },
]

// Fetch KYC status on mount
onMounted(async () => {
  await fetchKycStatus()
})

const fetchKycStatus = async () => {
  loading.value = true
  try {
    const response = await useSanctumFetch<any>(`${config.public.apiBase}/api/kyc/status`)
    hasKyc.value = response.data?.has_kyc || false
    kycStatus.value = response.data?.kyc || null
  } catch (e: any) {
    console.error('Failed to fetch KYC status:', e)
  } finally {
    loading.value = false
  }
}

// Handle file selection
const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files) {
    documents.value = Array.from(target.files)
  }
}

// Remove a document
const removeDocument = (index: number) => {
  documents.value.splice(index, 1)
}

// Submit KYC
const submitKyc = async () => {
  submitting.value = true
  try {
    const formPayload = new FormData()
    formPayload.append('kyc_type', kycType.value)
    formPayload.append('pan_number', formData.value.pan_number.toUpperCase())

    if (formData.value.aadhaar_number) {
      formPayload.append('aadhaar_number', formData.value.aadhaar_number)
    }

    if (kycType.value === 'business') {
      formPayload.append('company_name', formData.value.company_name)
      formPayload.append('company_type', formData.value.company_type)
      formPayload.append('gst_number', formData.value.gst_number.toUpperCase())
    }

    documents.value.forEach((doc, index) => {
      formPayload.append(`documents[${index}]`, doc)
    })

    const endpoint = kycStatus.value?.id
      ? `${config.public.apiBase}/api/kyc/${kycStatus.value.id}/resubmit`
      : `${config.public.apiBase}/api/kyc/submit`

    const response = await useSanctumFetch<any>(endpoint, {
      method: 'POST',
      body: formPayload,
    })

    toast.add({
      title: 'KYC Submitted',
      description: response.message || 'Your KYC has been submitted for verification.',
      color: 'success'
    })

    await fetchKycStatus()
  } catch (e: any) {
    toast.add({
      title: 'Submission Failed',
      description: e.data?.message || 'Failed to submit KYC. Please try again.',
      color: 'error'
    })
  } finally {
    submitting.value = false
  }
}

// Status display helpers
const statusColor = computed(() => {
  switch (kycStatus.value?.status) {
    case 'verified':
      return 'success'
    case 'pending':
      return 'warning'
    case 'rejected':
      return 'error'
    default:
      return 'neutral'
  }
})

const statusIcon = computed(() => {
  switch (kycStatus.value?.status) {
    case 'verified':
      return 'i-lucide-check-circle'
    case 'pending':
      return 'i-lucide-clock'
    case 'rejected':
      return 'i-lucide-x-circle'
    default:
      return 'i-lucide-file-question'
  }
})

const canSubmit = computed(() => {
  if (!kycStatus.value) return true
  return kycStatus.value.status === 'rejected'
})

// Format PAN input (uppercase)
const formatPan = (event: Event) => {
  const target = event.target as HTMLInputElement
  formData.value.pan_number = target.value.toUpperCase()
}

// Format GST input (uppercase)
const formatGst = (event: Event) => {
  const target = event.target as HTMLInputElement
  formData.value.gst_number = target.value.toUpperCase()
}
</script>

<template>
  <div class="max-w-2xl mx-auto">
    <div class="glass-card overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
        <div class="flex items-center gap-4">
          <NuxtLink
            to="/profile"
            class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center hover:bg-white/30 transition-colors"
          >
            <UIcon
              name="i-lucide-arrow-left"
              class="w-5 h-5"
            />
          </NuxtLink>
          <div>
            <h1 class="text-xl font-bold">
              KYC Verification
            </h1>
            <p class="text-indigo-100 text-sm">
              Verify your identity to unlock all features
            </p>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div
        v-if="loading"
        class="p-12 text-center"
      >
        <UIcon
          name="i-lucide-loader-circle"
          class="w-10 h-10 text-indigo-500 animate-spin mx-auto mb-4"
        />
        <p class="text-slate-600 dark:text-slate-400">
          Loading KYC status...
        </p>
      </div>

      <!-- KYC Status Display (if exists) -->
      <div
        v-else-if="hasKyc && kycStatus"
        class="p-6 space-y-6"
      >
        <!-- Status Badge -->
        <div class="flex items-center justify-center">
          <div
            :class="[
              'flex items-center gap-3 px-6 py-4 rounded-2xl',
              statusColor === 'success' ? 'bg-green-100 dark:bg-green-900/30' : '',
              statusColor === 'warning' ? 'bg-amber-100 dark:bg-amber-900/30' : '',
              statusColor === 'error' ? 'bg-red-100 dark:bg-red-900/30' : '',
            ]"
          >
            <UIcon
              :name="statusIcon"
              :class="[
                'w-8 h-8',
                statusColor === 'success' ? 'text-green-600 dark:text-green-400' : '',
                statusColor === 'warning' ? 'text-amber-600 dark:text-amber-400' : '',
                statusColor === 'error' ? 'text-red-600 dark:text-red-400' : '',
              ]"
            />
            <div>
              <p class="font-semibold text-lg text-slate-900 dark:text-white capitalize">
                {{ kycStatus.status }}
              </p>
              <p class="text-sm text-slate-600 dark:text-slate-400">
                {{ kycStatus.status === 'verified' ? 'Your KYC is verified' : '' }}
                {{ kycStatus.status === 'pending' ? 'Under review (1-3 business days)' : '' }}
                {{ kycStatus.status === 'rejected' ? 'Please resubmit with correct details' : '' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Rejection Reason -->
        <div
          v-if="kycStatus.status === 'rejected' && kycStatus.rejection_reason"
          class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4"
        >
          <div class="flex gap-3">
            <UIcon
              name="i-lucide-alert-circle"
              class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5"
            />
            <div>
              <p class="font-medium text-red-800 dark:text-red-300">
                Rejection Reason
              </p>
              <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                {{ kycStatus.rejection_reason }}
              </p>
            </div>
          </div>
        </div>

        <!-- KYC Details -->
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 space-y-3">
          <div class="flex justify-between">
            <span class="text-slate-600 dark:text-slate-400">Type</span>
            <span class="font-medium text-slate-900 dark:text-white capitalize">
              {{ kycStatus.kyc_type }}
            </span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-600 dark:text-slate-400">PAN Number</span>
            <span class="font-mono font-medium text-slate-900 dark:text-white">
              {{ kycStatus.pan_number }}
            </span>
          </div>
          <div
            v-if="kycStatus.aadhaar_number"
            class="flex justify-between"
          >
            <span class="text-slate-600 dark:text-slate-400">Aadhaar</span>
            <span class="font-mono font-medium text-slate-900 dark:text-white">
              XXXX-XXXX-{{ kycStatus.aadhaar_number?.slice(-4) }}
            </span>
          </div>
          <div
            v-if="kycStatus.company_name"
            class="flex justify-between"
          >
            <span class="text-slate-600 dark:text-slate-400">Company</span>
            <span class="font-medium text-slate-900 dark:text-white">
              {{ kycStatus.company_name }}
            </span>
          </div>
          <div
            v-if="kycStatus.gst_number"
            class="flex justify-between"
          >
            <span class="text-slate-600 dark:text-slate-400">GST</span>
            <span class="font-mono font-medium text-slate-900 dark:text-white">
              {{ kycStatus.gst_number }}
            </span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-600 dark:text-slate-400">Submitted</span>
            <span class="text-slate-900 dark:text-white">
              {{ new Date(kycStatus.created_at).toLocaleDateString() }}
            </span>
          </div>
        </div>

        <!-- Resubmit Button (for rejected) -->
        <UButton
          v-if="canSubmit"
          color="primary"
          size="lg"
          block
          @click="hasKyc = false"
        >
          Resubmit KYC
        </UButton>
      </div>

      <!-- KYC Form (if no KYC or can resubmit) -->
      <div
        v-else
        class="p-6 space-y-6"
      >
        <!-- KYC Type Selection -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
            KYC Type
          </label>
          <div class="grid grid-cols-2 gap-3">
            <button
              type="button"
              :class="[
                'p-4 rounded-xl border-2 text-left transition-all',
                kycType === 'personal'
                  ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20'
                  : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'
              ]"
              @click="kycType = 'personal'"
            >
              <UIcon
                name="i-lucide-user"
                :class="[
                  'w-6 h-6 mb-2',
                  kycType === 'personal' ? 'text-indigo-600' : 'text-slate-400'
                ]"
              />
              <p class="font-medium text-slate-900 dark:text-white">
                Personal
              </p>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Individual account
              </p>
            </button>
            <button
              type="button"
              :class="[
                'p-4 rounded-xl border-2 text-left transition-all',
                kycType === 'business'
                  ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20'
                  : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'
              ]"
              @click="kycType = 'business'"
            >
              <UIcon
                name="i-lucide-building"
                :class="[
                  'w-6 h-6 mb-2',
                  kycType === 'business' ? 'text-indigo-600' : 'text-slate-400'
                ]"
              />
              <p class="font-medium text-slate-900 dark:text-white">
                Business
              </p>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Company account
              </p>
            </button>
          </div>
        </div>

        <!-- PAN Number -->
        <UFormField
          label="PAN Number"
          required
        >
          <UInput
            v-model="formData.pan_number"
            placeholder="ABCDE1234F"
            maxlength="10"
            class="uppercase"
            @input="formatPan"
          />
          <template #hint>
            <span class="text-xs">Format: 5 letters + 4 digits + 1 letter</span>
          </template>
        </UFormField>

        <!-- Aadhaar Number (Optional) -->
        <UFormField label="Aadhaar Number (Optional)">
          <UInput
            v-model="formData.aadhaar_number"
            placeholder="123456789012"
            maxlength="12"
            inputmode="numeric"
          />
          <template #hint>
            <span class="text-xs">12-digit Aadhaar number</span>
          </template>
        </UFormField>

        <!-- Business Fields -->
        <template v-if="kycType === 'business'">
          <UFormField
            label="Company Name"
            required
          >
            <UInput
              v-model="formData.company_name"
              placeholder="Your Company Pvt Ltd"
            />
          </UFormField>

          <UFormField
            label="Company Type"
            required
          >
            <USelect
              v-model="formData.company_type"
              :options="companyTypes"
              placeholder="Select company type"
            />
          </UFormField>

          <UFormField
            label="GST Number"
            required
          >
            <UInput
              v-model="formData.gst_number"
              placeholder="22AAAAA0000A1Z5"
              maxlength="15"
              class="uppercase"
              @input="formatGst"
            />
            <template #hint>
              <span class="text-xs">15-character GST number</span>
            </template>
          </UFormField>
        </template>

        <!-- Document Upload -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
            Upload Documents (Optional)
          </label>
          <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-6 text-center">
            <input
              type="file"
              multiple
              accept=".jpg,.jpeg,.png,.pdf"
              class="hidden"
              id="document-upload"
              @change="handleFileSelect"
            >
            <label
              for="document-upload"
              class="cursor-pointer"
            >
              <UIcon
                name="i-lucide-upload-cloud"
                class="w-10 h-10 text-slate-400 mx-auto mb-2"
              />
              <p class="text-sm text-slate-600 dark:text-slate-400">
                Click to upload PAN card, Aadhaar, or other documents
              </p>
              <p class="text-xs text-slate-500 mt-1">
                JPEG, PNG, PDF (Max 5MB each)
              </p>
            </label>
          </div>

          <!-- Uploaded Files List -->
          <div
            v-if="documents.length > 0"
            class="mt-3 space-y-2"
          >
            <div
              v-for="(doc, index) in documents"
              :key="index"
              class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg"
            >
              <div class="flex items-center gap-2">
                <UIcon
                  name="i-lucide-file"
                  class="w-4 h-4 text-slate-400"
                />
                <span class="text-sm text-slate-700 dark:text-slate-300 truncate max-w-[200px]">
                  {{ doc.name }}
                </span>
              </div>
              <UButton
                icon="i-lucide-x"
                variant="ghost"
                size="xs"
                color="error"
                @click="removeDocument(index)"
              />
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <UButton
          color="primary"
          size="lg"
          block
          :loading="submitting"
          :disabled="!formData.pan_number || (kycType === 'business' && (!formData.company_name || !formData.company_type || !formData.gst_number))"
          @click="submitKyc"
        >
          {{ kycStatus?.id ? 'Resubmit KYC' : 'Submit KYC' }}
        </UButton>

        <p class="text-xs text-center text-slate-500 dark:text-slate-400">
          By submitting, you agree to our verification process.
          Your data is encrypted and secure.
        </p>
      </div>
    </div>
  </div>
</template>
