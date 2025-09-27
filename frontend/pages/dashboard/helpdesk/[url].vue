<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <!-- Floating Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div ref="orb1" class="absolute -top-32 -right-32 w-96 h-96 bg-blue-400/10 dark:bg-blue-400/5 rounded-full blur-3xl opacity-60 animate-pulse"></div>
      <div ref="orb2" class="absolute -bottom-32 -left-32 w-80 h-80 bg-purple-400/10 dark:bg-purple-400/5 rounded-full blur-3xl opacity-50 animate-pulse"></div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center min-h-screen relative z-10">
      <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-8 shadow-xl">
        <div class="flex flex-col items-center gap-4">
          <div class="w-12 h-12 border-4 border-blue-200 dark:border-blue-800 border-t-blue-600 dark:border-t-blue-400 rounded-full animate-spin"></div>
          <p class="text-slate-600 dark:text-slate-400 font-medium">Loading ticket...</p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else class="relative z-10 max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">

      <!-- Header -->
      <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm mb-4">
          <NuxtLink to="/dashboard" class="flex items-center gap-1 text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400">
            <Icon name="mdi:view-dashboard" class="w-4 h-4" />
            <span>Dashboard</span>
          </NuxtLink>
          <Icon name="mdi:chevron-right" class="w-4 h-4 text-slate-400" />
          <NuxtLink to="/dashboard/helpdesk" class="flex items-center gap-1 text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400">
            <Icon name="mdi:ticket" class="w-4 h-4" />
            <span>Help Desk</span>
          </NuxtLink>
          <Icon name="mdi:chevron-right" class="w-4 h-4 text-slate-400" />
          <span class="text-slate-700 dark:text-slate-300 font-medium">Ticket Details</span>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <!-- Title Section -->
          <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
              <Icon name="mdi:ticket-outline" class="w-8 h-8 text-white" />
            </div>
            <div>
              <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ ticket.title }}</h1>
              <p class="text-slate-500 dark:text-slate-400">ID: <span class="font-mono font-medium">{{ ticket.uuid.slice(0, 8) }}</span></p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-3">
            <button
                @click="chatExpanded = !chatExpanded"
                class="flex items-center gap-2 px-4 py-2 border-2 border-slate-200 dark:border-slate-600 rounded-xl hover:border-blue-300 dark:hover:border-blue-500 transition-colors"
                :class="chatExpanded ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-500 text-blue-600 dark:text-blue-400' : 'bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-400'"
            >
              <Icon :name="chatExpanded ? 'mdi:arrow-collapse' : 'mdi:arrow-expand'" class="w-5 h-5" />
              <span>{{ chatExpanded ? 'Minimize' : 'Expand' }}</span>
            </button>

            <NuxtLink to="/dashboard/helpdesk" class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl transition-colors text-slate-600 dark:text-slate-400">
              <Icon name="mdi:arrow-left" class="w-5 h-5" />
              <span>Back</span>
            </NuxtLink>
          </div>
        </div>

        <!-- Stats -->
        <div class="flex flex-wrap gap-4 mt-6">
          <div class="flex items-center gap-3 px-4 py-2 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="getStatusBgClass(ticket.status)">
              <Icon :name="getStatusIcon(ticket.status)" class="w-5 h-5" :class="getStatusTextClass(ticket.status)" />
            </div>
            <div>
              <div class="text-xs text-slate-500 dark:text-slate-400">Status</div>
              <div class="font-semibold" :class="getStatusTextClass(ticket.status)">{{ ticket.status }}</div>
            </div>
          </div>

          <div class="flex items-center gap-3 px-4 py-2 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="getPriorityBgClass(ticket.priority)">
              <Icon :name="getPriorityIcon(ticket.priority)" class="w-5 h-5" :class="getPriorityTextClass(ticket.priority)" />
            </div>
            <div>
              <div class="text-xs text-slate-500 dark:text-slate-400">Priority</div>
              <div class="font-semibold" :class="getPriorityTextClass(ticket.priority)">{{ ticket.priority }}</div>
            </div>
          </div>

          <div class="flex items-center gap-3 px-4 py-2 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
              <Icon name="mdi:message-text" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
              <div class="text-xs text-slate-500 dark:text-slate-400">Messages</div>
              <div class="font-semibold text-blue-600 dark:text-blue-400">{{ messages.length }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Grid -->
      <div class="grid gap-6" :class="chatExpanded ? 'grid-cols-1' : 'grid-cols-1 lg:grid-cols-3'">

        <!-- Chat Panel -->
        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl overflow-hidden" :class="chatExpanded ? 'col-span-1' : 'lg:col-span-2'">
          <!-- Chat Header -->
          <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-700/30">
            <div class="flex items-center gap-3">
              <Icon name="mdi:forum" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
              <div>
                <h3 class="font-semibold text-slate-900 dark:text-white">Live Conversation</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Chat with support team</p>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <button
                  @click="loadTicket(true)"
                  :disabled="isLoading"
                  class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400 disabled:opacity-50"
              >
                <Icon name="mdi:refresh" class="w-4 h-4" :class="{ 'animate-spin': isLoading }" />
              </button>

              <div class="flex items-center gap-2 text-sm">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-slate-600 dark:text-slate-400 font-medium">Live</span>
              </div>
            </div>
          </div>

          <!-- Messages Area -->
          <div
              ref="messagesContainer"
              class="overflow-y-auto bg-slate-50/30 dark:bg-slate-700/20 p-4 space-y-4"
              :style="{ height: chatExpanded ? '70vh' : '480px' }"
              @scroll.passive="onScrollMessages"
          >
            <!-- Loading Banner -->
            <div v-if="showNoNewBanner" class="flex items-center justify-center gap-2 py-2 px-4 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-xl text-sm font-medium mx-auto w-fit">
              <Icon name="mdi:check-circle" class="w-4 h-4" />
              <span>No new messages</span>
            </div>

            <!-- Messages -->
            <div
                v-for="(message, index) in messages"
                :key="index"
                class="flex gap-3"
                :class="message.isSupport ? 'justify-start' : 'justify-end'"
            >

              <!-- Support Message -->
              <template v-if="message.isSupport">
                <div class="relative">
                  <img
                      :src="message.author.avatar || getDefaultAvatar(message.author.name)"
                      :alt="message.author.name"
                      class="w-10 h-10 rounded-full border-2 border-white dark:border-slate-700 shadow-sm"
                      @error="handleAvatarError"
                  />
                  <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-blue-500 border-2 border-white dark:border-slate-800 rounded-full"></div>
                </div>

                <div class="max-w-[75%] space-y-1">
                  <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <span class="font-medium">{{ message.author.name }}</span>
                    <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full font-medium">Support</span>
                    <span>{{ formatRelativeTime(message.timestamp) }}</span>
                  </div>

                  <div class="bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-2xl rounded-tl-md p-4 shadow-sm">
                    <p class="text-slate-800 dark:text-slate-200 text-sm leading-relaxed">{{ message.text }}</p>

                    <!-- Attachments -->
                    <div v-if="message.attachments?.length" class="grid grid-cols-2 gap-2 mt-3">
                      <div v-for="(file, i) in message.attachments" :key="i">
                        <template v-if="isImageUrl(file.url)">
                          <div class="relative group">
                            <img :src="file.url" :alt="file.name" class="w-full h-32 object-cover rounded-lg border border-slate-200 dark:border-slate-600" />
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                              <a :href="file.url" target="_blank" class="flex items-center gap-1 text-white text-sm font-medium">
                                <Icon name="mdi:magnify-plus" class="w-4 h-4" />
                                View
                              </a>
                            </div>
                          </div>
                        </template>
                        <template v-else>
                          <a :href="file.url" target="_blank" class="flex items-center gap-2 p-3 bg-slate-100 dark:bg-slate-600 hover:bg-slate-200 dark:hover:bg-slate-500 rounded-lg transition-colors">
                            <Icon name="mdi:file-document" class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300 truncate">{{ file.name }}</span>
                          </a>
                        </template>
                      </div>
                    </div>

                    <div class="flex items-center justify-end gap-1 mt-2 text-xs text-slate-400">
                      <Icon name="mdi:clock-outline" class="w-3 h-3" />
                      <span>{{ new Date(message.timestamp).toLocaleTimeString() }}</span>
                    </div>
                  </div>
                </div>
              </template>

              <!-- User Message -->
              <template v-else>
                <div class="max-w-[75%] space-y-1">
                  <div class="flex items-center justify-end gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <span>{{ formatRelativeTime(message.timestamp) }}</span>
                    <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full font-medium">You</span>
                    <span class="font-medium">{{ message.author.name }}</span>
                  </div>

                  <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-2xl rounded-tr-md p-4 shadow-sm">
                    <p class="text-sm leading-relaxed">{{ message.text }}</p>

                    <!-- Attachments -->
                    <div v-if="message.attachments?.length" class="grid grid-cols-2 gap-2 mt-3">
                      <div v-for="(file, i) in message.attachments" :key="i">
                        <template v-if="isImageUrl(file.url)">
                          <div class="relative group">
                            <img :src="file.url" :alt="file.name" class="w-full h-32 object-cover rounded-lg border border-blue-400" />
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                              <a :href="file.url" target="_blank" class="flex items-center gap-1 text-white text-sm font-medium">
                                <Icon name="mdi:magnify-plus" class="w-4 h-4" />
                                View
                              </a>
                            </div>
                          </div>
                        </template>
                        <template v-else>
                          <a :href="file.url" target="_blank" class="flex items-center gap-2 p-3 bg-blue-500/30 hover:bg-blue-500/50 rounded-lg transition-colors">
                            <Icon name="mdi:file-document" class="w-5 h-5 text-white/80" />
                            <span class="text-sm font-medium text-white truncate">{{ file.name }}</span>
                          </a>
                        </template>
                      </div>
                    </div>

                    <div class="flex items-center justify-end gap-1 mt-2 text-xs text-blue-200">
                      <Icon name="mdi:clock-outline" class="w-3 h-3" />
                      <span>{{ new Date(message.timestamp).toLocaleTimeString() }}</span>
                    </div>
                  </div>
                </div>

                <div class="relative">
                  <img
                      :src="message.author.avatar || getDefaultAvatar(message.author.name)"
                      :alt="message.author.name"
                      class="w-10 h-10 rounded-full border-2 border-white dark:border-slate-700 shadow-sm"
                      @error="handleAvatarError"
                  />
                  <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white dark:border-slate-800 rounded-full"></div>
                </div>
              </template>
            </div>
          </div>

          <!-- Message Composer -->
          <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50">
            <form @submit.prevent="submitReply" class="space-y-3">
              <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1">
                <Icon name="mdi:information" class="w-4 h-4 text-blue-500" />
                Send message or attach up to {{ MAX_FILES }} images (max {{ humanSize(MAX_FILE_SIZE) }} each)
              </div>

              <div class="flex gap-3 items-end">
                <div class="flex-1">
                  <textarea
                      ref="textareaRef"
                      v-model="replyText"
                      @input="autoResize"
                      rows="1"
                      placeholder="Type your message..."
                      class="w-full resize-none bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-3 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                      :class="{ 'border-red-500 dark:border-red-400': fieldErrors.message }"
                  ></textarea>
                  <p v-if="fieldErrors.message" class="text-xs text-red-500 dark:text-red-400 mt-1 flex items-center gap-1">
                    <Icon name="mdi:alert-circle" class="w-3 h-3" />
                    {{ fieldErrors.message }}
                  </p>
                </div>

                <div class="flex items-center gap-2">
                  <label class="w-10 h-10 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg flex items-center justify-center cursor-pointer transition-colors">
                    <Icon name="mdi:paperclip" class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                    <input type="file" accept="image/*" multiple @change="handleFileChange" class="hidden" />
                  </label>

                  <button
                      type="submit"
                      :disabled="isSending || !canSubmit"
                      class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded-xl font-medium shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                  >
                    <Icon :name="isSending ? 'mdi:loading' : 'mdi:send'" class="w-5 h-5" :class="{ 'animate-spin': isSending }" />
                    <span>{{ isSending ? 'Sending...' : 'Send' }}</span>
                  </button>
                </div>
              </div>

              <p v-if="fieldErrors.attachments" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                <Icon name="mdi:alert-circle" class="w-3 h-3" />
                {{ fieldErrors.attachments }}
              </p>
            </form>

            <!-- Attachments Preview -->
            <div v-if="attachments.length" class="mt-4 p-4 bg-slate-100 dark:bg-slate-700 rounded-xl">
              <div class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
                <Icon name="mdi:image-multiple" class="w-4 h-4" />
                {{ attachments.length }} attachment{{ attachments.length !== 1 ? 's' : '' }}
              </div>
              <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
                <div
                    v-for="(file, index) in attachments"
                    :key="index"
                    class="relative group"
                >
                  <img :src="objectUrl(file)" :alt="file.name" class="w-full aspect-square object-cover rounded-lg border border-slate-200 dark:border-slate-600" />
                  <button
                      type="button"
                      @click="removeAttachment(index)"
                      class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-lg transition-colors"
                  >
                    <Icon name="mdi:close" class="w-3 h-3" />
                  </button>
                  <div class="mt-1 text-xs text-slate-600 dark:text-slate-400 truncate">{{ file.name }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Ticket Info Panel -->
        <div v-if="!chatExpanded" class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl overflow-hidden">
          <!-- Panel Header -->
          <div class="flex items-center gap-3 p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-700/30">
            <Icon name="mdi:information-outline" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
            <div>
              <h3 class="font-semibold text-slate-900 dark:text-white">Ticket Information</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">Complete details and status</p>
            </div>
          </div>

          <div class="p-4 space-y-6">
            <!-- Status Cards -->
            <div class="grid grid-cols-1 gap-4">
              <div class="p-4 rounded-xl border-2 transition-all" :class="getStatusCardClass(ticket.status)">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-xl flex items-center justify-center" :class="getStatusBgClass(ticket.status)">
                    <Icon :name="getStatusIcon(ticket.status)" class="w-6 h-6" :class="getStatusTextClass(ticket.status)" />
                  </div>
                  <div>
                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Current Status</div>
                    <div class="font-bold text-lg" :class="getStatusTextClass(ticket.status)">{{ ticket.status }}</div>
                  </div>
                </div>
              </div>

              <div class="p-4 rounded-xl border-2 transition-all" :class="getPriorityCardClass(ticket.priority)">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-xl flex items-center justify-center" :class="getPriorityBgClass(ticket.priority)">
                    <Icon :name="getPriorityIcon(ticket.priority)" class="w-6 h-6" :class="getPriorityTextClass(ticket.priority)" />
                  </div>
                  <div>
                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Priority Level</div>
                    <div class="font-bold text-lg" :class="getPriorityTextClass(ticket.priority)">{{ ticket.priority }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Ticket Details -->
            <div class="space-y-4">
              <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center mt-0.5">
                  <Icon name="mdi:text" class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">Title</div>
                  <div class="font-medium text-slate-900 dark:text-white">{{ ticket.title }}</div>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center mt-0.5">
                  <Icon name="mdi:calendar" class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                </div>
                <div class="flex-1">
                  <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">Created</div>
                  <div class="font-medium text-slate-900 dark:text-white">{{ formatFullDate(ticket.createdAt) }}</div>
                  <div class="text-sm text-slate-500 dark:text-slate-400">{{ formatRelativeTime(ticket.createdAt) }}</div>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center mt-0.5">
                  <Icon name="mdi:account" class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                </div>
                <div class="flex-1">
                  <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">Created By</div>
                  <div class="font-medium text-slate-900 dark:text-white">{{ ticket.author?.name || 'Unknown' }}</div>
                  <div class="text-sm text-slate-500 dark:text-slate-400">{{ ticket.author?.email }}</div>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center mt-0.5">
                  <Icon name="mdi:text-box" class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                </div>
                <div class="flex-1">
                  <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Description</div>
                  <div class="max-h-32 overflow-y-auto p-3 bg-slate-50 dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ ticket.description }}</div>
                </div>
              </div>

              <!-- Original Attachments -->
              <div v-if="ticketAttachments.length" class="flex items-start gap-3">
                <div class="w-8 h-8 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center mt-0.5">
                  <Icon name="mdi:paperclip" class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                </div>
                <div class="flex-1">
                  <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Original Attachments</div>
                  <div class="grid grid-cols-2 gap-2">
                    <a
                        v-for="(url, i) in ticketAttachments"
                        :key="i"
                        :href="url"
                        target="_blank"
                        class="relative group aspect-square rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600"
                    >
                      <img :src="url" alt="attachment" class="w-full h-full object-cover" />
                      <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <div class="flex items-center gap-1 text-white text-sm font-medium">
                          <Icon name="mdi:eye" class="w-4 h-4" />
                          View
                        </div>
                      </div>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRuntimeConfig, useToast, useSanctumFetch, useRoute } from '#imports'

// GSAP imports (client-side only)
let gsap: any = null
if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

definePageMeta({ layout: 'dashboard' })

/* Core */
const config = useRuntimeConfig()
const toast = useToast()
const route = useRoute()

/* Types */
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

/* State */
const ticket = ref<TicketVM>({
  uuid: '', title: '', status: '', priority: '', createdAt: '', description: ''
})
const ticketAttachments = ref<string[]>([])
const messages = ref<MessageVM[]>([])
const replyText = ref('')
const attachments = ref<File[]>([])

const isLoading = ref(true)
const isSending = ref(false)
const chatExpanded = ref(false)
const showNoNewBanner = ref(false)
const isFetchingOlder = ref(false)
const reachedBeginning = ref(false)

const MAX_FILE_SIZE = 1.5 * 1024 * 1024
const MAX_FILES = 10
const TOP_TRIGGER_PX = 120

const fieldErrors = reactive<{ message?: string; attachments?: string }>({})

// Refs
const messagesContainer = ref<HTMLElement>()
const textareaRef = ref<HTMLTextAreaElement>()
const topSentinel = ref<HTMLElement>()
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()

/* Helper Functions */
function getStatusIcon(status: string) {
  const icons = {
    open: 'mdi:clock-outline',
    resolved: 'mdi:check-circle',
    closed: 'mdi:lock',
    pending: 'mdi:clock-fast'
  }
  return icons[status.toLowerCase() as keyof typeof icons] || 'mdi:help-circle'
}

function getStatusBgClass(status: string) {
  const classes = {
    open: 'bg-yellow-100 dark:bg-yellow-900/30',
    resolved: 'bg-green-100 dark:bg-green-900/30',
    closed: 'bg-slate-100 dark:bg-slate-700',
    pending: 'bg-blue-100 dark:bg-blue-900/30'
  }
  return classes[status.toLowerCase() as keyof typeof classes] || classes.pending
}

function getStatusTextClass(status: string) {
  const classes = {
    open: 'text-yellow-600 dark:text-yellow-400',
    resolved: 'text-green-600 dark:text-green-400',
    closed: 'text-slate-600 dark:text-slate-400',
    pending: 'text-blue-600 dark:text-blue-400'
  }
  return classes[status.toLowerCase() as keyof typeof classes] || classes.pending
}

function getStatusCardClass(status: string) {
  const classes = {
    open: 'border-yellow-200 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/10',
    resolved: 'border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/10',
    closed: 'border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/30',
    pending: 'border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/10'
  }
  return classes[status.toLowerCase() as keyof typeof classes] || classes.pending
}

function getPriorityIcon(priority: string) {
  const icons = {
    high: 'mdi:chevron-double-up',
    urgent: 'mdi:alert',
    medium: 'mdi:minus',
    low: 'mdi:chevron-double-down'
  }
  return icons[priority.toLowerCase() as keyof typeof icons] || 'mdi:minus'
}

function getPriorityBgClass(priority: string) {
  const classes = {
    high: 'bg-orange-100 dark:bg-orange-900/30',
    urgent: 'bg-red-100 dark:bg-red-900/30',
    medium: 'bg-yellow-100 dark:bg-yellow-900/30',
    low: 'bg-green-100 dark:bg-green-900/30'
  }
  return classes[priority.toLowerCase() as keyof typeof classes] || classes.medium
}

function getPriorityTextClass(priority: string) {
  const classes = {
    high: 'text-orange-600 dark:text-orange-400',
    urgent: 'text-red-600 dark:text-red-400',
    medium: 'text-yellow-600 dark:text-yellow-400',
    low: 'text-green-600 dark:text-green-400'
  }
  return classes[priority.toLowerCase() as keyof typeof classes] || classes.medium
}

function getPriorityCardClass(priority: string) {
  const classes = {
    high: 'border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/10',
    urgent: 'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/10',
    medium: 'border-yellow-200 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/10',
    low: 'border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/10'
  }
  return classes[priority.toLowerCase() as keyof typeof classes] || classes.medium
}

function getDefaultAvatar(name: string) {
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=3b82f6&color=fff&size=64`
}

function handleAvatarError(e: Event) {
  const img = e.target as HTMLImageElement
  const name = img.alt || 'User'
  img.src = getDefaultAvatar(name)
}

function formatRelativeTime(dateString: string) {
  const date = new Date(dateString)
  const now = new Date()
  const diff = now.getTime() - date.getTime()

  const minutes = Math.floor(diff / (1000 * 60))
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))

  if (days > 0) return `${days}d ago`
  if (hours > 0) return `${hours}h ago`
  if (minutes > 0) return `${minutes}m ago`
  return 'Just now'
}

function formatFullDate(dateString: string) {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const objectUrl = (file: File) => URL.createObjectURL(file)
const humanSize = (bytes: number) => {
  const units = ['B', 'KB', 'MB', 'GB']
  let i = 0, n = bytes
  while (n >= 1024 && i < units.length - 1) { n /= 1024; i++ }
  return `${n.toFixed(1)} ${units[i]}`
}

const IMG_EXT_RE = /\.(jpe?g|png|gif|webp|avif)(?:\?.*)?$/i
const isImageUrl = (url: string) => {
  try { return !!url && IMG_EXT_RE.test(url) } catch { return false }
}

const safeFileName = (url: string, fallback = 'image') => {
  try {
    const u = new URL(url)
    return u.pathname.split('/').pop() || fallback
  } catch {
    return url.split('/').pop() || fallback
  }
}

const autoResize = () => {
  const el = textareaRef.value
  if (!el) return
  el.style.height = 'auto'
  el.style.height = `${el.scrollHeight}px`
}

const validateForm = () => {
  fieldErrors.message = undefined
  fieldErrors.attachments = undefined

  if (!replyText.value.trim() && attachments.value.length === 0) {
    fieldErrors.message = 'Please type a message or attach at least one image.'
  }

  if (attachments.value.length > MAX_FILES) {
    fieldErrors.attachments = `You can attach up to ${MAX_FILES} images.`
  }

  for (const f of attachments.value) {
    if (!f.type.startsWith('image/')) {
      fieldErrors.attachments = 'Only image files are allowed.'
      break
    }
    if (f.size > MAX_FILE_SIZE) {
      fieldErrors.attachments = `Each image must be ≤ ${humanSize(MAX_FILE_SIZE)}.`
      break
    }
  }

  return !fieldErrors.message && !fieldErrors.attachments
}

const handleFileChange = (e: Event) => {
  const input = e.target as HTMLInputElement
  if (!input.files) return

  const selected = Array.from(input.files)
  const combined = [...attachments.value, ...selected]
  const filtered: File[] = []

  for (const f of combined) {
    if (!f.type.startsWith('image/')) {
      toast.error({ title: 'Invalid File', message: `${f.name} is not an image` })
      continue
    }
    if (f.size > MAX_FILE_SIZE) {
      toast.error({ title: 'Too Large', message: `${f.name} exceeds ${humanSize(MAX_FILE_SIZE)}` })
      continue
    }
    filtered.push(f)
  }

  if (filtered.length > MAX_FILES) {
    toast.error({ title: 'Too Many Files', message: `Max ${MAX_FILES} images allowed` })
  }

  attachments.value = filtered.slice(0, MAX_FILES)
  input.value = ''
}

const removeAttachment = (i: number) => attachments.value.splice(i, 1)
const canSubmit = computed(() => !!replyText.value.trim() || attachments.value.length > 0)

async function loadTicket(showDeltaBanner = false) {
  try {
    if (!messages.value.length) isLoading.value = true

    const uuid = route.params.url
    const res: any = await useSanctumFetch(`${config.public.apiBase}/helpdesk/tickets/${uuid}`, {
      method: 'GET', credentials: 'include'
    })

    const payload = res.data?.value ?? res?.value ?? res
    if (!payload?.ticket) throw new Error('No ticket data returned')

    const t = payload.ticket
    ticket.value = {
      uuid: t.uuid, title: t.title, status: t.status, priority: t.priority,
      createdAt: t.created_at, description: t.description ?? '',
      author: t.author ? { email: t.author.email, name: t.author.name, fingerprint: t.author.fingerprint, avatar: t.author.avatar } : undefined
    }

    ticketAttachments.value = Array.isArray(t.attachment) ? t.attachment : []
    const ticketUserFp = ticket.value.author?.fingerprint || null
    const prevCount = messages.value.length

    messages.value = (payload.conversations ?? []).map((c: any) => {
      const author = {
        email: c.author?.email || '', name: c.author?.name || 'Unknown',
        fingerprint: c.author?.fingerprint || '', avatar: c.author?.avatar || ''
      }
      const isSupport = ticketUserFp ? (author.fingerprint !== ticketUserFp) : true

      return {
        isSupport, from: isSupport ? 'support' : 'user', text: c.message, timestamp: c.created_at, author,
        attachments: (Array.isArray(c.attachment) ? c.attachment : []).map((url: string) => ({ url, name: safeFileName(url) }))
      }
    })

    await nextTick()
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }

    if (showDeltaBanner && prevCount === messages.value.length) {
      showNoNewBanner.value = true
      setTimeout(() => (showNoNewBanner.value = false), 1500)
    }
  } catch (e: any) {
    console.error('Failed to load ticket', e)
    toast.error({ title: 'Error', message: 'Failed to load ticket details' })
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

async function submitReply() {
  if (!validateForm()) {
    const first = fieldErrors.message || fieldErrors.attachments
    if (first) toast.error({ title: 'Validation Error', message: first })
    return
  }

  try {
    isSending.value = true
    const uuid = route.params.url
    const formData = new FormData()
    formData.append('message', replyText.value)
    attachments.value.forEach(f => formData.append('attachments[]', f))

    const res: any = await useSanctumFetch(`${config.public.apiBase}/helpdesk/tickets/${uuid}/reply`, {
      method: 'POST', body: formData, credentials: 'include'
    })

    if (res.error?.value) throw new Error('Reply failed')

    replyText.value = ''
    attachments.value = []
    await loadTicket(true)

    await nextTick()
    if (textareaRef.value) textareaRef.value.style.height = 'auto'
    if (messagesContainer.value) messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight

    toast.success({ title: 'Success', message: 'Message sent successfully!' })
  } catch (e: any) {
    console.error('Reply failed', e)
    toast.error({ title: 'Error', message: 'Failed to send reply' })
  } finally {
    isSending.value = false
  }
}

onMounted(async () => {
  await loadTicket()

  if (process.client && gsap) {
    gsap.to([orb1.value, orb2.value], {
      rotation: 360, duration: 20, repeat: -1, ease: 'none'
    })
  }
})
</script>

<style scoped>
/* Custom scrollbar for messages */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.5);
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: rgba(148, 163, 184, 0.7);
}
</style>
