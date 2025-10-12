<template>
  <div class="avatar-uploader">
    <!-- Avatar -->
    <div class="avatar-container">
      <input ref="fileInput" type="file" accept="image/*" @change="onFileChange" class="avatar-input" />
      <img :src="croppedAvatar || user?.avatar || '/default-avatar.png'" alt="Avatar" class="avatar-img" />
      <div class="avatar-overlay">
        <Icon name="mdi:camera" class="w-6 h-6 text-white" />
        <span class="avatar-label">Change</span>
      </div>
    </div>

    <!-- Change Button -->
    <button @click="triggerFileInput" class="btn-change">
      <Icon name="mdi:camera" class="w-5 h-5" />
    </button>

    <!-- Tips -->
    <div class="tips">
      <div class="tip tip-info">
        <Icon name="mdi:information" class="w-4 h-4" />
        <span>Recommended: 400x400px</span>
      </div>
      <div class="tip tip-success">
        <Icon name="mdi:file-image" class="w-4 h-4" />
        <span>Formats: JPG, PNG, WebP</span>
      </div>
    </div>

    <!-- Cropping Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="cancel">
        <div class="modal-content">
          <!-- Header -->
          <div class="modal-header">
            <div class="modal-header-icon">
              <Icon name="mdi:crop" class="w-5 h-5 text-white" />
            </div>
            <div class="flex-1">
              <h3 class="modal-title">Crop your Avatar</h3>
              <p class="modal-subtitle">Adjust your profile picture</p>
            </div>
            <button @click="cancel" class="btn-close">
              <Icon name="mdi:close" class="w-4 h-4" />
            </button>
          </div>

          <!-- Cropper -->
          <div class="modal-body">
            <div class="cropper-wrap">
              <Cropper class="cropper" :src="image" :stencil-component="CircleStencil" :stencil-props="{ aspectRatio: 1 }" @change="onCrop" />
            </div>
          </div>

          <!-- Footer -->
          <div class="modal-footer">
            <button @click="cancel" class="btn-cancel">Cancel</button>
            <button @click="saveAvatar" :disabled="form.processing || !cropResult" class="btn-save">
              <Icon :name="form.processing ? 'mdi:loading' : 'mdi:content-save'" class="w-4 h-4" :class="{ 'animate-spin': form.processing }" />
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

const form = useSanctumForm('PUT', `${config.public.apiBase}/account/avatar`, { avatar: null as File | null })

function triggerFileInput() {
  fileInput.value?.click()
}

function onFileChange(event: Event) {
  const file = (event.target as HTMLInputElement)?.files?.[0]
  if (!file) return

  if (file.size > 5 * 1024 * 1024) {
    toast.error({ title: 'File Too Large', message: 'Please select an image smaller than 5MB' })
    return
  }

  if (!file.type.startsWith('image/')) {
    toast.error({ title: 'Invalid File Type', message: 'Please select a valid image file' })
    return
  }

  const reader = new FileReader()
  reader.onload = (e) => {
    image.value = e.target?.result as string
    showModal.value = true
    nextTick(() => { cropResult.value = null })
  }
  reader.readAsDataURL(file)
}

function onCrop({ canvas }: any) {
  cropResult.value = canvas
}

function cancel() {
  image.value = null
  showModal.value = false
  cropResult.value = null
  if (fileInput.value) fileInput.value.value = ''
}

async function saveAvatar() {
  if (!cropResult.value) {
    toast.error({ title: 'No Image Selected', message: 'Please crop the image before saving' })
    return
  }

  try {
    const blob = await new Promise<Blob>((resolve, reject) => {
      cropResult.value.toBlob((blob: Blob | null) => {
        blob ? resolve(blob) : reject(new Error('Failed to create blob'))
      }, 'image/png', 0.9)
    })

    form.avatar = new File([blob], 'avatar.png', { type: 'image/png' })
    await form.submit()
    croppedAvatar.value = cropResult.value.toDataURL()
    await refreshUser()
    cancel()
    toast.success({ title: 'Avatar Updated!', message: 'Your profile picture has been updated successfully' })
  } catch (e: any) {
    console.error('Avatar upload failed:', e)
    toast.error({ title: 'Upload Failed', message: e?.data?.message || 'Failed to update avatar. Please try again.' })
  }
}
</script>

