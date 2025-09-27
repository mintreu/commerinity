<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-gray-900 dark:to-purple-950 overflow-x-hidden">

    <!-- Optimized Floating Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden will-change-transform">
      <div ref="orb1" class="store-orb-1 absolute top-20 left-20 w-96 h-96 bg-gradient-to-r from-blue-400 to-cyan-400 rounded-full opacity-10 blur-3xl will-change-transform"></div>
      <div ref="orb2" class="store-orb-2 absolute bottom-20 right-20 w-80 h-80 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full opacity-15 blur-3xl will-change-transform"></div>
      <div ref="orb3" class="store-orb-3 absolute top-1/2 left-1/4 w-72 h-72 bg-gradient-to-r from-emerald-400 to-teal-400 rounded-full opacity-10 blur-2xl will-change-transform"></div>

      <!-- Optimized Shopping Particles -->
      <div class="shopping-particles">
        <div v-for="(particle, index) in particles" :key="`particle-${index}`"
             :ref="el => particleRefs[index] = el"
             class="particle absolute rounded-full opacity-60 animate-bounce will-change-transform"
             :class="particle.class"
             :style="particle.style">
        </div>
      </div>
    </div>

    <!-- Enhanced Hero Section with Fixed Layout -->
    <section class="hero-section relative min-h-screen flex flex-col justify-center overflow-hidden">
      <ClientOnly>
        <div v-if="heroSlides.length > 1" class="hero-carousel w-full flex-1 flex items-center">
          <Swiper
              ref="heroSwiper"
              :modules="[SwiperAutoplay, SwiperNavigation, SwiperPagination]"
              :slides-per-view="1"
              :autoplay="{ delay: 6000, disableOnInteraction: false }"
              :pagination="{ clickable: true }"
              :navigation="true"
              :loop="true"
              :lazy="{ loadPrevNext: true }"
              class="h-full w-full"
              @swiper="onSwiperInit"
          >
            <SwiperSlide v-for="slide in heroSlides" :key="`slide-${slide.id}`">
              <div class="relative h-full w-full flex items-center justify-center">
                <!-- Optimized Background -->
                <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-purple-700 to-pink-600">
                  <div v-if="slide.image"
                       class="absolute inset-0 bg-cover bg-center opacity-30"
                       :style="{ backgroundImage: `url(${slide.image})` }"
                       loading="lazy">
                  </div>
                  <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-black/20"></div>
                </div>

                <!-- Hero Content with Better Spacing -->
                <div ref="heroContent" class="hero-content relative z-10 text-center text-white px-6 max-w-6xl mx-auto pb-32 lg:pb-40">
                  <div class="hero-badge inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-yellow-400/20 to-orange-400/20 border border-yellow-300/30 backdrop-blur-sm mb-8">
                    <Icon name="mdi:star" class="w-5 h-5 mr-3 text-yellow-300" />
                    <span class="font-semibold text-yellow-100">Premium Shopping Experience</span>
                  </div>

                  <h1 class="hero-title text-4xl sm:text-6xl lg:text-8xl font-black mb-8 leading-tight">
                    <span class="block">{{ slide.title || 'Welcome to Our' }}</span>
                    <span class="block bg-gradient-to-r from-yellow-300 via-orange-300 to-red-300 bg-clip-text text-transparent">Premium Store</span>
                  </h1>

                  <p class="hero-subtitle text-xl sm:text-2xl lg:text-3xl mb-12 opacity-90 max-w-4xl mx-auto leading-relaxed">
                    {{ slide.subtitle || 'Discover amazing products with unbeatable deals and fast delivery!' }}
                  </p>

                  <div class="hero-actions flex flex-col sm:flex-row gap-6 justify-center items-center">
                    <NuxtLink
                        :to="firstCategoryLink"
                        class="hero-btn-primary group px-12 py-6 bg-gradient-to-r from-orange-500 via-red-500 to-pink-500 text-white font-black text-xl rounded-2xl shadow-2xl hover:shadow-orange-500/25 transition-all duration-300 transform hover:scale-110"
                    >
                      <Icon name="mdi:shopping" class="inline w-6 h-6 mr-3 group-hover:animate-bounce" />
                      {{ slide.cta || 'Start Shopping' }}
                      <Icon name="mdi:arrow-right" class="inline w-6 h-6 ml-3 group-hover:translate-x-3 transition-transform duration-300" />
                    </NuxtLink>

                    <button
                        @click="playDemo"
                        class="hero-btn-secondary group px-12 py-6 bg-white/10 backdrop-blur-md text-white font-bold text-xl rounded-2xl border-2 border-white/30 hover:bg-white/20 transition-all duration-300 transform hover:scale-105"
                    >
                      <Icon name="mdi:play-circle" class="inline w-6 h-6 mr-3 group-hover:animate-pulse" />
                      Watch Demo
                    </button>
                  </div>
                </div>
              </div>
            </SwiperSlide>
          </Swiper>
        </div>

        <div v-else class="hero-single w-full flex-1 flex items-center">
          <div class="relative h-full w-full flex items-center justify-center">
            <!-- Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-purple-700 to-pink-600">
              <div v-if="heroSlides[0]?.image"
                   class="absolute inset-0 bg-cover bg-center opacity-30"
                   :style="{ backgroundImage: `url(${heroSlides[0].image})` }">
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-black/20"></div>
            </div>

            <!-- Content with Better Spacing -->
            <div ref="heroContent" class="hero-content relative z-10 text-center text-white px-6 max-w-6xl mx-auto pb-32 lg:pb-40">
              <div class="hero-badge inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-yellow-400/20 to-orange-400/20 border border-yellow-300/30 backdrop-blur-sm mb-8">
                <Icon name="mdi:star" class="w-5 h-5 mr-3 text-yellow-300" />
                <span class="font-semibold text-yellow-100">Premium Shopping Experience</span>
              </div>

              <h1 class="hero-title text-4xl sm:text-6xl lg:text-8xl font-black mb-8 leading-tight">
                <span class="block">{{ heroSlides[0]?.title || 'Welcome to Our' }}</span>
                <span class="block bg-gradient-to-r from-yellow-300 via-orange-300 to-red-300 bg-clip-text text-transparent">Premium Store</span>
              </h1>

              <p class="hero-subtitle text-xl sm:text-2xl lg:text-3xl mb-12 opacity-90 max-w-4xl mx-auto leading-relaxed">
                {{ heroSlides[0]?.subtitle || 'Discover amazing products with unbeatable deals and fast delivery!' }}
              </p>

              <div class="hero-actions flex flex-col sm:flex-row gap-6 justify-center items-center">
                <NuxtLink
                    :to="firstCategoryLink"
                    class="hero-btn-primary group px-12 py-6 bg-gradient-to-r from-orange-500 via-red-500 to-pink-500 text-white font-black text-xl rounded-2xl shadow-2xl hover:shadow-orange-500/25 transition-all duration-300 transform hover:scale-110"
                >
                  <Icon name="mdi:shopping" class="inline w-6 h-6 mr-3 group-hover:animate-bounce" />
                  {{ heroSlides[0]?.cta || 'Start Shopping' }}
                  <Icon name="mdi:arrow-right" class="inline w-6 h-6 ml-3 group-hover:translate-x-3 transition-transform duration-300" />
                </NuxtLink>

                <button
                    @click="playDemo"
                    class="hero-btn-secondary group px-12 py-6 bg-white/10 backdrop-blur-md text-white font-bold text-xl rounded-2xl border-2 border-white/30 hover:bg-white/20 transition-all duration-300 transform hover:scale-105"
                >
                  <Icon name="mdi:play-circle" class="inline w-6 h-6 mr-3 group-hover:animate-pulse" />
                  Watch Demo
                </button>
              </div>
            </div>
          </div>
        </div>
      </ClientOnly>

      <!-- Enhanced Floating Stats - Fixed Position -->
      <div ref="heroStats" class="hero-stats absolute bottom-20 left-1/2 transform -translate-x-1/2 hidden xl:flex gap-6 z-30">
        <div v-for="(stat, index) in stats" :key="`stat-${index}`"
             class="stat-card bg-white/10 backdrop-blur-lg rounded-3xl px-6 py-4 text-center text-white border border-white/20 hover:bg-white/20 transition-all duration-300 transform hover:scale-110 shadow-2xl">
          <Icon :name="stat.icon" :class="`w-6 h-6 mx-auto mb-2 ${stat.color}`" />
          <div class="text-2xl font-black">{{ stat.value }}</div>
          <div class="text-xs opacity-80 font-medium">{{ stat.label }}</div>
        </div>
      </div>

      <!-- Scroll Indicator - Positioned Above Stats -->
      <div class="scroll-indicator absolute bottom-6 left-1/2 transform -translate-x-1/2 animate-bounce z-20">
        <div class="w-6 h-10 border-2 border-white/50 rounded-full flex justify-center backdrop-blur-sm">
          <div class="w-1 h-3 bg-white rounded-full mt-2 animate-pulse"></div>
        </div>
      </div>
    </section>

    <!-- Optimized Quick Categories -->
    <section class="categories-section py-20 bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg relative">
      <div class="max-w-7xl mx-auto px-6">
        <div ref="categoriesHeader" class="section-header text-center mb-16">
          <div class="header-badge inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-blue-500/10 to-purple-500/10 border border-blue-200 dark:border-blue-800 backdrop-blur-sm mb-6">
            <Icon name="mdi:view-grid" class="w-5 h-5 mr-3 text-blue-600 dark:text-blue-400" />
            <span class="font-semibold text-blue-700 dark:text-blue-300">Shop by Category</span>
          </div>

          <h2 class="text-3xl sm:text-5xl font-black text-gray-900 dark:text-white mb-6">
            <span class="block">Explore Our</span>
            <span class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">Premium Collections</span>
          </h2>

          <p class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
            Discover thousands of products across {{ categorySections.length }} categories with the best prices guaranteed
          </p>
        </div>

        <div ref="categoriesGrid" class="categories-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
          <NuxtLink
              v-for="(category, index) in quickCategories"
              :key="`category-${category.url}-${index}`"
              :to="`/category/${category.url}`"
              class="category-card group"
              :aria-label="`Shop ${category.name} starting from ₹${category.starting_from_price}`"
          >
            <div class="category-item bg-white dark:bg-gray-700 rounded-3xl p-6 shadow-xl hover:shadow-2xl transition-all duration-500 group-hover:scale-110 group-hover:-translate-y-2 border border-gray-200 dark:border-gray-600 relative overflow-hidden">
              <!-- Gradient Border Effect -->
              <div class="absolute inset-0 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10 blur-xl"></div>

              <!-- Image Container -->
              <div class="image-container aspect-square mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-600 dark:to-gray-500 relative">
                <img
                    :src="category.image"
                    :alt="category.name"
                    class="w-full h-full object-cover group-hover:scale-125 transition-transform duration-700"
                    loading="lazy"
                    :width="200"
                    :height="200"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </div>

              <!-- Content -->
              <div class="category-content text-center">
                <h3 class="category-name font-bold text-sm lg:text-base text-gray-900 dark:text-white mb-2 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                  {{ category.name }}
                </h3>
                <p class="category-price text-sm lg:text-base text-green-600 dark:text-green-400 font-bold">
                  From ₹{{ formatPrice(category.starting_from_price) }}
                </p>
                <div class="category-arrow opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:translate-x-2 mt-2">
                  <Icon name="mdi:arrow-right" class="w-4 h-4 mx-auto text-blue-600 dark:text-blue-400" />
                </div>
              </div>
            </div>
          </NuxtLink>
        </div>

        <div class="categories-cta text-center mt-16">
          <NuxtLink
              to="/categories"
              class="inline-flex items-center px-10 py-5 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold text-lg rounded-2xl shadow-xl hover:shadow-blue-500/25 transition-all duration-300 transform hover:scale-105 group"
          >
            <Icon name="mdi:view-grid-plus" class="w-6 h-6 mr-3 group-hover:rotate-12 transition-transform duration-300" />
            View All Categories
            <Icon name="mdi:arrow-right" class="w-6 h-6 ml-3 group-hover:translate-x-2 transition-transform duration-300" />
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Enhanced Flash Deals Banner -->
    <section class="flash-deals-section py-20 px-6 bg-gradient-to-r from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
      <div class="max-w-7xl mx-auto">
        <div class="flash-deals-card bg-gradient-to-r from-red-600 via-pink-600 to-purple-600 text-white p-8 lg:p-12 rounded-3xl shadow-2xl relative overflow-hidden">
          <!-- Background Effects -->
          <div class="absolute inset-0 bg-gradient-to-r from-red-600/90 via-pink-600/90 to-purple-600/90"></div>
          <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full -translate-y-48 translate-x-48 blur-3xl"></div>
          <div class="absolute bottom-0 left-0 w-80 h-80 bg-yellow-300/20 rounded-full translate-y-40 -translate-x-40 blur-2xl"></div>

          <div class="relative z-10 flex flex-col lg:flex-row justify-between items-center">
            <!-- Content -->
            <div class="flash-content text-center lg:text-left mb-8 lg:mb-0">
              <div class="flash-badge flex items-center gap-3 mb-6 justify-center lg:justify-start">
                <div class="badge-icon w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center">
                  <Icon name="mdi:flash" class="w-6 h-6 text-red-600 animate-pulse" />
                </div>
                <div class="badge-text">
                  <span class="block text-sm font-medium bg-yellow-400 text-red-600 px-4 py-1 rounded-full">LIMITED TIME OFFER</span>
                  <span class="block text-xs text-red-100 mt-1">Hurry up! Don't miss out</span>
                </div>
              </div>

              <h2 class="flash-title text-3xl sm:text-4xl lg:text-5xl font-black mb-4 leading-tight">
                <span class="block">⚡ Flash Sale</span>
                <span class="block text-yellow-300">Up to 70% Off!</span>
              </h2>

              <p class="flash-subtitle text-lg text-red-100 max-w-md">
                Incredible deals on thousands of products. Limited quantity available!
              </p>
            </div>

            <!-- Actions -->
            <div class="flash-actions flex flex-col sm:flex-row gap-4 lg:gap-6">
              <NuxtLink
                  to="/store/flash-deals"
                  class="flash-btn group px-8 py-4 bg-white text-red-600 font-black text-lg rounded-2xl hover:bg-red-50 transition-all duration-300 transform hover:scale-105 shadow-xl"
              >
                <Icon name="mdi:fire" class="inline w-6 h-6 mr-3 group-hover:animate-bounce text-red-500" />
                Shop Flash Deals
                <Icon name="mdi:arrow-right" class="inline w-6 h-6 ml-3 group-hover:translate-x-2 transition-transform duration-300" />
              </NuxtLink>

              <div class="countdown-timer bg-black/30 backdrop-blur-md rounded-2xl px-6 py-4 text-center border border-white/20">
                <div class="timer-label text-xs text-red-100 mb-1">Sale Ends In</div>
                <div class="timer-display font-black text-2xl text-yellow-300">{{ countdownTimer }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Optimized Featured Products -->
    <section v-if="featuredProducts.length" class="featured-products-section py-20 bg-white dark:bg-gray-800">
      <div class="max-w-7xl mx-auto px-6">
        <div class="section-header mb-16">
          <div class="header-content flex flex-col lg:flex-row justify-between items-center gap-8">
            <div class="header-left flex items-center gap-6">
              <div class="header-icon w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                <Icon name="mdi:star-box" class="w-8 h-8 text-white" />
              </div>
              <div class="header-text">
                <h2 class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white mb-2">
                  Featured Products
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                  Hand-picked favorites with amazing reviews
                </p>
              </div>
            </div>

            <div class="header-actions">
              <NuxtLink
                  to="/products"
                  class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold rounded-2xl shadow-xl hover:shadow-emerald-500/25 transition-all duration-300 transform hover:scale-105 group"
              >
                View All Products
                <Icon name="mdi:arrow-right" class="w-5 h-5 ml-3 group-hover:translate-x-2 transition-transform duration-300" />
              </NuxtLink>
            </div>
          </div>
        </div>

        <div ref="productsGrid" class="products-grid grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
          <div
              v-for="(product, index) in featuredProducts.slice(0, 10)"
              :key="`product-${product.id}-${index}`"
              class="product-card group bg-white dark:bg-gray-700 rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 hover:scale-105 hover:-translate-y-2 border border-gray-200 dark:border-gray-600 relative"
          >
            <!-- Gradient Border Effect -->
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 via-blue-500 to-purple-500 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10 blur-xl"></div>

            <!-- Image Container -->
            <div class="product-image relative aspect-square bg-gray-100 dark:bg-gray-600 overflow-hidden">
              <img
                  :src="product.thumbnail"
                  :alt="product.name"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                  loading="lazy"
                  :width="250"
                  :height="250"
              />

              <!-- Discount Badge -->
              <div class="discount-badge absolute top-3 left-3 bg-gradient-to-r from-red-500 to-pink-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                -{{ getRandomDiscount() }}%
              </div>

              <!-- Wishlist Button -->
              <button
                  @click.prevent="toggleWishlist(product.id)"
                  class="wishlist-btn absolute top-3 right-3 w-8 h-8 bg-white/90 dark:bg-gray-800/90 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:scale-110 hover:bg-red-50 dark:hover:bg-red-900/20"
                  :aria-label="`Add ${product.name} to wishlist`"
              >
                <Icon name="mdi:heart-outline" class="w-4 h-4 text-gray-600 dark:text-gray-400 hover:text-red-500" />
              </button>

              <!-- Quick Action Overlay -->
              <div class="quick-actions absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                <button
                    @click.prevent="quickView(product)"
                    class="quick-view-btn bg-white text-gray-900 px-4 py-2 rounded-xl font-semibold shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300 hover:bg-gray-100"
                >
                  Quick View
                </button>
              </div>
            </div>

            <!-- Product Info -->
            <div class="product-info p-4">
              <h3 class="product-name font-semibold text-sm lg:text-base text-gray-900 dark:text-white mb-3 line-clamp-2 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                {{ product.name }}
              </h3>

              <!-- Rating -->
              <div class="product-rating flex items-center gap-1 mb-2">
                <div class="stars flex">
                  <Icon v-for="i in 5" :key="`star-${i}`"
                        name="mdi:star"
                        :class="i <= 4 ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                        class="w-3 h-3" />
                </div>
                <span class="rating-text text-xs font-medium text-gray-600 dark:text-gray-400">
                  {{ getRating() }} ({{ getReviewCount() }})
                </span>
              </div>

              <!-- Price -->
              <div class="product-pricing flex items-end justify-between">
                <div class="price-info">
                  <p class="current-price text-lg lg:text-xl font-bold text-green-600 dark:text-green-400">
                    ₹{{ formatPrice(product.price) }}
                  </p>
                  <p class="original-price text-xs text-gray-500 dark:text-gray-400 line-through">
                    ₹{{ formatPrice(Math.floor(product.price * 1.6)) }}
                  </p>
                </div>

                <button
                    @click.prevent="addToCart(product)"
                    class="add-to-cart-btn w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:scale-110 hover:bg-blue-700"
                    :aria-label="`Add ${product.name} to cart`"
                >
                  <Icon name="mdi:plus" class="w-4 h-4" />
                </button>
              </div>

              <!-- Delivery Info -->
              <div class="delivery-info mt-3 flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400">
                <Icon name="mdi:truck-fast" class="w-3 h-3" />
                <span>Free Delivery Available</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Optimized Category Showcase Sections -->
    <section
        v-for="(category, categoryIndex) in categorySections"
        :key="`category-section-${category.url}-${categoryIndex}`"
        class="category-showcase-section py-20"
        :class="categoryIndex % 2 === 0 ? 'bg-gray-50 dark:bg-gray-900' : 'bg-white dark:bg-gray-800'"
    >
      <div class="max-w-7xl mx-auto px-6">

        <!-- Section Header -->
        <div class="category-header mb-12">
          <div class="header-content flex flex-col lg:flex-row justify-between items-center gap-8">
            <div class="header-left flex items-center gap-6">
              <div class="category-icon w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                <Icon name="mdi:storefront" class="w-8 h-8 text-white" />
              </div>
              <div class="header-text">
                <h2 class="text-2xl sm:text-4xl font-black text-gray-900 dark:text-white mb-2">
                  Best of {{ category.name }}
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                  {{ category.children?.length || 0 }} amazing subcategories with thousands of products
                </p>
              </div>
            </div>

            <NuxtLink
                :to="`/category/${category.url}`"
                class="category-view-all hidden lg:flex items-center gap-3 px-8 py-4 bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 font-bold rounded-2xl border-2 border-blue-200 dark:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-300 transform hover:scale-105 shadow-lg group"
            >
              <Icon name="mdi:eye" class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" />
              View All {{ category.name }}
              <Icon name="mdi:arrow-right" class="w-5 h-5 group-hover:translate-x-2 transition-transform duration-300" />
            </NuxtLink>
          </div>
        </div>

        <!-- Products Container -->
        <div class="category-products-container bg-white dark:bg-gray-700 rounded-3xl p-6 lg:p-8 shadow-xl border border-gray-200 dark:border-gray-600">
          <ClientOnly>
            <Swiper
                :slides-per-view="2"
                :space-between="20"
                :breakpoints="{
                640: { slidesPerView: 3, spaceBetween: 24 },
                768: { slidesPerView: 4, spaceBetween: 24 },
                1024: { slidesPerView: 5, spaceBetween: 24 },
                1280: { slidesPerView: 6, spaceBetween: 24 }
              }"
                :lazy="{ loadPrevNext: true }"
                class="category-swiper"
            >
              <SwiperSlide v-for="(child, childIndex) in category.children" :key="`child-${child.url}-${childIndex}`">
                <NuxtLink :to="`/category/${child.url}`" class="category-product-link group block">
                  <div class="category-product-card bg-white dark:bg-gray-600 rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 group-hover:scale-105 group-hover:-translate-y-2 border border-gray-100 dark:border-gray-500">

                    <!-- Image Container -->
                    <div class="product-image-container relative aspect-square bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-500 dark:to-gray-400 overflow-hidden">
                      <img
                          :src="child.image"
                          :alt="child.name"
                          class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                          loading="lazy"
                          :width="200"
                          :height="200"
                      />

                      <!-- Discount Badge -->
                      <div class="discount-badge absolute top-3 left-3 bg-gradient-to-r from-red-500 to-orange-500 text-white px-2 py-1 rounded-lg text-xs font-bold shadow-lg">
                        UP TO {{ getRandomDiscount(30, 50) }}% OFF
                      </div>

                      <!-- Quick Actions -->
                      <div class="quick-actions-overlay absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-4">
                        <div class="quick-actions-buttons flex gap-2">
                          <button
                              @click.prevent="quickView(child)"
                              class="quick-action-btn w-10 h-10 bg-white/90 text-gray-900 rounded-full flex items-center justify-center hover:bg-white transition-colors duration-200"
                              :aria-label="`Quick view ${child.name}`"
                          >
                            <Icon name="mdi:eye" class="w-4 h-4" />
                          </button>
                          <button
                              @click.prevent="toggleWishlist(child.url)"
                              class="quick-action-btn w-10 h-10 bg-white/90 text-gray-900 rounded-full flex items-center justify-center hover:bg-white transition-colors duration-200"
                              :aria-label="`Add ${child.name} to wishlist`"
                          >
                            <Icon name="mdi:heart-outline" class="w-4 h-4" />
                          </button>
                          <button
                              @click.prevent="addToCart(child)"
                              class="quick-action-btn w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700 transition-colors duration-200"
                              :aria-label="`Add ${child.name} to cart`"
                          >
                            <Icon name="mdi:cart-plus" class="w-4 h-4" />
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- Product Info -->
                    <div class="product-info-content p-4">
                      <h3 class="product-title font-bold text-sm lg:text-base text-gray-900 dark:text-white mb-3 line-clamp-2 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                        {{ child.name }}
                      </h3>

                      <!-- Pricing & Rating -->
                      <div class="product-details flex justify-between items-end">
                        <div class="pricing-info">
                          <p class="current-price text-lg lg:text-xl font-black text-green-600 dark:text-green-400">
                            ₹{{ formatPrice(child.starting_from_price) }}
                          </p>
                          <p class="original-price text-xs text-gray-500 dark:text-gray-400 line-through">
                            ₹{{ formatPrice(Math.floor(child.starting_from_price * 1.5)) }}
                          </p>
                        </div>

                        <!-- Rating -->
                        <div class="rating-info flex items-center gap-1">
                          <Icon name="mdi:star" class="w-4 h-4 text-yellow-400" />
                          <span class="rating-value text-sm font-semibold text-gray-600 dark:text-gray-400">
                            {{ getRating() }}
                          </span>
                        </div>
                      </div>

                      <!-- Delivery Info -->
                      <div class="delivery-info mt-3 flex items-center justify-between">
                        <div class="delivery-text flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400">
                          <Icon name="mdi:truck-fast" class="w-3 h-3" />
                          <span>Free Delivery</span>
                        </div>
                        <div class="stock-info text-xs text-orange-600 dark:text-orange-400 font-medium">
                          {{ getStockCount() }} left in stock
                        </div>
                      </div>
                    </div>
                  </div>
                </NuxtLink>
              </SwiperSlide>
            </Swiper>
          </ClientOnly>

          <!-- Mobile View All Button -->
          <div class="mobile-view-all mt-8 lg:hidden">
            <NuxtLink
                :to="`/category/${category.url}`"
                class="w-full flex items-center justify-center gap-3 py-4 text-blue-600 dark:text-blue-400 font-bold border-2 border-blue-200 dark:border-blue-600 rounded-2xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-300"
            >
              <Icon name="mdi:eye" class="w-5 h-5" />
              View All {{ category.name }}
              <Icon name="mdi:arrow-right" class="w-5 h-5" />
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>

    <!-- Enhanced Why Choose Us -->
    <section class="trust-features-section py-20 bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-blue-900/20 dark:via-purple-900/20 dark:to-pink-900/20">
      <div class="max-w-7xl mx-auto px-6">

        <!-- Section Header -->
        <div class="section-header text-center mb-20">
          <div class="header-badge inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-green-500/10 to-blue-500/10 border border-green-200 dark:border-green-800 backdrop-blur-sm mb-8">
            <Icon name="mdi:shield-check" class="w-5 h-5 mr-3 text-green-600 dark:text-green-400" />
            <span class="font-semibold text-green-700 dark:text-green-300">Why Choose Us</span>
          </div>

          <h2 class="text-3xl sm:text-5xl font-black text-gray-900 dark:text-white mb-6">
            <span class="block">Your Trusted</span>
            <span class="bg-gradient-to-r from-green-600 via-blue-600 to-purple-600 bg-clip-text text-transparent">Shopping Partner</span>
          </h2>

          <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed">
            We're committed to providing you with the best shopping experience through our premium services and customer-first approach
          </p>
        </div>

        <!-- Trust Features Grid -->
        <div ref="trustFeaturesGrid" class="trust-features-grid grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
          <div
              v-for="(feature, index) in trustFeatures"
              :key="`feature-${index}`"
              class="trust-feature-card group text-center p-8 rounded-3xl bg-white dark:bg-gray-700 hover:bg-gradient-to-br hover:from-blue-50 hover:to-purple-50 dark:hover:from-gray-600 dark:hover:to-gray-500 shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:scale-110 hover:-translate-y-4 border border-gray-200 dark:border-gray-600"
          >
            <!-- Icon Container -->
            <div class="feature-icon-container w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-125 group-hover:rotate-6 transition-all duration-500 shadow-lg">
              <Icon :name="feature.icon" class="w-10 h-10 text-white" />
            </div>

            <!-- Content -->
            <h3 class="feature-title text-xl lg:text-2xl font-black text-gray-900 dark:text-white mb-4 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
              {{ feature.title }}
            </h3>

            <p class="feature-description text-gray-600 dark:text-gray-300 leading-relaxed">
              {{ feature.description }}
            </p>

            <!-- Decorative Element -->
            <div class="feature-decoration w-12 h-1 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full mx-auto mt-6 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          </div>
        </div>

        <!-- CTA Section -->
        <div class="trust-cta text-center mt-20">
          <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-6">
            Ready to Start Shopping?
          </h3>
          <p class="text-lg text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
            Join millions of satisfied customers and discover amazing products with unbeatable prices
          </p>
          <NuxtLink
              :to="firstCategoryLink"
              class="inline-flex items-center px-12 py-6 bg-gradient-to-r from-green-600 via-blue-600 to-purple-600 text-white font-black text-xl rounded-2xl shadow-2xl hover:shadow-green-500/25 transition-all duration-300 transform hover:scale-110 group"
          >
            <Icon name="mdi:rocket-launch" class="w-6 h-6 mr-3 group-hover:animate-bounce" />
            Start Shopping Now
            <Icon name="mdi:arrow-right" class="w-6 h-6 ml-3 group-hover:translate-x-3 transition-transform duration-300" />
          </NuxtLink>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
