<template>
  <div class="page-container">
    <!-- Desktop Sidebar (Hidden on Mobile) -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-[60] w-72 transform transition-transform duration-300 ease-in-out',
        'hidden lg:block lg:translate-x-0'
      ]"
    >
      <div class="flex h-full flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800">
        <!-- Logo -->
        <NuxtLink
          to="/"
          class="flex h-16 items-center gap-3 px-6 border-b border-slate-200 dark:border-slate-800"
        >
          <img
            src="/logo.png"
            :alt="appName"
            class="h-10 w-auto"
          >
          <span class="text-lg font-bold gradient-text-primary">{{ appName }}</span>
        </NuxtLink>

        <!-- User Info (Compact) -->
        <div class="px-4 py-4 border-b border-slate-200 dark:border-slate-800">
          <NuxtLink
            to="/profile"
            class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          >
            <UAvatar
              :src="user?.avatar"
              :alt="user?.name"
              size="sm"
              class="ring-2 ring-violet-200 dark:ring-violet-800"
            />
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">
                {{ user?.name }}
              </p>
              <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                {{ getUserTypeLabel() }}
              </p>
            </div>
            <UIcon
              name="i-lucide-chevron-right"
              class="w-4 h-4 text-slate-400"
            />
          </NuxtLink>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto p-4 custom-scrollbar">
          <ul class="space-y-1">
            <li
              v-for="item in navigationItems"
              :key="item.to"
            >
              <NuxtLink
                :to="item.to"
                :class="[
                  'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                  item.highlight
                    ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-lg shadow-orange-500/25'
                    : isActiveRoute(item.to)
                      ? 'bg-violet-50 dark:bg-violet-950 text-violet-700 dark:text-violet-300 border border-violet-200 dark:border-violet-800'
                      : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white'
                ]"
              >
                <UIcon
                  :name="item.icon"
                  class="w-5 h-5"
                />
                <span class="flex-1">{{ item.label }}</span>
                <UBadge
                  v-if="item.badge"
                  :color="item.highlight ? 'neutral' : 'violet'"
                  size="xs"
                >
                  {{ item.badge }}
                </UBadge>
              </NuxtLink>
            </li>
          </ul>

          <!-- Account Section -->
          <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-800">
            <p class="px-3 mb-2 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
              Account
            </p>
            <ul class="space-y-1">
              <li
                v-for="item in accountMenuItems"
                :key="item.to"
              >
                <NuxtLink
                  :to="item.to"
                  :class="[
                    'flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition-colors',
                    isActiveRoute(item.to)
                      ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white'
                      : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white'
                  ]"
                >
                  <UIcon
                    :name="item.icon"
                    class="w-4 h-4"
                  />
                  <span>{{ item.label }}</span>
                </NuxtLink>
              </li>
            </ul>
          </div>
        </nav>

        <!-- Logout -->
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
          <button
            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-xl transition-colors"
            @click="handleLogout"
          >
            <UIcon
              name="i-lucide-log-out"
              class="w-4 h-4"
            />
            <span>Sign Out</span>
          </button>
        </div>
      </div>
    </aside>

    <!-- Mobile Slide-out Menu -->
    <Transition name="slide-menu">
      <aside
        v-if="mobileMenuOpen"
        class="fixed inset-y-0 left-0 z-[70] w-80 lg:hidden"
      >
        <div class="flex h-full flex-col bg-white dark:bg-slate-900 shadow-2xl">
          <!-- Close Button + Logo -->
          <div class="flex h-16 items-center justify-between px-4 border-b border-slate-200 dark:border-slate-800">
            <NuxtLink
              to="/"
              class="flex items-center gap-3"
            >
              <img
                src="/logo.png"
                :alt="appName"
                class="h-10 w-auto"
              >
              <span class="text-lg font-bold gradient-text-primary">{{ appName }}</span>
            </NuxtLink>
            <button
              class="p-2 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white rounded-xl transition-colors"
              @click="mobileMenuOpen = false"
            >
              <UIcon
                name="i-lucide-x"
                class="w-6 h-6"
              />
            </button>
          </div>

          <!-- User Info -->
          <div class="px-4 py-4 border-b border-slate-200 dark:border-slate-800">
            <NuxtLink
              to="/profile"
              class="flex items-center gap-3 p-3 rounded-2xl bg-gradient-to-r from-violet-50 to-fuchsia-50 dark:from-violet-900/30 dark:to-fuchsia-900/30"
              @click="mobileMenuOpen = false"
            >
              <UAvatar
                :src="user?.avatar"
                :alt="user?.name"
                size="lg"
                class="ring-2 ring-violet-200 dark:ring-violet-800"
              />
              <div class="flex-1 min-w-0">
                <p class="text-base font-bold text-slate-900 dark:text-white truncate">
                  {{ user?.name }}
                </p>
                <p class="text-sm text-violet-600 dark:text-violet-400 truncate">
                  {{ getUserTypeLabel() }}
                </p>
              </div>
              <UIcon
                name="i-lucide-chevron-right"
                class="w-5 h-5 text-violet-400"
              />
            </NuxtLink>
          </div>

          <!-- Navigation -->
          <nav class="flex-1 overflow-y-auto p-4">
            <ul class="space-y-1">
              <li
                v-for="item in navigationItems"
                :key="item.to"
              >
                <NuxtLink
                  :to="item.to"
                  :class="[
                    'flex items-center gap-3 px-4 py-3 rounded-xl text-base font-medium transition-all active:scale-[0.98]',
                    item.highlight
                      ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-lg'
                      : isActiveRoute(item.to)
                        ? 'bg-violet-100 dark:bg-violet-900/50 text-violet-700 dark:text-violet-300'
                        : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'
                  ]"
                  @click="mobileMenuOpen = false"
                >
                  <UIcon
                    :name="item.icon"
                    class="w-5 h-5"
                  />
                  <span class="flex-1">{{ item.label }}</span>
                  <UBadge
                    v-if="item.badge"
                    :color="item.highlight ? 'neutral' : 'violet'"
                    size="xs"
                  >
                    {{ item.badge }}
                  </UBadge>
                </NuxtLink>
              </li>
            </ul>

            <!-- Account Section -->
            <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-800">
              <p class="px-4 mb-2 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                Account
              </p>
              <ul class="space-y-1">
                <li
                  v-for="item in accountMenuItems"
                  :key="item.to"
                >
                  <NuxtLink
                    :to="item.to"
                    :class="[
                      'flex items-center gap-3 px-4 py-3 rounded-xl text-base transition-colors active:scale-[0.98]',
                      isActiveRoute(item.to)
                        ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white'
                        : 'text-slate-600 dark:text-slate-400'
                    ]"
                    @click="mobileMenuOpen = false"
                  >
                    <UIcon
                      :name="item.icon"
                      class="w-5 h-5"
                    />
                    <span>{{ item.label }}</span>
                  </NuxtLink>
                </li>
              </ul>
            </div>
          </nav>

          <!-- Logout -->
          <div class="p-4 border-t border-slate-200 dark:border-slate-800 safe-area-bottom">
            <button
              class="w-full flex items-center justify-center gap-2 px-4 py-3 text-base font-medium text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/50 rounded-xl transition-colors active:scale-[0.98]"
              @click="handleLogout"
            >
              <UIcon
                name="i-lucide-log-out"
                class="w-5 h-5"
              />
              <span>Sign Out</span>
            </button>
          </div>
        </div>
      </aside>
    </Transition>

    <!-- Mobile Menu Overlay -->
    <Transition name="fade">
      <div
        v-if="mobileMenuOpen"
        class="fixed inset-0 z-[65] bg-black/50 backdrop-blur-sm lg:hidden"
        @click="mobileMenuOpen = false"
      />
    </Transition>

    <!-- Main Content -->
    <div class="lg:pl-72 min-h-screen flex flex-col pb-16 lg:pb-0">
      <!-- Top Bar (Mobile-optimized) -->
      <header class="sticky top-0 z-30 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-b border-slate-200/80 dark:border-slate-800/80 safe-area-top">
        <div class="flex h-14 lg:h-16 items-center justify-between px-4 lg:px-6">
          <!-- Left: Menu Toggle (Mobile) + Page Title -->
          <div class="flex items-center gap-3">
            <button
              class="lg:hidden p-2 -ml-2 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white rounded-xl transition-colors active:bg-slate-100 dark:active:bg-slate-800"
              @click="mobileMenuOpen = true"
            >
              <UIcon
                name="i-lucide-menu"
                class="w-6 h-6"
              />
            </button>

            <div>
              <h1 class="text-lg font-bold text-slate-900 dark:text-white">
                {{ pageTitle }}
              </h1>
            </div>
          </div>

          <!-- Right: Actions -->
          <div class="flex items-center gap-1">
            <!-- Cart Button -->
            <NuxtLink
              to="/cart"
              class="p-2 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white rounded-xl transition-colors active:bg-slate-100 dark:active:bg-slate-800"
            >
              <UIcon
                name="i-lucide-shopping-cart"
                class="w-5 h-5"
              />
            </NuxtLink>

            <!-- Search -->
            <button class="p-2 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white rounded-xl transition-colors active:bg-slate-100 dark:active:bg-slate-800">
              <UIcon
                name="i-lucide-search"
                class="w-5 h-5"
              />
            </button>

            <!-- Notifications -->
            <NotificationBell />

            <!-- Theme Toggle -->
            <ClientOnly>
              <button
                class="p-2 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white rounded-xl transition-colors active:bg-slate-100 dark:active:bg-slate-800"
                @click="toggleDark"
              >
                <UIcon
                  :name="isDark ? 'i-lucide-sun' : 'i-lucide-moon'"
                  class="w-5 h-5"
                />
              </button>
            </ClientOnly>

            <!-- User Menu (Desktop) -->
            <div class="hidden lg:block ml-2">
              <UserDropdown />
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 p-4 lg:p-6">
        <slot />
      </main>

      <!-- Footer (Desktop Only) -->
      <footer class="hidden lg:block px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50">
        <div class="flex items-center justify-between text-sm text-slate-500 dark:text-slate-400">
          <p>&copy; {{ currentYear }} {{ appName }}. All rights reserved.</p>
          <div class="flex items-center gap-4">
            <NuxtLink
              to="/privacy"
              class="hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
            >
              Privacy
            </NuxtLink>
            <NuxtLink
              to="/terms"
              class="hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
            >
              Terms
            </NuxtLink>
          </div>
        </div>
      </footer>
    </div>

    <!-- Mobile Bottom Navigation (PWA Native Feel) -->
    <nav class="fixed bottom-0 left-0 right-0 z-50 lg:hidden bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-t border-slate-200/80 dark:border-slate-800/80 safe-area-bottom">
      <div class="flex items-center justify-around h-16 px-2">
        <NuxtLink
          v-for="item in mobileNavItems"
          :key="item.to"
          :to="item.to"
          :class="[
            'flex flex-col items-center justify-center flex-1 py-2 rounded-xl transition-all active:scale-95',
            isActiveRoute(item.to)
              ? 'text-violet-600 dark:text-violet-400'
              : 'text-slate-500 dark:text-slate-400'
          ]"
        >
          <div
            :class="[
              'w-10 h-10 rounded-xl flex items-center justify-center transition-all',
              isActiveRoute(item.to)
                ? 'bg-violet-100 dark:bg-violet-900/50'
                : ''
            ]"
          >
            <UIcon
              :name="item.icon"
              :class="[
                'w-6 h-6 transition-all',
                isActiveRoute(item.to) ? 'scale-110' : ''
              ]"
            />
          </div>
          <span
            :class="[
              'text-xs font-medium mt-1 transition-all',
              isActiveRoute(item.to) ? 'font-semibold' : ''
            ]"
          >
            {{ item.label }}
          </span>
        </NuxtLink>
      </div>
    </nav>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const router = useRouter()
