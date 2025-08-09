<template>
  <aside
      ref="sidebarEl"
      :class="[
      'bg-white dark:bg-gray-900 shadow-md fixed top-0 left-0 z-40 flex flex-col overflow-hidden transition-all duration-300 ease-in-out',
      collapsed ? 'w-20 h-16 md:h-screen' : 'w-64 h-screen'
    ]"
  >
    <!-- Header -->
    <div
        class="h-16 flex flex-row items-center justify-between px-2 border-b border-gray-200 dark:border-gray-800"
    >
      <NuxtLink
          to="/"
          class="flex items-center gap-3 select-none"
          :class="collapsed ? 'justify-center' : ''"
          aria-label="Homepage"
      >
        <img
            src="/logo.png"
            alt="Logo"
            class="object-contain"
            :class="collapsed ? 'w-8 h-8' : 'w-14 h-14'"
            draggable="false"
            loading="lazy"
        />
        <span
            v-if="!collapsed"
            class="text-primary-600 dark:text-primary-400 font-extrabold text-xl tracking-wide"
        >
          Commernity
        </span>
      </NuxtLink>

      <!-- Sidebar Toggle -->
      <button
          @click="toggleSidebar"
          :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
          :class="[
          'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-primary-500',
          collapsed ? 'p-1 w-8 h-8 flex items-center justify-center' : 'p-2'
        ]"
          type="button"
      >
        <Icon
            :name="collapsed ? 'mdi:chevron-right' : 'mdi:chevron-left'"
            :style="{ fontSize: (collapsed ? 20 : ICON_SIZE) + 'px' }"
            aria-hidden="true"
        />
      </button>
    </div>

    <!-- Navigation -->
    <nav
        class="flex-1 px-2 py-3 overflow-y-auto"
        :class="collapsed ? 'block md:block' : 'block'"
        style="scrollbar-width: thin; scrollbar-color: #a0aec0 transparent;"
        role="navigation"
        aria-label="Primary Navigation"
    >
      <!-- COLLAPSED: show only icons for standalone + groups -->
      <template v-if="collapsed">
        <ul class="flex flex-col items-center space-y-6 mt-4" role="list">
          <!-- Standalone links icons first -->
          <li
              v-for="link in standaloneLinks"
              :key="'collapsed-standalone-' + link.to"
              class="cursor-pointer"
              role="listitem"
          >
            <NuxtLink
                :to="link.to"
                class="text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors block"
                :title="link.label"
                aria-label="link.label"
            >
              <Icon
                  :name="link.icon || defaultGroupIcon"
                  :style="{ fontSize: ICON_SIZE + 'px' }"
                  aria-hidden="true"
              />
            </NuxtLink>
          </li>

          <!-- Group icons -->
          <li
              v-for="group in navGroups"
              :key="'collapsed-group-' + group.name"
              class="cursor-pointer"
              role="listitem"
          >
            <Icon
                :name="group.icon || defaultGroupIcon"
                class="text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                :style="{ fontSize: ICON_SIZE + 'px' }"
                :title="group.name"
                @click="onCollapsedGroupIconClick(group.name)"
                role="button"
                tabindex="0"
                @keydown.enter.prevent="onCollapsedGroupIconClick(group.name)"
            />
          </li>
        </ul>
      </template>

      <!-- EXPANDED: standalone links first, then groups -->
      <template v-else>
        <!-- Standalone Links -->
        <ul v-if="standaloneLinks.length" class="space-y-1 mb-6" role="list">
          <li
              v-for="link in standaloneLinks"
              :key="'standalone-' + link.to"
              role="listitem"
          >
            <NuxtLink
                :to="link.to"
                class="nav-link flex items-center gap-3 px-4 py-2 rounded-lg transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                :class="{ 'active-link': isActiveLink(link.to) }"
                :aria-current="isActiveLink(link.to) ? 'page' : undefined"
            >
              <Icon
                  :name="link.icon || defaultGroupIcon"
                  :style="{ fontSize: ICON_SIZE + 'px' }"
                  aria-hidden="true"
              />
              <span class="text-md truncate">{{ link.label }}</span>
            </NuxtLink>
          </li>
        </ul>

        <!-- Groups -->
        <template v-for="group in navGroups" :key="'expanded-group-' + group.name">
          <div
              class="uppercase text-gray-400 text-xs font-bold px-3 mt-6 flex items-center cursor-pointer select-none hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
              @click="toggleGroup(group.name)"
              role="button"
              tabindex="0"
              @keydown.enter.prevent="toggleGroup(group.name)"
              :aria-expanded="openGroup === group.name"
              :aria-controls="'group-links-' + group.name.replace(/\s+/g, '-')"
          >
            <Icon
                :name="group.icon || defaultGroupIcon"
                class="mr-2 text-gray-400"
                :style="{ fontSize: ICON_SIZE + 'px' }"
                aria-hidden="true"
            />
            {{ group.name }}
            <Icon
                :name="openGroup === group.name ? 'mdi:chevron-down' : 'mdi:chevron-right'"
                class="ml-auto text-gray-400"
                :style="{ fontSize: ICON_SIZE * 0.75 + 'px' }"
                aria-hidden="true"
            />
          </div>

          <div
              v-show="openGroup === group.name"
              class="mt-1 mb-6 ml-4 rounded-md bg-gray-50 dark:bg-gray-800 shadow-sm"
              :id="'group-links-' + group.name.replace(/\s+/g, '-')"
              role="region"
              aria-label="Sub-navigation for group"
          >
            <ul class="space-y-1" role="list">
              <li
                  v-for="link in groupedLinksMap[group.name]"
                  :key="link.to"
                  role="listitem"
              >
                <NuxtLink
                    :to="link.to"
                    class="nav-link flex items-center gap-3 py-2 px-3 rounded-md transition-colors
                       text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700
                       hover:text-primary-600 dark:hover:text-primary-400"
                    :class="{ 'active-link': isActiveLink(link.to) }"
                    :aria-current="isActiveLink(link.to) ? 'page' : undefined"
                >
                  <Icon
                      :name="link.icon || group.icon || defaultGroupIcon"
                      :style="{ fontSize: ICON_SIZE + 'px' }"
                      aria-hidden="true"
                  />
                  <span class="flex justify-between items-center w-full gap-2">
                    <span class="text-md truncate">{{ link.label }}</span>
                    <span
                        v-if="link.hasBadge"
                        class="ml-auto text-xs bg-blue-500 text-white rounded px-2 py-0.5 cursor-pointer select-none"
                        @click.stop.prevent="handleBadgeAction(link.badge_action_name, link.to)"
                        :title="link.badge_action_name ? 'Click for action' : ''"
                        role="button"
                        tabindex="0"
                        @keydown.enter.prevent="handleBadgeAction(link.badge_action_name, link.to)"
                    >
                      {{ typeof link.badge === 'function' ? link.badge() : link.badge }}
                    </span>
                  </span>
                </NuxtLink>
              </li>
            </ul>
          </div>
        </template>
      </template>
    </nav>

    <!-- User Info -->
    <div
        v-if="user"
        :class="collapsed ? 'hidden' : 'flex'"
        class="border-t border-gray-200 dark:border-gray-800 px-4 py-4 items-center gap-3 transition-all"
    >
      <img
          :src="user.avatar"
          alt="Avatar"
          class="w-10 h-10 rounded-full object-cover select-none"
          draggable="false"
          loading="lazy"
      />
      <div class="flex flex-col min-w-0">
        <span class="font-medium truncate">{{ user.name }}</span>
        <button
            @click="logout"
            class="text-sm text-red-500 hover:text-red-700 flex items-center gap-1 select-none focus:outline-none focus:ring-2 focus:ring-red-500 rounded"
            type="button"
        >
          <Icon name="heroicons-outline:logout" class="w-4 h-4" aria-hidden="true" />
          Logout
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import gsap from 'gsap'
import { useSanctum } from '#imports'