// [Keep the exact same script section as before - no changes needed]
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, Navigation, Pagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'

// Only import GSAP on client side
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

// Types
interface ChildCategory {
  name: string
  url: string
  image: string
  starting_from_price: number
}

interface ParentCategory {
  name: string
  url: string
  thumbnail: string
  children: ChildCategory[]
}

interface Product {
  id: number
  name: string
  thumbnail: string
  price: number
}

// Props
const props = defineProps<{
  categories: ParentCategory[]
}>()

const SwiperAutoplay = Autoplay
const SwiperNavigation = Navigation
const SwiperPagination = Pagination

const config = useRuntimeConfig()

// Refs for animations
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()
const orb3 = ref<HTMLElement>()
const heroContent = ref<HTMLElement>()
const heroStats = ref<HTMLElement>()
const categoriesHeader = ref<HTMLElement>()
const categoriesGrid = ref<HTMLElement>()
const productsGrid = ref<HTMLElement>()
const trustFeaturesGrid = ref<HTMLElement>()
const heroSwiper = ref()
const particleRefs = ref<HTMLElement[]>([])

// Reactive data
const categorySections = computed(() => props.categories || [])
const featuredProducts = ref<Product[]>([])
const countdownTimer = ref('23:45:12')
let countdownInterval: NodeJS.Timeout | null = null
let gsapContext: any = null

