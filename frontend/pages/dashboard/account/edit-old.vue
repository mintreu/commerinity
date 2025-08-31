<template>
  <div class="max-w-5xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-10">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">My Account</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Manage your personal profile and secure your account.
        </p>
      </div>
    </div>

    <!-- Avatar Upload Section -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-xl mb-10 overflow-hidden">
      <div class="grid grid-cols-1 md:grid-cols-[250px_1fr] divide-y md:divide-y-0 md:divide-x divide-gray-200 dark:divide-gray-700">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white">Profile Picture</h2>
          <p class="text-sm md:hidden text-gray-600 dark:text-gray-400">Upload and crop your profile avatar.</p>
        </div>
        <div class="p-6 flex flex-col gap-6">
          <div class="flex justify-center">
            <div class="relative w-40 h-40 rounded-full overflow-hidden border-4 border-white shadow-md cursor-pointer group">
              <input type="file" accept="image/*" @change="onFileChange" class="absolute inset-0 opacity-0 z-10 cursor-pointer" />
              <img v-if="croppedAvatar" :src="croppedAvatar" class="w-full h-full object-cover rounded-full" />
              <div v-else class="w-full h-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-300 text-sm">
                Click to Upload
              </div>
              <div class="absolute inset-0 bg-black bg-opacity-20 hidden group-hover:flex items-center justify-center text-white font-medium">
                Change
              </div>
            </div>
          </div>

          <!-- Cropper -->
          <div class="flex-1 space-y-4" v-if="image">
            <div class="relative w-full max-w-md aspect-square border rounded overflow-hidden bg-gray-100 dark:bg-gray-700">
              <Cropper
                  class="cropper"
                  :src="image"
                  :stencil-component="CircleStencil"
                  :stencil-props="{ aspectRatio: 1 }"
                  :auto-zoom="true"
                  @change="onCrop"
              />
            </div>
            <div class="text-right">
              <button class="btn-primary" @click="saveCroppedImage">Save Avatar</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Basic Info -->
    <form @submit.prevent="submitBasicInfo" class="bg-white dark:bg-gray-800 shadow rounded-xl">
      <div class="grid grid-cols-1 md:grid-cols-[250px_1fr] divide-y md:divide-y-0 md:divide-x divide-gray-200 dark:divide-gray-700">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white">Basic Information</h2>
          <p class="text-sm text-gray-600 dark:text-gray-400">This is how your profile appears across the system.</p>
        </div>
        <div class="p-6 space-y-6 flex flex-col">
          <div class="flex flex-col gap-4">
            <div class="gap-2 my-2">
              <label>Name</label>
              <input v-model="form.name" type="text" class="form-input" required minlength="3" placeholder="Your full name" />
              <span v-if="form.errors.name" class="form-error">{{ form.errors.name }}</span>
            </div>
            <div class="gap-2 my-2">
              <label>Mobile</label>
              <input v-model="form.mobile" type="tel" class="form-input" required placeholder="+91 98765 43210" />
              <span v-if="form.errors.mobile" class="form-error">{{ form.errors.mobile }}</span>
            </div>
            <div class="gap-2 my-2">
              <label>Gender</label>
              <select v-model="form.gender" class="form-input" required>
                <option value="">Select gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
              <span v-if="form.errors.gender" class="form-error">{{ form.errors.gender }}</span>
            </div>
            <div class="gap-2 my-2">
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

    <!-- Contact Info -->
    <form @submit.prevent="submitContactInfo" class="bg-white dark:bg-gray-800 shadow rounded-xl mt-10">
      <div class="grid grid-cols-1 md:grid-cols-[250px_1fr] divide-y md:divide-y-0 md:divide-x divide-gray-200 dark:divide-gray-700">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white">Contact & Bio</h2>
          <p class="text-sm text-gray-600 dark:text-gray-400">Optional contact details and bio for profile enrichment.</p>
        </div>
        <div class="p-6 space-y-6">
          <div class="flex flex-col gap-4">
            <div class="gap-2 my-2">
              <label>Email</label>
              <input v-model="form.email" type="email" class="form-input" placeholder="your@email.com" />
              <span v-if="form.errors.email" class="form-error">{{ form.errors.email }}</span>
            </div>
            <div class="gap-2 my-2">
              <label>Bio</label>
              <textarea ref="bioRef" v-model="form.bio" class="form-input  overflow-hidden" rows="2" maxlength="500" placeholder="Write something about yourself" @input="resizeBio"></textarea>
              <span v-if="form.errors.bio" class="form-error">{{ form.errors.bio }}</span>
            </div>
          </div>
          <div class="flex flex-row">
            <div class="grow hidden md:block"></div>
            <button type="submit" class="btn-primary" :disabled="form.processing">Save Contact Info</button>
          </div>
        </div>
      </div>
    </form>

    <!-- Password -->
    <form @submit.prevent="submitPassword" class="bg-white dark:bg-gray-800 shadow rounded-xl mt-10">
      <div class="grid grid-cols-1 md:grid-cols-[250px_1fr] divide-y md:divide-y-0 md:divide-x divide-gray-200 dark:divide-gray-700">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white">Change Password</h2>
          <p class="text-sm text-gray-600 dark:text-gray-400">Use a strong and unique password to secure your account.</p>
        </div>
        <div class="p-6 space-y-6">
          <div class="flex flex-col gap-4">
            <div class="gap-2 my-2">
              <label>Current Password</label>
              <div class="relative">
                <input :type="showCurrentPassword ? 'text' : 'password'" v-model="form.current_password" class="form-input pr-10" />
                <span class="absolute right-3 top-2.5 cursor-pointer" @click="showCurrentPassword = !showCurrentPassword">
                  <Icon :name="showCurrentPassword ? 'mdi:eye-off' : 'mdi:eye'" />
                </span>
              </div>
              <span v-if="form.errors.current_password" class="form-error">{{ form.errors.current_password }}</span>
            </div>
            <div class="gap-2 my-2">
              <label>New Password</label>
              <div class="relative">
                <input :type="showNewPassword ? 'text' : 'password'" v-model="form.password" class="form-input pr-10" />
                <span class="absolute right-3 top-2.5 cursor-pointer" @click="showNewPassword = !showNewPassword">
                  <Icon :name="showNewPassword ? 'mdi:eye-off' : 'mdi:eye'" />
                </span>
              </div>
              <span v-if="form.errors.password" class="form-error">{{ form.errors.password }}</span>
            </div>
            <div class="gap-2 my-2">
              <label>Confirm Password</label>
              <div class="relative">
                <input :type="showConfirmPassword ? 'text' : 'password'" v-model="form.password_confirmation" class="form-input pr-10" />
                <span class="absolute right-3 top-2.5 cursor-pointer" @click="showConfirmPassword = !showConfirmPassword">
                  <Icon :name="showConfirmPassword ? 'mdi:eye-off' : 'mdi:eye'" />
                </span>
              </div>
              <span v-if="form.errors.password_confirmation" class="form-error">{{ form.errors.password_confirmation }}</span>
            </div>
          </div>
          <div class="flex flex-row">
            <div class="grow hidden md:block"></div>
            <button type="submit" class="btn-primary" :disabled="form.processing">Update Password</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { Cropper, CircleStencil } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'
