<template>
  <!-- Floating Background Orbs -->
  <div class="fixed inset-0 pointer-events-none overflow-hidden">
    <div ref="orb1" class="absolute top-20 right-16 w-32 h-32 bg-gradient-to-r from-blue-400/10 to-cyan-400/10 dark:from-blue-400/5 dark:to-cyan-400/5 rounded-full blur-2xl opacity-60"></div>
    <div ref="orb2" class="absolute bottom-20 left-16 w-24 h-24 bg-gradient-to-r from-indigo-400/8 to-blue-400/8 dark:from-indigo-400/4 dark:to-blue-400/4 rounded-full blur-2xl opacity-40"></div>
  </div>

  <!-- Success Modal -->
  <Teleport to="body">
    <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="closeSuccessModal">
      <div class="w-full max-w-md mx-auto bg-white/95 dark:bg-slate-800/95 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-3xl">
        <!-- Success Header -->
        <div class="flex items-center gap-6 p-8 border-b border-blue-200/50 dark:border-blue-800/50">
          <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-cyan-600 rounded-3xl flex items-center justify-center shadow-2xl">
            <Icon name="mdi:email-check" class="w-8 h-8 text-white" />
          </div>
          <div>
            <h3 class="text-2xl font-bold text-blue-600 dark:text-blue-400">Export Initiated!</h3>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Check your email</p>
          </div>
        </div>

        <!-- Success Body -->
        <div class="p-8">
          <p class="text-slate-600 dark:text-slate-400 mb-8 leading-relaxed">
            Your data export has been initiated. You'll receive a PDF with all your account information at <span class="font-bold text-slate-900 dark:text-white">{{ user?.email }}</span> within the next few minutes.
          </p>

          <button
              @click="closeSuccessModal"
              class="w-full px-8 py-4 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white rounded-2xl font-bold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
          >
            Got it
          </button>
        </div>
      </div>
    </div>
  </Teleport>

  <div class="relative z-10 bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
    <!-- Header Background Gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-cyan-500/5 to-indigo-500/5 opacity-70"></div>

    <div class="relative z-10 p-8">
      <div class="flex items-center gap-6 mb-8">
        <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-cyan-600 rounded-2xl flex items-center justify-center shadow-lg hover:scale-105 transition-transform duration-300">
          <Icon name="mdi:download-box" class="w-6 h-6 text-white" />
        </div>
        <div>
          <h2 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">Export Your Data</h2>
          <div class="w-20 h-1 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-full mt-2"></div>
        </div>
      </div>

      <!-- Email Not Verified State -->
      <div v-if="!canExportData" class="space-y-6">
        <div class="bg-amber-50/80 dark:bg-amber-900/20 border-2 border-amber-200/60 dark:border-amber-800/60 rounded-2xl p-8 backdrop-blur-sm">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-amber-100/80 dark:bg-amber-900/50 rounded-2xl flex items-center justify-center">
              <Icon name="mdi:email-alert" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-amber-700 dark:text-amber-300">Email Verification Required</h3>
              <p class="text-amber-600 dark:text-amber-400 mt-1">To export your data, you need a verified email address</p>
            </div>
          </div>

          <div class="space-y-4 text-sm text-amber-600 dark:text-amber-400">
            <div class="flex items-center gap-3">
              <Icon name="mdi:shield-check" class="w-5 h-5" />
              <span>Secure delivery via encrypted email attachment</span>
            </div>
            <div class="flex items-center gap-3">
              <Icon name="mdi:file-pdf-box" class="w-5 h-5" />
              <span>Complete account data in PDF format</span>
            </div>
            <div class="flex items-center gap-3">
              <Icon name="mdi:clock-outline" class="w-5 h-5" />
              <span>Processing time: 5-10 minutes</span>
            </div>
          </div>
        </div>

        <div class="bg-blue-50/80 dark:bg-blue-900/20 border border-blue-200/60 dark:border-blue-800/60 rounded-2xl p-6 backdrop-blur-sm">
          <h4 class="font-bold text-blue-700 dark:text-blue-300 mb-3 flex items-center gap-2">
            <Icon name="mdi:information" class="w-5 h-5" />
            Next Steps
          </h4>
          <ol class="space-y-2 text-sm text-blue-600 dark:text-blue-400">
            <li class="flex items-start gap-2">
              <span class="w-6 h-6 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center text-xs font-bold text-blue-600 dark:text-blue-300 flex-shrink-0 mt-0.5">1</span>
              <span>Add and verify your email address in Account Security section</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="w-6 h-6 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center text-xs font-bold text-blue-600 dark:text-blue-300 flex-shrink-0 mt-0.5">2</span>
              <span>Return to this page to initiate your data export</span>
            </li>
          </ol>
        </div>

        <div class="flex justify-end">
          <NuxtLink
              to="#security-section"
              @click="scrollToSecurity"
              class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white rounded-2xl font-bold shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 group"
          >
            <Icon name="mdi:email-plus" class="w-6 h-6 group-hover:scale-110 transition-transform duration-300" />
            <span>Add Email Address</span>
          </NuxtLink>
        </div>
      </div>

      <!-- Email Verified - Ready to Export State -->
      <div v-else class="space-y-6">
        <div class="bg-green-50/80 dark:bg-green-900/20 border-2 border-green-200/60 dark:border-green-800/60 rounded-2xl p-8 backdrop-blur-sm">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-green-100/80 dark:bg-green-900/50 rounded-2xl flex items-center justify-center">
              <Icon name="mdi:shield-check" class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-green-700 dark:text-green-300">Ready for Export</h3>
              <p class="text-green-600 dark:text-green-400 mt-1">Your verified email: {{ user?.email }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="space-y-3">
              <h4 class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                <Icon name="mdi:database" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                Your Data Includes
              </h4>
              <div class="space-y-2 text-sm text-slate-600 dark:text-slate-400">
                <div class="flex items-center gap-2">
                  <Icon name="mdi:check-circle" class="w-4 h-4 text-green-500" />
                  <span>Personal profile information</span>
                </div>
                <div class="flex items-center gap-2">
                  <Icon name="mdi:check-circle" class="w-4 h-4 text-green-500" />
                  <span>Account activity logs</span>
                </div>
                <div class="flex items-center gap-2">
                  <Icon name="mdi:check-circle" class="w-4 h-4 text-green-500" />
                  <span>Transaction history</span>
                </div>
                <div class="flex items-center gap-2">
                  <Icon name="mdi:check-circle" class="w-4 h-4 text-green-500" />
                  <span>Security settings</span>
                </div>
              </div>
            </div>

            <div class="space-y-3">
              <h4 class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                <Icon name="mdi:security" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                Security Features
              </h4>
              <div class="space-y-2 text-sm text-slate-600 dark:text-slate-400">
                <div class="flex items-center gap-2">
                  <Icon name="mdi:check-circle" class="w-4 h-4 text-green-500" />
                  <span>Password-protected PDF</span>
                </div>
                <div class="flex items-center gap-2">
                  <Icon name="mdi:check-circle" class="w-4 h-4 text-green-500" />
                  <span>Encrypted email delivery</span>
                </div>
                <div class="flex items-center gap-2">
                  <Icon name="mdi:check-circle" class="w-4 h-4 text-green-500" />
                  <span>One-time access link</span>
                </div>
                <div class="flex items-center gap-2">
                  <Icon name="mdi:check-circle" class="w-4 h-4 text-green-500" />
                  <span>Auto-expires in 24 hours</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Export Progress -->
        <div v-if="isExporting" class="bg-blue-50/80 dark:bg-blue-900/20 border border-blue-200/60 dark:border-blue-800/60 rounded-2xl p-8 backdrop-blur-sm">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-blue-100/80 dark:bg-blue-900/50 rounded-2xl flex items-center justify-center">
              <Icon name="mdi:loading" class="w-6 h-6 text-blue-600 dark:text-blue-400 animate-spin" />
            </div>
            <div>
              <h4 class="text-xl font-bold text-blue-700 dark:text-blue-300">Processing Your Export</h4>
              <p class="text-blue-600 dark:text-blue-400 mt-1">{{ exportStatus }}</p>
            </div>
          </div>

          <!-- Progress Bar -->
          <div class="w-full bg-blue-200/50 dark:bg-blue-800/50 rounded-full h-3 overflow-hidden">
            <div
                class="h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full transition-all duration-500 ease-out"
                :style="{ width: exportProgress + '%' }"
            ></div>
          </div>
          <div class="text-center mt-3 text-sm font-bold text-blue-600 dark:text-blue-400">
            {{ exportProgress }}%
          </div>
        </div>

        <!-- Export Button -->
        <div v-if="!isExporting" class="flex justify-center">
          <button
              @click="initiateExport"
              :disabled="isExporting"
              class="inline-flex items-center gap-3 px-12 py-5 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-bold shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 group text-lg"
          >
            <Icon name="mdi:download" class="w-7 h-7 group-hover:scale-110 transition-transform duration-300" />
            <span>Export My Data</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRuntimeConfig, useSanctumFetch, useSanctum } from '#imports'

