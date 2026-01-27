<script setup lang="ts">
/**
 * FAQ - Premium Redesign
 * Searchable accordion with categories and animated transitions
 */

const search = ref('')
const selectedCategory = ref('All')

const categories = ['All', 'Account', 'Orders', 'Wallet', 'Security']

const faqs = ref([
  { category: 'Security', question: 'How do I reset my password?', answer: 'Navigate to the login page, click "Forgot Password," and follow the instructions sent to your registered mobile number or email.' },
  { category: 'Account', question: 'How long for support response?', answer: 'Our support team typically responds within 2-4 business hours. Premium members get priority 1-hour resolution.' },
  { category: 'Orders', question: 'Can I update my ticket?', answer: 'Yes, you can reply directly to any active ticket from your Support Center dashboard to provide additional information.' },
  { category: 'Wallet', question: 'How to check transaction status?', answer: 'All wallet transactions are visible in real-time under the "Transactions" section of your sidebar or within the Wallet dashboard.' },
  { category: 'Account', question: 'How do I upgrade my status?', answer: 'Visit the Membership page to see available plans and upgrade using your wallet credits.' },
  { category: 'Security', question: 'What is the transaction PIN?', answer: 'The transaction PIN is a secondary 6-digit security code required for all financial operations including subscriptions and withdrawals.' }
])

const filteredFaqs = computed(() => {
  return faqs.value.filter((f) => {
    const matchesSearch = f.question.toLowerCase().includes(search.value.toLowerCase())
      || f.answer.toLowerCase().includes(search.value.toLowerCase())
    const matchesCategory = selectedCategory.value === 'All' || f.category === selectedCategory.value
    return matchesSearch && matchesCategory
  })
})
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-12 pb-20 pt-8">
    <!-- Hero Section -->
    <div class="text-center space-y-4">
      <h1 class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
        How can we <span class="text-primary-600 dark:text-primary-400">help you?</span>
      </h1>
      <p class="text-slate-500 dark:text-slate-400 text-lg max-w-xl mx-auto font-medium">
        Search our knowledge base for answers to common questions about Mintreu.
      </p>

      <div class="max-w-2xl mx-auto pt-6">
        <UInput
          v-model="search"
          placeholder="Search for keywords (e.g. password, wallet, upgrade)..."
          icon="i-lucide-search"
          size="xl"
          class="rounded-[30px]"
          :ui="{ base: 'h-16 text-lg px-8 shadow-2xl shadow-primary-500/10 ring-2 ring-primary-500/10 focus:ring-primary-500' }"
        />
      </div>
    </div>

    <!-- Categories -->
    <div class="flex flex-wrap justify-center gap-2">
      <button
        v-for="cat in categories"
        :key="cat"
        :class="[
          'px-6 py-2 rounded-full text-sm font-black uppercase tracking-widest transition-all duration-300',
          selectedCategory === cat
            ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/20'
            : 'bg-slate-100 dark:bg-slate-800 text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700'
        ]"
        @click="selectedCategory = cat"
      >
        {{ cat }}
      </button>
    </div>

    <!-- FAQ Accordion -->
    <div class="glass-card p-4 border-none ring-1 ring-slate-200 dark:ring-slate-800 rounded-[40px]">
      <div
        v-if="filteredFaqs.length === 0"
        class="py-20 text-center text-slate-400 font-bold uppercase tracking-widest text-xs"
      >
        No matching questions found
      </div>

      <UAccordion
        v-else
        :items="filteredFaqs"
        multiple
        class="space-y-4"
        :ui="{ wrapper: 'flex flex-col gap-2' }"
      >
        <template #default="{ item, open }">
          <UButton
            variant="ghost"
            class="group w-full flex items-center justify-between p-6 rounded-3xl hover:bg-primary-500/5 transition-all text-left"
            :class="open ? 'bg-primary-500/5' : ''"
          >
            <div class="flex items-center gap-4">
              <div
                :class="[
                  'w-10 h-10 rounded-2xl flex items-center justify-center transition-all duration-500',
                  open ? 'bg-primary-600 text-white rotate-[360deg]' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 group-hover:text-primary-500'
                ]"
              >
                <UIcon
                  name="i-lucide-help-circle"
                  class="w-5 h-5"
                />
              </div>
              <span class="font-black text-slate-900 dark:text-white">{{ item.question }}</span>
            </div>

            <UIcon
              :name="open ? 'i-lucide-minus' : 'i-lucide-plus'"
              :class="['w-5 h-5 transition-all duration-300', open ? 'text-primary-500 rotate-180' : 'text-slate-300']"
            />
          </UButton>
        </template>

        <template #item="{ item }">
          <div class="px-20 pb-8 animate-in fade-in slide-in-from-top-2 duration-500">
            <p class="text-slate-600 dark:text-slate-400 leading-relaxed font-medium">
              {{ item.answer }}
            </p>
            <div class="mt-4 flex items-center gap-2">
              <span class="text-[10px] font-black uppercase tracking-widest text-slate-300">Category:</span>
              <UBadge
                size="xs"
                variant="soft"
                color="primary"
                class="rounded-full px-3 text-[10px] items-center"
              >
                {{ item.category }}
              </UBadge>
            </div>
          </div>
        </template>
      </UAccordion>
    </div>

    <!-- CTA Footer -->
    <div class="glass-card relative overflow-hidden p-10 border-none bg-slate-900 text-white rounded-[40px] text-center space-y-6">
      <div class="absolute inset-0 bg-gradient-to-br from-primary-600/20 to-purple-600/20" />
      <h3 class="text-2xl font-black relative z-10">
        Still need assistance?
      </h3>
      <p class="text-slate-400 max-w-sm mx-auto relative z-10">
        If you couldn't find the answer you looking for, please create a support ticket.
      </p>
      <UButton
        to="/helpdesk/create"
        size="xl"
        color="primary"
        class="rounded-2xl px-10 font-black relative z-10 shadow-xl shadow-primary-500/20 hover:scale-105 transition-all"
      >
        Open Support Ticket
      </UButton>
    </div>
  </div>
</template>
