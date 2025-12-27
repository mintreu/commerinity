<template>
  <div class="min-h-screen w-full bg-slate-50 dark:bg-slate-950 relative overflow-hidden">
    <!-- Floating Background Orbs with Violet/Fuchsia -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div class="absolute -top-32 -right-32 w-96 h-96 bg-gradient-to-br from-violet-400/15 to-purple-400/15 rounded-full blur-3xl opacity-70" />
      <div class="absolute -bottom-32 -left-32 w-80 h-80 bg-gradient-to-br from-fuchsia-400/15 to-pink-400/15 rounded-full blur-3xl opacity-60" />
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-gradient-to-br from-violet-400/10 to-fuchsia-400/10 rounded-full blur-2xl opacity-50" />
    </div>

    <!-- Top Navigation -->
    <TopNavbar />

    <!-- Main Content with padding for navbar -->
    <main class="relative z-10 pt-16">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="relative z-10 mt-auto">
      <div class="border-t border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-6 py-8">
          <!-- Footer Links Grid -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8">
            <!-- Company -->
            <div>
              <h4 class="font-semibold text-slate-900 dark:text-white mb-4">
                Company
              </h4>
              <ul class="space-y-3">
                <li>
                  <NuxtLink
                    to="/about"
                    class="text-sm text-slate-600 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                  >
                    About Us
                  </NuxtLink>
                </li>
                <li>
                  <NuxtLink
                    to="/contact"
                    class="text-sm text-slate-600 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                  >
                    Contact
                  </NuxtLink>
                </li>
              </ul>
            </div>

            <!-- Legal -->
            <div>
              <h4 class="font-semibold text-slate-900 dark:text-white mb-4">
                Legal
              </h4>
              <ul class="space-y-3">
                <li>
                  <NuxtLink
                    to="/privacy"
                    class="text-sm text-slate-600 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                  >
                    Privacy Policy
                  </NuxtLink>
                </li>
                <li>
                  <NuxtLink
                    to="/terms"
                    class="text-sm text-slate-600 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                  >
                    Terms & Conditions
                  </NuxtLink>
                </li>
              </ul>
            </div>

            <!-- Support -->
            <div>
              <h4 class="font-semibold text-slate-900 dark:text-white mb-4">
                Support
              </h4>
              <ul class="space-y-3">
                <li>
                  <NuxtLink
                    to="/shipping"
                    class="text-sm text-slate-600 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                  >
                    Shipping Policy
                  </NuxtLink>
                </li>
                <li>
                  <NuxtLink
                    to="/return-refund"
                    class="text-sm text-slate-600 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                  >
                    Return & Refund
                  </NuxtLink>
                </li>
              </ul>
            </div>

            <!-- Connect -->
            <div>
              <h4 class="font-semibold text-slate-900 dark:text-white mb-4">
                Connect
              </h4>
              <div class="flex items-center gap-3">
                <a
                  href="#"
                  class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:bg-violet-100 dark:hover:bg-violet-900/30 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                >
                  <UIcon
                    name="i-lucide-facebook"
                    class="w-4 h-4"
                  />
                </a>
                <a
                  href="#"
                  class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:bg-violet-100 dark:hover:bg-violet-900/30 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                >
                  <UIcon
                    name="i-lucide-twitter"
                    class="w-4 h-4"
                  />
                </a>
                <a
                  href="#"
                  class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:bg-violet-100 dark:hover:bg-violet-900/30 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                >
                  <UIcon
                    name="i-lucide-instagram"
                    class="w-4 h-4"
                  />
                </a>
              </div>
            </div>
          </div>

          <!-- Trust Indicators -->
          <div class="py-4 border-t border-b border-slate-200 dark:border-slate-800 mb-6">
            <div class="flex items-center justify-center gap-8 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
              <div class="flex items-center gap-2">
                <UIcon
                  name="i-lucide-shield-check"
                  class="w-4 h-4 text-emerald-500"
                />
                <span>SSL Secured</span>
              </div>
              <div class="flex items-center gap-2">
                <UIcon
                  name="i-lucide-landmark"
                  class="w-4 h-4 text-violet-500"
                />
                <span>Banking Partners</span>
              </div>
              <div class="flex items-center gap-2">
                <UIcon
                  name="i-lucide-truck"
                  class="w-4 h-4 text-fuchsia-500"
                />
                <span>Fast Delivery</span>
              </div>
            </div>
          </div>

          <!-- Copyright -->
          <div class="text-center">
            <p class="text-sm text-slate-600 dark:text-slate-400">
              &copy; {{ currentYear }} Commerinity Pro. All rights reserved.
            </p>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
const currentYear = new Date().getFullYear()
</script>
