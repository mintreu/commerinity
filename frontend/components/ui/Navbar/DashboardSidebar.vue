<template>
  <div class="sidebar-wrapper">
    <!-- ✅ Enhanced Mobile Overlay with Blur -->
    <Transition name="overlay">
      <div
          v-if="isMobile && mobileOpen"
          class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[40] md:hidden"
          @click="closeMobileSidebar"
      ></div>
    </Transition>

    <!-- ✅ Enhanced Sidebar with Modern Design -->
    <aside
        ref="sidebarEl"
        :class="sidebarClasses"
        aria-label="Dashboard Navigation"
        role="navigation"
    >
      <!-- ✅ Modern Glassmorphism Background -->
      <div class="absolute inset-0 bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border-r border-gray-200/50 dark:border-gray-700/50">
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-b from-blue-50/30 via-transparent to-purple-50/30 dark:from-blue-950/30 dark:via-transparent dark:to-purple-950/30"></div>

        <!-- Animated Border -->
        <div class="absolute inset-y-0 right-0 w-px bg-gradient-to-b from-transparent via-blue-500/30 to-transparent"></div>
      </div>

      <!-- ✅ Sidebar Content -->
      <div class="relative z-10 h-full flex flex-col">

        <!-- ✅ Enhanced Mobile User Profile -->
        <div v-if="isMobile" class="p-6 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-white/80 to-gray-50/80 dark:from-gray-900/80 dark:to-gray-800/80 backdrop-blur-sm">
          <div class="flex flex-col items-center text-center">
            <!-- Avatar with Glow Effect -->
            <div class="relative mb-4 group">
              <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full blur-lg opacity-0 group-hover:opacity-30 transition-opacity duration-300"></div>
              <div class="relative">
                <AvatarUploader class="!w-16 !h-16 ring-4 ring-white/50 dark:ring-gray-800/50 shadow-xl" />
                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-3 border-white dark:border-gray-900 rounded-full animate-pulse shadow-lg"></div>
              </div>
            </div>

            <!-- User Info -->
            <div class="w-full">
              <h2 class="text-xl font-black mb-1 truncate bg-gradient-to-r from-gray-900 via-blue-800 to-purple-800 dark:from-white dark:via-blue-200 dark:to-purple-200 bg-clip-text text-transparent">
                {{ user?.name }}
              </h2>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 truncate">{{ user?.email }}</p>

              <!-- Status Badges -->
              <div class="flex flex-wrap items-center justify-center gap-2">
                <div class="px-3 py-1 bg-gradient-to-r from-blue-500 to-purple-500 text-white text-xs font-bold rounded-full shadow-md">
                  {{ user?.role || 'Member' }}
                </div>
                <div class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 text-xs font-semibold rounded-full shadow-sm">
                  Active
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ✅ Navigation Container -->
        <nav class="flex-1 p-3 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-transparent">

          <!-- ✅ Collapsed Navigation (Desktop) -->
          <template v-if="collapsed && !isMobile">
            <div class="flex flex-col items-center space-y-4 py-4">

              <!-- Dashboard Icon -->
              <div class="nav-item-collapsed">
                <NuxtLink
                    to="/dashboard"
                    :class="getCollapsedLinkClasses('/dashboard')"
                    class="group relative w-12 h-12 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 rounded-xl flex items-center justify-center transition-all duration-300 hover:shadow-xl hover:scale-110 hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-500 hover:text-white"
                    title="Dashboard"
                >
                  <Icon name="mdi:view-dashboard" class="w-6 h-6" />
                  <div v-if="isActiveLink('/dashboard')" class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-6 h-1 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full"></div>
                </NuxtLink>
              </div>

              <!-- Standalone Links -->
              <div
                  v-for="link in standaloneLinks"
                  :key="'collapsed-standalone-' + link.to"
                  class="nav-item-collapsed"
              >
                <NuxtLink
                    :to="link.to"
                    :class="getCollapsedLinkClasses(link.to || '')"
                    class="group relative w-12 h-12 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 rounded-xl flex items-center justify-center transition-all duration-300 hover:shadow-xl hover:scale-110 hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-500 hover:text-white"
                    :title="link.label"
                >
                  <Icon :name="link.icon || defaultGroupIcon" class="w-6 h-6" />
                  <div v-if="isActiveLink(link.to || '')" class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-6 h-1 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full"></div>
                  <div v-if="link.badge" class="absolute -top-1 -right-1 min-w-5 h-5 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold rounded-full flex items-center justify-center shadow-lg animate-bounce">
                    {{ typeof link.badge === 'function' ? link.badge() : link.badge }}
                  </div>
                </NuxtLink>
              </div>

              <!-- Group Icons -->
              <div
                  v-for="group in navGroups"
                  :key="'collapsed-group-' + group.name"
                  class="nav-item-collapsed"
              >
                <button
                    v-if="group.links.length"
                    @click="onCollapsedGroupIconClick(group.name)"
                    class="group relative w-12 h-12 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 rounded-xl flex items-center justify-center transition-all duration-300 hover:shadow-xl hover:scale-110 hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-500 hover:text-white"
                    :title="group.name"
                >
                  <Icon :name="group.icon || defaultGroupIcon" class="w-6 h-6" />
                  <div v-if="group.badge" class="absolute -top-1 -right-1 min-w-5 h-5 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold rounded-full flex items-center justify-center shadow-lg">
                    {{ group.badge }}
                  </div>
                  <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-gray-500/80 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <Icon name="mdi:chevron-right" class="w-3 h-3" />
                  </div>
                </button>
              </div>

              <!-- Support Icons -->
              <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-300 dark:via-gray-600 to-transparent my-4"></div>

              <div class="nav-item-collapsed">
                <NuxtLink
                    to="/dashboard/helpdesk"
                    :class="getCollapsedLinkClasses('/dashboard/helpdesk')"
                    class="group relative w-12 h-12 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 rounded-xl flex items-center justify-center transition-all duration-300 hover:shadow-xl hover:scale-110 hover:bg-gradient-to-r hover:from-teal-500 hover:to-cyan-500 hover:text-white"
                    title="HelpDesk"
                >
                  <Icon name="mdi:help-circle-outline" class="w-6 h-6" />
                  <div v-if="isActiveLink('/dashboard/helpdesk')" class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-6 h-1 bg-gradient-to-r from-teal-500 to-cyan-500 rounded-full"></div>
                </NuxtLink>
              </div>

              <div class="nav-item-collapsed">
                <NuxtLink
                    to="/dashboard/faq"
                    :class="getCollapsedLinkClasses('/dashboard/faq')"
                    class="group relative w-12 h-12 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 rounded-xl flex items-center justify-center transition-all duration-300 hover:shadow-xl hover:scale-110 hover:bg-gradient-to-r hover:from-orange-500 hover:to-red-500 hover:text-white"
                    title="FAQ"
                >
                  <Icon name="mdi:comment-question-outline" class="w-6 h-6" />
                  <div v-if="isActiveLink('/dashboard/faq')" class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-6 h-1 bg-gradient-to-r from-orange-500 to-red-500 rounded-full"></div>
                </NuxtLink>
              </div>
            </div>
          </template>

          <!-- ✅ Expanded Navigation -->
          <template v-else>
            <div class="space-y-6 py-2">

              <!-- Dashboard Link -->
              <div class="nav-section">
                <NuxtLink
                    to="/dashboard"
                    :class="getExpandedLinkClasses('/dashboard')"
                    class="group flex items-center gap-3 p-3 rounded-xl transition-all duration-300 hover:shadow-lg hover:scale-[1.02] bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50"
                >
                  <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <Icon name="mdi:view-dashboard" class="w-5 h-5 text-white" />
                  </div>
                  <span class="font-semibold text-gray-800 dark:text-gray-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">Dashboard</span>
                  <div v-if="isActiveLink('/dashboard')" class="ml-auto w-2 h-2 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full animate-pulse"></div>
                </NuxtLink>
              </div>

              <!-- Standalone Links -->
              <div v-if="standaloneLinks.length" class="nav-section space-y-2">
                <div
                    v-for="link in standaloneLinks"
                    :key="'standalone-' + link.to"
                    class="nav-item"
                >
                  <NuxtLink
                      :to="link.to"
                      :class="getExpandedLinkClasses(link.to || '')"
                      class="group flex items-center gap-3 p-3 rounded-xl transition-all duration-300 hover:shadow-lg hover:scale-[1.02] bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50"
                  >
                    <div class="w-10 h-10 bg-gradient-to-br from-gray-500 to-slate-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                      <Icon :name="link.icon || defaultGroupIcon" class="w-5 h-5 text-white" />
                    </div>
                    <span class="font-semibold text-gray-800 dark:text-gray-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">{{ link.label }}</span>
                    <div v-if="link.badge" class="ml-auto px-2 py-1 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold rounded-full shadow-md animate-pulse">
                      {{ typeof link.badge === 'function' ? link.badge() : link.badge }}
                    </div>
                    <div v-if="isActiveLink(link.to || '')" class="ml-auto w-2 h-2 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full animate-pulse"></div>
                  </NuxtLink>
                </div>
              </div>

              <!-- Dynamic Groups -->
              <div
                  v-for="group in navGroups"
                  :key="'expanded-group-' + group.name"
                  class="nav-section"
              >
                <div v-if="group.links.length" class="nav-group">
                  <!-- Group Header -->
                  <button
                      @click="toggleGroup(group.name)"
                      class="w-full flex items-center gap-3 p-3 mb-2 bg-gray-50/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl border border-gray-200/50 dark:border-gray-700/50 hover:bg-gray-100/80 dark:hover:bg-gray-700/80 hover:shadow-md transition-all duration-300"
                      :aria-expanded="openGroup === group.name"
                  >
                    <div class="w-8 h-8 bg-gradient-to-br from-gray-400 to-gray-600 rounded-lg flex items-center justify-center">
                      <Icon :name="group.icon || defaultGroupIcon" class="w-4 h-4 text-white" />
                    </div>
                    <span class="flex-1 text-left text-sm font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">{{ group.name }}</span>
                    <div v-if="group.badge" class="px-2 py-1 bg-gradient-to-r from-blue-500 to-purple-500 text-white text-xs font-bold rounded-full shadow-md">
                      {{ group.badge }}
                    </div>
                    <Icon
                        :name="openGroup === group.name ? 'mdi:chevron-down' : 'mdi:chevron-right'"
                        class="w-5 h-5 text-gray-400 transition-transform duration-300"
                        :class="{ 'rotate-90': openGroup === group.name }"
                    />
                  </button>

                  <!-- Group Links -->
                  <Transition name="group-expand">
                    <div v-if="openGroup === group.name" class="space-y-1 pl-4 border-l-2 border-gradient-to-b border-gray-200/50 dark:border-gray-700/50">
                      <div
                          v-for="link in group.links.sort((a,b) => (a.order || 0) - (b.order || 0))"
                          :key="link.to"
                          class="group-link-item"
                      >
                        <NuxtLink
                            :to="link.to"
                            :class="getGroupLinkClasses(link.to || '')"
                            class="group flex items-center gap-3 p-2.5 rounded-lg transition-all duration-300 hover:bg-white/80 dark:hover:bg-gray-700/80 hover:shadow-md hover:translate-x-1"
                        >
                          <div class="w-6 h-6 flex items-center justify-center">
                            <Icon :name="link.icon || group.icon || defaultGroupIcon" class="w-4 h-4 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300" />
                          </div>
                          <span class="flex-1 text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors duration-300">{{ link.label }}</span>
                          <div
                              v-if="link.badge"
                              @click.stop.prevent="handleBadgeAction(link.badge_action_name, link.to || '')"
                              class="px-2 py-1 bg-gradient-to-r from-blue-500 to-purple-500 text-white text-xs font-bold rounded-full shadow-md cursor-pointer hover:scale-110 transition-transform duration-200"
                              :title="link.badge_action_name ? 'Click for action' : ''"
                          >
                            {{ typeof link.badge === 'function' ? link.badge() : link.badge }}
                          </div>
                          <div v-if="isActiveLink(link.to || '')" class="w-2 h-2 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full animate-pulse"></div>
                        </NuxtLink>
                      </div>
                    </div>
                  </Transition>
                </div>
              </div>

              <!-- Support Section -->
              <div class="nav-section">
                <div class="flex items-center gap-3 mb-3 p-3">
                  <div class="w-8 h-8 bg-gradient-to-br from-teal-400 to-cyan-600 rounded-lg flex items-center justify-center">
                    <Icon name="mdi:help-circle" class="w-4 h-4 text-white" />
                  </div>
                  <span class="text-sm font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Support</span>
                </div>

                <div class="space-y-2">
                  <NuxtLink
                      to="/dashboard/helpdesk"
                      :class="getExpandedLinkClasses('/dashboard/helpdesk')"
                      class="group flex items-center gap-3 p-3 rounded-xl transition-all duration-300 hover:shadow-lg hover:scale-[1.02] bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50"
                  >
                    <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                      <Icon name="mdi:help-circle-outline" class="w-5 h-5 text-white" />
                    </div>
                    <span class="font-semibold text-gray-800 dark:text-gray-200 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors duration-300">HelpDesk</span>
                    <div v-if="isActiveLink('/dashboard/helpdesk')" class="ml-auto w-2 h-2 bg-gradient-to-r from-teal-500 to-cyan-500 rounded-full animate-pulse"></div>
                  </NuxtLink>

                  <NuxtLink
                      to="/dashboard/faq"
                      :class="getExpandedLinkClasses('/dashboard/faq')"
                      class="group flex items-center gap-3 p-3 rounded-xl transition-all duration-300 hover:shadow-lg hover:scale-[1.02] bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50"
                  >
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                      <Icon name="mdi:comment-question-outline" class="w-5 h-5 text-white" />
                    </div>
                    <span class="font-semibold text-gray-800 dark:text-gray-200 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors duration-300">FAQ</span>
                    <div v-if="isActiveLink('/dashboard/faq')" class="ml-auto w-2 h-2 bg-gradient-to-r from-orange-500 to-red-500 rounded-full animate-pulse"></div>
                  </NuxtLink>
                </div>
              </div>
            </div>
          </template>
        </nav>
      </div>
    </aside>

    <!-- ✅ Swipe Handle for Mobile (iOS style) -->
    <div
        v-if="isMobile && !mobileOpen"
        @touchstart="onSwipeHandleStart"
        @touchmove="onSwipeHandleMove"
        @touchend="onSwipeHandleEnd"
        class="fixed top-1/2 left-0 transform -translate-y-1/2 w-1 h-16 bg-gradient-to-b from-blue-500 to-purple-500 rounded-r-full z-30 opacity-70 hover:opacity-100 transition-opacity duration-200"
        style="touch-action: none;"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, toRef } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSanctum } from '#imports'
