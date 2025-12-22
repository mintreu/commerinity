<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-gray-900 dark:to-purple-950 text-gray-900 dark:text-gray-100 p-4 sm:p-6 lg:p-12">
    <!-- Loader -->
    <div v-if="isLoading" class="flex items-center justify-center h-screen w-full">
      <div class="flex flex-col items-center space-y-4">
        <svg class="animate-spin h-16 w-16 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-label="Loading spinner">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
        </svg>
        <span class="text-xl font-semibold tracking-wide text-gray-700 dark:text-gray-300">Loading application details...</span>
      </div>
    </div>

    <div v-else class="max-w-screen-xl mx-auto space-y-8">
      <!-- Page Header -->
      <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-3xl shadow-2xl p-8 text-white">
        <div class="flex items-center mb-4">
          <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mr-4">
            <Icon name="mdi:file-document-check" class="w-8 h-8 text-white" />
          </div>
          <div>
            <h1 class="text-3xl font-black">Application Details</h1>
            <p class="text-indigo-100">Track your application status and payment information</p>
          </div>
        </div>
        <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full border border-white/30">
          <Icon name="mdi:identifier" class="w-4 h-4 mr-2" />
          <span class="font-mono text-sm">{{ app.uuid || 'N/A' }}</span>
        </div>
      </div>

      <!-- Split panels -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Application Card -->
        <section class="application-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/50 dark:border-gray-700/50 transform hover:scale-[1.02] transition-all duration-300">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center mr-4">
              <Icon name="mdi:clipboard-text" class="w-6 h-6 text-white" />
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Application</h2>
          </div>

          <dl class="space-y-4 text-base">
            <div class="detail-row flex items-start justify-between gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
              <dt class="font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                <Icon name="mdi:identifier" class="w-4 h-4 mr-2 text-indigo-500" />
                Application ID
              </dt>
              <dd class="text-right font-mono break-all text-indigo-600 dark:text-indigo-400 font-bold">
                {{ app.uuid || 'N/A' }}
              </dd>
            </div>

            <div class="detail-row flex items-start justify-between gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
              <dt class="font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                <Icon name="mdi:calendar-check" class="w-4 h-4 mr-2 text-green-500" />
                Submitted On
              </dt>
              <dd class="text-right font-medium">{{ formatDate(app.submit_on) }}</dd>
            </div>

            <div class="detail-row flex items-start justify-between gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
              <dt class="font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                <Icon name="mdi:account" class="w-4 h-4 mr-2 text-purple-500" />
                Reference Name
              </dt>
              <dd class="text-right break-words font-medium">{{ app.reference_name || '—' }}</dd>
            </div>

            <div class="detail-row flex items-start justify-between gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
              <dt class="font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                <Icon name="mdi:phone" class="w-4 h-4 mr-2 text-blue-500" />
                Reference Contact
              </dt>
              <dd class="text-right break-words font-medium">{{ app.reference_contact || '—' }}</dd>
            </div>

            <div class="detail-row flex items-start justify-between gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
              <dt class="font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                <Icon name="mdi:flag" class="w-4 h-4 mr-2 text-orange-500" />
                Status
              </dt>
              <dd class="text-right">
                <span class="px-4 py-2 rounded-full text-sm font-bold" :class="statusBadgeClass(app.status)">
                  {{ app.status || 'N/A' }}
                </span>
              </dd>
            </div>
          </dl>
        </section>

        <!-- Transaction Card -->
        <section class="transaction-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/50 dark:border-gray-700/50 transform hover:scale-[1.02] transition-all duration-300">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center mr-4">
              <Icon name="mdi:cash-multiple" class="w-6 h-6 text-white" />
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Transaction</h2>
          </div>

          <div v-if="tx" class="space-y-4 text-base">
            <!-- Receipt/Transaction UUID -->
            <div class="detail-row flex items-start justify-between gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
              <span class="font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                <Icon name="mdi:receipt" class="w-4 h-4 mr-2 text-indigo-500" />
                Receipt
              </span>
              <span class="text-right font-mono break-all text-indigo-600 dark:text-indigo-400 font-bold text-sm">
                {{ tx.uuid || 'N/A' }}
              </span>
            </div>

            <div class="detail-row flex items-start justify-between gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
              <span class="font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                <Icon name="mdi:currency-inr" class="w-4 h-4 mr-2 text-green-500" />
                Fees
              </span>
              <span class="text-right font-bold text-xl text-green-600 dark:text-green-400">
                {{ tx.amount }}
              </span>
            </div>

            <div class="detail-row flex items-start justify-between gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
              <span class="font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                <Icon name="mdi:shield-check" class="w-4 h-4 mr-2 text-blue-500" />
                Payment Status
              </span>
              <span class="text-right">
                <span v-if="!!tx.verified" class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full text-sm font-bold flex items-center">
                  <Icon name="mdi:check-circle" class="w-4 h-4 mr-1" />
                  Verified
                </span>
                <span v-else class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 rounded-full text-sm font-bold flex items-center">
                  <Icon name="mdi:clock-alert" class="w-4 h-4 mr-1" />
                  Pending
                </span>
              </span>
            </div>

            <div class="detail-row flex items-start justify-between gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
              <span class="font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                <Icon name="mdi:information" class="w-4 h-4 mr-2 text-indigo-500" />
                Status
              </span>
              <span class="text-right">
                <span class="px-4 py-2 rounded-full text-sm font-bold" :class="statusBadgeClass(tx.status)">
                  {{ tx.status || 'N/A' }}
                </span>
              </span>
            </div>

            <!-- Expires At - Only show when NOT verified/paid -->
            <div v-if="!tx.verified" class="detail-row flex items-start justify-between gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
              <span class="font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                <Icon name="mdi:clock-alert-outline" class="w-4 h-4 mr-2 text-red-500" />
                Expires At
              </span>
              <span class="text-right font-medium text-red-600 dark:text-red-400">
                {{ formatDate(tx.expire_at) }}
              </span>
            </div>

            <div v-if="tx.integration" class="detail-row flex items-start justify-between gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
              <span class="font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                <Icon name="mdi:bank" class="w-4 h-4 mr-2 text-purple-500" />
                Payment Gateway
              </span>
              <span class="text-right font-medium">{{ tx.integration }}</span>
            </div>

            <!-- Pay Now CTA only when not verified and not expired -->
            <div v-if="showPayNow" class="pt-4 border-t border-gray-200 dark:border-gray-700">
              <a
                  :href="payNowUrl"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="w-full flex items-center justify-center px-6 py-4 text-center bg-gradient-to-r from-green-500 to-emerald-500 text-white font-bold text-lg rounded-2xl shadow-xl hover:shadow-green-500/25 transition-all duration-300 transform hover:scale-105"
              >
                <Icon name="mdi:credit-card" class="w-5 h-5 mr-2" />
                Pay Now
                <Icon name="mdi:arrow-right" class="w-5 h-5 ml-2" />
              </a>
              <p class="mt-3 text-xs text-gray-600 dark:text-gray-400 text-center leading-relaxed">
                Complete payment before expiry to finalize your application.
              </p>
            </div>

            <div v-else-if="tx.verified" class="pt-4 border-t border-gray-200 dark:border-gray-700">
              <div class="flex items-center justify-center text-green-600 dark:text-green-400 font-semibold">
                <Icon name="mdi:check-circle" class="w-5 h-5 mr-2" />
                Payment Completed Successfully
              </div>
            </div>

            <div v-else class="pt-4 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400 text-center">
              <Icon name="mdi:information-outline" class="w-5 h-5 mx-auto mb-2 text-gray-400" />
              No payment action required at this time.
            </div>
          </div>

          <div v-else class="text-center py-8">
            <Icon name="mdi:cash-remove" class="w-16 h-16 mx-auto mb-4 text-gray-400" />
            <p class="text-gray-500 dark:text-gray-400 font-medium">No transaction data available</p>
          </div>
        </section>
      </div>

      <!-- Application Updates Section -->
      <section class="updates-section bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/50 dark:border-gray-700/50 transform hover:scale-[1.01] transition-all duration-300">
        <div class="flex items-center mb-6">
          <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-2xl flex items-center justify-center mr-4">
            <Icon name="mdi:bell-ring" class="w-6 h-6 text-white" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Application Updates</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">Important information and next steps</p>
          </div>
        </div>

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-200 dark:border-blue-800">
          <Icon name="mdi:information" class="w-4 h-4 inline mr-2 text-blue-500" />
          This section includes detailed instructions, timelines, or next steps regarding selection, interviews, onboarding, or required documents.
        </p>

        <!-- Read-only textarea-style display -->
        <div class="relative">
          <div class="absolute -top-3 left-4 bg-white dark:bg-gray-800 px-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400 flex items-center">
            <Icon name="mdi:message-text" class="w-3 h-3 mr-1" />
            Status Information
          </div>
          <textarea
              class="w-full min-h-[180px] rounded-2xl border-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 p-4 resize-y font-medium leading-relaxed focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/20 transition-all duration-300"
              :value="app.status_feedback || 'No updates available at the moment. We will notify you once there are any changes to your application status.'"
              readonly
          ></textarea>
        </div>
      </section>

      <!-- Additional Details Section -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Education -->
        <div v-if="app.educations && app.educations.length" class="details-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-xl p-6 border border-white/50 dark:border-gray-700/50">
          <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center mr-3">
              <Icon name="mdi:school" class="w-5 h-5 text-white" />
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Education</h3>
          </div>
          <div class="space-y-3">
            <div v-for="(edu, idx) in app.educations" :key="idx" class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
              <p class="font-semibold text-gray-900 dark:text-white">{{ edu.degree }}</p>
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ edu.institution }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Year: {{ edu.year }}</p>
            </div>
          </div>
        </div>

        <!-- Skills -->
        <div v-if="app.skills && app.skills.length" class="details-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-xl p-6 border border-white/50 dark:border-gray-700/50">
          <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center mr-3">
              <Icon name="mdi:brain" class="w-5 h-5 text-white" />
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Skills</h3>
          </div>
          <div class="flex flex-wrap gap-2">
            <span v-for="(skill, idx) in app.skills" :key="idx"
                  class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full text-sm font-semibold">
              {{ skill.skill }}
            </span>
          </div>
        </div>

        <!-- Guardian -->
        <div v-if="app.guardian" class="details-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-xl p-6 border border-white/50 dark:border-gray-700/50">
          <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center mr-3">
              <Icon name="mdi:account-supervisor" class="w-5 h-5 text-white" />
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Guardian</h3>
          </div>
          <p class="text-gray-700 dark:text-gray-300 font-medium">{{ app.guardian }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRuntimeConfig, useSanctumFetch, useToast } from '#imports'

