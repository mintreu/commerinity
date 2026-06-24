<script setup lang="ts">
/**
 * DashboardNotices - Container for dashboard promotional notices
 * Fetches and displays admin notices targeted to the current user
 */

const { notices, isLoading, fetchNotices, dismissNotice, recordClick } = useNotices()

// Fetch notices on mount
onMounted(() => {
  fetchNotices()
})

async function handleDismiss(uuid: string) {
  await dismissNotice(uuid)
}

async function handleClick(uuid: string) {
  await recordClick(uuid)
}
</script>

<template>
  <div
    v-if="notices.length > 0 || isLoading"
    class="space-y-4"
  >
    <!-- Loading skeleton -->
    <div
      v-if="isLoading"
      class="animate-pulse"
    >
      <div class="h-24 bg-slate-200 dark:bg-slate-700 rounded-xl" />
    </div>

    <!-- Notices -->
    <TransitionGroup
      name="notice-list"
      tag="div"
      class="space-y-4"
    >
      <DashboardNoticeCard
        v-for="notice in notices"
        :key="notice.uuid"
        :notice="notice"
        @dismiss="handleDismiss"
        @click="handleClick"
      />
    </TransitionGroup>
  </div>
</template>

<style scoped>
.notice-list-enter-active,
.notice-list-leave-active {
  transition: all 0.3s ease;
}
.notice-list-enter-from,
.notice-list-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}
</style>
