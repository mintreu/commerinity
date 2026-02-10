<script setup lang="ts">
/**
 * RichContent Component
 *
 * Renders HTML content from Filament v4 RichEditor with proper styling.
 * Compatible with Filament's TipTap-based editor output.
 *
 * Usage:
 *   <RichContent :content="description" />
 *   <RichContent :content="body" class="max-w-3xl" />
 */

interface Props {
  content: string | null | undefined
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  content: '',
  class: ''
})

const hasContent = computed(() => {
  if (!props.content) return false
  const stripped = props.content.replace(/<[^>]*>/g, '').trim()
  return stripped.length > 0
})
</script>

<template>
  <div
    v-if="hasContent"
    class="rich-content"
    :class="props.class"
    v-html="content"
  />
</template>

<style>
/**
 * Rich Content Styles
 *
 * Matches Filament v4 RichEditor output styling.
 * Based on Tailwind Typography (prose) + Filament's fi-prose styles.
 * Compatible with TipTap editor HTML output.
 */

.rich-content {
  @apply relative w-full text-slate-700 dark:text-slate-300 leading-relaxed;
}

/* ========================================
   Typography - Headings
   ======================================== */

.rich-content h1 {
  @apply text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white mt-10 mb-4 leading-tight;
}

.rich-content h2 {
  @apply text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white mt-8 mb-4 leading-tight;
}

.rich-content h3 {
  @apply text-xl sm:text-2xl font-semibold text-slate-900 dark:text-white mt-6 mb-3 leading-snug;
}

.rich-content h4 {
  @apply text-lg sm:text-xl font-semibold text-slate-900 dark:text-white mt-6 mb-3;
}

.rich-content h5 {
  @apply text-base sm:text-lg font-semibold text-slate-900 dark:text-white mt-4 mb-2;
}

.rich-content h6 {
  @apply text-sm sm:text-base font-semibold text-slate-800 dark:text-slate-200 mt-4 mb-2;
}

/* Remove margin from first heading */
.rich-content > h1:first-child,
.rich-content > h2:first-child,
.rich-content > h3:first-child,
.rich-content > h4:first-child,
.rich-content > h5:first-child,
.rich-content > h6:first-child {
  @apply mt-0;
}

/* ========================================
   Typography - Paragraphs & Text
   ======================================== */

.rich-content p {
  @apply mb-4 leading-relaxed;
}

.rich-content p:last-child {
  @apply mb-0;
}

.rich-content .lead,
.rich-content p.lead {
  @apply text-lg sm:text-xl text-slate-600 dark:text-slate-400 leading-relaxed;
}

.rich-content strong,
.rich-content b {
  @apply font-semibold text-slate-900 dark:text-white;
}

.rich-content em,
.rich-content i {
  @apply italic;
}

.rich-content u {
  @apply underline decoration-2 underline-offset-2;
}

.rich-content s,
.rich-content strike,
.rich-content del {
  @apply line-through text-slate-500 dark:text-slate-500;
}

.rich-content small {
  @apply text-sm;
}

.rich-content sup {
  @apply text-xs align-super;
}

.rich-content sub {
  @apply text-xs align-sub;
}

.rich-content mark {
  @apply bg-yellow-200 dark:bg-yellow-800 text-slate-900 dark:text-white px-1 rounded;
}

/* ========================================
   Links
   ======================================== */

.rich-content a {
  @apply text-violet-600 dark:text-violet-400 underline decoration-violet-400/50 dark:decoration-violet-500/50 underline-offset-2 transition-colors;
}

.rich-content a:hover {
  @apply text-violet-700 dark:text-violet-300 decoration-violet-600 dark:decoration-violet-400;
}

/* Anchor links (id-based) */
.rich-content a[id] {
  @apply text-slate-900 dark:text-white no-underline;
}

.rich-content a[id]::before {
  content: "# ";
  @apply text-slate-400 dark:text-slate-500;
}

/* Button-style links (Filament TipTap feature) */
.rich-content a[data-as-button="true"] {
  @apply inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-semibold rounded-lg no-underline transition-all hover:opacity-90;
}

.rich-content a[data-as-button="true"][data-as-button-theme="primary"] {
  @apply bg-violet-600 dark:bg-violet-500 text-white;
}

.rich-content a[data-as-button="true"][data-as-button-theme="secondary"] {
  @apply bg-amber-500 dark:bg-amber-400 text-white dark:text-slate-900;
}

.rich-content a[data-as-button="true"][data-as-button-theme="tertiary"] {
  @apply bg-emerald-500 dark:bg-emerald-400 text-white;
}

.rich-content a[data-as-button="true"][data-as-button-theme="accent"] {
  @apply bg-rose-500 dark:bg-rose-400 text-white;
}

