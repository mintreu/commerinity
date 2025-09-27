<template>
  <div class="dark-mode-toggle-container" ref="toggleContainer">
    <!-- Main Toggle Button -->
    <button
        @click="toggleDark"
        class="dark-mode-toggle group"
        :class="toggleClasses"
        :title="toggleTitle"
        :aria-label="toggleAriaLabel"
        :aria-pressed="isDark"
        ref="toggleButton"
    >
      <!-- Background Glow -->
      <div class="toggle-glow absolute inset-0 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

      <!-- Button Background -->
      <div class="toggle-background absolute inset-0 rounded-xl transition-all duration-500"></div>

      <!-- Icon Container -->
      <div class="icon-container relative z-10 flex items-center justify-center transition-transform duration-500" :class="{ 'rotate-180': isToggling }">

        <!-- Sun Icon (Light Mode) -->
        <transition name="icon-fade" mode="out-in">
          <div v-if="!isDark" key="sun" class="icon-wrapper sun-icon">
            <Icon name="mdi:white-balance-sunny" class="w-5 h-5" />

            <!-- Sun Rays -->
            <div class="sun-rays">
              <div v-for="i in 8" :key="i" class="sun-ray" :style="getSunRayStyle(i)"></div>
            </div>
          </div>

          <!-- Moon Icon (Dark Mode) -->
          <div v-else key="moon" class="icon-wrapper moon-icon">
            <Icon name="mdi:moon-waning-crescent" class="w-5 h-5" />

            <!-- Stars -->
            <div class="stars">
              <div v-for="i in 3" :key="i" class="star" :style="getStarStyle(i)"></div>
            </div>
          </div>
        </transition>
      </div>

      <!-- Ripple Effect -->
      <div v-if="showRipple" class="ripple-effect" ref="rippleEffect"></div>
    </button>

    <!-- Optional Label (for larger layouts) -->
    <div v-if="showLabel" class="toggle-label">
      <span class="label-text">{{ isDark ? 'Dark' : 'Light' }}</span>
    </div>

    <!-- Theme Selection Dropdown (Advanced Mode) -->
    <div v-if="showAdvanced && showDropdown" class="theme-dropdown card-premium" ref="dropdown">
      <div class="dropdown-header">
        <h3 class="dropdown-title">
          <Icon name="mdi:palette" class="w-4 h-4" />
          <span>Theme Settings</span>
        </h3>
      </div>

      <div class="dropdown-body">
        <div class="theme-options">
          <button
              v-for="option in themeOptions"
              :key="option.value"
              @click="setTheme(option.value)"
              class="theme-option"
              :class="{ 'theme-option-active': selectedTheme === option.value }"
          >
            <div class="option-icon" :class="option.iconClass">
              <Icon :name="option.icon" class="w-4 h-4" />
            </div>
            <div class="option-content">
              <span class="option-label">{{ option.label }}</span>
              <span class="option-description">{{ option.description }}</span>
            </div>
            <div v-if="selectedTheme === option.value" class="option-check">
              <Icon name="mdi:check" class="w-4 h-4 text-green-500" />
            </div>
          </button>
        </div>

        <!-- Additional Settings -->
        <div class="dropdown-footer">
          <div class="setting-item">
            <label class="setting-label">
              <input
                  v-model="followSystem"
                  type="checkbox"
                  class="setting-checkbox"
                  @change="updateSystemPreference"
              />
              <span class="checkbox-custom"></span>
              <span class="setting-text">Follow system preference</span>
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'

// GSAP imports (client-side only)
let gsap: any = null

if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// Props
interface Props {
  showLabel?: boolean
  showAdvanced?: boolean
  size?: 'sm' | 'md' | 'lg'
  variant?: 'default' | 'outlined' | 'filled'
}

const props = withDefaults(defineProps<Props>(), {
  showLabel: false,
  showAdvanced: false,
  size: 'md',
  variant: 'default'
})

