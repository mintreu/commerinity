<template>
  <main class="mx-auto max-w-7xl px-4 py-16">
    <!-- Header -->
    <header class="mb-10">
      <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
        Return & Refund Policy
      </h1>
      <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
        Effective Date:
        <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ effectiveDate }}</span>
      </p>
      <p class="mt-5 max-w-4xl text-base leading-7 text-zinc-700 dark:text-zinc-300">
        This Return & Refund Policy governs return eligibility and refund processing for purchases made on
        <strong class="font-semibold">{{ websiteName }}</strong>, operated by {{ companyName }}. Product‑specific
        eligibility applies as stated on each product page. If a product is marked “Non‑returnable,” the purchase is
        final and a refund is not possible except where required by applicable law.
      </p>
    </header>

    <!-- Returns -->
    <section class="space-y-6">
      <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Returns</h2>

      <!-- Eligibility & Product-Type Rules -->
      <div>
        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Eligibility & Product-Type Rules</h3>
        <p class="mt-2 text-base leading-7 text-zinc-800 dark:text-zinc-200">
          Return and refund eligibility solely depends on the product and its type. Each product page clearly states if
          the item is returnable. Items marked as “Non‑returnable” are not eligible for return or refund under any
          circumstance, except where a mandatory legal warranty applies.
        </p>
        <p class="mt-2 text-base leading-7 text-zinc-800 dark:text-zinc-200">
          Customers must first receive and accept delivery of the order. Returns are considered only when a logical,
          verifiable fault attributable to {{ companyName }} or the authorized seller is identified (e.g., wrong item
          shipped, manufacturing defect, or missing components as per listing).
        </p>
      </div>

      <!-- No Change-of-Mind or Choice Switching -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">No Change‑of‑Mind or Choice Switching</h3>
        <p class="mt-2 text-base leading-7 text-zinc-800 dark:text-zinc-200">
          Orders must be placed with a clear and specific choice (e.g., size, color, variant). After receiving “Choice A,”
          a desire to replace it with “Choice B” will not be treated as a valid reason for return or refund. Preference or
          change‑of‑mind requests are not accepted.
        </p>
      </div>

      <!-- Order Lock, No Cancellation -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Order Lock & No Cancellation</h3>
        <p class="mt-2 text-base leading-7 text-zinc-800 dark:text-zinc-200">
          Once the shipping process has started, no changes to the order are permitted. Orders cannot be cancelled after
          dispatch initiation or handover to the carrier.
        </p>
      </div>

      <!-- Return Window, Condition & Proof -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Return Window, Condition & Proof</h3>
        <p class="mt-2 text-base leading-7 text-zinc-800 dark:text-zinc-200">
          If a product is returnable, the applicable return window and conditions (including packaging, tags, accessories,
          and hygiene seals) are specified on the respective product page. Returns must include the original invoice and
          unboxing/issue evidence (e.g., photos or video) where requested, to validate the claim.
        </p>
      </div>

      <!-- How Returns Are Collected -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Return Pickup via Third‑Party Courier</h3>
        <p class="mt-2 text-base leading-7 text-zinc-800 dark:text-zinc-200">
          Return logistics are handled by third‑party shipping courier services appointed or partnered with
          {{ companyName }}. The pickup schedule and method depend on serviceability at the pickup address. If pickup is
          unavailable, {{ companyName }} may direct a drop‑off or an alternate method at its discretion.
        </p>
      </div>

      <!-- Inspection & Decision -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Inspection & Final Decision</h3>
        <p class="mt-2 text-base leading-7 text-zinc-800 dark:text-zinc-200">
          All returned items are inspected for condition and claimed fault. Approval or rejection of a return is determined
          after inspection and verification. If the item fails to meet the stated conditions or no seller/company fault is
          established, the return may be rejected and the item returned to the customer.
        </p>
      </div>

      <!-- Non-Refundable Categories -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Non‑Refundable Fees & Subscriptions</h3>
        <ul class="mt-2 space-y-2 text-base leading-7 text-zinc-800 dark:text-zinc-200 list-disc pl-5">
          <li>Career Job Application Fees are non‑refundable.</li>
          <li>Optional membership subscription charges are non‑refundable.</li>
        </ul>
      </div>
    </section>

    <!-- Refunds -->
    <section class="space-y-6 mt-12">
      <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Refunds</h2>

      <!-- Wallet-First, Timeline -->
      <div>
        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Refund Method & Timeline</h3>
        <p class="mt-2 text-base leading-7 text-zinc-800 dark:text-zinc-200">
          Upon approval of a return or claim, refunds are first credited to the customer’s {{ websiteName }} wallet
          (store credit). From the wallet, the customer may initiate a withdrawal to a verified bank account. Bank
          settlement can take up to 15 days from the date of refund approval/processing, subject to bank and payment
          processor timelines.
        </p>
      </div>

      <!-- What is Refundable -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">What May Be Refunded</h3>
        <p class="mt-2 text-base leading-7 text-zinc-800 dark:text-zinc-200">
          Refunds, where approved, typically include the product price paid. Shipping charges, COD fees, service fees,
          or promotional benefits may be excluded unless required by law or explicitly stated. In cases of confirmed
          seller/company fault (e.g., wrong or defective item), {{ companyName }} may, at its discretion, include
          reasonable shipping costs in the refund.
        </p>
      </div>

      <!-- Non-Refundable Scenarios -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Non‑Refundable Scenarios</h3>
        <ul class="mt-2 space-y-2 text-base leading-7 text-zinc-800 dark:text-zinc-200 list-disc pl-5">
          <li>Change‑of‑mind or preference changes (e.g., exchanging Choice A for Choice B after delivery).</li>
          <li>Products marked “Non‑returnable” on the product page, except where required by applicable law.</li>
          <li>Orders for which the shipping process has started (no cancellation; changes not permitted after dispatch initiation).</li>
          <li>Career Job Application Fees and optional membership subscription charges.</li>
        </ul>
      </div>

      <!-- Proof, Abuse Prevention -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Proof Requirements & Abuse Prevention</h3>
        <p class="mt-2 text-base leading-7 text-zinc-800 dark:text-zinc-200">
          We may require evidence (e.g., photos, videos, carrier notes) to validate claims. Fraudulent, abusive, or
          excessive return/refund activity may lead to policy restrictions, denial of requests, or account actions as
          permitted by law and platform rules.
        </p>
      </div>

      <!-- How to Request -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">How to Request Return or Refund</h3>
        <p class="mt-2 text-base leading-7 text-zinc-800 dark:text-zinc-200">
          If eligible, submit a request through your order details page or contact our support with order ID, issue
          description, and required evidence. We will guide you through pickup (via third‑party courier) or alternate
          instructions based on serviceability.
        </p>
      </div>

      <!-- Policy Updates & Contact -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Policy Updates & Contact</h3>
        <p class="mt-2 text-base leading-7 text-zinc-800 dark:text-zinc-200">
          {{ companyName }} may update this policy to align with product types, logistics, or regulatory changes.
          Material updates take effect upon posting unless stated otherwise. For queries or assistance, contact:
        </p>
        <p class="mt-1 text-base leading-7 text-zinc-800 dark:text-zinc-200">
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
