<script setup lang="ts">
import { defineProps, ref } from "vue";

const props = defineProps({
  name: String,
  icon: String,
  component: [Object, Function],
  relation: String,
  record: Object,
  data: Object,
  relationData: Object,
});

const stepComponentRef = ref(null);

// Parent Wizard calls this to validate the step
function validate() {
  if (
      stepComponentRef.value &&
      typeof stepComponentRef.value.validate === "function"
  ) {
    return stepComponentRef.value.validate();
  }
  return true;
}

defineExpose({ validate });
</script>

<template>
  <div>
    <component
        ref="stepComponentRef"
        :is="component"
        :record="record"
        :data="data"
        :relationData="relationData"
    />
  </div>
</template>
