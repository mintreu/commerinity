<template>
  <main v-if="props.text" class="w-full h-full mx-auto px-4 space-y-12 tiptap-editor">
    <div class="tiptap-prosemirror-wrapper mx-auto w-full h-full overflow-hidden rounded-b-md">
      <div class="tiptap-content min-h-full">
        <div v-html="props.text"></div>
      </div>
    </div>
  </main>
</template>

<script setup lang="ts">
const props = defineProps<{
  text: string
}>()
</script>

<style scoped>
/* ✅ Base Tiptap content */
.tiptap-content {
  @apply relative w-full flex-1 px-4 py-4 mx-auto text-black dark:text-gray-200 rounded-b-md break-words;
}

/* ✅ Headings */
.tiptap-content :deep(h1) { @apply font-bold text-4xl leading-tight mt-8; }
.tiptap-content :deep(h2) { @apply font-bold text-2xl leading-tight mt-8; }
.tiptap-content :deep(h3) { @apply font-bold text-xl leading-snug mt-8; }
.tiptap-content :deep(h4) { @apply font-bold text-lg mt-8; }
.tiptap-content :deep(h5) { @apply font-bold text-sm mt-8; }
.tiptap-content :deep(h6) { @apply font-bold text-xs mt-8; }

/* ✅ Paragraphs */
.tiptap-content :deep(p) { @apply mb-4; }
.tiptap-content :deep(.lead) { @apply text-xl leading-snug; }
.tiptap-content :deep(small) { @apply text-xs; }
.tiptap-content :deep(sup) { @apply text-[65%]; }

/* ✅ Placeholder */
.tiptap-content :deep(p.is-editor-empty:first-child::before) {
  @apply text-gray-400;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}

/* ✅ Lists */
.tiptap-content :deep(ul) { @apply list-disc ps-4 ms-4 space-y-2; }
.tiptap-content :deep(ol) { @apply list-decimal ps-4 ms-4 space-y-2; }
.tiptap-content :deep(ul.checked-list) { @apply list-none ms-0 ps-0; }
.tiptap-content :deep(ul.checked-list li) { @apply flex items-baseline gap-1.5; }
.tiptap-content :deep(ul.checked-list li::before) {
  content: "✓";
  @apply inline-block w-5 h-5 shrink-0;
}

/* ✅ Blockquote */
.tiptap-content :deep(blockquote) {
  @apply border-l-4 border-gray-400 ps-2 ms-4 text-lg dark:border-gray-500;
}

/* ✅ Horizontal rule */
.tiptap-content :deep(hr) { @apply border-gray-400 dark:border-gray-500; }

/* ✅ Links */
.tiptap-content :deep(a) { @apply text-blue-600 underline; }
.tiptap-content :deep(a[id]) { @apply text-black no-underline; }
.tiptap-content :deep(a[id]::before) {
  content: "# ";
  @apply text-gray-500;
}

/* ✅ Button Links */
.tiptap-content :deep(a[data-as-button="true"]) {
  @apply inline-block rounded-md px-5 py-2 bg-gray-900 text-white no-underline transition-colors;
}
.tiptap-content :deep(a[data-as-button="true"][data-as-button-theme="primary"]) { @apply bg-gray-600; }
.tiptap-content :deep(a[data-as-button="true"][data-as-button-theme="secondary"]) { @apply bg-yellow-600; }
.tiptap-content :deep(a[data-as-button="true"][data-as-button-theme="tertiary"]) { @apply bg-green-600; }
.tiptap-content :deep(a[data-as-button="true"][data-as-button-theme="accent"]) { @apply bg-red-600; }

/* ✅ Code & Pre */
.tiptap-content :deep(code) {
  @apply bg-gray-300 dark:bg-gray-800 rounded px-1;
}
.tiptap-content :deep(pre) {
  @apply p-3 rounded text-sm font-mono bg-gray-100 dark:bg-gray-900 overflow-x-auto;
}
.tiptap-content :deep(pre code) {
  @apply bg-transparent rounded-none px-0;
}

