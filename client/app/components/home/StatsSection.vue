<script setup lang="ts">
interface StatItem {
  value: number
  formatted: string
  label: string
}

interface StatsData {
  members: StatItem
  careers: StatItem
  payouts: StatItem
}

const config = useRuntimeConfig()

const { data: statsResponse, status } = await useFetch<{ success: boolean, data: StatsData }>(
  `${config.public.apiBase}/api/public/stats`,
  {
    lazy: true,
    server: false
  }
)

const stats = computed(() => {
  if (!statsResponse.value?.data) return []

  const data = statsResponse.value.data
  return [
    {
      ...data.members,
      icon: 'i-lucide-users',
      gradient: 'from-violet-500 to-purple-600',
      bgGradient: 'from-violet-500/10 to-purple-600/10'
    },
    {
      ...data.careers,
      icon: 'i-lucide-briefcase',
      gradient: 'from-emerald-500 to-green-600',
      bgGradient: 'from-emerald-500/10 to-green-600/10'
    },
    {
      ...data.payouts,
      icon: 'i-lucide-wallet',
      gradient: 'from-amber-500 to-orange-600',
      bgGradient: 'from-amber-500/10 to-orange-600/10'
    }
  ]
})
</script>

<template>
  <section class="py-12 md:py-16 relative overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0 bg-gradient-to-b from-white via-violet-50/30 to-white dark:from-slate-900 dark:via-purple-950/20 dark:to-slate-900" />

    <UContainer class="relative z-10">
      <!-- Loading State -->
      <div
        v-if="status === 'pending'"
        class="grid grid-cols-1 md:grid-cols-3 gap-6"
      >
        <div
          v-for="i in 3"
          :key="i"
          class="glass-subtle rounded-3xl p-6 animate-pulse"
        >
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-slate-200 dark:bg-slate-700" />
            <div class="flex-1">
              <div class="h-8 w-24 bg-slate-200 dark:bg-slate-700 rounded mb-2" />
              <div class="h-4 w-32 bg-slate-200 dark:bg-slate-700 rounded" />
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Grid -->
      <div
        v-else-if="stats.length"
        class="grid grid-cols-1 md:grid-cols-3 gap-6"
      >
        <div
          v-for="(stat, index) in stats"
          :key="index"
          class="group glass-subtle hover:glass-strong rounded-3xl p-6 transition-all duration-500 hover:scale-105 hover:shadow-xl"
        >
          <div class="flex items-center gap-4">
            <!-- Icon -->
            <div
              class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-br shadow-lg group-hover:scale-110 transition-transform duration-500"
              :class="stat.gradient"
            >
              <UIcon
                :name="stat.icon"
                class="w-7 h-7 text-white"
              />
            </div>

            <!-- Content -->
            <div>
              <div class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white">
                {{ stat.formatted }}
              </div>
              <div class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                {{ stat.label }}
              </div>
            </div>
          </div>

          <!-- Decorative gradient line -->
          <div
            class="absolute bottom-0 left-0 right-0 h-1 rounded-b-3xl opacity-0 group-hover:opacity-100 transition-opacity bg-gradient-to-r"
            :class="stat.gradient"
          />
        </div>
      </div>

      <!-- Error/Empty State -->
      <div
        v-else
        class="text-center py-8"
      >
        <p class="text-slate-500 dark:text-slate-400">
          Unable to load statistics
        </p>
      </div>
    </UContainer>
  </section>
</template>
