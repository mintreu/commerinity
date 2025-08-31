<template>
  <div>
    <!-- Mobile overlay -->
    <div
        v-if="isMobile && mobileOpen"
        class="fixed inset-0 bg-black/40 z-30 md:hidden"
        @click="closeMobileSidebar"
    ></div>

    <!-- Sidebar -->
    <aside
        ref="sidebarEl"
        :class="[
          isMobile
            ? 'mobile-sidebar fixed top-0 left-0 h-full bg-white dark:bg-gray-900 z-40 transform transition-transform duration-300 ease-in-out shadow-lg w-4/5 max-w-xs'
            : 'dashboard-sidebar',
          !isMobile && collapsed ? 'w-20' : !isMobile ? 'w-64' : '',
          isMobile && mobileOpen ? 'translate-x-0' : isMobile ? '-translate-x-full' : ''
        ]"
        aria-label="Sidebar Navigation"
    >
      <!-- User info mobile -->
      <div class="flex flex-col items-center text-center md:hidden py-3">
        <AvatarUploader />
        <h2 class="mt-4 text-xl font-semibold text-gray-900 dark:text-white">{{ user?.name }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ user?.email }}</p>
      </div>

      <!-- Navigation -->
      <nav
          class="flex-1 md:px-2 md:py-3 overflow-y-auto dark:text-white"
          style="scrollbar-width: thin; scrollbar-color: #a0aec0 transparent;"
          role="navigation"
          aria-label="Primary Navigation"
      >
        <!-- Collapsed sidebar -->
        <template v-if="collapsed && !isMobile">
          <ul class="flex flex-col items-center space-y-6 mt-4" role="list">
            <!-- Dashboard -->
            <li>
              <NuxtLink
                  to="/dashboard"
                  class="text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors block"
                  :class="{
                    'bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300': isActiveLink('/dashboard')
                  }"
                  title="Dashboard"
                  aria-label="Dashboard"
                  tabindex="-1"
              >
                <Icon name="mdi:view-dashboard" :style="{ fontSize: ICON_SIZE + 'px' }" aria-hidden="true"/>
              </NuxtLink>
            </li>

            <!-- Standalone Links -->
            <li v-for="link in standaloneLinks" :key="'collapsed-standalone-' + link.to" role="listitem">
              <NuxtLink
                  :to="link.to"
                  class="text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors block"
                  :class="{ 'text-primary-700 dark:text-primary-300': isActiveLink(link.to || '') }"
                  :title="link.label"
                  :aria-label="link.label"
                  tabindex="-1"
              >
                <Icon :name="link.icon || defaultGroupIcon" :style="{ fontSize: ICON_SIZE + 'px' }" aria-hidden="true"/>
              </NuxtLink>
            </li>

            <!-- Groups -->
            <li v-for="group in navGroups" :key="'collapsed-group-' + group.name" role="listitem">
              <Icon
                  v-if="group.links.length"
                  :name="group.icon || defaultGroupIcon"
                  class="text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors cursor-pointer"
                  :style="{ fontSize: ICON_SIZE + 'px' }"
                  @click="onCollapsedGroupIconClick(group.name)"
                  role="button"
                  tabindex="0"
                  @keydown.enter.prevent="onCollapsedGroupIconClick(group.name)"
                  :aria-label="'Open group ' + group.name"
              />
            </li>

            <!-- HelpDesk & FAQ -->
            <li>
              <NuxtLink
                  to="/dashboard/helpdesk"
                  class="text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors block"
                  :class="{ 'bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300': isActiveLink('/dashboard/helpdesk') }"
                  title="HelpDesk"
                  aria-label="HelpDesk"
                  tabindex="-1"
              >
                <Icon name="mdi:help-circle-outline" :style="{ fontSize: ICON_SIZE + 'px' }" aria-hidden="true"/>
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                  to="/dashboard/faq"
                  class="text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors block"
                  :class="{ 'bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300': isActiveLink('/dashboard/faq') }"
                  title="FAQ"
                  aria-label="FAQ"
                  tabindex="-1"
              >
                <Icon name="mdi:comment-question-outline" :style="{ fontSize: ICON_SIZE + 'px' }" aria-hidden="true"/>
              </NuxtLink>
            </li>
          </ul>
        </template>

        <!-- Expanded sidebar -->
        <template v-else>
          <!-- Dashboard -->
          <ul class="space-y-1 mb-6" role="list">
            <li>
              <NuxtLink
                  to="/dashboard"
                  class="nav-link flex items-center gap-3 px-4 py-2 rounded-lg transition-colors"
                  :class="{
                    'bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300': isActiveLink('/dashboard'),
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-primary-600 dark:hover:text-primary-400': !isActiveLink('/dashboard')
                  }"
                  aria-current="page"
              >
                <Icon name="mdi:view-dashboard" :style="{ fontSize: ICON_SIZE + 'px' }" aria-hidden="true"/>
                <span class="text-md truncate">Dashboard</span>
              </NuxtLink>
            </li>
          </ul>

          <!-- Dynamic Standalone Links -->
          <ul v-if="standaloneLinks.length" class="space-y-1 mb-6" role="list">
            <li v-for="link in standaloneLinks" :key="'standalone-' + link.to" role="listitem">
              <NuxtLink
                  :to="link.to"
                  class="nav-link flex items-center gap-3 px-4 py-2 rounded-lg transition-colors"
                  :class="{
                    'bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 font-semi-bold': isActiveLink(link.to || ''),
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-primary-600 dark:hover:text-primary-400': !isActiveLink(link.to || '')
                  }"
                  :aria-current="isActiveLink(link.to || '') ? 'page' : undefined"
              >
                <Icon :name="link.icon || defaultGroupIcon" :style="{ fontSize: ICON_SIZE + 'px' }" aria-hidden="true"/>
                <span class="text-md truncate">{{ link.label }}</span>
              </NuxtLink>
            </li>
          </ul>

          <!-- Dynamic Groups -->
          <template v-for="group in navGroups" :key="'expanded-group-' + group.name">
            <div
                v-if="group.links.length"
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
              <span v-if="group.badge" class="ml-2 bg-blue-500 text-white text-xs px-2 py-0.5 rounded">{{ group.badge }}</span>
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
                <li v-for="link in group.links.sort((a,b)=>a.order! - b.order!)" :key="link.to" role="listitem">
                  <NuxtLink
                      :to="link.to"
                      class="nav-link flex items-center gap-3 py-2 px-3 rounded-md transition-colors"
                      :class="{
                        'bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 font-semi-bold': isActiveLink(link.to || ''),
                        'text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-primary-600 dark:hover:text-primary-400': !isActiveLink(link.to || '')
                      }"
                      :aria-current="isActiveLink(link.to) ? 'page' : undefined"
                  >
                    <Icon :name="link.icon || group.icon || defaultGroupIcon" :style="{ fontSize: ICON_SIZE + 'px' }" aria-hidden="true"/>
                    <span class="flex justify-between items-center w-full gap-2">
                      <span class="text-md truncate">{{ link.label }}</span>
                      <span
                          v-if="link.badge"
                          class="ml-auto text-xs bg-blue-500 text-white rounded px-2 py-0.5 cursor-pointer select-none"
                          @click.stop.prevent="handleBadgeAction(link.badge_action_name, link.to || '')"
                          :title="link.badge_action_name ? 'Click for action' : ''"
                          role="button"
                          tabindex="0"
                          @keydown.enter.prevent="handleBadgeAction(link.badge_action_name, link.to || '')"
                      >
                        {{ typeof link.badge === 'function' ? link.badge() : link.badge }}
                      </span>
                    </span>
                  </NuxtLink>
                </li>
              </ul>
            </div>
          </template>

          <!-- HelpDesk & FAQ -->
          <ul class="space-y-1 mt-6" role="list">
            <li>
              <NuxtLink
                  to="/dashboard/helpdesk"
                  class="nav-link flex items-center gap-3 px-4 py-2 rounded-lg transition-colors"
                  :class="{
                    'bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 font-semi-bold': isActiveLink('/dashboard/helpdesk'),
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-primary-600 dark:hover:text-primary-400': !isActiveLink('/dashboard/helpdesk')
                  }"
                  :aria-current="isActiveLink('/dashboard/helpdesk') ? 'page' : undefined"
              >
                <Icon name="mdi:help-circle-outline" :style="{ fontSize: ICON_SIZE + 'px' }" aria-hidden="true"/>
                <span class="text-md truncate">HelpDesk</span>
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                  to="/dashboard/faq"
                  class="nav-link flex items-center gap-3 px-4 py-2 rounded-lg transition-colors"
                  :class="{
                    'bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 font-semi-bold': isActiveLink('/dashboard/faq'),
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-primary-600 dark:hover:text-primary-400': !isActiveLink('/dashboard/faq')
                  }"
                  :aria-current="isActiveLink('/dashboard/faq') ? 'page' : undefined"
              >
                <Icon name="mdi:comment-question-outline" :style="{ fontSize: ICON_SIZE + 'px' }" aria-hidden="true"/>
                <span class="text-md truncate">FAQ</span>
              </NuxtLink>
            </li>
          </ul>
        </template>
      </nav>
    </aside>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onBeforeUnmount, toRef } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import gsap from 'gsap'
