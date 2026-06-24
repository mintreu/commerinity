<template>
  <header class="fixed top-0 left-0 right-0 z-50 w-full">
    <div class="absolute inset-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-b border-slate-200/80 dark:border-slate-800/80">
      <div class="absolute inset-0 bg-gradient-to-r from-violet-500/5 via-fuchsia-500/5 to-pink-500/5 dark:from-violet-400/10 dark:via-fuchsia-400/10 dark:to-pink-400/10" />
    </div>

    <div class="relative z-10">
      <nav class="mx-auto w-full max-w-[1680px] px-4 md:px-6 lg:px-8">
        <!-- Mobile Layout: Menu Toggle | Brand (Center) | Dark Mode -->
        <div class="flex lg:hidden h-16 items-center justify-between w-full">
          <!-- Left: Mobile Menu Toggle -->
          <button
            class="w-10 h-10 bg-slate-100 dark:bg-slate-800 hover:bg-gradient-to-r hover:from-violet-500 hover:to-fuchsia-500 text-slate-600 dark:text-slate-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300"
            @click="toggleMobileMenu"
          >
            <UIcon
              :name="mobileMenuOpen ? 'i-lucide-x' : 'i-lucide-menu'"
              class="w-5 h-5"
            />
          </button>

          <!-- Center: Brand Logo -->
          <NuxtLink
            to="/"
            class="flex items-center gap-2 group"
          >
            <img
              src="/logo.png"
              :alt="config.public.companyName"
              class="h-10 w-auto"
            >
            <span class="gradient-text-primary text-lg font-bold">{{ config.public.companyName }}</span>
          </NuxtLink>

          <!-- Right: Dark Mode Toggle -->
          <ClientOnly>
            <button
              class="w-10 h-10 bg-slate-100 dark:bg-slate-800 hover:bg-gradient-to-r hover:from-amber-500 hover:to-orange-500 text-slate-600 dark:text-slate-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300"
              @click="toggleDark()"
            >
              <UIcon
                :name="isDark ? 'i-lucide-sun' : 'i-lucide-moon'"
                class="w-5 h-5"
              />
            </button>
          </ClientOnly>
        </div>

        <!-- Desktop Layout: Brand + Nav | Actions -->
        <div class="hidden lg:flex h-16 items-center justify-between w-full gap-4">
          <!-- Left: Brand + Main Nav -->
          <div class="flex min-w-0 items-center gap-6">
            <!-- Brand Logo -->
            <NuxtLink
              to="/"
              class="flex items-center gap-3 group"
            >
              <img
                src="/logo.png"
                :alt="config.public.companyName"
                class="h-10 w-auto"
              >
              <div>
                <span class="gradient-text-primary text-xl font-bold">{{ config.public.companyName }}</span>
                <p class="text-xs text-slate-500 dark:text-slate-400 hidden lg:block">Your Shopping Destination</p>
              </div>
            </NuxtLink>

            <!-- Main Navigation Links (Desktop) -->
            <div class="flex items-center gap-0.5">
              <NuxtLink
                to="/"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:text-violet-600 dark:hover:text-violet-400 rounded-xl hover:bg-violet-50 dark:hover:bg-violet-900/30 transition-all duration-300"
              >
                <UIcon
                  name="i-lucide-home"
                  class="w-4 h-4"
                />
                <span>Home</span>
              </NuxtLink>
              <NuxtLink
                to="/shop"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:text-violet-600 dark:hover:text-violet-400 rounded-xl hover:bg-violet-50 dark:hover:bg-violet-900/30 transition-all duration-300"
              >
                <UIcon
                  name="i-lucide-store"
                  class="w-4 h-4"
                />
                <span>Store</span>
              </NuxtLink>
              <NuxtLink
                to="/categories"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:text-violet-600 dark:hover:text-violet-400 rounded-xl hover:bg-violet-50 dark:hover:bg-violet-900/30 transition-all duration-300"
              >
                <UIcon
                  name="i-lucide-grid-3x3"
                  class="w-4 h-4"
                />
                <span>Categories</span>
              </NuxtLink>
              <NuxtLink
                to="/career"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:text-violet-600 dark:hover:text-violet-400 rounded-xl hover:bg-violet-50 dark:hover:bg-violet-900/30 transition-all duration-300"
              >
                <UIcon
                  name="i-lucide-briefcase"
                  class="w-4 h-4"
                />
                <span>Career</span>
              </NuxtLink>
              <NuxtLink
                to="/about"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:text-violet-600 dark:hover:text-violet-400 rounded-xl hover:bg-violet-50 dark:hover:bg-violet-900/30 transition-all duration-300"
              >
                <UIcon
                  name="i-lucide-info"
                  class="w-4 h-4"
                />
                <span>About</span>
              </NuxtLink>
            </div>
          </div>

          <!-- Right: Actions (Desktop Only) -->
          <div
            ref="searchBoxRef"
            class="relative flex flex-shrink-0 items-center gap-2"
          >
            <button
              type="button"
              class="relative w-10 h-10 bg-slate-100 dark:bg-slate-800 hover:bg-gradient-to-r hover:from-violet-500 hover:to-fuchsia-500 text-slate-600 dark:text-slate-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300"
              @click="toggleSearchPanel"
            >
              <UIcon
                name="i-lucide-search"
                class="w-5 h-5"
              />
            </button>

            <Transition name="fade">
              <div
                v-if="searchPanelOpen"
                class="absolute right-0 top-[calc(100%+0.75rem)] z-[70] w-[min(38rem,94vw)] overflow-hidden rounded-2xl border border-slate-200/70 bg-white/95 shadow-2xl shadow-slate-900/10 backdrop-blur-xl dark:border-slate-700/70 dark:bg-slate-900/95 dark:shadow-black/40"
              >
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-violet-500/5 via-transparent to-fuchsia-500/10 dark:from-violet-400/10 dark:to-fuchsia-400/10" />
                <div class="relative p-3">
                <form
                  class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                  @submit.prevent="submitGlobalSearch"
                >
                  <UIcon
                    name="i-lucide-search"
                    class="h-4 w-4 shrink-0 text-slate-400"
                  />
                  <input
                    ref="searchInputRef"
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search products, blogs, news"
                    class="w-full bg-transparent text-sm text-slate-700 outline-none placeholder:text-slate-400 dark:text-slate-200 dark:placeholder:text-slate-500"
                    @focus="openSuggestionsIfNeeded"
                  >
                  <button
                    type="submit"
                    class="inline-flex h-9 items-center justify-center rounded-lg bg-gradient-to-r from-violet-600 to-fuchsia-600 px-4 text-xs font-semibold text-white shadow-lg shadow-violet-500/25 transition-all hover:from-violet-700 hover:to-fuchsia-700"
                  >
                    Search
                  </button>
                </form>

                <div
                  v-if="showSuggestions"
                  class="mt-3 max-h-80 overflow-auto rounded-xl border border-slate-100 bg-white/90 dark:border-slate-800 dark:bg-slate-900/70"
                >
                  <div
                    v-if="suggestionLoading"
                    class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400"
                  >
                    Searching...
                  </div>

                  <div
                    v-else-if="suggestions.length === 0"
                    class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400"
                  >
                    No matches found.
                  </div>

                  <div v-else>
                    <button
                      v-for="item in suggestions"
                      :key="`${item.type}-${item.id}`"
                      type="button"
                      class="flex w-full items-center gap-3 border-b border-slate-100 px-4 py-3 text-left transition hover:bg-slate-50 last:border-b-0 dark:border-slate-800 dark:hover:bg-slate-800"
                      @click="openSuggestion(item.url)"
                    >
                      <img
                        v-if="item.thumbnail"
                        :src="item.thumbnail"
                        :alt="item.title"
                        class="h-10 w-10 rounded-lg object-cover"
                      >
                      <div
                        v-else
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800"
                      >
                        <UIcon
                          :name="item.type === 'product' ? 'i-lucide-package' : item.type === 'blog' ? 'i-lucide-newspaper' : 'i-lucide-megaphone'"
                          class="h-5 w-5 text-slate-400"
                        />
                      </div>
                      <div class="min-w-0 flex-1">
                        <p class="line-clamp-1 text-sm font-semibold text-slate-900 dark:text-slate-100">
                          {{ item.title }}
                        </p>
                        <p class="line-clamp-1 text-xs capitalize text-slate-500 dark:text-slate-400">
                          {{ item.type }}
                        </p>
                      </div>
                    </button>
                  </div>
                </div>
                </div>
              </div>
            </Transition>

            <!-- Cart (logged-in users only) -->
            <NuxtLink
              v-if="isAuthenticated"
              to="/cart"
              data-cart-target
              class="relative w-10 h-10 bg-slate-100 dark:bg-slate-800 hover:bg-gradient-to-r hover:from-emerald-500 hover:to-green-500 text-slate-600 dark:text-slate-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300"
            >
              <UIcon
                name="i-lucide-shopping-cart"
                class="w-5 h-5"
              />
              <span
                v-if="cartCount > 0"
                class="absolute -top-1 -right-1 min-w-5 h-5 px-1 bg-fuchsia-500 text-white text-xs font-bold rounded-full flex items-center justify-center"
              >
                {{ cartCount > 99 ? '99+' : cartCount }}
              </span>
            </NuxtLink>

            <!-- Authenticated Actions -->
            <template v-if="isAuthenticated">
              <!-- Notifications -->
              <NotificationBell />

              <!-- Divider -->
              <div class="w-px h-8 bg-gradient-to-b from-transparent via-slate-300 to-transparent dark:via-slate-600" />

              <!-- Theme Toggle -->
              <ClientOnly>
                <button
                  class="w-10 h-10 bg-slate-100 dark:bg-slate-800 hover:bg-gradient-to-r hover:from-amber-500 hover:to-orange-500 text-slate-600 dark:text-slate-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-105"
                  @click="toggleDark()"
                >
                  <UIcon
                    :name="isDark ? 'i-lucide-sun' : 'i-lucide-moon'"
                    class="w-5 h-5"
                  />
                </button>
              </ClientOnly>

              <!-- User Dropdown -->
              <UserDropdown />
            </template>

            <!-- Guest Actions -->
            <template v-else>
              <!-- Theme Toggle -->
              <ClientOnly>
                <button
                  class="w-10 h-10 bg-slate-100 dark:bg-slate-800 hover:bg-gradient-to-r hover:from-amber-500 hover:to-orange-500 text-slate-600 dark:text-slate-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-105"
                  @click="toggleDark()"
                >
                  <UIcon
                    :name="isDark ? 'i-lucide-sun' : 'i-lucide-moon'"
                    class="w-5 h-5"
                  />
                </button>
              </ClientOnly>

              <!-- Login Button -->
              <NuxtLink to="/auth/login">
                <button class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl font-semibold text-sm transition-all duration-300 flex items-center gap-2">
                  <UIcon
                    name="i-lucide-log-in"
                    class="w-4 h-4"
                  />
                  <span>Sign In</span>
                </button>
              </NuxtLink>

              <!-- Register Button -->
              <NuxtLink to="/auth/register">
                <button class="px-5 py-2.5 bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-700 hover:to-fuchsia-700 text-white rounded-xl font-semibold text-sm shadow-lg shadow-violet-500/25 transition-all duration-300 hover:-translate-y-0.5 flex items-center gap-2">
                  <UIcon
                    name="i-lucide-user-plus"
                    class="w-4 h-4"
                  />
                  <span>Sign Up</span>
                </button>
              </NuxtLink>
            </template>
          </div>
        </div>
      </nav>

      <!-- Mobile Menu Backdrop -->
      <Transition name="fade">
        <div
          v-if="mobileMenuOpen"
          class="fixed inset-0 bg-black/20  lg:hidden"
          style="z-index: 40;"
          @click="closeMobileMenu"
        />
      </Transition>

      <!-- Mobile Menu -->
      <Transition name="slide-down">
        <div
          v-if="mobileMenuOpen"
          class="lg:hidden border-t border-slate-200/50 dark:border-slate-700/50"
          style="position: relative; z-index: 50;"
        >
          <div class="px-4 py-4 space-y-2 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl max-h-[calc(100vh-4rem)] overflow-y-auto">
            <!-- Navigation Links -->
            <div class="mb-4">
              <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3 px-2">
                Navigation
              </h3>

              <NuxtLink
                to="/"
                class="flex items-center gap-4 p-3 rounded-xl hover:bg-violet-50 dark:hover:bg-violet-900/30 transition-all font-semibold text-slate-900 dark:text-white"
                @click="closeMobileMenu"
              >
                <div class="icon-box-primary icon-box-md">
                  <UIcon
                    name="i-lucide-home"
                    class="w-5 h-5 text-white"
                  />
                </div>
                <span>Home</span>
              </NuxtLink>

              <NuxtLink
                to="/shop"
                class="flex items-center gap-4 p-3 rounded-xl hover:bg-violet-50 dark:hover:bg-violet-900/30 transition-all font-semibold text-slate-900 dark:text-white"
                @click="closeMobileMenu"
              >
                <div class="icon-box-secondary icon-box-md">
                  <UIcon
                    name="i-lucide-store"
                    class="w-5 h-5 text-white"
                  />
                </div>
                <span>Store</span>
              </NuxtLink>

              <NuxtLink
                to="/categories"
                class="flex items-center gap-4 p-3 rounded-xl hover:bg-violet-50 dark:hover:bg-violet-900/30 transition-all font-semibold text-slate-900 dark:text-white"
                @click="closeMobileMenu"
              >
                <div class="icon-box-success icon-box-md">
                  <UIcon
                    name="i-lucide-grid-3x3"
                    class="w-5 h-5 text-white"
                  />
                </div>
                <span>Categories</span>
              </NuxtLink>

              <NuxtLink
                to="/career"
                class="flex items-center gap-4 p-3 rounded-xl hover:bg-violet-50 dark:hover:bg-violet-900/30 transition-all font-semibold text-slate-900 dark:text-white"
                @click="closeMobileMenu"
              >
                <div class="icon-box-warning icon-box-md">
                  <UIcon
                    name="i-lucide-briefcase"
                    class="w-5 h-5 text-white"
                  />
                </div>
                <span>Career</span>
              </NuxtLink>

              <NuxtLink
                to="/about"
                class="flex items-center gap-4 p-3 rounded-xl hover:bg-violet-50 dark:hover:bg-violet-900/30 transition-all font-semibold text-slate-900 dark:text-white"
                @click="closeMobileMenu"
              >
                <div class="icon-box-info icon-box-md">
                  <UIcon
                    name="i-lucide-info"
                    class="w-5 h-5 text-white"
                  />
                </div>
                <span>About</span>
              </NuxtLink>
            </div>

            <!-- Auth Section (if not logged in) -->
            <div
              v-if="!isAuthenticated"
              class="border-t border-slate-200 dark:border-slate-700 pt-4 mt-4"
            >
              <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3 px-2">
                Account
              </h3>

              <NuxtLink
                to="/auth/login"
                class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-all font-semibold text-slate-900 dark:text-white"
                @click="closeMobileMenu"
              >
                <div class="w-10 h-10 bg-slate-200 dark:bg-slate-700 rounded-xl flex items-center justify-center">
                  <UIcon
                    name="i-lucide-log-in"
                    class="w-5 h-5 text-slate-600 dark:text-slate-300"
                  />
                </div>
                <span>Sign In</span>
              </NuxtLink>

              <NuxtLink
                to="/auth/register"
                class="mt-2 flex items-center gap-4 p-3 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white font-bold shadow-lg"
                @click="closeMobileMenu"
              >
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                  <UIcon
                    name="i-lucide-user-plus"
                    class="w-5 h-5"
                  />
                </div>
                <span>Create Account</span>
              </NuxtLink>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </header>
