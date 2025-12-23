<script setup lang="ts">
/**
 * Network/Community Page
 *
 * Premium network visualization with:
 * - Org chart view (d3-org-chart)
 * - List view with member details
 * - Referral link sharing
 * - Stats overview
 * - Filter by referral code
 */

definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const config = useRuntimeConfig()
const { user } = useSanctum()
const toast = useToast()
const { treeData, stats, isLoading, currentReferralCode, totalMembers, activeMembers, maxDepth, currentViewingName, fetchTree, fetchStats, resetToOwnTree, viewMemberTree } = useMlmTree()

// State
const currentView = ref<'chart' | 'list'>('list')
const filterReferralCode = ref('')
const copied = ref(false)
const drawerOpen = ref(false)
const selectedMember = ref<any>(null)
const chartRef = ref<HTMLElement | null>(null)

let chartInstance: any = null

// Computed
const affiliateLink = computed(() => {
  const baseUrl = config.public.appUrl || window.location.origin
  const code = user.value?.referral_code || 'XXXXX'
  return `${baseUrl}/auth/register?ref=${code}`
})

// Methods
const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(affiliateLink.value)
    copied.value = true
    toast.add({
      title: 'Copied!',
      description: 'Referral link copied to clipboard',
      color: 'success'
    })
    setTimeout(() => (copied.value = false), 2000)
  } catch {
    toast.add({
      title: 'Error',
      description: 'Failed to copy link',
      color: 'error'
    })
  }
}

const applyFilter = async () => {
  if (filterReferralCode.value.trim()) {
    await viewMemberTree(filterReferralCode.value.trim())
    if (currentView.value === 'chart') {
      await nextTick()
      initChart()
    }
  }
}

const resetFilter = async () => {
  filterReferralCode.value = ''
  await resetToOwnTree()
  if (currentView.value === 'chart') {
    await nextTick()
    initChart()
  }
}

const selectMember = (member: any) => {
  selectedMember.value = member
  drawerOpen.value = true
}

const closeDrawer = () => {
  drawerOpen.value = false
}

const viewMemberNetwork = async (referralCode: string) => {
  filterReferralCode.value = referralCode
  closeDrawer()
  await viewMemberTree(referralCode)
  if (currentView.value === 'chart') {
    await nextTick()
    initChart()
  }
}

