<template>
  <div class="max-w-7xl mx-auto px-4 py-16 text-gray-900 dark:text-gray-100 space-y-20">

    <!-- Hero Section -->
    <section class="relative text-center overflow-hidden py-24 px-4 sm:px-8 lg:px-16">
      <!-- Background Overlay -->
      <div
          class="absolute inset-0 bg-cover bg-center opacity-20 dark:opacity-40"
          style="background-image: url('/hero-career-bg.jpg');"
      ></div>

      <!-- Heading -->
      <h1 class="relative z-10 text-4xl sm:text-5xl md:text-6xl font-extrabold text-gray-900 dark:text-white mb-4">
        Join Our Journey
      </h1>

      <!-- Subheading -->
      <p class="relative z-10 text-lg sm:text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
        Drive impact. Build the future. Work with purpose alongside brilliant minds.
      </p>

      <!-- Highlights -->
      <div class="relative z-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-12">
        <div
            class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm hover:shadow-md transition hover:scale-105 transform text-center space-y-3 group"
        >
          <div class="text-blue-600 dark:text-blue-400 text-4xl">
            <Icon name="mdi:lightbulb-on-outline" />
          </div>
          <h3 class="text-lg font-semibold group-hover:text-blue-600 dark:group-hover:text-blue-400">
            Purposeful Work
          </h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Contribute to something bigger and drive meaningful change.
          </p>
        </div>

        <div
            class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm hover:shadow-md transition hover:scale-105 transform text-center space-y-3 group"
        >
          <div class="text-green-600 dark:text-green-400 text-4xl">
            <Icon name="mdi:earth" />
          </div>
          <h3 class="text-lg font-semibold group-hover:text-green-600 dark:group-hover:text-green-400">
            Global Culture
          </h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Work with diverse, passionate people from around the world.
          </p>
        </div>

        <div
            class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm hover:shadow-md transition hover:scale-105 transform text-center space-y-3 group"
        >
          <div class="text-pink-600 dark:text-pink-400 text-4xl">
            <Icon name="mdi:school-outline" />
          </div>
          <h3 class="text-lg font-semibold group-hover:text-pink-600 dark:group-hover:text-pink-400">
            Learn & Grow
          </h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Upskill continuously with mentorship and resources at your fingertips.
          </p>
        </div>

        <div
            class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm hover:shadow-md transition hover:scale-105 transform text-center space-y-3 group"
        >
          <div class="text-yellow-500 dark:text-yellow-400 text-4xl">
            <Icon name="mdi:party-popper" />
          </div>
          <h3 class="text-lg font-semibold group-hover:text-yellow-500 dark:group-hover:text-yellow-400">
            Culture That Cares
          </h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            We celebrate wins, support each other, and grow as a team.
          </p>
        </div>
      </div>
    </section>


    <!-- Application Status Section -->
    <section v-if="isLoggedIn" class="bg-gray-100 dark:bg-gray-800 p-6 rounded-lg max-w-3xl mx-auto w-full">
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
        <p>
          Your application for <strong>{{ application.role }}</strong> is
          <strong>{{ application.status }}</strong>.
        </p>
        <NuxtLink :to="`/career/applications/${application.uuid}`" class="text-blue-600 hover:underline inline-block mt-2">
          View Details →
        </NuxtLink>
      </div>
    </section>

    <!-- Job Listings -->
    <div v-if="jobs.length" class="relative">
      <div class="flex justify-center items-center gap-4">
        <label class="flex items-center gap-2 text-sm">
          <input type="radio" value="type" v-model="filterBy" /> Filter by Type
        </label>
        <label class="flex items-center gap-2 text-sm">
          <input type="radio" value="role" v-model="filterBy" /> Filter by Role
        </label>
      </div>

      <section class="text-center" id="openings">
        <h2 class="text-3xl font-bold mb-4 flex items-center justify-center gap-2">
          <Icon name="mdi:clipboard-text" /> Explore Open Roles
        </h2>
        <div class="flex flex-wrap justify-center gap-4 mb-8">
          <button
              v-for="opt in teams"
              :key="opt"
              @click="filterTeam = opt"
              :class="[
              'px-4 py-2 rounded text-sm font-medium transition',
              filterTeam === opt
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
            ]"
          >
            {{ opt }}
          </button>
        </div>

        <div class="relative group ">
          <Swiper
              ref="swiper"
              :slides-per-view="1.1"
              :space-between="16"
              :breakpoints="{640:{slidesPerView:1.3},768:{slidesPerView:2},1024:{slidesPerView:3}}"
              class="w-full "
          >
            <SwiperSlide v-for="(job, index) in filteredJobs" :key="job.url">
              <div
                  class="group relative p-6 h-full my-6 border rounded-2xl bg-white dark:bg-gray-900 shadow-sm flex flex-col items-center text-center"
                  :ref="el => cardRefs[index] = el"
                  @mouseenter="animateIn(index)"
                  @mouseleave="animateOut(index)"
              >
                <div class="self-center mb-3 inline-block bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 text-xs font-semibold px-3 py-1 rounded-full shadow-sm uppercase tracking-wide">
                  {{ job.type }}
                </div>
                <div class="relative w-full mb-4 overflow-hidden rounded-xl">
                  <NuxtLink :to="`/career/${job.url}`">
                    <img
                        :src="job.thumbnail"
                        :alt="job.title"
                        class="w-full h-44 object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                  </NuxtLink>
                  <div class="absolute inset-0 bg-gradient-to-t from-white/90 via-white/30 to-transparent dark:from-gray-900/90 dark:via-gray-900/30 pointer-events-none rounded-xl" />
                </div>
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-1 group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">
                  {{ job.title }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">📍 {{ job.location }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-6 max-w-xs line-clamp-3">
                  {{ job.summary }}
                </p>
                <NuxtLink
                    :to="`/career/${job.url}`"
                    class="mt-auto inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-full transition duration-300 shadow hover:shadow-md"
                >
                  <Icon name="mdi:arrow-right" class="text-base" />
                  View & Apply
                </NuxtLink>
              </div>
            </SwiperSlide>
          </Swiper>

          <!-- Manual Controls -->
<!--          <button-->
<!--              @click="swiper?.slidePrev()"-->
<!--              class="absolute top-1/2 right-16 -translate-y-1/2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 p-3 rounded-full shadow hover:bg-gray-200 dark:hover:bg-gray-700 transition"-->
<!--          >-->
<!--            <Icon name="mdi:chevron-left" class="text-xl" />-->
<!--          </button>-->
<!--          <button-->
<!--              @click="swiper?.slideNext()"-->
<!--              class="absolute top-1/2 right-4 -translate-y-1/2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 p-3 rounded-full shadow hover:bg-gray-200 dark:hover:bg-gray-700 transition"-->
<!--          >-->
<!--            <Icon name="mdi:chevron-right" class="text-xl" />-->
<!--          </button>-->
        </div>
      </section>
    </div>

    <div v-else>
      <NoJobsHero />
    </div>

    <!-- Culture & Values -->
    <section class="space-y-8">
      <div class="text-center space-y-2">
        <h2 class="text-3xl font-bold flex items-center justify-center gap-2">
          <Icon name="mdi:heart-outline" /> Our Core Values
        </h2>
        <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto text-base">
          The principles that guide our decisions, our people, and the way we build.
        </p>
      </div>

      <ul class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 list-none">
        <li
            v-for="value in values"
            :key="value.title"
            class="group   hover:shadow-md  hover:scale-105 transform
            flex flex-col items-center text-center gap-3 p-5 rounded-2xl transition-all duration-300
             bg-white dark:bg-gray-900 shadow-sm"
            :style="{
        border: `2px solid transparent`,
      }"
            @mouseenter="hoverColor = value.color"
            @mouseleave="hoverColor = null"
        >
          <div
              class="flex items-center justify-center w-12 h-12 rounded-full text-2xl transition-transform transform group-hover:rotate-6 group-hover:scale-110"
              :style="{
          backgroundColor: value.color + '20', // light BG
          color: value.color,
        }"
          >
            <Icon :name="value.icon" />
          </div>
          <div>
            <h4
                class="font-semibold text-lg transition-colors"
                :style="{ color: hoverColor === value.color ? value.color : 'inherit' }"
            >
              {{ value.title }}
            </h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ value.text }}</p>
          </div>
          <div
              class="absolute inset-0 rounded-2xl pointer-events-none transition-all duration-300"
              :style="{
          boxShadow: hoverColor === value.color
            ? `0 0 0 2px ${value.color}80`
            : 'none',
        }"
          />
        </li>
      </ul>
    </section>



    <!-- Life In Comppany -->
    <section class="space-y-6">
      <h2 class="text-3xl font-bold">Life at Our Company</h2>
      <ul class="grid sm:grid-cols-2 gap-8 list-none">
        <li v-for="(item, i) in lifeAtCompany" :key="i" class="group flex items-start gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-gray-800 hover:ring-2 hover:ring-blue-400 transition">
          <Icon :name="item.icon" class="text-blue-600 dark:text-blue-400 text-2xl" />
          <div>
            <h4 class="font-semibold text-gray-800 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-300 transition">
              {{ item.title }}
            </h4>
            <p class="text-gray-600 dark:text-gray-400">{{ item.text }}</p>
          </div>
        </li>
      </ul>
    </section>


    <!-- Final Message Section -->
    <section
        class=" bg-gradient-to-r from-blue-300 via-indigo-500 to-purple-400
        dark:bg-gradient-to-r dark:from-blue-600 dark:via-indigo-700 dark:to-purple-700
        text-white text-center py-20 px-6 rounded-2xl shadow-xl overflow-hidden relative">
      <div class="max-w-3xl mx-auto space-y-6">
        <h2 class="text-3xl sm:text-4xl font-extrabold leading-tight">
          We’re not just offering jobs — we’re building a place where you can thrive.
        </h2>
        <p class="text-lg sm:text-xl text-blue-100">
          Join a team that values courage, creativity, and compassion. If you're ready to bring bold ideas to life, this is the place.
        </p>
        <p class="text-sm text-blue-200 italic">
          “Great things are done by a series of small things brought together.” — Vincent Van Gogh
        </p>
      </div>
    </section>





  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRuntimeConfig, useAsyncData, useSanctum } from '#imports'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import gsap from 'gsap'
