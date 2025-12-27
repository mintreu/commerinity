<script setup lang="ts">
definePageMeta({
  layout: 'guest'
})

const config = useRuntimeConfig()
const websiteName = config.public.appName
const companyName = config.public.companyLegalName
const supportEmail = config.public.supportEmail
const phoneNumber = config.public.supportPhone
const address = config.public.companyAddress

const effectiveDate = 'July 20, 2025'
const lastUpdated = 'September 25, 2025'

useSeoMeta({
  title: 'Privacy Policy - Your Data Protection',
  description: 'Learn how we collect, use, and protect your personal information. Your privacy is our priority.',
})
</script>

<template>
  <div class="policy-page min-h-screen">
    <!-- Hero Section -->
    <div class="policy-hero bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 py-16 md:py-20">
      <UContainer>
        <div class="text-center text-white">
          <div class="policy-badge inline-flex items-center px-5 py-2.5 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 mb-6">
            <UIcon name="i-lucide-shield-check" class="w-4 h-4 mr-2" />
            <span>Privacy & Data Protection</span>
          </div>
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 tracking-tight">
            Privacy Policy
          </h1>
          <div class="policy-meta flex flex-wrap justify-center gap-4 md:gap-6 text-white/80">
            <div class="flex items-center gap-2">
              <UIcon name="i-lucide-calendar" class="w-4 h-4" />
              <span>Effective: {{ effectiveDate }}</span>
            </div>
            <div class="hidden md:block w-px h-5 bg-white/30" />
            <div class="flex items-center gap-2">
              <UIcon name="i-lucide-clock" class="w-4 h-4" />
              <span>Updated: {{ lastUpdated }}</span>
            </div>
          </div>
        </div>
      </UContainer>
    </div>

    <UContainer class="py-12 md:py-16">
      <div class="glass-card p-6 md:p-10 lg:p-12 max-w-4xl mx-auto">
        <!-- Introduction -->
        <div class="policy-intro">
          <p>
            This Privacy Policy explains how <strong>{{ companyName }}</strong> ("we", "us", or "our")
            collects, uses, shares, and protects personal information in connection with <strong>{{ websiteName }}</strong>
            (the "Website") and related services. By using the Website, users acknowledge this Policy and consent to our data practices as described herein.
          </p>
        </div>

        <div class="policy-content">
          <h2>1. Information Collection</h2>
          <p>
            We collect information directly from users, automatically via the Website, and from service providers. This includes identity and contact data (name, email, mobile), account credentials, order and transaction records, delivery addresses, device and usage data (pages viewed, clicks, approximate location, browser, OS), and communications with support.
          </p>
          <p>
            Payments are handled by certified processors; we do not store full card numbers or sensitive authentication data. For payouts, banking data may be encrypted and used solely to execute transfers. Wallet PINs, OTPs, and passwords are used only for authentication with hashing or encryption.
          </p>

          <h2>2. How We Use Information</h2>
          <p>
            We use personal information to operate and secure the Website; process orders, payments, and deliveries; provide wallet services; verify identity and eligibility where required; deliver customer support; personalize content; and improve through analytics.
          </p>
          <p>
            For marketing, we obtain consent where required and provide opt-out options. We minimize collection and keep data only as long as needed unless law requires longer retention.
          </p>

          <h2>3. Cookies & Tracking</h2>
          <p>
            We use cookies, SDKs, and similar technologies to keep the Website reliable, remember preferences, measure performance, and understand usage. Essential cookies support core functionality; analytics or advertising cookies help improve experience.
          </p>
          <p>
            Third-party analytics and ad partners may receive pseudonymous identifiers and usage signals. Users can opt out via browser settings or platform controls as mandated by law.
          </p>

          <h2>4. Information Sharing</h2>
          <p>
            We do not sell personal information publicly. We share data only with service providers, distributors/store partners for fulfillment, and as required by law or to protect rights, safety, or property. Providers are bound by contract and security controls.
          </p>
          <p>
            In mergers or asset transfers, information may be moved under equivalent safeguards. Sensitive credentials are never disclosed publicly and are used only for their specific purpose with strict controls.
          </p>

          <h2>5. Your Rights</h2>
          <p>
            Depending on jurisdiction, users may access, correct, delete, or port their data; object to or restrict processing; and withdraw consent. We verify identity and respond within required timelines, subject to exceptions.
          </p>
          <p>
            Users can manage marketing preferences via unsubscribe links or account settings. Cookie preferences can be managed via browser settings and platform tools.
          </p>

          <h2>6. Security Measures</h2>
          <p>
            We apply organizational and technical safeguards—encryption, access controls, monitoring, and periodic reviews—to mitigate risks. Passwords are hashed, OTPs are time-bound, and wallet PINs are handled confidentially.
          </p>
          <p>
            If a data incident occurs, we follow legal notification duties and response procedures. Users should promptly report suspected compromise so we can assist and protect accounts.
          </p>

          <h2>7. Contact Us</h2>
          <p>For privacy questions, requests, or concerns, please contact our dedicated privacy team:</p>

          <div class="policy-contact">
            <div class="grid gap-4 sm:grid-cols-2">
              <div class="flex items-start gap-3">
                <div class="icon-box icon-box-sm icon-box-soft-primary rounded-lg">
                  <UIcon name="i-lucide-building-2" class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Company</p>
                  <p class="font-semibold text-slate-900 dark:text-white">{{ companyName }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="icon-box icon-box-sm icon-box-soft-primary rounded-lg">
                  <UIcon name="i-lucide-mail" class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Email</p>
                  <a :href="`mailto:${supportEmail}`" class="font-semibold text-violet-600 dark:text-violet-400 hover:underline">{{ supportEmail }}</a>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="icon-box icon-box-sm icon-box-soft-primary rounded-lg">
                  <UIcon name="i-lucide-phone" class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Phone</p>
                  <a :href="`tel:${phoneNumber}`" class="font-semibold text-violet-600 dark:text-violet-400 hover:underline">{{ phoneNumber }}</a>
                </div>
              </div>
              <div v-if="address" class="flex items-start gap-3">
                <div class="icon-box icon-box-sm icon-box-soft-primary rounded-lg">
                  <UIcon name="i-lucide-map-pin" class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Address</p>
                  <p class="font-semibold text-slate-900 dark:text-white">{{ address }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </UContainer>
  </div>
</template>
