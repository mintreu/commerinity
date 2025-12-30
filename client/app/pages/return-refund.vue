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
  title: 'Return & Refund Policy - Easy Returns Process',
  description: 'Learn about our return eligibility, refund process, and how to initiate returns. Clear policies for hassle-free shopping.',
})
</script>

<template>
  <div class="policy-page min-h-screen">
    <!-- Hero Section -->
    <div class="policy-hero bg-gradient-to-br from-rose-600 via-orange-500 to-amber-500 py-16 md:py-20">
      <UContainer>
        <div class="text-center text-white">
          <div class="policy-badge inline-flex items-center px-5 py-2.5 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 mb-6">
            <UIcon name="i-lucide-package-check" class="w-4 h-4 mr-2" />
            <span>Returns & Refunds</span>
          </div>
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 tracking-tight">
            Return & Refund Policy
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
      <!-- Quick Policy Overview -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10 max-w-4xl mx-auto">
        <div class="policy-quick-card">
          <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-500 rounded-xl flex items-center justify-center mx-auto mb-3">
            <UIcon name="i-lucide-check-circle" class="w-6 h-6 text-white" />
          </div>
          <h4>Product-Based</h4>
          <p>Eligibility varies</p>
        </div>
        <div class="policy-quick-card">
          <div class="w-12 h-12 bg-gradient-to-br from-rose-500 to-red-500 rounded-xl flex items-center justify-center mx-auto mb-3">
            <UIcon name="i-lucide-x-circle" class="w-6 h-6 text-white" />
          </div>
          <h4>No Change of Mind</h4>
          <p>Not accepted</p>
        </div>
        <div class="policy-quick-card">
          <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-500 rounded-xl flex items-center justify-center mx-auto mb-3">
            <UIcon name="i-lucide-wallet" class="w-6 h-6 text-white" />
          </div>
          <h4>Wallet First</h4>
          <p>Refunds to wallet</p>
        </div>
        <div class="policy-quick-card">
          <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center mx-auto mb-3">
            <UIcon name="i-lucide-truck" class="w-6 h-6 text-white" />
          </div>
          <h4>Free Pickup</h4>
          <p>Courier collection</p>
        </div>
      </div>

      <div class="glass-card p-6 md:p-10 lg:p-12 max-w-4xl mx-auto">
        <!-- Introduction -->
        <div class="policy-intro">
          <p>
            This Return & Refund Policy governs return eligibility and refund processing for purchases made on
            <strong>{{ websiteName }}</strong>, operated by <strong>{{ companyName }}</strong>.
            Product-specific eligibility applies as stated on each product page.
          </p>
        </div>

        <div class="policy-content">
          <h2>1. Return Eligibility</h2>
          <p>
            Return and refund eligibility <strong>depends entirely on the product and its type</strong>. Each product page clearly states if
            the item is returnable. Items marked as <strong class="text-rose-600 dark:text-rose-400">"Non-returnable"</strong> are not eligible for return or refund under any
            circumstance, except where a mandatory legal warranty applies.
          </p>
          <p>
            Customers must first receive and accept delivery of the order. Returns are considered only when a logical,
            verifiable fault attributable to {{ companyName }} or the authorized seller is identified (e.g., wrong item
            shipped, manufacturing defect, or missing components as per listing).
          </p>

          <!-- Valid Reasons Highlight -->
          <div class="policy-highlight my-6">
            <h4 class="font-bold text-slate-900 dark:text-white mb-3 flex items-center text-base">
              <UIcon name="i-lucide-check-circle" class="w-5 h-5 mr-2 text-emerald-600 dark:text-emerald-400" />
              Valid Reasons for Return
            </h4>
            <ul class="grid sm:grid-cols-2 gap-2 text-sm text-slate-700 dark:text-slate-300">
              <li class="flex items-center gap-2">
                <UIcon name="i-lucide-check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
                Wrong item shipped
              </li>
              <li class="flex items-center gap-2">
                <UIcon name="i-lucide-check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
                Manufacturing defect
              </li>
              <li class="flex items-center gap-2">
                <UIcon name="i-lucide-check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
                Missing components
              </li>
              <li class="flex items-center gap-2">
                <UIcon name="i-lucide-check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
                Damage during shipping
              </li>
            </ul>
          </div>

          <h2>2. No Change of Mind</h2>
          <p>
            Orders must be placed with a <strong>clear and specific choice</strong> (e.g., size, color, variant). After receiving "Choice A,"
            a desire to replace it with "Choice B" will <strong class="text-rose-600 dark:text-rose-400">not be treated as a valid reason</strong> for return or refund.
          </p>
          <p>
            Preference or change-of-mind requests are not accepted. We encourage customers to carefully review product
            specifications, images, and details before placing their order to ensure satisfaction.
          </p>

          <!-- Not Accepted Highlight -->
          <div class="policy-highlight danger my-6">
            <h4 class="font-bold text-slate-900 dark:text-white mb-3 flex items-center text-base">
              <UIcon name="i-lucide-x-circle" class="w-5 h-5 mr-2 text-rose-600 dark:text-rose-400" />
              Not Accepted as Return Reasons
            </h4>
            <ul class="grid sm:grid-cols-2 gap-2 text-sm text-slate-700 dark:text-slate-300">
              <li class="flex items-center gap-2">
                <UIcon name="i-lucide-x" class="w-4 h-4 text-rose-600 dark:text-rose-400 flex-shrink-0" />
                Size/color preference change
              </li>
              <li class="flex items-center gap-2">
                <UIcon name="i-lucide-x" class="w-4 h-4 text-rose-600 dark:text-rose-400 flex-shrink-0" />
                Changed mind after delivery
              </li>
              <li class="flex items-center gap-2">
                <UIcon name="i-lucide-x" class="w-4 h-4 text-rose-600 dark:text-rose-400 flex-shrink-0" />
                Didn't like the product
              </li>
              <li class="flex items-center gap-2">
                <UIcon name="i-lucide-x" class="w-4 h-4 text-rose-600 dark:text-rose-400 flex-shrink-0" />
                Found better price elsewhere
              </li>
            </ul>
          </div>

          <h2>3. Return Process</h2>
          <p>
            Return logistics are handled by <strong>third-party shipping courier services</strong> appointed or partnered with
            {{ companyName }}. The pickup schedule and method depend on serviceability at the pickup address.
          </p>
          <p>
            If pickup is unavailable, {{ companyName }} may direct a drop-off or an alternate method at its discretion.
            All returned items are inspected for condition and claimed fault before approval or rejection.
          </p>

          <!-- Return Steps -->
          <div class="policy-highlight info my-6">
            <h4 class="font-bold text-slate-900 dark:text-white mb-4 text-base">Return Steps</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="policy-step flex-col items-center text-center p-0">
                <div class="policy-step-number mb-2">1</div>
                <span class="policy-step-content text-sm">Submit return request</span>
              </div>
              <div class="policy-step flex-col items-center text-center p-0">
                <div class="policy-step-number mb-2">2</div>
                <span class="policy-step-content text-sm">Schedule pickup</span>
              </div>
              <div class="policy-step flex-col items-center text-center p-0">
                <div class="policy-step-number mb-2">3</div>
                <span class="policy-step-content text-sm">Quality inspection</span>
              </div>
              <div class="policy-step flex-col items-center text-center p-0">
                <div class="policy-step-number mb-2">4</div>
                <span class="policy-step-content text-sm">Refund processing</span>
              </div>
            </div>
          </div>

          <h2>4. Refund Process</h2>
          <p>
            Upon approval of a return or claim, refunds are <strong>first credited to the customer's {{ websiteName }} wallet</strong>
            (store credit). From the wallet, customers may initiate a withdrawal to a verified bank account.
          </p>
          <p>
            Bank settlement can take up to <strong>15 days</strong> from the date of refund approval/processing,
            subject to bank and payment processor timelines.
          </p>

          <h2>5. Non-Refundable Items</h2>
          <p>
            Certain items and fees are <strong>non-refundable</strong> as per our policy. These include items marked as
            "Non-returnable" on product pages, career application fees, and subscription charges.
          </p>
          <p>
            Orders for which the shipping process has started cannot be cancelled, and changes are not permitted after
            dispatch initiation. Please review your order carefully before confirming.
          </p>

          <!-- Non-Refundable Highlight -->
          <div class="policy-highlight warning my-6">
            <h4 class="font-bold text-slate-900 dark:text-white mb-3 flex items-center text-base">
              <UIcon name="i-lucide-alert-triangle" class="w-5 h-5 mr-2 text-amber-600 dark:text-amber-400" />
              Non-Refundable Items
            </h4>
            <ul class="space-y-2 text-sm text-slate-700 dark:text-slate-300">
              <li>• Products marked "Non-returnable" on product pages</li>
              <li>• Career job application fees</li>
              <li>• Membership subscription charges</li>
              <li>• Orders already shipped or dispatched</li>
            </ul>
          </div>

          <h2>6. Contact Support</h2>
          <p>
            If eligible, submit a return request through your order details page or contact our support team with order ID,
            issue description, and required evidence. We'll guide you through the pickup process.
          </p>

          <div class="policy-contact">
            <div class="grid gap-4 sm:grid-cols-2">
              <div class="flex items-start gap-3">
                <div class="icon-box icon-box-sm icon-box-soft-warning rounded-lg">
                  <UIcon name="i-lucide-building-2" class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Company</p>
                  <p class="font-semibold text-slate-900 dark:text-white">{{ companyName }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="icon-box icon-box-sm icon-box-soft-warning rounded-lg">
                  <UIcon name="i-lucide-mail" class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Email</p>
                  <a :href="`mailto:${supportEmail}`" class="font-semibold text-orange-600 dark:text-orange-400 hover:underline">{{ supportEmail }}</a>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="icon-box icon-box-sm icon-box-soft-warning rounded-lg">
                  <UIcon name="i-lucide-phone" class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Phone</p>
                  <a :href="`tel:${phoneNumber}`" class="font-semibold text-orange-600 dark:text-orange-400 hover:underline">{{ phoneNumber }}</a>
                </div>
              </div>
              <div v-if="address" class="flex items-start gap-3">
                <div class="icon-box icon-box-sm icon-box-soft-warning rounded-lg">
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
