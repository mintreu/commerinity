<template>
  <transition name="banner-slide" appear>
    <div
        v-if="showBanner"
        role="banner"
        class="onboarding-banner relative w-full overflow-hidden"
        ref="bannerRef"
    >
      <!-- Enhanced Background -->
      <div class="banner-background absolute inset-0">
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/95 via-purple-500/95 to-pink-500/95"></div>

        <!-- Animated Particles -->
        <div class="particles-container absolute inset-0 overflow-hidden">
          <div
              v-for="i in 12"
              :key="i"
              class="particle"
              :style="getParticleStyle(i)"
              ref="particles"
          ></div>
        </div>

        <!-- Glow Effects -->
        <div class="absolute inset-0 bg-gradient-to-r from-blue-400/20 via-purple-400/20 to-pink-400/20 blur-xl"></div>

        <!-- Border Glow -->
        <div class="absolute inset-0 border border-white/20 rounded-2xl"></div>
      </div>

      <!-- Main Content -->
      <div class="banner-content relative z-10 p-6">
        <div class="content-wrapper flex flex-col lg:flex-row items-start lg:items-center gap-6">

          <!-- Left Section: Progress & Current Step -->
          <div class="progress-section flex-1 min-w-0">
            <div class="progress-header flex items-center gap-4 mb-4">
              <!-- Progress Ring -->
              <div class="progress-ring-container relative">
                <svg class="progress-ring w-16 h-16 transform -rotate-90" viewBox="0 0 60 60">
                  <circle
                      cx="30"
                      cy="30"
                      r="26"
                      stroke="rgba(255, 255, 255, 0.2)"
                      stroke-width="4"
                      fill="none"
                  />
                  <circle
                      cx="30"
                      cy="30"
                      r="26"
                      stroke="url(#progressGradient)"
                      stroke-width="4"
                      fill="none"
                      stroke-linecap="round"
                      :stroke-dasharray="circumference"
                      :stroke-dashoffset="strokeDashoffset"
                      class="progress-circle transition-all duration-1000 ease-out"
                  />
                  <defs>
                    <linearGradient id="progressGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                      <stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
                      <stop offset="100%" style="stop-color:#06d6a0;stop-opacity:1" />
                    </linearGradient>
                  </defs>
                </svg>

                <!-- Center Progress Text -->
                <div class="absolute inset-0 flex items-center justify-center">
                  <div class="text-center">
                    <div class="text-lg font-bold text-white">{{ Math.round(progress) }}%</div>
                    <div class="text-xs text-white/80">Complete</div>
                  </div>
                </div>
              </div>

              <!-- Step Info -->
              <div class="step-info flex-1 min-w-0">
                <div class="step-header flex items-center gap-3 mb-2">
                  <div class="step-icon" :class="getStepIconClass()">
                    <Icon
                        :name="currentStep.completed ? 'mdi:check-circle' : currentStep.icon"
                        class="w-5 h-5 text-white"
                    />
                  </div>
                  <div class="step-status">
                    <div class="step-badge" :class="currentStep.completed ? 'step-badge-completed' : 'step-badge-pending'">
                      {{ currentStep.completed ? 'Completed' : 'Pending' }}
                    </div>
                  </div>
                </div>

                <h3 class="step-title text-xl font-bold text-white mb-1">
                  {{ currentStep.label }}
                </h3>

                <p class="step-description text-white/80 text-sm">
                  {{ getStepDescription(currentStep.key) }}
                </p>
              </div>
            </div>

            <!-- Enhanced Progress Bar -->
            <div class="progress-bar-section">
              <div class="progress-stats flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-white/90">
                  Step {{ completedSteps + 1 }} of {{ totalSteps }}
                </span>
                <span class="text-sm text-white/80">
                  {{ completedSteps }}/{{ totalSteps }} completed
                </span>
              </div>

              <div class="progress-bar-container relative">
                <div class="progress-bar-track bg-white/20 rounded-full h-3 overflow-hidden">
                  <div
                      class="progress-bar-fill h-full rounded-full transition-all duration-1000 ease-out"
                      :style="{ width: progress + '%' }"
                  ></div>
                </div>

                <!-- Step Markers -->
                <div class="step-markers absolute inset-0 flex items-center justify-between px-1">
                  <div
                      v-for="(step, index) in tourSteps"
                      :key="step.key"
                      class="step-marker"
                      :class="step.completed ? 'step-marker-completed' : 'step-marker-pending'"
                      :title="step.label"
                  >
                    <Icon
                        :name="step.completed ? 'mdi:check' : 'mdi:circle'"
                        class="w-3 h-3"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Section: Actions -->
          <div class="actions-section flex-shrink-0">
            <div class="actions-wrapper flex flex-col sm:flex-row lg:flex-col gap-3">

              <!-- Primary Action -->
              <component
                  :is="nextStep ? 'NuxtLink' : 'NuxtLink'"
                  :to="nextStep ? nextStep.path : '/terms'"
                  class="action-btn action-btn-primary group"
                  @click="trackOnboardingStep"
              >
                <div class="btn-content">
                  <Icon
                      :name="nextStep ? 'mdi:arrow-right-circle' : 'mdi:star-circle'"
                      class="w-5 h-5 transition-transform duration-300 group-hover:scale-110"
                  />
                  <span class="btn-text">
                    {{ nextStep ? 'Continue Setup' : 'Complete Onboarding' }}
                  </span>
                </div>
                <div class="btn-shimmer"></div>
              </component>

              <!-- Secondary Actions -->
              <div class="secondary-actions flex gap-2">
                <!-- Skip Step (only if not last step) -->
                <button
                    v-if="nextStep && completedSteps > 0"
                    @click="skipCurrentStep"
                    class="action-btn action-btn-secondary"
                    title="Skip this step"
                >
                  <Icon name="mdi:skip-next" class="w-4 h-4" />
                  <span class="sr-only">Skip</span>
                </button>

                <!-- Minimize Banner -->
                <button
                    @click="minimizeBanner"
                    class="action-btn action-btn-secondary"
                    title="Minimize banner"
                >
                  <Icon name="mdi:minus" class="w-4 h-4" />
                  <span class="sr-only">Minimize</span>
                </button>

                <!-- Close Banner (only when completed or last step) -->
                <button
                    v-if="!nextStep || completedSteps === totalSteps"
                    @click="closeBanner"
                    class="action-btn action-btn-secondary"
                    title="Close banner"
                >
                  <Icon name="mdi:close" class="w-4 h-4" />
                  <span class="sr-only">Close</span>
                </button>
              </div>
            </div>

            <!-- Completion Message -->
            <div v-if="!nextStep" class="completion-message mt-4 p-3 bg-white/10 rounded-lg backdrop-blur-sm">
              <div class="flex items-center gap-2 text-white">
                <Icon name="mdi:party-popper" class="w-5 h-5" />
                <span class="text-sm font-semibold">Congratulations! 🎉</span>
              </div>
              <p class="text-xs text-white/80 mt-1">
                You've completed your profile setup. Ready to explore all features!
              </p>
            </div>
          </div>
        </div>

        <!-- Steps Preview (Minimized State) -->
        <div v-if="isMinimized" class="steps-preview mt-4 pt-4 border-t border-white/20">
          <div class="steps-grid grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2">
            <button
                v-for="step in tourSteps"
                :key="step.key"
                @click="goToStep(step)"
                class="step-preview-item"
                :class="step.completed ? 'step-preview-completed' : 'step-preview-pending'"
                :title="step.label"
            >
              <Icon :name="step.icon" class="w-4 h-4" />
              <span class="text-xs truncate">{{ getStepShortLabel(step.key) }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Celebration Effects -->
      <div v-if="showCelebration" class="celebration-overlay absolute inset-0 pointer-events-none">
        <div
            v-for="i in 20"
            :key="`confetti-${i}`"
            class="confetti"
            :style="getConfettiStyle(i)"
        ></div>
      </div>
    </div>
  </transition>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useSanctumFetch, useRuntimeConfig, useSanctum } from '#imports'