import AvatarUploader from '~/components/account/AvatarUploader.vue'
import { RouteBuilder, type NavLinkOptions } from '~/utils/RouteBuilder'

// GSAP imports (client-side only)
let gsap: any = null

if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// Props & Emits
const props = defineProps<{
  collapsed: boolean
  isMobile?: boolean
  mobileOpen?: boolean
}>()

const emit = defineEmits<{
  (e: 'close-mobile-sidebar'): void
  (e: 'open-mobile-sidebar'): void
}>()

// Reactive props
const collapsed = toRef(props, 'collapsed')
const isMobile = toRef(props, 'isMobile')
const mobileOpen = toRef(props, 'mobileOpen')

// Composables
const { user } = useSanctum()
const routeInstance = useRoute()
const router = useRouter()

// State
const sidebarEl = ref<HTMLElement | null>(null)
const openGroup = ref<string | null>(null)
const defaultGroupIcon = 'material-symbols:folder'

// ✅ Touch/Swipe handling
const startX = ref(0)
const currentX = ref(0)
const threshold = 60
const swipeStartX = ref(0)
const swipeCurrentX = ref(0)
const isSwipeHandle = ref(false)

let gsapContext: any = null

// RouteBuilder setup (preserved exactly as original)
const route = new RouteBuilder()
route
    .group('Account', 'mdi:account-circle-outline', g => {
      g.link('/dashboard/account', 'Profile').auth(true).order(1)
      g.link('/dashboard/account/edit', 'Edit Profile').auth(true).currentUrl('/dashboard/account').order(2)
      g.link('/dashboard/account/address', 'Manage Address').auth(true).currentUrl('/dashboard/account').order(3)
      g.link('/dashboard/account/kyc', 'Manage KYC').auth(true).currentUrl('/dashboard/account').order(4).status('draft').type('regular')
    })
    .group('Shop', 'mdi:cart-outline', g => {
      g.link('/category', 'Categories').order(1)
      g.link('/dashboard/orders', 'My Orders').order(2)
    })
    .group('Wallet', 'mdi:wallet-outline', g => {
      g.link('/dashboard/wallet', 'My Wallet').order(1)
    })
    .group('Career', 'mdi:briefcase-outline', g => {
      g.link('/career', 'Vacancy').order(1)
      g.link('/dashboard/career/applications', 'My Applications').order(2)
    })

