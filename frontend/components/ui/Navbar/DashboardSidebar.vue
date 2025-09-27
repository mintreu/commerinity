<template>
  <div class="sidebar-wrapper">
    <!-- Enhanced Mobile Overlay -->
    <transition name="overlay">
      <div
          v-if="isMobile && mobileOpen"
          class="mobile-overlay fixed inset-0 bg-black/60 backdrop-blur-sm z-[40] md:hidden"
          @click="closeMobileSidebar"
      ></div>
    </transition>

    <!-- Enhanced Sidebar -->
    <aside
        ref="sidebarEl"
        :class="sidebarClasses"
        aria-label="Dashboard Navigation"
        role="navigation"
    >
      <!-- Glassmorphism Background -->
      <div class="sidebar-background absolute inset-0 backdrop-blur-xl">
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-b from-white/95 via-white/98 to-white/95 dark:from-gray-900/95 dark:via-gray-900/98 dark:to-gray-900/95"></div>

        <!-- Glow Effect -->
        <div class="absolute inset-0 bg-gradient-to-b from-blue-500/5 to-purple-500/5 dark:from-blue-400/10 dark:to-purple-400/10"></div>

        <!-- Border -->
        <div class="absolute inset-y-0 right-0 w-px bg-gradient-to-b from-transparent via-gray-200/50 to-transparent dark:via-gray-700/50"></div>
      </div>

      <!-- Sidebar Content -->
      <div class="sidebar-content relative z-10 h-full flex flex-col">

        <!-- Enhanced Mobile User Profile -->
        <div v-if="isMobile" class="mobile-profile-section p-6 border-b border-gray-200/50 dark:border-gray-700/50 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm">
          <div class="flex flex-col items-center text-center">
            <div class="profile-avatar-container relative mb-4">
              <!-- Avatar Glow Effect -->
              <div class="avatar-glow absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full blur-lg opacity-0 hover:opacity-40 transition-opacity duration-300"></div>

              <div class="avatar-wrapper relative">
                <AvatarUploader class="!w-16 !h-16" />
                <div class="status-indicator absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-3 border-white dark:border-gray-900 rounded-full animate-pulse"></div>
              </div>
            </div>

            <div class="profile-info">
              <h2 class="profile-name text-xl font-black text-gray-900 dark:text-white mb-1 truncate max-w-full">{{ user?.name }}</h2>
              <p class="profile-email text-sm text-gray-500 dark:text-gray-400 mb-3 truncate max-w-full">{{ user?.email }}</p>
              <div class="profile-badges flex flex-wrap items-center justify-center gap-2">
                <div class="badge badge-role px-3 py-1 bg-gradient-to-r from-blue-500 to-purple-500 text-white text-xs font-bold rounded-full">
                  {{ user?.role || 'Member' }}
                </div>
                <div class="badge badge-status px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 text-xs font-semibold rounded-full">
                  Active
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation Container -->
        <nav
            class="navigation-container flex-1 p-3 overflow-y-auto custom-scrollbar"
            role="navigation"
            aria-label="Primary Navigation"
        >
          <!-- Collapsed Sidebar Navigation -->
          <template v-if="collapsed && !isMobile">
            <div class="collapsed-nav flex flex-col items-center space-y-4 py-4">

              <!-- Dashboard Icon -->
              <div class="nav-item-collapsed">
                <NuxtLink
                    to="/dashboard"
                    class="nav-icon-link"
                    :class="getCollapsedLinkClasses('/dashboard')"
                    title="Dashboard"
                    aria-label="Dashboard"
                >
                  <Icon name="mdi:view-dashboard" class="nav-icon" />
                  <div v-if="isActiveLink('/dashboard')" class="active-indicator"></div>
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
                    class="nav-icon-link"
                    :class="getCollapsedLinkClasses(link.to || '')"
                    :title="link.label"
                    :aria-label="link.label"
                >
                  <Icon :name="link.icon || defaultGroupIcon" class="nav-icon" />
                  <div v-if="isActiveLink(link.to || '')" class="active-indicator"></div>
                  <div v-if="link.badge" class="icon-badge">{{ typeof link.badge === 'function' ? link.badge() : link.badge }}</div>
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
                    class="nav-icon-button group-icon-button"
                    @click="onCollapsedGroupIconClick(group.name)"
                    @keydown.enter.prevent="onCollapsedGroupIconClick(group.name)"
                    :title="group.name"
                    :aria-label="`Open group ${group.name}`"
                    type="button"
                >
                  <Icon :name="group.icon || defaultGroupIcon" class="nav-icon" />
                  <div v-if="group.badge" class="icon-badge">{{ group.badge }}</div>
                  <div class="expand-indicator">
                    <Icon name="mdi:chevron-right" class="w-3 h-3" />
                  </div>
                </button>
              </div>

              <!-- Help & FAQ Icons -->
              <div class="nav-item-collapsed">
                <NuxtLink
                    to="/dashboard/helpdesk"
                    class="nav-icon-link"
                    :class="getCollapsedLinkClasses('/dashboard/helpdesk')"
                    title="HelpDesk"
                    aria-label="HelpDesk"
                >
                  <Icon name="mdi:help-circle-outline" class="nav-icon" />
                  <div v-if="isActiveLink('/dashboard/helpdesk')" class="active-indicator"></div>
                </NuxtLink>
              </div>

              <div class="nav-item-collapsed">
                <NuxtLink
                    to="/dashboard/faq"
                    class="nav-icon-link"
                    :class="getCollapsedLinkClasses('/dashboard/faq')"
                    title="FAQ"
                    aria-label="FAQ"
                >
                  <Icon name="mdi:comment-question-outline" class="nav-icon" />
                  <div v-if="isActiveLink('/dashboard/faq')" class="active-indicator"></div>
                </NuxtLink>
              </div>
            </div>
          </template>

          <!-- Expanded Sidebar Navigation -->
          <template v-else>
            <div class="expanded-nav space-y-4">

              <!-- Dashboard Link -->
              <div class="nav-section">
                <div class="nav-item">
                  <NuxtLink
                      to="/dashboard"
                      class="nav-link"
                      :class="getExpandedLinkClasses('/dashboard')"
                  >
                    <div class="nav-link-content">
                      <div class="nav-link-icon bg-gradient-to-br from-blue-500 to-indigo-600">
                        <Icon name="mdi:view-dashboard" class="w-5 h-5 text-white" />
                      </div>
                      <span class="nav-link-label">Dashboard</span>
                      <div v-if="isActiveLink('/dashboard')" class="nav-link-indicator"></div>
                    </div>
                  </NuxtLink>
                </div>
              </div>

              <!-- Standalone Links Section -->
              <div v-if="standaloneLinks.length" class="nav-section">
                <div class="space-y-2">
                  <div
                      v-for="link in standaloneLinks"
                      :key="'standalone-' + link.to"
                      class="nav-item"
                  >
                    <NuxtLink
                        :to="link.to"
                        class="nav-link"
                        :class="getExpandedLinkClasses(link.to || '')"
                        :aria-current="isActiveLink(link.to || '') ? 'page' : undefined"
                    >
                      <div class="nav-link-content">
                        <div class="nav-link-icon bg-gradient-to-br from-gray-500 to-slate-600">
                          <Icon :name="link.icon || defaultGroupIcon" class="w-5 h-5 text-white" />
                        </div>
                        <span class="nav-link-label">{{ link.label }}</span>
                        <div v-if="link.badge" class="nav-link-badge">{{ typeof link.badge === 'function' ? link.badge() : link.badge }}</div>
                        <div v-if="isActiveLink(link.to || '')" class="nav-link-indicator"></div>
                      </div>
                    </NuxtLink>
                  </div>
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
                      class="group-header"
                      @click="toggleGroup(group.name)"
                      @keydown.enter.prevent="toggleGroup(group.name)"
                      :aria-expanded="openGroup === group.name"
                      :aria-controls="`group-links-${group.name.replace(/\s+/g, '-')}`"
                      type="button"
                  >
                    <div class="group-header-content">
                      <div class="group-icon">
                        <Icon :name="group.icon || defaultGroupIcon" class="w-4 h-4" />
                      </div>
                      <span class="group-title">{{ group.name }}</span>
                      <div v-if="group.badge" class="group-badge">{{ group.badge }}</div>
                      <Icon
                          :name="openGroup === group.name ? 'mdi:chevron-down' : 'mdi:chevron-right'"
                          class="group-chevron w-4 h-4"
                      />
                    </div>
                  </button>

                  <!-- Group Links -->
                  <transition name="group-expand">
                    <div
                        v-if="openGroup === group.name"
                        class="group-links"
                        :id="`group-links-${group.name.replace(/\s+/g, '-')}`"
                    >
                      <div class="group-links-container">
                        <div
                            v-for="link in group.links.sort((a,b) => (a.order || 0) - (b.order || 0))"
                            :key="link.to"
                            class="group-link-item"
                        >
                          <NuxtLink
                              :to="link.to"
                              class="group-link"
                              :class="getGroupLinkClasses(link.to || '')"
                              :aria-current="isActiveLink(link.to || '') ? 'page' : undefined"
                          >
                            <div class="group-link-content">
                              <div class="group-link-icon">
                                <Icon :name="link.icon || group.icon || defaultGroupIcon" class="w-4 h-4" />
                              </div>
                              <span class="group-link-label">{{ link.label }}</span>
                              <div
                                  v-if="link.badge"
                                  class="group-link-badge"
                                  @click.stop.prevent="handleBadgeAction(link.badge_action_name, link.to || '')"
                                  @keydown.enter.prevent="handleBadgeAction(link.badge_action_name, link.to || '')"
                                  :title="link.badge_action_name ? 'Click for action' : ''"
                                  role="button"
                                  tabindex="0"
                              >
                                {{ typeof link.badge === 'function' ? link.badge() : link.badge }}
                              </div>
                              <div v-if="isActiveLink(link.to || '')" class="group-link-indicator"></div>
                            </div>
                          </NuxtLink>
                        </div>
                      </div>
                    </div>
                  </transition>
                </div>
              </div>

              <!-- Help & Support Section -->
              <div class="nav-section">
                <div class="nav-group-title">
                  <Icon name="mdi:help-circle" class="w-4 h-4 text-gray-400" />
                  <span>Support</span>
                </div>
                <div class="space-y-2">
                  <div class="nav-item">
                    <NuxtLink
                        to="/dashboard/helpdesk"
                        class="nav-link"
                        :class="getExpandedLinkClasses('/dashboard/helpdesk')"
                        :aria-current="isActiveLink('/dashboard/helpdesk') ? 'page' : undefined"
                    >
                      <div class="nav-link-content">
                        <div class="nav-link-icon bg-gradient-to-br from-teal-500 to-cyan-600">
                          <Icon name="mdi:help-circle-outline" class="w-5 h-5 text-white" />
                        </div>
                        <span class="nav-link-label">HelpDesk</span>
                        <div v-if="isActiveLink('/dashboard/helpdesk')" class="nav-link-indicator"></div>
                      </div>
                    </NuxtLink>
                  </div>

                  <div class="nav-item">
                    <NuxtLink
                        to="/dashboard/faq"
                        class="nav-link"
                        :class="getExpandedLinkClasses('/dashboard/faq')"
                        :aria-current="isActiveLink('/dashboard/faq') ? 'page' : undefined"
                    >
                      <div class="nav-link-content">
                        <div class="nav-link-icon bg-gradient-to-br from-orange-500 to-red-600">
                          <Icon name="mdi:comment-question-outline" class="w-5 h-5 text-white" />
                        </div>
                        <span class="nav-link-label">FAQ</span>
                        <div v-if="isActiveLink('/dashboard/faq')" class="nav-link-indicator"></div>
                      </div>
                    </NuxtLink>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </nav>
      </div>
    </aside>
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
}>()

