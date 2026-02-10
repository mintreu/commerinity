<script setup lang="ts">
const config = useRuntimeConfig()

useSeoMeta({
  title: `${config.public.companyName} News`,
  description: `Announcements and updates from ${config.public.companyName}.`
})
</script>

<template>
  <ContentList
    type="news"
    hero-label="Updates"
    title="Latest news and announcements"
    subtitle="Company updates, partnerships, and industry highlights."
  />
</template>
