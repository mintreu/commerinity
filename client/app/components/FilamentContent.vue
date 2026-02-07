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
/* Base (light) styles */
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
  background: transparent;
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
  color: var(--slate-900, #111827);
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
  color: var(--slate-900, #111827);
}
.filament-content tbody td {
  padding: 0.5em;
  border-bottom: 1px solid var(--slate-200, #e5e7eb);
  color: var(--slate-700, #374151);
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

/* ---------------------------
   Dark mode overrides (scoped)
   Use :deep to target global .dark class (Tailwind-style)
   and also support prefers-color-scheme for system dark mode.
   --------------------------- */

/* If your app toggles dark mode by adding .dark on <html> or <body> */
:deep(.dark) .filament-content,
:deep(html.dark) .filament-content {
  color: #e6eef8 !important;
}
:deep(.dark) .filament-content h1,
:deep(.dark) .filament-content h2,
:deep(.dark) .filament-content h3,
:deep(.dark) .filament-content h4,
:deep(.dark) .filament-content h5,
:deep(.dark) .filament-content h6 {
  color: #ffffff !important;
}
:deep(.dark) .filament-content p,
:deep(.dark) .filament-content li {
  color: #dbeafe !important;
}
:deep(.dark) .filament-content a {
  color: #c4b5fd !important; /* lighter purple for contrast */
  text-decoration: underline;
}
:deep(.dark) .filament-content blockquote {
  border-inline-start-color: #374151 !important;
  color: #c7d2fe !important;
  background: rgba(255,255,255,0.02);
}
:deep(.dark) .filament-content pre {
  background: #0b1220 !important;
  color: #e6eef8 !important;
}
:deep(.dark) .filament-content code {
  background: #0f1724 !important;
  color: #e6eef8 !important;
}
:deep(.dark) .filament-content thead th {
  border-bottom-color: #374151 !important;
  color: #e6eef8 !important;
}
:deep(.dark) .filament-content tbody td {
  border-bottom-color: #1f2937 !important;
  color: #dbeafe !important;
}
:deep(.dark) .filament-content ::-webkit-scrollbar-thumb {
  background: #4b5563 !important;
}

/* Support system dark mode when no .dark class is present */
@media (prefers-color-scheme: dark) {
  .filament-content {
    color: #e6eef8;
  }
  .filament-content h1,
  .filament-content h2,
  .filament-content h3,
  .filament-content h4,
  .filament-content h5,
  .filament-content h6 {
    color: #ffffff;
  }
  .filament-content p,
  .filament-content li {
    color: #dbeafe;
  }
  .filament-content a {
    color: #c4b5fd;
  }
  .filament-content blockquote {
    border-inline-start-color: #374151;
    color: #c7d2fe;
    background: rgba(255,255,255,0.02);
  }
  .filament-content pre {
    background: #0b1220;
    color: #e6eef8;
  }
  .filament-content code {
    background: #0f1724;
    color: #e6eef8;
  }
  .filament-content thead th {
    border-bottom-color: #374151;
    color: #e6eef8;
  }
  .filament-content tbody td {
    border-bottom-color: #1f2937;
    color: #dbeafe;
  }
  .filament-content ::-webkit-scrollbar-thumb {
    background: #4b5563;
  }
}

/* Extra specificity for injected HTML (v-html) when other utilities override colors */
.filament-content,
.filament-content * {
  transition: color 0.12s ease, background-color 0.12s ease;
}

/* Utility: ensure links inside headings remain readable */
.filament-content h1 a,
.filament-content h2 a,
.filament-content h3 a {
  color: inherit;
  text-decoration: underline;
}
</style>