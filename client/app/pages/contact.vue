<script setup lang="ts">
definePageMeta({
  layout: 'public'
})

const config = useRuntimeConfig()
const toast = useToast()

// Form type toggle
const activeTab = ref('user')

// User inquiry form
const userForm = ref({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: ''
})

// Business inquiry form
const businessForm = ref({
  name: '',
  email: '',
  phone: '',
  company_name: '',
  address: '',
  website: '',
  message: ''
})

// Form errors
const userErrors = ref<Record<string, string>>({})
const businessErrors = ref<Record<string, string>>({})

// Loading states
const userLoading = ref(false)
const businessLoading = ref(false)

// Subject options for user form
const subjects = [
  { label: 'General Inquiry', value: 'general' },
  { label: 'Product Support', value: 'product' },
  { label: 'Order Issue', value: 'order' },
  { label: 'Membership Query', value: 'membership' },
  { label: 'Partnership', value: 'partnership' },
  { label: 'Other', value: 'other' }
]

// Tab items
const tabItems = [
  { label: 'General Inquiry', value: 'user', icon: 'i-lucide-user' },
  { label: 'Business Inquiry', value: 'business', icon: 'i-lucide-building-2' }
]

// Clear errors when switching tabs
watch(activeTab, () => {
  userErrors.value = {}
  businessErrors.value = {}
})

// Submit user inquiry
async function submitUserForm() {
  userErrors.value = {}
  userLoading.value = true

  try {
    const response = await useSanctumFetch<{ success: boolean, message: string }>(`${config.public.apiBase}/api/contact/user`, {
      method: 'POST',
      body: userForm.value
    })

    if (response?.success) {
      toast.add({
        title: 'Message Sent!',
        description: response.message || 'Thank you for contacting us. We will get back to you within 24-48 hours.',
        color: 'success'
      })

      // Reset form
      userForm.value = {
        name: '',
        email: '',
        phone: '',
        subject: '',
        message: ''
      }
    }
  } catch (error: any) {
    // Handle validation errors
    if (error?.data?.errors) {
      userErrors.value = Object.fromEntries(
        Object.entries(error.data.errors).map(([k, v]: [string, any]) => [k, Array.isArray(v) ? v[0] : String(v)])
      )
    }

    toast.add({
      title: 'Error',
      description: error?.data?.message || 'Could not send message. Please try again.',
      color: 'error'
    })
  } finally {
    userLoading.value = false
  }
}

// Submit business inquiry
async function submitBusinessForm() {
  businessErrors.value = {}
  businessLoading.value = true

  try {
    const response = await useSanctumFetch<{ success: boolean, message: string }>(`${config.public.apiBase}/api/contact/business`, {
      method: 'POST',
      body: businessForm.value
    })

    if (response?.success) {
      toast.add({
        title: 'Business Inquiry Sent!',
        description: response.message || 'Thank you for your business inquiry. Our team will review your message and contact you within 2-3 business days.',
        color: 'success'
      })

      // Reset form
      businessForm.value = {
        name: '',
        email: '',
        phone: '',
        company_name: '',
        address: '',
        website: '',
        message: ''
      }
    }
  } catch (error: any) {
    // Handle validation errors
    if (error?.data?.errors) {
      businessErrors.value = Object.fromEntries(
        Object.entries(error.data.errors).map(([k, v]: [string, any]) => [k, Array.isArray(v) ? v[0] : String(v)])
      )
    }

    toast.add({
      title: 'Error',
      description: error?.data?.message || 'Could not send business inquiry. Please try again.',
      color: 'error'
    })
  } finally {
    businessLoading.value = false
  }
}

useSeoMeta({
  title: 'Contact Us - Get In Touch',
  description: 'Have questions or need support? Contact us for general inquiries or business partnerships. We are here to help.'
})
</script>

