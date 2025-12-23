<script setup lang="ts">
definePageMeta({
  layout: 'default'
})

const config = useRuntimeConfig()

interface FaqItem {
  question: string
  answer: string
}

interface FaqCategory {
  title: string
  icon: string
  items: FaqItem[]
}

const faqCategories: FaqCategory[] = [
  {
    title: 'Account & Registration',
    icon: 'i-lucide-user-circle',
    items: [
      {
        question: 'How do I create an account?',
        answer: 'Click "Sign Up" and register using your mobile number. You\'ll receive an OTP to verify your account. Once verified, you can set your password and complete your profile.'
      },
      {
        question: 'I forgot my password. What should I do?',
        answer: 'Click "Forgot Password" on the login page. Enter your registered mobile number or email to receive a password reset link.'
      },
      {
        question: 'How do I update my profile information?',
        answer: 'Go to Profile > Edit Profile to update your name, email, address, and other details. Some changes may require OTP verification.'
      }
    ]
  },
  {
    title: 'Orders & Shopping',
    icon: 'i-lucide-shopping-bag',
    items: [
      {
        question: 'How can I track my order?',
        answer: 'Go to Orders in your dashboard to see all your orders and their current status. Click on any order to view detailed tracking information.'
      },
      {
        question: 'Can I cancel my order?',
        answer: 'Yes, orders can be cancelled before they are shipped. Go to Orders, select the order you want to cancel, and click "Cancel Order".'
      },
      {
        question: 'What payment methods do you accept?',
        answer: 'We accept UPI, Credit/Debit Cards, Net Banking, and Wallet payments. Cash on Delivery is available for select pin codes.'
      }
    ]
  },
  {
    title: 'Membership & Network',
    icon: 'i-lucide-crown',
    items: [
      {
        question: 'What are the membership benefits?',
        answer: 'Members get exclusive discounts, earn commissions on purchases, access to the referral program, and priority customer support.'
      },
      {
        question: 'How does the referral program work?',
        answer: 'Share your unique referral code with friends. When they sign up and make purchases, you earn commissions based on your membership level.'
      },
      {
        question: 'How do I check my earnings?',
        answer: 'Go to Earnings in your dashboard to view your commission history, pending payouts, and total earnings.'
      }
    ]
  },
  {
    title: 'Wallet & Withdrawals',
    icon: 'i-lucide-wallet',
    items: [
      {
        question: 'How do I withdraw money from my wallet?',
        answer: 'Go to Wallet > Withdraw, enter the amount, select your bank account, and enter your PIN. Withdrawals are processed within 2-3 business days.'
      },
      {
        question: 'What is the minimum withdrawal amount?',
        answer: 'The minimum withdrawal amount is Rs. 100. Ensure your KYC is verified before initiating withdrawals.'
      },
      {
        question: 'How do I set up my wallet PIN?',
        answer: 'When accessing wallet features for the first time, you\'ll be prompted to create a 6-digit PIN. This PIN is required for all financial transactions.'
      }
    ]
  }
]

const activeCategory = ref(0)
</script>