definePageMeta({ layout: 'dashboard' })

const isLoading = ref(false)
const application = ref<any>({})

const config = useRuntimeConfig()
const route = useRoute()
const toast = useToast()
const uuid = route.params.url

onMounted(async () => {
  isLoading.value = true
  try {
    await fetchApplication()
  } catch (e) {
    toast.error({ title: 'Error', message: '❌ Failed to load application details.' })
    console.error(e)
  } finally {
    isLoading.value = false
  }
})

async function fetchApplication() {
  const url = `${config.public.apiBase}/account/applications/${uuid}`
  const res: any = await useSanctumFetch(url, { method: 'GET' })
  if (res?.data) application.value = res.data
  else throw new Error('No data received')
}

// Shorthand refs
const app = computed(() => application.value || {})
const tx = computed(() => application.value?.transaction || null)

// Helpers
function formatDate(dateStr: string | null | undefined) {
  if (!dateStr) return 'N/A'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return 'N/A'
  return d.toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function statusBadgeClass(status: string | null | undefined) {
  if (!status) return 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
  const lower = status.toLowerCase()
  if (lower.includes('paid') || lower.includes('success') || lower.includes('completed') || lower.includes('verified')) {
    return 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'
  }
  if (lower.includes('pending') || lower.includes('await') || lower.includes('submitted')) {
    return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300'
  }
  if (lower.includes('fail') || lower.includes('reject') || lower.includes('declined') || lower.includes('cancel')) {
    return 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'
  }
  return 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300'
}

// Pay Now visibility: only when not verified and not expired
const showPayNow = computed(() => {
  if (!tx.value) return false
  const verified = !!tx.value.verified
  if (verified) return false
  const expireAt = tx.value.expire_at ? new Date(tx.value.expire_at) : null
  const now = new Date()
  return !!expireAt && expireAt > now
})

// Preferred Pay URL: if provider link exists, use it; else fallback
const payNowUrl = computed(() => {
  if (!tx.value) return '#'
  return tx.value.provider_gen_link || tx.value.success_redirect_url || `${config.public.webBase}/checkout/${tx.value.uuid}`
})
</script>

<style scoped>
/* Enhanced Animations */
.application-card,
.transaction-card,
.updates-section,
.details-card {
  position: relative;
  overflow: hidden;
}

.application-card::before,
.transaction-card::before,
.updates-section::before,
.details-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
  transition: left 0.8s;
  pointer-events: none;
}