<template>
  <div class="contact-page min-h-screen">
    <!-- Hero Section -->
    <div class="policy-hero bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 py-16 md:py-20">
      <UContainer>
        <div class="text-center text-white">
          <div class="policy-badge inline-flex items-center px-5 py-2.5 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 mb-6">
            <UIcon
              name="i-lucide-mail"
              class="w-4 h-4 mr-2"
            />
            <span>Get In Touch</span>
          </div>
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 tracking-tight">
            Contact Us
          </h1>
          <p class="text-xl text-white/80 max-w-2xl mx-auto">
            We're here to help. Reach out to us with any questions or concerns.
          </p>
        </div>
      </UContainer>
    </div>

    <UContainer class="py-12 md:py-16">
      <!-- Quick Contact Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10 max-w-5xl mx-auto">
        <div class="policy-quick-card">
          <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center mx-auto mb-3">
            <UIcon
              name="i-lucide-mail"
              class="w-6 h-6 text-white"
            />
          </div>
          <h4>Email</h4>
          <p class="text-xs">
            24hr Response
          </p>
        </div>
        <div class="policy-quick-card">
          <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-500 rounded-xl flex items-center justify-center mx-auto mb-3">
            <UIcon
              name="i-lucide-phone"
              class="w-6 h-6 text-white"
            />
          </div>
          <h4>Phone</h4>
          <p class="text-xs">
            9 AM - 6 PM IST
          </p>
        </div>
        <div class="policy-quick-card">
          <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-500 rounded-xl flex items-center justify-center mx-auto mb-3">
            <UIcon
              name="i-lucide-map-pin"
              class="w-6 h-6 text-white"
            />
          </div>
          <h4>Office</h4>
          <p class="text-xs">
            Visit Us
          </p>
        </div>
        <div class="policy-quick-card">
          <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl flex items-center justify-center mx-auto mb-3">
            <UIcon
              name="i-lucide-headphones"
              class="w-6 h-6 text-white"
            />
          </div>
          <h4>Support</h4>
          <p class="text-xs">
            24/7 Available
          </p>
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
        <!-- Contact Info Sidebar -->
        <div class="space-y-5 lg:order-2">
          <!-- Email Card -->
          <div class="glass-card p-5 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center shrink-0 shadow-lg shadow-blue-500/25">
                <UIcon
                  name="i-lucide-mail"
                  class="w-6 h-6 text-white"
                />
              </div>
              <div class="min-w-0 flex-1">
                <h3 class="font-bold text-slate-900 dark:text-white text-lg mb-0.5">
                  Email Us
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">
                  Get a response within 24 hours
                </p>
                <a
                  :href="`mailto:${config.public.supportEmail}`"
                  class="text-blue-600 dark:text-blue-400 hover:underline font-semibold text-sm break-all"
                >
                  {{ config.public.supportEmail }}
                </a>
              </div>
            </div>
          </div>

          <!-- Phone Card -->
          <div class="glass-card p-5 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-green-500 rounded-xl flex items-center justify-center shrink-0 shadow-lg shadow-emerald-500/25">
                <UIcon
                  name="i-lucide-phone"
                  class="w-6 h-6 text-white"
                />
              </div>
              <div class="min-w-0 flex-1">
                <h3 class="font-bold text-slate-900 dark:text-white text-lg mb-0.5">
                  Call Us
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">
                  Mon-Sat, 9 AM - 6 PM IST
                </p>
                <a
                  :href="`tel:${config.public.supportPhone}`"
                  class="text-emerald-600 dark:text-emerald-400 hover:underline font-semibold text-sm"
                >
                  {{ config.public.supportPhone }}
                </a>
              </div>
            </div>
          </div>

          <!-- Address Card -->
          <div class="glass-card p-5 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 bg-gradient-to-br from-violet-500 to-purple-500 rounded-xl flex items-center justify-center shrink-0 shadow-lg shadow-violet-500/25">
                <UIcon
                  name="i-lucide-map-pin"
                  class="w-6 h-6 text-white"
                />
              </div>
              <div class="min-w-0 flex-1">
                <h3 class="font-bold text-slate-900 dark:text-white text-lg mb-0.5">
                  Office Address
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">
                  Visit us in person
                </p>
                <p class="text-slate-700 dark:text-slate-300 text-sm font-medium leading-relaxed">
                  {{ config.public.companyAddress || '123 Business Park, Suite 456, New Delhi, India - 110001' }}
                </p>
              </div>
            </div>
          </div>

          <!-- Company Info Card -->
          <div class="glass-card p-5 bg-gradient-to-br from-slate-50 to-blue-50 dark:from-slate-800 dark:to-blue-900/20 border-l-4 border-blue-500">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                <UIcon
                  name="i-lucide-building"
                  class="w-5 h-5 text-blue-600 dark:text-blue-400"
                />
              </div>
              <h3 class="font-bold text-slate-900 dark:text-white text-lg">
                {{ config.public.companyName || 'Mintreu' }}
              </h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
              Your trusted partner for premium products and exceptional service. We're committed to helping you succeed.
            </p>
          </div>
        </div>

        <!-- Contact Forms -->
        <div class="lg:col-span-2 lg:order-1">
          <div class="glass-card overflow-hidden">
            <!-- Form Header with Tabs -->
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-6 md:p-8 text-white">
              <div class="flex items-center justify-between mb-5">
                <div>
                  <h2 class="text-2xl md:text-3xl font-bold mb-1">
                    Send us a Message
                  </h2>
                  <p class="text-white/70 text-sm">
                    Fill out the form below and we'll get back to you
                  </p>
                </div>
                <div class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-xl flex items-center justify-center">
                  <UIcon
                    name="i-lucide-message-square"
                    class="w-6 h-6"
                  />
                </div>
              </div>

              <!-- Tab Toggle -->
              <UTabs
                v-model="activeTab"
                :items="tabItems"
                :content="false"
                color="neutral"
                variant="pill"
                :ui="{
                  list: 'bg-white/10 backdrop-blur-sm p-1 rounded-xl',
                  trigger: 'text-white/70 data-[state=active]:text-white data-[state=active]:bg-white/20 rounded-lg px-4 py-2.5 font-medium transition-all'
                }"
              />
            </div>

            <!-- Form Content -->
            <div class="p-6 md:p-8">
              <!-- User Inquiry Form -->
              <form
                v-if="activeTab === 'user'"
                class="space-y-5"
                @submit.prevent="submitUserForm"
              >
                <div class="grid md:grid-cols-2 gap-5">
                  <UFormField
                    label="Your Name"
                    :error="userErrors.name"
                    required
                    class="contact-form-field"
                  >
                    <UInput
                      v-model="userForm.name"
                      placeholder="John Doe"
                      size="lg"
                      :color="userErrors.name ? 'error' : undefined"
                      class="w-full"
                      :ui="{ base: 'w-full' }"
                    />
                  </UFormField>

                  <UFormField
                    label="Email Address"
                    :error="userErrors.email"
                    required
                    class="contact-form-field"
                  >
                    <UInput
                      v-model="userForm.email"
                      type="email"
                      placeholder="you@example.com"
                      size="lg"
                      :color="userErrors.email ? 'error' : undefined"
                      class="w-full"
                      :ui="{ base: 'w-full' }"
                    />
                  </UFormField>
                </div>

                <div class="grid md:grid-cols-2 gap-5">
                  <UFormField
                    label="Phone Number"
                    :error="userErrors.phone"
                    class="contact-form-field"
                  >
                    <UInput
                      v-model="userForm.phone"
                      type="tel"
                      placeholder="+91 98765 43210"
                      size="lg"
                      :color="userErrors.phone ? 'error' : undefined"
                      class="w-full"
                      :ui="{ base: 'w-full' }"
                    />
                  </UFormField>

                  <UFormField
                    label="Subject"
                    :error="userErrors.subject"
                    class="contact-form-field"
                  >
                    <USelect
                      v-model="userForm.subject"
                      :items="subjects"
                      placeholder="Select a subject"
                      size="lg"
                      :color="userErrors.subject ? 'error' : undefined"
                      class="w-full"
                      :ui="{ base: 'w-full' }"
                    />
                  </UFormField>
                </div>

                <UFormField
                  label="Message"
                  :error="userErrors.message"
                  required
                  class="contact-form-field"
                >
                  <UTextarea
                    v-model="userForm.message"
                    placeholder="How can we help you? Please provide details about your inquiry..."
                    :rows="5"
                    :color="userErrors.message ? 'error' : undefined"
                    class="w-full"
                    :ui="{ base: 'w-full' }"
                  />
                </UFormField>

                <div class="pt-2">
                  <UButton
                    type="submit"
                    color="primary"
                    size="xl"
                    :loading="userLoading"
                    block
                    class="font-semibold shadow-lg shadow-blue-500/25 hover:shadow-xl hover:shadow-blue-500/30 transition-all"
                  >
                    <UIcon
                      name="i-lucide-send"
                      class="w-5 h-5 mr-2"
                    />
                    Send Message
                  </UButton>
                </div>
              </form>

              <!-- Business Inquiry Form -->
              <form
                v-else
                class="space-y-5"
                @submit.prevent="submitBusinessForm"
              >
                <div class="grid md:grid-cols-2 gap-5">
                  <UFormField
                    label="Company Name"
                    :error="businessErrors.company_name"
                    required
                    class="contact-form-field"
                  >
                    <UInput
                      v-model="businessForm.company_name"
                      placeholder="Acme Inc."
                      size="lg"
                      :color="businessErrors.company_name ? 'error' : undefined"
                      class="w-full"
                      :ui="{ base: 'w-full' }"
                    />
                  </UFormField>

                  <UFormField
                    label="Business Email"
                    :error="businessErrors.email"
                    required
                    class="contact-form-field"
                  >
                    <UInput
                      v-model="businessForm.email"
                      type="email"
                      placeholder="business@company.com"
                      size="lg"
                      :color="businessErrors.email ? 'error' : undefined"
                      class="w-full"
                      :ui="{ base: 'w-full' }"
                    />
                  </UFormField>
                </div>

                <div class="grid md:grid-cols-2 gap-5">
                  <UFormField
                    label="Contact Person"
                    :error="businessErrors.name"
                    required
                    class="contact-form-field"
                  >
                    <UInput
                      v-model="businessForm.name"
                      placeholder="Contact person name"
                      size="lg"
                      :color="businessErrors.name ? 'error' : undefined"
                      class="w-full"
                      :ui="{ base: 'w-full' }"
                    />
                  </UFormField>

                  <UFormField
                    label="Phone Number"
                    :error="businessErrors.phone"
                    required
                    class="contact-form-field"
                  >
                    <UInput
                      v-model="businessForm.phone"
                      type="tel"
                      placeholder="+91 98765 43210"
                      size="lg"
                      :color="businessErrors.phone ? 'error' : undefined"
                      class="w-full"
                      :ui="{ base: 'w-full' }"
                    />
                  </UFormField>
                </div>

                <UFormField
                  label="Company Address"
                  :error="businessErrors.address"
                  required
                  class="contact-form-field"
                >
                  <UInput
                    v-model="businessForm.address"
                    placeholder="123 Business Street, City, Country"
                    size="lg"
                    :color="businessErrors.address ? 'error' : undefined"
                    class="w-full"
                    :ui="{ base: 'w-full' }"
                  />
                </UFormField>

                <UFormField
                  label="Website"
                  :error="businessErrors.website"
                  class="contact-form-field"
                >
                  <UInput
                    v-model="businessForm.website"
                    type="url"
                    placeholder="https://example.com"
                    size="lg"
                    :color="businessErrors.website ? 'error' : undefined"
                    class="w-full"
                    :ui="{ base: 'w-full' }"
                  />
                </UFormField>

                <UFormField
                  label="Business Inquiry"
                  :error="businessErrors.message"
                  required
                  class="contact-form-field"
                >
                  <UTextarea
                    v-model="businessForm.message"
                    placeholder="Tell us about your business needs, partnership opportunities, or how we can help your company..."
                    :rows="5"
                    :color="businessErrors.message ? 'error' : undefined"
                    class="w-full"
                    :ui="{ base: 'w-full' }"
                  />
                </UFormField>

                <div class="pt-2">
                  <UButton
                    type="submit"
                    color="warning"
                    size="xl"
                    :loading="businessLoading"
                    block
                    class="font-semibold shadow-lg shadow-amber-500/25 hover:shadow-xl hover:shadow-amber-500/30 transition-all"
                  >
                    <UIcon
                      name="i-lucide-briefcase"
                      class="w-5 h-5 mr-2"
                    />
                    Submit Business Inquiry
                  </UButton>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </UContainer>
  </div>
</template>
