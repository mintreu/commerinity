<script setup lang="ts">
/**
 * Compose New Message Page
 *
 * Start a new conversation with a team member.
 */

definePageMeta({
  middleware: ['sanctum:auth'],
  layout: 'dashboard'
})

const router = useRouter()
const toast = useToast()

const {
  recipients,
  isLoading,
  error,
  fetchRecipients,
  startConversation
} = useMessages()

// Form state
const selectedRecipient = ref<string | null>(null)
const subject = ref('')
const message = ref('')
const searchQuery = ref('')
const isSending = ref(false)

// Load recipients on mount
onMounted(async () => {
  await fetchRecipients()
})

// Search recipients
const searchRecipients = useDebounceFn(async (query: string) => {
  await fetchRecipients(query)
}, 300)

// Watch search query
watch(searchQuery, (query) => {
  searchRecipients(query)
})

// Send message
async function handleSend() {
  if (!selectedRecipient.value || !message.value.trim()) {
    toast.add({
      title: 'Error',
      description: 'Please select a recipient and enter a message',
      color: 'error'
    })
    return
  }

  isSending.value = true
  try {
    const response = await startConversation(
      selectedRecipient.value,
      message.value.trim(),
      subject.value.trim() || undefined
    )

    if (response?.success) {
      toast.add({
        title: 'Message Sent',
        description: 'Your message has been sent successfully',
        color: 'success'
      })
      router.push(`/messages/${response.data.conversation_uuid}`)
    }
  } catch {
    toast.add({
      title: 'Error',
      description: error.value || 'Failed to send message',
      color: 'error'
    })
  } finally {
    isSending.value = false
  }
}

// Get recipient name by uuid
const selectedRecipientName = computed(() => {
  const recipient = recipients.value.find(r => r.uuid === selectedRecipient.value)
  return recipient?.name || ''
})
</script>

<template>
  <div class="container mx-auto px-4 py-8 max-w-2xl">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
      <UButton
        color="neutral"
        variant="ghost"
        icon="i-lucide-arrow-left"
        to="/messages"
      />
      <div>
        <h1 class="text-2xl font-bold text-highlighted">
          New Message
        </h1>
        <p class="text-muted mt-1">
          Start a conversation with a team member
        </p>
      </div>
    </div>

    <UCard>
      <div class="space-y-6">
        <!-- Recipient Selection -->
        <div>
          <label class="block text-sm font-medium text-highlighted mb-2">
            To
          </label>

          <!-- Search -->
          <UInput
            v-model="searchQuery"
            placeholder="Search team members..."
            icon="i-lucide-search"
            class="mb-3"
          />

          <!-- Loading -->
          <div
            v-if="isLoading"
            class="flex items-center justify-center py-8"
          >
            <UIcon
              name="i-lucide-loader-2"
              class="h-6 w-6 animate-spin text-primary"
            />
          </div>

          <!-- No recipients -->
          <div
            v-else-if="recipients.length === 0"
            class="text-center py-8"
          >
            <UIcon
              name="i-lucide-users"
              class="h-12 w-12 text-muted mx-auto mb-3"
            />
            <p class="text-muted">
              No team members found
            </p>
            <p class="text-sm text-muted/70 mt-1">
              You can only message your sponsor or direct referrals
            </p>
          </div>

          <!-- Recipients list -->
          <div
            v-else
            class="space-y-2 max-h-48 overflow-y-auto"
          >
            <button
              v-for="recipient in recipients"
              :key="recipient.uuid"
              type="button"
              class="w-full flex items-center gap-3 p-3 rounded-lg transition-colors"
              :class="selectedRecipient === recipient.uuid
                ? 'bg-primary/10 ring-2 ring-primary'
                : 'bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700'"
              @click="selectedRecipient = recipient.uuid"
            >
              <UAvatar
                :alt="recipient.name"
                size="sm"
              />
              <div class="text-left flex-1">
                <p class="font-medium text-highlighted">
                  {{ recipient.name }}
                </p>
                <p
                  v-if="recipient.mobile_masked"
                  class="text-xs text-muted"
                >
                  {{ recipient.mobile_masked }}
                </p>
              </div>
              <UIcon
                v-if="selectedRecipient === recipient.uuid"
                name="i-lucide-check-circle"
                class="h-5 w-5 text-primary"
              />
            </button>
          </div>
        </div>

        <!-- Subject (optional) -->
        <UFormField label="Subject (optional)">
          <UInput
            v-model="subject"
            placeholder="Enter subject..."
          />
        </UFormField>

        <!-- Message -->
        <UFormField
          label="Message"
          required
        >
          <UTextarea
            v-model="message"
            placeholder="Type your message..."
            :rows="5"
          />
        </UFormField>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
          <UButton
            color="neutral"
            variant="ghost"
            to="/messages"
          >
            Cancel
          </UButton>
          <UButton
            color="primary"
            icon="i-lucide-send"
            :loading="isSending"
            :disabled="!selectedRecipient || !message.trim()"
            @click="handleSend"
          >
            Send Message
          </UButton>
        </div>
      </div>
    </UCard>
  </div>
</template>
