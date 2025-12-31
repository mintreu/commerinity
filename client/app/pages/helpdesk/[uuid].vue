<script setup lang="ts">
/**
 * Support Ticket Conversation - Premium Mintreu Design
 * Chat interface with ticket metadata and real-time styled replies
 */

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const route = useRoute()
const { user } = useSanctumAuth()
const { loading, fetchTicket, replyTicket } = useHelpdesk()
const toast = useToast()

const ticket = ref<any>({})
const messages = ref<any[]>([])
const replyText = ref('')
const sending = ref(false)
const chatContainer = ref<HTMLElement | null>(null)

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
    nextTick(() => chatContainer.value?.scrollTo({ top: chatContainer.value.scrollHeight, behavior: 'smooth' }))
  } catch (e: any) {
    toast.add({ title: 'Error', description: 'Failed to send reply', color: 'error' })
  } finally {
    sending.value = false
  }
}

onMounted(async () => {
  const data = await fetchTicket(route.params.uuid as string)
  ticket.value = data.ticket
  messages.value = data.conversations || []
  nextTick(() => chatContainer.value?.scrollTo({ top: chatContainer.value.scrollHeight }))
})

const getStatusColor = (status: string) => {
  const map: Record<string, string> = { open: 'warning', resolved: 'success', closed: 'gray' }
  return map[status] || 'blue'
}

