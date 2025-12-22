<template>
  <div class="profile-card">
    <!-- Header -->
    <div class="card-header">
      <div class="header-icon">
        <Icon name="mdi:account-circle" class="w-6 h-6 text-white" />
      </div>
      <div class="flex-1 min-w-0">
        <h3 class="header-title">{{ headerTitle }}</h3>
        <p class="header-subtitle">{{ headerSubtitle }}</p>
      </div>
      <NuxtLink v-if="showEditButton" to="/dashboard/account/edit" class="btn-edit">
        <Icon name="mdi:account-edit" class="w-4 h-4" />
        <span>Edit</span>
      </NuxtLink>
    </div>

    <!-- Body -->
    <div class="card-body">
      <!-- Avatar Section -->
      <div class="avatar-section">
        <div class="avatar-wrap">
          <AvatarUploader v-if="useAvatarUploader" class="!w-32 !h-32" />
          <img v-else :src="user?.avatar || '/default-avatar.png'" :alt="user?.name || 'User'" class="avatar-img" @error="handleAvatarError" />
          <div class="avatar-status" :class="getStatusDotClass()"></div>
        </div>

        <h2 class="user-name">{{ user?.name || 'User Name' }}</h2>
        <p class="user-email">{{ user?.email || 'user@example.com' }}</p>

        <!-- Verification Badges -->
        <div v-if="showVerificationBadges" class="badges">
          <div class="badge" :class="user?.email_verified ? 'badge-success' : 'badge-warning'">
            <Icon :name="user?.email_verified ? 'mdi:email-check' : 'mdi:email-alert'" class="w-3 h-3" />
            <span>Email {{ user?.email_verified ? 'Verified' : 'Unverified' }}</span>
          </div>
          <div class="badge" :class="user?.mobile_verified ? 'badge-success' : 'badge-warning'">
            <Icon :name="user?.mobile_verified ? 'mdi:phone-check' : 'mdi:phone-alert'" class="w-3 h-3" />
            <span>Mobile {{ user?.mobile_verified ? 'Verified' : 'Unverified' }}</span>
          </div>
        </div>

        <!-- User Type & Status -->
        <div class="badges">
          <div class="badge badge-white" :class="getBadgeTypeClass(user?.type)">
            <Icon :name="getTypeIcon(user?.type)" class="w-3 h-3" />
            <span class="capitalize">{{ user?.type || 'User' }}</span>
          </div>
          <div class="badge badge-white" :class="getBadgeStatusClass(user?.status)">
            <Icon :name="getStatusIcon(user?.status)" class="w-3 h-3" />
            <span class="capitalize">{{ user?.status || 'Active' }}</span>
          </div>
        </div>
      </div>

      <!-- Referral Section -->
      <div v-if="showReferralSection && user?.status === 'Subscribed'" class="referral-section">
        <h4 class="section-title">
          <Icon name="mdi:share-variant" class="w-4 h-4" />
          <span>Your Referral Link</span>
        </h4>
        <div class="referral-input-wrap">
          <div @click="copyReferral" class="referral-input" title="Click to copy">{{ referralLink }}</div>
          <button @click="copyReferral" class="btn-copy">
            <Icon name="mdi:content-copy" class="w-4 h-4" />
            <span>Copy</span>
          </button>
        </div>
        <p class="referral-hint">Share this link to invite friends and earn rewards.</p>
      </div>

      <!-- Profile Details -->
      <div class="details-grid">
        <div class="detail-item">
          <Icon name="mdi:phone" class="detail-icon" />
          <div class="detail-content">
            <p class="detail-label">Mobile</p>
            <p class="detail-value">{{ user?.mobile || 'Not set' }}</p>
          </div>
        </div>

        <div class="detail-item">
          <Icon name="mdi:gender-male-female" class="detail-icon" />
          <div class="detail-content">
            <p class="detail-label">Gender</p>
            <p class="detail-value capitalize">{{ user?.gender || 'Not set' }}</p>
          </div>
        </div>

        <div class="detail-item">
          <Icon name="mdi:calendar" class="detail-icon" />
          <div class="detail-content">
            <p class="detail-label">Date of Birth</p>
            <p class="detail-value">{{ formatDate(user?.dob) || 'Not set' }}</p>
          </div>
        </div>

        <div v-if="showExtendedDetails" class="detail-item">
          <Icon name="mdi:account-supervisor" class="detail-icon" />
          <div class="detail-content">
            <p class="detail-label">Referral Code</p>
            <p class="detail-value font-mono">{{ user?.referral_code || 'N/A' }}</p>
          </div>
        </div>

        <div v-if="showExtendedDetails" class="detail-item">
          <Icon name="mdi:account-tree" class="detail-icon" />
          <div class="detail-content">
            <p class="detail-label">Parent</p>
            <p class="detail-value">{{ user?.hasParent ? user?.parent : 'None' }}</p>
          </div>
        </div>

        <div v-if="showExtendedDetails" class="detail-item">
          <Icon name="mdi:star" class="detail-icon" />
          <div class="detail-content">
            <p class="detail-label">Level</p>
            <p class="detail-value">{{ user?.hasLevel ? user?.level_id : 'Not assigned' }}</p>
          </div>
        </div>

        <div v-if="user?.bio" class="detail-item-full">
          <Icon name="mdi:text-account" class="detail-icon" />
          <div class="detail-content">
            <p class="detail-label">Bio</p>
            <p class="detail-value italic">{{ user.bio }}</p>
          </div>
        </div>
      </div>

      <!-- Social Share -->
      <div v-if="showSocialShare" class="social-section">
        <h4 class="section-title">
          <Icon name="mdi:share" class="w-4 h-4" />
          <span>Share Profile</span>
        </h4>
        <div class="social-grid">
          <button @click="shareProfile('whatsapp')" class="social-btn social-whatsapp">
            <Icon name="mdi:whatsapp" class="w-4 h-4" />
            <span class="hidden sm:inline">WhatsApp</span>
          </button>
          <button @click="shareProfile('twitter')" class="social-btn social-twitter">
            <Icon name="mdi:twitter" class="w-4 h-4" />
            <span class="hidden sm:inline">Twitter</span>
          </button>
          <button @click="shareProfile('facebook')" class="social-btn social-facebook">
            <Icon name="mdi:facebook" class="w-4 h-4" />
            <span class="hidden sm:inline">Facebook</span>
          </button>
          <button @click="shareProfile('copy')" class="social-btn social-copy">
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

