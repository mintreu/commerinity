<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-gray-900 dark:to-purple-950 overflow-x-hidden">

    <!-- Floating Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden will-change-transform">
      <div ref="orb1" class="contact-orb-1 absolute top-20 left-20 w-80 h-80 bg-gradient-to-r from-blue-400 to-cyan-400 rounded-full opacity-10 blur-3xl will-change-transform"></div>
      <div ref="orb2" class="contact-orb-2 absolute bottom-20 right-20 w-72 h-72 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full opacity-15 blur-3xl will-change-transform"></div>
      <div ref="orb3" class="contact-orb-3 absolute top-1/2 left-1/3 w-64 h-64 bg-gradient-to-r from-emerald-400 to-teal-400 rounded-full opacity-10 blur-2xl will-change-transform"></div>

      <!-- Contact Particles -->
      <div class="contact-particles">
        <div v-for="(particle, index) in particles" :key="`particle-${index}`"
             class="particle absolute rounded-full opacity-60 animate-bounce will-change-transform"
             :class="particle.class"
             :style="particle.style">
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 px-4 lg:px-20 py-16">

      <!-- Contact Header -->
      <div class="contact-header mb-16" ref="contactHeader">
        <div class="max-w-4xl mx-auto text-center">
          <div class="header-badge inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-blue-500/10 to-purple-500/10 border border-blue-200 dark:border-blue-800 backdrop-blur-sm mb-6">
            <Icon name="mdi:phone" class="w-5 h-5 mr-3 text-blue-600 dark:text-blue-400" />
            <span class="font-semibold text-blue-700 dark:text-blue-300">Get In Touch</span>
          </div>

          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gray-900 dark:text-white mb-6">
            Contact <span class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">Us</span>
          </h1>

          <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed">
            We'd love to hear from you. Send us a message and we'll respond as soon as possible.
          </p>
        </div>
      </div>

      <!-- Contact Content -->
      <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

          <!-- Contact Info Sidebar -->
          <div class="lg:col-span-1" ref="contactInfo">
            <div class="contact-info-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 p-8 sticky top-6">
              <!-- Header -->
              <div class="contact-info-header mb-8">
                <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-4">Get in Touch</h2>
                <p class="text-gray-600 dark:text-gray-300">
                  Have questions or need support? We're here to help you succeed.
                </p>
              </div>

              <!-- Contact Methods -->
              <div class="contact-methods space-y-6">
                <!-- Email -->
                <div class="contact-method flex items-start gap-4 p-4 rounded-2xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 hover:scale-105 transition-all duration-300">
                  <div class="contact-icon w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <Icon name="mdi:email" class="w-6 h-6 text-white" />
                  </div>
                  <div class="contact-details flex-1">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-1">Email Us</h3>
                    <a
                        :href="`mailto:${supportEmail}`"
                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors duration-200 font-semibold"
                    >
                      {{ supportEmail }}
                    </a>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                      We'll respond within 24 hours
                    </p>
                  </div>
                </div>

                <!-- Phone -->
                <div class="contact-method flex items-start gap-4 p-4 rounded-2xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 hover:scale-105 transition-all duration-300">
                  <div class="contact-icon w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <Icon name="mdi:phone" class="w-6 h-6 text-white" />
                  </div>
                  <div class="contact-details flex-1">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-1">Call Us</h3>
                    <a
                        :href="`tel:${phoneNumber}`"
                        class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 transition-colors duration-200 font-semibold"
                    >
                      {{ phoneNumber }}
                    </a>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                      Mon-Fri 9AM-6PM
                    </p>
                  </div>
                </div>

                <!-- Address -->
                <div class="contact-method flex items-start gap-4 p-4 rounded-2xl bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 hover:scale-105 transition-all duration-300">
                  <div class="contact-icon w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <Icon name="mdi:map-marker" class="w-6 h-6 text-white" />
                  </div>
                  <div class="contact-details flex-1">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-1">Visit Us</h3>
                    <p class="text-purple-600 dark:text-purple-400 font-semibold">{{ address }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                      Our headquarters
                    </p>
                  </div>
                </div>
              </div>

              <!-- Company Info -->
              <div class="company-info mt-8 p-6 bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-700 dark:to-blue-900/20 rounded-2xl border border-gray-200 dark:border-gray-600">
                <h3 class="font-bold text-gray-900 dark:text-white mb-2 flex items-center">
                  <Icon name="mdi:office-building" class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" />
                  {{ companyName }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  Your trusted partner for premium products and exceptional service.
                </p>
              </div>
            </div>
          </div>

          <!-- Contact Forms -->
          <div class="lg:col-span-2" ref="contactForms">
            <div class="contact-forms-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">

              <!-- Form Toggle Header -->
              <div class="form-toggle-header bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6">
                <div class="flex items-center justify-between mb-4">
                  <h2 class="text-2xl font-black">Send us a Message</h2>
                  <Icon name="mdi:message-text" class="w-6 h-6" />
                </div>

                <!-- Toggle Buttons -->
                <div class="toggle-buttons flex gap-3">
                  <button
                      @click="switchType('user')"
                      class="toggle-btn px-6 py-3 rounded-xl font-bold transition-all duration-300 transform hover:scale-105"
                      :class="[
                      formType === 'user'
                        ? 'bg-white/20 backdrop-blur-sm text-white shadow-lg'
                        : 'bg-white/10 text-white/70 hover:bg-white/15 hover:text-white'
                    ]"
                  >
                    <Icon name="mdi:account" class="w-4 h-4 mr-2 inline" />
                    General Inquiry
                  </button>
                  <button
                      @click="switchType('business')"
                      class="toggle-btn px-6 py-3 rounded-xl font-bold transition-all duration-300 transform hover:scale-105"
                      :class="[
                      formType === 'business'
                        ? 'bg-white/20 backdrop-blur-sm text-white shadow-lg'
                        : 'bg-white/10 text-white/70 hover:bg-white/15 hover:text-white'
                    ]"
                  >
                    <Icon name="mdi:office-building" class="w-4 h-4 mr-2 inline" />
                    Business Inquiry
                  </button>
                </div>
              </div>

              <!-- Form Content -->
              <div class="form-content p-8">

                <!-- User Form -->
                <form v-if="formType === 'user'" class="user-form space-y-6" @submit.prevent="submitUser">
                  <div class="form-group">
                    <label class="form-label block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">
                      <Icon name="mdi:account" class="w-4 h-4 mr-2 inline text-blue-600 dark:text-blue-400" />
                      Your Name
                    </label>
                    <input
                        v-model.trim="userForm.name"
                        type="text"
                        placeholder="Enter your full name"
                        :class="inputClass('user', 'name')"
                    />
                    <p v-if="errors.user.name" class="error-message mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                      <Icon name="mdi:alert-circle" class="w-4 h-4 mr-1" />
                      {{ errors.user.name }}
                    </p>
                  </div>

                  <div class="form-group">
                    <label class="form-label block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">
                      <Icon name="mdi:email" class="w-4 h-4 mr-2 inline text-green-600 dark:text-green-400" />
                      Your Email
                    </label>
                    <input
                        v-model.trim="userForm.email"
                        type="email"
                        placeholder="Enter your email address"
                        :class="inputClass('user', 'email')"
                    />
                    <p v-if="errors.user.email" class="error-message mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                      <Icon name="mdi:alert-circle" class="w-4 h-4 mr-1" />
                      {{ errors.user.email }}
                    </p>
                  </div>

                  <div class="form-group">
                    <label class="form-label block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">
                      <Icon name="mdi:message-text" class="w-4 h-4 mr-2 inline text-purple-600 dark:text-purple-400" />
                      Your Message
                    </label>
                    <textarea
                        v-model.trim="userForm.message"
                        rows="6"
                        placeholder="How can we help you? Please provide details about your inquiry..."
                        :class="textareaClass('user', 'message')"
                    ></textarea>
                    <p v-if="errors.user.message" class="error-message mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                      <Icon name="mdi:alert-circle" class="w-4 h-4 mr-1" />
                      {{ errors.user.message }}
                    </p>
                  </div>

                  <button
                      type="submit"
                      :disabled="submitting"
                      class="submit-btn w-full py-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-black text-lg rounded-2xl shadow-2xl hover:shadow-blue-500/25 transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-3"
                  >
                    <Icon v-if="submitting" name="mdi:loading" class="w-5 h-5 animate-spin" />
                    <Icon v-else name="mdi:send" class="w-5 h-5" />
                    <span>{{ submitting ? 'Sending...' : 'Send Message' }}</span>
                  </button>
                </form>

                <!-- Business Form -->
                <form v-else class="business-form space-y-6" @submit.prevent="submitBusiness">
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="form-group">
                      <label class="form-label block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">
                        <Icon name="mdi:office-building" class="w-4 h-4 mr-2 inline text-blue-600 dark:text-blue-400" />
                        Company Name
                      </label>
                      <input
                          v-model.trim="bizForm.company_name"
                          type="text"
                          placeholder="Acme Inc."
                          :class="inputClass('business', 'company_name')"
                      />
                      <p v-if="errors.business.company_name" class="error-message mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                        <Icon name="mdi:alert-circle" class="w-4 h-4 mr-1" />
                        {{ errors.business.company_name }}
                      </p>
                    </div>

                    <div class="form-group">
                      <label class="form-label block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">
                        <Icon name="mdi:email-outline" class="w-4 h-4 mr-2 inline text-green-600 dark:text-green-400" />
                        Business Email
                      </label>
                      <input
                          v-model.trim="bizForm.email"
                          type="email"
                          placeholder="business@company.com"
                          :class="inputClass('business', 'email')"
                      />
                      <p v-if="errors.business.email" class="error-message mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                        <Icon name="mdi:alert-circle" class="w-4 h-4 mr-1" />
                        {{ errors.business.email }}
                      </p>
                    </div>

                    <div class="form-group">
                      <label class="form-label block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">
                        <Icon name="mdi:phone" class="w-4 h-4 mr-2 inline text-orange-600 dark:text-orange-400" />
                        Mobile Number
                      </label>
                      <input
                          v-model.trim="bizForm.phone"
                          type="tel"
                          placeholder="+1 555 123 4567"
                          :class="inputClass('business', 'phone')"
                      />
                      <p v-if="errors.business.phone" class="error-message mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                        <Icon name="mdi:alert-circle" class="w-4 h-4 mr-1" />
                        {{ errors.business.phone }}
                      </p>
                    </div>

                    <div class="form-group">
                      <label class="form-label block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">
                        <Icon name="mdi:web" class="w-4 h-4 mr-2 inline text-purple-600 dark:text-purple-400" />
                        Website <span class="text-gray-500">(optional)</span>
                      </label>
                      <input
                          v-model.trim="bizForm.website"
                          type="text"
                          placeholder="https://example.com"
                          :class="inputClass('business', 'website', true)"
                      />
                      <p v-if="errors.business.website" class="error-message mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                        <Icon name="mdi:alert-circle" class="w-4 h-4 mr-1" />
                        {{ errors.business.website }}
                      </p>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="form-label block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">
                      <Icon name="mdi:account" class="w-4 h-4 mr-2 inline text-indigo-600 dark:text-indigo-400" />
                      Your Name
                    </label>
                    <input
                        v-model.trim="bizForm.name"
                        type="text"
                        placeholder="Contact person name"
                        :class="inputClass('business', 'name')"
                    />
                    <p v-if="errors.business.name" class="error-message mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                      <Icon name="mdi:alert-circle" class="w-4 h-4 mr-1" />
                      {{ errors.business.name }}
                    </p>
                  </div>

                  <div class="form-group">
                    <label class="form-label block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">
                      <Icon name="mdi:map-marker" class="w-4 h-4 mr-2 inline text-red-600 dark:text-red-400" />
                      Company Address
                    </label>
                    <input
                        v-model.trim="bizForm.address"
                        type="text"
                        placeholder="123 Business Street, City, Country"
                        :class="inputClass('business', 'address')"
                    />
                    <p v-if="errors.business.address" class="error-message mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                      <Icon name="mdi:alert-circle" class="w-4 h-4 mr-1" />
                      {{ errors.business.address }}
                    </p>
                  </div>

                  <div class="form-group">
                    <label class="form-label block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">
                      <Icon name="mdi:message-text-outline" class="w-4 h-4 mr-2 inline text-teal-600 dark:text-teal-400" />
                      Business Inquiry
                    </label>
                    <textarea
                        v-model.trim="bizForm.message"
                        rows="6"
                        placeholder="Tell us about your business needs, partnership opportunities, or how we can help your company..."
                        :class="textareaClass('business', 'message')"
                    ></textarea>
                    <p v-if="errors.business.message" class="error-message mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                      <Icon name="mdi:alert-circle" class="w-4 h-4 mr-1" />
                      {{ errors.business.message }}
                    </p>
                  </div>

                  <button
                      type="submit"
                      :disabled="submitting"
                      class="submit-btn w-full py-4 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white font-black text-lg rounded-2xl shadow-2xl hover:shadow-orange-500/25 transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-3"
                  >
                    <Icon v-if="submitting" name="mdi:loading" class="w-5 h-5 animate-spin" />
                    <Icon v-else name="mdi:briefcase" class="w-5 h-5" />
                    <span>{{ submitting ? 'Sending...' : 'Contact Us' }}</span>
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

