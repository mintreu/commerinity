<template>
  <!-- Global Loader until data populated -->
  <GlobalLoader v-if="isLoading" />

  <!-- Page Wrapper -->
  <div v-else class="kyc-page min-h-screen w-full bg-gradient-to-br from-gray-50 via-blue-50/30 to-purple-50/30 dark:from-gray-950 dark:via-blue-950/30 dark:to-purple-950/30">
    <!-- Background Effects -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div ref="pageOrb1" class="absolute -top-20 -right-20 w-96 h-96 bg-gradient-to-r from-blue-400/10 to-purple-400/10 rounded-full blur-3xl opacity-60"></div>
      <div ref="pageOrb2" class="absolute -bottom-20 -left-20 w-80 h-80 bg-gradient-to-r from-purple-400/10 to-pink-400/10 rounded-full blur-3xl opacity-70"></div>
    </div>

    <div class="relative z-10 mx-auto w-full max-w-7xl px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-10">
      <!-- Enhanced Card Shell -->
      <div class="w-full bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-2xl ring-1 ring-gray-200/50 dark:ring-gray-700/50 transition-all duration-300 hover:shadow-3xl" ref="mainCard">

        <!-- Enhanced Header -->
        <div class="header-section relative overflow-hidden">
          <!-- Header Background -->
          <div class="absolute inset-0 bg-gradient-to-r from-blue-600/5 via-purple-600/5 to-pink-600/5 dark:from-blue-400/10 dark:via-purple-400/10 dark:to-pink-400/10"></div>

          <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-6 md:p-8 border-b border-gray-200/50 dark:border-gray-700/50">
            <div class="header-content">
              <!-- Status Badge -->
              <div class="flex items-center gap-3 mb-3">
                <div class="status-badge px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
                     :class="hasExistingKyc ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'">
                  {{ hasExistingKyc ? 'KYC Active' : 'KYC Pending' }}
                </div>
                <div class="verification-level text-xs text-gray-500 dark:text-gray-400">
                  Verification Level: {{ hasExistingKyc ? 'Complete' : 'Incomplete' }}
                </div>
              </div>

              <h1 class="page-title text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mb-2">
                {{ hasExistingKyc ? 'Update KYC Record' : 'Complete KYC Verification' }}
              </h1>
              <p class="page-description text-gray-600 dark:text-gray-400 max-w-2xl">
                Secure your account with government-issued identification. Aadhaar and PAN with clear images are required.
                GST and utility bills are optional for enhanced verification.
              </p>
            </div>

            <!-- Enhanced Actions -->
            <div class="header-actions flex items-center gap-3">
              <button
                  v-if="hasExistingKyc && !isEditing"
                  type="button"
                  class="action-btn action-btn-primary"
                  @click="enableEdit"
                  aria-label="Edit KYC"
              >
                <Icon name="mdi:pencil" class="w-4 h-4" />
                Edit KYC
              </button>

              <template v-if="isEditing">
                <button
                    type="button"
                    class="action-btn action-btn-secondary"
                    @click="cancelEdit"
                    aria-label="Cancel editing"
                    :disabled="processing"
                >
                  <Icon name="mdi:close" class="w-4 h-4" />
                  Cancel
                </button>
                <button
                    type="button"
                    class="action-btn action-btn-success"
                    @click="submitIfValid"
                    :disabled="processing"
                    aria-label="Save KYC"
                >
                  <Icon v-if="processing" name="mdi:loading" class="w-4 h-4 animate-spin" />
                  <Icon v-else name="mdi:check" class="w-4 h-4" />
                  <span>{{ processing ? 'Saving...' : 'Save Changes' }}</span>
                </button>
              </template>
            </div>
          </div>
        </div>

        <!-- Enhanced Form -->
        <form @submit.prevent="handleSubmit" class="kyc-form p-6 md:p-8 space-y-8" enctype="multipart/form-data" ref="kycForm">

          <!-- Enhanced Tabs -->
          <div class="tabs-container">
            <div class="tabs-wrapper flex flex-wrap gap-2 mb-6" role="tablist" aria-label="KYC sections">
              <button
                  v-for="t in tabs"
                  :key="t.key"
                  role="tab"
                  type="button"
                  class="tab-button"
                  :class="getTabClasses(t.key)"
                  :aria-selected="activeTab === t.key"
                  :aria-controls="`panel-${t.key}`"
                  :id="`tab-${t.key}`"
                  @click="activeTab = t.key"
              >
                <Icon :name="t.icon" class="w-5 h-5" />
                <span class="font-semibold">{{ t.label }}</span>
                <div v-if="getTabStatus(t.key)" class="tab-status" :class="getTabStatusClasses(t.key)">
                  <Icon :name="getTabStatus(t.key) === 'complete' ? 'mdi:check-circle' : 'mdi:alert-circle'" class="w-4 h-4" />
                </div>
              </button>
            </div>

            <!-- Tab Descriptions -->
            <div class="tab-description p-4 bg-gray-50/50 dark:bg-gray-800/50 rounded-xl border border-gray-200/50 dark:border-gray-700/50 mb-6">
              <Icon :name="getActiveTabIcon()" class="w-5 h-5 text-blue-600 dark:text-blue-400 mb-2" />
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ getActiveTabDescription() }}</p>
            </div>
          </div>

          <!-- Aadhaar Section -->
          <section
              v-show="activeTab === 'aadhaar'"
              role="tabpanel"
              :id="'panel-aadhaar'"
              aria-labelledby="tab-aadhaar"
              class="form-section"
          >
            <div class="section-header">
              <div class="section-icon bg-gradient-to-br from-blue-500 to-indigo-600">
                <Icon name="mdi:card-account-details" class="w-6 h-6 text-white" />
              </div>
              <div>
                <h2 class="section-title">Aadhaar Verification</h2>
                <p class="section-subtitle">Government-issued unique identification</p>
              </div>
              <div class="section-badge section-badge-required">Required</div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <!-- Aadhaar Input -->
              <div class="form-group">
                <label class="form-label">Aadhaar Number</label>
                <div class="input-wrapper">
                  <input
                      v-model="form.aadhaar"
                      @input="clearError('aadhaar')"
                      maxlength="12"
                      inputmode="numeric"
                      placeholder="Enter 12-digit Aadhaar number"
                      :class="getInputClasses(errors.aadhaar)"
                      :disabled="!isEditing"
                  />
                  <Icon name="mdi:card-account-details" class="input-icon" />
                </div>
                <p class="form-hint">Enter without spaces or special characters</p>
                <p v-if="errors.aadhaar" class="form-error">{{ errors.aadhaar }}</p>
                <p v-if="form.aadhaar && !errors.aadhaar" class="form-success">
                  <Icon name="mdi:check-circle" class="w-4 h-4" />
                  Masked: {{ maskedAadhaar }}
                </p>
              </div>

              <!-- Aadhaar Upload -->
              <div class="form-group">
                <label class="form-label">Aadhaar Document</label>
                <div class="upload-container">
                  <div
                      class="upload-zone"
                      :class="{ 'upload-zone--drag': aadhaarDragOver, 'upload-zone--disabled': !isEditing }"
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
                    <label for="aadhaar_file" class="upload-label">
                      <Icon name="mdi:cloud-upload" class="w-8 h-8 text-blue-600 dark:text-blue-400 mb-2" />
                      <span class="upload-text">Drop your file here or click to browse</span>
                      <span class="upload-hint">JPG, PNG, PDF up to 10MB</span>
                    </label>

                    <!-- Upload Progress -->
                    <div v-if="uploadProgress.aadhaar > 0" class="upload-progress">
                      <div class="progress-bar" :style="{ width: uploadProgress.aadhaar + '%' }"></div>
                    </div>

                    <!-- Preview -->
                    <div v-if="preview.aadhaar" class="upload-preview group">
                      <img :src="preview.aadhaar" class="preview-image" alt="Aadhaar preview" />
                      <div class="preview-overlay">
                        <button
                            type="button"
                            @click="removePreview('aadhaar')"
                            class="preview-remove"
                            :disabled="!isEditing"
                        >
                          <Icon name="mdi:close" class="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <p class="form-hint">Ensure text is clear and readable</p>
              </div>
            </div>
          </section>

          <!-- PAN Section -->
          <section
              v-show="activeTab === 'pan'"
              role="tabpanel"
              :id="'panel-pan'"
              aria-labelledby="tab-pan"
              class="form-section"
          >
            <div class="section-header">
              <div class="section-icon bg-gradient-to-br from-purple-500 to-pink-600">
                <Icon name="mdi:credit-card" class="w-6 h-6 text-white" />
              </div>
              <div>
                <h2 class="section-title">PAN Verification</h2>
                <p class="section-subtitle">Permanent Account Number for tax purposes</p>
              </div>
              <div class="section-badge section-badge-required">Required</div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <!-- PAN Input -->
              <div class="form-group">
                <label class="form-label">PAN Number</label>
                <div class="input-wrapper">
                  <input
                      v-model="form.pan"
                      @input="form.pan = form.pan.toUpperCase(); clearError('pan')"
                      maxlength="10"
                      placeholder="Enter PAN (e.g. ABCDE1234F)"
                      :class="getInputClasses(errors.pan)"
                      :disabled="!isEditing"
                  />
                  <Icon name="mdi:credit-card" class="input-icon" />
                </div>
                <p class="form-hint">10 characters: 5 letters + 4 digits + 1 letter</p>
                <p v-if="errors.pan" class="form-error">{{ errors.pan }}</p>
                <p v-if="form.pan && !errors.pan" class="form-success">
                  <Icon name="mdi:check-circle" class="w-4 h-4" />
                  Masked: {{ maskedPan }}
                </p>
              </div>

              <!-- PAN Upload -->
              <div class="form-group">
                <label class="form-label">PAN Document</label>
                <div class="upload-container">
                  <div
                      class="upload-zone"
                      :class="{ 'upload-zone--drag': panDragOver, 'upload-zone--disabled': !isEditing }"
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
                    <label for="pan_file" class="upload-label">
                      <Icon name="mdi:cloud-upload" class="w-8 h-8 text-purple-600 dark:text-purple-400 mb-2" />
                      <span class="upload-text">Drop your file here or click to browse</span>
                      <span class="upload-hint">JPG, PNG, PDF up to 10MB</span>
                    </label>

                    <div v-if="uploadProgress.pan > 0" class="upload-progress">
                      <div class="progress-bar" :style="{ width: uploadProgress.pan + '%' }"></div>
                    </div>

                    <div v-if="preview.pan" class="upload-preview group">
                      <img :src="preview.pan" class="preview-image" alt="PAN preview" />
                      <div class="preview-overlay">
                        <button
                            type="button"
                            @click="removePreview('pan')"
                            class="preview-remove"
                            :disabled="!isEditing"
                        >
                          <Icon name="mdi:close" class="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <p class="form-hint">Ensure card details are clearly visible</p>
              </div>
            </div>
          </section>

          <!-- GST Section -->
          <section
              v-show="activeTab === 'gst'"
              role="tabpanel"
              :id="'panel-gst'"
              aria-labelledby="tab-gst"
              class="form-section"
          >
            <div class="section-header">
              <div class="section-icon bg-gradient-to-br from-green-500 to-emerald-600">
                <Icon name="mdi:receipt" class="w-6 h-6 text-white" />
              </div>
              <div>
                <h2 class="section-title">GST Registration</h2>
                <p class="section-subtitle">Goods and Services Tax registration</p>
              </div>
              <div class="section-badge section-badge-optional">Optional</div>
            </div>

            <div class="space-y-6">
              <!-- GST Toggle -->
              <div class="toggle-container">
                <label class="toggle-wrapper">
                  <input
                      type="checkbox"
                      v-model="form.hasTax"
                      class="sr-only peer"
                      :disabled="!isEditing"
                  />
                  <div class="toggle-switch peer-checked:bg-blue-600">
                    <div class="toggle-slider peer-checked:translate-x-6"></div>
                  </div>
                  <span class="toggle-label">This business has GST registration</span>
                </label>
              </div>

              <!-- GST Fields (when enabled) -->
              <div v-if="form.hasTax" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- GST Input -->
                <div class="form-group">
                  <label class="form-label">GSTIN Number</label>
                  <div class="input-wrapper">
                    <input
                        v-model="form.gst"
                        @input="form.gst = form.gst.toUpperCase(); clearError('gst')"
                        maxlength="15"
                        placeholder="Enter 15-character GSTIN"
                        :class="getInputClasses(errors.gst)"
                        :disabled="!isEditing"
                    />
                    <Icon name="mdi:receipt" class="input-icon" />
                  </div>
                  <p class="form-hint">15 alphanumeric characters</p>
                  <p v-if="errors.gst" class="form-error">{{ errors.gst }}</p>
                  <p v-if="form.gst && !errors.gst" class="form-success">
                    <Icon name="mdi:check-circle" class="w-4 h-4" />
                    Masked: {{ maskedGst }}
                  </p>
                </div>

                <!-- GST Upload -->
                <div class="form-group">
                  <label class="form-label">GST Certificate</label>
                  <div class="upload-container">
                    <div
                        class="upload-zone"
                        :class="{ 'upload-zone--drag': gstDragOver, 'upload-zone--disabled': !isEditing }"
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
                      <label for="gst_file" class="upload-label">
                        <Icon name="mdi:cloud-upload" class="w-8 h-8 text-green-600 dark:text-green-400 mb-2" />
                        <span class="upload-text">Drop your file here or click to browse</span>
                        <span class="upload-hint">JPG, PNG, PDF up to 10MB</span>
                      </label>

                      <div v-if="uploadProgress.gst > 0" class="upload-progress">
                        <div class="progress-bar" :style="{ width: uploadProgress.gst + '%' }"></div>
                      </div>

                      <div v-if="preview.gst" class="upload-preview group">
                        <img :src="preview.gst" class="preview-image" alt="GST preview" />
                        <div class="preview-overlay">
                          <button
                              type="button"
                              @click="removePreview('gst')"
                              class="preview-remove"
                              :disabled="!isEditing"
                          >
                            <Icon name="mdi:close" class="w-4 h-4" />
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <p class="form-hint">Upload GST registration certificate</p>
                </div>
              </div>
            </div>
          </section>

          <!-- Utilities Section -->
          <section
              v-show="activeTab === 'utilities'"
              role="tabpanel"
              :id="'panel-utilities'"
              aria-labelledby="tab-utilities"
              class="form-section"
          >
            <div class="section-header">
              <div class="section-icon bg-gradient-to-br from-orange-500 to-red-600">
                <Icon name="mdi:home-lightning-bolt" class="w-6 h-6 text-white" />
              </div>
              <div>
                <h2 class="section-title">Utility Bills</h2>
                <p class="section-subtitle">Additional verification documents</p>
              </div>
              <div class="section-badge section-badge-optional">Optional</div>
            </div>

            <div class="utilities-container space-y-4">
              <!-- Utility Rows -->
              <template v-for="(row, idx) in form.utility_bills" :key="row.id">
                <div class="utility-row">
                  <div class="utility-content">
                    <div class="utility-type">
                      <label class="form-label-sm">Utility Type</label>
                      <select
                          v-model="row.type"
                          @change="onUtilityTypeChange(idx)"
                          :class="getInputClasses(errors[`utility_${idx}`])"
                          :disabled="!isEditing"
                      >
                        <option value="">Select type</option>
                        <option v-for="opt in availableUtilityOptions(idx)" :key="opt.value" :value="opt.value">
                          {{ opt.label }}
                        </option>
                      </select>
                    </div>

                    <div class="utility-identifier">
                      <label class="form-label-sm">Account/Consumer Number</label>
                      <input
                          v-model="row.identifier"
                          type="text"
                          placeholder="Enter account number"
                          :class="getInputClasses(errors[`utility_${idx}`])"
                          :disabled="!isEditing"
                      />
                    </div>

                    <div class="utility-actions">
                      <button
                          type="button"
                          @click="removeUtility(idx)"
                          class="action-btn action-btn-danger action-btn-sm"
                          :disabled="!isEditing"
                      >
                        <Icon name="mdi:delete" class="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                  <p v-if="errors[`utility_${idx}`]" class="form-error">{{ errors[`utility_${idx}`] }}</p>
                </div>
              </template>

              <!-- Add Utility Button -->
              <div class="add-utility">
                <button
                    type="button"
                    @click="addUtility"
                    :disabled="!isEditing || form.utility_bills.length >= utilityOptions.length"
                    class="action-btn action-btn-outline"
                >
                  <Icon name="mdi:plus" class="w-4 h-4" />
                  Add Utility Bill
                </button>
                <p v-if="utilityAddHint" class="form-hint mt-2">{{ utilityAddHint }}</p>
              </div>
            </div>
          </section>

          <!-- Form Actions -->
          <div class="form-actions pt-8 border-t border-gray-200/50 dark:border-gray-700/50">
            <div class="flex items-center justify-between">
              <div class="action-info">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  <Icon name="mdi:information" class="w-4 h-4 inline mr-1" />
                  Aadhaar and PAN documents are mandatory for verification
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                  GST and utility bills are optional and enhance your verification level
                </p>
              </div>

              <button
                  type="submit"
                  class="action-btn action-btn-primary action-btn-lg"
                  :disabled="processing || !isEditing"
              >
                <Icon v-if="processing" name="mdi:loading" class="w-5 h-5 animate-spin" />
                <Icon v-else name="mdi:shield-check" class="w-5 h-5" />
                <span>{{ processing ? 'Submitting...' : 'Submit KYC' }}</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Enhanced Create KYC Modal -->
    <transition name="modal">
      <div v-if="createModalOpen" class="modal-overlay" @click="createModalOpen = false">
        <div class="modal-container" @click.stop>
          <div class="modal-header">
            <div class="modal-icon">
              <Icon name="mdi:shield-plus" class="w-8 h-8 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
              <h3 class="modal-title">Complete KYC Verification</h3>
              <p class="modal-subtitle">Secure your account with identity verification</p>
            </div>
          </div>

          <div class="modal-body">
            <div class="kyc-benefits">
              <div class="benefit-item">
                <Icon name="mdi:shield-check" class="w-5 h-5 text-green-600" />
                <span>Enhanced account security</span>
              </div>
              <div class="benefit-item">
                <Icon name="mdi:bank" class="w-5 h-5 text-blue-600" />
                <span>Access to all financial features</span>
              </div>
              <div class="benefit-item">
                <Icon name="mdi:account-check" class="w-5 h-5 text-purple-600" />
                <span>Verified account badge</span>
              </div>
            </div>
          </div>

          <div class="modal-actions">
            <button
                type="button"
                class="action-btn action-btn-secondary"
                @click="createModalOpen = false"
            >
              Maybe Later
            </button>
            <button
                type="button"
                class="action-btn action-btn-primary"
                @click="goToCreateForm"
            >
              <Icon name="mdi:shield-plus" class="w-4 h-4" />
              Start Verification
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Custom Toast System -->
    <CustomToast />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted, toRaw, onBeforeUnmount } from 'vue'
