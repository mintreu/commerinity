<template>
  <div class="relative z-10 w-full">

    <!-- Global Loader -->
    <GlobalLoader v-if="isLoading" />

    <!-- Error State -->
    <section v-else-if="error" class="flex flex-col items-center justify-center min-h-[80vh] px-4 relative z-10">
      <div class="max-w-lg mx-auto text-center">
        <div class="error-icon-container relative mb-8">
          <div class="w-24 h-24 mx-auto bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-full flex items-center justify-center shadow-2xl">
            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-orange-600 rounded-full flex items-center justify-center animate-pulse">
              <Icon name="mdi:store-alert" class="w-8 h-8 text-white" />
            </div>
          </div>
        </div>

        <div class="error-content bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-3xl p-8 shadow-xl border border-white/50 dark:border-slate-700/50">
          <h2 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white mb-4">
            Category Not Found
          </h2>
          <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
            {{ error }}
          </p>

          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button
                @click="refresh()"
                class="px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-blue-500/25 transition-all duration-300 transform hover:scale-105 group"
            >
              <Icon name="mdi:refresh" class="inline w-5 h-5 mr-3 group-hover:animate-spin" />
              Try Again
            </button>

            <NuxtLink
                to="/store"
                class="px-8 py-4 bg-white/10 backdrop-blur-md text-blue-600 dark:text-blue-400 font-bold rounded-2xl border-2 border-blue-200 dark:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-300 transform hover:scale-105 group"
            >
              <Icon name="mdi:store" class="inline w-5 h-5 mr-3 group-hover:scale-110 transition-transform duration-300" />
              Browse Store
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <template v-else>

      <!-- Enhanced Hero Banner -->
      <section class="hero-banner relative overflow-hidden mb-8">
        <div class="relative h-64 lg:h-80">
          <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600">
            <div class="absolute inset-0 bg-black/40"></div>
          </div>

          <div class="hero-content relative z-10 h-full flex items-center justify-center px-4">
            <div class="text-center text-white max-w-4xl mx-auto">
              <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black mb-4 leading-tight">
                <span class="block bg-gradient-to-r from-white via-blue-100 to-white bg-clip-text text-transparent">
                  {{ categoryName }}
                </span>
              </h1>

              <p class="text-lg lg:text-xl text-blue-100 mb-6 opacity-90">
                Discover curated products with premium quality
              </p>

              <div class="category-stats flex flex-wrap justify-center gap-4 lg:gap-6">
                <div class="stat-item bg-white/10 backdrop-blur-lg rounded-2xl px-4 py-3 border border-white/20">
                  <div class="text-xl font-black">{{ pagination.total || 0 }}</div>
                  <div class="text-xs opacity-80">Products</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- Cart Counter -->
      <div class="cart-counter-section px-4 lg:px-6 mb-6 w-full">
        <div class="w-full mx-auto flex justify-end">
          <div class="cart-counter-container bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-slate-700/50">
            <CartCounter />
          </div>
        </div>
      </div>
      <!-- Breadcrumbs -->
      <section class="breadcrumbs-section px-4 lg:px-6 mb-6 mt-2">
        <div class="max-w-7xl mx-auto">
          <nav class="breadcrumbs bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl px-6 py-3 shadow-lg border border-white/50 dark:border-slate-700/50">
            <ol class="flex items-center space-x-2 text-sm">
              <li>
                <NuxtLink to="/store" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 font-medium">
                  <Icon name="mdi:home" class="inline w-4 h-4 mr-1" />
                  Store
                </NuxtLink>
              </li>
              <li class="text-slate-400">/</li>
              <li>
                <NuxtLink to="/categories" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 font-medium">
                  Categories
                </NuxtLink>
              </li>
              <li class="text-slate-400">/</li>
              <li class="text-slate-900 dark:text-white font-semibold">{{ categoryName }}</li>
            </ol>
          </nav>
        </div>
      </section>



      <!-- Main Products Section -->
      <section class="products-section px-4 lg:px-6 mb-12">
        <div class="max-w-9xl mx-auto">
          <div class="flex flex-col xl:flex-row gap-6">

            <!-- Enhanced Sidebar -->
            <aside class="filters-sidebar xl:w-1/4">
              <div class="filters-container bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-3xl shadow-xl border border-white/50 dark:border-slate-700/50 sticky top-6 overflow-hidden">

                <!-- Filters Header -->
                <div class="filters-header p-6 bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                  <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold flex items-center">
                      <Icon name="mdi:filter-variant" class="w-5 h-5 mr-2" />
                      Filters
                    </h2>
                    <button
                        v-if="hasAnyFilter"
                        @click="clearAll"
                        class="text-sm bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full backdrop-blur-sm transition-colors duration-200"
                    >
                      Clear All
                    </button>
                  </div>

                  <!-- Mobile Filter Toggle -->
                  <button
                      @click="mobileFilterOpen = !mobileFilterOpen"
                      class="xl:hidden w-full mt-3 flex items-center justify-center gap-2 bg-white/20 hover:bg-white/30 py-2 rounded-xl backdrop-blur-sm transition-colors duration-200"
                  >
                    <Icon name="mdi:tune" class="w-4 h-4" />
                    <span>{{ mobileFilterOpen ? 'Hide Filters' : 'Show Filters' }}</span>
                  </button>
                </div>

                <!-- Filters Content -->
                <div :class="{'hidden xl:block': !mobileFilterOpen, 'block': mobileFilterOpen}" class="filters-content p-4 space-y-4 max-h-[70vh] lg:max-h-screen overflow-y-auto">

                  <!-- Search Filter -->
                  <div class="filter-group">
                    <div class="filter-header mb-3">
                      <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center">
                        <Icon name="mdi:magnify" class="w-4 h-4 mr-2 text-blue-600 dark:text-blue-400" />
                        Search
                      </h3>
                    </div>
                    <input
                        type="text"
                        v-model="searchQuery"
                        @input="debounceSearch"
                        placeholder="Search products..."
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                    />
                  </div>

                  <!-- Price Filter -->
                  <div class="filter-group">
                    <div class="filter-header mb-3">
                      <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center">
                        <Icon name="mdi:currency-inr" class="w-4 h-4 mr-2 text-green-600 dark:text-green-400" />
                        Price Range
                      </h3>
                    </div>

                    <div class="price-inputs mb-3">
                      <div class="flex items-center gap-2">
                        <input
                            type="number"
                            min="0"
                            v-model.number="priceMin"
                            placeholder="Min"
                            class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                        />
                        <span class="text-slate-500 dark:text-slate-400 text-sm">to</span>
                        <input
                            type="number"
                            min="0"
                            v-model.number="priceMax"
                            placeholder="Max"
                            class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                        />
                      </div>
                    </div>

                    <!-- Quick Price Buttons -->
                    <div class="quick-price-buttons grid grid-cols-2 gap-2">
                      <button @click="quickPrice(0, 500)" class="px-2 py-1 text-xs bg-slate-100 dark:bg-slate-700 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-slate-700 dark:text-slate-300 rounded-lg transition-colors duration-200">Under ₹500</button>
                      <button @click="quickPrice(500, 2000)" class="px-2 py-1 text-xs bg-slate-100 dark:bg-slate-700 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-slate-700 dark:text-slate-300 rounded-lg transition-colors duration-200">₹500–₹2K</button>
                      <button @click="quickPrice(2000, 5000)" class="px-2 py-1 text-xs bg-slate-100 dark:bg-slate-700 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-slate-700 dark:text-slate-300 rounded-lg transition-colors duration-200">₹2K–₹5K</button>
                      <button @click="quickPrice(5000, null)" class="px-2 py-1 text-xs bg-slate-100 dark:bg-slate-700 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-slate-700 dark:text-slate-300 rounded-lg transition-colors duration-200">₹5K+</button>
                    </div>
                  </div>

                  <!-- Availability Filters -->
                  <div class="filter-group">
                    <div class="filter-header mb-3">
                      <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center">
                        <Icon name="mdi:package-variant" class="w-4 h-4 mr-2 text-blue-600 dark:text-blue-400" />
                        Availability
                      </h3>
                    </div>

                    <div class="availability-options space-y-2">
                      <label class="availability-option flex items-center gap-2 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="onOffer"
                            class="w-4 h-4 text-red-600 bg-slate-100 dark:bg-slate-700 border-slate-300 dark:border-slate-600 rounded focus:ring-red-500"
                        />
                        <div class="flex items-center">
                          <Icon name="mdi:tag" class="w-3 h-3 text-red-500 mr-1" />
                          <span class="text-slate-900 dark:text-white text-sm">On Sale</span>
                        </div>
                      </label>

                      <label class="availability-option flex items-center gap-2 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="inStockOnly"
                            class="w-4 h-4 text-green-600 bg-slate-100 dark:bg-slate-700 border-slate-300 dark:border-slate-600 rounded focus:ring-green-500"
                        />
                        <div class="flex items-center">
                          <Icon name="mdi:check-circle" class="w-3 h-3 text-green-500 mr-1" />
                          <span class="text-slate-900 dark:text-white text-sm">In Stock</span>
                        </div>
                      </label>
                    </div>
                  </div>

                  <!-- Rating Filter -->
                  <div class="filter-group">
                    <div class="filter-header mb-3">
                      <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center">
                        <Icon name="mdi:star" class="w-4 h-4 mr-2 text-yellow-600 dark:text-yellow-400" />
                        Minimum Rating
                      </h3>
                    </div>

                    <div class="rating-options space-y-2">
                      <button
                          v-for="rating in [4, 3, 2, 1]"
                          :key="rating"
                          @click="minRating = minRating === rating ? null : rating"
                          class="w-full flex items-center gap-2 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                          :class="minRating === rating ? 'bg-yellow-100 dark:bg-yellow-900/30' : ''"
                      >
                        <div class="flex items-center">
                          <Icon v-for="i in rating" :key="i" name="mdi:star" class="w-4 h-4 text-yellow-400" />
                          <Icon v-for="i in (5 - rating)" :key="`empty-${i}`" name="mdi:star-outline" class="w-4 h-4 text-gray-300" />
                        </div>
                        <span class="text-sm text-slate-700 dark:text-slate-300">& Up</span>
                      </button>
                    </div>
                  </div>

                  <!-- Dynamic Filter Groups (Restored) -->
                  <template v-for="(filters, groupName) in filterGroups" :key="groupName">
                    <div class="filter-group">
                      <div class="filter-header mb-3">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center">
                          <Icon name="mdi:tune" class="w-4 h-4 mr-2 text-purple-600 dark:text-purple-400" />
                          {{ groupName }}
                        </h3>
                      </div>

                      <div class="filter-options space-y-3">
                        <div v-for="(optionsMap, filterName) in filters" :key="`${groupName}:${filterName}`">

                          <!-- Search Input for large option lists -->
                          <div v-if="shouldShowSearch(filterName, optionsMap)" class="search-input mb-2">
                            <div class="relative">
                              <Icon name="mdi:magnify" class="absolute left-2 top-1/2 transform -translate-y-1/2 w-3 h-3 text-slate-400" />
                              <input
                                  v-model="optionSearch[filterName]"
                                  type="text"
                                  :placeholder="`Search ${filterName}...`"
                                  class="w-full pl-8 pr-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-purple-500 transition-colors duration-200 text-sm"
                              />
                            </div>
                          </div>

                          <!-- Filter Label -->
                          <div class="filter-label mb-2">
                            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">{{ filterName }}</h4>
                          </div>

                          <!-- Color Swatches (for color filters) -->
                          <div v-if="isColorFilter(filterName)" class="color-swatches grid grid-cols-6 gap-1">
                            <button
                                v-for="(label, value) in filteredOptions(filterName, optionsMap)"
                                :key="`${filterName}:${value}`"
                                class="color-swatch w-8 h-8 rounded-lg border-2 overflow-hidden transition-all duration-200 hover:scale-110"
                                :class="isChecked(filterName, String(value)) ? 'border-blue-500 ring-2 ring-blue-500/50' : 'border-slate-300 dark:border-slate-600'"
                                :title="label"
                                @click="toggleColor(filterName, String(value))"
                            >
                              <span class="block w-full h-full" :style="{ backgroundColor: parseColor(value, label) }"></span>
                            </button>
                          </div>

                          <!-- Generic Options -->
                          <div v-else class="filter-options-list max-h-32 overflow-y-auto space-y-1">
                            <label
                                v-for="(label, value) in filteredOptions(filterName, optionsMap)"
                                :key="`${filterName}:${value}`"
                                class="filter-option flex items-center gap-2 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200 cursor-pointer"
                            >
                              <input
                                  type="checkbox"
                                  :value="value"
                                  :checked="isChecked(filterName, String(value))"
                                  @change="onFilterToggle(filterName, String(value), ($event.target as HTMLInputElement).checked)"
                                  class="w-4 h-4 text-purple-600 bg-slate-100 dark:bg-slate-700 border-slate-300 dark:border-slate-600 rounded focus:ring-purple-500 dark:focus:ring-purple-600 dark:ring-offset-slate-800 focus:ring-2"
                              />
                              <span class="text-slate-900 dark:text-white text-sm truncate">{{ label }}</span>
                            </label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </template>

                </div>
              </div>
            </aside>

            <!-- Products Content -->
            <div class="products-content xl:w-3/4">

              <!-- Applied Filters Chips -->
              <div v-if="hasAnyFilter" class="applied-filters mb-4">
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl p-3 shadow-lg border border-white/50 dark:border-slate-700/50">
                  <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300 mr-2">Active Filters:</span>

                    <!-- Search Chip -->
                    <template v-if="searchQuery">
                      <span class="filter-chip inline-flex items-center gap-1 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 px-2 py-1 rounded-full border border-blue-200 dark:border-blue-700">
                        <span>Search: {{ searchQuery }}</span>
                        <button @click="searchQuery = ''" class="hover:text-red-600 transition-colors duration-200">
                          <Icon name="mdi:close" class="w-3 h-3" />
                        </button>
                      </span>
                    </template>

                    <!-- Filter Chips -->
                    <template v-for="(values, fname) in selectedFilters" :key="`chip:${fname}`">
                      <span
                          v-for="val in values"
                          :key="`chip:${fname}:${val}`"
                          class="filter-chip inline-flex items-center gap-1 text-xs bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200 px-2 py-1 rounded-full border border-purple-200 dark:border-purple-700"
                      >
                        <span class="truncate">{{ fname }}: {{ val }}</span>
                        <button @click="removeOne(fname, String(val))" class="hover:text-red-600 transition-colors duration-200">
                          <Icon name="mdi:close" class="w-3 h-3" />
                        </button>
                      </span>
                    </template>

                    <!-- Price Chip -->
                    <template v-if="priceMin != null || priceMax != null">
                      <span class="filter-chip inline-flex items-center gap-1 text-xs bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 px-2 py-1 rounded-full border border-green-200 dark:border-green-700">
                        <span>₹{{ priceMin || 0 }}{{ priceMax != null ? `–₹${priceMax}` : '+' }}</span>
                        <button @click="clearPrice" class="hover:text-red-600 transition-colors duration-200">
                          <Icon name="mdi:close" class="w-3 h-3" />
                        </button>
                      </span>
                    </template>

                    <!-- Offer/Stock Chips -->
                    <template v-if="onOffer">
                      <span class="filter-chip inline-flex items-center gap-1 text-xs bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 px-2 py-1 rounded-full border border-red-200 dark:border-red-700">
                        <span>On Sale</span>
                        <button @click="onOffer = false" class="hover:text-red-600 transition-colors duration-200">
                          <Icon name="mdi:close" class="w-3 h-3" />
                        </button>
                      </span>
                    </template>

                    <template v-if="inStockOnly">
                      <span class="filter-chip inline-flex items-center gap-1 text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-200 px-2 py-1 rounded-full border border-emerald-200 dark:border-emerald-700">
                        <span>In Stock</span>
                        <button @click="inStockOnly = false" class="hover:text-red-600 transition-colors duration-200">
                          <Icon name="mdi:close" class="w-3 h-3" />
                        </button>
                      </span>
                    </template>

                    <!-- Rating Chip -->
                    <template v-if="minRating">
                      <span class="filter-chip inline-flex items-center gap-1 text-xs bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 px-2 py-1 rounded-full border border-yellow-200 dark:border-yellow-700">
                        <span>{{ minRating }}+ Stars</span>
                        <button @click="minRating = null" class="hover:text-red-600 transition-colors duration-200">
                          <Icon name="mdi:close" class="w-3 h-3" />
                        </button>
                      </span>
                    </template>

                    <!-- Clear All Button -->
                    <button @click="clearAll" class="ml-auto text-xs text-red-600 hover:text-red-800 font-semibold">
                      Clear All
                    </button>
                  </div>
                </div>
              </div>

              <!-- Products Header -->
              <div class="products-header mb-4">
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl p-4 shadow-lg border border-white/50 dark:border-slate-700/50">
                  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div class="products-title">
                      <h1 class="text-xl lg:text-2xl font-black text-slate-900 dark:text-white">
                        {{ categoryName }} Products
                      </h1>
                      <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                        {{ pagination.total || 0 }} products found
                      </p>
                    </div>

                    <div class="sort-dropdown">
                      <div class="relative">
                        <label class="sr-only" for="sort">Sort By</label>
                        <select
                            id="sort"
                            v-model="selectedSort"
                            class="sort-select pl-3 pr-8 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm shadow-sm focus:ring-2 focus:ring-blue-500 transition-colors duration-200 appearance-none cursor-pointer"
                        >
                          <option value="">Latest</option>
                          <!-- Dynamic sort options from API -->
                          <option v-for="sort in availableSorts" :key="`${sort.name}:${sort.direction}`" :value="`${sort.name}:${sort.direction}`">
                            {{ formatSortLabel(sort.name, sort.direction) }}
                          </option>
                        </select>
                        <Icon name="mdi:chevron-down" class="absolute right-2 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Products Grid -->
              <div v-if="products.length > 0" class="products-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                <ProductCard
                    v-for="product in products"
                    :key="product.sku"
                    :product="product"
                    :show-wishlist="true"
                    @wishlist-toggle="handleWishlistToggle"
                    @quick-view="handleQuickView"
                    @add-to-cart-success="handleAddToCartSuccess"
                    @add-to-cart-error="handleAddToCartError"
                />
              </div>

              <!-- Empty State -->
              <div v-else-if="!isLoading" class="text-center py-16">
                <div class="w-32 h-32 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-slate-700 dark:to-slate-600 rounded-full flex items-center justify-center mx-auto mb-8">
                  <Icon name="mdi:package-variant-closed" class="w-16 h-16 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">No Products Found</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
                  {{ hasAnyFilter
                    ? 'No products match your current filters. Try adjusting your search criteria.'
                    : 'This category is currently empty. Check back later for new products.'
                  }}
                </p>
                <button v-if="hasAnyFilter" @click="clearAll" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors duration-200">
                  Clear All Filters
                </button>
              </div>

              <!-- Loading State -->
              <div v-if="paging" class="text-center py-8">
                <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                <p class="text-gray-600 dark:text-gray-400">Loading more products...</p>
              </div>

              <!-- Load More / Pagination -->
              <div v-if="pagination.current_page < pagination.last_page" class="text-center">
                <button
                    @click="loadMore"
                    :disabled="paging"
                    class="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold rounded-xl shadow-xl hover:shadow-purple-500/25 transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                >
                  <span v-if="paging" class="flex items-center gap-2">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    Loading More...
                  </span>
                  <span v-else class="flex items-center gap-2">
                    <Icon name="mdi:plus" class="w-4 h-4" />
                    Load More Products
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSanctum, useSanctumFetch, useRuntimeConfig } from '#imports'
import GlobalLoader from "~/components/GlobalLoader.vue"
import ProductCard from "~/components/product/ProductCard.vue"
import CartCounter from "~/components/cart/CartCounter.vue"

