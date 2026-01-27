<script setup lang="ts">
definePageMeta({
  layout: 'public'
})

const config = useRuntimeConfig()
const { isLoggedIn } = useSanctum()

interface Recruitment {
  id: number
  uuid: string
  slug: string
  title: string
  description: string
  role: string
  role_label: string
  location: string
  employment_type: string
  employment_type_label: string
  vacancy: number
  open_date_formatted: string
  close_date_formatted: string
  is_payable: boolean
  fees_formatted: string
  is_open: boolean
}

interface FilterOption {
  value: string
  label: string
}

interface FiltersData {
  roles: FilterOption[]
  types: FilterOption[]
  counts_by_role: Record<string, number>
}

// Filter state
const selectedRole = ref<string>('all')
const selectedType = ref<string>('all')
const searchQuery = ref('')

const queryParams = computed(() => {
  const params = new URLSearchParams()
  if (selectedRole.value && selectedRole.value !== 'all') params.append('role', selectedRole.value)
  if (selectedType.value && selectedType.value !== 'all') params.append('type', selectedType.value)
  return params.toString()
})

const apiUrl = computed(() => {
  const base = `${config.public.apiBase}/api/careers`
  return queryParams.value ? `${base}?${queryParams.value}` : base
})

const { data: recruitments, status } = await useAsyncData<{ data: Recruitment[] }>(
  'careers',
  () => useSanctumFetch(apiUrl.value),
  { watch: [apiUrl] }
)

const { data: filtersData } = await useAsyncData<{ data: FiltersData }>(
  'career-filters',
  () => useSanctumFetch(`${config.public.apiBase}/api/careers/filters`)
)

const jobs = computed(() => recruitments.value?.data || [])

const filteredJobs = computed(() => {
  if (!searchQuery.value) return jobs.value
  const query = searchQuery.value.toLowerCase()
  return jobs.value.filter(job =>
    job.title.toLowerCase().includes(query)
    || job.role_label.toLowerCase().includes(query)
    || job.location.toLowerCase().includes(query)
  )
})

const roleOptions = computed(() => {
  const data = filtersData.value?.data
  if (!data?.roles) return []
  return data.roles
    .filter((r: FilterOption) => r.value && r.value.trim() !== '')
    .map((r: FilterOption) => ({ label: r.label, value: r.value }))
})

const typeOptions = computed(() => {
  const data = filtersData.value?.data
  if (!data?.types) return []
  return data.types
    .filter((t: FilterOption) => t.value && t.value.trim() !== '')
    .map((t: FilterOption) => ({ label: t.label, value: t.value }))
})

function clearFilters() {
  selectedRole.value = 'all'
  selectedType.value = 'all'
  searchQuery.value = ''
}

// Hero highlights
const heroHighlights = [
  {
    icon: 'i-lucide-lightbulb',
    title: 'Purposeful Work',
    description: 'Contribute to something bigger and drive meaningful change.',
    color: 'from-blue-500 to-cyan-500'
  },
  {
    icon: 'i-lucide-globe',
    title: 'Global Culture',
    description: 'Work with diverse, passionate people in an inclusive environment.',
    color: 'from-emerald-500 to-teal-500'
  },
  {
    icon: 'i-lucide-graduation-cap',
    title: 'Learn & Grow',
    description: 'Continuous learning with mentorship and resources.',
    color: 'from-pink-500 to-rose-500'
  },
  {
    icon: 'i-lucide-heart',
    title: 'Culture That Cares',
    description: 'We celebrate wins and support each other as one team.',
    color: 'from-amber-500 to-orange-500'
  }
]

// Company values
const companyValues = [
  { icon: 'i-lucide-handshake', title: 'Integrity', text: 'Honesty and transparency in all actions.', color: 'rose' },
  { icon: 'i-lucide-star', title: 'Excellence', text: 'Striving for the highest quality.', color: 'sky' },
  { icon: 'i-lucide-heart', title: 'Compassion', text: 'Empathy and care for all.', color: 'pink' },
  { icon: 'i-lucide-rocket', title: 'Innovation', text: 'Bold ideas and creative solutions.', color: 'violet' },
  { icon: 'i-lucide-search', title: 'Curiosity', text: 'Never stop learning and growing.', color: 'green' },
  { icon: 'i-lucide-scale', title: 'Accountability', text: 'Own our outcomes and grow from them.', color: 'orange' }
]
</script>

