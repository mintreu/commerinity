<script setup lang="ts">
import { ref } from "vue";
import { NuxtImg } from "#components";

const props = defineProps({
  media: { type: Array, required: true },
  thumbPosition: { type: String, default: "left" } // 'left' or 'bottom'
});

const currentIndex = ref(0);

function setPreview(i: number) {
  currentIndex.value = i < props.media.length ? i : props.media.length - 1;
}

function isVideo(url: string) {
  return /\.(mp4|webm|ogg)$/i.test(url);
}

// Lens + zoom state
const zoom = ref(2); // default zoom multiplier
const backgroundPos = ref("50% 50%");
const isHovering = ref(false);

function onMouseMove(e: MouseEvent) {
  const target = e.currentTarget as HTMLElement;
  const rect = target.getBoundingClientRect();
  const x = ((e.clientX - rect.left) / rect.width) * 100;
  const y = ((e.clientY - rect.top) / rect.height) * 100;
  backgroundPos.value = `${x}% ${y}%`;
}

function onWheel(e: WheelEvent) {
  e.preventDefault();
  zoom.value += e.deltaY < 0 ? 0.2 : -0.2;
  zoom.value = Math.max(1.5, Math.min(4, zoom.value)); // clamp
}
</script>

<template>
  <div class="flex flex-col md:flex-row w-full">
    <!-- Thumbnails (LEFT) -->
    <div
        v-if="thumbPosition === 'left'"
        class="hidden md:flex flex-col gap-2 w-24 mr-4 overflow-y-auto max-h-[500px]"
    >
      <div
          v-for="(url, i) in props.media"
          :key="i"
          class="cursor-pointer border rounded overflow-hidden"
          @click="setPreview(i)"
      >
        <NuxtImg
            v-if="!isVideo(url)"
            :src="url"
            class="w-20 h-20 object-cover"
        />
        <video
            v-else
            :src="url"
            class="w-20 h-20 object-cover"
            muted
        ></video>
      </div>
    </div>

    <!-- Preview Area -->
    <div class="flex-1 flex flex-col items-center">
      <div
          class="relative w-full h-[400px] md:h-[500px] bg-gray-100 overflow-hidden rounded-lg"
          @mousemove="onMouseMove"
          @mouseenter="isHovering = true"
          @mouseleave="isHovering = false"
          @wheel="onWheel"
      >
        <!-- Image with lens zoom -->
        <div
            v-if="!isVideo(props.media[currentIndex])"
            class="w-full h-full bg-center bg-no-repeat transition-all duration-100"
            :style="{
            backgroundImage: `url(${props.media[currentIndex]})`,
            backgroundSize: isHovering ? `${zoom * 100}%` : 'contain',
            backgroundPosition: isHovering ? backgroundPos : 'center',
            backgroundRepeat: 'no-repeat',
          }"
        ></div>

        <!-- Video stays normal -->
        <video
            v-else
            controls
            class="w-full h-full object-contain"
            :src="props.media[currentIndex]"
        ></video>
      </div>

      <!-- Thumbnails (BOTTOM) -->
      <div
          v-if="thumbPosition === 'bottom'"
          class="flex gap-2 mt-4 overflow-x-auto w-full"
      >
        <div
            v-for="(url, i) in props.media"
            :key="i"
            class="cursor-pointer border rounded overflow-hidden flex-shrink-0"
            @click="setPreview(i)"
        >
          <NuxtImg
              v-if="!isVideo(url)"
              :src="url"
              class="w-20 h-20 object-cover"
          />
          <video
              v-else
              :src="url"
              class="w-20 h-20 object-cover"
              muted
          ></video>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
::-webkit-scrollbar {
  display: none;
}
</style>