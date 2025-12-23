<template>
  <aside class="h-full">
    <!-- Background with glassmorphism -->
    <div class="absolute inset-0 bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border-r border-gray-200/50 dark:border-gray-700/50">
      <div class="absolute inset-0 bg-gradient-to-b from-blue-50/30 via-transparent to-purple-50/30 dark:from-blue-950/30 dark:via-transparent dark:to-purple-950/30" />
    </div>

    <!-- Content -->
    <div class="relative z-10 h-full flex flex-col">
      <!-- Logo/Brand -->
      <div class="flex h-16 items-center border-b border-gray-200/50 px-6 dark:border-gray-700/50">
        <NuxtLink
          to="/"
          class="flex items-center gap-2"
        >
          <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
            <UIcon
              name="i-lucide-hexagon"
              class="h-6 w-6 text-white"
            />
          </div>
          <span class="gradient-text-primary text-xl font-bold">Commerinity</span>
        </NuxtLink>
      </div>

      <!-- User Profile -->
      <div class="border-b border-gray-200/50 p-6 dark:border-gray-700/50">
        <NuxtLink
          to="/profile"
          class="flex flex-col items-center text-center group"
        >
          <div class="relative mb-4">
            <UAvatar
              :alt="user?.name"
              size="xl"
              class="ring-4 ring-white/50 dark:ring-gray-800/50 shadow-xl group-hover:ring-blue-300 dark:group-hover:ring-blue-700 transition-all"
            />
            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-3 border-white dark:border-gray-900 rounded-full animate-pulse" />
          </div>

          <div class="w-full">
            <h2 class="text-lg font-black mb-1 truncate gradient-text-primary">
              {{ user?.name }}
            </h2>
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-3 truncate">
              {{ user?.mobile }}
            </p>

            <div class="flex justify-center">
              <div class="px-3 py-1 bg-gradient-to-r from-blue-500 to-purple-500 text-white text-xs font-bold rounded-full">
                {{ getUserTypeLabel() }}
              </div>
            </div>
          </div>
        </NuxtLink>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 p-3 overflow-y-auto custom-scrollbar">
        <div class="space-y-2">
          <NuxtLink
            v-for="item in navigationItems"
            :key="item.to"
            :to="item.to"
            :class="[
              'flex items-center gap-3 p-3 rounded-xl transition-all duration-300',
              item.highlight
                ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5'
                : 'hover:bg-white/80 dark:hover:bg-gray-700/80'
            ]"
            :active-class="item.highlight ? '' : 'bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 border border-blue-200 dark:border-blue-800'"
          >
            <div
              :class="[
                'w-10 h-10 rounded-xl flex items-center justify-center shadow-lg',
                item.highlight
                  ? 'bg-white/20'
                  : 'bg-gradient-to-br from-blue-500 to-indigo-600'
              ]"
            >
              <UIcon
                :name="item.icon"
                class="h-5 w-5 text-white"
              />
            </div>
            <span
              :class="[
                'font-semibold flex-1',
                item.highlight ? 'text-white' : 'text-slate-900 dark:text-white'
              ]"
            >
              {{ item.label }}
            </span>
            <UBadge
              v-if="item.badge"
              :color="item.highlight ? 'neutral' : 'primary'"
              variant="solid"
              size="xs"
            >
              {{ item.badge }}
            </UBadge>
          </NuxtLink>
        </div>

        <!-- Account Section -->
        <div class="mt-6 pt-4 border-t border-gray-200/50 dark:border-gray-700/50">
          <p class="px-3 mb-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Account
          </p>
          <div class="space-y-1">
            <NuxtLink
              v-for="item in accountMenuItems"
              :key="item.to"
              :to="item.to"
              class="flex items-center gap-3 p-2.5 rounded-lg transition-all duration-200 hover:bg-white/80 dark:hover:bg-gray-700/80"
              active-class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400"
            >
              <UIcon
                :name="item.icon"
                class="h-4 w-4 text-gray-500 dark:text-gray-400"
              />
              <span class="text-sm text-slate-700 dark:text-slate-300">{{ item.label }}</span>
            </NuxtLink>
          </div>
        </div>
      </nav>

      <!-- Logout Button -->
      <div class="border-t border-gray-200/50 p-4 dark:border-gray-700/50">
        <button
          class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
          @click="handleLogout"
        >
          <UIcon
            name="i-lucide-log-out"
            class="w-5 h-5"
          />
          <span>Logout</span>
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
const { user, getNavigationItems, getAccountMenuItems, getUserTypeLabel } = useUserType()
const { logout } = useSanctum()
const router = useRouter()

const navigationItems = computed(() => getNavigationItems())
const accountMenuItems = computed(() => getAccountMenuItems())

const handleLogout = async () => {
  try {
    await logout()
    await router.push('/auth/login')
  } catch (error) {
    console.error('Logout error:', error)
  }
}
</script>
