<script setup lang="ts">
const config = useRuntimeConfig()

useSeoMeta({
  title: `${config.public.companyName} Help Center`,
  description: `Public help center and general FAQs for ${config.public.companyName}.`
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-violet-50/40 to-fuchsia-50/30 dark:from-slate-950 dark:via-slate-950 dark:to-slate-900">
    <section class="mx-auto max-w-6xl px-4 py-10 md:py-14 space-y-8">
      <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <NuxtLink
          to="/contact"
          class="rounded-2xl border border-slate-200 bg-white p-4 text-center text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
        >
          Contact
        </NuxtLink>
        <NuxtLink
          to="/shipping"
          class="rounded-2xl border border-slate-200 bg-white p-4 text-center text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
        >
          Shipping
        </NuxtLink>
        <NuxtLink
          to="/return-refund"
          class="rounded-2xl border border-slate-200 bg-white p-4 text-center text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
        >
          Returns
        </NuxtLink>
        <NuxtLink
          to="/privacy"
          class="rounded-2xl border border-slate-200 bg-white p-4 text-center text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
        >
          Privacy
        </NuxtLink>
      </div>

      <FaqKnowledgeBase
        context="general"
        title="Help Center FAQ"
        subtitle="General frequently asked questions for guests and shoppers."
      />
    </section>
  </div>
</template>
