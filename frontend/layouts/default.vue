<template>
  <div class="layout-container min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-blue-950 dark:to-purple-950 transition-all duration-500">

    <!-- Background Effects -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden will-change-transform">
      <!-- Floating Orbs -->
      <div ref="layoutOrb1" class="layout-orb-1 absolute -top-20 -left-20 w-80 h-80 bg-gradient-to-r from-blue-400/20 to-purple-400/20 rounded-full blur-3xl opacity-60 will-change-transform"></div>
      <div ref="layoutOrb2" class="layout-orb-2 absolute -bottom-20 -right-20 w-72 h-72 bg-gradient-to-r from-purple-400/20 to-pink-400/20 rounded-full blur-3xl opacity-70 will-change-transform"></div>

      <!-- Subtle Particles -->
      <div class="layout-particles">
        <div v-for="(particle, index) in particles" :key="`layout-particle-${index}`"
             class="particle absolute rounded-full opacity-30 animate-pulse will-change-transform"
             :class="particle.class"
             :style="particle.style">
        </div>
      </div>
    </div>

    <!-- Navbar -->
    <header class="navbar-container relative z-50" ref="navbarContainer">
      <DefaultNavbar />
    </header>

    <!-- Main Content -->
    <main
        id="main-content"
        aria-label="Main content"
        class="main-content relative z-10 flex-1 transition-all duration-300"
        ref="mainContent"
    >
      <slot />
    </main>

    <!-- Mobile Bottom Navigation -->
    <nav class="bottom-nav-container md:hidden" ref="bottomNavContainer">
      <BottomNavBar class="fixed inset-x-0 bottom-0 z-40 border-t border-white/20 bg-white/90 backdrop-blur-xl supports-[backdrop-filter]:bg-white/70 dark:border-gray-800/50 dark:bg-gray-900/90 dark:supports-[backdrop-filter]:bg-gray-900/70 shadow-2xl" />
    </nav>

    <!-- Enhanced Footer -->
    <footer
        role="contentinfo"
        class="footer-container relative mt-20 border-t border-white/30 dark:border-gray-800/50"
        ref="footerContainer"
    >
      <div class="footer-background bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl">
        <!-- Footer Content -->
        <div class="footer-content px-6 py-16 md:px-20">
          <div class="mx-auto max-w-7xl">

            <!-- Main Footer Grid -->
            <div class="grid grid-cols-1 gap-12 sm:grid-cols-2 lg:grid-cols-4">

              <!-- Brand Section -->
              <section aria-labelledby="footer-brand" class="footer-brand" ref="footerBrand">
                <div class="brand-header mb-6">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="brand-icon w-12 h-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                      <Icon name="mdi:store" class="w-6 h-6 text-white" />
                    </div>
                    <h3 id="footer-brand" class="text-2xl font-black text-gray-900 dark:text-white">
                      {{ websiteName }}
                    </h3>
                  </div>
                  <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-sm">
                    Your premier destination for quality products and exceptional service. Bringing everything you love under one roof.
                  </p>
                </div>

                <!-- Social Links -->
                <nav aria-label="Social media" class="social-links">
                  <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Follow Us</h4>
                  <ul class="flex items-center gap-3">
                    <li>
                      <a
                          href="#"
                          aria-label="Facebook"
                          class="social-link w-10 h-10 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl flex items-center justify-center transition-all duration-300 transform hover:scale-110 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                      >
                        <Icon name="uil:facebook-f" class="h-4 w-4" />
                      </a>
                    </li>
                    <li>
                      <a
                          href="#"
                          aria-label="Instagram"
                          class="social-link w-10 h-10 bg-gradient-to-r from-pink-600 to-rose-700 hover:from-pink-700 hover:to-rose-800 text-white rounded-xl flex items-center justify-center transition-all duration-300 transform hover:scale-110 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-500"
                      >
                        <Icon name="uil:instagram" class="h-4 w-4" />
                      </a>
                    </li>
                    <li>
                      <a
                          href="#"
                          aria-label="Twitter"
                          class="social-link w-10 h-10 bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-700 hover:to-blue-800 text-white rounded-xl flex items-center justify-center transition-all duration-300 transform hover:scale-110 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500"
                      >
                        <Icon name="uil:twitter" class="h-4 w-4" />
                      </a>
                    </li>
                    <li>
                      <a
                          href="#"
                          aria-label="LinkedIn"
                          class="social-link w-10 h-10 bg-gradient-to-r from-blue-700 to-indigo-700 hover:from-blue-800 hover:to-indigo-800 text-white rounded-xl flex items-center justify-center transition-all duration-300 transform hover:scale-110 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700"
                      >
                        <Icon name="uil:linkedin" class="h-4 w-4" />
                      </a>
                    </li>
                  </ul>
                </nav>
              </section>

              <!-- Categories -->
              <nav aria-labelledby="footer-categories" class="footer-section" ref="footerCategories">
                <div class="section-header mb-6">
                  <h3 id="footer-categories" class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                    <Icon name="mdi:format-list-bulleted" class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" />
                    Categories
                  </h3>
                </div>
                <ul class="space-y-3 text-sm">
                  <li>
                    <NuxtLink
                        to="/category/electronics"
                        class="footer-link flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 rounded-md p-1"
                    >
                      <Icon name="mdi:laptop" class="w-4 h-4" />
                      Electronics
                    </NuxtLink>
                  </li>
                  <li>
                    <NuxtLink
                        to="/category/fashion"
                        class="footer-link flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-500 rounded-md p-1"
                    >
                      <Icon name="mdi:tshirt-crew" class="w-4 h-4" />
                      Fashion
                    </NuxtLink>
                  </li>
                  <li>
                    <NuxtLink
                        to="/category/home"
                        class="footer-link flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 rounded-md p-1"
                    >
                      <Icon name="mdi:home" class="w-4 h-4" />
                      Home & Living
                    </NuxtLink>
                  </li>
                  <li>
                    <NuxtLink
                        to="/category/beauty"
                        class="footer-link flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-pink-600 dark:hover:text-pink-400 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-500 rounded-md p-1"
                    >
                      <Icon name="mdi:lipstick" class="w-4 h-4" />
                      Beauty
                    </NuxtLink>
                  </li>
                </ul>
              </nav>

              <!-- Support -->
              <nav aria-labelledby="footer-support" class="footer-section" ref="footerSupport">
                <div class="section-header mb-6">
                  <h3 id="footer-support" class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                    <Icon name="mdi:headset" class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" />
                    Support
                  </h3>
                </div>
                <ul class="space-y-3 text-sm">
                  <li>
                    <NuxtLink
                        to="/help"
                        class="footer-link flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 rounded-md p-1"
                    >
                      <Icon name="mdi:help-circle" class="w-4 h-4" />
                      Help Center
                    </NuxtLink>
                  </li>
                  <li>
                    <NuxtLink
                        to="/return-refund"
                        class="footer-link flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500 rounded-md p-1"
                    >
                      <Icon name="mdi:package-variant-closed" class="w-4 h-4" />
                      Return & Refund
                    </NuxtLink>
                  </li>
                  <li>
                    <NuxtLink
                        to="/shipping"
                        class="footer-link flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 rounded-md p-1"
                    >
                      <Icon name="mdi:truck-fast" class="w-4 h-4" />
                      Shipping Info
                    </NuxtLink>
                  </li>
                  <li>
                    <NuxtLink
                        to="/contact"
                        class="footer-link flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-500 rounded-md p-1"
                    >
                      <Icon name="mdi:email" class="w-4 h-4" />
                      Contact Us
                    </NuxtLink>
                  </li>
                </ul>
              </nav>

              <!-- Information -->
              <nav aria-labelledby="footer-info" class="footer-section" ref="footerInfo">
                <div class="section-header mb-6">
                  <h3 id="footer-info" class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                    <Icon name="mdi:information" class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" />
                    Information
                  </h3>
                </div>
                <ul class="space-y-3 text-sm">
                  <li>
                    <NuxtLink
                        to="/about"
                        class="footer-link flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 rounded-md p-1"
                    >
                      <Icon name="mdi:information-outline" class="w-4 h-4" />
                      About Us
                    </NuxtLink>
                  </li>
                  <li>
                    <NuxtLink
                        to="/privacy"
                        class="footer-link flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 rounded-md p-1"
                    >
                      <Icon name="mdi:shield-account" class="w-4 h-4" />
                      Privacy Policy
                    </NuxtLink>
                  </li>
                  <li>
                    <NuxtLink
                        to="/terms"
                        class="footer-link flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-500 rounded-md p-1"
                    >
                      <Icon name="mdi:file-document" class="w-4 h-4" />
                      Terms & Conditions
                    </NuxtLink>
                  </li>
                  <li>
                    <NuxtLink
                        to="/career"
                        class="footer-link flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500 rounded-md p-1"
                    >
                      <Icon name="mdi:briefcase" class="w-4 h-4" />
                      Careers
                    </NuxtLink>
                  </li>
                </ul>
              </nav>
            </div>

            <!-- Newsletter Subscription -->
            <div class="newsletter-section mt-16 pt-12 border-t border-gray-200 dark:border-gray-700" ref="newsletterSection">
              <div class="max-w-2xl mx-auto text-center">
                <div class="newsletter-header mb-6">
                  <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2">Stay Updated</h3>
                  <p class="text-gray-600 dark:text-gray-300">Subscribe to our newsletter for the latest products, offers, and updates.</p>
                </div>
                <form class="newsletter-form flex flex-col sm:flex-row gap-4 max-w-md mx-auto" @submit.prevent="subscribeNewsletter">
                  <input
                      v-model="newsletterEmail"
                      type="email"
                      placeholder="Enter your email address"
                      class="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                      required
                  />
                  <button
                      type="submit"
                      :disabled="subscribing"
                      class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-2"
                  >
                    <Icon v-if="subscribing" name="mdi:loading" class="w-4 h-4 animate-spin" />
                    <Icon v-else name="mdi:send" class="w-4 h-4" />
                    <span>{{ subscribing ? 'Subscribing...' : 'Subscribe' }}</span>
                  </button>
                </form>
              </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom mt-12 pt-8 border-t border-gray-200 dark:border-gray-700" ref="footerBottom">
              <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="copyright text-center md:text-left">
                  <p class="text-sm text-gray-600 dark:text-gray-400">
                    &copy; {{ currentYear }} <strong class="text-gray-900 dark:text-white">{{ websiteName }}</strong>. All rights reserved.
                  </p>
                </div>
                <div class="footer-badges flex items-center gap-4">
                  <div class="badge flex items-center gap-2 px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 rounded-full text-xs font-semibold">
                    <Icon name="mdi:shield-check" class="w-3 h-3" />
                    Secure
                  </div>
                  <div class="badge flex items-center gap-2 px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 rounded-full text-xs font-semibold">
                    <Icon name="mdi:truck-fast" class="w-3 h-3" />
                    Fast Delivery
                  </div>
                  <div class="badge flex items-center gap-2 px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400 rounded-full text-xs font-semibold">
                    <Icon name="mdi:heart" class="w-3 h-3" />
                    24/7 Support
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button
        v-show="showScrollTop"
        @click="scrollToTop"
        class="scroll-top fixed bottom-24 md:bottom-8 right-8 w-14 h-14 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-full shadow-2xl hover:shadow-blue-500/25 transition-all duration-300 transform hover:scale-110 flex items-center justify-center z-30 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
        aria-label="Scroll to top"
        ref="scrollTopButton"
    >
      <Icon name="mdi:arrow-up" class="w-6 h-6" />
    </button>

    <!-- Teleports Container -->
    <div id="teleports"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import DefaultNavbar from '~/components/ui/Navbar/DefaultNavbar.vue'