const colorMode = useColorMode()
const config = useRuntimeConfig()
const { user, getNavigationItems, getAccountMenuItems, getUserTypeLabel } = useUserType()
const { logout } = useSanctum()

const appName = config.public.appShortName || config.public.appName || 'Commerinity'
const currentYear = new Date().getFullYear()
const mobileMenuOpen = ref(false)

const isDark = computed(() => colorMode.value === 'dark')

const toggleDark = () => {
  colorMode.preference = colorMode.value === 'dark' ? 'light' : 'dark'
}

const navigationItems = computed(() => getNavigationItems())
const accountMenuItems = computed(() => getAccountMenuItems())

// Mobile bottom navigation items (most important 5 items)
const mobileNavItems = computed(() => [
  { to: '/dashboard', label: 'Home', icon: 'i-lucide-home' },
  { to: '/shop', label: 'Shop', icon: 'i-lucide-shopping-bag' },
  { to: '/orders', label: 'Orders', icon: 'i-lucide-package' },
  { to: '/wallet', label: 'Wallet', icon: 'i-lucide-wallet' },
  { to: '/profile', label: 'Profile', icon: 'i-lucide-user' }
])

const pageTitle = computed(() => {
  if (route.meta.title) return route.meta.title as string

  const pathParts = route.path.split('/').filter(Boolean)
  if (pathParts.length === 0) return 'Dashboard'

  const lastPart = pathParts[pathParts.length - 1]
  if (!lastPart) return 'Dashboard'

  return lastPart.charAt(0).toUpperCase() + lastPart.slice(1).replace(/-/g, ' ')
})

const isActiveRoute = (path: string) => {
  if (path === '/dashboard') {
    return route.path === '/dashboard' || route.path === '/'
  }
  return route.path === path || route.path.startsWith(path + '/')
}

const handleLogout = async () => {
  try {
    await logout()
    await router.push('/auth/login')
  } catch (error) {
    console.error('Logout error:', error)
  }
}

// Close mobile menu on route change
watch(() => route.path, () => {
  mobileMenuOpen.value = false
})
</script>

<style scoped>
/* Fade transition for overlay */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Slide menu transition */
.slide-menu-enter-active,
.slide-menu-leave-active {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-menu-enter-from,
.slide-menu-leave-to {
  transform: translateX(-100%);
}
</style>