const ICON_SIZE = 26 // px - consistent icon size everywhere

const route = useRoute()
const router = useRouter()
const { isLoggedIn, user, logout } = useSanctum()

const collapsed = ref(false)
const sidebarEl = ref<HTMLElement | null>(null)

// Track which group is open
const openGroup = ref<string | null>(null)

// LocalStorage helper with error handling
function safeLocalStorageGet(key: string): string | null {
  try {
    return localStorage.getItem(key)
  } catch {
    return null
  }
}

function safeLocalStorageSet(key: string, value: string) {
  try {
    localStorage.setItem(key, value)
  } catch {
    // silently fail or optionally log error
  }
}

function toggleSidebar() {
  collapsed.value = !collapsed.value
  safeLocalStorageSet('sidebar-collapsed', collapsed.value.toString())
}

function toggleGroup(groupName: string) {
  openGroup.value = openGroup.value === groupName ? null : groupName
}

async function onCollapsedGroupIconClick(groupName: string) {
  if (collapsed.value) {
    collapsed.value = false
    openGroup.value = groupName
    safeLocalStorageSet('sidebar-collapsed', 'false')

    // Navigate to first link inside that group if exists
    const firstLink = groupedLinksMap.value[groupName]?.[0]
    if (firstLink) {
      try {
        await router.push(firstLink.to)
      } catch (err) {
        console.error('Navigation error:', err)
      }
    }
  }
}