.application-card:hover::before,
.transaction-card:hover::before,
.updates-section:hover::before,
.details-card:hover::before {
  left: 100%;
}

/* Detail Row Hover Effect */
.detail-row {
  position: relative;
}

.detail-row::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  height: 100%;
  width: 3px;
  background: linear-gradient(180deg, #6366f1, #8b5cf6);
  transform: scaleY(0);
  transition: transform 0.3s ease;
  border-radius: 0 4px 4px 0;
}

.detail-row:hover::before {
  transform: scaleY(1);
}

/* Textarea styling */
textarea {
  scrollbar-width: thin;
  scrollbar-color: #6366f1 #e5e7eb;
}

textarea::-webkit-scrollbar {
  width: 8px;
}

textarea::-webkit-scrollbar-track {
  background: #e5e7eb;
  border-radius: 4px;
}

textarea::-webkit-scrollbar-thumb {
  background: #6366f1;
  border-radius: 4px;
}

textarea::-webkit-scrollbar-thumb:hover {
  background: #4f46e5;
}

/* Dark mode scrollbar */
.dark textarea::-webkit-scrollbar-track {
  background: #374151;
}

.dark textarea::-webkit-scrollbar-thumb {
  background: #6366f1;
}

/* Mobile Responsiveness */
@media (max-width: 640px) {
  .detail-row {
    flex-direction: column;
    align-items: flex-start;
  }

  .detail-row dd,
  .detail-row span:last-child {
    text-align: left;
    margin-top: 0.5rem;
  }
}

/* Smooth transitions */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>
