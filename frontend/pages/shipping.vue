<template>
  <main class="mx-auto max-w-7xl px-4 py-16">
    <!-- Header -->
    <header class="mb-10">
      <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
        Shipping Policy
      </h1>
      <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
        Effective Date:
        <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ effectiveDate }}</span>
      </p>
      <p class="mt-5 max-w-4xl text-base leading-7 text-zinc-700 dark:text-zinc-300">
        This Shipping Policy explains how orders are processed, shipped, and delivered for <strong class="font-semibold">{{ websiteName }}</strong>,
        operated by {{ companyName }}. It applies to domestic and, where offered, international deliveries. For returns and refunds,
        please see the separate policy linked from our help center or footer. [2][4]
      </p>
    </header>

    <!-- Content -->
    <section class="space-y-8 text-base leading-7 text-zinc-800 dark:text-zinc-200">
      <!-- Processing Time -->
      <div>
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Order Processing Time</h2>
        <p class="mt-2">
          Orders are typically processed within 1–2 business days, excluding weekends and public holidays. During peak periods,
          processing may take an additional 1–3 business days. Orders placed after our daily cut‑off may begin processing the
          following business day. [2][4]
        </p>
      </div>

      <!-- Delivery Estimates & Methods -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Shipping Methods & Delivery Estimates</h2>
        <p class="mt-2">
          Estimated delivery time is calculated from dispatch, not from the time of order placement. Standard delivery generally
          arrives within 3–7 business days domestically; expedited options (where available) may arrive within 1–3 business days.
          International delivery timelines vary by destination, customs, and carrier routes. These estimates are not guarantees. [1][4]
        </p>
        <p class="mt-2">
          Carrier selection is based on serviceability to the delivery address and may include regional partners. Transit times can
          be affected by circumstances beyond our control (e.g., weather, carrier delays, strikes, customs inspections). [4]
        </p>
      </div>

      <!-- Shipping Costs & Thresholds -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Shipping Costs & Free Shipping</h2>
        <p class="mt-2">
          Shipping fees are calculated at checkout based on weight, dimensions, destination, and selected service level. From time to
          time, we may offer free or discounted shipping subject to minimum cart value or promotional terms, which will be displayed
          on eligible products or during checkout. [4][3]
        </p>
      </div>

      <!-- Regions & Restrictions -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Shipping Regions & Restrictions</h2>
        <p class="mt-2">
          We currently ship to serviceable domestic locations and selected international destinations. Certain products may be
          restricted by law, carrier rules, or safety guidelines (e.g., hazardous goods), limiting available methods or regions.
          We do not ship to unsupported P.O. boxes or military addresses unless explicitly stated at checkout. [2][4]
        </p>
      </div>

      <!-- Address Accuracy -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Address Accuracy</h2>
        <p class="mt-2">
          Please ensure delivery details are complete and accurate, including apartment, building, landmark, or entry instructions.
          We are not responsible for delays or non‑delivery due to incorrect or incomplete addresses provided during checkout. [4]
        </p>
      </div>

      <!-- Taxes, Duties & Customs -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Taxes, Duties & Customs (International)</h2>
        <p class="mt-2">
          International orders may be subject to import duties, taxes, and fees levied by the destination country. Unless stated
          otherwise at checkout, these charges are the recipient’s responsibility and are not included in product or shipping
          prices. Customs processing may extend delivery timelines. [2][6]
        </p>
      </div>

      <!-- Shipment Confirmation & Tracking -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Shipment Confirmation & Tracking</h2>
        <p class="mt-2">
          Once an order ships, a confirmation email or message will include carrier information and, where available, a tracking
          number. Tracking visibility may begin after the carrier scans the parcel at its first processing facility. [2][4]
        </p>
      </div>

      <!-- Failed Delivery & Reattempts -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Failed Delivery & Reattempts</h2>
        <p class="mt-2">
          If delivery fails due to recipient unavailability, address issues, or refusal, carriers may attempt redelivery or route the
          package to a local pickup point. If the package is returned to sender, we will contact the customer with next steps; additional
          shipping fees may apply for reshipment. [4][6]
        </p>
      </div>

      <!-- Lost, Missing, or Damaged Packages -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Lost, Missing, or Damaged Packages</h2>
        <p class="mt-2">
          If an order is marked delivered but cannot be located, please check with household members, neighbors, security, and the
          carrier. Report missing or damaged items to our support team within 7 days of the stated delivery date, including photos for
          damage claims. We will investigate with the carrier and provide a resolution per our policy (replacement, reshipment, or
          refund), subject to verification. [1][8][6]
        </p>
      </div>

      <!-- Split Shipments -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Partial Fulfillment & Split Shipments</h2>
        <p class="mt-2">
          To expedite delivery, items in a single order may ship separately as they become available or from different facilities.
          Each shipment will have a separate tracking update where available. Shipping fees, if applicable, are charged once per order
          unless otherwise stated. [2]
        </p>
      </div>

      <!-- Returns & Exchanges link -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Returns & Exchanges</h2>
        <p class="mt-2">
          Shipping for returns or exchanges may be the customer’s responsibility unless stated otherwise. Please refer to our Returns
          & Refunds Policy for eligibility windows, conditions, and the return process. Where applicable, return labels or drop‑off
          options will be provided after return authorization. [5][4]
        </p>
      </div>

      <!-- Policy Changes -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Policy Updates</h2>
        <p class="mt-2">
          We may update this Shipping Policy as our operations evolve or as laws and carrier terms change. Material updates take
          effect upon posting unless stated otherwise. Customers are encouraged to review this page periodically for the latest terms. [5][1]
        </p>
      </div>

      <!-- Contact -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Contact</h2>
        <p class="mt-2">
          For shipping‑related questions or support, contact:
        </p>
        <p class="mt-1">
          <span class="font-semibold">{{ companyName }}</span><br />
          {{ address }}<br />
          Email: <a class="text-indigo-600 hover:underline" :href="`mailto:${supportEmail}`">{{ supportEmail }}</a><br />
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
const effectiveDate = 'July 20, 2025'
</script>