<template>
  <div class="min-h-screen w-full bg-gradient-to-br from-slate-50 via-violet-50 to-fuchsia-50 dark:from-slate-950 dark:via-slate-900 dark:to-purple-950">
    <!-- Hero Section -->
    <section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden">
      <!-- Animated Background Orbs -->
      <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute top-20 left-20 w-72 h-72 bg-gradient-to-r from-violet-400 to-purple-400 rounded-full opacity-20 blur-3xl animate-pulse" />
        <div
          class="absolute bottom-20 right-20 w-96 h-96 bg-gradient-to-r from-fuchsia-400 to-pink-400 rounded-full opacity-15 blur-3xl animate-pulse"
          style="animation-delay: 1s"
        />
        <div
          class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-gradient-to-r from-cyan-400 to-teal-400 rounded-full opacity-10 blur-2xl animate-pulse"
          style="animation-delay: 2s"
        />
      </div>

      <!-- Hero Content -->
      <div class="relative z-10 text-center max-w-6xl mx-auto px-6 py-20">
        <div class="space-y-8">
          <!-- Badge -->
          <div class="inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-violet-500/10 to-fuchsia-500/10 border border-violet-200 dark:border-violet-800 backdrop-blur-sm">
            <UIcon
              name="i-lucide-rocket"
              class="w-5 h-5 mr-3 text-violet-600 dark:text-violet-400"
            />
            <span class="font-semibold text-violet-700 dark:text-violet-300">Join Our Mission</span>
          </div>

          <!-- Main Title -->
          <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black leading-tight">
            <span class="block text-slate-900 dark:text-white">Shape the</span>
            <span class="block bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 bg-clip-text text-transparent">Future of Work</span>
            <span class="block text-slate-900 dark:text-white">With Us</span>
          </h1>

          <!-- Subtitle -->
          <p class="text-lg sm:text-xl lg:text-2xl max-w-4xl mx-auto text-slate-600 dark:text-slate-300 font-medium">
            Drive impact. Build tomorrow. Work alongside brilliant minds who are passionate about creating meaningful change.
          </p>

          <!-- CTA Buttons -->
          <div class="flex flex-col sm:flex-row gap-6 justify-center items-center pt-8">
            <a
              href="#openings"
              class="group relative px-10 py-5 bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white font-bold text-lg rounded-2xl shadow-2xl hover:shadow-violet-500/25 transition-all duration-300 transform hover:scale-105"
            >
              View Open Positions
              <UIcon
                name="i-lucide-arrow-down"
                class="inline w-5 h-5 ml-2 group-hover:translate-y-1 transition-transform"
              />
            </a>
            <NuxtLink
              v-if="isLoggedIn"
              to="/career/applications"
              class="group px-10 py-5 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-bold text-lg rounded-2xl border-2 border-slate-200 dark:border-slate-700 hover:border-violet-300 dark:hover:border-violet-600 transition-all duration-300 backdrop-blur-sm"
            >
              <UIcon
                name="i-lucide-file-text"
                class="inline w-5 h-5 mr-3"
              />
              My Applications
            </NuxtLink>
          </div>

          <!-- Hero Highlights -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 pt-16 max-w-5xl mx-auto">
            <div
              v-for="highlight in heroHighlights"
              :key="highlight.title"
              class="group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg p-8 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 border border-white/20 overflow-hidden"
            >
              <div
                :class="['w-16 h-16 mx-auto mb-6 rounded-2xl flex items-center justify-center bg-gradient-to-br', highlight.color, 'transition-transform duration-500 group-hover:scale-110 group-hover:rotate-6']"
              >
                <UIcon
                  :name="highlight.icon"
                  class="w-8 h-8 text-white"
                />
              </div>
              <h3 class="text-xl font-bold mb-4 text-slate-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                {{ highlight.title }}
              </h3>
              <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                {{ highlight.description }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Scroll Indicator -->
      <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
        <div class="w-6 h-10 border-2 border-slate-400 dark:border-slate-600 rounded-full flex justify-center">
          <div class="w-1 h-3 bg-gradient-to-b from-violet-600 to-transparent rounded-full mt-2" />
        </div>
      </div>
    </section>

    <!-- Job Listings Section -->
    <section
      id="openings"
      class="py-20 px-6 bg-gradient-to-b from-white to-slate-50 dark:from-slate-900 dark:to-slate-950"
    >
      <div class="max-w-7xl mx-auto">
        <!-- Section Header -->
        <div class="text-center mb-16">
          <div class="inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-violet-500/10 to-fuchsia-500/10 border border-violet-200 dark:border-violet-800 backdrop-blur-sm mb-6">
            <UIcon
              name="i-lucide-briefcase"
              class="w-4 h-4 mr-2 text-violet-600 dark:text-violet-400"
            />
            <span class="text-sm font-medium text-violet-700 dark:text-violet-300">Open Positions</span>
          </div>
          <h2 class="text-3xl sm:text-5xl font-black mb-6">
            <span class="text-slate-900 dark:text-white">Find Your Perfect</span><br>
            <span class="bg-gradient-to-r from-violet-600 to-fuchsia-600 bg-clip-text text-transparent">Career Match</span>
          </h2>
          <p class="text-xl text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
            Explore our openings and discover where you can make your mark.
          </p>
        </div>

        <!-- Filter Controls -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl p-6 mb-12 border border-slate-200 dark:border-slate-700">
          <div class="flex flex-col lg:flex-row gap-6 items-center">
            <!-- Search -->
            <div class="relative flex-1 w-full">
              <UInput
                v-model="searchQuery"
                placeholder="Search by title, role, or location..."
                size="xl"
                :ui="{ base: 'pl-12' }"
              />
              <UIcon
                name="i-lucide-search"
                class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"
              />
            </div>

            <!-- Role Filter -->
            <USelect
              v-model="selectedRole"
              :items="[{ label: 'All Roles', value: 'all' }, ...roleOptions]"
              placeholder="Filter by Role"
              size="lg"
              class="w-full lg:w-48"
            />

            <!-- Type Filter -->
            <USelect
              v-model="selectedType"
              :items="[{ label: 'All Types', value: 'all' }, ...typeOptions]"
              placeholder="Filter by Type"
              size="lg"
              class="w-full lg:w-48"
            />

            <!-- Clear -->
            <UButton
              v-if="selectedRole !== 'all' || selectedType !== 'all' || searchQuery"
              variant="ghost"
              icon="i-lucide-x"
              @click="clearFilters"
            >
              Clear
            </UButton>

            <!-- Results Count -->
            <div class="text-sm font-bold text-slate-600 dark:text-slate-400 whitespace-nowrap bg-violet-50 dark:bg-violet-900/20 px-6 py-3 rounded-xl">
              <span class="text-violet-600 dark:text-violet-400 text-lg">{{ filteredJobs.length }}</span>
              {{ filteredJobs.length === 1 ? 'job' : 'jobs' }}
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div
          v-if="status === 'pending'"
          class="flex justify-center py-20"
        >
          <div class="flex flex-col items-center gap-4">
            <UIcon
              name="i-lucide-loader-2"
              class="w-12 h-12 animate-spin text-violet-600"
            />
            <p class="text-slate-600 dark:text-slate-400">
              Loading opportunities...
            </p>
          </div>
        </div>

        <!-- No Jobs -->
        <div
          v-else-if="!filteredJobs.length"
          class="text-center py-20"
        >
          <div class="max-w-md mx-auto bg-white dark:bg-slate-800 rounded-3xl shadow-xl p-12 border border-slate-200 dark:border-slate-700">
            <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-violet-500/20 to-fuchsia-500/20 rounded-full flex items-center justify-center">
              <UIcon
                name="i-lucide-briefcase"
                class="w-12 h-12 text-violet-600 dark:text-violet-400"
              />
            </div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">
              No Open Positions
            </h3>
            <p class="text-slate-600 dark:text-slate-400 mb-6">
              We don't have any matching positions right now. Check back soon for new opportunities!
            </p>
            <UButton
              v-if="searchQuery || selectedRole !== 'all' || selectedType !== 'all'"
              @click="clearFilters"
            >
              Clear Filters
            </UButton>
          </div>
        </div>

        <!-- Job Cards Grid -->
        <div
          v-else
          class="grid gap-8 md:grid-cols-2 lg:grid-cols-3"
        >
          <NuxtLink
            v-for="job in filteredJobs"
            :key="job.uuid"
            :to="`/career/${job.slug}`"
            class="group relative bg-white dark:bg-slate-800 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 overflow-hidden border border-slate-200 dark:border-slate-700 transform hover:-translate-y-2"
          >
            <!-- Card Header with gradient -->
            <div class="h-3 bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600" />

            <div class="p-8">
              <!-- Title & Badges -->
              <div class="flex items-start justify-between gap-4 mb-6">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors line-clamp-2">
                  {{ job.title }}
                </h3>
                <UBadge
                  v-if="job.is_payable"
                  color="warning"
                  size="sm"
                  class="shrink-0"
                >
                  Paid
                </UBadge>
              </div>

              <!-- Job Details -->
              <div class="space-y-3 mb-6">
                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                  <div class="w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                    <UIcon
                      name="i-lucide-briefcase"
                      class="w-4 h-4 text-violet-600 dark:text-violet-400"
                    />
                  </div>
                  <span>{{ job.role_label }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                  <div class="w-8 h-8 rounded-lg bg-fuchsia-100 dark:bg-fuchsia-900/30 flex items-center justify-center">
                    <UIcon
                      name="i-lucide-clock"
                      class="w-4 h-4 text-fuchsia-600 dark:text-fuchsia-400"
                    />
                  </div>
                  <span>{{ job.employment_type_label }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                  <div class="w-8 h-8 rounded-lg bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center">
                    <UIcon
                      name="i-lucide-map-pin"
                      class="w-4 h-4 text-pink-600 dark:text-pink-400"
                    />
                  </div>
                  <span>{{ job.location }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                  <div class="w-8 h-8 rounded-lg bg-cyan-100 dark:bg-cyan-900/30 flex items-center justify-center">
                    <UIcon
                      name="i-lucide-users"
                      class="w-4 h-4 text-cyan-600 dark:text-cyan-400"
                    />
                  </div>
                  <span>{{ job.vacancy }} {{ job.vacancy === 1 ? 'vacancy' : 'vacancies' }}</span>
                </div>
              </div>

              <!-- Footer -->
              <div class="flex items-center justify-between pt-6 border-t border-slate-200 dark:border-slate-700">
                <div class="text-sm text-slate-500 dark:text-slate-400">
                  <UIcon
                    name="i-lucide-calendar"
                    class="w-4 h-4 inline mr-1"
                  />
                  Closes: {{ job.close_date_formatted }}
                </div>
                <div class="flex items-center gap-2 text-violet-600 dark:text-violet-400 font-semibold group-hover:gap-3 transition-all">
                  Apply
                  <UIcon
                    name="i-lucide-arrow-right"
                    class="w-4 h-4"
                  />
                </div>
              </div>

              <!-- Fee Badge -->
              <div
                v-if="job.is_payable"
                class="mt-4 px-4 py-2 bg-amber-50 dark:bg-amber-900/20 rounded-xl text-sm font-medium text-amber-700 dark:text-amber-400"
              >
                <UIcon
                  name="i-lucide-indian-rupee"
                  class="w-4 h-4 inline mr-1"
                />
                Application Fee: {{ job.fees_formatted }}
              </div>
            </div>

            <!-- Hover Overlay -->
            <div class="absolute inset-0 bg-gradient-to-r from-violet-600/5 to-fuchsia-600/5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none" />
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Values Section -->
    <section class="py-20 px-6 bg-white dark:bg-slate-900 relative overflow-hidden">
      <!-- Background Pattern -->
      <div
        class="absolute inset-0 opacity-5 dark:opacity-10 pointer-events-none"
        style="background-image: radial-gradient(circle, currentColor 2px, transparent 2px); background-size: 40px 40px;"
      />

      <div class="relative z-10 max-w-7xl mx-auto">
        <!-- Section Header -->
        <div class="text-center mb-16">
          <div class="inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-rose-500/10 to-orange-500/10 border border-rose-200 dark:border-rose-800 backdrop-blur-sm mb-6">
            <UIcon
              name="i-lucide-heart"
              class="w-4 h-4 mr-2 text-rose-600 dark:text-rose-400"
            />
            <span class="text-sm font-medium text-rose-700 dark:text-rose-300">Our DNA</span>
          </div>
          <h2 class="text-3xl sm:text-5xl font-black mb-6">
            <span class="text-slate-900 dark:text-white">Values That</span><br>
            <span class="bg-gradient-to-r from-rose-600 to-orange-600 bg-clip-text text-transparent">Drive Us Forward</span>
          </h2>
          <p class="text-xl text-slate-600 dark:text-slate-300 max-w-3xl mx-auto">
            The principles that guide our decisions and define who we are.
          </p>
        </div>

        <!-- Values Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
          <div
            v-for="value in companyValues"
            :key="value.title"
            class="group relative bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-900 p-8 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-4 border border-slate-200/50 dark:border-slate-700/50 text-center overflow-hidden"
          >
            <div
              :class="[
                'w-20 h-20 mx-auto mb-6 rounded-2xl flex items-center justify-center transition-transform duration-500 group-hover:scale-110 group-hover:rotate-12',
                `bg-${value.color}-100 dark:bg-${value.color}-900/30 text-${value.color}-600 dark:text-${value.color}-400`
              ]"
            >
              <UIcon
                :name="value.icon"
                class="w-10 h-10"
              />
            </div>
            <h4 class="text-xl font-bold mb-4 text-slate-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
              {{ value.title }}
            </h4>
            <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
              {{ value.text }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Final CTA Section -->
    <section class="py-24 px-6 bg-gradient-to-r from-slate-900 via-violet-900 to-fuchsia-900 text-white relative overflow-hidden">
      <!-- Animated Particles -->
      <div class="absolute inset-0 pointer-events-none">
        <div
          class="absolute w-2 h-2 bg-white rounded-full opacity-30 animate-pulse"
          style="top: 20%; left: 10%;"
        />
        <div
          class="absolute w-1 h-1 bg-violet-300 rounded-full opacity-40 animate-pulse"
          style="top: 60%; left: 80%; animation-delay: 1s;"
        />
        <div
          class="absolute w-3 h-3 bg-fuchsia-300 rounded-full opacity-20 animate-pulse"
          style="top: 80%; left: 20%; animation-delay: 2s;"
        />
        <div
          class="absolute w-2 h-2 bg-pink-300 rounded-full opacity-35 animate-pulse"
          style="top: 40%; left: 70%; animation-delay: 3s;"
        />
      </div>

      <div class="relative z-10 max-w-5xl mx-auto text-center">
        <h2 class="text-4xl sm:text-6xl font-black mb-8 leading-tight">
          Ready to <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-pink-400 to-violet-400">Transform</span><br>
          Your Career Journey?
        </h2>

        <p class="text-xl sm:text-2xl text-slate-300 mb-12 max-w-4xl mx-auto leading-relaxed">
          We're not just offering jobs — we're building a place where you can thrive, innovate, and make a lasting impact.
        </p>

        <div class="flex flex-col sm:flex-row gap-6 justify-center items-center mb-16">
          <a
            href="#openings"
            class="group relative px-12 py-6 bg-gradient-to-r from-amber-400 to-orange-500 text-slate-900 font-black text-xl rounded-2xl shadow-2xl hover:shadow-amber-500/25 transition-all duration-300 transform hover:scale-105"
          >
            Explore Opportunities
            <UIcon
              name="i-lucide-rocket"
              class="inline w-6 h-6 ml-3 group-hover:translate-x-2 transition-transform"
            />
          </a>

          <NuxtLink
            v-if="!isLoggedIn"
            to="/auth/register"
            class="group px-12 py-6 bg-transparent border-2 border-white/30 text-white font-bold text-xl rounded-2xl hover:bg-white/10 transition-all duration-300 backdrop-blur-sm"
          >
            <UIcon
              name="i-lucide-user-plus"
              class="inline w-6 h-6 mr-3"
            />
            Join Us
          </NuxtLink>
        </div>

        <!-- Trust Indicators -->
        <div class="flex flex-wrap justify-center items-center gap-8 opacity-70">
          <div class="flex items-center text-slate-300">
            <UIcon
              name="i-lucide-shield-check"
              class="w-5 h-5 text-emerald-400 mr-2"
            />
            <span>Equal Opportunity</span>
          </div>
          <div class="flex items-center text-slate-300">
            <UIcon
              name="i-lucide-heart-pulse"
              class="w-5 h-5 text-rose-400 mr-2"
            />
            <span>Health & Wellness</span>
          </div>
          <div class="flex items-center text-slate-300">
            <UIcon
              name="i-lucide-trending-up"
              class="w-5 h-5 text-blue-400 mr-2"
            />
            <span>Career Growth</span>
          </div>
          <div class="flex items-center text-slate-300">
            <UIcon
              name="i-lucide-globe"
              class="w-5 h-5 text-green-400 mr-2"
            />
            <span>Global Impact</span>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
