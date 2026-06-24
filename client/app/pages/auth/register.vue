<template>
  <div class="min-h-screen w-full flex relative overflow-hidden">
    <!-- Left Side: Platform Features (Desktop Only) -->
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative z-10 flex-col justify-center p-12">
      <!-- Background gradient overlay -->
      <div
        class="absolute inset-0 bg-gradient-to-br from-emerald-600/6 via-teal-600/4 to-cyan-600/6 backdrop-blur-sm"
      />

      <div class="relative z-10 max-w-lg">
        <!-- Brand Logo & Name -->
        <NuxtLink
          to="/"
          class="flex items-center gap-4 mb-12"
        >
          <div
            class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl flex items-center justify-center shadow-2xl transform transition-transform hover:scale-105 duration-300"
          >
            <UIcon
              name="i-lucide-hexagon"
              class="w-8 h-8 text-white"
            />
          </div>
          <div>
            <h1 class="gradient-text-primary text-3xl font-bold">
              {{ config.public.appName }}
            </h1>
            <p class="text-slate-600 dark:text-slate-400 font-medium">Your Shopping Destination</p>
          </div>
        </NuxtLink>

        <!-- Main Heading -->
        <h2 class="text-5xl xl:text-6xl font-bold mb-8 leading-tight">
          <span class="gradient-text-primary">
            Create Your
          </span>
          <br>
          <span class="gradient-text-accent">
            Account!
          </span>
        </h2>

        <!-- Description -->
        <p class="text-xl text-slate-600 dark:text-slate-400 mb-12 leading-relaxed">
          Create your account to start shopping, unlock exclusive deals, and enjoy premium loyalty rewards.
        </p>

        <!-- Features -->
        <div class="grid grid-cols-1 gap-6 mb-12">
          <div class="feature-card">
            <div class="icon-box-primary">
              <UIcon
                name="i-lucide-users"
                class="w-6 h-6 text-white"
              />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white mb-2">
                Referral Rewards
              </h3>
              <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Invite friends to shop with us and earn reward points on their purchases
              </p>
            </div>
          </div>

          <div class="feature-card">
            <div class="icon-box-success">
              <UIcon
                name="i-lucide-wallet"
                class="w-6 h-6 text-white"
              />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white mb-2">
                Loyalty Perks
              </h3>
              <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Unlock special bonuses, cashback, and exclusive loyalty incentives
              </p>
            </div>
          </div>

          <div class="feature-card">
            <div class="icon-box-purple">
              <UIcon
                name="i-lucide-sparkles"
                class="w-6 h-6 text-white"
              />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white mb-2">
                Exclusive Access
              </h3>
              <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Premium products, early sales, and special discounts for our shoppers
              </p>
            </div>
          </div>
        </div>

        <!-- Trust Indicators -->
        <div class="flex items-center gap-6 text-sm text-slate-500 dark:text-slate-400 flex-wrap">
          <div class="flex items-center gap-2">
            <UIcon
              name="i-lucide-shield-check"
              class="w-4 h-4"
            />
            <span>Secure Platform</span>
          </div>
          <div class="flex items-center gap-2">
            <UIcon
              name="i-lucide-zap"
              class="w-4 h-4"
            />
            <span>Quick Setup</span>
          </div>
          <div class="flex items-center gap-2">
            <UIcon
              name="i-lucide-check-circle"
              class="w-4 h-4"
            />
            <span>Free to Join</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Side: Register Form -->
    <div class="w-full lg:w-1/2 xl:w-2/5 relative z-10 flex items-center justify-center p-4 lg:p-8">
      <div class="w-full max-w-md">
        <!-- Register Card -->
        <div class="glass-card p-8">
          <!-- Mobile: Branding (shown only on mobile) -->
          <div class="lg:hidden text-center mb-8">
            <div class="flex items-center justify-center mb-6">
              <div
                class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-xl"
              >
                <UIcon
                  name="i-lucide-hexagon"
                  class="w-8 h-8 text-white"
                />
              </div>
            </div>
            <h1 class="gradient-text-primary text-3xl font-bold mb-3">
              Create Account
            </h1>
            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
              Join {{ config.public.appName }} today
            </p>
          </div>

          <!-- Desktop: Simple Header -->
          <div class="hidden lg:block text-center mb-8">
            <h2 class="gradient-text-primary text-2xl font-bold mb-2">
              Create Account
            </h2>
            <p class="text-slate-600 dark:text-slate-400 text-sm">
              Fill in your details to get started
            </p>
          </div>

          <!-- Signup Mode Toggle (only show if both modes are needed) -->
          <div class="flex items-center justify-center gap-2 mb-6">
            <button
              type="button"
              class="px-4 py-2 text-sm font-medium rounded-lg transition-all"
              :class="activeMode === 'mobile'
                ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300'
                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
              @click="activeMode = 'mobile'"
            >
              <UIcon
                name="i-lucide-smartphone"
                class="w-4 h-4 inline mr-1"
              />
              Mobile
            </button>
            <button
              type="button"
              class="px-4 py-2 text-sm font-medium rounded-lg transition-all"
              :class="activeMode === 'email'
                ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300'
                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
              @click="activeMode = 'email'"
            >
              <UIcon
                name="i-lucide-mail"
                class="w-4 h-4 inline mr-1"
              />
              Email
            </button>
          </div>

          <!-- Signup Components -->
          <AuthMobileSignup
            v-if="activeMode === 'mobile'"
            @success="handleRegistrationSuccess"
          />
          <AuthEmailSignup
            v-else
            @success="handleRegistrationSuccess"
          />

          <!-- Login Link -->
          <div class="mt-8 text-center">
            <p class="text-sm text-slate-600 dark:text-slate-400">
              Already have an account?
              <NuxtLink
                to="/auth/login"
                class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-semibold transition-colors"
              >
                Sign In
              </NuxtLink>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'guest',
  middleware: '$guest'
})

const router = useRouter()
const config = useRuntimeConfig()
const nuxtApp = useNuxtApp()
const { refreshUser, user } = useSanctum()
const toast = useToast()

// Get default signup mode from config
const defaultMode = config.public.signupMode as 'mobile' | 'email'
const activeMode = ref<'mobile' | 'email'>(defaultMode)

// Handle successful registration from child components
const handleRegistrationSuccess = async (token: string) => {
  // Store token using Sanctum module storage
  await useTokenStorage(nuxtApp).set(token)

  // Refresh user data
  await refreshUser()

  toast.add({
    title: 'Welcome!',
    description: 'Your account has been created successfully!',
    color: 'success'
  })

  const typedUser = user.value as any
  if (!typedUser || typedUser.onboarded !== true) {
    await router.push('/onboarding')
    return
  }
  const { getAndClearRedirectUrl } = useRedirectUrl()
  const intendedUrl = getAndClearRedirectUrl()
  if (intendedUrl) {
    await router.push(intendedUrl)
    return
  }
  const { getDashboardRoute } = useUserType()
  await router.push(getDashboardRoute())
}
</script>