// State
const isDark = ref(false)
const isToggling = ref(false)
const showRipple = ref(false)
const showDropdown = ref(false)
const followSystem = ref(false)
const selectedTheme = ref<'light' | 'dark' | 'system'>('system')

// Refs
const toggleContainer = ref<HTMLElement>()
const toggleButton = ref<HTMLElement>()
const rippleEffect = ref<HTMLElement>()
const dropdown = ref<HTMLElement>()

let gsapContext: any = null
let mediaQuery: MediaQueryList | null = null

// Theme options for advanced mode
const themeOptions = [
  {
    value: 'light',
    label: 'Light Mode',
    description: 'Clean and bright interface',
    icon: 'mdi:white-balance-sunny',
    iconClass: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400'
  },
  {
    value: 'dark',
    label: 'Dark Mode',
    description: 'Easy on the eyes',
    icon: 'mdi:moon-waning-crescent',
    iconClass: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400'
  },
  {
    value: 'system',
    label: 'System Default',
    description: 'Follows your device setting',
    icon: 'mdi:monitor',
    iconClass: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
  }
]

// Computed
const toggleClasses = computed(() => {
  const sizeClasses = {
    sm: 'w-8 h-8',
    md: 'w-10 h-10',
    lg: 'w-12 h-12'
  }

  const variantClasses = {
    default: 'bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700',
    outlined: 'border-2 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500',
    filled: isDark.value
        ? 'bg-gradient-to-br from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700'
        : 'bg-gradient-to-br from-yellow-400 to-orange-500 hover:from-yellow-500 hover:to-orange-600'
  }

  return [
    'relative overflow-hidden transition-all duration-300 ease-in-out',
    'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
    'active:scale-95 hover:scale-105 hover:shadow-lg',
    sizeClasses[props.size],
    variantClasses[props.variant]
  ]
})

const toggleTitle = computed(() => {
  if (selectedTheme.value === 'system') {
    return `System theme (currently ${isDark.value ? 'dark' : 'light'})`
  }
  return isDark.value ? 'Switch to Light Mode' : 'Switch to Dark Mode'
})

const toggleAriaLabel = computed(() => {
  return `Toggle theme. Current: ${selectedTheme.value === 'system' ? 'system' : (isDark.value ? 'dark' : 'light')} mode`
})

// Methods
function getSunRayStyle(index: number) {
  const angle = (index * 45) - 22.5
  return {
    transform: `rotate(${angle}deg)`,
    animationDelay: `${index * 0.1}s`
  }
}

function getStarStyle(index: number) {
  const positions = [
    { top: '20%', left: '25%', animationDelay: '0s' },
    { top: '30%', right: '30%', animationDelay: '0.5s' },
    { bottom: '25%', left: '35%', animationDelay: '1s' }
  ]
  return positions[index] || positions[0]
}

async function toggleDark() {
  if (isToggling.value) return

  isToggling.value = true
  showRipple.value = true

  // Haptic feedback
  if (process.client && 'vibrate' in navigator) {
    navigator.vibrate(50)
  }

  // Toggle theme
  if (selectedTheme.value === 'system') {
    // If currently system, switch to opposite of current
    selectedTheme.value = isDark.value ? 'light' : 'dark'
    followSystem.value = false
  } else {
    // Toggle between light and dark
    selectedTheme.value = selectedTheme.value === 'light' ? 'dark' : 'light'
  }

  await applyTheme(selectedTheme.value)

  // Trigger animations
  animateToggle()

  setTimeout(() => {
    isToggling.value = false
    showRipple.value = false
  }, 500)
}

function setTheme(theme: 'light' | 'dark' | 'system') {
  selectedTheme.value = theme
  followSystem.value = theme === 'system'
  applyTheme(theme)

  if (props.showAdvanced) {
    showDropdown.value = false
  }
}

