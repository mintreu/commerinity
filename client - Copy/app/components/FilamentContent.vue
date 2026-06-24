<script setup lang="ts">
import { contentToHtml } from '~/utils/tiptapToHtml'

const props = withDefaults(defineProps<{
  content: string
  tag?: string
  class?: string
  ariaLabel?: string
}>(), {
  tag: 'div',
  class: ''
})

const renderedHtml = computed(() => contentToHtml(props.content))
</script>

<template>
  <component
    :is="props.tag"
    class="filament-content"
    :class="props.class"
    :aria-label="props.ariaLabel"
    v-html="renderedHtml"
  />
</template>

<style scoped>

</style>