// Optimized particles array
const particles = [
  { class: 'w-4 h-4 bg-blue-400', style: 'top: 20%; left: 15%; animation-delay: 0s;' },
  { class: 'w-3 h-3 bg-purple-400', style: 'top: 30%; right: 20%; animation-delay: 1.5s;' },
  { class: 'w-5 h-5 bg-emerald-400', style: 'bottom: 25%; left: 25%; animation-delay: 3s;' },
  { class: 'w-3 h-3 bg-pink-400', style: 'bottom: 35%; right: 30%; animation-delay: 4.5s;' }
]

// Hero slides (optimized)
const heroSlides = ref([
  {
    id: 1,
    title: 'Welcome to Our Premium Store',
    subtitle: 'Discover amazing products with unbeatable deals and lightning-fast delivery!',
    cta: 'Start Shopping',
    image: null // Add image URL here for background
  }
])

// Stats data
const stats = [
  { icon: 'mdi:account-heart', value: '2M+', label: 'Happy Customers', color: 'text-pink-300' },
  { icon: 'mdi:package-variant', value: '100K+', label: 'Products', color: 'text-blue-300' },
  { icon: 'mdi:headset', value: '24/7', label: 'Support', color: 'text-green-300' },
  { icon: 'mdi:truck-fast', value: 'Same Day', label: 'Delivery', color: 'text-yellow-300' }
]