/* ✅ Highlight.js theme */
.tiptap-content :deep(.hljs) { @apply bg-gray-800 text-white p-2 rounded text-sm font-mono; }
.tiptap-content :deep(.hljs-keyword),
.tiptap-content :deep(.hljs-emphasis),
.tiptap-content :deep(.hljs-formula),
.tiptap-content :deep(.hljs-selector-pseudo) { @apply text-purple-400 italic; }
.tiptap-content :deep(.hljs-built_in),
.tiptap-content :deep(.hljs-variable),
.tiptap-content :deep(.hljs-template-variable),
.tiptap-content :deep(.hljs-selector-class),
.tiptap-content :deep(.hljs-addition) { @apply text-green-300 italic; }
.tiptap-content :deep(.hljs-type),
.tiptap-content :deep(.hljs-symbol),
.tiptap-content :deep(.hljs-function),
.tiptap-content :deep(.hljs-meta),
.tiptap-content :deep(.hljs-section) { @apply text-blue-400; }
.tiptap-content :deep(.hljs-literal) { @apply text-pink-400; }
.tiptap-content :deep(.hljs-number) { @apply text-orange-400; }
.tiptap-content :deep(.hljs-regexp) { @apply text-sky-400; }
.tiptap-content :deep(.hljs-string),
.tiptap-content :deep(.hljs-meta .hljs-string) { @apply text-amber-300; }
.tiptap-content :deep(.hljs-subst) { @apply text-red-500; }
.tiptap-content :deep(.hljs-class),
.tiptap-content :deep(.hljs-title) { @apply text-yellow-300; }
.tiptap-content :deep(.hljs-params),
.tiptap-content :deep(.hljs-doctag),
.tiptap-content :deep(.hljs-attr),
.tiptap-content :deep(.hljs-name),
.tiptap-content :deep(.hljs-tag),
.tiptap-content :deep(.hljs-meta .hljs-keyword) { @apply text-teal-300; }
.tiptap-content :deep(.hljs-attribute),
.tiptap-content :deep(.hljs-code) { @apply text-cyan-300; }
.tiptap-content :deep(.hljs-comment),
.tiptap-content :deep(.hljs-quote) { @apply text-gray-500; }
.tiptap-content :deep(.hljs-strong) { @apply font-bold text-green-300; }
.tiptap-content :deep(.hljs-link) { @apply text-pink-400; }
.tiptap-content :deep(.hljs-selector-tag) { @apply text-red-400; }
.tiptap-content :deep(.hljs-selector-id) { @apply text-yellow-400; }
.tiptap-content :deep(.hljs-deletion) { @apply text-red-400 italic; }

/* ✅ Tables */
.tiptap-content :deep(table) { @apply w-full border-collapse relative; }
.tiptap-content :deep(table th) {
  @apply bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-100 font-bold text-left px-2 py-1 border border-gray-400 dark:border-gray-600;
}
.tiptap-content :deep(table td) {
  @apply border border-gray-400 dark:border-gray-600 px-2 py-1 align-top;
}
.tiptap-content :deep(.tableWrapper) { @apply py-4 overflow-x-auto; }

/* ✅ Images */
.tiptap-content :deep(img) { @apply inline-block max-w-full rounded border-2 border-dashed border-transparent; }

/* ✅ Details */
.tiptap-content :deep(div[data-type="details"]) { @apply border border-dashed border-gray-400 rounded relative; }
.tiptap-content :deep(div[data-type="details"] summary) { @apply px-2 py-1 font-bold border-b border-gray-200; }
.tiptap-content :deep(div[data-type="details"] div[data-type="details-content"]) { @apply p-2; }

/* ✅ Grid */
.tiptap-content :deep(.filament-tiptap-grid) { @apply grid gap-4; }
.tiptap-content :deep(.filament-tiptap-grid .filament-tiptap-grid__column) { @apply border border-dashed border-gray-400 p-2 rounded; }

/* ✅ Hurdle */
.tiptap-content :deep(.filament-tiptap-hurdle) { @apply w-full py-4 bg-gray-800 text-white relative; }
.tiptap-content :deep(.filament-tiptap-hurdle[data-color="gray_light"]) { @apply bg-gray-300 text-gray-900; }
.tiptap-content :deep(.filament-tiptap-hurdle[data-color="gray"]) { @apply bg-gray-500 text-white; }
.tiptap-content :deep(.filament-tiptap-hurdle[data-color="gray_dark"]) { @apply bg-gray-800 text-white; }
.tiptap-content :deep(.filament-tiptap-hurdle[data-color="primary"]) { @apply bg-gray-500 text-gray-900; }
.tiptap-content :deep(.filament-tiptap-hurdle[data-color="secondary"]) { @apply bg-yellow-500 text-gray-900; }
.tiptap-content :deep(.filament-tiptap-hurdle[data-color="tertiary"]) { @apply bg-green-500 text-white; }
.tiptap-content :deep(.filament-tiptap-hurdle[data-color="accent"]) { @apply bg-red-500 text-white; }
</style>
