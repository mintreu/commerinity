<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-gray-900 dark:to-purple-950 text-gray-800 dark:text-gray-100 overflow-x-hidden">

    <!-- Enhanced Loader -->
    <div v-if="!loaded" class="min-h-screen flex items-center justify-center">
      <div class="loading-container text-center space-y-6">
        <!-- Animated Logo/Icon -->
        <div class="loading-icon w-20 h-20 mx-auto bg-gradient-to-r from-blue-500 to-purple-500 rounded-2xl flex items-center justify-center animate-pulse">
          <Icon name="mdi:briefcase" class="text-3xl text-white" />
        </div>

        <!-- Loading Spinner -->
        <div class="relative">
          <div class="w-16 h-16 mx-auto animate-spin rounded-full border-4 border-blue-500/20 border-t-blue-500 dark:border-blue-400/20 dark:border-t-blue-400"></div>
          <div class="absolute inset-0 w-16 h-16 mx-auto animate-ping rounded-full border-2 border-blue-500/30"></div>
        </div>

        <!-- Loading Text -->
        <div class="space-y-2">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white">Loading Job Details</h3>
          <p class="text-gray-600 dark:text-gray-400">Please wait while we fetch the latest information...</p>
        </div>
      </div>
    </div>

    <!-- Job Found -->
    <div v-else-if="job" class="job-detail-content">

      <!-- Enhanced Hero Section -->
      <section class="hero-section relative min-h-screen flex items-center justify-center overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0">
          <div class="hero-orb-1 absolute top-20 left-20 w-72 h-72 bg-gradient-to-r from-blue-400 to-cyan-400 rounded-full opacity-15 blur-3xl"></div>
          <div class="hero-orb-2 absolute bottom-20 right-20 w-96 h-96 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full opacity-20 blur-3xl"></div>
          <div class="hero-orb-3 absolute top-1/2 left-1/3 w-64 h-64 bg-gradient-to-r from-emerald-400 to-teal-400 rounded-full opacity-10 blur-2xl"></div>
        </div>

        <!-- Hero Content -->
        <div class="relative z-10 max-w-6xl mx-auto px-6 py-20">
          <div class="hero-content text-center space-y-8">
            <!-- Back Navigation -->
            <div class="hero-nav">
              <NuxtLink to="/career"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors duration-300 group">
                <Icon name="mdi:arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform duration-300" />
                Back to Careers
              </NuxtLink>
            </div>

            <!-- Job Type Badge -->
            <div class="hero-badge inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-blue-500/10 to-purple-500/10 border border-blue-200 dark:border-blue-800 backdrop-blur-sm">
              <Icon name="mdi:briefcase" class="w-5 h-5 mr-3 text-blue-600 dark:text-blue-400" />
              <span class="font-semibold text-blue-700 dark:text-blue-300">{{ job.type }} Position</span>
            </div>

            <!-- Job Title -->
            <h1 class="hero-title text-4xl sm:text-6xl lg:text-7xl font-black leading-tight">
              <span class="block bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">{{ job.name }}</span>
            </h1>

            <!-- Job Meta Info -->
            <div class="hero-meta flex flex-wrap justify-center items-center gap-6 text-lg text-gray-600 dark:text-gray-300">
              <div class="flex items-center">
                <Icon name="mdi:map-marker" class="w-5 h-5 mr-2 text-red-500" />
                {{ job.location }}
              </div>
              <div class="flex items-center">
                <Icon name="mdi:puzzle" class="w-5 h-5 mr-2 text-blue-500" />
                {{ job.role }}
              </div>
              <div class="flex items-center">
                <Icon name="mdi:clock" class="w-5 h-5 mr-2 text-green-500" />
                {{ job.type }}
              </div>
            </div>

            <!-- Quick Apply CTA -->
            <div class="hero-cta pt-8">
              <NuxtLink :to="`/career/${job.url}/apply`"
                        class="cta-primary group px-12 py-6 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold text-xl rounded-2xl shadow-2xl hover:shadow-blue-500/25 transition-all duration-300 transform hover:scale-105">
                <Icon name="mdi:send" class="inline w-6 h-6 mr-3 group-hover:rotate-12 transition-transform duration-300" />
                <span class="relative z-10">Apply Now</span>
                <Icon name="mdi:arrow-right" class="inline w-6 h-6 ml-3 group-hover:translate-x-2 transition-transform duration-300" />
              </NuxtLink>
            </div>
          </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="scroll-indicator absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
          <div class="w-6 h-10 border-2 border-gray-400 dark:border-gray-600 rounded-full flex justify-center">
            <div class="w-1 h-3 bg-gradient-to-b from-blue-600 to-transparent rounded-full mt-2"></div>
          </div>
        </div>
      </section>

      <!-- Job Thumbnail Section -->
      <section class="thumbnail-section py-20 px-6 bg-white dark:bg-gray-900 relative">
        <div class="max-w-4xl mx-auto">
          <div class="thumbnail-container relative rounded-3xl overflow-hidden shadow-2xl transform hover:scale-105 transition-all duration-500">
            <NuxtImg
                :src="job.thumbnail"
                :alt="job.name"
                class="w-full h-96 sm:h-[500px] object-cover"
                loading="lazy"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>

            <!-- Overlay Content -->
            <div class="absolute bottom-8 left-8 right-8 text-white">
              <h3 class="text-2xl font-bold mb-2">Join Our Team</h3>
              <p class="text-lg opacity-90">Be part of something extraordinary</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Job Details Section -->
      <section class="details-section py-20 px-6 bg-gradient-to-b from-gray-50 to-white dark:from-gray-950 dark:to-gray-900">
        <div class="max-w-6xl mx-auto">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-12">
              <!-- Job Description -->
              <div class="description-card bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-xl border border-gray-200/50 dark:border-gray-700/50">
                <div class="section-header mb-8">
                  <div class="section-badge inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-purple-500/10 to-pink-500/10 border border-purple-200 dark:border-purple-800 backdrop-blur-sm mb-4">
                    <Icon name="mdi:file-document" class="w-4 h-4 mr-2 text-purple-600 dark:text-purple-400" />
                    <span class="text-sm font-medium text-purple-700 dark:text-purple-300">Job Description</span>
                  </div>
                  <h2 class="text-3xl font-black text-gray-900 dark:text-white">About This Role</h2>
                </div>

                <div class="description-content space-y-6">
                  <div v-for="(para, index) in parsedDescription" :key="index"
                       class="description-paragraph text-gray-700 dark:text-gray-300 leading-relaxed text-lg whitespace-pre-line">
                    {{ para }}
                  </div>
                </div>
              </div>

              <!-- PDF Documents -->
              <div v-if="!isZip && pdfList.length" class="documents-section">
                <div class="section-header mb-8">
                  <div class="section-badge inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-emerald-500/10 to-teal-500/10 border border-emerald-200 dark:border-emerald-800 backdrop-blur-sm mb-4">
                    <Icon name="mdi:file-multiple" class="w-4 h-4 mr-2 text-emerald-600 dark:text-emerald-400" />
                    <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300">Documents</span>
                  </div>
                  <h2 class="text-3xl font-black text-gray-900 dark:text-white">Related Documents</h2>
                </div>

                <div class="documents-grid grid grid-cols-1 sm:grid-cols-2 gap-6">
                  <div v-for="(pdfUrl, index) in pdfList" :key="index"
                       class="document-card group bg-white dark:bg-gray-800 rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">

                    <!-- Document Preview -->
                    <div class="document-preview relative h-48 bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 flex items-center justify-center group-hover:scale-105 transition-transform duration-500">
                      <Icon name="mdi:file-pdf-box" class="text-6xl text-red-500 group-hover:scale-110 group-hover:rotate-6 transition-all duration-500" />
                      <div class="absolute inset-0 bg-gradient-to-t from-red-600/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>

                    <!-- Document Info -->
                    <div class="document-info p-6">
                      <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3 group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors duration-300 line-clamp-2">
                        {{ getPdfName(pdfUrl, index) }}
                      </h3>

                      <a :href="pdfUrl" target="_blank" rel="noopener"
                         class="download-btn inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-500 to-orange-500 text-white font-bold rounded-xl hover:shadow-lg transition-all duration-300 transform hover:scale-105 group/btn">
                        <Icon name="mdi:download" class="w-5 h-5 mr-2 group-hover/btn:animate-bounce" />
                        Download
                        <Icon name="mdi:external-link" class="w-4 h-4 ml-2 group-hover/btn:translate-x-1 transition-transform duration-300" />
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Sidebar -->
            <div class="sidebar space-y-8">
              <!-- Job Information Card -->
              <div class="info-card bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-3xl p-8 shadow-xl border border-gray-200/50 dark:border-gray-700/50">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                  <Icon name="mdi:information" class="w-6 h-6 mr-3 text-blue-500" />
                  Job Information
                </h3>

                <div class="info-grid space-y-6">
                  <div class="info-item">
                    <div class="flex items-center mb-2">
                      <Icon name="mdi:calendar-start" class="w-5 h-5 mr-3 text-green-500" />
                      <span class="font-semibold text-gray-700 dark:text-gray-300">Open Date</span>
                    </div>
                    <p class="text-gray-900 dark:text-white font-bold">{{ job.open_date }}</p>
                  </div>

                  <div class="info-item">
                    <div class="flex items-center mb-2">
                      <Icon name="mdi:calendar-end" class="w-5 h-5 mr-3 text-red-500" />
                      <span class="font-semibold text-gray-700 dark:text-gray-300">Close Date</span>
                    </div>
                    <p class="text-gray-900 dark:text-white font-bold">{{ job.close_date }}</p>
                  </div>

                  <div class="info-item">
                    <div class="flex items-center mb-2">
                      <Icon name="mdi:account-multiple" class="w-5 h-5 mr-3 text-blue-500" />
                      <span class="font-semibold text-gray-700 dark:text-gray-300">Vacancies</span>
                    </div>
                    <p class="text-gray-900 dark:text-white font-bold">{{ job.vacancy }} Position{{ job.vacancy > 1 ? 's' : '' }}</p>
                  </div>

                  <div v-if="job.is_payable" class="info-item">
                    <div class="flex items-center mb-2">
                      <Icon name="mdi:currency-inr" class="w-5 h-5 mr-3 text-emerald-500" />
                      <span class="font-semibold text-gray-700 dark:text-gray-300">Application Fee</span>
                    </div>
                    <p class="text-gray-900 dark:text-white font-bold">₹{{ job.fees }}</p>
                  </div>
                </div>
              </div>

              <!-- Action Cards -->
              <div class="action-cards space-y-6">
                <!-- Apply Card -->
                <div class="apply-card bg-gradient-to-r from-blue-600 to-purple-600 rounded-3xl p-8 text-white shadow-2xl transform hover:scale-105 transition-all duration-300">
                  <div class="text-center space-y-4">
                    <Icon name="mdi:rocket-launch" class="w-12 h-12 mx-auto text-yellow-300" />
                    <h3 class="text-xl font-bold">Ready to Apply?</h3>
                    <p class="text-blue-100">Take the next step in your career journey</p>
                    <NuxtLink :to="`/career/${job.url}/apply`"
                              class="apply-btn inline-flex items-center px-8 py-4 bg-white text-blue-600 font-bold rounded-2xl hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 group">
                      <Icon name="mdi:send" class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform duration-300" />
                      Apply Now
                    </NuxtLink>
                  </div>
                </div>

                <!-- Download ZIP Card -->
                <div v-if="isZip" class="download-card bg-gradient-to-r from-emerald-600 to-teal-600 rounded-3xl p-8 text-white shadow-2xl transform hover:scale-105 transition-all duration-300">
                  <div class="text-center space-y-4">
                    <Icon name="mdi:archive" class="w-12 h-12 mx-auto text-yellow-300" />
                    <h3 class="text-xl font-bold">Download Resources</h3>
                    <p class="text-emerald-100">Get all related documents in one package</p>
                    <a :href="pdfList[0]" target="_blank" rel="noopener"
                       class="download-btn inline-flex items-center px-8 py-4 bg-white text-emerald-600 font-bold rounded-2xl hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 group">
                      <Icon name="mdi:download" class="w-5 h-5 mr-2 group-hover:animate-bounce" />
                      Download ZIP
                    </a>
                  </div>
                </div>

                <!-- Share Card -->
                <div class="share-card bg-gradient-to-r from-pink-600 to-rose-600 rounded-3xl p-8 text-white shadow-2xl transform hover:scale-105 transition-all duration-300">
                  <div class="text-center space-y-4">
                    <Icon name="mdi:share-variant" class="w-12 h-12 mx-auto text-yellow-300" />
                    <h3 class="text-xl font-bold">Share This Job</h3>
                    <p class="text-pink-100">Help others find this opportunity</p>
                    <button @click="shareJob"
                            class="share-btn inline-flex items-center px-8 py-4 bg-white text-pink-600 font-bold rounded-2xl hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 group">
                      <Icon name="mdi:share" class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform duration-300" />
                      Share Job
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Similar Jobs Section -->
      <section class="similar-jobs py-20 px-6 bg-white dark:bg-gray-900">
        <div class="max-w-6xl mx-auto text-center">
          <h2 class="text-3xl sm:text-4xl font-black mb-12">
            <span class="text-gray-900 dark:text-white">Explore More</span><br>
            <span class="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Career Opportunities</span>
          </h2>

          <NuxtLink to="/career"
                    class="explore-btn group px-12 py-6 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold text-xl rounded-2xl shadow-2xl hover:shadow-purple-500/25 transition-all duration-300 transform hover:scale-105">
            <Icon name="mdi:briefcase-search" class="inline w-6 h-6 mr-3 group-hover:rotate-12 transition-transform duration-300" />
            View All Jobs
            <Icon name="mdi:arrow-right" class="inline w-6 h-6 ml-3 group-hover:translate-x-2 transition-transform duration-300" />
          </NuxtLink>
        </div>
      </section>
    </div>

    <!-- Not Found -->
    <div v-else class="not-found min-h-screen flex items-center justify-center">
      <div class="text-center space-y-8 max-w-md mx-auto px-6">
        <!-- 404 Icon -->
        <div class="error-icon w-24 h-24 mx-auto bg-gradient-to-r from-red-500 to-orange-500 rounded-full flex items-center justify-center">
          <Icon name="mdi:alert-circle" class="text-4xl text-white" />
        </div>

        <!-- Error Message -->
        <div class="space-y-4">
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Job Not Found</h2>
          <p class="text-lg text-gray-600 dark:text-gray-400">The job you're looking for doesn't exist or has been removed.</p>
        </div>

        <!-- Back Button -->
        <NuxtLink to="/career"
                  class="back-btn inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-2xl shadow-xl hover:shadow-blue-500/25 transition-all duration-300 transform hover:scale-105 group">
          <Icon name="mdi:arrow-left" class="w-5 h-5 mr-3 group-hover:-translate-x-2 transition-transform duration-300" />
          Back to Careers
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useSanctumFetch, useRuntimeConfig } from '#imports'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