// Types
interface ProductSale {
  sale_price: string
  discount: string
  ends_till: string
  remaining: string
  action_type: 'by_percent' | 'by_fixed' | 'to_percent' | 'to_fixed'
}

interface ProductTire {
  min_quantity: number
  max_quantity: number
  wholesale_unit_quantity: number | null
  price: string
  stock: number
  in_stock: boolean
}

interface ProductCategory {
  name: string
  url: string
  views: number
  thumbnail: string
}

interface Product {
  name: string
  url: string
  sku: string
  type: string
  views: number
  has_stock: boolean
  rating: string
  review: number
  review_avg_helpful_votes: string
  reward_point: number
  is_wishlisted: number | null
  price: string
  sales: ProductSale[]
  categories: ProductCategory[]
  thumbnail: string
  tire: ProductTire | null
}

interface PaginationMeta {
  current_page: number
  from: number
  last_page: number
  path: string
  per_page: number
  to: number
  total: number
}

interface SortOption {
  name: string
  value: string
  direction: string
}

// Composables
const route = useRoute()
const router = useRouter()
const { isLoggedIn } = useSanctum()
const config = useRuntimeConfig()

// State
const categoryUrl = computed(() => route.params.url as string)
const categoryName = computed(() => {
  if (products.value.length > 0) {
    return products.value[0]?.categories?.[0]?.name || categoryUrl.value
  }
  return categoryUrl.value
})