// GSAP imports (client-side only)
let gsap: any = null
let ScrollTrigger: any = null

if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
    import('gsap/ScrollTrigger').then(({ ScrollTrigger: ScrollTriggerModule }) => {
      ScrollTrigger = ScrollTriggerModule
      gsap.registerPlugin(ScrollTrigger)
    })
  })
}

// ✅ FIXED: Use the correct toast composable
const toast = useToast()

// Composables
const { public: { apiBase, supportEmail, companyName, phoneNumber, address } } = useRuntimeConfig()

// Refs for animations
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()
const orb3 = ref<HTMLElement>()
const contactHeader = ref<HTMLElement>()
const contactInfo = ref<HTMLElement>()
const contactForms = ref<HTMLElement>()

// State
const formType = ref<'user' | 'business'>('user')
const submitting = ref(false)
let gsapContext: any = null

// Animated particles
const particles = [
  { class: 'w-4 h-4 bg-blue-400', style: 'top: 15%; left: 10%; animation-delay: 0s;' },
  { class: 'w-3 h-3 bg-purple-400', style: 'top: 25%; right: 15%; animation-delay: 1.5s;' },
  { class: 'w-5 h-5 bg-emerald-400', style: 'bottom: 20%; left: 20%; animation-delay: 3s;' },
  { class: 'w-3 h-3 bg-pink-400', style: 'bottom: 30%; right: 25%; animation-delay: 4.5s;' }
]

