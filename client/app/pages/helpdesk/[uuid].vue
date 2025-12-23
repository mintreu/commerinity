<template>
  <UContainer class="py-8">
    <div v-if="loading" class="text-center py-12"><UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin" /></div>
    <div v-else class="grid lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <UCard>
          <template #header>
            <div class="flex items-center justify-between">
              <div>
                <h1 class="text-2xl font-bold">{{ ticket.title }}</h1>
                <p class="text-sm text-gray-600">ID: {{ ticket.uuid?.slice(0,8) }}</p>
              </div>
              <UButton to="/helpdesk" variant="ghost" icon="i-heroicons-arrow-left">Back</UButton>
            </div>
          </template>

          <div class="space-y-4 max-h-[500px] overflow-y-auto" ref="chatContainer">
            <div v-for="msg in messages" :key="msg.id" :class="msg.author?.fingerprint === user?.fingerprint ? 'flex justify-end' : ''">
              <div :class="msg.author?.fingerprint === user?.fingerprint ? 'bg-blue-100 dark:bg-blue-900 text-right' : 'bg-gray-100 dark:bg-gray-800'" class="p-4 rounded-lg max-w-[80%]">
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">{{ msg.author?.name }} · {{ new Date(msg.created_at).toLocaleString() }}</div>
                <p>{{ msg.message }}</p>
                <div v-if="msg.attachment?.length" class="mt-2 flex flex-wrap gap-2">
                  <a v-for="(url,i) in msg.attachment" :key="i" :href="url" target="_blank"><img :src="url" class="w-20 h-20 object-cover rounded" /></a>
                </div>
              </div>
            </div>
          </div>

          <template #footer>
            <UForm @submit="handleReply" class="flex gap-3">
              <UTextarea v-model="replyText" placeholder="Type your message..." :rows="2" class="flex-1" />
              <UButton type="submit" :loading="sending" color="primary">Send</UButton>
            </UForm>
          </template>
        </UCard>
      </div>

      <div class="lg:col-span-1">
        <UCard>
          <template #header><h3 class="font-bold">Ticket Info</h3></template>
          <div class="space-y-4">
            <div><span class="text-sm text-gray-600">Status:</span> <UBadge :color="ticket.status==='open'?'yellow':'green'">{{ ticket.status }}</UBadge></div>
            <div><span class="text-sm text-gray-600">Priority:</span> <UBadge :color="ticket.priority==='urgent'||ticket.priority==='high'?'red':'yellow'">{{ ticket.priority }}</UBadge></div>
            <div><span class="text-sm text-gray-600">Created:</span> {{ new Date(ticket.created_at).toLocaleDateString() }}</div>
          </div>
        </UCard>
      </div>
    </div>
  </UContainer>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })

const route = useRoute()
const { user } = useSanctumAuth()
const { loading, fetchTicket, replyTicket } = useHelpdesk()
const toast = useToast()

const ticket = ref({})
const messages = ref([])
const replyText = ref('')
const sending = ref(false)
const chatContainer = ref(null)

const handleReply = async () => {
  if (!replyText.value.trim()) return
  sending.value = true
  try {
    const formData = new FormData()
    formData.append('message', replyText.value)
    await replyTicket(route.params.uuid as string, formData)
    replyText.value = ''
    const data = await fetchTicket(route.params.uuid as string)
    ticket.value = data.ticket
    messages.value = data.conversations || []
    nextTick(() => chatContainer.value?.scrollTo(0, 999999))
  } catch (e: any) {
    toast.add({ title: 'Error', description: 'Failed to send reply', color: 'red' })
  } finally {
    sending.value = false
  }
}

onMounted(async () => {
  const data = await fetchTicket(route.params.uuid as string)
  ticket.value = data.ticket
  messages.value = data.conversations || []
  nextTick(() => chatContainer.value?.scrollTo(0, 999999))
})
</script>
