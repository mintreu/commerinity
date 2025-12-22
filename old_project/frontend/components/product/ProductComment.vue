<!--components/ProductComment.vue-->

<template>
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">

    <!-- Reviews Header -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Icon name="mdi:star-outline" class="w-6 h-6 text-indigo-600 dark:text-indigo-400"/>
          <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Customer Reviews</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              {{ totalReviews }} {{ totalReviews === 1 ? 'review' : 'reviews' }}
            </p>
          </div>
        </div>
        <div v-if="averageRating > 0" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
          <Icon name="mdi:star" class="w-4 h-4 text-yellow-400"/>
          <span>{{ averageRating.toFixed(1) }} average rating</span>
        </div>
      </div>
    </div>

    <!-- Review Form Section - Only for Logged In Users -->
    <div v-if="isLoggedIn" class="p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-start gap-4">
        <!-- User Avatar -->
        <div class="flex-shrink-0">
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">
            {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
          </div>
        </div>

        <!-- Review Form -->
        <div class="flex-1 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Share your experience with this product
            </label>
            <textarea
                v-model="newReview.text"
                :disabled="isSubmittingReview"
                rows="4"
                placeholder="Write your review here... Share details about quality, delivery, value for money, etc."
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white resize-none transition-all duration-200"
                :class="newReview.text.length > 500 ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
            ></textarea>
            <div class="flex justify-between items-center mt-2">
              <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ newReview.text.length }}/500 characters
              </div>
              <div v-if="newReview.text.length > 500" class="text-xs text-red-500">
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
                  :disabled="isSubmittingReview"
                  class="text-2xl transition-colors duration-200 focus:outline-none"
                  :class="star <= newReview.rating
                    ? 'text-yellow-400 hover:text-yellow-500'
                    : 'text-gray-300 hover:text-yellow-300 dark:text-gray-600 dark:hover:text-yellow-400'"
              >
                ★
              </button>
            </div>
            <span class="text-sm text-gray-600 dark:text-gray-400">
              {{ newReview.rating > 0 ? `${newReview.rating} star${newReview.rating > 1 ? 's' : ''}` : 'Select rating' }}
            </span>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end">
            <button
                @click="submitReview"
                :disabled="!canSubmitReview || isSubmittingReview"
                class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 flex items-center gap-2"
            >
              <Icon
                  v-if="isSubmittingReview"
                  name="mdi:loading"
                  class="w-4 h-4 animate-spin"
              />
              <Icon
                  v-else
                  name="mdi:send"
                  class="w-4 h-4"
              />
              {{ isSubmittingReview ? 'Posting...' : 'Post Review' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Guest User - Login to Review Banner -->
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

    <!-- Reviews List -->
    <div class="divide-y divide-gray-200 dark:divide-gray-700">
      <!-- Loading State -->
      <div v-if="isLoadingReviews" class="p-6">
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

      <!-- Reviews -->
      <div v-else-if="displayReviews.length > 0">
        <div
            v-for="review in topReviews"
            :key="review.id"
            class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-200"
        >
          <div class="flex gap-4">
            <!-- User Avatar -->
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-green-600 flex items-center justify-center text-white font-semibold text-sm">
                {{ review.author_name?.charAt(0).toUpperCase() || 'A' }}
              </div>
            </div>

            <!-- Review Content -->
            <div class="flex-1 min-w-0">
              <!-- User Info & Rating -->
              <div class="flex items-center gap-3 mb-2">
                <h4 class="font-medium text-gray-900 dark:text-white">
                  {{ review.author_name || 'Anonymous' }}
                </h4>
                <div class="flex items-center gap-1">
                  <div class="flex">
                    <span
                        v-for="star in 5"
                        :key="star"
                        class="text-sm"
                        :class="star <= review.rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                    >
                      ★
                    </span>
                  </div>
                  <span class="text-sm text-gray-600 dark:text-gray-400">
                    ({{ review.rating }}/5)
                  </span>
                </div>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                  {{ formatReviewDate(review.created_at) }}
                </span>
              </div>

              <!-- Review Text -->
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                {{ review.review }}
              </p>

              <!-- Helpful Actions -->
              <div class="flex items-center gap-4 mt-3">
                <button
                    @click="toggleHelpful(review)"
                    :disabled="!isLoggedIn || isTogglingHelpful[review.id]"
                    class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <Icon
                      :name="isTogglingHelpful[review.id] ? 'mdi:loading' : 'mdi:thumb-up-outline'"
                      class="w-4 h-4"
                      :class="isTogglingHelpful[review.id] ? 'animate-spin' : ''"
                  />
                  <span>Helpful ({{ review.helpful_count || 0 }})</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Reviews State -->
      <div v-else class="p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
          <Icon name="mdi:star-outline" class="w-8 h-8 text-gray-400"/>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
          No reviews yet
        </h3>
        <p class="text-gray-600 dark:text-gray-400 text-sm">
          Be the first to share your experience with this product
        </p>
      </div>
    </div>

    <!-- View All Reviews Button -->
    <div v-if="displayReviews.length > 5" class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
      <button
          @click="openAllReviewsModal"
          class="w-full px-4 py-3 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2"
      >
        <Icon name="mdi:star-box-multiple" class="w-4 h-4"/>
        View All {{ displayReviews.length }} Reviews
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

    <!-- Full Screen Reviews Modal -->
    <Teleport to="body">
      <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
      >
        <div v-if="showAllReviewsModal" class="fixed inset-0 z-50 overflow-hidden">
          <!-- Backdrop -->
          <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeAllReviewsModal"></div>

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
                <div v-if="showAllReviewsModal" class="w-full max-w-4xl max-h-[90vh] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">

                  <!-- Modal Header -->
                  <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-3">
                        <Icon name="mdi:star-box-multiple" class="w-6 h-6 text-indigo-600 dark:text-indigo-400"/>
                        <div>
                          <h2 class="text-xl font-bold text-gray-900 dark:text-white">All Customer Reviews</h2>
                          <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ displayReviews.length }} reviews • {{ averageRating.toFixed(1) }} average rating
                          </p>
                        </div>
                      </div>
                      <button
                          @click="closeAllReviewsModal"
                          class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
                      >
                        <Icon name="mdi:close" class="w-6 h-6 text-gray-500 dark:text-gray-400"/>
                      </button>
                    </div>
                  </div>

                  <!-- Modal Content - All Reviews -->
                  <div class="overflow-y-auto max-h-[calc(90vh-120px)] divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="review in displayReviews"
                        :key="review.id"
                        class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-200"
                    >
                      <div class="flex gap-4">
                        <!-- User Avatar -->
                        <div class="flex-shrink-0">
                          <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-green-600 flex items-center justify-center text-white font-semibold">
                            {{ review.author_name?.charAt(0).toUpperCase() || 'A' }}
                          </div>
                        </div>

                        <!-- Review Content -->
                        <div class="flex-1 min-w-0">
                          <!-- User Info & Rating -->
                          <div class="flex items-center gap-3 mb-3">
                            <h4 class="font-semibold text-gray-900 dark:text-white">
                              {{ review.author_name || 'Anonymous' }}
                            </h4>
                            <div class="flex items-center gap-1">
                              <div class="flex">
                                <span
                                    v-for="star in 5"
                                    :key="star"
                                    class="text-lg"
                                    :class="star <= review.rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                                >
                                  ★
                                </span>
                              </div>
                              <span class="text-sm text-gray-600 dark:text-gray-400 ml-1">
                                ({{ review.rating }}/5)
                              </span>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                              {{ formatReviewDate(review.created_at) }}
                            </span>
                          </div>

                          <!-- Review Text -->
                          <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                            {{ review.review }}
                          </p>

                          <!-- Helpful Actions -->
                          <div class="flex items-center gap-6">
                            <button
                                @click="toggleHelpful(review)"
                                :disabled="!isLoggedIn || isTogglingHelpful[review.id]"
                                class="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                              <Icon
                                  :name="isTogglingHelpful[review.id] ? 'mdi:loading' : 'mdi:thumb-up-outline'"
                                  class="w-4 h-4"
                                  :class="isTogglingHelpful[review.id] ? 'animate-spin' : ''"
                              />
                              <span>Helpful ({{ review.helpful_count || 0 }})</span>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Load More in Modal -->
                    <div v-if="hasMoreReviews" class="p-6 text-center border-t border-gray-200 dark:border-gray-700">
                      <button
                          @click="loadMoreReviews"
                          :disabled="isLoadingMoreReviews"
                          class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors duration-200 disabled:opacity-50 flex items-center gap-2 mx-auto"
                      >
                        <Icon
                            v-if="isLoadingMoreReviews"
                            name="mdi:loading"
                            class="w-4 h-4 animate-spin"
                        />
                        {{ isLoadingMoreReviews ? 'Loading...' : 'Load More Reviews' }}
                      </button>
                    </div>
                  </div>

                  <!-- Modal Footer -->
                  <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                    <div class="flex justify-between items-center">
                      <p class="text-sm text-gray-600 dark:text-gray-400">
                        Showing {{ displayReviews.length }} of {{ totalReviews }} reviews
                      </p>
                      <button
                          @click="closeAllReviewsModal"
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRuntimeConfig, useSanctum, useSanctumFetch } from '#imports'