// GSAP imports (client-side only)
let gsap: any = null

if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// Props & Emits
const emit = defineEmits<{
  (e: 'close'): void
  (e: 'minimize'): void
  (e: 'step-completed', step: string): void
}>()

// Composables
const config = useRuntimeConfig()
const { $toast } = useNuxtApp()

interface User {
  onboarded: boolean
  email_verified: boolean
  mobile_verified: boolean
}
const { user } = useSanctum<User>()

// State
const profileData = ref<any>(null)
const bannerClosed = ref(false)
const isMinimized = ref(false)
const showCelebration = ref(false)
const isLoading = ref(true)

// Refs
const bannerRef = ref<HTMLElement>()
const particles = ref<HTMLElement[]>([])

let gsapContext: any = null
let celebrationTimeout: number | null = null

// Configuration
const tourSteps = ref([
  {
    key: 'mobile',
    label: 'Add your mobile number',
    path: '/dashboard/account/edit',
    completed: false,
    icon: 'mdi:phone-plus'
  },
  {
    key: 'profile',
    label: 'Complete your profile details',
    path: '/dashboard/account/edit',
    completed: false,
    icon: 'mdi:account-edit'
  },
  {
    key: 'address',
    label: 'Add your home address',
    path: '/dashboard/account/address',
    completed: false,
    icon: 'mdi:home-plus'
  },
  {
    key: 'kyc',
    label: 'Submit your KYC documents',
    path: '/dashboard/account/kyc',
    completed: false,
    icon: 'mdi:file-document-check'
  },
  {
    key: 'subscription',
    label: 'Choose a subscription plan',
    path: '/dashboard/subscribe',
    completed: false,
    icon: 'mdi:crown-circle'
  },
])

