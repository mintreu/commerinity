<template>
  <div class="min-h-screen w-full bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-gray-900 dark:to-purple-950 text-gray-800 dark:text-gray-100 overflow-x-hidden">

    <!-- Enhanced Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
      <!-- Animated Background Elements -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="hero-orb-1 absolute top-20 left-20 w-72 h-72 bg-gradient-to-r from-blue-400 to-cyan-400 rounded-full opacity-20 blur-3xl"></div>
        <div class="hero-orb-2 absolute bottom-20 right-20 w-96 h-96 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full opacity-15 blur-3xl"></div>
        <div class="hero-orb-3 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-gradient-to-r from-emerald-400 to-teal-400 rounded-full opacity-10 blur-2xl"></div>
      </div>

      <!-- Hero Content -->
      <div class="relative z-10 text-center max-w-6xl mx-auto px-6 py-20">
        <div class="hero-content space-y-8">
          <!-- Badge -->
          <div class="hero-badge inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-blue-500/10 to-purple-500/10 border border-blue-200 dark:border-blue-800 backdrop-blur-sm">
            <Icon name="mdi:rocket-launch" class="w-5 h-5 mr-3 text-blue-600 dark:text-blue-400" />
            <span class="font-semibold text-blue-700 dark:text-blue-300">Join Our Mission</span>
          </div>

          <!-- Main Title -->
          <h1 class="hero-title text-4xl sm:text-6xl lg:text-7xl font-black leading-tight">
            <span class="block text-gray-900 dark:text-white">Shape the</span>
            <span class="block bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">Future of Work</span>
            <span class="block text-gray-900 dark:text-white">With Us</span>
          </h1>

          <!-- Subtitle -->
          <p class="hero-subtitle text-lg sm:text-xl lg:text-2xl max-w-4xl mx-auto text-gray-600 dark:text-gray-300 font-medium">
            Drive impact. Build tomorrow. Work alongside brilliant minds who are passionate about creating meaningful change in the world.
          </p>

          <!-- CTA Buttons -->
          <div class="hero-cta flex flex-col sm:flex-row gap-6 justify-center items-center pt-8">
            <a href="#openings" class="cta-primary group relative px-10 py-5 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold text-lg rounded-2xl shadow-2xl hover:shadow-blue-500/25 transition-all duration-300 transform hover:scale-105 overflow-hidden">
              <span class="relative z-10">View Open Positions</span>
            </a>
            <button class="cta-secondary group px-10 py-5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-bold text-lg rounded-2xl border-2 border-gray-200 dark:border-gray-700 hover:border-purple-300 dark:hover:border-purple-600 transition-all duration-300 backdrop-blur-sm">
              <Icon name="mdi:play-circle" class="inline w-6 h-6 mr-3 group-hover:text-purple-600 transition-colors duration-300" />
              Watch Culture Video
            </button>
          </div>

          <!-- Enhanced Highlights -->
          <div class="hero-highlights grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 pt-16 max-w-5xl mx-auto">
            <div v-for="(highlight, index) in heroHighlights" :key="highlight.title"
                 class="highlight-card relative group bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg p-8 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 border border-white/20 overflow-hidden">

              <div class="highlight-icon w-16 h-16 mx-auto mb-6 rounded-2xl flex items-center justify-center text-3xl transition-all duration-500 group-hover:scale-110 group-hover:rotate-6"
                   :style="{ background: `linear-gradient(135deg, ${highlight.color}20, ${highlight.color}40)`, color: highlight.color }">
                <Icon :name="highlight.icon" />
              </div>

              <h3 class="text-xl font-bold mb-4 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">
                {{ highlight.title }}
              </h3>
              <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                {{ highlight.description }}
              </p>

              <!-- Hover Effect Overlay -->
              <div class="absolute inset-0 bg-gradient-to-r from-blue-600/5 to-purple-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-3xl pointer-events-none"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Scroll Indicator -->
      <div class="scroll-indicator absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
        <div class="w-6 h-10 border-2 border-gray-400 dark:border-gray-600 rounded-full flex justify-center">
          <div class="w-1 h-3 bg-gradient-to-b from-blue-600 to-transparent rounded-full mt-2"></div>
        </div>
      </div>
    </section>

    <!-- Enhanced Application Status Section -->
    <section v-if="isLoggedIn" class="application-status py-20 px-6 bg-white dark:bg-gray-900 relative">
      <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12">
          <div class="section-badge inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-emerald-500/10 to-teal-500/10 border border-emerald-200 dark:border-emerald-800 backdrop-blur-sm mb-6">
            <Icon name="mdi:magnify" class="w-4 h-4 mr-2 text-emerald-600 dark:text-emerald-400" />
            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300">Application Tracker</span>
          </div>
          <h2 class="text-3xl sm:text-4xl font-black mb-4">
            Track Your <span class="bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">Application Journey</span>
          </h2>
        </div>

        <div class="application-card bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 p-8 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700">
          <form @submit.prevent="handleSearch" class="space-y-6">
            <div class="relative">
              <input
                  v-model="query"
                  :placeholder="login ? 'Enter your registered email or mobile' : 'Enter your Application ID'"
                  type="text"
                  class="w-full px-6 py-4 text-lg rounded-2xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-blue-500 dark:focus:border-blue-400 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300"
              />
              <Icon name="mdi:magnify" class="absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 text-xl" />
            </div>

            <button type="submit" class="search-btn w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold text-lg rounded-2xl shadow-xl hover:shadow-emerald-500/25 transition-all duration-300 transform hover:scale-105">
              <Icon name="mdi:magnify" class="inline w-5 h-5 mr-2" />
              Search Application
            </button>
          </form>

          <p v-if="error" class="error-message mt-6 p-4 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-xl border border-red-200 dark:border-red-800">
            {{ error }}
          </p>

          <div v-if="application" class="application-result mt-8 p-6 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-2xl border border-blue-200 dark:border-blue-800">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">Application Found!</h3>
              <div class="status-badge px-4 py-2 bg-blue-600 text-white rounded-full text-sm font-semibold">
                {{ application.status }}
              </div>
            </div>
            <p class="text-gray-700 dark:text-gray-300 mb-4">
              Your application for <strong class="text-blue-600 dark:text-blue-400">{{ application.role }}</strong> is currently <strong>{{ application.status }}</strong>.
            </p>
            <NuxtLink :to="`/career/applications/${application.uuid}`"
                      class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-xl hover:shadow-lg transition-all duration-300 transform hover:scale-105">
              View Full Details
              <Icon name="mdi:arrow-right" class="ml-2 w-5 h-5" />
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>

    <!-- Enhanced Job Listings -->
    <section v-if="jobs.length" class="jobs-section py-20 px-6 bg-gradient-to-b from-gray-50 to-white dark:from-gray-950 dark:to-gray-900" id="openings">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <div class="section-badge inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-purple-500/10 to-pink-500/10 border border-purple-200 dark:border-purple-800 backdrop-blur-sm mb-6">
            <Icon name="mdi:clipboard-text" class="w-4 h-4 mr-2 text-purple-600 dark:text-purple-400" />
            <span class="text-sm font-medium text-purple-700 dark:text-purple-300">Open Positions</span>
          </div>
          <h2 class="text-3xl sm:text-5xl font-black mb-6">
            <span class="text-gray-900 dark:text-white">Find Your Perfect</span><br>
            <span class="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Career Match</span>
          </h2>
        </div>

        <!-- Enhanced Filter Controls -->
        <div class="filter-controls mb-12 space-y-6">
          <div class="flex justify-center items-center gap-8 flex-wrap">
            <label class="filter-option group cursor-pointer flex items-center gap-3 px-4 py-2 rounded-xl transition-all duration-300 hover:bg-purple-50 dark:hover:bg-purple-900/20">
              <input type="radio" value="type" v-model="filterBy" class="w-4 h-4 text-purple-600" />
              <span class="font-medium group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">Filter by Type</span>
            </label>
            <label class="filter-option group cursor-pointer flex items-center gap-3 px-4 py-2 rounded-xl transition-all duration-300 hover:bg-purple-50 dark:hover:bg-purple-900/20">
              <input type="radio" value="role" v-model="filterBy" class="w-4 h-4 text-purple-600" />
              <span class="font-medium group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">Filter by Role</span>
            </label>
          </div>

          <div class="flex flex-wrap justify-center gap-4">
            <button
                v-for="opt in teams"
                :key="opt"
                @click="filterTeam = opt"
                :class="[
                'filter-tag px-6 py-3 rounded-2xl font-semibold transition-all duration-300 transform hover:scale-105',
                filterTeam === opt
                  ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg'
                  : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-2 border-gray-200 dark:border-gray-700 hover:border-purple-300 dark:hover:border-purple-600'
              ]"
            >
              {{ opt }}
            </button>
          </div>
        </div>

        <!-- Enhanced Job Cards Swiper -->
        <div class="jobs-swiper relative">
          <Swiper
              ref="jobsSwiper"
              :modules="[SwiperAutoplay, SwiperEffectCoverflow, SwiperPagination]"
              :slides-per-view="1.1"
              :space-between="24"
              :centered-slides="true"
              :autoplay="{
              delay: 4000,
              disableOnInteraction: false,
            }"
              :effect="'coverflow'"
              :coverflow-effect="{
              rotate: 10,
              stretch: 0,
              depth: 80,
              modifier: 1,
              slideShadows: true,
            }"
              :pagination="{
              clickable: true,
              dynamicBullets: true,
            }"
              :breakpoints="{
              640: { slidesPerView: 1.3 },
              768: { slidesPerView: 2.2 },
              1024: { slidesPerView: 3 },
              1280: { slidesPerView: 3.5 },
            }"
              class="pb-16"
          >
            <SwiperSlide v-for="(job, index) in filteredJobs" :key="job.url">
              <div class="job-card relative group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 overflow-hidden transform hover:scale-105 border border-gray-200/50 dark:border-gray-700/50 h-96">

                <!-- Job Type Badge -->
                <div class="absolute top-6 right-6 z-20">
                  <span class="job-type-badge px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs font-bold rounded-full shadow-lg">
                    {{ job.type }}
                  </span>
                </div>

                <!-- Job Image -->
                <div class="relative h-48 overflow-hidden">
                  <NuxtLink :to="`/career/${job.url}`">
                    <img
                        :src="job.thumbnail"
                        :alt="job.title"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                        loading="lazy"
                    />
                  </NuxtLink>
                  <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                  <!-- Location Badge -->
                  <div class="absolute bottom-4 left-4">
                    <div class="location-badge flex items-center px-3 py-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-full text-sm font-medium text-gray-700 dark:text-gray-300">
                      <Icon name="mdi:map-marker" class="w-4 h-4 mr-2 text-red-500" />
                      {{ job.location }}
                    </div>
                  </div>
                </div>

                <!-- Job Content -->
                <div class="relative p-8 flex flex-col h-48">
                  <h3 class="job-title text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300 line-clamp-2">
                    {{ job.title }}
                  </h3>

                  <p class="job-summary text-gray-600 dark:text-gray-300 leading-relaxed mb-6 flex-1 line-clamp-3">
                    {{ job.summary }}
                  </p>

                  <NuxtLink
                      :to="`/career/${job.url}`"
                      class="apply-btn group/btn inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold rounded-2xl shadow-xl hover:shadow-purple-500/25 transition-all duration-300 transform hover:scale-105"
                  >
                    <Icon name="mdi:send" class="w-5 h-5 mr-3 group-hover/btn:rotate-12 transition-transform duration-300" />
                    View & Apply
                    <Icon name="mdi:arrow-right" class="w-5 h-5 ml-3 group-hover/btn:translate-x-2 transition-transform duration-300" />
                  </NuxtLink>
                </div>

                <!-- Animated Background Gradient -->
                <div class="absolute inset-0 bg-gradient-to-r from-purple-600/10 to-pink-600/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
              </div>
            </SwiperSlide>
          </Swiper>
        </div>
      </div>
    </section>

    <!-- No Jobs Fallback -->
    <div v-else class="no-jobs py-20">
      <NoJobsHero />
    </div>

    <!-- Enhanced Core Values -->
    <section class="values-section py-20 px-6 bg-white dark:bg-gray-900 relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-5 dark:opacity-10 pointer-events-none">
        <div class="values-pattern w-full h-full" style="background-image: radial-gradient(circle, currentColor 2px, transparent 2px); background-size: 40px 40px;"></div>
      </div>

      <div class="relative z-10 max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <div class="section-badge inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-red-500/10 to-orange-500/10 border border-red-200 dark:border-red-800 backdrop-blur-sm mb-6">
            <Icon name="mdi:heart-outline" class="w-4 h-4 mr-2 text-red-600 dark:text-red-400" />
            <span class="text-sm font-medium text-red-700 dark:text-red-300">Our DNA</span>
          </div>
          <h2 class="text-3xl sm:text-5xl font-black mb-6">
            <span class="text-gray-900 dark:text-white">Values That</span><br>
            <span class="bg-gradient-to-r from-red-600 to-orange-600 bg-clip-text text-transparent">Drive Us Forward</span>
          </h2>
          <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
            The principles that guide our decisions, shape our culture, and define who we are as a team.
          </p>
        </div>

        <div class="values-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
          <div v-for="(value, index) in enhancedValues" :key="value.title"
               class="value-card relative group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 p-8 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-4 border border-gray-200/50 dark:border-gray-700/50 text-center overflow-hidden">

            <!-- Icon -->
            <div class="value-icon w-20 h-20 mx-auto mb-6 rounded-2xl flex items-center justify-center text-4xl transition-all duration-500 group-hover:scale-110 group-hover:rotate-12"
                 :style="{
                   background: `linear-gradient(135deg, ${value.color}20, ${value.color}40)`,
                   color: value.color,
                   boxShadow: `0 10px 30px ${value.color}30`
                 }">
              <Icon :name="value.icon" />
            </div>

            <!-- Content -->
            <h4 class="text-xl font-bold mb-4 text-gray-900 dark:text-white transition-colors duration-300 group-hover:text-purple-600 dark:group-hover:text-purple-400">
              {{ value.title }}
            </h4>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
              {{ value.text }}
            </p>

            <!-- Hover Border Effect -->
            <div class="absolute inset-0 rounded-3xl pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity duration-500"
                 :style="{
                   boxShadow: `0 0 0 3px ${value.color}40, 0 20px 40px ${value.color}20`
                 }"></div>

            <!-- Background Pattern -->
            <div class="absolute top-0 right-0 w-24 h-24 opacity-5 group-hover:opacity-10 transition-opacity duration-500 pointer-events-none">
              <Icon :name="value.icon" class="w-full h-full" :style="{ color: value.color }" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Enhanced Life at Company -->
    <section class="company-life py-20 px-6 bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 dark:from-purple-950/20 dark:via-pink-950/20 dark:to-blue-950/20">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl sm:text-5xl font-black mb-6">
            <span class="text-gray-900 dark:text-white">Life at</span><br>
            <span class="bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">{{ companyName }}</span>
          </h2>
          <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
            More than just a workplace - it's where careers flourish and dreams take flight.
          </p>
        </div>

        <div class="life-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="(item, index) in enhancedLifeAtCompany" :key="index"
               class="life-card relative group bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg p-8 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 border border-white/50 dark:border-gray-700/50">

            <!-- Icon -->
            <div class="life-icon w-16 h-16 mb-6 bg-gradient-to-r from-purple-500 to-blue-500 rounded-2xl flex items-center justify-center text-white text-2xl group-hover:scale-110 group-hover:rotate-6 transition-transform duration-500">
              <Icon :name="item.icon" />
            </div>

            <!-- Content -->
            <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-4 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">
              {{ item.title }}
            </h4>
            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
              {{ item.text }}
            </p>

            <!-- Hover Effect -->
            <div class="absolute inset-0 bg-gradient-to-r from-purple-600/5 to-blue-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-3xl pointer-events-none"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Enhanced Final CTA -->
    <section class="final-cta py-24 px-6 bg-gradient-to-r from-gray-900 via-purple-900 to-blue-900 text-white relative overflow-hidden">
      <!-- Animated Background -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="cta-particles">
          <div class="particle absolute w-2 h-2 bg-white rounded-full opacity-30 animate-pulse" style="top: 20%; left: 10%; animation-delay: 0s;"></div>
          <div class="particle absolute w-1 h-1 bg-purple-300 rounded-full opacity-40 animate-pulse" style="top: 60%; left: 80%; animation-delay: 1s;"></div>
          <div class="particle absolute w-3 h-3 bg-blue-300 rounded-full opacity-20 animate-pulse" style="top: 80%; left: 20%; animation-delay: 2s;"></div>
          <div class="particle absolute w-2 h-2 bg-pink-300 rounded-full opacity-35 animate-pulse" style="top: 40%; left: 70%; animation-delay: 3s;"></div>
        </div>
      </div>

      <div class="relative z-10 max-w-5xl mx-auto text-center">
        <h2 class="cta-title text-4xl sm:text-6xl font-black mb-8 leading-tight">
          Ready to <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 via-pink-400 to-purple-400">Transform</span><br>
          Your Career Journey?
        </h2>

        <p class="cta-subtitle text-xl sm:text-2xl text-gray-300 mb-12 max-w-4xl mx-auto leading-relaxed">
          We're not just offering jobs — we're building a place where you can thrive, innovate, and make a lasting impact. Join thousands who've already started their journey with us.
        </p>

        <div class="cta-actions flex flex-col sm:flex-row gap-6 justify-center items-center mb-16">
          <a href="#openings" class="cta-primary-btn relative group px-12 py-6 bg-gradient-to-r from-yellow-400 to-orange-500 text-gray-900 font-black text-xl rounded-2xl shadow-2xl hover:shadow-yellow-500/25 transition-all duration-300 transform hover:scale-105 overflow-hidden">
            <span class="relative z-10">Explore Opportunities</span>
            <Icon name="mdi:rocket" class="relative z-10 inline w-6 h-6 ml-3 group-hover:translate-x-2 transition-transform duration-300" />
          </a>

          <button class="cta-secondary-btn group px-12 py-6 bg-transparent border-2 border-white/30 text-white font-bold text-xl rounded-2xl hover:bg-white/10 transition-all duration-300 backdrop-blur-sm">
            <Icon name="mdi:account-plus" class="inline w-6 h-6 mr-3 group-hover:rotate-12 transition-transform duration-300" />
            Join Talent Pool
          </button>
        </div>

        <!-- Inspirational Quote -->
        <div class="quote-section max-w-3xl mx-auto">
          <blockquote class="text-lg sm:text-xl text-gray-300 italic leading-relaxed mb-4">
            "Great things are done by a series of small things brought together. Your next small thing could change everything."
          </blockquote>
          <cite class="text-purple-300 font-medium">— Inspired by Vincent Van Gogh</cite>
        </div>

        <!-- Trust Indicators -->
        <div class="trust-indicators flex flex-wrap justify-center items-center gap-8 mt-16 opacity-70">
          <div class="flex items-center text-gray-300">
            <Icon name="mdi:shield-check" class="w-5 h-5 text-emerald-400 mr-2" />
            <span>Equal Opportunity</span>
          </div>
          <div class="flex items-center text-gray-300">
            <Icon name="mdi:heart-pulse" class="w-5 h-5 text-red-400 mr-2" />
            <span>Health & Wellness</span>
          </div>
          <div class="flex items-center text-gray-300">
            <Icon name="mdi:chart-line" class="w-5 h-5 text-blue-400 mr-2" />
            <span>Career Growth</span>
          </div>
          <div class="flex items-center text-gray-300">
            <Icon name="mdi:earth" class="w-5 h-5 text-green-400 mr-2" />
            <span>Global Impact</span>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRuntimeConfig, useAsyncData, useSanctum } from '#imports'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, EffectCoverflow, Pagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/effect-coverflow'
