<script setup lang="ts">
import type { AdvertisementItem } from '~/composables/useAdvertisements'

interface Props {
  ad: AdvertisementItem
  variant?: 'default' | 'compact' | 'splash'
  trackClick?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  trackClick: true
})

const emit = defineEmits<{
  (e: 'clicked', ad: AdvertisementItem): void
}>()

const { recordAdClick } = useAdvertisements()
const thirdPartyHost = ref<HTMLElement | null>(null)

const isNativeLike = computed(() => props.ad.type === 'native' || props.ad.type === 'affiliate')
const isThirdParty = computed(() => !isNativeLike.value)

const executeThirdPartyCode = (ad: AdvertisementItem) => {
  if (!process.client || !thirdPartyHost.value) return

  const code = ad.third_party?.code || ad.ad_code
  thirdPartyHost.value.innerHTML = ''

  if (!code) return

  const wrapper = document.createElement('div')
  wrapper.innerHTML = code

  const scriptNodes = Array.from(wrapper.querySelectorAll('script'))
  for (const scriptNode of scriptNodes) {
    scriptNode.remove()
  }

  for (const child of Array.from(wrapper.childNodes)) {
    thirdPartyHost.value.appendChild(child.cloneNode(true))
  }

  const externalScriptUrl = ad.third_party?.script_url
  if (externalScriptUrl) {
    const externalScript = document.createElement('script')
    externalScript.src = externalScriptUrl
    externalScript.async = true
    thirdPartyHost.value.appendChild(externalScript)
  }

  for (const originalScript of scriptNodes) {
    const script = document.createElement('script')
    for (const attr of Array.from(originalScript.attributes)) {
      script.setAttribute(attr.name, attr.value)
    }
    if (originalScript.textContent) {
      script.textContent = originalScript.textContent
    }
    thirdPartyHost.value.appendChild(script)
  }
}

watch(() => props.ad, (ad) => {
  if (isThirdParty.value) {
    nextTick(() => executeThirdPartyCode(ad))
  }
}, { immediate: true })

const handleClick = async () => {
  emit('clicked', props.ad)
  if (props.trackClick) {
    await recordAdClick(props.ad.id)
  }
}

const imageSource = computed(() => props.ad.image_mobile || props.ad.image || '')
const linkTarget = computed(() => props.ad.open_in_new_tab ? '_blank' : '_self')
const wrapperClass = computed(() => {
  if (props.variant === 'compact') return 'rounded-xl border border-slate-200/70 p-2 bg-white/90 dark:bg-slate-900/90'
  if (props.variant === 'splash') return 'rounded-2xl border border-slate-200/70 p-4 sm:p-6 bg-white dark:bg-slate-900 shadow-2xl'
  return 'rounded-2xl border border-slate-200/70 p-3 sm:p-4 bg-white/95 dark:bg-slate-900/95'
})
</script>

<template>
  <article :class="wrapperClass">
    <template v-if="isNativeLike">
      <a
        v-if="ad.link_url"
        :href="ad.link_url"
        :target="linkTarget"
        rel="noopener noreferrer"
        class="block"
        @click="handleClick"
      >
        <img
          v-if="imageSource"
          :src="imageSource"
          :alt="ad.title || ad.slug"
          class="w-full h-auto rounded-xl object-cover"
          loading="lazy"
        >

        <div class="pt-3">
          <h3
            v-if="ad.title"
            class="font-semibold text-slate-900 dark:text-white"
          >
            {{ ad.title }}
          </h3>
          <p
            v-if="ad.description"
            class="text-sm text-slate-600 dark:text-slate-300 mt-1"
          >
            {{ ad.description }}
          </p>
          <div
            v-if="ad.link_text"
            class="mt-3 inline-flex items-center rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white"
          >
            {{ ad.link_text }}
          </div>
        </div>
      </a>

      <div v-else>
        <img
          v-if="imageSource"
          :src="imageSource"
          :alt="ad.title || ad.slug"
          class="w-full h-auto rounded-xl object-cover"
          loading="lazy"
        >
        <h3
          v-if="ad.title"
          class="mt-3 font-semibold text-slate-900 dark:text-white"
        >
          {{ ad.title }}
        </h3>
        <p
          v-if="ad.description"
          class="text-sm text-slate-600 dark:text-slate-300 mt-1"
        >
          {{ ad.description }}
        </p>
      </div>
    </template>

    <template v-else>
      <div
        ref="thirdPartyHost"
        class="ad-third-party overflow-hidden"
      />
    </template>
  </article>
</template>