// Computed properties
const firstCategoryLink = computed(() => {
  if (categorySections.value.length === 0) {
    return '/categories'
  }

  const firstParent = categorySections.value[0]
  if (firstParent.children && firstParent.children.length > 0) {
    return `/category/${firstParent.children[0].url}`
  }

  return `/category/${firstParent.url}`
})

const quickCategories = computed(() => {
  return categorySections.value
      .flatMap(parent => parent.children?.slice(0, 6) || [])
      .slice(0, 18)
})

// Enhanced trust features
const trustFeatures = [
  {
    icon: 'mdi:truck-fast',
    title: 'Lightning Fast Delivery',
    description: 'Free same-day delivery on orders above ₹499. Get your products delivered in hours, not days.'
  },
  {
    icon: 'mdi:shield-check',
    title: '100% Secure & Safe',
    description: 'Your payments and personal data are protected with bank-level security and encryption.'
  },
  {
    icon: 'mdi:refresh-circle',
    title: 'Hassle-Free Returns',
    description: '15-day return policy with free pickup. No questions asked, full refund guaranteed.'
  },
  {
    icon: 'mdi:headset',
    title: '24/7 Expert Support',
    description: 'Round-the-clock customer support via chat, phone, and email. We\'re always here to help.'
  }
]

// Utility functions
const formatPrice = (price: number): string => {
  return new Intl.NumberFormat('en-IN').format(price)
}