<style scoped>
.avatar-uploader {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

/* Avatar Container */
.avatar-container {
  position: relative;
  width: 8rem;
  height: 8rem;
  border-radius: 1rem;
  overflow: hidden;
  border: 4px solid white;
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
  cursor: pointer;
}

.dark .avatar-container {
  border-color: rgb(51,65,85);
}

.avatar-input {
  position: absolute;
  inset: 0;
  opacity: 0;
  z-index: 10;
  cursor: pointer;
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.6);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  opacity: 0;
  transition: opacity 0.3s;
}

.avatar-container:hover .avatar-overlay {
  opacity: 1;
}

.avatar-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

/* Change Button */
.btn-change {
  position: absolute;
  bottom: -0.5rem;
  right: -0.5rem;
  width: 2.5rem;
  height: 2.5rem;
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  color: white;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
  transition: all 0.3s;
}

.btn-change:hover {
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
  transform: scale(1.1);
}

/* Tips */
.tips {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.tip {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.tip-info {
  color: rgb(37,99,235);
}

.dark .tip-info {
  color: rgb(96,165,250);
}

.tip-success {
  color: rgb(22,163,74);
}

.dark .tip-success {
  color: rgb(52,211,153);
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(8px);
  padding: 1rem;
}

.modal-content {
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 1rem;
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
  width: 100%;
  max-width: 42rem;
  max-height: 90vh;
  overflow-y: auto;
}

.dark .modal-content {
  background: rgba(30,41,59,0.95);
  border-color: rgba(71,85,105,0.5);
}

.modal-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  border-bottom: 1px solid rgb(226,232,240);
  position: sticky;
  top: 0;
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(20px);
  z-index: 10;
}

.dark .modal-header {
  border-color: rgb(51,65,85);
  background: rgba(30,41,59,0.95);
}

.modal-header-icon {
  width: 2.5rem;
  height: 2.5rem;
  background: linear-gradient(135deg, #22c55e, #16a34a);
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.modal-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: rgb(15,23,42);
}

.dark .modal-title {
  color: white;
}

.modal-subtitle {
  font-size: 0.875rem;
  color: rgb(100,116,139);
}

.dark .modal-subtitle {
  color: rgb(148,163,184);
}

.btn-close {
  width: 2rem;
  height: 2rem;
  background: rgb(241,245,249);
  color: rgb(100,116,139);
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: colors 0.2s;
}

.btn-close:hover {
  background: rgb(226,232,240);
}

.dark .btn-close {
  background: rgb(51,65,85);
  color: rgb(148,163,184);
}

.dark .btn-close:hover {
  background: rgb(71,85,105);
}

.modal-body {
  padding: 1.5rem;
}

.cropper-wrap {
  width: 100%;
  height: 20rem;
  background: rgb(241,245,249);
  border-radius: 0.75rem;
  overflow: hidden;
}

@media (min-width: 640px) {
  .cropper-wrap {
    height: 24rem;
  }
}

.dark .cropper-wrap {
  background: rgb(51,65,85);
}

.cropper {
  width: 100%;
  height: 100%;
}

.modal-footer {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid rgb(226,232,240);
  position: sticky;
  bottom: 0;
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(20px);
}

.dark .modal-footer {
  border-color: rgb(51,65,85);
  background: rgba(30,41,59,0.95);
}

.btn-cancel {
  flex: 1;
  padding: 0.75rem 1.5rem;
  background: rgb(241,245,249);
  color: rgb(51,65,85);
  border-radius: 0.75rem;
  font-weight: 600;
  transition: colors 0.2s;
}

.btn-cancel:hover {
  background: rgb(226,232,240);
}

.dark .btn-cancel {
  background: rgb(51,65,85);
  color: rgb(203,213,225);
}

.dark .btn-cancel:hover {
  background: rgb(71,85,105);
}

.btn-save {
  flex: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  border-radius: 0.75rem;
  font-weight: 600;
  transition: colors 0.2s;
}

.btn-save:hover {
  background: linear-gradient(135deg, #16a34a, #15803d);
}

.btn-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
