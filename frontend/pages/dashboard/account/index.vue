<template>
  <div class="w-full min-h-screen bg-gray-50 dark:bg-gray-900 py-10 px-4 md:px-10">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
        {{ isOwnProfile ? 'Welcome, ' : '' }}{{ currentUser?.name || 'User' }}
      </h1>
      <div v-if="isOwnProfile">
        <NuxtLink
            to="/dashboard/account/edit"
            class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:underline font-medium"
        >
          <Icon name="mdi:account-edit-outline" class="text-xl" />
          Edit Profile
        </NuxtLink>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Profile Card -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 space-y-6">
        <div class="flex flex-col items-center text-center space-y-3">
<!--          <NuxtImg-->
<!--              :src="currentUser?.avatar || '/default-avatar.png'"-->
<!--              alt="Avatar"-->
<!--              class="w-32 h-32 rounded-full border-4 border-blue-600 dark:border-blue-400 object-cover shadow"-->
<!--          />-->

          <AvatarUploader />

          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ currentUser?.name }}</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ currentUser?.email }}</p>

          <!-- Verification Badges -->
          <div class="flex flex-row gap-2 mt-2 text-sm">
            <span
                class="px-3 py-1 rounded-xl text-white"
                :class="currentUser?.email_verified ? 'bg-green-600' : 'bg-gray-500'"
            >
              Email {{ currentUser?.email_verified ? 'Verified' : 'Unverified' }}
            </span>
            <span
                class="px-3 py-1 rounded-xl text-white"
                :class="currentUser?.mobile_verified ? 'bg-green-600' : 'bg-gray-500'"
            >
              Mobile {{ currentUser?.mobile_verified ? 'Verified' : 'Unverified' }}
            </span>
          </div>

          <!-- User Type & Status -->
          <div class="flex flex-row gap-2 mt-2 text-sm font-semibold">
            <span class="px-3 py-1 rounded-xl bg-blue-600 text-white">{{ currentUser?.type || 'Member' }}</span>
            <span
                class="px-3 py-1 rounded-xl"
                :class="{
                'bg-gray-500 text-white': !currentUser?.status,
                'bg-yellow-500 text-white': currentUser?.status === 'Draft',
                'bg-green-600 text-white': currentUser?.status === 'Subscribed',
                'bg-red-500 text-white': currentUser?.status === 'Blocked'
              }"
            >
              {{ currentUser?.status || 'Unknown' }}
            </span>
          </div>

          <!-- Referral Link -->
          <div v-if="isOwnProfile && currentUser?.status === 'Subscribed'" class="mt-4 w-full">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Your Referral Link
            </label>
            <div class="flex flex-col sm:flex-row items-center gap-2">
              <div
                  @click="copyReferral"
                  class="flex-1 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 transition overflow-x-auto whitespace-nowrap text-sm referral-scrollbar"
                  title="Click to copy"
              >
                {{ referralLink }}
              </div>
              <button
                  @click="copyReferral"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl transition mt-2 sm:mt-0 text-sm"
              >
                Copy
              </button>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              Share this link to invite friends and earn rewards.
            </p>
          </div>
        </div>

        <!-- Personal Info -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-2 text-sm text-gray-700 dark:text-gray-300">
          <p><strong>Mobile:</strong> {{ currentUser?.mobile || 'Not set' }}</p>
          <p><strong>Gender:</strong> {{ currentUser?.gender || 'Not set' }}</p>
          <p><strong>DOB:</strong> {{ currentUser?.dob || 'Not set' }}</p>
          <p><strong>Referral Code:</strong> <span class="font-medium">{{ currentUser?.referral_code || 'N/A' }}</span></p>
          <p><strong>Parent:</strong> {{ currentUser?.hasParent ? currentUser?.parent : 'None' }}</p>
          <p><strong>Level:</strong> {{ currentUser?.hasLevel ? currentUser?.level_id : 'Not assigned' }}</p>
        </div>

        <!-- Share Profile -->
        <div v-if="isOwnProfile" class="mt-4">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Share Your Profile</h3>
          <div class="flex flex-wrap gap-2">
            <button @click="shareProfile('whatsapp')" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-xl text-sm">WhatsApp</button>
            <button @click="shareProfile('twitter')" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-sm">Twitter</button>
            <button @click="shareProfile('facebook')" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-xl text-sm">Facebook</button>
            <button @click="shareProfile('copy')" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-xl text-sm">Copy Link</button>
          </div>
        </div>
      </div>

      <!-- Stats / Activity / Bio -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Bio Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">About Me</h3>
          <div class="text-gray-700 dark:text-gray-300 text-sm max-h-72 overflow-y-auto">
            <p v-if="currentUser?.bio">{{ currentUser.bio }}</p>
            <p v-else class="italic text-gray-500 dark:text-gray-400">No bio yet</p>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activity</h3>
          <p class="text-gray-500 dark:text-gray-400 text-sm">No recent activity available.</p>
        </div>

        <!-- Additional Information -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Additional Information</h3>
          <p class="text-gray-500 dark:text-gray-400 text-sm">You can display other data like posts, achievements, or stats here.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useSanctum,useToast } from '#imports'
import { computed } from 'vue'
import AvatarUploader from "~/components/account/AvatarUploader.vue";

const toast = useToast()

interface User {
  uuid: string;
  avatar: string;
  dob: string;
  email: string;
  email_verified: boolean;
  gender: string;
  hasLevel: boolean;
  hasParent: boolean;
  level_id: number | null;
  mobile: string;
  mobile_verified: boolean;
  name: string;
  parent: string | null;
  referral_code: string;
  status: string;
  status_feedback: string | null;
  type: string;
}

const { user } = useSanctum<User>();
const currentUser = computed(() => user.value)

definePageMeta({ layout: 'dashboard' })

const isOwnProfile = computed(() => true)

const referralLink = computed(() => {
  if (!currentUser.value?.referral_code) return ''
  return `${window.location.origin}/register?referral=${currentUser.value.referral_code}`
})

function copyReferral() {
  if (!referralLink.value) return
  navigator.clipboard.writeText(referralLink.value)
      .then(() => toast.success({ title: 'Copied!', message: 'Referral link copied to clipboard.' }))
      .catch(() => toast.error({ title: 'Error', message: 'Failed to copy referral link.' }))
}

function shareProfile(platform: string) {
  const url = `${window.location.origin}/community/account/${currentUser.value?.uuid || ''}`

  switch (platform) {
    case 'whatsapp':
      window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(url)}`, '_blank')
      break
    case 'twitter':
      window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}`, '_blank')
      break
    case 'facebook':
      window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank')
      break
    case 'copy':
      navigator.clipboard.writeText(url)
      toast.success({ title: 'Copied!', message: 'Profile link copied.' })
      break
  }
}
</script>

<style scoped>
/* Referral Link Scrollbar: hidden by default, visible on hover */
.referral-scrollbar::-webkit-scrollbar {
  height: 6px;
  width: 0;
  background: transparent;
}
.referral-scrollbar {
  -ms-overflow-style: none; /* IE 10+ */
  scrollbar-width: none; /* Firefox */
}
.referral-scrollbar:hover::-webkit-scrollbar {
  height: 6px;
  width: 6px;
}
.referral-scrollbar:hover::-webkit-scrollbar-thumb {
  background-color: rgba(100, 100, 100, 0.5);
  border-radius: 10px;
}
.referral-scrollbar:hover::-webkit-scrollbar-track {
  background: transparent;
}
</style>
