<template>
  <div class="relative z-10 w-full">

    <!-- Global Loader -->
    <GlobalLoader v-if="isLoading" />

    <!-- Error State -->
    <section v-else-if="error" class="flex flex-col items-center justify-center min-h-[80vh] px-6 relative z-10">
      <div class="max-w-lg mx-auto text-center">
        <!-- Enhanced Error Icon -->
        <div class="error-icon-container relative mb-8">
          <div class="w-24 h-24 mx-auto bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-full flex items-center justify-center shadow-2xl">
            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-orange-600 rounded-full flex items-center justify-center animate-pulse">
              <Icon name="mdi:store-alert" class="w-8 h-8 text-white" />
            </div>
          </div>
          <!-- Animated Elements -->
          <div class="absolute -top-2 -right-2 w-4 h-4 bg-red-400 rounded-full animate-bounce"></div>
          <div class="absolute -bottom-1 -left-1 w-3 h-3 bg-orange-400 rounded-full animate-bounce" style="animation-delay: 0.5s;"></div>
        </div>

        <!-- Error Content -->
        <div class="error-content bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-3xl p-8 shadow-xl border border-white/50 dark:border-slate-700/50">
          <h2 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white mb-4">
            Category Not Found
          </h2>
          <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
            We couldn't find this category. It might have been moved or doesn't exist anymore.
          </p>

          <!-- Action Buttons -->
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
      <section class="hero-banner relative overflow-hidden mb-12">
        <div class="relative h-96 lg:h-[500px]">
          <!-- Background Image with Overlay -->
          <div
              class="absolute inset-0 bg-cover bg-center bg-no-repeat"
              :style="category.banner ? `background-image: url(${category.banner})` : 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)'"
          >
            <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/40 to-black/60"></div>
          </div>

          <!-- Hero Content -->
          <div class="hero-content relative z-10 h-full flex items-center justify-center px-6">
            <div class="text-center text-white max-w-4xl mx-auto">
              <!-- Category Icon/Thumbnail -->
              <div class="category-icon-container mb-8">
                <div class="relative inline-block">
                  <div class="w-32 h-32 lg:w-40 lg:h-40 mx-auto rounded-3xl overflow-hidden shadow-2xl border-4 border-white/20 backdrop-blur-sm">
                    <img
                        :src="category.thumbnail"
                        :alt="category.name"
                        class="w-full h-full object-cover"
                    />
                  </div>
                  <!-- Icon rings -->
                  <div class="absolute inset-0 rounded-3xl border-4 border-white/30 animate-ping"></div>
                  <div class="absolute -inset-4 rounded-3xl border-2 border-white/20 animate-ping" style="animation-delay: 0.5s;"></div>
                </div>
              </div>

              <!-- Category Info -->
              <div class="category-info">
                <h1 class="text-4xl sm:text-5xl lg:text-7xl font-black mb-6 leading-tight">
                  <span class="block bg-gradient-to-r from-white via-blue-100 to-white bg-clip-text text-transparent">
                    {{ category.name }}
                  </span>
                </h1>

                <p class="text-xl lg:text-2xl text-blue-100 mb-8 opacity-90 max-w-2xl mx-auto leading-relaxed">
                  Discover curated items with premium quality and unbeatable deals
                </p>

                <!-- Stats -->
                <div class="category-stats flex flex-wrap justify-center gap-6 lg:gap-8">
                  <div class="stat-item bg-white/10 backdrop-blur-lg rounded-2xl px-6 py-4 border border-white/20">
                    <div class="text-2xl font-black">{{ formatNumber(category.views || 0) }}+</div>
                    <div class="text-sm opacity-80">Views</div>
                  </div>
                  <div class="stat-item bg-white/10 backdrop-blur-lg rounded-2xl px-6 py-4 border border-white/20">
                    <div class="text-2xl font-black">{{ allProducts.length }}+</div>
                    <div class="text-sm opacity-80">Products</div>
                  </div>
                  <div class="stat-item bg-white/10 backdrop-blur-lg rounded-2xl px-6 py-4 border border-white/20">
                    <div class="text-2xl font-black">{{ category.children?.length || 0 }}</div>
                    <div class="text-sm opacity-80">Categories</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Scroll Indicator -->
          <div class="scroll-indicator absolute bottom-6 left-1/2 transform -translate-x-1/2 animate-bounce z-20">
            <div class="w-6 h-10 border-2 border-white/50 rounded-full flex justify-center backdrop-blur-sm">
              <div class="w-1 h-3 bg-white rounded-full mt-2 animate-pulse"></div>
            </div>
          </div>
        </div>
      </section>

      <!-- Breadcrumbs -->
      <section class="breadcrumbs-section px-6 lg:px-12 mb-8">
        <div class="max-w-7xl mx-auto">
          <nav class="breadcrumbs bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl px-6 py-4 shadow-lg border border-white/50 dark:border-slate-700/50">
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
              <li class="text-slate-900 dark:text-white font-semibold">{{ category.name }}</li>
            </ol>
          </nav>
        </div>
      </section>

      <!-- Subcategories Section -->
      <section v-if="category.children?.length" class="subcategories-section px-6 lg:px-12 mb-16">
        <div class="max-w-7xl mx-auto">
          <div class="section-header text-center mb-12">
            <div class="header-badge inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-purple-500/10 to-pink-500/10 border border-purple-200 dark:border-purple-800 backdrop-blur-sm mb-6">
              <Icon name="mdi:view-grid" class="w-5 h-5 mr-3 text-purple-600 dark:text-purple-400" />
              <span class="font-semibold text-purple-700 dark:text-purple-300">Subcategories</span>
            </div>

            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mb-4">
              Explore <span class="bg-gradient-to-r from-purple-600 via-pink-600 to-red-600 bg-clip-text text-transparent">{{ category.name }}</span>
            </h2>

            <p class="text-lg text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
              Discover specialized products across {{ category.children.length }} different subcategories
            </p>
          </div>

          <div class="subcategories-grid grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
            <NuxtLink
                v-for="child in category.children"
                :key="child.url"
                :to="`/category/${child.url}`"
                class="subcategory-card group"
            >
              <div class="subcategory-item bg-white dark:bg-slate-700 rounded-3xl p-6 shadow-xl hover:shadow-2xl transition-all duration-500 group-hover:scale-110 group-hover:-translate-y-2 border border-slate-200 dark:border-slate-600 relative overflow-hidden">
                <!-- Gradient Border Effect -->
                <div class="absolute inset-0 bg-gradient-to-r from-purple-500 via-pink-500 to-red-500 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10 blur-xl"></div>

                <!-- Image Container -->
                <div class="image-container aspect-square mb-4 overflow-hidden rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-600 dark:to-slate-500 relative">
                  <img
                      :src="child.thumbnail"
                      :alt="child.name"
                      class="w-full h-full object-cover group-hover:scale-125 transition-transform duration-700"
                      loading="lazy"
                  />
                  <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>

                <!-- Content -->
                <div class="subcategory-content text-center">
                  <h3 class="font-bold text-sm lg:text-base text-slate-900 dark:text-white mb-2 line-clamp-2 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">
                    {{ child.name }}
                  </h3>
                  <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ formatNumber(child.views || 0) }} views
                  </p>
                  <div class="subcategory-arrow opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:translate-x-2 mt-2">
                    <Icon name="mdi:arrow-right" class="w-4 h-4 mx-auto text-purple-600 dark:text-purple-400" />
                  </div>
                </div>
              </div>
            </NuxtLink>
          </div>
        </div>
      </section>

      <!-- Cart Counter -->
      <div class="cart-counter-section px-6 lg:px-12 mb-8">
        <div class="max-w-7xl mx-auto flex justify-end">
          <div class="cart-counter-container bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-slate-700/50">
            <CartCounter />
          </div>
        </div>
      </div>

      <!-- Main Products Section -->
      <section class="products-section px-6 lg:px-12 mb-16">
        <div class="max-w-7xl mx-auto">
          <div class="flex flex-col lg:flex-row gap-8">

            <!-- Enhanced Sidebar -->
            <aside class="filters-sidebar lg:w-1/4">
              <div class="filters-container bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-3xl shadow-xl border border-white/50 dark:border-slate-700/50 sticky top-6 overflow-hidden">
                <!-- Filters Header -->
                <div class="filters-header p-6 bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                  <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold flex items-center">
                      <Icon name="mdi:filter-variant" class="w-6 h-6 mr-3" />
                      Filters
                    </h2>
                    <button
                        v-if="hasAnyFilter"
                        @click="clearAll"
                        class="text-sm bg-white/20 hover:bg-white/30 px-4 py-2 rounded-full backdrop-blur-sm transition-colors duration-200"
                    >
                      Clear All
                    </button>
                  </div>

                  <!-- Mobile Filter Toggle -->
                  <button
                      @click="mobileFilterOpen = !mobileFilterOpen"
                      class="lg:hidden w-full mt-4 flex items-center justify-center gap-2 bg-white/20 hover:bg-white/30 py-3 rounded-2xl backdrop-blur-sm transition-colors duration-200"
                  >
                    <Icon name="mdi:tune" class="w-5 h-5" />
                    <span>{{ mobileFilterOpen ? 'Hide Filters' : 'Show Filters' }}</span>
                  </button>
                </div>

                <!-- Filters Content -->
                <div :class="{'hidden lg:block': !mobileFilterOpen, 'block': mobileFilterOpen}" class="filters-content p-6 space-y-6">

                  <!-- Price Filter -->
                  <div class="filter-group">
                    <div class="filter-header mb-4">
                      <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center">
                        <Icon name="mdi:currency-inr" class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" />
                        Price Range
                      </h3>
                    </div>

                    <div class="price-inputs mb-4">
                      <div class="flex items-center gap-3">
                        <div class="relative flex-1">
                          <input
                              type="number"
                              inputmode="numeric"
                              min="0"
                              v-model.number="priceMin"
                              placeholder="Min"
                              class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                          />
                        </div>
                        <span class="text-slate-500 dark:text-slate-400 font-medium">to</span>
                        <div class="relative flex-1">
                          <input
                              type="number"
                              inputmode="numeric"
                              min="0"
                              v-model.number="priceMax"
                              placeholder="Max"
                              class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                          />
                        </div>
                      </div>
                    </div>

                    <!-- Quick Price Buttons -->
                    <div class="quick-price-buttons grid grid-cols-2 gap-2">
                      <button @click="quickPrice(0, 500)" class="price-btn px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-slate-700 dark:text-slate-300 rounded-xl transition-colors duration-200">Under ₹500</button>
                      <button @click="quickPrice(500, 2000)" class="price-btn px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-slate-700 dark:text-slate-300 rounded-xl transition-colors duration-200">₹500–₹2K</button>
                      <button @click="quickPrice(2000, 5000)" class="price-btn px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-slate-700 dark:text-slate-300 rounded-xl transition-colors duration-200">₹2K–₹5K</button>
                      <button @click="quickPrice(5000, null)" class="price-btn px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-slate-700 dark:text-slate-300 rounded-xl transition-colors duration-200">₹5K+</button>
                    </div>
                  </div>

                  <!-- Availability Filters -->
                  <div class="filter-group">
                    <div class="filter-header mb-4">
                      <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center">
                        <Icon name="mdi:package-variant" class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" />
                        Availability
                      </h3>
                    </div>

                    <div class="availability-options space-y-3">
                      <label class="availability-option flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200 cursor-pointer">
                        <input
                            type="checkbox"
                            :checked="onOffer"
                            @change="toggleFlag('offer', ($event.target as HTMLInputElement).checked)"
                            class="w-5 h-5 text-red-600 bg-slate-100 dark:bg-slate-700 border-slate-300 dark:border-slate-600 rounded focus:ring-red-500 dark:focus:ring-red-600 dark:ring-offset-slate-800 focus:ring-2"
                        />
                        <div class="flex items-center">
                          <Icon name="mdi:tag" class="w-4 h-4 text-red-500 mr-2" />
                          <span class="text-slate-900 dark:text-white font-medium">On Sale</span>
                        </div>
                      </label>

                      <label class="availability-option flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200 cursor-pointer">
                        <input
                            type="checkbox"
                            :checked="inStockOnly"
                            @change="toggleFlag('in_stock', ($event.target as HTMLInputElement).checked)"
                            class="w-5 h-5 text-green-600 bg-slate-100 dark:bg-slate-700 border-slate-300 dark:border-slate-600 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-slate-800 focus:ring-2"
                        />
                        <div class="flex items-center">
                          <Icon name="mdi:check-circle" class="w-4 h-4 text-green-500 mr-2" />
                          <span class="text-slate-900 dark:text-white font-medium">In Stock</span>
                        </div>
                      </label>
                    </div>
                  </div>

                  <!-- Dynamic Filter Groups -->
                  <template v-for="(filters, groupName) in filterGroups" :key="groupName">
                    <div class="filter-group">
                      <div class="filter-header mb-4">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center">
                          <Icon name="mdi:tune" class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-400" />
                          {{ groupName }}
                        </h3>
                      </div>

                      <div class="filter-options space-y-4">
                        <div v-for="(optionsMap, filterName) in filters" :key="`${groupName}:${filterName}`">
                          <!-- Search Input -->
                          <div v-if="shouldShowSearch(filterName, optionsMap)" class="search-input mb-3">
                            <div class="relative">
                              <Icon name="mdi:magnify" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400" />
                              <input
                                  v-model="optionSearch[filterName]"
                                  type="text"
                                  :placeholder="`Search ${filterName}...`"
                                  class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-purple-500 transition-colors duration-200"
                              />
                            </div>
                          </div>

                          <!-- Filter Label -->
                          <div class="filter-label mb-2">
                            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">{{ filterName }}</h4>
                          </div>

                          <!-- Color Swatches -->
                          <div v-if="isColorFilter(filterName)" class="color-swatches grid grid-cols-6 gap-2">
                            <button
                                v-for="(label, value) in filteredOptions(filterName, optionsMap)"
                                :key="`${filterName}:${value}`"
                                class="color-swatch w-10 h-10 rounded-xl border-2 overflow-hidden transition-all duration-200 hover:scale-110"
                                :class="isChecked(filterName, String(value)) ? 'border-blue-500 ring-2 ring-blue-500/50' : 'border-slate-300 dark:border-slate-600'"
                                :title="label"
                                @click="toggleColor(filterName, String(value))"
                            >
                              <span class="block w-full h-full" :style="{ backgroundColor: parseColor(value, label) }"></span>
                            </button>
                          </div>

                          <!-- Generic Options -->
                          <div v-else class="filter-options-list max-h-48 overflow-y-auto space-y-2">
                            <label
                                v-for="(label, value) in filteredOptions(filterName, optionsMap)"
                                :key="`${filterName}:${value}`"
                                class="filter-option flex items-center justify-between gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200 cursor-pointer"
                            >
                              <div class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    :value="value"
                                    :checked="isChecked(filterName, String(value))"
                                    @change="onFilterToggle(filterName, String(value), ($event.target as HTMLInputElement).checked)"
                                    class="w-5 h-5 text-purple-600 bg-slate-100 dark:bg-slate-700 border-slate-300 dark:border-slate-600 rounded focus:ring-purple-500 dark:focus:ring-purple-600 dark:ring-offset-slate-800 focus:ring-2"
                                />
                                <span class="text-slate-900 dark:text-white font-medium truncate">{{ label }}</span>
                              </div>
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
            <div class="products-content lg:w-3/4">

              <!-- Applied Filters Chips -->
              <div v-if="hasAnyFilter" class="applied-filters mb-6">
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl p-4 shadow-lg border border-white/50 dark:border-slate-700/50">
                  <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300 mr-2">Active Filters:</span>

                    <!-- Filter Chips -->
                    <template v-for="(values, fname) in selectedFilters" :key="`chip:${fname}`">
                      <span
                          v-for="val in values"
                          :key="`chip:${fname}:${val}`"
                          class="filter-chip inline-flex items-center gap-2 text-sm bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 px-3 py-2 rounded-full border border-blue-200 dark:border-blue-700"
                      >
                        <span class="truncate">{{ fname }}: {{ val }}</span>
                        <button @click="removeOne(fname, String(val))" class="hover:text-red-600 transition-colors duration-200" aria-label="Remove">
                          <Icon name="mdi:close" class="w-4 h-4" />
                        </button>
                      </span>
                    </template>

                    <!-- Price Chip -->
                    <template v-if="priceMin != null || priceMax != null">
                      <span class="filter-chip inline-flex items-center gap-2 text-sm bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 px-3 py-2 rounded-full border border-green-200 dark:border-green-700">
                        <span>₹{{ priceMin || 0 }}{{ priceMax != null ? `–₹${priceMax}` : '+' }}</span>
                        <button @click="clearPrice" class="hover:text-red-600 transition-colors duration-200" aria-label="Remove">
                          <Icon name="mdi:close" class="w-4 h-4" />
                        </button>
                      </span>
                    </template>

                    <!-- Offer/Stock Chips -->
                    <template v-if="onOffer">
                      <span class="filter-chip inline-flex items-center gap-2 text-sm bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 px-3 py-2 rounded-full border border-red-200 dark:border-red-700">
                        <span>On Sale</span>
                        <button @click="setFlag('offer', false)" class="hover:text-red-600 transition-colors duration-200" aria-label="Remove">
                          <Icon name="mdi:close" class="w-4 h-4" />
                        </button>
                      </span>
                    </template>

                    <template v-if="inStockOnly">
                      <span class="filter-chip inline-flex items-center gap-2 text-sm bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-200 px-3 py-2 rounded-full border border-emerald-200 dark:border-emerald-700">
                        <span>In Stock</span>
                        <button @click="setFlag('in_stock', false)" class="hover:text-red-600 transition-colors duration-200" aria-label="Remove">
                          <Icon name="mdi:close" class="w-4 h-4" />
                        </button>
                      </span>
                    </template>

                    <!-- Clear All Button -->
                    <button @click="clearAll" class="ml-auto text-sm text-red-600 hover:text-red-800 font-semibold">
                      Clear All
                    </button>
                  </div>
                </div>
              </div>

              <!-- Products Header -->
              <div class="products-header mb-6">
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl p-4 shadow-lg border border-white/50 dark:border-slate-700/50">
                  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="products-title">
                      <h1 class="text-2xl lg:text-3xl font-black text-slate-900 dark:text-white">
                        {{ category.name }} Products
                      </h1>
                      <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                        {{ pagination.total || allProducts.length }} products found
                      </p>
                    </div>

                    <div class="sort-dropdown">
                      <div class="relative">
                        <label class="sr-only" for="sort">Sort By</label>
                        <select
                            id="sort"
                            v-model="selectedSortName"
                            class="sort-select pl-4 pr-10 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-medium shadow-sm focus:ring-2 focus:ring-blue-500 transition-colors duration-200 appearance-none cursor-pointer"
                        >
                          <option v-for="sort in sortOptions" :key="sort.name" :value="sort.name">
                            {{ formatSortName(sort.name) }}
                          </option>
                        </select>
                        <Icon name="mdi:chevron-down" class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Products Grid -->
              <div class="products-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                <div
                    v-for="product in allProducts"
                    :key="product.url"
                    class="product-card group bg-white dark:bg-slate-700 rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 hover:scale-105 hover:-translate-y-2 border border-slate-200 dark:border-slate-600 relative"
                >
                  <!-- Gradient Border Effect -->
                  <div class="absolute inset-0 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10 blur-xl"></div>

                  <!-- Product Link -->
                  <NuxtLink :to="`/product/${product.url}`" class="block">
                    <!-- Image Container -->
                    <div class="product-image relative aspect-square bg-slate-100 dark:bg-slate-600 overflow-hidden">
                      <img
                          loading="lazy"
                          :src="product.thumbnail"
                          :alt="product.name"
                          class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                      />

                      <!-- Sale Badge -->
                      <div v-if="product.on_sale" class="sale-badge absolute top-3 left-3 bg-gradient-to-r from-red-500 to-pink-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                        SALE
                      </div>

                      <!-- Stock Badge -->
                      <div v-if="product.stock_status === 'low'" class="stock-badge absolute top-3 right-3 bg-orange-500 text-white px-2 py-1 rounded-lg text-xs font-bold">
                        Low Stock
                      </div>

                      <!-- Quick Actions Overlay -->
                      <div class="quick-actions absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <div class="quick-actions-buttons flex gap-3">
                          <button class="quick-action-btn w-12 h-12 bg-white/90 text-slate-900 rounded-full flex items-center justify-center hover:bg-white transition-colors duration-200">
                            <Icon name="mdi:eye" class="w-5 h-5" />
                          </button>
                          <button class="quick-action-btn w-12 h-12 bg-white/90 text-slate-900 rounded-full flex items-center justify-center hover:bg-white transition-colors duration-200">
                            <Icon name="mdi:heart-outline" class="w-5 h-5" />
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- Product Info -->
                    <div class="product-info p-4">
                      <h3 class="product-name font-bold text-lg text-slate-900 dark:text-white mb-2 line-clamp-2 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                        {{ product.name }}
                      </h3>

                      <p class="product-description text-sm text-slate-600 dark:text-slate-400 mb-3 line-clamp-2">
                        {{ product.short_description }}
                      </p>

                      <!-- Price -->
                      <div class="product-pricing mb-4">
                        <div class="flex items-center gap-2">
                          <span class="current-price text-2xl font-black text-green-600 dark:text-green-400">
                            {{ product.price }}
                          </span>
                          <span v-if="product.original_price && product.original_price > product.price" class="original-price text-sm text-slate-500 dark:text-slate-400 line-through">
                            {{ product.price }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </NuxtLink>

                  <!-- Add to Cart Button -->
                  <div class="product-actions p-4 pt-0">
                    <AddToCartButton
                        :sku="product.sku"
                        :quantity="1"
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-blue-500/25 transition-all duration-300 transform hover:scale-105"
                    />
                  </div>
                </div>
              </div>

              <!-- Load More / Pagination -->
              <div class="load-more text-center">
                <button
                    @click="nextPage"
                    :disabled="pagination.current_page >= pagination.last_page || paging"
                    class="load-more-btn px-12 py-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-purple-500/25 transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                >
                  <span v-if="paging" class="flex items-center gap-3">
                    <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    Loading More Products...
                  </span>
                  <span v-else class="flex items-center gap-3">
                    <Icon name="mdi:plus" class="w-5 h-5" />
                    Load More Products
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Top Products Section -->
      <section v-if="topProducts.length" class="top-products-section px-6 lg:px-12 mb-16 bg-gradient-to-r from-slate-50 to-white dark:from-slate-900 dark:to-slate-800 py-20">
        <div class="max-w-7xl mx-auto">
          <div class="section-header text-center mb-12">
            <div class="header-badge inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-yellow-500/10 to-orange-500/10 border border-yellow-200 dark:border-yellow-800 backdrop-blur-sm mb-6">
              <Icon name="mdi:star" class="w-5 h-5 mr-3 text-yellow-600 dark:text-yellow-400" />
              <span class="font-semibold text-yellow-700 dark:text-yellow-300">Top Rated</span>
            </div>

            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mb-4">
              Top Products in <span class="bg-gradient-to-r from-yellow-600 via-orange-600 to-red-600 bg-clip-text text-transparent">{{ category.name }}</span>
            </h2>

            <p class="text-lg text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
              Discover our most popular and highest-rated products in this category
            </p>
          </div>

          <div class="top-products-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div
                v-for="product in topProducts"
                :key="product.url"
                class="top-product-card group bg-white dark:bg-slate-700 rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 hover:scale-105 hover:-translate-y-2 border border-slate-200 dark:border-slate-600 relative"
            >
              <!-- Top Product Badge -->
              <div class="absolute top-3 left-3 bg-gradient-to-r from-yellow-400 to-orange-400 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg z-10">
                <Icon name="mdi:crown" class="inline w-3 h-3 mr-1" />
                TOP
              </div>

              <NuxtLink :to="`/product/${product.url}`" class="block">
                <div class="product-image relative aspect-square bg-slate-100 dark:bg-slate-600 overflow-hidden">
                  <img
                      loading="lazy"
                      :src="product.thumbnail"
                      :alt="product.name"
                      class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                  />
                </div>

                <div class="product-info p-4">
                  <h3 class="font-bold text-lg text-slate-900 dark:text-white mb-2 line-clamp-2 group-hover:text-yellow-600 dark:group-hover:text-yellow-400 transition-colors duration-300">
                    {{ product.name }}
                  </h3>

                  <p class="text-sm text-slate-600 dark:text-slate-400 mb-3 line-clamp-2">
                    {{ product.short_description }}
                  </p>

                  <div class="flex items-center justify-between">
                    <span class="text-2xl font-black text-green-600 dark:text-green-400">
                      {{ product.price }}
                    </span>

                    <div class="flex items-center gap-1 text-yellow-500">
                      <Icon name="mdi:star" class="w-4 h-4" />
                      <span class="text-sm font-bold">{{ (Math.random() * 2 + 3).toFixed(1) }}</span>
                    </div>
                  </div>
                </div>
              </NuxtLink>

              <div class="product-actions p-4 pt-0">
                <AddToCartButton
                    :sku="product.sku"
                    :quantity="1"
                    class="w-full bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-yellow-500/25 transition-all duration-300 transform hover:scale-105"
                />
              </div>
            </div>
          </div>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch, computed, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSanctum, useSanctumFetch, useRuntimeConfig } from '#imports'