async function applyTheme(theme: 'light' | 'dark' | 'system') {
  const html = document.documentElement

  if (theme === 'system') {
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    isDark.value = systemPrefersDark
    html.classList.toggle('dark', systemPrefersDark)
    localStorage.setItem('theme', 'system')
  } else {
    isDark.value = theme === 'dark'
    html.classList.toggle('dark', isDark.value)
    localStorage.setItem('theme', theme)
  }

  // Dispatch custom event for other components
  if (process.client) {
    window.dispatchEvent(new CustomEvent('theme-changed', {
      detail: { theme: selectedTheme.value, isDark: isDark.value }
    }))
  }
}

function updateSystemPreference() {
  if (followSystem.value) {
    setTheme('system')
  }
}

function handleSystemThemeChange(e: MediaQueryListEvent) {
  if (selectedTheme.value === 'system') {
    isDark.value = e.matches
    document.documentElement.classList.toggle('dark', e.matches)
  }
}

function handleClickOutside(event: Event) {
  if (showDropdown.value && dropdown.value && !dropdown.value.contains(event.target as Node) && !toggleButton.value?.contains(event.target as Node)) {
    showDropdown.value = false
  }
}

function animateToggle() {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
    // Icon rotation animation
    const iconContainer = toggleButton.value?.querySelector('.icon-container')
    if (iconContainer) {
      gsap.to(iconContainer, {
        rotation: 360,
        duration: 0.6,
        ease: 'back.out(1.7)'
      })
    }

    // Ripple effect
    if (rippleEffect.value) {
      gsap.fromTo(rippleEffect.value,
          { scale: 0, opacity: 0.8 },
          { scale: 2, opacity: 0, duration: 0.6, ease: 'power2.out' }
      )
    }

    // Animate rays and stars
    const rays = toggleButton.value?.querySelectorAll('.sun-ray')
    if (rays && !isDark.value) {
      gsap.fromTo(rays,
          { scale: 0.5, opacity: 0 },
          { scale: 1, opacity: 1, duration: 0.3, stagger: 0.05, delay: 0.2 }
      )
    }

    const stars = toggleButton.value?.querySelectorAll('.star')
    if (stars && isDark.value) {
      gsap.fromTo(stars,
          { scale: 0, opacity: 0 },
          { scale: 1, opacity: 1, duration: 0.4, stagger: 0.1, delay: 0.3 }
      )
    }
  })
}

function initializeAnimations() {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
    // Entrance animation
    if (toggleContainer.value) {
      gsap.fromTo(toggleContainer.value,
          { scale: 0.8, opacity: 0 },
          { scale: 1, opacity: 1, duration: 0.5, ease: 'back.out(1.7)' }
      )
    }

    // Animate decorative elements
    const rays = toggleButton.value?.querySelectorAll('.sun-ray')
    if (rays) {
      gsap.to(rays, {
        rotation: 360,
        duration: 8,
        repeat: -1,
        ease: 'none',
        stagger: 0.1
      })
    }

    const stars = toggleButton.value?.querySelectorAll('.star')
    if (stars) {
      gsap.to(stars, {
        scale: 1.2,
        duration: 2,
        repeat: -1,
        yoyo: true,
        ease: 'power2.inOut',
        stagger: 0.3
      })
    }
  })
}

// Initialize theme on mount
onMounted(() => {
  if (!process.client) return

  const saved = localStorage.getItem('theme')

  if (saved === 'system' || !saved) {
    selectedTheme.value = 'system'
    followSystem.value = true
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    isDark.value = systemPrefersDark
    document.documentElement.classList.toggle('dark', systemPrefersDark)

    // Listen for system theme changes
    mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    mediaQuery.addListener(handleSystemThemeChange)
  } else if (saved === 'dark') {
    selectedTheme.value = 'dark'
    isDark.value = true
    document.documentElement.classList.add('dark')
  } else {
    selectedTheme.value = 'light'
    isDark.value = false
    document.documentElement.classList.remove('dark')
  }

  // Add click outside listener for dropdown
  if (props.showAdvanced) {
    document.addEventListener('click', handleClickOutside)
  }

  nextTick(() => {
    setTimeout(initializeAnimations, 100)
  })
})

