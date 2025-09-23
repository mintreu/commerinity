<template>
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">

    <!-- Comments Header -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Icon name="mdi:comment-text-outline" class="w-6 h-6 text-indigo-600 dark:text-indigo-400"/>
          <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Customer Reviews</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              {{ totalComments }} {{ totalComments === 1 ? 'review' : 'reviews' }}
            </p>
          </div>
        </div>
        <div v-if="averageRating > 0" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
          <Icon name="mdi:star" class="w-4 h-4 text-yellow-400"/>
          <span>{{ averageRating.toFixed(1) }} average rating</span>
        </div>
      </div>
    </div>

    <!-- Comment Form Section - Only for Logged In Users -->
    <div v-if="isLoggedIn" class="p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-start gap-4">
        <!-- User Avatar -->
        <div class="flex-shrink-0">
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">
            {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
          </div>
        </div>

        <!-- Comment Form -->
        <div class="flex-1 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Share your experience with this product
            </label>
            <textarea
                v-model="newComment.text"
                :disabled="isSubmittingComment"
                rows="4"
                placeholder="Write your review here... Share details about quality, delivery, value for money, etc."
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white resize-none transition-all duration-200"
                :class="newComment.text.length > 500 ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
            ></textarea>
            <div class="flex justify-between items-center mt-2">
              <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ newComment.text.length }}/500 characters
              </div>
              <div v-if="newComment.text.length > 500" class="text-xs text-red-500">
                Please keep your review under 500 characters
              </div>
            </div>
          </div>

          <!-- Rating Stars -->
          <div class="flex items-center gap-4">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Rating:</span>
            <div class="flex gap-1">
              <button
                  v-for="star in 5"
                  :key="star"
                  @click="setRating(star)"
                  :disabled="isSubmittingComment"
                  class="text-2xl transition-colors duration-200 focus:outline-none"
                  :class="star <= newComment.rating
                    ? 'text-yellow-400 hover:text-yellow-500'
                    : 'text-gray-300 hover:text-yellow-300 dark:text-gray-600 dark:hover:text-yellow-400'"
              >
                ★
              </button>
            </div>
            <span class="text-sm text-gray-600 dark:text-gray-400">
              {{ newComment.rating > 0 ? `${newComment.rating} star${newComment.rating > 1 ? 's' : ''}` : 'Select rating' }}
            </span>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end">
            <button
                @click="submitComment"
                :disabled="!canSubmitComment || isSubmittingComment"
                class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 flex items-center gap-2"
            >
              <Icon
                  v-if="isSubmittingComment"
                  name="mdi:loading"
                  class="w-4 h-4 animate-spin"
              />
              <Icon
                  v-else
                  name="mdi:send"
                  class="w-4 h-4"
              />
              {{ isSubmittingComment ? 'Posting...' : 'Post Review' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Guest User - Login to Comment Banner -->
    <div v-else class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Icon name="mdi:account-circle" class="w-6 h-6 text-indigo-600 dark:text-indigo-400"/>
          <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Want to share your experience?</h3>
            <p class="text-xs text-gray-600 dark:text-gray-400">Login to write a review and help other customers</p>
          </div>
        </div>
        <NuxtLink
            :to="`/auth/login?redirect=${currentPath}`"
            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center gap-1"
        >
          <Icon name="mdi:login" class="w-4 h-4"/>
          Login
        </NuxtLink>
      </div>
    </div>

    <!-- Comments List - Show Top 5 -->
    <div class="divide-y divide-gray-200 dark:divide-gray-700">
      <!-- Loading State -->
      <div v-if="isLoadingComments" class="p-6">
        <div class="space-y-4">
          <div v-for="i in 3" :key="i" class="flex gap-4 animate-pulse">
            <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Top 5 Comments -->
      <div v-else-if="displayComments.length > 0">
        <div
            v-for="comment in topComments"
            :key="comment.id"
            class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-200"
        >
          <div class="flex gap-4">
            <!-- User Avatar -->
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-green-600 flex items-center justify-center text-white font-semibold text-sm">
                {{ comment.user_name?.charAt(0).toUpperCase() || 'A' }}
              </div>
            </div>

            <!-- Comment Content -->
            <div class="flex-1 min-w-0">
              <!-- User Info & Rating -->
              <div class="flex items-center gap-3 mb-2">
                <h4 class="font-medium text-gray-900 dark:text-white">
                  {{ comment.user_name || 'Anonymous' }}
                </h4>
                <div class="flex items-center gap-1">
                  <div class="flex">
                    <span
                        v-for="star in 5"
                        :key="star"
                        class="text-sm"
                        :class="star <= comment.rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                    >
                      ★
                    </span>
                  </div>
                  <span class="text-sm text-gray-600 dark:text-gray-400">
                    ({{ comment.rating }}/5)
                  </span>
                </div>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                  {{ formatCommentDate(comment.created_at) }}
                </span>
              </div>

              <!-- Comment Text -->
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                {{ comment.comment }}
              </p>

              <!-- Helpful Actions -->
              <div class="flex items-center gap-4 mt-3">
                <button
                    @click="toggleHelpful(comment)"
                    :disabled="!isLoggedIn"
                    class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <Icon name="mdi:thumb-up-outline" class="w-4 h-4"/>
                  <span>Helpful ({{ comment.helpful_count || 0 }})</span>
                </button>
                <button
                    :disabled="!isLoggedIn"
                    class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <Icon name="mdi:reply" class="w-4 h-4"/>
                  <span>Reply</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Comments State -->
      <div v-else class="p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
          <Icon name="mdi:comment-outline" class="w-8 h-8 text-gray-400"/>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
          No reviews yet
        </h3>
        <p class="text-gray-600 dark:text-gray-400 text-sm">
          Be the first to share your experience with this product
        </p>
      </div>
    </div>

    <!-- View All Comments Button - Show when comments > 5 -->
    <div v-if="displayComments.length > 5" class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
      <button
          @click="openAllCommentsModal"
          class="w-full px-4 py-3 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2"
      >
        <Icon name="mdi:comment-multiple" class="w-4 h-4"/>
        View All {{ displayComments.length }} Reviews
      </button>
    </div>

    <!-- Success/Error Messages -->
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 transform scale-95"
        enter-to-class="opacity-100 transform scale-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 transform scale-100"
        leave-to-class="opacity-0 transform scale-95"
    >
      <div v-if="message.text" class="p-4 border-t border-gray-200 dark:border-gray-700">
        <div
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm"
            :class="message.type === 'success'
              ? 'bg-green-50 text-green-800 border border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800'
              : 'bg-red-50 text-red-800 border border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800'"
        >
          <Icon
              :name="message.type === 'success' ? 'mdi:check-circle' : 'mdi:alert-circle'"
              class="w-4 h-4 flex-shrink-0"
          />
          <span>{{ message.text }}</span>
          <button
              @click="clearMessage"
              class="ml-auto hover:opacity-70"
          >
            <Icon name="mdi:close" class="w-4 h-4"/>
          </button>
        </div>
      </div>
    </Transition>

    <!-- Full Screen Comments Modal -->
    <Teleport to="body">
      <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
      >
        <div v-if="showAllCommentsModal" class="fixed inset-0 z-50 overflow-hidden">
          <!-- Backdrop -->
          <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeAllCommentsModal"></div>

          <!-- Modal Content -->
          <div class="fixed inset-0 overflow-hidden">
            <div class="flex min-h-full items-center justify-center p-4">
              <Transition
                  enter-active-class="transition ease-out duration-200"
                  enter-from-class="opacity-0 transform scale-95"
                  enter-to-class="opacity-100 transform scale-100"
                  leave-active-class="transition ease-in duration-150"
                  leave-from-class="opacity-100 transform scale-100"
                  leave-to-class="opacity-0 transform scale-95"
              >
                <div v-if="showAllCommentsModal" class="w-full max-w-4xl max-h-[90vh] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">

                  <!-- Modal Header -->
                  <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-3">
                        <Icon name="mdi:comment-multiple" class="w-6 h-6 text-indigo-600 dark:text-indigo-400"/>
                        <div>
                          <h2 class="text-xl font-bold text-gray-900 dark:text-white">All Customer Reviews</h2>
                          <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ displayComments.length }} reviews • {{ averageRating.toFixed(1) }} average rating
                          </p>
                        </div>
                      </div>
                      <button
                          @click="closeAllCommentsModal"
                          class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
                      >
                        <Icon name="mdi:close" class="w-6 h-6 text-gray-500 dark:text-gray-400"/>
                      </button>
                    </div>
                  </div>

                  <!-- Modal Content - All Comments -->
                  <div class="overflow-y-auto max-h-[calc(90vh-120px)] divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="comment in displayComments"
                        :key="comment.id"
                        class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-200"
                    >
                      <div class="flex gap-4">
                        <!-- User Avatar -->
                        <div class="flex-shrink-0">
                          <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-green-600 flex items-center justify-center text-white font-semibold">
                            {{ comment.user_name?.charAt(0).toUpperCase() || 'A' }}
                          </div>
                        </div>

                        <!-- Comment Content -->
                        <div class="flex-1 min-w-0">
                          <!-- User Info & Rating -->
                          <div class="flex items-center gap-3 mb-3">
                            <h4 class="font-semibold text-gray-900 dark:text-white">
                              {{ comment.user_name || 'Anonymous' }}
                            </h4>
                            <div class="flex items-center gap-1">
                              <div class="flex">
                                <span
                                    v-for="star in 5"
                                    :key="star"
                                    class="text-lg"
                                    :class="star <= comment.rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                                >
                                  ★
                                </span>
                              </div>
                              <span class="text-sm text-gray-600 dark:text-gray-400 ml-1">
                                ({{ comment.rating }}/5)
                              </span>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                              {{ formatCommentDate(comment.created_at) }}
                            </span>
                          </div>

                          <!-- Comment Text -->
                          <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                            {{ comment.comment }}
                          </p>

                          <!-- Helpful Actions -->
                          <div class="flex items-center gap-6">
                            <button
                                @click="toggleHelpful(comment)"
                                :disabled="!isLoggedIn"
                                class="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                              <Icon name="mdi:thumb-up-outline" class="w-4 h-4"/>
                              <span>Helpful ({{ comment.helpful_count || 0 }})</span>
                            </button>
                            <button
                                :disabled="!isLoggedIn"
                                class="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                              <Icon name="mdi:reply" class="w-4 h-4"/>
                              <span>Reply</span>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Load More in Modal -->
                    <div v-if="hasMoreComments" class="p-6 text-center border-t border-gray-200 dark:border-gray-700">
                      <button
                          @click="loadMoreComments"
                          :disabled="isLoadingMoreComments"
                          class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors duration-200 disabled:opacity-50 flex items-center gap-2 mx-auto"
                      >
                        <Icon
                            v-if="isLoadingMoreComments"
                            name="mdi:loading"
                            class="w-4 h-4 animate-spin"
                        />
                        {{ isLoadingMoreComments ? 'Loading...' : 'Load More Reviews' }}
                      </button>
                    </div>
                  </div>

                  <!-- Modal Footer -->
                  <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                    <div class="flex justify-between items-center">
                      <p class="text-sm text-gray-600 dark:text-gray-400">
                        Showing {{ displayComments.length }} of {{ totalComments }} reviews
                      </p>
                      <button
                          @click="closeAllCommentsModal"
                          class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-medium transition-colors duration-200"
                      >
                        Close
                      </button>
                    </div>
                  </div>

                </div>
              </Transition>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRuntimeConfig, useSanctum, useSanctumFetch } from '#imports'

