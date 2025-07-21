<template>
  <div class="max-w-7xl mx-auto px-4 py-16 text-gray-800 dark:text-gray-200 space-y-20">

    <!-- Hero + Why Join -->
    <section class="text-center space-y-6">
      <h1 class="text-5xl font-bold">Join the {{ companyName }} Family</h1>
      <p class="text-lg text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
        Drive impact. Work with purpose. Collaborate with brilliant minds around the globe.
      </p>
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8 mt-8">
        <div v-for="reason in whyJoin" :key="reason.title" class="space-y-2">
          <Icon :name="reason.icon" class="text-blue-600 text-3xl" />
          <h3 class="font-semibold text-xl">{{ reason.title }}</h3>
          <p class="text-gray-600 dark:text-gray-400">{{ reason.text }}</p>
        </div>
      </div>
    </section>

    <!-- Application Status Search -->
    <section class="bg-gray-100 dark:bg-gray-800 p-6 rounded-lg max-w-3xl mx-auto w-full">
      <h2 class="text-2xl font-semibold mb-4 flex items-center gap-2">
        <Icon name="mdi:magnify" /> Check Your Application
      </h2>
      <form @submit.prevent="handleSearch" class="flex flex-col sm:flex-row gap-4 items-start w-full">
        <input
            v-model="query"
            :placeholder="login ? 'Your registered mobile or email' : 'Application ID'"
            type="text"
            class="flex-1 px-4 py-2 border rounded text-black w-full"
        />
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded w-full sm:w-auto">
          Search
        </button>
      </form>
      <p v-if="error" class="text-red-500 mt-2">{{ error }}</p>
      <div v-if="application" class="mt-6 p-4 border rounded bg-white dark:bg-gray-900 text-center">
        <p>Your application for <strong>{{ application.role }}</strong> is <strong>{{ application.status }}</strong>.</p>
        <NuxtLink :to="`/career/applications/${application.uuid}`" class="text-blue-600 hover:underline inline-block mt-2">
          View Application Details →
        </NuxtLink>
      </div>
    </section>

    <!-- Filter Buttons -->
    <section class="text-center">
      <h2 class="text-3xl font-bold mb-4 flex items-center justify-center gap-2">
        <Icon name="mdi:clipboard-text" /> Explore Open Roles
      </h2>
      <div class="flex flex-wrap justify-center gap-4 mb-8">
        <button
            v-for="team in teams"
            :key="team"
            @click="filterTeam = team"
            :class="[
            'px-4 py-2 rounded text-sm',
            filterTeam === team ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
          ]"
        >
          {{ team }}
        </button>
      </div>

      <!-- Swiper Job Cards -->
      <Swiper
          :slides-per-view="1.1"
          :space-between="16"
          :breakpoints="{
          640: { slidesPerView: 1.3 },
          768: { slidesPerView: 2 },
          1024: { slidesPerView: 3 }
        }"
          class="w-full"
      >
        <SwiperSlide v-for="job in filteredJobs" :key="job.id">
          <div class="p-6 h-full border rounded bg-white dark:bg-gray-900 shadow">
            <Icon name="mdi:briefcase-outline" class="text-blue-600 text-xl" />
            <h3 class="font-semibold text-xl mt-2">{{ job.title }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ job.location }}</p>
            <p class="text-sm mt-2">{{ job.summary }}</p>
            <NuxtLink :to="`/career/${job.id}`" class="text-blue-600 hover:underline mt-4 inline-flex items-center gap-1">
              <Icon name="mdi:arrow-right" /> Details
            </NuxtLink>
          </div>
        </SwiperSlide>
      </Swiper>
    </section>

    <!-- Culture & Values -->
    <section class="space-y-6">
      <h2 class="text-3xl font-bold flex items-center gap-2">
        <Icon name="mdi:heart-outline" /> Our Core Values
      </h2>
      <ul class="grid sm:grid-cols-2 gap-8 list-none">
        <li v-for="value in values" :key="value.title" class="flex items-start gap-4">
          <Icon :name="value.icon" class="text-blue-600 text-2xl" />
          <div>
            <h4 class="font-semibold">{{ value.title }}</h4>
            <p class="text-gray-600 dark:text-gray-400">{{ value.text }}</p>
          </div>
        </li>
      </ul>
    </section>

    <!-- Perks & Benefits -->
    <section class="space-y-6">
      <h2 class="text-3xl font-bold flex items-center gap-2">
        <Icon name="mdi:gift-outline" /> Perks & Benefits
      </h2>
      <ul class="grid sm:grid-cols-2 gap-8 list-none">
        <li v-for="perk in perks" :key="perk.title" class="flex items-start gap-4">
          <Icon :name="perk.icon" class="text-blue-600 text-2xl" />
          <div>
            <h4 class="font-semibold">{{ perk.title }}</h4>
            <p class="text-gray-600 dark:text-gray-400">{{ perk.description }}</p>
          </div>
        </li>
      </ul>
    </section>

    <!-- Testimonials -->
    <section class="space-y-6">
      <h2 class="text-3xl font-bold">What Our Team Says</h2>
      <Swiper
          :slides-per-view="1.1"
          :space-between="16"
          :breakpoints="{
          640: { slidesPerView: 1.3 },
          768: { slidesPerView: 2 },
          1024: { slidesPerView: 3 }
        }"
          class="w-full"
      >
        <SwiperSlide v-for="t in testimonials" :key="t.name">
          <div class="h-full p-6 bg-gray-100 dark:bg-gray-800 rounded-lg shadow-sm">
            <p class="italic text-gray-800 dark:text-gray-200">“{{ t.text }}”</p>
            <p class="mt-4 text-sm font-semibold text-gray-700 dark:text-gray-300">
              — {{ t.name }}, {{ t.role }}
            </p>
          </div>
        </SwiperSlide>
      </Swiper>
    </section>

    <!-- Final CTA -->
    <section class="bg-blue-600 text-white text-center py-16 rounded-lg">
      <h2 class="text-3xl font-bold mb-4">Ready to Shape the Future?</h2>
      <NuxtLink to="#openings" class="bg-white text-blue-600 px-6 py-3 rounded text-lg inline-flex items-center gap-2">
        <Icon name="mdi:briefcase" /> Explore Open Roles
      </NuxtLink>
    </section>

  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'

