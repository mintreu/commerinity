<script setup lang="ts">
import { useAdvisorTeamLeader } from '~/composables/useAdvisorTeamLeader'
import { useRouter } from '#app'

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const { createTeamLeader } = useAdvisorTeamLeader()
const toast = useToast()
const router = useRouter()

const form = reactive({
  name: '',
  mobile: '',
  email: '',
  dob: '',
  gender: '',
  kyc_type: '',
  pan_number: '',
  aadhaar_number: '',
  company_name: '',
  company_type: '',
  gst_number: '',
  address: {
    person_name: '',
    person_mobile: '',
    address_1: '',
    city: '',
    postal_code: '',
    country_code: 'IN',
    state_code: ''
  },
  beneficiary: {
    type: '',
    account_number: '',
    holder_name: '',
    ifsc_code: '',
    bank_name: '',
    upi_id: ''
  }
})

const avatarFile = ref<File | null>(null)
const submitting = ref(false)

const handleAvatar = (event: Event) => {
  const target = event.target as HTMLInputElement
  avatarFile.value = target.files?.[0] ?? null
}

const submit = async () => {
  if (submitting.value || !form.name || !form.mobile || !form.kyc_type || !form.pan_number) {
    toast.warning('Required fields missing.')
    return
  }

  submitting.value = true
  try {
    const payload = new FormData()
    payload.append('name', form.name)
    payload.append('mobile', form.mobile)
    if (form.email) payload.append('email', form.email)
    if (form.dob) payload.append('dob', form.dob)
    if (form.gender) payload.append('gender', form.gender)
    payload.append('kyc_type', form.kyc_type)
    payload.append('pan_number', form.pan_number)
    if (form.aadhaar_number) payload.append('aadhaar_number', form.aadhaar_number)
    if (form.company_name) payload.append('company_name', form.company_name)
    if (form.company_type) payload.append('company_type', form.company_type)
    if (form.gst_number) payload.append('gst_number', form.gst_number)
    payload.append('address[person_name]', form.address.person_name)
    payload.append('address[person_mobile]', form.address.person_mobile)
    payload.append('address[address_1]', form.address.address_1)
    payload.append('address[city]', form.address.city)
    payload.append('address[postal_code]', form.address.postal_code)
    payload.append('address[country_code]', form.address.country_code)
    if (form.address.state_code) payload.append('address[state_code]', form.address.state_code)

    if (form.beneficiary.type) {
      payload.append('beneficiary[type]', form.beneficiary.type)
      payload.append('beneficiary[account_number]', form.beneficiary.account_number)
      payload.append('beneficiary[holder_name]', form.beneficiary.holder_name)
      if (form.beneficiary.ifsc_code) payload.append('beneficiary[ifsc_code]', form.beneficiary.ifsc_code)
      if (form.beneficiary.bank_name) payload.append('beneficiary[bank_name]', form.beneficiary.bank_name)
      if (form.beneficiary.upi_id) payload.append('beneficiary[upi_id]', form.beneficiary.upi_id)
    }

    if (avatarFile.value) {
      payload.append('avatar', avatarFile.value)
    }

    await createTeamLeader(payload)
    toast.success('Team leader created. Review in dashboard.')
    await router.push('/dashboard')
  } catch (error) {
    toast.error('Unable to create team leader.')
    console.error(error)
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="space-y-8">
    <!-- Header Section with Gradient -->
    <div class="relative">
      <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/20 via-purple-500/20 to-pink-500/20 rounded-3xl blur-3xl -z-10" />
      <div class="glass-card p-8 rounded-3xl border border-white/20 dark:border-white/10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div class="space-y-2">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <UIcon name="i-lucide-user-plus" class="w-6 h-6 text-white" />
              </div>
              <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 dark:from-white dark:via-slate-100 dark:to-slate-300 bg-clip-text text-transparent">
                  Add Team Leader
                </h1>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                  Capture personal details, KYC, and beneficiary info in one flow
                </p>
              </div>
            </div>
          </div>
          <UButton
            to="/dashboard"
            variant="soft"
            color="neutral"
            icon="i-lucide-arrow-left"
            size="lg"
            class="lg:w-auto w-full"
          >
            Back to dashboard
          </UButton>
        </div>
      </div>
    </div>

    <!-- Form Section -->
    <form @submit.prevent="submit" class="space-y-6">
      <!-- Personal Information Card -->
      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <div class="flex items-center gap-3 mb-4 sm:mb-6">
          <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center flex-shrink-0">
            <UIcon name="i-lucide-user" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
          </div>
          <div>
            <h2 class="text-lg sm:text-xl font-semibold text-slate-900 dark:text-white">Personal Information</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Basic details and contact information</p>
          </div>
        </div>

        <div class="space-y-4 sm:space-y-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Full Name *
              </label>
              <UInput
                v-model="form.name"
                size="lg"
                placeholder="Enter full name"
                icon="i-lucide-user"
                required
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Mobile Number *
              </label>
              <UInput
                v-model="form.mobile"
                size="lg"
                placeholder="+91 98765 43210"
                icon="i-lucide-phone"
                required
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full sm:col-span-2 lg:col-span-1">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Email Address
              </label>
              <UInput
                v-model="form.email"
                type="email"
                size="lg"
                placeholder="email@example.com"
                icon="i-lucide-mail"
                class="w-full"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Date of Birth
              </label>
              <UInput
                v-model="form.dob"
                type="date"
                size="lg"
                icon="i-lucide-calendar"
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Gender
              </label>
              <USelectMenu
                v-model="form.gender"
                :options="[
                  { value: 'male', label: 'Male' },
                  { value: 'female', label: 'Female' },
                  { value: 'other', label: 'Other' }
                ]"
                value-attribute="value"
                option-attribute="label"
                size="lg"
                placeholder="Select gender"
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full sm:col-span-2 lg:col-span-1">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Avatar Photo
              </label>
              <input
                type="file"
                accept="image/*"
                @change="handleAvatar"
                class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 file:mr-2 sm:file:mr-4 file:py-1.5 sm:file:py-2 file:px-3 sm:file:px-4 file:rounded-lg file:border-0 file:text-xs sm:file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/50 dark:file:text-indigo-300"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- KYC Information Card -->
      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <div class="flex items-center gap-3 mb-4 sm:mb-6">
          <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center flex-shrink-0">
            <UIcon name="i-lucide-shield-check" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
          </div>
          <div>
            <h2 class="text-lg sm:text-xl font-semibold text-slate-900 dark:text-white">KYC Information</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Identity verification and tax details</p>
          </div>
        </div>

        <div class="space-y-4 sm:space-y-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                KYC Type *
              </label>
              <USelectMenu
                v-model="form.kyc_type"
                :options="[
                  { value: 'individual', label: 'Individual' },
                  { value: 'business', label: 'Business' },
                  { value: 'company', label: 'Company' }
                ]"
                value-attribute="value"
                option-attribute="label"
                size="lg"
                placeholder="Select KYC type"
                required
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                PAN Number *
              </label>
              <UInput
                v-model="form.pan_number"
                size="lg"
                placeholder="ABCDE1234F"
                icon="i-lucide-credit-card"
                required
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full sm:col-span-2 lg:col-span-1">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Aadhaar Number
              </label>
              <UInput
                v-model="form.aadhaar_number"
                size="lg"
                placeholder="1234 5678 9012"
                icon="i-lucide-fingerprint"
                class="w-full"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Company Name
              </label>
              <UInput
                v-model="form.company_name"
                size="lg"
                placeholder="Company name"
                icon="i-lucide-building-2"
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Company Type
              </label>
              <USelectMenu
                v-model="form.company_type"
                :options="[
                  { value: 'proprietorship', label: 'Proprietorship' },
                  { value: 'partnership', label: 'Partnership' },
                  { value: 'private_limited', label: 'Private Limited' },
                  { value: 'llp', label: 'LLP' }
                ]"
                value-attribute="value"
                option-attribute="label"
                size="lg"
                placeholder="Select company type"
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full sm:col-span-2 lg:col-span-1">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                GST Number
              </label>
              <UInput
                v-model="form.gst_number"
                size="lg"
                placeholder="22AAAAA0000A1Z5"
                icon="i-lucide-receipt"
                class="w-full"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Address Information Card -->
      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <div class="flex items-center gap-3 mb-4 sm:mb-6">
          <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center flex-shrink-0">
            <UIcon name="i-lucide-map-pin" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
          </div>
          <div>
            <h2 class="text-lg sm:text-xl font-semibold text-slate-900 dark:text-white">Address Details</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Complete address and contact information</p>
          </div>
        </div>

        <div class="space-y-4 sm:space-y-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Contact Person *
              </label>
              <UInput
                v-model="form.address.person_name"
                size="lg"
                placeholder="Contact name"
                icon="i-lucide-user"
                required
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Contact Mobile *
              </label>
              <UInput
                v-model="form.address.person_mobile"
                size="lg"
                placeholder="+91 98765 43210"
                icon="i-lucide-phone"
                required
                class="w-full"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Address Line *
              </label>
              <UInput
                v-model="form.address.address_1"
                size="lg"
                placeholder="Street address"
                icon="i-lucide-map"
                required
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                City *
              </label>
              <UInput
                v-model="form.address.city"
                size="lg"
                placeholder="City name"
                icon="i-lucide-building"
                required
                class="w-full"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Postal Code *
              </label>
              <UInput
                v-model="form.address.postal_code"
                size="lg"
                placeholder="Postal/ZIP code"
                icon="i-lucide-hash"
                required
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                State Code
              </label>
              <UInput
                v-model="form.address.state_code"
                size="lg"
                placeholder="State code (e.g., DL, MH)"
                icon="i-lucide-map-pinned"
                class="w-full"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Beneficiary Information Card -->
      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <div class="flex items-center gap-3 mb-4 sm:mb-6">
          <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center flex-shrink-0">
            <UIcon name="i-lucide-landmark" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
          </div>
          <div>
            <h2 class="text-lg sm:text-xl font-semibold text-slate-900 dark:text-white">Beneficiary Details</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Bank account or UPI information (optional)</p>
          </div>
        </div>

        <div class="space-y-4 sm:space-y-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Account Type
              </label>
              <USelectMenu
                v-model="form.beneficiary.type"
                :options="[
                  { value: 'savings', label: 'Savings' },
                  { value: 'current', label: 'Current' },
                  { value: 'upi', label: 'UPI' }
                ]"
                value-attribute="value"
                option-attribute="label"
                size="lg"
                placeholder="Select account type"
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Account Number
              </label>
              <UInput
                v-model="form.beneficiary.account_number"
                size="lg"
                placeholder="Account number"
                icon="i-lucide-hash"
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full sm:col-span-2 lg:col-span-1">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Account Holder Name
              </label>
              <UInput
                v-model="form.beneficiary.holder_name"
                size="lg"
                placeholder="Holder name"
                icon="i-lucide-user"
                class="w-full"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                IFSC Code
              </label>
              <UInput
                v-model="form.beneficiary.ifsc_code"
                size="lg"
                placeholder="SBIN0001234"
                icon="i-lucide-building-2"
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Bank Name
              </label>
              <UInput
                v-model="form.beneficiary.bank_name"
                size="lg"
                placeholder="Bank name"
                icon="i-lucide-landmark"
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full sm:col-span-2 lg:col-span-1">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                UPI ID
              </label>
              <UInput
                v-model="form.beneficiary.upi_id"
                size="lg"
                placeholder="user@upi"
                icon="i-lucide-smartphone"
                class="w-full"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
          <UButton
            type="button"
            to="/dashboard"
            variant="soft"
            color="neutral"
            size="lg"
            icon="i-lucide-x"
            class="w-full sm:w-auto"
          >
            Cancel
          </UButton>
          <UButton
            type="submit"
            color="primary"
            size="lg"
            icon="i-lucide-check"
            :loading="submitting"
            class="w-full sm:w-auto sm:min-w-[200px]"
          >
            Create Team Leader
          </UButton>
        </div>
      </div>
    </form>
  </div>
</template>
