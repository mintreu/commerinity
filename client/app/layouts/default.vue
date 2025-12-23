<template>
  <div class="page-container">
    <!-- Desktop Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 w-72 transform transition-transform duration-300 ease-in-out lg:translate-x-0',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <div class="flex h-full flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800">
        <!-- Logo -->
        <div class="flex h-16 items-center gap-3 px-6 border-b border-slate-200 dark:border-slate-800">
          <div class="w-10 h-10 bg-gradient-to-br from-violet-600 to-fuchsia-600 rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/25">
            <UIcon
              name="i-lucide-hexagon"
              class="w-5 h-5 text-white"
            />
          </div>
          <span class="text-lg font-bold gradient-text-primary">Commerinity</span>
        </div>

        <!-- User Info (Compact) -->
        <div class="px-4 py-4 border-b border-slate-200 dark:border-slate-800">
          <NuxtLink
            to="/profile"
            class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          >
            <UAvatar
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
            <li v-for="item in navigationItems" :key="item.to">
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
                @click="closeSidebarOnMobile"
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
              <li v-for="item in accountMenuItems" :key="item.to">
                <NuxtLink
                  :to="item.to"
                  :class="[
                    'flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition-colors',
                    isActiveRoute(item.to)
                      ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white'
                      : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white'
                  ]"
                  @click="closeSidebarOnMobile"
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

    <!-- Sidebar Overlay (Mobile) -->
    <Transition name="fade">
      <div
        v-if="sidebarOpen"
        class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"
        @click="sidebarOpen = false"
      />
    </Transition>

    <!-- Main Content -->
    <div class="lg:pl-72 min-h-screen flex flex-col">
      <!-- Top Bar -->
      <header class="sticky top-0 z-30 h-16 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-b border-slate-200 dark:border-slate-800">
        <div class="flex h-full items-center justify-between px-4 lg:px-6">
          <!-- Left: Menu Toggle (Mobile) + Page Title -->
          <div class="flex items-center gap-4">
            <button
              class="lg:hidden p-2 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors"
              @click="sidebarOpen = true"
            >
              <UIcon
                name="i-lucide-menu"
                class="w-5 h-5"
              />
            </button>

            <div>
              <h1 class="text-lg font-semibold text-slate-900 dark:text-white">
                {{ pageTitle }}
              </h1>
            </div>
          </div>

          <!-- Right: Actions -->
          <div class="flex items-center gap-2">
            <!-- Search -->
            <button class="p-2 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
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
                class="p-2 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors"
                @click="toggleDark"
              >
                <UIcon
                  :name="isDark ? 'i-lucide-sun' : 'i-lucide-moon'"
                  class="w-5 h-5"
                />
              </button>
            </ClientOnly>

            <!-- User Menu (Desktop) -->
            <div class="hidden sm:block">
              <UserDropdown />
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 p-4 lg:p-6">
        <slot />
      </main>

      <!-- Footer -->
      <footer class="px-4 lg:px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-slate-500 dark:text-slate-400">
          <p>&copy; {{ currentYear }} Commerinity Pro. All rights reserved.</p>
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
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const router = useRouter()
const colorMode = useColorMode()
const { user, getNavigationItems, getAccountMenuItems, getUserTypeLabel } = useUserType()
const { logout } = useSanctum()

const currentYear = new Date().getFullYear()
const sidebarOpen = ref(false)

const isDark = computed(() => colorMode.value === 'dark')

const toggleDark = () => {
  colorMode.preference = colorMode.value === 'dark' ? 'light' : 'dark'
}

const navigationItems = computed(() => getNavigationItems())
const accountMenuItems = computed(() => getAccountMenuItems())

const pageTitle = computed(() => {
  if (route.meta.title) return route.meta.title as string

  const pathParts = route.path.split('/').filter(Boolean)
  if (pathParts.length === 0) return 'Dashboard'

  const lastPart = pathParts[pathParts.length - 1]
  if (!lastPart) return 'Dashboard'

  return lastPart.charAt(0).toUpperCase() + lastPart.slice(1).replace(/-/g, ' ')
})

const isActiveRoute = (path: string) => {
  return route.path === path || route.path.startsWith(path + '/')
}

const closeSidebarOnMobile = () => {
  if (window.innerWidth < 1024) {
    sidebarOpen.value = false
  }
}

const handleLogout = async () => {
  try {
    await logout()
    await router.push('/auth/login')
  } catch (error) {
    console.error('Logout error:', error)
  }
}

// Close sidebar on route change (mobile)
watch(() => route.path, () => {
  sidebarOpen.value = false
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
