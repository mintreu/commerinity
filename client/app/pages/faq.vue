<template>
  <div class="min-h-screen p-6">
    <div class="max-w-4xl mx-auto space-y-6">
      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Frequently Asked Questions</h1>
            <UInput v-model="search" placeholder="Search FAQs..." icon="i-heroicons-magnifying-glass" />
          </div>
        </template>

        <UAccordion :items="filteredFaqs" :ui="{ wrapper: 'space-y-2' }">
          <template #default="{ item, open }">
            <UButton variant="ghost" class="w-full">
              <template #leading>
                <UIcon :name="open ? 'i-heroicons-chevron-up' : 'i-heroicons-chevron-down'" />
              </template>
              {{ item.question }}
            </UButton>
          </template>
          <template #item="{ item }">
            <p class="text-gray-600 dark:text-gray-400">{{ item.answer }}</p>
          </template>
        </UAccordion>

        <template #footer>
          <div class="text-center">
            <p class="mb-4">Didn't find your answer?</p>
            <UButton to="/helpdesk/create" color="primary">Submit a Ticket</UButton>
          </div>
        </template>
      </UCard>
    </div>
  </div>
</template>

<script setup lang="ts">
const search = ref('')
const faqs = ref([
  { question: 'How do I reset my password?', answer: 'Click "Forgot Password" on login page and follow email instructions.' },
  { question: 'How long for support response?', answer: 'We respond within 24 hours on business days.' },
  { question: 'Can I update my ticket?', answer: 'Yes, reply to your ticket anytime with updates.' },
  { question: 'How to check ticket status?', answer: 'View all tickets in the Helpdesk section.' },
])

const filteredFaqs = computed(() => 
  faqs.value.filter(f => 
    f.question.toLowerCase().includes(search.value.toLowerCase()) ||
    f.answer.toLowerCase().includes(search.value.toLowerCase())
  )
)
</script>