/* ========================================
   Lists
   ======================================== */

.rich-content ul {
  @apply list-disc pl-6 mb-4 space-y-2;
}

.rich-content ol {
  @apply list-decimal pl-6 mb-4 space-y-2;
}

.rich-content li {
  @apply text-slate-700 dark:text-slate-300;
}

.rich-content li p {
  @apply mb-2;
}

.rich-content li:last-child p:last-child {
  @apply mb-0;
}

/* Nested lists */
.rich-content ul ul,
.rich-content ul ol,
.rich-content ol ul,
.rich-content ol ol {
  @apply mt-2 mb-0;
}

/* Checked list (Filament TipTap feature) */
.rich-content ul.checked-list,
.rich-content ul[data-type="taskList"] {
  @apply list-none pl-0 space-y-2;
}

.rich-content ul.checked-list li,
.rich-content ul[data-type="taskList"] li {
  @apply flex items-start gap-3;
}

.rich-content ul.checked-list li::before {
  content: "";
  @apply w-5 h-5 shrink-0 mt-0.5 rounded bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2310b981' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: center;
}

/* Task list items with checkboxes */
.rich-content li[data-type="taskItem"] {
  @apply flex items-start gap-3;
}

.rich-content li[data-type="taskItem"] > label {
  @apply flex items-center;
}

.rich-content li[data-type="taskItem"] input[type="checkbox"] {
  @apply w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-violet-600 focus:ring-violet-500;
}

/* ========================================
   Blockquotes
   ======================================== */

.rich-content blockquote {
  @apply border-l-4 border-violet-400 dark:border-violet-600 pl-4 py-1 my-6 text-slate-600 dark:text-slate-400 italic bg-slate-50 dark:bg-slate-800/50 rounded-r-lg;
}

.rich-content blockquote p {
  @apply mb-0;
}

.rich-content blockquote cite {
  @apply block mt-2 text-sm not-italic text-slate-500 dark:text-slate-500;
}

/* ========================================
   Horizontal Rules
   ======================================== */

.rich-content hr {
  @apply my-8 border-t-2 border-slate-200 dark:border-slate-700;
}

/* ========================================
   Code & Pre (Syntax Highlighting)
   ======================================== */

.rich-content code {
  @apply px-1.5 py-0.5 text-sm font-mono bg-slate-100 dark:bg-slate-800 text-rose-600 dark:text-rose-400 rounded;
}

.rich-content pre {
  @apply p-4 my-4 overflow-x-auto text-sm font-mono bg-slate-900 dark:bg-slate-950 text-slate-100 rounded-xl border border-slate-800;
}

.rich-content pre code {
  @apply p-0 bg-transparent text-inherit rounded-none;
}

/* Highlight.js Theme (GitHub Dark) */
.rich-content .hljs { @apply bg-slate-900 text-slate-100; }
.rich-content .hljs-keyword,
.rich-content .hljs-selector-tag { @apply text-purple-400; }
.rich-content .hljs-built_in,
.rich-content .hljs-type { @apply text-cyan-400; }
.rich-content .hljs-literal,
.rich-content .hljs-number { @apply text-orange-400; }
.rich-content .hljs-string,
.rich-content .hljs-attr { @apply text-emerald-400; }
.rich-content .hljs-symbol,
.rich-content .hljs-bullet { @apply text-emerald-400; }
.rich-content .hljs-title,
.rich-content .hljs-section { @apply text-blue-400 font-bold; }
.rich-content .hljs-function { @apply text-violet-400; }
.rich-content .hljs-variable { @apply text-rose-400; }
.rich-content .hljs-params { @apply text-orange-300; }
.rich-content .hljs-comment,
.rich-content .hljs-quote { @apply text-slate-500 italic; }
.rich-content .hljs-meta { @apply text-slate-400; }
.rich-content .hljs-deletion { @apply text-rose-400 bg-rose-900/30; }
.rich-content .hljs-addition { @apply text-emerald-400 bg-emerald-900/30; }
.rich-content .hljs-emphasis { @apply italic; }
.rich-content .hljs-strong { @apply font-bold; }

/* ========================================
   Tables
   ======================================== */

.rich-content table {
  @apply w-full border-collapse my-6 text-sm;
}

.rich-content thead {
  @apply bg-slate-100 dark:bg-slate-800;
}

.rich-content th {
  @apply px-4 py-3 text-left font-semibold text-slate-900 dark:text-white border border-slate-200 dark:border-slate-700;
}

.rich-content td {
  @apply px-4 py-3 border border-slate-200 dark:border-slate-700 align-top;
}

.rich-content tbody tr {
  @apply transition-colors;
}

.rich-content tbody tr:hover {
  @apply bg-slate-50 dark:bg-slate-800/50;
}

