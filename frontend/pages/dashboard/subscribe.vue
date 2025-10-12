<!-- pages/dashboard/subscribe.vue -->
<template>
  <div class="subscription-page">

    <!-- Loader -->
    <GlobalLoader v-if="isLoading" />

    <!-- ✅ Active Subscription -->
    <transition name="fade-slide-up" mode="out-in">
      <div v-if="!isLoading && hasActiveSubscription" class="active-subscription">
        <div class="container">

          <!-- Success Header -->
          <div class="success-header" data-animate>
            <div class="success-icon-wrapper">
              <div class="success-icon-glow"></div>
              <Icon name="mdi:check-decagram" class="success-icon" />
            </div>
            <h1 class="success-title">Premium Membership Active</h1>
            <p class="success-subtitle">
              Your subscription is active until
              <strong class="success-date">{{ formatDate(subscription.subscription.expire_at) }}</strong>
            </p>
            <div class="countdown-badge">
              <Icon name="mdi:timer-sand" class="w-5 h-5 animate-pulse" />
              <span>{{ remainingDays }} {{ remainingDays === 1 ? 'Day' : 'Days' }} Remaining</span>
            </div>
          </div>

          <!-- Subscription Details -->
          <div class="details-grid">
            <div class="detail-card" data-animate data-delay="100">
              <div class="detail-icon-wrapper">
                <div class="detail-icon detail-icon-blue">
                  <Icon name="mdi:trophy-variant" class="w-6 h-6" />
                </div>
              </div>
              <div class="detail-content">
                <h3 class="detail-title">{{ subscription.subscription.stage?.name }}</h3>
                <p class="detail-subtitle">{{ subscription.subscription.stage?.description || 'Premium Tier' }}</p>
              </div>
            </div>

            <div class="detail-card" data-animate data-delay="200">
              <div class="detail-icon-wrapper">
                <div class="detail-icon detail-icon-purple">
                  <Icon name="mdi:account-multiple" class="w-6 h-6" />
                </div>
              </div>
              <div class="detail-content">
                <h3 class="detail-title">{{ subscription.subscription.level?.name }}</h3>
                <p class="detail-subtitle">Team Capacity: {{ subscription.subscription.level?.max_team_capacity }} members</p>
              </div>
            </div>
          </div>

          <!-- Benefits -->
          <div class="benefits-card" data-animate data-delay="300">
            <div class="benefits-header">
              <Icon name="mdi:star-circle-outline" class="w-6 h-6 text-amber-500 dark:text-amber-400" />
              <h3 class="benefits-title">Your Active Benefits</h3>
            </div>
            <div class="benefits-grid">
              <div
                  v-for="(status, benefit, index) in subscription.subscription.stage?.benefits"
                  :key="benefit"
                  class="benefit-item"
                  :class="{ 'benefit-active': status === 'Active', 'benefit-inactive': status !== 'Active' }"
                  :style="{ animationDelay: `${index * 50}ms` }"
              >
                <Icon
                    :name="status === 'Active' ? 'mdi:check-circle' : 'mdi:cancel'"
                    class="w-5 h-5 flex-shrink-0"
                />
                <span>{{ benefit }}</span>
              </div>
            </div>
          </div>

          <!-- Renew CTA -->
          <div class="renew-cta" data-animate data-delay="400">
            <button @click="currentStep = 1" class="btn-renew">
              <Icon name="mdi:autorenew" class="w-5 h-5" />
              <span>Renew Subscription</span>
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ✅ Multi-Step Subscription Wizard -->
    <transition name="fade-slide-up" mode="out-in">
      <div v-if="!isLoading && !hasActiveSubscription && subscriptionData?.name" class="wizard-container">
        <div class="container">

          <!-- Progress Bar -->
          <div class="progress-bar" data-animate>
            <div class="progress-track">
              <div class="progress-fill" :style="{ width: `${(currentStep / 4) * 100}%` }"></div>
              <div class="progress-shimmer"></div>
            </div>
            <div class="progress-steps">
              <div
                  v-for="(label, index) in stepLabels"
                  :key="index"
                  class="progress-step"
                  :class="{
                  'progress-step-active': index + 1 === currentStep,
                  'progress-step-completed': index + 1 < currentStep
                }"
              >
                <div class="progress-step-circle">
                  <Icon v-if="index + 1 < currentStep" name="mdi:check" class="w-4 h-4" />
                  <span v-else>{{ index + 1 }}</span>
                </div>
                <span class="progress-step-label">{{ label }}</span>
              </div>
            </div>
          </div>

          <!-- Step Content -->
          <transition :name="slideDirection" mode="out-in">
            <div :key="currentStep" class="step-wrapper">

              <!-- Step 1: Welcome -->
              <div v-if="currentStep === 1" class="step-content">
                <div class="hero-card">
                  <div class="hero-bg-pattern"></div>
                  <div class="hero-content">
                    <div class="hero-icon-wrapper">
                      <div class="hero-icon-glow"></div>
                      <Icon name="mdi:rocket-launch-outline" class="hero-icon" />
                    </div>
                    <h1 class="hero-title">Transform Your Business Journey</h1>
                    <p class="hero-subtitle">
                      Join thousands of successful entrepreneurs building sustainable income streams
                    </p>

                    <div class="stats-row">
                      <div class="stat-item">
                        <div class="stat-value">
                          <span class="counter" data-target="5000">0</span>+
                        </div>
                        <div class="stat-label">Active Members</div>
                      </div>
                      <div class="stat-divider"></div>
                      <div class="stat-item">
                        <div class="stat-value">
                          ₹<span class="counter" data-target="250">0</span>M+
                        </div>
                        <div class="stat-label">Earnings Paid</div>
                      </div>
                      <div class="stat-divider"></div>
                      <div class="stat-item">
                        <div class="stat-value">
                          <span class="counter" data-target="4.9">0</span>★
                        </div>
                        <div class="stat-label">User Rating</div>
                      </div>
                    </div>

                    <button @click="nextStep" class="btn-hero">
                      <span>Get Started</span>
                      <Icon name="mdi:arrow-right" class="w-5 h-5" />
                    </button>
                  </div>
                </div>
              </div>

              <!-- Step 2: Benefits -->
              <div v-else-if="currentStep === 2" class="step-content">
                <h2 class="step-title">Exclusive Member Benefits</h2>
                <p class="step-subtitle">Everything you need to build and scale your network</p>

                <div class="features-grid">
                  <div
                      v-for="(status, benefit, index) in subscriptionData.benefits"
                      :key="benefit"
                      class="feature-card"
                      :class="{ 'feature-active': status === 'Active', 'feature-inactive': status !== 'Active' }"
                      :style="{ animationDelay: `${index * 80}ms` }"
                  >
                    <div class="feature-icon-wrapper">
                      <div class="feature-icon-bg" :class="status === 'Active' ? 'active' : 'inactive'"></div>
                      <Icon
                          :name="status === 'Active' ? 'mdi:check-decagram' : 'mdi:lock-outline'"
                          class="feature-icon"
                      />
                    </div>
                    <h3 class="feature-title">{{ benefit }}</h3>
                    <p class="feature-status">
                      <span v-if="status === 'Active'" class="status-active">Included in Plan</span>
                      <span v-else class="status-inactive">Not Available</span>
                    </p>
                  </div>
                </div>

                <div class="step-actions">
                  <button @click="prevStep" class="btn-back">
                    <Icon name="mdi:arrow-left" class="w-5 h-5" />
                    <span>Back</span>
                  </button>
                  <button @click="nextStep" class="btn-next">
                    <span>Continue</span>
                    <Icon name="mdi:arrow-right" class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <!-- Step 3: Package Details -->
              <div v-else-if="currentStep === 3" class="step-content">
                <h2 class="step-title">Your Selected Plan</h2>
                <p class="step-subtitle">Review your subscription details before proceeding</p>

                <div class="package-card">
                  <div class="package-bg-pattern"></div>
                  <div class="package-badge">
                    <Icon name="mdi:star" class="w-4 h-4" />
                    <span>Best Value</span>
                  </div>
                  <div class="package-content">
                    <h3 class="package-name">{{ subscriptionData.name }}</h3>
                    <div class="package-price">
                      <span class="price-currency">₹</span>
                      <span class="price-amount">{{ subscriptionData.price.replace('₹', '').replace('.00', '') }}</span>
                      <span class="price-period">/year</span>
                    </div>
                    <p class="package-description">{{ subscriptionData.description }}</p>

                    <div class="package-details">
                      <div class="package-detail-item">
                        <Icon name="mdi:account-group-outline" class="w-5 h-5" />
                        <span><strong>Team Capacity:</strong> {{ subscriptionData.max_team_capacity }} members</span>
                      </div>
                      <div class="package-detail-item">
                        <Icon name="mdi:trophy-outline" class="w-5 h-5" />
                        <span><strong>Level:</strong> {{ subscriptionData.levels[0]?.name }}</span>
                      </div>
                      <div class="package-detail-item">
                        <Icon name="mdi:calendar-check-outline" class="w-5 h-5" />
                        <span><strong>Validity:</strong> {{ subscriptionData.levels[0]?.validate_years }} Year</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="testimonial-card">
                  <Icon name="mdi:format-quote-open" class="quote-icon" />
                  <p class="testimonial-text">
                    "This subscription changed my life! In just 6 months, I've built a team of 50+ members and earned over ₹1.2L in commissions."
                  </p>
                  <div class="testimonial-author">
                    <div class="author-avatar">
                      <span>RS</span>
                    </div>
                    <div class="author-info">
                      <div class="author-name">Rajesh Sharma</div>
                      <div class="author-role">Premium Member Since 2024</div>
                    </div>
                  </div>
                </div>

                <div class="step-actions">
                  <button @click="prevStep" class="btn-back">
                    <Icon name="mdi:arrow-left" class="w-5 h-5" />
                    <span>Back</span>
                  </button>
                  <button @click="nextStep" class="btn-next">
                    <span>Continue</span>
                    <Icon name="mdi:arrow-right" class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <!-- Step 4: Confirmation -->
              <div v-else-if="currentStep === 4" class="step-content">
                <div class="confirm-card">
                  <div class="confirm-icon-wrapper">
                    <div class="confirm-icon-glow"></div>
                    <Icon name="mdi:shield-check-outline" class="confirm-icon" />
                  </div>
                  <h2 class="confirm-title">Ready to Get Started?</h2>
                  <p class="confirm-subtitle">You're one step away from unlocking your full potential</p>

                  <div class="guarantee-badges">
                    <div class="guarantee-badge">
                      <Icon name="mdi:lock-check-outline" class="w-5 h-5 md:w-6 md:h-6" />
                      <span>Secure Payment</span>
                    </div>
                    <div class="guarantee-badge">
                      <Icon name="mdi:cash-refund" class="w-5 h-5 md:w-6 md:h-6" />
                      <span>Gurranted Savings</span>
                    </div>
                    <div class="guarantee-badge">
                      <Icon name="mdi:headset" class="w-5 h-5 md:w-6 md:h-6" />
                      <span>24/7 Support</span>
                    </div>
                  </div>

                  <div class="price-summary">
                    <div class="price-row price-row-main">
                      <span>Subscription Price</span>
                      <span class="price-final">{{ subscriptionData.price }}</span>
                    </div>
                    <div class="price-divider"></div>
                    <div class="price-row price-row-small">
                      <span>Validity Period</span>
                      <span>{{ subscriptionData.levels[0]?.validate_years }} Year</span>
                    </div>
                  </div>

                  <div class="urgency-banner">
                    <Icon name="mdi:clock-time-four-outline" class="w-5 h-5 animate-pulse" />
                    <span>Limited time offer - Get exclusive bonuses worth ₹5,000!</span>
                  </div>

                  <div class="step-actions">
                    <button @click="prevStep" class="btn-back">
                      <Icon name="mdi:arrow-left" class="w-5 h-5" />
                      <span>Back</span>
                    </button>
                    <button @click="handleSubscribe" :disabled="isSubmitting" class="btn-subscribe">
                      <Icon v-if="isSubmitting" name="mdi:loading" class="w-5 h-5 animate-spin" />
                      <Icon v-else name="mdi:credit-card-outline" class="w-5 h-5" />
                      <span>{{ isSubmitting ? 'Processing...' : 'Subscribe Now' }}</span>
                    </button>
                  </div>

                  <p class="terms-text">
                    By subscribing, you agree to our
                    <NuxtLink to="/terms" class="terms-link">Terms of Service</NuxtLink> and
                    <NuxtLink to="/privacy" class="terms-link">Privacy Policy</NuxtLink>
                  </p>
                </div>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </transition>

    <!-- Error State -->
    <transition name="fade" mode="out-in">
      <div v-if="!isLoading && hasError" class="error-state">
        <div class="error-content">
          <Icon name="mdi:alert-circle-outline" class="error-icon" />
          <h3 class="error-title">Unable to Load Subscription</h3>
          <p class="error-subtitle">Please contact support for assistance</p>
          <button @click="router.push('/dashboard/helpdesk')" class="btn-support">
            <Icon name="mdi:headset" class="w-5 h-5" />
            <span>Contact Support</span>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from "vue"