// Cleanup
onUnmounted(() => {
  if (mediaQuery) {
    mediaQuery.removeListener(handleSystemThemeChange)
  }

  if (props.showAdvanced) {
    document.removeEventListener('click', handleClickOutside)
  }

  if (gsapContext) {
    gsapContext.kill()
  }
})

// Advanced mode toggle
function toggleAdvanced() {
  if (!props.showAdvanced) return
  showDropdown.value = !showDropdown.value
}

// Watch for advanced mode
watch(() => props.showAdvanced, (newVal) => {
  if (newVal) {
    toggleButton.value?.addEventListener('contextmenu', (e) => {
      e.preventDefault()
      toggleAdvanced()
    })
  }
})
</script>

<style scoped>
/* Dark Mode Toggle Container */
.dark-mode-toggle-container {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Main Toggle Button */
.dark-mode-toggle {
  position: relative;
  border: none;
  cursor: pointer;
  border-radius: 0.75rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.dark-mode-toggle:focus {
  transform: scale(1.05);
}

.dark-mode-toggle:active {
  transform: scale(0.95);
}

/* Glow Effects */
.toggle-glow {
  background: radial-gradient(circle, rgba(59, 130, 246, 0.4), transparent 70%);
  filter: blur(8px);
}

.toggle-background {
  background: inherit;
  border-radius: inherit;
}

/* Icon Container */
.icon-container {
  width: 100%;
  height: 100%;
}

.icon-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}

/* Sun Icon & Effects */
.sun-icon {
  color: #f59e0b;
}

.sun-rays {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.sun-ray {
  position: absolute;
  width: 2px;
  height: 6px;
  background: linear-gradient(to bottom, #fbbf24, transparent);
  border-radius: 1px;
  top: -8px;
  left: 50%;
  transform-origin: 1px 20px;
  animation: sunRayPulse 2s ease-in-out infinite;
}

@keyframes sunRayPulse {
  0%, 100% {
    opacity: 0.6;
    transform: translateX(-50%) scale(1);
  }
  50% {
    opacity: 1;
    transform: translateX(-50%) scale(1.1);
  }
}

/* Moon Icon & Effects */
.moon-icon {
  color: #6366f1;
}

.stars {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.star {
  position: absolute;
  width: 2px;
  height: 2px;
  background: #fbbf24;
  border-radius: 50%;
  animation: starTwinkle 1.5s ease-in-out infinite;
}

.star::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 6px;
  height: 1px;
  background: inherit;
  border-radius: 1px;
}

.star::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) rotate(90deg);
  width: 6px;
  height: 1px;
  background: inherit;
  border-radius: 1px;
}

@keyframes starTwinkle {
  0%, 100% {
    opacity: 0.4;
    transform: scale(1);
  }
  50% {
    opacity: 1;
    transform: scale(1.2);
  }
}

/* Ripple Effect */
.ripple-effect {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 20px;
  height: 20px;
  background: radial-gradient(circle, rgba(59, 130, 246, 0.3), rgba(59, 130, 246, 0.1));
  border-radius: 50%;
  transform: translate(-50%, -50%);
  pointer-events: none;
}

/* Icon Transitions */
.icon-fade-enter-active,
.icon-fade-leave-active {
  transition: all 0.3s ease-in-out;
}

.icon-fade-enter-from {
  opacity: 0;
  transform: scale(0.8) rotate(-90deg);
}

.icon-fade-leave-to {
  opacity: 0;
  transform: scale(0.8) rotate(90deg);
}

/* Toggle Label */
.toggle-label {
  display: flex;
  align-items: center;
}

.label-text {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  transition: color 0.2s ease;
}

@media (prefers-color-scheme: dark) {
  .label-text {
    color: #d1d5db;
  }
}

/* Theme Dropdown (Advanced Mode) */
.theme-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 0.5rem;
  width: 16rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-radius: 1rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  overflow: hidden;
  z-index: 50;
  animation: dropdownSlide 0.2s ease-out;
}