// Register GSAP plugins
if (process.client) {
  gsap.registerPlugin(ScrollTrigger)
}

// Global loading animation
const loading = useState('pageLoading', () => false)
loading.value = true

const job = ref<Job | null>(null)
const loaded = ref(false)

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

// PDF handling
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

// Share functionality
const shareJob = async () => {
  if (navigator.share && job.value) {
    try {
      await navigator.share({
        title: job.value.name,
        text: `Check out this job opportunity: ${job.value.name} at ${job.value.location}`,
        url: window.location.href
      })
    } catch (error) {
      // Fallback to copying URL
      copyToClipboard()
    }
  } else {
    copyToClipboard()
  }
}

const copyToClipboard = () => {
  navigator.clipboard.writeText(window.location.href).then(() => {
    // You could add a toast notification here
    alert('Job URL copied to clipboard!')
  })
}

// Data fetching
onMounted(async () => {
  try {
    const apiUrl = `${config.public.apiBase}/recruitment/${route.params.url}`
    const response = await useSanctumFetch(apiUrl)
    job.value = response?.data || null
  } catch (error) {
    console.error('Error fetching job:', error)
    job.value = null
  } finally {
    loaded.value = true
    loading.value = false

    // Initialize animations after data loads
    if (process.client && job.value) {
      initializeAnimations()
    }
  }
})