import { useSanctumFetch, useRuntimeConfig, useRouter, useSanctum } from "#imports"
import GlobalLoader from "~/components/GlobalLoader.vue"

definePageMeta({ layout: "dashboard" })

const config = useRuntimeConfig()
const router = useRouter()
const { user } = useSanctum<User>()

// State
const isLoading = useState('pageLoading', () => true)
const isSubmitting = ref(false)
const subscription = ref<any>(null)
const hasError = ref(false)
const currentStep = ref(1)
const slideDirection = ref('slide-left')

const subscriptionData = reactive({
  name: "",
  url: "",
  description: "",
  price: "",
  max_team_capacity: 0,
  benefits: {} as Record<string, string>,
  levels: [] as any[],
})

const stepLabels = ['Welcome', 'Benefits', 'Details', 'Confirm']

// Computed
const hasActiveSubscription = computed(() => {
  return subscription.value?.subscription && !subscription.value.subscription.expired
})

const remainingDays = computed(() => {
  if (!subscription.value?.subscription?.expire_at) return 0
  const expireDate = new Date(subscription.value.subscription.expire_at).getTime()
  const today = new Date().getTime()
  return Math.max(0, Math.ceil((expireDate - today) / (1000 * 60 * 60 * 24)))
})

// Methods
function nextStep() {
  if (currentStep.value < 4) {
    slideDirection.value = 'slide-left'
    currentStep.value++
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

function prevStep() {
  if (currentStep.value > 1) {
    slideDirection.value = 'slide-right'
    currentStep.value--
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString('en-IN', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

function animateCounters() {
  if (!process.client) return

  const counters = document.querySelectorAll('.counter')
  counters.forEach((counter) => {
    const target = parseFloat(counter.getAttribute('data-target') || '0')
    let current = 0
    const increment = target / 50

    const timer = setInterval(() => {
      current += increment
      if (current >= target) {
        current = target
        clearInterval(timer)
      }
      counter.textContent = current.toFixed(target % 1 !== 0 ? 1 : 0)
    }, 20)
  })
}

function animateElements() {
  if (!process.client) return

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animated')
      }
    })
  }, { threshold: 0.1 })

  document.querySelectorAll('[data-animate]').forEach((el) => {
    observer.observe(el)
  })
}