// Sanctum composable for authentication
const { isLoggedIn, user } = useSanctum()

// Props
const props = defineProps({
  productId: {
    type: [String, Number],
    required: true
  },
  initialComments: {
    type: Array,
    default: () => []
  },
  autoLoad: {
    type: Boolean,
    default: true
  }
})

// Composables
const route = useRoute()
const config = useRuntimeConfig()
const currentPath = computed(() => route.fullPath)

// State
const comments = ref([...props.initialComments])
const isLoadingComments = ref(false)
const isSubmittingComment = ref(false)
const isLoadingMoreComments = ref(false)
const hasMoreComments = ref(true)
const commentsPage = ref(1)
const message = ref({ text: '', type: 'success' })
const showAllCommentsModal = ref(false)

// New Comment State
const newComment = ref({
  text: '',
  rating: 0
})

// Computed Properties
const canSubmitComment = computed(() => {
  return isLoggedIn.value &&
      newComment.value.text.trim().length > 0 &&
      newComment.value.text.length <= 500 &&
      newComment.value.rating > 0
})

const displayComments = computed(() => {
  return [...comments.value].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})

const topComments = computed(() => {
  return displayComments.value.slice(0, 5)
})

const totalComments = computed(() => {
  return comments.value.length
})

const averageRating = computed(() => {
  if (comments.value.length === 0) return 0
  const sum = comments.value.reduce((acc, comment) => acc + comment.rating, 0)
  return sum / comments.value.length
})

