<template>
  <div class="space-y-6">
    <div class="glass-card p-8">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-3xl font-bold gradient-text-primary">
            Edit Profile
          </h2>
          <p class="text-slate-600 dark:text-slate-400 mt-1">
            Update your account information
          </p>
        </div>
        <NuxtLink to="/profile">
          <UButton
            color="neutral"
            variant="ghost"
          >
            <UIcon
              name="i-lucide-x"
              class="w-4 h-4"
            />
            Cancel
          </UButton>
        </NuxtLink>
      </div>

      <!-- Loading State -->
      <div
        v-if="isLoading"
        class="flex items-center justify-center py-12"
      >
        <UIcon
          name="i-lucide-loader-2"
          class="w-8 h-8 text-blue-500 animate-spin"
        />
      </div>

      <template v-else>
        <!-- Avatar Upload Section -->
        <div class="flex flex-col items-center mb-8">
          <div class="relative">
            <UAvatar
              :src="avatarPreview || currentUser?.avatar"
              :alt="currentUser?.name"
              size="3xl"
              class="ring-4 ring-blue-100 dark:ring-blue-900/30"
            />
            <label
              class="absolute bottom-0 right-0 w-10 h-10 bg-blue-500 hover:bg-blue-600 text-white rounded-full flex items-center justify-center shadow-lg cursor-pointer transition-colors"
            >
              <UIcon
                name="i-lucide-camera"
                class="w-5 h-5"
              />
              <input
                type="file"
                accept="image/*"
                class="hidden"
                @change="handleAvatarChange"
              >
            </label>
          </div>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-3">
            Click the camera icon to change your photo
          </p>
          <p
            v-if="avatarError"
            class="text-sm text-red-500 mt-1"
          >
            {{ avatarError }}
          </p>
        </div>
        <!-- Success Message -->
        <div
          v-if="success"
          class="mb-6"
        >
          <div class="flex items-center gap-3 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200/60 dark:border-green-800/60 rounded-xl text-green-700 dark:text-green-400">
            <UIcon
              name="i-lucide-check-circle"
              class="w-5 h-5 flex-shrink-0"
            />
            <div>
              <p class="font-semibold">
                {{ successMessage }}
              </p>
              <p
                v-if="pendingVerification.length > 0"
                class="text-sm mt-1"
              >
                Pending verification: {{ pendingVerification.join(', ') }}
              </p>
            </div>
          </div>
        </div>

        <!-- Edit Form -->
        <form
          class="space-y-6"
          @submit.prevent="handleUpdate"
        >
          <!-- Name -->
          <div class="space-y-2">
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
              <UIcon
                name="i-lucide-user"
                class="w-4 h-4"
              />
              <span>Full Name</span>
              <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="John Doe"
              class="w-full px-4 py-3 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              :class="{ 'border-red-500': errors.name }"
            >
            <p
              v-if="errors.name"
              class="text-sm text-red-500"
            >
              {{ errors.name }}
            </p>
          </div>

          <!-- Email -->
          <div class="space-y-2">
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
              <UIcon
                name="i-lucide-mail"
                class="w-4 h-4"
              />
              <span>Email Address</span>
              <span class="text-red-500">*</span>
              <span class="text-slate-500 text-xs">(Requires verification if changed)</span>
            </label>
            <div class="relative group">
              <input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="you@example.com"
                class="w-full h-12 pl-4 pr-12 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 font-semibold"
                :class="{
                  'border-red-500 bg-red-50 dark:bg-red-900/20': errors.email,
                  'border-green-300 bg-green-50 dark:bg-green-900/10': canChangeEmail && !errors.email,
                  'border-blue-300 bg-blue-50 dark:bg-blue-900/10': !canChangeEmail
                }"
              >
              <NuxtLink
                to="/profile/change-email"
                class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg flex items-center justify-center shadow-md transition-all duration-300 hover:scale-105"
                title="Change Email"
              >
                <UIcon
                  name="i-lucide-edit-3"
                  class="w-4 h-4"
                />
              </NuxtLink>
            </div>
            <p
              v-if="errors.email"
              class="text-sm text-red-500"
            >
              {{ errors.email }}
            </p>
          </div>

          <!-- Mobile -->
          <div class="space-y-2">
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
              <UIcon
                name="i-lucide-smartphone"
                class="w-4 h-4"
              />
              <span>Mobile Number</span>
              <span class="text-slate-500 text-xs">(Requires verification if changed)</span>
            </label>
            <div class="relative group">
              <input
                v-model="form.mobile"
                type="text"
                placeholder="10-digit mobile number"
                class="w-full h-12 pl-4 pr-12 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 font-semibold"
                :class="{
                  'border-red-500 bg-red-50 dark:bg-red-900/20': errors.mobile,
                  'border-green-300 bg-green-50 dark:bg-green-900/10': canChangeMobile && !errors.mobile,
                  'border-blue-300 bg-blue-50 dark:bg-blue-900/10': !canChangeMobile
                }"
              >
              <NuxtLink
                to="/profile/change-mobile"
                class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg flex items-center justify-center shadow-md transition-all duration-300 hover:scale-105"
                title="Change Mobile"
              >
                <UIcon
                  name="i-lucide-edit-3"
                  class="w-4 h-4"
                />
              </NuxtLink>
            </div>
            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">
              Enter a 10-digit mobile number.
            </p>
            <p
              v-if="errors.mobile"
              class="text-sm text-red-500"
            >
              {{ errors.mobile }}
            </p>
          </div>

          <!-- Bio -->
          <div class="space-y-2">
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
              <UIcon
                name="i-lucide-file-text"
                class="w-4 h-4"
              />
              <span>Bio</span>
              <span class="text-slate-500 text-xs">(Optional)</span>
            </label>
            <textarea
              v-model="form.bio"
              placeholder="Tell us about yourself..."
              rows="4"
              maxlength="500"
              class="w-full px-4 py-3 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
              :class="{ 'border-red-500': errors.bio }"
            />
            <div class="text-xs text-slate-500 text-right">
              {{ form.bio?.length || 0 }} / 500
            </div>
            <p
              v-if="errors.bio"
              class="text-sm text-red-500"
            >
              {{ errors.bio }}
            </p>
          </div>

          <!-- Gender -->
          <div class="space-y-2">
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
              <UIcon
                name="i-lucide-user-circle"
                class="w-4 h-4"
              />
              <span>Gender</span>
              <span class="text-slate-500 text-xs">(Optional)</span>
            </label>
            <select
              v-model="form.gender"
              class="w-full px-4 py-3 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              :class="{ 'border-red-500': errors.gender }"
            >
              <option value="">
                Select Gender
              </option>
              <option value="male">
                Male
              </option>
              <option value="female">
                Female
              </option>
              <option value="other">
                Other
              </option>
            </select>
            <p
              v-if="errors.gender"
              class="text-sm text-red-500"
            >
              {{ errors.gender }}
            </p>
          </div>

          <!-- Date of Birth -->
          <div class="space-y-2">
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
              <UIcon
                name="i-lucide-calendar"
                class="w-4 h-4"
              />
              <span>Date of Birth</span>
              <span class="text-slate-500 text-xs">(Optional)</span>
            </label>
            <input
              v-model="form.dob"
              type="date"
              :max="maxDate"
              class="w-full px-4 py-3 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              :class="{ 'border-red-500': errors.dob }"
            >
            <p
              v-if="errors.dob"
              class="text-sm text-red-500"
            >
              {{ errors.dob }}
            </p>
          </div>

          <!-- Error Alert -->
          <div
            v-if="generalError"
            class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200/60 dark:border-red-800/60 rounded-xl text-red-600 dark:text-red-400 text-sm"
          >
            <UIcon
              name="i-lucide-alert-circle"
              class="w-5 h-5 flex-shrink-0"
            />
            <p>{{ generalError }}</p>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-4 pt-4">
            <button
              type="submit"
              :disabled="loading"
              class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-300"
            >
              <UIcon
                :name="loading ? 'i-lucide-loader-2' : 'i-lucide-save'"
                :class="{ 'animate-spin': loading }"
                class="w-5 h-5"
              />
              <span>{{ loading ? 'Saving...' : 'Save Changes' }}</span>
            </button>
            <NuxtLink
              to="/profile"
              class="flex-shrink-0"
            >
              <UButton
                color="neutral"
                variant="soft"
                size="lg"
              >
                Cancel
              </UButton>
            </NuxtLink>
          </div>
        </form>
      </template>
    </div>

    <!-- Change Password Card -->
    <div class="glass-card p-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center">
            <UIcon
              name="i-lucide-key"
              class="w-6 h-6 text-white"
            />
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">
              Password & Security
            </h3>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              Update your password to keep your account secure
            </p>
          </div>
        </div>
        <NuxtLink to="/profile/change-password">
          <UButton
            color="warning"
            variant="soft"
          >
            <UIcon
              name="i-lucide-shield"
              class="w-4 h-4"
            />
            Change Password
          </UButton>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: '$auth',
  title: 'Edit Profile',
  layout: 'dashboard'
})