</template>

<script setup lang="ts">
import type { GlobalSearchItem } from '~/composables/useGlobalSearch'

const { isLoggedIn: isAuthenticated } = useSanctum()
const colorMode = useColorMode()
const config = useRuntimeConfig()
const { cartCount, fetchCart } = useCart()
const router = useRouter()
const { search, buildSuggestions } = useGlobalSearch()

const mobileMenuOpen = ref(false)
const searchQuery = ref('')
const suggestions = ref<GlobalSearchItem[]>([])
const suggestionLoading = ref(false)
const showSuggestions = ref(false)
const searchPanelOpen = ref(false)
const searchBoxRef = ref<HTMLElement | null>(null)
const searchInputRef = ref<HTMLInputElement | null>(null)
let suggestionTimer: ReturnType<typeof setTimeout> | null = null

// Fetch cart on mount
onMounted(() => {
  if (isAuthenticated.value) {
    fetchCart()
  }
})

watch(isAuthenticated, (value) => {
  if (value) {
    fetchCart()
  }
})

const isDark = computed(() => colorMode.value === 'dark')

const toggleDark = () => {
  colorMode.preference = colorMode.value === 'dark' ? 'light' : 'dark'
}

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}

const toggleSearchPanel = async () => {
  searchPanelOpen.value = !searchPanelOpen.value

  if (searchPanelOpen.value) {
    await nextTick()
    searchInputRef.value?.focus()
    openSuggestionsIfNeeded()
  } else {
    showSuggestions.value = false
  }
}

