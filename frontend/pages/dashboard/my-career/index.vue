<template>
  <div class="relative min-h-screen bg-gray-900 py-16 px-6 text-white">
    <!-- Timeline vertical line -->
    <div class="absolute left-1/2 top-0 w-1 bg-gray-700 transform -translate-x-1/2"></div>

    <div class="max-w-5xl mx-auto space-y-24">
      <div
          v-for="(stage, index) in stages"
          :key="stage.url"
          ref="stageRefs"
          class="relative flex items-center w-full"
      >
        <!-- Left content -->
        <div
            v-if="index % 2 !== 0"
            class="w-1/2 pr-12 text-right hidden md:block"
        >
          <StageCard :stage="stage" :active="isActive(stage)" />
        </div>

        <!-- Timeline point -->
        <div class="z-10 flex items-center justify-center w-10 h-10 bg-gray-900 border-4 border-blue-500 rounded-full relative">
          <Icon name="mdi:flag" class="text-blue-500" size="20" />
        </div>

        <!-- Right content -->
        <div
            v-if="index % 2 === 0"
            class="w-1/2 pl-12 hidden md:block"
        >
          <StageCard :stage="stage" :active="isActive(stage)" />
        </div>

        <!-- Mobile layout (full width) -->
        <div class="block md:hidden w-full mt-6">
          <StageCard :stage="stage" :active="isActive(stage)" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import gsap from 'gsap'
import ScrollTrigger from 'gsap/ScrollTrigger'
import { useSanctum } from '#imports'

gsap.registerPlugin(ScrollTrigger)

// StageCard subcomponent
const StageCard = defineComponent({
  props: {
    stage: { type: Object, required: true },
    active: { type: Boolean, default: false }
  },
  template: `
    <div :class="['bg-gray-800 rounded-xl shadow-lg p-6 transition-all', active ? 'ring-4 ring-blue-500 scale-105' : '']">
      <h3 class="text-xl font-bold mb-2">{{ stage.name }} <span class="text-blue-400">{{ stage.price }}</span></h3>
      <p class="text-gray-300 mb-4">{{ stage.description }}</p>
      <div class="text-sm text-gray-400">
        Max Capacity: {{ stage.max_team_capacity }}
      </div>
    </div>
  `
})

const { user } = useSanctum()

const stages = ref([])
const stageRefs = ref<HTMLElement[]>([])

const isActive = (stage: any) => {
  if (!user.value) return false
  return stage.levels?.some(l => l.url === user.value?.level?.url || l.id === user.value?.level_id)
}

onMounted(async () => {
  const res = await $fetch('http://localhost:8000/api/lifecycle/stages')
  stages.value = res.data || []

  // Animate each stage
  stageRefs.value.forEach((el) => {
    gsap.from(el, {
      opacity: 0,
      y: 50,
      duration: 0.8,
      ease: 'power3.out',
      scrollTrigger: {
        trigger: el,
        start: 'top 80%',
        toggleActions: 'play none none reverse'
      }
    })
  })
})

definePageMeta({
  layout: 'dashboard'
})
</script>