async function fetchSubscriptionData() {
  try {
    const url = `${config.public.apiBase}/account/subscription`
    const res = await useSanctumFetch(url, { method: "GET" })
    if (res?.data) subscription.value = res.data
    else hasError.value = true
  } catch (error) {
    console.error("Failed to fetch user subscription:", error)
    hasError.value = true
  }
}

async function fetchNewLifecycle() {
  try {
    const url = `${config.public.apiBase}/lifecycle/subscribable`
    const res = await useSanctumFetch(url, { method: "GET" })
    if (res?.data) Object.assign(subscriptionData, res.data)
    else hasError.value = true
  } catch (e) {
    console.error("Failed to fetch subscription data:", e)
    hasError.value = true
  }
}

async function handleSubscribe() {
  try {
    isSubmitting.value = true

    let stageId, levelId
    if (subscriptionData?.levels?.length) {
      levelId = subscriptionData.levels[0]?.id
      stageId = subscriptionData.levels[0]?.stage_id || subscriptionData.id
    } else {
      stageId = subscriptionData.stage_id || subscriptionData.id
      levelId = subscriptionData.level_id
    }

    const url = `${config.public.apiBase}/account/subscription`
    const payload = { stage_id: stageId, level_id: levelId }
    const res = await useSanctumFetch(url, { method: "POST", body: payload })
    const response = res?.data

    if (response?.status) {
      if (response?.redirect && response?.redirect_url) {
        window.location.href = response.redirect_url
      } else {
        router.push("/dashboard/subscription/success")
      }
    } else {
      alert(response?.message || "Failed to subscribe.")
    }
  } catch (error) {
    console.error("Subscription request failed:", error)
    alert("Error occurred while subscribing.")
  } finally {
    isSubmitting.value = false
  }
}