// Computed
const isOnboarded = computed(() => user.value?.onboarded === true)
const completedSteps = computed(() => tourSteps.value.filter(s => s.completed).length)
const totalSteps = computed(() => tourSteps.value.length)
const nextStep = computed(() => tourSteps.value.find(s => !s.completed))
const currentStep = computed(() => nextStep.value ?? tourSteps.value[tourSteps.value.length - 1])
const progress = computed(() => (completedSteps.value / totalSteps.value) * 100)

// Progress circle calculations
const circumference = computed(() => 2 * Math.PI * 26)
const strokeDashoffset = computed(() => {
  const progressDecimal = progress.value / 100
  return circumference.value - (progressDecimal * circumference.value)
})

const showBanner = computed(() => {
  if (isOnboarded.value) return false
  if (bannerClosed.value) return false
  if (isLoading.value) return false
  return completedSteps.value < totalSteps.value
})

// Methods
function getStepDescription(stepKey: string) {
  const descriptions = {
    mobile: 'Add and verify your mobile number for account security',
    profile: 'Complete your name, date of birth, and gender information',
    address: 'Add your home address for faster delivery and billing',
    kyc: 'Upload required documents for account verification',
    subscription: 'Unlock premium features with a subscription plan'
  }
  return descriptions[stepKey as keyof typeof descriptions] || 'Complete this step to continue'
}

function getStepShortLabel(stepKey: string) {
  const labels = {
    mobile: 'Mobile',
    profile: 'Profile',
    address: 'Address',
    kyc: 'KYC',
    subscription: 'Plan'
  }
  return labels[stepKey as keyof typeof labels] || stepKey
}

function getStepIconClass() {
  return currentStep.value.completed
      ? 'step-icon-completed'
      : 'step-icon-pending'
}

function getParticleStyle(index: number) {
  const delay = index * 0.2
  const duration = 3 + (index % 3)
  const size = 2 + (index % 3)

  return {
    left: `${10 + (index * 8) % 80}%`,
    top: `${20 + (index * 12) % 60}%`,
    width: `${size}px`,
    height: `${size}px`,
    animationDelay: `${delay}s`,
    animationDuration: `${duration}s`
  }
}

function getConfettiStyle(index: number) {
  const colors = ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444']
  const color = colors[index % colors.length]

  return {
    left: `${Math.random() * 100}%`,
    backgroundColor: color,
    animationDelay: `${Math.random() * 2}s`,
    animationDuration: `${2 + Math.random() * 2}s`
  }
}

