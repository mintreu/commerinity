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

const effectiveDate = 'July 20, 2025'
const lastUpdated = 'September 25, 2025'

useSeoMeta({
  title: 'Terms & Conditions - Legal Information',
  description: 'Read our terms and conditions to understand your rights and responsibilities when using our services.'
})
</script>

<template>
  <div class="policy-page min-h-screen">
    <!-- Hero Section -->
    <div class="policy-hero bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 py-16 md:py-20">
      <UContainer>
        <div class="text-center text-white">
          <div class="policy-badge inline-flex items-center px-5 py-2.5 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 mb-6">
            <UIcon
              name="i-lucide-scale"
              class="w-4 h-4 mr-2"
            />
            <span>Legal Information</span>
          </div>
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 tracking-tight">
            Terms & Conditions
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
            These Terms and Conditions ("Terms") govern access to and use of <strong>{{ websiteName }}</strong>
            (the "Website") and services provided by <strong>{{ companyName }}</strong> ("we", "us", or "our").
            By accessing or using the Website, you agree to be bound by these Terms. If you do not agree with these Terms,
            please discontinue use of the Website.
          </p>
        </div>

        <div class="policy-content">
          <h2>1. Eligibility & Accounts</h2>
          <p>
            Use of the Website requires that users have the legal capacity to form a binding contract under applicable law.
            Account information must be current, accurate, and complete, and users agree to promptly update their
            information as it changes over time. Users are solely responsible for maintaining the confidentiality of login
            credentials and for all activities occurring under their accounts.
          </p>
          <p>
            Users must immediately notify us of any unauthorized access or suspected compromise of their accounts so that
            appropriate measures can be taken. We reserve the right to suspend or terminate accounts that violate these
            Terms, applicable laws, or present a security risk to the Website or other users, subject to any rights users
            may have under local law.
          </p>

          <h2>2. Acceptable Use</h2>
          <p>
            Users agree to use the Website in a lawful, responsible manner and to refrain from activities that could
            disrupt service, harm others, or infringe on rights. Prohibited behavior includes, without limitation,
            distributing malware, attempting unauthorized access, scraping without consent, spamming, harassing other users,
            misrepresenting identity, and violating intellectual property or privacy rights.
          </p>
          <p>
            Where required by law or to protect the rights and safety of our users and systems, we may cooperate with law
            enforcement or other authorities. Users are responsible for ensuring their devices, networks, and software used
            to access the Website are adequately secured and compliant with these Terms.
          </p>

          <h2>3. Intellectual Property</h2>
          <p>
            The Website and its content—including text, graphics, logos, icons, images, audiovisual materials, software,
            and trademarks—are owned by <strong>{{ companyName }}</strong> or our licensors and are protected by intellectual property laws.
            Subject to compliance with these Terms, we grant a limited, non-exclusive, non-transferable, revocable license
            to access and use the Website for its intended purposes.
          </p>
          <p>
            Users must not modify, reverse engineer, decompile, disassemble, copy, distribute, or create derivative works
            from the Website or its content except as permitted by mandatory law. All goodwill arising from permitted use
            of our marks accrues to <strong>{{ companyName }}</strong>.
          </p>

          <h2>4. User Content</h2>
          <p>
            Users may submit content such as text, images, or other materials subject to these Terms and applicable law.
            By submitting content, users grant us a worldwide, non-exclusive, royalty-free license to host, store,
            reproduce, display, and process such content solely as necessary to operate, maintain, and improve the Website
            and services.
          </p>
          <p>
            We may moderate or remove content that, in our reasonable judgment, violates these Terms or applicable law.
            We are not obligated to monitor all content and do not assume responsibility for user submissions. Users remain
            fully responsible for the content they provide.
          </p>

          <h2>5. Third-Party Services</h2>
          <p>
            The Website may reference or link to third-party websites, products, or services for convenience. We do not
            control and are not responsible for third-party content, policies, or practices. Accessing third-party services
            is at the user's sole risk and may be subject to separate terms and privacy policies.
          </p>
          <p>
            Where the Website integrates third-party features, the functionality and availability of such features may be
            controlled by those providers. We may add, modify, or discontinue integrations at any time to maintain
            compatibility, security, or compliance.
          </p>

          <h2>6. Payments & Purchases</h2>
          <p>
            If the Website offers paid features, users agree to pay all fees, charges, and applicable taxes in accordance
            with the displayed pricing and payment terms. Prices, features, and billing cycles may change with appropriate
            notice as required by law. Unless otherwise stated, fees are non-refundable to the fullest extent permitted by law.
          </p>
          <p>
            Users authorize us and our payment processors to charge the chosen payment method for purchases and renewals,
            where applicable. Users are responsible for maintaining accurate billing information and for any charges
            incurred under their account.
          </p>

          <h2>7. Privacy</h2>
          <p>
            Use of the Website is also governed by our Privacy Policy, which explains how we collect, use, disclose, and
            safeguard personal data. By using the Website, users acknowledge that they have reviewed our Privacy Policy and
            consent to our data practices as described therein.
          </p>
          <p>
            We implement appropriate technical and organizational measures intended to protect personal data, but no
            system is fully secure. Users should take steps to protect their own information, including using strong
            passwords and enabling available security features.
          </p>

          <h2>8. Disclaimers</h2>
          <p>
            The Website and all related services are provided on an "as is" and "as available" basis, without warranties
            of any kind, whether express, implied, or statutory. To the maximum extent permitted by law, we disclaim all
            implied warranties, including merchantability, fitness for a particular purpose, and non-infringement.
          </p>
          <p>
            Some jurisdictions do not allow limitations on implied warranties, so parts of this disclaimer may not apply
            to certain users. Where required by law, users may have specific legal rights, and these Terms are not intended
            to restrict such non-waivable rights.
          </p>

          <h2>9. Limitation of Liability</h2>
          <p>
            To the maximum extent permitted by law, <strong>{{ companyName }}</strong> will not be liable for indirect, incidental,
            consequential, special, exemplary, or punitive damages; loss of profits, revenues, goodwill, or data; or
            business interruption arising from or related to use of or inability to use the Website.
          </p>
          <p>
            Our total liability for any claim relating to the Website shall not exceed the greater of (a) amounts paid by the user
            for the service in the twelve (12) months preceding the claim or (b) one hundred (100) USD (or equivalent).
          </p>

          <h2>10. Termination</h2>
          <p>
            We may suspend or terminate access to the Website at any time where reasonably necessary to address security
            risks, legal compliance, or material violations of these Terms. Where required by law, we will provide advance
            notice; otherwise, action may be immediate to protect the Website and users.
          </p>
          <p>
            Upon termination, rights granted to users under these Terms will cease, and users must stop using the Website.
            Sections that by nature should survive termination will continue to apply.
          </p>

          <h2>11. Governing Law</h2>
          <p>
            These Terms are governed by the laws of the jurisdiction where <strong>{{ companyName }}</strong> is established, excluding its
            conflict-of-law principles, unless a different governing law is required by mandatory local consumer
            protection laws.
          </p>
          <p>
            Subject to mandatory law, disputes will be subject to the exclusive jurisdiction and venue of
            the competent courts in that jurisdiction, and users consent to personal jurisdiction there.
          </p>

          <h2>12. Changes to Terms</h2>
          <p>
            We may update these Terms from time to time to reflect operational, legal, or regulatory changes. Material
            updates will be effective upon posting or as otherwise stated at the time of publication. Continued use of the
            Website after changes become effective constitutes acceptance of the updated Terms.
          </p>
          <p>
            If a user does not agree to the updated Terms, they should discontinue use of the Website. Users should review
            this page periodically for any changes.
          </p>

          <h2>13. Contact Information</h2>
          <p>Questions or concerns regarding these Terms can be directed to:</p>

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