import BottomNavBar from '~/components/ui/BottomNavBar.vue'

// GSAP imports (client-side only)
let gsap: any = null
let ScrollTrigger: any = null

if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
    import('gsap/ScrollTrigger').then(({ ScrollTrigger: ScrollTriggerModule }) => {
      ScrollTrigger = ScrollTriggerModule
      gsap.registerPlugin(ScrollTrigger)
    })
  })
}

// Configuration
const config = useRuntimeConfig()
const websiteName = config.public.websiteName

// Refs for animations
const layoutOrb1 = ref<HTMLElement>()
const layoutOrb2 = ref<HTMLElement>()
const navbarContainer = ref<HTMLElement>()
const mainContent = ref<HTMLElement>()
const footerContainer = ref<HTMLElement>()
const footerBrand = ref<HTMLElement>()
const footerCategories = ref<HTMLElement>()
const footerSupport = ref<HTMLElement>()
const footerInfo = ref<HTMLElement>()
const newsletterSection = ref<HTMLElement>()
const footerBottom = ref<HTMLElement>()
const bottomNavContainer = ref<HTMLElement>()
const scrollTopButton = ref<HTMLElement>()

// State
const showScrollTop = ref(false)
const newsletterEmail = ref('')
const subscribing = ref(false)
let gsapContext: any = null

