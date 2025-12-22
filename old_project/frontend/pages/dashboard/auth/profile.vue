<template>
  <div class="max-w-full mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">My Profile</h1>
      <button
          @click="toggleEdit"
          class="text-sm text-blue-600 hover:text-blue-800 transition-colors duration-200"
      >
        {{ isEditing ? 'Cancel' : 'Edit Profile' }}
      </button>
    </div>

    <!-- Profile Card -->
    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-xl divide-y divide-gray-200 dark:divide-gray-700">
      <!-- Avatar & Basic Info -->
      <div class="p-6 flex flex-col sm:flex-row items-center">
        <div class="relative">
          <img
              :src="avatarPreview || user?.avatar"
              alt="User Avatar"
              class="w-28 md:w-36 h-28 md:h-36 rounded-full object-cover border-4 border-blue-500 dark:border-blue-400"
          />
          <div v-if="isEditing"
               class="absolute bottom-0 right-0 bg-blue-600 text-white p-1 rounded-full cursor-pointer hover:bg-blue-700">
            <label class="cursor-pointer">
              <input type="file" accept="image/*" class="hidden" @change="onFileChange"/>
              📸
            </label>
          </div>
        </div>

        <div class="sm:ml-6 mt-4 sm:mt-0 text-center sm:text-left flex-1">
          <h2 class="text-xl md:text-3xl font-semibold text-gray-900 dark:text-white">{{ user?.name }}</h2>
          <p class="text-sm md:text-lg text-gray-600 dark:text-gray-300 flex items-center gap-1">
            <Icon name="material-icon-theme:email" />
            {{ user?.email }}
          </p>
          <p class="text-sm md:text-lg text-gray-600 dark:text-gray-300 flex items-center gap-1">
            <Icon name="solar:phone-rounded-bold" />
            {{ user?.mobile || 'not set' }}
          </p>
        </div>
      </div>

      <!-- Avatar Upload Form -->
      <form
          v-if="isEditing && selectedFile"
          @submit.prevent="uploadAvatar"
          class="px-6 py-4"
      >
        <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition disabled:opacity-50"
            :disabled="uploading"
        >
          {{ uploading ? 'Uploading...' : 'Upload Avatar' }}
        </button>
      </form>

      <!-- Profile Update Form -->
      <form
          v-if="isEditing"
          @submit.prevent="updateProfile"
          class="p-6 space-y-6"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
            <input
                v-model="form.name"
                type="text"
                placeholder="Enter your full name"
                class="mt-1 block w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border rounded-md text-gray-900 dark:text-white"
                :class="form.errors.name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                required
                minlength="3"
            />
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Used for account display & notifications.</p>
            <span v-if="form.errors.name" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</span>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
            <input
                v-model="form.email"
                type="email"
                placeholder="you@example.com"
                class="mt-1 block w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border rounded-md text-gray-900 dark:text-white"
                :class="form.errors.email ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                required
            />
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Primary contact & login email.</p>
            <span v-if="form.errors.email" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.email }}</span>
          </div>

          <!-- Mobile -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mobile</label>
            <input
                v-model="form.mobile"
                type="tel"
                placeholder="+1 555 123 4567"
                class="mt-1 block w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border rounded-md text-gray-900 dark:text-white"
                :class="form.errors.mobile ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                required
            />
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Used for SMS alerts & two-factor auth.</p>
            <span v-if="form.errors.mobile" class="text-sm text-red-600 dark:text-red-400">{{
                form.errors.mobile
              }}</span>
          </div>

          <!-- Gender -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
            <select
                v-model="form.gender"
                class="mt-1 block w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border rounded-md text-gray-900 dark:text-white"
                :class="form.errors.gender ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
            >
              <option value="" disabled>Select gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Optional demographic info.</p>
            <span v-if="form.errors.gender" class="text-sm text-red-600 dark:text-red-400">{{
                form.errors.gender
              }}</span>
          </div>

          <!-- Date of Birth -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Birth</label>
            <input
                v-model="form.dob"
                type="date"
                class="mt-1 block w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border rounded-md text-gray-900 dark:text-white"
                :class="form.errors.dob ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
            />
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Used to verify age-restricted features.</p>
            <span v-if="form.errors.dob" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.dob }}</span>
          </div>

          <!-- Bio -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bio</label>
            <textarea
                v-model="form.bio"
                placeholder="Tell us something about yourself"
                rows="3"
                class="mt-1 block w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border rounded-md text-gray-900 dark:text-white"
                :class="form.errors.bio ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
            ></textarea>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">A short personal description.</p>
            <span v-if="form.errors.bio" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.bio }}</span>
          </div>
        </div>

        <!-- Change Password Section -->
        <div class="border-t pt-6 space-y-4">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Change Password</h3>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Password</label>
            <input
                v-model="form.current_password"
                type="password"
                placeholder="••••••••"
                class="mt-1 block w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border rounded-md text-gray-900 dark:text-white"
                :class="form.errors.current_password ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
            />
            <span v-if="form.errors.current_password"
                  class="text-sm text-red-600 dark:text-red-400">{{ form.errors.current_password }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
            <input
                v-model="form.password"
                type="password"
                placeholder="••••••••"
                class="mt-1 block w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border rounded-md text-gray-900 dark:text-white"
                :class="form.errors.password ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
            />
            <span v-if="form.errors.password" class="text-sm text-red-600 dark:text-red-400">{{
                form.errors.password
              }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm New Password</label>
            <input
                v-model="form.password_confirmation"
                type="password"
                placeholder="••••••••"
                class="mt-1 block w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border rounded-md text-gray-900 dark:text-white"
                :class="form.errors.password_confirmation ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
            />
            <span v-if="form.errors.password_confirmation"
                  class="text-sm text-red-600 dark:text-red-400">{{ form.errors.password_confirmation }}</span>
          </div>
        </div>

        <!-- Save Button -->
        <div>
          <button
              type="submit"
              class="w-full md:w-auto px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition"
              :disabled="form.processing"
          >
            {{ form.processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>


<script setup lang="ts">
import {ref, watch} from 'vue'
import {useSanctum, useSanctumFetch, useSanctumForm, useRuntimeConfig} from '#imports'

interface User {
  id: number
  name: string
  email: string
  avatar: string
  mobile?: string
  bio?: string
  gender?: string
  dob?: string
}

const config = useRuntimeConfig()
const {user, refreshUser} = useSanctum<User>()
const isEditing = ref(false)
const avatarPreview = ref<string | null>(null)
const selectedFile = ref<File | null>(null)
const uploading = ref(false)

const form = useSanctumForm('POST', `${config.public.apiBase}/user/profile`, {
  name: '',
  email: '',
  mobile: '',
  bio: '',
  gender: '',
  dob: '',
  current_password: '',
  password: '',
  password_confirmation: '',
})

// Pre-fill form when editing starts
function toggleEdit() {
  isEditing.value = !isEditing.value
  if (isEditing.value && user.value) {
    form.setData({
      name: user.value.name,
      email: user.value.email,
      mobile: (user.value as any).mobile || '',
      bio: user.value.bio || '',
      gender: user.value.gender || '',
      dob: user.value.dob || '',
      current_password: '',
      password: '',
      password_confirmation: ''
    })
  }
}

// Watch user avatar changes to clear preview
watch(() => user.value?.avatar, () => {
  avatarPreview.value = null
})

// Handle avatar file selection
function onFileChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0] || null
  if (file && file.type.startsWith('image/')) {
    selectedFile.value = file
    avatarPreview.value = URL.createObjectURL(file)
  }
}

// Upload avatar endpoint
async function uploadAvatar() {
  if (!selectedFile.value) return
  uploading.value = true
  const formData = new FormData()
  formData.append('avatar', selectedFile.value)

  try {
    await useSanctumFetch(`${config.public.apiBase}/user/avatar`, {
      method: 'POST',
      body: formData
    })
    await refreshUser()
    selectedFile.value = null
    avatarPreview.value = null
  } catch {
    // handle error UI here
  } finally {
    uploading.value = false
  }
}

// Submit profile changes
async function updateProfile() {
  await form.submit()
      .then(async () => {
        await refreshUser()
        isEditing.value = false
      })
      .catch(() => {
        // errors are auto-populated in form.errors
      })
}
</script>

<style scoped>
/* Tailwind CSS handles all styling */
</style>