const isLoading = ref(false)
const paging = ref(false)
const error = ref<string | null>(null)
const mobileFilterOpen = ref(false)

// Products and pagination
const products = ref<Product[]>([])
const pagination = ref<PaginationMeta>({
  current_page: 1,
  from: 1,
  last_page: 1,
  path: '',
  per_page: 12,
  to: 12,
  total: 0
})

// Filter state
const filterGroups = ref<Record<string, Record<string, Record<string, string>>>>({})
const selectedFilters = reactive<Record<string, string[]>>({})
const searchQuery = ref('')
const searchDebounce = ref<number | null>(null)
const priceMin = ref<number | null>(null)
const priceMax = ref<number | null>(null)
const onOffer = ref(false)
const inStockOnly = ref(false)
const minRating = ref<number | null>(null)
const selectedSort = ref('')

// Sort state
const availableSorts = ref<SortOption[]>([])

// Option search state
const optionSearch = reactive<Record<string, string>>({})

// Computed
const hasAnyFilter = computed(() => {
  return searchQuery.value ||
      Object.values(selectedFilters).some(arr => arr?.length) ||
      priceMin.value != null ||
      priceMax.value != null ||
      onOffer.value ||
      inStockOnly.value ||
      minRating.value != null
})

// Filter utility methods
const isChecked = (fname: string, val: string) => {
  return (selectedFilters[fname] || []).includes(val)
}

