<template>
  <div class="m-2">
    <!-- Avatar circle -->
    <div class="relative w-40 h-40 rounded-full overflow-hidden border-4 border-white shadow-md cursor-pointer group mx-auto">
      <input
          type="file"
          accept="image/*"
          @change="onFileChange"
          class="absolute inset-0 opacity-0 z-10 cursor-pointer"
      />
      <NuxtImg
          :src="croppedAvatar || user?.avatar || '/default-avatar.png'"
          alt="Avatar"
          class="w-full h-full object-cover rounded-full"
      />
      <div class="absolute inset-0 bg-black bg-opacity-20 hidden group-hover:flex items-center justify-center text-white font-medium">
        Change
      </div>
    </div>

    <!-- Modal -->
    <div
        v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    >
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-lg">
        <h3 class="text-lg font-semibold mb-4">Crop your Avatar</h3>
        <div class="relative w-full aspect-square border rounded overflow-hidden bg-gray-100 dark:bg-gray-700">
          <Cropper
              class="cropper"
              :src="image"
              :stencil-component="CircleStencil"
              :stencil-props="{ aspectRatio: 1 }"
              @change="onCrop"
          />
        </div>
        <div class="flex justify-end gap-3 mt-4">
          <button class="btn-secondary" @click="cancel">Cancel</button>
          <button
              class="btn-primary"
              @click="saveAvatar"
              :disabled="form.processing"
          >
            {{ form.processing ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Cropper, CircleStencil } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'
import { useSanctum, useSanctumForm,useRuntimeConfig } from '#imports'

const { refreshUser, user } = useSanctum()
const config = useRuntimeConfig()

const image = ref<string | null>(null)
const croppedAvatar = ref<string | null>(null)
const cropResult = ref<any>(null)
const showModal = ref(false)

const toast = useToast()

const form = useSanctumForm('put', `${config.public.apiBase}/account/avatar`, {
  avatar: null as File | null,
})

function onFileChange(event: Event) {
  const file = (event.target as HTMLInputElement)?.files?.[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      image.value = e.target?.result as string
      showModal.value = true
    }
    reader.readAsDataURL(file)
  }
}

function onCrop({ canvas }: any) {
  cropResult.value = canvas
}

function cancel() {
  image.value = null
  showModal.value = false
  cropResult.value = null
}

async function saveAvatar() {
  if (!cropResult.value) return

  const blob = await new Promise<Blob>((resolve) =>
      cropResult.value.toBlob(resolve, 'image/png')
  )

  form.avatar = new File([blob], 'avatar.png', { type: 'image/png' })

  try {
    await form.submit()
    croppedAvatar.value = cropResult.value.toDataURL()
    refreshUser()
    cancel()
  } catch (e) {
    console.error('Upload failed:', e)
    toast.error({ title: 'Error!', message: 'Something went wrong.' })
  }
}
</script>

<style scoped>
.btn-primary {
  @apply px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition disabled:opacity-50;
}
.btn-secondary {
  @apply px-6 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 transition;
}
.cropper {
  width: 100%;
  height: 100%;
}
</style>
