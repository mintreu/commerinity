<template>
  <div>
    <!-- Mobile Overlay -->
    <transition name="fade">
      <div
          v-if="isMobile && mobileOpen"
          class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[40] md:hidden"
          @click="closeMobileSidebar"
      />
    </transition>

    <!-- Sidebar -->
    <aside
        ref="sidebar"
        :class="sidebarClasses"
        aria-label="Dashboard Navigation"
    >
      <!-- Background -->
      <div class="absolute inset-0 bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border-r border-gray-200/50 dark:border-gray-700/50">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-50/30 via-transparent to-purple-50/30 dark:from-blue-950/30 dark:via-transparent dark:to-purple-950/30" />
      </div>

      <!-- Content -->
      <div class="relative z-10 h-full flex flex-col">

        <!-- Mobile User Profile -->
        <div v-if="isMobile" class="p-6 border-b border-gray-200/50 dark:border-gray-700/50">
          <div class="flex flex-col items-center text-center">
            <div class="relative mb-4 group">
              <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full blur-lg opacity-0 group-hover:opacity-30 transition-opacity" />
              <div class="relative">
                <AvatarUploader class="!w-16 !h-16 ring-4 ring-white/50 dark:ring-gray-800/50 shadow-xl" />
                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-3 border-white dark:border-gray-900 rounded-full animate-pulse" />
              </div>
            </div>

            <div class="w-full">
              <h2 class="text-xl font-black mb-1 truncate bg-gradient-to-r from-gray-900 via-blue-800 to-purple-800 dark:from-white dark:via-blue-200 dark:to-purple-200 bg-clip-text text-transparent">
                {{ user?.name }}
              </h2>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 truncate">{{ user?.email }}</p>

              <div class="flex flex-wrap items-center justify-center gap-2">
                <div class="px-3 py-1 bg-gradient-to-r from-blue-500 to-purple-500 text-white text-xs font-bold rounded-full">
                  {{ user?.role || 'Member' }}
                </div>
                <div class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 text-xs font-semibold rounded-full">
                  Active
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-3 overflow-y-auto custom-scrollbar">

          <!-- Collapsed View -->
          <template v-if="collapsed && !isMobile">
            <div class="flex flex-col items-center space-y-4 py-4" ref="collapsedNav">

              <!-- Dashboard -->
              <NuxtLink
                  to="/dashboard"
                  :class="getLinkClasses('/dashboard')"
                  class="nav-icon-btn"
                  title="Dashboard"
              >
                <Icon name="mdi:view-dashboard" class="w-6 h-6" />
                <div v-if="isActive('/dashboard')" class="active-indicator" />
              </NuxtLink>

              <!-- Standalone Links -->
              <NuxtLink
                  v-for="link in standaloneLinks"
                  :key="link.to"
                  :to="link.to"
                  :class="getLinkClasses(link.to || '')"
                  class="nav-icon-btn"
                  :title="link.label"
              >
                <Icon :name="link.icon || defaultIcon" class="w-6 h-6" />
                <div v-if="isActive(link.to || '')" class="active-indicator" />
                <div v-if="link.badge" class="badge">
                  {{ typeof link.badge === 'function' ? link.badge() : link.badge }}
                </div>
              </NuxtLink>

              <!-- Group Icons -->
              <button
                  v-for="group in navGroups"
                  :key="group.name"
                  @click="onGroupClick(group.name)"
                  class="nav-icon-btn"
                  :title="group.name"
              >
                <Icon :name="group.icon || defaultIcon" class="w-6 h-6" />
                <div v-if="group.badge" class="badge">{{ group.badge }}</div>
              </button>

              <!-- Divider -->
              <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-300 dark:via-gray-600 to-transparent my-4" />

              <!-- Support Links -->
              <NuxtLink to="/dashboard/helpdesk" :class="getLinkClasses('/dashboard/helpdesk')" class="nav-icon-btn" title="HelpDesk">
                <Icon name="mdi:help-circle-outline" class="w-6 h-6" />
                <div v-if="isActive('/dashboard/helpdesk')" class="active-indicator" />
              </NuxtLink>

              <NuxtLink to="/dashboard/faq" :class="getLinkClasses('/dashboard/faq')" class="nav-icon-btn" title="FAQ">
                <Icon name="mdi:comment-question-outline" class="w-6 h-6" />
                <div v-if="isActive('/dashboard/faq')" class="active-indicator" />
              </NuxtLink>
            </div>
          </template>

          <!-- Expanded View -->
          <template v-else>
            <div class="space-y-6 py-2" ref="expandedNav">

              <!-- Dashboard -->
              <NuxtLink to="/dashboard" :class="getExpandedClasses('/dashboard')" class="nav-link">
                <div class="nav-link-icon">
                  <Icon name="mdi:view-dashboard" class="w-5 h-5 text-white" />
                </div>
                <span class="font-semibold">Dashboard</span>
                <div v-if="isActive('/dashboard')" class="ml-auto w-2 h-2 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full animate-pulse" />
              </NuxtLink>

              <!-- Standalone Links -->
              <div v-if="standaloneLinks.length" class="space-y-2">
                <NuxtLink
                    v-for="link in standaloneLinks"
                    :key="link.to"
                    :to="link.to"
                    :class="getExpandedClasses(link.to || '')"
                    class="nav-link"
                >
                  <div class="nav-link-icon-gray">
                    <Icon :name="link.icon || defaultIcon" class="w-5 h-5 text-white" />
                  </div>
                  <span class="font-semibold">{{ link.label }}</span>
                  <div v-if="link.badge" class="ml-auto px-2 py-1 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold rounded-full">
                    {{ typeof link.badge === 'function' ? link.badge() : link.badge }}
                  </div>
                  <div v-if="isActive(link.to || '')" class="ml-auto w-2 h-2 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full animate-pulse" />
                </NuxtLink>
              </div>

              <!-- Groups -->
              <div v-for="group in navGroups" :key="group.name">
                <button
                    @click="toggleGroup(group.name)"
                    class="w-full flex items-center gap-3 p-3 mb-2 bg-gray-50/80 dark:bg-gray-800/80 rounded-xl border border-gray-200/50 dark:border-gray-700/50 hover:bg-gray-100/80 dark:hover:bg-gray-700/80 transition-all"
                >
                  <div class="w-8 h-8 bg-gradient-to-br from-gray-400 to-gray-600 rounded-lg flex items-center justify-center">
                    <Icon :name="group.icon || defaultIcon" class="w-4 h-4 text-white" />
                  </div>
                  <span class="flex-1 text-left text-sm font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">{{ group.name }}</span>
                  <Icon :name="openGroup === group.name ? 'mdi:chevron-down' : 'mdi:chevron-right'" class="w-5 h-5 text-gray-400 transition-transform" />
                </button>

                <transition name="expand">
                  <div v-if="openGroup === group.name" class="space-y-1 pl-4 border-l-2 border-gray-200/50 dark:border-gray-700/50">
                    <NuxtLink
                        v-for="link in group.links"
                        :key="link.to"
                        :to="link.to"
                        :class="getGroupClasses(link.to || '')"
                        class="group flex items-center gap-3 p-2.5 rounded-lg transition-all hover:bg-white/80 dark:hover:bg-gray-700/80 hover:translate-x-1"
                    >
                      <Icon :name="link.icon || group.icon || defaultIcon" class="w-4 h-4 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" />
                      <span class="flex-1 text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white">{{ link.label }}</span>
                      <div v-if="isActive(link.to || '')" class="w-2 h-2 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full animate-pulse" />
                    </NuxtLink>
                  </div>
                </transition>
              </div>

              <!-- Support Section -->
              <div>
                <div class="flex items-center gap-3 mb-3 p-3">
                  <div class="w-8 h-8 bg-gradient-to-br from-teal-400 to-cyan-600 rounded-lg flex items-center justify-center">
                    <Icon name="mdi:help-circle" class="w-4 h-4 text-white" />
                  </div>
                  <span class="text-sm font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Support</span>
                </div>

                <div class="space-y-2">
                  <NuxtLink to="/dashboard/helpdesk" :class="getExpandedClasses('/dashboard/helpdesk')" class="nav-link">
                    <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                      <Icon name="mdi:help-circle-outline" class="w-5 h-5 text-white" />
                    </div>
                    <span class="font-semibold">HelpDesk</span>
                    <div v-if="isActive('/dashboard/helpdesk')" class="ml-auto w-2 h-2 bg-gradient-to-r from-teal-500 to-cyan-500 rounded-full animate-pulse" />
                  </NuxtLink>

                  <NuxtLink to="/dashboard/faq" :class="getExpandedClasses('/dashboard/faq')" class="nav-link">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                      <Icon name="mdi:comment-question-outline" class="w-5 h-5 text-white" />
                    </div>
                    <span class="font-semibold">FAQ</span>
                    <div v-if="isActive('/dashboard/faq')" class="ml-auto w-2 h-2 bg-gradient-to-r from-orange-500 to-red-500 rounded-full animate-pulse" />
                  </NuxtLink>
                </div>
              </div>
            </div>
          </template>
        </nav>
      </div>
    </aside>

    <!-- Swipe Handle (Mobile) -->
    <div
        v-if="isMobile && !mobileOpen"
        @touchstart="onSwipeStart"
        @touchmove="onSwipeMove"
        @touchend="onSwipeEnd"
        class="fixed top-1/2 left-0 -translate-y-1/2 w-1 h-16 bg-gradient-to-b from-blue-500 to-purple-500 rounded-r-full z-30 opacity-70"
        style="touch-action: none"
    />
  </div>
