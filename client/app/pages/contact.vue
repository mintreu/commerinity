<script setup lang="ts">
definePageMeta({
  layout: 'default'
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
    const response = await useSanctumFetch<{ success: boolean; message: string }>(`${config.public.apiBase}/api/contact/user`, {
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
  }
  catch (error: any) {
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
  }
  finally {
    userLoading.value = false
  }
}

// Submit business inquiry
async function submitBusinessForm() {
  businessErrors.value = {}
  businessLoading.value = true

  try {
    const response = await useSanctumFetch<{ success: boolean; message: string }>(`${config.public.apiBase}/api/contact/business`, {
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
  }
  catch (error: any) {
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
  }
  finally {
    businessLoading.value = false
  }
}

useSeoMeta({
  title: 'Contact Us - Get In Touch',
  description: 'Have questions or need support? Contact us for general inquiries or business partnerships. We are here to help.'
})
</script>

<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 py-16">
      <UContainer>
        <div class="text-center text-white">
          <h1 class="text-4xl md:text-5xl font-bold mb-4">
            Contact Us
          </h1>
          <p class="text-xl text-white/80 max-w-2xl mx-auto">
            We're here to help. Reach out to us with any questions or concerns.
          </p>
        </div>
      </UContainer>
    </div>

    <UContainer class="py-12">
      <div class="grid lg:grid-cols-3 gap-8">
        <!-- Contact Info Sidebar -->
        <div class="space-y-6">
          <!-- Email Card -->
          <div class="glass-card p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                <UIcon
                  name="i-lucide-mail"
                  class="w-6 h-6 text-blue-600 dark:text-blue-400"
                />
              </div>
              <div>
                <h3 class="font-semibold text-slate-900 dark:text-white">
                  Email Us
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Get a response within 24 hours
                </p>
              </div>
            </div>
            <a
              :href="`mailto:${config.public.supportEmail}`"
              class="text-blue-600 dark:text-blue-400 hover:underline font-medium"
            >
              {{ config.public.supportEmail }}
            </a>
          </div>

          <!-- Phone Card -->
          <div class="glass-card p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                <UIcon
                  name="i-lucide-phone"
                  class="w-6 h-6 text-green-600 dark:text-green-400"
                />
              </div>
              <div>
                <h3 class="font-semibold text-slate-900 dark:text-white">
                  Call Us
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Mon-Sat, 9 AM - 6 PM IST
                </p>
              </div>
            </div>
            <a
              :href="`tel:${config.public.supportPhone}`"
              class="text-green-600 dark:text-green-400 hover:underline font-medium"
            >
              {{ config.public.supportPhone }}
            </a>
          </div>

          <!-- Address Card -->
          <div class="glass-card p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                <UIcon
                  name="i-lucide-map-pin"
                  class="w-6 h-6 text-purple-600 dark:text-purple-400"
                />
              </div>
              <div>
                <h3 class="font-semibold text-slate-900 dark:text-white">
                  Office Address
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Visit us in person
                </p>
              </div>
            </div>
            <p class="text-slate-600 dark:text-slate-400">
              {{ config.public.companyAddress || '123 Business Park, Suite 456, New Delhi, India - 110001' }}
            </p>
          </div>

          <!-- Company Info Card -->
          <div class="glass-card p-6 bg-gradient-to-br from-slate-50 to-blue-50 dark:from-slate-800 dark:to-blue-900/20">
            <div class="flex items-center gap-3 mb-3">
              <UIcon
                name="i-lucide-building"
                class="w-5 h-5 text-blue-600 dark:text-blue-400"
              />
              <h3 class="font-bold text-slate-900 dark:text-white">
                {{ config.public.companyName || 'Mintreu' }}
              </h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              Your trusted partner for premium products and exceptional service.
            </p>
          </div>
        </div>

        <!-- Contact Forms -->
        <div class="lg:col-span-2">
          <div class="glass-card overflow-hidden">
            <!-- Form Header with Tabs -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-white">
              <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold">
                  Send us a Message
                </h2>
                <UIcon
                  name="i-lucide-message-square"
                  class="w-6 h-6"
                />
              </div>

              <!-- Tab Toggle -->
              <UTabs
                v-model="activeTab"
                :items="tabItems"
                :content="false"
                color="neutral"
                variant="pill"
                :ui="{
                  list: 'bg-white/10 backdrop-blur-sm',
                  trigger: 'text-white/70 data-[state=active]:text-white data-[state=active]:bg-white/20'
                }"
              />
            </div>

            <!-- Form Content -->
            <div class="p-8">
              <!-- User Inquiry Form -->
              <form
                v-if="activeTab === 'user'"
                class="space-y-6"
                @submit.prevent="submitUserForm"
              >
                <div class="grid md:grid-cols-2 gap-6">
                  <UFormField
                    label="Your Name"
                    :error="userErrors.name"
                    required
                  >
                    <UInput
                      v-model="userForm.name"
                      placeholder="John Doe"
                      size="lg"
                      :color="userErrors.name ? 'error' : undefined"
                    />
                  </UFormField>

                  <UFormField
                    label="Email Address"
                    :error="userErrors.email"
                    required
                  >
                    <UInput
                      v-model="userForm.email"
                      type="email"
                      placeholder="you@example.com"
                      size="lg"
                      :color="userErrors.email ? 'error' : undefined"
                    />
                  </UFormField>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                  <UFormField
                    label="Phone Number"
                    :error="userErrors.phone"
                  >
                    <UInput
                      v-model="userForm.phone"
                      type="tel"
                      placeholder="+91 98765 43210"
                      size="lg"
                      :color="userErrors.phone ? 'error' : undefined"
                    />
                  </UFormField>

                  <UFormField
                    label="Subject"
                    :error="userErrors.subject"
                  >
                    <USelect
                      v-model="userForm.subject"
                      :items="subjects"
                      placeholder="Select a subject"
                      size="lg"
                      :color="userErrors.subject ? 'error' : undefined"
                    />
                  </UFormField>
                </div>

                <UFormField
                  label="Message"
                  :error="userErrors.message"
                  required
                >
                  <UTextarea
                    v-model="userForm.message"
                    placeholder="How can we help you? Please provide details about your inquiry..."
                    :rows="5"
                    :color="userErrors.message ? 'error' : undefined"
                  />
                </UFormField>

                <UButton
                  type="submit"
                  color="primary"
                  size="lg"
                  :loading="userLoading"
                  block
                >
                  <UIcon
                    name="i-lucide-send"
                    class="w-4 h-4 mr-2"
                  />
                  Send Message
                </UButton>
              </form>

              <!-- Business Inquiry Form -->
              <form
                v-else
                class="space-y-6"
                @submit.prevent="submitBusinessForm"
              >
                <div class="grid md:grid-cols-2 gap-6">
                  <UFormField
                    label="Company Name"
                    :error="businessErrors.company_name"
                    required
                  >
                    <UInput
                      v-model="businessForm.company_name"
                      placeholder="Acme Inc."
                      size="lg"
                      :color="businessErrors.company_name ? 'error' : undefined"
                    />
                  </UFormField>

                  <UFormField
                    label="Business Email"
                    :error="businessErrors.email"
                    required
                  >
                    <UInput
                      v-model="businessForm.email"
                      type="email"
                      placeholder="business@company.com"
                      size="lg"
                      :color="businessErrors.email ? 'error' : undefined"
                    />
                  </UFormField>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                  <UFormField
                    label="Contact Person"
                    :error="businessErrors.name"
                    required
                  >
                    <UInput
                      v-model="businessForm.name"
                      placeholder="Contact person name"
                      size="lg"
                      :color="businessErrors.name ? 'error' : undefined"
                    />
                  </UFormField>

                  <UFormField
                    label="Phone Number"
                    :error="businessErrors.phone"
                    required
                  >
                    <UInput
                      v-model="businessForm.phone"
                      type="tel"
                      placeholder="+91 98765 43210"
                      size="lg"
                      :color="businessErrors.phone ? 'error' : undefined"
                    />
                  </UFormField>
                </div>

                <UFormField
                  label="Company Address"
                  :error="businessErrors.address"
                  required
                >
                  <UInput
                    v-model="businessForm.address"
                    placeholder="123 Business Street, City, Country"
                    size="lg"
                    :color="businessErrors.address ? 'error' : undefined"
                  />
                </UFormField>

                <UFormField
                  label="Website"
                  :error="businessErrors.website"
                  hint="Optional"
                >
                  <UInput
                    v-model="businessForm.website"
                    type="url"
                    placeholder="https://example.com"
                    size="lg"
                    :color="businessErrors.website ? 'error' : undefined"
                  />
                </UFormField>

                <UFormField
                  label="Business Inquiry"
                  :error="businessErrors.message"
                  required
                >
                  <UTextarea
                    v-model="businessForm.message"
                    placeholder="Tell us about your business needs, partnership opportunities, or how we can help your company..."
                    :rows="5"
                    :color="businessErrors.message ? 'error' : undefined"
                  />
                </UFormField>

                <UButton
                  type="submit"
                  color="warning"
                  size="lg"
                  :loading="businessLoading"
                  block
                >
                  <UIcon
                    name="i-lucide-briefcase"
                    class="w-4 h-4 mr-2"
                  />
                  Submit Business Inquiry
                </UButton>
              </form>
            </div>
          </div>
        </div>
      </div>
    </UContainer>
  </div>
</template>