const getRandomDiscount = (min: number = 20, max: number = 40): number => {
  return Math.floor(Math.random() * (max - min) + min)
}

const getRating = (): string => {
  return (Math.random() * 2 + 3).toFixed(1)
}

const getReviewCount = (): number => {
  return Math.floor(Math.random() * 500 + 50)
}

const getStockCount = (): number => {
  return Math.floor(Math.random() * 50 + 10)
}

// API calls
const loadFeaturedProducts = async () => {
  try {
    const { data } = await useLazyFetch<Product[]>(`${config.public.apiBase}/products/suggestions/get`, {
      key: 'featured-products',
      default: () => [],
      server: false
    })
    if (data.value) {
      featuredProducts.value = data.value
    }
  } catch (error) {
    console.error('Failed to load featured products:', error)
  }
}

// Countdown timer
const updateCountdown = () => {
  const now = new Date()
  const endOfDay = new Date()
  endOfDay.setHours(23, 59, 59, 999)

  const diff = endOfDay.getTime() - now.getTime()
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
  const seconds = Math.floor((diff % (1000 * 60)) / 1000)

  countdownTimer.value = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
}

// Event handlers
const playDemo = () => {
  console.log('Playing demo...')
}

const toggleWishlist = (id: string | number) => {
  console.log('Toggle wishlist for:', id)
}

