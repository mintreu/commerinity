<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 p-4 sm:p-6 lg:p-12">
    <!-- Loader -->
    <div v-if="isLoading" class="flex items-center justify-center h-screen w-full">
      <svg class="animate-spin h-12 w-12 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-label="Loading spinner">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
      </svg>
      <span class="ml-4 text-xl font-semibold tracking-wide">Loading application details...</span>
    </div>

    <div v-else class="max-w-screen-xl mx-auto space-y-8">
      <!-- Split panels -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Application Minimal -->
        <section class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-200 dark:border-gray-700">
          <h2 class="text-2xl font-bold mb-4">Application</h2>
          <dl class="space-y-3 text-base">
            <div class="flex items-start justify-between gap-4">
              <dt class="font-semibold text-gray-700 dark:text-gray-300">Application ID</dt>
              <dd class="text-right font-mono break-all text-indigo-600 dark:text-indigo-400">{{ app.uuid || 'N/A' }}</dd>
            </div>

            <div class="flex items-start justify-between gap-4">
              <dt class="font-semibold text-gray-700 dark:text-gray-300">Submitted On</dt>
              <dd class="text-right">{{ formatDate(app.submit_on) }}</dd>
            </div>

            <div class="flex items-start justify-between gap-4">
              <dt class="font-semibold text-gray-700 dark:text-gray-300">Reference Name</dt>
              <dd class="text-right break-words">{{ app.reference_name || '—' }}</dd>
            </div>

            <div class="flex items-start justify-between gap-4">
              <dt class="font-semibold text-gray-700 dark:text-gray-300">Reference Contact</dt>
              <dd class="text-right break-words">{{ app.reference_contact || '—' }}</dd>
            </div>

            <div class="flex items-start justify-between gap-4">
              <dt class="font-semibold text-gray-700 dark:text-gray-300">Status</dt>
              <dd class="text-right" :class="statusColorClass(app.status)">{{ app.status || 'N/A' }}</dd>
            </div>
          </dl>
        </section>

        <!-- Transaction Minimal -->
        <section class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-200 dark:border-gray-700">
          <h2 class="text-2xl font-bold mb-4">Transaction</h2>

          <div v-if="tx" class="space-y-3 text-base">
            <div class="flex items-start justify-between gap-4">
              <span class="font-semibold text-gray-700 dark:text-gray-300">Fees</span>
              <span class="text-right font-semibold">{{ formatAmount(tx.amount) }}</span>
            </div>

            <div class="flex items-start justify-between gap-4">
              <span class="font-semibold text-gray-700 dark:text-gray-300">Verified</span>
              <span class="text-right">
                <span v-if="!!tx.verified" class="text-green-600 dark:text-green-400 font-semibold">Yes</span>
                <span v-else class="text-yellow-600 dark:text-yellow-400 font-semibold">No</span>
              </span>
            </div>

            <div class="flex items-start justify-between gap-4">
              <span class="font-semibold text-gray-700 dark:text-gray-300">Status</span>
              <span class="text-right" :class="statusColorClass(tx.status)">{{ tx.status || 'N/A' }}</span>
            </div>

            <div class="flex items-start justify-between gap-4">
              <span class="font-semibold text-gray-700 dark:text-gray-300">Expires At</span>
              <span class="text-right">{{ formatDate(tx.expire_at) }}</span>
            </div>

            <!-- Pay Now CTA only when not verified and not expired -->
            <div v-if="showPayNow" class="pt-2">
              <a
                  :href="payNowUrl"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="w-full block px-5 py-3 text-center bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700 transition"
              >
                Pay Now
              </a>
              <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 text-center">
                Complete payment before expiry to finalize the application.
              </p>
            </div>

            <div v-else class="pt-1 text-sm text-gray-500 dark:text-gray-400 text-center">
              No payment action required.
            </div>
          </div>

          <div v-else class="text-center text-gray-500 dark:text-gray-400">
            No transaction data available.
          </div>
        </section>
      </div>

      <!-- Application Situation & Updates (Status Feedback) -->
      <section class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-2xl font-bold mb-3">Application Updates</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
          This section may include detailed instructions, timelines, or next steps regarding selection, interviews, onboarding, or required documents.
        </p>

        <!-- Read-only textarea-style display -->
        <div class="relative">
          <textarea
              class="w-full min-h-[140px] rounded-md border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 p-3 resize-y"
              :value="app.status_feedback || 'No updates available at the moment.'"
              readonly
          ></textarea>
          <span class="absolute -top-3 left-2 bg-white dark:bg-gray-800 px-1 text-xs text-gray-500 dark:text-gray-400">Status Feedback</span>
        </div>
      </section>
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
    toast.error({ title: 'Failed to load application details.' })
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
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}

function formatAmount(amount: number | null | undefined) {
  if (amount == null) return '₹0'
  try {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 0 }).format(amount)
  } catch {
    return `₹${amount}`
  }
}

function statusColorClass(status: string | null | undefined) {
  if (!status) return 'text-gray-500'
  const lower = status.toLowerCase()
  if (lower.includes('paid') || lower.includes('success') || lower.includes('completed')) return 'text-green-600 dark:text-green-400 font-semibold'
  if (lower.includes('pending') || lower.includes('await')) return 'text-yellow-600 dark:text-yellow-400 font-semibold'
  if (lower.includes('fail') || lower.includes('reject') || lower.includes('declined') || lower.includes('cancel')) return 'text-red-600 dark:text-red-400 font-semibold'
  return 'text-gray-600 dark:text-gray-400'
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
/* Tailwind only */
</style>
