<template>
  <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-8">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">My Account</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Manage your personal profile and secure your account.
        </p>
      </div>
    </div>


    <!-- Loader -->
    <GlobalLoader v-if="isLoading" />


    <div v-else>
      <!-- Avatar Uploader -->
      <AvatarUploader />

      <!-- Basic Info -->
      <form @submit.prevent="submitBasicInfo" class="bg-white dark:bg-gray-800 shadow rounded-xl">
        <div class="grid grid-cols-1 md:grid-cols-[250px_1fr] divide-y md:divide-y-0 md:divide-x divide-gray-200 dark:divide-gray-700">

          <!-- Title & Description -->
          <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Basic Information</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
              Update your name, gender, and date of birth.
            </p>
          </div>

          <!-- Form Fields -->
          <div class="p-6 space-y-6 flex flex-col">
            <div class="flex flex-col gap-4">
              <div class="gap-2">
                <label>Name</label>
                <input v-model="form.name" type="text" class="form-input" required minlength="3" placeholder="Your full name" />
                <span v-if="form.errors.name" class="form-error">{{ form.errors.name }}</span>
              </div>

              <div class="gap-2">
                <label>Gender</label>
                <select v-model="form.gender" class="form-input" required>
                  <option value="">Select gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
                <span v-if="form.errors.gender" class="form-error">{{ form.errors.gender }}</span>
              </div>

              <div class="gap-2">
                <label>Date of Birth</label>
                <input v-model="form.dob" type="date" class="form-input" required />
                <span v-if="form.errors.dob" class="form-error">{{ form.errors.dob }}</span>
              </div>
            </div>

            <div class="flex flex-row">
              <div class="grow hidden md:block"></div>
              <button type="submit" class="btn-primary" :disabled="form.processing">Update Info</button>
            </div>
          </div>
        </div>
      </form>

      <!-- Bio Section -->
      <form @submit.prevent="submitBio" class="bg-white dark:bg-gray-800 shadow rounded-xl">
        <div class="grid grid-cols-1 md:grid-cols-[250px_1fr] divide-y md:divide-y-0 md:divide-x divide-gray-200 dark:divide-gray-700">

          <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Bio</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
              Write something about yourself for your profile.
            </p>
          </div>

          <div class="p-4 space-y-6">
            <div class="flex flex-col gap-4">
              <div class="gap-2">
                <label>Bio</label>
                <textarea
                    ref="bioRef"
                    v-model="form.bio"
                    class="form-input overflow-hidden"
                    rows="2"
                    maxlength="500"
                    placeholder="Write something about yourself"
                    @input="resizeBio"
                ></textarea>
                <span v-if="form.errors.bio" class="form-error">{{ form.errors.bio }}</span>
              </div>
            </div>

            <div class="flex flex-row">
              <div class="grow hidden md:block"></div>
              <button type="submit" class="btn-primary" :disabled="form.processing">Save Bio</button>
            </div>
          </div>
        </div>
      </form>

      <!-- Separate Components -->
      <ChangeMobile />
      <ChangeEmail />
      <ChangePassword />
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useSanctumForm, useRuntimeConfig, useSanctumFetch, useSanctum, useToast } from '#imports'
import GlobalLoader from "~/components/GlobalLoader.vue"

import AvatarUploader from "~/components/account/AvatarUploader.vue";
import ChangePassword from "~/components/account/ChangePassword.vue";
import ChangeMobile from "~/components/account/ChangeMobile.vue";
import ChangeEmail from "~/components/account/ChangeEmail.vue";

const config = useRuntimeConfig()
const { refreshUser } = useSanctum()
const toast = useToast()

const isLoading = useState('pageLoading', () => false)

// Form for profile (basic info + bio)
const form = useSanctumForm('PUT', `${config.public.apiBase}/account/profile`, {
  name: '',
  bio: '',
  gender: '',
  dob: '',
})

// Bio auto-resize
const bioRef = ref<HTMLTextAreaElement | null>(null)
function resizeBio() {
  if (bioRef.value) {
    bioRef.value.style.height = 'auto'
    bioRef.value.style.height = bioRef.value.scrollHeight + 'px'
  }
}

// Load profile data from API
async function loadProfile() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/account/profile`)
    const data = res.data
    form.name = data.name ?? ''
    form.gender = data.gender ?? ''
    form.dob = data.dob ?? ''
    form.bio = data.bio ?? ''
  } catch (err: any) {
    toast.error({ title: 'Error', message: 'Failed to load profile' })
  }
}

onMounted(() => {
  loadProfile()
  resizeBio()
  isLoading.value = false
})

// Watch bio for auto-resize
watch(() => form.bio, resizeBio)

// Submit functions with toast
async function submitBasicInfo() {
  clearFormErrors()
  try {
    await form.submit({ only: ['name', 'gender', 'dob'] })
    toast.success({ title: 'Success', message: 'Profile info updated successfully' })
    refreshUser()
  } catch (err: any) {
    toast.error({ title: 'Error', message: err?.data?.message || 'Failed to update profile' })
  }
}

async function submitBio() {
  clearFormErrors()
  try {
    await form.submit({ only: ['bio'] })
    toast.success({ title: 'Success', message: 'Bio updated successfully' })
    refreshUser()
  } catch (err: any) {
    toast.error({ title: 'Error', message: err?.data?.message || 'Failed to update bio' })
  }
}

function clearFormErrors() {
  form.errors = {}
}

definePageMeta({ layout: 'dashboard' })
</script>

<style scoped>
.form-input {
  @apply w-full mt-1 px-4 py-2 bg-gray-50 dark:bg-gray-700 border rounded-md text-gray-900 dark:text-white border-gray-300 dark:border-gray-600;
}
.form-error {
  @apply text-sm text-red-600 dark:text-red-400 mt-1 block;
}
.btn-primary {
  @apply px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition disabled:opacity-50;
}
label {
  @apply block text-sm font-medium text-gray-700 dark:text-gray-300;
}
</style>