import { useSanctum, useSanctumForm, useRuntimeConfig } from '#imports'

const config = useRuntimeConfig()
const { user, refreshUser } = useSanctum()

const form = useSanctumForm('POST', `${config.public.apiBase}/user/profile`, {
  name: user.value?.name ?? '',
  email: user.value?.email ?? '',
  mobile: user.value?.mobile ?? '',
  bio: user.value?.bio ?? '',
  gender: user.value?.gender ?? '',
  dob: user.value?.dob ?? '',
  current_password: '',
  password: '',
  password_confirmation: '',
})

const image = ref<string | null>(null)
const croppedAvatar = ref<string | null>(null)
const cropResult = ref(null)

function onFileChange(event: Event) {
  const file = (event.target as HTMLInputElement)?.files?.[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      image.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

function onCrop({ canvas }: any) {
  cropResult.value = canvas
}

function saveCroppedImage() {
  if (cropResult.value) {
    croppedAvatar.value = cropResult.value.toDataURL()
    image.value = null
  }
}

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const bioRef = ref<HTMLTextAreaElement | null>(null)
function resizeBio() {
  if (bioRef.value) {
    bioRef.value.style.height = 'auto'
    bioRef.value.style.height = bioRef.value.scrollHeight + 'px'
  }
}
onMounted(resizeBio)
watch(() => form.bio, resizeBio)

function submitBasicInfo() {
  clearFormErrors()
  form.submit({ only: ['name', 'mobile', 'gender', 'dob'] }).then(refreshUser)
}
function submitContactInfo() {
  clearFormErrors()
  form.submit({ only: ['email', 'bio'] }).then(refreshUser)
}
function submitPassword() {
  clearFormErrors()
  if (
      form.current_password.length < 6 ||
      form.password.length < 8 ||
      form.password !== form.password_confirmation
  ) return
  form.submit({ only: ['current_password', 'password', 'password_confirmation'] }).then(refreshUser)
}

function clearFormErrors() {
  form.errors = {} // reset errors object
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
.cropper {
  width: 100%;
  height: 100%;
}
</style>
