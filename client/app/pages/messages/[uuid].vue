<script setup lang="ts">
/**
 * Conversation Detail Page
 *
 * View and reply to messages in a conversation.
 */

definePageMeta({
  middleware: ['sanctum:auth'],
  layout: 'dashboard'
})

const route = useRoute()
const toast = useToast()
const user = useCurrentUser()

const {
  currentConversation,
  messages,
  isLoading,
  error,
  fetchConversation,
  sendMessage,
  markAsRead
} = useMessages()

// Form state
const newMessage = ref('')
const isSending = ref(false)
const messagesContainer = ref<HTMLElement | null>(null)

// Get conversation UUID
const conversationUuid = computed(() => route.params.uuid as string)

// Load conversation on mount
onMounted(async () => {
  await loadConversation()
})

async function loadConversation() {
  await fetchConversation(conversationUuid.value)
  // Mark as read when viewing
  await markAsRead(conversationUuid.value)
  // Scroll to bottom
  nextTick(() => {
    scrollToBottom()
  })
}

// Scroll to bottom of messages
function scrollToBottom() {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

// Format time
function formatTime(dateString: string): string {
  const date = new Date(dateString)
  return date.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })
}

// Format date header
function formatDateHeader(dateString: string): string {
  const date = new Date(dateString)
  const now = new Date()
  const yesterday = new Date(now)
  yesterday.setDate(yesterday.getDate() - 1)

  if (date.toDateString() === now.toDateString()) {
    return 'Today'
  }
  if (date.toDateString() === yesterday.toDateString()) {
    return 'Yesterday'
  }
  return date.toLocaleDateString('en-US', {
    weekday: 'long',
    month: 'short',
    day: 'numeric'
  })
}

// Check if message is from current user
function isMyMessage(message: { sender_user_id: number | null }): boolean {
  return message.sender_user_id === (user.value as { id: number } | null)?.id
}

// Group messages by date
const groupedMessages = computed(() => {
  const groups: { date: string; messages: typeof messages.value }[] = []
  let currentDate = ''

  // Messages are in reverse order (newest first), so reverse for display
  const sortedMessages = [...messages.value].reverse()

  for (const msg of sortedMessages) {
    const msgDate = new Date(msg.created_at).toDateString()
    if (msgDate !== currentDate) {
      currentDate = msgDate
      groups.push({
        date: formatDateHeader(msg.created_at),
        messages: []
      })
    }
    groups[groups.length - 1].messages.push(msg)
  }

  return groups
})

// Send message
async function handleSend() {
  if (!newMessage.value.trim()) return

  isSending.value = true
  const messageText = newMessage.value.trim()
  newMessage.value = ''

  try {
    await sendMessage(conversationUuid.value, messageText)
    // Scroll to bottom after sending
    nextTick(() => {
      scrollToBottom()
    })
  }
  catch {
    // Restore message on error
    newMessage.value = messageText
    toast.add({
      title: 'Error',
      description: 'Failed to send message',
      color: 'error'
    })
  }
  finally {
    isSending.value = false
  }
}

// Handle enter key
function handleKeydown(event: KeyboardEvent) {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault()
    handleSend()
  }
}
</script>

