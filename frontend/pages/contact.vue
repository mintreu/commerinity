<template>
  <div class="max-w-3xl mx-auto px-4 py-16 text-gray-800 dark:text-gray-200">
    <h1 class="text-3xl font-bold mb-6">Contact Us</h1>

    <!-- Toggle -->
    <div class="flex gap-3 mb-8">
      <button
          @click="switchType('user')"
          :class="[
          'px-4 py-2 rounded-md font-medium border',
          formType === 'user'
            ? 'bg-blue-600 text-white border-blue-600'
            : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-700'
        ]"
      >
        General Inquiry
      </button>
      <button
          @click="switchType('business')"
          :class="[
          'px-4 py-2 rounded-md font-medium border',
          formType === 'business'
            ? 'bg-blue-600 text-white border-blue-600'
            : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-700'
        ]"
      >
        Business Inquiry
      </button>
    </div>

    <!-- User form -->
    <form v-if="formType === 'user'" class="space-y-4 mb-12" @submit.prevent="submitUser">
      <div>
        <label class="block text-sm mb-1">Your Name</label>
        <input
            v-model.trim="userForm.name"
            type="text"
            placeholder="Jane Doe"
            :class="inputClass('user','name')"
        />
        <p v-if="errors.user.name" class="mt-1 text-xs text-red-600">{{ errors.user.name }}</p>
      </div>
      <div>
        <label class="block text-sm mb-1">Your Email</label>
        <input
            v-model.trim="userForm.email"
            type="email"
            placeholder="you@example.com"
            :class="inputClass('user','email')"
        />
        <p v-if="errors.user.email" class="mt-1 text-xs text-red-600">{{ errors.user.email }}</p>
      </div>
      <div>
        <label class="block text-sm mb-1">Message</label>
        <textarea
            v-model.trim="userForm.message"
            rows="5"
            placeholder="How can we help?"
            :class="textareaClass('user','message')"
        ></textarea>
        <p v-if="errors.user.message" class="mt-1 text-xs text-red-600">{{ errors.user.message }}</p>
      </div>
      <button
          type="submit"
          :disabled="submitting"
          class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
      >
        <Icon v-if="submitting" name="mdi-loading" class="animate-spin" />
        <span>Send Message</span>
      </button>
    </form>

    <!-- Business form -->
    <form v-else class="space-y-4 mb-12" @submit.prevent="submitBusiness">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm mb-1">Company Name</label>
          <input
              v-model.trim="bizForm.company_name"
              type="text"
              placeholder="Acme Inc."
              :class="inputClass('business','company_name')"
          />
          <p v-if="errors.business.company_name" class="mt-1 text-xs text-red-600">{{ errors.business.company_name }}</p>
        </div>
        <div>
          <label class="block text-sm mb-1">Business Email</label>
          <input
              v-model.trim="bizForm.email"
              type="email"
              placeholder="biz@example.com"
              :class="inputClass('business','email')"
          />
          <p v-if="errors.business.email" class="mt-1 text-xs text-red-600">{{ errors.business.email }}</p>
        </div>
        <div>
          <label class="block text-sm mb-1">Mobile Number</label>
          <input
              v-model.trim="bizForm.phone"
              type="tel"
              placeholder="+1 555 123 4567"
              :class="inputClass('business','phone')"
          />
          <p v-if="errors.business.phone" class="mt-1 text-xs text-red-600">{{ errors.business.phone }}</p>
        </div>
        <div>
          <label class="block text-sm mb-1">Website (optional)</label>
          <input
              v-model.trim="bizForm.website"
              type="text"
              placeholder="https://example.com"
              :class="inputClass('business','website', true)"
          />
          <p v-if="errors.business.website" class="mt-1 text-xs text-red-600">{{ errors.business.website }}</p>
        </div>
      </div>


      <div>
        <label class="block text-sm mb-1">Your Name</label>
        <input
            v-model.trim="bizForm.name"
            type="text"
            placeholder="Jane Doe"
            :class="inputClass('business','name')"
        />
        <p v-if="errors.business.name" class="mt-1 text-xs text-red-600">{{ errors.business.name }}</p>
      </div>


      <div>
        <label class="block text-sm mb-1">Company Address</label>
        <input
            v-model.trim="bizForm.address"
            type="text"
            placeholder="123 Street, City, Country"
            :class="inputClass('business','address')"
        />
        <p v-if="errors.business.address" class="mt-1 text-xs text-red-600">{{ errors.business.address }}</p>
      </div>
      <div>
        <label class="block text-sm mb-1">Inquiry</label>
        <textarea
            v-model.trim="bizForm.message"
            rows="5"
            placeholder="Tell us about your business needs"
            :class="textareaClass('business','message')"
        ></textarea>
        <p v-if="errors.business.message" class="mt-1 text-xs text-red-600">{{ errors.business.message }}</p>
      </div>
      <button
          type="submit"
          :disabled="submitting"
          class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
      >
        <Icon v-if="submitting" name="mdi-loading" class="animate-spin" />
        <span>Contact Us</span>
      </button>
    </form>

    <!-- Contact Info -->
    <div class="border-t pt-6 mt-8 text-sm text-gray-700 dark:text-gray-400">
      <p><strong>Company:</strong> {{ companyName }}</p>
      <p><strong>Email:</strong>
        <a :href="`mailto:${supportEmail}`" class="text-blue-600 hover:underline">{{ supportEmail }}</a>
      </p>
      <p><strong>Phone:</strong>
        <a :href="`tel:${phoneNumber}`" class="text-blue-600 hover:underline">{{ phoneNumber }}</a>
      </p>
      <p><strong>Address:</strong> {{ address }}</p>
    </div>

    <!-- Success Modal -->
    <Teleport to="#teleports">
      <div v-if="showSuccess" class="fixed inset-0 z-50 flex items-center justify-center" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-black/50"></div>
        <div class="relative z-10 w-full max-w-md rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 shadow-xl">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-green-600 text-white flex items-center justify-center">✓</div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Success</h3>
          </div>
          <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">{{ successMessage }}</p>
          <div class="mt-6 flex justify-end gap-3">
            <button @click="closeSuccess(true)" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">OK</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Error Modal -->
    <Teleport to="#teleports">
      <div v-if="showError" class="fixed inset-0 z-50 flex items-center justify-center" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-black/50"></div>
        <div class="relative z-10 w-full max-w-md rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 shadow-xl">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-red-600 text-white flex items-center justify-center">!</div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Something went wrong</h3>
          </div>
          <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">{{ errorMessage }}</p>
          <div class="mt-6 flex justify-end gap-3">
            <button @click="showError = false" class="px-4 py-2 rounded-md bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-700">
              Close
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const { public: { apiBase, supportEmail, companyName, phoneNumber, address } } = useRuntimeConfig()
const toast = useToast()

