<script setup lang="ts">
import type { AdvertisementItem } from '~/composables/useAdvertisements'

interface Props {
  placement: string
  block?: string
  positionType?: string
  pagePath?: string
  useCurrentRoutePath?: boolean
  mode?: 'single' | 'stack'
  limit?: number
  variant?: 'default' | 'compact' | 'splash'
  autoLoad?: boolean
  emptyClass?: string
}

const props = withDefaults(defineProps<Props>(), {
  block: undefined,
  positionType: undefined,
  pagePath: undefined,
  useCurrentRoutePath: true,
  mode: 'single',
  limit: 1,
  variant: 'default',
  autoLoad: true,
  emptyClass: ''
})

const emit = defineEmits<{
  (e: 'loaded', ads: AdvertisementItem[]): void
}>()

const { loading, fetchPlacementAds } = useAdvertisements()
const route = useRoute()
const ads = ref<AdvertisementItem[]>([])

const resolvedPagePath = computed(() => {
  if (props.pagePath && props.pagePath.trim().length > 0) return props.pagePath
  if (props.useCurrentRoutePath) return route.path
  return undefined
})

const visibleAds = computed(() => {
  const limited = props.limit > 0 ? ads.value.slice(0, props.limit) : ads.value
  if (props.mode === 'single') return limited.slice(0, 1)
  return limited
})

const loadAds = async () => {
  ads.value = await fetchPlacementAds(props.placement, {
    block: props.block,
    positionType: props.positionType,
    pagePath: resolvedPagePath.value
  })
  emit('loaded', ads.value)
}

watch(
  () => [props.placement, props.block, props.positionType, resolvedPagePath.value],
  async () => {
    if (props.autoLoad) {
      await loadAds()
    }
  },
  { immediate: true }
)

defineExpose({
  loadAds,
  ads
})
</script>

<template>
  <section>
    <div v-if="loading && !visibleAds.length" class="animate-pulse rounded-xl border border-slate-200/60 bg-slate-100 dark:bg-slate-800 h-20" />

    <div v-else-if="visibleAds.length" class="space-y-3">
      <AdRenderer
        v-for="ad in visibleAds"
        :key="`${ad.id}-${ad.slug}`"
        :ad="ad"
        :variant="variant"
      />
    </div>

    <div v-else :class="emptyClass" />
  </section>
</template>
