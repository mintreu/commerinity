<template>
  <div class="p-4 sm:p-6 max-w-screen-xl mx-auto space-y-6 sm:space-y-8">
    <!-- Page Title -->
    <div class="flex items-center gap-3">
      <Icon name="mdi-ticket" class="text-blue-600 dark:text-blue-400 text-2xl" />
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Ticket Details</h1>
    </div>

    <!-- Loader -->
    <div v-if="isLoading" class="w-full flex items-center justify-center py-16">
      <div class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
        <Icon name="mdi-loading" class="animate-spin" />
        <span>Loading ticket and conversation…</span>
      </div>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Conversation -->
      <section
          :class="[
          'order-1 lg:order-none bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col',
          chatExpanded ? 'lg:col-span-2' : ''
        ]"
      >
        <header class="px-4 sm:px-6 py-3 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 rounded-t-lg">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Conversation</h2>
            <button
                type="button"
                @click="chatExpanded = !chatExpanded"
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
                :aria-pressed="chatExpanded"
            >
              <Icon :name="chatExpanded ? 'mdi-arrow-collapse' : 'mdi-arrow-expand'" />
              <span>{{ chatExpanded ? 'Minimize' : 'Maximize' }}</span>
            </button>
          </div>
        </header>

        <!-- Fixed-height scroll; latest at bottom via column-reverse -->
        <div
            ref="messagesContainer"
            class="px-4 sm:px-6 py-4 space-y-4 flex flex-col-reverse overflow-y-auto"
            :style="{ height: chatExpanded ? expandedHeight : compactHeight }"
            @scroll.passive="onScrollMessages"
        >
          <!-- sentinel -->
          <div ref="topSentinel"></div>

          <!-- Messages in ASC; visual bottom thanks to column-reverse -->
          <div
              v-for="(message, index) in messages"
              :key="index"
              :class="[
              'flex items-start gap-3',
              message.isSupport ? 'justify-start' : 'justify-end',
            ]"
          >
            <!-- Left aligned (support): avatar left, bubble right -->
            <template v-if="message.isSupport">
              <img
                  :src="message.author.avatar"
                  :alt="message.author.name"
                  class="w-8 h-8 rounded-full border border-gray-200 dark:border-gray-700 shrink-0"
              />
              <div class="max-w-[82%] sm:max-w-[75%]">
                <div class="text-xs text-gray-700 dark:text-gray-300 mb-1">
                  {{ message.author.name }} • {{ message.author.email }}
                </div>
                <div class="p-3 rounded-2xl text-sm shadow-sm break-words ring-1 bg-gray-50 text-gray-900 ring-gray-200 dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-700">
                  <p class="leading-relaxed">{{ message.text }}</p>
                  <div v-if="message.attachments?.length" class="mt-2 grid grid-cols-2 gap-2">
                    <div v-for="(file, i) in message.attachments" :key="i" class="text-xs">
                      <template v-if="isImageUrl(file.url)">
                        <img :src="file.url" :alt="file.name || 'image'" class="max-h-40 w-auto rounded-md border border-gray-200 dark:border-gray-700" />
                        <div class="mt-1 text-[11px] flex items-center justify-between">
                          <span class="truncate">{{ file.name || 'image' }}</span>
                          <span class="text-gray-700 dark:text-gray-300">{{ file.size ? humanSize(file.size) : '' }}</span>
                        </div>
                      </template>
                      <template v-else>
                        <a :href="file.url" target="_blank" class="inline-flex items-center gap-1 underline underline-offset-2 text-blue-800 dark:text-blue-300">
                          📎 {{ file.name || 'file' }}
                        </a>
                        <span class="ml-1 text-gray-700 dark:text-gray-300">{{ file.size ? humanSize(file.size) : '' }}</span>
                      </template>
                    </div>
                  </div>
                  <div class="mt-1 text-[10px] text-gray-700 dark:text-gray-300">
                    {{ message.timestamp }}
                  </div>
                </div>
              </div>
            </template>

            <!-- Right aligned (user): bubble left, avatar right -->
            <template v-else>
              <div class="max-w-[82%] sm:max-w-[75%]">
                <div class="text-right text-xs text-gray-700 dark:text-gray-300 mb-1">
                  {{ message.author.name }} • {{ message.author.email }}
                </div>
                <div class="p-3 rounded-2xl text-sm shadow-sm break-words ring-1 bg-blue-500 text-white ring-blue-600 dark:bg-blue-600 dark:ring-blue-700">
                  <p class="leading-relaxed">{{ message.text }}</p>
                  <div v-if="message.attachments?.length" class="mt-2 grid grid-cols-2 gap-2">
                    <div v-for="(file, i) in message.attachments" :key="i" class="text-xs">
                      <template v-if="isImageUrl(file.url)">
                        <img :src="file.url" :alt="file.name || 'image'" class="max-h-40 w-auto rounded-md border border-gray-200 dark:border-gray-700" />
                        <div class="mt-1 text-[11px] flex items-center justify-between">
                          <span class="truncate">{{ file.name || 'image' }}</span>
                          <span class="text-white dark:text-gray-100">{{ file.size ? humanSize(file.size) : '' }}</span>
                        </div>
                      </template>
                      <template v-else>
                        <a :href="file.url" target="_blank" class="inline-flex items-center gap-1 underline underline-offset-2 text-white dark:text-gray-100">
                          📎 {{ file.name || 'file' }}
                        </a>
                        <span class="ml-1 text-white dark:text-gray-100">{{ file.size ? humanSize(file.size) : '' }}</span>
                      </template>
                    </div>
                  </div>
                  <div class="mt-1 text-[10px] text-white dark:text-gray-100 text-right">
                    {{ message.timestamp }}
                  </div>
                </div>
              </div>
              <img
                  :src="message.author.avatar"
                  :alt="message.author.name"
                  class="w-8 h-8 rounded-full border border-gray-200 dark:border-gray-700 shrink-0"
              />
            </template>
          </div>

          <div v-if="showNoNewBanner" class="flex items-center justify-center py-2">
            <span class="text-xs text-gray-600 dark:text-gray-400">No new messages</span>
          </div>
        </div>

        <!-- Composer (unchanged) -->
        <form novalidate @submit.prevent="submitReply" class="px-4 sm:px-6 py-3 border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 rounded-b-lg">
          <div class="mb-2 text-xs text-gray-700 dark:text-gray-400">
            Send a message and optionally attach up to 10 images (JPG, PNG, GIF, WEBP, AVIF). Max size per image: {{ humanSize(MAX_FILE_SIZE) }}.
          </div>
          <div class="flex items-end gap-2">
            <div class="flex-1">
              <textarea
                  ref="textareaRef"
                  v-model.trim="replyText"
                  @input="autoResize"
                  rows="1"
                  placeholder="Type a message to support…"
                  class="w-full resize-none overflow-hidden bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :aria-invalid="!!fieldErrors.message"
              ></textarea>
              <p v-if="fieldErrors.message" class="mt-1 text-xs text-red-600">
                {{ fieldErrors.message }}
              </p>
            </div>
            <div class="flex items-center gap-2">
              <label class="cursor-pointer p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 shrink-0" title="Attach images">
                📎
                <input type="file" accept="image/*" multiple @change="handleFileChange" class="hidden" />
              </label>
              <button
                  type="submit"
                  :disabled="isSending || !canSubmit"
                  class="shrink-0 inline-flex items-center justify-center px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <Icon v-if="isSending" name="mdi-loading" class="animate-spin mr-2" />
                <span>{{ isSending ? 'Sending…' : 'Send' }}</span>
              </button>
            </div>
          </div>
          <p v-if="fieldErrors.attachments" class="mt-2 text-xs text-red-600">
            {{ fieldErrors.attachments }}
          </p>
          <div v-if="attachments.length" class="mt-3 grid grid-cols-3 sm:grid-cols-5 gap-3 text-xs text-gray-800 dark:text-gray-200">
            <div v-for="(file, index) in attachments" :key="index" class="relative group">
              <img :src="objectUrl(file)" :alt="file.name" class="h-20 w-20 object-cover rounded-md border border-gray-200 dark:border-gray-700" />
              <div class="mt-1 w-20 truncate">{{ file.name }}</div>
              <div class="text-[11px] text-gray-700 dark:text-gray-300">{{ humanSize(file.size) }}</div>
              <button
                  type="button"
                  @click="removeAttachment(index)"
                  class="absolute -top-1.5 -right-1.5 bg-red-600 text-white text-[10px] rounded-full px-1.5 py-0.5 opacity-90 hover:opacity-100"
                  aria-label="Remove attachment"
                  title="Remove"
              >
                ✕
              </button>
            </div>
          </div>
        </form>
      </section>

      <!-- Ticket Information (unchanged) -->
      <section v-if="!chatExpanded" class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm">
        <header class="px-4 sm:px-6 py-3 border-b border-gray-100 dark:border-gray-800">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ticket Information</h2>
        </header>
        <div class="px-4 sm:px-6 py-4 sm:py-6 space-y-4">
          <!-- fields ... -->
          <div class="flex items-start gap-3">
            <Icon name="mdi-format-title" class="text-gray-600 dark:text-gray-400 mt-1" />
            <div class="min-w-0">
              <div class="text-xs text-gray-600 dark:text-gray-400">Title</div>
              <div class="text-base font-medium text-gray-900 dark:text-gray-100 truncate">{{ ticket.title }}</div>
            </div>
          </div>

          <div class="flex items-start gap-3">
            <Icon name="mdi-information" class="text-gray-600 dark:text-gray-400 mt-1" />
            <div>
              <div class="text-xs text-gray-600 dark:text-gray-400">Status</div>
              <span
                  :class="[
                  'inline-block px-3 py-1 text-xs font-semibold rounded-full',
                  ticket.status === 'Open'
                    ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                    : ticket.status === 'Resolved'
                      ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                      : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                ]"
              >
                {{ ticket.status }}
              </span>
            </div>
          </div>

          <div class="flex items-start gap-3">
            <Icon name="mdi-alert-circle" class="text-gray-600 dark:text-gray-400 mt-1" />
            <div>
              <div class="text-xs text-gray-600 dark:text-gray-400">Priority</div>
              <span
                  :class="[
                  'inline-block px-3 py-1 text-xs font-semibold rounded-full',
                  ticket.priority === 'High'
                    ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200'
                    : ticket.priority === 'Medium'
                      ? 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-200'
                      : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                ]"
              >
                {{ ticket.priority }}
              </span>
            </div>
          </div>

          <div class="flex items-start gap-3">
            <Icon name="mdi-calendar-clock" class="text-gray-600 dark:text-gray-400 mt-1" />
            <div>
              <div class="text-xs text-gray-600 dark:text-gray-400">Created At</div>
              <div class="text-base text-gray-900 dark:text-gray-100">{{ ticket.createdAt }}</div>
            </div>
          </div>

          <div class="flex items-start gap-3">
            <Icon name="mdi-text" class="text-gray-600 dark:text-gray-400 mt-1" />
            <div class="min-w-0">
              <div class="text-xs text-gray-600 dark:text-gray-400">Description</div>
              <div class="text-base text-gray-800 dark:text-gray-300 whitespace-pre-line break-words">{{ ticket.description }}</div>
            </div>
          </div>

          <div v-if="ticketAttachments.length" class="space-y-2">
            <div class="text-xs text-gray-600 dark:text-gray-400">Ticket Attachments</div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <a v-for="(url, i) in ticketAttachments" :key="i" :href="url" target="_blank" class="block">
                <img :src="url" alt="ticket attachment" class="rounded-md border border-gray-200 dark:border-gray-700 max-h-36 w-auto" />
              </a>
            </div>
          </div>
        </div>
      </section>
    </div>

    <div v-if="!isLoading">
      <NuxtLink to="/dashboard/helpdesk">
        <button class="px-4 py-2 bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-700">
          ← Back to List
        </button>
      </NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { nextTick, onMounted, ref, computed } from 'vue'