const formType = ref<'user' | 'business'>('user')
const submitting = ref(false)

const userForm = ref({ name: '', email: '', message: '' })
const bizForm = ref({ company_name: '', address: '', email: '',name: '', phone: '', website: '', message: '' })

const errors = ref<{ user: Record<string, string>, business: Record<string, string> }>({ user: {}, business: {} })

// Modals
const showSuccess = ref(false)
const showError = ref(false)
const successMessage = ref('Your message has been sent successfully.')
const errorMessage = ref('Could not send your message. Please try again.')

const baseInput =
    'w-full px-4 py-2 border rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 border-gray-300 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500'
const errorInput = baseInput + ' border-red-500 focus:ring-red-500'
const inputClass = (type: 'user'|'business', field: string, optional = false) => {
  const e = errors.value[type][field]
  return e ? errorInput : baseInput
}
const textareaClass = inputClass

function switchType(type: 'user'|'business') {
  formType.value = type
  errors.value.user = {}
  errors.value.business = {}
}

// Validation
function validateUser() {
  const e: Record<string, string> = {}
  if (!userForm.value.name) e.name = 'Name is required'
  if (!userForm.value.email) e.email = 'Email is required'
  else if (!/^\S+@\S+\.\S+$/.test(userForm.value.email)) e.email = 'Enter a valid email'
  if (!userForm.value.message) e.message = 'Message is required'
  errors.value.user = e
  return Object.keys(e).length === 0
}
function validateBusiness() {
  const e: Record<string, string> = {}
  if (!bizForm.value.name) e.name = 'Name is required'
  if (!bizForm.value.company_name) e.company_name = 'Company name is required'
  if (!bizForm.value.address) e.address = 'Address is required'
  if (!bizForm.value.email) e.email = 'Email is required'
  else if (!/^\S+@\S+\.\S+$/.test(bizForm.value.email)) e.email = 'Enter a valid email'
  if (!bizForm.value.phone) e.phone = 'Mobile number is required'
  if (!bizForm.value.message) e.message = 'Inquiry message is required'
  if (bizForm.value.website && !/^https?:\/\/\S+$/i.test(bizForm.value.website)) e.website = 'Enter a valid URL (https://...)'
  errors.value.business = e
  return Object.keys(e).length === 0
}