import ScrollTrigger from 'gsap/ScrollTrigger'
import NoJobsHero from '~/components/career/NoJobsHero.vue'
const cardRefs = ref([])  // all card DOMs
gsap.registerPlugin(ScrollTrigger)

const config = useRuntimeConfig()
const companyName = config.public.companyName
const { isLoggedIn } = useSanctum()

const query = ref('')
const login = ref(true)
const error = ref('')
const application = ref<any>(null)


const animateIn = (index: number) => {
  const el = cardRefs.value[index]
  if (!el) return

  gsap.to(el, {
    scale: 1.03,
    y: -10,
    boxShadow: '0px 20px 30px rgba(0,0,0,0.1)',
    duration: 0.3,
    ease: 'power2.out'
  })
}

const animateOut = (index: number) => {
  const el = cardRefs.value[index]
  if (!el) return

  gsap.to(el, {
    scale: 1,
    y: 0,
    boxShadow: '0px 4px 10px rgba(0,0,0,0.05)',
    duration: 0.3,
    ease: 'power2.inOut'
  })
}







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
  { icon: 'mdi:people-group-outline', title: 'Team Culture', text: 'Collaborative & supportive teams.' },
  { icon: 'mdi:flash-outline', title: 'Impact', text: 'Work that truly matters every day.' },
  { icon: 'mdi:chart-line', title: 'Growth', text: 'Continuous learning & personal development.' },
  { icon: 'mdi:earth', title: 'Global Reach', text: 'Projects spanning geographies.' }
]

