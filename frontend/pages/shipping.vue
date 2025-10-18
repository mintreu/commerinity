<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-green-50 to-blue-50 dark:from-gray-950 dark:via-green-950 dark:to-blue-950 overflow-x-hidden">

    <!-- Floating Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden will-change-transform">
      <div ref="orb1" class="shipping-orb-1 absolute top-20 left-20 w-80 h-80 bg-gradient-to-r from-green-400 to-emerald-400 rounded-full opacity-10 blur-3xl will-change-transform"></div>
      <div ref="orb2" class="shipping-orb-2 absolute bottom-20 right-20 w-72 h-72 bg-gradient-to-r from-blue-400 to-cyan-400 rounded-full opacity-15 blur-3xl will-change-transform"></div>
      <div ref="orb3" class="shipping-orb-3 absolute top-1/2 left-1/3 w-64 h-64 bg-gradient-to-r from-orange-400 to-yellow-400 rounded-full opacity-10 blur-2xl will-change-transform"></div>

      <!-- Shipping Particles -->
      <div class="shipping-particles">
        <div v-for="(particle, index) in particles" :key="`particle-${index}`"
             class="particle absolute rounded-full opacity-60 animate-bounce will-change-transform"
             :class="particle.class"
             :style="particle.style">
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 px-4 lg:px-20 py-12">

      <!-- Shipping Header -->
      <div class="shipping-header mb-16 max-w-5xl mx-auto" ref="shippingHeader">
        <div class="text-center">
          <div class="header-badge inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-green-500/10 to-blue-500/10 border border-green-200 dark:border-green-800 backdrop-blur-sm mb-6">
            <Icon name="mdi:truck-fast" class="w-5 h-5 mr-3 text-green-600 dark:text-green-400" />
            <span class="font-semibold text-green-700 dark:text-green-300">Shipping & Delivery</span>
          </div>

          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gray-900 dark:text-white mb-6">
            Shipping <span class="bg-gradient-to-r from-green-600 via-blue-600 to-cyan-600 bg-clip-text text-transparent">Policy</span>
          </h1>

          <div class="shipping-meta bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6 mb-8">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-sm">
              <div class="flex items-center gap-2">
                <Icon name="mdi:calendar" class="w-4 h-4 text-green-600 dark:text-green-400" />
                <span class="text-gray-600 dark:text-gray-300">Effective Date:</span>
                <span class="font-bold text-gray-900 dark:text-white">{{ effectiveDate }}</span>
              </div>
              <div class="h-4 w-px bg-gray-300 dark:bg-gray-600 hidden sm:block"></div>
              <div class="flex items-center gap-2">
                <Icon name="mdi:update" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                <span class="text-gray-600 dark:text-gray-300">Last Updated:</span>
                <span class="font-bold text-gray-900 dark:text-white">{{ lastUpdated }}</span>
              </div>
            </div>
          </div>

          <div class="shipping-intro bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50 p-8">
            <p class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
              This Shipping Policy explains how orders are processed, shipped, and delivered for <strong class="text-gray-900 dark:text-white">{{ websiteName }}</strong>,
              operated by <strong class="text-gray-900 dark:text-white">{{ companyName }}</strong>. It applies to domestic and international deliveries where offered.
            </p>
          </div>
        </div>
      </div>

      <!-- Quick Info Cards -->
      <div class="quick-info mb-16 max-w-6xl mx-auto" ref="quickInfo">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="info-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6 text-center hover:scale-105 transition-all duration-300">
            <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <Icon name="mdi:clock-fast" class="w-8 h-8 text-white" />
            </div>
            <h3 class="font-bold text-gray-900 dark:text-white mb-2">Processing Time</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">1-2 business days</p>
          </div>

          <div class="info-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6 text-center hover:scale-105 transition-all duration-300">
            <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <Icon name="mdi:truck" class="w-8 h-8 text-white" />
            </div>
            <h3 class="font-bold text-gray-900 dark:text-white mb-2">Standard Delivery</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">3-7 business days</p>
          </div>

          <div class="info-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6 text-center hover:scale-105 transition-all duration-300">
            <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <Icon name="mdi:rocket" class="w-8 h-8 text-white" />
            </div>
            <h3 class="font-bold text-gray-900 dark:text-white mb-2">Express Delivery</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">1-3 business days</p>
          </div>

          <div class="info-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6 text-center hover:scale-105 transition-all duration-300">
            <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <Icon name="mdi:gift" class="w-8 h-8 text-white" />
            </div>
            <h3 class="font-bold text-gray-900 dark:text-white mb-2">Free Shipping</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">On qualifying orders</p>
          </div>
        </div>
      </div>

      <!-- Table of Contents -->
      <div class="toc-section mb-16 max-w-5xl mx-auto" ref="tocSection">
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
          <div class="toc-header bg-gradient-to-r from-green-600 to-blue-600 text-white p-6">
            <h2 class="text-2xl font-black flex items-center">
              <Icon name="mdi:format-list-numbered" class="w-6 h-6 mr-3" />
              Shipping Information
            </h2>
            <p class="text-green-100 mt-2">Everything you need to know about our shipping process</p>
          </div>
          <div class="toc-content p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <a
                  v-for="(section, index) in sections"
                  :key="section.id"
                  :href="`#${section.id}`"
                  class="toc-item flex items-center gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-700 hover:bg-green-50 dark:hover:bg-green-900/20 transition-all duration-300 hover:scale-105 group"
                  @click="scrollToSection(section.id, $event)"
              >
                <div class="toc-icon w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" :class="section.iconBg">
                  <Icon :name="section.icon" class="w-5 h-5 text-white" />
                </div>
                <div class="flex-1">
                  <span class="text-gray-800 dark:text-gray-200 group-hover:text-green-600 dark:group-hover:text-green-400 font-semibold block">
                    {{ section.title }}
                  </span>
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ section.subtitle }}
                  </span>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Shipping Content -->
      <div class="shipping-content max-w-5xl mx-auto space-y-12" ref="shippingContent">

        <!-- Processing Time -->
        <section :id="sections[0].id" class="shipping-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-green-600 to-emerald-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:clock-check" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-2xl font-black">{{ sections[0].title }}</h3>
                  <p class="text-green-100 mt-1">{{ sections[0].subtitle }}</p>
                </div>
              </div>
            </div>
            <div class="section-content p-8">
              <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <div class="lg:col-span-2 space-y-4">
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Orders are typically processed within <strong class="text-green-600 dark:text-green-400">1–2 business days</strong>, excluding weekends and public holidays. During peak periods,
                    processing may take an additional 1–3 business days. Orders placed after our daily cut-off may begin processing the
                    following business day.
                  </p>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    We'll send you a confirmation email once your order has been processed and is ready for shipment. You can track your order status
                    in real-time through your account dashboard or the tracking link provided.
                  </p>
                </div>
                <div class="lg:col-span-1">
                  <div class="processing-timeline bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 border border-green-200 dark:border-green-800">
                    <h4 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                      <Icon name="mdi:timeline" class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" />
                      Processing Timeline
                    </h4>
                    <ul class="space-y-3 text-sm">
                      <li class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-gray-700 dark:text-gray-300">Order confirmed</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <span class="text-gray-700 dark:text-gray-300">Payment verified</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                        <span class="text-gray-700 dark:text-gray-300">Items prepared</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                        <span class="text-gray-700 dark:text-gray-300">Ready to ship</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Delivery Methods -->
        <section :id="sections[1].id" class="shipping-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-blue-600 to-cyan-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:truck-delivery" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-2xl font-black">{{ sections[1].title }}</h3>
                  <p class="text-blue-100 mt-1">{{ sections[1].subtitle }}</p>
                </div>
              </div>
            </div>
            <div class="section-content p-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Estimated delivery time is calculated from dispatch, not from the time of order placement. Standard delivery generally
                    arrives within <strong class="text-blue-600 dark:text-blue-400">3–7 business days</strong> domestically; expedited options arrive within <strong class="text-purple-600 dark:text-purple-400">1–3 business days</strong>.
                  </p>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    International delivery timelines vary by destination, customs, and carrier routes. These estimates are not guarantees and may be affected by weather,
                    carrier delays, or other circumstances beyond our control.
                  </p>
                </div>
                <div class="delivery-methods space-y-4">
                  <div class="method-card bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-3 mb-2">
                      <Icon name="mdi:truck" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                      <h5 class="font-bold text-gray-900 dark:text-white">Standard Delivery</h5>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">3-7 business days • Most economical option</p>
                  </div>
                  <div class="method-card bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl p-4 border border-purple-200 dark:border-purple-800">
                    <div class="flex items-center gap-3 mb-2">
                      <Icon name="mdi:rocket" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                      <h5 class="font-bold text-gray-900 dark:text-white">Express Delivery</h5>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">1-3 business days • Priority handling</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Shipping Costs -->
        <section :id="sections[2].id" class="shipping-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-orange-600 to-red-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:currency-usd" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-2xl font-black">{{ sections[2].title }}</h3>
                  <p class="text-orange-100 mt-1">{{ sections[2].subtitle }}</p>
                </div>
              </div>
            </div>
            <div class="section-content p-8">
              <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <div class="lg:col-span-2 space-y-4">
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Shipping fees are calculated at checkout based on weight, dimensions, destination, and selected service level. We offer
                    <strong class="text-orange-600 dark:text-orange-400">free or discounted shipping</strong> subject to minimum cart value or promotional terms, displayed during checkout.
                  </p>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Take advantage of our free shipping offers by meeting the minimum order requirements. We frequently run promotions
                    that make shipping even more affordable for our valued customers.
                  </p>
                </div>
                <div class="lg:col-span-1">
                  <div class="cost-breakdown bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-2xl p-6 border border-orange-200 dark:border-orange-800">
                    <h4 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                      <Icon name="mdi:calculator" class="w-5 h-5 mr-2 text-orange-600 dark:text-orange-400" />
                      Cost Factors
                    </h4>
                    <ul class="space-y-2 text-sm">
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:weight" class="w-4 h-4 text-orange-600 dark:text-orange-400" />
                        <span class="text-gray-700 dark:text-gray-300">Package weight</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:cube-outline" class="w-4 h-4 text-red-600 dark:text-red-400" />
                        <span class="text-gray-700 dark:text-gray-300">Dimensions</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:map-marker" class="w-4 h-4 text-pink-600 dark:text-pink-400" />
                        <span class="text-gray-700 dark:text-gray-300">Destination</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:speedometer" class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                        <span class="text-gray-700 dark:text-gray-300">Service level</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Regions & Restrictions -->
        <section :id="sections[3].id" class="shipping-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:earth" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-2xl font-black">{{ sections[3].title }}</h3>
                  <p class="text-purple-100 mt-1">{{ sections[3].subtitle }}</p>
                </div>
              </div>
            </div>
            <div class="section-content p-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    We currently ship to serviceable domestic locations and selected international destinations. Certain products may be
                    restricted by law, carrier rules, or safety guidelines, limiting available methods or regions.
                  </p>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    We do not ship to unsupported P.O. boxes or military addresses unless explicitly stated at checkout. Check our shipping
                    calculator during checkout to confirm availability to your location.
                  </p>
                </div>
                <div class="shipping-zones space-y-4">
                  <div class="zone-card bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border border-green-200 dark:border-green-800">
                    <div class="flex items-center gap-3 mb-2">
                      <Icon name="mdi:home" class="w-6 h-6 text-green-600 dark:text-green-400" />
                      <h5 class="font-bold text-gray-900 dark:text-white">Domestic Shipping</h5>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Available nationwide with standard and express options</p>
                  </div>
                  <div class="zone-card bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-3 mb-2">
                      <Icon name="mdi:airplane" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                      <h5 class="font-bold text-gray-900 dark:text-white">International Shipping</h5>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Selected destinations with customs clearance support</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Tracking & Support -->
        <section :id="sections[4].id" class="shipping-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-teal-600 to-cyan-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:package-variant" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-2xl font-black">{{ sections[4].title }}</h3>
                  <p class="text-teal-100 mt-1">{{ sections[4].subtitle }}</p>
                </div>
              </div>
            </div>
            <div class="section-content p-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Once an order ships, a confirmation email includes carrier information and tracking number where available.
                    Tracking visibility begins after the carrier scans the parcel at their first processing facility.
                  </p>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    If delivery fails due to recipient unavailability or address issues, carriers may attempt redelivery or route
                    the package to a local pickup point. Additional shipping fees may apply for reshipment.
                  </p>
                </div>
                <div class="tracking-features space-y-4">
                  <div class="feature-card bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 rounded-xl p-4 border border-teal-200 dark:border-teal-800">
                    <div class="flex items-center gap-3 mb-2">
                      <Icon name="mdi:map-marker-path" class="w-6 h-6 text-teal-600 dark:text-teal-400" />
                      <h5 class="font-bold text-gray-900 dark:text-white">Real-time Tracking</h5>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Monitor your package every step of the way</p>
                  </div>
                  <div class="feature-card bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-3 mb-2">
                      <Icon name="mdi:bell-ring" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                      <h5 class="font-bold text-gray-900 dark:text-white">Delivery Notifications</h5>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Get updates via email and SMS</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Contact Support -->
        <section :id="sections[5].id" class="shipping-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:headset" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-2xl font-black">{{ sections[5].title }}</h3>
                  <p class="text-indigo-100 mt-1">{{ sections[5].subtitle }}</p>
                </div>
              </div>
            </div>
            <div class="section-content p-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                    For shipping-related questions, tracking issues, or delivery concerns, our support team is here to help.
                    Contact us through any of the methods below for prompt assistance.
                  </p>
                  <div class="support-stats grid grid-cols-2 gap-4">
                    <div class="stat-card bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 text-center border border-green-200 dark:border-green-800">
                      <div class="text-2xl font-black text-green-600 dark:text-green-400">24/7</div>
                      <div class="text-sm text-gray-600 dark:text-gray-400">Support Available</div>
                    </div>
                    <div class="stat-card bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-xl p-4 text-center border border-blue-200 dark:border-blue-800">
                      <div class="text-2xl font-black text-blue-600 dark:text-blue-400">&lt;2h</div>
                      <div class="text-sm text-gray-600 dark:text-gray-400">Response Time</div>
                    </div>
                  </div>
                </div>
                <div class="contact-methods space-y-4">
                  <div class="contact-method flex items-center gap-4 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl border border-indigo-200 dark:border-indigo-800">
                    <Icon name="mdi:email" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    <div>
                      <h5 class="font-bold text-gray-900 dark:text-white">Email Support</h5>
                      <a :href="`mailto:${supportEmail}`" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-200 font-semibold">
                        {{ supportEmail }}
                      </a>
                    </div>
                  </div>
                  <div class="contact-method flex items-center gap-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-200 dark:border-green-800">
                    <Icon name="mdi:phone" class="w-6 h-6 text-green-600 dark:text-green-400" />
                    <div>
                      <h5 class="font-bold text-gray-900 dark:text-white">Phone Support</h5>
                      <a :href="`tel:${phoneNumber}`" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 font-semibold">
                        {{ phoneNumber }}
                      </a>
                    </div>
                  </div>
                  <div class="company-info bg-gradient-to-r from-gray-50 to-slate-50 dark:from-gray-800 dark:to-slate-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <h5 class="font-bold text-gray-900 dark:text-white mb-2 flex items-center">
                      <Icon name="mdi:office-building" class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" />
                      {{ companyName }}
                    </h5>
                    <p class="text-sm text-gray-600 dark:text-gray-400 flex items-start gap-2">
                      <Icon name="mdi:map-marker" class="w-4 h-4 mt-0.5" />
                      {{ address }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>


    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

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
const companyName = config.public.companyName
const supportEmail = config.public.supportEmail
const phoneNumber = config.public.phoneNumber
const address = config.public.address

// Dates
const effectiveDate = 'July 20, 2025'
const lastUpdated = 'September 25, 2025'

// Refs for animations
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()
const orb3 = ref<HTMLElement>()
const shippingHeader = ref<HTMLElement>()
const quickInfo = ref<HTMLElement>()
const tocSection = ref<HTMLElement>()
const shippingContent = ref<HTMLElement>()

let gsapContext: any = null

// Animated particles
const particles = [
  { class: 'w-4 h-4 bg-green-400', style: 'top: 15%; left: 10%; animation-delay: 0s;' },
  { class: 'w-3 h-3 bg-blue-400', style: 'top: 25%; right: 15%; animation-delay: 1.5s;' },
  { class: 'w-5 h-5 bg-orange-400', style: 'bottom: 20%; left: 20%; animation-delay: 3s;' },
  { class: 'w-3 h-3 bg-cyan-400', style: 'bottom: 30%; right: 25%; animation-delay: 4.5s;' }
]

// Sections data
const sections = [
  {
    id: 'processing-time',
    title: 'Processing Time',
    subtitle: 'Order preparation',
    icon: 'mdi:clock-check',
    iconBg: 'bg-gradient-to-r from-green-500 to-emerald-500'
  },
  {
    id: 'delivery-methods',
    title: 'Delivery Methods',
    subtitle: 'Shipping options',
    icon: 'mdi:truck-delivery',
    iconBg: 'bg-gradient-to-r from-blue-500 to-cyan-500'
  },
  {
    id: 'shipping-costs',
    title: 'Shipping Costs',
    subtitle: 'Pricing & free shipping',
    icon: 'mdi:currency-usd',
    iconBg: 'bg-gradient-to-r from-orange-500 to-red-500'
  },
  {
    id: 'regions-restrictions',
    title: 'Regions & Restrictions',
    subtitle: 'Where we ship',
    icon: 'mdi:earth',
    iconBg: 'bg-gradient-to-r from-purple-500 to-indigo-500'
  },
  {
    id: 'tracking-support',
    title: 'Tracking & Support',
    subtitle: 'Monitor your order',
    icon: 'mdi:package-variant',
    iconBg: 'bg-gradient-to-r from-teal-500 to-cyan-500'
  },
  {
    id: 'contact-support',
    title: 'Contact Support',
    subtitle: 'Get shipping help',
    icon: 'mdi:headset',
    iconBg: 'bg-gradient-to-r from-indigo-500 to-purple-500'
  }
]

// Methods
const scrollToSection = (sectionId: string, event: Event) => {
  event.preventDefault()
  const element = document.getElementById(sectionId)
  if (element) {
    element.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// GSAP animations
const initializeAnimations = () => {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
    // Floating orbs animation
    if (orb1.value) {
      gsap.to(orb1.value, {
        y: -40,
        rotation: 15,
        duration: 8,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: -1
      })
    }

    if (orb2.value) {
      gsap.to(orb2.value, {
        y: 30,
        rotation: -12,
        duration: 10,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: -1
      })
    }

    if (orb3.value) {
      gsap.to(orb3.value, {
        y: -25,
        rotation: 20,
        duration: 6,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: -1
      })
    }

    // Entrance animations
    if (ScrollTrigger) {
      // Header animation
      if (shippingHeader.value) {
        gsap.fromTo(shippingHeader.value,
            { y: -50, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 1,
              ease: 'back.out(1.7)'
            }
        )
      }

      // Quick info animation
      if (quickInfo.value) {
        gsap.fromTo(quickInfo.value,
            { y: 50, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 1,
              ease: 'back.out(1.7)',
              delay: 0.2
            }
        )
      }

      // TOC animation
      if (tocSection.value) {
        gsap.fromTo(tocSection.value,
            { y: 40, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 1,
              ease: 'back.out(1.7)',
              delay: 0.4
            }
        )
      }

      // Content animation
      if (shippingContent.value) {
        gsap.fromTo(shippingContent.value,
            { y: 30, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 1,
              ease: 'back.out(1.7)',
              delay: 0.6
            }
        )
      }

      // Section animations
      gsap.utils.toArray('.shipping-section').forEach((section: any, index: number) => {
        gsap.fromTo(section,
            { y: 30, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 0.6,
              ease: 'back.out(1.7)',
              scrollTrigger: {
                trigger: section,
                start: 'top 80%',
                toggleActions: 'play none none reverse'
              }
            }
        )
      })
    }
  })
}

// Lifecycle
onMounted(() => {
  setTimeout(() => {
    initializeAnimations()
  }, 100)
})

onUnmounted(() => {
  if (gsapContext) {
    gsapContext.kill()
  }

  if (process.client && ScrollTrigger) {
    ScrollTrigger.getAll().forEach((trigger: any) => trigger.kill())
  }
})

// SEO
useSeoMeta({
  title: 'Shipping Policy - Delivery Information',
  description: 'Learn about our shipping methods, delivery times, costs, and tracking options. Fast and reliable delivery worldwide.',
})
</script>

<style scoped>
/* Performance optimizations */
.will-change-transform {
  will-change: transform;
}

/* Info card hover effects */
.info-card {
  position: relative;
  transition: all 0.3s ease;
}

.info-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, transparent, rgba(34, 197, 94, 0.1));
  opacity: 0;
  transition: opacity 0.3s ease;
  border-radius: 1rem;
}