// Form data
const userForm = ref({ name: '', email: '', message: '' })
const bizForm = ref({
  company_name: '', address: '', email: '',
  name: '', phone: '', website: '', message: ''
})

// Errors
const errors = ref<{ user: Record<string, string>, business: Record<string, string> }>({
  user: {},
  business: {}
})

// Styling
const baseInput = 'w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200'
const errorInput = 'w-full px-4 py-3 border border-red-500 rounded-xl bg-red-50 dark:bg-red-900/20 text-gray-900 dark:text-white placeholder-red-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-200'

const inputClass = (type: 'user' | 'business', field: string, optional = false) => {
  const e = errors.value[type][field]
  return e ? errorInput : baseInput
}

const textareaClass = inputClass

// Methods
function switchType(type: 'user' | 'business') {
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
  if (!bizForm.value.name) e.name = 'Contact name is required'
  if (!bizForm.value.company_name) e.company_name = 'Company name is required'
  if (!bizForm.value.address) e.address = 'Company address is required'
  if (!bizForm.value.email) e.email = 'Business email is required'
  else if (!/^\S+@\S+\.\S+$/.test(bizForm.value.email)) e.email = 'Enter a valid email'
  if (!bizForm.value.phone) e.phone = 'Mobile number is required'
  if (!bizForm.value.message) e.message = 'Business inquiry is required'
  if (bizForm.value.website && !/^https?:\/\/\S+$/i.test(bizForm.value.website)) e.website = 'Enter a valid URL (https://...)'
  errors.value.business = e
  return Object.keys(e).length === 0
}