const getPriorityColor = (priority: string) => {
  const map: Record<string, string> = { urgent: 'red', high: 'orange', medium: 'amber', low: 'green' }
  return map[priority] || 'gray'
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleString('en-IN', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<template>
  <div class="max-w-6xl mx-auto space-y-6 pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
         <UButton
           to="/helpdesk"
           variant="ghost"
           color="neutral"
           icon="i-lucide-arrow-left"
           class="rounded-xl"
         />
         <div>
           <h1 class="text-xl font-black text-slate-900 dark:text-white">
             {{ ticket.title || 'Loading Ticket...' }}
           </h1>
           <p class="text-xs font-mono text-slate-500 uppercase tracking-widest">
             Ticket ID: #{{ route.params.uuid.toString().slice(0,8).toUpperCase() }}
           </p>
         </div>
      </div>
      <div class="flex gap-2">
        <UBadge :color="getStatusColor(ticket.status)" variant="subtle" class="rounded-full px-4 font-black text-[10px] uppercase tracking-widest">
          {{ ticket.status }}
        </UBadge>
      </div>
    </div>

    <div class="grid lg:grid-cols-4 gap-8">
      <!-- Chat Interface -->
      <div class="lg:col-span-3 space-y-6">
        <div class="glass-card p-0 border-none ring-1 ring-slate-200 dark:ring-slate-800 flex flex-col h-[600px]">
          <!-- Messages Area -->
          <div
            ref="chatContainer"
            class="flex-1 overflow-y-auto p-6 space-y-8 scrollbar-hide bg-slate-50/30 dark:bg-slate-900/10"
          >
            <div v-if="loading && messages.length === 0" class="h-full flex items-center justify-center">
               <div class="w-10 h-10 border-4 border-primary-500/20 border-t-primary-500 rounded-full animate-spin" />
            </div>

            <template v-else>
              <div
                v-for="(msg, index) in messages"
                :key="msg.id || index"
                :class="[
                  'flex w-full',
                  msg.author?.fingerprint === user?.fingerprint ? 'justify-end' : 'justify-start'
                ]"
              >
                <div class="max-w-[80%] space-y-2">
                  <div
                    :class="[
                      'p-4 rounded-3xl text-sm leading-relaxed',
                      msg.author?.fingerprint === user?.fingerprint
                        ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/20 rounded-tr-none'
                        : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 shadow-sm border border-slate-100 dark:border-slate-700 rounded-tl-none'
                    ]"
                  >
                    <p class="whitespace-pre-wrap">{{ msg.message }}</p>

                    <!-- Attachments -->
                    <div v-if="msg.attachments?.length" class="mt-4 flex flex-wrap gap-2">
                      <a
                        v-for="(url, i) in msg.attachments"
                        :key="i"
                        :href="url"
                        target="_blank"
                        class="relative group overflow-hidden rounded-xl h-24 w-24 border border-white/20"
                      >
                        <img :src="url" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform" />
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                          <UIcon name="i-lucide-external-link" class="text-white w-5 h-5" />
                        </div>
                      </a>
                    </div>
                  </div>
                  <div
                    :class="[
                      'text-[10px] font-black uppercase tracking-widest text-slate-400 px-2 flex items-center gap-2',
                      msg.author?.fingerprint === user?.fingerprint ? 'justify-end' : 'justify-start'
                    ]"
                  >
                    <span>{{ msg.author?.name }}</span>
                    <span class="w-1 h-1 bg-slate-300 rounded-full" />
                    <span>{{ formatDate(msg.created_at) }}</span>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <!-- Input Area -->
          <div class="p-6 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 rounded-b-3xl">
            <UForm @submit="handleReply" class="relative">
              <UTextarea
                v-model="replyText"
                placeholder="Write your message here..."
                :rows="3"
                variant="none"
                class="w-full bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 pr-16 focus:ring-2 focus:ring-primary-500 transition-all border-none ring-1 ring-slate-200 dark:ring-slate-700"
                @keydown.enter.ctrl="handleReply"
              />
              <div class="absolute bottom-3 right-3 flex items-center gap-2">
                <UButton
                  type="submit"
                  icon="i-lucide-send"
                  color="primary"
                  :loading="sending"
                  size="xl"
                  class="rounded-xl shadow-lg shadow-primary-500/20"
                />
              </div>
            </UForm>
            <div class="mt-2 flex items-center justify-between">
              <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                Press Ctrl + Enter to send
              </p>
              <div class="flex items-center gap-2 text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                 <UIcon name="i-lucide-shield-check" class="text-emerald-500" />
                 End-to-End Encrypted
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Ticket Details Sidebar -->
      <div class="lg:col-span-1 space-y-6">
        <div class="glass-card p-6 border-none ring-1 ring-slate-200 dark:ring-slate-800">
          <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-6 flex items-center justify-between">
            Ticket Metadata
          </h3>

          <div class="space-y-6">
            <div>
              <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 block">Resolution Priority</span>
              <div class="flex items-center gap-2">
                <div :class="`w-2 h-2 rounded-full animate-pulse bg-${getPriorityColor(ticket.priority)}-500`" />
                <span class="text-sm font-bold text-slate-900 dark:text-white capitalize">{{ ticket.priority }} Priority</span>
              </div>
            </div>

            <div>
              <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 block">Department / Topic</span>
              <div class="flex items-center gap-2 text-slate-900 dark:text-white">
                <UIcon name="i-lucide-tag" class="text-primary-500 w-4 h-4" />
                <span class="text-sm font-bold">{{ ticket.topic?.name || 'General' }}</span>
              </div>
            </div>

            <div>
              <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 block">Submission Date</span>
              <div class="flex items-center gap-2 text-slate-900 dark:text-white">
                 <UIcon name="i-lucide-calendar-days" class="text-slate-400 w-4 h-4" />
                 <span class="text-sm font-bold">{{ formatDate(ticket.created_at || new Date()) }}</span>
              </div>
            </div>
          </div>

          <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-800">
            <UButton block color="neutral" variant="soft" class="rounded-xl font-bold py-3" icon="i-lucide-check-check">
              Mark as Resolved
            </UButton>
          </div>
        </div>

        <!-- Security Badge -->
        <div class="p-6 bg-slate-50/50 dark:bg-slate-800/30 rounded-3xl border border-slate-200 dark:border-slate-800 text-center">
           <UIcon name="i-lucide-headphones" class="w-10 h-10 text-primary-500/50 mx-auto mb-3" />
           <p class="text-xs font-bold text-slate-600 dark:text-slate-400">
             Need faster response? Premium users get priority 1-hour resolution.
           </p>
           <UButton to="/subscription" variant="link" color="primary" class="mt-2 text-[10px] font-black uppercase tracking-widest p-0">
             Upgrade Now
           </UButton>
        </div>
      </div>
    </div>
  </div>
</template>