// Constants
const ICON_SIZE = 26

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

// Touch handling for mobile
const startX = ref(0)
const currentX = ref(0)
const threshold = 60

let gsapContext: any = null

// RouteBuilder setup
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

// Dynamic links based on user
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

// Computed navigation data
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

// Computed styles and classes
const sidebarClasses = computed(() => [
  'dashboard-sidebar relative transition-all duration-300 ease-in-out overflow-hidden',
  {
    // Mobile classes
    'mobile-sidebar fixed top-0 left-0 h-full z-[50] shadow-2xl': isMobile.value,
    'w-80 max-w-[85vw]': isMobile.value,
    'transform translate-x-0': isMobile.value && mobileOpen.value,
    'transform -translate-x-full': isMobile.value && !mobileOpen.value,

    // Desktop classes
    'h-full': !isMobile.value,
    'w-20': !isMobile.value && collapsed.value,
    'w-64': !isMobile.value && !collapsed.value,
  }
])

// Methods
function toggleGroup(name: string) {
  openGroup.value = openGroup.value === name ? null : name
}

function onCollapsedGroupIconClick(name: string) {
  if (collapsed.value) {
    // Expand sidebar and open group
    collapsed.value = false
    setTimeout(() => {
      openGroup.value = name
    }, 300)

    // Navigate to first link in group
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
  // Handle specific badge actions here
}

function closeMobileSidebar() {
  emit('close-mobile-sidebar')
}

// Style helper methods
function getCollapsedLinkClasses(path: string) {
  return {
    'nav-icon-active': isActiveLink(path),
    'nav-icon-inactive': !isActiveLink(path)
  }
}

function getExpandedLinkClasses(path: string) {
  return {
    'nav-link-active': isActiveLink(path),
    'nav-link-inactive': !isActiveLink(path)
  }
}

function getGroupLinkClasses(path: string) {
  return {
    'group-link-active': isActiveLink(path),
    'group-link-inactive': !isActiveLink(path)
  }
}

// Touch handlers for mobile swipe
function onTouchStart(e: TouchEvent) {
  startX.value = e.touches[0].clientX
}

function onTouchMove(e: TouchEvent) {
  currentX.value = e.touches[0].clientX
}

function onTouchEnd() {
  if (currentX.value - startX.value < -threshold) {
    closeMobileSidebar()
  }
}

// Animations
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
    const navItems = sidebarEl.value?.querySelectorAll('.nav-item, .nav-item-collapsed')
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
/* Sidebar Base */
.dashboard-sidebar {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

.sidebar-background {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

.sidebar-content {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

/* Mobile Profile Section */
.mobile-profile-section {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.profile-avatar-container:hover .avatar-glow {
  opacity: 0.4;
}

.profile-name {
  background: linear-gradient(135deg, #1f2937 0%, #3b82f6 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Custom Scrollbar */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.3) transparent;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.3);
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(107, 114, 128, 0.5);
}

/* Collapsed Navigation */
.collapsed-nav {
  padding-top: 1rem;
}

.nav-item-collapsed {
  position: relative;
}

.nav-icon-link,
.nav-icon-button {
  position: relative;
  width: 3rem;
  height: 3rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  overflow: hidden;
  text-decoration: none;
}

.nav-icon-link {
  background: rgba(243, 244, 246, 0.5);
  color: #6b7280;
}

.nav-icon-button {
  background: rgba(243, 244, 246, 0.5);
  color: #6b7280;
  border: none;
  cursor: pointer;
}

.nav-icon-link:hover,
.nav-icon-button:hover {
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  color: white;
  transform: scale(1.05);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

.nav-icon-active {
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%) !important;
  color: white !important;
  transform: scale(1.05);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
}

.nav-icon {
  width: 1.25rem;
  height: 1.25rem;
}

.active-indicator {
  position: absolute;
  bottom: 0.25rem;
  left: 50%;
  transform: translateX(-50%);
  width: 1rem;
  height: 0.125rem;
  background: white;
  border-radius: 9999px;
}

.icon-badge {
  position: absolute;
  top: -0.25rem;
  right: -0.25rem;
  min-width: 1.25rem;
  height: 1.25rem;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  font-size: 0.625rem;
  font-weight: 700;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid white;
  animation: badgePulse 2s infinite;
}

.expand-indicator {
  position: absolute;
  bottom: 0.25rem;
  right: 0.25rem;
  width: 0.75rem;
  height: 0.75rem;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.group-icon-button:hover .expand-indicator {
  opacity: 1;
}

/* Expanded Navigation */
.expanded-nav {
  padding-top: 0.5rem;
}

.nav-section {
  margin-bottom: 1.5rem;
}

.nav-item {
  margin-bottom: 0.5rem;
}

.nav-link {
  display: block;
  text-decoration: none;
  border-radius: 0.75rem;
  transition: all 0.3s ease;
  overflow: hidden;
  position: relative;
}

.nav-link-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  position: relative;
  z-index: 10;
}

.nav-link-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.nav-link-label {
  flex: 1;
  font-weight: 600;
  font-size: 0.875rem;
  color: #374151;
  transition: color 0.3s ease;
}

.nav-link-badge {
  padding: 0.25rem 0.5rem;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  color: white;
  font-size: 0.625rem;
  font-weight: 700;
  border-radius: 9999px;
  animation: badgePulse 2s infinite;
}

.nav-link-indicator {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 0.375rem;
  height: 0.375rem;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  border-radius: 50%;
}

.nav-link-inactive {
  background: rgba(243, 244, 246, 0.3);
}

.nav-link-inactive:hover {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
  transform: translateX(0.25rem);
}

.nav-link-inactive:hover .nav-link-icon {
  transform: scale(1.1);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.nav-link-inactive:hover .nav-link-label {
  color: #1f2937;
}

.nav-link-active {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
  border: 1px solid rgba(59, 130, 246, 0.2);
}

.nav-link-active .nav-link-label {
  color: #1f2937;
  font-weight: 700;
}

.nav-link-active .nav-link-icon {
  transform: scale(1.05);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

/* Group Styles */
.nav-group {
  margin-bottom: 1rem;
}

.group-header {
  width: 100%;
  padding: 0.5rem 1rem;
  background: rgba(243, 244, 246, 0.3);
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-bottom: 0.5rem;
}

.group-header:hover {
  background: rgba(243, 244, 246, 0.5);
  transform: translateX(0.125rem);
}

.group-header-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.group-icon {
  color: #6b7280;
}

.group-title {
  flex: 1;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #6b7280;
}

.group-badge {
  padding: 0.125rem 0.375rem;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  color: white;
  font-size: 0.625rem;
  font-weight: 700;
  border-radius: 9999px;
}

.group-chevron {
  color: #6b7280;
  transition: transform 0.3s ease;
}

.group-links {
  margin-left: 1rem;
  padding-left: 1rem;
  border-left: 2px solid rgba(229, 231, 235, 0.5);
  background: rgba(249, 250, 251, 0.3);
  border-radius: 0 0.5rem 0.5rem 0;
}

.group-links-container {
  padding: 0.5rem 0;
}

.group-link-item {
  margin-bottom: 0.25rem;
}

.group-link {
  display: block;
  text-decoration: none;
  border-radius: 0.5rem;
  transition: all 0.3s ease;
}

.group-link-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
}

.group-link-icon {
  width: 1.5rem;
  height: 1.5rem;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.3s ease;
}

.group-link-label {
  flex: 1;
  font-size: 0.875rem;
  color: #4b5563;
  font-weight: 500;
  transition: color 0.3s ease;
}

.group-link-badge {
  padding: 0.125rem 0.375rem;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  color: white;
  font-size: 0.625rem;
  font-weight: 700;
  border-radius: 9999px;
  cursor: pointer;
}

.group-link-indicator {
  width: 0.25rem;
  height: 0.25rem;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  border-radius: 50%;
}

.group-link-inactive {
  color: #6b7280;
}

.group-link-inactive:hover {
  background: rgba(243, 244, 246, 0.5);
  transform: translateX(0.25rem);
}

.group-link-inactive:hover .group-link-icon {
  color: #3b82f6;
}

.group-link-inactive:hover .group-link-label {
  color: #1f2937;
}

.group-link-active {
  background: rgba(59, 130, 246, 0.1);
  border-left: 3px solid #3b82f6;
}

.group-link-active .group-link-icon {
  color: #3b82f6;
}

.group-link-active .group-link-label {
  color: #1f2937;
  font-weight: 600;
}

/* Support Section */
.nav-group-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  padding: 0.5rem 1rem;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #6b7280;
}

/* Transitions */
.overlay-enter-active,
.overlay-leave-active {
  transition: opacity 0.3s ease;
}

.overlay-enter-from,
.overlay-leave-to {
  opacity: 0;
}

.group-expand-enter-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.group-expand-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.group-expand-enter-from {
  opacity: 0;
  max-height: 0;
  padding: 0;
}

.group-expand-leave-to {
  opacity: 0;
  max-height: 0;
  padding: 0;
}

.group-expand-enter-to {
  opacity: 1;
  max-height: 500px;
}

.group-expand-leave-from {
  opacity: 1;
  max-height: 500px;
}

/* Animations */
@keyframes badgePulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
  .sidebar-background {
    background: rgba(17, 24, 39, 0.95);
  }

  .nav-link-label,
  .group-link-label {
    color: #d1d5db;
  }

  .nav-link-active .nav-link-label,
  .group-link-active .group-link-label {
    color: white;
  }

  .custom-scrollbar {
    scrollbar-color: rgba(75, 85, 99, 0.3) transparent;
  }

  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(75, 85, 99, 0.3);
  }

  .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(55, 65, 81, 0.5);
  }

  .nav-icon-link,
  .nav-icon-button {
    background: rgba(31, 41, 55, 0.5);
    color: #9ca3af;
  }

  .group-header {
    background: rgba(31, 41, 55, 0.3);
  }

  .group-links {
    background: rgba(17, 24, 39, 0.3);
    border-left-color: rgba(75, 85, 99, 0.5);
  }
}

/* Focus States */
.nav-link:focus-visible,
.nav-icon-link:focus-visible,
.nav-icon-button:focus-visible,
.group-header:focus-visible,
.group-link:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Mobile Optimizations */
@media (max-width: 768px) {
  .mobile-sidebar {
    width: 20rem;
    max-width: 85vw;
  }

  .group-links {
    margin-left: 0.5rem;
    padding-left: 0.5rem;
  }
}

/* Responsive adjustments */
@media (max-height: 600px) {
  .mobile-profile-section {
    padding: 1rem;
  }

  .nav-section {
    margin-bottom: 1rem;
  }
}
</style>