<template>
  <div class="h-full flex flex-col max-w-4xl mx-auto">
    <!-- Header -->
    <div class="shrink-0 flex items-center gap-4 p-4 border-b border-slate-200 dark:border-slate-700">
      <UButton
        color="neutral"
        variant="ghost"
        icon="i-lucide-arrow-left"
        to="/messages"
      />

      <template v-if="currentConversation">
        <template v-if="currentConversation.is_broadcast">
          <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
            <UIcon name="i-lucide-megaphone" class="h-5 w-5 text-primary" />
          </div>
          <div>
            <h1 class="font-semibold text-highlighted">
              {{ currentConversation.subject || 'Company Announcement' }}
            </h1>
            <p class="text-sm text-muted">
              From: {{ currentConversation.admin?.name || 'Admin' }}
            </p>
          </div>
        </template>
        <template v-else>
          <UAvatar
            :alt="currentConversation.other_participant?.name || 'User'"
            size="lg"
          />
          <div>
            <h1 class="font-semibold text-highlighted">
              {{ currentConversation.other_participant?.name || 'Unknown' }}
            </h1>
            <p v-if="currentConversation.subject" class="text-sm text-muted">
              {{ currentConversation.subject }}
            </p>
          </div>
        </template>
      </template>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex-1 flex items-center justify-center">
      <UIcon name="i-lucide-loader-2" class="h-8 w-8 animate-spin text-primary" />
    </div>

    <!-- Error -->
    <div v-else-if="error" class="flex-1 flex items-center justify-center p-4">
      <UCard class="text-center py-8">
        <UIcon name="i-lucide-alert-circle" class="h-12 w-12 text-red-500 mx-auto mb-4" />
        <p class="text-red-600 dark:text-red-400">{{ error }}</p>
        <UButton color="primary" class="mt-4" @click="loadConversation">
          Retry
        </UButton>
      </UCard>
    </div>

    <template v-else>
      <!-- Messages -->
      <div
        ref="messagesContainer"
        class="flex-1 overflow-y-auto p-4 space-y-6"
      >
        <!-- Empty state -->
        <div v-if="messages.length === 0" class="text-center py-12">
          <UIcon name="i-lucide-message-square" class="h-16 w-16 text-muted mx-auto mb-4" />
          <p class="text-muted">No messages yet</p>
        </div>

        <!-- Grouped messages -->
        <div v-for="group in groupedMessages" :key="group.date" class="space-y-4">
          <!-- Date header -->
          <div class="flex items-center justify-center">
            <span class="px-3 py-1 text-xs font-medium text-muted bg-slate-100 dark:bg-slate-800 rounded-full">
              {{ group.date }}
            </span>
          </div>

          <!-- Messages -->
          <div
            v-for="msg in group.messages"
            :key="msg.uuid"
            class="flex"
            :class="isMyMessage(msg) ? 'justify-end' : 'justify-start'"
          >
            <div
              class="max-w-[75%] rounded-2xl px-4 py-2"
              :class="isMyMessage(msg)
                ? 'bg-primary text-white rounded-br-md'
                : 'bg-slate-100 dark:bg-slate-800 text-highlighted rounded-bl-md'"
            >
              <!-- Sender name for broadcasts or group conversations -->
              <p
                v-if="!isMyMessage(msg) && (currentConversation?.is_broadcast || msg.sender_admin_id)"
                class="text-xs font-medium mb-1"
                :class="isMyMessage(msg) ? 'text-white/70' : 'text-primary'"
              >
                {{ msg.sender_admin?.name || msg.sender_user?.name || 'Unknown' }}
              </p>

              <!-- Message body -->
              <p class="whitespace-pre-wrap break-words">
                {{ msg.body }}
              </p>

              <!-- Time -->
              <p
                class="text-xs mt-1"
                :class="isMyMessage(msg) ? 'text-white/70' : 'text-muted'"
              >
                {{ formatTime(msg.created_at) }}
                <span v-if="isMyMessage(msg) && msg.read_at" class="ml-1">
                  <UIcon name="i-lucide-check-check" class="h-3 w-3 inline" />
                </span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Input (only for direct messages, not broadcasts) -->
      <div
        v-if="currentConversation && !currentConversation.is_broadcast"
        class="shrink-0 p-4 border-t border-slate-200 dark:border-slate-700"
      >
        <div class="flex items-end gap-3">
          <UTextarea
            v-model="newMessage"
            placeholder="Type a message..."
            :rows="1"
            class="flex-1"
            autoresize
            @keydown="handleKeydown"
          />
          <UButton
            color="primary"
            icon="i-lucide-send"
            :loading="isSending"
            :disabled="!newMessage.trim()"
            @click="handleSend"
          />
        </div>
      </div>

      <!-- Broadcast notice -->
      <div
        v-else-if="currentConversation?.is_broadcast"
        class="shrink-0 p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50"
      >
        <p class="text-center text-sm text-muted">
          <UIcon name="i-lucide-info" class="h-4 w-4 inline mr-1" />
          This is a company announcement. You cannot reply to this message.
          <NuxtLink to="/helpdesk" class="text-primary hover:underline">
            Contact support
          </NuxtLink>
          if you have questions.
        </p>
      </div>
    </template>
  </div>
</template>