</template>


<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, toRef } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSanctum } from '#imports'
import AvatarUploader from '~/components/account/AvatarUploader.vue'
import { RouteBuilder, type NavLinkOptions } from '~/utils/RouteBuilder'

// ✅ Optimized GSAP
let gsap: any = null
let gsapCtx: any = null

// Props
const props = defineProps<{
  collapsed: boolean
  isMobile?: boolean
  mobileOpen?: boolean
}>()

const emit = defineEmits<{
  (e: 'close-mobile-sidebar'): void
  (e: 'open-mobile-sidebar'): void
}>()

// Refs
const collapsed = toRef(props, 'collapsed')
const isMobile = toRef(props, 'isMobile')
const mobileOpen = toRef(props, 'mobileOpen')

// Composables
const { user } = useSanctum()
const routeInstance = useRoute()
const router = useRouter()

// State
const sidebar = ref<HTMLElement>()
const collapsedNav = ref<HTMLElement>()
const expandedNav = ref<HTMLElement>()
const openGroup = ref<string | null>(null)
const defaultIcon = 'material-symbols:folder'

// Touch
const touchStartX = ref(0)
const touchCurrentX = ref(0)
const threshold = 60

// ✅ Route Builder (preserved)
const route = new RouteBuilder()
route
    .group('Account', 'mdi:account-circle-outline', g => {
      g.link('/dashboard/account', 'Profile').auth(true).order(1)
      g.link('/dashboard/account/edit', 'Edit Profile').auth(true).currentUrl('/dashboard/account').order(2)
      g.link('/dashboard/account/address', 'Manage Address').auth(true).currentUrl('/dashboard/account').order(3)
      g.link('/dashboard/account/kyc', 'Manage KYC').auth(true).currentUrl('/dashboard/account').order(4)
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

// ✅ Dynamic Links (preserved)
const dynamicLinks = computed(() => {
  if (!user.value) return []
  const u = user.value
  const links: NavLinkOptions[] = []

  if (u.type?.toLowerCase() !== 'applicant') {
    links.push({ to: '/dashboard/members', label: 'Members', order: 1, group: 'Community', icon: 'mdi:account-multiple-outline' })
  }

  if (!u.level_id || u.status?.toLowerCase() !== 'subscribed') {
    links.push({ to: '/dashboard/subscribe', label: 'Subscribe Now', order: 10, group: '', icon: 'mdi:star-outline', badge: 'New' })
  }

  return links
})

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
    filteredLinks.value.filter(l => !l.group).sort((a, b) => (a.order || 0) - (b.order || 0))
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
    icon: map[k][0].icon || defaultIcon,
    badge: map[k][0].groupBadge,
    links: map[k].sort((a, b) => (a.order || 0) - (b.order || 0))
  }))
})