// Lifecycle
onMounted(async () => {
  try {
    await fetchSubscriptionData()
    if (!subscription.value?.subscription || subscription.value.subscription.expired) {
      await fetchNewLifecycle()
    }
  } catch (e) {
    console.error("Init error", e)
    hasError.value = true
  } finally {
    isLoading.value = false
    await nextTick()
    animateElements()
    animateCounters()
  }
})
</script>

<style scoped>
/* Base */
.subscription-page {
  min-height: 100vh;
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  padding: 1.5rem 0;
}

.dark .subscription-page {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
}

@media (min-width: 768px) {
  .subscription-page {
    padding: 3rem 0;
  }
}

.container {
  max-width: 64rem;
  margin: 0 auto;
  padding: 0 1rem;
}

@media (min-width: 640px) {
  .container {
    padding: 0 1.5rem;
  }
}

/* Animations */
@keyframes fadeSlideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

[data-animate] {
  opacity: 0;
  transform: translateY(30px);
  transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

[data-animate].animated {
  opacity: 1;
  transform: translateY(0);
}

[data-delay="100"] {
  transition-delay: 100ms;
}

[data-delay="200"] {
  transition-delay: 200ms;
}

[data-delay="300"] {
  transition-delay: 300ms;
}

[data-delay="400"] {
  transition-delay: 400ms;
}

/* Transitions */
.fade-slide-up-enter-active,
.fade-slide-up-leave-active {
  transition: all 0.4s ease;
}

.fade-slide-up-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.fade-slide-up-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

.slide-left-enter-active,
.slide-left-leave-active,
.slide-right-enter-active,
.slide-right-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-left-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.slide-left-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}

.slide-right-enter-from {
  opacity: 0;
  transform: translateX(-30px);
}

.slide-right-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

/* Success Header */
.success-header {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  border-radius: 1rem;
  padding: 2rem 1.5rem;
  text-align: center;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  position: relative;
  overflow: hidden;
}

.dark .success-header {
  background: linear-gradient(135deg, #047857 0%, #065f46 100%);
}

@media (min-width: 768px) {
  .success-header {
    border-radius: 1.5rem;
    padding: 3rem 2rem;
  }
}

.success-icon-wrapper {
  position: relative;
  display: inline-block;
  margin-bottom: 1rem;
}

.success-icon-glow {
  position: absolute;
  inset: -20%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
  border-radius: 50%;
  animation: pulse 2s ease-in-out infinite;
}

.success-icon {
  width: 4rem;
  height: 4rem;
  color: white;
  position: relative;
  z-index: 1;
}

@media (min-width: 768px) {
  .success-icon {
    width: 5rem;
    height: 5rem;
  }
}

.success-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: white;
  margin-bottom: 0.5rem;
}

@media (min-width: 768px) {
  .success-title {
    font-size: 2rem;
  }
}

@media (min-width: 1024px) {
  .success-title {
    font-size: 2.5rem;
  }
}

.success-subtitle {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 1rem;
}

@media (min-width: 768px) {
  .success-subtitle {
    font-size: 1rem;
  }
}

