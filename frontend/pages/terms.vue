<template>
  <div class="max-w-3xl mx-auto px-4 py-16 text-gray-800 dark:text-gray-200">
    <h1 class="text-3xl font-bold mb-6">Terms & Conditions</h1>

    <p class="mb-4">Effective Date: July 20, 2025</p>

    <p class="mb-6">
      These Terms and Conditions govern your use of <strong>{{ websiteName }}</strong> and its services.
      By accessing or using our platform, you agree to these terms.
    </p>

    <h2 class="text-xl font-semibold mt-8 mb-4">1. Use of the Website</h2>
    <p class="mb-4">
      You agree to use the platform lawfully and respectfully. Spamming, abuse, or fraudulent behavior is strictly prohibited.
    </p>

    <h2 class="text-xl font-semibold mt-8 mb-4">2. User Accounts</h2>
    <p class="mb-4">
      You are responsible for maintaining the confidentiality of your account credentials. Any activity under your account is your responsibility.
    </p>

    <h2 class="text-xl font-semibold mt-8 mb-4">3. Intellectual Property</h2>
    <p class="mb-4">
      All content, trademarks, and assets on <strong>{{ websiteName }}</strong> are owned by {{ companyName }} or its licensors.
      Unauthorized use or distribution is prohibited.
    </p>

    <h2 class="text-xl font-semibold mt-8 mb-4">4. Limitation of Liability</h2>
    <p class="mb-4">
      {{ companyName }} shall not be liable for any indirect or consequential damages from the use or inability to use the platform.
    </p>

    <h2 class="text-xl font-semibold mt-8 mb-4">5. Changes to Terms</h2>
    <p class="mb-4">
      We may update these terms at any time. Continued use of the service constitutes acceptance of any modifications.
    </p>

    <h2 class="text-xl font-semibold mt-8 mb-4">6. Contact</h2>
    <p>
      If you have any questions or concerns, contact us at:
      <br />
      <a :href="`mailto:${supportEmail}`" class="text-blue-600 hover:underline">{{ supportEmail }}</a>
    </p>
  </div>
</template>

<script setup lang="ts">
const config = useRuntimeConfig()
const websiteName = config.public.websiteName
const companyName = config.public.companyName
const supportEmail = config.public.supportEmail
</script>