// Computed
const currentYear = computed(() => new Date().getFullYear())

// Animated particles
const particles = [
  { class: 'w-2 h-2 bg-blue-400', style: 'top: 10%; left: 5%; animation-delay: 0s; animation-duration: 3s;' },
  { class: 'w-3 h-3 bg-purple-400', style: 'top: 20%; right: 10%; animation-delay: 1s; animation-duration: 4s;' },
  { class: 'w-2 h-2 bg-pink-400', style: 'bottom: 15%; left: 15%; animation-delay: 2s; animation-duration: 5s;' },
  { class: 'w-3 h-3 bg-cyan-400', style: 'bottom: 25%; right: 20%; animation-delay: 3s; animation-duration: 3.5s;' },
  { class: 'w-2 h-2 bg-indigo-400', style: 'top: 50%; left: 8%; animation-delay: 4s; animation-duration: 4.5s;' }
]

// Methods
const subscribeNewsletter = async () => {
  if (!newsletterEmail.value.trim()) return

  subscribing.value = true

  try {
    // Simulate newsletter subscription
    await new Promise(resolve => setTimeout(resolve, 1500))

    // Show success feedback (you can integrate with your toast system)
    console.log('Newsletter subscription successful:', newsletterEmail.value)
    newsletterEmail.value = ''

    // Here you would integrate with your actual newsletter service

  } catch (error) {
    console.error('Newsletter subscription failed:', error)
  } finally {
    subscribing.value = false
  }
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const handleScroll = () => {
  showScrollTop.value = window.scrollY > 400
}

// GSAP animations
const initializeAnimations = () => {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
    // Floating orbs animation
    if (layoutOrb1.value) {
      gsap.to(layoutOrb1.value, {
        y: -30,
        rotation: 10,
        duration: 10,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: -1
      })
    }

    if (layoutOrb2.value) {
      gsap.to(layoutOrb2.value, {
        y: 20,
        rotation: -8,
        duration: 12,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: -1
      })
    }

    // Footer animations
    if (ScrollTrigger && footerContainer.value) {
      // Footer sections staggered animation
      const footerSections = [footerBrand.value, footerCategories.value, footerSupport.value, footerInfo.value]

      gsap.fromTo(footerSections.filter(Boolean),
          { y: 30, opacity: 0 },
          {
            y: 0,
            opacity: 1,
            duration: 0.8,
            stagger: 0.1,
            ease: 'back.out(1.7)',
            scrollTrigger: {
              trigger: footerContainer.value,
              start: 'top 80%',
              toggleActions: 'play none none reverse'
            }
          }
      )

      // Newsletter section animation
      if (newsletterSection.value) {
        gsap.fromTo(newsletterSection.value,
            { y: 40, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 1,
              ease: 'back.out(1.7)',
              scrollTrigger: {
                trigger: newsletterSection.value,
                start: 'top 85%',
                toggleActions: 'play none none reverse'
              }
            }
        )
      }

      // Footer bottom animation
      if (footerBottom.value) {
        gsap.fromTo(footerBottom.value,
            { y: 20, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 0.8,
              ease: 'power2.out',
              scrollTrigger: {
                trigger: footerBottom.value,
                start: 'top 90%',
                toggleActions: 'play none none reverse'
              }
            }
        )
      }
    }

    // Scroll to top button animation
    if (scrollTopButton.value) {
      gsap.set(scrollTopButton.value, { scale: 0 })

      const showTopButton = () => {
        gsap.to(scrollTopButton.value, {
          scale: showScrollTop.value ? 1 : 0,
          duration: 0.3,
          ease: 'back.out(1.7)'
        })
      }

      // Watch for scroll changes
      window.addEventListener('scroll', () => {
        const shouldShow = window.scrollY > 400
        if (shouldShow !== showScrollTop.value) {
          showScrollTop.value = shouldShow
          showTopButton()
        }
      })
    }
  })
}