// ✅ FIXED: Form submitters with correct toast usage
async function submitUser() {
  if (!validateUser()) {
    toast.error({
      title: 'Validation Error',
      message: 'Please correct the highlighted fields.',
      timeout: 3000
    })
    return
  }

  submitting.value = true
  errors.value.user = {}

  try {
    const res = await useSanctumFetch(`${apiBase}/contact/user`, {
      method: 'POST',
      body: userForm.value
    })

    const data = res
    if (data && data.success === true) {
      toast.success({
        title: 'Message Sent!',
        message: data.message || 'Thank you for contacting us. Our support team will review your request and get back to you within 2–3 business days.',
        timeout: 5000
      })
      userForm.value = { name: '', email: '', message: '' }
      return
    }

    const apiErrors = data?.errors
    if (apiErrors && typeof apiErrors === 'object') {
      errors.value.user = Object.fromEntries(
          Object.entries(apiErrors).map(([k, v]: any) => [k, Array.isArray(v) ? v[0] : String(v)])
      )
    }

    toast.error({
      title: 'Error',
      message: data?.message || 'Could not send message. Please try again.',
      timeout: 4000
    })
  } catch (e: any) {
    console.error('[contact:user] error:', e)
    const ve = e?.errors || {}
    if (ve && typeof ve === 'object') {
      errors.value.user = Object.fromEntries(
          Object.entries(ve).map(([k, v]: any) => [k, Array.isArray(v) ? v[0] : String(v)])
      )
    }
    toast.error({
      title: 'Network Error',
      message: e?.message || 'Could not send message. Please check your connection and try again.',
      timeout: 4000
    })
  } finally {
    submitting.value = false
  }
}

