import type { Ref } from 'vue'

export interface OnboardingStatus {
  onboarded: boolean
  progress: number
  steps: {
    profile: boolean
    address: boolean
    kyc: boolean
  }
}

export interface OnboardingProfileData {
  name: string
  gender: 'male' | 'female' | 'other' | null
  dob: string | null
  bio?: string
}

export const useOnboarding = () => {
  const config = useRuntimeConfig()
  const toast = useToast()

  const status: Ref<OnboardingStatus | null> = ref(null)
  const loading = ref(false)
  const error: Ref<string | null> = ref(null)

  /**
   * Fetch current onboarding status
   */
  const fetchStatus = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/onboarding/status`)
      status.value = response
      return response
    } catch (err: unknown) {
      error.value = (err as Error).message || 'Failed to fetch onboarding status'
      toast.add({
        title: 'Error',
        description: error.value,
        color: 'red'
      })
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Update profile information (Step 1)
   */
  const updateProfile = async (data: OnboardingProfileData) => {
    loading.value = true
    error.value = null

    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/onboarding/profile`, {
        method: 'PUT',
        body: data
      })

      toast.add({
        title: 'Success',
        description: 'Profile updated successfully',
        color: 'green'
      })

      // Refresh status
      await fetchStatus()

      return response
    } catch (err: unknown) {
      error.value = (err as Error).message || 'Failed to update profile'
      toast.add({
        title: 'Error',
        description: error.value,
        color: 'red'
      })
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Complete onboarding process
   */
  const completeOnboarding = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/onboarding/complete`, {
        method: 'POST'
      })

      toast.add({
        title: 'Welcome! 🎉',
        description: response.message || 'Onboarding completed successfully',
        color: 'green'
      })

      // Refresh status
      await fetchStatus()

      return response
    } catch (err: unknown) {
      error.value = (err as Error).message || 'Failed to complete onboarding'
      toast.add({
        title: 'Error',
        description: error.value,
        color: 'red'
      })
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Check if user needs onboarding
   */
  const needsOnboarding = computed(() => {
    return status.value && !status.value.onboarded
  })

  /**
   * Get completion percentage
   */
  const completionPercentage = computed(() => {
    return status.value?.progress || 0
  })

  return {
    status,
    loading,
    error,
    fetchStatus,
    updateProfile,
    completeOnboarding,
    needsOnboarding,
    completionPercentage
  }
}