const openSuggestionsIfNeeded = () => {
  if (searchQuery.value.trim().length >= 2) {
    showSuggestions.value = true
  }
}

const fetchSuggestions = async (term: string) => {
  if (term.trim().length < 2) {
    suggestions.value = []
    showSuggestions.value = false
    return
  }

  suggestionLoading.value = true
  try {
    const data = await search(term, 3)
    suggestions.value = buildSuggestions(data.results, 9)
    showSuggestions.value = true
  } catch {
    suggestions.value = []
    showSuggestions.value = true
  } finally {
    suggestionLoading.value = false
  }
}

watch(searchQuery, (value) => {
  if (suggestionTimer) clearTimeout(suggestionTimer)
  suggestionTimer = setTimeout(() => {
    fetchSuggestions(value)
  }, 250)
})

const submitGlobalSearch = async () => {
  const query = searchQuery.value.trim()
  showSuggestions.value = false
  searchPanelOpen.value = false
  await router.push({
    path: '/search',
    query: query ? { q: query } : {}
  })
}

const openSuggestion = async (url: string) => {
  showSuggestions.value = false
  searchPanelOpen.value = false
  await navigateTo(url)
}

const onDocumentClick = (event: MouseEvent) => {
  if (!searchBoxRef.value) return
  const target = event.target as Node
  if (!searchBoxRef.value.contains(target)) {
    showSuggestions.value = false
    searchPanelOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', onDocumentClick)
})

onUnmounted(() => {
  if (suggestionTimer) {
    clearTimeout(suggestionTimer)
  }
  document.removeEventListener('click', onDocumentClick)
})
</script>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}

.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
