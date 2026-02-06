<script setup lang="ts">
const props = withDefaults(defineProps<{
  content: string
  tag?: string
  class?: string
  ariaLabel?: string
}>(), {
  tag: 'div',
  class: ''
})
</script>

<template>
  <component
    :is="props.tag"
    class="filament-content"
    :class="props.class"
    :aria-label="props.ariaLabel"
    v-html="props.content"
  />
</template>

<style scoped>
.filament-content {
  color: var(--slate-900, #111827);
  font-size: 1rem;
  line-height: 1.75;
  font-weight: 400;
  letter-spacing: -0.01em;
}
.filament-content h1,
.filament-content h2,
.filament-content h3,
.filament-content h4,
.filament-content h5,
.filament-content h6 {
  color: var(--slate-900, #111827);
  font-weight: 600;
  line-height: 1.4;
  margin-top: 1.5em;
  margin-bottom: 0.75em;
}
.filament-content p {
  margin-top: 1em;
  margin-bottom: 1em;
}
.filament-content ol,
.filament-content ul {
  padding-inline-start: 1.5rem;
  margin-top: 1em;
  margin-bottom: 1em;
}
.filament-content blockquote {
  border-inline-start: 0.25rem solid var(--slate-300, #d1d5db);
  padding-inline-start: 1rem;
  color: var(--slate-600, #4b5563);
  font-style: italic;
  margin: 1.25em 0;
}
.filament-content pre {
  background: var(--slate-900, #111827);
  color: #f9fafb;
  border-radius: 0.75rem;
  padding: 1rem;
  overflow: auto;
  font-family: var(--font-mono, ui-monospace, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace);
}
.filament-content code {
  background: var(--slate-100, #f3f4f6);
  padding: 0.15rem 0.35rem;
  border-radius: 0.35rem;
  font-family: var(--font-mono, ui-monospace, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace);
}
.filament-content table {
  width: 100%;
  border-collapse: collapse;
  margin: 1.5em 0;
}
.filament-content thead th {
  border-bottom: 1px solid var(--slate-200, #e5e7eb);
  text-align: left;
  padding: 0.5em;
  font-weight: 600;
}
.filament-content tbody td {
  padding: 0.5em;
  border-bottom: 1px solid var(--slate-200, #e5e7eb);
}
.filament-content a {
  color: var(--primary-500, #a855f7);
  text-decoration: underline;
}
.filament-content img {
  max-width: 100%;
  height: auto;
  display: block;
  margin: 1em auto;
}
.filament-content ::-webkit-scrollbar {
  height: 7px;
  width: 7px;
}
.filament-content ::-webkit-scrollbar-thumb {
  background: var(--slate-400, #9ca3af);
  border-radius: 999px;
}
</style>