import 'swiper/css/pagination'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import NoJobsHero from '~/components/career/NoJobsHero.vue'

// Register GSAP plugins
if (process.client) {
  gsap.registerPlugin(ScrollTrigger)
}

const config = useRuntimeConfig()
const companyName = config.public.companyName || 'Our Company'
const { isLoggedIn } = useSanctum()

// Swiper modules
const SwiperAutoplay = Autoplay
const SwiperEffectCoverflow = EffectCoverflow
const SwiperPagination = Pagination

// Form state
const query = ref('')
const login = ref(true)
const error = ref('')
const application = ref<any>(null)

// Filter state
const filterBy = ref<'type' | 'role'>('type')
const filterTeam = ref('All')

// Enhanced data
const heroHighlights = ref([
  {
    icon: 'mdi:lightbulb-on-outline',
    title: 'Purposeful Work',
    description: 'Contribute to something bigger and drive meaningful change that impacts millions.',
    color: '#3b82f6' // blue-500
  },
  {
    icon: 'mdi:earth',
    title: 'Global Culture',
    description: 'Work with diverse, passionate people from around the world in an inclusive environment.',
    color: '#10b981' // emerald-500
  },
  {
    icon: 'mdi:school-outline',
    title: 'Learn & Grow',
    description: 'Continuous learning with mentorship, courses, and resources at your fingertips.',
    color: '#ec4899' // pink-500
  },
  {
    icon: 'mdi:party-popper',
    title: 'Culture That Cares',
    description: 'We celebrate wins, support each other, and grow as one unified team.',
    color: '#f59e0b' // amber-500
  }
])

