# Frontend Design System - Old Commerinity

## Overview
Premium-quality Nuxt 3 application with custom-built component system, sophisticated animations, and glassmorphism aesthetic.

**No UI Framework** - Custom components with Tailwind CSS

## Color Palette System

### Brand Colors
```css
/* Primary: Purple-Pink-Blue Gradient */
--brand-gradient: from-purple-600 via-pink-600 to-blue-600

/* Component Colors (Light Mode) */
--primary: orange-500 (hover: 400)
--secondary: blue-500 (hover: 400)
--success: green-500 (hover: 400)
--danger: red-600 (hover: 500)
--warning: yellow-500 (hover: 400)
--info: teal-500 (hover: 400)
--muted: gray-700 (hover: 900)

/* Component Colors (Dark Mode) */
--primary: orange-400 (hover: 300)
--secondary: blue-400 (hover: 300)
--success: green-400 (hover: 300)
--danger: red-500 (hover: 400)
--warning: yellow-400 (hover: 300)
--info: teal-400 (hover: 300)
--muted: gray-200 (hover: white)
```

### Gradient Patterns
```css
/* Backgrounds */
.bg-gradient-light { @apply from-gray-50 via-blue-50 to-purple-50; }
.bg-gradient-dark { @apply from-gray-950 via-blue-950 to-purple-950; }

/* Navbar */
.navbar-gradient {
  @apply from-white/90 via-white/95 to-white/90;
  /* Overlay: from-blue-500/5 to-transparent */
}

/* Hero */
.hero-gradient { @apply from-purple-600 via-pink-600 to-blue-600; }

/* Buttons */
.btn-primary { @apply from-purple-600 to-pink-600; }
.btn-secondary { @apply from-blue-600 to-purple-600; }

/* Icons */
.icon-green { @apply from-emerald-500 to-teal-600; }
.icon-blue { @apply from-blue-500 to-cyan-600; }
.icon-purple { @apply from-purple-500 to-pink-600; }
.icon-orange { @apply from-orange-500 to-red-600; }
```

## Typography System

### Font Family
- **Primary**: Roboto (400, 700) - Google Fonts
- **Fallback**: system-ui, -apple-system, sans-serif

### Font Scales
```css
/* Mobile First */
.text-xs   → 0.75rem (12px)
.text-sm   → 0.875rem (14px)
.text-base → 1rem (16px)
.text-lg   → 1.125rem (18px)
.text-xl   → 1.25rem (20px)
.text-2xl  → 1.5rem (24px)

/* Desktop Hero */
.text-3xl  → 1.875rem (30px)
.text-4xl  → 2.25rem (36px)
.text-5xl  → 3rem (48px)
.text-6xl  → 3.75rem (60px)
.text-7xl  → 4.5rem (72px)
```

### Font Weights
```css
.font-normal    → 400
.font-semibold  → 600
.font-bold      → 700
.font-extrabold → 800
.font-black     → 900
```

### Line Heights
```css
.leading-tight   → 1.25
.leading-snug    → 1.375
.leading-normal  → 1.5
.leading-relaxed → 1.625
```

## Spacing System

### Common Patterns
```css
/* Gap (Flexbox/Grid) */
gap-2  → 0.5rem (8px)
gap-3  → 0.75rem (12px)
gap-4  → 1rem (16px)
gap-6  → 1.5rem (24px)
gap-8  → 2rem (32px)

/* Padding */
p-3 → 0.75rem (12px)
p-4 → 1rem (16px)
p-5 → 1.25rem (20px)
p-6 → 1.5rem (24px)

/* Margins */
mb-3 → 0.75rem
mb-4 → 1rem
mt-6 → 1.5rem
mt-8 → 2rem
```

## Border Radius System

```css
/* Modern, Rounded Aesthetic */
.rounded-xl  → 0.75rem (12px)   /* Cards, buttons */
.rounded-2xl → 1rem (16px)      /* Large cards */
.rounded-3xl → 1.5rem (24px)    /* Hero sections */
.rounded-full → 9999px          /* Circular badges */
```

## Shadow System

```css
/* Elevation Hierarchy */
.shadow-sm   → Default card state
.shadow-md   → Hover state
.shadow-lg   → Elevated cards
.shadow-xl   → Buttons, important cards
.shadow-2xl  → Maximum elevation

/* Premium Glow Effects */
.shadow-purple-500/20 → Purple glow
.shadow-blue-500/25   → Blue glow
```

## Responsive Breakpoints

```css
/* Mobile First */
sm:  640px   /* Small tablets */
md:  768px   /* Tablets */
lg:  1024px  /* Laptops */
xl:  1280px  /* Desktops */
```

## Design Tokens for Nuxt UI Migration

### Recommended app.config.ts
```typescript
export default defineAppConfig({
  ui: {
    primary: 'purple',  // Brand color
    gray: 'slate',      // Neutral color
    colors: ['purple', 'pink', 'blue', 'green', 'orange', 'red'],

    button: {
      rounded: 'rounded-xl',
      default: {
        size: 'lg',
        variant: 'solid'
      }
    },

    card: {
      rounded: 'rounded-2xl',
      background: 'bg-white/90 dark:bg-gray-900/90',
      ring: 'ring-1 ring-gray-200 dark:ring-gray-800',
      shadow: 'shadow-sm hover:shadow-md',
      body: {
        padding: 'p-4 sm:p-6'
      }
    },

    input: {
      rounded: 'rounded-xl',
      padding: {
        sm: 'px-3 py-2',
        md: 'px-4 py-3',
        lg: 'px-5 py-3'
      }
    },

    modal: {
      rounded: 'rounded-2xl',
      background: 'bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl'
    }
  }
})
```

