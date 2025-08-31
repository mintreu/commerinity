<script setup lang="ts">
import { ref, computed, getCurrentInstance } from "vue";

const props = defineProps({
  record: Object,
  data: Object,
  onsubmit: Function,
});

const emit = defineEmits(["submit"]);

const instance = getCurrentInstance();
const currentStepIndex = ref(0);

const steps = computed(() => {
  const defaultSlots = instance?.slots.default?.() || [];
  return defaultSlots.filter((vnode) => vnode.type?.name === "WizardStep");
});

const currentStep = computed(() => steps.value[currentStepIndex.value] ?? null);

function getRelationData(stepProps) {
  if (!stepProps?.relation) return null;
  return props.record?.[stepProps.relation] ?? null;
}

async function next() {
  if (!currentStep.value) return;

  const compInstance = currentStep.value.component;

  if (compInstance?.proxy?.validate) {
    const valid = await compInstance.proxy.validate();
    if (!valid) return;
  }

  if (currentStepIndex.value < steps.value.length - 1) {
    currentStepIndex.value++;
  }
}

function prev() {
  if (currentStepIndex.value > 0) {
    currentStepIndex.value--;
  }
}

async function submit() {
  if (props.onsubmit) {
    await props.onsubmit();
  }
  emit("submit");
}
</script>

<template>
  <div class="flex flex-col max-w-3xl mx-auto">
    <!-- Step indicators -->
    <div class="flex space-x-4 mb-6 justify-center">
      <template v-for="(step, index) in steps" :key="index">
        <div
            :class="[
            'flex items-center space-x-2 cursor-pointer select-none',
            currentStepIndex === index
              ? 'text-blue-600 font-semibold'
              : 'text-gray-400'
          ]"
            @click="currentStepIndex = index"
        >
          <span class="material-icons">{{ step.props.icon || "circle" }}</span>
          <span>{{ step.props.name }}</span>
        </div>
      </template>
    </div>

    <!-- Step content -->
    <div class="border rounded p-6 shadow-md min-h-[300px]">
      <component
          v-if="currentStep"
          :is="currentStep.type"
          v-bind="currentStep.props"
          :record="props.record"
          :data="props.data"
          :relationData="getRelationData(currentStep.props)"
      />
    </div>

    <!-- Navigation buttons -->
    <div class="flex justify-between mt-6">
      <button
          class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50"
          :disabled="currentStepIndex === 0"
          @click="prev"
      >
        Previous
      </button>

      <button
          v-if="currentStepIndex < steps.length - 1"
          class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700"
          @click="next"
      >
        Next
      </button>

      <button
          v-else
          class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700"
          @click="submit"
      >
        Submit
      </button>
    </div>
  </div>
</template>

<style scoped>
.material-icons {
  font-family: 'Material Icons';
  font-style: normal;
  font-weight: normal;
  line-height: 1;
  letter-spacing: normal;
  text-transform: none;
  display: inline-block;
  white-space: nowrap;
  direction: ltr;
  -webkit-font-feature-settings: 'liga';
  -webkit-font-smoothing: antialiased;
}
</style>
