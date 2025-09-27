<!-- components/AvatarUploader.vue -->
<template>
  <div class="flex flex-col items-center gap-4">
    <!-- Avatar Circle -->
    <div class="relative group">
      <div class="relative w-32 h-32 rounded-2xl overflow-hidden border-4 border-white dark:border-slate-700 shadow-xl cursor-pointer">
        <input
            ref="fileInput"
            type="file"
            accept="image/*"
            @change="onFileChange"
            class="absolute inset-0 opacity-0 z-10 cursor-pointer"
        />
        <img
            :src="croppedAvatar || user?.avatar || '/default-avatar.png'"
            alt="Avatar"
            class="w-full h-full object-cover"
        />
        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center">
          <div class="flex flex-col items-center gap-2 text-white">
            <Icon name="mdi:camera" class="w-6 h-6" />
            <span class="text-xs font-semibold">Change</span>
          </div>
        </div>
      </div>

      <!-- Change Avatar Button -->
      <button
          @click="triggerFileInput"
          class="absolute -bottom-2 -right-2 w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-110"
      >
        <Icon name="mdi:camera" class="w-5 h-5" />
      </button>
    </div>

    <!-- Upload Tips -->
    <div class="flex flex-col items-center gap-2">
      <div class="flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400">
        <Icon name="mdi:information" class="w-4 h-4" />
        <span>Recommended size: 400x400px</span>
      </div>
      <div class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
        <Icon name="mdi:file-image" class="w-4 h-4" />
        <span>Supported formats: JPG, PNG, WebP</span>
      </div>
    </div>

    <!-- Cropping Modal with Higher Z-Index -->
    <Teleport to="body">
      <div
          v-if="showModal"
          class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
          @click.self="cancel"
      >
        <div class="bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700 sticky top-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl z-10">
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                <Icon name="mdi:crop" class="w-5 h-5 text-white" />
              </div>
              <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Crop your Avatar</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Adjust your profile picture</p>
              </div>
            </div>
            <button
                @click="cancel"
                class="w-8 h-8 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg flex items-center justify-center text-slate-500 dark:text-slate-400 transition-colors"
            >
              <Icon name="mdi:close" class="w-4 h-4" />
            </button>
          </div>

          <!-- Cropper Area with Better Size Control -->
          <div class="p-6">
            <div class="w-full h-80 sm:h-96 bg-slate-100 dark:bg-slate-700 rounded-xl overflow-hidden">
              <Cropper
                  class="w-full h-full"
                  :src="image"
                  :stencil-component="CircleStencil"
                  :stencil-props="{ aspectRatio: 1 }"
                  @change="onCrop"
              />
            </div>
          </div>

          <!-- Modal Actions - Sticky Footer -->
          <div class="flex gap-4 p-6 border-t border-slate-200 dark:border-slate-700 sticky bottom-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl">
            <button
                @click="cancel"
                class="flex-1 px-6 py-3 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-semibold transition-colors"
            >
              Cancel
            </button>
            <button
                @click="saveAvatar"
                :disabled="form.processing || !cropResult"
                class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl font-semibold transition-colors"
            >
              <Icon
                  :name="form.processing ? 'mdi:loading' : 'mdi:content-save'"
                  class="w-4 h-4"
                  :class="{ 'animate-spin': form.processing }"
              />
              <span>{{ form.processing ? 'Saving...' : 'Save Avatar' }}</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, nextTick } from 'vue'
import { Cropper, CircleStencil } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'
import { useSanctum, useSanctumForm, useRuntimeConfig, useToast } from '#imports'

const { refreshUser, user } = useSanctum()
const config = useRuntimeConfig()
const toast = useToast()

const fileInput = ref<HTMLInputElement>()
const image = ref<string | null>(null)
const croppedAvatar = ref<string | null>(null)
const cropResult = ref<any>(null)
const showModal = ref(false)

const form = useSanctumForm('PUT', `${config.public.apiBase}/account/avatar`, {
  avatar: null as File | null,
})

function triggerFileInput() {
  if (fileInput.value) {
    fileInput.value.click()
  }
}

function onFileChange(event: Event) {
  const file = (event.target as HTMLInputElement)?.files?.[0]
  if (file) {
    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      toast.error({
        title: 'File Too Large',
        message: 'Please select an image smaller than 5MB'
      })
      return
    }

    // Validate file type
    if (!file.type.startsWith('image/')) {
      toast.error({
        title: 'Invalid File Type',
        message: 'Please select a valid image file'
      })
      return
    }

    const reader = new FileReader()
    reader.onload = (e) => {
      image.value = e.target?.result as string
      showModal.value = true

      // Ensure modal is properly rendered
      nextTick(() => {
        // Reset crop result when opening modal
        cropResult.value = null
      })
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

  // Reset file input
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

async function saveAvatar() {
  if (!cropResult.value) {
    toast.error({
      title: 'No Image Selected',
      message: 'Please crop the image before saving'
    })
    return
  }

  try {
    const blob = await new Promise<Blob>((resolve, reject) => {
      cropResult.value.toBlob((blob: Blob | null) => {
        if (blob) {
          resolve(blob)
        } else {
          reject(new Error('Failed to create blob from canvas'))
        }
      }, 'image/png', 0.9)
    })

    form.avatar = new File([blob], 'avatar.png', { type: 'image/png' })

    await form.submit()

    // Update the preview with the cropped image
    croppedAvatar.value = cropResult.value.toDataURL()

    // Refresh user data
    await refreshUser()

    // Close modal
    cancel()

    toast.success({
      title: 'Avatar Updated!',
      message: 'Your profile picture has been updated successfully'
    })
  } catch (e: any) {
    console.error('Avatar upload failed:', e)
    toast.error({
      title: 'Upload Failed',
      message: e?.data?.message || 'Failed to update avatar. Please try again.'
    })
  }
}
</script>
