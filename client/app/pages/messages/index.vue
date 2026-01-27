<script setup lang="ts">
/**
 * Messages Inbox Page
 *
 * Shows all conversations with team members.
 * Only available for subscribed members (Member, Promoter, Advisor, Mentor).
 */

definePageMeta({
  middleware: ['sanctum:auth'],
  layout: 'dashboard'
})

const router = useRouter()
const {
  conversations,
  broadcasts,
  unreadCount,
  isLoading,
  error,
  requiresSubscription,
  fetchConversations,
  fetchBroadcasts,
  fetchUnreadCount
} = useMessages()

// Active tab
const activeTab = ref<'direct' | 'broadcasts'>('direct')

// Load data on mount
onMounted(async () => {
  await Promise.all([
    fetchConversations(),
    fetchBroadcasts(),
    fetchUnreadCount()
  ])
})

// Format relative time
function formatTime(dateString: string | null): string {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

// Get other participant name
function getParticipantName(conversation: { user_one?: { name: string }, user_two?: { name: string } }) {
  // This is simplified - in practice we'd compare with current user
  return conversation.user_one?.name || conversation.user_two?.name || 'Unknown'
}

// Open conversation
function openConversation(uuid: string) {
  router.push(`/messages/${uuid}`)
}

// Start new conversation
function startNewConversation() {
  router.push('/messages/compose')
}
</script>

<template>
  <div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-bold text-highlighted">
          Messages
        </h1>
        <p class="text-muted mt-1">
          Communicate with your team members
        </p>
      </div>

      <UButton
        v-if="!requiresSubscription"
        color="primary"
        icon="i-lucide-plus"
        @click="startNewConversation"
      >
        New Message
      </UButton>
    </div>

    <!-- Subscription Required -->
    <UCard
      v-if="requiresSubscription"
      class="text-center py-12"
    >
      <div class="flex flex-col items-center">
        <UIcon
          name="i-lucide-lock"
          class="h-16 w-16 text-amber-500 mb-4"
        />
        <h3 class="text-lg font-medium text-highlighted mb-2">
          Subscription Required
        </h3>
        <p class="text-muted max-w-md mx-auto mb-6">
          Messaging is available only for subscribed members. Upgrade your subscription to communicate with your team.
        </p>
        <UButton
          color="primary"
          to="/subscription"
        >
          View Subscription Plans
        </UButton>
      </div>
    </UCard>

    <template v-else>
      <!-- Tabs -->
      <div class="flex items-center gap-2 mb-6 border-b border-slate-200 dark:border-slate-700">
        <button
          class="px-4 py-2 text-sm font-medium transition-colors relative"
          :class="activeTab === 'direct' ? 'text-primary' : 'text-muted hover:text-highlighted'"
          @click="activeTab = 'direct'"
        >
          Direct Messages
          <UBadge
            v-if="unreadCount.direct_unread > 0"
            color="error"
            size="xs"
            class="ml-1"
          >
            {{ unreadCount.direct_unread }}
          </UBadge>
          <span
            v-if="activeTab === 'direct'"
            class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary"
          />
        </button>
        <button
          class="px-4 py-2 text-sm font-medium transition-colors relative"
          :class="activeTab === 'broadcasts' ? 'text-primary' : 'text-muted hover:text-highlighted'"
          @click="activeTab = 'broadcasts'"
        >
          Company Announcements
          <UBadge
            v-if="unreadCount.broadcast_count > 0"
            color="info"
            size="xs"
            class="ml-1"
          >
            {{ unreadCount.broadcast_count }}
          </UBadge>
          <span
            v-if="activeTab === 'broadcasts'"
            class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary"
          />
        </button>
      </div>

      <!-- Loading -->
      <div
        v-if="isLoading"
        class="flex items-center justify-center py-16"
      >
        <UIcon
          name="i-lucide-loader-2"
          class="h-8 w-8 animate-spin text-primary"
        />
      </div>

      <!-- Error -->
      <UCard
        v-else-if="error"
        class="text-center py-12 bg-red-50 dark:bg-red-900/20"
      >
        <UIcon
          name="i-lucide-alert-circle"
          class="h-12 w-12 text-red-500 mx-auto mb-4"
        />
        <p class="text-red-600 dark:text-red-400">
          {{ error }}
        </p>
      </UCard>

      <!-- Direct Messages -->
      <template v-else-if="activeTab === 'direct'">
        <UCard
          v-if="conversations.length === 0"
          class="text-center py-12"
        >
          <div class="flex flex-col items-center">
            <UIcon
              name="i-lucide-message-square"
              class="h-16 w-16 text-muted mb-4"
            />
            <h3 class="text-lg font-medium text-highlighted mb-2">
              No conversations yet
            </h3>
            <p class="text-muted mb-6">
              Start a conversation with your team members
            </p>
            <UButton
              color="primary"
              @click="startNewConversation"
            >
              Start New Conversation
            </UButton>
          </div>
        </UCard>

        <div
          v-else
          class="space-y-3"
        >
          <UCard
            v-for="conversation in conversations"
            :key="conversation.uuid"
            class="cursor-pointer transition-all duration-200 hover:shadow-md"
            :class="{ 'ring-2 ring-primary/20 bg-primary/5': conversation.unread_count > 0 }"
            @click="openConversation(conversation.uuid)"
          >
            <div class="flex items-center gap-4">
              <!-- Avatar -->
              <UAvatar
                :alt="getParticipantName(conversation)"
                size="lg"
              />

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                  <p class="font-medium text-highlighted truncate">
                    {{ getParticipantName(conversation) }}
                  </p>
                  <span class="text-xs text-muted shrink-0">
                    {{ formatTime(conversation.last_message_at) }}
                  </span>
                </div>
                <p
                  v-if="conversation.latest_message?.length"
                  class="text-sm text-muted truncate mt-1"
                >
                  {{ conversation.latest_message[0].body }}
                </p>
              </div>

              <!-- Unread badge -->
              <div
                v-if="conversation.unread_count > 0"
                class="shrink-0"
              >
                <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-primary text-white text-xs font-medium">
                  {{ conversation.unread_count }}
                </span>
              </div>
            </div>
          </UCard>
        </div>
      </template>

      <!-- Broadcasts -->
      <template v-else-if="activeTab === 'broadcasts'">
        <UCard
          v-if="broadcasts.length === 0"
          class="text-center py-12"
        >
          <div class="flex flex-col items-center">
            <UIcon
              name="i-lucide-megaphone"
              class="h-16 w-16 text-muted mb-4"
            />
            <h3 class="text-lg font-medium text-highlighted mb-2">
              No announcements
            </h3>
            <p class="text-muted">
              Company announcements will appear here
            </p>
          </div>
        </UCard>

        <div
          v-else
          class="space-y-3"
        >
          <UCard
            v-for="broadcast in broadcasts"
            :key="broadcast.uuid"
            class="cursor-pointer transition-all duration-200 hover:shadow-md"
            @click="openConversation(broadcast.uuid)"
          >
            <div class="flex items-start gap-4">
              <!-- Icon -->
              <div class="shrink-0 w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                <UIcon
                  name="i-lucide-megaphone"
                  class="h-5 w-5 text-primary"
                />
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                  <p class="font-medium text-highlighted">
                    {{ broadcast.subject || 'Company Announcement' }}
                  </p>
                  <span class="text-xs text-muted shrink-0">
                    {{ formatTime(broadcast.last_message_at) }}
                  </span>
                </div>
                <p class="text-sm text-primary mt-1">
                  From: {{ broadcast.admin?.name || 'Admin' }}
                </p>
                <p
                  v-if="broadcast.messages?.length"
                  class="text-sm text-muted truncate mt-1"
                >
                  {{ broadcast.messages[0].body }}
                </p>
              </div>
            </div>
          </UCard>
        </div>
      </template>
    </template>
  </div>
</template>