const getDefaultAvatar = (name: string) => {
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&size=80&background=8B5CF6&color=fff&bold=true`
}

const initChart = async () => {
  if (!chartRef.value || treeData.value.length === 0) return

  const { OrgChart } = await import('d3-org-chart')

  const isDark = document.documentElement.classList.contains('dark')

  chartInstance = new OrgChart()
    .container(chartRef.value)
    .data(treeData.value)
    .nodeHeight(() => 120)
    .nodeWidth(() => 220)
    .childrenMargin(() => 50)
    .compactMarginBetween(() => 35)
    .compactMarginPair(() => 30)
    .neighbourMargin(() => 20)
    .nodeContent((d: any) => {
      const member = d.data
      const bg = isDark ? '#1E293B' : '#ffffff'
      const text = isDark ? '#F8FAFC' : '#1E293B'
      const border = isDark ? '#334155' : '#E2E8F0'
      const accent = '#8B5CF6'

      return `
        <div style="width:${d.width}px;height:${d.height}px;padding:12px;font-family:system-ui;
                    border-radius:16px;border:1px solid ${border};background:${bg};color:${text};
                    box-shadow:0 4px 12px rgba(0,0,0,0.1);">
          <div style="display:flex;align-items:center;gap:12px;">
            <img src="${member.image || getDefaultAvatar(member.name)}"
                 style="border-radius:50%;width:48px;height:48px;object-fit:cover;border:2px solid ${accent};" />
            <div style="flex:1;min-width:0;">
              <div style="font-weight:600;font-size:14px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${member.name}</div>
              <div style="color:#8B5CF6;font-size:11px;margin-top:2px;font-weight:500;">${member.level}</div>
              <div style="color:#94A3B8;font-size:10px;font-family:monospace;margin-top:2px;">${member.referral_code}</div>
            </div>
          </div>
          ${member.depth > 0 ? `<div style="position:absolute;top:8px;right:8px;background:linear-gradient(135deg,#8B5CF6,#D946EF);color:white;padding:2px 10px;border-radius:12px;font-size:10px;font-weight:600;">L${member.depth}</div>` : ''}
        </div>
      `
    })
    .onNodeClick((d: any) => {
      selectMember(d.data)
    })

  await nextTick()
  chartInstance.render()
}

// Lifecycle
onMounted(async () => {
  await Promise.all([fetchTree(), fetchStats()])
})

// Watch view changes
watch(currentView, async (newVal) => {
  if (newVal === 'chart') {
    await nextTick()
    if (treeData.value.length > 0) {
      initChart()
    }
  }
})
</script>

<template>
  <div class="max-w-7xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Network</h1>
        <p class="text-gray-500 dark:text-gray-400">
          Total Members: <span class="font-semibold text-violet-600 dark:text-violet-400">{{ totalMembers }}</span>
        </p>
      </div>

      <!-- View Toggle -->
      <div class="flex items-center gap-2 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl">
        <button
          @click="currentView = 'chart'"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition flex items-center gap-2',
            currentView === 'chart'
              ? 'bg-white dark:bg-gray-700 text-violet-600 dark:text-violet-400 shadow-sm'
              : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
          ]"
        >
          <UIcon name="i-lucide-git-branch" class="w-4 h-4" />
          <span class="hidden sm:inline">Chart</span>
        </button>
        <button
          @click="currentView = 'list'"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition flex items-center gap-2',
            currentView === 'list'
              ? 'bg-white dark:bg-gray-700 text-violet-600 dark:text-violet-400 shadow-sm'
              : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
          ]"
        >
          <UIcon name="i-lucide-list" class="w-4 h-4" />
          <span class="hidden sm:inline">List</span>
        </button>
      </div>
    </div>

    <!-- Referral Link Card -->
    <div class="bg-gradient-to-r from-violet-600 to-fuchsia-600 rounded-2xl shadow-xl p-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="flex-1">
          <h3 class="text-lg font-semibold mb-2">Your Referral Link</h3>
          <div class="flex items-center gap-2 bg-white/20 backdrop-blur rounded-xl p-3">
            <input
              :value="affiliateLink"
              readonly
              class="flex-1 bg-transparent border-none outline-none text-sm font-mono text-white placeholder-white/70"
            />
            <UButton
              @click="copyLink"
              color="white"
              variant="solid"
              size="sm"
            >
              {{ copied ? 'Copied!' : 'Copy' }}
            </UButton>
          </div>
        </div>
        <div class="text-center">
          <div class="text-4xl font-bold">{{ user?.referral_code }}</div>
          <div class="text-sm text-white/80">Your Code</div>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="glass-card p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Members</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ totalMembers }}</p>
          </div>
          <div class="icon-box icon-box-md icon-box-soft-primary">
            <UIcon name="i-lucide-users" class="w-5 h-5" />
          </div>
        </div>
      </div>

      <div class="glass-card p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Active</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ activeMembers }}</p>
          </div>
          <div class="icon-box icon-box-md icon-box-soft-success">
            <UIcon name="i-lucide-check-circle" class="w-5 h-5" />
          </div>
        </div>
      </div>

      <div class="glass-card p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Max Depth</p>
            <p class="text-2xl font-bold text-violet-600 dark:text-violet-400 mt-1">{{ maxDepth }}</p>
          </div>
          <div class="icon-box icon-box-md icon-box-soft-secondary">
            <UIcon name="i-lucide-layers" class="w-5 h-5" />
          </div>
        </div>
      </div>

      <div class="glass-card p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Viewing</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1 truncate">{{ currentViewingName }}</p>
          </div>
          <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
            <UIcon name="i-lucide-eye" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
          </div>
        </div>
      </div>
    </div>

    <!-- Filter -->
    <div class="glass-card p-4">
      <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            View Member's Network
          </label>
          <div class="flex gap-2">
            <UInput
              v-model="filterReferralCode"
              placeholder="Enter referral code..."
              class="flex-1"
              @keyup.enter="applyFilter"
            />
            <UButton
              @click="applyFilter"
              :loading="isLoading"
              color="primary"
            >
              Apply
            </UButton>
            <UButton
              v-if="currentReferralCode"
              @click="resetFilter"
              color="neutral"
              variant="outline"
            >
              Reset
            </UButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center py-12">
      <UIcon name="i-lucide-loader-2" class="w-8 h-8 animate-spin text-violet-500" />
    </div>

    <!-- Chart View -->
    <div v-else-if="currentView === 'chart'" class="glass-card overflow-hidden">
      <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Organization Chart</h3>
      </div>
      <div v-if="treeData.length > 0" class="relative" style="min-height: 500px;">
        <div ref="chartRef" id="chart-container" class="overflow-auto p-4"></div>
      </div>
      <div v-else class="p-12 text-center">
        <UIcon name="i-lucide-git-branch" class="w-16 h-16 mx-auto text-gray-400 mb-4" />
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Network Data</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6">Share your referral link to start building your network.</p>
      </div>
    </div>

    <!-- List View -->
    <div v-else-if="currentView === 'list'" class="glass-card overflow-hidden">
      <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Members List</h3>
      </div>

      <div v-if="treeData.length > 0" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Member</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Level</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Depth</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="member in treeData"
              :key="member.userId"
              class="hover:bg-gray-50 dark:hover:bg-gray-800 transition cursor-pointer"
              @click="selectMember(member)"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <img :src="member.image || getDefaultAvatar(member.name)" class="w-10 h-10 rounded-full" />
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ member.name }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ member.referral_code }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <UBadge color="violet" variant="subtle" size="sm">{{ member.level }}</UBadge>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                Level {{ member.depth }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <UBadge v-if="member.hasChildren" color="success" variant="subtle" size="sm">Active</UBadge>
                <UBadge v-else color="neutral" variant="subtle" size="sm">No Downline</UBadge>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <UButton
                  @click.stop="viewMemberNetwork(member.referral_code)"
                  variant="link"
                  color="primary"
                  size="sm"
                >
                  View Network
                </UButton>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="p-12 text-center">
        <UIcon name="i-lucide-users" class="w-16 h-16 mx-auto text-gray-400 mb-4" />
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Members Yet</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6">Share your referral link to start building your network.</p>
      </div>
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
            :src="selectedMember.image || getDefaultAvatar(selectedMember.name)"
            class="w-24 h-24 rounded-full mx-auto border-4 border-violet-200 dark:border-violet-800 shadow-lg"
          />
          <h4 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">{{ selectedMember.name }}</h4>
          <p class="text-sm text-gray-500 dark:text-gray-400 font-mono mt-1">{{ selectedMember.referral_code }}</p>
          <UBadge color="violet" class="mt-2">{{ selectedMember.level }}</UBadge>
        </div>

        <!-- Info Cards -->
        <div class="space-y-3">
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
            <div class="flex items-center justify-between mb-1">
              <span class="text-sm text-gray-500 dark:text-gray-400">Email</span>
              <UIcon name="i-lucide-mail" class="w-4 h-4 text-gray-400" />
            </div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ selectedMember.email || 'N/A' }}</p>
          </div>

          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
            <div class="flex items-center justify-between mb-1">
              <span class="text-sm text-gray-500 dark:text-gray-400">Joined On</span>
              <UIcon name="i-lucide-calendar" class="w-4 h-4 text-gray-400" />
            </div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ selectedMember.joinedOn }}</p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 text-center">
              <UIcon name="i-lucide-layers" class="w-6 h-6 text-violet-600 mx-auto mb-2" />
              <p class="text-xs text-gray-500 dark:text-gray-400">Network Depth</p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">Level {{ selectedMember.depth }}</p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 text-center">
              <UIcon name="i-lucide-users" class="w-6 h-6 text-green-600 mx-auto mb-2" />
              <p class="text-xs text-gray-500 dark:text-gray-400">Downline</p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">
                {{ selectedMember.hasChildren ? 'Active' : 'None' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <UButton
          @click="viewMemberNetwork(selectedMember.referral_code)"
          color="primary"
          block
          size="lg"
          class="mt-4"
        >
          <UIcon name="i-lucide-git-branch" class="w-5 h-5 mr-2" />
          View Their Network
        </UButton>
      </div>
    </USlideover>
  </div>
</template>


