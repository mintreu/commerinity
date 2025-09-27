<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-gray-900 dark:to-purple-950 overflow-x-hidden">

    <!-- Floating Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden will-change-transform">
      <div ref="orb1" class="terms-orb-1 absolute top-20 left-20 w-80 h-80 bg-gradient-to-r from-blue-400 to-cyan-400 rounded-full opacity-10 blur-3xl will-change-transform"></div>
      <div ref="orb2" class="terms-orb-2 absolute bottom-20 right-20 w-72 h-72 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full opacity-15 blur-3xl will-change-transform"></div>
      <div ref="orb3" class="terms-orb-3 absolute top-1/2 left-1/3 w-64 h-64 bg-gradient-to-r from-emerald-400 to-teal-400 rounded-full opacity-10 blur-2xl will-change-transform"></div>

      <!-- Terms Particles -->
      <div class="terms-particles">
        <div v-for="(particle, index) in particles" :key="`particle-${index}`"
             class="particle absolute rounded-full opacity-60 animate-bounce will-change-transform"
             :class="particle.class"
             :style="particle.style">
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 px-4 lg:px-20 py-12">

      <!-- Terms Header -->
      <div class="terms-header mb-16 max-w-4xl mx-auto" ref="termsHeader">
        <div class="text-center">
          <div class="header-badge inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-indigo-500/10 to-purple-500/10 border border-indigo-200 dark:border-indigo-800 backdrop-blur-sm mb-6">
            <Icon name="mdi:file-document" class="w-5 h-5 mr-3 text-indigo-600 dark:text-indigo-400" />
            <span class="font-semibold text-indigo-700 dark:text-indigo-300">Legal Information</span>
          </div>

          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gray-900 dark:text-white mb-6">
            Terms & <span class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">Conditions</span>
          </h1>

          <div class="terms-meta bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6 mb-8">
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

          <div class="terms-intro bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50 p-8">
            <p class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
              These Terms and Conditions ("Terms") govern access to and use of <strong class="text-gray-900 dark:text-white">{{ websiteName }}</strong>
              (the "Website") and services provided by <strong class="text-gray-900 dark:text-white">{{ companyName }}</strong> ("we", "us", or "our").
              By accessing or using the Website, you agree to be bound by these Terms. If you do not agree with these Terms,
              please discontinue use of the Website.
            </p>
          </div>
        </div>
      </div>

      <!-- Table of Contents -->
      <div class="toc-section mb-16 max-w-4xl mx-auto" ref="tocSection">
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
          <div class="toc-header bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-6">
            <h2 class="text-2xl font-black flex items-center">
              <Icon name="mdi:format-list-numbered" class="w-6 h-6 mr-3" />
              Table of Contents
            </h2>
          </div>
          <div class="toc-content p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <a
                  v-for="(section, index) in sections"
                  :key="section.id"
                  :href="`#${section.id}`"
                  class="toc-item flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-300 hover:scale-105 group"
                  @click="scrollToSection(section.id, $event)"
              >
                <span class="toc-number w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-full flex items-center justify-center text-sm font-bold">
                  {{ index + 1 }}
                </span>
                <span class="text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 font-medium">
                  {{ section.title }}
                </span>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Terms Content -->
      <div class="terms-content max-w-4xl mx-auto" ref="termsContent">
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
          <div class="content-header bg-gradient-to-r from-purple-600 to-pink-600 text-white p-6">
            <h2 class="text-2xl font-black flex items-center">
              <Icon name="mdi:gavel" class="w-6 h-6 mr-3" />
              Terms & Conditions
            </h2>
            <p class="text-purple-100 mt-2">Please read these terms carefully before using our services</p>
          </div>

          <div class="terms-sections p-8 space-y-12">

            <!-- Eligibility & Accounts -->
            <section :id="sections[0].id" class="terms-section">
              <div class="section-header flex items-center gap-4 mb-6">
                <div class="section-icon w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:account-check" class="w-6 h-6 text-white" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ sections[0].title }}</h3>
              </div>
              <div class="section-content space-y-4">
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  Use of the Website requires that users have the legal capacity to form a binding contract under applicable law.
                  Account information must be current, accurate, and complete, and users agree to promptly update their
                  information as it changes over time. Users are solely responsible for maintaining the confidentiality of login
                  credentials and for all activities occurring under their accounts.
                </p>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  Users must immediately notify us of any unauthorized access or suspected compromise of their accounts so that
                  appropriate measures can be taken. We reserve the right to suspend or terminate accounts that violate these
                  Terms, applicable laws, or present a security risk to the Website or other users, subject to any rights users
                  may have under local law.
                </p>
              </div>
            </section>

            <!-- Acceptable Use -->
            <section :id="sections[1].id" class="terms-section">
              <div class="section-header flex items-center gap-4 mb-6">
                <div class="section-icon w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:shield-check" class="w-6 h-6 text-white" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ sections[1].title }}</h3>
              </div>
              <div class="section-content space-y-4">
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  Users agree to use the Website in a lawful, responsible manner and to refrain from activities that could
                  disrupt service, harm others, or infringe on rights. Prohibited behavior includes, without limitation,
                  distributing malware, attempting unauthorized access, scraping without consent, spamming, harassing other users,
                  misrepresenting identity, and violating intellectual property or privacy rights.
                </p>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  Where required by law or to protect the rights and safety of our users and systems, we may cooperate with law
                  enforcement or other authorities. Users are responsible for ensuring their devices, networks, and software used
                  to access the Website are adequately secured and compliant with these Terms.
                </p>
              </div>
            </section>

            <!-- Intellectual Property -->
            <section :id="sections[2].id" class="terms-section">
              <div class="section-header flex items-center gap-4 mb-6">
                <div class="section-icon w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:copyright" class="w-6 h-6 text-white" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ sections[2].title }}</h3>
              </div>
              <div class="section-content space-y-4">
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  The Website and its content—including text, graphics, logos, icons, images, audiovisual materials, software,
                  and trademarks—are owned by <strong class="text-gray-900 dark:text-white">{{ companyName }}</strong> or our licensors and are protected by intellectual property laws.
                  Subject to compliance with these Terms, we grant a limited, non-exclusive, non-transferable, revocable license
                  to access and use the Website for its intended purposes.
                </p>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  Users must not modify, reverse engineer, decompile, disassemble, copy, distribute, or create derivative works
                  from the Website or its content except as permitted by mandatory law. All goodwill arising from permitted use
                  of our marks accrues to <strong class="text-gray-900 dark:text-white">{{ companyName }}</strong>.
                </p>
              </div>
            </section>

            <!-- User Content -->
            <section :id="sections[3].id" class="terms-section">
              <div class="section-header flex items-center gap-4 mb-6">
                <div class="section-icon w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:upload" class="w-6 h-6 text-white" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ sections[3].title }}</h3>
              </div>
              <div class="section-content space-y-4">
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  Users may submit content such as text, images, or other materials subject to these Terms and applicable law.
                  By submitting content, users grant us a worldwide, non-exclusive, royalty-free license to host, store,
                  reproduce, display, and process such content solely as necessary to operate, maintain, and improve the Website
                  and services.
                </p>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  We may moderate or remove content that, in our reasonable judgment, violates these Terms or applicable law.
                  We are not obligated to monitor all content and do not assume responsibility for user submissions. Users remain
                  fully responsible for the content they provide.
                </p>
              </div>
            </section>

            <!-- Third-Party Services -->
            <section :id="sections[4].id" class="terms-section">
              <div class="section-header flex items-center gap-4 mb-6">
                <div class="section-icon w-12 h-12 bg-gradient-to-r from-teal-500 to-cyan-500 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:link-variant" class="w-6 h-6 text-white" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ sections[4].title }}</h3>
              </div>
              <div class="section-content space-y-4">
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  The Website may reference or link to third-party websites, products, or services for convenience. We do not
                  control and are not responsible for third-party content, policies, or practices. Accessing third-party services
                  is at the user's sole risk and may be subject to separate terms and privacy policies.
                </p>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  Where the Website integrates third-party features, the functionality and availability of such features may be
                  controlled by those providers. We may add, modify, or discontinue integrations at any time to maintain
                  compatibility, security, or compliance.
                </p>
              </div>
            </section>

            <!-- Payments & Purchases -->
            <section :id="sections[5].id" class="terms-section">
              <div class="section-header flex items-center gap-4 mb-6">
                <div class="section-icon w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:credit-card" class="w-6 h-6 text-white" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ sections[5].title }}</h3>
              </div>
              <div class="section-content space-y-4">
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  If the Website offers paid features, users agree to pay all fees, charges, and applicable taxes in accordance
                  with the displayed pricing and payment terms. Prices, features, and billing cycles may change with appropriate
                  notice as required by law. Unless otherwise stated, fees are non-refundable to the fullest extent permitted by law.
                </p>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  Users authorize us and our payment processors to charge the chosen payment method for purchases and renewals,
                  where applicable. Users are responsible for maintaining accurate billing information and for any charges
                  incurred under their account.
                </p>
              </div>
            </section>

            <!-- Privacy -->
            <section :id="sections[6].id" class="terms-section">
              <div class="section-header flex items-center gap-4 mb-6">
                <div class="section-icon w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:shield-account" class="w-6 h-6 text-white" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ sections[6].title }}</h3>
              </div>
              <div class="section-content space-y-4">
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  Use of the Website is also governed by our Privacy Policy, which explains how we collect, use, disclose, and
                  safeguard personal data. By using the Website, users acknowledge that they have reviewed our Privacy Policy and
                  consent to our data practices as described therein.
                </p>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  We implement appropriate technical and organizational measures intended to protect personal data, but no
                  system is fully secure. Users should take steps to protect their own information, including using strong
                  passwords and enabling available security features.
                </p>
              </div>
            </section>

            <!-- Disclaimers -->
            <section :id="sections[7].id" class="terms-section">
              <div class="section-header flex items-center gap-4 mb-6">
                <div class="section-icon w-12 h-12 bg-gradient-to-r from-red-500 to-pink-500 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:alert-circle" class="w-6 h-6 text-white" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ sections[7].title }}</h3>
              </div>
              <div class="section-content space-y-4">
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  The Website and all related services are provided on an "as is" and "as available" basis, without warranties
                  of any kind, whether express, implied, or statutory. To the maximum extent permitted by law, we disclaim all
                  implied warranties, including merchantability, fitness for a particular purpose, and non-infringement.
                </p>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  Some jurisdictions do not allow limitations on implied warranties, so parts of this disclaimer may not apply
                  to certain users. Where required by law, users may have specific legal rights, and these Terms are not intended
                  to restrict such non-waivable rights.
                </p>
              </div>
            </section>

            <!-- Limitation of Liability -->
            <section :id="sections[8].id" class="terms-section">
              <div class="section-header flex items-center gap-4 mb-6">
                <div class="section-icon w-12 h-12 bg-gradient-to-r from-gray-500 to-gray-600 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:scale-balance" class="w-6 h-6 text-white" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ sections[8].title }}</h3>
              </div>
              <div class="section-content space-y-4">
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  To the maximum extent permitted by law, <strong class="text-gray-900 dark:text-white">{{ companyName }}</strong> will not be liable for indirect, incidental,
                  consequential, special, exemplary, or punitive damages; loss of profits, revenues, goodwill, or data; or
                  business interruption arising from or related to use of or inability to use the Website.
                </p>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  Our total liability for any claim relating to the Website shall not exceed the greater of (a) amounts paid by the user
                  for the service in the twelve (12) months preceding the claim or (b) one hundred (100) USD (or equivalent).
                </p>
              </div>
            </section>

            <!-- Termination -->
            <section :id="sections[9].id" class="terms-section">
              <div class="section-header flex items-center gap-4 mb-6">
                <div class="section-icon w-12 h-12 bg-gradient-to-r from-red-600 to-rose-600 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:account-remove" class="w-6 h-6 text-white" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ sections[9].title }}</h3>
              </div>
              <div class="section-content space-y-4">
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  We may suspend or terminate access to the Website at any time where reasonably necessary to address security
                  risks, legal compliance, or material violations of these Terms. Where required by law, we will provide advance
                  notice; otherwise, action may be immediate to protect the Website and users.
                </p>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  Upon termination, rights granted to users under these Terms will cease, and users must stop using the Website.
                  Sections that by nature should survive termination will continue to apply.
                </p>
              </div>
            </section>

            <!-- Governing Law -->
            <section :id="sections[10].id" class="terms-section">
              <div class="section-header flex items-center gap-4 mb-6">
                <div class="section-icon w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:gavel" class="w-6 h-6 text-white" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ sections[10].title }}</h3>
              </div>
              <div class="section-content space-y-4">
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  These Terms are governed by the laws of the jurisdiction where <strong class="text-gray-900 dark:text-white">{{ companyName }}</strong> is established, excluding its
                  conflict-of-law principles, unless a different governing law is required by mandatory local consumer
                  protection laws.
                </p>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  Subject to mandatory law, disputes will be subject to the exclusive jurisdiction and venue of
                  the competent courts in that jurisdiction, and users consent to personal jurisdiction there.
                </p>
              </div>
            </section>

            <!-- Changes to Terms -->
            <section :id="sections[11].id" class="terms-section">
              <div class="section-header flex items-center gap-4 mb-6">
                <div class="section-icon w-12 h-12 bg-gradient-to-r from-green-600 to-teal-600 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:update" class="w-6 h-6 text-white" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ sections[11].title }}</h3>
              </div>
              <div class="section-content space-y-4">
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  We may update these Terms from time to time to reflect operational, legal, or regulatory changes. Material
                  updates will be effective upon posting or as otherwise stated at the time of publication. Continued use of the
                  Website after changes become effective constitutes acceptance of the updated Terms.
                </p>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                  If a user does not agree to the updated Terms, they should discontinue use of the Website. Users should review
                  this page periodically for any changes.
                </p>
              </div>
            </section>

            <!-- Contact -->
            <section :id="sections[12].id" class="terms-section">
              <div class="section-header flex items-center gap-4 mb-6">
                <div class="section-icon w-12 h-12 bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl flex items-center justify-center">
                  <Icon name="mdi:phone" class="w-6 h-6 text-white" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ sections[12].title }}</h3>
              </div>
              <div class="section-content">
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                  Questions or concerns regarding these Terms can be directed to:
                </p>
                <div class="contact-card bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl p-6 border border-purple-200 dark:border-purple-800">
                  <div class="space-y-3">
                    <div class="flex items-center gap-3">
                      <Icon name="mdi:domain" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                      <span class="font-bold text-gray-900 dark:text-white">{{ companyName }}</span>
                    </div>
                    <div class="flex items-start gap-3">
                      <Icon name="mdi:map-marker" class="w-5 h-5 text-purple-600 dark:text-purple-400 mt-0.5" />
                      <span class="text-gray-700 dark:text-gray-300">{{ address }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                      <Icon name="mdi:email" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                      <a :href="`mailto:${supportEmail}`" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-200 font-semibold transition-colors duration-200">
                        {{ supportEmail }}
                      </a>
                    </div>
                    <div class="flex items-center gap-3">
                      <Icon name="mdi:phone" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                      <a :href="`tel:${phoneNumber}`" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-200 font-semibold transition-colors duration-200">
                        {{ phoneNumber }}
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>

      <!-- Back to Top -->
      <div class="back-to-top fixed bottom-8 right-8 z-20">
        <button
            @click="scrollToTop"
            class="w-14 h-14 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-full shadow-2xl hover:shadow-blue-500/25 transition-all duration-300 transform hover:scale-110 flex items-center justify-center"
        >
          <Icon name="mdi:arrow-up" class="w-6 h-6" />
        </button>
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
const termsHeader = ref<HTMLElement>()
const tocSection = ref<HTMLElement>()
const termsContent = ref<HTMLElement>()

let gsapContext: any = null

// Animated particles
const particles = [
  { class: 'w-4 h-4 bg-indigo-400', style: 'top: 15%; left: 10%; animation-delay: 0s;' },
  { class: 'w-3 h-3 bg-purple-400', style: 'top: 25%; right: 15%; animation-delay: 1.5s;' },
  { class: 'w-5 h-5 bg-pink-400', style: 'bottom: 20%; left: 20%; animation-delay: 3s;' },
  { class: 'w-3 h-3 bg-blue-400', style: 'bottom: 30%; right: 25%; animation-delay: 4.5s;' }
]

// Sections data
const sections = [
  { id: 'eligibility', title: 'Eligibility & Accounts' },
  { id: 'acceptable-use', title: 'Acceptable Use' },
  { id: 'intellectual-property', title: 'Intellectual Property' },
  { id: 'user-content', title: 'User Content' },
  { id: 'third-party', title: 'Third-Party Services' },
  { id: 'payments', title: 'Payments & Purchases' },
  { id: 'privacy', title: 'Privacy' },
  { id: 'disclaimers', title: 'Disclaimers' },
  { id: 'liability', title: 'Limitation of Liability' },
  { id: 'termination', title: 'Termination' },
  { id: 'governing-law', title: 'Governing Law' },
  { id: 'changes', title: 'Changes to Terms' },
  { id: 'contact', title: 'Contact Information' }
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
      if (termsHeader.value) {
        gsap.fromTo(termsHeader.value,
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
      if (termsContent.value) {
        gsap.fromTo(termsContent.value,
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
      gsap.utils.toArray('.terms-section').forEach((section: any, index: number) => {
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
  title: 'Terms & Conditions - Legal Information',
  description: 'Read our terms and conditions to understand your rights and responsibilities when using our services.',
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
}

.toc-item::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, transparent, rgba(59, 130, 246, 0.1));
  opacity: 0;
  transition: opacity 0.3s ease;
  border-radius: 0.75rem;
}

.toc-item:hover::before {
  opacity: 1;
}

/* Section animations */
.terms-section {
  position: relative;
}

.section-icon {
  position: relative;
  transition: all 0.3s ease;
}

.section-icon:hover {
  transform: scale(1.1);
}

/* Contact card hover effects */
.contact-card {
  position: relative;
  transition: all 0.3s ease;
}

.contact-card:hover {
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
  .terms-orb-2 {
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

  .section-icon {
    align-self: center;
  }
}

@media (max-width: 480px) {
  .terms-orb-1, .terms-orb-3 {
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
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Smooth scroll behavior */
html {
  scroll-behavior: smooth;
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .bg-white\/80,
  .bg-white\/80 {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }
}
</style>
