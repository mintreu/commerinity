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

    <!-- Enhanced Jobs Table Section with Pagination -->
    <section v-if="jobs.length" class="jobs-table-section py-20 px-6 bg-gradient-to-br from-gray-50 via-indigo-50 to-purple-50 dark:from-gray-950 dark:via-indigo-950/20 dark:to-purple-950/20">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <div class="section-badge inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-indigo-500/10 to-cyan-500/10 border border-indigo-200 dark:border-indigo-800 backdrop-blur-sm mb-6">
            <Icon name="mdi:table" class="w-4 h-4 mr-2 text-indigo-600 dark:text-indigo-400" />
            <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300">Detailed View</span>
          </div>
          <h2 class="text-3xl sm:text-5xl font-black mb-6">
            <span class="text-gray-900 dark:text-white">Browse All</span><br>
            <span class="bg-gradient-to-r from-indigo-600 to-cyan-600 bg-clip-text text-transparent">Opportunities</span>
          </h2>
          <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
            Explore our complete list of openings with advanced search and filtering options.
          </p>
        </div>

        <!-- Table Controls: Search + Type Filter -->
        <div class="table-controls mb-8">
          <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row gap-6 items-center">

              <!-- Search Bar -->
              <div class="relative flex-1 w-full">
                <input
                    v-model="tableSearch"
                    type="text"
                    placeholder="Search by job title, role, or location..."
                    class="w-full px-6 py-4 pl-14 text-base rounded-2xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/20 transition-all duration-300"
                />
                <Icon name="mdi:magnify" class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 text-xl pointer-events-none" />
                <button
                    v-if="tableSearch"
                    @click="tableSearch = ''"
                    class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200"
                >
                  <Icon name="mdi:close-circle" class="text-xl" />
                </button>
              </div>

              <!-- Type Filter -->
              <div class="flex items-center gap-4 w-full lg:w-auto">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">
                  <Icon name="mdi:filter-variant" class="inline w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" />
                  Filter by Type:
                </label>
                <select
                    v-model="tableTypeFilter"
                    class="flex-1 lg:w-64 px-6 py-4 rounded-2xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-medium focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/20 transition-all duration-300 cursor-pointer"
                >
                  <option value="All">All Types</option>
                  <option v-for="type in uniqueTypes" :key="type" :value="type">{{ type }}</option>
                </select>
              </div>

              <!-- Results Count -->
              <div class="text-sm font-bold text-gray-600 dark:text-gray-400 whitespace-nowrap bg-indigo-50 dark:bg-indigo-900/20 px-6 py-4 rounded-2xl">
                <span class="text-indigo-600 dark:text-indigo-400 text-lg">{{ searchedJobs.length }}</span>
                <span class="hidden sm:inline">{{ searchedJobs.length === 1 ? 'job' : 'jobs' }} found</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Desktop Table View -->
        <div class="table-wrapper hidden lg:block bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gradient-to-r from-indigo-600 via-indigo-500 to-cyan-500 text-white">
              <tr>
                <th @click="sortTable('title')" class="cursor-pointer px-6 py-5 text-left text-sm font-bold uppercase tracking-wider hover:bg-indigo-700/50 transition-colors duration-300 group">
                  <div class="flex items-center gap-3">
                    <Icon name="mdi:briefcase" class="w-5 h-5" />
                    Job Title
                    <Icon :name="getSortIcon('title')" class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" />
                  </div>
                </th>
                <th @click="sortTable('role')" class="cursor-pointer px-6 py-5 text-left text-sm font-bold uppercase tracking-wider hover:bg-indigo-700/50 transition-colors duration-300 group">
                  <div class="flex items-center gap-3">
                    <Icon name="mdi:account-tie" class="w-5 h-5" />
                    Role
                    <Icon :name="getSortIcon('role')" class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" />
                  </div>
                </th>
                <th @click="sortTable('type')" class="cursor-pointer px-6 py-5 text-left text-sm font-bold uppercase tracking-wider hover:bg-indigo-700/50 transition-colors duration-300 group">
                  <div class="flex items-center gap-3">
                    <Icon name="mdi:clock-outline" class="w-5 h-5" />
                    Type
                    <Icon :name="getSortIcon('type')" class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" />
                  </div>
                </th>
                <th @click="sortTable('location')" class="cursor-pointer px-6 py-5 text-left text-sm font-bold uppercase tracking-wider hover:bg-indigo-700/50 transition-colors duration-300 group">
                  <div class="flex items-center gap-3">
                    <Icon name="mdi:map-marker" class="w-5 h-5" />
                    Location
                    <Icon :name="getSortIcon('location')" class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" />
                  </div>
                </th>
                <th class="px-6 py-5 text-right text-sm font-bold uppercase tracking-wider">
                  <Icon name="mdi:cursor-pointer" class="inline w-5 h-5 mr-2" />
                  Action
                </th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="paginatedJobs.length === 0">
                <td colspan="5" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center space-y-4">
                    <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                      <Icon name="mdi:briefcase-search-outline" class="w-10 h-10 text-gray-400" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">No Jobs Found</h3>
                    <p class="text-gray-600 dark:text-gray-400 max-w-md">
                      We couldn't find any jobs matching your search criteria. Try adjusting your filters.
                    </p>
                    <button
                        @click="resetFilters"
                        class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-cyan-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 transform hover:scale-105"
                    >
                      <Icon name="mdi:refresh" class="inline w-5 h-5 mr-2" />
                      Clear All Filters
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-for="(job, index) in paginatedJobs" :key="`table-${job.url}`"
                  class="table-row hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all duration-300"
                  :style="{ animationDelay: `${index * 0.05}s` }">
                <td class="px-6 py-5">
                  <NuxtLink :to="`/career/${job.url}`" class="flex items-center gap-4 group">
                    <div class="relative">
                      <img
                          :src="job.thumbnail"
                          :alt="job.title"
                          class="w-14 h-14 rounded-xl object-cover shadow-md ring-2 ring-white dark:ring-gray-700 group-hover:ring-indigo-400 transition-all duration-300"
                          loading="lazy"
                      />
                      <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-indigo-600/0 to-cyan-600/0 group-hover:from-indigo-600/20 group-hover:to-cyan-600/20 transition-all duration-300"></div>
                    </div>
                    <div>
                      <div class="text-base font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-300 line-clamp-1">
                        {{ job.title }}
                      </div>
                      <div class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">
                        {{ job.summary }}
                      </div>
                    </div>
                  </NuxtLink>
                </td>
                <td class="px-6 py-5">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                      <Icon name="mdi:account-tie" class="w-4 h-4 mr-2" />
                      {{ job.role }}
                    </span>
                </td>
                <td class="px-6 py-5">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300">
                      <Icon name="mdi:clock-outline" class="w-4 h-4 mr-2" />
                      {{ job.type }}
                    </span>
                </td>
                <td class="px-6 py-5">
                  <div class="flex items-center text-gray-700 dark:text-gray-300 font-medium">
                    <Icon name="mdi:map-marker" class="w-5 h-5 mr-2 text-red-500" />
                    {{ job.location }}
                  </div>
                </td>
                <td class="px-6 py-5 text-right">
                  <NuxtLink
                      :to="`/career/${job.url}`"
                      class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-cyan-600 text-white font-bold text-sm rounded-xl hover:shadow-lg hover:shadow-indigo-500/25 transform hover:scale-105 transition-all duration-300"
                  >
                    View & Apply
                    <Icon name="mdi:arrow-right" class="w-5 h-5 ml-2" />
                  </NuxtLink>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Mobile Card View (Responsive for mobile/tablet) -->
        <div class="mobile-cards lg:hidden grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-if="paginatedJobs.length === 0" class="col-span-full">
            <div class="flex flex-col items-center justify-center py-16 px-6 bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700">
              <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
                <Icon name="mdi:briefcase-search-outline" class="w-10 h-10 text-gray-400" />
              </div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Jobs Found</h3>
              <p class="text-gray-600 dark:text-gray-400 text-center mb-6">
                We couldn't find any jobs matching your search criteria.
              </p>
              <button
                  @click="resetFilters"
                  class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-cyan-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300"
              >
                <Icon name="mdi:refresh" class="inline w-5 h-5 mr-2" />
                Clear Filters
              </button>
            </div>
          </div>

          <div v-for="(job, index) in paginatedJobs" :key="`mobile-${job.url}`"
               class="mobile-job-card group relative bg-white dark:bg-gray-800 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 overflow-hidden border border-gray-200 dark:border-gray-700 transform hover:-translate-y-2"
               :style="{ animationDelay: `${index * 0.1}s` }">

            <!-- Card Image -->
            <div class="relative h-48 overflow-hidden">
              <img
                  :src="job.thumbnail"
                  :alt="job.title"
                  class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                  loading="lazy"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

              <!-- Type Badge -->
              <div class="absolute top-4 right-4">
                <span class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-cyan-500 text-white text-xs font-bold rounded-full shadow-lg backdrop-blur-sm">
                  {{ job.type }}
                </span>
              </div>

              <!-- Location -->
              <div class="absolute bottom-4 left-4 flex items-center px-3 py-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-full shadow-lg">
                <Icon name="mdi:map-marker" class="w-4 h-4 mr-2 text-red-500" />
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ job.location }}</span>
              </div>
            </div>

            <!-- Card Content -->
            <div class="p-6">
              <div class="mb-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                  <Icon name="mdi:account-tie" class="w-3 h-3 mr-1" />
                  {{ job.role }}
                </span>
              </div>

              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-300 line-clamp-2">
                {{ job.title }}
              </h3>

              <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-6 line-clamp-2">
                {{ job.summary }}
              </p>

              <NuxtLink
                  :to="`/career/${job.url}`"
                  class="inline-flex items-center justify-center w-full px-6 py-3 bg-gradient-to-r from-indigo-600 to-cyan-600 text-white font-bold rounded-xl hover:shadow-lg hover:shadow-indigo-500/25 transition-all duration-300 transform hover:scale-105"
              >
                View & Apply
                <Icon name="mdi:arrow-right" class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform duration-300" />
              </NuxtLink>
            </div>

            <!-- Hover Effect -->
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-600/5 to-cyan-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none rounded-3xl"></div>
          </div>
        </div>

        <!-- Enhanced Pagination -->
        <div v-if="searchedJobs.length > 0" class="pagination-wrapper mt-12">
          <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-6">

              <!-- Items per page selector -->
              <div class="flex items-center gap-4">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">
                  <Icon name="mdi:format-list-numbered" class="inline w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" />
                  Rows per page:
                </span>
                <select
                    v-model="itemsPerPage"
                    @change="currentPage = 1"
                    class="px-5 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-bold focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/20 transition-all duration-300 cursor-pointer hover:scale-105"
                >
                  <option :value="6">6</option>
                  <option :value="12">12</option>
                  <option :value="24">24</option>
                  <option :value="50">50</option>
                </select>
              </div>

              <!-- Pagination Controls -->
              <nav class="flex items-center gap-2" aria-label="Pagination">
                <!-- Previous Button -->
                <button
                    @click="currentPage--"
                    :disabled="currentPage === 1"
                    :class="[
                      'group flex items-center gap-2 px-5 py-3 rounded-xl font-bold text-sm transition-all duration-300 border-2',
                      currentPage === 1
                        ? 'border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-600 cursor-not-allowed opacity-50'
                        : 'border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:scale-105'
                    ]"
                    title="Previous page"
                >
                  <Icon name="mdi:chevron-left" class="w-5 h-5" />
                  <span class="hidden sm:inline">Previous</span>
                </button>

                <!-- Page Numbers -->
                <div class="hidden md:flex items-center gap-2">
                  <button
                      v-for="page in visiblePages"
                      :key="page"
                      @click="currentPage = page"
                      :class="[
                        'min-w-[3rem] px-4 py-3 rounded-xl font-bold text-sm transition-all duration-300 transform',
                        currentPage === page
                          ? 'bg-gradient-to-r from-indigo-600 to-cyan-600 text-white shadow-lg shadow-indigo-500/25 scale-110'
                          : 'border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-indigo-300 dark:hover:border-indigo-600 hover:scale-105'
                      ]"
                  >
                    {{ page }}
                  </button>
                </div>

                <!-- Current page indicator for mobile -->
                <div class="md:hidden px-5 py-3 bg-gradient-to-r from-indigo-600 to-cyan-600 text-white font-bold text-sm rounded-xl shadow-lg">
                  {{ currentPage }} / {{ totalPages }}
                </div>

                <!-- Next Button -->
                <button
                    @click="currentPage++"
                    :disabled="currentPage === totalPages"
                    :class="[
                      'group flex items-center gap-2 px-5 py-3 rounded-xl font-bold text-sm transition-all duration-300 border-2',
                      currentPage === totalPages
                        ? 'border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-600 cursor-not-allowed opacity-50'
                        : 'border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:scale-105'
                    ]"
                    title="Next page"
                >
                  <span class="hidden sm:inline">Next</span>
                  <Icon name="mdi:chevron-right" class="w-5 h-5" />
                </button>
              </nav>

              <!-- Page Info -->
              <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 px-5 py-3 rounded-xl">
                Page <span class="text-indigo-600 dark:text-indigo-400 text-base">{{ currentPage }}</span> of
                <span class="text-indigo-600 dark:text-indigo-400 text-base">{{ totalPages }}</span>
              </div>
            </div>
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

