<template>
  <transition name="banner-slide" appear>
    <div v-if="showBanner" role="banner" class="banner" ref="bannerRef">

      <!-- Simplified Background -->
      <div class="banner-bg"></div>

      <!-- Main Content -->
      <div class="banner-content">
        <div class="content-grid">

          <!-- Progress Section -->
          <div class="progress-section">

            <!-- Progress Ring & Info -->
            <div class="progress-header">
              <!-- Progress Ring -->
              <div class="progress-ring-wrap">
                <svg class="progress-ring" viewBox="0 0 60 60">
                  <circle cx="30" cy="30" r="26" stroke="rgba(255,255,255,0.2)" stroke-width="4" fill="none" />
                  <circle
                      cx="30" cy="30" r="26"
                      stroke="url(#grad)"
                      stroke-width="4"
                      fill="none"
                      stroke-linecap="round"
                      :stroke-dasharray="circumference"
                      :stroke-dashoffset="strokeDashoffset"
                      class="progress-circle"
                  />
                  <defs>
                    <linearGradient id="grad">
                      <stop offset="0%" stop-color="#10b981"/>
                      <stop offset="100%" stop-color="#06d6a0"/>
                    </linearGradient>
                  </defs>
                </svg>
                <div class="progress-text">
                  <div class="progress-percent">{{ Math.round(progress) }}%</div>
                  <div class="progress-label">Complete</div>
                </div>
              </div>

              <!-- Step Info -->
              <div class="step-info">
                <div class="step-header">
                  <div class="step-icon" :class="currentStep.completed ? 'completed' : 'pending'">
                    <Icon :name="currentStep.completed ? 'mdi:check-circle' : currentStep.icon" class="w-5 h-5" />
                  </div>
                  <div class="step-badge" :class="currentStep.completed ? 'completed' : 'pending'">
                    {{ currentStep.completed ? 'Completed' : 'Pending' }}
                  </div>
                </div>
                <h3 class="step-title">{{ currentStep.label }}</h3>
                <p class="step-desc">{{ getStepDescription(currentStep.key) }}</p>
              </div>
            </div>

            <!-- Progress Bar -->
            <div class="progress-bar-section">
              <div class="progress-stats">
                <span>Step {{ completedSteps + 1 }} of {{ totalSteps }}</span>
                <span>{{ completedSteps }}/{{ totalSteps }} completed</span>
              </div>
              <div class="progress-bar-wrap">
                <div class="progress-bar-track">
                  <div class="progress-bar-fill" :style="{ width: progress + '%' }"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions Section -->
          <div class="actions-section">
            <NuxtLink
                :to="nextStep ? nextStep.path : '/terms'"
                class="btn-primary"
                @click="trackOnboardingStep"
            >
              <Icon :name="nextStep ? 'mdi:arrow-right-circle' : 'mdi:star-circle'" class="w-5 h-5" />
              <span>{{ nextStep ? 'Continue Setup' : 'Complete Onboarding' }}</span>
            </NuxtLink>

            <div class="btn-group">
              <button v-if="nextStep && completedSteps > 0" @click="skipCurrentStep" class="btn-icon" title="Skip">
                <Icon name="mdi:skip-next" class="w-4 h-4" />
              </button>
              <button @click="minimizeBanner" class="btn-icon" title="Minimize">
                <Icon name="mdi:minus" class="w-4 h-4" />
              </button>
              <button v-if="!nextStep || completedSteps === totalSteps" @click="closeBanner" class="btn-icon" title="Close">
                <Icon name="mdi:close" class="w-4 h-4" />
              </button>
            </div>

            <!-- Completion Message -->
            <div v-if="!nextStep" class="completion-msg">
              <Icon name="mdi:party-popper" class="w-5 h-5" />
              <div>
                <div class="completion-title">Congratulations! 🎉</div>
                <p class="completion-text">You've completed your profile setup!</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Steps Preview (Minimized) -->
        <div v-if="isMinimized" class="steps-preview">
          <button
              v-for="step in tourSteps"
              :key="step.key"
              @click="goToStep(step)"
              class="step-preview"
              :class="step.completed ? 'completed' : 'pending'"
              :title="step.label"
          >
            <Icon :name="step.icon" class="w-4 h-4" />
            <span>{{ getStepShortLabel(step.key) }}</span>
          </button>
        </div>
      </div>

      <!-- Celebration -->
      <div v-if="showCelebration" class="celebration">
        <div v-for="i in 15" :key="i" class="confetti" :style="getConfettiStyle(i)"></div>
      </div>
    </div>
  </transition>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useSanctumFetch, useRuntimeConfig, useSanctum } from '#imports'

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'minimize'): void
  (e: 'step-completed', step: string): void
}>()

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
const bannerRef = ref<HTMLElement>()
let celebrationTimeout: number | null = null

