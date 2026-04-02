<template>
  <div class="step-profile">
    <div class="w-full">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <UIcon
            name="i-lucide-user"
            class="w-8 h-8 text-blue-600 dark:text-blue-400"
          />
        </div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-2">
          Tell us about yourself
        </h2>
        <p class="text-gray-600 dark:text-gray-400 text-sm md:text-base">
          This helps us personalize your experience
        </p>
      </div>

      <!-- Avatar Upload -->
      <div class="flex justify-center mb-6">
        <div class="relative group">
          <div
            class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center overflow-hidden border-4 border-white dark:border-gray-800 shadow-lg cursor-pointer"
            @click="triggerFileInput"
          >
            <img
              v-if="avatarPreview"
              :src="avatarPreview"
              alt="Avatar"
              class="w-full h-full object-cover"
            >
            <UIcon
              v-else
              name="i-lucide-user"
              class="w-10 h-10 text-gray-400 dark:text-gray-500"
            />
          </div>
          <button
            type="button"
            class="absolute bottom-0 right-0 w-8 h-8 bg-primary-500 hover:bg-primary-600 rounded-full flex items-center justify-center shadow-lg transition-colors"
            @click="triggerFileInput"
          >
            <UIcon
              name="i-lucide-camera"
              class="w-4 h-4 text-white"
            />
          </button>
          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleFileChange"
          >
        </div>
      </div>
      <p class="text-center text-xs text-gray-500 dark:text-gray-400 mb-8">
        Use a clear headshot for a more trusted profile.
      </p>

      <!-- Form Fields -->
      <UForm
        :state="formState"
        :schema="schema"
        class="space-y-6 w-full"
        @submit="handleSubmit"
      >
        <!-- Full Name (Full width) -->
        <UFormField
          label="Full Name"
          name="name"
          required
          class="w-full"
        >
          <UInput
            v-model="formState.name"
            placeholder="Enter your full name"
            size="lg"
            icon="i-lucide-user"
            class="w-full"
          />
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            This will appear on invoices and your public profile.
          </p>
        </UFormField>

        <!-- Date of Birth & Gender (2 columns on desktop) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
          <UFormField
            label="Date of Birth"
            name="dob"
            required
            class="w-full"
          >
            <UInput
              v-model="formState.dob"
              type="date"
              size="lg"
              icon="i-lucide-calendar"
              :max="maxDate"
              class="w-full"
            />
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
              You must be at least 13 years old.
            </p>
          </UFormField>

          <UFormField
            label="Gender"
            name="gender"
            required
            class="w-full"
          >
            <URadioGroup
              v-model="formState.gender"
              :items="genderOptions"
              class="flex flex-wrap gap-3 mt-2"
            />
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
              Used to personalize recommendations.
            </p>
          </UFormField>
        </div>

        <!-- Bio (Optional, Full width) -->
        <UFormField
          label="Bio"
          name="bio"
          class="w-full"
        >
          <UTextarea
            v-model="formState.bio"
            placeholder="Tell us a little about yourself..."
            :rows="3"
            size="lg"
            class="w-full"
          />
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            Keep it short and professional (max 280 characters).
          </p>
        </UFormField>
      </UForm>
    </div>
  </div>
</template>

<script setup lang="ts">
import { z } from 'zod'

interface ProfileData {
  name: string
  dob: string
  gender: string
  bio: string
  avatar: File | null
}

interface Props {
  initialData?: Partial<ProfileData>
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:data': [data: ProfileData]
  'valid': [isValid: boolean]
}>()

const fileInput = ref<HTMLInputElement>()
const avatarPreview = ref<string | null>(null)
const avatarFile = ref<File | null>(null)

// Calculate max date (must be at least 13 years old)
const maxDate = computed(() => {
  const date = new Date()
  date.setFullYear(date.getFullYear() - 13)
  return date.toISOString().split('T')[0]
})

const formState = reactive({
  name: props.initialData?.name || '',
  dob: props.initialData?.dob || '',
  gender: props.initialData?.gender || '',
  bio: props.initialData?.bio || ''
})

const genderOptions = [
  { label: 'Male', value: 'male' },
  { label: 'Female', value: 'female' },
  { label: 'Other', value: 'other' }
]

const schema = z.object({
  name: z.string().min(2, 'Name must be at least 2 characters'),
  dob: z.string().min(1, 'Date of birth is required'),
  gender: z.string().min(1, 'Please select your gender'),
  bio: z.string().optional()
})

// Watch for changes and emit data
watch(
  () => ({ ...formState, avatar: avatarFile.value }),
  (newData) => {
    emit('update:data', newData)

    // Check validity
    const result = schema.safeParse(formState)
    emit('valid', result.success)
  },
  { deep: true, immediate: true }
)

const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (file) {
    avatarFile.value = file

    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      avatarPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const handleSubmit = () => {
  // Form is valid, parent will handle navigation
}

// Expose validation method
const validate = (): boolean => {
  const result = schema.safeParse(formState)
  return result.success
}

const getData = (): ProfileData => ({
  ...formState,
  avatar: avatarFile.value
})

defineExpose({
  validate,
  getData
})
</script>
