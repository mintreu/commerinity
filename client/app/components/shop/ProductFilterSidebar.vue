<script setup lang="ts">
/**
 * Product Filter Sidebar (Flipkart/Amazon Style)
 * Features:
 * - Collapsible filter sections to save vertical space
 * - Price range with quick buttons
 * - Category filter with nested children (radio selection, scrollable)
 * - Availability (In Stock, On Sale)
 * - Rating filter
 * - Dynamic filter groups (Color, Size, etc.)
 * - Applied filters chips with clear
 * - Mobile drawer support
 * - Configurable filter visibility
 */

interface FilterOption {
  id: number
  value: string
  swatch: string | null
  count: number
}

interface FilterGroup {
  name: string
  options: FilterOption[]
}

interface CategoryChild {
  name: string
  slug: string
  product_count: number
  total_products?: number
  children?: CategoryChild[]
}

interface Category {
  name: string
  slug: string
  product_count?: number
  total_products?: number
  thumbnail?: string
  children?: CategoryChild[]
}

interface FilterConfig {
  showSearch?: boolean
  showCategories?: boolean
  showPrice?: boolean
  showAvailability?: boolean
  showRating?: boolean
  showDynamicFilters?: boolean
}

interface Props {
  priceRange: { min: number; max: number }
  filterGroups: FilterGroup[]
  categories: Category[]
  loading?: boolean
  filterConfig?: FilterConfig
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  filterConfig: () => ({
    showSearch: true,
    showCategories: true,
    showPrice: true,
    showAvailability: true,
    showRating: true,
    showDynamicFilters: true
  })
})

const emit = defineEmits<{
  (e: 'update:filters', filters: Record<string, any>): void
  (e: 'clear'): void
}>()

// Filter state
const searchQuery = ref('')
const selectedCategory = ref('')
const priceMin = ref<number | null>(null)
const priceMax = ref<number | null>(null)
const inStockOnly = ref(false)
const onSaleOnly = ref(false)
const minRating = ref<number | null>(null)
const selectedFilters = ref<Record<string, number[]>>({})

// Category expansion state
const expandedCategories = ref<Set<string>>(new Set())

// Section collapse state (default: categories and price open, others closed)
const collapsedSections = ref<Set<string>>(new Set(['rating', 'availability']))

// Mobile drawer state
const isMobileFilterOpen = ref(false)

// Toggle section collapse
const toggleSection = (section: string) => {
  if (collapsedSections.value.has(section)) {
    collapsedSections.value.delete(section)
  } else {
    collapsedSections.value.add(section)
  }
}

// Check if section is expanded
const isSectionExpanded = (section: string) => !collapsedSections.value.has(section)

// Quick price ranges
const quickPriceRanges = [
  { label: 'Under 500', min: 0, max: 500 },
  { label: '500-2K', min: 500, max: 2000 },
  { label: '2K-5K', min: 2000, max: 5000 },
  { label: '5K+', min: 5000, max: null }
]

// Rating options
const ratingOptions = [
  { value: 4, label: '4+ Stars' },
  { value: 3, label: '3+ Stars' },
  { value: 2, label: '2+ Stars' }
]

// Color detection for swatch display
const isColorFilter = (filterName: string): boolean => {
  const colorKeywords = ['color', 'colour', 'shade', 'tint']
  return colorKeywords.some(keyword => filterName.toLowerCase().includes(keyword))
}

// Parse color value to display color swatch
const getSwatchColor = (swatch: string | null, value: string): string => {
  if (swatch && swatch !== 'multi-color') return swatch

  const colorMap: Record<string, string> = {
    red: '#ef4444', blue: '#3b82f6', green: '#10b981', yellow: '#eab308',
    orange: '#f97316', purple: '#a855f7', pink: '#ec4899', black: '#1f2937',
    white: '#f9fafb', gray: '#6b7280', brown: '#92400e', navy: '#1e3a5a',
    maroon: '#7f1d1d', gold: '#d97706', silver: '#9ca3af', beige: '#d4c4a8'
  }

  const colorStr = value.toLowerCase().trim()
  return colorMap[colorStr] || '#6b7280'
}

