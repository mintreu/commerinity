<script setup lang="ts">
definePageMeta({
  layout: 'default'
})

const config = useRuntimeConfig()
const appName = config.public.appName
const companyName = config.public.companyLegalName
</script>

<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 py-12">
      <UContainer>
        <div class="text-center text-white">
          <h1 class="text-3xl md:text-4xl font-bold mb-2">
            Terms & Conditions
          </h1>
          <p class="text-slate-300">
            Last updated: December 2024
          </p>
        </div>
      </UContainer>
    </div>

    <UContainer class="py-12">
      <div class="glass-card p-8 max-w-4xl mx-auto">
        <div class="prose dark:prose-invert max-w-none">
          <h2>1. Agreement to Terms</h2>
          <p>
            By accessing or using {{ appName }} ("Platform"), you agree to be bound by these Terms and Conditions. If you disagree with any part of these terms, you may not access our services.
          </p>

          <h2>2. User Accounts</h2>
          <h3>Registration</h3>
          <p>To use certain features, you must register for an account. You agree to:</p>
          <ul>
            <li>Provide accurate and complete information</li>
            <li>Maintain the security of your account credentials</li>
            <li>Notify us immediately of any unauthorized access</li>
            <li>Be responsible for all activities under your account</li>
          </ul>

          <h3>Account Termination</h3>
          <p>
            We reserve the right to terminate or suspend your account at our discretion for violations of these terms or any illegal activities.
          </p>

          <h2>3. Membership & Network Marketing</h2>
          <h3>Subscription Plans</h3>
          <p>
            By subscribing to a membership plan, you agree to the specific terms and pricing of that plan. Membership benefits are non-transferable.
          </p>

          <h3>Referral Program</h3>
          <p>When participating in our referral program:</p>
          <ul>
            <li>Commissions are paid according to our published compensation plan</li>
            <li>Earnings depend on actual product sales and team performance</li>
            <li>No income guarantees are made</li>
            <li>Pyramid schemes and recruitment-only models are prohibited</li>
          </ul>

          <h2>4. Products and Orders</h2>
          <h3>Purchases</h3>
          <p>
            All purchases are subject to product availability and pricing accuracy. We reserve the right to refuse or cancel orders in case of pricing errors.
          </p>

          <h3>Payment</h3>
          <p>
            You agree to pay all charges at the prices then in effect. Payment must be made through our approved payment methods.
          </p>

          <h2>5. Wallet & Transactions</h2>
          <p>By using our wallet feature:</p>
          <ul>
            <li>You must maintain adequate security (PIN, security questions)</li>
            <li>Withdrawals are subject to KYC verification</li>
            <li>Minimum withdrawal amounts and processing times apply</li>
            <li>We are not responsible for unauthorized transactions due to compromised credentials</li>
          </ul>

          <h2>6. Prohibited Activities</h2>
          <p>You may not:</p>
          <ul>
            <li>Violate any applicable laws or regulations</li>
            <li>Impersonate others or provide false information</li>
            <li>Engage in fraudulent activities</li>
            <li>Interfere with the platform's operation</li>
            <li>Harvest or collect user data without consent</li>
            <li>Use automated tools to access the platform</li>
          </ul>

          <h2>7. Intellectual Property</h2>
          <p>
            All content on {{ appName }}, including logos, text, graphics, and software, is our property or licensed to us. You may not reproduce, distribute, or create derivative works without our permission.
          </p>

          <h2>8. Limitation of Liability</h2>
          <p>
            To the maximum extent permitted by law, {{ companyName }} shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from your use of our services.
          </p>

          <h2>9. Indemnification</h2>
          <p>
            You agree to indemnify and hold harmless {{ companyName }} from any claims, damages, or expenses arising from your violation of these terms or misuse of our services.
          </p>

          <h2>10. Dispute Resolution</h2>
          <p>
            Any disputes arising from these terms shall be resolved through arbitration in accordance with Indian law. The courts of New Delhi shall have exclusive jurisdiction.
          </p>

          <h2>11. Changes to Terms</h2>
          <p>
            We may modify these terms at any time. Continued use of the platform after changes constitutes acceptance of the modified terms.
          </p>

          <h2>12. Contact</h2>
          <p>For questions about these terms, contact us at:</p>
          <ul>
            <li>Email: {{ config.public.supportEmail }}</li>
            <li>Phone: {{ config.public.supportPhone }}</li>
          </ul>
        </div>
      </div>
    </UContainer>
  </div>
</template>