@media (min-width: 1024px) {
  .success-subtitle {
    font-size: 1.125rem;
  }
}

.success-date {
  color: white;
  font-weight: 700;
}

.countdown-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
  color: white;
}

@media (min-width: 768px) {
  .countdown-badge {
    font-size: 1rem;
  }
}

/* Details Grid */
.active-subscription,
.wizard-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .active-subscription,
  .wizard-container {
    gap: 2rem;
  }
}

.details-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

@media (min-width: 768px) {
  .details-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
  }
}

.detail-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: white;
  border-radius: 1rem;
  padding: 1rem 1.25rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: all 0.3s ease;
}

.dark .detail-card {
  background: #1e293b;
}

@media (min-width: 768px) {
  .detail-card {
    border-radius: 1.5rem;
    padding: 1.5rem;
  }
}

.detail-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.detail-icon-wrapper {
  flex-shrink: 0;
}

.detail-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.detail-icon-blue {
  background: linear-gradient(135deg, #3b82f6 0%, #4f46e5 100%);
}

.detail-icon-purple {
  background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
}

.detail-content {
  flex: 1;
  min-width: 0;
}

.detail-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.25rem;
}

.dark .detail-title {
  color: white;
}

@media (min-width: 768px) {
  .detail-title {
    font-size: 1rem;
  }
}

@media (min-width: 1024px) {
  .detail-title {
    font-size: 1.125rem;
  }
}

.detail-subtitle {
  font-size: 0.75rem;
  color: #64748b;
}

.dark .detail-subtitle {
  color: #94a3b8;
}

@media (min-width: 768px) {
  .detail-subtitle {
    font-size: 0.875rem;
  }
}

/* Benefits Card */
.benefits-card {
  background: white;
  border-radius: 1rem;
  padding: 1rem 1.25rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.dark .benefits-card {
  background: #1e293b;
}

@media (min-width: 768px) {
  .benefits-card {
    border-radius: 1.5rem;
    padding: 1.5rem;
  }
}

.benefits-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

@media (min-width: 768px) {
  .benefits-header {
    margin-bottom: 1.5rem;
  }
}

.benefits-title {
  font-size: 1rem;
  font-weight: 700;
  color: #0f172a;
}

.dark .benefits-title {
  color: white;
}

@media (min-width: 768px) {
  .benefits-title {
    font-size: 1.125rem;
  }
}

@media (min-width: 1024px) {
  .benefits-title {
    font-size: 1.25rem;
  }
}

.benefits-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.75rem;
}

@media (min-width: 640px) {
  .benefits-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 768px) {
  .benefits-grid {
    gap: 1rem;
  }
}

.benefit-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  transition: all 0.3s ease;
  animation: fadeSlideUp 0.5s ease-out backwards;
}

@media (min-width: 768px) {
  .benefit-item {
    font-size: 1rem;
  }
}

.benefit-active {
  background: #f0fdf4;
  color: #15803d;
}

.dark .benefit-active {
  background: rgba(21, 128, 61, 0.2);
  color: #4ade80;
}

.benefit-inactive {
  background: #f8fafc;
  color: #94a3b8;
  text-decoration: line-through;
}

.dark .benefit-inactive {
  background: rgba(71, 85, 105, 0.3);
  color: #64748b;
}

/* Renew CTA */
.renew-cta {
  text-align: center;
}

.btn-renew {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  color: white;
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: 0.875rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
}

@media (min-width: 768px) {
  .btn-renew {
    font-size: 1rem;
  }
}

.btn-renew:hover {
  transform: translateY(-2px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.btn-renew:active {
  transform: translateY(0);
}

/* Progress Bar */
.progress-bar {
  margin-bottom: 2rem;
}

@media (min-width: 768px) {
  .progress-bar {
    margin-bottom: 3rem;
  }
}

.progress-track {
  position: relative;
  height: 0.5rem;
  background: #e2e8f0;
  border-radius: 9999px;
  overflow: hidden;
}

.dark .progress-track {
  background: #334155;
}

.progress-fill {
  position: absolute;
  inset: 0;
  left: 0;
  background: linear-gradient(90deg, #3b82f6 0%, #8b5cf6 100%);
  border-radius: 9999px;
  transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.progress-shimmer {
  position: absolute;
  inset: 0;
  background: linear-gradient(
      90deg,
      transparent 0%,
      rgba(255, 255, 255, 0.3) 50%,
      transparent 100%
  );
  animation: shimmer 2s infinite;
}

.progress-steps {
  display: flex;
  justify-content: space-between;
  margin-top: 1rem;
}

.progress-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
}

.progress-step-circle {
  width: 2rem;
  height: 2rem;
  border-radius: 9999px;
  background: #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  font-weight: 600;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.dark .progress-step-circle {
  background: #334155;
  color: #94a3b8;
}

@media (min-width: 768px) {
  .progress-step-circle {
    width: 2.5rem;
    height: 2.5rem;
  }
}

.progress-step-active .progress-step-circle {
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  color: white;
  transform: scale(1.1);
}

.progress-step-completed .progress-step-circle {
  background: #10b981;
  color: white;
}

.progress-step-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: #64748b;
  display: none;
}

.dark .progress-step-label {
  color: #94a3b8;
}

@media (min-width: 640px) {
  .progress-step-label {
    display: block;
  }
}

@media (min-width: 768px) {
  .progress-step-label {
    font-size: 0.875rem;
  }
}

/* Step Wrapper */
.step-wrapper {
  width: 100%;
}

.step-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .step-content {
    gap: 2rem;
  }
}

/* Hero Card */
.hero-card {
  position: relative;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%);
  border-radius: 1rem;
  padding: 2rem 1.5rem;
  text-align: center;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}