import { useSanctumFetch, useRuntimeConfig } from '#imports'
import GlobalLoader from '~/components/GlobalLoader.vue'
import CustomToast from '~/components/ui/Toast.vue'

definePageMeta({ layout: 'dashboard' })

/* GSAP imports (client-side only) */
let gsap: any = null

if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

/* Custom Toast System */
const toasts = ref<Array<{
  id: number
  type: 'success' | 'error' | 'info' | 'warning'
  title: string
  message: string
  duration?: number
}>>([])

let toastId = 1

const showToast = (type: 'success' | 'error' | 'info' | 'warning', title: string, message: string, duration = 5000) => {
  const id = toastId++
  toasts.value.push({ id, type, title, message, duration })

  setTimeout(() => {
    removeToast(id)
  }, duration)
}

const removeToast = (id: number) => {
  const index = toasts.value.findIndex(t => t.id === id)
  if (index > -1) {
    toasts.value.splice(index, 1)
  }
}

/* Loading */
const isLoading = useState('pageLoading', () => true)

/* Core state */
const config = useRuntimeConfig()
const processing = ref(false)
const isEditing = ref(false)
const hasExistingKyc = ref(false)
const createModalOpen = ref(false)

/* Simple ID counter for utility bills */
let utilityIdCounter = 1

