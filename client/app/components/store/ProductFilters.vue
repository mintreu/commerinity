<script setup lang="ts">
/**
 * Product Filters Component (Flipkart/Amazon Style)
 * Sidebar filters with price range, sorting, filter options (color, size, etc.)
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

interface FilterOptions {
  price_range: {
    min: number
    max: number
  }
  sort_options: Array<{
    value: string
    label: string
  }>
  filter_options?: FilterGroup[]
}

interface Props {
  filters?: FilterOptions | null
  selectedCategory?: string
  selectedSort?: string
  priceMin?: number | null
  priceMax?: number | null
  selectedFilterOptions?: Record<string, number[]>
  loading?: boolean
  showSort?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  filters: null,
  selectedCategory: '',
  selectedSort: 'popularity',
  priceMin: null,
  priceMax: null,
  selectedFilterOptions: () => ({}),
  loading: false,
  showSort: true
})

const emit = defineEmits<{
  (e: 'update:selectedSort', value: string): void
  (e: 'update:priceMin', value: number | null): void
  (e: 'update:priceMax', value: number | null): void
  (e: 'update:selectedFilterOptions', value: Record<string, number[]>): void
  (e: 'applyFilters'): void
  (e: 'clearFilters'): void
}>()

// Local state for price inputs
const localPriceMin = ref(props.priceMin)
const localPriceMax = ref(props.priceMax)
const sortValue = ref(props.selectedSort)
const localFilterOptions = ref<Record<string, number[]>>({ ...props.selectedFilterOptions })
const syncingPriceFromProps = ref(false)

// Sync with props
watch(() => props.priceMin, (val) => {
  syncingPriceFromProps.value = true
  localPriceMin.value = val
  syncingPriceFromProps.value = false
})
watch(() => props.priceMax, (val) => {
  syncingPriceFromProps.value = true
  localPriceMax.value = val
  syncingPriceFromProps.value = false
})
watch(() => props.selectedSort, (val) => { sortValue.value = val })
watch(() => props.selectedFilterOptions, (val) => { localFilterOptions.value = { ...val } }, { deep: true })

// Apply price filter
const applyPriceFilter = () => {
  emit('update:priceMin', localPriceMin.value)
  emit('update:priceMax', localPriceMax.value)
  emit('applyFilters')
}

// Clear all filters
const clearAllFilters = () => {
  localPriceMin.value = null
  localPriceMax.value = null
  if (props.showSort) {
    sortValue.value = 'popularity'
    emit('update:selectedSort', 'popularity')
  }
  localFilterOptions.value = {}
  emit('update:priceMin', null)
  emit('update:priceMax', null)
  emit('update:selectedFilterOptions', {})
  emit('clearFilters')
}

// Update sort
const updateSort = (value: string) => {
  sortValue.value = value
  emit('update:selectedSort', value)
}

// Toggle filter option selection
const toggleFilterOption = (filterName: string, optionId: number) => {
  const current = localFilterOptions.value[filterName] || []
  const index = current.indexOf(optionId)

  if (index === -1) {
    localFilterOptions.value[filterName] = [...current, optionId]
  } else {
    localFilterOptions.value[filterName] = current.filter(id => id !== optionId)
    if (localFilterOptions.value[filterName].length === 0) {
      delete localFilterOptions.value[filterName]
    }
  }

  emit('update:selectedFilterOptions', { ...localFilterOptions.value })
  emit('applyFilters')
}

// Check if option is selected
const isOptionSelected = (filterName: string, optionId: number): boolean => {
  return (localFilterOptions.value[filterName] || []).includes(optionId)
}

const rupeeFormatter = new Intl.NumberFormat('en-IN', {
  style: 'currency',
  currency: 'INR',
  maximumFractionDigits: 2
})

const priceRangeLimits = computed(() => {
  const min = (props.filters?.price_range?.min ?? 0) / 100
  let max = (props.filters?.price_range?.max ?? 0) / 100
  if (max < min) {
    max = min
  }
  return { min, max }
})

const formatCurrencyValue = (value: number) => rupeeFormatter.format(value / 100)
const formatSliderValue = (value: number) => rupeeFormatter.format(value)

const priceRangeValue = computed<[number, number]>({
  get() {
    return [
      localPriceMin.value ?? priceRangeLimits.value.min,
      localPriceMax.value ?? priceRangeLimits.value.max
    ]
  },
  set([min, max]) {
    localPriceMin.value = min
    localPriceMax.value = max
    emit('update:priceMin', min)
    emit('update:priceMax', max)
    emit('applyFilters')
  }
})

// Check if any filter is active
const hasActiveFilters = computed(() => {
  return props.priceMin !== null
    || props.priceMax !== null
    || props.selectedSort !== 'popularity'
    || Object.keys(localFilterOptions.value).length > 0
})

// Get count of selected filter options
const selectedFilterCount = computed(() => {
  return Object.values(localFilterOptions.value).flat().length
})
</script>

<template>
  <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg flex flex-col h-full lg:max-h-[calc(100vh-4rem)] lg:min-h-[calc(100vh-6rem)]">
    <!-- Header -->
    <div class="flex items-center justify-between p-4 border-b border-slate-200/50 dark:border-slate-700/50">
      <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
        <UIcon
          name="i-lucide-filter"
          class="w-5 h-5 text-primary-500"
        />
        Filters
        <span
          v-if="selectedFilterCount"
          class="ml-1 px-2 py-0.5 text-xs bg-primary-500 text-white rounded-full"
        >
          {{ selectedFilterCount }}
        </span>
      </h3>
      <button
        v-if="hasActiveFilters"
        class="text-sm text-red-500 hover:text-red-600 font-medium"
        @click="clearAllFilters"
      >
        Clear All
      </button>
    </div>

    <div class="p-4 space-y-6 overflow-y-auto flex-1 max-h-[70vh] lg:max-h-[calc(100vh-4rem)]">
      <!-- Sort Options -->
      <div v-if="props.showSort">
        <h4 class="font-semibold text-slate-800 dark:text-slate-200 mb-3 text-sm uppercase tracking-wide">
          Sort By
        </h4>
        <div class="space-y-2">
          <button
            v-for="option in filters?.sort_options || []"
            :key="option.value"
            :class="[
              'w-full text-left px-3 py-2 rounded-lg text-sm transition-all',
              sortValue === option.value
                ? 'bg-primary-500 text-white font-medium'
                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'
            ]"
            @click="updateSort(option.value)"
          >
            {{ option.label }}
          </button>
        </div>
      </div>

      <!-- Price Range -->
      <div v-if="filters?.price_range">
        <h4 class="font-semibold text-slate-800 dark:text-slate-200 mb-3 text-sm uppercase tracking-wide">
          Price Range
        </h4>
        <div class="pb-3">
          <USlider
            v-model="priceRangeValue"
            :min="priceRangeLimits.min"
            :max="priceRangeLimits.max"
            range
            thumbs-size="lg"
            color="primary"
            step="0.01"
            class="h-2 [&_track]:bg-gradient-to-r [&_track]:from-slate-200 [&_track]:to-slate-300 [&_thumb]:bg-gradient-to-r [&_thumb]:from-primary-500 [&_thumb]:to-primary-600 [&_thumb]:shadow-lg [&_thumb]:ring-4 [&_thumb]:ring-white/50"
          />
          <div class="flex justify-between text-xs text-slate-500 mt-2 px-1">
            {{ formatSliderValue(localPriceMin ?? priceRangeLimits.min) }} - {{ formatSliderValue(localPriceMax ?? priceRangeLimits.max) }}
          </div>
        </div>

        <!-- Custom Price Input -->
        <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
          <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">
            Custom Range
          </p>
          <div class="flex items-center gap-2">
            <UInput
              v-model.number="localPriceMin"
              type="number"
              placeholder="Min"
              size="sm"
              :min="0"
              class="w-full"
            />
            <span class="text-slate-400">-</span>
            <UInput
              v-model.number="localPriceMax"
              type="number"
              placeholder="Max"
              size="sm"
              :min="localPriceMin || 0"
              class="w-full"
            />
          </div>
          <UButton
            size="sm"
            color="primary"
            variant="outline"
            class="w-full mt-2"
            @click="applyPriceFilter"
          >
            Apply
          </UButton>
        </div>

        <!-- Price Range Display -->
        <p class="text-xs text-slate-400 mt-2">
          Range: {{ formatCurrencyValue(filters.price_range.min) }} - {{ formatCurrencyValue(filters.price_range.max) }}
        </p>
      </div>

      <!-- Dynamic Filter Options (Color, Size, etc.) - Flipkart Style -->
      <div
        v-for="filterGroup in filters?.filter_options || []"
        :key="filterGroup.name"
      >
        <h4 class="font-semibold text-slate-800 dark:text-slate-200 mb-3 text-sm uppercase tracking-wide flex items-center gap-2">
          <UIcon
            v-if="filterGroup.name.toLowerCase() === 'color'"
            name="i-lucide-palette"
            class="w-4 h-4"
          />
          <UIcon
            v-else-if="filterGroup.name.toLowerCase() === 'size'"
            name="i-lucide-ruler"
            class="w-4 h-4"
          />
          <UIcon
            v-else-if="filterGroup.name.toLowerCase() === 'weight'"
            name="i-lucide-weight"
            class="w-4 h-4"
          />
          <UIcon
            v-else
            name="i-lucide-tag"
            class="w-4 h-4"
          />
          {{ filterGroup.name }}
        </h4>

        <!-- Color Swatch Style (for color filters) -->
        <div
          v-if="filterGroup.name.toLowerCase() === 'color'"
          class="flex flex-wrap gap-2"
        >
          <button
            v-for="option in filterGroup.options"
            :key="option.id"
            :title="`${option.value} (${option.count})`"
            :class="[
              'w-8 h-8 rounded-full border-2 transition-all relative',
              isOptionSelected(filterGroup.name, option.id)
                ? 'border-primary-500 ring-2 ring-primary-500/30 scale-110'
                : 'border-slate-300 dark:border-slate-600 hover:border-primary-300'
            ]"
            :style="option.swatch ? { backgroundColor: option.swatch } : {}"
            @click="toggleFilterOption(filterGroup.name, option.id)"
          >
            <UIcon
              v-if="isOptionSelected(filterGroup.name, option.id)"
              name="i-lucide-check"
              class="w-4 h-4 text-white absolute inset-0 m-auto drop-shadow-lg"
            />
            <span
              v-if="!option.swatch"
              class="text-xs font-bold"
            >{{ option.value.charAt(0) }}</span>
          </button>
        </div>

        <!-- Checkbox Style (for other filters) -->
        <div
          v-else
          class="space-y-2"
        >
          <label
            v-for="option in filterGroup.options"
            :key="option.id"
            :class="[
              'flex items-center gap-3 px-3 py-2 rounded-lg cursor-pointer transition-all',
              isOptionSelected(filterGroup.name, option.id)
                ? 'bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-800'
                : 'hover:bg-slate-50 dark:hover:bg-slate-800'
            ]"
          >
            <input
              type="checkbox"
              :checked="isOptionSelected(filterGroup.name, option.id)"
              class="w-4 h-4 rounded border-slate-300 text-primary-500 focus:ring-primary-500"
              @change="toggleFilterOption(filterGroup.name, option.id)"
            >
            <span class="flex-1 text-sm text-slate-700 dark:text-slate-300">
              {{ option.value }}
            </span>
            <span class="text-xs text-slate-400 dark:text-slate-500">
              ({{ option.count }})
            </span>
          </label>
        </div>
      </div>

      <!-- Loading State -->
      <div
        v-if="loading"
        class="space-y-3"
      >
        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded animate-pulse" />
        <div class="h-8 bg-slate-200 dark:bg-slate-700 rounded animate-pulse" />
        <div class="h-8 bg-slate-200 dark:bg-slate-700 rounded animate-pulse" />
      </div>
    </div>
  </div>
</template>