import GlobalLoader from "~/components/GlobalLoader.vue"
import AddToCartButton from "~/components/cart/AddToCartButton.vue"
import CartCounter from "~/components/cart/CartCounter.vue"

// Composables
const route = useRoute()
const router = useRouter()
const { isLoggedIn } = useSanctum()
const config = useRuntimeConfig()

// Reactive state
const categoryUrl = computed(() => route.params.url as string)
const mobileFilterOpen = ref(false)
const paging = ref(false)
const isLoading = useState('pageLoading', () => false)
const error = ref<any>(null)
const category = ref<any>({})
const topProducts = ref<any[]>([])
const latestProducts = ref<any[]>([])
const allProducts = ref<any[]>([])
const pagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 12 })

// Filtering state
const filterGroups = ref<Record<string, Record<string, Record<string, string>>>>({})
const selectedFilters = reactive<Record<string, string[]>>({})
const optionSearch = reactive<Record<string, string>>({})
const priceMin = ref<number | null>(null)
const priceMax = ref<number | null>(null)
const onOffer = ref<boolean>(false)
const inStockOnly = ref<boolean>(false)

// Sorting state
type SortOption = { name: string; value: string; direction: 'asc' | 'desc' }
const sortOptions = ref<SortOption[]>([])
const selectedSortName = ref<string>('latest')

