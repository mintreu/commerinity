<script setup lang="ts">
/**
 * DashboardHeader - Welcome section with user info and level badge
 * Shows personalized greeting, user type badge, and optional level indicator
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

const greeting = computed(() => getGreeting(props.user?.name?.split(' ')[0]))
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
  if (props.user.email_verified) completed++

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
  <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
    <!-- Left: Greeting & Info -->
    <div class="flex items-start gap-4">
      <!-- Avatar -->
      <div class="relative flex-shrink-0">
        <UAvatar
          :src="user?.avatar"
          :alt="user?.name || 'User'"
          size="xl"
        />
        <!-- Online indicator -->
        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white dark:border-slate-800" />
      </div>

      <!-- Text -->
      <div>
        <h1 class="text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white">
          {{ greeting }}
        </h1>
        <p class="text-slate-600 dark:text-slate-400 mt-1">
          {{ today }}
        </p>

        <!-- Type Badge -->
        <div class="flex items-center gap-2 mt-2">
          <UBadge
            :color="typeBadgeColor as any"
            variant="soft"
            size="sm"
          >
            {{ typeLabel }}
          </UBadge>

          <!-- Level Badge (if applicable) -->
          <UBadge
            v-if="showLevel && user?.hasLevel"
            color="warning"
            variant="soft"
            size="sm"
          >
            <UIcon
              name="i-lucide-crown"
              class="w-3 h-3 mr-1"
            />
            Level {{ user.level_id }}
          </UBadge>
        </div>
      </div>
    </div>

    <!-- Right: Actions & Progress -->
    <div class="flex items-center gap-4">
      <!-- Onboarding Progress (if not complete) -->
      <div
        v-if="showOnboardingProgress && user && !user.onboarded"
        class="flex items-center gap-3 px-4 py-2 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800"
      >
        <CommonProgressRing
          :progress="onboardingProgress"
          size="sm"
          color="warning"
          :show-percentage="true"
        />
        <div>
          <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
            Profile Setup
          </p>
          <NuxtLink
            to="/onboarding"
            class="text-xs text-amber-600 dark:text-amber-400 hover:underline"
          >
            Continue setup
          </NuxtLink>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="flex items-center gap-2">
        <UTooltip text="Notifications">
          <UButton
            icon="i-lucide-bell"
            variant="ghost"
            color="neutral"
            size="lg"
            class="relative"
          >
            <!-- Notification dot -->
            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full" />
          </UButton>
        </UTooltip>

        <UTooltip text="Settings">
          <UButton
            icon="i-lucide-settings"
            variant="ghost"
            color="neutral"
            size="lg"
            to="/profile"
          />
        </UTooltip>
      </div>
    </div>
  </div>
</template>