// Dynamic links (preserved exactly as original)
const dynamicLinks = computed(() => {
  if (!user.value) return []
  const u = user.value
  const type = u.type?.toLowerCase()
  const links: NavLinkOptions[] = []

  if (type !== 'applicant') {
    links.push({
      to: '/dashboard/members',
      label: 'Members',
      order: 1,
      group: 'Community',
      icon: 'mdi:account-multiple-outline'
    })
  }

  if (!u.level_id || u.status?.toLowerCase() !== 'subscribed') {
    links.push({
      to: '/dashboard/subscribe',
      label: 'Subscribe Now',
      order: 10,
      group: '',
      icon: 'mdi:star-outline',
      badge: 'New'
    })
  }

  return links
})

// Computed navigation data (preserved exactly as original)
const allLinks = computed(() => [...route.getLinks(), ...dynamicLinks.value])

const filteredLinks = computed(() =>
    allLinks.value.filter(l => {
      if (l.currentUrl) {
        const urls = Array.isArray(l.currentUrl) ? l.currentUrl : [l.currentUrl]
        if (!urls.some(u => routeInstance.path.startsWith(u))) return false
      }
      if (l.auth && !user.value) return false
      return true
    })
)

const standaloneLinks = computed(() =>
    filteredLinks.value
        .filter(l => !l.group)
        .sort((a, b) => (a.order || 0) - (b.order || 0))
)