.info-card:hover::before {
  opacity: 1;
}

/* TOC item hover effects */
.toc-item {
  position: relative;
  transition: all 0.3s ease;
}

.toc-item::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, transparent, rgba(34, 197, 94, 0.1));
  opacity: 0;
  transition: opacity 0.3s ease;
  border-radius: 0.75rem;
}

.toc-item:hover::before {
  opacity: 1;
}

/* Section animations */
.shipping-section {
  position: relative;
}

.section-icon {
  position: relative;
  transition: all 0.3s ease;
}

.section-icon:hover {
  transform: scale(1.1);
}

/* Card hover effects */
.method-card,
.zone-card,
.feature-card,
.contact-method,
.stat-card {
  position: relative;
  transition: all 0.3s ease;
}

.method-card:hover,
.zone-card:hover,
.feature-card:hover,
.contact-method:hover,
.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

/* Back to top button */
.back-to-top button {
  position: relative;
  overflow: hidden;
}

.back-to-top button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.6s ease;
}

.back-to-top button:hover::before {
  left: 100%;
}

/* Particle animations */
.particle {
  animation: float 6s ease-in-out infinite;
  will-change: transform;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
  }
}

/* Responsive design */
@media (max-width: 1024px) {
  .shipping-orb-2 {
    display: none;
  }
}

@media (max-width: 640px) {
  .quick-info .grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .toc-content .grid {
    grid-template-columns: 1fr;
  }

  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .section-content .grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .shipping-orb-1, .shipping-orb-3 {
    width: 4rem;
    height: 4rem;
  }

  .particles {
    display: none;
  }

  .quick-info .grid {
    grid-template-columns: 1fr;
  }
}

/* Focus states for accessibility */
.toc-item:focus-visible,
.back-to-top button:focus-visible {
  outline: 2px solid #22c55e;
  outline-offset: 2px;
}

/* Smooth scroll behavior */
html {
  scroll-behavior: smooth;
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .bg-white\/80 {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }
}
</style>