/* Refs for animations */
const pageOrb1 = ref<HTMLElement>()
const pageOrb2 = ref<HTMLElement>()
const mainCard = ref<HTMLElement>()
const kycForm = ref<HTMLElement>()

let gsapContext: any = null

/* Tabs */
const tabs = [
  { key: 'aadhaar', label: 'Aadhaar', icon: 'mdi:card-account-details' },
  { key: 'pan', label: 'PAN', icon: 'mdi:credit-card' },
  { key: 'gst', label: 'GST', icon: 'mdi:receipt' },
  { key: 'utilities', label: 'Utilities', icon: 'mdi:home-lightning-bolt' }
]
const activeTab = ref<'aadhaar'|'pan'|'gst'|'utilities'>('aadhaar')

/* Options */
const utilityOptions = [
  { value: 'electricity', label: 'Electricity Bill' },
  { value: 'gas', label: 'Gas Bill' },
  { value: 'telephone', label: 'Telephone / Landline' },
  { value: 'water', label: 'Water Bill' },
  { value: 'other', label: 'Other Utility' }
]

/* Form */
const form = reactive({
  aadhaar: '',
  pan: '',
  aadhaarFile: null as File | null,
  panFile: null as File | null,
  hasTax: false,
  gst: '',
  gstFile: null as File | null,
  utility_bills: [] as Array<{ id: number, type: string, identifier: string }>,
})

