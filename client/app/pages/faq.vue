<script setup lang="ts">
const config = useRuntimeConfig()

useSeoMeta({
  title: `${config.public.companyName} FAQ`,
  description: `General frequently asked questions for guests and shoppers on ${config.public.companyName}.`
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-violet-50/40 to-fuchsia-50/30 dark:from-slate-950 dark:via-slate-950 dark:to-slate-900">
    <section class="mx-auto max-w-6xl px-4 py-10 md:py-14">
      <FaqKnowledgeBase
        context="general"
        title="FAQ for Everyone"
        subtitle="Public and footer FAQ items for visitors and guests."
      />
    </section>
  </div>
</template>
