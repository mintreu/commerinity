<script setup lang="ts">
/**
 * Team (Direct Children/Downline) Page
 *
 * Shows direct team members (first level downline)
 * with improved design and member details drawer
 */

definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const config = useRuntimeConfig()
const { children, childrenMeta, isLoading, fetchChildren } = useNetwork()
const toast = useToast()

const drawerOpen = ref(false)
const selectedMember = ref<any>(null)
const currentPage = ref(1)

onMounted(async () => {
  try {
    await fetchChildren()
  } catch {
    toast.add({
      title: 'Error',
      description: 'Failed to load team members',
      color: 'error'
    })
  }
})

const loadPage = async (page: number) => {
  currentPage.value = page
  await fetchChildren({ page })
}

const selectMember = (member: any) => {
  selectedMember.value = member
  drawerOpen.value = true
}

const closeDrawer = () => {
  drawerOpen.value = false
}

const getDefaultAvatar = (name: string) => {
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&size=80&background=8B5CF6&color=fff&bold=true`
}

const formatDate = (dateString: string | null) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const getStatusColor = (status: string): 'success' | 'warning' | 'error' | 'neutral' => {
  const colors: Record<string, 'success' | 'warning' | 'error' | 'neutral'> = {
    active: 'success',
    pending: 'warning',
    suspended: 'error',
    banned: 'error'
  }
  return colors[status] || 'neutral'
}
</script>

<template>
  <div class="max-w-6xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Team</h1>
        <p class="text-gray-500 dark:text-gray-400">
          Direct team members: <span class="font-semibold text-violet-600 dark:text-violet-400">{{ childrenMeta?.total || 0 }}</span>
        </p>
      </div>
      <UButton to="/network" variant="outline" color="primary">
        <UIcon name="i-lucide-git-branch" class="w-4 h-4 mr-2" />
        View Full Network
      </UButton>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading && children.length === 0" class="flex justify-center py-12">
      <UIcon name="i-lucide-loader-2" class="w-8 h-8 animate-spin text-violet-500" />
    </div>

    <!-- Team List -->
    <template v-else-if="children.length > 0">
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="member in children"
          :key="member.uuid"
          class="glass-card p-5 cursor-pointer hover:shadow-lg transition-shadow"
          @click="selectMember(member)"
        >
          <div class="flex items-center gap-4">
            <img
              :src="getDefaultAvatar(member.name)"
              class="w-14 h-14 rounded-full border-2 border-violet-200 dark:border-violet-800"
            />
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ member.name }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ member.email }}</p>
              <div class="flex items-center gap-2 mt-1">
                <UBadge :color="getStatusColor(member.status)" variant="subtle" size="xs">
                  {{ member.status }}
                </UBadge>
                <span class="text-xs text-gray-400">{{ formatDate(member.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="childrenMeta && childrenMeta.last_page > 1" class="flex justify-center pt-4">
        <UPagination
          :model-value="childrenMeta.current_page"
          :total="childrenMeta.total"
          :page-count="childrenMeta.per_page"
          @update:model-value="loadPage"
        />
      </div>
    </template>

    <!-- Empty State -->
    <div v-else class="glass-card p-12 text-center">
      <UIcon name="i-lucide-users" class="w-16 h-16 mx-auto text-gray-400 mb-4" />
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Team Members Yet</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-6">Share your referral link to start building your team.</p>
      <UButton color="primary" to="/network">Get Referral Link</UButton>
    </div>

    <!-- Member Details Drawer -->
    <USlideover v-model="drawerOpen" side="right">
      <template #header>
        <div class="flex items-center justify-between w-full">
          <h3 class="text-lg font-semibold">Member Details</h3>
        </div>
      </template>

      <div v-if="selectedMember" class="p-6 space-y-6">
        <!-- Profile -->
        <div class="text-center">
          <img
            :src="getDefaultAvatar(selectedMember.name)"
            class="w-24 h-24 rounded-full mx-auto border-4 border-violet-200 dark:border-violet-800 shadow-lg"
          />
          <h4 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">{{ selectedMember.name }}</h4>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ selectedMember.email }}</p>
          <UBadge :color="getStatusColor(selectedMember.status)" class="mt-2">{{ selectedMember.status }}</UBadge>
        </div>

        <!-- Info Cards -->
        <div class="space-y-3">
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
            <div class="flex items-center justify-between mb-1">
              <span class="text-sm text-gray-500 dark:text-gray-400">User Type</span>
              <UIcon name="i-lucide-user" class="w-4 h-4 text-gray-400" />
            </div>
            <p class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ selectedMember.type }}</p>
          </div>

          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
            <div class="flex items-center justify-between mb-1">
              <span class="text-sm text-gray-500 dark:text-gray-400">Joined On</span>
              <UIcon name="i-lucide-calendar" class="w-4 h-4 text-gray-400" />
            </div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(selectedMember.created_at) }}</p>
          </div>

          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
            <div class="flex items-center justify-between mb-1">
              <span class="text-sm text-gray-500 dark:text-gray-400">UUID</span>
              <UIcon name="i-lucide-fingerprint" class="w-4 h-4 text-gray-400" />
            </div>
            <p class="text-xs font-mono text-gray-900 dark:text-white">{{ selectedMember.uuid }}</p>
          </div>
        </div>
      </div>
    </USlideover>
  </div>
</template>

<style scoped>
@reference "tailwindcss";

.glass-card {
  @apply bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm;
}
</style>