// Sanctum composable for authentication
const { isLoggedIn, user } = useSanctum()

// Props
const props = defineProps({
  url: {
    type: String,
    required: true
  }
})

// Composables
const route = useRoute()
const config = useRuntimeConfig()
const currentPath = computed(() => route.fullPath)

// State
const reviews = ref([])
const isLoadingReviews = ref(false)
const isSubmittingReview = ref(false)
const isLoadingMoreReviews = ref(false)
const hasMoreReviews = ref(true)
const reviewsPage = ref(1)
const message = ref({ text: '', type: 'success' })
const showAllReviewsModal = ref(false)
const isTogglingHelpful = ref({}) // Track loading state per review

// New Review State
const newReview = ref({
  text: '',
  rating: 0
})

// Computed Properties
const canSubmitReview = computed(() => {
  return isLoggedIn.value &&
      newReview.value.text.trim().length >= 10 &&
      newReview.value.text.length <= 500 &&
      newReview.value.rating > 0
})

const displayReviews = computed(() => {
  return [...reviews.value].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})

const topReviews = computed(() => {
  return displayReviews.value.slice(0, 5)
})

const totalReviews = computed(() => {
  return reviews.value.length
})

const averageRating = computed(() => {
  if (reviews.value.length === 0) return 0
  const sum = reviews.value.reduce((acc, review) => acc + review.rating, 0)
  return sum / reviews.value.length
})

