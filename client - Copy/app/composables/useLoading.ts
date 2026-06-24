/**
 * useLoading - Global loading state management
 */
export const useLoading = () => {
  const isLoading = useState<boolean>('app:loading', () => false)
  const loadingMessage = useState<string>('app:loading_message', () => 'Loading...')

  const startLoading = (message?: string) => {
    loadingMessage.value = message || 'Loading...'
    isLoading.value = true
  }

  const stopLoading = () => {
    isLoading.value = false
  }

  return {
    isLoading,
    loadingMessage,
    startLoading,
    stopLoading
  }
}