// Lifecycle
onMounted(() => {
  // Add scroll listener
  window.addEventListener('scroll', handleScroll, { passive: true })

  // Initialize animations after a short delay
  setTimeout(() => {
    initializeAnimations()
  }, 100)
})

onUnmounted(() => {
  // Clean up
  if (process.client) {
    window.removeEventListener('scroll', handleScroll)
  }

  if (gsapContext) {
    gsapContext.kill()
  }

  if (process.client && ScrollTrigger) {
    ScrollTrigger.getAll().forEach((trigger: any) => trigger.kill())
  }
})
</script>

<style scoped>
/* Performance optimizations */
.will-change-transform {
  will-change: transform;
}

/* Layout animations */
.layout-container {
  position: relative;
}

/* Navbar enhancements */
.navbar-container {
  backdrop-filter: blur(20px);
}

/* Main content transitions */
.main-content {
  min-height: 50vh;
}

/* Footer enhancements */
.footer-container {
  position: relative;
}

.footer-background {
  position: relative;
}

.footer-background::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(147, 51, 234, 0.05));
  pointer-events: none;
}

/* Brand section */
.brand-icon {
  position: relative;
  transition: all 0.3s ease;
}

.brand-icon::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2));
  border-radius: 1rem;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.brand-icon:hover::before {
  opacity: 1;
}