// Track fetched pages
const lastFetchedPage = ref<number>(1)

// Computed properties
const selectedSort = computed<SortOption | null>(() =>
    sortOptions.value.find(s => s.name === selectedSortName.value) || null
)

const hasAnyFilter = computed(() => {
  const any = Object.values(selectedFilters).some(arr => arr?.length)
  return any || priceMin.value != null || priceMax.value != null || onOffer.value || inStockOnly.value
})

// Utility functions
const formatNumber = (num: number): string => {
  if (num >= 1000000) return `${(num / 1000000).toFixed(1)}M`
  if (num >= 1000) return `${(num / 1000).toFixed(1)}K`
  return num.toString()
}

const formatPrice = (price: number): string => {
  return new Intl.NumberFormat('en-IN').format(price)
}

const formatSortName = (name: string): string => {
  return name.replace(/([a-z])([A-Z])/g, '$1 $2').toUpperCase()
}

// Filter helper functions
const isColorFilter = (name: string) => {
  const n = name.toLowerCase()
  return n === 'color' || n === 'colour'
}

const parseColor = (value: string, label: string) => {
  const v = String(value || '').trim()
  const hex = /^#([0-9a-f]{3}|[0-9a-f]{6})$/i
  if (hex.test(v)) return v
  if (hex.test(label.trim())) return label.trim()
  return label
}