import { useRuntimeConfig, useSanctumFetch, useToast } from '#imports'

definePageMeta({ layout: 'dashboard' })

const route = useRoute()
const toast = useToast()
const config = useRuntimeConfig()

// Heights: chat has its own scrollbar; page remains compact
const compactHeight = '480px'
const expandedHeight = '68vh'

type TicketVM = {
  uuid: string
  title: string
  status: string
  priority: string
  createdAt: string
  description: string
  author?: { email: string; name: string; fingerprint: string; avatar?: string }
}

type MessageFile = { name?: string; url: string; size?: number }
type MessageVM = {
  isSupport: boolean
  from: 'user' | 'support'
  text: string
  timestamp: string
  author: { email: string; name: string; fingerprint: string; avatar?: string }
  attachments?: Array<MessageFile>
}

const ticket = ref<TicketVM>({ uuid: '', title: '', status: '', priority: '', createdAt: '', description: '' })
const ticketAttachments = ref<string[]>([])
const messages = ref<MessageVM[]>([])
const replyText = ref('')
const attachments = ref<File[]>([])
const textareaRef = ref<HTMLTextAreaElement | null>(null)
const messagesContainer = ref<HTMLElement | null>(null)

const isLoading = ref(true)
const isSending = ref(false)
const chatExpanded = ref(false)
const showNoNewBanner = ref(false)
const isFetchingOlder = ref(false)
const reachedBeginning = ref(false)

