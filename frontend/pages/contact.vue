<template>
  <div class="max-w-3xl mx-auto px-4 py-16 text-gray-800 dark:text-gray-200">
    <h1 class="text-3xl font-bold mb-6">Contact Us</h1>

    <!-- Toggle Buttons -->
    <div class="flex gap-4 mb-8">
      <button
          @click="formType = 'user'"
          :class="[
          'px-4 py-2 rounded font-medium',
          formType === 'user'
            ? 'bg-blue-600 text-white'
            : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
        ]"
      >
        General Inquiry
      </button>
      <button
          @click="formType = 'business'"
          :class="[
          'px-4 py-2 rounded font-medium',
          formType === 'business'
            ? 'bg-blue-600 text-white'
            : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
        ]"
      >
        Business Inquiry
      </button>
    </div>

    <!-- General User Contact Form -->
    <form v-if="formType === 'user'" class="space-y-4 mb-12">
      <input type="text" placeholder="Your Name" class="w-full px-4 py-2 border rounded" required />
      <input type="email" placeholder="Your Email" class="w-full px-4 py-2 border rounded" required />
      <textarea placeholder="Your Message" class="w-full px-4 py-2 border rounded h-32" required></textarea>
      <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
        Send Message
      </button>
    </form>

    <!-- Business Contact Form -->
    <form v-if="formType === 'business'" class="space-y-4 mb-12">
      <input type="text" placeholder="Company Name" class="w-full px-4 py-2 border rounded" required />
      <input type="text" placeholder="Company Address" class="w-full px-4 py-2 border rounded" required />
      <input type="email" placeholder="Business Email" class="w-full px-4 py-2 border rounded" required />
      <input type="tel" placeholder="Mobile Number" class="w-full px-4 py-2 border rounded" required />
      <input type="text" placeholder="Website (optional)" class="w-full px-4 py-2 border rounded" />
      <textarea placeholder="Business Inquiry" class="w-full px-4 py-2 border rounded h-32" required></textarea>
      <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
        Contact Us
      </button>
    </form>

    <!-- Contact Information -->
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
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const formType = ref<'user' | 'business'>('user')

// Inject public runtime config values
const config = useRuntimeConfig()
const supportEmail = config.public.supportEmail
const companyName = config.public.companyName
const phoneNumber = config.public.phoneNumber
const address = config.public.address
</script>