const onFilterToggle = (fname: string, value: string, checked: boolean) => {
  const curr = selectedFilters[fname] ? [...selectedFilters[fname]] : []
  const idx = curr.indexOf(value)

  if (checked && idx === -1) curr.push(value)
  if (!checked && idx !== -1) curr.splice(idx, 1)

  selectedFilters[fname] = curr
  if (curr.length === 0) delete selectedFilters[fname]

  fetchProducts(1)
}

const removeOne = (fname: string, value: string) => {
  if (!selectedFilters[fname]) return
  selectedFilters[fname] = selectedFilters[fname].filter(v => v !== value)
  if (selectedFilters[fname].length === 0) delete selectedFilters[fname]
  fetchProducts(1)
}

const shouldShowSearch = (filterName: string, optionsMap: Record<string, string>) => {
  return Object.keys(optionsMap).length > 8
}

const isColorFilter = (filterName: string) => {
  const colorKeywords = ['color', 'colour', 'shade', 'tint']
  return colorKeywords.some(keyword => filterName.toLowerCase().includes(keyword))
}

const parseColor = (value: string | number, label: string) => {
  // Try to parse color from value or label
  const colorStr = String(label).toLowerCase()

  // Basic color mapping
  const colorMap: Record<string, string> = {
    red: '#ef4444',
    blue: '#3b82f6',
    green: '#10b981',
    yellow: '#f59e0b',
    purple: '#8b5cf6',
    pink: '#ec4899',
    black: '#000000',
    white: '#ffffff',
    gray: '#6b7280',
    grey: '#6b7280',
    orange: '#f97316',
    brown: '#92400e'
  }

  return colorMap[colorStr] || '#6b7280'
}