// GSAP imports (client-side only)
let gsap: any = null
if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// Composables
const { $toast } = useNuxtApp()
const config = useRuntimeConfig()
const { user } = useSanctum()

// Emits
const emit = defineEmits<{
  success: []
}>()

// Refs for animations
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()

// State
const isExporting = ref(false)
const showSuccessModal = ref(false)
const exportProgress = ref(0)
const exportStatus = ref('Preparing your data...')

// Computed
const canExportData = computed(() => {
  return user.value?.email && user.value?.email_verified
})

// Lifecycle
onMounted(() => {
  // Initialize GSAP animations
  if (process.client && gsap) {
    gsap.to([orb1.value, orb2.value], {
      rotation: 360,
      duration: 30,
      repeat: -1,
      ease: 'none'
    })
  }
})

// Methods
async function initiateExport() {
  if (!canExportData.value) {
    $toast.error({
      title: 'Export Not Available',
      message: 'Please verify your email address first to export your data.'
    })
    return
  }

  isExporting.value = true
  exportProgress.value = 0
  exportStatus.value = 'Preparing your data...'

  try {
    // Simulate progress for better UX
    const progressSteps = [
      { progress: 20, status: 'Collecting profile information...' },
      { progress: 40, status: 'Gathering transaction history...' },
      { progress: 60, status: 'Compiling security logs...' },
      { progress: 80, status: 'Generating PDF document...' },
      { progress: 90, status: 'Encrypting data...' },
      { progress: 100, status: 'Sending to your email...' }
    ]

    for (const step of progressSteps) {
      await new Promise(resolve => setTimeout(resolve, 800))
      exportProgress.value = step.progress
      exportStatus.value = step.status
    }

    // Make actual API call
    const response = await useSanctumFetch(`${config.public.apiBase}/account/export-data`, {
      method: 'POST'
    })

    if (response.success) {
      showSuccessModal.value = true
      $toast.success({
        title: 'Export Started!',
        message: response.message || 'Your data export has been initiated. Check your email.'
      })
    }
  } catch (err: any) {
    $toast.error({
      title: 'Export Failed',
      message: err?.data?.message || 'Failed to initiate data export. Please try again.'
    })
  } finally {
    isExporting.value = false
    exportProgress.value = 0
  }
}

function scrollToSecurity() {
  // Emit event to parent to scroll to security section
  emit('scrollToSecurity')
}

function closeSuccessModal() {
  showSuccessModal.value = false
  emit('success')
}
</script>