// Filter state
const filterBy = ref<'type' | 'role'>('type')
const filterTeam = ref('All')

// Table state
const tableSearch = ref('')
const tableTypeFilter = ref('All')
const currentPage = ref(1)
const itemsPerPage = ref(12)
const sortBy = ref<'title' | 'role' | 'type' | 'location'>('title')
const sortOrder = ref<'asc' | 'desc'>('asc')

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

// Table computed properties
const uniqueTypes = computed(() => {
  const types = new Set(jobs.value.map(j => j.type))
  return Array.from(types).sort()
})

const searchedJobs = computed(() => {
  let filtered = filteredJobs.value

  // Apply type filter
  if (tableTypeFilter.value !== 'All') {
    filtered = filtered.filter(job => job.type === tableTypeFilter.value)
  }

  // Apply search
  if (!tableSearch.value) return filtered

  const search = tableSearch.value.toLowerCase()
  return filtered.filter(job =>
      job.title.toLowerCase().includes(search) ||
      job.role.toLowerCase().includes(search) ||
      job.location.toLowerCase().includes(search) ||
      job.type.toLowerCase().includes(search) ||
      job.summary.toLowerCase().includes(search)
  )
})

const sortedJobs = computed(() => {
  const jobsCopy = [...searchedJobs.value]

  jobsCopy.sort((a, b) => {
    const aVal = a[sortBy.value].toLowerCase()
    const bVal = b[sortBy.value].toLowerCase()

    if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1
    if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1
    return 0
  })

  return jobsCopy
})

