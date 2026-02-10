<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-violet-50 to-fuchsia-50 dark:from-slate-950 dark:via-violet-950 dark:to-fuchsia-950 transition-colors duration-500">
    <!-- Background Effects -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
      <div class="absolute -top-40 -left-40 w-96 h-96 bg-gradient-to-r from-violet-400/20 to-fuchsia-400/20 rounded-full blur-3xl opacity-60" />
      <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-gradient-to-r from-fuchsia-400/20 to-pink-400/20 rounded-full blur-3xl opacity-70" />
    </div>

    <!-- Main Layout -->
    <div class="relative z-10 flex min-h-screen flex-col">
      <!-- Top Navbar -->
      <TopNavbar />

      <!-- Main Content (Full Width) -->
      <main class="flex-1 pt-16">
        <slot />
      </main>

      <!-- Footer -->
      <footer class="mt-auto border-t border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl">
        <div class="px-6 py-16 md:px-12 lg:px-20">
          <div class="mx-auto max-w-7xl">
            <!-- Main Footer Grid -->
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-12 lg:gap-8">
              <!-- Brand Section -->
              <section class="lg:col-span-4">
                <NuxtLink
                  to="/"
                  class="inline-flex items-center gap-3 mb-6 group"
                >
                  <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-600 to-fuchsia-600 flex items-center justify-center shadow-lg shadow-violet-500/25 group-hover:scale-105 transition-transform">
                    <UIcon
                      name="i-lucide-hexagon"
                      class="w-6 h-6 text-white"
                    />
                  </div>
                  <div>
                    <h3 class="text-2xl font-bold gradient-text-primary">{{ config.public.companyName }}</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400">Your Premium Marketplace</p>
                  </div>
                </NuxtLink>

                <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed mb-6">
                  Discover quality products at unbeatable prices. Shop with confidence with our secure payment system and fast delivery.
                </p>

                <!-- Social Links -->
                <div class="flex items-center gap-3">
                  <a
                    v-for="social in socialLinks"
                    :key="social.name"
                    :href="social.url"
                    :aria-label="social.name"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-gradient-to-r hover:from-violet-600 hover:to-fuchsia-600 flex items-center justify-center transition-all duration-300 hover:scale-110 group"
                  >
                    <UIcon
                      :name="social.icon"
                      class="w-5 h-5 text-slate-600 dark:text-slate-300 group-hover:text-white transition-colors"
                    />
                  </a>
                </div>
              </section>

              <!-- Footer Links Grid -->
              <div class="lg:col-span-8 grid grid-cols-2 gap-8 sm:grid-cols-3 lg:grid-cols-4">
                <!-- Shop Section -->
                <nav>
                  <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-4 uppercase tracking-wide">
                    Shop
                  </h4>
                  <ul class="space-y-3 text-sm">
                    <li
                      v-for="link in shopLinks"
                      :key="link.url"
                    >
                      <NuxtLink
                        :to="link.url"
                        class="text-slate-600 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                      >
                        {{ link.title }}
                      </NuxtLink>
                    </li>
                  </ul>
                </nav>

                <!-- Support -->
                <nav>
                  <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-4 uppercase tracking-wide">
                    Support
                  </h4>
                  <ul class="space-y-3 text-sm">
                    <li
                      v-for="link in supportLinks"
                      :key="link.url"
                    >
                      <NuxtLink
                        :to="link.url"
                        class="text-slate-600 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                      >
                        {{ link.title }}
                      </NuxtLink>
                    </li>
                  </ul>
                </nav>

                <!-- Company -->
                <nav>
                  <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-4 uppercase tracking-wide">
                    Company
                  </h4>
                  <ul class="space-y-3 text-sm">
                    <li
                      v-for="link in companyLinks"
                      :key="link.url"
                    >
                      <NuxtLink
                        :to="link.url"
                        class="text-slate-600 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                      >
                        {{ link.title }}
                      </NuxtLink>
                    </li>
                  </ul>
                </nav>

                <!-- Contact -->
                <div>
                  <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-4 uppercase tracking-wide">
                    Contact
                  </h4>
                  <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-400">
                    <li class="flex items-start gap-2">
                      <UIcon
                        name="i-lucide-mail"
                        class="w-5 h-5 text-violet-600 flex-shrink-0 mt-0.5"
                      />
                      <a
                        :href="`mailto:${siteConfig.email}`"
                        class="hover:text-violet-600 dark:hover:text-violet-400 transition-colors break-all"
                      >
                        {{ siteConfig.email }}
                      </a>
                    </li>
                    <li class="flex items-start gap-2">
                      <UIcon
                        name="i-lucide-phone"
                        class="w-5 h-5 text-violet-600 flex-shrink-0 mt-0.5"
                      />
                      <a
                        :href="`tel:${siteConfig.phone}`"
                        class="hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                      >
                        {{ siteConfig.phone }}
                      </a>
                    </li>
                    <li class="flex items-start gap-2">
                      <UIcon
                        name="i-lucide-map-pin"
                        class="w-5 h-5 text-violet-600 flex-shrink-0 mt-0.5"
                      />
                      <span class="leading-relaxed">{{ siteConfig.address }}</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <ContentFooterHighlights />

            <!-- Bottom Bar -->
            <div class="mt-12 pt-8 border-t border-slate-200 dark:border-slate-800">
              <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-slate-600 dark:text-slate-400">
                <p>&copy; {{ currentYear }} {{ config.public.companyName }}. All rights reserved.</p>
                <div class="flex items-center gap-6 flex-wrap justify-center">
                  <NuxtLink
                    to="/privacy"
                    class="hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                  >
                    Privacy Policy
                  </NuxtLink>
                  <NuxtLink
                    to="/terms"
                    class="hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                  >
                    Terms of Service
                  </NuxtLink>
                  <NuxtLink
                    to="/cookies"
                    class="hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                  >
                    Cookie Policy
                  </NuxtLink>
                </div>
              </div>
            </div>
          </div>
        </div>
      </footer>
    </div>

    <!-- Mobile Bottom Nav - ONLY for authenticated users -->
    <nav
      v-if="isLoggedIn"
      class="fixed bottom-0 left-0 right-0 z-50 lg:hidden bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-t border-slate-200/80 dark:border-slate-800/80 safe-area-bottom"
    >
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

    <!-- Scroll to Top Button -->
    <Transition name="fade">
      <button
        v-show="showScrollTop"
        :class="[
          'fixed right-6 z-40 w-12 h-12 bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-700 hover:to-fuchsia-700 text-white rounded-full shadow-xl shadow-violet-500/25 transition-all duration-300 hover:scale-110 active:scale-95',
          isLoggedIn ? 'bottom-20 lg:bottom-8' : 'bottom-8'
        ]"
        aria-label="Scroll to top"
        @click="scrollToTop"
      >
        <UIcon
          name="i-lucide-arrow-up"
          class="w-5 h-5 mx-auto"
        />
      </button>
    </Transition>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const config = useRuntimeConfig()
