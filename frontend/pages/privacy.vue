<template>
  <main class="mx-auto max-w-7xl px-4 py-16">
    <!-- Header -->
    <header class="mb-12">
      <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
        Privacy Policy
      </h1>
      <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
        Effective Date:
        <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ effectiveDate }}</span>
      </p>
      <p class="mt-6 max-w-7xl text-base leading-7 text-zinc-700 dark:text-zinc-300 text-justify">
        This Privacy Policy explains how <strong class="font-semibold">{{ companyName }}</strong> (“we”, “us”, or “our”)
        collects, uses, shares, and protects personal information in connection with <strong class="font-semibold">{{ websiteName }}</strong>
        (the “Website”) and related services. By using the Website, users acknowledge this Policy. Where required by law,
        we will request consent for certain processing and provide mechanisms to exercise privacy rights. This document is written
        for clarity and does not waive any non‑waivable consumer rights.
      </p>
    </header>

    <!-- Alternating 3-column layout with fieldsets and legends -->
    <section class="space-y-12">
      <!-- Pattern A: Left legend (2 cols), 1 col spacer, Right content (2 cols) -->
      <fieldset class="grid grid-cols-1 gap-y-4 lg:grid-cols-3 lg:gap-6 items-center">
        <legend class="col-span-1 lg:col-span-2 text-xl font-bold text-zinc-900 dark:text-zinc-100">
          Information We Collect
        </legend>
        <div class="col-span-1  lg:col-span-2 text-base leading-7 text-zinc-800 dark:text-zinc-200 space-y-3">
          <p class="text-justify">
            We collect information directly from users, automatically via the Website, and from service providers. This includes identity and contact data (name, email, mobile), account credentials, order and transaction records, delivery addresses, device and usage data (pages viewed, clicks, approximate location, browser, OS), and communications with support. Where necessary for a feature, we may process compliance‑related identifiers for verification (e.g., GST, Aadhaar segments for KYC) under strict minimization.
          </p>
          <p class="text-justify">
            Payments are handled by certified processors; we do not store full card numbers or sensitive authentication data. For payouts (e.g., seller/partner withdrawals), banking data may be encrypted and used solely to execute transfers and meet statutory duties. Wallet PINs, OTPs (mobile/email), and passwords are used only for authentication/authorization with hashing or encryption. We do not knowingly collect children’s data where parental consent is required.
          </p>
        </div>
        <div class="hidden lg:block lg:col-span-1 mx-auto">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="0.5" stroke="currentColor" class="size-6 w-60 h-60 ">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" />
          </svg>
        </div>
      </fieldset>

      <!-- Pattern B: Right legend (2 cols), 1 col spacer, Left content (2 cols) -->
      <fieldset class="grid grid-cols-1 gap-y-4 lg:grid-cols-3 lg:gap-6 lg:text-right items-center">
        <legend class="col-span-1 lg:col-span-2 lg:col-start-4 text-xl font-bold text-zinc-900 dark:text-zinc-100">
          Why We Use Your Information
        </legend>
        <div class="hidden lg:block lg:col-span-1 mx-auto">
          <svg xmlns="http://www.w3.org/2000/svg"
               fill="none" viewBox="0 0 24 24"
               stroke-width="0.5" stroke="currentColor"
               class="size-6 w-60 h-60
            fill-gray-300 dark:fill-gray-800
            dark:drop-shadow-[0_0_20px_rgba(59,130,246,0.7)]">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
          </svg>
        </div>
        <div class="col-span-1 lg:col-span-2 lg:col-start-2 lg:text-right text-base leading-7 text-zinc-800 dark:text-zinc-200 space-y-3">
          <p class="text-justify">
            We use personal information to operate and secure the Website; process orders, payments, and deliveries; provide wallet services; verify identity and eligibility (KYC) where required; deliver customer support; personalize content; and improve through analytics. Where applicable (e.g., GDPR), our lawful bases include consent, contract performance, legitimate interests (e.g., fraud prevention), and legal obligations (e.g., tax or KYC).
          </p>
          <p class="text-justify">
            For marketing, we obtain consent where required and provide opt‑out options. We minimize collection and keep data only as long as needed unless law requires or permits longer retention (e.g., regulatory windows).
          </p>
        </div>
      </fieldset>

      <!-- A -->
      <fieldset class="grid grid-cols-1 gap-y-4 lg:grid-cols-3 lg:gap-6">
        <legend class="col-span-1 lg:col-span-2 text-xl font-bold text-zinc-900 dark:text-zinc-100">
          Cookies & Similar Technologies
        </legend>

        <div class="col-span-1 lg:col-span-2 text-base leading-7 text-zinc-800 dark:text-zinc-200 space-y-3">
          <p class="text-justify">
            We use cookies, SDKs, and similar technologies to keep the Website reliable, remember preferences, measure performance, and understand usage. Essential cookies support core functionality; analytics or advertising cookies help improve experience and measure campaigns. Where required, we obtain consent and provide preference controls via browser settings or in‑site options.
          </p>
          <p class="text-justify">
            Third‑party analytics and ad partners (e.g., Google advertising services) may receive pseudonymous identifiers and usage signals to facilitate reporting and ad delivery. These partners operate under their own privacy terms. Users can opt out as mandated by law or via platform controls.
          </p>
        </div>
        <div class="hidden lg:block lg:col-span-1 mx-auto">
          <svg xmlns="http://www.w3.org/2000/svg"
               viewBox="0 0 64 64"
               aria-hidden="true" role="img"
               class="w-60 h-60 stroke-current fill-gray-300 dark:fill-gray-800 dark:drop-shadow-[0_0_20px_rgba(59,130,246,0.8)]">
            <!-- Cookie outline with bite taken -->
            <path d="M44.8 10.7
           a3.8 3.8 0 0 0 4.9 4.9
           a3.8 3.8 0 0 0 4.8 5
           a3.8 3.8 0 0 0 -4.4 6
           a3.8 3.8 0 0 0 -1.8 6.5
           a3.8 3.8 0 0 0 -4.4 6
           a3.8 3.8 0 0 0 -6.7 2.4
           a3.8 3.8 0 0 0 -6.7 2.4
           a3.8 3.8 0 0 0 -6.4 -1.8
           a3.8 3.8 0 0 0 -5.8 -4.2
           a3.8 3.8 0 0 0 -4.6 -5.9
           a18.5 18.5 0 0 1 0 -22.4
           a3.8 3.8 0 0 0 4.6 -4
           a3.8 3.8 0 0 0 5.8 -4.2
           a3.8 3.8 0 0 0 6.4 -1.8
           a3.8 3.8 0 0 0 6.7 2.4
           a3.8 3.8 0 0 0 6.7 2.4
           Z"
                  stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>

            <!-- Chocolate chip holes (stroked circles) -->
            <circle cx="24" cy="26" r="1.8" stroke="currentColor" stroke-width="2" />
            <circle cx="32" cy="22" r="1.6" stroke="currentColor" stroke-width="2" />
            <circle cx="39" cy="28" r="2.0" stroke="currentColor" stroke-width="2" />
            <circle cx="28" cy="36" r="1.6" stroke="currentColor" stroke-width="2" />
            <circle cx="36" cy="38" r="1.5" stroke="currentColor" stroke-width="2" />
          </svg>
        </div>
      </fieldset>

      <!-- B -->
      <fieldset class="grid grid-cols-1 gap-y-4 lg:grid-cols-3 lg:gap-6 lg:text-right items-center">
        <legend class="col-span-1 lg:col-span-2 lg:col-start-4 text-xl font-bold text-zinc-900 dark:text-zinc-100">
          How We Share Information
        </legend>

        <div class="hidden lg:block lg:col-span-1 mx-auto ">
          <svg xmlns="http://www.w3.org/2000/svg"  fill="currentColor" stroke-width="0.5" class="size-6 w-60 h-60 stroke-current fill-gray-300 dark:fill-gray-800 dark:drop-shadow-[0_0_20px_rgba(59,130,246,0.8)]" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M6 3.5A1.5 1.5 0 0 1 7.5 2h1A1.5 1.5 0 0 1 10 3.5v1A1.5 1.5 0 0 1 8.5 6v1H14a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 2 7h5.5V6A1.5 1.5 0 0 1 6 4.5zM8.5 5a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5zM0 11.5A1.5 1.5 0 0 1 1.5 10h1A1.5 1.5 0 0 1 4 11.5v1A1.5 1.5 0 0 1 2.5 14h-1A1.5 1.5 0 0 1 0 12.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm4.5.5A1.5 1.5 0 0 1 7.5 10h1a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 8.5 14h-1A1.5 1.5 0 0 1 6 12.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm4.5.5a1.5 1.5 0 0 1 1.5-1.5h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
          </svg>
        </div>

        <div class="col-span-1 lg:col-span-2 lg:col-start-2 lg:text-left text-base leading-7 text-zinc-800 dark:text-zinc-200 space-y-3">
          <p class="text-justify">
            We do not sell personal information publicly. We share data only with service providers (hosting, analytics, security, communications, identity/KYC, payments, banking for payouts, shipping/delivery, wallet), distributors/store partners for fulfillment or pickup, and as required by law or to protect rights, safety, or property. Providers are bound by contract and security controls.
          </p>
          <p class="text-justify">
            Where law defines “sharing” for cross‑context advertising, we honor opt‑out choices. In mergers or asset transfers, information may be moved under equivalent safeguards. Aadhaar, banking credentials, wallet PINs, and passwords are never disclosed publicly and are used only for their specific purpose with strict controls.
          </p>
        </div>

      </fieldset>

      <!-- A -->
      <fieldset class="grid grid-cols-1 gap-y-4 lg:grid-cols-3 lg:gap-6 items-center">
        <legend class="col-span-1 lg:col-span-2 text-xl font-bold text-zinc-900 dark:text-zinc-100">
          Your Choices & Rights
        </legend>

        <div class="col-span-1 lg:col-span-2 text-base leading-7 text-zinc-800 dark:text-zinc-200 space-y-3">
          <p class="text-justify">
            Depending on jurisdiction, users may access, correct, delete, or port their data; object to or restrict processing; and withdraw consent without affecting prior lawful processing. We verify identity and respond within required timelines, subject to exceptions. Users can manage marketing preferences via unsubscribe links or account settings where available.
          </p>
          <p class="text-justify">
            Cookie/ad preferences can be managed via browser/device settings and platform tools. If processing relies on consent (e.g., optional analytics/ads), users may withdraw it at any time using available mechanisms. Users may lodge complaints with a supervisory authority where applicable.
          </p>
        </div>

        <div class="hidden lg:block lg:col-span-1 mx-auto ">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="0.5" fill="currentColor" class="size-6 w-60 h-60 stroke-current fill-gray-300 dark:fill-gray-800 dark:drop-shadow-[0_0_20px_rgba(59,130,246,0.8)]">
            <path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0 1 12 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 0 1 3.498 1.307 4.491 4.491 0 0 1 1.307 3.497A4.49 4.49 0 0 1 21.75 12a4.49 4.49 0 0 1-1.549 3.397 4.491 4.491 0 0 1-1.307 3.497 4.491 4.491 0 0 1-3.497 1.307A4.49 4.49 0 0 1 12 21.75a4.49 4.49 0 0 1-3.397-1.549 4.49 4.49 0 0 1-3.498-1.306 4.491 4.491 0 0 1-1.307-3.498A4.49 4.49 0 0 1 2.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 0 1 1.307-3.497 4.49 4.49 0 0 1 3.497-1.307Zm7.007 6.387a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
          </svg>
        </div>
      </fieldset>

      <!-- B -->
      <fieldset class="grid grid-cols-1 gap-y-4 lg:grid-cols-3 lg:gap-6 lg:text-right items-center">
        <legend class="col-span-1 lg:col-span-2 lg:col-start-4 text-xl font-bold text-zinc-900 dark:text-zinc-100">
          Security Measures
        </legend>

        <div class="hidden lg:block lg:col-span-1 mx-auto ">
          <svg xmlns="http://www.w3.org/2000/svg"   viewBox="0 0 24 24" stroke-width="0.5" fill="currentColor" class="size-6 w-60 h-60 stroke-current fill-gray-300 dark:fill-gray-800 dark:drop-shadow-[0_0_20px_rgba(59,130,246,0.8)]">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
          </svg>

        </div>

        <div class="col-span-1 lg:col-span-2 lg:col-start-2 lg:text-left text-base leading-7 text-zinc-800 dark:text-zinc-200 space-y-3">
          <p class="text-justify">
            We apply organizational and technical safeguards—encryption (at rest/in transit where suitable), access controls, monitoring, and periodic reviews—to mitigate risks. Passwords are hashed, OTPs are time‑bound, and wallet PINs are handled confidentially and never shown in plain text. No system is perfectly secure; users should maintain strong security practices.
          </p>
          <p class="text-justify">
            If a data incident occurs, we follow legal notification duties and response procedures to limit harm. Users should promptly report suspected compromise so we can assist and protect accounts.
          </p>
        </div>
      </fieldset>

      <!-- A -->
      <fieldset class="grid grid-cols-1 gap-y-4 lg:grid-cols-3 lg:gap-6 items-center">
        <legend class="col-span-1 lg:col-span-2 text-xl font-bold text-zinc-900 dark:text-zinc-100">
          Data Retention
        </legend>
        <div class="col-span-1 lg:col-span-2 text-base leading-7 text-zinc-800 dark:text-zinc-200 space-y-3">
          <p class="text-justify">
            We retain information for as long as needed to provide services, fulfill orders, comply with legal and tax requirements, prevent fraud, resolve disputes, and enforce agreements. KYC and payout records may require longer statutory retention under applicable regulations.
          </p>
          <p class="text-justify">
            When no longer necessary, data is deleted or de‑identified per our schedule and legal obligations. Aggregated or anonymized insights that do not reasonably identify individuals may be kept for analytics and service improvement.
          </p>
        </div>
        <div class="hidden lg:block lg:col-span-1 mx-auto ">
          <svg xmlns="http://www.w3.org/2000/svg" stroke-width="0.5" fill="currentColor" class="w-52 h-52 stroke-current fill-gray-300 dark:fill-gray-800 dark:drop-shadow-[0_0_20px_rgba(59,130,246,0.8)]" viewBox="0 0 16 16">
            <path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
          </svg>

        </div>
      </fieldset>

      <!-- B -->
      <fieldset class="grid grid-cols-1 gap-y-4 lg:grid-cols-3 lg:gap-6 lg:text-right items-center">
        <legend class="col-span-1 lg:col-span-2 lg:col-start-4 text-xl font-bold text-zinc-900 dark:text-zinc-100">
          International Transfers
        </legend>

        <div class="hidden lg:block lg:col-span-1 mx-auto ">
          <svg xmlns="http://www.w3.org/2000/svg" stroke-width="0.5" fill="currentColor" class="w-52 h-52 stroke-current fill-gray-300 dark:fill-gray-800 dark:drop-shadow-[0_0_20px_rgba(59,130,246,0.8)]" viewBox="0 0 16 16">
            <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0M4.882 1.731a.48.48 0 0 0 .14.291.487.487 0 0 1-.126.78l-.291.146a.7.7 0 0 0-.188.135l-.48.48a1 1 0 0 1-1.023.242l-.02-.007a1 1 0 0 0-.462-.04 7 7 0 0 1 2.45-2.027m-3 9.674.86-.216a1 1 0 0 0 .758-.97v-.184a1 1 0 0 1 .445-.832l.04-.026a1 1 0 0 0 .152-1.54L3.121 6.621a.414.414 0 0 1 .542-.624l1.09.818a.5.5 0 0 0 .523.047.5.5 0 0 1 .724.447v.455a.8.8 0 0 0 .131.433l.795 1.192a1 1 0 0 1 .116.238l.73 2.19a1 1 0 0 0 .949.683h.058a1 1 0 0 0 .949-.684l.73-2.189a1 1 0 0 1 .116-.238l.791-1.187A.45.45 0 0 1 11.743 8c.16 0 .306.084.392.218.557.875 1.63 2.282 2.365 2.282l.04-.001a7.003 7.003 0 0 1-12.658.905Z"/>
          </svg>
        </div>

        <div class="col-span-1 lg:col-span-2 lg:col-start-2 lg:text-left text-base leading-7 text-zinc-800 dark:text-zinc-200 space-y-3">
          <p class="text-justify">
            We may process data outside the user’s country. Where required, we use safeguards such as standard contractual clauses or other lawful mechanisms to ensure adequate protection. Users may request information on transfer mechanisms, subject to confidentiality and legal limits.
          </p>
          <p class="text-justify">
            Transfers to payment, shipping, identity verification, or analytics partners are limited to providing the requested service and governed by contracts and security requirements.
          </p>
        </div>
      </fieldset>

      <!-- A -->
      <fieldset class="grid grid-cols-1 gap-y-4 lg:grid-cols-3 lg:gap-6 items-center">
        <legend class="col-span-1 lg:col-span-2 text-xl font-bold text-zinc-900 dark:text-zinc-100">
          Guest Checkout & Distributor Purchases
        </legend>

        <div class="col-span-1 lg:col-span-2 text-base leading-7 text-zinc-800 dark:text-zinc-200 space-y-3">
          <p class="text-justify">
            Customers may purchase from company distributors or stores without creating an account or subscription. Only the minimum information needed for fulfillment—contact details and delivery address—is collected and shared with the relevant distributor solely for that transaction, under confidentiality obligations.
          </p>
          <p class="text-justify">
            During guest checkout, payment and shipping partners process the data required to complete the order. We do not publicly disclose personal information and restrict use to the transaction’s purpose, post‑sale support, and legal compliance.
          </p>
        </div>


        <div class="hidden lg:block lg:col-span-1 mx-auto ">
          <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" stroke-width="0.5" fill="currentColor" class="w-60 h-60 stroke-current fill-gray-300 dark:fill-gray-800 dark:drop-shadow-[0_0_20px_rgba(59,130,246,0.8)]">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
          </svg>
        </div>
      </fieldset>

      <!-- B -->
      <fieldset class="grid grid-cols-1 gap-y-4 lg:grid-cols-3 lg:gap-6 lg:text-right items-center">
        <legend class="col-span-1 lg:col-span-2 lg:col-start-4 text-xl font-bold text-zinc-900 dark:text-zinc-100">
          Delivery Addresses & Order Fulfillment
        </legend>
        <div class="hidden lg:block lg:col-span-1 mx-auto ">
          <svg xmlns="http://www.w3.org/2000/svg" stroke-width="0.5" fill="currentColor" class="w-52 h-52 stroke-current fill-gray-300 dark:fill-gray-800 dark:drop-shadow-[0_0_20px_rgba(59,130,246,0.8)]" viewBox="0 0 16 16">
            <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"/>
            <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
          </svg>
        </div>
        <div class="col-span-1 lg:col-span-2 lg:col-start-2 lg:text-left text-base leading-7 text-zinc-800 dark:text-zinc-200 space-y-3">
          <p class="text-justify">
            Delivery addresses are used to calculate shipping options, estimate timelines, and fulfill orders. Address details are shared only with logistics partners or distributors involved in fulfillment and are not made public. Access is limited to personnel who need it to provide the service.
          </p>
          <p class="text-justify">
            Users can update addresses via account settings (if available) or by contacting support for guest orders. Where required, delivery records may be retained for tax, warranty, or dispute resolution.
          </p>
        </div>
      </fieldset>

      <!-- A -->
      <fieldset class="grid grid-cols-1 gap-y-4 lg:grid-cols-3 lg:gap-6 items-center">
        <legend class="col-span-1 lg:col-span-2 text-xl font-bold text-zinc-900 dark:text-zinc-100">
          Wallet PIN, OTP & Passwords
        </legend>
        <div class="col-span-1 lg:col-span-2 text-base leading-7 text-zinc-800 dark:text-zinc-200 space-y-3">
          <p class="text-justify">
            Wallet PINs, one‑time passwords (OTPs), and passwords are treated as confidential authentication factors. Passwords are stored using strong hashing, OTPs are time‑bound and used only for verification, and wallet PINs are processed for wallet transactions and never displayed in plain text. We strongly advise users not to share these credentials.
          </p>
          <p class="text-justify">
            If a credential is suspected to be compromised, reset it immediately and contact support. We may block or reset access to protect users and the service from fraud or abuse.
          </p>
        </div>
        <div class="hidden lg:block lg:col-span-1 mx-auto">
          <svg xmlns="http://www.w3.org/2000/svg" stroke-width="0.5" fill="currentColor" class="w-52 h-52 stroke-current fill-gray-300 dark:fill-gray-800 dark:drop-shadow-[0_0_20px_rgba(59,130,246,0.8)]" viewBox="0 0 16 16">
            <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
            <path d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415"/>
          </svg>
        </div>
      </fieldset>

      <!-- B -->
      <fieldset class="grid grid-cols-1 gap-y-4 lg:grid-cols-3 lg:gap-6 lg:text-right items-center">
        <legend class="col-span-1 lg:col-span-2 lg:col-start-4 text-xl font-bold text-zinc-900 dark:text-zinc-100">
          Do Not Track & Opt‑Out Signals
        </legend>
        <div class="hidden lg:block lg:col-span-1 mx-auto">
          <svg xmlns="http://www.w3.org/2000/svg" stroke-width="0.5" fill="currentColor" class="w-52 h-52 stroke-current fill-gray-300 dark:fill-gray-800 dark:drop-shadow-[0_0_20px_rgba(59,130,246,0.8)]" viewBox="0 0 16 16">
            <path d="M8 5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3m0 4a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3m1.5 2.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
            <path d="M4 2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2h2c-.167.5-.8 1.6-2 2v2h2c-.167.5-.8 1.6-2 2v2h2c-.167.5-.8 1.6-2 2v1a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-1c-1.2-.4-1.833-1.5-2-2h2V8c-1.2-.4-1.833-1.5-2-2h2V4c-1.2-.4-1.833-1.5-2-2zm2-1a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1z"/>
          </svg>
        </div>
        <div class="col-span-1 lg:col-span-2 lg:col-start-2 lg:text-left text-base leading-7 text-zinc-800 dark:text-zinc-200 space-y-3">
          <p class="text-justify">
            Browser “Do Not Track” or similar signals are not consistently handled across the industry. We honor legally mandated universal opt‑out signals and provide additional controls where required. As standards evolve, our response may change and will be reflected here or on a dedicated page.
          </p>
          <p class="text-justify">
            Users can also manage advertising or analytics preferences via browser/device settings and platform tools. Where law provides specific opt‑out rights, we will make mechanisms available to exercise those rights.
          </p>
        </div>
      </fieldset>

      <!-- A -->
      <fieldset class="grid grid-cols-1 gap-y-4 lg:grid-cols-3 lg:gap-6 items-center">
        <legend class="col-span-1 lg:col-span-2 text-xl font-bold text-zinc-900 dark:text-zinc-100">
          Changes to This Policy
        </legend>
        <div class="col-span-1 lg:col-span-2 text-base leading-7 text-zinc-800 dark:text-zinc-200 space-y-3">
          <p class="text-justify">
            We may revise this Policy to reflect updates in our practices or legal requirements. Material changes will be effective upon posting or as otherwise communicated. Continued use after changes take effect signifies acceptance. Users should review this page periodically for the latest information.
          </p>
          <p class="text-justify">
            Where a change requires new consent under applicable law, we will obtain such consent. Prior versions may be available upon request, subject to our retention policies.
          </p>
        </div>
        <div class="hidden lg:block lg:col-span-1 mx-auto">
          <svg xmlns="http://www.w3.org/2000/svg"
               stroke-width="0.5" fill="currentColor"
               class="w-48 h-48 stroke-current
              fill-gray-300 dark:fill-gray-800
              dark:drop-shadow-[0_0_20px_rgba(59,130,246,0.8)]
              transform scale-x-[-1]"
               viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M10.646.646a.5.5 0 0 1 .708 0l4 4a.5.5 0 0 1 0 .708l-1.902 1.902-.829 3.313a1.5 1.5 0 0 1-1.024 1.073L1.254 14.746 4.358 4.4A1.5 1.5 0 0 1 5.43 3.377l3.313-.828zm-1.8 2.908-3.173.793a.5.5 0 0 0-.358.342l-2.57 8.565 8.567-2.57a.5.5 0 0 0 .34-.357l.794-3.174-3.6-3.6z"/>
            <path fill-rule="evenodd" d="M2.832 13.228 8 9a1 1 0 1 0-1-1l-4.228 5.168-.026.086z"/>
          </svg>
        </div>

      </fieldset>

      <hr class=" dark:drop-shadow-[0_0_20px_rgba(59,130,246,0.8)]">

      <!-- B -->
      <fieldset class="grid grid-cols-1 gap-y-4 lg:grid-cols-3 lg:gap-6 lg:text-left">
        <legend class="col-span-1 lg:col-span-2  text-xl font-bold text-zinc-900 dark:text-zinc-100">
          Contact Us
        </legend>
        <div class="col-span-1 lg:col-span-2 lg:col-start-1 lg:text-left text-base leading-7 text-zinc-800 dark:text-zinc-200 space-y-3">
          <p class="text-justify">
            For privacy questions, requests, or concerns, please contact:
          </p>
          <p>
            <span class="font-semibold">{{ companyName }}</span><br />
            {{ address }}<br />
            Email: <a class="text-indigo-600 hover:underline" :href="`mailto:${supportEmail}`">{{ supportEmail }}</a><br />
            Phone: <span class="tabular-nums">{{ phoneNumber }}</span>
          </p>
        </div>
      </fieldset>
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
