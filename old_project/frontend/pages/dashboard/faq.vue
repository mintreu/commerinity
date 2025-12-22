<template>
  <div class="min-h-screen flex flex-col">
    <!-- Content Section -->
    <div class="flex-grow p-6 max-w-6xl w-full mx-auto space-y-10">
      <!-- Page Header -->
      <div class="flex items-center gap-3">
        <Icon name="mdi-frequently-asked-questions" class="text-3xl text-blue-600 dark:text-blue-400" />
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">FAQs · Help Center</h1>
      </div>

      <!-- FAQ Table with Accordion -->
      <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden shadow-sm">
        <!-- Header with Search -->
        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Frequently Asked Questions</h2>
          <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by keyword..."
              class="w-full sm:w-64 px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring focus:ring-blue-500"
          />
        </div>

        <!-- FAQ Rows -->
        <div class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
          <div
              v-for="(faq, index) in paginatedFaqs"
              :key="index"
              class="hover:bg-gray-50 dark:hover:bg-gray-800 transition"
          >
            <button
                @click="toggle(index)"
                class="w-full text-left px-6 py-5 flex justify-between items-start gap-4"
            >
              <span class="text-gray-800 dark:text-gray-100 font-medium leading-snug">
                {{ index + 1 + (currentPage - 1) * pageSize }}. {{ faq.question }}
              </span>
              <Icon :name="openIndex === index ? 'mdi-chevron-up' : 'mdi-chevron-down'" class="text-xl text-blue-600 dark:text-blue-400 mt-1" />
            </button>

            <transition name="fade">
              <div
                  v-if="openIndex === index"
                  class="px-6 pb-5 pt-0 text-gray-700 dark:text-gray-300 text-sm leading-relaxed"
              >
                {{ faq.answer }}
              </div>
            </transition>
          </div>
        </div>

        <!-- Table Footer: Pagination -->
        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-4 flex flex-col sm:flex-row justify-between items-center text-sm text-gray-700 dark:text-gray-300 gap-4">
          <div>
            Showing
            <strong>{{ startIndex + 1 }}</strong>
            to
            <strong>{{ endIndex }}</strong>
            of
            <strong>{{ filteredFaqs.length }}</strong>
            FAQs
          </div>
          <div class="flex items-center gap-2">
            <button
                @click="prevPage"
                :disabled="currentPage === 1"
                class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50"
            >
              Prev
            </button>
            <span class="min-w-[60px] text-center">Page {{ currentPage }}</span>
            <button
                @click="nextPage"
                :disabled="endIndex >= filteredFaqs.length"
                class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- CTA Footer -->
    <div class="mt-auto bg-blue-50 dark:bg-blue-900 border-t border-blue-200 dark:border-blue-700 py-6 px-6 text-center">
      <h2 class="text-lg font-semibold text-blue-800 dark:text-blue-100 mb-2">
        Didn't find your answer?
      </h2>
      <p class="text-sm text-blue-700 dark:text-blue-300 mb-4">
        Submit a support ticket and we’ll respond within 24 hours.
      </p>
      <NuxtLink to="/dashboard/helpdesk/create">
        <button class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700 transition">
          <Icon name="mdi-lifebuoy" class="inline-block mr-1 -mt-1" />
          Submit a Ticket
        </button>
      </NuxtLink>
    </div>
  </div>
</template>

<script setup>
definePageMeta({ layout: 'dashboard' })

const searchQuery = ref('')
const openIndex = ref(null)
const currentPage = ref(1)
const pageSize = 5

const faqs = ref([
  { question: 'How long does it take to get a response?', answer: 'We usually respond within 24 hours on business days. For critical issues, response times may be shorter.' },
  { question: 'Can I update my ticket after submitting it?', answer: 'Yes. You can reply to your ticket thread and upload additional attachments or information at any time.' },
  { question: 'What should I include in my ticket?', answer: 'Please include a clear description, relevant screenshots, error messages, and steps to reproduce the issue.' },
  { question: 'Where can I track my ticket status?', answer: 'All your submitted tickets appear under the Helpdesk section. You can view conversations and status there.' },
  { question: 'Is there a phone or live chat support?', answer: 'Currently, we provide support via our ticketing system only to ensure proper tracking and documentation.' },
  { question: 'How do I reset my password?', answer: 'Click on the “Forgot password” link on the login page and follow the instructions sent to your email.' },
  { question: 'Can I delete my account?', answer: 'Please contact support with a formal request and account verification. Deletion is permanent.' },
  { question: 'Is there a mobile app available?', answer: 'Yes, our mobile app is available for iOS and Android on their respective stores.' },
])

const toggle = (index) => {
  openIndex.value = openIndex.value === index ? null : index
}

const filteredFaqs = computed(() =>
    faqs.value.filter(faq =>
        faq.question.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        faq.answer.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
)

const startIndex = computed(() => (currentPage.value - 1) * pageSize)
const endIndex = computed(() => Math.min(startIndex.value + pageSize, filteredFaqs.value.length))
const paginatedFaqs = computed(() =>
    filteredFaqs.value.slice(startIndex.value, endIndex.value)
)

function nextPage() {
  if (endIndex.value < filteredFaqs.value.length) currentPage.value++
}

function prevPage() {
  if (currentPage.value > 1) currentPage.value--
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: all 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: scaleY(0.95);
}
</style>
