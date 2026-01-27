<script setup lang="ts">
/**
 * Create Support Ticket - Premium Mintreu Design
 * Professional form for ticket submission with priority and topic selection
 */

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const { fetchTopics, createTicket } = useHelpdesk()
const toast = useToast()
const router = useRouter()

const form = reactive({
  title: '',
  description: '',
  priority: 'medium',
  topic_slug: '',
  screenshot: null as File | null
})

const topics = ref([])
const loadingTopics = ref(false)
const submitting = ref(false)

const priorityOptions = [
  { label: 'Low - General Inquiry', value: 'low', icon: 'i-lucide-circle-dot', color: 'green' },
  { label: 'Medium - Normal Issue', value: 'medium', icon: 'i-lucide-alert-circle', color: 'amber' },
  { label: 'High - Service Interruption', value: 'high', icon: 'i-lucide-zap', color: 'orange' },
  { label: 'Urgent - Critical Blocker', value: 'urgent', icon: 'i-lucide-flame', color: 'red' }
]

const handleFile = (e: any) => {
  const file = e.target.files?.[0]
  if (file && file.size > 5 * 1024 * 1024) {
    toast.add({ title: 'File too large', description: 'Maximum file size is 5MB', color: 'error' })
    return
  }
  form.screenshot = file || null
}

