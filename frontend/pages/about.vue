<template>
  <div class="max-w-3xl mx-auto px-4 py-16 text-gray-800 dark:text-gray-200">
    <!-- Page Title -->
    <h1 class="text-3xl font-bold mb-6">About Us</h1>

    <!-- Introduction -->
    <p class="mb-4">
      Welcome to <strong>{{ websiteName }}</strong>, a platform crafted by <strong>{{ companyName }}</strong> with one mission — to empower people and businesses through seamless digital experiences.
    </p>

    <p class="mb-4">
      Whether you're here to explore, learn, build, or grow, we’re here to support that journey. Our work is rooted in thoughtful design, open technology, and an inclusive community vision.
    </p>

    <!-- Our Story -->
    <h2 class="text-xl font-semibold mt-10 mb-4">Our Story</h2>
    <p class="mb-4">
      {{ companyName }} was born out of the belief that the digital world should be more accessible, more ethical, and more human. We started as a small team with a big dream — to bridge the gap between creativity, commerce, and community.
    </p>
    <p class="mb-4">
      Today, {{ websiteName }} serves as a growing hub for users, creators, and organizations looking for smarter, cleaner, and future-friendly tools to connect and succeed.
    </p>

    <!-- Mission -->
    <h2 class="text-xl font-semibold mt-10 mb-4">Our Mission</h2>
    <p class="mb-4">
      To design and deliver digital experiences that enable individuals and organizations to thrive online — by building products that are simple, secure, and scalable.
    </p>

    <!-- Vision -->
    <h2 class="text-xl font-semibold mt-10 mb-4">Our Vision</h2>
    <p class="mb-4">
      We envision a web where innovation is inclusive, privacy is respected, and success is shared. Our aim is to be a trusted partner in the digital transformation journey of every user — from the first click to future growth.
    </p>

    <!-- Our Values -->
    <h2 class="text-xl font-semibold mt-10 mb-4">What We Stand For</h2>
    <ul class="list-none space-y-2">
    <li><strong>User-First:</strong> Everything we build starts with real people and their needs.</li>
      <li><strong>Transparency:</strong> We value honesty and openness in all our interactions.</li>
      <li><strong>Excellence:</strong> We obsess over quality and continuous improvement.</li>
      <li><strong>Accessibility:</strong> We believe everyone deserves great design and functionality — equally.</li>
      <li><strong>Community:</strong> We grow together through collaboration, feedback, and shared purpose.</li>
    </ul>

    <!-- Optional Team Highlight -->
    <h2 class="text-xl font-semibold mt-10 mb-4">Our Team</h2>
    <p class="mb-4">
      Behind {{ companyName }} is a diverse team of designers, developers, strategists, and creators who care deeply about people, the web, and its potential to do good.
    </p>
  </div>
</template>

<script setup lang="ts">
const config = useRuntimeConfig()
const websiteName = config.public.websiteName
const companyName = config.public.companyName



usePageMeta({
  title: product.value?.name,
  description: product.value?.short_description,
  image: product.value?.image,
  price: product.value?.price,
  currency: 'USD',
  keywords: product.value?.tags || [],
  type: 'product',
})



</script>
