<template>
  <div class="min-h-screen w-full bg-gray-50 dark:bg-gray-900 dark:text-white mb-10 sm:mb-8">
    <!-- Loader -->
    <GlobalLoader v-if="isLoading" />

    <!-- ✅ Active Subscription -->
    <div
        v-else-if="!hasError && subscription?.subscription && !subscription.subscription.expired"
        class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6"
    >
      <!-- Header Card -->
      <div class="bg-gradient-to-r from-green-400 to-emerald-600 dark:from-green-700 dark:to-emerald-800 text-white rounded-3xl p-6 shadow-xl">
        <h2 class="text-3xl font-bold mb-2 flex items-center gap-2">✅ Active Subscription</h2>
        <p class="opacity-90">
          You’re enjoying premium access until
          <strong>{{ subscription.subscription.expire_at }}</strong>.
        </p>
        <p class="mt-2 text-sm">Remaining Days: {{ remainingDays }}</p>
      </div>

      <!-- Details Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow hover:shadow-lg transition">
          <h3 class="text-xl font-semibold mb-2 text-gray-800 dark:text-white">Stage Details</h3>
          <p><strong>Name:</strong> {{ subscription.subscription.stage?.name }}</p>
          <p><strong>Description:</strong> {{ subscription.subscription.stage?.description || 'N/A' }}</p>
        </div>

        <div class="bg-gradient-to-r from-purple-400 to-pink-500 dark:from-purple-700 dark:to-pink-700 rounded-2xl p-5 shadow text-white hover:scale-105 transform transition">
          <h3 class="text-xl font-semibold mb-2">Level Details</h3>
          <p><strong>Name:</strong> {{ subscription.subscription.level?.name }}</p>
          <p><strong>Max Team Capacity:</strong> {{ subscription.subscription.level?.max_team_capacity }}</p>
        </div>

        <div class="bg-gradient-to-r from-blue-400 to-indigo-500 dark:from-blue-700 dark:to-indigo-800 rounded-2xl p-5 shadow text-white lg:col-span-3">
          <h3 class="text-xl font-semibold mb-2">Benefits</h3>
          <ul class="space-y-2">
            <li
                v-for="(status, benefit) in subscription.subscription.stage?.benefits"
                :key="benefit"
                :class="status === 'Active' ? 'text-white dark:text-green-400' : 'line-through text-red-700'"
            >
              {{ benefit }} - {{ status }}
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- ✨ Upgrade / New Subscription Features Cards -->
    <div
        v-else-if="!hasError && subscriptionData?.name"
        class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-8"
    >
      <!-- Hero Section -->
      <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 dark:from-indigo-800 dark:via-purple-800 dark:to-pink-700 rounded-3xl p-6 shadow-xl text-white text-center space-y-4">
        <h2 class="text-3xl font-extrabold flex items-center justify-center gap-2">💎 Upgrade to Premium</h2>
        <p class="opacity-90 text-lg">Unlock exclusive benefits, network growth, and advanced earning tools.</p>
      </div>

      <!-- Feature Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-r from-blue-400 to-indigo-500 dark:from-blue-700 dark:to-indigo-800 rounded-2xl p-5 shadow-lg text-white hover:scale-105 transform transition">
          <h3 class="text-xl font-semibold mb-2">Affiliate Commissions</h3>
          <p class="opacity-90">Earn rewards from your downline and grow your passive income.</p>
        </div>

        <div class="bg-gradient-to-r from-emerald-400 to-teal-500 dark:from-emerald-700 dark:to-teal-800 rounded-2xl p-5 shadow-lg text-white hover:scale-105 transform transition">
          <h3 class="text-xl font-semibold mb-2">Exclusive Benefits</h3>
          <p class="opacity-90">Get premium tools, early access features, and member-only perks.</p>
        </div>

        <div class="bg-gradient-to-r from-orange-400 to-pink-500 dark:from-orange-700 dark:to-pink-700 rounded-2xl p-5 shadow-lg text-white hover:scale-105 transform transition">
          <h3 class="text-xl font-semibold mb-2">Network Growth</h3>
          <p class="opacity-90">Build your influence, connect with others, and scale your reach.</p>
        </div>
      </div>

      <!-- Subscription Info Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow hover:shadow-lg transition">
          <h3 class="text-xl font-semibold mb-2 text-gray-800 dark:text-white">Stage Info</h3>
          <p><strong>Name:</strong> {{ subscriptionData.name }}</p>
          <p><strong>Max Team Capacity:</strong> {{ subscriptionData.max_team_capacity }}</p>
          <p><strong>Price:</strong> <span class="text-green-600 dark:text-green-400 font-semibold">{{ subscriptionData.price }}</span></p>
          <p><strong>Description:</strong> {{ subscriptionData.description }}</p>
        </div>

        <div class="bg-gradient-to-r from-purple-400 to-pink-500 dark:from-purple-700 dark:to-pink-700 rounded-2xl p-5 shadow text-white hover:scale-105 transform transition">
          <h3 class="text-xl font-semibold mb-2">Level Info</h3>
          <p><strong>Name:</strong> {{ subscriptionData.levels[0]?.name }}</p>
          <p><strong>Team Capacity:</strong> {{ subscriptionData.levels[0]?.team_member_limit }}</p>
        </div>
      </div>

      <div class="bg-gradient-to-r from-blue-400 to-indigo-500 dark:from-blue-700 dark:to-indigo-800 rounded-2xl p-5 shadow text-white mt-6">
        <h3 class="text-xl font-semibold mb-2">Benefits</h3>
        <ul class="space-y-2">
          <li
              v-for="(status, benefit) in subscriptionData.benefits"
              :key="benefit"
              :class="status === 'Active' ? 'text-white dark:text-green-400' : 'line-through text-red-700'"
          >
            {{ benefit }} - {{ status }}
          </li>
        </ul>
      </div>

      <!-- Bottom CTA -->
      <div class="sticky bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-6 py-4 shadow-lg space-y-2">
        <p class="text-lg font-semibold text-gray-900 dark:text-white text-center">
          Subscription Price: <span class="text-green-600 dark:text-green-400">{{ subscriptionData.price }}</span>
        </p>
        <button
            @click="handleSubscribe"
            :disabled="isSubmitting"
            class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white text-lg font-semibold rounded-xl shadow transition disabled:opacity-50"
        >
          {{ subscription?.subscription ? 'Renew Now' : 'Subscribe Now' }}
        </button>
      </div>
    </div>

    <!-- 🚫 API Error / No Data -->
    <div v-else class="text-center py-20 text-gray-500 dark:text-gray-400">
      No subscription information found.
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, computed, onMounted } from "vue"
import {useSanctumFetch, useRuntimeConfig, useRouter, useSanctum} from "#imports"
import GlobalLoader from "~/components/GlobalLoader.vue";

