<script setup lang="ts">
/**
 * UserJourneyCard - Upgrade prompts and journey progression
 * Shows personalized upgrade paths based on user type
 * Gamification-style presentation to encourage progression
 */

import { UserType } from '~/types/user'
import type { User } from '~/types/user'

interface Props {
  user: User | null
}

const props = defineProps<Props>()

const { formatCurrency } = useBranding()

interface JourneyStep {
  title: string
  description: string
  icon: string
  benefits: string[]
  actionLabel: string
  actionTo: string
  highlight?: boolean
  price?: number
}

// Define journey steps based on current user type
const journeySteps = computed((): JourneyStep[] => {
  if (!props.user) return []

  switch (props.user.type) {
    case UserType.REGULAR:
      return [{
        title: 'Become a Member',
        description: 'Unlock exclusive benefits and start earning!',
        icon: 'i-lucide-crown',
        benefits: [
          'Enjoy discount on all products',
          'Earn commissions by referring',
          'Access to member-only deals',
          'Priority customer support'
        ],
        actionLabel: 'Subscribe Now',
        actionTo: '/subscription',
        highlight: true,
        price: 299
      }]

    case UserType.MEMBER:
      return [{
        title: 'Become a Promoter',
        description: 'Build your team and earn team bonuses!',
        icon: 'i-lucide-users',
        benefits: [
          'Team building rewards',
          'Higher commission rates',
          'Leadership bonuses',
          'Marketing tools access'
        ],
        actionLabel: 'Upgrade Now',
        actionTo: '/upgrade/promoter',
        highlight: true
      }]

    case UserType.PROMOTER:
      return [{
        title: 'Become an Advisor',
        description: 'Train others and earn recruitment bonuses!',
        icon: 'i-lucide-graduation-cap',
        benefits: [
          'Recruitment commissions',
          'Training incentives',
          'Regional leadership',
          'Advanced analytics'
        ],
        actionLabel: 'Apply Now',
        actionTo: '/careers/advisor',
        highlight: true
      }]

    default:
      return []
  }
})

const showCard = computed(() => {
  return props.user?.features.show_upgrade_prompt && journeySteps.value.length > 0
})
</script>

<template>
  <div
    v-if="showCard"
    class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-6 text-white"
  >
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white" />
      <div class="absolute -bottom-10 -left-10 w-32 h-32 rounded-full bg-white" />
    </div>

    <!-- Content -->
    <div
      v-for="step in journeySteps"
      :key="step.title"
      class="relative z-10"
    >
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <!-- Left: Info -->
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
              <UIcon
                :name="step.icon"
                class="w-6 h-6 text-white"
              />
            </div>
            <div>
              <h3 class="text-xl font-bold">
                {{ step.title }}
              </h3>
              <p class="text-white/80 text-sm">
                {{ step.description }}
              </p>
            </div>
          </div>

          <!-- Benefits -->
          <div class="grid grid-cols-2 gap-2 mt-4">
            <div
              v-for="benefit in step.benefits"
              :key="benefit"
              class="flex items-center gap-2"
            >
              <UIcon
                name="i-lucide-check-circle"
                class="w-4 h-4 text-emerald-300 flex-shrink-0"
              />
              <span class="text-sm text-white/90">{{ benefit }}</span>
            </div>
          </div>
        </div>

        <!-- Right: CTA -->
        <div class="flex flex-col items-center lg:items-end gap-2">
          <div
            v-if="step.price"
            class="text-center lg:text-right"
          >
            <p class="text-white/70 text-sm">
              Starting at
            </p>
            <p class="text-3xl font-bold">
              {{ formatCurrency(step.price) }}
              <span class="text-base font-normal text-white/70">/month</span>
            </p>
          </div>

          <UButton
            :to="step.actionTo"
            size="lg"
            class="bg-white text-indigo-600 hover:bg-white/90 font-bold px-8"
          >
            {{ step.actionLabel }}
            <UIcon
              name="i-lucide-arrow-right"
              class="w-4 h-4 ml-2"
            />
          </UButton>

          <p class="text-xs text-white/60">
            Cancel anytime. No hidden fees.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