const config = useRuntimeConfig()
const router = useRouter()
const { refreshUser } = useSanctum()
const currentUser = useCurrentUser()

// Avatar upload form
const avatarForm = useSanctumForm('POST', `${config.public.apiBase}/api/user/avatar`, {
  avatar: null as File | null
})

// State
const isLoading = ref(true)
const avatarPreview = ref<string | null>(null)
const avatarFile = ref<File | null>(null)
const avatarError = ref<string | null>(null)
const loading = ref(false)
const success = ref(false)
const successMessage = ref('')
const generalError = ref<string | null>(null)
const pendingVerification = ref<string[]>([])

// Errors object
const errors = reactive<Record<string, string | null>>({
  name: null,
  email: null,
  mobile: null,
  bio: null,
  gender: null,
  dob: null
})

// Form data - separate from user state to avoid reactivity issues
const form = reactive({
  name: '',
  email: '',
  mobile: '',
  bio: '',
  gender: '',
  dob: ''
})

const maxDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

// Computed properties for showing edit button availability
const canChangeEmail = computed(() => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(form.email)
})

const canChangeMobile = computed(() => {
  // Indian mobile validation: starts with 6-9, 10 digits
  const mobileRegex = /^\d{10}$/
  return mobileRegex.test(form.mobile)
})