const enhancedValues = ref([
  {
    icon: 'mdi:handshake-outline',
    title: 'Integrity',
    text: 'Honesty and transparency in all our actions and decisions.',
    color: '#e11d48' // rose-600
  },
  {
    icon: 'mdi:account-star-outline',
    title: 'Excellence',
    text: 'Always striving for the highest quality in everything we do.',
    color: '#0ea5e9' // sky-500
  },
  {
    icon: 'mdi:heart-outline',
    title: 'Compassion',
    text: 'Empathy, respect, and care for all team members and clients.',
    color: '#ec4899' // pink-500
  },
  {
    icon: 'mdi:rocket-launch-outline',
    title: 'Innovation',
    text: 'Bold ideas and creative solutions for better outcomes.',
    color: '#a855f7' // violet-500
  },
  {
    icon: 'mdi:chat-question-outline',
    title: 'Curiosity',
    text: 'We ask why, explore how, and never stop learning and growing.',
    color: '#22c55e' // green-500
  },
  {
    icon: 'mdi:scale-balance',
    title: 'Accountability',
    text: 'We own our outcomes and grow from both successes and failures.',
    color: '#f97316' // orange-500
  }
])

const enhancedLifeAtCompany = ref([
  {
    icon: 'mdi:calendar-star',
    title: 'Flexible Work',
    text: 'Balance your work and personal life with flexible schedules and remote options.'
  },
  {
    icon: 'mdi:brain',
    title: 'Learning Culture',
    text: 'Access to courses, mentorship, conferences, and dedicated learning time.'
  },
  {
    icon: 'mdi:emoticon-happy-outline',
    title: 'Team Bonding',
    text: 'Monthly events, virtual games, celebrations, and meaningful connections.'
  },
  {
    icon: 'mdi:office-building',
    title: 'Modern Workspace',
    text: 'Ergonomic, collaborative spaces designed for creativity and productivity.'
  },
  {
    icon: 'mdi:currency-usd',
    title: 'Competitive Pay',
    text: 'We reward talent generously with market-leading compensation packages.'
  },
  {
    icon: 'mdi:heart-pulse',
    title: 'Health Benefits',
    text: 'Comprehensive wellness coverage including mental health and fitness.'
  },
  {
    icon: 'mdi:map-marker-distance',
    title: 'Remote First',
    text: 'Freedom to work from anywhere with responsibility and clear communication.'
  },
  {
    icon: 'mdi:target',
    title: 'Goal Oriented',
    text: 'Everyone owns outcomes with OKRs, trust, support, and aligned vision.'
  }
])