// Methods
const setRating = (rating) => {
  if (!isSubmittingReview.value) {
    newReview.value.rating = rating
  }
}

const openAllReviewsModal = () => {
  showAllReviewsModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeAllReviewsModal = () => {
  showAllReviewsModal.value = false
  document.body.style.overflow = 'auto'
}

async function fetchReviews() {
  if (!props.url) return

  try {
    isLoadingReviews.value = true

    // Updated to match your API route: /product/engagements/{product:url}
    const response = await useSanctumFetch(`${config.public.apiBase}/product/engagements/${props.url}`, {
      method: 'GET',
      query: { page: 1, per_page: 10 }
    })

    // Handle Laravel Resource Collection response
    if (response?.data) {
      reviews.value = response.data
      // Check for pagination meta
      hasMoreReviews.value = response?.meta?.has_more_pages || false
      reviewsPage.value = 1
    }
  } catch (error) {
    console.error('[✘] Failed to load reviews', error)
    showMessage('Failed to load reviews. Please try again.', 'error')
  } finally {
    isLoadingReviews.value = false
  }
}

async function submitReview() {
  if (!canSubmitReview.value || isSubmittingReview.value) return

  try {
    isSubmittingReview.value = true

    // Updated to match your API route: /product/engagement/{product:url}
    const response = await useSanctumFetch(`${config.public.apiBase}/product/engagement/${props.url}`, {
      method: 'POST',
      body: {
        review: newReview.value.text.trim(),
        rating: newReview.value.rating
      }
    })

    // Handle both Resource response and error response
    if (response?.data || response?.review) {
      // Extract data from Resource response
      const responseData = response.data || response

      // Add new review to the beginning of the list
      const newReviewData = {
        id: responseData.id || Date.now(),
        review: newReview.value.text.trim(),
        rating: newReview.value.rating,
        author_name: responseData.author?.name || user.value?.name || 'You',
        author_type: responseData.author?.type || 'User',
        helpful_count: 0,
        created_at: responseData.created_at || new Date().toISOString(),
        updated_at: responseData.updated_at || new Date().toISOString(),
        ...responseData
      }

      reviews.value.unshift(newReviewData)

      // Reset form
      newReview.value = {
        text: '',
        rating: 0
      }

      showMessage('Review posted successfully! Thank you for sharing your experience.', 'success')
    }
  } catch (error) {
    console.error('[✘] Failed to submit review', error)

    // Handle validation error (422) specifically
    if (error?.response?.status === 422 && error?.response?.data?.message) {
      showMessage(error.response.data.message, 'error')
    } else {
      showMessage('Failed to post review. Please try again.', 'error')
    }
  } finally {
    isSubmittingReview.value = false
  }
}

async function loadMoreReviews() {
  if (!hasMoreReviews.value || isLoadingMoreReviews.value) return

  try {
    isLoadingMoreReviews.value = true
    reviewsPage.value++

    const response = await useSanctumFetch(`${config.public.apiBase}/product/engagements/${props.url}`, {
      method: 'GET',
      query: { page: reviewsPage.value, per_page: 10 }
    })

    if (response?.data) {
      reviews.value.push(...response.data)
      hasMoreReviews.value = response?.meta?.has_more_pages || false
    }
  } catch (error) {
    console.error('[✘] Failed to load more reviews', error)
    showMessage('Failed to load more reviews. Please try again.', 'error')
    reviewsPage.value-- // Revert page increment on error
  } finally {
    isLoadingMoreReviews.value = false
  }
}

async function toggleHelpful(review) {
  if (!isLoggedIn.value || isTogglingHelpful.value[review.id]) return

  try {
    isTogglingHelpful.value[review.id] = true

    // Updated to match your API route: /product/engagement/{product_engagement}/helpfull
    const response = await useSanctumFetch(`${config.public.apiBase}/product/engagement/${review.id}/helpfull`, {
      method: 'POST'
    })

    if (response?.success) {
      // Update helpful count from response or increment locally
      const index = reviews.value.findIndex(r => r.id === review.id)
      if (index !== -1) {
        reviews.value[index].helpful_count = response.helpful_count || (review.helpful_count || 0) + 1
      }

      showMessage(response.message || 'Review marked as helpful!', 'success')
    }
  } catch (error) {
    console.error('[✘] Failed to toggle helpful', error)

    // Handle specific error messages
    if (error?.response?.status === 422 && error?.response?.data?.message) {
      showMessage(error.response.data.message, 'error')
    } else {
      showMessage('Failed to mark as helpful. Please try again.', 'error')
    }
  } finally {
    isTogglingHelpful.value[review.id] = false
  }
}

function formatReviewDate(dateString) {
  try {
    const date = new Date(dateString)
    const now = new Date()
    const diffTime = Math.abs(now - date)
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

    if (diffDays === 1) return '1 day ago'
    if (diffDays < 7) return `${diffDays} days ago`
    if (diffDays < 30) return `${Math.ceil(diffDays / 7)} weeks ago`
    if (diffDays < 365) return `${Math.ceil(diffDays / 30)} months ago`
    return `${Math.ceil(diffDays / 365)} years ago`
  } catch (error) {
    return 'Recently'
  }
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

// Lifecycle - Auto-load reviews on mount
onMounted(() => {
  if (props.url) {
    fetchReviews()
  }
})

// Watch for URL changes and reload reviews
watch(() => props.url, (newUrl) => {
  if (newUrl) {
    reviews.value = [] // Clear existing reviews
    fetchReviews()
  }
}, { immediate: false })

// Expose methods for parent component
defineExpose({
  fetchReviews,
  submitReview,
  loadMoreReviews,
  refresh: fetchReviews,
  openModal: openAllReviewsModal,
  closeModal: closeAllReviewsModal
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
