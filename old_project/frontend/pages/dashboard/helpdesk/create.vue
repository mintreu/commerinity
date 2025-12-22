<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <!-- Floating Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div ref="orb1" class="absolute -top-32 -right-32 w-96 h-96 bg-blue-400/10 dark:bg-blue-400/5 rounded-full blur-3xl opacity-60 animate-pulse"></div>
      <div ref="orb2" class="absolute -bottom-32 -left-32 w-80 h-80 bg-purple-400/10 dark:bg-purple-400/5 rounded-full blur-3xl opacity-50 animate-pulse"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 max-w-4xl mx-auto p-4 sm:p-6 lg:p-8 space-y-8">

      <!-- Header -->
      <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm mb-6">
          <NuxtLink to="/dashboard" class="flex items-center gap-1 text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
            <Icon name="mdi:view-dashboard" class="w-4 h-4" />
            <span>Dashboard</span>
          </NuxtLink>
          <Icon name="mdi:chevron-right" class="w-4 h-4 text-slate-400" />
          <NuxtLink to="/dashboard/helpdesk" class="flex items-center gap-1 text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
            <Icon name="mdi:ticket" class="w-4 h-4" />
            <span>Help Desk</span>
          </NuxtLink>
          <Icon name="mdi:chevron-right" class="w-4 h-4 text-slate-400" />
          <span class="text-slate-700 dark:text-slate-300 font-medium">Create Ticket</span>
        </div>

        <!-- Title Section -->
        <div class="flex items-center gap-4">
          <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
            <Icon name="mdi:plus-circle" class="w-8 h-8 text-white" />
          </div>
          <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Create Support Ticket</h1>
            <p class="text-slate-600 dark:text-slate-400">Get help from our support team by creating a detailed ticket</p>
          </div>
        </div>
      </div>

      <!-- Main Form -->
      <form @submit.prevent="handleSubmit" class="space-y-8">

        <!-- Ticket Information -->
        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl">
          <h3 class="flex items-center gap-2 text-xl font-bold text-slate-900 dark:text-white mb-6 pb-4 border-b border-slate-200 dark:border-slate-700">
            <Icon name="mdi:ticket-outline" class="w-6 h-6 text-blue-600" />
            <span>Ticket Information</span>
          </h3>

          <div class="space-y-6">
            <!-- Title Field -->
            <div>
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                <Icon name="mdi:text" class="w-4 h-4" />
                <span>Ticket Title</span>
                <span class="text-red-500">*</span>
              </label>
              <input
                  v-model="form.title"
                  type="text"
                  required
                  placeholder="Brief summary of your issue or request"
                  class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.title }"
                  @input="clearFieldError('title')"
                  @blur="validateField('title')"
              />
              <div class="flex items-center gap-1 mt-2 text-xs text-blue-600 dark:text-blue-400">
                <Icon name="mdi:information" class="w-3 h-3" />
                <span>Use a clear, descriptive title that summarizes your issue</span>
              </div>
              <div v-if="errors.title" class="flex items-center gap-1 mt-2 text-xs text-red-500">
                <Icon name="mdi:alert-circle" class="w-3 h-3" />
                <span>{{ errors.title }}</span>
              </div>
            </div>

            <!-- Description Field -->
            <div>
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                <Icon name="mdi:text-box-outline" class="w-4 h-4" />
                <span>Detailed Description</span>
                <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <textarea
                    v-model="form.description"
                    rows="6"
                    required
                    maxlength="2000"
                    placeholder="Please provide a detailed description of your issue, including:&#10;• What you were trying to do&#10;• What happened instead&#10;• Any error messages you received&#10;• Steps to reproduce the problem"
                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-y min-h-32"
                    :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.description }"
                    @input="clearFieldError('description')"
                    @blur="validateField('description')"
                ></textarea>
                <div class="absolute bottom-3 right-3 text-xs font-medium px-2 py-1 bg-white/80 dark:bg-slate-800/80 rounded-md"
                     :class="{ 'text-red-500': form.description.length > 1900 }">
                  {{ form.description.length }} / 2000
                </div>
              </div>
              <div class="flex items-center gap-1 mt-2 text-xs text-blue-600 dark:text-blue-400">
                <Icon name="mdi:information" class="w-3 h-3" />
                <span>The more details you provide, the faster we can help you</span>
              </div>
              <div v-if="errors.description" class="flex items-center gap-1 mt-2 text-xs text-red-500">
                <Icon name="mdi:alert-circle" class="w-3 h-3" />
                <span>{{ errors.description }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Priority & Topic Settings -->
        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl">
          <h3 class="flex items-center gap-2 text-xl font-bold text-slate-900 dark:text-white mb-6 pb-4 border-b border-slate-200 dark:border-slate-700">
            <Icon name="mdi:cog-outline" class="w-6 h-6 text-purple-600" />
            <span>Ticket Settings</span>
          </h3>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Priority Field -->
            <div>
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">
                <Icon name="mdi:flag-outline" class="w-4 h-4" />
                <span>Priority Level</span>
                <span class="text-red-500">*</span>
              </label>

              <div class="space-y-3">
                <div
                    v-for="priority in priorityOptions"
                    :key="priority.value"
                    class="p-4 border-2 rounded-xl cursor-pointer transition-all hover:bg-slate-50 dark:hover:bg-slate-700/30"
                    :class="form.priority === priority.value
                    ? `${priority.borderClass} ${priority.bgClass}`
                    : 'border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800'"
                    @click="selectPriority(priority.value)"
                >
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="priority.iconBgClass">
                      <Icon :name="priority.icon" class="w-5 h-5" :class="priority.iconClass" />
                    </div>
                    <div class="flex-1">
                      <div class="font-semibold text-slate-900 dark:text-white">{{ priority.label }}</div>
                      <div class="text-sm text-slate-500 dark:text-slate-400">{{ priority.description }}</div>
                    </div>
                    <div v-if="form.priority === priority.value" class="w-6 h-6 text-blue-600 dark:text-blue-400">
                      <Icon name="mdi:check-circle" class="w-6 h-6" />
                    </div>
                  </div>
                </div>
              </div>
              <div v-if="errors.priority" class="flex items-center gap-1 mt-2 text-xs text-red-500">
                <Icon name="mdi:alert-circle" class="w-3 h-3" />
                <span>{{ errors.priority }}</span>
              </div>
            </div>

            <!-- Topic Field -->
            <div>
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                <Icon name="mdi:tag-outline" class="w-4 h-4" />
                <span>Topic Category</span>
                <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <select
                    v-model="form.topic_slug"
                    required
                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none"
                    :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.topic_slug }"
                    @change="clearFieldError('topic_slug')"
                    @blur="validateField('topic_slug')"
                >
                  <option value="" disabled>Select a topic category</option>
                  <option
                      v-for="topic in topics"
                      :key="topic.slug"
                      :value="topic.slug"
                  >
                    {{ topic.name }}
                  </option>
                </select>
                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                  <Icon name="mdi:chevron-down" class="w-5 h-5 text-slate-400" />
                </div>
              </div>
              <div class="flex items-center gap-1 mt-2 text-xs text-blue-600 dark:text-blue-400">
                <Icon name="mdi:information" class="w-3 h-3" />
                <span>Choose the category that best describes your issue</span>
              </div>
              <div v-if="errors.topic_slug" class="flex items-center gap-1 mt-2 text-xs text-red-500">
                <Icon name="mdi:alert-circle" class="w-3 h-3" />
                <span>{{ errors.topic_slug }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Attachments -->
        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl">
          <h3 class="flex items-center gap-2 text-xl font-bold text-slate-900 dark:text-white mb-6 pb-4 border-b border-slate-200 dark:border-slate-700">
            <Icon name="mdi:paperclip" class="w-6 h-6 text-green-600" />
            <span>Attachments</span>
            <span class="text-sm font-normal text-slate-500 dark:text-slate-400">(Optional)</span>
          </h3>

          <!-- File Upload Area -->
          <div>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
              <Icon name="mdi:camera" class="w-4 h-4" />
              <span>Screenshot or File</span>
              <span class="text-slate-500 font-normal">(Optional)</span>
            </label>

            <div class="border-2 border-dashed rounded-xl p-8 text-center transition-all"
                 :class="form.screenshot
                   ? 'border-green-300 bg-green-50 dark:border-green-700 dark:bg-green-900/10'
                   : 'border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/30 hover:border-blue-400 hover:bg-blue-50 dark:hover:border-blue-500 dark:hover:bg-blue-900/10'">

              <input
                  type="file"
                  ref="fileInput"
                  accept="image/*,.pdf,.doc,.docx,.txt"
                  @change="handleFileChange"
                  class="hidden"
              />

              <div v-if="!form.screenshot" class="cursor-pointer" @click="triggerFileSelect">
                <Icon name="mdi:cloud-upload-outline" class="w-16 h-16 mx-auto text-slate-400 mb-4" />
                <p class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-2">Click to upload or drag and drop</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">PNG, JPG, PDF, DOC up to 10MB</p>
              </div>

              <div v-else class="flex items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-600">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-lg flex items-center justify-center" :class="getFileIconClass(form.screenshot.type)">
                    <Icon :name="getFileIcon(form.screenshot.type)" class="w-6 h-6" />
                  </div>
                  <div>
                    <div class="font-semibold text-slate-900 dark:text-white">{{ form.screenshot.name }}</div>
                    <div class="text-sm text-slate-500 dark:text-slate-400">{{ formatFileSize(form.screenshot.size) }}</div>
                  </div>
                </div>
                <button
                    type="button"
                    @click="removeFile"
                    class="w-8 h-8 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-lg flex items-center justify-center transition-colors"
                >
                  <Icon name="mdi:close" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <div class="flex items-center gap-1 mt-2 text-xs text-blue-600 dark:text-blue-400">
              <Icon name="mdi:information" class="w-3 h-3" />
              <span>Screenshots and relevant files help us understand your issue better</span>
            </div>
            <div v-if="errors.screenshot" class="flex items-center gap-1 mt-2 text-xs text-red-500">
              <Icon name="mdi:alert-circle" class="w-3 h-3" />
              <span>{{ errors.screenshot }}</span>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
              <NuxtLink to="/dashboard/helpdesk" class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl transition-colors font-medium">
                <Icon name="mdi:arrow-left" class="w-4 h-4" />
                <span>Back to Tickets</span>
              </NuxtLink>
            </div>

            <div class="flex items-center gap-3">
              <button
                  type="button"
                  @click="resetForm"
                  :disabled="processing || isFormEmpty"
                  class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed text-slate-700 dark:text-slate-300 rounded-xl transition-colors font-medium"
              >
                <Icon name="mdi:refresh" class="w-4 h-4" />
                <span>Reset Form</span>
              </button>

              <button
                  type="submit"
                  :disabled="processing || !isFormValid"
                  class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl transition-all font-semibold shadow-lg"
              >
                <Icon
                    :name="processing ? 'mdi:loading' : 'mdi:send'"
                    class="w-5 h-5"
                    :class="{ 'animate-spin': processing }"
                />
                <span>{{ processing ? 'Creating Ticket...' : 'Create Ticket' }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Help Section -->
        <div class="bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-6 shadow-xl">
          <div class="flex items-start gap-4 mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
              <Icon name="mdi:help-circle" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-emerald-900 dark:text-emerald-100 mb-2">Need Help?</h3>
              <p class="text-emerald-700 dark:text-emerald-300">Check our resources before creating a ticket</p>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Tips -->
            <div class="lg:col-span-2">
              <h4 class="flex items-center gap-2 text-lg font-semibold text-emerald-900 dark:text-emerald-100 mb-4">
                <Icon name="mdi:lightbulb-outline" class="w-5 h-5" />
                <span>Tips for Better Support</span>
              </h4>
              <div class="space-y-3">
                <div class="flex items-start gap-3">
                  <Icon name="mdi:check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 mt-0.5 flex-shrink-0" />
                  <span class="text-sm text-emerald-700 dark:text-emerald-300">Be specific about what you were trying to accomplish</span>
                </div>
                <div class="flex items-start gap-3">
                  <Icon name="mdi:check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 mt-0.5 flex-shrink-0" />
                  <span class="text-sm text-emerald-700 dark:text-emerald-300">Include any error messages you received</span>
                </div>
                <div class="flex items-start gap-3">
                  <Icon name="mdi:check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 mt-0.5 flex-shrink-0" />
                  <span class="text-sm text-emerald-700 dark:text-emerald-300">Mention your browser and device information</span>
                </div>
                <div class="flex items-start gap-3">
                  <Icon name="mdi:check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 mt-0.5 flex-shrink-0" />
                  <span class="text-sm text-emerald-700 dark:text-emerald-300">Attach screenshots showing the problem</span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-3">
              <NuxtLink to="/dashboard/faq" class="flex items-center gap-2 px-4 py-3 bg-white dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 rounded-xl transition-colors font-medium">
                <Icon name="mdi:book-open-page-variant" class="w-5 h-5" />
                <span>Browse FAQ</span>
              </NuxtLink>
              <NuxtLink to="/dashboard/help" class="flex items-center gap-2 px-4 py-3 bg-white dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 rounded-xl transition-colors font-medium">
                <Icon name="mdi:help-circle-outline" class="w-5 h-5" />
                <span>Help Center</span>
              </NuxtLink>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useRuntimeConfig, useToast, useSanctumFetch, useRouter } from '#imports'

// GSAP imports (client-side only)
let gsap: any = null
if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

definePageMeta({ layout: 'dashboard' })

/* Core */
const config = useRuntimeConfig()
const toast = useToast()
const router = useRouter()
const processing = ref(false)

/* Types */
type Topic = {
  slug: string
  name: string
}

type Priority = {
  value: string
  label: string
  description: string
  icon: string
  iconClass: string
  iconBgClass: string
  borderClass: string
  bgClass: string
}

/* State */
const topics = ref<Topic[]>([])
const form = reactive({
  title: '',
  description: '',
  priority: 'medium',
  screenshot: null as File | null,
  topic_slug: ''
})
const errors = reactive<Record<string, string>>({})

// Refs
const fileInput = ref<HTMLInputElement>()
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()

/* Priority Options */
const priorityOptions: Priority[] = [
  {
    value: 'low',
    label: 'Low Priority',
    description: 'General questions, feature requests',
    icon: 'mdi:chevron-double-down',
    iconClass: 'text-green-600 dark:text-green-400',
    iconBgClass: 'bg-green-100 dark:bg-green-900/30',
    borderClass: 'border-green-300 dark:border-green-700',
    bgClass: 'bg-green-50 dark:bg-green-900/20'
  },
  {
    value: 'medium',
    label: 'Medium Priority',
    description: 'Account issues, billing questions',
    icon: 'mdi:minus',
    iconClass: 'text-yellow-600 dark:text-yellow-400',
    iconBgClass: 'bg-yellow-100 dark:bg-yellow-900/30',
    borderClass: 'border-yellow-300 dark:border-yellow-700',
    bgClass: 'bg-yellow-50 dark:bg-yellow-900/20'
  },
  {
    value: 'high',
    label: 'High Priority',
    description: 'Service problems, security concerns',
    icon: 'mdi:chevron-double-up',
    iconClass: 'text-orange-600 dark:text-orange-400',
    iconBgClass: 'bg-orange-100 dark:bg-orange-900/30',
    borderClass: 'border-orange-300 dark:border-orange-700',
    bgClass: 'bg-orange-50 dark:bg-orange-900/20'
  },
  {
    value: 'urgent',
    label: 'Urgent',
    description: 'Critical issues, system outages',
    icon: 'mdi:alert',
    iconClass: 'text-red-600 dark:text-red-400',
    iconBgClass: 'bg-red-100 dark:bg-red-900/30',
    borderClass: 'border-red-300 dark:border-red-700',
    bgClass: 'bg-red-50 dark:bg-red-900/20'
  }
]

/* Helper Functions */
function selectPriority(priority: string) {
  form.priority = priority
  clearFieldError('priority')
}

function triggerFileSelect() {
  fileInput.value?.click()
}

function handleFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]

  if (file) {
    // Validate file size (10MB max)
    if (file.size > 10 * 1024 * 1024) {
      errors.screenshot = 'File size must be less than 10MB'
      return
    }

    form.screenshot = file
    clearFieldError('screenshot')
  }
}

