/**
 * Wallet Composable
 * Handles wallet state and API interactions
 */

interface WalletData {
  uuid: string
  balance: number
  balance_formatted: string
  hold_balance: number
  hold_balance_formatted: string
  available_balance: number
  available_balance_formatted: string
  total_credited: number
  total_credited_formatted: string
  total_debited: number
  total_debited_formatted: string
  points: number
  currency: string
  status: string
  can_transact: boolean
  can_receive: boolean
  has_pin: boolean
  requires_pin_setup: boolean
  pin_updated_at: string | null
}

interface WalletSummary {
  balance: number
  hold_balance: number
  available_balance: number
  total_credited: number
  total_debited: number
  points: number
  currency: string
  status: string
  has_pin: boolean
}

interface Transaction {
  uuid: string
  type: string
  type_label: string
  status: string
  status_label: string
  amount: number
  amount_formatted: string
  fee: number
  fee_formatted: string
  net_amount: number
  net_amount_formatted: string
  currency: string
  purpose: string
  description: string
  reference_number: string
  balance_after: number
  balance_after_formatted: string
  is_positive: boolean
  formatted_amount: string
  created_at: string
}

interface SecurityQuestion {
  key: string
  label: string
}

interface WalletStats {
  balance: number
  balance_formatted: string
  available_balance: number
  available_balance_formatted: string
  hold_balance: number
  hold_balance_formatted: string
  total_credited: number
  total_credited_formatted: string
  total_debited: number
  total_debited_formatted: string
  points: number
  monthly_credits: number
  monthly_credits_formatted: string
  monthly_debits: number
  monthly_debits_formatted: string
  pending_amount: number
  pending_amount_formatted: string
  recent_transaction_count: number
  requires_pin_setup: boolean
}