const navGroups = computed(() => {
  const map: Record<string, typeof filteredLinks.value> = {}
  filteredLinks.value.forEach(l => {
    if (l.group) {
      if (!map[l.group]) map[l.group] = []
      map[l.group].push(l)
    }
  })
  return Object.keys(map).map(k => ({
    name: k,
    icon: map[k][0].icon || defaultGroupIcon,
    badge: map[k][0].groupBadge,
    links: map[k].sort((a, b) => (a.order || 0) - (b.order || 0))
  }))
})

// ✅ Enhanced Computed Styles
const sidebarClasses = computed(() => [
  'fixed md:relative transition-all duration-300 ease-out overflow-hidden z-50 md:z-auto',
  {
    // Mobile classes
    'top-0 left-0 h-full shadow-2xl': isMobile.value,
    'w-80 max-w-[85vw]': isMobile.value,
    'transform translate-x-0': isMobile.value && mobileOpen.value,
    'transform -translate-x-full': isMobile.value && !mobileOpen.value,

    // Desktop classes
    'h-full': !isMobile.value,
    'w-20': !isMobile.value && collapsed.value,
    'w-64': !isMobile.value && !collapsed.value,
  }
])

// Methods (preserved exactly as original)
function toggleGroup(name: string) {
  openGroup.value = openGroup.value === name ? null : name
}