// Load profile data from the authenticated user (from Sanctum's useCurrentUser)
const loadProfile = async () => {
  try {
    isLoading.value = true
    generalError.value = null

    // First refresh user data from server to ensure we have latest
    await refreshUser()

    // Wait a tick for the reactive state to update
    await nextTick()

    // Use the current authenticated user from Sanctum (use the composable ref directly)
    const userData = currentUser.value

    if (!userData) {
      generalError.value = 'Please log in to edit your profile.'
      return
    }

    // Populate form with user data - handle all possible field variations
    form.name = userData.name || ''
    form.email = userData.email || ''
    form.mobile = userData.mobile || ''
    form.bio = userData.bio || ''
    form.gender = userData.gender || ''
    // Handle date format - could be ISO string or YYYY-MM-DD
    const dob = userData.dob || ''
    form.dob = dob ? dob.split('T')[0] : '' // Ensure YYYY-MM-DD format for input

    console.log('Profile loaded:', { name: form.name, email: form.email, mobile: form.mobile, bio: form.bio, gender: form.gender, dob: form.dob })
  } catch (err) {
    console.error('Failed to load profile:', err)
    generalError.value = 'Failed to load profile data. Please refresh the page.'
  } finally {
    isLoading.value = false
  }
}

// Clear all field errors
const clearErrors = () => {
  Object.keys(errors).forEach((key) => {
    errors[key] = null
  })
  generalError.value = null
}

// Handle form submission
const handleUpdate = async () => {
  loading.value = true
  success.value = false
  clearErrors()

  try {
    const response = await useSanctumFetch<{
      message: string
      data: { user: Record<string, unknown> }
      pending_verification?: string[]
    }>(`${config.public.apiBase}/api/user/profile`, {
      method: 'PUT',
      body: {
        name: form.name,
        email: form.email || null,
        mobile: form.mobile || null,
        bio: form.bio || null,
        gender: form.gender || null,
        dob: form.dob || null
      }
    })

    success.value = true
    successMessage.value = response.message || 'Profile updated successfully!'
    pendingVerification.value = response.pending_verification || []

    // Refresh auth user data
    await refreshUser()

    // Navigate back to profile after short delay
    setTimeout(() => {
      router.push('/profile')
    }, 2000)
  } catch (err: unknown) {
    const fetchError = err as {
      data?: {
        message?: string
        errors?: Record<string, string[]>
      }
      statusCode?: number
    }

    // Handle validation errors - set field-specific errors
    if (fetchError.data?.errors) {
      Object.entries(fetchError.data.errors).forEach(([field, messages]) => {
        if (field in errors) {
          errors[field] = (messages as string[])[0]
        }
      })
    }

    // Handle general error message
    if (fetchError.data?.message) {
      generalError.value = fetchError.data.message
    } else {
      generalError.value = 'Failed to update profile. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

// Handle avatar file selection
const handleAvatarChange = async (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (!file) return

  // Validate file type
  if (!file.type.startsWith('image/')) {
    avatarError.value = 'Please select an image file'
    return
  }

  // Validate file size (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    avatarError.value = 'Image must be less than 2MB'
    return
  }

  avatarError.value = null
  avatarFile.value = file

  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    avatarPreview.value = e.target?.result as string
  }
  reader.readAsDataURL(file)

  // Upload avatar immediately
  await uploadAvatar()
}

// Upload avatar to server
const uploadAvatar = async () => {
  if (!avatarFile.value) return

  try {
    // Set avatar file and submit using Sanctum form
    avatarForm.avatar = avatarFile.value
    await avatarForm.submit()

    // Refresh user data to get new avatar URL
    await refreshUser()
    avatarFile.value = null
    avatarPreview.value = null
  } catch (err: unknown) {
    const fetchError = err as { data?: { message?: string } }
    avatarError.value = fetchError.data?.message || 'Failed to upload avatar'
    avatarPreview.value = null
  }
}

// Load profile on mount
onMounted(() => {
  loadProfile()
})
</script>
