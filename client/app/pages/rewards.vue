<script setup lang="ts">
/**
 * Rewards Program Policy Page
 * Explains the loyalty points system (Rewards) for users and payment gateways.
 */

definePageMeta({
  layout: 'public'
})

useSeoMeta({
  title: 'Rewards Program - VRIDDHI VIKASH',
  description: 'Learn about our loyalty rewards program, how to earn reward points and redeem them for exclusive benefits.'
})

const faqs = [
  {
    label: 'What is the VRIDDHI VIKASH Rewards Program?',
    content: 'Our Rewards Program is a standard e-commerce loyalty initiative. Customers earn Reward Points (formerly referred to as Coins) based on their shopping activity, which can be used to get discounts on future purchases.'
  },
  {
    label: 'What are the primary use cases for Reward Points?',
    content: '1. Shopping Discounts: Use points to pay for a portion of your order during checkout. 2. Exclusive Deals: Access special products or sales available only through points. 3. Community Engagement: Earn points by participating in platform activities and successful referrals.'
  },
  {
    label: 'How are points earned on purchases?',
    content: 'Eligible products in our catalog are marked with a specific number of Reward Points. When you complete a purchase of these items, the points are credited to your rewards wallet after the return period expires.'
  },
  {
    label: 'Can I earn points by referring friends?',
    content: 'Yes. Our community-driven growth model allows you to earn bonus reward points when a friend you referred completes their first successful purchase. This is a one-time referral incentive to encourage community sharing.'
  },
  {
    label: 'Is there a limit to how many points I can use?',
    content: 'Redemption limits may apply based on the product category or current promotional offers. You can see the maximum points applicable for an order on the checkout page.'
  },
  {
    label: 'Is this an investment program or financial scheme?',
    content: 'Absolutely not. This is a standard loyalty rewards program. There are no joining fees, no mandatory investments, and no guaranteed returns. Points have no monetary value outside our platform and cannot be withdrawn as cash. It is strictly a customer retention and loyalty program similar to those offered by major global e-commerce retailers.'
  }
]
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-12 md:py-20">
    <UContainer>
      <!-- Header -->
      <div class="max-w-3xl mx-auto text-center mb-16">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 rounded-full text-sm font-bold mb-6">
          <UIcon name="i-lucide-sparkles" class="w-4 h-4" />
          Customer Loyalty Program
        </div>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-slate-900 dark:text-white mb-6 leading-tight">
          VRIDDHI VIKASH <br>
          <span class="text-primary-600 dark:text-primary-400">Rewards & Benefits</span>
        </h1>
        <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
          We value our community. Our rewards system is designed to provide real value to our frequent shoppers through a transparent and easy-to-use points program.
        </p>
      </div>

      <!-- Use Cases Section -->
      <div class="mb-20">
        <h2 class="text-2xl font-bold text-center mb-10 text-slate-900 dark:text-white">How Reward Points Work</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center mb-6">
              <UIcon name="i-lucide-shopping-bag" class="w-6 h-6" />
            </div>
            <h3 class="text-xl font-bold mb-3">1. Earn on Shop</h3>
            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
              Earn points automatically on every eligible purchase. The more you shop, the more you accumulate.
            </p>
          </div>

          <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center mb-6">
              <UIcon name="i-lucide-users" class="w-6 h-6" />
            </div>
            <h3 class="text-xl font-bold mb-3">2. Community Referral</h3>
            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
              Invite friends to join VRIDDHI VIKASH. Receive bonus points when they make their first successful purchase.
            </p>
          </div>

          <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-2xl flex items-center justify-center mb-6">
              <UIcon name="i-lucide-zap" class="w-6 h-6" />
            </div>
            <h3 class="text-xl font-bold mb-3">3. Direct Discounts</h3>
            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
              Redeem your points directly at checkout for instant discounts. No complicated conversion needed.
            </p>
          </div>
        </div>
      </div>

      <!-- FAQ Section -->
      <div class="max-w-3xl mx-auto mb-20">
        <h2 class="text-3xl font-black text-center mb-10">Frequently Asked Questions</h2>
        <div class="space-y-4">
          <UAccordion
            :items="faqs"
            variant="soft"
            size="lg"
            :ui="{ 
              wrapper: 'flex flex-col gap-4',
              item: {
                base: 'bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden px-4 py-2 shadow-sm transition-all',
                content: 'text-slate-600 dark:text-slate-400 text-sm leading-relaxed pb-4'
              }
            }"
          />
        </div>
      </div>

      <!-- Footer Policy Links -->
      <div class="max-w-2xl mx-auto text-center">
        <div class="p-8 bg-slate-100 dark:bg-slate-900/50 rounded-3xl border border-dashed border-slate-300 dark:border-slate-700">
          <h4 class="font-bold mb-4">Need more information?</h4>
          <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
            Our rewards program is governed by our standard terms of service. For information on returns and refunds related to rewarded purchases, please visit:
          </p>
          <div class="flex flex-wrap justify-center gap-4">
            <UButton
              to="/return-refund"
              variant="outline"
              color="neutral"
              icon="i-lucide-refresh-cw"
            >
              Return & Refund Policy
            </UButton>
            <UButton
              to="/terms"
              variant="ghost"
              color="neutral"
            >
              Terms of Service
            </UButton>
          </div>
        </div>
      </div>
    </UContainer>
  </div>
</template>
