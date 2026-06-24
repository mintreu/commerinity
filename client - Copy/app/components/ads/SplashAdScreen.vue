<script setup lang="ts">
import type { AdvertisementItem } from '~/composables/useAdvertisements'

interface Props {
  enabled?: boolean
  placement?: string
  positionType?: string
  block?: string
  durationMs?: number
  skippable?: boolean
  oncePerSession?: boolean
  sessionKey?: string
}

const props = withDefaults(defineProps<Props>(), {
  enabled: true,
  placement: 'popup_modal',
  positionType: 'popup',
  block: undefined,
  durationMs: 5000,
  skippable: true,
  oncePerSession: true,
  sessionKey: 'ad-splash-shown'
})

const open = ref(false)
const ad = ref<AdvertisementItem | null>(null)
let timer: ReturnType<typeof setTimeout> | null = null

const { fetchPlacementAds } = useAdvertisements()
const route = useRoute()

const closeSplash = () => {
  open.value = false
  if (timer) {
    clearTimeout(timer)
    timer = null
  }
  if (process.client && props.oncePerSession) {
    sessionStorage.setItem(props.sessionKey, '1')
  }
}

onMounted(async () => {
  if (!props.enabled || !process.client) return

  if (props.oncePerSession && sessionStorage.getItem(props.sessionKey) === '1') {
    return
  }

  const ads = await fetchPlacementAds(props.placement, {
    block: props.block,
    positionType: props.positionType,
    pagePath: route.path
  })

  if (!ads.length) return

  ad.value = ads[0]
  open.value = true

  if (props.durationMs > 0) {
    timer = setTimeout(() => {
      closeSplash()
    }, props.durationMs)
  }
})

onBeforeUnmount(() => {
  if (timer) clearTimeout(timer)
})
</script>

<template>
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="open && ad"
      class="fixed inset-0 z-[140] flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
    >
      <div class="relative w-full max-w-2xl">
        <button
          v-if="skippable"
          type="button"
          class="absolute -top-3 -right-3 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-800 shadow"
          @click="closeSplash"
        >
          <UIcon name="i-lucide-x" class="h-5 w-5" />
        </button>

        <AdRenderer :ad="ad" variant="splash" />
      </div>
    </div>
  </Transition>
</template>
