<script setup lang="ts">
const props = defineProps<{
  text: string,
}>()
</script>

<template>
  <main v-if="props.text" class="w-full h-full mx-auto px-4 space-y-12 tiptap-editor">

    <div v-if="props.text"
         class="tiptap-prosemirror-wrapper mx-auto w-full h-full overflow-hidden rounded-b-md prosemirror-w-5xl">
      <div class="tiptap-content min-h-full">
        <div v-html="props.text"></div>
      </div>
    </div>


  </main>
</template>

<style scoped>

</style>