export const useWallet = () => {
  const config = useRuntimeConfig()

  const wallet = ref<WalletData | null>(null)
  const stats = ref<WalletStats | null>(null)
  const transactions = ref<Transaction[]>([])
  const securityQuestions = ref<SecurityQuestion[]>([])
  const userSecurityQuestions = ref<SecurityQuestion[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Fetch wallet data
  const fetchWallet = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet`)
      if (response.success) {
        wallet.value = response.data.wallet
      }
    } catch (e: any) {
      error.value = e.data?.message || 'Failed to fetch wallet'
    } finally {
      loading.value = false
    }
  }

  // Fetch wallet stats
  const fetchStats = async () => {
    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/stats`)
      if (response.success) {
        stats.value = response.data
      }
    } catch (e: any) {
      console.error('Failed to fetch stats:', e)
    }
  }

  // Fetch transactions
  const fetchTransactions = async (page = 1, perPage = 20) => {
    try {
      const response = await useSanctumFetch(
        `${config.public.apiBase}/api/wallet/transactions?page=${page}&per_page=${perPage}`
      )
      transactions.value = response.data || []
      return response
    } catch (e: any) {
      console.error('Failed to fetch transactions:', e)
      return null
    }
  }

  // Fetch available security questions
  const fetchSecurityQuestions = async () => {
    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/security-questions`)
      if (response.success) {
        securityQuestions.value = response.data.questions
      }
    } catch (e: any) {
      console.error('Failed to fetch security questions:', e)
    }
  }

  // Fetch user's security questions
  const fetchUserSecurityQuestions = async () => {
    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/my-security-questions`)
      if (response.success) {
        userSecurityQuestions.value = response.data.questions
      }
      return response.data
    } catch (e: any) {
      console.error('Failed to fetch user security questions:', e)
      return null
    }
  }

  // Setup PIN
  const setupPin = async (data: {
    pin: string
    confirm_pin: string
    security_question_1: string
    security_answer_1: string
    security_question_2: string
    security_answer_2: string
  }) => {
    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/setup-pin`, {
        method: 'POST',
        body: data
      })
      await fetchWallet() // Refresh wallet data
      return { success: true, message: response.message }
    } catch (e: any) {
      return { success: false, message: e.data?.message || 'Failed to setup PIN', errors: e.data?.errors }
    }
  }

  // Request PIN change OTP
  const requestPinChangeOtp = async (method: 'mobile' | 'email') => {
    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/request-pin-otp`, {
        method: 'POST',
        body: { method }
      })
      return { success: true, data: response.data, message: response.message }
    } catch (e: any) {
      return { success: false, message: e.data?.message || 'Failed to send OTP' }
    }
  }

  // Change PIN
  const changePin = async (data: {
    otp: string
    method: 'mobile' | 'email'
    new_pin: string
    confirm_pin: string
  }) => {
    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/change-pin`, {
        method: 'POST',
        body: data
      })
      return { success: true, message: response.message }
    } catch (e: any) {
      return { success: false, message: e.data?.message || 'Failed to change PIN' }
    }
  }

  // Verify PIN
  const verifyPin = async (pin: string) => {
    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/verify-pin`, {
        method: 'POST',
        body: { pin }
      })
      return { success: true, message: response.message }
    } catch (e: any) {
      return {
        success: false,
        message: e.data?.message || 'Invalid PIN',
        attemptsRemaining: e.data?.attempts_remaining,
        locked: e.data?.locked,
        retryAfter: e.data?.retry_after
      }
    }
  }

  // Verify security question
  const verifySecurityQuestion = async (questionKey: string, answer: string) => {
    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/verify-security-question`, {
        method: 'POST',
        body: { question_key: questionKey, answer }
      })
      return { success: true, data: response.data, message: response.message }
    } catch (e: any) {
      return { success: false, message: e.data?.message || 'Security answer incorrect' }
    }
  }

  // Reset PIN with token
  const resetPinWithToken = async (data: {
    reset_token: string
    new_pin: string
    confirm_pin: string
  }) => {
    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/reset-pin`, {
        method: 'POST',
        body: data
      })
      await fetchWallet()
      return { success: true, message: response.message }
    } catch (e: any) {
      return { success: false, message: e.data?.message || 'Failed to reset PIN' }
    }
  }

  // Send money
  const sendMoney = async (data: {
    pin: string
    recipient_mobile: string
    amount: number
    note?: string
  }) => {
    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/send`, {
        method: 'POST',
        body: data
      })
      await fetchWallet()
      return { success: true, data: response.data, message: response.message }
    } catch (e: any) {
      return {
        success: false,
        message: e.data?.message || 'Failed to send money',
        requiresPinSetup: e.data?.requires_pin_setup,
        attemptsRemaining: e.data?.attempts_remaining
      }
    }
  }

  // Withdraw to bank account/UPI
  const withdraw = async (data: {
    pin: string
    amount: number
    beneficiary_uuid: string
  }) => {
    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/withdraw`, {
        method: 'POST',
        body: data
      })
      await fetchWallet()
      return { success: true, data: response.data, message: response.message }
    } catch (e: any) {
      return {
        success: false,
        message: e.data?.message || 'Failed to process withdrawal',
        requiresPinSetup: e.data?.requires_pin_setup,
        attemptsRemaining: e.data?.attempts_remaining
      }
    }
  }

  // Pay via wallet
  const payViaWallet = async (data: {
    pin: string
    amount: number
    purpose: 'order' | 'subscription' | 'service'
    reference_type?: string
    reference_id?: number
    description?: string
  }) => {
    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/pay`, {
        method: 'POST',
        body: data
      })
      await fetchWallet()
      return { success: true, data: response.data, message: response.message }
    } catch (e: any) {
      return {
        success: false,
        message: e.data?.message || 'Payment failed',
        requiresPinSetup: e.data?.requires_pin_setup,
        attemptsRemaining: e.data?.attempts_remaining
      }
    }
  }

  // Computed properties
  const requiresPinSetup = computed(() => wallet.value?.requires_pin_setup ?? true)
  const hasPin = computed(() => wallet.value?.has_pin ?? false)
  const canTransact = computed(() => wallet.value?.can_transact ?? false)
  const availableBalance = computed(() => wallet.value?.available_balance ?? 0)
  const availableBalanceFormatted = computed(() => wallet.value?.available_balance_formatted ?? '0.00')

  /**
   * Initiate wallet topup (add money)
   */
  const topup = async (amount: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/topup`, {
        method: 'POST',
        body: { amount }
      })

      if (response.success) {
        // Redirect to checkout page
        const checkoutUrl = response.data.checkout_url
        window.location.href = checkoutUrl
        return { success: true, data: response.data }
      } else {
        error.value = response.message || 'Failed to initiate topup'
        return { success: false, message: error.value }
      }
    } catch (e: any) {
      error.value = e.data?.message || 'Failed to initiate topup'
      return { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  return {
    // State
    wallet,
    stats,
    transactions,
    securityQuestions,
    userSecurityQuestions,
    loading,
    error,

    // Computed
    requiresPinSetup,
    hasPin,
    canTransact,
    availableBalance,
    availableBalanceFormatted,

    // Methods
    fetchWallet,
    fetchStats,
    fetchTransactions,
    fetchSecurityQuestions,
    fetchUserSecurityQuestions,
    setupPin,
    requestPinChangeOtp,
    changePin,
    verifyPin,
    verifySecurityQuestion,
    resetPinWithToken,
    sendMoney,
    withdraw,
    payViaWallet,
    topup // ⭐ NEW - Add money to wallet
  }
}
