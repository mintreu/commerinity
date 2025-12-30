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
  title: 'Shipping Policy - Delivery Information',
  description: 'Learn about our shipping methods, delivery times, costs, and tracking options. Fast and reliable delivery.',
})
</script>

<template>
  <div class="policy-page min-h-screen">
    <!-- Hero Section -->
    <div class="policy-hero bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 py-16 md:py-20">
      <UContainer>
        <div class="text-center text-white">
          <div class="policy-badge inline-flex items-center px-5 py-2.5 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 mb-6">
            <UIcon name="i-lucide-truck" class="w-4 h-4 mr-2" />
            <span>Shipping & Delivery</span>
          </div>
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 tracking-tight">
            Shipping Policy
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
      <!-- Quick Info Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10 max-w-4xl mx-auto">
        <div class="policy-quick-card">
          <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center mx-auto mb-3">
            <UIcon name="i-lucide-clock" class="w-6 h-6 text-white" />
          </div>
          <h4>Processing</h4>
          <p>1-2 business days</p>
        </div>
        <div class="policy-quick-card">
          <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center mx-auto mb-3">
            <UIcon name="i-lucide-truck" class="w-6 h-6 text-white" />
          </div>
          <h4>Standard</h4>
          <p>3-7 business days</p>
        </div>
        <div class="policy-quick-card">
          <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-500 rounded-xl flex items-center justify-center mx-auto mb-3">
            <UIcon name="i-lucide-zap" class="w-6 h-6 text-white" />
          </div>
          <h4>Express</h4>
          <p>1-3 business days</p>
        </div>
        <div class="policy-quick-card">
          <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl flex items-center justify-center mx-auto mb-3">
            <UIcon name="i-lucide-gift" class="w-6 h-6 text-white" />
          </div>
          <h4>Free Shipping</h4>
          <p>On qualifying orders</p>
        </div>
      </div>

      <div class="glass-card p-6 md:p-10 lg:p-12 max-w-4xl mx-auto">
        <!-- Introduction -->
        <div class="policy-intro">
          <p>
            This Shipping Policy explains how orders are processed, shipped, and delivered for <strong>{{ websiteName }}</strong>,
            operated by <strong>{{ companyName }}</strong>. It applies to domestic and international deliveries where offered.
          </p>
        </div>

        <div class="policy-content">
          <h2>1. Processing Time</h2>
          <p>
            Orders are typically processed within <strong>1–2 business days</strong>, excluding weekends and public holidays. During peak periods,
            processing may take an additional 1–3 business days. Orders placed after our daily cut-off may begin processing the
            following business day.
          </p>
          <p>
            We'll send you a confirmation email once your order has been processed and is ready for shipment. You can track your order status
            in real-time through your account dashboard or the tracking link provided.
          </p>

          <h2>2. Delivery Methods</h2>
          <p>
            Estimated delivery time is calculated from dispatch, not from the time of order placement. Standard delivery generally
            arrives within <strong>3–7 business days</strong> domestically; expedited options arrive within <strong>1–3 business days</strong>.
          </p>
          <p>
            International delivery timelines vary by destination, customs, and carrier routes. These estimates are not guarantees and may be affected by weather,
            carrier delays, or other circumstances beyond our control.
          </p>

          <h2>3. Shipping Costs</h2>
          <p>
            Shipping fees are calculated at checkout based on weight, dimensions, destination, and selected service level. We offer
            <strong>free or discounted shipping</strong> subject to minimum cart value or promotional terms, displayed during checkout.
          </p>
          <p>
            Take advantage of our free shipping offers by meeting the minimum order requirements. We frequently run promotions
            that make shipping even more affordable for our valued customers.
          </p>

          <h2>4. Regions & Restrictions</h2>
          <p>
            We currently ship to serviceable domestic locations and selected international destinations. Certain products may be
            restricted by law, carrier rules, or safety guidelines, limiting available methods or regions.
          </p>
          <p>
            We do not ship to unsupported P.O. boxes or military addresses unless explicitly stated at checkout. Check our shipping
            calculator during checkout to confirm availability to your location.
          </p>

          <h2>5. Tracking & Support</h2>
          <p>
            Once an order ships, a confirmation email includes carrier information and tracking number where available.
            Tracking visibility begins after the carrier scans the parcel at their first processing facility.
          </p>
          <p>
            If delivery fails due to recipient unavailability or address issues, carriers may attempt redelivery or route
            the package to a local pickup point. Additional shipping fees may apply for reshipment.
          </p>

          <h2>6. Contact Support</h2>
          <p>For shipping-related questions, tracking issues, or delivery concerns, contact us:</p>

          <div class="policy-contact">
            <div class="grid gap-4 sm:grid-cols-2">
              <div class="flex items-start gap-3">
                <div class="icon-box icon-box-sm icon-box-soft-success rounded-lg">
                  <UIcon name="i-lucide-building-2" class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Company</p>
                  <p class="font-semibold text-slate-900 dark:text-white">{{ companyName }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="icon-box icon-box-sm icon-box-soft-success rounded-lg">
                  <UIcon name="i-lucide-mail" class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Email</p>
                  <a :href="`mailto:${supportEmail}`" class="font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">{{ supportEmail }}</a>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="icon-box icon-box-sm icon-box-soft-success rounded-lg">
                  <UIcon name="i-lucide-phone" class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Phone</p>
                  <a :href="`tel:${phoneNumber}`" class="font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">{{ phoneNumber }}</a>
                </div>
              </div>
              <div v-if="address" class="flex items-start gap-3">
                <div class="icon-box icon-box-sm icon-box-soft-success rounded-lg">
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
