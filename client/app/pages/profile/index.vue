<script setup lang="ts">
/**
 * Profile Dashboard - Premium Mintreu Design
 * Displays user identity, status, and business metrics (PV, Rank, Directs)
 */

import type { GenealogyData } from '~/types/user'

definePageMeta({
  middleware: '$auth',
  title: 'My Profile',
  layout: 'dashboard'
})

const { user, getUserTypeLabel, getUserTypeBadgeColor } = useUserType()
const { formatCurrency, formatDate } = useBranding()

// Computed helpers
const kycStatusColor = computed(() => {
  if (!user.value) return 'gray'
  const map: Record<string, string> = { verified: 'emerald', pending: 'amber', rejected: 'red', not_submitted: 'slate' }
  return map[user.value.kyc_status] || 'gray'
})

const kycLabel = computed(() => {
  if (!user.value) return 'Checking...'
  const map: Record<string, string> = { verified: 'Verified', pending: 'Reviewing', rejected: 'Rejected', not_submitted: 'Unverified' }
  return map[user.value.kyc_status] || 'Unknown'
})

const genealogy = computed<GenealogyData | null>(() => user.value?.genealogy || null)
const currentLevel = computed(() => genealogy.value?.level)
const currentStage = computed(() => genealogy.value?.stage)

// Format PV with suffix
const formatPV = (pv: number | undefined) => {
  if (!pv) return '0 PV'
  if (pv >= 1000) return `${(pv / 1000).toFixed(1)}k PV`
  return `${pv} PV`
}

// Business metrics computed from genealogy
const businessStats = computed(() => {
  const g = genealogy.value
  if (!g) {
    return [
      { label: 'Direct Referrals', value: user.value?.team_summary?.direct_count || 0, icon: 'i-lucide-users', color: 'blue' },
      { label: 'Network Points', value: '0 PV', icon: 'i-lucide-zap', color: 'amber' },
      { label: 'Commission Earned', value: formatCurrency(0), icon: 'i-lucide-award', color: 'emerald' }
    ]
  }

  return [
    { label: 'Direct Referrals', value: g.direct_count, icon: 'i-lucide-users', color: 'blue' },
    { label: 'Team Points (PV)', value: formatPV(g.team_pv), icon: 'i-lucide-zap', color: 'amber' },
    { label: 'Total Earned', value: formatCurrency(g.total_team_sales), icon: 'i-lucide-award', color: 'emerald' }
  ]
})

// Network breakdown stats
const networkStats = computed(() => {
  const g = genealogy.value
  if (!g) return []

  return [
    { label: 'Level 1', value: g.level_1_count, icon: 'i-lucide-circle-1' },
    { label: 'Level 2', value: g.level_2_count, icon: 'i-lucide-circle-2' },
    { label: 'Level 3', value: g.level_3_count, icon: 'i-lucide-circle-3' },
    { label: 'Level 4', value: g.level_4_count, icon: 'i-lucide-circle-4' },
  ]
})

// PV Progress data
const pvProgress = computed(() => {
  const g = genealogy.value
  if (!g || !currentStage.value) return { personal: { current: 0, target: 100, percent: 0 }, team: { current: 0, target: 100, percent: 0 } }

  const targetPV = currentStage.value.pv || 100

  return {
    personal: {
      current: g.personal_pv,
      target: targetPV,
      percent: Math.min((g.personal_pv / targetPV) * 100, 100)
    },
    team: {
      current: g.team_pv,
      target: targetPV * 10, // Team target is typically 10x personal
      percent: Math.min((g.team_pv / (targetPV * 10)) * 100, 100)
    }
  }
})

// Rank badge color mapping
const getRankColor = (color: string | null) => {
  if (!color) return 'primary'
  const map: Record<string, string> = {
    amber: 'amber', emerald: 'emerald', blue: 'blue', purple: 'purple',
    rose: 'rose', indigo: 'indigo', cyan: 'cyan', pink: 'pink'
  }
  return map[color] || 'primary'
}

// Get rank icon based on level name
const getRankIcon = (name: string | undefined) => {
  if (!name) return 'i-lucide-medal'
  const lower = name.toLowerCase()
  if (lower.includes('bronze')) return 'i-lucide-medal'
  if (lower.includes('silver')) return 'i-lucide-medal'
  if (lower.includes('gold')) return 'i-lucide-crown'
  if (lower.includes('diamond')) return 'i-lucide-gem'
  return 'i-lucide-medal'
}

const formatGender = (gender: string | null) => {
  if (!gender) return 'N/A'
  return gender.charAt(0).toUpperCase() + gender.slice(1)
}
</script>

