<template>
  <div class="space-y-6">
    <div class="glass-card p-8">
      <div class="flex items-center justify-between mb-6">
        <div class="text-center flex-1">
          <!-- Avatar Section -->
          <div class="relative inline-block mb-4">
            <UAvatar
              :src="user?.avatar"
              :alt="user?.name"
              size="3xl"
              class="ring-4 ring-blue-100 dark:ring-blue-900/30"
            />
            <NuxtLink
              to="/profile/edit"
              class="absolute bottom-0 right-0 w-10 h-10 bg-blue-500 hover:bg-blue-600 text-white rounded-full flex items-center justify-center shadow-lg transition-colors"
            >
              <UIcon name="i-lucide-camera" class="w-5 h-5" />
            </NuxtLink>
          </div>
          <h2 class="text-3xl font-bold gradient-text-primary mb-2">
            {{ user?.name || 'Profile Settings' }}
          </h2>
          <p class="text-slate-600 dark:text-slate-400">
            Manage your account information
          </p>
        </div>
      </div>

      <div class="flex justify-end mb-6">
        <NuxtLink to="/profile/edit">
          <UButton
            color="primary"
            variant="soft"
          >
            <UIcon
              name="i-lucide-edit"
              class="w-4 h-4"
            />
            Edit Profile
          </UButton>
        </NuxtLink>
      </div>
    </div>

    <div class="stat-card">
      <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">
        Account Information
      </h3>
      <div class="space-y-3">
        <div class="flex justify-between py-2 border-b border-slate-200 dark:border-slate-700">
          <span class="text-slate-600 dark:text-slate-400">Name:</span>
          <span class="font-semibold text-slate-900 dark:text-white">{{ user?.name }}</span>
        </div>
        <div class="flex justify-between py-2 border-b border-slate-200 dark:border-slate-700">
          <span class="text-slate-600 dark:text-slate-400">Mobile:</span>
          <span class="font-semibold text-slate-900 dark:text-white">{{ user?.mobile }}</span>
        </div>
        <div class="flex justify-between py-2 border-b border-slate-200 dark:border-slate-700">
          <span class="text-slate-600 dark:text-slate-400">Email:</span>
          <span class="font-semibold text-slate-900 dark:text-white">{{ user?.email || 'Not set' }}</span>
        </div>
        <div class="flex justify-between py-2 border-b border-slate-200 dark:border-slate-700">
          <span class="text-slate-600 dark:text-slate-400">Type:</span>
          <span class="font-semibold text-slate-900 dark:text-white">{{ getUserTypeLabel() }}</span>
        </div>
        <div
          v-if="user?.bio"
          class="flex justify-between py-2 border-b border-slate-200 dark:border-slate-700"
        >
          <span class="text-slate-600 dark:text-slate-400">Bio:</span>
          <span class="font-semibold text-slate-900 dark:text-white text-right max-w-md">{{ user?.bio }}</span>
        </div>
        <div
          v-if="user?.gender"
          class="flex justify-between py-2 border-b border-slate-200 dark:border-slate-700"
        >
          <span class="text-slate-600 dark:text-slate-400">Gender:</span>
          <span class="font-semibold text-slate-900 dark:text-white">{{ formatGender(user?.gender) }}</span>
        </div>
        <div
          v-if="user?.dob"
          class="flex justify-between py-2"
        >
          <span class="text-slate-600 dark:text-slate-400">Date of Birth:</span>
          <span class="font-semibold text-slate-900 dark:text-white">{{ formatDate(user?.dob) }}</span>
        </div>
      </div>
    </div>

    <!-- Change Password Card -->
    <div class="glass-card p-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center">
            <UIcon
              name="i-lucide-key"
              class="w-6 h-6 text-white"
            />
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">
              Password & Security
            </h3>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              Update your password to keep your account secure
            </p>
          </div>
        </div>
        <NuxtLink to="/profile/change-password">
          <UButton
            color="amber"
            variant="soft"
          >
            <UIcon
              name="i-lucide-shield"
              class="w-4 h-4"
            />
            Change Password
          </UButton>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: '$auth',
  title: 'Profile Settings'
})

const { user, getUserTypeLabel } = useUserType()

const formatGender = (gender: string) => {
  const genderMap: Record<string, string> = {
    male: 'Male',
    female: 'Female',
    other: 'Other'
  }
  return genderMap[gender] || gender
}

const formatDate = (dateString: string) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
</script>
