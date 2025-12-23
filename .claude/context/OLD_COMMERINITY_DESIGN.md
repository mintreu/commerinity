# Old Commerinity Design Specifications

## Color Scheme

### Background Gradients
```css
/* Light Mode */
bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50

/* Dark Mode */
dark:from-slate-900 dark:via-indigo-900 dark:to-purple-900
```

### Accent Colors
- **Primary**: Blue (blue-600, blue-500, blue-400)
- **Secondary**: Indigo (indigo-600, indigo-500, indigo-400)
- **Tertiary**: Purple (purple-600, purple-500, purple-400)
- **Success**: Emerald/Green (emerald-500, green-600)
- **Warning**: Orange/Amber
- **Error**: Red

### Text Colors
```css
/* Headings */
text-slate-900 dark:text-white

/* Body Text */
text-slate-600 dark:text-slate-400

/* Muted Text */
text-slate-500 dark:text-slate-500
```

## Animated Floating Orbs

### Main Orbs (Background Effects)
```vue
<!-- Orb 1: Top Right -->
<div class="absolute -top-40 -right-40 w-96 h-96
     bg-gradient-to-br from-blue-400/8 to-indigo-400/8
     rounded-full blur-3xl opacity-70 animate-pulse">
</div>

<!-- Orb 2: Bottom Left -->
<div class="absolute -bottom-40 -left-40 w-80 h-80
     bg-gradient-to-br from-purple-400/8 to-pink-400/8
     rounded-full blur-3xl opacity-60 animate-pulse">
</div>

<!-- Orb 3: Center -->
<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72
     bg-gradient-to-br from-emerald-400/6 to-cyan-400/6
     rounded-full blur-3xl opacity-50 animate-pulse">
</div>
```

### Micro Orbs
```css
w-3 h-3 bg-gradient-to-br from-blue-400/20 to-indigo-400/20 rounded-full blur-sm
```

### Brand Icons (Floating)
- Opacity: 8% (opacity-8)
- Icons: mdi:cart-outline, mdi:heart-outline, mdi:star-outline, etc.
- Size: w-5 h-5
- Animation: float-gentle (12s ease-in-out infinite)

## Typography

### Headings
```css
/* H1 - Brand Name */
text-3xl font-bold
bg-gradient-to-r from-slate-900 via-blue-600 to-indigo-600
dark:from-white dark:via-blue-400 dark:to-indigo-400
bg-clip-text text-transparent

/* H2 - Main Heading */
text-5xl xl:text-6xl font-bold

/* H3 - Section Heading */
text-lg font-semibold
```

### Body Text
```css
text-xl text-slate-600 dark:text-slate-400 leading-relaxed
text-sm text-slate-600 dark:text-slate-400
text-xs text-slate-500 dark:text-slate-500
```

## Glassmorphism Effects

### Cards/Panels
```css
bg-white/60 dark:bg-slate-800/60
backdrop-blur-xl
rounded-2xl
border border-white/20 dark:border-slate-700/50
hover:bg-white/80 dark:hover:bg-slate-800/80
transition-all duration-300
hover:-translate-y-1 hover:shadow-xl
```

### Overlays
```css
bg-white/95 dark:bg-slate-800/95
backdrop-blur-xl
```

## Feature Cards

### Icon Container
```css
w-12 h-12
bg-gradient-to-br from-blue-500 to-indigo-600
rounded-2xl
flex items-center justify-center
shadow-lg
```

### Card Structure
```vue
<div class="flex items-start gap-4 p-6
     bg-white/60 dark:bg-slate-800/60 backdrop-blur-xl
     rounded-2xl border border-white/20 dark:border-slate-700/50
     hover:bg-white/80 dark:hover:bg-slate-800/80
     transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">

  <!-- Icon -->
  <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600
       rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
    <Icon name="mdi:store-24-hour" class="w-6 h-6 text-white" />
  </div>

  <!-- Content -->
  <div>
    <h3 class="font-bold text-slate-900 dark:text-white mb-2">Title</h3>
    <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
      Description text
    </p>
  </div>
</div>
```

## Buttons

### Primary CTA Button
```css
px-8 py-4
bg-gradient-to-r from-blue-600 to-indigo-600
hover:from-blue-700 hover:to-indigo-700
text-white
rounded-2xl
font-bold text-lg
shadow-2xl hover:shadow-3xl
transition-all duration-300
hover:-translate-y-1
```

### Secondary Button
```css
px-6 py-3
bg-white/80 dark:bg-slate-700/80
hover:bg-white dark:hover:bg-slate-600
text-slate-700 dark:text-slate-300
rounded-xl
font-semibold
border-2 border-slate-200/60 dark:border-slate-600/60
hover:border-blue-300 dark:hover:border-blue-600
transition-all duration-300
hover:-translate-y-0.5
```