@media (min-width: 768px) {
  .hero-card {
    border-radius: 1.5rem;
    padding: 3rem 2rem;
  }
}

@media (min-width: 1024px) {
  .hero-card {
    padding: 4rem 3rem;
  }
}

.hero-bg-pattern {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.1) 1px, transparent 0);
  background-size: 40px 40px;
  opacity: 0.3;
}

.hero-content {
  position: relative;
  z-index: 1;
}

.hero-icon-wrapper {
  position: relative;
  display: inline-block;
  margin-bottom: 1rem;
}

.hero-icon-glow {
  position: absolute;
  inset: -30%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
  border-radius: 50%;
  animation: pulse 3s ease-in-out infinite;
}

.hero-icon {
  width: 4rem;
  height: 4rem;
  color: white;
  position: relative;
  z-index: 1;
}

@media (min-width: 768px) {
  .hero-icon {
    width: 5rem;
    height: 5rem;
  }
}

.hero-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: white;
  margin-bottom: 0.75rem;
}

@media (min-width: 768px) {
  .hero-title {
    font-size: 2rem;
  }
}

@media (min-width: 1024px) {
  .hero-title {
    font-size: 2.5rem;
  }
}

.hero-subtitle {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.9);
  max-width: 42rem;
  margin: 0 auto 1.5rem;
}

@media (min-width: 768px) {
  .hero-subtitle {
    font-size: 1rem;
  }
}

@media (min-width: 1024px) {
  .hero-subtitle {
    font-size: 1.125rem;
    margin-bottom: 2rem;
  }
}

.stats-row {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

@media (min-width: 768px) {
  .stats-row {
    gap: 2rem;
    margin-bottom: 2rem;
  }
}

.stat-item {
  text-align: center;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
}

@media (min-width: 768px) {
  .stat-value {
    font-size: 2rem;
  }
}

.stat-label {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.75);
}

@media (min-width: 768px) {
  .stat-label {
    font-size: 0.875rem;
  }
}

.stat-divider {
  width: 1px;
  height: 2rem;
  background: rgba(255, 255, 255, 0.3);
  display: none;
}

@media (min-width: 640px) {
  .stat-divider {
    display: block;
  }
}

.btn-hero {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 2rem;
  background: white;
  color: #3b82f6;
  border-radius: 0.75rem;
  font-weight: 700;
  font-size: 0.875rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
}

@media (min-width: 768px) {
  .btn-hero {
    font-size: 1rem;
    padding: 1rem 2.5rem;
  }
}

.btn-hero:hover {
  transform: translateY(-2px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Step Title/Subtitle */
.step-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  text-align: center;
  margin-bottom: 0.5rem;
}

.dark .step-title {
  color: white;
}

@media (min-width: 768px) {
  .step-title {
    font-size: 2rem;
  }
}

.step-subtitle {
  font-size: 0.875rem;
  color: #64748b;
  text-align: center;
  margin-bottom: 1.5rem;
}

.dark .step-subtitle {
  color: #94a3b8;
}

@media (min-width: 768px) {
  .step-subtitle {
    font-size: 1rem;
    margin-bottom: 2rem;
  }
}

@media (min-width: 1024px) {
  .step-subtitle {
    font-size: 1.125rem;
  }
}

/* Features Grid */
.features-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

@media (min-width: 640px) {
  .features-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .features-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
  }
}

.feature-card {
  background: white;
  border-radius: 1rem;
  padding: 1.25rem;
  text-align: center;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: all 0.3s ease;
  animation: fadeSlideUp 0.6s ease-out backwards;
}

.dark .feature-card {
  background: #1e293b;
}

@media (min-width: 768px) {
  .feature-card {
    border-radius: 1.5rem;
    padding: 1.5rem;
  }
}

.feature-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.feature-active {
  border: 2px solid #10b981;
}

.feature-inactive {
  opacity: 0.6;
}

.feature-icon-wrapper {
  position: relative;
  display: inline-block;
  margin-bottom: 1rem;
}

.feature-icon-bg {
  position: absolute;
  inset: -20%;
  border-radius: 50%;
  opacity: 0.2;
}