const toggleColor = (filterName: string, value: string) => {
  const isCurrentlyChecked = isChecked(filterName, value)
  onFilterToggle(filterName, value, !isCurrentlyChecked)
}

const filteredOptions = (filterName: string, optionsMap: Record<string, string>) => {
  const search = optionSearch[filterName]?.toLowerCase() || ''
  if (!search) return optionsMap

  const filtered: Record<string, string> = {}
  for (const [value, label] of Object.entries(optionsMap)) {
    if (label.toLowerCase().includes(search) || value.toLowerCase().includes(search)) {
      filtered[value] = label
    }
  }
  return filtered
}

const formatSortLabel = (name: string, direction: string) => {
  const labels: Record<string, string> = {
    popularity: 'Most Popular',
    latest: 'Latest',
    pricelow2high: 'Price: Low to High',
    pricehigh2low: 'Price: High to Low',
    rating: 'Highest Rated',
    name: direction === 'asc' ? 'Name A-Z' : 'Name Z-A'
  }

  return labels[name] || name
}

// Query building
const buildQueryParams = (page = 1) => {
  const params: Record<string, any> = {
    categories: categoryUrl.value,
    page
  }

  // Search
  if (searchQuery.value) {
    params.search = searchQuery.value
  }

  // Price range
  if (priceMin.value != null) {
    params['price[min]'] = priceMin.value
  }
  if (priceMax.value != null) {
    params['price[max]'] = priceMax.value
  }

  // Boolean filters
  if (onOffer.value) {
    params.offer = 1
  }
  if (inStockOnly.value) {
    params.in_stock = 1
  }
  if (minRating.value) {
    params.min_rating = minRating.value
  }

  // Dynamic filters
  for (const [filterName, values] of Object.entries(selectedFilters)) {
    if (values?.length) {
      params[`filters[${filterName}][]`] = values
    }
  }

  // Sorting
  if (selectedSort.value) {
    const [field, direction] = selectedSort.value.split(':')
    params[`sort[${field}]`] = direction || 'desc'
  }

  return params
}