/* Social links */
.social-link {
  position: relative;
  overflow: hidden;
}

.social-link::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.6s ease;
}

.social-link:hover::before {
  left: 100%;
}

/* Footer links */
.footer-link {
  position: relative;
  transition: all 0.2s ease;
}

.footer-link:hover {
  transform: translateX(5px);
}

/* Newsletter form */
.newsletter-form input:focus {
  transform: scale(1.02);
}

.newsletter-form button {
  position: relative;
  overflow: hidden;
}

.newsletter-form button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.6s ease;
}

.newsletter-form button:hover::before {
  left: 100%;
}

/* Footer badges */
.badge {
  transition: all 0.3s ease;
}

.badge:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Scroll to top button */
.scroll-top {
  position: relative;
  overflow: hidden;
}

.scroll-top::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.6s ease;
}

.scroll-top:hover::before {
  left: 100%;
}

/* Particle animations */
.particle {
  animation: floatParticle 4s ease-in-out infinite;
  will-change: transform;
}

@keyframes floatParticle {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
    opacity: 0.3;
  }
  50% {
    transform: translateY(-10px) rotate(180deg);
    opacity: 0.6;
  }
}

/* Responsive design */
@media (max-width: 1024px) {
  .layout-orb-2 {
    display: none;
  }
}

@media (max-width: 640px) {
  .footer-content {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }

  .newsletter-form {
    flex-direction: column;
  }

  .footer-bottom {
    flex-direction: column;
    text-align: center;
  }

  .footer-badges {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .layout-orb-1 {
    width: 4rem;
    height: 4rem;
  }

  .layout-particles {
    display: none;
  }
}

/* Focus states for accessibility */
.social-link:focus-visible,
.footer-link:focus-visible,
.scroll-top:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .footer-background {
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1);
  }
}

/* Smooth transitions for theme changes */
* {
  transition-property: background-color, border-color, color, fill, stroke;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>