const { data: rawData, error: fetchError } = await useAsyncData('recruit', () =>
    $fetch(`${config.public.apiBase}/recruitment`)
)

const jobs = computed(() => {
  if (fetchError.value || !rawData.value?.data) return []
  return rawData.value.data.map((job: any) => ({
    title: job.name,
    url: job.url,
    role: job.role,
    type: job.type,
    location: job.location,
    thumbnail: job.thumbnail,
    summary: job.role
  }))
})

const filterBy = ref<'type' | 'role'>('type')
const filterTeam = ref('All')
const teams = computed(() => ['All', ...new Set(jobs.value.map(j => j[filterBy.value]))])
const filteredJobs = computed(() => filterTeam.value === 'All' ? jobs.value : jobs.value.filter(j => j[filterBy.value] === filterTeam.value))

const values = [
  {
    icon: 'mdi:handshake-outline',
    title: 'Integrity',
    text: 'Honesty in all our actions.',
    color: '#e11d48' // rose-600
  },
  {
    icon: 'mdi:account-star-outline',
    title: 'Excellence',
    text: 'Striving for the best.',
    color: '#0ea5e9' // sky-500
  },
  {
    icon: 'mdi:heart-outline',
    title: 'Compassion',
    text: 'Empathy & respect for all.',
    color: '#ec4899' // pink-500
  },
  {
    icon: 'mdi:rocket-launch-outline',
    title: 'Innovation',
    text: 'Bold ideas, better outcomes.',
    color: '#a855f7' // violet-500
  },
  {
    icon: 'mdi:chat-question-outline',
    title: 'Curiosity',
    text: 'We ask why, explore how, and never stop learning.',
    color: '#22c55e' // green-500
  },
  {
    icon: 'mdi:scale-balance',
    title: 'Accountability',
    text: 'We own our outcomes — good or bad — and grow from them.',
    color: '#f97316' // orange-500
  }
]