// API calls
const fetchFilters = async () => {
  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/products/filters/get`, {
      params: { category: categoryUrl.value },
      credentials: 'include'
    })

    if (response) {
      filterGroups.value = response
    }
  } catch (e: any) {
    console.error('Failed to fetch filters:', e)
  }
}

const fetchSorts = async () => {
  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/products/sorts/get`, {
      credentials: 'include'
    })

    if (response && Array.isArray(response)) {
      availableSorts.value = response
    }
  } catch (e: any) {
    console.error('Failed to fetch sorts:', e)
  }
}

const fetchProducts = async (page = 1, append = false) => {
  if (append) paging.value = true
  else isLoading.value = true

  error.value = null

  try {
    const params = buildQueryParams(page)
    const response = await useSanctumFetch(`${config.public.apiBase}/products`, {
      params,
      credentials: 'include'
    })

    if (response?.data) {
      if (append) {
        products.value.push(...response.data)
      } else {
        products.value = response.data
      }

      if (response.meta) {
        pagination.value = response.meta
      }
    }

  } catch (e: any) {
    console.error('Failed to fetch products:', e)
    error.value = e.message || 'Failed to load products'
  } finally {
    isLoading.value = false
    paging.value = false
  }
}

// Filter methods
const clearPrice = () => {
  priceMin.value = null
  priceMax.value = null
  fetchProducts(1)
}

