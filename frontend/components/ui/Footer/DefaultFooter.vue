<template>
  <footer
      role="contentinfo"
      class="footer-wrapper relative border-t border-gray-200 dark:border-gray-800 mt-auto"
  >
    <!-- Background with gradient -->
    <div class="footer-background bg-gradient-to-b from-white to-gray-50 dark:from-gray-900 dark:to-gray-950">
      <div class="footer-content px-6 py-16 md:px-12 lg:px-20">
        <div class="mx-auto max-w-7xl">

          <!-- Main Footer Grid -->
          <div class="grid grid-cols-1 gap-12 lg:grid-cols-12 lg:gap-8">

            <!-- Brand Section (Larger on Desktop) -->
            <section class="footer-brand lg:col-span-4">
              <NuxtLink to="/" class="inline-flex items-center gap-3 mb-6 group">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow">
                  <Icon name="mdi:storefront" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ siteConfig.name }}</h3>
                  <p class="text-xs text-gray-600 dark:text-gray-400">{{ siteConfig.tagline }}</p>
                </div>
              </NuxtLink>

              <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-6">
                {{ siteConfig.description }}
              </p>

              <!-- Social Links -->
              <div class="flex items-center gap-4">
                <a
                    v-for="social in socialLinks"
                    :key="social.name"
                    :href="social.url"
                    :aria-label="social.name"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 hover:bg-blue-600 dark:hover:bg-blue-600 flex items-center justify-center transition-all duration-300 hover:scale-110 group"
                >
                  <Icon :name="social.icon" class="w-5 h-5 text-gray-600 dark:text-gray-300 group-hover:text-white transition-colors" />
                </a>
              </div>
            </section>

            <!-- Footer Links Grid -->
            <div class="lg:col-span-8 grid grid-cols-2 gap-8 sm:grid-cols-3 lg:grid-cols-4">

              <!-- Shop Section -->
              <nav class="footer-section">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 uppercase tracking-wide">Shop</h4>
                <ul class="space-y-3 text-sm">
                  <li v-for="link in shopLinks" :key="link.url">
                    <NuxtLink
                        :to="link.url"
                        class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors inline-flex items-center group"
                    >
                      <Icon name="mdi:chevron-right" class="w-4 h-4 opacity-0 group-hover:opacity-100 -ml-4 group-hover:ml-0 transition-all" />
                      <span>{{ link.title }}</span>
                    </NuxtLink>
                  </li>
                </ul>
              </nav>

              <!-- Customer Service -->
              <nav class="footer-section">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 uppercase tracking-wide">Support</h4>
                <ul class="space-y-3 text-sm">
                  <li v-for="link in supportLinks" :key="link.url">
                    <NuxtLink
                        :to="link.url"
                        class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors inline-flex items-center group"
                    >
                      <Icon name="mdi:chevron-right" class="w-4 h-4 opacity-0 group-hover:opacity-100 -ml-4 group-hover:ml-0 transition-all" />
                      <span>{{ link.title }}</span>
                    </NuxtLink>
                  </li>
                </ul>
              </nav>

              <!-- Company Info -->
              <nav class="footer-section">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 uppercase tracking-wide">Company</h4>
                <ul class="space-y-3 text-sm">
                  <li v-for="link in companyLinks" :key="link.url">
                    <NuxtLink
                        :to="link.url"
                        class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors inline-flex items-center group"
                    >
                      <Icon name="mdi:chevron-right" class="w-4 h-4 opacity-0 group-hover:opacity-100 -ml-4 group-hover:ml-0 transition-all" />
                      <span>{{ link.title }}</span>
                    </NuxtLink>
                  </li>
                </ul>
              </nav>

              <!-- Contact Info -->
              <div class="footer-section">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 uppercase tracking-wide">Contact</h4>
                <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                  <li class="flex items-start gap-2">
                    <Icon name="mdi:email-outline" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                    <a :href="`mailto:${siteConfig.email}`" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors break-all">
                      {{ siteConfig.email }}
                    </a>
                  </li>
                  <li class="flex items-start gap-2">
                    <Icon name="mdi:phone-outline" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                    <a :href="`tel:${siteConfig.phone}`" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                      {{ siteConfig.phone }}
                    </a>
                  </li>
                  <li class="flex items-start gap-2">
                    <Icon name="mdi:map-marker-outline" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                    <span class="leading-relaxed">{{ siteConfig.address }}</span>
                  </li>
                </ul>
              </div>

            </div>
          </div>

          <!-- Bottom Bar -->
          <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-800">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-gray-600 dark:text-gray-400">
              <p>&copy; {{ currentYear }} {{ siteConfig.name }}. All rights reserved.</p>
              <div class="flex items-center gap-6 flex-wrap justify-center">
                <NuxtLink to="/privacy" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Privacy Policy
                </NuxtLink>
                <NuxtLink to="/terms" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Terms of Service
                </NuxtLink>
                <NuxtLink to="/cookies" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Cookie Policy
                </NuxtLink>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRuntimeConfig } from '#app'

const config = useRuntimeConfig()

// Computed
const currentYear = computed(() => new Date().getFullYear())

// ✅ FROZEN CONFIG - Zero reactivity overhead
const siteConfig = Object.freeze({
  name: config.public.websiteName || 'VVIndia',
  tagline: 'Your Premium Marketplace',
  description: 'Discover quality products at unbeatable prices. Shop with confidence with our secure payment system and fast delivery.',
  email: config.public.supportEmail || 'support@vvindia.com',
  phone: config.public.phoneNumber || '+91 98765 43210',
  address: config.public.address || 'Kolkata, West Bengal 700156, India'
})

const socialLinks = Object.freeze([
  { name: 'Facebook', icon: 'mdi:facebook', url: '#' },
  { name: 'Twitter', icon: 'mdi:twitter', url: '#' },
  { name: 'Instagram', icon: 'mdi:instagram', url: '#' },
  { name: 'LinkedIn', icon: 'mdi:linkedin', url: '#' }
])

const shopLinks = Object.freeze([
  { title: 'Electronics', url: '/category/electronics' },
  { title: 'Electrical Appliances', url: '/category/electrical' },
  { title: 'Fashion & Accessories', url: '/category/fashion' },
  { title: 'All Products', url: '/store' }
]);


const supportLinks = Object.freeze([
  // { title: 'Help Center', url: '/help' },
  { title: 'Shipping Info', url: '/shipping' },
  { title: 'Returns', url: '/return-refund' },
  { title: 'Privacy', url: '/privacy' },
  { title: 'Terms & Condition', url: '/terms' },
  { title: 'Contact Us', url: '/contact' }
])

const companyLinks = Object.freeze([
  { title: 'About Us', url: '/about' },
  { title: 'Careers', url: '/careers' },
  { title: 'Blog', url: '/blog' },
  // { title: 'Affiliate Program', url: '/affiliate' }
])
</script>

<style scoped>
/* Smooth transitions */
.footer-section a {
  position: relative;
}

.footer-section a::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 2px;
  background: currentColor;
  transition: width 0.3s ease;
}

.footer-section a:hover::after {
  width: 100%;
}
</style>