// In <script setup>
const lifeAtCompany = [
  {
    icon: 'mdi:calendar-star',
    title: 'Flexible Work Schedules',
    text: 'Balance your work and personal life with our flexible timings.',
  },
  {
    icon: 'mdi:brain',
    title: 'Learning Culture',
    text: 'Access to courses, mentorship, and R&D time every week.',
  },
  {
    icon: 'mdi:emoticon-happy-outline',
    title: 'Team Bonding Events',
    text: 'Monthly team outings, virtual games, and fun Fridays.',
  },
  {
    icon: 'mdi:office-building',
    title: 'Modern Workspace',
    text: 'Ergonomic, collaborative and inclusive office environment.',
  },
  {
    icon: 'mdi:office-building',
    title: 'Competitive Pay',
    text: 'We reward talent generously.',
  },
  {
    icon: 'mdi:office-building',
    title: 'Health Benefits',
    text: 'Comprehensive wellness coverage.',
  },
  {
    icon: 'mdi:map-marker-distance',
    title: 'Remote-first',
    text: 'Our teams collaborate across timezones — freedom with responsibility.',
  },
  {
    icon: 'mdi:bullseye-arrow',
    title: 'Goal-Oriented',
    text: 'Everyone owns outcomes. We trust, support, and stay aligned through OKRs.',
  }
]


const swiper = ref<any>(null)
const heroRef = ref<HTMLElement | null>(null)
const heroTitle = ref<HTMLElement | null>(null)
const heroSub = ref<HTMLElement | null>(null)
const heroIcons = ref<HTMLElement | null>(null)

onMounted(() => {
  gsap.timeline({
    scrollTrigger: {
      trigger: heroRef.value,
      start: 'top center'
    }
  })
      .from(heroTitle.value, { opacity: 0, y: 60, duration: 0.8 })
      .from(heroSub.value, { opacity: 0, y: 40, duration: 0.6 }, '-=0.5')
      .from(heroIcons.value?.children || [], { opacity: 0, y: 20, duration: 0.6, stagger: 0.2 }, '-=0.5')
})
</script>

<style scoped>
.glow-on-hover:hover {
  box-shadow: 0 0 15px rgba(59, 130, 246, 0.6);
}
.glow-icon:hover {
  animation: glow 1.5s infinite alternate;
}
@keyframes glow {
  to {
    filter: drop-shadow(0 0 10px rgba(59, 130, 246, 0.7));
  }
}
</style>