// API data fetching
const { data: rawData, error: fetchError } = await useAsyncData('recruit', () =>
    $fetch(`${config.public.apiBase}/recruitment`)
)

const jobs = computed(() => {
  if (fetchError.value || !rawData.value?.data) return []
  return rawData.value.data.map((job: any) => ({
    title: job.name,
    url: job.url,
    role: job.role,
    type: job.type,
    location: job.location,
    thumbnail: job.thumbnail || '/api/placeholder/400/300',
    summary: job.role || job.summary || 'Join our team and make a difference'
  }))
})

const teams = computed(() => ['All', ...new Set(jobs.value.map(j => j[filterBy.value]))])
const filteredJobs = computed(() =>
    filterTeam.value === 'All' ? jobs.value : jobs.value.filter(j => j[filterBy.value] === filterTeam.value)
)

// Methods
function handleSearch() {
  if (!query.value.trim()) {
    error.value = 'Please enter your application ID, email, or mobile number.'
    application.value = null
    return
  }

  // Demo search functionality
  if (['demo-uuid-123', 'user@example.com', '+91-1234567890'].includes(query.value)) {
    application.value = {
      uuid: 'demo-uuid-123',
      role: 'Senior Frontend Developer',
      status: 'Under Review'
    }
    error.value = ''
  } else {
    application.value = null
    error.value = 'No application found with the provided information.'
  }
}

