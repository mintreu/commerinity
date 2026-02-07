<script setup lang="ts">
const config = useRuntimeConfig()

useHead({
  title: `${config.public.companyName} News & Updates`,
  meta: [
    {
      name: 'description',
      content: `Announcements, product releases, and community highlights from ${config.public.companyName}.`
    }
  ]
})

const highlights = [
  {
    title: 'New seller dashboard updates',
    date: 'Feb 8, 2026',
    summary: 'Sneak peek at the simplified seller onboarding and analytics refresh.'
  },
  {
    title: 'Mobile app release date confirmed',
    date: 'Jan 27, 2026',
    summary: 'VVIndia mobile gets offline catalog browsing, order tracking, and wallet syncing.'
  },
  {
    
    title: 'Community meetup in Bengaluru',
    date: 'Jan 15, 2026',
    summary: 'An invite-only gathering for top affiliates to network with category managers.'
  }
]
</script>

<template>
  <div class="min-h-screen bg-white dark:bg-slate-950">
    <section class="bg-slate-900 text-white py-16">
      <div class="mx-auto max-w-5xl px-6 text-center space-y-4">
        <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Updates</p>
        <h1 class="text-4xl md:text-5xl font-black leading-tight">
          News from the VVIndia family
        </h1>
        <p class="text-lg text-slate-200 max-w-3xl mx-auto">
          Follow the latest product features, trust & safety policies, and community milestones.
        </p>
      </div>
    </section>

    <section class="mx-auto max-w-5xl px-6 py-16 space-y-10">
      <header class="space-y-2">
        <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Current happenings</h2>
        <p class="text-slate-600 dark:text-slate-300 max-w-2xl">
          News is still being journaled; this is a placeholder while we replicate the full CMS feed.
        </p>
      </header>

      <div class="space-y-6">
        <article
          v-for="item in highlights"
          :key="item.title"
          class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 p-7 shadow-sm dark:shadow-none"
        >
          <div class="flex items-center justify-between text-sm uppercase tracking-[0.4em] text-slate-500 dark:text-slate-400">
            <span>{{ item.date }}</span>
            <span>News</span>
          </div>
          <h3 class="text-2xl font-semibold text-slate-900 dark:text-white mt-4">{{ item.title }}</h3>
          <p class="text-slate-600 dark:text-slate-300 mt-3">{{ item.summary }}</p>
        </article>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <div class="p-6 rounded-3xl border border-dashed border-violet-400 dark:border-violet-600 bg-white dark:bg-slate-900 shadow-lg">
          <h3 class="text-xl font-bold text-slate-900 dark:text-white">Need media assets?</h3>
          <p class="text-sm text-slate-600 dark:text-slate-300 mt-2">
            Email the growth team for press kits, logos, and quotes. We'll respond within one business day.
          </p>
        </div>
        <div class="p-6 rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-lg">
          <h3 class="text-xl font-bold text-slate-900 dark:text-white">Want to share your story?</h3>
          <p class="text-sm text-slate-600 dark:text-slate-300 mt-2">
            We are still wiring the submission form. Until then, send your story to
            <a href="mailto:support@vvindia.in" class="text-violet-600 dark:text-violet-400 font-semibold">support@vvindia.in</a>.
          </p>
        </div>
      </div>
    </section>
  </div>
</template>