// Methods
const setRating = (rating) => {
  if (!isSubmittingComment.value) {
    newComment.value.rating = rating
  }
}

const openAllCommentsModal = () => {
  showAllCommentsModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeAllCommentsModal = () => {
  showAllCommentsModal.value = false
  document.body.style.overflow = 'auto'
}

async function fetchComments() {
  if (!props.productId) return

  try {
    isLoadingComments.value = true

    const res = await useSanctumFetch(`${config.public.apiBase}/products/${props.productId}/comments`, {
      method: 'GET',
      query: { page: 1, per_page: 10 }
    })

    if (res?.data) {
      comments.value = res.data
      hasMoreComments.value = res?.has_more || false
      commentsPage.value = 1
    }
  } catch (error) {
    console.error('[✘] Failed to load comments', error)
    showMessage('Failed to load comments. Please try again.', 'error')
  } finally {
    isLoadingComments.value = false
  }
}

async function submitComment() {
  if (!canSubmitComment.value || isSubmittingComment.value) return

  try {
    isSubmittingComment.value = true

    const res = await useSanctumFetch(`${config.public.apiBase}/products/${props.productId}/comments`, {
      method: 'POST',
      body: {
        comment: newComment.value.text.trim(),
        rating: newComment.value.rating
      }
    })

    if (res?.success) {
      // Add new comment to the beginning of the list
      const newCommentData = {
        id: res.data.id || Date.now(),
        comment: newComment.value.text.trim(),
        rating: newComment.value.rating,
        user_name: user.value?.name || 'You',
        helpful_count: 0,
        created_at: new Date().toISOString(),
        ...res.data
      }

      comments.value.unshift(newCommentData)

      // Reset form
      newComment.value = {
        text: '',
        rating: 0
      }

      showMessage('Review posted successfully! Thank you for sharing your experience.', 'success')
    }
  } catch (error) {
    console.error('[✘] Failed to submit comment', error)
    showMessage('Failed to post review. Please try again.', 'error')
  } finally {
    isSubmittingComment.value = false
  }
}

async function loadMoreComments() {
  if (!hasMoreComments.value || isLoadingMoreComments.value) return

  try {
    isLoadingMoreComments.value = true
    commentsPage.value++

    const res = await useSanctumFetch(`${config.public.apiBase}/products/${props.productId}/comments`, {
      method: 'GET',
      query: { page: commentsPage.value, per_page: 10 }
    })

    if (res?.data) {
      comments.value.push(...res.data)
      hasMoreComments.value = res?.has_more || false
    }
  } catch (error) {
    console.error('[✘] Failed to load more comments', error)
    showMessage('Failed to load more comments. Please try again.', 'error')
    commentsPage.value-- // Revert page increment on error
  } finally {
    isLoadingMoreComments.value = false
  }
}

async function toggleHelpful(comment) {
  if (!isLoggedIn.value) return

  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/comments/${comment.id}/helpful`, {
      method: 'POST'
    })

    if (res?.success) {
      const index = comments.value.findIndex(c => c.id === comment.id)
      if (index !== -1) {
        comments.value[index].helpful_count = res.helpful_count || (comment.helpful_count || 0) + 1
      }
    }
  } catch (error) {
    console.error('[✘] Failed to toggle helpful', error)
    showMessage('Failed to mark as helpful. Please try again.', 'error')
  }
}

function formatCommentDate(dateString) {
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = Math.abs(now - date)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

  if (diffDays === 1) return '1 day ago'
  if (diffDays < 7) return `${diffDays} days ago`
  if (diffDays < 30) return `${Math.ceil(diffDays / 7)} weeks ago`
  if (diffDays < 365) return `${Math.ceil(diffDays / 30)} months ago`
  return `${Math.ceil(diffDays / 365)} years ago`
}

function showMessage(text, type = 'success') {
  message.value = { text, type }
  setTimeout(() => {
    clearMessage()
  }, 5000)
}

function clearMessage() {
  message.value = { text: '', type: 'success' }
}

// Cleanup on unmount
onUnmounted(() => {
  document.body.style.overflow = 'auto'
})

// Lifecycle
onMounted(() => {
  if (props.autoLoad && !props.initialComments.length) {
    fetchComments()
  }
})

// Watch for product ID changes
watch(() => props.productId, () => {
  if (props.autoLoad) {
    comments.value = [] // Clear existing comments
    fetchComments()
  }
})

// Expose methods for parent component
defineExpose({
  fetchComments,
  submitComment,
  loadMoreComments,
  refresh: fetchComments,
  openModal: openAllCommentsModal,
  closeModal: closeAllCommentsModal
})
</script>

<style scoped>
/* Modal scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  @apply bg-gray-100 dark:bg-gray-800;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  @apply bg-gray-300 dark:bg-gray-600 rounded-full hover:bg-gray-400 dark:hover:bg-gray-500;
}
</style>
