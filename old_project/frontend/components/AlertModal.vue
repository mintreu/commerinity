<template>
  <transition name="fade">
    <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
      <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-md w-full p-6">
        <div class="text-center space-y-2">
          <h2 class="text-lg font-semibold text-gray-800 dark:text-white">{{ title }}</h2>
          <p class="text-sm text-gray-600 dark:text-gray-300">{{ message }}</p>
          <button
              @click="close"
              class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            OK
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
const props = defineProps({
  title: { type: String, default: 'Alert' },
  message: { type: String, required: true },
  modelValue: { type: Boolean, required: true },
});

const emit = defineEmits(['update:modelValue']);

const visible = computed(() => props.modelValue);
const close = () => emit('update:modelValue', false);
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
