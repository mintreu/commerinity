<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-red-50 to-orange-50 dark:from-gray-950 dark:via-red-950 dark:to-orange-950 overflow-x-hidden">

    <!-- Floating Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden will-change-transform">
      <div ref="orb1" class="return-orb-1 absolute top-20 left-20 w-80 h-80 bg-gradient-to-r from-red-400 to-orange-400 rounded-full opacity-10 blur-3xl will-change-transform"></div>
      <div ref="orb2" class="return-orb-2 absolute bottom-20 right-20 w-72 h-72 bg-gradient-to-r from-orange-400 to-yellow-400 rounded-full opacity-15 blur-3xl will-change-transform"></div>
      <div ref="orb3" class="return-orb-3 absolute top-1/2 left-1/3 w-64 h-64 bg-gradient-to-r from-pink-400 to-rose-400 rounded-full opacity-10 blur-2xl will-change-transform"></div>

      <!-- Return Particles -->
      <div class="return-particles">
        <div v-for="(particle, index) in particles" :key="`particle-${index}`"
             class="particle absolute rounded-full opacity-60 animate-bounce will-change-transform"
             :class="particle.class"
             :style="particle.style">
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 px-4 lg:px-20 py-12">

      <!-- Return Header -->
      <div class="return-header mb-16 max-w-5xl mx-auto" ref="returnHeader">
        <div class="text-center">
          <div class="header-badge inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-red-500/10 to-orange-500/10 border border-red-200 dark:border-red-800 backdrop-blur-sm mb-6">
            <Icon name="mdi:package-variant-closed" class="w-5 h-5 mr-3 text-red-600 dark:text-red-400" />
            <span class="font-semibold text-red-700 dark:text-red-300">Returns & Refunds</span>
          </div>

          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gray-900 dark:text-white mb-6">
            Return & <span class="bg-gradient-to-r from-red-600 via-orange-600 to-yellow-600 bg-clip-text text-transparent">Refund</span> Policy
          </h1>

          <div class="return-meta bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6 mb-8">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-sm">
              <div class="flex items-center gap-2">
                <Icon name="mdi:calendar" class="w-4 h-4 text-red-600 dark:text-red-400" />
                <span class="text-gray-600 dark:text-gray-300">Effective Date:</span>
                <span class="font-bold text-gray-900 dark:text-white">{{ effectiveDate }}</span>
              </div>
              <div class="h-4 w-px bg-gray-300 dark:bg-gray-600 hidden sm:block"></div>
              <div class="flex items-center gap-2">
                <Icon name="mdi:update" class="w-4 h-4 text-orange-600 dark:text-orange-400" />
                <span class="text-gray-600 dark:text-gray-300">Last Updated:</span>
                <span class="font-bold text-gray-900 dark:text-white">{{ lastUpdated }}</span>
              </div>
            </div>
          </div>

          <div class="return-intro bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50 p-8">
            <p class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
              This Return & Refund Policy governs return eligibility and refund processing for purchases made on
              <strong class="text-gray-900 dark:text-white">{{ websiteName }}</strong>, operated by <strong class="text-gray-900 dark:text-white">{{ companyName }}</strong>.
              Product-specific eligibility applies as stated on each product page.
            </p>
          </div>
        </div>
      </div>

      <!-- Quick Policy Overview -->
      <div class="policy-overview mb-16 max-w-6xl mx-auto" ref="policyOverview">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="overview-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6 text-center hover:scale-105 transition-all duration-300">
            <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <Icon name="mdi:check-circle" class="w-8 h-8 text-white" />
            </div>
            <h3 class="font-bold text-gray-900 dark:text-white mb-2">Product-Based</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Eligibility varies by product</p>
          </div>

          <div class="overview-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6 text-center hover:scale-105 transition-all duration-300">
            <div class="w-16 h-16 bg-gradient-to-r from-red-500 to-rose-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <Icon name="mdi:close-circle" class="w-8 h-8 text-white" />
            </div>
            <h3 class="font-bold text-gray-900 dark:text-white mb-2">No Change of Mind</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Preference changes not accepted</p>
          </div>

          <div class="overview-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6 text-center hover:scale-105 transition-all duration-300">
            <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <Icon name="mdi:wallet" class="w-8 h-8 text-white" />
            </div>
            <h3 class="font-bold text-gray-900 dark:text-white mb-2">Wallet First</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Refunds credited to wallet</p>
          </div>

          <div class="overview-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6 text-center hover:scale-105 transition-all duration-300">
            <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <Icon name="mdi:truck" class="w-8 h-8 text-white" />
            </div>
            <h3 class="font-bold text-gray-900 dark:text-white mb-2">Free Pickup</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Courier collection service</p>
          </div>
        </div>
      </div>

      <!-- Table of Contents -->
      <div class="toc-section mb-16 max-w-5xl mx-auto" ref="tocSection">
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
          <div class="toc-header bg-gradient-to-r from-red-600 to-orange-600 text-white p-6">
            <h2 class="text-2xl font-black flex items-center">
              <Icon name="mdi:format-list-numbered" class="w-6 h-6 mr-3" />
              Policy Overview
            </h2>
            <p class="text-red-100 mt-2">Understanding our return and refund process</p>
          </div>
          <div class="toc-content p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <a
                  v-for="(section, index) in sections"
                  :key="section.id"
                  :href="`#${section.id}`"
                  class="toc-item flex items-center gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-300 hover:scale-105 group"
                  @click="scrollToSection(section.id, $event)"
              >
                <div class="toc-icon w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" :class="section.iconBg">
                  <Icon :name="section.icon" class="w-5 h-5 text-white" />
                </div>
                <div class="flex-1">
                  <span class="text-gray-800 dark:text-gray-200 group-hover:text-red-600 dark:group-hover:text-red-400 font-semibold block">
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

      <!-- Returns & Refunds Content -->
      <div class="return-content max-w-5xl mx-auto space-y-12" ref="returnContent">

        <!-- Return Eligibility -->
        <section :id="sections[0].id" class="return-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-green-600 to-emerald-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:clipboard-check" class="w-6 h-6 text-white" />
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
                    Return and refund eligibility <strong class="text-green-600 dark:text-green-400">depends entirely on the product and its type</strong>. Each product page clearly states if
                    the item is returnable. Items marked as <strong class="text-red-600 dark:text-red-400">"Non-returnable"</strong> are not eligible for return or refund under any
                    circumstance, except where a mandatory legal warranty applies.
                  </p>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Customers must first receive and accept delivery of the order. Returns are considered only when a logical,
                    verifiable fault attributable to {{ companyName }} or the authorized seller is identified (e.g., wrong item
                    shipped, manufacturing defect, or missing components as per listing).
                  </p>
                </div>
                <div class="lg:col-span-1">
                  <div class="eligibility-checklist bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 border border-green-200 dark:border-green-800">
                    <h4 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                      <Icon name="mdi:checklist" class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" />
                      Valid Reasons
                    </h4>
                    <ul class="space-y-2 text-sm">
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:check" class="w-4 h-4 text-green-600 dark:text-green-400" />
                        <span class="text-gray-700 dark:text-gray-300">Wrong item shipped</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:check" class="w-4 h-4 text-green-600 dark:text-green-400" />
                        <span class="text-gray-700 dark:text-gray-300">Manufacturing defect</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:check" class="w-4 h-4 text-green-600 dark:text-green-400" />
                        <span class="text-gray-700 dark:text-gray-300">Missing components</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:check" class="w-4 h-4 text-green-600 dark:text-green-400" />
                        <span class="text-gray-700 dark:text-gray-300">Damage during shipping</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- No Change of Mind -->
        <section :id="sections[1].id" class="return-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-red-600 to-rose-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:close-circle-outline" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-2xl font-black">{{ sections[1].title }}</h3>
                  <p class="text-red-100 mt-1">{{ sections[1].subtitle }}</p>
                </div>
              </div>
            </div>
            <div class="section-content p-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Orders must be placed with a <strong class="text-red-600 dark:text-red-400">clear and specific choice</strong> (e.g., size, color, variant). After receiving "Choice A,"
                    a desire to replace it with "Choice B" will <strong class="text-red-600 dark:text-red-400">not be treated as a valid reason</strong> for return or refund.
                  </p>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Preference or change-of-mind requests are not accepted. We encourage customers to carefully review product
                    specifications, images, and details before placing their order to ensure satisfaction.
                  </p>
                </div>
                <div class="not-accepted bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-2xl p-6 border border-red-200 dark:border-red-800">
                  <h4 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                    <Icon name="mdi:cancel" class="w-5 h-5 mr-2 text-red-600 dark:text-red-400" />
                    Not Accepted
                  </h4>
                  <ul class="space-y-2 text-sm">
                    <li class="flex items-center gap-2">
                      <Icon name="mdi:close" class="w-4 h-4 text-red-600 dark:text-red-400" />
                      <span class="text-gray-700 dark:text-gray-300">Size/color preference change</span>
                    </li>
                    <li class="flex items-center gap-2">
                      <Icon name="mdi:close" class="w-4 h-4 text-red-600 dark:text-red-400" />
                      <span class="text-gray-700 dark:text-gray-300">Changed mind after delivery</span>
                    </li>
                    <li class="flex items-center gap-2">
                      <Icon name="mdi:close" class="w-4 h-4 text-red-600 dark:text-red-400" />
                      <span class="text-gray-700 dark:text-gray-300">Didn't like the product</span>
                    </li>
                    <li class="flex items-center gap-2">
                      <Icon name="mdi:close" class="w-4 h-4 text-red-600 dark:text-red-400" />
                      <span class="text-gray-700 dark:text-gray-300">Found better price elsewhere</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Return Process -->
        <section :id="sections[2].id" class="return-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-blue-600 to-cyan-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:package-variant" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-2xl font-black">{{ sections[2].title }}</h3>
                  <p class="text-blue-100 mt-1">{{ sections[2].subtitle }}</p>
                </div>
              </div>
            </div>
            <div class="section-content p-8">
              <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <div class="lg:col-span-2 space-y-4">
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Return logistics are handled by <strong class="text-blue-600 dark:text-blue-400">third-party shipping courier services</strong> appointed or partnered with
                    {{ companyName }}. The pickup schedule and method depend on serviceability at the pickup address.
                  </p>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    If pickup is unavailable, {{ companyName }} may direct a drop-off or an alternate method at its discretion.
                    All returned items are inspected for condition and claimed fault before approval or rejection.
                  </p>
                </div>
                <div class="lg:col-span-1">
                  <div class="process-steps bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-2xl p-6 border border-blue-200 dark:border-blue-800">
                    <h4 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                      <Icon name="mdi:format-list-numbered" class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" />
                      Return Steps
                    </h4>
                    <div class="space-y-3 text-sm">
                      <div class="flex items-start gap-2">
                        <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">1</div>
                        <span class="text-gray-700 dark:text-gray-300">Submit return request</span>
                      </div>
                      <div class="flex items-start gap-2">
                        <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">2</div>
                        <span class="text-gray-700 dark:text-gray-300">Schedule pickup</span>
                      </div>
                      <div class="flex items-start gap-2">
                        <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">3</div>
                        <span class="text-gray-700 dark:text-gray-300">Quality inspection</span>
                      </div>
                      <div class="flex items-start gap-2">
                        <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">4</div>
                        <span class="text-gray-700 dark:text-gray-300">Refund processing</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Refund Process -->
        <section :id="sections[3].id" class="return-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:cash-refund" class="w-6 h-6 text-white" />
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
                    Upon approval of a return or claim, refunds are <strong class="text-purple-600 dark:text-purple-400">first credited to the customer's {{ websiteName }} wallet</strong>
                    (store credit). From the wallet, customers may initiate a withdrawal to a verified bank account.
                  </p>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Bank settlement can take up to <strong class="text-purple-600 dark:text-purple-400">15 days</strong> from the date of refund approval/processing,
                    subject to bank and payment processor timelines.
                  </p>
                </div>
                <div class="refund-timeline space-y-4">
                  <div class="timeline-card bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-purple-200 dark:border-purple-800">
                    <div class="flex items-center gap-3 mb-2">
                      <Icon name="mdi:wallet" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                      <h5 class="font-bold text-gray-900 dark:text-white">Wallet Credit</h5>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Immediate • Use for future purchases</p>
                  </div>
                  <div class="timeline-card bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-3 mb-2">
                      <Icon name="mdi:bank" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                      <h5 class="font-bold text-gray-900 dark:text-white">Bank Transfer</h5>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">3-15 business days • To verified account</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Non-Refundable Items -->
        <section :id="sections[4].id" class="return-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-orange-600 to-red-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:cancel" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-2xl font-black">{{ sections[4].title }}</h3>
                  <p class="text-orange-100 mt-1">{{ sections[4].subtitle }}</p>
                </div>
              </div>
            </div>
            <div class="section-content p-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Certain items and fees are <strong class="text-orange-600 dark:text-orange-400">non-refundable</strong> as per our policy. These include items marked as
                    "Non-returnable" on product pages, career application fees, and subscription charges.
                  </p>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Orders for which the shipping process has started cannot be cancelled, and changes are not permitted after
                    dispatch initiation. Please review your order carefully before confirming.
                  </p>
                </div>
                <div class="non-refundable-list space-y-4">
                  <div class="list-card bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-xl p-4 border border-orange-200 dark:border-orange-800">
                    <h5 class="font-bold text-gray-900 dark:text-white mb-3 flex items-center">
                      <Icon name="mdi:alert-circle" class="w-5 h-5 mr-2 text-orange-600 dark:text-orange-400" />
                      Non-Refundable Items
                    </h5>
                    <ul class="space-y-2 text-sm">
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:circle" class="w-2 h-2 text-orange-600 dark:text-orange-400" />
                        <span class="text-gray-700 dark:text-gray-300">Products marked "Non-returnable"</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:circle" class="w-2 h-2 text-red-600 dark:text-red-400" />
                        <span class="text-gray-700 dark:text-gray-300">Career job application fees</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:circle" class="w-2 h-2 text-pink-600 dark:text-pink-400" />
                        <span class="text-gray-700 dark:text-gray-300">Membership subscription charges</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:circle" class="w-2 h-2 text-purple-600 dark:text-purple-400" />
                        <span class="text-gray-700 dark:text-gray-300">Orders already shipped</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Contact Support -->
        <section :id="sections[5].id" class="return-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-teal-600 to-cyan-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:headset" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-2xl font-black">{{ sections[5].title }}</h3>
                  <p class="text-teal-100 mt-1">{{ sections[5].subtitle }}</p>
                </div>
              </div>
            </div>
            <div class="section-content p-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                    If eligible, submit a return request through your order details page or contact our support team with order ID,
                    issue description, and required evidence. We'll guide you through the pickup process.
                  </p>
                  <div class="support-features grid grid-cols-2 gap-4">
                    <div class="feature-card bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 text-center border border-green-200 dark:border-green-800">
                      <div class="text-2xl font-black text-green-600 dark:text-green-400">Free</div>
                      <div class="text-sm text-gray-600 dark:text-gray-400">Return Pickup</div>
                    </div>
                    <div class="feature-card bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-xl p-4 text-center border border-blue-200 dark:border-blue-800">
                      <div class="text-2xl font-black text-blue-600 dark:text-blue-400">24/7</div>
                      <div class="text-sm text-gray-600 dark:text-gray-400">Support Available</div>
                    </div>
                  </div>
                </div>
                <div class="contact-methods space-y-4">
                  <div class="contact-method flex items-center gap-4 p-4 bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 rounded-xl border border-teal-200 dark:border-teal-800">
                    <Icon name="mdi:email" class="w-6 h-6 text-teal-600 dark:text-teal-400" />
                    <div>
                      <h5 class="font-bold text-gray-900 dark:text-white">Email Support</h5>
                      <a :href="`mailto:${supportEmail}`" class="text-teal-600 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-200 font-semibold">
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
const returnHeader = ref<HTMLElement>()
const policyOverview = ref<HTMLElement>()
const tocSection = ref<HTMLElement>()
const returnContent = ref<HTMLElement>()