function onCollapsedGroupIconClick(name: string) {
  if (collapsed.value) {
    emit('expand-sidebar') // You may need to handle this in parent
    setTimeout(() => {
      openGroup.value = name
    }, 300)

    const group = navGroups.value.find(g => g.name === name)
    if (group && group.links.length > 0) {
      router.push(group.links[0].to || '/')
    }
  } else {
    toggleGroup(name)
  }
}

function isActiveLink(path: string) {
  return routeInstance.path === path || routeInstance.path.startsWith(path + '/')
}

function handleBadgeAction(actionName: string | undefined, to: string) {
  if (!actionName) {
    router.push(to)
  }
}

function closeMobileSidebar() {
  emit('close-mobile-sidebar')
}

function openMobileSidebar() {
  emit('open-mobile-sidebar')
}

// Style helper methods (preserved exactly as original)
function getCollapsedLinkClasses(path: string) {
  return {
    'text-blue-600 dark:text-blue-400 shadow-lg bg-gradient-to-r from-blue-500 to-purple-500 !text-white': isActiveLink(path),
    'text-gray-600 dark:text-gray-400': !isActiveLink(path)
  }
}

function getExpandedLinkClasses(path: string) {
  return {
    'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/30 dark:to-purple-900/30 border-blue-200 dark:border-blue-700 shadow-lg': isActiveLink(path),
    'hover:bg-gray-50 dark:hover:bg-gray-700/50': !isActiveLink(path)
  }
}