const shouldShowSearch = (filterName: string, opt: Record<string, string>) => {
  return ['Brand', 'Spice Type'].includes(filterName) && Object.keys(opt || {}).length > 8
}

const filteredOptions = (filterName: string, opt: Record<string, string>) => {
  const q = (optionSearch[filterName] || '').toLowerCase()
  if (!q) return opt
  const out: Record<string, string> = {}
  for (const [val, label] of Object.entries(opt)) {
    if (String(val).toLowerCase().includes(q) || String(label).toLowerCase().includes(q)) {
      out[val] = label
    }
  }
  return out
}

const isChecked = (fname: string, val: string) => {
  return (selectedFilters[fname] || []).includes(val)
}

// Query building and comparison
const buildQueryObject = (page: number) => {
  const q: Record<string, any> = { page }
  if (selectedSort.value) q[`sort[${selectedSort.value.name}]`] = selectedSort.value.direction
  for (const [fname, values] of Object.entries(selectedFilters)) {
    if (values?.length) q[`filters[${fname}][]`] = values
  }
  if (priceMin.value != null) q['price[min]'] = priceMin.value
  if (priceMax.value != null) q['price[max]'] = priceMax.value
  if (onOffer.value) q['offer'] = 1
  if (inStockOnly.value) q['in_stock'] = 1
  return q
}