function removeFile() {
  form.screenshot = null
  clearFieldError('screenshot')
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

function getFileIcon(fileType: string) {
  if (fileType.startsWith('image/')) return 'mdi:image'
  if (fileType.includes('pdf')) return 'mdi:file-pdf-box'
  if (fileType.includes('doc')) return 'mdi:file-word-box'
  if (fileType.includes('txt')) return 'mdi:file-document'
  return 'mdi:file'
}

function getFileIconClass(fileType: string) {
  if (fileType.startsWith('image/')) return 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400'
  if (fileType.includes('pdf')) return 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'
  if (fileType.includes('doc')) return 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
  return 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400'
}

function formatFileSize(bytes: number) {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Error management functions
function clearFieldError(field: string) {
  if (errors[field]) {
    delete errors[field]
  }
}

function clearAllErrors() {
  Object.keys(errors).forEach(k => delete errors[k])
}

function validateField(field: string) {
  // Clear existing error first
  clearFieldError(field)

  switch (field) {
    case 'title':
      if (!form.title.trim()) {
        errors.title = 'Ticket title is required'
      } else if (form.title.trim().length < 10) {
        errors.title = 'Title must be at least 10 characters long'
      }
      break

    case 'description':
      if (!form.description.trim()) {
        errors.description = 'Description is required'
      } else if (form.description.trim().length < 20) {
        errors.description = 'Description must be at least 20 characters long'
      } else if (form.description.length > 2000) {
        errors.description = 'Description cannot exceed 2000 characters'
      }
      break

    case 'topic_slug':
      if (!form.topic_slug) {
        errors.topic_slug = 'Please select a topic category'
      }
      break

    case 'priority':
      if (!form.priority) {
        errors.priority = 'Please select a priority level'
      }
      break
  }
}

function validateForm() {
  clearAllErrors()

  validateField('title')
  validateField('description')
  validateField('topic_slug')
  validateField('priority')

  return Object.keys(errors).length === 0
}

function resetForm() {
  form.title = ''
  form.description = ''
  form.priority = 'medium'
  form.screenshot = null
  form.topic_slug = ''
  clearAllErrors()

  if (fileInput.value) {
    fileInput.value.value = ''
  }

  toast.success({ title: 'Form Reset', message: 'Form has been cleared' })
}

/* Computed */
const isFormValid = computed(() => {
  const hasRequiredFields = form.title.trim() &&
      form.description.trim() &&
      form.topic_slug &&
      form.priority

  const hasNoErrors = Object.keys(errors).length === 0

  const hasValidLength = form.title.trim().length >= 10 &&
      form.description.trim().length >= 20 &&
      form.description.length <= 2000

  return hasRequiredFields && hasNoErrors && hasValidLength
})

const isFormEmpty = computed(() => {
  return !form.title &&
      !form.description &&
      !form.screenshot &&
      form.topic_slug === '' &&
      form.priority === 'medium'
})

/* API Functions */
async function fetchTopics() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/helpdesk/topics/ticket`)
    topics.value = res?.data ?? []
  } catch (e: any) {
    console.error('Failed to load topics:', e)
    toast.error({
      title: 'Error',
      message: 'Failed to load topic categories'
    })
    topics.value = []
  }
}

async function handleSubmit() {
  if (!validateForm()) {
    toast.error({
      title: 'Validation Error',
      message: 'Please fix the highlighted errors'
    })
    return
  }

  processing.value = true

  try {
    const formData = new FormData()
    formData.append('title', form.title.trim())
    formData.append('description', form.description.trim())
    formData.append('priority', form.priority)
    formData.append('topic_slug', form.topic_slug)

    if (form.screenshot) {
      formData.append('screenshot', form.screenshot)
    }

    const res = await useSanctumFetch(`${config.public.apiBase}/helpdesk/tickets`, {
      method: 'POST',
      body: formData,
    })

    if (res?.error) {
      throw new Error(res.error)
    }

    toast.success({
      title: 'Success',
      message: 'Support ticket created successfully!'
    })

    router.push('/dashboard/helpdesk')
  } catch (e: any) {
    console.error('Ticket creation failed:', e)

    // Handle server validation errors
    const serverErrors = e?.data?.errors
    if (serverErrors && typeof serverErrors === 'object') {
      clearAllErrors()
      Object.entries(serverErrors).forEach(([key, value]) => {
        errors[key] = Array.isArray(value) ? value.join(', ') : String(value)
      })
    }

    toast.error({
      title: 'Error',
      message: e?.data?.message || 'Failed to create support ticket'
    })
  } finally {
    processing.value = false
  }
}

// Watch form fields for real-time validation clearing
watch(() => form.title, (newValue, oldValue) => {
  if (newValue !== oldValue && errors.title) {
    if (newValue.trim().length >= 10) {
      clearFieldError('title')
    }
  }
})

watch(() => form.description, (newValue, oldValue) => {
  if (newValue !== oldValue && errors.description) {
    if (newValue.trim().length >= 20 && newValue.length <= 2000) {
      clearFieldError('description')
    }
  }
})

watch(() => form.topic_slug, (newValue, oldValue) => {
  if (newValue !== oldValue && errors.topic_slug) {
    if (newValue) {
      clearFieldError('topic_slug')
    }
  }
})

watch(() => form.priority, (newValue, oldValue) => {
  if (newValue !== oldValue && errors.priority) {
    if (newValue) {
      clearFieldError('priority')
    }
  }
})

// Lifecycle
onMounted(async () => {
  await fetchTopics()

  if (process.client && gsap) {
    gsap.to([orb1.value, orb2.value], {
      rotation: 360,
      duration: 20,
      repeat: -1,
      ease: 'none'
    })
  }
})
</script>

<style scoped>
/* Custom scrollbar for textarea */
textarea::-webkit-scrollbar {
  width: 4px;
}

textarea::-webkit-scrollbar-track {
  background: transparent;
}

textarea::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.5);
  border-radius: 2px;
}

textarea::-webkit-scrollbar-thumb:hover {
  background: rgba(148, 163, 184, 0.7);
}
</style>
