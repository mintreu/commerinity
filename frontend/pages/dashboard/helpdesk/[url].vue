<template>
  <div class="p-6 max-w-screen-xl mx-auto space-y-10">
    <!-- Page Header -->
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Ticket Details</h1>

    <div class="flex flex-col lg:flex-row gap-6">
      <!-- Ticket Info -->
      <div class="flex-1 bg-white dark:bg-gray-900 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700 space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
          <Icon name="mdi-ticket" class="text-blue-600 dark:text-blue-400 text-xl" />
          <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Ticket Information</h2>
        </div>

        <!-- Title -->
        <div class="flex items-start gap-3">
          <Icon name="mdi-format-title" class="text-gray-500 dark:text-gray-400 mt-1" />
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Title</div>
            <div class="text-base font-medium text-gray-800 dark:text-gray-100">{{ ticket.title }}</div>
          </div>
        </div>

        <!-- Status -->
        <div class="flex items-start gap-3">
          <Icon name="mdi-information" class="text-gray-500 dark:text-gray-400 mt-1" />
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Status</div>
            <span
                :class="[
          'inline-block px-3 py-1 text-xs font-semibold rounded-full',
          ticket.status === 'Open'
            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
            : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
        ]"
            >
        {{ ticket.status }}
      </span>
          </div>
        </div>

        <!-- Priority -->
        <div class="flex items-start gap-3">
          <Icon name="mdi-alert-circle" class="text-gray-500 dark:text-gray-400 mt-1" />
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Priority</div>
            <span
                :class="[
          'inline-block px-3 py-1 text-xs font-semibold rounded-full',
          ticket.priority === 'High' ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'
            : ticket.priority === 'Medium' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300'
            : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
        ]"
            >
        {{ ticket.priority }}
      </span>
          </div>
        </div>

        <!-- Created At -->
        <div class="flex items-start gap-3">
          <Icon name="mdi-calendar-clock" class="text-gray-500 dark:text-gray-400 mt-1" />
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Created At</div>
            <div class="text-base text-gray-800 dark:text-gray-100">{{ ticket.createdAt }}</div>
          </div>
        </div>

        <!-- Description -->
        <div class="flex items-start gap-3">
          <Icon name="mdi-text" class="text-gray-500 dark:text-gray-400 mt-1" />
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Description</div>
            <div class="text-base text-gray-700 dark:text-gray-300">Ticket description goes here (static).</div>
          </div>
        </div>
      </div>


      <!-- Conversation Section -->
      <div class="flex-1 bg-white dark:bg-gray-900 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700 flex flex-col">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4">Conversation</h2>

        <!-- Messages -->
        <div class="h-96 overflow-y-auto pr-2 space-y-4 mb-4">
          <div
              v-for="(message, index) in messages"
              :key="index"
              :class="['flex items-start gap-3', message.from === 'support' ? 'flex-row' : 'flex-row-reverse']"
          >
            <div class="w-8 h-8 rounded-full bg-gray-300 dark:bg-gray-700 flex items-center justify-center text-white text-sm">
              <span v-if="message.from === 'support'">💬</span>
              <span v-else>👤</span>
            </div>
            <div
                :class="[
                'max-w-xs p-3 rounded-lg text-sm shadow',
                message.from === 'support'
                  ? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100'
                  : 'bg-blue-600 text-white dark:bg-blue-500'
              ]"
            >
              {{ message.text }}
              <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ message.timestamp }}</div>
            </div>
          </div>
        </div>

        <!-- Reply Input Box -->
        <form @submit.prevent="submitReply" class="relative flex flex-col gap-2">
          <!-- Textarea -->
          <textarea
              ref="textareaRef"
              v-model="replyText"
              @input="autoResize"
              rows="1"
              placeholder="Type your message..."
              class="w-full resize-y overflow-auto bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 border border-gray-300 dark:border-gray-700 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          ></textarea>

          <!-- Toolbar -->
          <div class="flex items-center justify-between px-1">
            <!-- Attachment -->
            <label class="cursor-pointer p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
              📎
              <input type="file" accept="image/*" multiple @change="handleFileChange" class="hidden" />
            </label>

            <!-- Send Button -->
            <button
                type="submit"
                :disabled="!replyText.trim()"
                class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 disabled:opacity-50"
            >
              📨
            </button>
          </div>
        </form>

        <!-- Attachment Previews -->
        <div v-if="attachments.length" class="mt-2 text-sm text-gray-600 dark:text-gray-400 space-y-1">
          <div v-for="(file, index) in attachments" :key="index">📎 {{ file.name }}</div>
        </div>
      </div>
    </div>

    <!-- Back Button -->
    <div>
      <NuxtLink to="/dashboard/helpdesk">
        <button class="px-4 py-2 bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-700">
          ← Back to List
        </button>
      </NuxtLink>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'dashboard'
})

