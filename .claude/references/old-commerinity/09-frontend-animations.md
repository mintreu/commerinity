# Frontend Animations & Interactions - Old Commerinity

## Animation Libraries

### Primary: GSAP 3.13.0
- Complex entrance animations
- Scroll-triggered effects
- Timeline orchestration
- Background orb animations

### Secondary: CSS Transitions
- Hover effects
- Micro-interactions
- State changes

### Tertiary: Vue Transitions
- Route transitions
- Component mount/unmount
- List transitions

## GSAP Animations

### Hero Section Entrance (`pages/index.vue`)

```javascript
const tl = gsap.timeline()

// Badge entrance (bounce back)
tl.from('.hero-badge', {
  y: 50,
  opacity: 0,
  duration: 1,
  ease: 'back.out(1.7)'
})

// Title entrance (stagger)
.from('.hero-title', {
  y: 100,
  opacity: 0,
  duration: 1.2,
  ease: 'back.out(1.7)'
}, '-=0.8')  // Overlap with previous by 0.8s

// Subtitle entrance
.from('.hero-subtitle', {
  y: 50,
  opacity: 0,
  duration: 1,
  ease: 'back.out(1.7)'
}, '-=0.6')

// CTA buttons entrance
.from('.hero-cta', {
  y: 50,
  opacity: 0,
  duration: 1,
  ease: 'back.out(1.7)'
}, '-=0.4')

// Stats counter entrance (stagger each item)
.from('.hero-stat', {
  y: 30,
  opacity: 0,
  duration: 0.8,
  stagger: 0.1,
  ease: 'back.out(1.5)'
}, '-=0.2')
```

**Key Techniques**:
- `back.out(1.7)` - Bounce back effect for playful feel
- Negative offsets (`-=`) - Overlap animations for smooth flow
- Stagger - Animate multiple elements with delay

### Background Orbs Animation (Dashboard)

```javascript
// Orb 1 - Slow float
gsap.to(orb1.value, {
  x: 80,
  y: 40,
  rotation: 180,
  duration: 30,
  repeat: -1,
  yoyo: true,
  ease: 'sine.inOut',
  force3D: true  // GPU acceleration
})

// Orb 2 - Medium float
gsap.to(orb2.value, {
  x: -60,
  y: 60,
  rotation: -150,
  duration: 25,
  repeat: -1,
  yoyo: true,
  ease: 'sine.inOut',
  force3D: true
})

// Orb 3 - Fast float
gsap.to(orb3.value, {
  x: 50,
  y: -50,
  rotation: 120,
  duration: 20,
  repeat: -1,
  yoyo: true,
  ease: 'sine.inOut',
  force3D: true
})
```

**Key Techniques**:
- `repeat: -1` - Infinite loop
- `yoyo: true` - Reverse animation on alternate cycles
- `sine.inOut` - Smooth oscillation
- `force3D: true` - GPU acceleration for performance

### Scroll-Triggered Animations (if used)

```javascript
gsap.registerPlugin(ScrollTrigger)

gsap.from('.fade-in-section', {
  scrollTrigger: {
    trigger: '.fade-in-section',
    start: 'top 80%',
    end: 'bottom 20%',
    toggleActions: 'play none none reverse'
  },
  opacity: 0,
  y: 50,
  duration: 1
})
```

## CSS Animations

### Floating Elements

```css
@keyframes float {
  0%, 100% {
    transform: translate(0, 0);
  }
  50% {
    transform: translate(20px, -20px);
  }
}

.animate-float {
  animation: float 15s ease-in-out infinite;
}

.animate-float-slow {
  animation: float 20s ease-in-out infinite;
}

.animate-float-slower {
  animation: float 25s ease-in-out infinite;
}
```

### Shimmer Effect (Global Loader, Cards)

```css
@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(200%);
  }
}

.shimmer {
  position: relative;
  overflow: hidden;
}

.shimmer::after {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  background: linear-gradient(
    90deg,
    transparent 0%,
    rgba(255, 255, 255, 0.3) 50%,
    transparent 100%
  );
  animation: shimmer 2s ease-in-out infinite;
}
```

