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
            Privacy Policy
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
          <h2>1. Introduction</h2>
          <p>
            {{ companyName }} ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our services.
          </p>

          <h2>2. Information We Collect</h2>
          <h3>Personal Information</h3>
          <p>We may collect the following personal information:</p>
          <ul>
            <li>Name, email address, and contact information</li>
            <li>Phone number and address</li>
            <li>Payment and billing information</li>
            <li>KYC documents (Aadhaar, PAN for Indian users)</li>
            <li>Account credentials</li>
          </ul>

          <h3>Automatically Collected Information</h3>
          <p>When you access our services, we automatically collect:</p>
          <ul>
            <li>Device information (type, operating system, browser)</li>
            <li>IP address and location data</li>
            <li>Usage patterns and preferences</li>
            <li>Cookies and similar tracking technologies</li>
          </ul>

          <h2>3. How We Use Your Information</h2>
          <p>We use your information to:</p>
          <ul>
            <li>Provide and maintain our services</li>
            <li>Process transactions and send notifications</li>
            <li>Verify your identity and prevent fraud</li>
            <li>Communicate with you about products, services, and promotions</li>
            <li>Improve our services and develop new features</li>
            <li>Comply with legal obligations</li>
          </ul>

          <h2>4. Information Sharing</h2>
          <p>We may share your information with:</p>
          <ul>
            <li><strong>Service Providers:</strong> Third parties who assist in operating our platform</li>
            <li><strong>Payment Processors:</strong> To process your transactions securely</li>
            <li><strong>Legal Requirements:</strong> When required by law or to protect our rights</li>
            <li><strong>Business Transfers:</strong> In connection with mergers or acquisitions</li>
          </ul>
          <p>We do not sell your personal information to third parties.</p>

          <h2>5. Data Security</h2>
          <p>
            We implement appropriate technical and organizational measures to protect your personal information, including:
          </p>
          <ul>
            <li>Encryption of sensitive data</li>
            <li>Secure servers and firewalls</li>
            <li>Regular security assessments</li>
            <li>Access controls and authentication</li>
          </ul>

          <h2>6. Your Rights</h2>
          <p>You have the right to:</p>
          <ul>
            <li>Access your personal information</li>
            <li>Correct inaccurate data</li>
            <li>Request deletion of your data</li>
            <li>Opt-out of marketing communications</li>
            <li>Data portability</li>
          </ul>

          <h2>7. Cookies</h2>
          <p>
            We use cookies and similar technologies to enhance your experience. You can control cookie settings through your browser. Essential cookies are required for the platform to function properly.
          </p>

          <h2>8. Third-Party Links</h2>
          <p>
            Our services may contain links to third-party websites. We are not responsible for the privacy practices of these external sites.
          </p>

          <h2>9. Children's Privacy</h2>
          <p>
            Our services are not intended for individuals under 18 years of age. We do not knowingly collect personal information from children.
          </p>

          <h2>10. Changes to This Policy</h2>
          <p>
            We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last updated" date.
          </p>

          <h2>11. Contact Us</h2>
          <p>
            If you have questions about this Privacy Policy, please contact us at:
          </p>
          <ul>
            <li>Email: {{ config.public.supportEmail }}</li>
            <li>Phone: {{ config.public.supportPhone }}</li>
          </ul>
        </div>
      </div>
    </UContainer>
  </div>
</template>
