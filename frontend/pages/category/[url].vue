<template>
  <div class="min-h-screen bg-white dark:bg-gray-950 text-gray-800 dark:text-gray-100 py-8 px-4 md:px-12">
    <GlobalLoader v-if="isLoading" />

    <section v-else-if="error" class="flex flex-col items-center justify-center min-h-[60vh] px-6">
      <div class="max-w-md mx-auto text-center">

        <!-- Error Icon with Animation -->
        <div class="relative mb-8">
          <div class="w-24 h-24 mx-auto bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-full flex items-center justify-center">
            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-orange-600 rounded-full flex items-center justify-center animate-pulse">
              <Icon name="mdi:store-alert" class="w-8 h-8 text-white" />
            </div>
          </div>
          <!-- Floating Elements -->
          <div class="absolute -top-2 -right-2 w-4 h-4 bg-red-400 rounded-full animate-bounce"></div>
          <div class="absolute -bottom-1 -left-1 w-3 h-3 bg-orange-400 rounded-full animate-bounce" style="animation-delay: 0.5s;"></div>
        </div>

        <!-- Error Message -->
        <div class="mb-8">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-3">
            Oops! Something went wrong
          </h2>
          <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
            We're having trouble loading this page right now. Don't worry, it's not you - it's us!
          </p>
          <p class="text-sm text-gray-500 dark:text-gray-500">
            Our team has been notified and we're working to fix this issue.
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-4">
          <!-- Primary Action -->
          <button
              @click="refresh()"
              class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200"
          >
            <Icon name="mdi:refresh" class="w-4 h-4" />
            Try Again
          </button>

          <!-- Secondary Actions -->
          <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <NuxtLink
                to="/"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200"
            >
              <Icon name="mdi:home" class="w-4 h-4" />
              Go Home
            </NuxtLink>

            <NuxtLink
                to="/categories"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200"
            >
              <Icon name="mdi:view-grid" class="w-4 h-4" />
              Browse Categories
            </NuxtLink>
          </div>
        </div>

        <!-- Help Section -->
        <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-center gap-6 text-sm text-gray-500 dark:text-gray-400">
            <a
                href="/contact"
                class="flex items-center gap-1 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
            >
              <Icon name="mdi:headset" class="w-4 h-4" />
              Contact Support
            </a>
            <a
                href="/help"
                class="flex items-center gap-1 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
            >
              <Icon name="mdi:help-circle" class="w-4 h-4" />
              Help Center
            </a>
          </div>

          <!-- Status Indicator -->
          <div class="mt-4 flex items-center justify-center gap-2 text-xs text-gray-400 dark:text-gray-500">
            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
            All systems operational • We're on it!
          </div>
        </div>

      </div>

      <!-- Background Decoration -->
      <div class="absolute inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-32 h-32 bg-blue-100 dark:bg-blue-900/20 rounded-full blur-3xl opacity-30"></div>
        <div class="absolute bottom-1/4 right-1/4 w-40 h-40 bg-purple-100 dark:bg-purple-900/20 rounded-full blur-3xl opacity-30"></div>
      </div>
    </section>


    <template v-else>
      <!-- Banner -->
      <section class="text-center mb-6">
        <div
            class="relative py-16 px-6 bg-center bg-cover bg-no-repeat rounded-xl overflow-hidden h-96"
            :style="category.banner ? `background-image: url(${category.banner})` : ''"
        >
          <div class="absolute inset-0 bg-black/30"></div>
          <div class="relative z-10">
            <div class="inline-block mb-4">
              <img :src="category.thumbnail" alt="Thumbnail" class="w-32 h-32 rounded-full object-cover mx-auto border-4 border-white shadow" />
            </div>
            <h1 class="text-3xl font-bold capitalize mb-2 text-white">{{ category.name }}</h1>
            <p class="text-sm text-gray-200">{{ category.views }} views</p>
          </div>
        </div>
      </section>

      <!-- Description -->
      <section class="text-center mb-10 max-w-2xl mx-auto">
        <p class="text-base text-gray-700 dark:text-gray-300 leading-relaxed">
          Discover curated items in <strong>{{ category.name }}</strong>; filters auto-apply, persist in the URL, and stay checked after refresh or navigation.
        </p>
      </section>

      <!-- Subcategories -->
      <section v-if="category.children?.length" class="mb-10">
        <h2 class="text-2xl font-bold mb-6">Subcategories</h2>
        <Swiper
            :slides-per-view="1"
            :space-between="20"
            :breakpoints="{ 640: { slidesPerView: 2 }, 768: { slidesPerView: 3 }, 1024: { slidesPerView: 4 }, 1280: { slidesPerView: 6 } }"
            class="my-6"
        >
          <SwiperSlide v-for="child in category.children" :key="child.url">
            <NuxtLink :to="`/category/${child.url}`" class="block hover:no-underline">
              <div class="bg-gray-100 dark:bg-gray-900 p-4 w-48 md:w-full md:h-60 rounded-lg shadow hover:shadow-lg transition hover:border-2 border-gray-400">
                <img :src="child.thumbnail" class="w-full h-32 object-cover rounded mb-4" alt="" />
                <h3 class="text-lg font-semibold">{{ child.name }}</h3>
                <p class="text-xs text-gray-500">{{ child.views }} views</p>
              </div>
            </NuxtLink>
          </SwiperSlide>
        </Swiper>
      </section>

      <div class="flex flex-row gap-6 items-center w-full my-4">
        <span class="grow"></span>
        <CartCounter />
      </div>

      <!-- Main -->
      <section class="mb-16 mt-2 w-full">
        <div class="flex flex-col md:flex-row gap-6">
          <!-- Sidebar -->
          <aside class="w-full md:w-1/4 space-y-4 md:sticky md:top-6 self-start">
            <div class="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg shadow">
              <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">Filters</h2>
                <button v-if="hasAnyFilter" @click="clearAll" class="text-xs text-blue-600 underline">Clear all</button>
              </div>

              <div class="md:hidden flex justify-between items-center mt-2 mb-2">
                <button @click="mobileFilterOpen = !mobileFilterOpen" class="flex items-center gap-2 text-blue-600 font-medium">
                  <Icon name="mdi:filter-variant" class="w-5 h-5" />
                  <span>{{ mobileFilterOpen ? 'Hide' : 'Show' }}</span>
                </button>
              </div>

              <div :class="{'hidden': !mobileFilterOpen, 'block': mobileFilterOpen, 'md:block': true}">
                <!-- Price -->
                <details class="mb-3 group" open>
                  <summary class="cursor-pointer flex items-center justify-between py-2 text-sm font-semibold uppercase">
                    <span>Price</span>
                    <Icon name="mdi:chevron-down" class="transition-transform group-open:rotate-180" />
                  </summary>
                  <div class="mt-2">
                    <div class="flex items-center gap-2">
                      <input type="number" inputmode="numeric" min="0" v-model.number="priceMin" placeholder="Min" class="w-1/2 px-2 py-1 rounded border text-sm bg-white dark:bg-gray-950" />
                      <span class="text-sm">to</span>
                      <input type="number" inputmode="numeric" min="0" v-model.number="priceMax" placeholder="Max" class="w-1/2 px-2 py-1 rounded border text-sm bg-white dark:bg-gray-950" />
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                      <button @click="quickPrice(0, 500)" class="text-xs px-2 py-1 rounded border hover:bg-gray-50 dark:hover:bg-gray-800">Under ₹500</button>
                      <button @click="quickPrice(500, 2000)" class="text-xs px-2 py-1 rounded border hover:bg-gray-50 dark:hover:bg-gray-800">₹500–₹2,000</button>
                      <button @click="quickPrice(2000, null)" class="text-xs px-2 py-1 rounded border hover:bg-gray-50 dark:hover:bg-gray-800">₹2,000+</button>
                    </div>
                  </div>
                </details>

                <!-- Offer & Availability -->
                <details class="mb-3 group" open>
                  <summary class="cursor-pointer flex items-center justify-between py-2 text-sm font-semibold uppercase">
                    <span>Offer & Availability</span>
                    <Icon name="mdi:chevron-down" class="transition-transform group-open:rotate-180" />
                  </summary>
                  <div class="space-y-2 mt-2">
                    <label class="flex items-center gap-2 text-sm">
                      <input type="checkbox" :checked="onOffer" @change="toggleFlag('offer', ($event.target as HTMLInputElement).checked)" />
                      <span>On offer</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                      <input type="checkbox" :checked="inStockOnly" @change="toggleFlag('in_stock', ($event.target as HTMLInputElement).checked)" />
                      <span>In stock</span>
                    </label>
                  </div>
                </details>

                <!-- Dynamic filter groups -->
                <template v-for="(filters, groupName) in filterGroups" :key="groupName">
                  <details class="mb-3 group" open>
                    <summary class="cursor-pointer flex items-center justify-between py-2 text-sm font-semibold uppercase">
                      <span>{{ groupName }}</span>
                      <Icon name="mdi:chevron-down" class="transition-transform group-open:rotate-180" />
                    </summary>

                    <div class="mt-1">
                      <div v-for="(optionsMap, filterName) in filters" :key="`${groupName}:${filterName}`" class="mb-3">
                        <div class="flex items-center justify-between">
                          <p class="text-xs font-medium">{{ filterName }}</p>
                          <input
                              v-if="shouldShowSearch(filterName, optionsMap)"
                              v-model="optionSearch[filterName]"
                              type="text"
                              placeholder="Search"
                              class="ml-2 px-2 py-1 text-xs rounded border bg-white dark:bg-gray-950"
                          />
                        </div>

                        <!-- Color swatches -->
                        <div v-if="isColorFilter(filterName)" class="mt-2 grid grid-cols-6 gap-2">
                          <button
                              v-for="(label, value) in filteredOptions(filterName, optionsMap)"
                              :key="`${filterName}:${value}`"
                              class="relative w-8 h-8 rounded border overflow-hidden"
                              :title="label"
                              @click="toggleColor(filterName, String(value))"
                          >
                            <span class="absolute inset-0" :style="{ backgroundColor: parseColor(value, label) }"></span>
                            <span v-if="isChecked(filterName, String(value))" class="absolute inset-0 ring-2 ring-blue-500 rounded pointer-events-none"></span>
                          </button>
                        </div>

                        <!-- Generic checkbox list -->
                        <div v-else class="mt-2 space-y-1 max-h-48 overflow-y-auto pr-1">
                          <label
                              v-for="(label, value) in filteredOptions(filterName, optionsMap)"
                              :key="`${filterName}:${value}`"
                              class="flex items-center justify-between gap-2 text-sm rounded px-2 py-1 hover:bg-gray-50 dark:hover:bg-gray-800"
                          >
                            <div class="flex items-center gap-2">
                              <input
                                  type="checkbox"
                                  :value="value"
                                  :checked="isChecked(filterName, String(value))"
                                  @change="onFilterToggle(filterName, String(value), ($event.target as HTMLInputElement).checked)"
                                  class="accent-blue-600"
                              />
                              <span class="truncate">{{ label }}</span>
                            </div>
                          </label>
                        </div>
                      </div>
                    </div>
                  </details>
                </template>
              </div>
            </div>
          </aside>

          <!-- Products -->
          <div class="w-full md:w-3/4">
            <!-- Applied chips -->
            <div v-if="hasAnyFilter" class="flex flex-wrap items-center gap-2 mb-3">
              <span class="text-sm text-gray-600 dark:text-gray-300 mr-1">Applied:</span>
              <template v-for="(values, fname) in selectedFilters" :key="`chip:${fname}`">
                <span
                    v-for="val in values"
                    :key="`chip:${fname}:${val}`"
                    class="inline-flex items-center gap-1 text-xs bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200 px-2 py-1 rounded border border-blue-200 dark:border-blue-800"
                >
                  <span class="truncate">{{ fname }}: {{ val }}</span>
                  <button @click="removeOne(fname, String(val))" class="hover:text-red-600" aria-label="Remove">✕</button>
                </span>
              </template>
              <template v-if="priceMin != null || priceMax != null">
                <span class="inline-flex items-center gap-1 text-xs bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200 px-2 py-1 rounded border border-blue-200 dark:border-blue-800">
                  <span>Price: {{ priceMin ?? 0 }}{{ priceMax != null ? `–${priceMax}` : '+' }}</span>
                  <button @click="clearPrice" class="hover:text-red-600" aria-label="Remove">✕</button>
                </span>
              </template>
              <template v-if="onOffer">
                <span class="inline-flex items-center gap-1 text-xs bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200 px-2 py-1 rounded border border-blue-200 dark:border-blue-800">
                  <span>On offer</span>
                  <button @click="setFlag('offer', false)" class="hover:text-red-600" aria-label="Remove">✕</button>
                </span>
              </template>
              <template v-if="inStockOnly">
                <span class="inline-flex items-center gap-1 text-xs bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200 px-2 py-1 rounded border border-blue-200 dark:border-blue-800">
                  <span>In stock</span>
                  <button @click="setFlag('in_stock', false)" class="hover:text-red-600" aria-label="Remove">✕</button>
                </span>
              </template>
              <button @click="clearAll" class="ml-auto text-xs text-blue-600 underline">Clear all</button>
            </div>

            <div class="flex justify-between items-center mb-4 gap-3">
              <h1 class="text-xl font-semibold">{{ category.name }} Products</h1>
              <div class="relative w-full md:w-auto">
                <label class="sr-only" for="sort">Sort By</label>
                <select
                    id="sort"
                    v-model="selectedSortName"
                    class="border border-gray-300 dark:border-gray-700 rounded px-4 py-2 text-sm bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 shadow-sm"
                >
                  <option v-for="sort in sortOptions" :key="sort.name" :value="sort.name">
                    {{ sort.name.replace(/([a-z])([A-Z])/g, '$1 $2').toUpperCase() }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              <div v-for="product in allProducts" :key="product.url" class="border border-gray-200 dark:border-gray-700 rounded p-4 hover:shadow-md transition">
                <NuxtLink :to="`/product/${product.url}`">
                  <div class="w-full h-52 mx-auto rounded mb-4">
                    <img loading="lazy" :src="product.thumbnail" class="h-full w-full object-contain rounded mb-4" alt="" />
                  </div>
                  <h3 class="font-semibold mb-1">{{ product.name }}</h3>
                  <p class="text-sm text-gray-500 mb-1">₹{{ product.price }}</p>
                  <p class="text-xs text-gray-400 mb-2">{{ product.short_description }}</p>
                </NuxtLink>
                <AddToCartButton :sku="product.sku" :quantity="1" class="mt-2 w-full" />
              </div>



            </div>

            <!-- Pagination -->
            <div class="text-center mt-8">
              <button
                  @click="nextPage"
                  :disabled="pagination.current_page >= pagination.last_page || paging"
                  class="px-6 py-2 border rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition disabled:opacity-50"
              >
                <span v-if="paging">Loading...</span>
                <span v-else>Load More</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Top Products -->
        <section v-if="topProducts.length" class="mt-16">
          <h2 class="text-2xl font-bold mb-6">Top Products</h2>
          <Swiper
              :slides-per-view="1"
              :space-between="20"
              :breakpoints="{ 640: { slidesPerView: 2 }, 768: { slidesPerView: 3 }, 1024: { slidesPerView: 4 }, 1280: { slidesPerView: 6 } }"
              class="my-6"
          >
            <SwiperSlide v-for="product in topProducts" :key="product.url">
              <div class="border border-gray-200 dark:border-gray-700 rounded p-4 hover:shadow-lg transition">
                <NuxtLink :to="`/product/${product.url}`" class="block">
                  <img loading="lazy" :src="product.thumbnail" class="h-[250px] w-full object-contain rounded mb-4" alt="" />
                  <h3 class="font-semibold mb-1">{{ product.name }}</h3>
                  <p class="text-sm text-gray-500 mb-1">₹{{ product.price }}</p>
                  <p class="text-xs text-gray-400 mb-2">{{ product.short_description }}</p>
                </NuxtLink>
                <AddToCartButton :sku="product.sku" :quantity="1" class="mt-2 w-full" />
              </div>
            </SwiperSlide>
          </Swiper>
        </section>
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSanctum, useSanctumFetch, useRuntimeConfig } from '#imports'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import GlobalLoader from "~/components/GlobalLoader.vue"
import AddToCartButton from "~/components/cart/AddToCartButton.vue"
import ProductCardComponent from "~/components/card/ProductCardComponent.vue";

