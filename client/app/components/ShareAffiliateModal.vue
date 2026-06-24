<template>
  <UModal v-model:open="isOpen">
    <template #content>
      <div class="p-6">
        <!-- Header -->
        <div class="text-center mb-6">
          <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <UIcon
              name="i-lucide-gift"
              class="w-8 h-8 text-primary-600 dark:text-primary-400"
            />
          </div>
          <h3 class="text-xl font-bold text-gray-900 dark:text-white">
            Invite & Get Rewards
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Invite friends and earn reward points when they shop
          </p>
        </div>

        <!-- Referral Code -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Your Referral Code
          </label>
          <div class="flex gap-2">
            <div class="flex-1 bg-gray-100 dark:bg-gray-800 rounded-lg px-4 py-3 font-mono text-lg font-bold text-center text-primary-600 dark:text-primary-400 tracking-widest">
              {{ referralCode }}
            </div>
            <UButton
              color="primary"
              variant="soft"
              icon="i-lucide-copy"
              @click="copyCode"
            />
          </div>
        </div>

        <!-- Referral Link -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Your Referral Link
          </label>
          <div class="flex gap-2">
            <UInput
              :model-value="referralLink"
              readonly
              class="flex-1 font-mono text-sm"
            />
            <UButton
              color="primary"
              variant="soft"
              icon="i-lucide-copy"
              @click="copyLink"
            />
          </div>
        </div>

        <!-- Share Buttons -->
        <div class="space-y-3">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Share via
          </label>
          <div class="grid grid-cols-4 gap-3">
            <button
              class="flex flex-col items-center gap-2 p-3 rounded-xl bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors"
              @click="shareVia('whatsapp')"
            >
              <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                <UIcon
                  name="i-lucide-message-circle"
                  class="w-5 h-5 text-white"
                />
              </div>
              <span class="text-xs text-gray-600 dark:text-gray-400">WhatsApp</span>
            </button>

            <button
              class="flex flex-col items-center gap-2 p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors"
              @click="shareVia('facebook')"
            >
              <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                <UIcon
                  name="i-lucide-facebook"
                  class="w-5 h-5 text-white"
                />
              </div>
              <span class="text-xs text-gray-600 dark:text-gray-400">Facebook</span>
            </button>

            <button
              class="flex flex-col items-center gap-2 p-3 rounded-xl bg-sky-50 dark:bg-sky-900/20 hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-colors"
              @click="shareVia('twitter')"
            >
              <div class="w-10 h-10 bg-sky-500 rounded-full flex items-center justify-center">
                <UIcon
                  name="i-lucide-twitter"
                  class="w-5 h-5 text-white"
                />
              </div>
              <span class="text-xs text-gray-600 dark:text-gray-400">Twitter</span>
            </button>

            <button
              class="flex flex-col items-center gap-2 p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors"
              @click="shareVia('telegram')"
            >
              <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                <UIcon
                  name="i-lucide-send"
                  class="w-5 h-5 text-white"
                />
              </div>
              <span class="text-xs text-gray-600 dark:text-gray-400">Telegram</span>
            </button>
          </div>

          <!-- Native Share (if supported) -->
          <UButton
            v-if="canNativeShare"
            block
            color="primary"
            variant="outline"
            icon="i-lucide-share"
            @click="nativeShare"
          >
            More Share Options
          </UButton>
        </div>

        <!-- Stats -->
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
          <div class="grid grid-cols-3 gap-4 text-center">
            <div>
              <div class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ stats.totalReferrals }}
              </div>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                Total Referrals
              </div>
            </div>
            <div>
              <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                {{ stats.activeReferrals }}
              </div>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                Active
              </div>
            </div>
            <div>
              <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                {{ stats.earnings }}
              </div>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                Earned
              </div>
            </div>
          </div>
        </div>

        <!-- Close Button -->
        <div class="mt-6">
          <UButton
            block
            color="neutral"
            variant="ghost"
            @click="isOpen = false"
          >
            Close
          </UButton>
        </div>
      </div>
    </template>
  </UModal>
</template>

<script setup lang="ts">
interface Props {
  open?: boolean
  referralCode: string
  userName?: string
}

const props = withDefaults(defineProps<Props>(), {
  open: false,
  userName: 'Friend'
})

const emit = defineEmits<{
  'update:open': [value: boolean]
}>()

const config = useRuntimeConfig()
const toast = useToast()

const isOpen = computed({
  get: () => props.open,
  set: value => emit('update:open', value)
})

// Generate referral link
const referralLink = computed(() => {
  const baseUrl = config.public.appUrl || (typeof window !== 'undefined' ? window.location.origin : '')
  return `${baseUrl}/auth/register?ref=${props.referralCode}`
})

// Check if native share is supported
const canNativeShare = computed(() => {
  return typeof navigator !== 'undefined' && 'share' in navigator
})

// Stats (can be passed as props or fetched)
const stats = reactive({
  totalReferrals: 0,
  activeReferrals: 0,
  earnings: '₹0'
})

// Share message
const shareMessage = computed(() => {
  return `Join me on Commerinity Pro and start earning! Use my referral code: ${props.referralCode}\n\nSign up here: ${referralLink.value}`
})

// Copy referral code
const copyCode = async () => {
  try {
    await navigator.clipboard.writeText(props.referralCode)
    toast.add({
      title: 'Copied!',
      description: 'Referral code copied to clipboard',
      color: 'success'
    })
  } catch {
    toast.add({
      title: 'Error',
      description: 'Failed to copy code',
      color: 'error'
    })
  }
}

// Copy referral link
const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(referralLink.value)
    toast.add({
      title: 'Copied!',
      description: 'Referral link copied to clipboard',
      color: 'success'
    })
  } catch {
    toast.add({
      title: 'Error',
      description: 'Failed to copy link',
      color: 'error'
    })
  }
}

// Share via social platforms
const shareVia = (platform: string) => {
  const text = encodeURIComponent(shareMessage.value)
  const url = encodeURIComponent(referralLink.value)

  const urls: Record<string, string> = {
    whatsapp: `https://wa.me/?text=${text}`,
    facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${text}`,
    twitter: `https://twitter.com/intent/tweet?text=${text}`,
    telegram: `https://t.me/share/url?url=${url}&text=${text}`
  }

  if (urls[platform]) {
    window.open(urls[platform], '_blank', 'width=600,height=400')
  }
}

// Native share
const nativeShare = async () => {
  if (!navigator.share) return

  try {
    await navigator.share({
      title: 'Join Commerinity Pro',
      text: shareMessage.value,
      url: referralLink.value
    })
  } catch (err) {
    // User cancelled or error
    if ((err as Error).name !== 'AbortError') {
      toast.add({
        title: 'Error',
        description: 'Failed to share',
        color: 'error'
      })
    }
  }
}

// Fetch referral stats
const fetchStats = async () => {
  try {
    const config = useRuntimeConfig()
    const response = await useSanctumFetch<{
      success: boolean
      data: {
        total_referrals: number
        active_referrals: number
        total_earnings_formatted: string
      }
    }>(`${config.public.apiBase}/api/affiliate/stats`)

    if (response?.success) {
      stats.totalReferrals = response.data.total_referrals || 0
      stats.activeReferrals = response.data.active_referrals || 0
      stats.earnings = response.data.total_earnings_formatted || '₹0'
    }
  } catch {
    // Silently fail
  }
}

// Watch for modal open
watch(isOpen, (open) => {
  if (open) {
    fetchStats()
  }
})

onMounted(() => {
  if (isOpen.value) {
    fetchStats()
  }
})
</script>
