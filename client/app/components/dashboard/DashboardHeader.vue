<script setup lang="ts">
/**
 * DashboardHeader - Welcome section with user info and level badge
 * Shows personalized greeting, user type badge, and optional level indicator
 * PWA-optimized mobile-first design
 */

import type { User } from '~/types/user'
import { KycStatus } from '~/types/user'

interface Props {
  user: User | null
  showLevel?: boolean
  showOnboardingProgress?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  showLevel: false,
  showOnboardingProgress: true
})

const { getGreeting, formatDate } = useBranding()
const { getUserTypeLabel, getUserTypeBadgeColor } = useUserType()

const greeting = computed(() => {
  const hour = new Date().getHours()
  const firstName = props.user?.name?.split(' ')[0] || 'User'

  let timeStr = 'Morning'
  if (hour >= 12 && hour < 17) timeStr = 'Afternoon'
  else if (hour >= 17 && hour < 22) timeStr = 'Evening'
  else if (hour >= 22 || hour < 5) timeStr = 'Night'

  return `Good ${timeStr}, ${firstName}!`
})
const today = computed(() => formatDate(new Date(), 'long'))
const typeLabel = computed(() => getUserTypeLabel())
const typeBadgeColor = computed(() => getUserTypeBadgeColor())

// Calculate onboarding progress
const onboardingProgress = computed(() => {
  if (!props.user) return 0
  let steps = 0
  let completed = 0

  // Profile basics
  steps++
  if (props.user.name && props.user.mobile) completed++

  // Email verified
  steps++
  if (props.user.email_verified_at) completed++

  // Gender & DOB
  steps++
  if (props.user.gender && props.user.dob) completed++

  // KYC
  steps++
  if (props.user.kyc_status === KycStatus.VERIFIED) completed++

  return Math.round((completed / steps) * 100)
})
</script>

<template>
  <div class="glass-card p-4 md:p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <!-- Left: User Info -->
      <div class="flex items-center gap-3 md:gap-4">
        <!-- Avatar -->
        <div class="relative shrink-0">
          <UAvatar
            :src="user?.avatar"
            :alt="user?.name || 'User'"
            size="lg"
            class="ring-2 ring-violet-200 dark:ring-violet-800"
          />
          <!-- Online indicator -->
          <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 rounded-full border-2 border-white dark:border-slate-800" />
        </div>

        <!-- Text -->
        <div class="min-w-0">
          <h1 class="text-lg md:text-2xl font-bold text-slate-900 dark:text-white truncate">
            {{ greeting }}
          </h1>
          <p class="text-sm text-slate-500 dark:text-slate-400 hidden md:block">
            {{ today }}
          </p>

          <!-- Badges Row -->
          <div class="flex items-center gap-2 mt-1.5">
            <UBadge
              :color="typeBadgeColor as any"
              variant="soft"
              size="xs"
            >
              {{ typeLabel }}
            </UBadge>

            <!-- Level Badge (if applicable) -->
            <UBadge
              v-if="showLevel && user?.current_level"
              color="warning"
              variant="soft"
              size="xs"
            >
              <UIcon name="i-lucide-crown" class="w-3 h-3 mr-1" />
              {{ user.current_level.name }}
            </UBadge>
          </div>
        </div>
      </div>

      <!-- Right: Onboarding Progress -->
      <div
        v-if="showOnboardingProgress && user && !user.onboarded"
        class="flex items-center gap-3"
      >
        <NuxtLink
          to="/onboarding"
          class="flex items-center gap-3 px-3 py-2 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/30 dark:to-orange-900/30 rounded-xl border border-amber-200/50 dark:border-amber-700/50 active:scale-[0.98] transition-transform"
        >
          <CommonProgressRing
            :progress="onboardingProgress"
            size="sm"
            color="warning"
            :show-percentage="false"
          />
          <div>
            <p class="text-sm font-semibold text-amber-800 dark:text-amber-200">
              {{ onboardingProgress }}% Complete
            </p>
            <p class="text-xs text-amber-600 dark:text-amber-400">
              Continue setup
            </p>
          </div>
          <UIcon name="i-lucide-chevron-right" class="w-4 h-4 text-amber-500" />
        </NuxtLink>
      </div>
    </div>
  </div>
</template>
