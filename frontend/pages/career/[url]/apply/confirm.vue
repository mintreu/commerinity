<template>
  <div class="max-w-2xl mx-auto px-6 py-20 text-center text-gray-800 dark:text-gray-200">
    <h1 class="text-3xl font-bold mb-4 text-green-600">✅ Application Submitted Successfully!</h1>
    <p class="text-lg mb-6">Thank you for applying to the <strong>{{ formattedJobTitle }}</strong> role.</p>

    <!-- Serial / UUID -->
    <div class="mb-6">
      <label class="block text-sm font-medium mb-2">Application Serial</label>
      <div
          class="bg-gray-100 dark:bg-gray-800 p-3 rounded cursor-pointer select-all text-sm break-all border dark:border-gray-700"
          @click="copy(uuid)"
      >
        {{ uuid }}
      </div>
      <p class="text-xs text-gray-500 mt-1">Click to copy</p>
    </div>

    <!-- Applicant Info -->
    <div class="text-left space-y-2 mb-8">
      <p><strong>Name:</strong> {{ name }}</p>
      <p><strong>Email:</strong> {{ email }}</p>
      <p><strong>Mobile:</strong> {{ mobile }}</p>
    </div>

    <NuxtLink
        to="/career"
        class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded text-lg"
    >
      Close & Return to Career Page
    </NuxtLink>
  </div>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'
import { ref, onMounted, computed } from 'vue'

const route = useRoute()

const uuid = ref('')
const name = ref('')
const email = ref('')
const mobile = ref('')
const jobTitle = ref('')

onMounted(() => {
  uuid.value = route.query.uuid as string || ''
  name.value = route.query.name as string || ''
  email.value = route.query.email as string || ''
  mobile.value = route.query.mobile as string || ''
  jobTitle.value = (route.params.url as string || '').replace(/-/g, ' ')
})

const formattedJobTitle = computed(() =>
    jobTitle.value
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ')
)

const copy = async (text: string) => {
  try {
    await navigator.clipboard.writeText(text)
    alert('Copied!')
  } catch {
    alert('Copy failed.')
  }
}
</script>