// Animations
const initializeAnimations = () => {
  // Hero timeline animation
  const heroTl = gsap.timeline()

  heroTl.from('.hero-orb-1', { scale: 0, opacity: 0, duration: 2, ease: 'elastic.out(1, 0.5)' })
      .from('.hero-orb-2', { scale: 0, opacity: 0, duration: 2, ease: 'elastic.out(1, 0.5)' }, '-=1.5')
      .from('.hero-orb-3', { scale: 0, opacity: 0, duration: 2, ease: 'elastic.out(1, 0.5)' }, '-=1.5')
      .from('.hero-nav', { y: -30, opacity: 0, duration: 0.8, ease: 'back.out(1.7)' })
      .from('.hero-badge', { y: 50, opacity: 0, duration: 1, ease: 'back.out(1.7)' }, '-=0.6')
      .from('.hero-title', { y: 100, opacity: 0, duration: 1.2, ease: 'back.out(1.7)' }, '-=0.8')
      .from('.hero-meta', { y: 30, opacity: 0, duration: 0.8, ease: 'back.out(1.7)' }, '-=0.6')
      .from('.hero-cta', { y: 50, opacity: 0, duration: 1, ease: 'back.out(1.7)' }, '-=0.4')

  // Floating animations
  gsap.to('.hero-orb-1', {
    y: -30,
    rotation: 10,
    duration: 6,
    ease: 'power2.inOut',
    yoyo: true,
    repeat: -1
  })

  gsap.to('.hero-orb-2', {
    y: 40,
    rotation: -15,
    duration: 8,
    ease: 'power2.inOut',
    yoyo: true,
    repeat: -1
  })

  gsap.to('.hero-orb-3', {
    y: -20,
    rotation: 12,
    duration: 5,
    ease: 'power2.inOut',
    yoyo: true,
    repeat: -1
  })

  // Scroll-triggered animations
  const observerOptions = {
    start: 'top 80%',
    toggleActions: 'play none none reverse'
  }

  // Thumbnail animation
  gsap.fromTo('.thumbnail-container',
      { y: 100, opacity: 0, scale: 0.9 },
      {
        y: 0,
        opacity: 1,
        scale: 1,
        duration: 1.2,
        ease: 'back.out(1.7)',
        scrollTrigger: {
          trigger: '.thumbnail-section',
          ...observerOptions
        }
      }
  )

  // Description card animation
  gsap.fromTo('.description-card',
      { y: 80, opacity: 0, scale: 0.95 },
      {
        y: 0,
        opacity: 1,
        scale: 1,
        duration: 1,
        ease: 'back.out(1.7)',
        scrollTrigger: {
          trigger: '.description-card',
          ...observerOptions
        }
      }
  )

  // Description paragraphs stagger
  gsap.fromTo('.description-paragraph',
      { y: 30, opacity: 0 },
      {
        y: 0,
        opacity: 1,
        duration: 0.8,
        ease: 'power2.out',
        stagger: 0.2,
        scrollTrigger: {
          trigger: '.description-content',
          ...observerOptions
        }
      }
  )

  // Document cards animation
  gsap.fromTo('.document-card',
      { y: 60, opacity: 0, scale: 0.9, rotateY: 10 },
      {
        y: 0,
        opacity: 1,
        scale: 1,
        rotateY: 0,
        duration: 0.8,
        ease: 'back.out(1.7)',
        stagger: 0.2,
        scrollTrigger: {
          trigger: '.documents-grid',
          ...observerOptions
        }
      }
  )

  // Sidebar animations
  gsap.fromTo('.info-card, .action-cards > *',
      { x: 100, opacity: 0, scale: 0.9 },
      {
        x: 0,
        opacity: 1,
        scale: 1,
        duration: 0.8,
        ease: 'back.out(1.7)',
        stagger: 0.2,
        scrollTrigger: {
          trigger: '.sidebar',
          ...observerOptions
        }
      }
  )

  // Section badges
  gsap.fromTo('.section-badge',
      { y: 20, opacity: 0, scale: 0.8 },
      {
        y: 0,
        opacity: 1,
        scale: 1,
        duration: 0.6,
        ease: 'back.out(1.7)',
        scrollTrigger: {
          trigger: '.section-badge',
          ...observerOptions
        }
      }
  )
}
</script>

