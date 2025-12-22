<template>
  <BlogList
      title="News & Updates"
      category="company"
      basePath="/news"
      breadcrumbLabel="News"
  />
</template>

<script setup>
import BlogList from '~/components/blog/BlogList.vue'

useHead({
  title: 'News & Updates - Company News',
  meta: [
    { name: 'description', content: 'Stay updated with our latest company news, updates, and announcements.' }
  ]
})
</script>