.rich-content .tableWrapper {
  @apply overflow-x-auto my-4;
}

/* ========================================
   Images & Media
   ======================================== */

.rich-content img {
  @apply max-w-full h-auto rounded-xl my-6 shadow-lg;
}

.rich-content figure {
  @apply my-6;
}

.rich-content figure img {
  @apply my-0;
}

.rich-content figcaption {
  @apply mt-3 text-sm text-center text-slate-500 dark:text-slate-400 italic;
}

/* Video embeds */
.rich-content iframe,
.rich-content video {
  @apply w-full aspect-video rounded-xl my-6 shadow-lg;
}

/* ========================================
   Details/Accordion (Filament TipTap)
   ======================================== */

.rich-content div[data-type="details"],
.rich-content details {
  @apply my-4 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden;
}

.rich-content div[data-type="details"] summary,
.rich-content details summary {
  @apply px-4 py-3 font-semibold bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white cursor-pointer select-none transition-colors hover:bg-slate-100 dark:hover:bg-slate-700;
}

.rich-content div[data-type="details"] div[data-type="details-content"],
.rich-content details > *:not(summary) {
  @apply p-4;
}

/* ========================================
   Grid Layout (Filament TipTap)
   ======================================== */

.rich-content .filament-tiptap-grid {
  @apply grid gap-4 my-6;
}

.rich-content .filament-tiptap-grid[data-cols="2"] {
  @apply grid-cols-1 sm:grid-cols-2;
}

.rich-content .filament-tiptap-grid[data-cols="3"] {
  @apply grid-cols-1 sm:grid-cols-2 lg:grid-cols-3;
}

.rich-content .filament-tiptap-grid[data-cols="4"] {
  @apply grid-cols-1 sm:grid-cols-2 lg:grid-cols-4;
}

.rich-content .filament-tiptap-grid__column {
  @apply p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700;
}

/* ========================================
   Hurdle/Banner (Filament TipTap)
   ======================================== */

.rich-content .filament-tiptap-hurdle {
  @apply w-full py-6 px-6 my-6 rounded-xl text-center;
}

.rich-content .filament-tiptap-hurdle[data-color="gray_light"] {
  @apply bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white;
}

.rich-content .filament-tiptap-hurdle[data-color="gray"] {
  @apply bg-slate-500 text-white;
}

.rich-content .filament-tiptap-hurdle[data-color="gray_dark"] {
  @apply bg-slate-800 dark:bg-slate-950 text-white;
}

.rich-content .filament-tiptap-hurdle[data-color="primary"] {
  @apply bg-violet-600 text-white;
}

.rich-content .filament-tiptap-hurdle[data-color="secondary"] {
  @apply bg-amber-500 text-white;
}

.rich-content .filament-tiptap-hurdle[data-color="tertiary"] {
  @apply bg-emerald-500 text-white;
}

.rich-content .filament-tiptap-hurdle[data-color="accent"] {
  @apply bg-rose-500 text-white;
}

/* ========================================
   Text Alignment
   ======================================== */

.rich-content [style*="text-align: center"],
.rich-content .text-center {
  @apply text-center;
}

.rich-content [style*="text-align: right"],
.rich-content .text-right {
  @apply text-right;
}

.rich-content [style*="text-align: justify"],
.rich-content .text-justify {
  @apply text-justify;
}

/* ========================================
   Text Colors (Filament RichEditor)
   ======================================== */

.rich-content [style*="color: rgb(239, 68, 68)"],
.rich-content .text-red { color: #ef4444; }

.rich-content [style*="color: rgb(249, 115, 22)"],
.rich-content .text-orange { color: #f97316; }

.rich-content [style*="color: rgb(234, 179, 8)"],
.rich-content .text-yellow { color: #eab308; }

.rich-content [style*="color: rgb(34, 197, 94)"],
.rich-content .text-green { color: #22c55e; }

.rich-content [style*="color: rgb(59, 130, 246)"],
.rich-content .text-blue { color: #3b82f6; }

.rich-content [style*="color: rgb(139, 92, 246)"],
.rich-content .text-violet { color: var(--theme-primary-500); }

.rich-content [style*="color: rgb(236, 72, 153)"],
.rich-content .text-pink { color: var(--theme-accent-600); }

/* ========================================
   Placeholder (Editor empty state)
   ======================================== */

.rich-content p.is-editor-empty:first-child::before,
.rich-content [data-placeholder]::before {
  @apply text-slate-400 dark:text-slate-500;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}

/* ========================================
   Print Styles
   ======================================== */

@media print {
  .rich-content {
    @apply text-black;
  }

  .rich-content a {
    @apply text-black underline;
  }

  .rich-content pre,
  .rich-content code {
    @apply bg-gray-100 border border-gray-300;
  }
}
</style>
