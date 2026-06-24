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
          <p
            v-for="message in fieldErrorsFor('name')"
            :key="`name-${message}`"
            class="text-xs text-red-500 mt-1"
          >
            {{ message }}
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
              :min="minDate"
              :max="maxDate"
              class="w-full"
            />
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
              You must be at least 18 years old.
            </p>
            <p
              v-for="message in fieldErrorsFor('dob')"
              :key="`dob-${message}`"
              class="text-xs text-red-500 mt-1"
            >
              {{ message }}
            </p>
          </UFormField>

          <UFormField
            label="Gender"
            name="gender"
            required
            class="w-full"
          >
            <div class="grid grid-cols-3 gap-2 mt-2">
              <button
                v-for="option in genderOptions"
                :key="option.value"
                type="button"
                :class="formState.gender === option.value
                  ? 'bg-primary-500 text-white border-primary-500'
                  : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600'"
                class="px-3 py-2 rounded-lg border text-sm font-medium transition-colors"
                @click="formState.gender = option.value"
              >
                {{ option.label }}
              </button>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
              Used to personalize recommendations.
            </p>
            <p
              v-for="message in fieldErrorsFor('gender')"
              :key="`gender-${message}`"
              class="text-xs text-red-500 mt-1"
            >
              {{ message }}
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
  fieldErrors?: Record<string, string | string[]>
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:data': [data: ProfileData]
  'valid': [isValid: boolean]
}>()

const fileInput = ref<HTMLInputElement>()
const avatarPreview = ref<string | null>(null)
const avatarFile = ref<File | null>(null)

// Date of birth range: 18 to 100 years
const minDate = computed(() => {
  const date = new Date()
  date.setFullYear(date.getFullYear() - 100)
  return date.toISOString().split('T')[0]
})

const maxDate = computed(() => {
  const date = new Date()
  date.setFullYear(date.getFullYear() - 18)
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
  name: z.string().min(3, 'Name must be at least 3 characters'),
  dob: z
    .string()
    .min(1, 'Date of birth is required')
    .refine((value) => value >= minDate.value && value <= maxDate.value, {
      message: 'Date of birth must be between 18 and 100 years ago'
    }),
  gender: z.string().min(1, 'Please select your gender'),
  bio: z.string().optional()
})

const fieldErrorsFor = (key: string): string[] => {
  const messages: string[] = []

  const zodResult = schema.safeParse(formState)
  if (!zodResult.success) {
    const zodIssue = zodResult.error.issues.find(issue => issue.path[0] === key)
    if (zodIssue?.message) messages.push(zodIssue.message)
  }

  const serverValue = props.fieldErrors?.[key]
  if (Array.isArray(serverValue)) {
    for (const msg of serverValue) {
      if (msg && !messages.includes(msg)) messages.push(msg)
    }
  } else if (typeof serverValue === 'string' && serverValue && !messages.includes(serverValue)) {
    messages.push(serverValue)
  }

  return messages
}

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
