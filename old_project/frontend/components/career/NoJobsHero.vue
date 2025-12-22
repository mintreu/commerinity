<template>
  <section
      class="relative px-6 py-32 text-center bg-gray-50 dark:bg-gray-900 rounded-lg overflow-hidden"
      ref="heroRef"
  >
    <Icon
        name="mdi:briefcase-off-outline"
        class="mx-auto text-6xl text-gray-400 dark:text-gray-600 mb-6 opacity-0"
        ref="iconRef"
    />
    <h2 class="text-4xl font-bold mb-4 opacity-0" ref="titleRef">
      No Open Positions Right Now
    </h2>
    <p class="text-lg text-gray-600 dark:text-gray-400 max-w-xl mx-auto opacity-0" ref="textRef">
      We’re not actively hiring at the moment, but we’d love to hear from talented people.
    </p>
    <button
        class="mt-8 inline-flex items-center gap-2 text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 px-6 py-3 rounded-full opacity-0 transition-all shadow-md hover:shadow-blue-500/40 hover:scale-105"
        ref="buttonRef"
        @click="showModal = true"
    >
      <Icon name="mdi:upload" class="text-xl animate-pulse" />
      Send Resume
    </button>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg w-full max-w-lg relative shadow-lg transition-all animate-fade-in">
        <button
            class="absolute top-2 right-2 text-gray-400 hover:text-red-500 hover:scale-110 transition-all"
            @click="closeModal"
        >
          ✕
        </button>

        <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white">Send Your Resume</h3>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block mb-1 text-gray-700 dark:text-gray-200">Name *</label>
            <input
                v-model="form.name"
                type="text"
                class="w-full px-4 py-2 border rounded bg-white dark:bg-gray-700 text-black dark:text-white"
                :class="{ 'border-red-500': errors.name }"
            />
            <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
          </div>

          <div>
            <label class="block mb-1 text-gray-700 dark:text-gray-200">Email *</label>
            <input
                v-model="form.email"
                type="email"
                class="w-full px-4 py-2 border rounded bg-white dark:bg-gray-700 text-black dark:text-white"
                :class="{ 'border-red-500': errors.email }"
            />
            <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
          </div>

          <div>
            <label class="block mb-1 text-gray-700 dark:text-gray-200">Profile Link (Optional)</label>
            <input
                v-model="form.profile"
                type="url"
                class="w-full px-4 py-2 border rounded bg-white dark:bg-gray-700 text-black dark:text-white"
                placeholder="https://yourportfolio.com"
            />
          </div>

          <div>
            <label class="block mb-1 text-gray-700 dark:text-gray-200">Upload Resume *</label>
            <input type="file" accept=".pdf,.doc,.docx" @change="handleFileChange" class="w-full" />
            <p class="text-xs text-gray-500 mt-1">Accepted: PDF, DOC, DOCX | Max 2MB</p>
            <p v-if="errors.file" class="text-red-500 text-sm mt-1">{{ errors.file }}</p>
          </div>

          <p v-if="error" class="text-red-500 text-sm mt-2">{{ error }}</p>
          <p v-if="success" class="text-green-500 text-sm mt-2">{{ success }}</p>

          <button
              type="submit"
              :disabled="loading"
              class="bg-blue-600 text-white px-6 py-2 rounded shadow-lg hover:bg-blue-700 hover:shadow-blue-500/40 transition-all hover:scale-105 disabled:opacity-50"
          >
            <span v-if="loading" class="animate-pulse">Sending...</span>
            <span v-else>Send Resume</span>
          </button>
        </form>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import gsap from 'gsap'
import { useRuntimeConfig } from '#imports'

const config = useRuntimeConfig()
const heroRef = ref(null)
const iconRef = ref(null)
const titleRef = ref(null)
const textRef = ref(null)
const buttonRef = ref(null)
const showModal = ref(false)

onMounted(() => {
  const tl = gsap.timeline()
  tl.to(iconRef.value, { opacity: 1, y: -20, duration: 0.6 })
      .to(titleRef.value, { opacity: 1, y: -10, duration: 0.5 }, '-=0.3')
      .to(textRef.value, { opacity: 1, y: -5, duration: 0.5 }, '-=0.3')
      .to(buttonRef.value, { opacity: 1, y: -5, duration: 0.5 }, '-=0.3')
})

// Form state
const form = ref({ name: '', email: '', profile: '' })
const file = ref<File | null>(null)
const loading = ref(false)
const error = ref('')
const success = ref('')
const errors = ref<{ name?: string; email?: string; file?: string }>({})

function closeModal() {
  showModal.value = false
  resetForm()
}

function handleFileChange(e: Event) {
  const target = e.target as HTMLInputElement
  const f = target.files?.[0] || null

  if (!f) {
    errors.value.file = 'File is required.'
    return
  }

  const validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
  if (!validTypes.includes(f.type)) {
    errors.value.file = 'Invalid file type.'
    file.value = null
    return
  }

  if (f.size > 2 * 1024 * 1024) {
    errors.value.file = 'File too large (max 2MB).'
    file.value = null
    return
  }

  file.value = f
  errors.value.file = ''
}

function validateForm() {
  let isValid = true
  errors.value = {}

  if (!form.value.name) {
    errors.value.name = 'Name is required.'
    isValid = false
  }

  if (!form.value.email) {
    errors.value.email = 'Email is required.'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    errors.value.email = 'Invalid email format.'
    isValid = false
  }

  if (!file.value) {
    errors.value.file = 'Resume file is required.'
    isValid = false
  }

  return isValid
}

async function handleSubmit() {
  error.value = ''
  success.value = ''

  if (!validateForm()) return

  loading.value = true
  const formData = new FormData()
  formData.append('name', form.value.name)
  formData.append('email', form.value.email)
  formData.append('profile', form.value.profile || '')
  formData.append('resume', file.value!)

  try {
    await $fetch(`${config.public.apiBase}/career/send-resume`, {
      method: 'POST',
      body: formData
    })
    success.value = '🎉 Resume sent successfully! We’ll contact you soon.'
    resetForm()
  } catch (e) {
    error.value = `😔 Failed to send resume. Please try again. Alternatively, email it to us directly at hr@commernity.com. We appreciate your interest!`
  } finally {
    loading.value = false
  }
}

function resetForm() {
  form.value = { name: '', email: '', profile: '' }
  file.value = null
  errors.value = {}
}
</script>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
.animate-fade-in {
  animation: fade-in 0.4s ease-out;
}
</style>
