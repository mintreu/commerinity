<template>
  <div class="flex min-h-screen flex-col bg-gray-50 text-gray-900 transition-colors duration-300 dark:bg-gray-950 dark:text-gray-100">
    <!-- Navbar -->
    <DefaultNavbar />

    <!-- Content -->
    <main id="main-content" aria-label="Main content" class="flex-1 pt-4">
      <slot />
    </main>

    <!-- Mobile Bottom Nav -->
    <BottomNavBar class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/60 dark:border-gray-800 dark:bg-gray-900/95 dark:supports-[backdrop-filter]:bg-gray-900/60 md:hidden" />

    <!-- Footer -->
    <footer role="contentinfo" class="mt-16 border-t border-gray-200 bg-gray-100/70 px-6 py-12 text-gray-700 transition-colors dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-300 md:px-16">
      <div class="mx-auto max-w-7xl">
        <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 md:grid-cols-4">
          <!-- Brand and Social -->
          <section aria-labelledby="footer-brand">
            <h3 id="footer-brand" class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ websiteName }}</h3>
            <p class="mt-2 text-sm leading-6">Bringing everything you love under one roof.</p>

            <nav aria-label="Social media" class="mt-4">
              <ul class="flex items-center gap-4">
                <li>
                  <a href="#" aria-label="Facebook" class="text-gray-600 transition hover:text-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 dark:text-gray-400">
                    <Icon name="uil:facebook-f" class="h-5 w-5" />
                  </a>
                </li>
                <li>
                  <a href="#" aria-label="Instagram" class="text-gray-600 transition hover:text-pink-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-500 dark:text-gray-400">
                    <Icon name="uil:instagram" class="h-5 w-5" />
                  </a>
                </li>
                <li>
                  <a href="#" aria-label="Twitter" class="text-gray-600 transition hover:text-sky-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 dark:text-gray-400">
                    <Icon name="uil:twitter" class="h-5 w-5" />
                  </a>
                </li>
                <li>
                  <a href="#" aria-label="LinkedIn" class="text-gray-600 transition hover:text-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 dark:text-gray-400">
                    <Icon name="uil:linkedin" class="h-5 w-5" />
                  </a>
                </li>
              </ul>
            </nav>
          </section>

          <!-- Categories -->
          <nav aria-labelledby="footer-categories">
            <h3 id="footer-categories" class="text-base font-bold text-gray-900 dark:text-gray-100">Categories</h3>
            <ul class="mt-2 space-y-2 text-sm">
              <li><NuxtLink to="/category/electronics" class="hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">Electronics</NuxtLink></li>
              <li><NuxtLink to="/category/fashion" class="hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">Fashion</NuxtLink></li>
              <li><NuxtLink to="/category/home" class="hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">Home</NuxtLink></li>
              <li><NuxtLink to="/category/beauty" class="hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">Beauty</NuxtLink></li>
            </ul>
          </nav>

          <!-- Support -->
          <nav aria-labelledby="footer-support">
            <h3 id="footer-support" class="text-base font-bold text-gray-900 dark:text-gray-100">Support</h3>
            <ul class="mt-2 space-y-2 text-sm">
              <li><NuxtLink to="/help" class="hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">Help Center</NuxtLink></li>
              <li><NuxtLink to="/return-and-refund" class="hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">Return &amp; Refund</NuxtLink></li>
              <li><NuxtLink to="/shipping" class="hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">Shipping</NuxtLink></li>
              <li><NuxtLink to="/contact" class="hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">Contact Us</NuxtLink></li>
            </ul>
          </nav>

          <!-- Information -->
          <nav aria-labelledby="footer-info">
            <h3 id="footer-info" class="text-base font-bold text-gray-900 dark:text-gray-100">Information</h3>
            <ul class="mt-2 space-y-2 text-sm">
              <li><NuxtLink to="/about" class="hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">About Us</NuxtLink></li>
              <li><NuxtLink to="/privacy" class="hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">Privacy Policy</NuxtLink></li>
              <li><NuxtLink to="/terms" class="hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">Terms &amp; Conditions</NuxtLink></li>
              <li><NuxtLink to="/career" class="hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">Careers</NuxtLink></li>
            </ul>
          </nav>
        </div>

        <!-- Bottom -->
        <div class="mt-10 border-t border-gray-200 pt-6 text-center text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
          &copy; 2025 {{ websiteName }}. All rights reserved.
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import DefaultNavbar from '~/components/ui/Navbar/DefaultNavbar.vue'
import BottomNavBar from '~/components/ui/BottomNavBar.vue'

const config = useRuntimeConfig()
const websiteName = config.public.websiteName
</script>