// Interfaces
interface NavGroup {
  name: string
  icon: string
  order?: number
}

interface NavLink {
  to: string
  label: string
  icon: string
  order?: number
  group?: string
  hasBadge?: boolean
  badge?: string | (() => string | number)
  badge_action_name?: string
}

// Groups with explicit order & icons
const navGroups: NavGroup[] = [
  { name: 'Orders', icon: 'material-symbols:folder', order: 1 },
  { name: 'Career', icon: 'mdi:briefcase-outline', order: 2 },
  { name: 'Community', icon: 'material-symbols:folder-supervised-rounded', order: 3 },
  { name: 'Support', icon: 'material-symbols:folder-match', order: 4 },
  { name: 'Settings', icon: 'material-symbols:folder-managed-sharp', order: 5 },
  { name: 'Setup', icon: 'mdi:settings-outline', order: 6 },
]

const defaultGroupIcon = 'material-symbols:folder'

// Base static links
const baseLinks: NavLink[] = [
  { to: '/dashboard', label: 'Dashboard', icon: 'mdi:view-dashboard', order: 1, group: '' },
  { to: '/dashboard/orders', label: 'Orders', icon: 'mdi:cart-outline', order: 2, group: 'Orders' },

  { to: '/dashboard/helpdesk', label: 'HelpDesk', icon: 'mdi:help-circle-outline', order: 100, group: 'Support' },
  { to: '/dashboard/faq', label: 'FAQ', icon: 'mdi:comment-question-outline', order: 101, group: 'Support' },
  { to: '/dashboard/settings', label: 'Settings', icon: 'mdi:cog-outline', order: 102, group: 'Settings' },

  { to: '/target-url', label: 'New Label', icon: 'mdi:cog-outline', order: 103, group: 'Settings', badge_action_name: 'clickToCallAction()' },
]