// Toggle category expansion
const toggleCategoryExpand = (slug: string) => {
  if (expandedCategories.value.has(slug)) {
    expandedCategories.value.delete(slug)
  } else {
    expandedCategories.value.add(slug)
  }
}

// Select category (radio behavior)
const selectCategory = (slug: string) => {
  selectedCategory.value = slug
  emitFilters()
}

// Computed: Check if any filter is active
const hasActiveFilters = computed(() => {
  return searchQuery.value ||
    selectedCategory.value ||
    priceMin.value !== null ||
    priceMax.value !== null ||
    inStockOnly.value ||
    onSaleOnly.value ||
    minRating.value !== null ||
    Object.values(selectedFilters.value).some(arr => arr.length > 0)
})

// Computed: Active filter count
const activeFilterCount = computed(() => {
  let count = 0
  if (searchQuery.value) count++
  if (selectedCategory.value) count++
  if (priceMin.value !== null || priceMax.value !== null) count++
  if (inStockOnly.value) count++
  if (onSaleOnly.value) count++
  if (minRating.value !== null) count++
  for (const ids of Object.values(selectedFilters.value)) {
    if (Array.isArray(ids)) count += ids.length
  }
  return count
})

// Computed: Applied filters for chips display
const appliedFilters = computed(() => {
  const chips: Array<{ key: string; label: string; color: string }> = []

  if (searchQuery.value) {
    chips.push({ key: 'search', label: `"${searchQuery.value}"`, color: 'blue' })
  }
  if (selectedCategory.value) {
    // Find category name (including nested)
    let catName = selectedCategory.value
    for (const cat of props.categories) {
      if (cat.slug === selectedCategory.value) {
        catName = cat.name
        break
      }
      for (const child of cat.children || []) {
        if (child.slug === selectedCategory.value) {
          catName = child.name
          break
        }
        for (const grandchild of child.children || []) {
          if (grandchild.slug === selectedCategory.value) {
            catName = grandchild.name
            break
          }
        }
      }
    }
    chips.push({ key: 'category', label: catName, color: 'violet' })
  }
  if (priceMin.value !== null || priceMax.value !== null) {
    const min = priceMin.value || 0
    const max = priceMax.value ? `${priceMax.value}` : '+'
    chips.push({ key: 'price', label: `${min} - ${max}`, color: 'emerald' })
  }
  if (inStockOnly.value) {
    chips.push({ key: 'stock', label: 'In Stock', color: 'green' })
  }
  if (onSaleOnly.value) {
    chips.push({ key: 'sale', label: 'On Sale', color: 'red' })
  }
  if (minRating.value !== null) {
    chips.push({ key: 'rating', label: `${minRating.value}+ Stars`, color: 'amber' })
  }

  // Dynamic filters
  for (const [filterName, optionIds] of Object.entries(selectedFilters.value)) {
    if (optionIds.length > 0) {
      const group = props.filterGroups.find(g => g.name === filterName)
      for (const optionId of optionIds) {
        const opt = group?.options.find(o => o.id === optionId)
        if (opt) {
          chips.push({ key: `filter-${filterName}-${optionId}`, label: opt.value, color: 'purple' })
        }
      }
    }
  }

  return chips
})

// Toggle filter option
const toggleFilterOption = (filterName: string, optionId: number) => {
  if (!selectedFilters.value[filterName]) {
    selectedFilters.value[filterName] = []
  }

  const idx = selectedFilters.value[filterName].indexOf(optionId)
  if (idx > -1) {
    selectedFilters.value[filterName].splice(idx, 1)
  } else {
    selectedFilters.value[filterName].push(optionId)
  }

  emitFilters()
}

// Apply quick price range
const applyQuickPrice = (range: { min: number; max: number | null }) => {
  priceMin.value = range.min
  priceMax.value = range.max
  emitFilters()
}