const mobileFilterOpen = ref(false)
const paging = ref(false)
const route = useRoute()
const router = useRouter()
const { isLoggedIn } = useSanctum()
const config = useRuntimeConfig()
const categoryUrl = computed(() => route.params.url as string)

const isLoading = useState('pageLoading', () => false)
const error = ref<any>(null)
const category = ref<any>({})
const topProducts = ref<any[]>([])
const latestProducts = ref<any[]>([])
const allProducts = ref<any[]>([])
const pagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 12 })

const filterGroups = ref<Record<string, Record<string, Record<string, string>>>>({})
const selectedFilters = reactive<Record<string, string[]>>({})
const optionSearch = reactive<Record<string, string>>({})
const priceMin = ref<number | null>(null)
const priceMax = ref<number | null>(null)
const onOffer = ref<boolean>(false)
const inStockOnly = ref<boolean>(false)
type SortOption = { name: string; value: string; direction: 'asc' | 'desc' }
const sortOptions = ref<SortOption[]>([])
const selectedSortName = ref<string>('latest')

const selectedSort = computed<SortOption | null>(() => sortOptions.value.find(s => s.name === selectedSortName.value) || null)

// Track last fetched page to decide append vs replace
const lastFetchedPage = ref<number>(1)

function isColorFilter(name: string) {
  const n = name.toLowerCase()
  return n === 'color' || n === 'colour'
}
function parseColor(value: string, label: string) {
  const v = String(value || '').trim()
  const hex = /^#([0-9a-f]{3}|[0-9a-f]{6})$/i
  if (hex.test(v)) return v
  if (hex.test(label.trim())) return label.trim()
  return label
}