<template>
  <div class="max-w-7xl mx-auto space-y-8 pb-12">
    <!-- Profile Hero Section -->
    <div class="relative overflow-hidden group">
      <!-- Background Ambient Effects -->
      <div class="absolute -top-32 -left-32 w-96 h-96 bg-primary-500/10 blur-3xl rounded-full" />
      <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-indigo-500/10 blur-3xl rounded-full" />

      <div class="relative glass-card border-none p-10 bg-white/40 dark:bg-slate-900/40 backdrop-blur-2xl">
        <div class="flex flex-col lg:flex-row items-center lg:items-start gap-10">
          <!-- Avatar Section -->
          <div class="relative shrink-0">
            <div class="w-40 h-40 rounded-[48px] bg-gradient-to-tr from-primary-600 to-indigo-600 p-1 shadow-2xl shadow-primary-500/20 group-hover:rotate-2 transition-transform duration-500">
               <div class="w-full h-full rounded-[45px] overflow-hidden border-4 border-white dark:border-slate-900 bg-white dark:bg-slate-800">
                 <img
                   :src="user?.avatar || 'https://api.dicebear.com/7.x/avataaars/svg?seed=' + user?.name"
                   class="w-full h-full object-cover"
                 />
               </div>
            </div>
            <NuxtLink
              to="/profile/edit"
              class="absolute -bottom-2 -right-2 w-12 h-12 bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 rounded-2xl flex items-center justify-center shadow-xl border border-slate-100 dark:border-slate-700 hover:scale-110 active:scale-95 transition-all"
            >
              <UIcon name="i-lucide-camera-plus" class="w-6 h-6" />
            </NuxtLink>
          </div>

          <!-- Identity Details -->
          <div class="flex-1 text-center lg:text-left space-y-4 min-w-0">
            <div class="space-y-1">
              <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3">
                <h1 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight truncate">
                  {{ user?.name }}
                </h1>
                <UBadge
                  :color="getUserTypeBadgeColor()"
                  variant="solid"
                  class="rounded-full px-4 text-[10px] font-black uppercase tracking-widest"
                >
                  {{ getUserTypeLabel() }}
                </UBadge>
                <div :class="`inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-${kycStatusColor}-500/10 border border-${kycStatusColor}-500/20 text-${kycStatusColor}-600 dark:text-${kycStatusColor}-400 text-[10px] font-black uppercase tracking-widest`">
                   <UIcon :name="user?.kyc_status === 'verified' ? 'i-lucide-check-circle' : 'i-lucide-shield-alert'" />
                   {{ kycLabel }} Identity
                </div>
              </div>
              <p class="text-slate-500 dark:text-slate-400 font-medium text-lg italic max-w-2xl truncate">
                {{ user?.bio || 'Building financial resilience and community-driven success.' }}
              </p>
            </div>

            <!-- Meta Badges -->
            <div class="flex flex-wrap justify-center lg:justify-start gap-6 pt-2">
              <div class="flex items-center gap-2 text-sm font-bold text-slate-600 dark:text-slate-300">
                <div class="p-2 bg-slate-100 dark:bg-slate-800 rounded-xl">
                  <UIcon name="i-lucide-hash" class="w-4 h-4 text-primary-500" />
                </div>
                <span>UID: {{ user?.uuid.slice(0,8).toUpperCase() }}</span>
              </div>
              <div class="flex items-center gap-2 text-sm font-bold text-slate-600 dark:text-slate-300">
                <div class="p-2 bg-slate-100 dark:bg-slate-800 rounded-xl">
                  <UIcon name="i-lucide-ticket" class="w-4 h-4 text-amber-500" />
                </div>
                <span>Ref: {{ user?.referral_code }}</span>
              </div>
              <div class="flex items-center gap-2 text-sm font-bold text-slate-600 dark:text-slate-300">
                <div class="p-2 bg-slate-100 dark:bg-slate-800 rounded-xl">
                  <UIcon name="i-lucide-calendar-heart" class="w-4 h-4 text-pink-500" />
                </div>
                <span>Joined {{ formatDate(user?.created_at || new Date(), 'short') }}</span>
              </div>
            </div>
          </div>

          <div class="shrink-0 flex gap-3">
             <UButton to="/profile/edit" color="primary" variant="soft" size="xl" class="rounded-2xl font-black px-6">
                <template #leading><UIcon name="i-lucide-user-cog" /></template>
                Settings
             </UButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Business Metrics Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
       <div
         v-for="stat in businessStats"
         :key="stat.label"
         class="glass-card p-8 border-none ring-1 ring-slate-200 dark:ring-slate-800 group hover:ring-primary-500/50 transition-all duration-300"
       >
          <div class="flex items-center justify-between mb-4">
             <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{ stat.label }}</span>
             <div :class="`p-3 bg-${stat.color}-500/10 text-${stat.color}-500 rounded-2xl group-hover:scale-110 group-hover:bg-${stat.color}-500 group-hover:text-white transition-all duration-500`">
                <UIcon :name="stat.icon" class="w-6 h-6" />
             </div>
          </div>
          <p class="text-3xl font-black text-slate-900 dark:text-white">{{ stat.value }}</p>
       </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
      <!-- Left Column: Personal Data -->
      <div class="lg:col-span-3 space-y-8">
        <div class="glass-card p-0 border-none ring-1 ring-slate-200 dark:ring-slate-800 overflow-hidden">
          <div class="p-8 bg-slate-50/50 dark:bg-slate-900/30 border-b border-slate-200/50 dark:border-slate-800 flex items-center gap-3">
             <UIcon name="i-lucide-fingerprint" class="w-6 h-6 text-primary-600" />
             <h3 class="text-lg font-black text-slate-900 dark:text-white">Account Dossier</h3>
          </div>
          <div class="p-8 grid md:grid-cols-2 gap-x-12 gap-y-8">
             <div v-for="field in [
               { label: 'Official Name', value: user?.name, icon: 'i-lucide-user' },
               { label: 'Mobile Link', value: user?.mobile, icon: 'i-lucide-smartphone' },
               { label: 'Primary Email', value: user?.email, icon: 'i-lucide-mail' },
               { label: 'Gender', value: formatGender(user?.gender), icon: 'i-lucide-venus-mars' },
               { label: 'Date of Birth', value: user?.dob ? formatDate(user.dob) : 'N/A', icon: 'i-lucide-cake' },
               { label: 'KYC Eligibility', value: kycLabel.value, icon: 'i-lucide-shield-check' }
             ]" :key="field.label">
                <div class="flex items-start gap-4">
                   <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800/50 flex items-center justify-center text-slate-400">
                      <UIcon :name="field.icon" class="w-5 h-5" />
                   </div>
                   <div class="space-y-0.5">
                      <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ field.label }}</span>
                      <p class="font-bold text-slate-900 dark:text-white">{{ field.value || 'Not Configured' }}</p>
                   </div>
                </div>
             </div>
          </div>
        </div>

        <!-- Security & Compliance Shortcuts -->
        <div class="grid sm:grid-cols-2 gap-6">
           <NuxtLink to="/profile/kyc" class="glass-card p-6 border-none ring-1 ring-slate-200 dark:ring-slate-800 hover:bg-white dark:hover:bg-slate-800 transition-all group">
              <div class="flex items-center gap-4">
                 <div class="w-12 h-12 bg-emerald-500/10 text-emerald-500 rounded-2xl flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-all">
                    <UIcon name="i-lucide-user-check" class="w-6 h-6" />
                 </div>
                 <div>
                    <h4 class="font-black text-slate-900 dark:text-white leading-none mb-1 text-sm">Identity Verification</h4>
                    <p class="text-xs text-slate-500 font-medium">Verify your PAN & Aadhaar</p>
                 </div>
              </div>
           </NuxtLink>

           <NuxtLink to="/profile/change-password" class="glass-card p-6 border-none ring-1 ring-slate-200 dark:ring-slate-800 hover:bg-white dark:hover:bg-slate-800 transition-all group">
              <div class="flex items-center gap-4">
                 <div class="w-12 h-12 bg-amber-500/10 text-amber-500 rounded-2xl flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-all">
                    <UIcon name="i-lucide-shield-half" class="w-6 h-6" />
                 </div>
                 <div>
                    <h4 class="font-black text-slate-900 dark:text-white leading-none mb-1 text-sm">Vault Security</h4>
                    <p class="text-xs text-slate-500 font-medium">Update access keys & PIN</p>
                 </div>
              </div>
           </NuxtLink>
        </div>
      </div>

      <!-- Right Column: Rank & Hierarchy -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Rank Card -->
        <div class="glass-card relative overflow-hidden p-8 border-none bg-slate-900 text-white shadow-2xl">
           <div class="absolute inset-0 bg-gradient-to-br from-primary-600/20 to-purple-600/20" />
           <h3 class="text-xs font-black uppercase tracking-[0.3em] mb-6 relative z-10 text-white/50 text-center">Current Rank</h3>

           <div class="flex flex-col items-center gap-4 relative z-10">
              <!-- Rank Badge -->
              <div
                v-if="currentLevel"
                :class="`w-32 h-32 rounded-[40px] bg-gradient-to-br from-${getRankColor(currentLevel.badge_color)}-500 to-${getRankColor(currentLevel.badge_color)}-600 p-1 mx-auto shadow-2xl shadow-${getRankColor(currentLevel.badge_color)}-500/30 animate-pulse-slow`"
              >
                 <div class="w-full h-full rounded-[38px] bg-slate-900 flex items-center justify-center p-2">
                    <UIcon :name="getRankIcon(currentLevel.name)" :class="`w-16 h-16 text-${getRankColor(currentLevel.badge_color)}-400`" />
                 </div>
              </div>
              <div v-else class="w-32 h-32 rounded-[40px] bg-gradient-to-br from-slate-600 to-slate-700 p-1 mx-auto shadow-2xl">
                 <div class="w-full h-full rounded-[38px] bg-slate-900 flex items-center justify-center p-2">
                    <UIcon name="i-lucide-medal" class="w-16 h-16 text-slate-500" />
                 </div>
              </div>

              <div class="text-center">
                 <span class="text-[10px] font-black uppercase tracking-[0.2em] text-white/40 block mb-1">Your Level</span>
                 <p class="text-2xl font-black">{{ currentLevel?.full_name || 'No Rank Yet' }}</p>
                 <p v-if="currentStage" class="text-xs font-bold text-white/60 uppercase tracking-widest mt-1">{{ currentStage.name }} Stage</p>
                 <p v-else class="text-xs font-bold text-white/60 uppercase tracking-widest mt-1">Subscribe to unlock</p>
              </div>

              <!-- Rank Number -->
              <div v-if="currentLevel" class="flex items-center gap-2 px-4 py-2 bg-white/5 rounded-full">
                 <UIcon name="i-lucide-hash" class="w-4 h-4 text-primary-400" />
                 <span class="text-sm font-black text-primary-400">Rank {{ currentLevel.global_rank }}</span>
              </div>
           </div>
        </div>

        <!-- PV Progress -->
        <div v-if="genealogy" class="glass-card p-8 border-none ring-1 ring-slate-200 dark:ring-slate-800">
           <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-6">Progress to Next Level</h3>

           <!-- Personal PV -->
           <div class="space-y-3 mb-6">
              <div class="flex justify-between items-center text-xs">
                 <span class="font-black text-slate-900 dark:text-white uppercase flex items-center gap-2">
                    <UIcon name="i-lucide-user" class="w-4 h-4" /> Personal PV
                 </span>
                 <span class="font-bold text-slate-400">{{ pvProgress.personal.current }} / {{ pvProgress.personal.target }}</span>
              </div>
              <div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                 <div
                   class="h-full bg-gradient-to-r from-primary-600 to-primary-400 rounded-full transition-all duration-1000"
                   :style="{ width: `${pvProgress.personal.percent}%` }"
                 />
              </div>
           </div>

           <!-- Team PV -->
           <div class="space-y-3">
              <div class="flex justify-between items-center text-xs">
                 <span class="font-black text-slate-900 dark:text-white uppercase flex items-center gap-2">
                    <UIcon name="i-lucide-users" class="w-4 h-4" /> Team PV
                 </span>
                 <span class="font-bold text-slate-400">{{ pvProgress.team.current }} / {{ pvProgress.team.target }}</span>
              </div>
              <div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                 <div
                   class="h-full bg-gradient-to-r from-amber-600 to-amber-400 rounded-full transition-all duration-1000"
                   :style="{ width: `${pvProgress.team.percent}%` }"
                 />
              </div>
           </div>
        </div>

        <!-- Network Breakdown -->
        <div v-if="genealogy" class="glass-card p-8 border-none ring-1 ring-slate-200 dark:ring-slate-800">
           <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-6">Network Breakdown</h3>
           <div class="grid grid-cols-2 gap-4">
              <div v-for="stat in networkStats" :key="stat.label" class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                 <UIcon :name="stat.icon" class="w-5 h-5 text-primary-500" />
                 <div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 block">{{ stat.label }}</span>
                    <span class="text-lg font-black text-slate-900 dark:text-white">{{ stat.value }}</span>
                 </div>
              </div>
           </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-pulse-slow {
  animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; filter: drop-shadow(0 0 0px rgba(79, 70, 229, 0)); }
  50% { opacity: 0.9; filter: drop-shadow(0 0 20px rgba(79, 70, 229, 0.4)); }
}
</style>
