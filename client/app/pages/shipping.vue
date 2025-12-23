<script setup lang="ts">
definePageMeta({
  layout: 'default'
})

const config = useRuntimeConfig()
</script>

<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-blue-600 to-cyan-600 py-12">
      <UContainer>
        <div class="text-center text-white">
          <h1 class="text-3xl md:text-4xl font-bold mb-2">
            Shipping Policy
          </h1>
          <p class="text-blue-100">
            Delivery information and shipping guidelines
          </p>
        </div>
      </UContainer>
    </div>

    <UContainer class="py-12">
      <div class="glass-card p-8 max-w-4xl mx-auto">
        <div class="prose dark:prose-invert max-w-none">
          <h2>1. Shipping Coverage</h2>
          <p>
            We currently ship to all serviceable pin codes across India. International shipping is not available at this time.
          </p>

          <h2>2. Shipping Methods & Timeframes</h2>
          <div class="not-prose">
            <table class="w-full border-collapse border border-slate-200 dark:border-slate-700 mb-6">
              <thead>
                <tr class="bg-slate-100 dark:bg-slate-800">
                  <th class="border border-slate-200 dark:border-slate-700 p-3 text-left">
                    Shipping Method
                  </th>
                  <th class="border border-slate-200 dark:border-slate-700 p-3 text-left">
                    Estimated Delivery
                  </th>
                  <th class="border border-slate-200 dark:border-slate-700 p-3 text-left">
                    Cost
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="border border-slate-200 dark:border-slate-700 p-3">
                    Standard Delivery
                  </td>
                  <td class="border border-slate-200 dark:border-slate-700 p-3">
                    5-7 business days
                  </td>
                  <td class="border border-slate-200 dark:border-slate-700 p-3">
                    Free on orders above Rs. 499
                  </td>
                </tr>
                <tr>
                  <td class="border border-slate-200 dark:border-slate-700 p-3">
                    Express Delivery
                  </td>
                  <td class="border border-slate-200 dark:border-slate-700 p-3">
                    2-3 business days
                  </td>
                  <td class="border border-slate-200 dark:border-slate-700 p-3">
                    Rs. 99
                  </td>
                </tr>
                <tr>
                  <td class="border border-slate-200 dark:border-slate-700 p-3">
                    Same-Day Delivery*
                  </td>
                  <td class="border border-slate-200 dark:border-slate-700 p-3">
                    Same day (order before 12 PM)
                  </td>
                  <td class="border border-slate-200 dark:border-slate-700 p-3">
                    Rs. 199
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <p class="text-sm text-slate-500">
            *Same-day delivery available only in select metro cities
          </p>

          <h2>3. Order Processing</h2>
          <ul>
            <li>Orders are processed within 24-48 hours of placement</li>
            <li>Orders placed on weekends/holidays will be processed on the next business day</li>
            <li>You will receive tracking information via SMS and email</li>
          </ul>

          <h2>4. Shipping Charges</h2>
          <ul>
            <li>Free standard shipping on orders above Rs. 499</li>
            <li>Rs. 49 shipping charge for orders below Rs. 499</li>
            <li>Express and same-day delivery charges apply regardless of order value</li>
          </ul>

          <h2>5. Delivery Attempts</h2>
          <ul>
            <li>Our delivery partner will make up to 3 delivery attempts</li>
            <li>If delivery fails after 3 attempts, the order will be returned to us</li>
            <li>A refund will be initiated after deducting shipping charges</li>
          </ul>

          <h2>6. Damaged or Missing Items</h2>
          <p>If you receive a damaged or incorrect item:</p>
          <ul>
            <li>Report within 48 hours of delivery</li>
            <li>Provide photos of the damaged item and packaging</li>
            <li>We will arrange a replacement or refund</li>
          </ul>

          <h2>7. Remote Area Delivery</h2>
          <p>
            For deliveries to remote areas, additional time (2-5 days) may be required. Some pin codes may have limited delivery options.
          </p>

          <h2>8. Order Tracking</h2>
          <p>You can track your order:</p>
          <ul>
            <li>Through your account dashboard</li>
            <li>Using the tracking link sent via SMS/email</li>
            <li>By contacting our support team</li>
          </ul>

          <h2>9. Contact Us</h2>
          <p>For shipping-related queries, reach us at:</p>
          <ul>
            <li>Email: {{ config.public.supportEmail }}</li>
            <li>Phone: {{ config.public.supportPhone }}</li>
          </ul>
        </div>
      </div>
    </UContainer>
  </div>
</template>
