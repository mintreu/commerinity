<template>
  <div class="max-w-4xl mx-auto px-4 py-16 text-gray-800 dark:text-gray-200">
    <!-- Job Found -->
    <div v-if="job" class="space-y-10">
      <!-- Header -->
      <div>
        <h1 class="text-4xl font-bold">{{ job.title }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          {{ job.location }} | {{ job.team }}
        </p>
      </div>

      <!-- Job Description -->
      <div class="space-y-4 text-base leading-relaxed">
        <div v-for="para in parsedDescription" :key="para" class="whitespace-pre-line">
          {{ para }}
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="text-center flex flex-col sm:flex-row justify-center gap-4">
        <NuxtLink
            :to="`/career/${job.url}/apply`"
            class="bg-blue-600 text-white px-6 py-3 rounded text-lg hover:bg-blue-700 inline-flex items-center gap-2 justify-center"
        >
          <Icon name="mdi:send" /> Apply Now
        </NuxtLink>


        <a
            v-if="job.pdf"
            :href="job.pdf"
            target="_blank"
            rel="noopener"
            class="bg-gray-100 dark:bg-gray-800 text-blue-600 border border-blue-600 px-6 py-3 rounded text-lg hover:bg-blue-50 dark:hover:bg-gray-700 inline-flex items-center gap-2 justify-center"
        >
          <Icon name="mdi:file-download" /> Download PDF
        </a>
      </div>
    </div>

    <!-- Not Found -->
    <div v-else class="text-center py-24">
      <h2 class="text-2xl font-semibold mb-2">Job not found</h2>
      <NuxtLink to="/career" class="text-blue-600 hover:underline">
        ← Back to Careers
      </NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'
import { computed } from 'vue'

const route = useRoute()

// Inline job data with optional PDF URLs
const jobs = [
  {
    id: 'frontend-dev',
    url: 'frontend-developer',
    title: 'Frontend Developer',
    location: 'Remote / Kolkata',
    team: 'Engineering',
    summary: 'Craft modern UIs with Nuxt & Tailwind.',
    pdf: '/pdfs/frontend-developer.pdf',
    description: `
As a Frontend Developer, you'll help build and scale modern web applications using Nuxt 3 and TailwindCSS.

Responsibilities:
- Develop responsive and performant UIs
- Collaborate with designers and backend engineers
- Write scalable, maintainable Vue components

Requirements:
- Vue 3 / Nuxt 3
- TailwindCSS
- REST APIs / Git
    `
  },
  {
    id: 'backend-engineer',
    url: 'backend-engineer',
    title: 'Backend Engineer',
    location: 'Remote / Bengaluru',
    team: 'Engineering',
    summary: 'Design scalable Node.js services.',
    // No PDF for this one
    description: `
You'll help architect and scale backend systems that power real-time features and critical APIs.

Requirements:
- Node.js, PostgreSQL
- Redis, Docker, CI/CD
    `
  },
  {
    id: 'product-designer',
    url: 'product-designer',
    title: 'Product Designer',
    location: 'Remote / Mumbai',
    team: 'Design',
    summary: 'Shape elegant, usable interfaces.',
    pdf: '/pdfs/product-designer.pdf',
    description: `
You'll lead product design from concept to launch, focusing on usability, accessibility, and simplicity.

Requirements:
- Figma, prototyping, UX research
- Design systems, responsive layouts
    `
  }
]

// Find job from URL param
const job = jobs.find(j => j.url === route.params.url)

// Parse description into paragraphs
const parsedDescription = computed(() => {
  return job?.description.trim().split('\n\n') || []
})
</script>

<style scoped>
</style>