function shouldShowSearch(filterName: string, opt: Record<string, string>) {
  return ['Brand', 'Spice Type'].includes(filterName) && Object.keys(opt || {}).length > 8
}
function filteredOptions(filterName: string, opt: Record<string, string>) {
  const q = (optionSearch[filterName] || '').toLowerCase()
  if (!q) return opt
  const out: Record<string, string> = {}
  for (const [val, label] of Object.entries(opt)) if (String(val).toLowerCase().includes(q) || String(label).toLowerCase().includes(q)) out[val] = label
  return out
}
function isChecked(fname: string, val: string) { return (selectedFilters[fname] || []).includes(val) }

// Build desired query from state
function buildQueryObject(page: number) {
  const q: Record<string, any> = { page }
  if (selectedSort.value) q[`sort[${selectedSort.value.name}]`] = selectedSort.value.direction
  for (const [fname, values] of Object.entries(selectedFilters)) if (values?.length) q[`filters[${fname}][]`] = values
  if (priceMin.value != null) q['price[min]'] = priceMin.value
  if (priceMax.value != null) q['price[max]'] = priceMax.value
  if (onOffer.value) q['offer'] = 1
  if (inStockOnly.value) q['in_stock'] = 1
  return q
}

// Compare two queries shallowly (array order-insensitive)
function isSameQuery(a: any, b: any) {
  const ak = Object.keys(a || {})
  const bk = Object.keys(b || {})
  if (ak.length !== bk.length) return false
  for (const k of ak) {
    const av = a[k], bv = b[k]
    if (Array.isArray(av) || Array.isArray(bv)) {
      const aa = Array.isArray(av) ? av.map(String).sort() : []
      const bb = Array.isArray(bv) ? bv.map(String).sort() : []
      if (aa.length !== bb.length) return false
      for (let i=0;i<aa.length;i++) if (aa[i] !== bb[i]) return false
    } else if (String(av) !== String(bv)) return false
  }
  return true
}