## Custom Tailwind Extensions

### Main.css Utilities
```css
/* Text Colors */
.text-primary { @apply text-orange-500 dark:text-orange-400; }
.text-secondary { @apply text-blue-500 dark:text-blue-400; }
.text-success { @apply text-green-500 dark:text-green-400; }
.text-danger { @apply text-red-600 dark:text-red-500; }
.text-warning { @apply text-yellow-500 dark:text-yellow-400; }
.text-info { @apply text-teal-500 dark:text-teal-400; }
.text-muted { @apply text-gray-700 dark:text-gray-200; }

/* Background Colors */
.bg-primary { @apply bg-orange-500 hover:bg-orange-400; }
.bg-secondary { @apply bg-blue-500 hover:bg-blue-400; }
.bg-success { @apply bg-green-500 hover:bg-green-400; }
.bg-danger { @apply bg-red-600 hover:bg-red-500; }

/* Gradient Text */
.text-gradient {
  @apply bg-gradient-to-r from-purple-600 via-pink-600 to-blue-600
         bg-clip-text text-transparent;
}

/* Glassmorphism */
.glass {
  @apply bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl;
}

.glass-navbar {
  @apply bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl
         border-b border-gray-200/50 dark:border-gray-800/50;
}
```

## Dark Mode Strategy

### Implementation
```typescript
// Class-based dark mode
// Toggle stored in localStorage
// System preference detection
// MediaQuery listener for changes
```

### Pattern
```vue
<template>
  <div class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <button class="bg-blue-500 dark:bg-blue-400
                   hover:bg-blue-400 dark:hover:bg-blue-300">
    </button>
  </div>
</template>
```

## Accessibility Standards

### Focus States
```css
.focus-ring {
  @apply focus:ring-2 focus:ring-blue-500 focus:border-transparent
         focus:outline-none transition-all;
}
```

### Touch Targets
- Minimum 44x44px for mobile
- Proper spacing between interactive elements

### Semantic HTML
- `<header>`, `<main>`, `<aside>`, `<nav>`, `<footer>`
- Proper heading hierarchy (h1-h6)
- ARIA labels where needed

## Animation Standards

### Transition Durations
```css
.transition-fast    → 150ms
.transition-base    → 300ms
.transition-slow    → 500ms
.transition-slower  → 700ms
```

### Easing Functions
```css
/* Standard */
ease-in-out → Cubic bezier
ease-out    → Deceleration
ease-in     → Acceleration

/* Custom (GSAP) */
back.out(1.7)  → Bounce back effect
sine.inOut     → Smooth oscillation
```

### GPU Acceleration
```css
/* Enable GPU acceleration for smooth animations */
.gpu-accelerate {
  will-change: transform;
  transform: translateZ(0);
}
```

## Component Design Principles

### 1. Layered Depth
- Use shadows for elevation
- Gradient overlays for depth
- Backdrop blur for glassmorphism
- Z-index hierarchy

### 2. Visual Hierarchy
- Size contrast (xl → sm)
- Weight contrast (bold → normal)
- Color contrast (brand → muted)
- Spacing rhythm

### 3. Consistency
- Consistent border radius (xl, 2xl)
- Consistent spacing scale (4, 6, 8)
- Consistent color usage
- Consistent iconography

### 4. Responsiveness
- Mobile-first approach
- Progressive enhancement
- Adaptive layouts
- Touch-friendly

### 5. Interactivity
- Hover states (scale, shadow)
- Active states (scale down)
- Focus states (ring)
- Loading states (skeleton)

## Premium Design Characteristics

### Glassmorphism
- Semi-transparent backgrounds (white/90, gray-900/90)
- Backdrop blur (blur-xl)
- Subtle borders (border-gray-200/50)
- Layered effects

### Gradient Magic
- Multi-stop gradients
- Gradient text (bg-clip-text)
- Gradient borders
- Gradient icons
- Gradient overlays

### Smooth Animations
- GSAP for complex animations
- CSS transitions for simple ones
- 300-500ms durations
- Easing functions for natural feel
- GPU-accelerated transforms

### Micro-interactions
- Scale on hover (105%)
- Scale on active (95%)
- Shadow on hover
- Icon animations
- Ripple effects
- Haptic feedback (mobile)

## Migration Checklist

### Keep As-Is
- [x] Color palette
- [x] Typography system
- [x] Gradient patterns
- [x] Animation durations
- [x] Border radius values
- [x] Shadow system
- [x] Spacing rhythm

### Migrate to Nuxt UI
- [ ] Button component
- [ ] Input component
- [ ] Card component
- [ ] Modal component
- [ ] Dropdown component
- [ ] Badge component
- [ ] Avatar component
- [ ] Table component

### Configure in Nuxt UI
- [ ] Primary color (purple)
- [ ] Gray color (slate)
- [ ] Border radius (xl, 2xl)
- [ ] Shadow values
- [ ] Padding values
- [ ] Font sizes

### Keep Custom
- [ ] Toast system (or adapt to Nuxt UI toast)
- [ ] Global loader
- [ ] Chart components
- [ ] Complex feature components
- [ ] GSAP animations
- [ ] Page layouts