function getGroupLinkClasses(path: string) {
  return {
    'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 border-l-2 border-blue-500': isActiveLink(path),
  }
}

// ✅ Enhanced Touch/Swipe handlers
function onTouchStart(e: TouchEvent) {
  if (!isMobile.value) return
  startX.value = e.touches[0].clientX
}

function onTouchMove(e: TouchEvent) {
  if (!isMobile.value) return
  currentX.value = e.touches[0].clientX
}

function onTouchEnd() {
  if (!isMobile.value) return
  if (currentX.value - startX.value < -threshold && mobileOpen.value) {
    closeMobileSidebar()
  }
}

// ✅ NEW: Swipe handle handlers
function onSwipeHandleStart(e: TouchEvent) {
  if (!isMobile.value) return
  isSwipeHandle.value = true
  swipeStartX.value = e.touches[0].clientX
}

function onSwipeHandleMove(e: TouchEvent) {
  if (!isMobile.value || !isSwipeHandle.value) return
  swipeCurrentX.value = e.touches[0].clientX
}

function onSwipeHandleEnd() {
  if (!isMobile.value || !isSwipeHandle.value) return

  if (swipeCurrentX.value - swipeStartX.value > threshold) {
    openMobileSidebar()
  }

  isSwipeHandle.value = false
  swipeStartX.value = 0
  swipeCurrentX.value = 0
}

// ✅ Enhanced Animations
function initializeAnimations() {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
    // Sidebar entrance animation
    if (sidebarEl.value && !isMobile.value) {
      gsap.fromTo(sidebarEl.value,
          { x: -50, opacity: 0 },
          {
            x: 0,
            opacity: 1,
            duration: 0.8,
            ease: 'back.out(1.7)'
          }
      )
    }

    // Navigation items stagger animation
    const navItems = sidebarEl.value?.querySelectorAll('.nav-item, .nav-item-collapsed, .nav-section')
    if (navItems) {
      gsap.fromTo(navItems,
          { opacity: 0, y: 20 },
          {
            opacity: 1,
            y: 0,
            duration: 0.6,
            stagger: 0.1,
            ease: 'back.out(1.7)',
            delay: 0.3
          }
      )
    }
  })
}