let gsapContext: any = null

// Animated particles
const particles = [
  { class: 'w-4 h-4 bg-red-400', style: 'top: 15%; left: 10%; animation-delay: 0s;' },
  { class: 'w-3 h-3 bg-orange-400', style: 'top: 25%; right: 15%; animation-delay: 1.5s;' },
  { class: 'w-5 h-5 bg-yellow-400', style: 'bottom: 20%; left: 20%; animation-delay: 3s;' },
  { class: 'w-3 h-3 bg-pink-400', style: 'bottom: 30%; right: 25%; animation-delay: 4.5s;' }
]

// Sections data
const sections = [
  {
    id: 'return-eligibility',
    title: 'Return Eligibility',
    subtitle: 'What can be returned',
    icon: 'mdi:clipboard-check',
    iconBg: 'bg-gradient-to-r from-green-500 to-emerald-500'
  },
  {
    id: 'no-change-mind',
    title: 'No Change of Mind',
    subtitle: 'Policy restrictions',
    icon: 'mdi:close-circle-outline',
    iconBg: 'bg-gradient-to-r from-red-500 to-rose-500'
  },
  {
    id: 'return-process',
    title: 'Return Process',
    subtitle: 'How returns work',
    icon: 'mdi:package-variant',
    iconBg: 'bg-gradient-to-r from-blue-500 to-cyan-500'
  },
  {
    id: 'refund-process',
    title: 'Refund Process',
    subtitle: 'How refunds work',
    icon: 'mdi:cash-refund',
    iconBg: 'bg-gradient-to-r from-purple-500 to-indigo-500'
  },
  {
    id: 'non-refundable',
    title: 'Non-Refundable Items',
    subtitle: 'Restrictions apply',
    icon: 'mdi:cancel',
    iconBg: 'bg-gradient-to-r from-orange-500 to-red-500'
  },
  {
    id: 'contact-support',
    title: 'Contact Support',
    subtitle: 'Get help with returns',
    icon: 'mdi:headset',
    iconBg: 'bg-gradient-to-r from-teal-500 to-cyan-500'
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
      if (returnHeader.value) {
        gsap.fromTo(returnHeader.value,
            { y: -50, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 1,
              ease: 'back.out(1.7)'
            }
        )
      }

      // Policy overview animation
      if (policyOverview.value) {
        gsap.fromTo(policyOverview.value,
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
      if (returnContent.value) {
        gsap.fromTo(returnContent.value,
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
      gsap.utils.toArray('.return-section').forEach((section: any, index: number) => {
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
  title: 'Return & Refund Policy - Easy Returns Process',
  description: 'Learn about our return eligibility, refund process, and how to initiate returns. Clear policies for hassle-free shopping.',
})
</script>

<style scoped>
/* Performance optimizations */
.will-change-transform {
  will-change: transform;
}

/* Overview card hover effects */
.overview-card {
  position: relative;
  transition: all 0.3s ease;
}

.overview-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, transparent, rgba(239, 68, 68, 0.1));
  opacity: 0;
  transition: opacity 0.3s ease;
  border-radius: 1rem;
}

.overview-card:hover::before {
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
  background: linear-gradient(135deg, transparent, rgba(239, 68, 68, 0.1));
  opacity: 0;
  transition: opacity 0.3s ease;
  border-radius: 0.75rem;
}

.toc-item:hover::before {
  opacity: 1;
}

/* Section animations */
.return-section {
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
.timeline-card,
.feature-card,
.contact-method,
.list-card {
  position: relative;
  transition: all 0.3s ease;
}

.timeline-card:hover,
.feature-card:hover,
.contact-method:hover,
.list-card:hover {
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
  .return-orb-2 {
    display: none;
  }
}

@media (max-width: 640px) {
  .policy-overview .grid {
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
  .return-orb-1, .return-orb-3 {
    width: 4rem;
    height: 4rem;
  }

  .particles {
    display: none;
  }

  .policy-overview .grid {
    grid-template-columns: 1fr;
  }
}

/* Focus states for accessibility */
.toc-item:focus-visible,
.back-to-top button:focus-visible {
  outline: 2px solid #ef4444;
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