.feature-icon-bg.active {
  background: radial-gradient(circle, #10b981 0%, transparent 70%);
}

.feature-icon-bg.inactive {
  background: radial-gradient(circle, #64748b 0%, transparent 70%);
}

.feature-icon {
  width: 2rem;
  height: 2rem;
  position: relative;
  z-index: 1;
}

@media (min-width: 768px) {
  .feature-icon {
    width: 2.5rem;
    height: 2.5rem;
  }
}

.feature-active .feature-icon {
  color: #10b981;
}

.dark .feature-active .feature-icon {
  color: #4ade80;
}

.feature-inactive .feature-icon {
  color: #94a3b8;
}

.dark .feature-inactive .feature-icon {
  color: #64748b;
}

.feature-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.5rem;
}

.dark .feature-title {
  color: white;
}

@media (min-width: 768px) {
  .feature-title {
    font-size: 1rem;
  }
}

@media (min-width: 1024px) {
  .feature-title {
    font-size: 1.125rem;
  }
}

.feature-status {
  font-size: 0.75rem;
}

@media (min-width: 768px) {
  .feature-status {
    font-size: 0.875rem;
  }
}

.status-active {
  color: #15803d;
}

.dark .status-active {
  color: #4ade80;
}

.status-inactive {
  color: #64748b;
}

.dark .status-inactive {
  color: #94a3b8;
}

/* Package Card */
.package-card {
  position: relative;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  border-radius: 1rem;
  padding: 2rem 1.5rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}

@media (min-width: 768px) {
  .package-card {
    border-radius: 1.5rem;
    padding: 3rem 2rem;
  }
}

.package-bg-pattern {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.1) 1px, transparent 0);
  background-size: 40px 40px;
  opacity: 0.3;
}

.package-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.5rem 0.75rem;
  background: #fbbf24;
  color: #78350f;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 700;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

@media (min-width: 768px) {
  .package-badge {
    font-size: 0.875rem;
  }
}

.package-content {
  position: relative;
  z-index: 1;
  color: white;
}

.package-name {
  font-size: 1.5rem;
  font-weight: 800;
  margin-bottom: 1rem;
}

@media (min-width: 768px) {
  .package-name {
    font-size: 2rem;
  }
}

.package-price {
  display: flex;
  align-items: baseline;
  justify-content: center;
  gap: 0.25rem;
  margin-bottom: 1rem;
}

@media (min-width: 768px) {
  .package-price {
    gap: 0.5rem;
    margin-bottom: 1.5rem;
  }
}

.price-currency {
  font-size: 1.5rem;
}

@media (min-width: 768px) {
  .price-currency {
    font-size: 2rem;
  }
}

.price-amount {
  font-size: 2.5rem;
  font-weight: 800;
}

@media (min-width: 768px) {
  .price-amount {
    font-size: 3rem;
  }
}

@media (min-width: 1024px) {
  .price-amount {
    font-size: 4rem;
  }
}

.price-period {
  font-size: 1rem;
  opacity: 0.75;
}

@media (min-width: 768px) {
  .price-period {
    font-size: 1.25rem;
  }
}

.package-description {
  font-size: 0.875rem;
  opacity: 0.9;
  margin-bottom: 1.5rem;
}

@media (min-width: 768px) {
  .package-description {
    font-size: 1rem;
  }
}

.package-details {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 0.75rem;
  padding: 1rem;
}

.package-detail-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.875rem;
}

@media (min-width: 768px) {
  .package-detail-item {
    font-size: 1rem;
  }
}

/* Testimonial Card */
.testimonial-card {
  background: white;
  border-radius: 1rem;
  padding: 1.25rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.dark .testimonial-card {
  background: #1e293b;
}

@media (min-width: 768px) {
  .testimonial-card {
    border-radius: 1.5rem;
    padding: 1.5rem;
  }
}

.quote-icon {
  width: 2rem;
  height: 2rem;
  color: #3b82f6;
  margin-bottom: 0.75rem;
}

.testimonial-text {
  font-size: 0.875rem;
  color: #475569;
  font-style: italic;
  margin-bottom: 1rem;
  line-height: 1.6;
}

.dark .testimonial-text {
  color: #cbd5e1;
}

@media (min-width: 768px) {
  .testimonial-text {
    font-size: 1rem;
  }
}

.testimonial-author {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.author-avatar {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 9999px;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  flex-shrink: 0;
}

.author-info {
  flex: 1;
  min-width: 0;
}

.author-name {
  font-size: 0.875rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.125rem;
}

.dark .author-name {
  color: white;
}

@media (min-width: 768px) {
  .author-name {
    font-size: 1rem;
  }
}

.author-role {
  font-size: 0.75rem;
  color: #64748b;
}

.dark .author-role {
  color: #94a3b8;
}

@media (min-width: 768px) {
  .author-role {
    font-size: 0.875rem;
  }
}

/* Confirm Card */
.confirm-card {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  text-align: center;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.dark .confirm-card {
  background: #1e293b;
}

@media (min-width: 768px) {
  .confirm-card {
    border-radius: 1.5rem;
    padding: 2rem;
  }
}

.confirm-icon-wrapper {
  position: relative;
  display: inline-block;
  margin-bottom: 1rem;
}

.confirm-icon-glow {
  position: absolute;
  inset: -20%;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.3) 0%, transparent 70%);
  border-radius: 50%;
  animation: pulse 2s ease-in-out infinite;
}

.confirm-icon {
  width: 4rem;
  height: 4rem;
  color: #10b981;
  position: relative;
  z-index: 1;
}

.confirm-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: 0.5rem;
}