// Lifecycle
onMounted(() => {
  if (sidebarEl.value && isMobile.value) {
    sidebarEl.value.addEventListener('touchstart', onTouchStart, { passive: true })
    sidebarEl.value.addEventListener('touchmove', onTouchMove, { passive: true })
    sidebarEl.value.addEventListener('touchend', onTouchEnd)
  }

  setTimeout(() => {
    initializeAnimations()
  }, 100)
})

onUnmounted(() => {
  if (sidebarEl.value && isMobile.value) {
    sidebarEl.value.removeEventListener('touchstart', onTouchStart)
    sidebarEl.value.removeEventListener('touchmove', onTouchMove)
    sidebarEl.value.removeEventListener('touchend', onTouchEnd)
  }

  if (gsapContext) {
    gsapContext.kill()
  }
})

// Watch for sidebar collapse changes
watch(collapsed, (val) => {
  if (sidebarEl.value && !isMobile.value && process.client && gsap) {
    gsap.to(sidebarEl.value, {
      width: val ? '5rem' : '16rem',
      duration: 0.3,
      ease: 'power3.inOut'
    })
  }
}, { immediate: false })
</script>

<style scoped>
/* ✅ Modern Scrollbar */
.scrollbar-thin {
  scrollbar-width: thin;
}

.scrollbar-thumb-gray-300 {
  scrollbar-color: rgb(209 213 219) transparent;
}

.dark .scrollbar-thumb-gray-600 {
  scrollbar-color: rgb(75 85 99) transparent;
}

.scrollbar-track-transparent {
  scrollbar-color: transparent transparent;
}

::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  @apply bg-gray-300 dark:bg-gray-600 rounded-full;
}

::-webkit-scrollbar-thumb:hover {
  @apply bg-gray-400 dark:bg-gray-500;
}

/* ✅ Enhanced Transitions */
.overlay-enter-active,
.overlay-leave-active {
  transition: opacity 0.3s ease;
}

.overlay-enter-from,
.overlay-leave-to {
  opacity: 0;
}

.group-expand-enter-active {
  transition: all 0.3s ease-out;
  overflow: hidden;
}

.group-expand-leave-active {
  transition: all 0.3s ease-in;
  overflow: hidden;
}

.group-expand-enter-from {
  opacity: 0;
  max-height: 0;
  transform: translateY(-10px);
}

.group-expand-leave-to {
  opacity: 0;
  max-height: 0;
  transform: translateY(-10px);
}

.group-expand-enter-to {
  opacity: 1;
  max-height: 500px;
  transform: translateY(0);
}

.group-expand-leave-from {
  opacity: 1;
  max-height: 500px;
  transform: translateY(0);
}

/* ✅ Enhanced Animations */
@keyframes slideInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-4px);
  }
}

/* ✅ Focus States for Accessibility */
.nav-item-collapsed > *:focus-visible,
.nav-item > *:focus-visible,
button:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* ✅ Mobile Optimizations */
@media (max-width: 768px) {
  .nav-section {
    margin-bottom: 1rem;
  }
}

/* ✅ Reduced Motion Support */
@media (prefers-reduced-motion: reduce) {
  *,
  ::before,
  ::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* ✅ High Contrast Mode */
@media (prefers-contrast: high) {
  .bg-gradient-to-r {
    background: currentColor !important;
  }
}

/* ✅ Custom Properties for Theme */
:root {
  --sidebar-bg: rgba(255, 255, 255, 0.9);
  --sidebar-border: rgba(229, 231, 235, 0.5);
  --nav-item-bg: rgba(255, 255, 255, 0.6);
  --nav-item-hover: rgba(243, 244, 246, 0.8);
}

@media (prefers-color-scheme: dark) {
  :root {
    --sidebar-bg: rgba(17, 24, 39, 0.9);
    --sidebar-border: rgba(75, 85, 99, 0.5);
    --nav-item-bg: rgba(31, 41, 55, 0.6);
    --nav-item-hover: rgba(55, 65, 81, 0.8);
  }
}
</style>