const { isLoggedIn } = useSanctum()
const user = useCurrentUser()

const currentYear = new Date().getFullYear()
const showScrollTop = ref(false)

// Site config
const siteConfig = {
  email: config.public.supportEmail || 'support@commerinity.com',
  phone: config.public.phoneNumber || '+91 98765 43210',
  address: config.public.address || 'Kolkata, West Bengal 700156, India'
}

const socialLinks = [
  { name: 'Facebook', icon: 'i-lucide-facebook', url: '#' },
  { name: 'Twitter', icon: 'i-lucide-twitter', url: '#' },
  { name: 'Instagram', icon: 'i-lucide-instagram', url: '#' },
  { name: 'LinkedIn', icon: 'i-lucide-linkedin', url: '#' }
]

const shopLinks = [
  { title: 'All Products', url: '/shop' },
  { title: 'Categories', url: '/categories' },
  { title: 'Hot Deals', url: '/shop/deals' },
  { title: 'New Arrivals', url: '/shop/products?sort=latest' }
]

const supportLinks = [
  { title: 'Help Center', url: '/help' },
  { title: 'Shipping Info', url: '/shipping' },
  { title: 'Returns & Refunds', url: '/return-refund' },
  { title: 'Contact Us', url: '/contact' }
]

const companyLinks = [
  { title: 'About Us', url: '/about' },
  { title: 'Careers', url: '/career' },
  { title: 'Blog', url: '/blogs' },
  { title: 'News', url: '/news' }
]

// Mobile bottom navigation items (ONLY for authenticated users)
const mobileNavItems = computed(() => [
  { to: '/dashboard', label: 'Home', icon: 'i-lucide-home' },
  { to: '/shop', label: 'Shop', icon: 'i-lucide-shopping-bag' },
  { to: '/orders', label: 'Orders', icon: 'i-lucide-package' },
  { to: '/wallet', label: 'Wallet', icon: 'i-lucide-wallet' },
  { to: '/profile', label: 'Profile', icon: 'i-lucide-user' }
])

const isActiveRoute = (path: string) => {
  if (path === '/dashboard') {
    return route.path === '/dashboard' || route.path === '/'
  }
  return route.path === path || route.path.startsWith(path + '/')
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Handle scroll for scroll-to-top button
let ticking = false
const handleScroll = () => {
  if (!ticking) {
    requestAnimationFrame(() => {
      showScrollTop.value = window.scrollY > 400
      ticking = false
    })
    ticking = true
  }
}

onMounted(() => {
  if (import.meta.client) {
    window.addEventListener('scroll', handleScroll, { passive: true })
  }
})

onUnmounted(() => {
  if (import.meta.client) {
    window.removeEventListener('scroll', handleScroll)
  }
})
</script>

<style scoped>
/* Fade transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Safe area for mobile devices */
.safe-area-bottom {
  padding-bottom: env(safe-area-inset-bottom);
}
</style>
