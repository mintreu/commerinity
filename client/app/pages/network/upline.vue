<script setup lang="ts">
/**
 * Upline (Sponsor/Parent) Page
 *
 * Shows the direct parent/sponsor - only 1 record
 * Clean, focused design for displaying sponsor info
 */

definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const config = useRuntimeConfig()
const { user } = useSanctum()
const toast = useToast()

const parent = ref<any>(null)
const isLoading = ref(false)

const fetchParent = async () => {
  isLoading.value = true
  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/api/affiliate/upline`, {
      method: 'GET'
    })

    if (response?.success && response?.data) {
      // Upline returns array, we only need the first one (direct parent)
      parent.value = Array.isArray(response.data) && response.data.length > 0
        ? response.data[0]
        : null
    }
  } catch (err: any) {
    console.error('Failed to fetch upline:', err)
    toast.add({
      title: 'Error',
      description: 'Failed to load sponsor information',
      color: 'error'
    })
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  await fetchParent()
})

const getDefaultAvatar = (name: string) => {
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&size=120&background=8B5CF6&color=fff&bold=true`
}
</script>

<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          My Sponsor
        </h1>
        <p class="text-gray-500 dark:text-gray-400">
          Your direct upline/referrer
        </p>
      </div>
      <UButton
        to="/network"
        variant="outline"
        color="primary"
      >
        <UIcon
          name="i-lucide-git-branch"
          class="w-4 h-4 mr-2"
        />
        View Network
      </UButton>
    </div>

    <!-- Loading State -->
    <div
      v-if="isLoading"
      class="flex justify-center py-12"
    >
      <UIcon
        name="i-lucide-loader-2"
        class="w-8 h-8 animate-spin text-violet-500"
      />
    </div>

    <!-- Sponsor Card -->
    <div
      v-else-if="parent"
      class="glass-card overflow-hidden"
    >
      <!-- Gradient Header -->
      <div class="bg-gradient-to-r from-violet-600 to-fuchsia-600 p-8 text-center">
        <img
          :src="getDefaultAvatar(parent.name)"
          class="w-28 h-28 rounded-full mx-auto border-4 border-white shadow-xl"
        >
        <h2 class="mt-4 text-2xl font-bold text-white">
          {{ parent.name }}
        </h2>
        <UBadge
          color="white"
          variant="subtle"
          class="mt-2 capitalize"
        >
          {{ parent.type?.value || parent.type || 'Member' }}
        </UBadge>
      </div>

      <!-- Info Section -->
      <div class="p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div class="text-center p-4 bg-violet-50 dark:bg-violet-900/20 rounded-xl">
            <UIcon
              name="i-lucide-user"
              class="w-8 h-8 mx-auto text-violet-600 dark:text-violet-400 mb-2"
            />
            <p class="text-xs text-gray-500 dark:text-gray-400">
              Relationship
            </p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">
              Sponsor
            </p>
          </div>

          <div class="text-center p-4 bg-fuchsia-50 dark:bg-fuchsia-900/20 rounded-xl">
            <UIcon
              name="i-lucide-award"
              class="w-8 h-8 mx-auto text-fuchsia-600 dark:text-fuchsia-400 mb-2"
            />
            <p class="text-xs text-gray-500 dark:text-gray-400">
              Their Level
            </p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">
              {{ parent.level || 1 }}
            </p>
          </div>
        </div>

        <!-- User Info -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
          <div class="flex items-center justify-between mb-1">
            <span class="text-sm text-gray-500 dark:text-gray-400">User ID</span>
            <UIcon
              name="i-lucide-fingerprint"
              class="w-4 h-4 text-gray-400"
            />
          </div>
          <p class="text-sm font-mono text-gray-900 dark:text-white">
            {{ parent.uuid }}
          </p>
        </div>

        <!-- Note -->
        <div class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
          <UIcon
            name="i-lucide-info"
            class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 shrink-0"
          />
          <div>
            <p class="text-sm text-blue-800 dark:text-blue-300 font-medium">
              Your Sponsor
            </p>
            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
              This is the person who referred you to the network. Contact them for guidance and support in your journey.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- No Sponsor State -->
    <div
      v-else
      class="glass-card p-12 text-center"
    >
      <div class="w-20 h-20 mx-auto bg-gradient-to-br from-violet-100 to-fuchsia-100 dark:from-violet-900/30 dark:to-fuchsia-900/30 rounded-full flex items-center justify-center mb-6">
        <UIcon
          name="i-lucide-crown"
          class="w-10 h-10 text-violet-600 dark:text-violet-400"
        />
      </div>
      <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
        You're at the Top!
      </h3>
      <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
        You don't have a sponsor. This means you joined the network as a top-level member.
      </p>
    </div>

    <!-- Your Info Card -->
    <div class="glass-card p-6">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        Your Referral Details
      </h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
            Your Referral Code
          </p>
          <p class="text-lg font-bold font-mono text-violet-600 dark:text-violet-400">
            {{ user?.referral_code }}
          </p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
            Your Position
          </p>
          <p class="text-lg font-bold text-gray-900 dark:text-white capitalize">
            {{ user?.type || 'Member' }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss";

.glass-card {
  @apply bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm;
}
</style>
