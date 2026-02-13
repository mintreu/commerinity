<script setup lang="ts">
import type { FaqSection } from '~/composables/useFaq'
import { getEmptyStateMessage } from '~/utils/api-error'

const props = withDefaults(defineProps<{
  context?: 'general' | 'dashboard'
  title?: string
  subtitle?: string
}>(), {
  context: 'general'
})

const search = ref('')
const selectedTopic = ref<'all' | string>('all')
const { user, isLoggedIn } = useSanctum()
const {
  sections,
  isLoading,
  error,
  fetchGeneralFaqSections,
  fetchDashboardFaqSections
} = useFaq()

const sectionList = computed<FaqSection[]>(() => sections.value || [])

const availableTopics = computed(() => {
  return sectionList.value.map(section => ({
    slug: section.topic.slug,
    name: section.topic.name
  }))
})

const flattenedFaqs = computed(() => {
  return sectionList.value.flatMap(section =>
    section.faqs.map(faq => ({
      ...faq,
      topic_name: section.topic.name,
      topic_slug: section.topic.slug
    }))
  )
})

const accordionItems = computed(() => {
  return filteredFaqs.value.map(faq => ({
    label: faq.question,
    slot: `faq-${faq.id}`
  }))
})

const totalFaqCount = computed(() => flattenedFaqs.value.length)
const activeTopicLabel = computed(() => {
  if (selectedTopic.value === 'all') return 'All topics'
  return availableTopics.value.find(topic => topic.slug === selectedTopic.value)?.name || 'All topics'
})

const filteredFaqs = computed(() => {
  const query = search.value.trim().toLowerCase()

  return flattenedFaqs.value.filter((faq) => {
    const matchesTopic = selectedTopic.value === 'all' || faq.topic_slug === selectedTopic.value
    if (!matchesTopic) return false

    if (!query) return true
    const haystack = `${faq.question} ${faq.answer} ${(faq.tags || []).join(' ')}`.toLowerCase()
    return haystack.includes(query)
  })
})

const load = async () => {
  if (props.context === 'dashboard') {
    const audience = (user.value?.type as string | undefined) || (isLoggedIn.value ? 'regular' : 'guest')
    await fetchDashboardFaqSections(audience)
  } else {
    await fetchGeneralFaqSections()
  }

  if (selectedTopic.value !== 'all') {
    const exists = availableTopics.value.some(topic => topic.slug === selectedTopic.value)
    if (!exists) selectedTopic.value = 'all'
  }
}

watch(() => props.context, load, { immediate: true })
</script>

<template>
  <div class="space-y-8 md:space-y-10">
    <section class="relative overflow-hidden rounded-3xl border border-slate-200/70 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-8">
      <div class="pointer-events-none absolute -right-16 -top-16 h-44 w-44 rounded-full bg-violet-500/10 blur-3xl" />
      <div class="pointer-events-none absolute -bottom-20 -left-16 h-48 w-48 rounded-full bg-fuchsia-500/10 blur-3xl" />

      <div class="relative space-y-5">
        <div class="space-y-2 text-center">
          <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500 dark:text-slate-400">
            FAQ
          </p>
          <h1 class="text-3xl font-black text-slate-900 dark:text-white md:text-4xl">
            {{ props.title || 'Help Center FAQ' }}
          </h1>
          <p class="mx-auto max-w-2xl text-sm text-slate-600 dark:text-slate-300 md:text-base">
            {{ props.subtitle || 'Browse common questions and answers that serve every user role.' }}
          </p>
        </div>

        <div class="flex flex-wrap justify-center gap-2 text-xs">
          <UBadge
            variant="soft"
            color="primary"
          >
            {{ totalFaqCount }} total FAQs
          </UBadge>
          <UBadge
            variant="soft"
            color="neutral"
          >
            {{ filteredFaqs.length }} matched
          </UBadge>
          <UBadge
            variant="soft"
            color="neutral"
          >
            {{ activeTopicLabel }}
          </UBadge>
        </div>
      </div>
    </section>

    <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:grid-cols-3">
      <div class="md:col-span-2">
        <UInput
          v-model="search"
          icon="i-lucide-search"
          placeholder="Search by question, answer, or keyword..."
          size="xl"
          class="w-full"
        />
      </div>
      <USelectMenu
        v-model="selectedTopic"
        :options="[
          { label: 'All topics', value: 'all' },
          ...availableTopics.map(topic => ({ label: topic.name, value: topic.slug }))
        ]"
        value-key="value"
        size="xl"
        class="w-full"
      />
    </div>

    <div class="flex flex-wrap gap-2">
      <button
        type="button"
        class="rounded-full border px-3 py-1.5 text-xs font-medium transition"
        :class="selectedTopic === 'all'
          ? 'border-violet-600 bg-violet-600 text-white'
          : 'border-slate-200 bg-white text-slate-600 hover:border-violet-300 hover:text-violet-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'"
        @click="selectedTopic = 'all'"
      >
        All topics
      </button>
      <button
        v-for="topic in availableTopics"
        :key="topic.slug"
        type="button"
        class="rounded-full border px-3 py-1.5 text-xs font-medium transition"
        :class="selectedTopic === topic.slug
          ? 'border-violet-600 bg-violet-600 text-white'
          : 'border-slate-200 bg-white text-slate-600 hover:border-violet-300 hover:text-violet-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'"
        @click="selectedTopic = topic.slug"
      >
        {{ topic.name }}
      </button>
    </div>

    <div
      v-if="isLoading"
      class="space-y-4"
    >
      <div
        v-for="idx in 6"
        :key="idx"
        class="h-20 rounded-2xl bg-slate-200 dark:bg-slate-800 animate-pulse"
      />
    </div>

    <div
      v-else-if="error"
      class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-300"
    >
      {{ error }}
    </div>

    <div
      v-else-if="filteredFaqs.length === 0"
      class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
    >
      {{ getEmptyStateMessage('general', 'No FAQs found for this filter.') }}
    </div>

    <div
      v-else
      class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-5"
    >
      <UAccordion
        :items="accordionItems"
        multiple
        :ui="{ wrapper: 'space-y-4' }"
      >
        <template
          v-for="faq in filteredFaqs"
          :key="faq.id"
          #[`faq-${faq.id}`]
        >
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:shadow-sm dark:border-slate-700 dark:bg-slate-800/40 md:p-5">
            <div class="mb-2 flex flex-wrap items-center gap-2">
              <UBadge
                size="sm"
                variant="soft"
                color="primary"
              >
                {{ faq.topic_name }}
              </UBadge>
              <span class="text-xs text-slate-500 dark:text-slate-400">
                {{ faq.views || 0 }} views
              </span>
            </div>

            <p class="whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-300 md:text-base">
              {{ faq.answer }}
            </p>
          </div>
        </template>
      </UAccordion>
    </div>
  </div>
</template>