// Push URL and refetch if nothing changed (guarantee request)
async function pushQuery(page = 1) {
  const desired = buildQueryObject(page)
  const current = route.query
  const changed = !isSameQuery(current, desired)
  if (changed) {
    await router.replace({ query: desired })
    // route watcher will hydrate and fetch
  } else {
    // Force fetch if router didn't change (safety net)
    hydrateFromRoute()
    const append = page > (lastFetchedPage.value || 1)
    await fetchCategory(page, append)
  }
}

// Hydrate from URL
function hydrateFromRoute() {
  // sort
  for (const [k, v] of Object.entries(route.query)) {
    const sm = k.match(/^sort\[(.+)\]$/)
    if (sm && typeof v === 'string') selectedSortName.value = sm[1]
  }
  // filters
  const fresh: Record<string, string[]> = {}
  for (const [k, v] of Object.entries(route.query)) {
    const fm = k.match(/^filters\[(.+)\](\[\])?$/)
    if (fm) fresh[fm[1]] = (Array.isArray(v) ? v : [String(v)]).map(String)
  }
  for (const key of Object.keys(selectedFilters)) delete selectedFilters[key]
  for (const [k, arr] of Object.entries(fresh)) selectedFilters[k] = arr
  // price/flags
  const pm = route.query['price[min]']; const px = route.query['price[max]']
  priceMin.value = pm != null ? Number(pm) : null
  priceMax.value = px != null ? Number(px) : null
  onOffer.value = route.query.offer != null ? String(route.query.offer) === '1' : false
  inStockOnly.value = route.query.in_stock != null ? String(route.query.in_stock) === '1' : false
}

