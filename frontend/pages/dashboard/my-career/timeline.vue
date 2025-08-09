<template>
  <div class="relative min-h-screen py-16 px-4 sm:px-6 lg:px-8 font-sans my-4">
    <!-- Vertical center line -->
    <div
        class="absolute top-0 bottom-0 left-1/2 w-1 bg-blue-600/50 -translate-x-1/2"
    ></div>

    <!-- Start label -->
    <div
        class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-blue-600 text-white text-sm font-semibold rounded px-3 py-1 select-none"
        style="z-index: 20;"
    >
      Finish
    </div>

    <!-- Finish label -->
    <div
        class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 bg-blue-600 text-white text-sm font-semibold rounded px-3 py-1 select-none"
        style="z-index: 20;"
    >
      Start
    </div>

    <ul class="max-w-4xl mx-auto">
      <li
          v-for="(stage, idx) in timeline"
          :key="stage.url"
          ref="el => timelineRefs[idx] = el"
          class="relative mb-20 clear-both"
      >
        <!-- Timeline dot -->
        <span
            class="absolute w-6 h-6 rounded-full border-4 border-gray-900 top-5 z-10"
            :class="[
            'bg-blue-500',
            idx % 2 === 0 ? 'left-1/2 -translate-x-1/2' : 'left-1/2 -translate-x-1/2',
            isUserLevel(stage, user.level_id) ? 'bg-green-400 border-green-700' : ''
          ]"
            title="Current Stage"
        ></span>

        <!-- Content container -->
        <div
            :class="[
            'w-full md:w-1/2 px-6 py-5 rounded-lg shadow-lg border',
            isUserLevel(stage, user.level_id)
              ? 'border-green-500 bg-green-900 text-green-300'
              : 'border-blue-600 bg-blue-600 text-white',
            idx % 2 === 0 ? 'md:ml-[52%] md:text-left' : 'md:mr-[52%] md:text-right'
          ]"
        >
          <h3 class="text-xl font-semibold mb-1">{{ stage.name }}</h3>
          <p class="mb-2 text-gray-300">{{ stage.description }}</p>

          <div class="text-sm mb-2">Price: <span class="font-medium">{{ stage.price }}</span></div>

          <div class="text-sm mb-4">
            Max Team Capacity: <span class="font-medium">{{ stage.max_team_capacity }}</span>
          </div>

          <div>
            <h4 class="font-semibold mb-1">Levels:</h4>
            <ul class="list-none ml-5 space-y-1">
              <li
                  v-for="level in stage.levels"
                  :key="level.id"
                  :class="[
                  'cursor-default ',
                  level.id === user.level_id
                    ? 'text-green-400 font-bold'
                    : 'text-gray-400 hover:text-blue-400'
                ]"
              >
                {{ level.name }}
              </li>
            </ul>
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>








<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useSanctum } from '#imports'
import gsap from 'gsap'
import ScrollTrigger from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const { user } = useSanctum()

const timeline = ref([]) // stages array from API
const timelineRefs = ref([] as HTMLElement[])

// Fetch timeline data from API
async function fetchTimeline() {
  try {
    const res = await fetch('http://localhost:8000/api/lifecycle/timeline')
    const json = await res.json()
    timeline.value = json.data || []
  } catch (error) {
    console.error('Failed to fetch timeline:', error)
  }
}

onMounted(async () => {
  await fetchTimeline()

  // Animate timeline items on scroll
  timelineRefs.value.forEach((el) => {
    if (!el) return
    gsap.from(el, {
      opacity: 0,
      y: 40,
      duration: 0.8,
      ease: 'power3.out',
      scrollTrigger: {
        trigger: el,
        start: 'top 80%',
        toggleActions: 'play none none reverse',
      },
    })
  })
})

// Helper to check if this stage or level matches user's current level
function isUserLevel(stage, userLevelId) {
  if (!userLevelId) return false
  return stage.levels.some((lvl) => lvl.id === userLevelId)
}

definePageMeta({
  layout: 'dashboard'
})
</script>


<style scoped>
li::after {
  content: '';
  display: table;
  clear: both;
}
</style>

