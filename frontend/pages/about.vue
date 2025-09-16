<template>
  <main class="mx-auto max-w-7xl px-4 py-16">
    <!-- Hero -->
    <section class="mb-14">
      <div class="max-w-3xl">
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
          About {{ companyName }}
        </h1>
        <p class="mt-5 text-lg leading-8 text-zinc-700 dark:text-zinc-300">
          {{ companyName }} powers trusted commerce and services across communities. With a privacy‑first
          approach and clear, fair policies, {{ companyName }} focuses on delivering value, reliability,
          and respectful experiences for every customer and partner.
        </p>
      </div>
    </section>

    <!-- Mission & Values -->
    <section class="mb-16">
      <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Our Mission & Values</h2>
      <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 bg-white dark:bg-zinc-900">
          <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Customer Respect</h3>
          <p class="mt-2 text-sm leading-6 text-zinc-700 dark:text-zinc-300">
            Clear information, fair policies, and responsive support. We strive to earn trust in every step of the journey.
          </p>
        </div>
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 bg-white dark:bg-zinc-900">
          <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Privacy‑First</h3>
          <p class="mt-2 text-sm leading-6 text-zinc-700 dark:text-zinc-300">
            Data is handled with care: transparent practices, minimal collection, secure storage, and compliant processing.
          </p>
        </div>
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 bg-white dark:bg-zinc-900">
          <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Reliability</h3>
          <p class="mt-2 text-sm leading-6 text-zinc-700 dark:text-zinc-300">
            Robust operations with vetted partners for shipping, payments, and verification to keep promises on time.
          </p>
        </div>
      </div>
    </section>

    <!-- What We Do -->
    <section class="mb-16">
      <div class="flex items-end justify-between gap-4">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">What We Do</h2>
      </div>
      <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 bg-white dark:bg-zinc-900">
          <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Commerce & Fulfillment</h3>
          <p class="mt-2 text-sm leading-6 text-zinc-700 dark:text-zinc-300">
            End‑to‑end order flow with third‑party logistics partners, accurate timelines, and transparent tracking.
          </p>
        </div>
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 bg-white dark:bg-zinc-900">
          <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Secure Payments & Wallet</h3>
          <p class="mt-2 text-sm leading-6 text-zinc-700 dark:text-zinc-300">
            PCI‑aware payment processors, website wallet credits, and bank withdrawals that follow strong security controls.
          </p>
        </div>
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 bg-white dark:bg-zinc-900">
          <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Responsible Policies</h3>
          <p class="mt-2 text-sm leading-6 text-zinc-700 dark:text-zinc-300">
            Clear terms for shipping, returns, and refunds—product‑specific eligibility, no mid‑shipping changes, and fair resolution.
          </p>
        </div>
      </div>
    </section>

    <!-- How We Operate -->
    <section class="mb-16">
      <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">How We Operate</h2>
      <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 bg-white dark:bg-zinc-900">
          <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Shipping with Trusted Partners</h3>
          <p class="mt-2 text-sm leading-6 text-zinc-700 dark:text-zinc-300">
            Delivery is handled by vetted third‑party carriers matched by serviceability and route performance. We provide tracking and clear status updates.
          </p>
        </div>
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 bg-white dark:bg-zinc-900">
          <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Returns & Refunds—Clarity First</h3>
          <p class="mt-2 text-sm leading-6 text-zinc-700 dark:text-zinc-300">
            Return eligibility is defined on product pages. Approved refunds credit to wallet first; bank settlement may take up to 15 days.
          </p>
        </div>
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 bg-white dark:bg-zinc-900">
          <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Verification & Compliance</h3>
          <p class="mt-2 text-sm leading-6 text-zinc-700 dark:text-zinc-300">
            KYC and payouts follow lawful verification, encryption, and retention practices, balancing compliance and user convenience.
          </p>
        </div>
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 bg-white dark:bg-zinc-900">
          <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Support & Transparency</h3>
          <p class="mt-2 text-sm leading-6 text-zinc-700 dark:text-zinc-300">
            Helpful documentation and responsive channels keep customers informed—from purchase to delivery to post‑sale assistance.
          </p>
        </div>
      </div>
    </section>

    <!-- Social Proof / Highlights -->
    <section class="mb-16">
      <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Highlights</h2>
      <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 bg-white dark:bg-zinc-900 text-center">
          <p class="text-3xl font-extrabold text-zinc-900 dark:text-zinc-100">99.9%</p>
          <p class="mt-1 text-xs tracking-wide uppercase text-zinc-500">Uptime</p>
        </div>
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 bg-white dark:bg-zinc-900 text-center">
          <p class="text-3xl font-extrabold text-zinc-900 dark:text-zinc-100">Trusted</p>
          <p class="mt-1 text-xs tracking-wide uppercase text-zinc-500">Logistics & Payments</p>
        </div>
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 bg-white dark:bg-zinc-900 text-center">
          <p class="text-3xl font-extrabold text-zinc-900 dark:text-zinc-100">Clarity</p>
          <p class="mt-1 text-xs tracking-wide uppercase text-zinc-500">Policies & Docs</p>
        </div>
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 bg-white dark:bg-zinc-900 text-center">
          <p class="text-3xl font-extrabold text-zinc-900 dark:text-zinc-100">Secure</p>
          <p class="mt-1 text-xs tracking-wide uppercase text-zinc-500">Data & Wallet</p>
        </div>
      </div>
    </section>

    <!-- Leadership / Team Cards -->
    <section class="mb-16">
      <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Leadership</h2>
      <p class="mt-2 text-sm leading-6 text-zinc-700 dark:text-zinc-300">
        Meet the people responsible for strategy, reliability, and customer trust.
      </p>
      <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Example placeholder cards; replace with your data or slots -->
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
          <div class="h-16 w-16 rounded-full bg-zinc-200 dark:bg-zinc-800" />
          <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100">Head of Operations</h3>
          <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Logistics, SLAs, and partner quality.</p>
        </div>
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
          <div class="h-16 w-16 rounded-full bg-zinc-200 dark:bg-zinc-800" />
          <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100">Head of Customer Trust</h3>
          <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Policy clarity, fairness, and support.</p>
        </div>
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
          <div class="h-16 w-16 rounded-full bg-zinc-200 dark:bg-zinc-800" />
          <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100">Head of Security & Compliance</h3>
          <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Data protection, verification, and audits.</p>
        </div>
      </div>
    </section>

    <!-- Timeline / Milestones -->
    <section class="mb-16">
      <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Milestones</h2>
      <ol class="mt-6 space-y-6">
        <li class="relative pl-6">
          <span class="absolute left-0 top-1.5 h-3 w-3 rounded-full bg-indigo-500"></span>
          <p class="text-sm text-zinc-600 dark:text-zinc-400">Founded to bring clarity and respect to everyday commerce.</p>
        </li>
        <li class="relative pl-6">
          <span class="absolute left-0 top-1.5 h-3 w-3 rounded-full bg-indigo-500"></span>
          <p class="text-sm text-zinc-600 dark:text-zinc-400">Expanded with trusted logistics partners to improve delivery coverage.</p>
        </li>
        <li class="relative pl-6">
          <span class="absolute left-0 top-1.5 h-3 w-3 rounded-full bg-indigo-500"></span>
          <p class="text-sm text-zinc-600 dark:text-zinc-400">Launched wallet credits and secure withdrawal flow for faster refunds.</p>
        </li>
      </ol>
    </section>

    <!-- Call to Action -->
    <section class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 sm:p-8 bg-white dark:bg-zinc-900">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Questions? We’re here to help.</h2>
          <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            Reach out to our support team for policy guidance, order help, or partnership queries.
          </p>
        </div>
        <div class="flex gap-3">
          <NuxtLink to="/contact" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Contact Us
          </NuxtLink>
          <NuxtLink to="/policies" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 dark:border-zinc-700 px-4 py-2 text-sm font-semibold text-zinc-900 dark:text-zinc-100 hover:bg-zinc-50 dark:hover:bg-zinc-800">
            View Policies
          </NuxtLink>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup lang="ts">
const config = useRuntimeConfig()
const companyName = config.public.companyName
const websiteName = config.public.websiteName
</script>