const totalPages = computed(() => Math.ceil(searchedJobs.value.length / itemsPerPage.value))

const paginatedJobs = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return sortedJobs.value.slice(start, end)
})

const visiblePages = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value

  if (total <= 7) {
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) pages.push(i)
      pages.push('...')
      pages.push(total)
    } else if (current >= total - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = total - 4; i <= total; i++) pages.push(i)
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) pages.push(i)
      pages.push('...')
      pages.push(total)
    }
  }

  return pages.filter(p => p !== '...')
})

// Methods
function sortTable(column: 'title' | 'role' | 'type' | 'location') {
  if (sortBy.value === column) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = column
    sortOrder.value = 'asc'
  }
}

function getSortIcon(column: string) {
  if (sortBy.value !== column) return 'mdi:unfold-more-horizontal'
  return sortOrder.value === 'asc' ? 'mdi:arrow-up' : 'mdi:arrow-down'
}

function resetFilters() {
  tableSearch.value = ''
  tableTypeFilter.value = 'All'
  currentPage.value = 1
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

    // Table section animations
    if (document.querySelector('.jobs-table-section')) {
      gsap.fromTo('.table-controls',
          { y: 30, opacity: 0 },
          {
            y: 0,
            opacity: 1,
            duration: 0.8,
            ease: 'power3.out',
            scrollTrigger: {
              trigger: '.jobs-table-section',
              ...scrollConfig
            }
          }
      )

      gsap.fromTo('.table-wrapper',
          { y: 40, opacity: 0, scale: 0.98 },
          {
            y: 0,
            opacity: 1,
            scale: 1,
            duration: 1,
            ease: 'back.out(1.7)',
            scrollTrigger: {
              trigger: '.table-wrapper',
              ...scrollConfig
            }
          }
      )

      // Animate table rows with stagger
      gsap.fromTo('.table-row',
          { x: -20, opacity: 0 },
          {
            x: 0,
            opacity: 1,
            duration: 0.5,
            ease: 'power2.out',
            stagger: 0.05,
            scrollTrigger: {
              trigger: '.table-wrapper',
              start: 'top 70%',
              toggleActions: 'play none none reverse'
            }
          }
      )

      // Animate mobile cards
      gsap.utils.toArray('.mobile-job-card').forEach((card: any, index: number) => {
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

      // Pagination animation
      gsap.fromTo('.pagination-wrapper',
          { y: 20, opacity: 0 },
          {
            y: 0,
            opacity: 1,
            duration: 0.6,
            ease: 'power3.out',
            scrollTrigger: {
              trigger: '.pagination-wrapper',
              ...scrollConfig
            }
          }
      )
    }

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

/* Table animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.table-row {
  animation: fadeInUp 0.5s ease forwards;
  opacity: 0;
}

.mobile-job-card {
  animation: fadeInUp 0.6s ease forwards;
  opacity: 0;
}

/* Hover effects for table rows */
.table-row:hover {
  transform: scale(1.01);
  box-shadow: 0 4px 20px rgba(99, 102, 241, 0.15);
}

/* Mobile responsiveness */
@media (max-width: 1024px) {
  .table-wrapper {
    display: none !important;
  }
}

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

  .pagination-wrapper nav {
    flex-wrap: wrap;
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

  .mobile-job-card {
    padding: 1rem;
  }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .job-card, .value-card, .life-card, .highlight-card, .mobile-job-card {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }

  .table-wrapper {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
  }
}

/* Custom scroll indicator */
.scroll-indicator:hover {
  transform: translateX(-50%) scale(1.1);
}

/* Smooth transitions */
select, input[type="text"] {
  transition: all 0.3s ease;
}

/* Pagination hover effects */
.pagination-wrapper button:not(:disabled):hover {
  transform: scale(1.05);
}

.pagination-wrapper button:disabled {
  opacity: 0.5;
}

/* Smooth scroll */
* {
  scroll-behavior: smooth;
}
</style>