const MAX_FILE_SIZE = 1.5 * 1024 * 1024
const MAX_FILES = 10
const TOP_TRIGGER_PX = 120

const fieldErrors = ref<{ message?: string; attachments?: string }>({})

// Helpers
const objectUrl = (file: File) => URL.createObjectURL(file)
const humanSize = (bytes: number) => { const u = ['B','KB','MB','GB']; let i=0, n=bytes; while(n>=1024&&i<u.length-1){n/=1024;i++} return `${n.toFixed(1)} ${u[i]}` }
const IMG_EXT_RE = /\.(jpe?g|png|gif|webp|avif)(?:\?.*)?$/i
const isImageUrl = (url: string) => { try { return !!url && IMG_EXT_RE.test(url) } catch { return false } }
const safeFileName = (url: string, fallback = 'image') => { try { const u = new URL(url); return (u.pathname.split('/').pop() || fallback) } catch { return (url.split('/').pop() || fallback) } }

const autoResize = () => { const el = textareaRef.value; if (!el) return; el.style.height='auto'; el.style.height=`${el.scrollHeight}px` }

const validateForm = () => {
  fieldErrors.value = {}
  if (!replyText.value.trim() && attachments.value.length === 0) fieldErrors.value.message = 'Please type a message or attach at least one image.'
  if (attachments.value.length > MAX_FILES) fieldErrors.value.attachments = `You can attach up to ${MAX_FILES} images.`
  for (const f of attachments.value) {
    if (!f.type.startsWith('image/')) { fieldErrors.value.attachments = 'Only image files are allowed.'; break }
    if (f.size > MAX_FILE_SIZE) { fieldErrors.value.attachments = `Each image must be ≤ ${humanSize(MAX_FILE_SIZE)}.`; break }
  }
  return Object.keys(fieldErrors.value).length === 0
}

