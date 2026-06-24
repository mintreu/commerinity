<script setup lang="ts">
import { useDashboardChallenges } from '~/composables/useDashboardChallenges'
import type { DashboardChallenge } from '~/types/dashboard'
import { useRoute } from '#app'

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const { show } = useDashboardChallenges()
const { formatCurrency } = useBranding()
const route = useRoute()
const toast = useToast()

const challenge = ref<DashboardChallenge | null>(null)
const loading = ref(true)

const loadChallenge = async () => {
  loading.value = true
  try {
    const response = await show(route.params.uuid as string)
    challenge.value = response.data
  } catch (error) {
    toast.error('Unable to load challenge.')
    console.error(error)
  } finally {
    loading.value = false
  }
}

onMounted(loadChallenge)
</script>

<template>
  <div class="space-y-6">
    <div
      v-if="loading"
      class="glass-card p-6"
    >
      <UIcon
        name="i-lucide-loader-circle"
        class="animate-spin w-6 h-6 text-primary"
      />
    </div>

    <div
      v-else-if="challenge"
      class="space-y-4"
    >
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold">
            {{ challenge.title }}
          </h1>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            Reward: {{ formatCurrency(challenge.reward.value || 0) }}
          </p>
        </div>
        <UBadge
          color="warning"
          variant="soft"
        >
          {{ challenge.status }}
        </UBadge>
      </div>

      <div class="glass-card p-6 space-y-3">
        <p class="text-slate-600 dark:text-slate-400">
          {{ challenge.description || 'No description available.' }}
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <h4 class="text-xs uppercase text-slate-500">
              Start Date
            </h4>
            <p class="font-semibold">
              {{ challenge.start_at || 'TBD' }}
            </p>
          </div>
          <div>
            <h4 class="text-xs uppercase text-slate-500">
              End Date
            </h4>
            <p class="font-semibold">
              {{ challenge.end_at || 'TBD' }}
            </p>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <h4 class="text-xs uppercase text-slate-500">
              Goal
            </h4>
            <p class="font-semibold">
              {{ challenge.goal.value }}
            </p>
          </div>
          <div>
            <h4 class="text-xs uppercase text-slate-500">
              Target
            </h4>
            <p class="font-semibold">
              {{ challenge.target_user_type || 'Team' }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <div
      v-else
      class="glass-card p-6"
    >
      <p class="text-sm text-slate-500 dark:text-slate-400">
        Challenge not found.
      </p>
    </div>
  </div>
</template>