definePageMeta({ layout: "dashboard" })

const config = useRuntimeConfig()
const router = useRouter()
const isLoading = useState('pageLoading', () => false)
const isSubmitting = ref(false)
const subscription = ref<any>(null)
const hasError = ref(false) // ✅ new error state
const { user } = useSanctum<User>()

const subscriptionData = reactive({
  name: "",
  url: "",
  description: "",
  price: "",
  max_team_capacity: 0,
  benefits: {} as Record<string, string>,
  levels: [] as any[],
})

const remainingDays = computed(() => {
  if (!subscription.value?.subscription?.expire_at) return 0
  const expireDate = new Date(subscription.value.subscription.expire_at).getTime()
  const today = new Date().getTime()
  return Math.max(0, Math.ceil((expireDate - today) / (1000 * 60 * 60 * 24)))
})

onMounted(async () => {
  try {
    await fetchSubscriptionData()
    if (!subscription.value?.subscription || subscription.value.subscription.expired) {
      await fetchNewLifecycle()
    }
  } catch (e) {
    console.error("Init error", e)
    hasError.value = true
  } finally {
    isLoading.value = false
  }
})

async function fetchNewLifecycle() {
  try {
    const url = `${config.public.apiBase}/lifecycle/subscribable`
    const res = await useSanctumFetch(url, { method: "GET" })
    if (res?.data) Object.assign(subscriptionData, res.data)
    else hasError.value = true
  } catch (e) {
    console.error("Failed to fetch subscription data:", e)
    hasError.value = true
  }
}

async function fetchSubscriptionData() {
  try {
    const url = `${config.public.apiBase}/account/subscription`
    const res = await useSanctumFetch(url, { method: "GET" })
    if (res?.data) subscription.value = res.data
    else hasError.value = true
  } catch (error) {
    console.error("Failed to fetch user subscription:", error)
    hasError.value = true
  }
}

async function handleSubscribe() {
  try {
    isSubmitting.value = true
    let stageId, levelId
    if (subscriptionData?.levels?.length) {
      levelId = subscriptionData.levels[0]?.id
      stageId = subscriptionData.levels[0]?.stage_id || subscriptionData.id
    } else {
      stageId = subscriptionData.stage_id || subscriptionData.id
      levelId = subscriptionData.level_id
    }
    const url = `${config.public.apiBase}/account/subscription`
    const payload = { stage_id: stageId, level_id: levelId }
    const res = await useSanctumFetch(url, { method: "POST", body: payload })
    const response = res?.data
    if (response?.status) {
      if (response?.redirect && response?.redirect_url) {
        window.location.href = response.redirect_url
      } else {
        router.push("/dashboard/subscription/success")
      }
    } else {
      alert(response?.message || "Failed to subscribe.")
    }
  } catch (error) {
    console.error("Subscription request failed:", error)
    alert("Error occurred while subscribing.")
  } finally {
    isSubmitting.value = false
  }
}
</script>