// Clear individual filter
const clearFilter = (key: string) => {
  if (key === 'search') searchQuery.value = ''
  else if (key === 'category') selectedCategory.value = ''
  else if (key === 'price') { priceMin.value = null; priceMax.value = null }
  else if (key === 'stock') inStockOnly.value = false
  else if (key === 'sale') onSaleOnly.value = false
  else if (key === 'rating') minRating.value = null
  else if (key.startsWith('filter-')) {
    const parts = key.split('-')
    const filterName = parts[1]
    const optionId = parseInt(parts[2])
    if (selectedFilters.value[filterName]) {
      const idx = selectedFilters.value[filterName].indexOf(optionId)
      if (idx > -1) selectedFilters.value[filterName].splice(idx, 1)
    }
  }
  emitFilters()
}

// Clear all filters
const clearAllFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = ''
  priceMin.value = null
  priceMax.value = null
  inStockOnly.value = false
  onSaleOnly.value = false
  minRating.value = null
  selectedFilters.value = {}
  emit('clear')
}

// Emit filter changes
const emitFilters = () => {
  emit('update:filters', {
    search: searchQuery.value || undefined,
    category: selectedCategory.value || undefined,
    min_price: priceMin.value,
    max_price: priceMax.value,
    in_stock: inStockOnly.value || undefined,
    on_sale: onSaleOnly.value || undefined,
    min_rating: minRating.value,
    filters: Object.fromEntries(
      Object.entries(selectedFilters.value).filter(([_, v]) => v.length > 0)
    )
  })
}

// Watch and emit on changes (debounced)
let debounceTimer: ReturnType<typeof setTimeout> | null = null
watch([searchQuery, priceMin, priceMax, inStockOnly, onSaleOnly, minRating], () => {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    emitFilters()
  }, 300)
})

// Expose mobile drawer toggle
defineExpose({ isMobileFilterOpen, activeFilterCount })
</script>