// Steps
const tourSteps = ref([
  { key: 'mobile', label: 'Add your mobile number', path: '/dashboard/account/edit', completed: false, icon: 'mdi:phone-plus' },
  { key: 'profile', label: 'Complete your profile details', path: '/dashboard/account/edit', completed: false, icon: 'mdi:account-edit' },
  { key: 'address', label: 'Add your home address', path: '/dashboard/account/address', completed: false, icon: 'mdi:home-plus' },
  { key: 'kyc', label: 'Submit your KYC documents', path: '/dashboard/account/kyc', completed: false, icon: 'mdi:file-document-check' },
  { key: 'subscription', label: 'Choose a subscription plan', path: '/dashboard/subscribe', completed: false, icon: 'mdi:crown-circle' }
])

// Computed
const isOnboarded = computed(() => user.value?.onboarded === true)
const completedSteps = computed(() => tourSteps.value.filter(s => s.completed).length)
const totalSteps = computed(() => tourSteps.value.length)
const nextStep = computed(() => tourSteps.value.find(s => !s.completed))
const currentStep = computed(() => nextStep.value ?? tourSteps.value[tourSteps.value.length - 1])
const progress = computed(() => (completedSteps.value / totalSteps.value) * 100)
const circumference = computed(() => 2 * Math.PI * 26)
const strokeDashoffset = computed(() => circumference.value - ((progress.value / 100) * circumference.value))
const showBanner = computed(() => !isOnboarded.value && !bannerClosed.value && !isLoading.value && completedSteps.value < totalSteps.value)

// Methods
function getStepDescription(key: string) {
  const desc = {
    mobile: 'Add and verify your mobile number for account security',
    profile: 'Complete your name, date of birth, and gender information',
    address: 'Add your home address for faster delivery and billing',
    kyc: 'Upload required documents for account verification',
    subscription: 'Unlock premium features with a subscription plan'
  }
  return desc[key as keyof typeof desc] || 'Complete this step to continue'
}

function getStepShortLabel(key: string) {
  const labels = { mobile: 'Mobile', profile: 'Profile', address: 'Address', kyc: 'KYC', subscription: 'Plan' }
  return labels[key as keyof typeof labels] || key
}

function getConfettiStyle(i: number) {
  const colors = ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444']
  return {
    left: `${Math.random() * 100}%`,
    backgroundColor: colors[i % colors.length],
    animationDelay: `${Math.random() * 2}s`,
    animationDuration: `${2 + Math.random() * 2}s`
  }
}