<style scoped>
/* Custom animations and effects */
.cta-primary::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s;
}

.cta-primary:hover::before {
  left: 100%;
}

.document-card::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #ef4444, #f97316, #f59e0b);
  transform: scaleX(0);
  transition: transform 0.4s ease;
  border-radius: 1rem 1rem 0 0;
}

.document-card:hover::after {
  transform: scaleX(1);
}

.info-card::before {
  content: '';
  position: absolute;
  inset: 0;
  padding: 2px;
  background: linear-gradient(135deg, transparent, rgba(59, 130, 246, 0.3), transparent);
  border-radius: 1.5rem;
  mask: linear-gradient(white 0 0) content-box, linear-gradient(white 0 0);
  mask-composite: exclude;
  opacity: 0;
  transition: opacity 0.4s ease;
}

.info-card:hover::before {
  opacity: 1;
}

.apply-card, .download-card, .share-card {
  position: relative;
  overflow: hidden;
}

.apply-card::before,
.download-card::before,
.share-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
  transition: left 0.6s;
}

.apply-card:hover::before,
.download-card:hover::before,
.share-card:hover::before {
  left: 100%;
}

/* Loading animation */
.loading-icon {
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
  }
  50% {
    transform: scale(1.05);
    box-shadow: 0 0 0 20px rgba(59, 130, 246, 0);
  }
}

/* Mobile responsiveness */
@media (max-width: 640px) {
  .hero-title {
    font-size: 2.5rem;
    line-height: 1.2;
  }

  .hero-meta {
    flex-direction: column;
    gap: 1rem;
  }

  .document-card, .info-card {
    padding: 1.5rem;
  }

  .hero-orb-2 {
    display: none;
  }
}

@media (max-width: 480px) {
  .hero-orb-1, .hero-orb-3 {
    width: 8rem;
    height: 8rem;
  }

  .thumbnail-container {
    border-radius: 1rem;
  }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .document-card, .info-card, .description-card {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }
}

/* Scroll indicator */
.scroll-indicator:hover {
  transform: translateX(-50%) scale(1.1);
}

/* Line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
