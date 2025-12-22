<template>
  <div class="tabs-wrapper min-h-screen flex flex-col">
    <!-- Regular -->
    <RegularInsight v-if="userRole === 'regular'" />

    <!-- Applicant -->
    <ApplicantInsight v-else-if="userRole === 'applicant'" />

    <!-- Member / Promoter -->
    <MemberInsight v-else-if="['member','promoter'].includes(userRole)" />

    <!-- Advisor / Mentor -->
    <OrganizerInsight v-else-if="['advisor','mentor'].includes(userRole)" />

    <!-- Fallback -->
    <div v-else class="flex flex-col justify-center items-center flex-1">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        Welcome!
      </h2>
      <p class="text-gray-700 dark:text-gray-300 mt-2 text-center max-w-md">
        Your dashboard content will appear here based on your role.
        Upgrade or apply for jobs to access more features.
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useSanctum } from '#imports'

import ApplicantInsight from '~/components/insights/applicant/ApplicantInsight.vue'
import MemberInsight from '~/components/insights/member/MemberInsight.vue'
import OrganizerInsight from '~/components/insights/organizer/OrganizerInsight.vue'
import RegularInsight from '~/components/insights/regular/RegularInsight.vue'

// Sanctum user
const { user } = useSanctum()

// Safe role string (avoid undefined errors)
const userRole = computed(() =>
    user.value?.type?.toLowerCase() ?? 'guest'
)

definePageMeta({
  layout: 'dashboard',
})
</script>