### Pulse Animation

```css
@keyframes pulse-slow {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.05);
    opacity: 0.8;
  }
}

.animate-pulse-slow {
  animation: pulse-slow 3s ease-in-out infinite;
}
```

### Spin Animation (Loader Rings)

```css
@keyframes spin-slow {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin-slow {
  animation: spin-slow 3s linear infinite;
}

.animate-spin-reverse {
  animation: spin-slow 3s linear infinite reverse;
}
```

### Bounce Animation (Loader Dots)

```css
@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.animate-bounce-1 {
  animation: bounce 1s ease-in-out infinite;
  animation-delay: 0s;
}

.animate-bounce-2 {
  animation: bounce 1s ease-in-out infinite;
  animation-delay: 0.2s;
}

.animate-bounce-3 {
  animation: bounce 1s ease-in-out infinite;
  animation-delay: 0.4s;
}
```

### Gradient Animation (Hero Background)

```css
@keyframes gradient-shift {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

.bg-gradient-animated {
  background: linear-gradient(
    270deg,
    #667eea 0%,
    #764ba2 25%,
    #f093fb 50%,
    #667eea 75%,
    #764ba2 100%
  );
  background-size: 400% 400%;
  animation: gradient-shift 15s ease infinite;
}
```

## Hover Effects

### Card Hover Pattern

```vue
<div class="group relative overflow-hidden
     transition-all duration-300
     hover:-translate-y-1 hover:scale-[1.02]
     hover:shadow-xl hover:shadow-purple-500/20">

  <!-- Content -->

  <!-- Gradient Overlay (appears on hover) -->
  <div class="absolute inset-0
       bg-gradient-to-br from-purple-500/0 to-transparent
       group-hover:from-purple-500/5
       transition-all duration-500" />

  <!-- Shine Effect (sweeps across on hover) -->
  <div class="absolute inset-0 opacity-0 group-hover:opacity-100
       transition-opacity duration-300">
    <div class="absolute inset-0
         bg-gradient-to-r from-transparent via-white/20 to-transparent
         -translate-x-full group-hover:translate-x-full
         transition-transform duration-700" />
  </div>

  <!-- Bottom Glow (appears on hover) -->
  <div class="absolute bottom-0 left-0 right-0 h-1
       bg-gradient-to-r from-purple-500 via-pink-500 to-blue-500
       opacity-0 group-hover:opacity-100
       transition-opacity duration-300" />
</div>
```

**Key Techniques**:
- `group` - Parent class for hover state
- `group-hover:` - Child responds to parent hover
- Multiple layers with different transition durations
- Gradient overlays for depth

### Button Hover Pattern

```vue
<button class="
  px-6 py-3
  bg-gradient-to-r from-purple-600 to-pink-600
  text-white font-bold rounded-xl
  shadow-xl hover:shadow-2xl
  transform
  hover:scale-105 active:scale-95
  transition-all duration-300">
  Click Me
</button>
```

**States**:
- Default: Normal scale, xl shadow
- Hover: Scale 1.05, 2xl shadow
- Active: Scale 0.95 (pressed effect)

### Icon Rotation on Hover

```vue
<div class="group">
  <Icon name="mdi:rocket-launch"
        class="transition-transform duration-300
               group-hover:rotate-12 group-hover:scale-110" />
</div>
```

### Pulsing Ring on Hover

```vue
<div class="relative group">
  <!-- Icon Container -->
  <div class="w-16 h-16 rounded-2xl bg-gradient-to-br
       from-purple-500 to-pink-600">
    <Icon name="mdi:star" class="w-8 h-8 text-white" />
  </div>

  <!-- Pulsing Ring -->
  <div class="absolute inset-0 rounded-2xl
       border-2 border-purple-500
       opacity-0 group-hover:opacity-100
       animate-ping" />
</div>
```

## Loading States

### Global Loader Animation