async function fetchSortOptions() {
  const res = await useSanctumFetch(`${config.public.apiBase}/products/sorts/get`)
  sortOptions.value = (res || []) as SortOption[]
  const def = sortOptions.value.find(s => s.name === 'latest') || sortOptions.value[0]
  if (def) selectedSortName.value = def.name
}

async function fetchFilterOptions() {
  const res = await useSanctumFetch(`${config.public.apiBase}/products/filters/get?category=${encodeURIComponent(categoryUrl.value)}`)
  filterGroups.value = (res || {}) as Record<string, Record<string, Record<string, string>>>
}

async function fetchCategory(page = 1, append = false) {
  isLoading.value = !append
  if (append) paging.value = true
  error.value = null
  try {
    const params = new URLSearchParams()
    for (const [k, v] of Object.entries(route.query)) {
      if (Array.isArray(v)) v.forEach(val => params.append(k, String(val)))
      else params.set(k, String(v))
    }
    if (!params.has('page')) params.set('page', String(page))
    const url = `${config.public.apiBase}/categories/${categoryUrl.value}?${params.toString()}`
    const res = await useSanctumFetch(url)

    if (!append) {
      category.value = res.data || {}
      topProducts.value = res.top_products || []
      latestProducts.value = res.latest_products || []
      allProducts.value = res.all_products || []
    } else {
      const pageProducts = (res.all_products || []) as any[]
      allProducts.value = allProducts.value.concat(pageProducts)
    }

    if (res.pagination) pagination.value = res.pagination
    lastFetchedPage.value = Number(res?.pagination?.current_page || page || 1)
  } catch (e: any) {
    error.value = e
  } finally {
    isLoading.value = false
    paging.value = false
  }
}