/* Snapshot */
let initialSnapshot: any = null

/* Previews */
const preview = reactive({ aadhaar: '', pan: '', gst: '' })
function safeRevoke(url?: string) {
  try {
    if (url && url.startsWith('blob:')) URL.revokeObjectURL(url)
  } catch {}
}
onBeforeUnmount(() => {
  safeRevoke(preview.aadhaar)
  safeRevoke(preview.pan)
  safeRevoke(preview.gst)
})

/* Upload progress per field */
const uploadProgress = reactive({ aadhaar: 0, pan: 0, gst: 0 })
const aadhaarDragOver = ref(false)
const panDragOver = ref(false)
const gstDragOver = ref(false)

/* Drag & drop handlers */
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
  // Attach file
  if (type === 'aadhaar') form.aadhaarFile = file
  if (type === 'pan') form.panFile = file
  if (type === 'gst') form.gstFile = file

  // Preview for images (PDF won't preview as img)
  const isImage = file.type.startsWith('image/')
  const url = isImage ? URL.createObjectURL(file) : ''
  // Revoke previous
  safeRevoke(preview[type])
  preview[type] = url

  // Simulate progress locally
  uploadProgress[type] = 0
  const step = () => {
    if (uploadProgress[type] >= 100) return
    uploadProgress[type] += 10
    setTimeout(step, 60)
  }
  step()
}