const quickView = (product: any) => {
  console.log('Quick view for:', product.name || product.title)
}

const addToCart = (product: any) => {
  console.log('Add to cart:', product.name || product.title)
}

const onSwiperInit = (swiper: any) => {
  console.log('Swiper initialized:', swiper)
}

// Optimized animations with client-side check
const initializeAnimations = () => {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
    // Floating orbs with performance optimization
    if (orb1.value) {
      gsap.to(orb1.value, {
        y: -50,
        rotation: 20,
        duration: 8,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: -1
      })
    }

    if (orb2.value) {
      gsap.to(orb2.value, {
        y: 40,
        rotation: -15,
        duration: 10,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: -1
      })
    }

    if (orb3.value) {
      gsap.to(orb3.value, {
        y: -30,
        rotation: 25,
        duration: 6,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: -1
      })
    }

    // Hero content animation
    if (heroContent.value) {
      gsap.fromTo(heroContent.value,
          { y: 100, opacity: 0 },
          { y: 0, opacity: 1, duration: 1.5, ease: 'back.out(1.7)', delay: 0.5 }
      )
    }

    // Stats cards animation
    if (heroStats.value && heroStats.value.children) {
      gsap.fromTo(heroStats.value.children,
          { y: 50, opacity: 0, scale: 0.9 },
          {
            y: 0,
            opacity: 1,
            scale: 1,
            duration: 0.8,
            ease: 'back.out(1.7)',
            stagger: 0.2,
            delay: 1.5
          }
      )
    }

    // Scroll-triggered animations with performance optimization
    if (categoriesGrid.value && categoriesGrid.value.children && ScrollTrigger) {
      gsap.fromTo(categoriesGrid.value.children,
          { y: 60, opacity: 0, scale: 0.9 },
          {
            y: 0,
            opacity: 1,
            scale: 1,
            duration: 0.8,
            ease: 'back.out(1.7)',
            stagger: 0.1,
            scrollTrigger: {
              trigger: categoriesGrid.value,
              start: 'top 80%',
              end: 'bottom 20%',
              toggleActions: 'play none none reverse',
              once: true
            }
          }
      )
    }

    if (productsGrid.value && productsGrid.value.children && ScrollTrigger) {
      gsap.fromTo(productsGrid.value.children,
          { y: 40, opacity: 0 },
          {
            y: 0,
            opacity: 1,
            duration: 0.6,
            ease: 'back.out(1.7)',
            stagger: 0.1,
            scrollTrigger: {
              trigger: productsGrid.value,
              start: 'top 80%',
              end: 'bottom 20%',
              toggleActions: 'play none none reverse',
              once: true
            }
          }
      )
    }

    if (trustFeaturesGrid.value && trustFeaturesGrid.value.children && ScrollTrigger) {
      gsap.fromTo(trustFeaturesGrid.value.children,
          { y: 50, opacity: 0, scale: 0.9 },
          {
            y: 0,
            opacity: 1,
            scale: 1,
            duration: 0.8,
            ease: 'back.out(1.7)',
            stagger: 0.2,
            scrollTrigger: {
              trigger: trustFeaturesGrid.value,
              start: 'top 80%',
              end: 'bottom 20%',
              toggleActions: 'play none none reverse',
              once: true
            }
          }
      )
    }

    // Optimized particle animations
    particleRefs.value.forEach((particle, index) => {
      if (particle) {
        gsap.to(particle, {
          y: -200,
          opacity: 0,
          duration: 8,
          ease: 'power2.out',
          delay: index * 2,
          repeat: -1,
          repeatDelay: 4
        })
      }
    })
  })
}

