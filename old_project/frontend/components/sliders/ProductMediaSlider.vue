<template>
  <div class="product-media-slider bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-xl">
    <div class="flex flex-col md:flex-row w-full">
      <!-- Thumbnails (LEFT for desktop) -->
      <div
          v-if="thumbPosition === 'left'"
          class="hidden md:flex flex-col gap-3 w-24 mr-6 p-4 overflow-y-auto max-h-[500px] scrollbar-thin scrollbar-track-gray-100 scrollbar-thumb-gray-300 dark:scrollbar-track-gray-700 dark:scrollbar-thumb-gray-600"
      >
        <div
            v-for="(url, i) in media"
            :key="`thumb-${i}`"
            class="thumbnail-item cursor-pointer border-2 rounded-xl overflow-hidden transition-all duration-300 hover:scale-110 hover:shadow-lg"
            :class="currentIndex === i ? 'border-blue-500 ring-2 ring-blue-500/30' : 'border-gray-200 dark:border-gray-600 hover:border-blue-300'"
            @click="setPreview(i)"
        >
          <img
              v-if="!isVideo(url)"
              :src="url"
              :alt="`Product image ${i + 1}`"
              class="w-20 h-20 object-cover"
              loading="lazy"
          />
          <div v-else class="relative w-20 h-20 bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
            <video
                :src="url"
                class="w-full h-full object-cover"
                muted
            ></video>
            <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
              <Icon name="mdi:play-circle" class="w-6 h-6 text-white" />
            </div>
          </div>
        </div>
      </div>

      <!-- Main Preview Area -->
      <div class="flex-1 flex flex-col items-center">
        <div
            class="media-preview relative w-full h-[400px] md:h-[500px] bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 overflow-hidden rounded-xl cursor-zoom-in"
            @mousemove="onMouseMove"
            @mouseenter="isHovering = true"
            @mouseleave="isHovering = false; resetZoom()"
            @wheel="onWheel"
        >
          <!-- Image with lens zoom effect -->
          <div
              v-if="!isVideo(media[currentIndex])"
              class="w-full h-full bg-center bg-no-repeat transition-all duration-200 ease-out"
              :style="{
              backgroundImage: `url(${media[currentIndex]})`,
              backgroundSize: isHovering ? `${zoom * 100}%` : 'contain',
              backgroundPosition: isHovering ? backgroundPos : 'center',
              backgroundRepeat: 'no-repeat',
            }"
          >
            <!-- Zoom indicator -->
            <div
                v-if="isHovering"
                class="zoom-indicator absolute top-4 right-4 bg-black/70 text-white px-3 py-1 rounded-full text-sm font-medium backdrop-blur-sm"
            >
              {{ Math.round(zoom * 100) }}%
            </div>
          </div>

          <!-- Video player -->
          <video
              v-else
              controls
              class="w-full h-full object-contain rounded-xl"
              :src="media[currentIndex]"
              :poster="getVideoPoster(media[currentIndex])"
          ></video>

          <!-- Navigation arrows for mobile -->
          <button
              v-if="media.length > 1"
              @click="previousImage"
              class="nav-arrow nav-arrow-left absolute left-2 top-1/2 transform -translate-y-1/2 w-10 h-10 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center transition-all duration-300 md:hidden"
          >
            <Icon name="mdi:chevron-left" class="w-5 h-5" />
          </button>

          <button
              v-if="media.length > 1"
              @click="nextImage"
              class="nav-arrow nav-arrow-right absolute right-2 top-1/2 transform -translate-y-1/2 w-10 h-10 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center transition-all duration-300 md:hidden"
          >
            <Icon name="mdi:chevron-right" class="w-5 h-5" />
          </button>

          <!-- Image counter -->
          <div class="image-counter absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/70 text-white px-4 py-2 rounded-full text-sm font-medium backdrop-blur-sm">
            {{ currentIndex + 1 }} / {{ media.length }}
          </div>

          <!-- Fullscreen button -->
          <button
              @click="toggleFullscreen"
              class="fullscreen-btn absolute top-4 left-4 w-10 h-10 bg-black/70 hover:bg-black/90 text-white rounded-full flex items-center justify-center transition-all duration-300 backdrop-blur-sm"
          >
            <Icon name="mdi:fullscreen" class="w-5 h-5" />
          </button>
        </div>

        <!-- Thumbnails (BOTTOM for mobile) -->
        <div
            v-if="thumbPosition === 'bottom' || (thumbPosition === 'left' && windowWidth < 768)"
            class="flex gap-3 mt-6 overflow-x-auto w-full px-4 pb-2 scrollbar-thin scrollbar-track-gray-100 scrollbar-thumb-gray-300 dark:scrollbar-track-gray-700 dark:scrollbar-thumb-gray-600"
        >
          <div
              v-for="(url, i) in media"
              :key="`thumb-bottom-${i}`"
              class="thumbnail-item flex-shrink-0 cursor-pointer border-2 rounded-xl overflow-hidden transition-all duration-300 hover:scale-110"
              :class="currentIndex === i ? 'border-blue-500 ring-2 ring-blue-500/30' : 'border-gray-200 dark:border-gray-600 hover:border-blue-300'"
              @click="setPreview(i)"
          >
            <img
                v-if="!isVideo(url)"
                :src="url"
                :alt="`Product image ${i + 1}`"
                class="w-16 h-16 sm:w-20 sm:h-20 object-cover"
                loading="lazy"
            />
            <div v-else class="relative w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
              <video
                  :src="url"
                  class="w-full h-full object-cover"
                  muted
              ></video>
              <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                <Icon name="mdi:play-circle" class="w-4 h-4 sm:w-6 sm:h-6 text-white" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile swipe indicators -->
    <div v-if="media.length > 1" class="flex justify-center mt-4 md:hidden">
      <div class="flex gap-2">
        <button
            v-for="(_, i) in media"
            :key="`indicator-${i}`"
            @click="setPreview(i)"
            class="w-2 h-2 rounded-full transition-all duration-300"
            :class="currentIndex === i ? 'bg-blue-500' : 'bg-gray-300 dark:bg-gray-600'"
        ></button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

