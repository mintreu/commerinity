<template>
  <div
    v-if="user"
    class="relative"
  >
    <!-- User Button -->
    <button
      class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
      :class="{ 'bg-slate-100 dark:bg-slate-800': isOpen }"
      @click="toggleDropdown"
    >
      <UAvatar
        :alt="user.name"
        size="sm"
        class="ring-2 ring-slate-200 dark:ring-slate-700"
      />
      <UIcon
        name="i-lucide-chevron-down"
        class="w-4 h-4 text-slate-400 transition-transform duration-200"
        :class="{ 'rotate-180': isOpen }"
      />
    </button>

    <!-- Dropdown Menu -->
    <Transition name="dropdown">
      <div
        v-if="isOpen"
        class="absolute right-0 top-full mt-2 w-64 z-50"
      >
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-lg overflow-hidden">
          <!-- User Header -->
          <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-3">
              <UAvatar
                :alt="user.name"
                size="md"
              />
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-slate-900 dark:text-white truncate">
                  {{ user.name }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                  {{ user.email }}
                </p>
              </div>
            </div>
          </div>

          <!-- Menu Items -->
          <div class="p-2">
            <NuxtLink
              to="/profile"
              class="flex items-center gap-3 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
              @click="closeDropdown"
            >
              <UIcon
                name="i-lucide-user"
                class="w-4 h-4"
              />
              <span>Profile</span>
            </NuxtLink>

            <NuxtLink
              to="/orders"
              class="flex items-center gap-3 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
              @click="closeDropdown"
            >
              <UIcon
                name="i-lucide-package"
                class="w-4 h-4"
              />
              <span>Orders</span>
            </NuxtLink>

            <NuxtLink
              to="/notifications"
              class="flex items-center gap-3 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
              @click="closeDropdown"
            >
              <UIcon
                name="i-lucide-bell"
                class="w-4 h-4"
              />
              <span>Notifications</span>
            </NuxtLink>
          </div>

          <!-- Logout -->
          <div class="p-2 border-t border-slate-200 dark:border-slate-800">
            <button
              class="w-full flex items-center gap-3 px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950 rounded-lg transition-colors"
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
      </div>
    </Transition>

    <!-- Backdrop -->
    <Transition name="fade">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-40"
        @click="closeDropdown"
      />
    </Transition>
  </div>
</template>

<script setup lang="ts">
const { user } = useUserType()
const { logout } = useSanctum()
const router = useRouter()

const isOpen = ref(false)

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
}

const closeDropdown = () => {
  isOpen.value = false
}

const handleLogout = async () => {
  try {
    await logout()
    await router.push('/auth/login')
  } catch (error) {
    console.error('Logout error:', error)
  }
}
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.15s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
