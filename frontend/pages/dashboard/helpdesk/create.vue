<template>
  <div class="p-4 sm:p-6 max-w-3xl mx-auto space-y-8">
    <div class="flex items-center gap-3">
      <Icon name="mdi-plus-box" class="text-blue-600 dark:text-blue-400 text-2xl" />
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        Create New Support Ticket
      </h1>
    </div>

    <form
        @submit.prevent="handleSubmit"
        class="space-y-6 bg-white dark:bg-gray-900 p-4 sm:p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
    >
      <!-- Title -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Title
        </label>
        <input
            v-model="form.title"
            type="text"
            required
            placeholder="Brief summary of your issue"
            class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-4 py-2
                 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                 placeholder-gray-400 dark:placeholder-gray-500
                 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- Description -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Description
        </label>
        <textarea
            v-model="form.description"
            rows="5"
            required
            placeholder="Please describe your issue in detail..."
            class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-4 py-2
                 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                 placeholder-gray-400 dark:placeholder-gray-500
                 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y"
        ></textarea>
      </div>

      <!-- Priority + Topic -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Priority -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Priority
          </label>
          <select
              v-model="form.priority"
              class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-4 py-2
                   bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                   focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
          </select>
        </div>

        <!-- Topic -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Topic
          </label>
          <select
              v-model="form.topic_slug"
              class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-4 py-2
                   bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                   focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option disabled value="">Select a topic</option>
            <option
                v-for="topic in topics"
                :key="topic.slug"
                :value="topic.slug"
            >
              {{ topic.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- Screenshot -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Screenshot (optional)
        </label>
        <input
            type="file"
            accept="image/*"
            @change="handleFile"
            class="block w-full border border-gray-300 dark:border-gray-700 rounded-md px-4 py-2
                 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800
                 file:bg-gray-100 file:dark:bg-gray-700 file:border-0 file:px-4 file:py-2"
        />
        <div
            v-if="form.screenshot"
            class="mt-2 text-sm text-gray-600 dark:text-gray-400"
        >
          Selected: {{ form.screenshot.name }}
        </div>
      </div>

      <!-- Submit -->
      <div class="pt-2">
        <button
            type="submit"
            class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-md
                 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500
                 disabled:opacity-50"
            :disabled="!form.title || !form.description || !form.topic_slug"
        >
          <Icon name="mdi-send" class="inline-block mr-2 -mt-1" />
          Submit Ticket
        </button>
      </div>
    </form>

    <!-- FAQ -->
    <div
        class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4 sm:p-6"
    >
      <h2
          class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2"
      >
        <Icon name="mdi-frequently-asked-questions" class="text-xl text-blue-500" />
        Frequently Asked Questions
      </h2>
      <ul
          class="space-y-2 text-sm text-gray-700 dark:text-gray-300 list-disc list-inside"
      >
        <li>Check the Help Center for common issues.</li>
        <li>Include browser info and relevant screenshots.</li>
        <li>Use a clear title describing the problem.</li>
      </ul>
      <div class="mt-4">
        <NuxtLink to="/dashboard/faq">
          <button
              class="text-sm px-4 py-2 bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-md hover:bg-gray-300 dark:hover:bg-gray-700"
          >
            <Icon name="mdi-book-open-page-variant" class="inline-block mr-2 -mt-1" />
            Browse Help Center
          </button>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRuntimeConfig, useSanctumFetch, useToast } from '#imports'

definePageMeta({ layout: 'dashboard' })

const router = useRouter()
const toast = useToast()
const config = useRuntimeConfig()


const form = reactive({
  title: '',
  description: '',
  priority: 'medium',
  screenshot: null as File | null,
  topic_slug: ''
})

function handleFile(e: Event) {
  const input = e.target as HTMLInputElement
  form.screenshot = input.files?.[0] ?? null
}

const topics = ref<any[]>([])

async function fetchTopics() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/helpdesk/topics/ticket`)
    topics.value = res?.data ?? []
  } catch (e) {
    console.error('❌ Failed to load helpdesk topics', e)
    topics.value = []
  }
}



onMounted(async () => {
  await fetchTopics()
})


async function handleSubmit() {
  try {
    const fd = new FormData()
    fd.append('title', form.title)
    fd.append('description', form.description)
    fd.append('priority', form.priority)

    if (form.topic_slug) fd.append('topic_slug', form.topic_slug)
    if (form.screenshot) fd.append('screenshot', form.screenshot)

    const res = await useSanctumFetch(
        `${config.public.apiBase}/helpdesk/tickets`,
        {
          method: 'POST',
          body: fd,
          credentials: 'include'
        }
    )

    console.log('📩 Ticket submit response:', res)

    if (res?.error) {
      throw res.error
    }

    toast.success({
      title: 'Success',
      message: '✅ Ticket submitted successfully!',
      timeout: 3000
    })

    router.push('/dashboard/helpdesk')
  } catch (e) {
    console.error('❌ Ticket submit failed', e)
    toast.error({
      title: 'Error',
      message: '❌ Ticket submit failed',
      timeout: 3000
    })
  }
}


</script>
