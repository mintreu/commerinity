<template>
  <div class="flex flex-col min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
    <!-- Navbar -->
    <DefaultNavbar />

    <!-- Content -->
    <div class="flex flex-col flex-1 pt-4 overflow-auto dark:text-white" style="min-height: calc(100vh - 4rem);">
      <main class="flex-1">
        <slot />
      </main>

      <!-- Footer -->
      <footer class="bg-gray-200 dark:bg-gray-900 text-gray-800 dark:text-gray-200 py-10 px-6 md:px-16 mt-12 transition-colors">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10 text-sm">

          <!-- Brand and Social Icons -->
          <div>
            <h3 class="text-lg font-bold mb-2">Commernity</h3>
            <p class="mb-4">Bringing everything you love under one roof.</p>

            <!-- Social Icons Row -->
            <div class="flex items-center space-x-4 mt-4">
              <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 transition">
                <Icon name="uil:facebook-f" class="w-5 h-5" />
              </a>
              <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-pink-500 transition">
                <Icon name="uil:instagram" class="w-5 h-5" />
              </a>
              <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-blue-400 transition">
                <Icon name="uil:twitter" class="w-5 h-5" />
              </a>
              <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-blue-700 transition">
                <Icon name="uil:linkedin" class="w-5 h-5" />
              </a>
            </div>
          </div>

          <!-- Categories -->
          <div>
            <h3 class="font-bold mb-2">Categories</h3>
            <ul class="space-y-1">
              <li><a href="#" class="hover:underline">Electronics</a></li>
              <li><a href="#" class="hover:underline">Fashion</a></li>
              <li><a href="#" class="hover:underline">Home</a></li>
              <li><a href="#" class="hover:underline">Beauty</a></li>
            </ul>
          </div>

          <!-- Support -->
          <div>
            <h3 class="font-bold mb-2">Support</h3>
            <ul class="space-y-1">
              <li><a href="#" class="hover:underline">Help Center</a></li>
              <li><a href="#" class="hover:underline">Returns</a></li>
              <li><a href="#" class="hover:underline">Shipping</a></li>
              <li><NuxtLink to="/contact" class="hover:underline">Contact Us</NuxtLink></li>
            </ul>
          </div>

          <!-- Legal & Info -->
          <div>
            <h3 class="font-bold mb-2">Information</h3>
            <ul class="space-y-1">
              <li><NuxtLink to="/about" class="hover:underline">About Us</NuxtLink></li>
              <li><NuxtLink to="/privacy" class="hover:underline">Privacy Policy</NuxtLink></li>
              <li><NuxtLink to="/terms" class="hover:underline">Terms & Conditions</NuxtLink></li>
              <li><NuxtLink to="/career" class="hover:underline">Careers</NuxtLink></li>

            </ul>
          </div>
        </div>

        <!-- Bottom copyright -->
        <div class="text-center mt-10 text-xs text-gray-500 dark:text-gray-400">
          &copy; 2025 Commernity. All rights reserved.
        </div>
      </footer>




    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
// import { useSanctum } from '#imports'
// import DarkModeToggle from "~/components/ui/DarkModeToggle.vue";
// import UserDropdown from "~/components/ui/UserDropdown.vue";
import DefaultNavbar from "~/components/ui/Navbar/DefaultNavbar.vue";

// interface User {
//   name: string
//   email: string
//   avatar: string
// }
//
// const { isLoggedIn, user, logout } = useSanctum<User>()
onMounted(() => {

})
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateX(-100%);
}
</style>