const { $toast } = useNuxtApp()

const referralLink = computed(() => {
  if (!props.user?.referral_code || !process.client) return ''
  return `${window.location.origin}/register?referral=${props.user.referral_code}`
})

function getBadgeTypeClass(type?: string) {
  const map = { admin: 'from-red-600 to-red-700', user: 'from-blue-600 to-blue-700', member: 'from-purple-600 to-purple-700', premium: 'from-amber-500 to-amber-600' }
  return map[type?.toLowerCase() as keyof typeof map] || 'from-slate-600 to-slate-700'
}

function getBadgeStatusClass(status?: string) {
  const map = { active: 'from-green-600 to-green-700', subscribed: 'from-green-600 to-green-700', inactive: 'from-amber-500 to-amber-600', draft: 'from-amber-500 to-amber-600', suspended: 'from-red-600 to-red-700', blocked: 'from-red-600 to-red-700', pending: 'from-blue-600 to-blue-700' }
  return map[status?.toLowerCase() as keyof typeof map] || 'from-green-600 to-green-700'
}

function getTypeIcon(type?: string) {
  const map = { admin: 'mdi:shield-crown', user: 'mdi:account', member: 'mdi:account-star', premium: 'mdi:crown' }
  return map[type?.toLowerCase() as keyof typeof map] || 'mdi:account'
}

function getStatusIcon(status?: string) {
  const map = { active: 'mdi:check-circle', subscribed: 'mdi:check-circle', inactive: 'mdi:clock', draft: 'mdi:clock', suspended: 'mdi:alert-circle', blocked: 'mdi:alert-circle', pending: 'mdi:help-circle' }
  return map[status?.toLowerCase() as keyof typeof map] || 'mdi:check-circle'
}

function getStatusDotClass() {
  const status = props.user?.status?.toLowerCase()
  const map = { active: 'bg-green-500', subscribed: 'bg-green-500', inactive: 'bg-amber-500', draft: 'bg-amber-500', suspended: 'bg-red-500', blocked: 'bg-red-500', pending: 'bg-blue-500' }
  return map[status as keyof typeof map] || 'bg-green-500'
}