async function loadProfile() {
  isLoading.value = true
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/account/profile`)
    profileData.value = res?.data ?? res

    // Update step completion status
    tourSteps.value = tourSteps.value.map(step => {
      let completed = false
      switch (step.key) {
        case 'mobile':
          completed = Boolean(profileData.value?.mobile)
          break
        case 'profile':
          completed = Boolean(profileData.value?.name) &&
              Boolean(profileData.value?.dob) &&
              Boolean(profileData.value?.gender)
          break
        case 'address':
          completed = profileData.value?.address?.type === 'HOME'
          break
        case 'kyc':
          completed = Boolean(profileData.value?.kyc)
          break
        case 'subscription':
          completed = Boolean(profileData.value?.hasLevel) || Boolean(profileData.value?.level_id)
          break
      }
      return { ...step, completed }
    })
  } catch (err) {
    console.error('Failed to load profile', err)
    if ($toast && $toast.error) {
      $toast.error('Error', 'Failed to load onboarding progress')
    }
  } finally {
    isLoading.value = false
  }
}

function closeBanner() {
  bannerClosed.value = true
  emit('close')
}

function minimizeBanner() {
  isMinimized.value = !isMinimized.value
  emit('minimize')
}

function skipCurrentStep() {
  if (!nextStep.value) return

  // Mark current step as completed (skipped)
  const stepIndex = tourSteps.value.findIndex(s => s.key === nextStep.value!.key)
  if (stepIndex !== -1) {
    tourSteps.value[stepIndex].completed = true
    emit('step-completed', nextStep.value.key)

    if ($toast && $toast.info) {
      $toast.info('Skipped', `${nextStep.value.label} has been skipped`)
    }

    // Check if all steps completed
    if (completedSteps.value === totalSteps.value) {
      triggerCelebration()
    }
  }
}

function goToStep(step: any) {
  if (step.path) {
    navigateTo(step.path)
  }
}

function trackOnboardingStep() {
  // Track analytics here
  if (nextStep.value) {
    emit('step-completed', nextStep.value.key)
  }
}

function triggerCelebration() {
  showCelebration.value = true

  if (celebrationTimeout) {
    clearTimeout(celebrationTimeout)
  }

  celebrationTimeout = setTimeout(() => {
    showCelebration.value = false
  }, 3000)
}

function initializeAnimations() {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
    // Banner entrance animation
    if (bannerRef.value) {
      gsap.fromTo(bannerRef.value,
          { y: -100, opacity: 0 },
          { y: 0, opacity: 1, duration: 0.8, ease: 'back.out(1.7)' }
      )
    }

    // Particle animations
    if (particles.value.length > 0) {
      particles.value.forEach((particle, index) => {
        gsap.to(particle, {
          y: -20,
          opacity: 0.8,
          duration: 2 + (index % 3),
          repeat: -1,
          yoyo: true,
          ease: 'power2.inOut',
          delay: index * 0.2
        })
      })
    }

    // Progress ring animation
    const progressRing = bannerRef.value?.querySelector('.progress-circle')
    if (progressRing) {
      gsap.fromTo(progressRing,
          { strokeDashoffset: circumference.value },
          {
            strokeDashoffset: strokeDashoffset.value,
            duration: 2,
            ease: 'power2.out',
            delay: 0.5
          }
      )
    }
  })
}

// Watchers
watch(() => completedSteps.value, (newVal, oldVal) => {
  if (newVal > oldVal && newVal === totalSteps.value) {
    nextTick(() => {
      triggerCelebration()
    })
  }
})

// Lifecycle
onMounted(async () => {
  if (!isOnboarded.value && !bannerClosed.value) {
    await loadProfile()

    nextTick(() => {
      setTimeout(initializeAnimations, 100)
    })
  }
})

onUnmounted(() => {
  if (gsapContext) {
    gsapContext.kill()
  }

  if (celebrationTimeout) {
    clearTimeout(celebrationTimeout)
  }
})
</script>

<style scoped>
/* Banner Layout */
.onboarding-banner {
  position: relative;
  border-radius: 1.5rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  margin-bottom: 2rem;
}

.banner-background {
  border-radius: 1.5rem;
}

.particles-container {
  pointer-events: none;
}

.particle {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  animation: float infinite ease-in-out;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) scale(1);
    opacity: 0.7;
  }
  50% {
    transform: translateY(-10px) scale(1.1);
    opacity: 1;
  }
}

.banner-content {
  position: relative;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.content-wrapper {
  align-items: stretch;
}

@media (min-width: 1024px) {
  .content-wrapper {
    align-items: center;
  }
}

/* Progress Section */
.progress-section {
  min-width: 0;
}

.progress-header {
  align-items: flex-start;
}

@media (min-width: 640px) {
  .progress-header {
    align-items: center;
  }
}

.progress-ring-container {
  flex-shrink: 0;
}

.progress-ring {
  filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
}

.progress-circle {
  transition: stroke-dashoffset 1s ease-out;
}

.step-info {
  min-width: 0;
}

.step-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.step-icon-completed {
  background: linear-gradient(135deg, #10b981, #059669);
}

.step-icon-pending {
  background: rgba(255, 255, 255, 0.2);
}

.step-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.step-badge-completed {
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.step-badge-pending {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.step-title {
  line-height: 1.2;
}

.step-description {
  line-height: 1.4;
}

/* Progress Bar */
.progress-bar-container {
  position: relative;
}

.progress-bar-fill {
  background: linear-gradient(90deg, #10b981, #06d6a0);
  box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
}

.step-markers {
  pointer-events: none;
}

.step-marker {
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.step-marker-completed {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  box-shadow: 0 0 8px rgba(16, 185, 129, 0.5);
}

.step-marker-pending {
  background: rgba(255, 255, 255, 0.3);
  color: white;
}

/* Actions Section */
.actions-section {
  flex-shrink: 0;
}

.actions-wrapper {
  align-items: stretch;
}

@media (min-width: 640px) {
  .actions-wrapper {
    align-items: center;
  }
}

@media (min-width: 1024px) {
  .actions-wrapper {
    flex-direction: column;
    align-items: stretch;
  }
}

/* Action Buttons */
.action-btn {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: 0.875rem;
  transition: all 0.3s ease;
  text-decoration: none;
  border: none;
  cursor: pointer;
  overflow: hidden;
}

.action-btn-primary {
  background: linear-gradient(135deg, #ffffff, #f8fafc);
  color: #1f2937;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  min-width: 12rem;
}

.action-btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.action-btn-secondary {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
  padding: 0.5rem;
  min-width: 2.5rem;
  width: 2.5rem;
  height: 2.5rem;
}

.action-btn-secondary:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.05);
}

.btn-content {
  position: relative;
  z-index: 10;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-shimmer {
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
  transition: left 0.5s ease;
}

.action-btn-primary:hover .btn-shimmer {
  left: 100%;
}

.secondary-actions {
  flex-shrink: 0;
}

/* Completion Message */
.completion-message {
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Steps Preview (Minimized) */
.steps-preview {
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.step-preview-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  padding: 0.5rem;
  border-radius: 0.5rem;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  transition: all 0.2s ease;
  font-size: 0.75rem;
}

.step-preview-item:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: scale(1.05);
}

.step-preview-completed {
  background: rgba(16, 185, 129, 0.3);
  border-color: rgba(16, 185, 129, 0.5);
}

.step-preview-pending {
  opacity: 0.7;
}

/* Celebration Effects */
.celebration-overlay {
  pointer-events: none;
}

.confetti {
  position: absolute;
  width: 8px;
  height: 8px;
  animation: confettiFall linear infinite;
}

@keyframes confettiFall {
  0% {
    transform: translateY(-100vh) rotate(0deg);
    opacity: 1;
  }
  100% {
    transform: translateY(100vh) rotate(360deg);
    opacity: 0;
  }
}

/* Transitions */
.banner-slide-enter-active {
  transition: all 0.5s ease-out;
}

.banner-slide-leave-active {
  transition: all 0.3s ease-in;
}

.banner-slide-enter-from {
  transform: translateY(-100%);
  opacity: 0;
}

.banner-slide-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}

/* Responsive Design */
@media (max-width: 1023px) {
  .progress-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .step-info {
    width: 100%;
  }

  .actions-wrapper {
    width: 100%;
  }

  .action-btn-primary {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 639px) {
  .banner-content {
    padding: 1rem;
  }

  .content-wrapper {
    gap: 1.5rem;
  }

  .progress-ring {
    width: 3.5rem;
    height: 3.5rem;
  }

  .step-title {
    font-size: 1.125rem;
  }

  .steps-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .secondary-actions {
    justify-content: center;
  }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
  .particle,
  .progress-circle,
  .confetti {
    animation: none !important;
  }

  .action-btn:hover {
    transform: none;
  }
}

/* Focus States */
.action-btn:focus-visible,
.step-preview-item:focus-visible {
  outline: 2px solid #ffffff;
  outline-offset: 2px;
}

/* Screen Reader Only */
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}
</style>