@keyframes dropdownSlide {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (prefers-color-scheme: dark) {
  .theme-dropdown {
    background: rgba(17, 24, 39, 0.95);
    border: 1px solid rgba(55, 65, 81, 0.3);
  }
}

.dropdown-header {
  padding: 1rem;
  border-bottom: 1px solid rgba(229, 231, 235, 0.3);
}

@media (prefers-color-scheme: dark) {
  .dropdown-header {
    border-bottom-color: rgba(75, 85, 99, 0.3);
  }
}

.dropdown-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #111827;
}

@media (prefers-color-scheme: dark) {
  .dropdown-title {
    color: #f9fafb;
  }
}

.dropdown-body {
  padding: 0.5rem;
}

.theme-options {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.theme-option {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.75rem;
  background: transparent;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: left;
}

.theme-option:hover {
  background: rgba(243, 244, 246, 0.5);
}

.theme-option-active {
  background: rgba(59, 130, 246, 0.1);
  border: 1px solid rgba(59, 130, 246, 0.2);
}

@media (prefers-color-scheme: dark) {
  .theme-option:hover {
    background: rgba(55, 65, 81, 0.5);
  }

  .theme-option-active {
    background: rgba(59, 130, 246, 0.1);
    border-color: rgba(59, 130, 246, 0.3);
  }
}

.option-icon {
  width: 2rem;
  height: 2rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.option-content {
  flex: 1;
  min-width: 0;
}

.option-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #111827;
  margin-bottom: 0.125rem;
}

.option-description {
  display: block;
  font-size: 0.75rem;
  color: #6b7280;
}

@media (prefers-color-scheme: dark) {
  .option-label {
    color: #f3f4f6;
  }

  .option-description {
    color: #9ca3af;
  }
}

.option-check {
  flex-shrink: 0;
}

/* Dropdown Footer */
.dropdown-footer {
  padding: 0.75rem 0.5rem 0.25rem;
  border-top: 1px solid rgba(229, 231, 235, 0.3);
  margin-top: 0.5rem;
}

@media (prefers-color-scheme: dark) {
  .dropdown-footer {
    border-top-color: rgba(75, 85, 99, 0.3);
  }
}

.setting-item {
  display: flex;
  align-items: center;
}

.setting-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-size: 0.75rem;
  color: #6b7280;
}

@media (prefers-color-scheme: dark) {
  .setting-label {
    color: #9ca3af;
  }
}

.setting-checkbox {
  display: none;
}

.checkbox-custom {
  width: 1rem;
  height: 1rem;
  border: 2px solid #d1d5db;
  border-radius: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  position: relative;
}

.checkbox-custom::after {
  content: '';
  width: 0.5rem;
  height: 0.25rem;
  border: 2px solid transparent;
  border-top: none;
  border-right: none;
  transform: rotate(-45deg);
  opacity: 0;
  transition: opacity 0.2s ease;
}

.setting-checkbox:checked + .checkbox-custom {
  background: #3b82f6;
  border-color: #3b82f6;
}

.setting-checkbox:checked + .checkbox-custom::after {
  border-color: white;
  opacity: 1;
}

@media (prefers-color-scheme: dark) {
  .checkbox-custom {
    border-color: #6b7280;
  }
}

.setting-text {
  user-select: none;
}

/* Responsive Design */
@media (max-width: 640px) {
  .theme-dropdown {
    position: fixed;
    top: auto;
    bottom: 1rem;
    left: 1rem;
    right: 1rem;
    width: auto;
  }
}

/* Focus States */
.dark-mode-toggle:focus-visible,
.theme-option:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
  .sun-ray,
  .star {
    animation: none !important;
  }

  .icon-container {
    transition-duration: 0.1s;
  }

  .theme-dropdown {
    animation: none;
  }
}
</style>
