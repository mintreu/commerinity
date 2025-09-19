<script setup lang="ts">
const route = useRoute()
const router = useRouter()

// Drawer state (mobile)
const drawerOpen = ref(false)

// Dark mode state: initialize from localStorage or system preference
const colorScheme = useState<'light' | 'dark'>('color-scheme', () => {
  if (process.client) {
    const saved = localStorage.getItem('theme')
    if (saved === 'dark' || saved === 'light') return saved as 'dark' | 'light'
    return window.matchMedia?.('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  }
  return 'light'
})

function applyTheme(theme: 'light' | 'dark') {
  if (!process.client) return
  const html = document.documentElement
  html.classList.toggle('dark', theme === 'dark')
  localStorage.setItem('theme', theme)
}
onMounted(() => applyTheme(colorScheme.value))
watch(colorScheme, (v) => applyTheme(v))

// App title from page meta
const pageTitle = computed(() => (route.meta?.title as string) || 'Dashboard')

// Primary tabs (top-level app areas)
const tabs = [
  { key: 'home',    label: 'Home',    to: '/dashboard',           icon: 'M3 12h18M12 3v18', activeIcon: 'M3 12h18M12 3v18' },
  { key: 'orders',  label: 'Orders',  to: '/dashboard/orders',    icon: 'M4 6h16M4 12h16M4 18h16', activeIcon: 'M4 6h16M4 12h16M4 18h16' },
  { key: 'wallet',  label: 'Wallet',  to: '/dashboard/wallet',    icon: 'M12 4v16m8-8H4', activeIcon: 'M12 4v16m8-8H4' },
  { key: 'profile', label: 'Profile', to: '/dashboard/account',   icon: 'M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-5 0-8 2.5-8 5v1h16v-1c0-2.5-3-5-8-5z', activeIcon: 'M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-5 0-8 2.5-8 5v1h16v-1c0-2.5-3-5-8-5z' }
]

// Active check
const isActive = (to: string) => route.path === to || route.path.startsWith(to + '/')

// Close drawer on route change
watch(() => route.fullPath, () => { drawerOpen.value = false })

// Meta: theme-color for light/dark and viewport-fit=cover for safe areas
useHead({
  title: pageTitle.value,
  meta: [
    { name: 'theme-color', media: '(prefers-color-scheme: light)', content: '#ffffff' },
    { name: 'theme-color', media: '(prefers-color-scheme: dark)', content: '#0b0f19' },
    { name: 'viewport', content: 'width=device-width, initial-scale=1, viewport-fit=cover' }
  ]
})
</script>

<template>
  <div class="d-native grid min-h-dvh bg-white text-gray-900 dark:bg-[#0b0f19] dark:text-gray-100 lg:grid-cols-[280px_minmax(0,1fr)]">
    <!-- Sidebar (desktop and up) -->
    <aside class="hidden border-r border-gray-200/70 bg-white/70 backdrop-blur dark:border-white/10 dark:bg-white/5 lg:flex lg:flex-col">
      <div class="px-6 pt-[calc(12px+env(safe-area-inset-top))] pb-3">
        <p class="text-lg font-semibold tracking-tight">Dashboard</p>
      </div>
      <nav class="flex-1 overflow-y-auto px-2 pb-6">
        <ul class="space-y-1">
          <li v-for="t in tabs" :key="t.key">
            <button
                class="group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-white/10"
                :class="isActive(t.to) ? 'bg-gray-100 text-gray-900 dark:bg-white/10 dark:text-white' : 'text-gray-600 dark:text-gray-300'"
                @click="router.push(t.to)"
                :aria-current="isActive(t.to) ? 'page' : undefined"
            >
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                <path :d="isActive(t.to) ? t.activeIcon : t.icon" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <span class="font-medium">{{ t.label }}</span>
            </button>
          </li>
        </ul>
      </nav>
      <div class="border-t border-gray-200/70 px-4 py-3 dark:border-white/10">
        <button
            class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200/70 px-3 py-2 text-sm hover:bg-gray-100 dark:border-white/10 dark:hover:bg-white/10"
            @click="colorScheme = colorScheme === 'dark' ? 'light' : 'dark'"
        >
          <span>{{ colorScheme === 'dark' ? 'Light mode' : 'Dark mode' }}</span>
          <svg v-if="colorScheme === 'dark'" viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
          <svg v-else viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor"><path d="M6.76 4.84l-1.8-1.79L3.17 4.84l1.79 1.8 1.8-1.8zM1 13h3v-2H1v2zm10 10h2v-3h-2v3zM4.84 19.16l1.8 1.79 1.79-1.79-1.79-1.8-1.8 1.8zM20 11v2h3v-2h-3zm-1.84-6.16l-1.8-1.8-1.79 1.8 1.79 1.8 1.8-1.8zM12 1h-2v3h2V1zm6.16 18.16l-1.8-1.8-1.79 1.8 1.79 1.8 1.8-1.8z"/></svg>
        </button>
      </div>
    </aside>

    <!-- Main column -->
    <div class="relative flex min-w-0 flex-col">
      <!-- Top app bar (mobile) -->
      <header class="sticky top-0 z-40 border-b border-gray-200/70 bg-white/90 backdrop-blur dark:border-white/10 dark:bg-[#0b0f19]/75 lg:hidden"
              :style="{ paddingTop: 'calc(12px + env(safe-area-inset-top))' }">
        <div class="mx-auto max-w-7xl px-4 py-3">
          <div class="flex items-center gap-3">
            <!-- Hamburger opens drawer -->
            <button
                class="inline-flex h-10 w-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-white/10"
                aria-label="Open menu"
                @click="drawerOpen = true"
            >
              <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>

            <h1 class="truncate text-[17px] font-semibold leading-6">{{ pageTitle }}</h1>

            <div class="ml-auto flex items-center gap-2">
              <button
                  class="inline-flex h-10 w-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-white/10"
                  aria-label="Toggle dark mode"
                  @click="colorScheme = colorScheme === 'dark' ? 'light' : 'dark'"
              >
                <svg v-if="colorScheme === 'dark'" viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                <svg v-else viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor"><path d="M6.76 4.84l-1.8-1.79L3.17 4.84l1.79 1.8 1.8-1.8zM1 13h3v-2H1v2zm10 10h2v-3h-2v3zM4.84 19.16l1.8 1.79 1.79-1.79-1.79-1.8-1.8 1.8zM20 11v2h3v-2h-3zm-1.84-6.16l-1.8-1.8-1.79 1.8 1.79 1.8 1.8-1.8zM12 1h-2v3h2V1zm6.16 18.16l-1.8-1.8-1.79 1.8 1.79 1.8 1.8-1.8z"/></svg>
              </button>
            </div>
          </div>
        </div>
      </header>

      <!-- Content -->
      <main
          class="relative mx-auto w-full max-w-7xl flex-1 px-4 pb-24 pt-4 lg:pb-8"
          :style="{ paddingBottom: 'calc(88px + env(safe-area-inset-bottom))' }"
      >
        <slot />
      </main>

      <!-- Bottom Navigation (mobile only) -->
      <nav
          class="fixed inset-x-0 bottom-0 z-50 border-t border-gray-200/70 bg-white/95 backdrop-blur dark:border-white/10 dark:bg-[#0b0f19]/80 lg:hidden"
          :style="{ paddingBottom: 'env(safe-area-inset-bottom)' }"
          role="navigation"
          aria-label="Primary"
      >
        <ul class="mx-auto grid max-w-xl grid-cols-4 gap-1 px-3 py-2">
          <li v-for="t in tabs" :key="t.key" class="flex">
            <button
                class="group relative flex w-full flex-col items-center justify-center gap-1 rounded-lg px-2 py-2"
                :class="isActive(t.to) ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-300'"
                @click="router.push(t.to)"
                :aria-current="isActive(t.to) ? 'page' : undefined"
            >
              <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2">
                <path :d="isActive(t.to) ? t.activeIcon : t.icon" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <span class="text-[11px] font-medium leading-4">{{ t.label }}</span>
              <span v-if="isActive(t.to)" class="absolute -z-10 h-9 w-9 rounded-full bg-indigo-50 dark:bg-indigo-400/10" />
            </button>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Navigation Drawer (mobile) -->
    <transition name="fade">
      <div
          v-if="drawerOpen"
          class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm lg:hidden"
          @click="drawerOpen = false"
      />
    </transition>
    <transition name="slide">
      <aside
          v-if="drawerOpen"
          class="fixed inset-y-0 left-0 z-50 w-[86%] max-w-xs border-r border-gray-200/70 bg-white p-3 pt-[calc(12px+env(safe-area-inset-top))] shadow-xl dark:border-white/10 dark:bg-[#0b0f19] lg:hidden"
          role="dialog"
          aria-modal="true"
          aria-label="Navigation"
      >
        <div class="mb-3 flex items-center justify-between px-2">
          <p class="text-base font-semibold">Menu</p>
          <button class="inline-flex h-10 w-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-white/10" aria-label="Close menu" @click="drawerOpen = false">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 6l12 12M6 18L18 6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
        </div>
        <nav class="max-h-[70vh] overflow-y-auto px-1">
          <ul class="space-y-1">
            <li v-for="t in tabs" :key="t.key">
              <button
                  class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-white/10"
                  :class="isActive(t.to) ? 'bg-gray-100 text-gray-900 dark:bg-white/10 dark:text-white' : 'text-gray-600 dark:text-gray-300'"
                  @click="router.push(t.to)"
                  :aria-current="isActive(t.to) ? 'page' : undefined"
              >
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path :d="isActive(t.to) ? t.activeIcon : t.icon" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="font-medium">{{ t.label }}</span>
              </button>
            </li>
          </ul>
        </nav>

        <div class="mt-3 border-t border-gray-200/70 px-2 pt-3 dark:border-white/10">
          <button
              class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200/70 px-3 py-2 text-sm hover:bg-gray-100 dark:border-white/10 dark:hover:bg-white/10"
              @click="colorScheme = colorScheme === 'dark' ? 'light' : 'dark'"
          >
            <span>{{ colorScheme === 'dark' ? 'Light mode' : 'Dark mode' }}</span>
          </button>
        </div>

        <div class="h-[env(safe-area-inset-bottom)]" />
      </aside>
    </transition>
  </div>
</template>

<style scoped>
.d-native {
  padding-left: max(0px, env(safe-area-inset-left));
  padding-right: max(0px, env(safe-area-inset-right));
}
.fade-enter-active, .fade-leave-active { transition: opacity .18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-enter-active, .slide-leave-active { transition: transform .22s ease; }
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }
</style>