const handleSubmit = async () => {
  if (!form.topic_slug) {
    toast.add({ title: 'Topic Required', description: 'Please select a topic for your ticket', color: 'warning' })
    return
  }

  submitting.value = true
  try {
    const formData = new FormData()
    formData.append('title', form.title)
    formData.append('description', form.description)
    formData.append('priority', form.priority)
    formData.append('topic_slug', form.topic_slug)
    if (form.screenshot) formData.append('screenshot', form.screenshot)

    await createTicket(formData)
    toast.add({
      title: 'Ticket Submitted',
      description: 'Our support team will review your request shortly.',
      color: 'success',
      icon: 'i-lucide-check-circle'
    })
    router.push('/helpdesk')
  } catch (e: any) {
    toast.add({
      title: 'Submission Error',
      description: e.data?.message || 'Failed to create ticket. Please try again.',
      color: 'error'
    })
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  loadingTopics.value = true
  try {
    topics.value = await fetchTopics()
  } finally {
    loadingTopics.value = false
  }
})
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-8 pb-12">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-slate-400">
      <NuxtLink
        to="/helpdesk"
        class="hover:text-primary-500 transition-colors"
      >Support Center</NuxtLink>
      <UIcon name="i-lucide-chevron-right" />
      <span class="text-slate-900 dark:text-white">New Ticket</span>
    </div>

    <!-- Header Section -->
    <div class="space-y-2">
      <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
        Open a <span class="bg-gradient-to-r from-primary-600 to-indigo-600 bg-clip-text text-transparent">New Ticket</span>
      </h1>
      <p class="text-slate-500 dark:text-slate-400">
        Fill out the form below and we'll get back to you as soon as possible.
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Main Form -->
      <div class="lg:col-span-2">
        <div class="glass-card p-8 border-none ring-1 ring-slate-200 dark:ring-slate-800">
          <UForm
            :state="form"
            class="space-y-8"
            @submit="handleSubmit"
          >
            <!-- Topic & Priority Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <UFormField
                label="Select Topic"
                required
              >
                <USelectMenu
                  v-model="form.topic_slug"
                  :options="topics"
                  option-attribute="name"
                  value-attribute="slug"
                  placeholder="Choose topic..."
                  size="xl"
                  :loading="loadingTopics"
                  class="rounded-2xl"
                />
              </UFormField>

              <UFormField
                label="Priority Level"
                required
              >
                <USelectMenu
                  v-model="form.priority"
                  :options="priorityOptions"
                  value-attribute="value"
                  size="xl"
                  class="rounded-2xl"
                >
                  <template #label>
                    <div
                      v-if="form.priority"
                      class="flex items-center gap-2"
                    >
                      <UIcon
                        :name="priorityOptions.find(o => o.value === form.priority)?.icon"
                        :class="`text-${priorityOptions.find(o => o.value === form.priority)?.color}-500`"
                      />
                      {{ priorityOptions.find(o => o.value === form.priority)?.label.split(' - ')[0] }}
                    </div>
                  </template>
                  <template #option="{ option }">
                    <div class="flex items-center gap-2">
                      <UIcon
                        :name="option.icon"
                        :class="`text-${option.color}-500`"
                      />
                      <span class="font-medium">{{ option.label }}</span>
                    </div>
                  </template>
                </USelectMenu>
              </UFormField>
            </div>

            <UFormField
              label="Subject Title"
              required
            >
              <UInput
                v-model="form.title"
                placeholder="Briefly describe what's happening"
                size="xl"
                class="rounded-2xl"
              />
            </UFormField>

            <UFormField
              label="Detailed Description"
              required
            >
              <UTextarea
                v-model="form.description"
                :rows="6"
                placeholder="Please provide as much detail as possible to help us resolve the issue faster..."
                size="xl"
                class="rounded-2xl"
              />
            </UFormField>

            <UFormField label="Attachments (Optional)">
              <div class="relative group cursor-pointer">
                <input
                  type="file"
                  accept="image/*"
                  class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                  @change="handleFile"
                >
                <div class="p-6 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-3xl group-hover:border-primary-500/50 group-hover:bg-primary-500/5 transition-all flex flex-col items-center justify-center gap-3">
                  <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center text-slate-400 group-hover:text-primary-500 transition-colors">
                    <UIcon
                      :name="form.screenshot ? 'i-lucide-file-check' : 'i-lucide-upload-cloud'"
                      class="w-6 h-6"
                    />
                  </div>
                  <div class="text-center">
                    <p class="text-sm font-black text-slate-900 dark:text-white">
                      {{ form.screenshot ? form.screenshot.name : 'Upload Screenshot' }}
                    </p>
                    <p class="text-xs text-slate-500">
                      Drag and drop or click to browse (Max 5MB)
                    </p>
                  </div>
                </div>
              </div>
            </UFormField>

            <div class="flex items-center justify-end gap-4 pt-4">
              <UButton
                to="/helpdesk"
                variant="ghost"
                size="xl"
                class="rounded-2xl font-bold px-8"
              >
                Cancel
              </UButton>
              <UButton
                type="submit"
                size="xl"
                color="primary"
                :loading="submitting"
                class="rounded-2xl font-black px-12 shadow-xl shadow-primary-500/20"
              >
                Submit Ticket
              </UButton>
            </div>
          </UForm>
        </div>
      </div>

      <!-- Guidelines Sidebar -->
      <div class="space-y-6">
        <div class="glass-card p-6 border-none bg-gradient-to-br from-indigo-600 to-primary-700 text-white shadow-2xl shadow-primary-500/20">
          <h3 class="font-black uppercase tracking-widest text-[10px] text-white/60 mb-4 flex items-center gap-2">
            <UIcon name="i-lucide-shield-check" />
            Support Guidelines
          </h3>
          <ul class="space-y-4">
            <li
              v-for="(item, i) in [
                'Be specific about the issue',
                'Include error messages if any',
                'Attach relevant screenshots',
                'Response time is < 24 hours'
              ]"
              :key="i"
              class="flex gap-3 text-sm font-medium"
            >
              <div class="w-5 h-5 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                <UIcon
                  name="i-lucide-check"
                  class="w-3 h-3 text-white"
                />
              </div>
              {{ item }}
            </li>
          </ul>
        </div>

        <div class="glass-card p-6 border-none ring-1 ring-slate-200 dark:ring-slate-800">
          <h3 class="font-black text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <UIcon
              name="i-lucide-help-circle"
              class="text-primary-500"
            />
            Need Quick Help?
          </h3>
          <p class="text-sm text-slate-500 mb-6">
            Check our knowledge base for instant answers to common questions.
          </p>
          <UButton
            to="/faq"
            block
            color="neutral"
            variant="soft"
            class="rounded-xl font-bold"
          >
            View FAQ
          </UButton>
        </div>
      </div>
    </div>
  </div>
</template>
