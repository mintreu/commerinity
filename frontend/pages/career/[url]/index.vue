<template>
  <div class="max-w-4xl mx-auto px-4 py-16 text-gray-800 dark:text-gray-200">
    <!-- Loader (before data loaded) -->
    <div v-if="!loaded" class="text-center py-24">
      <div class="h-12 w-12 mx-auto animate-spin rounded-full border-4 border-blue-500 border-t-transparent dark:border-blue-400 dark:border-t-transparent"></div>
    </div>

    <!-- Job Found -->
    <div v-else-if="job" class="space-y-10">
      <!-- Header -->
      <div class="text-center space-y-2">
        <h1 class="text-4xl font-bold">{{ job.name }}</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          📍 {{ job.location }} &nbsp; | &nbsp; 🧩 {{ job.role }} &nbsp; | &nbsp; 🕒 {{ job.type }}
        </p>
      </div>

      <!-- Meta Info -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700 dark:text-gray-300">
        <p><strong>Open Date:</strong> {{ job.open_date }}</p>
        <p><strong>Close Date:</strong> {{ job.close_date }}</p>
        <p><strong>Vacancies:</strong> {{ job.vacancy }}</p>
        <p v-if="job.is_payable"><strong>Fees:</strong> ₹{{ job.fees }}</p>
      </div>

      <!-- Job Thumbnail -->
      <div>
        <img
          :src="job.thumbnail"
          :alt="job.name"
          class="w-full h-64 object-cover rounded-xl shadow"
        />
      </div>

      <!-- Description -->
      <div class="space-y-4 text-base leading-relaxed">
        <div
          v-for="(para, index) in parsedDescription"
          :key="index"
          class="whitespace-pre-line"
        >
          {{ para }}
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="text-center flex flex-col sm:flex-row justify-center gap-4">
        <!-- Apply Now -->
        <NuxtLink
          :to="`/career/${job.url}/apply`"
          class="bg-blue-600 text-white px-6 py-3 rounded text-lg hover:bg-blue-700 inline-flex items-center gap-2 justify-center"
        >
          <Icon name="mdi:send" /> Apply Now
        </NuxtLink>

        <!-- Download ZIP -->
        <a
          v-if="isZip"
          :href="pdfList[0]"
          target="_blank"
          rel="noopener"
          class="bg-gray-100 dark:bg-gray-800 text-blue-600 border border-blue-600 px-6 py-3 rounded text-lg hover:bg-blue-50 dark:hover:bg-gray-700 inline-flex items-center gap-2 justify-center"
        >
          <Icon name="mdi:archive" /> Download ZIP
        </a>
      </div>

      <!-- PDF Previews -->
      <div v-if="!isZip && pdfList.length" class="grid sm:grid-cols-2 gap-4 mt-8">
        <div
          v-for="(pdfUrl, index) in pdfList"
          :key="index"
          class="border border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden shadow-sm bg-white dark:bg-gray-900"
        >
          <div class="relative h-48 bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
            <Icon name="mdi:file-pdf-box" class="text-red-500 text-6xl" />
          </div>
          <div class="p-4 flex justify-between items-center">
            <p class="text-sm truncate text-gray-700 dark:text-gray-300">
              {{ getPdfName(pdfUrl, index) }}
            </p>

            <a
  :href="pdfUrl"
  target="_blank"
  rel="noopener"
  class="text-blue-600 hover:underline text-sm flex items-center gap-1 transition-all duration-200 hover:translate-x-1 hover:text-blue-700"
>
  <Icon name="mdi:file-download" class="text-base transition-transform duration-200" />
  Download
</a>

          </div>
        </div>
      </div>
    </div>

    <!-- Not Found -->
    <div v-else class="text-center py-24">
      <h2 class="text-2xl font-semibold mb-2">Job not found</h2>
      <NuxtLink to="/career" class="text-blue-600 hover:underline">← Back to Careers</NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useSanctumFetch, useRuntimeConfig } from '#imports'

// Global loading animation
const loading = useState('pageLoading', () => false)
loading.value = true

const job = ref<Job | null>(null)
const loaded = ref(false) // ✅ New flag to control display after fetch

const route = useRoute()
const config = useRuntimeConfig()

// Type definition
interface Job {
  name: string
  url: string
  role: string
  type: string
  location: string
  thumbnail: string
  pdf?: string | string[]
  description: string
  vacancy: number
  open_date: string
  close_date: string
  is_payable: boolean
  fees?: string
}

// Description parsing
const parsedDescription = computed(() =>
  job.value?.description?.trim().split(/\n{2,}/) || []
)

onMounted(async () => {
  try {
    const apiUrl = `${config.public.apiBase}/recruitment/${route.params.url}`
    const response = await useSanctumFetch(apiUrl)
    job.value = response?.data || null
  } catch (error) {
    console.error('Error fetching job:', error)
    job.value = null
  } finally {
    loaded.value = true        // ✅ Mark fetch complete
    loading.value = false      // ✅ Stop global loading
  }
})

const pdfList = computed(() => {
  if (!job.value?.pdf) return []
  if (Array.isArray(job.value.pdf)) return job.value.pdf
  if (typeof job.value.pdf === 'string') return [job.value.pdf]
  return []
})

const isZip = computed(() => {
  return pdfList.value.length === 1 && pdfList.value[0].endsWith('.zip')
})


const getPdfName = (url: string, index: number): string => {
  try {
    const decodedUrl = decodeURIComponent(url)
    const parts = decodedUrl.split('/').pop()?.split('#')[0].split('?')[0]
    const name = parts?.replace(/\.pdf$/i, '')?.trim()
    return name || `Document ${index + 1}`
  } catch {
    return `Document ${index + 1}`
  }
}



</script>

<style scoped>
/* Optional styles can go here */
</style>