// Lifecycle
onMounted(async () => {
  await nextTick()

  // Load featured products
  await loadFeaturedProducts()

  // Initialize countdown timer
  updateCountdown()
  countdownInterval = setInterval(updateCountdown, 1000)

  // Initialize animations after GSAP is loaded
  setTimeout(() => {
    initializeAnimations()
  }, 100)
})

onUnmounted(() => {
  // Cleanup
  if (countdownInterval) {
    clearInterval(countdownInterval)
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
/* Line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Performance optimizations */
.will-change-transform {
  will-change: transform;
}

/* Enhanced button effects with better performance */
.hero-btn-primary,
.flash-btn {
  position: relative;
  overflow: hidden;
}

.hero-btn-primary::before,
.flash-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.6s ease;
  will-change: transform;
}

.hero-btn-primary:hover::before,
.flash-btn:hover::before {
  left: 100%;
}

/* Category card effects */
.category-item {
  position: relative;
}

.category-item::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
  transform: scaleX(0);
  transition: transform 0.4s ease;
  border-radius: 1.5rem 1.5rem 0 0;
  will-change: transform;
}

.category-item:hover::after {
  transform: scaleX(1);
}

/* Product card hover effects with better performance */
.product-card {
  position: relative;
}

.product-card::before {
  content: '';
  position: absolute;
  inset: 0;
  padding: 2px;
  background: linear-gradient(135deg, transparent, rgba(59, 130, 246, 0.3), transparent);
  border-radius: 1rem;
  mask: linear-gradient(white 0 0) content-box, linear-gradient(white 0 0);
  mask-composite: exclude;
  opacity: 0;
  transition: opacity 0.4s ease;
}

.product-card:hover::before {
  opacity: 1;
}

/* Trust feature card effects */
.trust-feature-card {
  position: relative;
}

.trust-feature-card::before {
  content: '';
  position: absolute;
  inset: 0;
  padding: 2px;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6, #ec4899);
  border-radius: 1.5rem;
  mask: linear-gradient(white 0 0) content-box, linear-gradient(white 0 0);
  mask-composite: exclude;
  opacity: 0;
  transition: opacity 0.5s ease;
}

.trust-feature-card:hover::before {
  opacity: 1;
}

/* Optimized Swiper customizations */
:deep(.swiper-button-next),
:deep(.swiper-button-prev) {
  color: #4f46e5;
  background: rgba(255, 255, 255, 0.95);
  width: 48px;
  height: 48px;
  border-radius: 50%;
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

:deep(.swiper-button-next):hover,
:deep(.swiper-button-prev):hover {
  transform: scale(1.1);
}

:deep(.swiper-button-next:after),
:deep(.swiper-button-prev:after) {
  font-size: 18px;
  font-weight: bold;
}

:deep(.swiper-pagination-bullet) {
  background: rgba(255, 255, 255, 0.6);
  opacity: 1;
  width: 12px;
  height: 12px;
  transition: all 0.3s ease;
}

:deep(.swiper-pagination-bullet-active) {
  background: white;
  transform: scale(1.2);
}

/* Optimized particle animations */
.particle {
  animation: float 6s ease-in-out infinite;
  will-change: transform;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-30px) rotate(180deg);
  }
}