const clearAll = () => {
  // Clear all filters
  for (const k of Object.keys(selectedFilters)) delete selectedFilters[k]
  searchQuery.value = ''
  priceMin.value = null
  priceMax.value = null
  onOffer.value = false
  inStockOnly.value = false
  minRating.value = null
  selectedSort.value = ''

  // Clear option searches
  for (const k of Object.keys(optionSearch)) delete optionSearch[k]

  fetchProducts(1)
}

const quickPrice = (min: number | null, max: number | null) => {
  priceMin.value = min
  priceMax.value = max
  fetchProducts(1)
}

const debounceSearch = () => {
  if (searchDebounce.value) clearTimeout(searchDebounce.value)
  searchDebounce.value = setTimeout(() => {
    fetchProducts(1)
  }, 500)
}

const loadMore = () => {
  const nextPage = pagination.value.current_page + 1
  if (nextPage <= pagination.value.last_page) {
    fetchProducts(nextPage, true)
  }
}

const refresh = () => {
  fetchProducts(1)
}

// Event handlers
const handleWishlistToggle = (product: Product, newStatus: boolean) => {
  console.log('Wishlist toggle for:', product.name, 'New status:', newStatus)
  // Update local product state
  const productIndex = products.value.findIndex(p => p.sku === product.sku)
  if (productIndex !== -1) {
    products.value[productIndex].is_wishlisted = newStatus ? 1 : 0
  }
}

