<script setup lang="ts">
definePageMeta({
  layout: 'default'
})

const config = useRuntimeConfig()
</script>

<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-amber-500 to-orange-600 py-12">
      <UContainer>
        <div class="text-center text-white">
          <h1 class="text-3xl md:text-4xl font-bold mb-2">
            Return & Refund Policy
          </h1>
          <p class="text-amber-100">
            Easy returns, hassle-free refunds
          </p>
        </div>
      </UContainer>
    </div>

    <UContainer class="py-12">
      <div class="glass-card p-8 max-w-4xl mx-auto">
        <div class="prose dark:prose-invert max-w-none">
          <h2>1. Return Policy Overview</h2>
          <p>
            We want you to be completely satisfied with your purchase. If you're not happy with your order, you can return eligible products within the return window.
          </p>

          <h2>2. Return Eligibility</h2>
          <h3>Returnable Items</h3>
          <ul>
            <li>Products in original, unused condition</li>
            <li>Items with original packaging and tags</li>
            <li>Products returned within 7 days of delivery</li>
          </ul>

          <h3>Non-Returnable Items</h3>
          <ul>
            <li>Personal care and hygiene products</li>
            <li>Perishable goods and food items</li>
            <li>Digital products and downloadable items</li>
            <li>Customized or personalized products</li>
            <li>Products marked as non-returnable</li>
          </ul>

          <h2>3. Return Process</h2>
          <div class="not-prose">
            <div class="grid md:grid-cols-4 gap-4 my-6">
              <div class="text-center p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center mx-auto mb-3 font-bold">
                  1
                </div>
                <p class="font-medium text-slate-900 dark:text-white">
                  Initiate Return
                </p>
                <p class="text-sm text-slate-500">
                  Go to Orders &gt; Return
                </p>
              </div>
              <div class="text-center p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center mx-auto mb-3 font-bold">
                  2
                </div>
                <p class="font-medium text-slate-900 dark:text-white">
                  Select Reason
                </p>
                <p class="text-sm text-slate-500">
                  Choose return reason
                </p>
              </div>
              <div class="text-center p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center mx-auto mb-3 font-bold">
                  3
                </div>
                <p class="font-medium text-slate-900 dark:text-white">
                  Schedule Pickup
                </p>
                <p class="text-sm text-slate-500">
                  Choose pickup date
                </p>
              </div>
              <div class="text-center p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center mx-auto mb-3 font-bold">
                  4
                </div>
                <p class="font-medium text-slate-900 dark:text-white">
                  Get Refund
                </p>
                <p class="text-sm text-slate-500">
                  Receive your refund
                </p>
              </div>
            </div>
          </div>

          <h2>4. Refund Options</h2>
          <div class="not-prose">
            <table class="w-full border-collapse border border-slate-200 dark:border-slate-700 mb-6">
              <thead>
                <tr class="bg-slate-100 dark:bg-slate-800">
                  <th class="border border-slate-200 dark:border-slate-700 p-3 text-left">
                    Refund Method
                  </th>
                  <th class="border border-slate-200 dark:border-slate-700 p-3 text-left">
                    Processing Time
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="border border-slate-200 dark:border-slate-700 p-3">
                    Wallet Credit
                  </td>
                  <td class="border border-slate-200 dark:border-slate-700 p-3">
                    Instant (upon pickup)
                  </td>
                </tr>
                <tr>
                  <td class="border border-slate-200 dark:border-slate-700 p-3">
                    Original Payment Method
                  </td>
                  <td class="border border-slate-200 dark:border-slate-700 p-3">
                    5-7 business days
                  </td>
                </tr>
                <tr>
                  <td class="border border-slate-200 dark:border-slate-700 p-3">
                    Bank Transfer
                  </td>
                  <td class="border border-slate-200 dark:border-slate-700 p-3">
                    3-5 business days
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <h2>5. Refund Amount</h2>
          <ul>
            <li>Full product price will be refunded for eligible returns</li>
            <li>Shipping charges are non-refundable unless the return is due to our error</li>
            <li>Any discounts or promotions will be adjusted in the refund</li>
          </ul>

          <h2>6. Damaged or Defective Products</h2>
          <p>If you receive a damaged or defective product:</p>
          <ul>
            <li>Report within 48 hours of delivery with photos</li>
            <li>Full refund including shipping charges</li>
            <li>Free return pickup</li>
            <li>Option to receive replacement instead of refund</li>
          </ul>

          <h2>7. Wrong Product Delivered</h2>
          <p>
            If you receive a wrong product, we will arrange a free pickup and send the correct product or provide a full refund at your choice.
          </p>

          <h2>8. Cancellation Policy</h2>
          <ul>
            <li>Orders can be cancelled before shipping</li>
            <li>Full refund for cancelled orders</li>
            <li>No cancellation charges</li>
          </ul>

          <h2>9. Contact Us</h2>
          <p>For return and refund queries:</p>
          <ul>
            <li>Email: {{ config.public.supportEmail }}</li>
            <li>Phone: {{ config.public.supportPhone }}</li>
          </ul>
        </div>
      </div>
    </UContainer>
  </div>
</template>
