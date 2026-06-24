<script setup lang="ts">
const config = useRuntimeConfig()

useSeoMeta({
  title: `${config.public.companyName} Blog`,
  description: `Insights, guides, and featured stories from ${config.public.companyName}.`
})
</script>

<template>
  <ContentList
    type="blog"
    hero-label="Insights"
    title="Stories from the VVIndia universe"
    subtitle="Practical advice, product spotlights, and community highlights curated for shoppers and partners."
  />
</template>
