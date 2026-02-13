<script setup lang="ts">
definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

useSeoMeta({
  title: 'Dashboard FAQ',
  description: 'Personalized FAQ for your current dashboard role.'
})
</script>

<template>
  <div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-900/80 md:p-6">
      <FaqKnowledgeBase
        context="dashboard"
        title="Personalized FAQ"
        subtitle="Answers tailored for your current dashboard role."
      />
    </div>
  </div>
</template>