async function loadProfile() {
  isLoading.value = true
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/account/profile`)
    profileData.value = res?.data ?? res

    tourSteps.value = tourSteps.value.map(step => {
      let completed = false
      switch (step.key) {
        case 'mobile': completed = Boolean(profileData.value?.mobile); break
        case 'profile': completed = Boolean(profileData.value?.name) && Boolean(profileData.value?.dob) && Boolean(profileData.value?.gender); break
        case 'address': completed = profileData.value?.address?.type === 'HOME'; break
        case 'kyc': completed = Boolean(profileData.value?.kyc); break
        case 'subscription': completed = Boolean(profileData.value?.hasLevel) || Boolean(profileData.value?.level_id); break
      }
      return { ...step, completed }
    })
  } catch (err) {
    console.error('Failed to load profile', err)
    if ($toast?.error) $toast.error('Error', 'Failed to load onboarding progress')
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
  const stepIndex = tourSteps.value.findIndex(s => s.key === nextStep.value!.key)
  if (stepIndex !== -1) {
    tourSteps.value[stepIndex].completed = true
    emit('step-completed', nextStep.value.key)
    if ($toast?.info) $toast.info('Skipped', `${nextStep.value.label} has been skipped`)
    if (completedSteps.value === totalSteps.value) triggerCelebration()
  }
}

function goToStep(step: any) {
  if (step.path) navigateTo(step.path)
}

function trackOnboardingStep() {
  if (nextStep.value) emit('step-completed', nextStep.value.key)
}

function triggerCelebration() {
  showCelebration.value = true
  if (celebrationTimeout) clearTimeout(celebrationTimeout)
  celebrationTimeout = setTimeout(() => { showCelebration.value = false }, 3000)
}

watch(() => completedSteps.value, (newVal, oldVal) => {
  if (newVal > oldVal && newVal === totalSteps.value) {
    nextTick(() => triggerCelebration())
  }
})

onMounted(async () => {
  if (!isOnboarded.value && !bannerClosed.value) await loadProfile()
})

onUnmounted(() => {
  if (celebrationTimeout) clearTimeout(celebrationTimeout)
})
</script>

<style scoped>
/* Banner */
.banner {
  position: relative;
  border-radius: 1.5rem;
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
  backdrop-filter: blur(20px);
  margin-bottom: 2rem;
  overflow: hidden;
}

.banner-bg {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%);
  opacity: 0.95;
  border-radius: 1.5rem;
}

.banner-content {
  position: relative;
  z-index: 10;
  padding: 1.5rem;
}

/* Content Grid */
.content-grid {
  display: grid;
  gap: 1.5rem;
  grid-template-columns: 1fr;
}

@media (min-width: 1024px) {
  .content-grid {
    grid-template-columns: 1fr auto;
    align-items: center;
  }
}

/* Progress Section */
.progress-header {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-direction: column;
}

@media (min-width: 640px) {
  .progress-header {
    flex-direction: row;
    align-items: center;
  }
}

.progress-ring-wrap {
  position: relative;
  width: 4rem;
  height: 4rem;
  flex-shrink: 0;
}

.progress-ring {
  width: 100%;
  height: 100%;
  transform: rotate(-90deg);
  filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
}

.progress-circle {
  transition: stroke-dashoffset 1s ease-out;
}

.progress-text {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.progress-percent {
  font-size: 1.125rem;
  font-weight: 700;
  color: white;
  line-height: 1;
}

.progress-label {
  font-size: 0.75rem;
  color: rgba(255,255,255,0.8);
}

.step-info {
  flex: 1;
  min-width: 0;
}

.step-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.step-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.step-icon.completed {
  background: linear-gradient(135deg, #10b981, #059669);
}

.step-icon.pending {
  background: rgba(255,255,255,0.2);
}

.step-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.step-badge.completed {
  background: rgba(16,185,129,0.2);
  color: #10b981;
  border: 1px solid rgba(16,185,129,0.3);
}

.step-badge.pending {
  background: rgba(255,255,255,0.2);
  color: white;
  border: 1px solid rgba(255,255,255,0.3);
}

.step-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: white;
  margin-bottom: 0.25rem;
}

.step-desc {
  font-size: 0.875rem;
  color: rgba(255,255,255,0.8);
}

/* Progress Bar */
.progress-stats {
  display: flex;
  justify-content: space-between;
  font-size: 0.875rem;
  color: rgba(255,255,255,0.9);
  margin-bottom: 0.5rem;
}

.progress-bar-track {
  height: 0.75rem;
  background: rgba(255,255,255,0.2);
  border-radius: 9999px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #10b981, #06d6a0);
  border-radius: 9999px;
  transition: width 1s ease-out;
  box-shadow: 0 0 10px rgba(16,185,129,0.5);
}

/* Actions */
.actions-section {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  flex-shrink: 0;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: white;
  color: #1f2937;
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: 0.875rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  transition: all 0.3s;
  text-decoration: none;
  min-width: 12rem;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.btn-group {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.btn-icon {
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,0.2);
  color: white;
  border: 1px solid rgba(255,255,255,0.3);
  border-radius: 0.5rem;
  transition: all 0.2s;
  cursor: pointer;
}

.btn-icon:hover {
  background: rgba(255,255,255,0.3);
  transform: scale(1.05);
}

.completion-msg {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 0.75rem;
  color: white;
}

.completion-title {
  font-size: 0.875rem;
  font-weight: 600;
}

.completion-text {
  font-size: 0.75rem;
  color: rgba(255,255,255,0.8);
  margin-top: 0.25rem;
}

/* Steps Preview */
.steps-preview {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255,255,255,0.2);
}

@media (min-width: 640px) {
  .steps-preview {
    grid-template-columns: repeat(5, 1fr);
  }
}

.step-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  padding: 0.5rem;
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 0.5rem;
  color: white;
  font-size: 0.75rem;
  transition: all 0.2s;
  cursor: pointer;
}

.step-preview:hover {
  background: rgba(255,255,255,0.2);
  transform: scale(1.05);
}

.step-preview.completed {
  background: rgba(16,185,129,0.3);
  border-color: rgba(16,185,129,0.5);
}

/* Celebration */
.celebration {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.confetti {
  position: absolute;
  width: 8px;
  height: 8px;
  animation: fall linear infinite;
}

@keyframes fall {
  0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
  100% { transform: translateY(100vh) rotate(360deg); opacity: 0; }
}

/* Transitions */
.banner-slide-enter-active {
  transition: all 0.5s ease-out;
}

.banner-slide-leave-active {
  transition: all 0.3s ease-in;
}

.banner-slide-enter-from,
.banner-slide-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}

/* Responsive */
@media (max-width: 639px) {
  .banner-content {
    padding: 1rem;
  }
  .step-title {
    font-size: 1.125rem;
  }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
  .confetti,
  .progress-circle {
    animation: none !important;
  }
  .btn-primary:hover,
  .btn-icon:hover {
    transform: none;
  }
}

/* Focus */
.btn-primary:focus-visible,
.btn-icon:focus-visible,
.step-preview:focus-visible {
  outline: 2px solid white;
  outline-offset: 2px;
}
</style>
