<script setup lang="ts">
import { useDashboardChallenges } from '~/composables/useDashboardChallenges'

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const { fetchActive } = useDashboardChallenges()
const { formatCurrency } = useBranding()
const toast = useToast()

const challenges = ref([] as Array<{ uuid: string; title: string; status: string; end_at?: string; reward: { value: number }; goal: { value: number }; meta?: Record<string, unknown> }>)
const loading = ref(false)

const getProgress = (challenge: typeof challenges.value[number]) => {
  const current = Number(challenge.meta?.current ?? 0)
  const goal = Math.max(1, challenge.goal?.value ?? 1)
  return Math.min(100, Math.round((current / goal) * 100))
}

const loadChallenges = async () => {
  loading.value = true
  try {
    const response = await fetchActive()
    challenges.value = response.data
  } catch (error) {
    toast.error('Unable to load challenges.')
    console.error(error)
  } finally {
    loading.value = false
  }
}

onMounted(loadChallenges)
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold">Challenges</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
          Motivate your team with measurable goals.
        </p>
      </div>
      <UButton to="/challenges/new" color="primary">
        Create Challenge
      </UButton>
    </div>

    <div v-if="loading" class="glass-card p-6 space-y-3">
      <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-3/4 animate-pulse" />
      <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/2 animate-pulse" />
    </div>

    <div v-else-if="!challenges.length" class="glass-card p-6">
      <CommonEmptyState
        icon="i-lucide-flame"
        title="No active challenges"
        description="Create a challenge to boost performance."
        action-label="Create Challenge"
        action-to="/challenges/new"
      />
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="challenge in challenges"
        :key="challenge.uuid"
        class="glass-card p-4 space-y-3"
      >
        <div class="flex items-center justify-between">
          <h3 class="font-semibold text-slate-900 dark:text-white">
            {{ challenge.title }}
          </h3>
          <UBadge color="warning" variant="soft">
            {{ challenge.status }}
          </UBadge>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400">
          Ends {{ challenge.end_at || 'Ongoing' }}
        </p>
        <div class="space-y-1">
          <div class="flex justify-between text-sm text-slate-600 dark:text-slate-400">
            <span>Reward</span>
            <span>{{ formatCurrency(challenge.reward.value || 0) }}</span>
          </div>
          <div class="flex justify-between text-sm text-slate-600 dark:text-slate-400">
            <span>Target</span>
            <span>{{ challenge.goal.value }}</span>
          </div>
        </div>
        <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
          <div
            class="h-full bg-gradient-to-r from-emerald-500 to-green-500 rounded-full"
            :style="{ width: `${getProgress(challenge)}%` }"
          />
        </div>
        <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
          <span>{{ challenge.meta?.current ?? 0 }} achieved</span>
          <NuxtLink
            class="text-primary hover:underline"
            :to="`/challenges/${challenge.uuid}`"
          >
            View
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>