const isSameQuery = (a: any, b: any) => {
  const ak = Object.keys(a || {})
  const bk = Object.keys(b || {})
  if (ak.length !== bk.length) return false
  for (const k of ak) {
    const av = a[k], bv = b[k]
    if (Array.isArray(av) || Array.isArray(bv)) {
      const aa = Array.isArray(av) ? av.map(String).sort() : []
      const bb = Array.isArray(bv) ? bv.map(String).sort() : []
      if (aa.length !== bb.length) return false
      for (let i = 0; i < aa.length; i++) {
        if (aa[i] !== bb[i]) return false
      }
    } else if (String(av) !== String(bv)) return false
  }
  return true
}

// URL and API functions
const pushQuery = async (page = 1) => {
  const desired = buildQueryObject(page)
  const current = route.query
  const changed = !isSameQuery(current, desired)
  if (changed) {
    await router.replace({ query: desired })
  } else {
    hydrateFromRoute()
    const append = page > (lastFetchedPage.value || 1)
    await fetchCategory(page, append)
  }
}

const hydrateFromRoute = () => {
  // Hydrate sort
  for (const [k, v] of Object.entries(route.query)) {
    const sm = k.match(/^sort\[(.+)\]$/)
    if (sm && typeof v === 'string') selectedSortName.value = sm[1]
  }

  // Hydrate filters
  const fresh: Record<string, string[]> = {}
  for (const [k, v] of Object.entries(route.query)) {
    const fm = k.match(/^filters\[(.+)\](\[\])?$/)
    if (fm) fresh[fm[1]] = (Array.isArray(v) ? v : [String(v)]).map(String)
  }
  for (const key of Object.keys(selectedFilters)) delete selectedFilters[key]
  for (const [k, arr] of Object.entries(fresh)) selectedFilters[k] = arr

  // Hydrate price/flags
  const pm = route.query['price[min]']
  const px = route.query['price[max]']
  priceMin.value = pm != null ? Number(pm) : null
  priceMax.value = px != null ? Number(px) : null
  onOffer.value = route.query.offer != null ? String(route.query.offer) === '1' : false
  inStockOnly.value = route.query.in_stock != null ? String(route.query.in_stock) === '1' : false
}

