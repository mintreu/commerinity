<template>
  <div class="max-w-4xl mx-auto p-6 space-y-8">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Your Profile</h1>
      <button @click="toggleEdit"
              class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">
        {{ isEditing ? 'Cancel' : 'Edit Profile' }}
      </button>
    </div>

    <!-- Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow divide-y divide-gray-200 dark:divide-gray-700">
      <!-- Top: Avatar & Info -->
      <div class="px-6 py-8 flex flex-col sm:flex-row items-center">
        <img :src="avatarPreview || user?.avatar"
             class="w-24 h-24 rounded-full object-cover border-4 border-blue-600 dark:border-blue-400 shadow" />
        <div class="mt-4 sm:mt-0 sm:ml-6 flex-1 text-center sm:text-left">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ user?.name }}</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ user?.email }}</p>

          <!-- View Mode Stats -->
          <div v-if="!isEditing" class="mt-4 flex flex-wrap gap-4 justify-center sm:justify-start">
            <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-300">
              <Icon name="ph:shopping-bag-duotone" class="w-5 h-5 text-blue-600" />
              <span class="font-medium">{{ stats.orders }}</span> Orders
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-300">
              <Icon name="ph:heart-duotone" class="w-5 h-5 text-pink-600" />
              <span class="font-medium">{{ stats.wishlist }}</span> Wishlist
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-300">
              <Icon name="ph:user-plus-duotone" class="w-5 h-5 text-green-600" />
              <span class="font-medium">{{ stats.referrals }}</span> Referrals
            </div>
          </div>
        </div>

        <!-- Referral Copy Button -->
        <div v-if="!isEditing" class="mt-4 sm:mt-0 sm:ml-auto">
          <div @click="copyReferral"
               class="flex items-center px-3 py-1 border border-gray-300 dark:border-gray-600 rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
            <Icon name="ph:link-simple" class="w-4 h-4 mr-1 text-gray-600" />
            <span class="text-sm text-gray-600 dark:text-gray-300">Copy referral</span>
          </div>
        </div>
      </div>

      <!-- Edit Forms -->
      <div class="px-6 py-6 space-y-6">
        <form v-if="isEditing" @submit.prevent="confirmUpload" class="space-y-4">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Change Avatar</h3>
          <input type="file" accept="image/*" @change="onFileChange"
                 class="block w-full text-sm text-gray-700 dark:text-gray-300" />
          <button :disabled="!selectedFile || uploading" type="submit"
                  class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
            {{ uploading ? 'Uploading...' : 'Upload Avatar' }}
          </button>
        </form>

        <form v-if="isEditing" @submit.prevent="confirmProfileUpdate" class="space-y-4">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Update Profile</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
              <input v-model="form.name" type="text" required minlength="3"
                     class="mt-1 w-full px-3 py-2 border rounded bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
              <input v-model="form.email" type="email" required
                     class="mt-1 w-full px-3 py-2 border rounded bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
          </div>
          <button type="submit"
                  class="mt-4 w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            Save Profile
          </button>
        </form>

        <form v-if="isEditing" @submit.prevent="updatePassword" class="space-y-4">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Change Password</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current</label>
              <input v-model="password.current" type="password" minlength="6" required
                     class="mt-1 w-full px-3 py-2 border rounded bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">New</label>
              <input v-model="password.new" type="password" minlength="6" required
                     class="mt-1 w-full px-3 py-2 border rounded bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm</label>
              <input v-model="password.confirm" type="password" minlength="6" required
                     class="mt-1 w-full px-3 py-2 border rounded bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
          </div>
          <button type="submit"
                  class="mt-4 w-full px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
            Update Password
          </button>
        </form>
      </div>
    </div>

    <!-- Confirmation Modals -->
    <Modal v-if="showUploadConfirm" title="Confirm Avatar Upload" @close="showUploadConfirm = false">
      <p>Are you sure you want to upload this avatar?</p>
      <template #footer>
        <button @click="showUploadConfirm = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Cancel</button>
        <button @click="uploadAvatar" class="px-4 py-2 bg-blue-600 text-white rounded">Confirm</button>
      </template>
    </Modal>

    <Modal v-if="showProfileConfirm" title="Confirm Profile Update" @close="showProfileConfirm = false">
      <p>Save changes to your profile?</p>
      <template #footer>
        <button @click="showProfileConfirm = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Cancel</button>
        <button @click="updateProfile" class="px-4 py-2 bg-green-600 text-white rounded">Confirm</button>
      </template>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useSanctum } from '#imports'
import { useRouter } from '#app'

interface User { name: string; email: string; avatar: string; id: number }

const { user } = useSanctum<User>()
const router = useRouter()

const isEditing = ref(false)
const copied = ref(false)

const form = reactive({ name: '', email: '' })
const password = reactive({ current: '', new: '', confirm: '' })

const selectedFile = ref<File | null>(null)
const avatarPreview = ref<string | null>(null)
const uploading = ref(false)

const showUploadConfirm = ref(false)
const showProfileConfirm = ref(false)

const stats = reactive({ orders: 12, wishlist: 5, referrals: 8 })
const referralLink = ref('')

function toggleEdit() { isEditing.value = !isEditing.value }

function confirmUpload() { showUploadConfirm.value = true }
function confirmProfileUpdate() { showProfileConfirm.value = true }

onMounted(() => {
  if (user.value) {
    form.name = user.value.name
    form.email = user.value.email
    referralLink.value = `${window.location.origin}/register?ref=${user.value.id}`
  }
})

function onFileChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0] || null
  if (file?.type.startsWith('image/')) {
    selectedFile.value = file
    avatarPreview.value = URL.createObjectURL(file)
  }
}

async function uploadAvatar() {
  if (!selectedFile.value) return
  uploading.value = true
  const data = new FormData()
  data.append('avatar', selectedFile.value)
  await $fetch('/api/user/avatar', { method: 'POST', body: data })
  uploading.value = false
  router.go(0)
}

async function updateProfile() {
  await $fetch('/api/user/profile', { method: 'POST', body: form })
  alert('Profile updated.')
  router.go(0)
}

async function updatePassword() {
  if (password.new !== password.confirm) {
    return alert("Passwords do not match.")
  }
  await $fetch('/api/user/password', {
    method: 'POST',
    body: {
      current_password: password.current,
      new_password: password.new
    }
  })
  alert('Password updated.')
  password.current = password.new = password.confirm = ''
}

function copyReferral() {
  navigator.clipboard.writeText(referralLink.value)
  copied.value = true
  setTimeout(() => copied.value = false, 2000)
}
</script>

<style scoped>
/* No additional CSS—Tailwind covers the layout and styles */
</style>