/* Mobile responsiveness with better performance */
@media (max-width: 1280px) {
  .hero-stats {
    display: none !important; /* Hide stats on smaller screens to prevent overlap */
  }
}

@media (max-width: 640px) {
  .hero-title {
    font-size: 2.5rem;
    line-height: 1.2;
  }

  .hero-content {
    padding-bottom: 8rem; /* Reduce bottom padding on mobile */
  }

  .categories-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }

  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .store-orb-2 {
    display: none; /* Improve mobile performance */
  }

  .hero-actions {
    flex-direction: column;
    gap: 1rem;
  }

  .trust-features-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  .hero-content {
    padding-bottom: 6rem; /* Further reduce on very small screens */
  }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .category-item,
  .product-card,
  .trust-feature-card {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }
}

/* Optimized smooth transitions */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Reduce motion for accessibility */
@media (prefers-reduced-motion: reduce) {
  .particle,
  .animate-bounce,
  .animate-pulse {
    animation: none !important;
  }

  * {
    transition-duration: 0.01ms !important;
  }
}

/* Focus states for accessibility */
.category-card:focus-visible,
.product-card:focus-visible,
.trust-feature-card:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Loading states */
.loading {
  @apply animate-pulse bg-gray-200 dark:bg-gray-700;
}
</style>