// Animations
onMounted(() => {
  if (process.client) {
    // Hero timeline animation
    const heroTl = gsap.timeline({ defaults: { ease: 'power3.out' } })

    heroTl.from('.hero-orb-1', { scale: 0, opacity: 0, duration: 1.5, ease: 'elastic.out(1, 0.5)' })
        .from('.hero-orb-2', { scale: 0, opacity: 0, duration: 1.5, ease: 'elastic.out(1, 0.5)' }, '-=1.2')
        .from('.hero-orb-3', { scale: 0, opacity: 0, duration: 1.5, ease: 'elastic.out(1, 0.5)' }, '-=1.2')
        .from('.hero-badge', { y: 30, opacity: 0, duration: 0.8, ease: 'back.out(1.7)' })
        .from('.hero-title', { y: 50, opacity: 0, duration: 1, ease: 'back.out(1.7)' }, '-=0.6')
        .from('.hero-subtitle', { y: 30, opacity: 0, duration: 0.8, ease: 'back.out(1.7)' }, '-=0.5')
        .from('.hero-cta', { y: 30, opacity: 0, duration: 0.8, ease: 'back.out(1.7)' }, '-=0.4')
        .from('.hero-highlights .highlight-card', {
          y: 50,
          opacity: 0,
          scale: 0.9,
          duration: 0.8,
          ease: 'back.out(1.7)',
          stagger: 0.15
        }, '-=0.3')

    // Floating animations
    gsap.to('.hero-orb-1', {
      y: -30,
      rotation: 10,
      duration: 5,
      ease: 'sine.inOut',
      yoyo: true,
      repeat: -1
    })

    gsap.to('.hero-orb-2', {
      y: 25,
      rotation: -8,
      duration: 7,
      ease: 'sine.inOut',
      yoyo: true,
      repeat: -1
    })

    gsap.to('.hero-orb-3', {
      y: -20,
      rotation: 15,
      duration: 4,
      ease: 'sine.inOut',
      yoyo: true,
      repeat: -1
    })

    // Scroll-triggered animations
    const scrollConfig = {
      start: 'top 80%',
      toggleActions: 'play none none reverse'
    }

    // Section badges
    gsap.utils.toArray('.section-badge').forEach((badge: any) => {
      gsap.fromTo(badge,
          { y: 20, opacity: 0, scale: 0.9 },
          {
            y: 0,
            opacity: 1,
            scale: 1,
            duration: 0.6,
            ease: 'back.out(1.7)',
            scrollTrigger: {
              trigger: badge,
              ...scrollConfig
            }
          }
      )
    })

    // Application card
    if (document.querySelector('.application-card')) {
      gsap.fromTo('.application-card',
          { y: 40, opacity: 0, scale: 0.95 },
          {
            y: 0,
            opacity: 1,
            scale: 1,
            duration: 0.8,
            ease: 'back.out(1.7)',
            scrollTrigger: {
              trigger: '.application-status',
              ...scrollConfig
            }
          }
      )
    }

    // Job cards
    if (document.querySelector('.jobs-swiper')) {
      gsap.fromTo('.jobs-swiper',
          { y: 60, opacity: 0 },
          {
            y: 0,
            opacity: 1,
            duration: 1,
            ease: 'power3.out',
            scrollTrigger: {
              trigger: '.jobs-section',
              ...scrollConfig
            }
          }
      )
    }

    // Values grid
    gsap.utils.toArray('.value-card').forEach((card: any, index: number) => {
      gsap.fromTo(card,
          { y: 40, opacity: 0, scale: 0.95 },
          {
            y: 0,
            opacity: 1,
            scale: 1,
            duration: 0.6,
            ease: 'back.out(1.7)',
            scrollTrigger: {
              trigger: card,
              ...scrollConfig
            }
          }
      )
    })

    // Life cards
    gsap.utils.toArray('.life-card').forEach((card: any) => {
      gsap.fromTo(card,
          { y: 30, opacity: 0, scale: 0.95 },
          {
            y: 0,
            opacity: 1,
            scale: 1,
            duration: 0.6,
            ease: 'back.out(1.7)',
            scrollTrigger: {
              trigger: card,
              ...scrollConfig
            }
          }
      )
    })

    // Final CTA
    gsap.fromTo(['.cta-title', '.cta-subtitle', '.cta-actions'],
        { y: 30, opacity: 0 },
        {
          y: 0,
          opacity: 1,
          duration: 0.8,
          ease: 'power3.out',
          stagger: 0.2,
          scrollTrigger: {
            trigger: '.final-cta',
            ...scrollConfig
          }
        }
    )

    // Particle animation
    gsap.to('.cta-particles .particle', {
      y: -150,
      opacity: 0,
      duration: 3,
      ease: 'power2.out',
      stagger: 0.6,
      repeat: -1,
      repeatDelay: 1.5
    })
  }
})
</script>