async function submitBusiness() {
  if (!validateBusiness()) {
    toast.error({
      title: 'Validation Error',
      message: 'Please correct the highlighted fields.',
      timeout: 3000
    })
    return
  }

  submitting.value = true
  errors.value.business = {}

  try {
    const res = await useSanctumFetch(`${apiBase}/contact/business`, {
      method: 'POST',
      body: bizForm.value
    })

    const data = res
    if (data && data.success === true) {
      toast.success({
        title: 'Business Inquiry Sent!',
        message: data.message || 'Thank you for reaching out regarding a business inquiry. Our team will carefully review your message and contact you within 2–3 business days.',
        timeout: 5000
      })
      bizForm.value = {
        company_name: '', address: '', email: '',
        name: '', phone: '', website: '', message: ''
      }
      return
    }

    const apiErrors = data?.errors
    if (apiErrors && typeof apiErrors === 'object') {
      errors.value.business = Object.fromEntries(
          Object.entries(apiErrors).map(([k, v]: any) => [k, Array.isArray(v) ? v[0] : String(v)])
      )
    }

    toast.error({
      title: 'Error',
      message: data?.message || 'Could not send business inquiry. Please try again.',
      timeout: 4000
    })
  } catch (e: any) {
    console.error('[contact:business] error:', e)
    const ve = e?.errors || {}
    if (ve && typeof ve === 'object') {
      errors.value.business = Object.fromEntries(
          Object.entries(ve).map(([k, v]: any) => [k, Array.isArray(v) ? v[0] : String(v)])
      )
    }
    toast.error({
      title: 'Network Error',
      message: e?.message || 'Could not send business inquiry. Please check your connection and try again.',
      timeout: 4000
    })
  } finally {
    submitting.value = false
  }
}