```vue
<div class="fixed inset-0 z-50 flex items-center justify-center
     bg-gradient-to-br from-purple-600 via-pink-600 to-blue-600
     backdrop-blur-xl">

  <!-- Floating Orbs -->
  <div class="absolute w-64 h-64 bg-white/10 rounded-full blur-3xl
       animate-float" style="top: 10%; left: 10%;" />
  <div class="absolute w-48 h-48 bg-white/10 rounded-full blur-3xl
       animate-float-slow" style="top: 60%; right: 20%;" />
  <div class="absolute w-56 h-56 bg-white/10 rounded-full blur-3xl
       animate-float-slower" style="bottom: 10%; left: 40%;" />

  <!-- Center Content -->
  <div class="relative text-center">
    <!-- Logo with Shimmer -->
    <div class="mb-8 shimmer">
      <img src="/logo.png" class="w-32 h-32 mx-auto" />
    </div>

    <!-- Triple Rotating Rings -->
    <div class="relative w-24 h-24 mx-auto">
      <div class="absolute inset-0 border-4 border-t-white/50 border-r-transparent
           border-b-transparent border-l-transparent
           rounded-full animate-spin" />
      <div class="absolute inset-2 border-4 border-t-transparent border-r-white/50
           border-b-transparent border-l-transparent
           rounded-full animate-spin-reverse" />
      <div class="absolute inset-4 border-4 border-t-transparent border-r-transparent
           border-b-white/50 border-l-transparent
           rounded-full animate-spin-slow" />
      <!-- Center Dot -->
      <div class="absolute inset-0 flex items-center justify-center">
        <div class="w-3 h-3 bg-white rounded-full animate-pulse-slow" />
      </div>
    </div>

    <!-- Animated Dots -->
    <div class="mt-8 flex justify-center gap-2">
      <div class="w-2 h-2 bg-white rounded-full animate-bounce-1" />
      <div class="w-2 h-2 bg-white rounded-full animate-bounce-2" />
      <div class="w-2 h-2 bg-white rounded-full animate-bounce-3" />
    </div>

    <!-- Optional Progress Bar -->
    <div v-if="showProgress" class="mt-6 w-64 h-1 bg-white/20 rounded-full overflow-hidden">
      <div class="h-full bg-gradient-to-r from-white/50 to-white rounded-full
           shimmer" :style="{ width: progress + '%' }" />
    </div>
  </div>
</div>
```

### Skeleton Loaders

```vue
<!-- Card Skeleton -->
<div class="bg-white dark:bg-gray-800 rounded-2xl p-6 space-y-4">
  <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse" />
  <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 animate-pulse" />
  <div class="h-32 bg-gray-200 dark:bg-gray-700 rounded animate-pulse" />
</div>

<!-- Chart Skeleton -->
<div class="h-80 bg-gray-100 dark:bg-gray-700 rounded-2xl animate-pulse" />

<!-- List Skeleton -->
<div class="space-y-3">
  <div v-for="i in 5" :key="i"
       class="h-16 bg-gray-100 dark:bg-gray-700 rounded-xl animate-pulse" />
</div>
```

## Micro-interactions

### Ripple Effect on Click

```vue
<script setup>
const createRipple = (event) => {
  const button = event.currentTarget
  const ripple = document.createElement('span')
  const rect = button.getBoundingClientRect()
  const size = Math.max(rect.width, rect.height)
  const x = event.clientX - rect.left - size / 2
  const y = event.clientY - rect.top - size / 2

  ripple.style.width = ripple.style.height = size + 'px'
  ripple.style.left = x + 'px'
  ripple.style.top = y + 'px'
  ripple.classList.add('ripple')

  button.appendChild(ripple)

  setTimeout(() => ripple.remove(), 600)
}
</script>

<style>
.ripple {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.6);
  transform: scale(0);
  animation: ripple-animation 0.6s ease-out;
  pointer-events: none;
}

@keyframes ripple-animation {
  to {
    transform: scale(4);
    opacity: 0;
  }
}
</style>

<template>
  <button @click="createRipple" class="relative overflow-hidden">
    Click Me
  </button>
</template>
```