<style scoped>
/* Custom animations */
.highlight-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  transition: left 0.6s;
}

.highlight-card:hover::before {
  left: 100%;
}

.job-card::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #8b5cf6, #ec4899, #06b6d4);
  transform: scaleX(0);
  transition: transform 0.4s ease;
  border-radius: 1.5rem 1.5rem 0 0;
}

.job-card:hover::after {
  transform: scaleX(1);
}

.value-card::before {
  content: '';
  position: absolute;
  inset: 0;
  padding: 2px;
  background: linear-gradient(135deg, transparent, rgba(168, 85, 247, 0.3), transparent);
  border-radius: 1.5rem;
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  opacity: 0;
  transition: opacity 0.4s ease;
}

.value-card:hover::before {
  opacity: 1;
}

.cta-primary::before,
.cta-primary-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s;
}

.cta-primary:hover::before,
.cta-primary-btn:hover::before {
  left: 100%;
}

/* Mobile responsiveness */
@media (max-width: 640px) {
  .hero-title {
    font-size: 2.5rem;
    line-height: 1.2;
  }

  .job-card {
    height: auto;
    min-height: 380px;
  }

  .value-card, .life-card {
    padding: 1.5rem;
  }

  .hero-orb-2 {
    opacity: 0.1;
    width: 16rem;
    height: 16rem;
  }
}

@media (max-width: 480px) {
  .hero-orb-1, .hero-orb-3 {
    width: 8rem;
    height: 8rem;
  }

  .highlight-card {
    padding: 1.5rem;
  }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .job-card, .value-card, .life-card, .highlight-card {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }
}

/* Custom scroll indicator */
.scroll-indicator:hover {
  transform: translateX(-50%) scale(1.1);
}
</style>
