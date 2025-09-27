<!-- components/ProfileCard.vue -->
<template>
  <div
      ref="profileCard"
      class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 overflow-hidden"
  >
    <!-- Card Header -->
    <div class="flex items-center gap-4 p-6 border-b border-slate-200/30 dark:border-slate-700/30">
      <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
        <Icon name="mdi:account-circle" class="w-6 h-6 text-white" />
      </div>

      <div class="flex-1">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">{{ headerTitle }}</h3>
        <p class="text-sm text-slate-600 dark:text-slate-400">{{ headerSubtitle }}</p>
      </div>

      <div v-if="showEditButton" class="flex-shrink-0">
        <NuxtLink
            to="/dashboard/account/edit"
            class="inline-flex items-center gap-2 px-3 py-2 bg-transparent hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 border border-slate-300 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500 rounded-lg text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5"
        >
          <Icon name="mdi:account-edit" class="w-4 h-4" />
          <span>Edit</span>
        </NuxtLink>
      </div>
    </div>

    <!-- Card Body -->
    <div class="p-6">

      <!-- Profile Avatar Section -->
      <div class="flex flex-col items-center text-center mb-8">
        <div class="relative mb-4">
          <!-- Avatar Glow Effect -->
          <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full opacity-0 group-hover:opacity-40 blur-lg transition-opacity duration-300"></div>

          <!-- Avatar Container -->
          <div class="relative group">
            <!-- Use AvatarUploader or static image -->
            <AvatarUploader v-if="useAvatarUploader" class="!w-32 !h-32" />
            <img
                v-else
                :src="user?.avatar || '/default-avatar.png'"
                :alt="user?.name || 'User'"
                class="w-32 h-32 rounded-full border-4 border-blue-500 object-cover shadow-lg transition-transform duration-300 hover:scale-105 relative z-10"
                @error="handleAvatarError"
            />

            <!-- Status Indicator -->
            <div class="absolute bottom-2 right-2 z-20">
              <div
                  class="w-5 h-5 rounded-full border-3 border-white dark:border-slate-900 animate-pulse"
                  :class="getStatusDotClass()"
              ></div>
            </div>
          </div>
        </div>

        <div class="w-full">
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">{{ user?.name || 'User Name' }}</h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">{{ user?.email || 'user@example.com' }}</p>

          <!-- Verification Badges -->
          <div v-if="showVerificationBadges" class="flex gap-2 flex-wrap justify-center mb-2">
            <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold" :class="user?.email_verified ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'">
              <Icon :name="user?.email_verified ? 'mdi:email-check' : 'mdi:email-alert'" class="w-3 h-3" />
              <span>Email {{ user?.email_verified ? 'Verified' : 'Unverified' }}</span>
            </div>
            <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold" :class="user?.mobile_verified ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'">
              <Icon :name="user?.mobile_verified ? 'mdi:phone-check' : 'mdi:phone-alert'" class="w-3 h-3" />
              <span>Mobile {{ user?.mobile_verified ? 'Verified' : 'Unverified' }}</span>
            </div>
          </div>

          <!-- User Type & Status Badges -->
          <div class="flex gap-2 flex-wrap justify-center">
            <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold text-white capitalize" :class="getBadgeTypeClass(user?.type)">
              <Icon :name="getTypeIcon(user?.type)" class="w-3 h-3" />
              <span>{{ user?.type || 'User' }}</span>
            </div>
            <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold text-white capitalize" :class="getBadgeStatusClass(user?.status)">
              <Icon :name="getStatusIcon(user?.status)" class="w-3 h-3" />
              <span>{{ user?.status || 'Active' }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Referral Section -->
      <div v-if="showReferralSection && user?.status === 'Subscribed'" class="mb-8 p-4 bg-blue-50/50 dark:bg-blue-900/20 border border-blue-200/50 dark:border-blue-800/50 rounded-xl">
        <h4 class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
          <Icon name="mdi:share-variant" class="w-4 h-4" />
          <span>Your Referral Link</span>
        </h4>

        <div class="flex gap-2 mb-2">
          <div
              @click="copyReferral"
              class="flex-1 px-3 py-2 bg-white/80 dark:bg-slate-700/80 hover:bg-white dark:hover:bg-slate-700 border border-slate-300/50 dark:border-slate-600/50 hover:border-blue-500 rounded-lg text-xs text-slate-700 dark:text-slate-300 cursor-pointer transition-all duration-200 overflow-hidden text-ellipsis whitespace-nowrap"
              title="Click to copy"
          >
            {{ referralLink }}
          </div>
          <button
              @click="copyReferral"
              class="flex items-center gap-1 px-3 py-2 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white rounded-lg text-xs font-semibold shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5"
          >
            <Icon name="mdi:content-copy" class="w-4 h-4" />
            <span>Copy</span>
          </button>
        </div>

        <p class="text-xs text-slate-600 dark:text-slate-400 text-center">
          Share this link to invite friends and earn rewards.
        </p>
      </div>

      <!-- Profile Details -->
      <div class="space-y-4 mb-8">
        <div class="flex items-center gap-3 p-3 bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-100/50 dark:hover:bg-slate-800 rounded-xl border border-slate-200/50 dark:border-slate-700/50 transition-all duration-200 hover:translate-x-1">
          <Icon name="mdi:phone" class="w-5 h-5 text-slate-600 dark:text-slate-400 flex-shrink-0" />
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Mobile</p>
            <p class="text-sm text-slate-900 dark:text-white font-medium">{{ user?.mobile || 'Not set' }}</p>
          </div>
        </div>

        <div class="flex items-center gap-3 p-3 bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-100/50 dark:hover:bg-slate-800 rounded-xl border border-slate-200/50 dark:border-slate-700/50 transition-all duration-200 hover:translate-x-1">
          <Icon name="mdi:gender-male-female" class="w-5 h-5 text-slate-600 dark:text-slate-400 flex-shrink-0" />
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Gender</p>
            <p class="text-sm text-slate-900 dark:text-white font-medium capitalize">{{ user?.gender || 'Not set' }}</p>
          </div>
        </div>

        <div class="flex items-center gap-3 p-3 bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-100/50 dark:hover:bg-slate-800 rounded-xl border border-slate-200/50 dark:border-slate-700/50 transition-all duration-200 hover:translate-x-1">
          <Icon name="mdi:calendar" class="w-5 h-5 text-slate-600 dark:text-slate-400 flex-shrink-0" />
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Date of Birth</p>
            <p class="text-sm text-slate-900 dark:text-white font-medium">{{ formatDate(user?.dob) || 'Not set' }}</p>
          </div>
        </div>

        <div v-if="showExtendedDetails" class="flex items-center gap-3 p-3 bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-100/50 dark:hover:bg-slate-800 rounded-xl border border-slate-200/50 dark:border-slate-700/50 transition-all duration-200 hover:translate-x-1">
          <Icon name="mdi:account-supervisor" class="w-5 h-5 text-slate-600 dark:text-slate-400 flex-shrink-0" />
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Referral Code</p>
            <p class="text-sm text-slate-900 dark:text-white font-medium font-mono">{{ user?.referral_code || 'N/A' }}</p>
          </div>
        </div>

        <div v-if="showExtendedDetails" class="flex items-center gap-3 p-3 bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-100/50 dark:hover:bg-slate-800 rounded-xl border border-slate-200/50 dark:border-slate-700/50 transition-all duration-200 hover:translate-x-1">
          <Icon name="mdi:account-tree" class="w-5 h-5 text-slate-600 dark:text-slate-400 flex-shrink-0" />
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Parent</p>
            <p class="text-sm text-slate-900 dark:text-white font-medium">{{ user?.hasParent ? user?.parent : 'None' }}</p>
          </div>
        </div>

        <div v-if="showExtendedDetails" class="flex items-center gap-3 p-3 bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-100/50 dark:hover:bg-slate-800 rounded-xl border border-slate-200/50 dark:border-slate-700/50 transition-all duration-200 hover:translate-x-1">
          <Icon name="mdi:star" class="w-5 h-5 text-slate-600 dark:text-slate-400 flex-shrink-0" />
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Level</p>
            <p class="text-sm text-slate-900 dark:text-white font-medium">{{ user?.hasLevel ? user?.level_id : 'Not assigned' }}</p>
          </div>
        </div>

        <div v-if="user?.bio" class="p-3 bg-slate-50/50 dark:bg-slate-800/50 rounded-xl border border-slate-200/50 dark:border-slate-700/50">
          <div class="flex items-start gap-3">
            <Icon name="mdi:text-account" class="w-5 h-5 text-slate-600 dark:text-slate-400 flex-shrink-0 mt-0.5" />
            <div class="flex-1">
              <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Bio</p>
              <p class="text-sm text-slate-900 dark:text-white italic leading-relaxed">{{ user.bio }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Social Share Section -->
      <div v-if="showSocialShare" class="p-4 bg-slate-50/50 dark:bg-slate-800/50 rounded-xl border border-slate-200/50 dark:border-slate-700/50">
        <h4 class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
          <Icon name="mdi:share" class="w-4 h-4" />
          <span>Share Profile</span>
        </h4>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
          <button
              @click="shareProfile('whatsapp')"
              class="flex items-center justify-center gap-2 px-3 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg"
          >
            <Icon name="mdi:whatsapp" class="w-4 h-4" />
            <span class="hidden sm:inline">WhatsApp</span>
          </button>

          <button
              @click="shareProfile('twitter')"
              class="flex items-center justify-center gap-2 px-3 py-2 bg-gradient-to-r from-blue-400 to-blue-500 hover:from-blue-500 hover:to-blue-600 text-white rounded-lg text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg"
          >
            <Icon name="mdi:twitter" class="w-4 h-4" />
            <span class="hidden sm:inline">Twitter</span>
          </button>

          <button
              @click="shareProfile('facebook')"
              class="flex items-center justify-center gap-2 px-3 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg"
          >
            <Icon name="mdi:facebook" class="w-4 h-4" />
            <span class="hidden sm:inline">Facebook</span>
          </button>

          <button
              @click="shareProfile('copy')"
              class="flex items-center justify-center gap-2 px-3 py-2 bg-gradient-to-r from-slate-600 to-slate-700 hover:from-slate-700 hover:to-slate-800 text-white rounded-lg text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg"
          >
            <Icon name="mdi:link" class="w-4 h-4" />
            <span class="hidden sm:inline">Copy Link</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import AvatarUploader from '~/components/account/AvatarUploader.vue'

// Props
interface Props {
  user?: any
  headerTitle?: string
  headerSubtitle?: string
  showEditButton?: boolean
  showVerificationBadges?: boolean
  showReferralSection?: boolean
  showExtendedDetails?: boolean
  showSocialShare?: boolean
  useAvatarUploader?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  headerTitle: 'Profile Overview',
  headerSubtitle: 'Your account details',
  showEditButton: false,
  showVerificationBadges: false,
  showReferralSection: false,
  showExtendedDetails: false,
  showSocialShare: false,
  useAvatarUploader: false
})

// Composables
const { $toast } = useNuxtApp()

// Computed
const referralLink = computed(() => {
  if (!props.user?.referral_code) return ''
  if (process.client) {
    return `${window.location.origin}/register?referral=${props.user.referral_code}`
  }
  return ''
})

// Methods
function getBadgeTypeClass(type?: string) {
  const classes = {
    'admin': 'bg-gradient-to-r from-red-600 to-red-700',
    'user': 'bg-gradient-to-r from-blue-600 to-blue-700',
    'member': 'bg-gradient-to-r from-purple-600 to-purple-700',
    'premium': 'bg-gradient-to-r from-amber-500 to-amber-600'
  }
  return classes[type?.toLowerCase() as keyof typeof classes] || 'bg-gradient-to-r from-slate-600 to-slate-700'
}

function getBadgeStatusClass(status?: string) {
  const classes = {
    'active': 'bg-gradient-to-r from-green-600 to-green-700',
    'subscribed': 'bg-gradient-to-r from-green-600 to-green-700',
    'inactive': 'bg-gradient-to-r from-amber-500 to-amber-600',
    'draft': 'bg-gradient-to-r from-amber-500 to-amber-600',
    'suspended': 'bg-gradient-to-r from-red-600 to-red-700',
    'blocked': 'bg-gradient-to-r from-red-600 to-red-700',
    'pending': 'bg-gradient-to-r from-blue-600 to-blue-700'
  }
  return classes[status?.toLowerCase() as keyof typeof classes] || 'bg-gradient-to-r from-green-600 to-green-700'
}

function getTypeIcon(type?: string) {
  const icons = {
    'admin': 'mdi:shield-crown',
    'user': 'mdi:account',
    'member': 'mdi:account-star',
    'premium': 'mdi:crown'
  }
  return icons[type?.toLowerCase() as keyof typeof icons] || 'mdi:account'
}

function getStatusIcon(status?: string) {
  const icons = {
    'active': 'mdi:check-circle',
    'subscribed': 'mdi:check-circle',
    'inactive': 'mdi:clock',
    'draft': 'mdi:clock',
    'suspended': 'mdi:alert-circle',
    'blocked': 'mdi:alert-circle',
    'pending': 'mdi:help-circle'
  }
  return icons[status?.toLowerCase() as keyof typeof icons] || 'mdi:check-circle'
}

function getStatusDotClass() {
  const status = props.user?.status?.toLowerCase()
  const classes = {
    'active': 'bg-green-500',
    'subscribed': 'bg-green-500',
    'inactive': 'bg-amber-500',
    'draft': 'bg-amber-500',
    'suspended': 'bg-red-500',
    'blocked': 'bg-red-500',
    'pending': 'bg-blue-500'
  }
  return classes[status as keyof typeof classes] || 'bg-green-500'
}

function formatDate(dateString?: string) {
  if (!dateString) return null
  try {
    return new Date(dateString).toLocaleDateString()
  } catch {
    return dateString
  }
}

function handleAvatarError(event: Event) {
  const target = event.target as HTMLImageElement
  target.src = '/default-avatar.png'
}

function copyReferral() {
  if (!referralLink.value || !process.client) return

  navigator.clipboard.writeText(referralLink.value)
      .then(() => {
        if ($toast && $toast.success) {
          $toast.success('Copied!', 'Referral link copied to clipboard.')
        } else {
          console.log('Referral link copied to clipboard')
        }
      })
      .catch(() => {
        if ($toast && $toast.error) {
          $toast.error('Error', 'Failed to copy referral link.')
        } else {
          console.error('Failed to copy referral link')
        }
      })
}

function shareProfile(platform: string) {
  if (!process.client) return

  const url = `${window.location.origin}/community/account/${props.user?.uuid || ''}`

  switch (platform) {
    case 'whatsapp':
      window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(url)}`, '_blank')
      break
    case 'twitter':
      window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}`, '_blank')
      break
    case 'facebook':
      window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank')
      break
    case 'copy':
      navigator.clipboard.writeText(url)
          .then(() => {
            if ($toast && $toast.success) {
              $toast.success('Copied!', 'Profile link copied.')
            } else {
              console.log('Profile link copied')
            }
          })
          .catch(() => {
            if ($toast && $toast.error) {
              $toast.error('Error', 'Failed to copy profile link.')
            } else {
              console.error('Failed to copy profile link')
            }
          })
      break
  }
}
</script>