const handleFileChange = (e: Event) => {
  const input = e.target as HTMLInputElement
  if (!input.files) return
  const selected = Array.from(input.files)
  const combined = [...attachments.value, ...selected]
  const filtered: File[] = []
  for (const f of combined) {
    if (!f.type.startsWith('image/')) { toast.error({ title: 'Invalid File', message: `❌ ${f.name} is not an image`, timeout: 2500 }); continue }
    if (f.size > MAX_FILE_SIZE) { toast.error({ title: 'Too Large', message: `❌ ${f.name} exceeds ${humanSize(MAX_FILE_SIZE)}`, timeout: 2500 }); continue }
    filtered.push(f)
  }
  if (filtered.length > MAX_FILES) toast.error({ title: 'Too Many Files', message: `❌ Max ${MAX_FILES} images allowed`, timeout: 2500 })
  attachments.value = filtered.slice(0, MAX_FILES)
  input.value = ''
}

const removeAttachment = (i: number) => attachments.value.splice(i, 1)

async function loadTicket(showDeltaBanner = false) {
  try {
    if (!messages.value.length) isLoading.value = true
    const uuid = route.params.url
    const res: any = await useSanctumFetch(`${config.public.apiBase}/helpdesk/tickets/${uuid}`, { method: 'GET', credentials: 'include' })
    const payload = res.data?.value ?? res?.value ?? res
    if (!payload?.ticket) throw new Error('No ticket data returned')

    const t = payload.ticket
    ticket.value = {
      uuid: t.uuid,
      title: t.title,
      status: t.status,
      priority: t.priority,
      createdAt: new Date(t.created_at).toLocaleString(),
      description: t.description ?? '',
      author: t.author ? { email: t.author.email, name: t.author.name, fingerprint: t.author.fingerprint, avatar: t.author.avatar } : undefined
    }
    ticketAttachments.value = Array.isArray(t.attachment) ? t.attachment : []

    // Determine "user" vs "support" by fingerprint against ticket.author.fingerprint
    const ticketUserFp = ticket.value.author?.fingerprint || null

    const prevCount = messages.value.length
    messages.value = (payload.conversations ?? []).map((c: any) => {
      const author = {
        email: c.author?.email || '',
        name: c.author?.name || 'Unknown',
        fingerprint: c.author?.fingerprint || '',
        avatar: c.author?.avatar || ''
      }
      const isSupport = ticketUserFp ? (author.fingerprint !== ticketUserFp) : true
      return {
        isSupport,
        from: isSupport ? 'support' : 'user',
        text: c.message,
        timestamp: new Date(c.created_at).toLocaleString(),
        author,
        attachments: (Array.isArray(c.attachment) ? c.attachment : []).map((url: string) => ({ url, name: safeFileName(url) }))
      }
    })

    await nextTick()
    if (messagesContainer.value) messagesContainer.value.scrollTop = 0

    if (showDeltaBanner && prevCount === messages.value.length) {
      showNoNewBanner.value = true
      setTimeout(() => (showNoNewBanner.value = false), 1500)
    }
  } catch (e) {
    console.error('Failed to load ticket', e)
    toast.error({ title: 'Error', message: '❌ Failed to load ticket', timeout: 3000 })
  } finally {
    isLoading.value = false
  }
}

