<template>
  <main class="mx-auto max-w-7xl px-4 py-16">
    <!-- Header -->
    <header class="mb-10">
      <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
        Terms & Conditions
      </h1>
      <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
        Effective Date:
        <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ effectiveDate }}</span>
      </p>
      <p class="mt-5 text-base leading-7 text-zinc-700 dark:text-zinc-300 text-justify">
        These Terms and Conditions (“Terms”) govern access to and use of <strong class="font-semibold">{{ websiteName }}</strong>
        (the “Website”) and services provided by {{ companyName }} (“we”, “us”, or “our”). By accessing or using the Website,
        the user agrees to be bound by these Terms. If any user does not agree with these Terms, they should discontinue
        use of the Website. Nothing herein is intended to override any non‑waivable consumer protection right under applicable law.
      </p>
    </header>

    <!-- Body: clean, SEO-friendly paragraphs with generous spacing -->
    <section class="space-y-8 text-base leading-7 text-zinc-800 dark:text-zinc-200">
      <!-- Eligibility & Accounts -->
      <div>
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Eligibility & Accounts</h2>
        <p class="mt-2 text-justify">
          Use of the Website requires that users have the legal capacity to form a binding contract under applicable law.
          Account information must be current, accurate, and complete, and users agree to promptly update their
          information as it changes over time. Users are solely responsible for maintaining the confidentiality of login
          credentials and for all activities occurring under their accounts.
        </p>
        <p class="mt-2 text-justify">
          Users must immediately notify us of any unauthorized access or suspected compromise of their accounts so that
          appropriate measures can be taken. We reserve the right to suspend or terminate accounts that violate these
          Terms, applicable laws, or present a security risk to the Website or other users, subject to any rights users
          may have under local law. Reinstatement may be considered at our discretion in line with security and compliance needs.
        </p>
      </div>

      <!-- Acceptable Use -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Acceptable Use</h2>
        <p class="mt-2 text-justify">
          Users agree to use the Website in a lawful, responsible manner and to refrain from activities that could
          disrupt service, harm others, or infringe on rights. Prohibited behavior includes, without limitation,
          distributing malware, attempting unauthorized access, scraping without consent, spamming, harassing other users,
          misrepresenting identity, and violating intellectual property or privacy rights. We may investigate and take
          appropriate action in response to suspected misuse, including access restrictions or account termination.
        </p>
        <p class="mt-2 text-justify">
          Where required by law or to protect the rights and safety of our users and systems, we may cooperate with law
          enforcement or other authorities. Users are responsible for ensuring their devices, networks, and software used
          to access the Website are adequately secured and compliant with these Terms and all applicable regulations.
        </p>
      </div>

      <!-- Intellectual Property & License -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Intellectual Property & License</h2>
        <p class="mt-2 text-justify">
          The Website and its content—including text, graphics, logos, icons, images, audiovisual materials, software,
          and trademarks—are owned by {{ companyName }} or our licensors and are protected by intellectual property laws.
          Subject to compliance with these Terms, we grant a limited, non‑exclusive, non‑transferable, revocable license
          to access and use the Website for its intended purposes. Any use of the Website or content not expressly
          permitted is prohibited and may violate applicable laws.
        </p>
        <p class="mt-2 text-justify">
          Users must not modify, reverse engineer, decompile, disassemble, copy, distribute, or create derivative works
          from the Website or its content except as permitted by mandatory law. All goodwill arising from permitted use
          of our marks accrues to {{ companyName }}. We do not grant any implied rights; all rights not expressly
          granted are reserved. Third‑party content remains the property of its respective owners and is used with
          permission or under applicable licenses.
        </p>
      </div>

      <!-- User Content -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">User Content</h2>
        <p class="mt-2 text-justify">
          Users may submit content such as text, images, or other materials subject to these Terms and applicable law.
          By submitting content, users grant us a worldwide, non‑exclusive, royalty‑free license to host, store,
          reproduce, display, and process such content solely as necessary to operate, maintain, and improve the Website
          and services. Users represent and warrant they have the necessary rights to submit their content and that it
          does not infringe any third‑party rights or violate any law.
        </p>
        <p class="mt-2 text-justify">
          We may moderate or remove content that, in our reasonable judgment, violates these Terms or applicable law.
          We are not obligated to monitor all content and do not assume responsibility for user submissions. Users remain
          fully responsible for the content they provide and agree to indemnify us against claims arising from that content.
        </p>
      </div>

      <!-- Third-Party Links & Services -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Third‑Party Links & Services</h2>
        <p class="mt-2 text-justify">
          The Website may reference or link to third‑party websites, products, or services for convenience. We do not
          control and are not responsible for third‑party content, policies, or practices. Accessing third‑party services
          is at the user’s sole risk and may be subject to separate terms and privacy policies. Users should review
          applicable third‑party terms before engaging with such services.
        </p>
        <p class="mt-2 text-justify">
          Where the Website integrates third‑party features, the functionality and availability of such features may be
          controlled by those providers. We may add, modify, or discontinue integrations at any time to maintain
          compatibility, security, or compliance with third‑party or regulatory requirements.
        </p>
      </div>

      <!-- Purchases, Payments & Taxes -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Purchases, Payments & Taxes</h2>
        <p class="mt-2 text-justify">
          If the Website offers paid features, users agree to pay all fees, charges, and applicable taxes in accordance
          with the displayed pricing and payment terms. Prices, features, and billing cycles may change with appropriate
          notice as required by law. Unless otherwise stated, fees are non‑refundable to the fullest extent permitted by law.
        </p>
        <p class="mt-2 text-justify">
          Users authorize us and our payment processors to charge the chosen payment method for purchases and renewals,
          where applicable. Users are responsible for maintaining accurate billing information and for any charges
          incurred under their account. Any specific refund or cancellation rights will be governed by our posted
          policies or by mandatory law.
        </p>
      </div>

      <!-- Privacy -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Privacy</h2>
        <p class="mt-2 text-justify">
          Use of the Website is also governed by our Privacy Policy, which explains how we collect, use, disclose, and
          safeguard personal data. By using the Website, users acknowledge that they have reviewed our Privacy Policy and
          consent to our data practices as described therein, subject to any rights available under applicable data
          protection laws.
        </p>
        <p class="mt-2 text-justify">
          We implement appropriate technical and organizational measures intended to protect personal data, but no
          system is fully secure. Users should take steps to protect their own information, including using strong
          passwords, enabling available security features, and promptly notifying us of any suspected compromise.
        </p>
      </div>

      <!-- Disclaimer of Warranties -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Disclaimer of Warranties</h2>
        <p class="mt-2 text-justify">
          The Website and all related services are provided on an “as is” and “as available” basis, without warranties
          of any kind, whether express, implied, or statutory. To the maximum extent permitted by law, we disclaim all
          implied warranties, including merchantability, fitness for a particular purpose, non‑infringement, and
          uninterrupted or error‑free operation. Users assume all risk for their use of the Website.
        </p>
        <p class="mt-2 text-justify">
          Some jurisdictions do not allow limitations on implied warranties, so parts of this disclaimer may not apply
          to certain users. Where required by law, users may have specific legal rights, and these Terms are not intended
          to restrict such non‑waivable rights.
        </p>
      </div>

      <!-- Limitation of Liability -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Limitation of Liability</h2>
        <p class="mt-2 text-justify">
          To the maximum extent permitted by law, {{ companyName }} will not be liable for indirect, incidental,
          consequential, special, exemplary, or punitive damages; loss of profits, revenues, goodwill, or data; or
          business interruption arising from or related to use of or inability to use the Website, even if advised of the
          possibility of such damages. Our total liability for any claim relating to the Website shall not exceed the
          greater of (a) amounts paid by the user for the service in the twelve (12) months preceding the claim or
          (b) one hundred (100) USD (or equivalent).
        </p>
        <p class="mt-2 text-justify">
          Some jurisdictions do not allow exclusion or limitation of certain damages, so the above limitations may not
          fully apply to certain users. In such cases, liability will be limited to the maximum extent permitted by applicable law.
        </p>
      </div>

      <!-- Indemnification -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Indemnification</h2>
        <p class="mt-2 text-justify">
          Users agree to defend, indemnify, and hold harmless {{ companyName }}, its affiliates, officers, directors,
          employees, and agents from and against any claims, liabilities, damages, losses, and expenses (including
          reasonable attorneys’ fees) arising out of or related to user content, misuse of the Website, violation of
          these Terms, or infringement of third‑party rights. We reserve the right to assume the exclusive defense and
          control of any matter subject to indemnification, in which case users will cooperate with us in asserting defenses.
        </p>
        <p class="mt-2 text-justify">
          This indemnity will not apply to the extent a claim results from our willful misconduct or where prohibited by law.
          Users may not settle any indemnified claim without our prior written consent if the settlement imposes obligations on or
          requires an admission of liability by {{ companyName }}.
        </p>
      </div>

      <!-- Suspension & Termination -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Suspension & Termination</h2>
        <p class="mt-2 text-justify">
          We may suspend or terminate access to the Website at any time where reasonably necessary to address security
          risks, legal compliance, or material violations of these Terms. Where required by law, we will provide advance
          notice; otherwise, action may be immediate to protect the Website and users. Upon termination, rights granted
          to users under these Terms will cease, and users must stop using the Website.
        </p>
        <p class="mt-2 text-justify">
          Sections that by nature should survive termination—including intellectual property, disclaimers, limitations of
          liability, indemnity, and governing law—will continue to apply. Termination is without prejudice to any other
          rights or remedies available under these Terms or applicable law.
        </p>
      </div>

      <!-- Governing Law & Disputes -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Governing Law & Dispute Resolution</h2>
        <p class="mt-2 text-justify">
          These Terms are governed by the laws of the jurisdiction where {{ companyName }} is established, excluding its
          conflict‑of‑law principles, unless a different governing law is required by mandatory local consumer
          protection laws. Subject to mandatory law, disputes will be subject to the exclusive jurisdiction and venue of
          the competent courts in that jurisdiction, and users consent to personal jurisdiction there.
        </p>
        <p class="mt-2 text-justify">
          Where permitted, the parties waive participation in class or representative actions. Nothing in this section
          prevents either party from seeking interim injunctive relief in any competent court to protect rights pending a
          final determination on the merits.
        </p>
      </div>

      <!-- Changes to Terms -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Changes to These Terms</h2>
        <p class="mt-2 text-justify">
          We may update these Terms from time to time to reflect operational, legal, or regulatory changes. Material
          updates will be effective upon posting or as otherwise stated at the time of publication. Continued use of the
          Website after changes become effective constitutes acceptance of the updated Terms. Users should review this page periodically.
        </p>
        <p class="mt-2 text-justify">
          If a user does not agree to the updated Terms, they should discontinue use of the Website. Certain updates may
          require additional consent where mandated by applicable law, and we will obtain such consent as required.
        </p>
      </div>

      <!-- General Provisions -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">General Provisions</h2>
        <p class="mt-2 text-justify">
          If any provision of these Terms is held invalid or unenforceable, the remaining provisions will remain in full
          force and effect. Failure to enforce any provision is not a waiver of the right to enforce it later. Users may
          not assign or transfer their rights or obligations under these Terms without our prior written consent; any
          attempted assignment in violation of the foregoing is void. We may assign these Terms in connection with a
          merger, acquisition, or sale of assets.
        </p>
        <p class="mt-2 text-justify">
          These Terms, together with referenced policies (including our Privacy Policy), constitute the entire agreement
          between the parties regarding the Website and supersede prior or contemporaneous agreements on the subject
          matter. Headings are for convenience only and do not affect interpretation.
        </p>
      </div>

      <!-- Contact -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Contact</h2>
        <p class="mt-2">
          Questions or concerns regarding these Terms can be directed to:
        </p>
        <p class="mt-2">
          <span class="font-semibold">{{ companyName }}</span><br>
          {{ address }}<br>
          Email: <a class="text-indigo-600 hover:underline" :href="`mailto:${supportEmail}`">{{ supportEmail }}</a><br>
          Phone: <span class="tabular-nums">{{ phoneNumber }}</span>
        </p>
      </div>
    </section>
  </main>
</template>

<script setup lang="ts">
const config = useRuntimeConfig()
const websiteName = config.public.websiteName
const companyName = config.public.companyName
const supportEmail = config.public.supportEmail
const phoneNumber = config.public.phoneNumber
const address = config.public.address

// Centralize the effective date for easy maintenance or drive via CMS/config
const effectiveDate = 'July 20, 2025'
</script>