// API calls
const fetchSortOptions = async () => {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/products/sorts/get`)
    sortOptions.value = (res || []) as SortOption[]
    const def = sortOptions.value.find(s => s.name === 'latest') || sortOptions.value[0]
    if (def) selectedSortName.value = def.name
  } catch (e) {
    console.error('Failed to fetch sort options:', e)
  }
}

const fetchFilterOptions = async () => {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/products/filters/get?category=${encodeURIComponent(categoryUrl.value)}`)
    filterGroups.value = (res || {}) as Record<string, Record<string, Record<string, string>>>
  } catch (e) {
    console.error('Failed to fetch filter options:', e)
  }
}

const fetchCategory = async (page = 1, append = false) => {
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
    console.error('Failed to fetch category:', e)
  } finally {
    isLoading.value = false
    paging.value = false
  }
}

// UI action handlers
const onFilterToggle = (fname: string, value: string, checked: boolean) => {
  const curr = selectedFilters[fname] ? [...selectedFilters[fname]] : []
  const idx = curr.indexOf(value)
  if (checked && idx === -1) curr.push(value)
  if (!checked && idx !== -1) curr.splice(idx, 1)
  selectedFilters[fname] = curr
  pushQuery(1)
}

const toggleColor = (fname: string, value: string) => {
  const curr = selectedFilters[fname] ? [...selectedFilters[fname]] : []
  const idx = curr.indexOf(value)
  if (idx === -1) curr.push(value)
  else curr.splice(idx, 1)
  selectedFilters[fname] = curr
  pushQuery(1)
}