### Small Button
```css
px-4 py-2
bg-gradient-to-r from-emerald-500 to-green-600
hover:from-emerald-600 hover:to-green-700
text-white
rounded-xl
font-semibold text-sm
transition-all duration-300
hover:-translate-y-0.5 hover:shadow-lg
```

## Auth Layout Specific

### Split-Screen Layout (Desktop)
```vue
<div class="min-h-screen w-full flex">
  <!-- Left Side: Features (lg:w-1/2 xl:w-3/5) -->
  <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative flex-col justify-center p-12">
    <!-- Brand Logo -->
    <!-- Main Heading with Gradient Text -->
    <!-- Feature Cards -->
  </div>

  <!-- Right Side: Auth Form (lg:w-1/2 xl:w-2/5) -->
  <div class="flex-1 lg:w-1/2 xl:w-2/5 flex items-center justify-center p-6 lg:p-12">
    <!-- Login/Register Form -->
  </div>
</div>
```

### Logo
```vue
<img src="/logo.png" class="object-contain w-12 h-12" alt="Logo" />
```

### Brand Name
```vue
<h1 class="text-3xl font-bold bg-gradient-to-r
    from-slate-900 via-blue-600 to-indigo-600
    dark:from-white dark:via-blue-400 dark:to-indigo-400
    bg-clip-text text-transparent">
  {{ companyName }}
</h1>
<p class="text-slate-600 dark:text-slate-400 font-medium">
  Your Shopping Destination
</p>
```

## Dashboard Layout

### Sidebar
- Fixed left position
- Width: w-64 (expanded), w-20 (collapsed)
- Background: Uses gradient with floating orbs
- Contains user profile at top with avatar and badge

### Top Bar
- Sticky top-0
- Contains: Page title, notifications, theme toggle
- Background: bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm

### Main Content
- Padding: p-4 md:p-8
- Background maintains gradient with orbs
- Custom scrollbar styling

### Footer
```css
p-6
bg-white/50 dark:bg-gray-900/50
backdrop-blur-sm
border-t border-gray-200/50 dark:border-gray-800/50
```

## Animations

### GSAP Animations
```javascript
gsap.to(orb1, {
  x: 80,
  y: 40,
  rotation: 180,
  duration: 30,
  repeat: -1,
  yoyo: true,
  ease: 'sine.inOut',
  force3D: true
})
```

### CSS Animations
```css
@keyframes float-slow {
  0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.3; }
  50% { transform: translateY(-8px) rotate(2deg); opacity: 0.5; }
}

@keyframes float-gentle {
  0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.1; }
  50% { transform: translateY(-6px) rotate(3deg); opacity: 0.2; }
}

.animate-float-slow {
  animation: float-slow 8s ease-in-out infinite;
}

.animate-float-gentle {
  animation: float-gentle 12s ease-in-out infinite;
}
```

## Spacing & Layout

### Container Max Width
- max-w-7xl (dashboard)
- max-w-lg (auth forms)
- max-w-4xl (landing page content)

### Padding
- Desktop: p-12
- Mobile: p-6
- Content: p-4 md:p-8

### Gaps
- Card grids: gap-6
- Button groups: gap-4
- Text groups: gap-2, gap-3

### Rounded Corners
- Cards: rounded-2xl
- Buttons: rounded-xl, rounded-2xl
- Orbs: rounded-full
- Inputs: rounded-lg

## Icons

### Library
Material Design Icons (MDI)
Examples:
- mdi:store-24-hour
- mdi:truck-fast
- mdi:shield-check
- mdi:shopping
- mdi:cart-outline
- mdi:heart-outline
- mdi:star-outline

### Sizes
- Small: w-4 h-4
- Medium: w-5 h-5, w-6 h-6
- Large: w-8 h-8, w-12 h-12

## Trust Indicators / Badges

```vue
<div class="flex items-center gap-2">
  <Icon name="mdi:shield-check" class="w-4 h-4 text-green-500" />
  <span>SSL Secured</span>
</div>
```

## Responsive Breakpoints

- Mobile: < 640px
- Tablet: 640px - 1024px
- Desktop: > 1024px
- Large: > 1280px

### Mobile-Specific
- Stacked layout (auth pages)
- Bottom navigation bar
- Hamburger menu for sidebar
- Reduced padding (p-4 instead of p-12)

## Dark Mode Support

Every component has dark mode variants using Tailwind's dark: prefix
- Backgrounds become slate-900, slate-800
- Text becomes white, slate-400
- Borders become slate-700, slate-600
- Overlays maintain transparency for glassmorphism

## Summary

The old Commerinity design is characterized by:
1. **Soft, dreamy aesthetics** with blue/indigo/purple gradients
2. **Glassmorphism** with backdrop-blur and semi-transparent backgrounds
3. **Floating animated orbs** creating depth and movement
4. **Modern e-commerce feel** with clear CTAs and feature showcases
5. **Smooth animations** and hover effects throughout
6. **Professional typography** with gradient text effects
7. **Accessible dark mode** with careful color contrast