<template>
  <!-- Mobile Filter Drawer -->
  <USlideover v-model:open="isMobileFilterOpen" side="left" class="lg:hidden">
    <template #header>
      <div class="flex items-center justify-between w-full">
        <div class="flex items-center gap-2">
          <h3 class="text-lg font-bold">Filters</h3>
          <span v-if="activeFilterCount > 0" class="px-2 py-0.5 text-xs bg-primary-500 text-white rounded-full">
            {{ activeFilterCount }}
          </span>
        </div>
        <UButton
          v-if="hasActiveFilters"
          variant="ghost"
          color="error"
          size="sm"
          @click="clearAllFilters"
        >
          Clear All
        </UButton>
      </div>
    </template>

    <div class="p-3 space-y-1 overflow-y-auto">
      <!-- Applied Filters Chips -->
      <div v-if="appliedFilters.length" class="flex flex-wrap gap-1.5 pb-2 mb-2 border-b border-slate-200 dark:border-slate-700">
        <span
          v-for="chip in appliedFilters"
          :key="chip.key"
          class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300"
        >
          {{ chip.label }}
          <button @click="clearFilter(chip.key)" class="ml-0.5 hover:text-red-500">
            <UIcon name="i-lucide-x" class="w-3 h-3" />
          </button>
        </span>
      </div>

      <!-- Search (Always visible) -->
      <div v-if="filterConfig.showSearch" class="pb-2">
        <UInput
          v-model="searchQuery"
          placeholder="Search products..."
          icon="i-lucide-search"
          size="sm"
        />
      </div>

      <!-- Categories (Collapsible) -->
      <div v-if="filterConfig.showCategories && categories.length" class="border-b border-slate-200 dark:border-slate-700">
        <button
          class="w-full flex items-center justify-between py-2 text-sm font-semibold text-slate-700 dark:text-slate-300"
          @click="toggleSection('categories')"
        >
          <span>Category</span>
          <UIcon
            :name="isSectionExpanded('categories') ? 'i-lucide-chevron-up' : 'i-lucide-chevron-down'"
            class="w-4 h-4 text-slate-400"
          />
        </button>
        <div v-show="isSectionExpanded('categories')" class="pb-3">
          <div class="max-h-48 overflow-y-auto space-y-0.5 scrollbar-thin">
            <!-- All Categories Option -->
            <label class="flex items-center gap-2 px-2 py-1 rounded cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800">
              <input
                type="radio"
                name="category-mobile"
                :checked="!selectedCategory"
                class="w-3.5 h-3.5 text-primary-600"
                @change="selectCategory('')"
              >
              <span class="text-xs font-medium text-slate-700 dark:text-slate-300">All</span>
            </label>
            <!-- Parent Categories -->
            <div v-for="cat in categories" :key="cat.slug" class="border-l border-slate-200 dark:border-slate-700 ml-1">
              <div class="flex items-center">
                <label class="flex-1 flex items-center gap-1.5 px-2 py-1 rounded cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800">
                  <input
                    type="radio"
                    name="category-mobile"
                    :checked="selectedCategory === cat.slug"
                    class="w-3.5 h-3.5 text-primary-600"
                    @change="selectCategory(cat.slug)"
                  >
                  <span class="text-xs text-slate-700 dark:text-slate-300 truncate">{{ cat.name }}</span>
                  <span class="text-[10px] text-slate-400">({{ cat.total_products || 0 }})</span>
                </label>
                <button
                  v-if="cat.children?.length"
                  class="p-0.5 hover:bg-slate-200 dark:hover:bg-slate-700 rounded"
                  @click.stop="toggleCategoryExpand(cat.slug)"
                >
                  <UIcon
                    :name="expandedCategories.has(cat.slug) ? 'i-lucide-chevron-down' : 'i-lucide-chevron-right'"
                    class="w-3 h-3 text-slate-400"
                  />
                </button>
              </div>
              <!-- Children -->
              <div v-if="expandedCategories.has(cat.slug) && cat.children?.length" class="ml-3 space-y-0.5">
                <div v-for="child in cat.children" :key="child.slug">
                  <label class="flex items-center gap-1.5 px-2 py-0.5 rounded cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800">
                    <input
                      type="radio"
                      name="category-mobile"
                      :checked="selectedCategory === child.slug"
                      class="w-3 h-3 text-primary-600"
                      @change="selectCategory(child.slug)"
                    >
                    <span class="text-[11px] text-slate-600 dark:text-slate-400 truncate">{{ child.name }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Price Range (Collapsible) -->
      <div v-if="filterConfig.showPrice" class="border-b border-slate-200 dark:border-slate-700">
        <button
          class="w-full flex items-center justify-between py-2 text-sm font-semibold text-slate-700 dark:text-slate-300"
          @click="toggleSection('price')"
        >
          <span>Price</span>
          <UIcon
            :name="isSectionExpanded('price') ? 'i-lucide-chevron-up' : 'i-lucide-chevron-down'"
            class="w-4 h-4 text-slate-400"
          />
        </button>
        <div v-show="isSectionExpanded('price')" class="pb-3">
          <div class="flex gap-2 mb-2">
            <UInput v-model.number="priceMin" type="number" placeholder="Min" :min="0" size="xs" />
            <span class="self-center text-slate-400 text-xs">-</span>
            <UInput v-model.number="priceMax" type="number" placeholder="Max" :min="0" size="xs" />
          </div>
          <div class="flex flex-wrap gap-1">
            <button
              v-for="range in quickPriceRanges"
              :key="range.label"
              :class="[
                'px-2 py-0.5 text-[10px] font-medium rounded-full border transition-colors',
                priceMin === range.min && priceMax === range.max
                  ? 'bg-primary-500 text-white border-primary-500'
                  : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700'
              ]"
              @click="applyQuickPrice(range)"
            >
              {{ range.label }}
            </button>
          </div>
        </div>
      </div>

      <!-- Availability (Collapsible) -->
      <div v-if="filterConfig.showAvailability" class="border-b border-slate-200 dark:border-slate-700">
        <button
          class="w-full flex items-center justify-between py-2 text-sm font-semibold text-slate-700 dark:text-slate-300"
          @click="toggleSection('availability')"
        >
          <span>Availability</span>
          <UIcon
            :name="isSectionExpanded('availability') ? 'i-lucide-chevron-up' : 'i-lucide-chevron-down'"
            class="w-4 h-4 text-slate-400"
          />
        </button>
        <div v-show="isSectionExpanded('availability')" class="pb-3 space-y-1.5">
          <label class="flex items-center gap-2 cursor-pointer text-xs">
            <input v-model="inStockOnly" type="checkbox" class="w-3.5 h-3.5 text-primary-600 rounded">
            <span class="text-slate-700 dark:text-slate-300">In Stock Only</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer text-xs">
            <input v-model="onSaleOnly" type="checkbox" class="w-3.5 h-3.5 text-primary-600 rounded">
            <span class="text-slate-700 dark:text-slate-300">On Sale</span>
          </label>
        </div>
      </div>

      <!-- Rating (Collapsible) -->
      <div v-if="filterConfig.showRating" class="border-b border-slate-200 dark:border-slate-700">
        <button
          class="w-full flex items-center justify-between py-2 text-sm font-semibold text-slate-700 dark:text-slate-300"
          @click="toggleSection('rating')"
        >
          <span>Rating</span>
          <UIcon
            :name="isSectionExpanded('rating') ? 'i-lucide-chevron-up' : 'i-lucide-chevron-down'"
            class="w-4 h-4 text-slate-400"
          />
        </button>
        <div v-show="isSectionExpanded('rating')" class="pb-3 space-y-0.5">
          <button
            v-for="opt in ratingOptions"
            :key="opt.value"
            :class="[
              'w-full flex items-center gap-1.5 px-2 py-1 rounded text-xs transition-colors',
              minRating === opt.value
                ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700'
                : 'hover:bg-slate-100 dark:hover:bg-slate-800'
            ]"
            @click="minRating = minRating === opt.value ? null : opt.value"
          >
            <UIcon v-for="i in opt.value" :key="i" name="i-lucide-star" class="w-3 h-3 text-amber-400 fill-amber-400" />
            <span class="text-[10px]">& Up</span>
          </button>
        </div>
      </div>

      <!-- Dynamic Filter Groups (Collapsible) -->
      <template v-if="filterConfig.showDynamicFilters">
        <div v-for="group in filterGroups" :key="group.name" class="border-b border-slate-200 dark:border-slate-700">
          <button
            class="w-full flex items-center justify-between py-2 text-sm font-semibold text-slate-700 dark:text-slate-300"
            @click="toggleSection(`filter-${group.name}`)"
          >
            <span>
              {{ group.name }}
              <span v-if="selectedFilters[group.name]?.length" class="ml-1 text-[10px] text-primary-500">
                ({{ selectedFilters[group.name].length }})
              </span>
            </span>
            <UIcon
              :name="isSectionExpanded(`filter-${group.name}`) ? 'i-lucide-chevron-up' : 'i-lucide-chevron-down'"
              class="w-4 h-4 text-slate-400"
            />
          </button>
          <div v-show="isSectionExpanded(`filter-${group.name}`)" class="pb-3">
            <!-- Color Swatch -->
            <div v-if="isColorFilter(group.name)" class="flex flex-wrap gap-1.5">
              <button
                v-for="option in group.options"
                :key="option.id"
                :class="[
                  'w-6 h-6 rounded-full border-2 transition-all hover:scale-110 relative',
                  selectedFilters[group.name]?.includes(option.id)
                    ? 'ring-2 ring-primary-500 ring-offset-1'
                    : 'border-slate-300 dark:border-slate-600'
                ]"
                :style="{ backgroundColor: getSwatchColor(option.swatch, option.value) }"
                :title="`${option.value} (${option.count})`"
                @click="toggleFilterOption(group.name, option.id)"
              />
            </div>
            <!-- Checkbox -->
            <div v-else class="space-y-0.5 max-h-28 overflow-y-auto scrollbar-thin">
              <label
                v-for="option in group.options"
                :key="option.id"
                class="flex items-center gap-1.5 cursor-pointer text-[11px] px-1 py-0.5 rounded hover:bg-slate-50 dark:hover:bg-slate-800"
              >
                <input
                  type="checkbox"
                  :checked="selectedFilters[group.name]?.includes(option.id)"
                  class="w-3 h-3 text-primary-600 rounded"
                  @change="toggleFilterOption(group.name, option.id)"
                >
                <span class="flex-1 text-slate-700 dark:text-slate-300 truncate">{{ option.value }}</span>
                <span class="text-[10px] text-slate-400">({{ option.count }})</span>
              </label>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- Apply Button (Mobile) -->
    <template #footer>
      <div class="flex gap-2 p-3 border-t border-slate-200 dark:border-slate-700">
        <UButton variant="outline" size="sm" class="flex-1" @click="clearAllFilters">Clear</UButton>
        <UButton color="primary" size="sm" class="flex-1" @click="isMobileFilterOpen = false">
          Apply ({{ activeFilterCount }})
        </UButton>
      </div>
    </template>
  </USlideover>

  <!-- Desktop Sidebar -->
  <aside class="hidden lg:block w-56 shrink-0 self-stretch">
    <div class="sticky top-20 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm max-h-[calc(100vh-6rem)] min-h-[400px] overflow-y-auto scrollbar-thin">
      <!-- Header -->
      <div class="flex items-center justify-between p-3 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
          <UIcon name="i-lucide-sliders-horizontal" class="w-4 h-4" />
          Filters
          <span v-if="activeFilterCount > 0" class="px-1.5 py-0.5 text-[10px] bg-primary-500 text-white rounded-full">
            {{ activeFilterCount }}
          </span>
        </h3>
        <button
          v-if="hasActiveFilters"
          class="text-[10px] text-red-500 hover:text-red-600 font-medium"
          @click="clearAllFilters"
        >
          Clear
        </button>
      </div>

      <!-- Applied Filters Chips -->
      <div v-if="appliedFilters.length" class="flex flex-wrap gap-1 p-2 border-b border-slate-200 dark:border-slate-700">
        <span
          v-for="chip in appliedFilters"
          :key="chip.key"
          class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300"
        >
          {{ chip.label }}
          <button @click="clearFilter(chip.key)" class="hover:text-red-500">
            <UIcon name="i-lucide-x" class="w-2.5 h-2.5" />
          </button>
        </span>
      </div>

      <div class="p-2 space-y-0">
        <!-- Search -->
        <div v-if="filterConfig.showSearch" class="pb-2">
          <UInput
            v-model="searchQuery"
            placeholder="Search..."
            icon="i-lucide-search"
            size="xs"
          />
        </div>

        <!-- Categories (Collapsible) -->
        <div v-if="filterConfig.showCategories && categories.length" class="border-t border-slate-200 dark:border-slate-700">
          <button
            class="w-full flex items-center justify-between py-2 text-xs font-semibold text-slate-700 dark:text-slate-300"
            @click="toggleSection('categories')"
          >
            <span>Category</span>
            <UIcon
              :name="isSectionExpanded('categories') ? 'i-lucide-chevron-up' : 'i-lucide-chevron-down'"
              class="w-3.5 h-3.5 text-slate-400"
            />
          </button>
          <div v-show="isSectionExpanded('categories')" class="pb-2">
            <div class="max-h-40 overflow-y-auto space-y-0.5 scrollbar-thin">
              <!-- All Categories Option -->
              <label
                :class="[
                  'flex items-center gap-1.5 px-2 py-1 rounded cursor-pointer transition-colors text-[11px]',
                  !selectedCategory
                    ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 font-medium'
                    : 'hover:bg-slate-100 dark:hover:bg-slate-800'
                ]"
              >
                <input
                  type="radio"
                  name="category-desktop"
                  :checked="!selectedCategory"
                  class="w-3 h-3 text-primary-600"
                  @change="selectCategory('')"
                >
                <span>All Categories</span>
              </label>
              <!-- Parent Categories -->
              <div v-for="cat in categories" :key="cat.slug" class="border-l border-slate-200 dark:border-slate-700 ml-1">
                <div class="flex items-center">
                  <label
                    :class="[
                      'flex-1 flex items-center gap-1 px-1.5 py-0.5 rounded cursor-pointer transition-colors text-[11px]',
                      selectedCategory === cat.slug
                        ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 font-medium'
                        : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300'
                    ]"
                  >
                    <input
                      type="radio"
                      name="category-desktop"
                      :checked="selectedCategory === cat.slug"
                      class="w-3 h-3 text-primary-600"
                      @change="selectCategory(cat.slug)"
                    >
                    <span class="truncate">{{ cat.name }}</span>
                    <span class="text-[9px] text-slate-400">({{ cat.total_products || 0 }})</span>
                  </label>
                  <button
                    v-if="cat.children?.length"
                    class="p-0.5 hover:bg-slate-200 dark:hover:bg-slate-700 rounded"
                    @click.stop="toggleCategoryExpand(cat.slug)"
                  >
                    <UIcon
                      :name="expandedCategories.has(cat.slug) ? 'i-lucide-chevron-down' : 'i-lucide-chevron-right'"
                      class="w-3 h-3 text-slate-400"
                    />
                  </button>
                </div>
                <!-- Children -->
                <div v-if="expandedCategories.has(cat.slug) && cat.children?.length" class="ml-2 space-y-0.5">
                  <label
                    v-for="child in cat.children"
                    :key="child.slug"
                    :class="[
                      'flex items-center gap-1 px-1.5 py-0.5 rounded cursor-pointer transition-colors text-[10px]',
                      selectedCategory === child.slug
                        ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 font-medium'
                        : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600'
                    ]"
                  >
                    <input
                      type="radio"
                      name="category-desktop"
                      :checked="selectedCategory === child.slug"
                      class="w-2.5 h-2.5 text-primary-600"
                      @change="selectCategory(child.slug)"
                    >
                    <span class="truncate">{{ child.name }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Price Range (Collapsible) -->
        <div v-if="filterConfig.showPrice" class="border-t border-slate-200 dark:border-slate-700">
          <button
            class="w-full flex items-center justify-between py-2 text-xs font-semibold text-slate-700 dark:text-slate-300"
            @click="toggleSection('price')"
          >
            <span>Price</span>
            <UIcon
              :name="isSectionExpanded('price') ? 'i-lucide-chevron-up' : 'i-lucide-chevron-down'"
              class="w-3.5 h-3.5 text-slate-400"
            />
          </button>
          <div v-show="isSectionExpanded('price')" class="pb-2">
            <div class="flex gap-1 mb-1.5">
              <UInput v-model.number="priceMin" type="number" placeholder="Min" :min="0" size="xs" class="text-[10px]" />
              <span class="self-center text-slate-400 text-[10px]">-</span>
              <UInput v-model.number="priceMax" type="number" placeholder="Max" :min="0" size="xs" class="text-[10px]" />
            </div>
            <div class="flex flex-wrap gap-1">
              <button
                v-for="range in quickPriceRanges"
                :key="range.label"
                :class="[
                  'px-1.5 py-0.5 text-[9px] font-medium rounded-full border transition-colors',
                  priceMin === range.min && priceMax === range.max
                    ? 'bg-primary-500 text-white border-primary-500'
                    : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-primary-300'
                ]"
                @click="applyQuickPrice(range)"
              >
                {{ range.label }}
              </button>
            </div>
          </div>
        </div>

        <!-- Availability (Collapsible) -->
        <div v-if="filterConfig.showAvailability" class="border-t border-slate-200 dark:border-slate-700">
          <button
            class="w-full flex items-center justify-between py-2 text-xs font-semibold text-slate-700 dark:text-slate-300"
            @click="toggleSection('availability')"
          >
            <span>Availability</span>
            <UIcon
              :name="isSectionExpanded('availability') ? 'i-lucide-chevron-up' : 'i-lucide-chevron-down'"
              class="w-3.5 h-3.5 text-slate-400"
            />
          </button>
          <div v-show="isSectionExpanded('availability')" class="pb-2 space-y-1">
            <label class="flex items-center gap-1.5 cursor-pointer text-[11px]">
              <input v-model="inStockOnly" type="checkbox" class="w-3 h-3 text-primary-600 rounded">
              <span class="text-slate-700 dark:text-slate-300">In Stock</span>
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer text-[11px]">
              <input v-model="onSaleOnly" type="checkbox" class="w-3 h-3 text-primary-600 rounded">
              <span class="text-slate-700 dark:text-slate-300">On Sale</span>
            </label>
          </div>
        </div>

        <!-- Rating (Collapsible) -->
        <div v-if="filterConfig.showRating" class="border-t border-slate-200 dark:border-slate-700">
          <button
            class="w-full flex items-center justify-between py-2 text-xs font-semibold text-slate-700 dark:text-slate-300"
            @click="toggleSection('rating')"
          >
            <span>Rating</span>
            <UIcon
              :name="isSectionExpanded('rating') ? 'i-lucide-chevron-up' : 'i-lucide-chevron-down'"
              class="w-3.5 h-3.5 text-slate-400"
            />
          </button>
          <div v-show="isSectionExpanded('rating')" class="pb-2 space-y-0.5">
            <button
              v-for="opt in ratingOptions"
              :key="opt.value"
              :class="[
                'w-full flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] transition-colors',
                minRating === opt.value
                  ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700'
                  : 'hover:bg-slate-100 dark:hover:bg-slate-800'
              ]"
              @click="minRating = minRating === opt.value ? null : opt.value"
            >
              <UIcon v-for="i in opt.value" :key="i" name="i-lucide-star" class="w-2.5 h-2.5 text-amber-400 fill-amber-400" />
              <span class="text-[9px]">& Up</span>
            </button>
          </div>
        </div>

        <!-- Dynamic Filter Groups (Collapsible) -->
        <template v-if="filterConfig.showDynamicFilters">
          <div v-for="group in filterGroups" :key="group.name" class="border-t border-slate-200 dark:border-slate-700">
            <button
              class="w-full flex items-center justify-between py-2 text-xs font-semibold text-slate-700 dark:text-slate-300"
              @click="toggleSection(`filter-${group.name}`)"
            >
              <span>
                {{ group.name }}
                <span v-if="selectedFilters[group.name]?.length" class="ml-0.5 text-[9px] text-primary-500">
                  ({{ selectedFilters[group.name].length }})
                </span>
              </span>
              <UIcon
                :name="isSectionExpanded(`filter-${group.name}`) ? 'i-lucide-chevron-up' : 'i-lucide-chevron-down'"
                class="w-3.5 h-3.5 text-slate-400"
              />
            </button>
            <div v-show="isSectionExpanded(`filter-${group.name}`)" class="pb-2">
              <!-- Color Swatch -->
              <div v-if="isColorFilter(group.name)" class="flex flex-wrap gap-1">
                <button
                  v-for="option in group.options"
                  :key="option.id"
                  :class="[
                    'w-5 h-5 rounded-full border-2 transition-all hover:scale-110 relative',
                    selectedFilters[group.name]?.includes(option.id)
                      ? 'ring-2 ring-primary-500 ring-offset-1'
                      : 'border-slate-300 dark:border-slate-600'
                  ]"
                  :style="{ backgroundColor: getSwatchColor(option.swatch, option.value) }"
                  :title="`${option.value} (${option.count})`"
                  @click="toggleFilterOption(group.name, option.id)"
                />
              </div>
              <!-- Checkbox -->
              <div v-else class="space-y-0.5 max-h-24 overflow-y-auto scrollbar-thin">
                <label
                  v-for="option in group.options"
                  :key="option.id"
                  class="flex items-center gap-1 cursor-pointer text-[10px] px-1 py-0.5 rounded hover:bg-slate-50 dark:hover:bg-slate-800"
                >
                  <input
                    type="checkbox"
                    :checked="selectedFilters[group.name]?.includes(option.id)"
                    class="w-2.5 h-2.5 text-primary-600 rounded"
                    @change="toggleFilterOption(group.name, option.id)"
                  >
                  <span class="flex-1 text-slate-700 dark:text-slate-300 truncate">{{ option.value }}</span>
                  <span class="text-[9px] text-slate-400">({{ option.count }})</span>
                </label>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </aside>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar {
  width: 3px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.4);
  border-radius: 2px;
}
</style>
