<template>
  <div class="community-world-root">
    <!-- Splash Screen (container-sized, not fullscreen) -->
    <transition name="fade">
      <div v-if="showSplash && !worldReady" class="splash-screen" :style="splashBgStyle">
        <div class="splash-overlay"></div>
        <div class="splash-content">
          <div class="splash-logo animate-bounce">
            <div class="text-7xl mb-4">🏰</div>
            <h1 class="text-5xl font-bold text-white mb-2">{{ websiteName }}</h1>
            <p class="text-xl text-white/80">{{ kingdomLeader?.name }}'s Kingdom</p>
          </div>
          <div class="splash-version">v0.0.1</div>
          <button @click="enterWorld" :disabled="isEntering" class="splash-enter-btn">
            <span>{{ isEntering ? 'Entering...' : 'Enter World' }}</span>
            <Icon name="heroicons:arrow-right" class="w-6 h-6" />
          </button>
        </div>
      </div>
    </transition>

    <!-- Teleport Animation (container-sized, not fullscreen) -->
    <transition name="teleport">
      <div v-if="isEntering" class="teleport-screen">
        <div class="teleport-circles">
          <div v-for="i in 5" :key="i" class="teleport-circle" :style="{ animationDelay: (i * 0.2) + 's' }"></div>
        </div>
        <div class="text-center z-10 animate-pulse">
          <div class="text-6xl mb-4">🌍</div>
          <h2 class="text-4xl font-bold text-white mb-2">Teleporting...</h2>
          <p class="text-xl text-white/80">Welcome to {{ websiteName }}</p>
        </div>
      </div>
    </transition>

    <!-- 3D World -->
    <transition name="world-fade">
      <div v-if="worldReady && !isEntering" class="world-wrapper">
        <UserVillage
            :tree-data="processedTreeData"
            :skip-splash="true"
            :auto-enter="true"
            @select-member="handleMemberSelect"
            @upgrade-building="handleUpgradeBuilding"
            @exit-world="handleExitWorld"
        />
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import UserVillage from "~/components/games/UserVillage.vue"
import { useToast, useRuntimeConfig } from '#imports'

interface Member {
  userId: number
  parentId?: number | null
  name: string
  level: string
  referral_code: string
  image?: string
  children?: Member[]
  childrenCount?: number
  depth: number
  hasChildren: boolean
  email?: string
  joinedOn?: string
}

interface Props {
  treeData: Member[]
  splashBackground?: string
}

const props = withDefaults(defineProps<Props>(), {
  splashBackground: ''
})

const emit = defineEmits<{
  (e: 'selectMember', member: Member): void
}>()

const config = useRuntimeConfig()
const toast = useToast()

const showSplash = ref(true)
const isEntering = ref(false)
const worldReady = ref(false)

const websiteName = computed(() => config.public.websiteName || 'Kingdom')
const kingdomLeader = computed(() => props.treeData.find(m => m.depth === 0))
const splashBgStyle = computed(() =>
    props.splashBackground
        ? { backgroundImage: `url(${props.splashBackground})`, backgroundSize: 'cover', backgroundPosition: 'center' }
        : {}
)

// Process tree data
const processedTreeData = computed(() => {
  if (!props.treeData || props.treeData.length === 0) return []

  return props.treeData.map(member => ({
    userId: member.userId,
    parentId: member.parentId || null,
    name: member.name || 'Unknown',
    level: member.level || 'Member',
    referral_code: member.referral_code || '',
    image: member.image || undefined,
    children: member.children || [],
    childrenCount: member.childrenCount || 0,
    depth: member.depth || 0,
    hasChildren: member.hasChildren || false,
    email: member.email,
    joinedOn: member.joinedOn
  }))
})

// Enter world with animation
const enterWorld = () => {
  isEntering.value = true

  setTimeout(() => {
    showSplash.value = false
    worldReady.value = true

    setTimeout(() => {
      isEntering.value = false
    }, 500)
  }, 2000)
}

// Handle exit from world (return to splash)
const handleExitWorld = () => {
  console.log('Exiting world, returning to splash...')
  worldReady.value = false
  showSplash.value = true
}

// Handle member selection
const handleMemberSelect = (member: Member) => {
  emit('selectMember', member)
}

// Handle building upgrade
const handleUpgradeBuilding = async (building: any) => {
  try {
    // Show upgrade confirmation
    const confirmed = confirm(`Upgrade ${building.name} to Level ${building.level + 1}?`)

    if (confirmed) {
      // Call your upgrade API here
      // Example:
      // const response = await useSanctumFetch(`${config.public.apiBase}/buildings/upgrade`, {
      //   method: 'POST',
      //   body: { buildingId: building.id, level: building.level + 1 }
      // })

      toast.success({
        title: 'Building Upgraded!',
        message: `${building.name} upgraded to Level ${building.level + 1}`,
        timeout: 3000
      })
    }
  } catch (error: any) {
    console.error('Upgrade failed:', error)
    toast.error({
      title: 'Upgrade Failed',
      message: error.message || 'Could not upgrade building',
      timeout: 5000
    })
  }
}
</script>

<style scoped>
.community-world-root {
  @apply relative w-full h-full;
  min-height: 600px;
}

/* Splash Screen - Container sized */
.splash-screen {
  @apply absolute inset-0 z-50 flex items-center justify-center bg-cover bg-center rounded-xl overflow-hidden;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.splash-overlay {
  @apply absolute inset-0 bg-black/30;
}

.splash-content {
  @apply relative z-10 text-center;
}

.splash-logo {
  @apply mb-8;
}

.splash-version {
  @apply absolute top-4 right-4 px-3 py-1 bg-white/20 rounded-full text-white text-xs backdrop-blur-sm;
}

.splash-enter-btn {
  @apply flex items-center gap-3 px-8 py-4 rounded-2xl bg-white text-indigo-600 mx-auto;
  @apply font-bold text-lg shadow-2xl hover:scale-105 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed;
}

/* Teleport Animation - Container sized */
.teleport-screen {
  @apply absolute inset-0 z-50 flex items-center justify-center rounded-xl overflow-hidden;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%, #f093fb 100%);
}

.teleport-circles {
  @apply absolute inset-0 flex items-center justify-center;
}

.teleport-circle {
  @apply absolute w-32 h-32 rounded-full border-4 border-white/30;
  animation: ripple 2s infinite ease-out;
}

@keyframes ripple {
  0% {
    transform: scale(0);
    opacity: 1;
  }
  100% {
    transform: scale(4);
    opacity: 0;
  }
}

/* World Wrapper */
.world-wrapper {
  @apply w-full h-full relative;
}

.world-wrapper :deep(.world-container) {
  @apply w-full h-full;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.teleport-enter-active,
.teleport-leave-active {
  transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.teleport-enter-from,
.teleport-leave-to {
  opacity: 0;
  transform: scale(0.8);
}

.world-fade-enter-active {
  transition: all 0.5s ease-out;
}

.world-fade-enter-from {
  opacity: 0;
  transform: scale(1.1);
}

/* Smooth theme transitions */
* {
  transition-property: background-color, border-color, color;
  transition-duration: 300ms;
  transition-timing-function: ease-in-out;
}
</style>