const route = useRoute()
const replyText = ref('')
const attachments = ref([])
const textareaRef = ref(null)

const autoResize = () => {
  const el = textareaRef.value
  if (!el) return
  el.style.height = 'auto'
  el.style.height = `${el.scrollHeight}px`
}

const handleFileChange = (event) => {
  attachments.value = Array.from(event.target.files)
}

const submitReply = () => {
  if (!replyText.value.trim()) return

  messages.value.push({
    from: 'user',
    text: replyText.value + (attachments.value.length ? ` (📎 ${attachments.value.length} attachment${attachments.value.length > 1 ? 's' : ''})` : ''),
    timestamp: new Date().toLocaleString()
  })

  replyText.value = ''
  attachments.value = []

  // Reset textarea height manually after message sent
  nextTick(() => {
    if (textareaRef.value) {
      textareaRef.value.style.height = 'auto'
    }
  })
}

const tickets = [
  {id: 1, url: 'login-issue', title: 'Login issue', status: 'Open', priority: 'High', createdAt: '2025-07-21'},
  {
    id: 2,
    url: 'dashboard-bug',
    title: 'Bug in dashboard',
    status: 'Resolved',
    priority: 'Medium',
    createdAt: '2025-07-20'
  },
  {
    id: 3,
    url: 'file-upload-broken',
    title: 'File upload broken',
    status: 'Open',
    priority: 'Low',
    createdAt: '2025-07-19'
  },
  {
    id: 4,
    url: 'button-misaligned',
    title: 'Button misaligned',
    status: 'Open',
    priority: 'Low',
    createdAt: '2025-07-18'
  },
  {
    id: 5,
    url: 'password-reset-issue',
    title: 'Password reset not working',
    status: 'Open',
    priority: 'High',
    createdAt: '2025-07-17'
  },
  {id: 6, url: 'billing-error', title: 'Billing error', status: 'Resolved', priority: 'Medium', createdAt: '2025-07-16'}
]

const ticket = tickets.find(t => t.url === route.params.url)
if (!ticket) throw createError({statusCode: 404, message: 'Ticket not found'})

const messages = ref([
  {from: 'user', text: 'Hi, I’m having trouble logging into my account.', timestamp: '2025-07-21 09:00 AM'},
  {
    from: 'support',
    text: 'Thanks for reaching out! Could you confirm if you’re receiving any error message?',
    timestamp: '2025-07-21 09:05 AM'
  },
  {
    from: 'user',
    text: 'Yes, it says "Invalid credentials" even though the password is correct.',
    timestamp: '2025-07-21 09:07 AM'
  },
  {
    from: 'support',
    text: 'We’ve reset your password and sent an email. Please try again and let us know.',
    timestamp: '2025-07-21 09:15 AM'
  },
  {from: 'user', text: 'Got it. It’s working now. Thanks!', timestamp: '2025-07-21 09:20 AM'}
])
</script>