.dark .confirm-title {
  color: white;
}

@media (min-width: 768px) {
  .confirm-title {
    font-size: 2rem;
  }
}

.confirm-subtitle {
  font-size: 0.875rem;
  color: #64748b;
  margin-bottom: 1.5rem;
}

.dark .confirm-subtitle {
  color: #94a3b8;
}

@media (min-width: 768px) {
  .confirm-subtitle {
    font-size: 1rem;
    margin-bottom: 2rem;
  }
}

@media (min-width: 1024px) {
  .confirm-subtitle {
    font-size: 1.125rem;
  }
}

.guarantee-badges {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

@media (min-width: 768px) {
  .guarantee-badges {
    gap: 1rem;
    margin-bottom: 2rem;
  }
}

.guarantee-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: #f0fdf4;
  color: #15803d;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.dark .guarantee-badge {
  background: rgba(21, 128, 61, 0.2);
  color: #4ade80;
}

@media (min-width: 768px) {
  .guarantee-badge {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
  }
}

.price-summary {
  background: #f8fafc;
  border-radius: 0.75rem;
  padding: 1rem 1.25rem;
  margin-bottom: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.dark .price-summary {
  background: #334155;
}

@media (min-width: 768px) {
  .price-summary {
    padding: 1.5rem;
  }
}

.price-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.875rem;
}

@media (min-width: 768px) {
  .price-row {
    font-size: 1rem;
  }
}

@media (min-width: 1024px) {
  .price-row {
    font-size: 1.125rem;
  }
}

.price-row-main {
  font-weight: 600;
  color: #0f172a;
}

.dark .price-row-main {
  color: white;
}

.price-row-small {
  font-size: 0.75rem;
  color: #64748b;
}

.dark .price-row-small {
  color: #94a3b8;
}

@media (min-width: 768px) {
  .price-row-small {
    font-size: 0.875rem;
  }
}

@media (min-width: 1024px) {
  .price-row-small {
    font-size: 1rem;
  }
}

.price-divider {
  height: 1px;
  background: #e2e8f0;
}

.dark .price-divider {
  background: #475569;
}

.price-final {
  font-size: 1.5rem;
  font-weight: 700;
  color: #10b981;
}

.dark .price-final {
  color: #4ade80;
}

@media (min-width: 768px) {
  .price-final {
    font-size: 2rem;
  }
}

.urgency-banner {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: #fef3c7;
  color: #78350f;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

.dark .urgency-banner {
  background: rgba(251, 191, 36, 0.2);
  color: #fbbf24;
}

@media (min-width: 768px) {
  .urgency-banner {
    font-size: 0.875rem;
  }
}

/* Step Actions */
.step-actions {
  display: flex;
  gap: 0.75rem;
}

@media (min-width: 768px) {
  .step-actions {
    gap: 1rem;
  }
}

.btn-back,
.btn-next,
.btn-subscribe {
  flex: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: 0.875rem;
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
}

@media (min-width: 768px) {
  .btn-back,
  .btn-next,
  .btn-subscribe {
    padding: 0.875rem 1.5rem;
    font-size: 1rem;
  }
}

.btn-back {
  background: #e2e8f0;
  color: #475569;
}

.dark .btn-back {
  background: #334155;
  color: #cbd5e1;
}

.btn-back:hover {
  background: #cbd5e1;
}

.dark .btn-back:hover {
  background: #475569;
}

.btn-next {
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  color: white;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.btn-next:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.btn-subscribe {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.btn-subscribe:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.btn-subscribe:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-subscribe:active:not(:disabled),
.btn-next:active,
.btn-back:active {
  transform: translateY(0);
}

.terms-text {
  font-size: 0.75rem;
  color: #64748b;
  margin-top: 1rem;
}

.dark .terms-text {
  color: #94a3b8;
}

@media (min-width: 768px) {
  .terms-text {
    font-size: 0.875rem;
  }
}

.terms-link {
  color: #3b82f6;
  text-decoration: underline;
  transition: color 0.2s ease;
}

.dark .terms-link {
  color: #60a5fa;
}

.terms-link:hover {
  color: #2563eb;
}

.dark .terms-link:hover {
  color: #3b82f6;
}

/* Error State */
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  padding: 2rem 1rem;
  text-align: center;
}

.error-content {
  max-width: 28rem;
}

.error-icon {
  width: 4rem;
  height: 4rem;
  color: #ef4444;
  margin-bottom: 1rem;
}

.error-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.5rem;
}

.dark .error-title {
  color: white;
}

@media (min-width: 768px) {
  .error-title {
    font-size: 1.5rem;
  }
}

.error-subtitle {
  font-size: 0.875rem;
  color: #64748b;
  margin-bottom: 1.5rem;
}

.dark .error-subtitle {
  color: #94a3b8;
}

@media (min-width: 768px) {
  .error-subtitle {
    font-size: 1rem;
  }
}

.btn-support {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  color: white;
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: 0.875rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
}

@media (min-width: 768px) {
  .btn-support {
    font-size: 1rem;
  }
}

.btn-support:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
