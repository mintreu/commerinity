<template>
  <div class="min-h-screen w-screen  bg-gradient-to-br from-gray-50 via-indigo-50 to-purple-50 dark:from-gray-950 dark:via-indigo-950 dark:to-purple-950 overflow-x-hidden">

    <!-- Floating Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden will-change-transform">
      <div ref="orb1" class="privacy-orb-1 absolute top-20 left-20 w-80 h-80 bg-gradient-to-r from-indigo-400 to-blue-400 rounded-full opacity-10 blur-3xl will-change-transform"></div>
      <div ref="orb2" class="privacy-orb-2 absolute bottom-20 right-20 w-72 h-72 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full opacity-15 blur-3xl will-change-transform"></div>
      <div ref="orb3" class="privacy-orb-3 absolute top-1/2 left-1/3 w-64 h-64 bg-gradient-to-r from-cyan-400 to-teal-400 rounded-full opacity-10 blur-2xl will-change-transform"></div>

      <!-- Privacy Particles -->
      <div class="privacy-particles">
        <div v-for="(particle, index) in particles" :key="`particle-${index}`"
             class="particle absolute rounded-full opacity-60 animate-bounce will-change-transform"
             :class="particle.class"
             :style="particle.style">
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 px-4 lg:px-20 py-12">

      <!-- Privacy Header -->
      <div class="privacy-header mb-16 max-w-5xl mx-auto" ref="privacyHeader">
        <div class="text-center">
          <div class="header-badge inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-indigo-500/10 to-purple-500/10 border border-indigo-200 dark:border-indigo-800 backdrop-blur-sm mb-6">
            <Icon name="mdi:shield-account" class="w-5 h-5 mr-3 text-indigo-600 dark:text-indigo-400" />
            <span class="font-semibold text-indigo-700 dark:text-indigo-300">Privacy & Data Protection</span>
          </div>

          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gray-900 dark:text-white mb-6">
            Privacy <span class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">Policy</span>
          </h1>

          <div class="privacy-meta bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6 mb-8">
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

          <div class="privacy-intro bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50 p-8">
            <p class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
              This Privacy Policy explains how <strong class="text-gray-900 dark:text-white">{{ companyName }}</strong> ("we", "us", or "our")
              collects, uses, shares, and protects personal information in connection with <strong class="text-gray-900 dark:text-white">{{ websiteName }}</strong>
              (the "Website") and related services. By using the Website, users acknowledge this Policy and consent to our data practices as described herein.
            </p>
          </div>
        </div>
      </div>

      <!-- Table of Contents -->
      <div class="toc-section mb-16 max-w-5xl mx-auto" ref="tocSection">
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
          <div class="toc-header bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6">
            <h2 class="text-2xl font-black flex items-center">
              <Icon name="mdi:format-list-numbered" class="w-6 h-6 mr-3" />
              What's Covered
            </h2>
            <p class="text-indigo-100 mt-2">Quick navigation to our privacy practices</p>
          </div>
          <div class="toc-content p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <a
                  v-for="(section, index) in sections"
                  :key="section.id"
                  :href="`#${section.id}`"
                  class="toc-item flex items-center gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all duration-300 hover:scale-105 group"
                  @click="scrollToSection(section.id, $event)"
              >
                <div class="toc-icon w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" :class="section.iconBg">
                  <Icon :name="section.icon" class="w-5 h-5 text-white" />
                </div>
                <div class="flex-1">
                  <span class="text-gray-800 dark:text-gray-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 font-semibold block">
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

      <!-- Privacy Content -->
      <div class="privacy-content max-w-5xl mx-auto space-y-12" ref="privacyContent">

        <!-- Information Collection -->
        <section :id="sections[0].id" class="privacy-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:database" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-2xl font-black">{{ sections[0].title }}</h3>
                  <p class="text-blue-100 mt-1">{{ sections[0].subtitle }}</p>
                </div>
              </div>
            </div>
            <div class="section-content p-8 space-y-6">
              <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <div class="lg:col-span-2 space-y-4">
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    We collect information directly from users, automatically via the Website, and from service providers. This includes identity and contact data (name, email, mobile), account credentials, order and transaction records, delivery addresses, device and usage data (pages viewed, clicks, approximate location, browser, OS), and communications with support.
                  </p>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Payments are handled by certified processors; we do not store full card numbers or sensitive authentication data. For payouts, banking data may be encrypted and used solely to execute transfers. Wallet PINs, OTPs, and passwords are used only for authentication with hashing or encryption.
                  </p>
                </div>
                <div class="lg:col-span-1 flex justify-center">
                  <div class="data-types bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 border border-blue-200 dark:border-blue-800">
                    <h4 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                      <Icon name="mdi:folder-multiple" class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" />
                      Data Categories
                    </h4>
                    <ul class="space-y-2 text-sm">
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:account" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                        <span class="text-gray-700 dark:text-gray-300">Identity & Contact</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:shopping" class="w-4 h-4 text-green-600 dark:text-green-400" />
                        <span class="text-gray-700 dark:text-gray-300">Order & Transaction</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:monitor" class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                        <span class="text-gray-700 dark:text-gray-300">Device & Usage</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- How We Use Information -->
        <section :id="sections[1].id" class="privacy-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-green-600 to-emerald-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:cogs" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-2xl font-black">{{ sections[1].title }}</h3>
                  <p class="text-green-100 mt-1">{{ sections[1].subtitle }}</p>
                </div>
              </div>
            </div>
            <div class="section-content p-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    We use personal information to operate and secure the Website; process orders, payments, and deliveries; provide wallet services; verify identity and eligibility where required; deliver customer support; personalize content; and improve through analytics.
                  </p>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    For marketing, we obtain consent where required and provide opt-out options. We minimize collection and keep data only as long as needed unless law requires longer retention.
                  </p>
                </div>
                <div class="use-purposes grid grid-cols-1 gap-4">
                  <div class="purpose-card bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border border-green-200 dark:border-green-800">
                    <div class="flex items-center gap-3">
                      <Icon name="mdi:shield-check" class="w-6 h-6 text-green-600 dark:text-green-400" />
                      <div>
                        <h5 class="font-bold text-gray-900 dark:text-white">Service Operation</h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Secure platform functionality</p>
                      </div>
                    </div>
                  </div>
                  <div class="purpose-card bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-3">
                      <Icon name="mdi:heart" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                      <div>
                        <h5 class="font-bold text-gray-900 dark:text-white">Personalization</h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tailored user experience</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Cookies & Tracking -->
        <section :id="sections[2].id" class="privacy-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-orange-600 to-red-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:cookie" class="w-6 h-6 text-white" />
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
                    We use cookies, SDKs, and similar technologies to keep the Website reliable, remember preferences, measure performance, and understand usage. Essential cookies support core functionality; analytics or advertising cookies help improve experience.
                  </p>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Third-party analytics and ad partners may receive pseudonymous identifiers and usage signals. Users can opt out via browser settings or platform controls as mandated by law.
                  </p>
                </div>
                <div class="lg:col-span-1">
                  <div class="cookie-types bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-2xl p-6 border border-orange-200 dark:border-orange-800">
                    <h4 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                      <Icon name="mdi:cookie-settings" class="w-5 h-5 mr-2 text-orange-600 dark:text-orange-400" />
                      Cookie Types
                    </h4>
                    <ul class="space-y-3 text-sm">
                      <li class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-gray-700 dark:text-gray-300">Essential</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <span class="text-gray-700 dark:text-gray-300">Analytics</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                        <span class="text-gray-700 dark:text-gray-300">Marketing</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Information Sharing -->
        <section :id="sections[3].id" class="privacy-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-purple-600 to-pink-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:share-variant" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-2xl font-black">{{ sections[3].title }}</h3>
                  <p class="text-purple-100 mt-1">{{ sections[3].subtitle }}</p>
                </div>
              </div>
            </div>
            <div class="section-content p-8 space-y-6">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    We do not sell personal information publicly. We share data only with service providers, distributors/store partners for fulfillment, and as required by law or to protect rights, safety, or property. Providers are bound by contract and security controls.
                  </p>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    In mergers or asset transfers, information may be moved under equivalent safeguards. Sensitive credentials are never disclosed publicly and are used only for their specific purpose with strict controls.
                  </p>
                </div>
                <div class="sharing-partners space-y-4">
                  <div class="partner-card bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl p-4 border border-purple-200 dark:border-purple-800">
                    <h5 class="font-bold text-gray-900 dark:text-white mb-2 flex items-center">
                      <Icon name="mdi:handshake" class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-400" />
                      Trusted Partners Only
                    </h5>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                      <li>• Payment processors</li>
                      <li>• Shipping partners</li>
                      <li>• Security services</li>
                      <li>• Analytics providers</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- User Rights -->
        <section :id="sections[4].id" class="privacy-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-teal-600 to-cyan-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:account-key" class="w-6 h-6 text-white" />
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
                    Depending on jurisdiction, users may access, correct, delete, or port their data; object to or restrict processing; and withdraw consent. We verify identity and respond within required timelines, subject to exceptions.
                  </p>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Users can manage marketing preferences via unsubscribe links or account settings. Cookie preferences can be managed via browser settings and platform tools.
                  </p>
                </div>
                <div class="user-rights grid grid-cols-1 gap-3">
                  <div class="right-item bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 rounded-xl p-4 border border-teal-200 dark:border-teal-800">
                    <div class="flex items-center gap-3">
                      <Icon name="mdi:eye" class="w-6 h-6 text-teal-600 dark:text-teal-400" />
                      <div>
                        <h5 class="font-bold text-gray-900 dark:text-white">Access</h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">View your data</p>
                      </div>
                    </div>
                  </div>
                  <div class="right-item bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-3">
                      <Icon name="mdi:pencil" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                      <div>
                        <h5 class="font-bold text-gray-900 dark:text-white">Correct</h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Update information</p>
                      </div>
                    </div>
                  </div>
                  <div class="right-item bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-xl p-4 border border-red-200 dark:border-red-800">
                    <div class="flex items-center gap-3">
                      <Icon name="mdi:delete" class="w-6 h-6 text-red-600 dark:text-red-400" />
                      <div>
                        <h5 class="font-bold text-gray-900 dark:text-white">Delete</h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Remove your data</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Security Measures -->
        <section :id="sections[5].id" class="privacy-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-gray-700 to-gray-800 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:security" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-2xl font-black">{{ sections[5].title }}</h3>
                  <p class="text-gray-300 mt-1">{{ sections[5].subtitle }}</p>
                </div>
              </div>
            </div>
            <div class="section-content p-8">
              <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <div class="lg:col-span-2 space-y-4">
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    We apply organizational and technical safeguards—encryption, access controls, monitoring, and periodic reviews—to mitigate risks. Passwords are hashed, OTPs are time-bound, and wallet PINs are handled confidentially.
                  </p>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    If a data incident occurs, we follow legal notification duties and response procedures. Users should promptly report suspected compromise so we can assist and protect accounts.
                  </p>
                </div>
                <div class="lg:col-span-1">
                  <div class="security-features bg-gradient-to-br from-gray-50 to-slate-50 dark:from-gray-800 dark:to-slate-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
                    <h4 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                      <Icon name="mdi:shield-lock" class="w-5 h-5 mr-2 text-gray-700 dark:text-gray-300" />
                      Security Features
                    </h4>
                    <ul class="space-y-2 text-sm">
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:lock" class="w-4 h-4 text-green-600 dark:text-green-400" />
                        <span class="text-gray-700 dark:text-gray-300">Encryption</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:account-lock" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                        <span class="text-gray-700 dark:text-gray-300">Access Controls</span>
                      </li>
                      <li class="flex items-center gap-2">
                        <Icon name="mdi:monitor-eye" class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                        <span class="text-gray-700 dark:text-gray-300">24/7 Monitoring</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Contact Information -->
        <section :id="sections[6].id" class="privacy-section">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="section-header bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6">
              <div class="flex items-center gap-4">
                <div class="section-icon w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:phone" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-2xl font-black">{{ sections[6].title }}</h3>
                  <p class="text-indigo-100 mt-1">{{ sections[6].subtitle }}</p>
                </div>
              </div>
            </div>
            <div class="section-content p-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                    For privacy questions, requests, or concerns, please contact our dedicated privacy team. We're committed to addressing your inquiries promptly and thoroughly.
                  </p>
                  <div class="contact-methods space-y-4">
                    <div class="contact-method flex items-center gap-4 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl border border-indigo-200 dark:border-indigo-800">
                      <Icon name="mdi:email" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                      <div>
                        <h5 class="font-bold text-gray-900 dark:text-white">Email</h5>
                        <a :href="`mailto:${supportEmail}`" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-200 font-semibold">
                          {{ supportEmail }}
                        </a>
                      </div>
                    </div>
                    <div class="contact-method flex items-center gap-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-200 dark:border-green-800">
                      <Icon name="mdi:phone" class="w-6 h-6 text-green-600 dark:text-green-400" />
                      <div>
                        <h5 class="font-bold text-gray-900 dark:text-white">Phone</h5>
                        <a :href="`tel:${phoneNumber}`" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 font-semibold">
                          {{ phoneNumber }}
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="contact-card bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-2xl p-6 border border-indigo-200 dark:border-indigo-800">
                  <h4 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                    <Icon name="mdi:office-building" class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" />
                    Company Information
                  </h4>
                  <div class="space-y-3 text-sm">
                    <div>
                      <span class="font-semibold text-gray-900 dark:text-white block">{{ companyName }}</span>
                    </div>
                    <div class="flex items-start gap-2">
                      <Icon name="mdi:map-marker" class="w-4 h-4 text-indigo-600 dark:text-indigo-400 mt-0.5" />
                      <span class="text-gray-700 dark:text-gray-300">{{ address }}</span>
                    </div>
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
const privacyHeader = ref<HTMLElement>()
const tocSection = ref<HTMLElement>()
const privacyContent = ref<HTMLElement>()