import { useSanctum } from '#imports'
import AvatarUploader from '~/components/account/AvatarUploader.vue'
import { RouteBuilder, type NavLinkOptions } from '~/utils/RouteBuilder'

const emit = defineEmits<{
  (e: 'close-mobile-sidebar'): void
}>()


const ICON_SIZE = 26
const props = defineProps<{ collapsed: boolean; isMobile?: boolean; mobileOpen?: boolean }>()
const collapsed = toRef(props, 'collapsed')
const isMobile = toRef(props, 'isMobile')
const mobileOpen = toRef(props, 'mobileOpen')

const { user } = useSanctum()
const routeInstance = useRoute()
const router = useRouter()
const sidebarEl = ref<HTMLElement | null>(null)
const openGroup = ref<string | null>(null)
const defaultGroupIcon = 'material-symbols:folder'
const startX = ref(0)
const currentX = ref(0)
const threshold = 60

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

const dynamicLinks = computed(() => {
  if (!user.value) return []
  const u = user.value
  const type = u.type?.toLowerCase()
  const links: NavLinkOptions[] = []


  if (type === 'applicant') links.push({ to: '/dashboard/career/applications', label: 'My Applications', order: 1, group: 'Career', icon: 'mdi:briefcase-outline' })
  if (type !== 'applicant') links.push({ to: '/dashboard/members', label: 'Members', order: 1, group: 'Community', icon: 'mdi:account-multiple-outline' })
  if (!u.level_id || u.status?.toLowerCase() !== 'subscribed') links.push({ to: '/dashboard/subscribe', label: 'Subscribe Now', order: 10, group: '', icon: 'mdi:star-outline' })

  return links
})

