<template>
  <BlogList
      title="Blog And KnowledgeBase"
      category="blog"
      basePath="/blogs"
      breadcrumbLabel="Blog"
  />
</template>

<script setup>
import BlogList from '~/components/blog/BlogList.vue'

useHead({
  title: 'Blog And KnowledgeBase',
  meta: [
    { name: 'description', content: 'Explore our comprehensive blog and knowledge base with articles, guides, and tutorials.' }
  ]
})
</script>
