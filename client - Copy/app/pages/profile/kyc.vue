<script setup lang="ts">
/**
 * KYC (Know Your Customer) Page - Premium Mintreu Design
 * Only Personal KYC mode with individual image upload fields for PAN, Aadhaar, and GST.
 * Simplified for Nuxt frontend, leaving complex cases for the Admin panel.
 */

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const toast = useToast()
const config = useRuntimeConfig()

// State
const loading = ref(true)
const submitting = ref(false)
const kycStatus = ref<any>(null)
const hasKyc = ref(false)

// Form data - Simplified to Personal only
const formData = ref({
  pan_number: '',
  aadhaar_number: '',
  gst_number: ''
})

// Use specific file refs for each field
const panFile = ref<File | null>(null)
const aadhaarFile = ref<File | null>(null)
const gstFile = ref<File | null>(null)
const otherDocuments = ref<File[]>([])

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

    if (kycStatus.value && kycStatus.value.status === 'rejected') {
      formData.value.pan_number = kycStatus.value.pan_number || ''
      formData.value.aadhaar_number = kycStatus.value.aadhaar_number || ''
      formData.value.gst_number = kycStatus.value.gst_number || ''
    }
  } catch (e: any) {
    console.error('Failed to fetch KYC status:', e)
  } finally {
    loading.value = false
  }
}

const handleFileSelect = (event: Event, type: 'pan' | 'aadhaar' | 'gst' | 'other') => {
  const target = event.target as HTMLInputElement
  if (target.files?.length) {
    const file = target.files[0]
    if (file.size > 5 * 1024 * 1024) {
      toast.add({ title: 'File too large', description: 'Maximum file size is 5MB', color: 'error' })
      return
    }

    if (type === 'pan') panFile.value = file
    else if (type === 'aadhaar') aadhaarFile.value = file
    else if (type === 'gst') gstFile.value = file
    else otherDocuments.value.push(file)
  }
}

const submitKyc = async () => {
  if (!formData.value.pan_number) return

  submitting.value = true
  try {
    const formPayload = new FormData()
    formPayload.append('kyc_type', 'personal')
    formPayload.append('pan_number', formData.value.pan_number.toUpperCase())

    if (formData.value.aadhaar_number) {
      formPayload.append('aadhaar_number', formData.value.aadhaar_number)
    }

    if (formData.value.gst_number) {
      formPayload.append('gst_number', formData.value.gst_number.toUpperCase())
    }

    // Append specific images
    if (panFile.value) formPayload.append('pan_image', panFile.value)
    if (aadhaarFile.value) formPayload.append('aadhaar_image', aadhaarFile.value)
    if (gstFile.value) formPayload.append('gst_image', gstFile.value)

    // Append other documents if any
    otherDocuments.value.forEach((doc, index) => {
      formPayload.append(`documents[${index}]`, doc)
    })

    const endpoint = kycStatus.value?.uuid
      ? `${config.public.apiBase}/api/kyc/${kycStatus.value.uuid}/resubmit`
      : `${config.public.apiBase}/api/kyc/submit`

    const response = await useSanctumFetch<any>(endpoint, {
      method: 'POST',
      body: formPayload
    })

    toast.add({
      title: 'Success',
      description: response.message || 'Identity verification submitted.',
      color: 'success',
      icon: 'i-lucide-shield-check'
    })

    await fetchKycStatus()
  } catch (e: any) {
    toast.add({
      title: 'Submission Error',
      description: e.data?.message || 'Verification failed. Please check required fields.',
      color: 'error'
    })
  } finally {
    submitting.value = false
  }
}

const statusColor = computed(() => {
  switch (kycStatus.value?.status) {
    case 'verified': return 'success'
    case 'pending': return 'warning'
    case 'rejected': return 'error'
    default: return 'neutral'
  }
})

const statusIcon = computed(() => {
  switch (kycStatus.value?.status) {
    case 'verified': return 'i-lucide-check-circle'
    case 'pending': return 'i-lucide-clock'
    case 'rejected': return 'i-lucide-shield-x'
    default: return 'i-lucide-shield'
  }
})

// Dynamic class bindings - computed to avoid Vue template parsing issues with Tailwind
const statusHeaderBg = computed(() => `bg-${statusColor.value}-500/5`)
const statusIconBg = computed(() => `bg-${statusColor.value}-500/10`)
const statusIconColor = computed(() => `text-${statusColor.value}-500`)
const statusIconShadow = computed(() => `shadow-${statusColor.value}-500/20`)
const statusTitleColor = computed(() => `text-${statusColor.value}-600 dark:text-${statusColor.value}-400`)
const statusDivider = computed(() => `text-${statusColor.value}-500`)
</script>