### Haptic Feedback (Mobile)

```typescript
const triggerHaptic = () => {
  if (process.client && window.navigator.vibrate) {
    window.navigator.vibrate(50)  // 50ms vibration
  }
}

// Usage
<button @click="triggerHaptic(); handleClick()">
```

### Scale Transform on Click

```vue
<button class="
  transform transition-transform duration-150
  active:scale-95">
  <!-- Pressed effect -->
</button>
```

### Icon Spin on Action

```vue
<script setup>
const isLoading = ref(false)

const handleAction = async () => {
  isLoading.value = true
  await someAsyncAction()
  isLoading.value = false
}
</script>

<template>
  <button @click="handleAction">
    <Icon name="mdi:reload"
          :class="{ 'animate-spin': isLoading }" />
  </button>
</template>
```

## Page Transitions

### Fade Transition (Overlays)

```vue
<Transition name="fade">
  <div v-if="show" class="overlay">
    Content
  </div>
</Transition>

<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
```

### Slide Transition (Mobile Sidebar)

```vue
<Transition name="slide-right">
  <aside v-if="isOpen" class="sidebar">
    Content
  </aside>
</Transition>

<style>
.slide-right-enter-active, .slide-right-leave-active {
  transition: transform 0.3s ease;
}

.slide-right-enter-from {
  transform: translateX(-100%);
}

.slide-right-leave-to {
  transform: translateX(-100%);
}
</style>
```

### View Transitions API (Nuxt Config)

```typescript
// nuxt.config.ts
export default defineNuxtConfig({
  experimental: {
    viewTransition: true  // Native page transitions
  }
})
```

## Performance Optimization

### GPU Acceleration

```css
/* Enable GPU acceleration for smooth animations */
.gpu-accelerate {
  will-change: transform;
  transform: translateZ(0);
  backface-visibility: hidden;
}
```

### Reduce Motion (Accessibility)

```css
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

### Debounced Animations

```typescript
import { debounce } from 'lodash-es'

const handleScroll = debounce(() => {
  // Animate on scroll
}, 100)

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
```

## Animation Best Practices

### 1. Duration Guidelines
- **Instant**: 0-100ms (micro-interactions)
- **Fast**: 100-300ms (hover, focus)
- **Medium**: 300-500ms (modals, drawers)
- **Slow**: 500-1000ms (page transitions)
- **Very Slow**: 1000ms+ (decorative, background)

### 2. Easing Functions
- **ease-in**: Acceleration (use for exits)
- **ease-out**: Deceleration (use for entrances)
- **ease-in-out**: Smooth (use for movements)
- **back.out**: Bounce (use for playful entrances)
- **sine.inOut**: Natural oscillation

### 3. Stagger Timing
- List items: 50-100ms delay between items
- Hero elements: 200-400ms overlap
- Card grids: 100-150ms delay

### 4. Mobile Considerations
- Reduce animation complexity on mobile
- Use `transform` over position changes
- Prefer CSS animations over JS for simple effects
- Test on low-end devices

### 5. Accessibility
- Provide `prefers-reduced-motion` support
- Don't rely solely on animation for information
- Ensure animations don't cause seizures (no rapid flashing)
- Allow animations to be skipped

## Migration to Nuxt UI

### What to Keep
- ✅ GSAP entrance animations
- ✅ Background orb animations
- ✅ Global loader design
- ✅ Card hover effects
- ✅ Shimmer effects
- ✅ Custom micro-interactions

### What to Adapt
- ⚠️ Button transitions (integrate with UButton)
- ⚠️ Card animations (integrate with UCard)
- ⚠️ Modal transitions (use UModal transitions)

### Integration Strategy
1. Keep GSAP for complex animations
2. Use CSS transitions for Nuxt UI components
3. Configure Nuxt UI transition durations to match existing design
4. Maintain custom animation classes
5. Test all animations in both light and dark modes