const removeOne = (fname: string, value: string) => {
  if (!selectedFilters[fname]) return
  selectedFilters[fname] = selectedFilters[fname].filter(v => v !== value)
  if (selectedFilters[fname].length === 0) delete selectedFilters[fname]
  pushQuery(1)
}

const clearPrice = () => {
  priceMin.value = null
  priceMax.value = null
  pushQuery(1)
}

const clearAll = () => {
  for (const k of Object.keys(selectedFilters)) delete selectedFilters[k]
  priceMin.value = null
  priceMax.value = null
  onOffer.value = false
  inStockOnly.value = false
  pushQuery(1)
}

const quickPrice = (min: number | null, max: number | null) => {
  priceMin.value = min
  priceMax.value = max
  pushQuery(1)
}

const toggleFlag = (flag: 'offer' | 'in_stock', v: boolean) => {
  if (flag === 'offer') onOffer.value = v
  else inStockOnly.value = v
  pushQuery(1)
}

const setFlag = (flag: 'offer' | 'in_stock', v: boolean) => {
  toggleFlag(flag, v)
}

const nextPage = () => {
  const next = Math.min((pagination.value.current_page || 1) + 1, pagination.value.last_page || 1)
  pushQuery(next)
}

const refresh = () => {
  window.location.reload()
}