<template>
  <div class="max-w-6xl mx-auto space-y-8 pb-12">
    <!-- Header -->
    <div class="relative overflow-hidden glass-card p-10 border-none bg-gradient-to-br from-white/40 to-slate-50/20 dark:from-slate-900/40 dark:to-slate-900/10 rounded-[40px] shadow-2xl">
      <div class="absolute -top-24 -right-24 w-96 h-96 bg-primary-500/10 blur-3xl rounded-full" />
      <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-500/10 blur-3xl rounded-full" />

      <div class="relative flex flex-col md:flex-row items-center justify-between gap-8 text-center md:text-left">
        <div class="space-y-4 flex-1">
          <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-primary-500/10 border border-primary-500/20 text-primary-600 dark:text-primary-400 text-xs font-black uppercase tracking-widest mb-2 shadow-sm">
            <UIcon
              name="i-lucide-shield-check"
              class="w-4 h-4"
            />
            Fortified Verification Protocol
          </div>
          <h1 class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
            Identity & <span class="bg-gradient-to-r from-primary-600 to-indigo-600 bg-clip-text text-transparent">Compliance</span>
          </h1>
          <p class="text-lg text-slate-500 dark:text-slate-400 font-medium max-w-xl">
            Confirm your professional identity to enable full liquidity cycles, high-tier network access, and direct institutional settlements.
          </p>
        </div>

        <div class="relative shrink-0">
          <div
            class="w-32 h-32 md:w-40 md:h-40 bg-gradient-to-br from-primary-500 to-indigo-600 rounded-[40px] flex items-center justify-center shadow-2xl shadow-primary-500/40 rotate-6 hover:rotate-0 transition-all duration-500 group"
          >
            <UIcon
              :name="statusIcon"
              class="w-16 h-16 md:w-20 md:h-20 text-white group-hover:scale-110 transition-transform duration-500"
            />
          </div>
          <!-- Decorative dots -->
          <div class="absolute -top-4 -left-4 grid grid-cols-2 gap-2 opacity-20">
            <div
              v-for="i in 4"
              :key="i"
              class="w-2 h-2 rounded-full bg-primary-500"
            />
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
      <!-- Left: Form or Status -->
      <div class="lg:col-span-8">
        <div
          v-if="loading"
          class="glass-card h-96 flex flex-col items-center justify-center rounded-[40px]"
        >
          <div class="w-16 h-16 border-4 border-primary-500/20 border-t-primary-500 rounded-full animate-spin mb-6" />
          <p class="text-xs font-black uppercase tracking-[0.3em] text-slate-400 animate-pulse">
            Syncing compliance nodes...
          </p>
        </div>

        <!-- Verified / Pending State -->
        <div
          v-else-if="hasKyc && kycStatus?.status !== 'rejected'"
          class="glass-card p-0 border-none overflow-hidden h-full flex flex-col rounded-[40px] shadow-xl"
        >
          <div :class="[statusHeaderBg, 'p-12 text-center space-y-6 relative overflow-hidden shadow-xl']">
            <div
              :class="[statusIconBg, statusIconColor, 'w-24 h-24 rounded-[35px] flex items-center justify-center mx-auto mb-6 shadow-lg', statusIconShadow]"
              animate-pulse
            >
              <UIcon
                :name="statusIcon"
                class="w-14 h-14"
              />
            </div>
            <div class="space-y-2">
              <h2 :class="['text-3xl font-black capitalize tracking-tight', statusTitleColor]">
                Verification {{ kycStatus.status }}
              </h2>
              <div :class="['h-1 w-20 bg-current mx-auto opacity-20 rounded-full', statusDivider]" />
            </div>
            <p class="text-slate-600 dark:text-slate-400 max-w-sm mx-auto font-medium text-lg leading-relaxed">
              {{ kycStatus.status === 'verified'
                ? 'Professional credentials authorized. Your account is operating with full clearance and unrestricted liquidity.'
                : 'Compliance audit in progress. Our team is synchronizing your identity with the primary network.'
              }}
            </p>
          </div>

          <div class="p-10 border-t border-slate-100 dark:border-slate-800 flex-1 space-y-8 bg-white/50 dark:bg-slate-900/50">
            <div class="flex items-center justify-between">
              <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                <div class="w-4 h-[1px] bg-slate-200 dark:bg-slate-700" />
                Vault Dossier
              </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
              <div
                v-for="item in [
                  { label: 'PAN NUMBER', value: kycStatus.pan_number, icon: 'i-lucide-credit-card' },
                  { label: 'AADHAAR (TAIL)', value: kycStatus.aadhaar_number ? `XXXX-XXXX-${kycStatus.aadhaar_number.slice(-4)}` : 'Not Provided', icon: 'i-lucide-user' },
                  { label: 'GSTIN ACCESS', value: kycStatus.gst_number || 'NOT REGISTERED', icon: 'i-lucide-building' },
                  { label: 'TIMESTAMP', value: new Date(kycStatus.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' }), icon: 'i-lucide-calendar' }
                ]"
                :key="item.label"
                class="flex flex-col gap-2 p-5 rounded-3xl bg-white dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800/50 group hover:border-primary-500/30 transition-all shadow-sm"
              >
                <div class="flex items-center gap-2">
                  <UIcon
                    :name="item.icon"
                    class="w-4 h-4 text-slate-400 group-hover:text-primary-500 transition-colors"
                  />
                  <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ item.label }}</span>
                </div>
                <span class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-wider">{{ item.value }}</span>
              </div>
            </div>

            <div
              v-if="kycStatus.status === 'verified'"
              class="pt-6"
            >
              <UButton
                to="/wallet"
                block
                size="xl"
                color="success"
                class="rounded-3xl font-black py-4 shadow-xl shadow-success-500/20 uppercase tracking-widest text-xs"
              >
                Access Liquid Markets
              </UButton>
            </div>
          </div>
        </div>

        <!-- Submission Form (New or Rejected) -->
        <div
          v-else
          class="glass-card p-10 border-none ring-1 ring-slate-200 dark:ring-slate-800 shadow-2xl rounded-[40px]"
        >
          <div
            v-if="kycStatus?.status === 'rejected'"
            class="mb-10 p-8 bg-red-500/5 border-2 border-dashed border-red-500/20 rounded-[35px] flex gap-6 items-start"
          >
            <div class="w-14 h-14 rounded-2xl bg-red-500/10 flex items-center justify-center shrink-0">
              <UIcon
                name="i-lucide-alert-octagon"
                class="w-8 h-8 text-red-500"
              />
            </div>
            <div>
              <h4 class="text-xl font-black text-red-600 dark:text-red-400">
                Compliance Rejection
              </h4>
              <p class="text-base text-red-500 mt-2 font-medium leading-relaxed">
                {{ kycStatus.rejection_reason || 'Identity data could not be verified against institutional nodes. Please recalibrate your submission.' }}
              </p>
            </div>
          </div>

          <UForm
            :state="formData"
            class="space-y-10"
            @submit.prevent="submitKyc"
          >
            <div class="space-y-10">
              <!-- ID Numbers -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <UFormField
                  label="PAN Card Identifier"
                  required
                  hint="Permanent Account Number"
                >
                  <UInput
                    v-model="formData.pan_number"
                    placeholder="ABCDE1234F"
                    maxlength="10"
                    size="xl"
                    class="rounded-[20px] font-black uppercase tracking-[0.2em] bg-slate-50 dark:bg-slate-800/50"
                    :ui="{ rounded: 'rounded-[20px]' }"
                  >
                    <template #leading>
                      <UIcon
                        name="i-lucide-credit-card"
                        class="text-slate-400"
                      />
                    </template>
                  </UInput>
                </UFormField>

                <UFormField
                  label="Aadhaar UID"
                  required
                  hint="12-digit Identity"
                >
                  <UInput
                    v-model="formData.aadhaar_number"
                    placeholder="0000 0000 0000"
                    maxlength="12"
                    size="xl"
                    class="rounded-[20px] font-black tracking-[0.3em] bg-slate-50 dark:bg-slate-800/50"
                    :ui="{ rounded: 'rounded-[20px]' }"
                  >
                    <template #leading>
                      <UIcon
                        name="i-lucide-user"
                        class="text-slate-400"
                      />
                    </template>
                  </UInput>
                </UFormField>
              </div>

              <UFormField
                label="Institutional GSTIN"
                hint="Goods & Services Tax Identifier"
              >
                <UInput
                  v-model="formData.gst_number"
                  placeholder="22AAAAA0000A1Z5"
                  maxlength="15"
                  size="xl"
                  class="rounded-[20px] font-black uppercase tracking-[0.2em] bg-slate-50 dark:bg-slate-800/50"
                  :ui="{ rounded: 'rounded-[20px]' }"
                >
                  <template #leading>
                    <UIcon
                      name="i-lucide-building"
                      class="text-slate-400"
                    />
                  </template>
                </UInput>
              </UFormField>

              <!-- Documents Grid -->
              <div class="space-y-6">
                <div class="flex items-center justify-between">
                  <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">
                    Compliance Assets
                  </h3>
                  <span class="text-[10px] text-slate-400 font-bold">MAX 5MB PER ITEM</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <!-- PAN -->
                  <div class="relative group h-40 rounded-[30px] border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-primary-500 transition-all flex flex-col items-center justify-center gap-3 cursor-pointer overflow-hidden p-6 bg-slate-50/50 dark:bg-slate-800/20 hover:bg-white dark:hover:bg-slate-800 duration-300">
                    <input
                      type="file"
                      accept="image/*"
                      class="absolute inset-0 opacity-0 cursor-pointer z-10"
                      @change="handleFileSelect($event, 'pan')"
                    >
                    <div
                      :class="panFile ? 'bg-emerald-500 shadow-emerald-500/40' : 'bg-slate-200 dark:bg-slate-700'"
                      class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-500 group-hover:scale-110 shadow-lg"
                    >
                      <UIcon
                        :name="panFile ? 'i-lucide-check' : 'i-lucide-upload-cloud'"
                        :class="panFile ? 'text-white' : 'text-slate-500 dark:text-slate-400'"
                        class="w-7 h-7"
                      />
                    </div>
                    <div class="text-center">
                      <p class="text-[10px] font-black uppercase tracking-widest text-slate-900 dark:text-white mb-1">
                        {{ panFile ? 'PAN ATTACHED' : 'UPLOAD PAN' }}
                      </p>
                      <p class="text-[8px] font-bold text-slate-400 truncate w-32">
                        {{ panFile ? panFile.name : 'FRONT SIDE PHOTO' }}
                      </p>
                    </div>
                  </div>

                  <!-- Aadhaar -->
                  <div class="relative group h-40 rounded-[30px] border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-primary-500 transition-all flex flex-col items-center justify-center gap-3 cursor-pointer overflow-hidden p-6 bg-slate-50/50 dark:bg-slate-800/20 hover:bg-white dark:hover:bg-slate-800 duration-300">
                    <input
                      type="file"
                      accept="image/*"
                      class="absolute inset-0 opacity-0 cursor-pointer z-10"
                      @change="handleFileSelect($event, 'aadhaar')"
                    >
                    <div
                      :class="aadhaarFile ? 'bg-emerald-500 shadow-emerald-500/40' : 'bg-slate-200 dark:bg-slate-700'"
                      class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-500 group-hover:scale-110 shadow-lg"
                    >
                      <UIcon
                        :name="aadhaarFile ? 'i-lucide-check' : 'i-lucide-upload-cloud'"
                        :class="aadhaarFile ? 'text-white' : 'text-slate-500 dark:text-slate-400'"
                        class="w-7 h-7"
                      />
                    </div>
                    <div class="text-center">
                      <p class="text-[10px] font-black uppercase tracking-widest text-slate-900 dark:text-white mb-1">
                        {{ aadhaarFile ? 'AADHAAR ATTACHED' : 'UPLOAD AADHAAR' }}
                      </p>
                      <p class="text-[8px] font-bold text-slate-400 truncate w-32">
                        {{ aadhaarFile ? aadhaarFile.name : 'UIDAI CARD PHOTO' }}
                      </p>
                    </div>
                  </div>

                  <!-- GST -->
                  <div class="relative group h-40 rounded-[30px] border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-primary-500 transition-all flex flex-col items-center justify-center gap-3 cursor-pointer overflow-hidden p-6 bg-slate-50/50 dark:bg-slate-800/20 hover:bg-white dark:hover:bg-slate-800 duration-300">
                    <input
                      type="file"
                      accept="image/*"
                      class="absolute inset-0 opacity-0 cursor-pointer z-10"
                      @change="handleFileSelect($event, 'gst')"
                    >
                    <div
                      :class="gstFile ? 'bg-emerald-500 shadow-emerald-500/40' : 'bg-slate-200 dark:bg-slate-700'"
                      class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-500 group-hover:scale-110 shadow-lg"
                    >
                      <UIcon
                        :name="gstFile ? 'i-lucide-check' : 'i-lucide-upload-cloud'"
                        :class="gstFile ? 'text-white' : 'text-slate-500 dark:text-slate-400'"
                        class="w-7 h-7"
                      />
                    </div>
                    <div class="text-center">
                      <p class="text-[10px] font-black uppercase tracking-widest text-slate-900 dark:text-white mb-1">
                        {{ gstFile ? 'GSTIN ATTACHED' : 'GST' }}
                      </p>
                      <p class="text-[8px] font-bold text-slate-400 truncate w-32">
                        {{ gstFile ? gstFile.name : 'REGISTRATION DOC' }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="pt-10">
              <UButton
                type="submit"
                block
                size="xl"
                color="primary"
                :loading="submitting"
                :disabled="!formData.pan_number || !panFile || !aadhaarFile"
                class="rounded-[30px] font-black py-6 shadow-2xl shadow-primary-500/40 active:scale-[0.98] transition-all text-sm uppercase tracking-[0.3em]"
              >
                <template #leading>
                  <UIcon
                    name="i-lucide-shield-check"
                    class="w-6 h-6"
                  />
                </template>
                Authorize Identity Verification
              </UButton>
              <div class="mt-8 flex items-center justify-center gap-3">
                <UIcon
                  name="i-lucide-lock"
                  class="w-4 h-4 text-slate-400"
                />
                <p class="text-[9px] text-slate-400 uppercase font-black tracking-widest">
                  AES-256 Link Protection Enabled
                </p>
              </div>
            </div>
          </UForm>
        </div>
      </div>

      <!-- Right: Security & FAQ -->
      <div class="lg:col-span-4 space-y-8">
        <div class="glass-card p-10 border-none bg-slate-900 text-white rounded-[40px] shadow-2xl relative overflow-hidden group">
          <div class="absolute -top-24 -right-24 w-64 h-64 bg-primary-500/20 blur-3xl rounded-full group-hover:bg-primary-500/30 transition-all duration-700" />
          <div class="relative">
            <h3 class="text-xs font-black mb-12 border-l-4 border-primary-500 pl-4 uppercase tracking-[0.3em] text-primary-400">
              Vault Protocol
            </h3>
            <ul class="space-y-10">
              <li
                v-for="(benefit, i) in [
                  { title: 'Liquidity Matrix', desc: 'Verify to unlock synchronized wallet withdrawal nodes.', icon: 'i-lucide-zap' },
                  { title: 'Network Pulse', desc: 'Expand your affiliate radius with unlimited depth.', icon: 'i-lucide-network' },
                  { title: 'Clearing Tier', desc: 'Direct institutional settlements for top-tier advisors.', icon: 'i-lucide-landmark' }
                ]"
                :key="i"
                class="flex gap-6 group/item"
              >
                <div class="w-14 h-14 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center shrink-0 group-hover/item:border-primary-500/50 transition-colors shadow-lg">
                  <UIcon
                    :name="benefit.icon"
                    class="w-7 h-7 text-primary-400"
                  />
                </div>
                <div>
                  <h4 class="font-black text-sm uppercase tracking-wider group-hover/item:text-primary-400 transition-colors">
                    {{ benefit.title }}
                  </h4>
                  <p class="text-xs text-slate-400 font-medium mt-1.5 leading-relaxed">
                    {{ benefit.desc }}
                  </p>
                </div>
              </li>
            </ul>
          </div>
        </div>

        <div class="glass-card p-10 border-none ring-1 ring-slate-200 dark:ring-slate-800 rounded-[40px] shadow-xl">
          <h3 class="font-black text-slate-900 dark:text-white mb-8 uppercase text-xs tracking-[0.2em] flex items-center gap-3">
            <div class="w-2.5 h-2.5 rounded-full bg-primary-500 animate-pulse" />
            Validation Logic
          </h3>
          <div class="space-y-6">
            <div
              v-for="q in [
                'Only Individual Personal verification is currently operational.',
                'High-resolution PDF/JPEG assets yield 40% faster clearing.',
                'Identity data encrypted at rest via hardware security modules.',
                'Standard verification latency: 12-24 business cycles.'
              ]"
              :key="q"
              class="flex items-start gap-4 text-[11px] font-bold text-slate-600 dark:text-slate-400 leading-relaxed"
            >
              <div class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700 mt-1.5 shrink-0" />
              {{ q }}
            </div>
          </div>
          <UButton
            to="/help"
            variant="link"
            color="primary"
            class="mt-10 p-0 text-[10px] font-black uppercase tracking-widest group"
          >
            Access Compliance Knowledge Base <UIcon
              name="i-lucide-arrow-right"
              class="ml-1 w-3 h-3 group-hover:translate-x-1 transition-transform"
            />
          </UButton>
        </div>
      </div>
    </div>
  </div>
</template>