function formatDate(dateString?: string) {
  if (!dateString) return null
  try { return new Date(dateString).toLocaleDateString() } catch { return dateString }
}

function handleAvatarError(event: Event) {
  (event.target as HTMLImageElement).src = '/default-avatar.png'
}

function copyReferral() {
  if (!referralLink.value || !process.client) return
  navigator.clipboard.writeText(referralLink.value).then(() => {
    if ($toast?.success) $toast.success('Copied!', 'Referral link copied to clipboard.')
  }).catch(() => {
    if ($toast?.error) $toast.error('Error', 'Failed to copy referral link.')
  })
}

function shareProfile(platform: string) {
  if (!process.client) return
  const url = `${window.location.origin}/community/account/${props.user?.uuid || ''}`
  const actions = {
    whatsapp: () => window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(url)}`, '_blank'),
    twitter: () => window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}`, '_blank'),
    facebook: () => window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank'),
    copy: () => navigator.clipboard.writeText(url).then(() => {
      if ($toast?.success) $toast.success('Copied!', 'Profile link copied.')
    })
  }
  actions[platform as keyof typeof actions]?.()
}
</script>

<style scoped>
.profile-card {
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 1rem;
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
  overflow: hidden;
  transition: all 0.3s;
}

.profile-card:hover {
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
  transform: translateY(-0.25rem);
}

.dark .profile-card {
  background: rgba(15,23,42,0.95);
  border-color: rgba(71,85,105,0.5);
}

/* Header */
.card-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  border-bottom: 1px solid rgba(226,232,240,0.3);
}

.dark .card-header {
  border-color: rgba(51,65,85,0.3);
}

.header-icon {
  width: 3rem;
  height: 3rem;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
  flex-shrink: 0;
}

.header-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: rgb(15,23,42);
  margin-bottom: 0.25rem;
}

.dark .header-title {
  color: white;
}

.header-subtitle {
  font-size: 0.875rem;
  color: rgb(71,85,105);
}

.dark .header-subtitle {
  color: rgb(148,163,184);
}

.btn-edit {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: transparent;
  color: rgb(71,85,105);
  border: 1px solid rgb(203,213,225);
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  transition: all 0.2s;
  flex-shrink: 0;
  text-decoration: none;
}

.btn-edit:hover {
  background: rgb(241,245,249);
  color: rgb(37,99,235);
  border-color: rgb(147,197,253);
  transform: translateY(-0.125rem);
}

.dark .btn-edit {
  color: rgb(148,163,184);
  border-color: rgb(71,85,105);
}

.dark .btn-edit:hover {
  background: rgb(30,41,59);
  color: rgb(96,165,250);
  border-color: rgb(59,130,246);
}

/* Body */
.card-body {
  padding: 1.5rem;
}

/* Avatar Section */
.avatar-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  margin-bottom: 2rem;
}

.avatar-wrap {
  position: relative;
  margin-bottom: 1rem;
}

.avatar-img {
  width: 8rem;
  height: 8rem;
  border-radius: 9999px;
  border: 4px solid rgb(59,130,246);
  object-fit: cover;
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
  transition: transform 0.3s;
}

.avatar-img:hover {
  transform: scale(1.05);
}

.avatar-status {
  position: absolute;
  bottom: 0.5rem;
  right: 0.5rem;
  width: 1.25rem;
  height: 1.25rem;
  border-radius: 9999px;
  border: 3px solid white;
  animation: pulse 2s ease-in-out infinite;
}

.dark .avatar-status {
  border-color: rgb(15,23,42);
}

.user-name {
  font-size: 1.5rem;
  font-weight: 700;
  color: rgb(15,23,42);
  margin-bottom: 0.5rem;
}

.dark .user-name {
  color: white;
}

.user-email {
  font-size: 0.875rem;
  color: rgb(71,85,105);
  margin-bottom: 1rem;
}

.dark .user-email {
  color: rgb(148,163,184);
}

