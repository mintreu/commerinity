<template>
  <form class="space-y-6" @submit.prevent="emit('next')">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="col-span-full">
        <label class="form-label">Full Name</label>
        <input v-model="model.name" type="text" class="form-input" placeholder="Your name" />
      </div>

      <div>
        <label class="form-label">Mobile</label>
        <input v-model="model.mobile" type="number" class="form-input" placeholder="Your mobile" />
      </div>
      <div>
        <label class="form-label">Email</label>
        <input v-model="model.email" type="email" class="form-input" placeholder="Your email" />
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="form-label">Date of Birth</label>
        <input v-model="model.dob" type="date" class="form-input" />
      </div>
      <div>
        <label class="form-label">Gender</label>
        <select v-model="model.gender" class="form-input">
          <option disabled value="">Select Gender</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
          <option value="other">Other</option>
        </select>
      </div>
    </div>

    <div class="flex justify-between pt-4">
      <button
          type="button"
          class="btn-secondary"
          @click="emit('back')"
      >
        Back
      </button>
      <button type="submit" class="btn-primary">
        Continue
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
const model = defineModel()
const emit = defineEmits(['next', 'back'])
</script>

<style scoped>
.form-label {
  @apply block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300;
}
.form-input {
  @apply w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition;
}
.btn-primary {
  @apply bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition font-medium;
}
.btn-secondary {
  @apply bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white px-4 py-2 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition font-medium;
}
</style>