// Derived
const hasAnyFilter = computed(() => {
  const any = Object.values(selectedFilters).some(arr => arr?.length)
  return any || priceMin.value != null || priceMax.value != null || onOffer.value || inStockOnly.value
})

// UI actions -> URL (then route watcher fetches, or fallback forces fetch)
function onFilterToggle(fname: string, value: string, checked: boolean) {
  const curr = selectedFilters[fname] ? [...selectedFilters[fname]] : []
  const idx = curr.indexOf(value)
  if (checked && idx === -1) curr.push(value)
  if (!checked && idx !== -1) curr.splice(idx, 1)
  selectedFilters[fname] = curr
  pushQuery(1)
}
function toggleColor(fname: string, value: string) {
  const curr = selectedFilters[fname] ? [...selectedFilters[fname]] : []
  const idx = curr.indexOf(value)
  if (idx === -1) curr.push(value); else curr.splice(idx, 1)
  selectedFilters[fname] = curr
  pushQuery(1)
}
function removeOne(fname: string, value: string) {
  if (!selectedFilters[fname]) return
  selectedFilters[fname] = selectedFilters[fname].filter(v => v !== value)
  if (selectedFilters[fname].length === 0) delete selectedFilters[fname]
  pushQuery(1)
}
function clearPrice() { priceMin.value = null; priceMax.value = null; pushQuery(1) }
function clearAll() {
  for (const k of Object.keys(selectedFilters)) delete selectedFilters[k]
  priceMin.value = null; priceMax.value = null; onOffer.value = false; inStockOnly.value = false
  pushQuery(1)
}
function quickPrice(min: number | null, max: number | null) { priceMin.value = min; priceMax.value = max; pushQuery(1) }
function toggleFlag(flag: 'offer'|'in_stock', v: boolean) {
  if (flag==='offer') onOffer.value = v; else inStockOnly.value = v
  pushQuery(1)
}
function setFlag(flag: 'offer'|'in_stock', v: boolean) { toggleFlag(flag, v) }
function nextPage() {
  const next = Math.min((pagination.value.current_page || 1) + 1, pagination.value.last_page || 1)
  pushQuery(next)
}

// Watch route.fullPath so any query change triggers rehydrate+fetch
watch(
    () => route.fullPath,
    async (cur, prev) => {
      // refresh filter options when category URL changes
      if (!prev || (prev.split('?')[0] !== cur.split('?')[0])) {
        await fetchFilterOptions()
        lastFetchedPage.value = 1
      }
      hydrateFromRoute()
      const page = Number(route.query.page || 1)
      const append = page > (lastFetchedPage.value || 1)
      await fetchCategory(page, append)
    },
    { immediate: true }
)

// Sort -> URL (which triggers fetch)
watch(selectedSortName, () => pushQuery(1))

// Boot: fetch sort options and initial filter options
onMounted(async () => {
  await fetchSortOptions()
  await fetchFilterOptions()
})
</script>