/* Badges */
.badges {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  justify-content: center;
  margin-bottom: 0.5rem;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-success {
  background: rgba(16,185,129,0.1);
  color: rgb(5,150,105);
}

.dark .badge-success {
  background: rgba(16,185,129,0.3);
  color: rgb(52,211,153);
}

.badge-warning {
  background: rgba(245,158,11,0.1);
  color: rgb(217,119,6);
}

.dark .badge-warning {
  background: rgba(245,158,11,0.3);
  color: rgb(251,191,36);
}

.badge-white {
  background: linear-gradient(135deg, var(--tw-gradient-stops));
  color: white;
}

/* Referral Section */
.referral-section {
  margin-bottom: 2rem;
  padding: 1rem;
  background: rgba(59,130,246,0.05);
  border: 1px solid rgba(59,130,246,0.2);
  border-radius: 0.75rem;
}

.dark .referral-section {
  background: rgba(59,130,246,0.1);
  border-color: rgba(59,130,246,0.3);
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: rgb(51,65,85);
  margin-bottom: 0.75rem;
}

.dark .section-title {
  color: rgb(203,213,225);
}

.referral-input-wrap {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.referral-input {
  flex: 1;
  padding: 0.5rem 0.75rem;
  background: rgba(255,255,255,0.8);
  border: 1px solid rgba(203,213,225,0.5);
  border-radius: 0.5rem;
  font-size: 0.75rem;
  color: rgb(51,65,85);
  cursor: pointer;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  transition: all 0.2s;
}

.referral-input:hover {
  background: white;
  border-color: rgb(59,130,246);
}

.dark .referral-input {
  background: rgba(51,65,85,0.8);
  border-color: rgba(71,85,105,0.5);
  color: rgb(203,213,225);
}

.dark .referral-input:hover {
  background: rgb(51,65,85);
}

.btn-copy {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.5rem 0.75rem;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  color: white;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
  transition: all 0.2s;
}

.btn-copy:hover {
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
  transform: translateY(-0.125rem);
}

.referral-hint {
  font-size: 0.75rem;
  color: rgb(71,85,105);
  text-align: center;
}

.dark .referral-hint {
  color: rgb(148,163,184);
}

/* Details Grid */
.details-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: rgba(248,250,252,0.5);
  border: 1px solid rgba(226,232,240,0.5);
  border-radius: 0.75rem;
  transition: all 0.2s;
}

.detail-item:hover {
  background: rgba(241,245,249,0.5);
  transform: translateX(0.25rem);
}

.dark .detail-item {
  background: rgba(30,41,59,0.5);
  border-color: rgba(51,65,85,0.5);
}

.dark .detail-item:hover {
  background: rgb(30,41,59);
}

.detail-item-full {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.75rem;
  background: rgba(248,250,252,0.5);
  border: 1px solid rgba(226,232,240,0.5);
  border-radius: 0.75rem;
}

.dark .detail-item-full {
  background: rgba(30,41,59,0.5);
  border-color: rgba(51,65,105,0.5);
}

.detail-icon {
  width: 1.25rem;
  height: 1.25rem;
  color: rgb(71,85,105);
  flex-shrink: 0;
}

.dark .detail-icon {
  color: rgb(148,163,184);
}

.detail-content {
  flex: 1;
  min-width: 0;
}

.detail-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: rgb(71,85,105);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.dark .detail-label {
  color: rgb(148,163,184);
}

.detail-value {
  font-size: 0.875rem;
  color: rgb(15,23,42);
  font-weight: 500;
}

.dark .detail-value {
  color: white;
}

/* Social Section */
.social-section {
  padding: 1rem;
  background: rgba(248,250,252,0.5);
  border: 1px solid rgba(226,232,240,0.5);
  border-radius: 0.75rem;
}

.dark .social-section {
  background: rgba(30,41,59,0.5);
  border-color: rgba(51,65,85,0.5);
}

.social-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
}

@media (min-width: 640px) {
  .social-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

.social-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
  transition: all 0.2s;
}

.social-btn:hover {
  transform: translateY(-0.125rem);
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
}

.social-whatsapp {
  background: linear-gradient(135deg, #22c55e, #16a34a);
}

.social-twitter {
  background: linear-gradient(135deg, #60a5fa, #3b82f6);
}

.social-facebook {
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

.social-copy {
  background: linear-gradient(135deg, #64748b, #475569);
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
</style>