let gsapContext: any = null

// Animated particles
const particles = [
  { class: 'w-4 h-4 bg-indigo-400', style: 'top: 15%; left: 10%; animation-delay: 0s;' },
  { class: 'w-3 h-3 bg-purple-400', style: 'top: 25%; right: 15%; animation-delay: 1.5s;' },
  { class: 'w-5 h-5 bg-cyan-400', style: 'bottom: 20%; left: 20%; animation-delay: 3s;' },
  { class: 'w-3 h-3 bg-pink-400', style: 'bottom: 30%; right: 25%; animation-delay: 4.5s;' }
]

// Sections data
const sections = [
  {
    id: 'information-collection',
    title: 'Information Collection',
    subtitle: 'What data we gather',
    icon: 'mdi:database',
    iconBg: 'bg-gradient-to-r from-blue-500 to-indigo-500'
  },
  {
    id: 'information-use',
    title: 'How We Use Data',
    subtitle: 'Purpose of processing',
    icon: 'mdi:cogs',
    iconBg: 'bg-gradient-to-r from-green-500 to-emerald-500'
  },
  {
    id: 'cookies-tracking',
    title: 'Cookies & Tracking',
    subtitle: 'Website technologies',
    icon: 'mdi:cookie',
    iconBg: 'bg-gradient-to-r from-orange-500 to-red-500'
  },
  {
    id: 'information-sharing',
    title: 'Information Sharing',
    subtitle: 'When we share data',
    icon: 'mdi:share-variant',
    iconBg: 'bg-gradient-to-r from-purple-500 to-pink-500'
  },
  {
    id: 'user-rights',
    title: 'Your Rights',
    subtitle: 'Control your data',
    icon: 'mdi:account-key',
    iconBg: 'bg-gradient-to-r from-teal-500 to-cyan-500'
  },
  {
    id: 'security-measures',
    title: 'Security Measures',
    subtitle: 'How we protect data',
    icon: 'mdi:security',
    iconBg: 'bg-gradient-to-r from-gray-600 to-gray-700'
  },
  {
    id: 'contact-information',
    title: 'Contact Us',
    subtitle: 'Get privacy support',
    icon: 'mdi:phone',
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
      if (privacyHeader.value) {
        gsap.fromTo(privacyHeader.value,
            { y: -50, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 1,
              ease: 'back.out(1.7)'
            }
        )
      }

      // TOC animation
      if (tocSection.value) {
        gsap.fromTo(tocSection.value,
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

      // Content animation
      if (privacyContent.value) {
        gsap.fromTo(privacyContent.value,
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

      // Section animations
      gsap.utils.toArray('.privacy-section').forEach((section: any, index: number) => {
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
  title: 'Privacy Policy - Your Data Protection',
  description: 'Learn how we collect, use, and protect your personal information. Your privacy is our priority.',
})
</script>

<style scoped>
/* Performance optimizations */
.will-change-transform {
  will-change: transform;
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
  background: linear-gradient(135deg, transparent, rgba(79, 70, 229, 0.1));
  opacity: 0;
  transition: opacity 0.3s ease;
  border-radius: 0.75rem;
}

.toc-item:hover::before {
  opacity: 1;
}

/* Section animations */
.privacy-section {
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
.purpose-card,
.right-item,
.contact-method {
  position: relative;
  transition: all 0.3s ease;
}

.purpose-card:hover,
.right-item:hover,
.contact-method:hover {
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
  .privacy-orb-2 {
    display: none;
  }
}

@media (max-width: 640px) {
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
  .privacy-orb-1, .privacy-orb-3 {
    width: 4rem;
    height: 4rem;
  }

  .particles {
    display: none;
  }
}

/* Focus states for accessibility */
.toc-item:focus-visible,
.back-to-top button:focus-visible {
  outline: 2px solid #6366f1;
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