/* Remove preview */
function removePreview(type: 'aadhaar'|'pan'|'gst') {
  if (!isEditing.value) return
  safeRevoke(preview[type])
  preview[type] = ''
  if (type === 'aadhaar') form.aadhaarFile = null
  if (type === 'pan') form.panFile = null
  if (type === 'gst') form.gstFile = null
  uploadProgress[type] = 0
}

/* Errors */
const errors = reactive<Record<string, string>>({})
function clearError(field: string) {
  errors[field] = ''
}

/* Utilities helpers */
function availableUtilityOptions(index: number) {
  const used = form.utility_bills.map((b, i) => (i === index ? null : b.type)).filter(Boolean) as string[]
  return utilityOptions.filter(o => !used.includes(o.value))
}

const utilityAddHint = computed(() => {
  const left = utilityOptions.length - form.utility_bills.length
  return left <= 0 ? 'All utility types added.' : `${left} types remaining`
})

function addUtility() {
  if (form.utility_bills.length < utilityOptions.length && isEditing.value) {
    form.utility_bills.push({ id: utilityIdCounter++, type: '', identifier: '' })
  }
}

function removeUtility(idx: number) {
  if (!isEditing.value) return
  form.utility_bills.splice(idx, 1)
  delete errors[`utility_${idx}`]
}