const sidebarClasses = computed(() => [
  'fixed md:relative transition-all duration-300 overflow-hidden z-50 md:z-auto',
  {
    'top-0 left-0 h-full shadow-2xl': isMobile.value,
    'w-80 max-w-[85vw]': isMobile.value,
    'translate-x-0': isMobile.value && mobileOpen.value,
    '-translate-x-full': isMobile.value && !mobileOpen.value,
    'h-full': !isMobile.value,
    'w-20': !isMobile.value && collapsed.value,
    'w-64': !isMobile.value && !collapsed.value,
  }
])

// Methods
function toggleGroup(name: string) {
  openGroup.value = openGroup.value === name ? null : name
}

function onGroupClick(name: string) {
  const group = navGroups.value.find(g => g.name === name)
  if (group?.links.length) {
    router.push(group.links[0].to || '/')
  }
}

function isActive(path: string) {
  return routeInstance.path === path || routeInstance.path.startsWith(path + '/')
}

function closeMobileSidebar() {
  emit('close-mobile-sidebar')
}

function openMobileSidebar() {
  emit('open-mobile-sidebar')
}

function getLinkClasses(path: string) {
  return {
    'text-blue-600 dark:text-blue-400 bg-gradient-to-r from-blue-500 to-purple-500 !text-white': isActive(path),
    'text-gray-600 dark:text-gray-400': !isActive(path)
  }
}