const allLinks = computed(() => [...route.getLinks(), ...dynamicLinks.value])
const filteredLinks = computed(() => allLinks.value.filter(l => {
  if (l.currentUrl) {
    const urls = Array.isArray(l.currentUrl) ? l.currentUrl : [l.currentUrl]
    if (!urls.some(u => routeInstance.path.startsWith(u))) return false
  }
  if (l.auth && !user.value) return false
  return true
}))

const standaloneLinks = computed(() => filteredLinks.value.filter(l => !l.group).sort((a,b)=> (a.order||0) - (b.order||0)))
const navGroups = computed(() => {
  const map: Record<string, typeof filteredLinks.value> = {}
  filteredLinks.value.forEach(l => { if (l.group) { if (!map[l.group]) map[l.group] = []; map[l.group].push(l) } })
  return Object.keys(map).map(k => ({
    name: k,
    icon: map[k][0].icon || defaultGroupIcon,
    badge: map[k][0].groupBadge,
    links: map[k].sort((a,b)=> (a.order||0) - (b.order||0))
  }))
})

function toggleGroup(name: string) { openGroup.value = openGroup.value === name ? null : name }
function onCollapsedGroupIconClick(name: string) { if (collapsed.value) { collapsed.value = false; openGroup.value = name; router.push(navGroups.value.find(g => g.name === name)?.links[0]?.to || '/') } }
function isActiveLink(path: string) { return routeInstance.path === path || routeInstance.path.startsWith(path + '/') }
function handleBadgeAction(actionName: string | undefined, to: string) { if (!actionName) router.push(to) }
function closeMobileSidebar() { emit('close-mobile-sidebar') }
function onTouchStart(e: TouchEvent) { startX.value = e.touches[0].clientX }
function onTouchMove(e: TouchEvent) { currentX.value = e.touches[0].clientX }
function onTouchEnd() { if (currentX.value - startX.value < -threshold) closeMobileSidebar() }

onMounted(() => {
  if (sidebarEl.value && isMobile.value) {
    sidebarEl.value.addEventListener('touchstart', onTouchStart)
    sidebarEl.value.addEventListener('touchmove', onTouchMove)
    sidebarEl.value.addEventListener('touchend', onTouchEnd)
  }
})
onBeforeUnmount(() => {
  if (sidebarEl.value && isMobile.value) {
    sidebarEl.value.removeEventListener('touchstart', onTouchStart)
    sidebarEl.value.removeEventListener('touchmove', onTouchMove)
    sidebarEl.value.removeEventListener('touchend', onTouchEnd)
  }
})

watch(collapsed, val => {
  if (sidebarEl.value && !isMobile.value)
    gsap.to(sidebarEl.value, { width: val ? '5rem' : '16rem', duration: 0.15, ease: 'power3.inOut' })
})
</script>
