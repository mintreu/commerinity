<script setup lang="ts">
definePageMeta({
  layout: 'public'
})

const config = useRuntimeConfig()
const websiteName = config.public.appName
const companyName = config.public.companyLegalName
const supportEmail = config.public.supportEmail
const phoneNumber = config.public.supportPhone
const address = config.public.companyAddress

const effectiveDate = 'February 3, 2026'
const lastUpdated = 'February 3, 2026'

useSeoMeta({
  title: 'Cookie Policy - How We Use Cookies',
  description: 'Learn about how we use cookies and similar technologies to improve your browsing experience and provide better services.'
})
</script>

<template>
  <div class="policy-page min-h-screen">
    <!-- Hero Section -->
    <div class="policy-hero bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 py-16 md:py-20">
      <UContainer>
        <div class="text-center text-white">
          <div class="policy-badge inline-flex items-center px-5 py-2.5 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 mb-6">
            <UIcon
              name="i-lucide-cookie"
              class="w-4 h-4 mr-2"
            />
            <span>Cookies & Tracking</span>
          </div>
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 tracking-tight">
            Cookie Policy
          </h1>
          <div class="policy-meta flex flex-wrap justify-center gap-4 md:gap-6 text-white/80">
            <div class="flex items-center gap-2">
              <UIcon
                name="i-lucide-calendar"
                class="w-4 h-4"
              />
              <span>Effective: {{ effectiveDate }}</span>
            </div>
            <div class="hidden md:block w-px h-5 bg-white/30" />
            <div class="flex items-center gap-2">
              <UIcon
                name="i-lucide-clock"
                class="w-4 h-4"
              />
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
            This Cookie Policy explains how <strong>{{ companyName }}</strong> uses cookies and similar technologies on <strong>{{ websiteName }}</strong>. By continuing to browse our website, you consent to our use of cookies as described in this policy.
          </p>
        </div>

        <div class="policy-content">
          <h2>1. What Are Cookies?</h2>
          <p>
            Cookies are small text files stored on your browser or device when you visit a website. They help websites remember your preferences, analyze site traffic, and provide personalized experiences. Cookies contain information that is transferred to your device's storage.
          </p>

          <h2>2. Types of Cookies We Use</h2>
          <p>We use several categories of cookies on our website:</p>

          <h3>Strictly Necessary Cookies</h3>
          <p>
            These cookies are essential for the website to function properly. They enable core functionality such as secure login, shopping cart operations, payment processing, and account access. Without these cookies, certain services cannot be provided.
          </p>

          <h3>Analytical/Performance Cookies</h3>
          <p>
            These cookies help us understand how visitors interact with our website by collecting and reporting information anonymously. They allow us to count visits, identify traffic sources, and measure site performance. This data helps us improve user experience by ensuring visitors find what they need easily.
          </p>

          <h3>Functionality Cookies</h3>
          <p>
            These cookies enable enhanced functionality and personalization. They remember your preferences such as language settings, region selection, username display, and interface customizations. This allows us to provide a more personalized experience when you return to our website.
          </p>

          <h3>Targeting/Advertising Cookies</h3>
          <p>
            These cookies track your browsing activity across our website to build a profile of your interests. They record pages visited, links followed, and content viewed. We use this information to deliver relevant advertisements and make our marketing more effective. This data may be shared with third-party advertising partners.
          </p>

          <h2>3. Third-Party Cookies</h2>
          <p>
            Third-party service providers may set cookies on our website for analytics, advertising, and external services. These partners include advertising networks, social media platforms, and web traffic analysis providers. We do not control these third-party cookies.
          </p>
          <p>
            We use analytics services to monitor website performance and understand visitor behavior. These services are subject to their own privacy policies and terms of use. Third-party cookies are typically analytical/performance or targeting cookies used for measurement and advertising purposes.
          </p>

          <h2>4. How Long Do Cookies Last?</h2>
          <p>
            Cookies can be session-based or persistent. Session cookies are temporary and deleted when you close your browser. Persistent cookies remain on your device for a set period or until manually deleted. The duration depends on the cookie's purpose—some last days, others months or years.
          </p>

          <h2>5. Managing Your Cookie Preferences</h2>
          <p>
            You can control and manage cookies through your browser settings. Most browsers allow you to refuse all cookies or accept only certain types. However, blocking strictly necessary cookies may prevent access to essential website features and services.
          </p>
          <p>
            To manage cookies, adjust your browser preferences. You can view, delete, or block cookies at any time. For detailed instructions, visit your browser's help section. Note that disabling cookies may affect website functionality and user experience.
          </p>
          <p>
            For more information about cookies and how to manage them, visit <a
              href="https://www.allaboutcookies.org"
              target="_blank"
              class="text-violet-600 dark:text-violet-400 hover:underline"
            >www.allaboutcookies.org</a> or <a
              href="https://www.aboutcookies.org"
              target="_blank"
              class="text-violet-600 dark:text-violet-400 hover:underline"
            >www.aboutcookies.org</a>.
          </p>

          <h2>6. Do Not Track Signals</h2>
          <p>
            Some browsers support "Do Not Track" (DNT) signals. We respect user privacy preferences and handle DNT signals according to applicable regulations. However, industry standards for interpreting and responding to DNT signals are still evolving.
          </p>

          <h2>7. Changes to This Cookie Policy</h2>
          <p>
            We may update this Cookie Policy periodically to reflect changes in our practices, technologies, legal requirements, or business operations. Updates will be posted on this page with a revised "Last Updated" date. Significant changes may be communicated via email or website notifications. Please review this policy regularly to stay informed.
          </p>

          <h2>8. Contact Us</h2>
          <p>If you have questions about our use of cookies or this Cookie Policy, please contact us:</p>

          <div class="policy-contact">
            <div class="grid gap-4 sm:grid-cols-2">
              <div class="flex items-start gap-3">
                <div class="icon-box icon-box-sm icon-box-soft-primary rounded-lg">
                  <UIcon
                    name="i-lucide-building-2"
                    class="w-4 h-4"
                  />
                </div>
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">
                    Company
                  </p>
                  <p class="font-semibold text-slate-900 dark:text-white">
                    {{ companyName }}
                  </p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="icon-box icon-box-sm icon-box-soft-primary rounded-lg">
                  <UIcon
                    name="i-lucide-mail"
                    class="w-4 h-4"
                  />
                </div>
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">
                    Email
                  </p>
                  <a
                    :href="`mailto:${supportEmail}`"
                    class="font-semibold text-violet-600 dark:text-violet-400 hover:underline"
                  >{{ supportEmail }}</a>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="icon-box icon-box-sm icon-box-soft-primary rounded-lg">
                  <UIcon
                    name="i-lucide-phone"
                    class="w-4 h-4"
                  />
                </div>
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">
                    Phone
                  </p>
                  <a
                    :href="`tel:${phoneNumber}`"
                    class="font-semibold text-violet-600 dark:text-violet-400 hover:underline"
                  >{{ phoneNumber }}</a>
                </div>
              </div>
              <div
                v-if="address"
                class="flex items-start gap-3"
              >
                <div class="icon-box icon-box-sm icon-box-soft-primary rounded-lg">
                  <UIcon
                    name="i-lucide-map-pin"
                    class="w-4 h-4"
                  />
                </div>
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">
                    Address
                  </p>
                  <p class="font-semibold text-slate-900 dark:text-white">
                    {{ address }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </UContainer>
  </div>
</template>
