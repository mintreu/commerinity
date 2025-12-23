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
        <div class="max-w-7xl mx-auto px-6 py-6">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-center sm:text-left">
              <p class="text-sm text-slate-600 dark:text-slate-400">
                &copy; {{ currentYear }} Commerinity Pro. All rights reserved.
              </p>
            </div>
            <div class="flex items-center gap-6 text-sm">
              <NuxtLink
                to="/privacy"
                class="text-slate-600 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
              >
                Privacy
              </NuxtLink>
              <NuxtLink
                to="/terms"
                class="text-slate-600 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
              >
                Terms
              </NuxtLink>
            </div>
          </div>

          <!-- Trust Indicators -->
          <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-800">
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
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
const currentYear = new Date().getFullYear()
</script>