interface Props {
  media: string[]
  thumbPosition?: 'left' | 'bottom'
}

const props = withDefaults(defineProps<Props>(), {
  thumbPosition: 'left'
})

// State
const currentIndex = ref(0)
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)

// Lens + zoom state
const zoom = ref(2)
const backgroundPos = ref('50% 50%')
const isHovering = ref(false)

// Methods
function setPreview(i: number) {
  currentIndex.value = Math.max(0, Math.min(i, props.media.length - 1))
}

function previousImage() {
  currentIndex.value = currentIndex.value > 0 ? currentIndex.value - 1 : props.media.length - 1
}

function nextImage() {
  currentIndex.value = currentIndex.value < props.media.length - 1 ? currentIndex.value + 1 : 0
}

function isVideo(url: string): boolean {
  return /\.(mp4|webm|ogg|mov|avi)$/i.test(url)
}

function getVideoPoster(videoUrl: string): string {
  // You can implement logic to get video poster/thumbnail
  return videoUrl.replace(/\.(mp4|webm|ogg|mov|avi)$/i, '_poster.jpg')
}

function onMouseMove(e: MouseEvent) {
  if (isVideo(props.media[currentIndex.value])) return

  const target = e.currentTarget as HTMLElement
  const rect = target.getBoundingClientRect()
  const x = ((e.clientX - rect.left) / rect.width) * 100
  const y = ((e.clientY - rect.top) / rect.height) * 100
  backgroundPos.value = `${x}% ${y}%`
}

function onWheel(e: WheelEvent) {
  if (isVideo(props.media[currentIndex.value])) return

  e.preventDefault()
  zoom.value += e.deltaY < 0 ? 0.3 : -0.3
  zoom.value = Math.max(1.5, Math.min(5, zoom.value))
}

function resetZoom() {
  zoom.value = 2
  backgroundPos.value = '50% 50%'
}

function toggleFullscreen() {
  // Implement fullscreen functionality
  const element = document.querySelector('.media-preview')
  if (element) {
    if (document.fullscreenElement) {
      document.exitFullscreen()
    } else {
      element.requestFullscreen()
    }
  }
}

function updateWindowWidth() {
  windowWidth.value = window.innerWidth
}

// Keyboard navigation
function handleKeydown(e: KeyboardEvent) {
  switch (e.key) {
    case 'ArrowLeft':
      e.preventDefault()
      previousImage()
      break
    case 'ArrowRight':
      e.preventDefault()
      nextImage()
      break
    case 'Escape':
      if (document.fullscreenElement) {
        document.exitFullscreen()
      }
      break
  }
}

// Lifecycle
onMounted(() => {
  if (process.client) {
    window.addEventListener('resize', updateWindowWidth)
    window.addEventListener('keydown', handleKeydown)
  }
})

onUnmounted(() => {
  if (process.client) {
    window.removeEventListener('resize', updateWindowWidth)
    window.removeEventListener('keydown', handleKeydown)
  }
})
</script>

<style scoped>
.cursor-zoom-in {
  cursor: zoom-in;
}

.cursor-zoom-in:hover {
  cursor: zoom-in;
}

/* Custom scrollbar */
.scrollbar-thin {
  scrollbar-width: thin;
}

.scrollbar-thin::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.scrollbar-track-gray-100::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 3px;
}

.scrollbar-thumb-gray-300::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.scrollbar-thumb-gray-300::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

.dark .scrollbar-track-gray-700::-webkit-scrollbar-track {
  background: #374151;
}

.dark .scrollbar-thumb-gray-600::-webkit-scrollbar-thumb {
  background: #4b5563;
}

.dark .scrollbar-thumb-gray-600::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}

/* Navigation arrows animations */
.nav-arrow {
  opacity: 0.7;
  transition: all 0.3s ease;
}

.nav-arrow:hover {
  opacity: 1;
  transform: translateY(-50%) scale(1.1);
}

/* Thumbnail hover effects */
.thumbnail-item {
  position: relative;
}

.thumbnail-item::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(59, 130, 246, 0.1));
  opacity: 0;
  transition: opacity 0.3s ease;
  border-radius: 0.75rem;
}

.thumbnail-item:hover::before {
  opacity: 1;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .media-preview {
    height: 300px;
  }
}

@media (max-width: 480px) {
  .media-preview {
    height: 250px;
  }

  .thumbnail-item {
    width: 60px;
    height: 60px;
  }
}
</style>