const config = useRuntimeConfig()
const companyName = config.public.companyName

const query = ref('')
const login = ref(true)
const error = ref('')
const application = ref<any>(null)

function handleSearch() {
  if (!query.value.trim()) {
    error.value = 'Please enter your application ID, email, or mobile.'
    application.value = null
    return
  }
  if (['demo-uuid-123', 'user@example.com', '+91-1234567890'].includes(query.value)) {
    application.value = { uuid: 'demo-uuid-123', role: 'Frontend Developer', status: 'Under Review' }
    error.value = ''
  } else {
    application.value = null
    error.value = 'No application found.'
  }
}

const whyJoin = [
  { icon: 'mdi:rocket', title: 'Grow Fast', text: 'Work on high-impact projects.' },
  { icon: 'mdi:earth', title: 'Global Impact', text: 'Our products reach millions.' },
  { icon: 'mdi:account-group', title: 'Collaborative Team', text: 'Supportive, cross-functional peers.' },
  { icon: 'mdi:star', title: 'Innovative Culture', text: 'Ideas thrive here.' }
]

const jobs = [
  { id: 'frontend-dev', title: 'Frontend Developer', location: 'Remote / Kolkata', team: 'Engineering', summary: 'Craft modern UIs with Nuxt & Tailwind.' },
  { id: 'backend-engineer', title: 'Backend Engineer', location: 'Remote / Bengaluru', team: 'Engineering', summary: 'Design scalable Node.js services.' },
  { id: 'product-designer', title: 'Product Designer', location: 'Remote / Mumbai', team: 'Design', summary: 'Shape elegant, usable interfaces.' }
]

const teams = ['All', 'Engineering', 'Design']
const filterTeam = ref('All')
const filteredJobs = computed(() => filterTeam.value === 'All' ? jobs : jobs.filter(j => j.team === filterTeam.value))

const values = [
  { icon: 'mdi:account-heart-outline', title: 'User-First', text: 'Every decision starts with our users.' },
  { icon: 'mdi:eye-outline', title: 'Transparency', text: 'We believe in open communication.' },
  { icon: 'mdi:check-decagram', title: 'Excellence', text: 'We take pride in doing things well.' },
  { icon: 'mdi:earth', title: 'Inclusion', text: 'Everyone deserves a voice at the table.' }
]

const perks = [
  { icon: 'mdi:laptop', title: 'Remote First', description: 'Work from anywhere that works for you.' },
  { icon: 'mdi:heart-pulse', title: 'Health Benefits', description: 'Comprehensive care for you & your family.' },
  { icon: 'mdi:school-outline', title: 'Learning Support', description: 'Grow with courses, books & more.' },
  { icon: 'mdi:beach', title: 'Paid Leave', description: 'Plenty of time to recharge and reset.' }
]

const testimonials = [
  { name: 'Ria Sen', role: 'Frontend Engineer', text: 'Working here means I learn every day and feel valued.' },
  { name: 'Arun Das', role: 'Product Designer', text: 'I love the creativity and openness of our team.' }
]
</script>

<style scoped>
.swiper {
  padding-bottom: 2rem;
}
.swiper-slide {
  height: auto;
}
</style>