const handleQuickView = (product: Product) => {
  console.log('Quick view for:', product.name)
  // Implement quick view modal
}

const handleAddToCartSuccess = (product: Product) => {
  console.log('Added to cart:', product.name)
  // Show success notification
}

const handleAddToCartError = (error: any) => {
  console.error('Add to cart error:', error)
  // Show error notification
}

// Watchers
watch([onOffer, inStockOnly], () => {
  fetchProducts(1)
})

watch(minRating, () => {
  fetchProducts(1)
})

watch(selectedSort, () => {
  fetchProducts(1)
})

watch([priceMin, priceMax], () => {
  if (searchDebounce.value) clearTimeout(searchDebounce.value)
  searchDebounce.value = setTimeout(() => {
    fetchProducts(1)
  }, 800)
})

// Lifecycle
onMounted(async () => {
  // Fetch filters and sorts in parallel with products
  await Promise.all([
    fetchProducts(1),
    fetchFilters(),
    fetchSorts()
  ])
})

onUnmounted(() => {
  if (searchDebounce.value) {
    clearTimeout(searchDebounce.value)
  }
})

// SEO
useSeoMeta({
  title: computed(() => `${categoryName.value} - Premium Products`),
  description: computed(() => `Discover amazing ${categoryName.value} products with great deals and fast delivery. Shop now for the best selection.`),
})
</script>

<style scoped>
/* Line clamp utilities */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Filter chip animation */
.filter-chip {
  animation: slideInUp 0.3s ease-out;
}

@keyframes slideInUp {
  from {
    transform: translateY(10px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* Color swatch hover effects */
.color-swatch:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Mobile responsiveness */
@media (max-width: 1024px) {
  .hero-content h1 {
    font-size: 2rem;
    line-height: 1.2;
  }

  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .products-grid {
    grid-template-columns: repeat(1, 1fr);
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