function onUtilityTypeChange(idx: number) {
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

/* Load existing */
async function loadExisting() {
  try {
    isLoading.value = true
    const res = await useSanctumFetch(`${config.public.apiBase}/account/kyc`)
    const k = res?.data ?? null
    hasExistingKyc.value = !!k

    if (k) {
      // Load existing data
      form.aadhaar = (k.aadhaar || '').replace(/\D/g, '')
      form.pan = (k.pan || '').toUpperCase()
      form.hasTax = !!k.has_tax
      form.gst = (k.gst || '').toUpperCase()
      if (k.aadhaarImage) {
        safeRevoke(preview.aadhaar)
        preview.aadhaar = k.aadhaarImage
      }
      if (k.panImage) {
        safeRevoke(preview.pan)
        preview.pan = k.panImage
      }
      if (k.gstImage) {
        safeRevoke(preview.gst)
        preview.gst = k.gstImage
      }
      if (Array.isArray(k.utility_bills)) {
        form.utility_bills = k.utility_bills.map((b: any, index: number) => ({
          id: index + 1,
          type: b.type || '',
          identifier: b.value || ''
        }))
        utilityIdCounter = form.utility_bills.length + 1
      }
      initialSnapshot = deepCloneForm()
      // Don't enable editing automatically for existing KYC
    } else {
      // No existing KYC - automatically enable editing for new users
      isEditing.value = true
      createModalOpen.value = true
    }
  } catch (e) {
    console.error('loadExisting KYC error', e)
    showToast('error', 'Load Error', 'Failed to load existing KYC data')
  } finally {
    isLoading.value = false
  }
}

/* Masking */
const maskedAadhaar = computed(() => {
  const v = (form.aadhaar || '').replace(/\D/g, '')
  return v.length === 12 ? `${v.slice(0,4)}-${'•'.repeat(4)}-${v.slice(8)}` : (v || '—')
})

const maskedPan = computed(() => {
  const v = (form.pan || '').toUpperCase().trim()
  return v.length === 10 ? `${v.slice(0,3)}${'•'.repeat(4)}${v.slice(7)}` : (v || '—')
})

const maskedGst = computed(() => {
  const v = (form.gst || '').toUpperCase().trim()
  return v.length === 15 ? `${v.slice(0,4)}${'•'.repeat(7)}${v.slice(11)}` : (v || '—')
})

/* Validation */
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
    errors.gst = 'GST number is required when GST is enabled.'
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
  const used = new Set<string>()
  form.utility_bills.forEach((row, i) => {
    if (!row.type && !row.identifier) {
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

function validateImages() {
  if (!form.aadhaarFile && !preview.aadhaar) {
    errors.aadhaar = (errors.aadhaar || '') + ' Image required.'
    return false
  }
  if (!form.panFile && !preview.pan) {
    errors.pan = (errors.pan || '') + ' Image required.'
    return false
  }
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
function getInputClasses(error = '') {
  const baseClasses = 'form-input w-full'
  return error ? `${baseClasses} form-input--error` : baseClasses
}

function getTabClasses(tabKey: string) {
  const baseClasses = 'tab-button'
  const isActive = activeTab.value === tabKey
  return isActive ? `${baseClasses} tab-button--active` : baseClasses
}

function getTabStatus(tabKey: string): 'complete' | 'incomplete' | null {
  switch (tabKey) {
    case 'aadhaar':
      return form.aadhaar && preview.aadhaar ? 'complete' : 'incomplete'
    case 'pan':
      return form.pan && preview.pan ? 'complete' : 'incomplete'
    case 'gst':
      return !form.hasTax ? null : (form.gst && preview.gst ? 'complete' : 'incomplete')
    case 'utilities':
      return form.utility_bills.length > 0 ? 'complete' : null
    default:
      return null
  }
}

function getTabStatusClasses(tabKey: string) {
  const status = getTabStatus(tabKey)
  if (!status) return ''
  return status === 'complete' ? 'tab-status--complete' : 'tab-status--incomplete'
}

function getActiveTabIcon() {
  const tab = tabs.find(t => t.key === activeTab.value)
  return tab?.icon || 'mdi:information'
}

function getActiveTabDescription() {
  const descriptions = {
    aadhaar: 'Enter your 12-digit Aadhaar number and upload a clear image or PDF document.',
    pan: 'Enter your PAN in uppercase format and upload a clear image or PDF document.',
    gst: 'Toggle GST registration. If enabled, provide your 15-character GSTIN and certificate.',
    utilities: 'Add utility bill identifiers for additional verification (no documents needed).'
  }
  return descriptions[activeTab.value] || ''
}

/* Edit controls */
function enableEdit() {
  isEditing.value = true
  Object.keys(errors).forEach(k => delete errors[k])
}

function cancelEdit() {
  if (initialSnapshot) applySnapshot(initialSnapshot)
  form.aadhaarFile = null
  form.panFile = null
  form.gstFile = null
  isEditing.value = false
}

function submitIfValid() {
  handleSubmit()
}

/* Submit */
async function handleSubmit() {
  if (!isEditing.value) return
  if (!validateAll()) {
    showToast('error', 'Validation Error', 'Please fix the highlighted errors before submitting.')
    return
  }

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

    const utilityPayload = form.utility_bills
        .filter(b => b.type && b.identifier && b.identifier.trim())
        .map(b => ({ type: b.type, value: b.identifier.trim() }))
    payload.append('utility_bills', JSON.stringify(utilityPayload))

    // FIXED: Proper method handling - no _method field for FormData
    const isUpdate = hasExistingKyc.value
    const method = isUpdate ? 'PUT' : 'POST'

    // For PUT requests with FormData, we need to add _method field
    if (isUpdate) {
      payload.append('_method', 'PUT')
    }

    const url = `${config.public.apiBase}/account/kyc`

    // Always use POST for FormData with files, Laravel will handle _method override
    const res = await useSanctumFetch(url, {
      method: 'POST', // Always POST for FormData with files
      body: payload,
      headers: {
        'Accept': 'application/json',
        // Don't set Content-Type, let browser set it for FormData
      }
    })

    const ok = res?.data?.success || res?.success || (res?.status === 'ok') || res?.message?.includes('success')
    if (ok) {
      showToast('success', 'Success!', `KYC ${isUpdate ? 'updated' : 'created'} successfully.`)
      await loadExisting()
      initialSnapshot = deepCloneForm()
      isEditing.value = false
      hasExistingKyc.value = true
      createModalOpen.value = false
    } else {
      const serverErrors = res?.data?.errors ?? res?.errors ?? null
      if (serverErrors && typeof serverErrors === 'object') {
        Object.entries(serverErrors).forEach(([k, v]) => {
          errors[k] = Array.isArray(v) ? (v as any).join(', ') : (v as any)
        })
        showToast('error', 'Validation Failed', 'Server validation failed. Please check highlighted fields.')
      } else {
        const message = res?.data?.message || res?.message || 'Unable to submit KYC. Please try again.'
        showToast('error', 'Submission Failed', message)
      }
    }
  } catch (err: any) {
    console.error('Submit error:', err)
    showToast('error', 'Error', err?.message || 'An error occurred during submission')
  } finally {
    processing.value = false
  }
}
/* Modal controls */
function goToCreateForm() {
  createModalOpen.value = false
  isEditing.value = true
}

/* Snapshot helpers */
function deepCloneForm() {
  return {
    aadhaar: form.aadhaar,
    pan: form.pan,
    hasTax: form.hasTax,
    gst: form.gst,
    utility_bills: form.utility_bills.map(b => ({
      id: b.id,
      type: b.type,
      identifier: b.identifier
    })),
    preview: { ...toRaw(preview) }
  }
}

function applySnapshot(s: any) {
  form.aadhaar = s.aadhaar || ''
  form.pan = s.pan || ''
  form.hasTax = !!s.hasTax
  form.gst = s.gst || ''
  form.utility_bills = Array.isArray(s.utility_bills)
      ? s.utility_bills.map((b: any) => ({
        id: b.id || utilityIdCounter++,
        type: b.type || '',
        identifier: b.identifier || ''
      }))
      : []
  preview.aadhaar = s.preview?.aadhaar || ''
  preview.pan = s.preview?.pan || ''
  preview.gst = s.preview?.gst || ''
}

/* Animations */
function initializeAnimations() {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
    // Floating orbs animation
    if (pageOrb1.value && pageOrb2.value) {
      gsap.to(pageOrb1.value, {
        x: -100,
        y: 100,
        rotation: 360,
        duration: 25,
        repeat: -1,
        yoyo: true,
        ease: 'none'
      })

      gsap.to(pageOrb2.value, {
        x: 100,
        y: -80,
        rotation: -360,
        duration: 30,
        repeat: -1,
        yoyo: true,
        ease: 'none'
      })
    }

    // Card entrance animation
    if (mainCard.value) {
      gsap.fromTo(mainCard.value,
          { opacity: 0, y: 50, scale: 0.95 },
          {
            opacity: 1,
            y: 0,
            scale: 1,
            duration: 0.8,
            ease: 'back.out(1.7)'
          }
      )
    }
  })
}

/* Lifecycle */
onMounted(() => {
  loadExisting()

  // Initialize animations
  setTimeout(() => {
    initializeAnimations()
  }, 100)
})

onUnmounted(() => {
  if (gsapContext) {
    gsapContext.kill()
  }
})

// Provide toast functions globally
provide('showToast', showToast)
provide('toasts', toasts)
provide('removeToast', removeToast)
</script>

<style scoped>
/* Same as before - all the existing styles remain */
/* Page Styles */
.kyc-page {
  min-height: 100vh;
  position: relative;
}

/* Header Section */
.header-section {
  position: relative;
  border-radius: 1rem 1rem 0 0;
}

.page-title {
  background: linear-gradient(135deg, #1f2937 0%, #3b82f6 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.status-badge {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* Action Buttons */
.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.75rem;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  transform-origin: center;
}

.action-btn:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.action-btn:active {
  transform: scale(0.98);
}

.action-btn-primary {
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  color: white;
}

.action-btn-primary:hover {
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
}

.action-btn-secondary {
  background-color: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.action-btn-secondary:hover {
  background-color: #e5e7eb;
}

.action-btn-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.action-btn-success:hover {
  background: linear-gradient(135deg, #047857 0%, #065f46 100%);
}

.action-btn-danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.action-btn-danger:hover {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
}

.action-btn-outline {
  background: transparent;
  color: #6b7280;
  border: 2px dashed #d1d5db;
}

.action-btn-outline:hover {
  color: #3b82f6;
  border-color: #3b82f6;
}

.action-btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
}

.action-btn-lg {
  padding: 0.75rem 1.5rem;
  font-size: 1.125rem;
}

/* Tabs */
.tabs-container {
  position: relative;
}

.tab-button {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-radius: 0.75rem;
  font-weight: 600;
  transition: all 0.3s ease;
  border: 1px solid #e5e7eb;
  background-color: #f9fafb;
  color: #6b7280;
}

.tab-button:hover {
  background-color: #f3f4f6;
  transform: translateY(-2px);
}

.tab-button--active {
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  color: white;
  border-color: transparent;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.tab-status {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
}

.tab-status--complete {
  background-color: #10b981;
  color: white;
}

.tab-status--incomplete {
  background-color: #f59e0b;
  color: white;
}

/* Form Sections */
.form-section {
  background: linear-gradient(135deg, rgba(255,255,255,0.5) 0%, rgba(249,250,251,0.5) 100%);
  border-radius: 1rem;
  padding: 2rem;
  border: 1px solid rgba(229, 231, 235, 0.5);
  backdrop-filter: blur(10px);
}

.section-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid rgba(229, 231, 235, 0.5);
}

.section-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-center: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.section-title {
  font-size: 1.25rem;
  font-weight: 800;
  color: #111827;
}

.section-subtitle {
  font-size: 0.875rem;
  color: #6b7280;
  margin-top: 0.25rem;
}

.section-badge {
  margin-left: auto;
  padding: 0.375rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.section-badge-required {
  background-color: #fee2e2;
  color: #dc2626;
}

.section-badge-optional {
  background-color: #dbeafe;
  color: #2563eb;
}

/* Form Groups */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.form-label {
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
}

.form-label-sm {
  font-weight: 600;
  color: #6b7280;
  font-size: 0.75rem;
}

.input-wrapper {
  position: relative;
}

.form-input {
  width: 100%;
  padding: 0.75rem 1rem;
  padding-right: 2.5rem;
  border-radius: 0.75rem;
  border: 1px solid #d1d5db;
  background-color: white;
  color: #111827;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input--error {
  border-color: #ef4444;
}

.form-input--error:focus {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.input-icon {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 1.25rem;
  height: 1.25rem;
  color: #9ca3af;
}

.form-hint {
  font-size: 0.75rem;
  color: #6b7280;
}

.form-error {
  font-size: 0.75rem;
  color: #ef4444;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.form-success {
  font-size: 0.75rem;
  color: #10b981;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

/* Upload Zones */
.upload-zone {
  position: relative;
  width: 100%;
  min-height: 12rem;
  border: 2px dashed #d1d5db;
  border-radius: 1rem;
  background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
  transition: all 0.3s ease;
  overflow: hidden;
}

.upload-zone--drag {
  border-color: #3b82f6;
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
}

.upload-zone--disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.upload-label {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  text-align: center;
  padding: 1.5rem;
}

.upload-text {
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.25rem;
}

.upload-hint {
  font-size: 0.75rem;
  color: #6b7280;
}

.upload-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 0.5rem;
  background-color: #e5e7eb;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6 0%, #8b5cf6 100%);
  transition: width 0.3s ease;
}

.upload-preview {
  position: absolute;
  inset: 0;
}

.preview-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 1rem;
}

.preview-overlay {
  position: absolute;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  opacity: 0;
  transition: opacity 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.group:hover .preview-overlay {
  opacity: 1;
}

.preview-remove {
  width: 2.5rem;
  height: 2.5rem;
  background-color: #ef4444;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.preview-remove:hover {
  background-color: #dc2626;
  transform: scale(1.1);
}

/* Toggle Switch */
.toggle-container {
  padding: 1rem;
  background-color: #f9fafb;
  border-radius: 0.75rem;
}

.toggle-wrapper {
  display: flex;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
}

.toggle-switch {
  position: relative;
  width: 2.75rem;
  height: 1.5rem;
  background-color: #d1d5db;
  border-radius: 9999px;
  transition: background-color 0.3s ease;
}

.toggle-slider {
  position: absolute;
  top: 0.125rem;
  left: 0.125rem;
  width: 1.25rem;
  height: 1.25rem;
  background-color: white;
  border-radius: 50%;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s ease;
}

.toggle-label {
  font-weight: 600;
  color: #374151;
}

/* Utilities */
.utility-row {
  padding: 1rem;
  background-color: white;
  border-radius: 0.75rem;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.utility-content {
  display: grid;
  grid-template-columns: 1fr 2fr auto;
  gap: 1rem;
  align-items: end;
}

@media (max-width: 768px) {
  .utility-content {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background-color: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
}

.modal-container {
  width: 100%;
  max-width: 32rem;
  background-color: white;
  border-radius: 1rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  border: 1px solid #e5e7eb;
  overflow: hidden;
}

.modal-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-icon {
  width: 4rem;
  height: 4rem;
  background: linear-gradient(135deg, #dbeafe 0%, #c7d2fe 100%);
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 800;
  color: #111827;
}

.modal-subtitle {
  font-size: 0.875rem;
  color: #6b7280;
}

.modal-body {
  padding: 1.5rem;
}

.kyc-benefits {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.benefit-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.modal-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  background-color: #f9fafb;
}

/* Transitions */
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
  .form-section {
    background: linear-gradient(135deg, rgba(17,24,39,0.5) 0%, rgba(31,41,55,0.5) 100%);
    border-color: rgba(75, 85, 99, 0.5);
  }

  .section-title {
    color: white;
  }

  .form-input {
    background-color: #374151;
    border-color: #4b5563;
    color: white;
  }

  .upload-zone {
    background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
    border-color: #6b7280;
  }

  .modal-container {
    background-color: #1f2937;
    border-color: #374151;
  }
}

/* Form Actions */
.form-actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (min-width: 1024px) {
  .form-actions {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
  }
}

.action-info p {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

/* Responsive */
@media (max-width: 768px) {
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .form-actions {
    align-items: stretch;
  }

  .action-btn-lg {
    width: 100%;
    justify-content: center;
  }
}
</style>