<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-green-600 to-teal-600 py-12">
      <UContainer>
        <div class="text-center text-white">
          <h1 class="text-3xl md:text-4xl font-bold mb-2">
            Help Center
          </h1>
          <p class="text-green-100 mb-6">
            Find answers to commonly asked questions
          </p>

          <!-- Search Box -->
          <div class="max-w-xl mx-auto">
            <UInput
              placeholder="Search for help..."
              size="lg"
              icon="i-lucide-search"
              class="bg-white/20 backdrop-blur"
            />
          </div>
        </div>
      </UContainer>
    </div>

    <UContainer class="py-12">
      <!-- Quick Links -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
        <NuxtLink
          to="/contact"
          class="glass-card p-4 text-center hover:shadow-lg transition-all group"
        >
          <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
            <UIcon
              name="i-lucide-message-circle"
              class="w-6 h-6 text-blue-600 dark:text-blue-400"
            />
          </div>
          <p class="font-medium text-slate-900 dark:text-white">
            Contact Us
          </p>
        </NuxtLink>

        <NuxtLink
          to="/shipping"
          class="glass-card p-4 text-center hover:shadow-lg transition-all group"
        >
          <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
            <UIcon
              name="i-lucide-truck"
              class="w-6 h-6 text-green-600 dark:text-green-400"
            />
          </div>
          <p class="font-medium text-slate-900 dark:text-white">
            Shipping Info
          </p>
        </NuxtLink>

        <NuxtLink
          to="/return-refund"
          class="glass-card p-4 text-center hover:shadow-lg transition-all group"
        >
          <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
            <UIcon
              name="i-lucide-rotate-ccw"
              class="w-6 h-6 text-amber-600 dark:text-amber-400"
            />
          </div>
          <p class="font-medium text-slate-900 dark:text-white">
            Returns & Refunds
          </p>
        </NuxtLink>

        <NuxtLink
          to="/privacy"
          class="glass-card p-4 text-center hover:shadow-lg transition-all group"
        >
          <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
            <UIcon
              name="i-lucide-shield"
              class="w-6 h-6 text-purple-600 dark:text-purple-400"
            />
          </div>
          <p class="font-medium text-slate-900 dark:text-white">
            Privacy Policy
          </p>
        </NuxtLink>
      </div>

      <!-- FAQ Section -->
      <div class="grid lg:grid-cols-4 gap-8">
        <!-- Category Navigation -->
        <div class="lg:col-span-1">
          <div class="glass-card p-4 sticky top-4">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">
              Categories
            </h3>
            <nav class="space-y-2">
              <button
                v-for="(category, index) in faqCategories"
                :key="category.title"
                :class="[
                  'w-full flex items-center gap-3 p-3 rounded-xl transition-all text-left',
                  activeCategory === index
                    ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400'
                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'
                ]"
                @click="activeCategory = index"
              >
                <UIcon
                  :name="category.icon"
                  class="w-5 h-5"
                />
                <span class="font-medium">{{ category.title }}</span>
              </button>
            </nav>
          </div>
        </div>

        <!-- FAQ Content -->
        <div class="lg:col-span-3">
          <div class="glass-card p-6">
            <div class="flex items-center gap-3 mb-6">
              <UIcon
                :name="faqCategories[activeCategory].icon"
                class="w-6 h-6 text-primary-600 dark:text-primary-400"
              />
              <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                {{ faqCategories[activeCategory].title }}
              </h2>
            </div>

            <UAccordion
              :items="faqCategories[activeCategory].items.map((item, i) => ({
                label: item.question,
                slot: `item-${i}`,
                defaultOpen: i === 0
              }))"
            >
              <template
                v-for="(item, i) in faqCategories[activeCategory].items"
                :key="i"
                #[`item-${i}`]
              >
                <p class="text-slate-600 dark:text-slate-400">
                  {{ item.answer }}
                </p>
              </template>
            </UAccordion>
          </div>
        </div>
      </div>

      <!-- Contact CTA -->
      <div class="mt-12 glass-card p-8 text-center bg-gradient-to-r from-primary-50 to-secondary-50 dark:from-primary-900/20 dark:to-secondary-900/20">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
          Still need help?
        </h3>
        <p class="text-slate-600 dark:text-slate-400 mb-6">
          Our support team is available to assist you
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <NuxtLink to="/contact">
            <UButton
              color="primary"
              size="lg"
            >
              <UIcon
                name="i-lucide-mail"
                class="w-4 h-4 mr-2"
              />
              Contact Support
            </UButton>
          </NuxtLink>
          <a :href="`tel:${config.public.supportPhone}`">
            <UButton
              variant="outline"
              color="primary"
              size="lg"
            >
              <UIcon
                name="i-lucide-phone"
                class="w-4 h-4 mr-2"
              />
              {{ config.public.supportPhone }}
            </UButton>
          </a>
        </div>
      </div>
    </UContainer>
  </div>
</template>