function getExpandedClasses(path: string) {
  return {
    'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/30 dark:to-purple-900/30 border-blue-200 dark:border-blue-700 shadow-lg': isActive(path),
  }
}

function getGroupClasses(path: string) {
  return {
    'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 border-l-2 border-blue-500': isActive(path),
  }
}

// Touch
function onSwipeStart(e: TouchEvent) {
  touchStartX.value = e.touches[0].clientX
}

function onSwipeMove(e: TouchEvent) {
  touchCurrentX.value = e.touches[0].clientX
}

function onSwipeEnd() {
  if (touchCurrentX.value - touchStartX.value > threshold) {
    openMobileSidebar()
  }
}

// ✅ OPTIMIZED GSAP
async function initGSAP() {
  if (!process.client || gsap) return

  try {
    const gsapModule = await import('gsap')
    gsap = gsapModule.default

    gsapCtx = gsap.context(() => {
      // Sidebar entrance (desktop only)
      if (sidebar.value && !isMobile.value) {
        gsap.fromTo(sidebar.value,
            { x: -40, opacity: 0 },
            { x: 0, opacity: 1, duration: 0.6, ease: 'power2.out', force3D: true }
        )
      }

      // Nav items stagger
      const navEl = collapsed.value ? collapsedNav.value : expandedNav.value
      const items = navEl?.querySelectorAll('a, button')
      if (items?.length) {
        gsap.fromTo(Array.from(items),
            { opacity: 0, y: 15 },
            { opacity: 1, y: 0, duration: 0.4, stagger: 0.05, ease: 'power2.out', force3D: true }
        )
      }
    })
  } catch (e) {
    console.warn('GSAP load failed')
  }
}

// Lifecycle
onMounted(() => {
  requestAnimationFrame(() => {
    initGSAP()
  })
})

onUnmounted(() => {
  if (gsapCtx) gsapCtx.kill()
})

// Watch collapsed state
watch(collapsed, () => {
  if (gsapCtx) {
    gsapCtx.revert()
    requestAnimationFrame(() => initGSAP())
  }
})
</script>

<style scoped>
/* Scrollbar */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(156,163,175,0.3) transparent;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(156,163,175,0.3);
  border-radius: 4px;
}

/* Transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.expand-enter-active {
  transition: all 0.3s ease-out;
  overflow: hidden;
}

.expand-leave-active {
  transition: all 0.2s ease-in;
  overflow: hidden;
}

.expand-enter-from, .expand-leave-to {
  opacity: 0;
  max-height: 0;
}

.expand-enter-to {
  opacity: 1;
  max-height: 500px;
}

/* Nav Icon Button */
.nav-icon-btn {
  position: relative;
  width: 3rem;
  height: 3rem;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(229, 231, 235, 0.5);
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
}

.nav-icon-btn:hover {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  transform: scale(1.1);
  background: linear-gradient(to right, rgb(59 130 246), rgb(168 85 247));
  color: white;
}

.dark .nav-icon-btn {
  background: rgba(31, 41, 55, 0.8);
  border-color: rgba(75, 85, 99, 0.5);
}

.active-indicator {
  position: absolute;
  bottom: -0.25rem;
  left: 50%;
  transform: translateX(-50%);
  width: 1.5rem;
  height: 0.25rem;
  background: linear-gradient(to right, rgb(59 130 246), rgb(168 85 247));
  border-radius: 9999px;
}

.badge {
  position: absolute;
  top: -0.25rem;
  right: -0.25rem;
  min-width: 1.25rem;
  height: 1.25rem;
  background: linear-gradient(to right, rgb(239 68 68), rgb(236 72 153));
  color: white;
  font-size: 0.75rem;
  font-weight: 700;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

/* Nav Link - Fixed without 'group' in @apply */
.nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border-radius: 0.75rem;
  transition: all 0.3s;
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(229, 231, 235, 0.5);
}

.nav-link:hover {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  transform: scale(1.02);
}

.dark .nav-link {
  background: rgba(31, 41, 55, 0.6);
  border-color: rgba(75, 85, 99, 0.5);
}

.nav-link-icon {
  width: 2.5rem;
  height: 2.5rem;
  background: linear-gradient(to bottom right, rgb(59 130 246), rgb(79 70 229));
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s;
}

.nav-link:hover .nav-link-icon {
  transform: scale(1.1);
}

.nav-link-icon-gray {
  width: 2.5rem;
  height: 2.5rem;
  background: linear-gradient(to bottom right, rgb(107 114 128), rgb(71 85 105));
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s;
}

.nav-link:hover .nav-link-icon-gray {
  transform: scale(1.1);
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
  * {
    transition-duration: 0.01ms !important;
  }
}
</style>


