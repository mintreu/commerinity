<script setup lang="ts">
const config = useRuntimeConfig()

useHead({
  title: `${config.public.companyName} Blog & Knowledge Base`,
  meta: [
    {
      name: 'description',
      content: `Insights, guides, and featured stories from ${config.public.companyName}.`
    }
  ]
})

const topicPillars = [
  'Affiliate marketing',
  'New launches',
  'Tips & tricks',
  'Behind the scenes',
  'Community stories',
  'E-commerce growth'
]

const teaserPosts = [
  {
    title: 'Maximizing affiliate commissions',
    summary: 'A playbook for partners to boost conversions using high-CTR creatives and workflows.',
    readingTime: '4 min read'
  },
  {
    title: 'How we vet premium sellers',
    summary: 'Peek behind our quality assurance checklist that keeps every catalogue product trustworthy.',
    readingTime: '3 min read'
  },
  {
    title: 'Preparing for festival sales',
    summary: 'Engineering demand with bundling, new arrivals, and hyper-personalized campaigns.',
    readingTime: '5 min read'
  }
]
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <section class="bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 text-white py-16">
      <div class="mx-auto max-w-5xl px-6 text-center space-y-4">
        <p class="text-sm uppercase tracking-[0.3em] text-violet-200">Insights</p>
        <h1 class="text-4xl md:text-5xl font-black leading-tight">
          Stories from the VVIndia universe
        </h1>
        <p class="text-lg text-violet-100 max-w-3xl mx-auto">
          Explore practical advice, product spotlights, and community highlights curated for shoppers,
          partners, and modern sellers.
        </p>
        <div class="flex flex-wrap justify-center gap-3 pt-4">
          <span
            v-for="topic in topicPillars"
            :key="topic"
            class="px-4 py-2 bg-white/20 backdrop-blur rounded-full text-sm font-semibold"
          >
            {{ topic }}
          </span>
        </div>
      </div>
    </section>

    <section class="mx-auto max-w-5xl px-6 py-16 space-y-10">
      <header class="space-y-2">
        <p class="text-sm uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">Latest</p>
        <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Need-to-know stories</h2>
        <p class="text-slate-600 dark:text-slate-300 max-w-2xl">
          This channel is still being built. Bookmark it and come back for new briefs, deep dives,
          and featured voices from the brand.
        </p>
      </header>

      <div class="grid gap-6 md:grid-cols-3">
        <article
          v-for="post in teaserPosts"
          :key="post.title"
          class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-lg shadow-violet-500/5 transition hover:-translate-y-1 hover:shadow-xl"
        >
          <p class="text-xs uppercase tracking-[0.3em] text-violet-500">{{ post.readingTime }}</p>
          <h3 class="text-xl font-semibold text-slate-900 dark:text-white mt-3 mb-2">{{ post.title }}</h3>
          <p class="text-slate-600 dark:text-slate-400">{{ post.summary }}</p>
        </article>
      </div>

      <div class="rounded-3xl bg-white dark:bg-slate-900 border border-violet-200 dark:border-violet-800 p-8 text-center space-y-4 shadow-xl">
        <p class="text-sm uppercase tracking-[0.5em] text-violet-500">Subscribe</p>
        <h3 class="text-2xl font-bold text-slate-900 dark:text-white">
          Receive every new post directly in your inbox.
        </h3>
        <p class="text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
          We are still rounding up the editorial calendar. Leave your email and we will notify you when
          the next guide lands.
        </p>
        <button
          type="button"
          class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-violet-600 text-white font-semibold shadow-lg shadow-violet-500/40 hover:bg-violet-700 transition"
        >
          Join the list
        </button>
      </div>
    </section>
  </div>
</template>