function closeSuccess() {
  showSuccess.value = false
}


// Submitters
async function submitUser() {
  if (!validateUser()) {
    toast.error({ title: 'Validation', message: 'Please correct the highlighted fields.', timeout: 2500 })
    return
  }

  submitting.value = true
  showError.value = false
  errors.value.user = {}

  try {
    const res = await useSanctumFetch(`${apiBase}/contact/user`, {
      method: 'POST',
      body: userForm.value
    })

    console.log('[contact:user] res:', res)

    const data = res // ✅ FIX: don't unwrap .data
    if (data && data.success === true) {
      successMessage.value = data.message || 'Thank you for contacting us. Our support team will review your request and get back to you within 2–3 business days.'
      showSuccess.value = true
      userForm.value = { name: '', email: '', message: '' }
      return
    }

    const apiErrors = data?.errors
    if (apiErrors && typeof apiErrors === 'object') {
      errors.value.user = Object.fromEntries(
          Object.entries(apiErrors).map(([k, v]: any) => [k, Array.isArray(v) ? v : String(v)])
      )
    }
    errorMessage.value = data?.message || 'Could not send message'
    showError.value = true
  } catch (e: any) {
    console.log('[contact:user] catch error:', e)
    const ve = e?.errors || {}
    if (ve && typeof ve === 'object') {
      errors.value.user = Object.fromEntries(
          Object.entries(ve).map(([k, v]: any) => [k, Array.isArray(v) ? v : String(v)])
      )
    }
    errorMessage.value = e?.message || 'Could not send message'
    showError.value = true
  } finally {
    submitting.value = false
  }
}

async function submitBusiness() {
  if (!validateBusiness()) {
    toast.error({ title: 'Validation', message: 'Please correct the highlighted fields.', timeout: 2500 })
    return
  }

  submitting.value = true
  showError.value = false
  errors.value.business = {}

  try {
    const res = await useSanctumFetch(`${apiBase}/contact/business`, {
      method: 'POST',
      body: bizForm.value
    })

    console.log('[contact:business] res:', res)

    const data = res // ✅ FIX: don't unwrap .data
    if (data && data.success === true) {
      successMessage.value = data.message || 'Thank you for reaching out regarding a business inquiry. Our team will carefully review your message and contact you within 2–3 business days.'
      showSuccess.value = true
      bizForm.value = { company_name: '', address: '', email: '',name: '', phone: '', website: '', message: '' }
      return
    }

    const apiErrors = data?.errors
    if (apiErrors && typeof apiErrors === 'object') {
      errors.value.business = Object.fromEntries(
          Object.entries(apiErrors).map(([k, v]: any) => [k, Array.isArray(v) ? v : String(v)])
      )
    }
    errorMessage.value = data?.message || 'Could not send message'
    showError.value = true
  } catch (e: any) {
    console.log('[contact:business] catch error:', e)
    const ve = e?.errors || {}
    if (ve && typeof ve === 'object') {
      errors.value.business = Object.fromEntries(
          Object.entries(ve).map(([k, v]: any) => [k, Array.isArray(v) ? v : String(v)])
      )
    }
    errorMessage.value = e?.message || 'Could not send message'
    showError.value = true
  } finally {
    submitting.value = false
  }
}
</script>