// Watchers
watch(
    () => route.fullPath,
    async (cur, prev) => {
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

watch(selectedSortName, () => pushQuery(1))

// Lifecycle
onMounted(async () => {
  await fetchSortOptions()
  await fetchFilterOptions()
})

// SEO
useSeoMeta({
  title: computed(() => `${category.value.name || 'Category'} - Premium Products`),
  description: computed(() => `Discover amazing ${category.value.name || 'products'} with great deals and fast delivery. Shop now for the best selection.`),
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

/* Enhanced product card effects */
.product-card {
  position: relative;
}

.product-card::before {
  content: '';
  position: absolute;
  inset: 0;
  padding: 2px;
  background: linear-gradient(135deg, transparent, rgba(59, 130, 246, 0.3), transparent);
  border-radius: 1.5rem;
  mask: linear-gradient(white 0 0) content-box, linear-gradient(white 0 0);
  mask-composite: exclude;
  opacity: 0;
  transition: opacity 0.4s ease;
}

.product-card:hover::before {
  opacity: 1;
}

/* Subcategory card effects */
.subcategory-item::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #8b5cf6, #ec4899, #f59e0b);
  transform: scaleX(0);
  transition: transform 0.4s ease;
  border-radius: 1.5rem 1.5rem 0 0;
}

.subcategory-item:hover::after {
  transform: scaleX(1);
}

/* Filter animations */
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

/* Loading button animation */
.load-more-btn:disabled {
  background: linear-gradient(45deg, #d1d5db, #9ca3af) !important;
}

/* Mobile responsiveness */
@media (max-width: 640px) {
  .hero-content h1 {
    font-size: 2.5rem;
    line-height: 1.2;
  }

  .category-stats {
    flex-direction: column;
    gap: 1rem;
  }

  .subcategories-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }

  .products-grid {
    grid-template-columns: repeat(1, 1fr);
  }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .product-card,
  .subcategory-card {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }
}

/* Focus states for accessibility */
.product-card:focus-visible,
.subcategory-card:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
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
