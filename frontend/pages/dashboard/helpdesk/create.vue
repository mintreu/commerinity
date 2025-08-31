<template>
  <div class="p-6 max-w-3xl mx-auto space-y-10">
    <!-- Page Title -->
    <div class="flex items-center gap-3">
      <Icon name="mdi-plus-box" class="text-blue-600 dark:text-blue-400 text-2xl" />
      <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Create New Support Ticket</h1>
    </div>

    <!-- Form Section -->
    <form @submit.prevent="handleSubmit" class="space-y-6 bg-white dark:bg-gray-900 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
      <!-- Title -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
        <input
            v-model="form.title"
            type="text"
            required
            placeholder="Brief summary of your issue"
            class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-4 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- Description -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
        <textarea
            v-model="form.description"
            rows="5"
            required
            placeholder="Please describe your issue in detail..."
            class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-4 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y"
        ></textarea>
      </div>

      <!-- Priority -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
        <select
            v-model="form.priority"
            class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-4 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="Low">Low</option>
          <option value="Medium">Medium</option>
          <option value="High">High</option>
        </select>
      </div>

      <!-- Screenshot Upload -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Screenshot (optional)</label>
        <input
            type="file"
            accept="image/*"
            @change="handleFile"
            class="block w-full border border-gray-300 dark:border-gray-700 rounded-md px-4 py-2 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 file:bg-gray-100 file:dark:bg-gray-700 file:border-0 file:px-4 file:py-2"
        />
        <div v-if="form.screenshot" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          Selected: {{ form.screenshot.name }}
        </div>
      </div>

      <!-- Submit Button -->
      <div class="pt-4">
        <button
            type="submit"
            class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition disabled:opacity-50"
            :disabled="!form.title || !form.description"
        >
          <Icon name="mdi-send" class="inline-block mr-2 -mt-1" />
          Submit Ticket
        </button>
      </div>
    </form>

    <!-- FAQ / CTA Section -->
    <div class="bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
      <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
        <Icon name="mdi-frequently-asked-questions" class="text-xl text-blue-500" />
        Frequently Asked Questions
      </h2>
      <ul class="space-y-3 text-sm text-gray-700 dark:text-gray-300 list-disc list-inside">
        <li>Check if your issue is listed in our <NuxtLink to="/dashboard/faq" class="text-blue-600 hover:underline dark:text-blue-400">Help Center</NuxtLink>.</li>
        <li>Make sure to include browser info and any relevant screenshots.</li>
        <li>Use a clear title that describes your problem.</li>
      </ul>

      <div class="mt-4">
        <NuxtLink to="/dashboard/faq">
          <button class="text-sm px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition">
            <Icon name="mdi-book-open-page-variant" class="inline-block mr-2 -mt-1" />
            Browse Help Center
          </button>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({layout: 'dashboard'})

const router = useRouter()

const form = reactive({
  title: '',
  description: '',
  priority: 'Medium',
  screenshot: null
})

function handleFile(e) {
  form.screenshot = e.target.files[0]
}

function handleSubmit() {
  console.log('Ticket submitted:', form)
  alert('✅ Ticket submitted successfully!')
  router.push('/dashboard/helpdesk')
}
</script>