// GSAP animations
const initializeAnimations = () => {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
    // Floating orbs animation
    if (orb1.value) {
      gsap.to(orb1.value, {
        y: -40,
        rotation: 15,
        duration: 8,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: -1
      })
    }

    if (orb2.value) {
      gsap.to(orb2.value, {
        y: 30,
        rotation: -12,
        duration: 10,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: -1
      })
    }

    if (orb3.value) {
      gsap.to(orb3.value, {
        y: -25,
        rotation: 20,
        duration: 6,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: -1
      })
    }

    // Entrance animations
    if (ScrollTrigger) {
      // Header animation
      if (contactHeader.value) {
        gsap.fromTo(contactHeader.value,
            { y: -50, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 1,
              ease: 'back.out(1.7)'
            }
        )
      }

      // Contact info animation
      if (contactInfo.value) {
        gsap.fromTo(contactInfo.value,
            { x: -50, opacity: 0 },
            {
              x: 0,
              opacity: 1,
              duration: 1,
              ease: 'back.out(1.7)',
              delay: 0.2
            }
        )
      }

      // Forms animation
      if (contactForms.value) {
        gsap.fromTo(contactForms.value,
            { x: 50, opacity: 0 },
            {
              x: 0,
              opacity: 1,
              duration: 1,
              ease: 'back.out(1.7)',
              delay: 0.4
            }
        )
      }
    }
  })
}

// Lifecycle
onMounted(() => {
  // Initialize animations
  setTimeout(() => {
    initializeAnimations()
  }, 100)
})

onUnmounted(() => {
  if (gsapContext) {
    gsapContext.kill()
  }

  if (process.client && ScrollTrigger) {
    ScrollTrigger.getAll().forEach((trigger: any) => trigger.kill())
  }
})

// SEO
useSeoMeta({
  title: 'Contact Us - Get In Touch',
  description: 'Have questions or need support? Contact us for general inquiries or business partnerships. We\'re here to help.',
})
</script>

<style scoped>
/* All your existing styles remain the same */
/* Performance optimizations */
.will-change-transform {
  will-change: transform;
}

/* Contact method hover effects */
.contact-method {
  position: relative;
  transition: all 0.3s ease;
}

.contact-method:hover {
  transform: translateY(-5px);
}

/* Form animations */
.form-group {
  position: relative;
}

.form-group input:focus,
.form-group textarea:focus {
  transform: scale(1.02);
}

/* Submit button effects */
.submit-btn {
  position: relative;
  overflow: hidden;
}

.submit-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.6s ease;
}

.submit-btn:hover::before {
  left: 100%;
}

/* Toggle button effects */
.toggle-btn {
  position: relative;
  overflow: hidden;
}

.toggle-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1));
  transform: translateX(-100%);
  transition: transform 0.6s ease;
}

.toggle-btn:hover::before {
  transform: translateX(100%);
}

/* Particle animations */
.particle {
  animation: float 6s ease-in-out infinite;
  will-change: transform;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
  }
}

/* Responsive design */
@media (max-width: 1024px) {
  .contact-orb-2 {
    display: none;
  }
}

@media (max-width: 640px) {
  .toggle-buttons {
    flex-direction: column;
    gap: 0.75rem;
  }

  .toggle-btn {
    text-align: center;
  }

  .contact-methods {
    gap: 1rem;
  }

  .contact-method {
    padding: 1rem;
  }
}

@media (max-width: 480px) {
  .contact-orb-1, .contact-orb-3 {
    width: 4rem;
    height: 4rem;
  }

  .particles {
    display: none;
  }
}

/* Focus states for accessibility */
.contact-method:focus-visible,
.toggle-btn:focus-visible,
.submit-btn:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .contact-info-card,
  .contact-forms-card {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }
}

/* Error state animation */
.error-message {
  animation: slideInUp 0.3s ease-out;
}

@keyframes slideInUp {
  from {
    transform: translateY(10px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
</style>