const navLinks = computed(() => {
  const links: NavLink[] = [...baseLinks]

  if (isLoggedIn.value && user.value) {
    const u = user.value

    if (u.type?.toLowerCase() === 'applicant') {
      links.push({
        to: '/dashboard/my-career',
        label: 'My Career',
        icon: 'mdi:briefcase-outline',
        order: 7,
        group: 'Career',
      })
    }

    if (u.type?.toLowerCase() !== 'applicant' && u.status?.toLowerCase() !== 'draft') {
      links.push({
        to: '/dashboard/members',
        label: 'Members',
        icon: 'mdi:account-multiple-outline',
        order: 8,
        group: 'Community',
        hasBadge: true,
        badge: () => 42,
      })
    }

    if (u.type?.toLowerCase() !== 'applicant') {
      if (u.status?.toLowerCase() !== 'subscribed') {
        links.push({
          to: '/dashboard/subscribe-now',
          label: 'Subscribe Now',
          icon: 'mdi:account-multiple-outline',
          order: 10,
          hasBadge: true,
          badge: () => 'New',
        })
      }
    }

    if (u.status?.toLowerCase() === 'draft') {
      links.push({
        to: '/dashboard/my-account/onboarding',
        label: 'OnBoarding',
        icon: 'mdi:document',
        order: 0,
        hasBadge: true,
        badge: 'Recommended',
      })
    }
  }
  return links
})

// Group links by group name, sorted by order
const groupedLinksMap = computed<Record<string, NavLink[]>>(() => {
  const map: Record<string, NavLink[]> = {}

  navGroups.forEach((group) => {
    map[group.name] = []
  })

  navLinks.value.forEach((link) => {
    if (link.group && map[link.group]) {
      map[link.group].push(link)
    }
  })

  Object.keys(map).forEach((grp) => {
    map[grp].sort((a, b) => (a.order ?? 999) - (b.order ?? 999))
  })

  return map
})

// Standalone links (no group or group = '')
const standaloneLinks = computed(() => {
  return navLinks.value.filter((link) => !link.group || link.group === '')
})

// Auto open group based on current route
function autoOpenGroupByRoute() {
  const currentPath = route.path.toLowerCase()
  for (const group of navGroups) {
    const links = groupedLinksMap.value[group.name]
    if (links && links.some((l) => l.to.toLowerCase() === currentPath)) {
      openGroup.value = group.name
      return
    }
  }
  openGroup.value = null
}

// Check active link
function isActiveLink(path: string) {
  return route.path === path
}

// Handle badge click
function handleBadgeAction(actionName: string | undefined, to: string) {
  if (actionName) {
    // Replace with your actual action handler if needed
    console.log(`Action triggered: ${actionName}`)
  } else {
    router.push(to).catch((err) => {
      if (err.name !== 'NavigationDuplicated') {
        console.error('Navigation error:', err)
      }
    })
  }
}

// Collapse sidebar if clicked outside
function handleClickOutside(event: MouseEvent) {
  if (sidebarEl.value && !sidebarEl.value.contains(event.target as Node)) {
    collapsed.value = true
    safeLocalStorageSet('sidebar-collapsed', 'true')
  }
}

onMounted(() => {
  const stored = safeLocalStorageGet('sidebar-collapsed')
  collapsed.value = stored === 'true'
  document.addEventListener('click', handleClickOutside)
  autoOpenGroupByRoute()
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

watch(collapsed, (val) => {
  if (sidebarEl.value) {
    gsap.to(sidebarEl.value, {
      width: val ? '5rem' : '16rem',
      duration: 0.15,
      ease: 'power3.inOut',
    })
  }
})

// Watch route changes to auto open the relevant group
watch(
    () => route.path,
    () => {
      autoOpenGroupByRoute()
    }
)
</script>

<style scoped>
.nav-link {
  @apply flex items-center gap-3 py-2 rounded-md transition-colors text-gray-700 dark:text-gray-300;
}
.nav-link:hover {
  @apply bg-gray-200 dark:bg-gray-700 text-blue-600 dark:text-blue-400;
}
.active-link {
  @apply bg-gray-200 dark:bg-gray-800 font-semibold;
}
::-webkit-scrollbar {
  width: 8px;
}
::-webkit-scrollbar-thumb {
  background-color: #a0aec0;
  border-radius: 10px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
</style>