const onScrollMessages = async () => {
  const el = messagesContainer.value
  if (!el || isFetchingOlder.value || reachedBeginning.value) return
  if (el.scrollTop <= TOP_TRIGGER_PX) await loadOlder()
}

async function loadOlder() {
  const el = messagesContainer.value
  if (!el) return
  isFetchingOlder.value = true
  const prevScrollHeight = el.scrollHeight

  // TODO: implement real older fetch
  const older: MessageVM[] = []
  if (!older.length) {
    reachedBeginning.value = true
    isFetchingOlder.value = false
    return
  }

  messages.value = [...older, ...messages.value]
  await nextTick()
  const newScrollHeight = el.scrollHeight
  el.scrollTop += (newScrollHeight - prevScrollHeight)
  isFetchingOlder.value = false
}

const canSubmit = computed(() => !!replyText.value.trim() || attachments.value.length > 0)

async function submitReply() {
  if (!validateForm()) {
    const first = fieldErrors.value.message || fieldErrors.value.attachments
    if (first) toast.error({ title: 'Validation error', message: first, timeout: 2500 })
    return
  }
  try {
    isSending.value = true
    const uuid = route.params.url
    const fd = new FormData()
    fd.append('message', replyText.value)
    attachments.value.forEach(f => fd.append('attachments[]', f))
    const res: any = await useSanctumFetch(`${config.public.apiBase}/helpdesk/tickets/${uuid}/reply`, { method: 'POST', body: fd, credentials: 'include' })
    if (res.error?.value) throw new Error('Reply failed')
    replyText.value = ''
    attachments.value = []
    await loadTicket(true)
    await nextTick()
    if (textareaRef.value) textareaRef.value.style.height = 'auto'
    if (messagesContainer.value) messagesContainer.value.scrollTop = 0
  } catch (e) {
    console.error('Reply failed', e)
    toast.error({ title: 'Error', message: '❌ Failed to send reply', timeout: 3000 })
  } finally {
    isSending.value = false
  }
}

onMounted(() => loadTicket())
</script>
