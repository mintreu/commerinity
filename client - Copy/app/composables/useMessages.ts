import type { Ref } from 'vue'

export interface MessageUser {
  uuid: string
  name: string
  avatar_url?: string
  mobile_masked?: string
}

export interface Message {
  id: number
  uuid: string
  body: string
  type: 'text' | 'image' | 'file' | 'system'
  attachments?: string[]
  read_at: string | null
  created_at: string
  sender_user_id: number | null
  sender_admin_id: number | null
  sender_user?: MessageUser
  sender_admin?: { name: string }
}

export interface Conversation {
  id: number
  uuid: string
  subject: string | null
  is_broadcast: boolean
  last_message_at: string | null
  unread_count: number
  user_one?: MessageUser
  user_two?: MessageUser
  admin?: { name: string }
  latest_message?: Message[]
}

export interface ConversationDetail {
  uuid: string
  subject: string | null
  is_broadcast: boolean
  last_message_at: string | null
  other_participant?: MessageUser
  admin?: { name: string }
}

export const useMessages = () => {
  const config = useRuntimeConfig()

  const conversations: Ref<Conversation[]> = ref([])
  const broadcasts: Ref<Conversation[]> = ref([])
  const currentConversation: Ref<ConversationDetail | null> = ref(null)
  const messages: Ref<Message[]> = ref([])
  const unreadCount: Ref<{ direct_unread: number, broadcast_count: number, total: number }> = ref({
    direct_unread: 0,
    broadcast_count: 0,
    total: 0
  })
  const recipients: Ref<MessageUser[]> = ref([])
  const isLoading = ref(false)
  const error: Ref<string | null> = ref(null)
  const requiresSubscription = ref(false)

  const fetchConversations = async (page = 1) => {
    isLoading.value = true
    error.value = null
    requiresSubscription.value = false
    try {
      const response = await useSanctumFetch<{
        success: boolean
        data: Conversation[]
        requires_subscription?: boolean
        message?: string
      }>(`${config.public.apiBase}/api/messages?page=${page}`)

      if (response?.requires_subscription) {
        requiresSubscription.value = true
        error.value = response.message || 'Subscription required'
        return response
      }

      if (response?.success) {
        conversations.value = response.data
      }
      return response
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch conversations'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const fetchBroadcasts = async (page = 1) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<{
        success: boolean
        data: Conversation[]
      }>(`${config.public.apiBase}/api/messages/broadcasts?page=${page}`)

      if (response?.success) {
        broadcasts.value = response.data
      }
      return response
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch broadcasts'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const fetchConversation = async (uuid: string, page = 1) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<{
        success: boolean
        data: {
          conversation: ConversationDetail
          messages: Message[]
        }
      }>(`${config.public.apiBase}/api/messages/${uuid}?page=${page}`)

      if (response?.success) {
        currentConversation.value = response.data.conversation
        messages.value = response.data.messages
      }
      return response
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch conversation'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const fetchUnreadCount = async () => {
    try {
      const response = await useSanctumFetch<{
        success: boolean
        data: { direct_unread: number, broadcast_count: number, total: number }
      }>(`${config.public.apiBase}/api/messages/unread-count`)

      if (response?.success) {
        unreadCount.value = response.data
      }
      return response
    } catch {
      // Silent fail for unread count
    }
  }

  const fetchRecipients = async (search?: string) => {
    try {
      const url = search
        ? `${config.public.apiBase}/api/messages/recipients?search=${encodeURIComponent(search)}`
        : `${config.public.apiBase}/api/messages/recipients`

      const response = await useSanctumFetch<{
        success: boolean
        data: MessageUser[]
      }>(url)

      if (response?.success) {
        recipients.value = response.data
      }
      return response
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch recipients'
      throw err
    }
  }

  const startConversation = async (recipientUuid: string, message: string, subject?: string) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<{
        success: boolean
        data: { conversation_uuid: string, message: Message }
        requires_subscription?: boolean
        message?: string
      }>(`${config.public.apiBase}/api/messages`, {
        method: 'POST',
        body: {
          recipient_uuid: recipientUuid,
          message,
          subject
        }
      })

      if (response?.requires_subscription) {
        requiresSubscription.value = true
        error.value = response.message || 'Subscription required'
        return response
      }

      return response
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to send message'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const sendMessage = async (conversationUuid: string, body: string, type: 'text' | 'image' | 'file' = 'text') => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch<{
        success: boolean
        data: Message
      }>(`${config.public.apiBase}/api/messages/${conversationUuid}`, {
        method: 'POST',
        body: {
          message: body,
          type
        }
      })

      if (response?.success && response.data) {
        // Add message to local state
        messages.value.unshift(response.data)
      }

      return response
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to send message'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const markAsRead = async (conversationUuid: string) => {
    try {
      await useSanctumFetch(`${config.public.apiBase}/api/messages/${conversationUuid}/read`, {
        method: 'POST'
      })

      // Update local unread count
      const conversation = conversations.value.find(c => c.uuid === conversationUuid)
      if (conversation) {
        unreadCount.value.direct_unread -= conversation.unread_count
        unreadCount.value.total -= conversation.unread_count
        conversation.unread_count = 0
      }
    } catch {
      // Silent fail
    }
  }

  const deleteMessage = async (messageUuid: string) => {
    try {
      const response = await useSanctumFetch<{ success: boolean }>(`${config.public.apiBase}/api/messages/message/${messageUuid}`, {
        method: 'DELETE'
      })

      if (response?.success) {
        // Remove from local state
        messages.value = messages.value.filter(m => m.uuid !== messageUuid)
      }

      return response
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to delete message'
      throw err
    }
  }

  return {
    conversations,
    broadcasts,
    currentConversation,
    messages,
    unreadCount,
    recipients,
    isLoading,
    error,
    requiresSubscription,
    fetchConversations,
    fetchBroadcasts,
    fetchConversation,
    fetchUnreadCount,
    fetchRecipients,
    startConversation,
    sendMessage,
    markAsRead,
    deleteMessage
  }
}
