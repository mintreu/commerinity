/**
 * MLM Tree Composable
 *
 * Handles fetching and managing MLM tree data for org chart visualization
 */

interface TreeMember {
  id: string
  parentId: string
  userId: number
  name: string
  email: string
  referral_code: string
  type: string
  level: string
  depth: number
  image: string | null
  hasChildren: boolean
  joinedOn: string
}

interface MlmStats {
  direct_children: number
  max_direct_children: number
  available_slots: number
  total_downline: number
  tree_depth: number
}

export function useMlmTree() {
  const config = useRuntimeConfig()

  const treeData = ref<TreeMember[]>([])
  const stats = ref<MlmStats | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  const currentReferralCode = ref<string | null>(null)

  /**
   * Fetch MLM tree data for org chart
   */
  async function fetchTree(referralCode?: string): Promise<void> {
    isLoading.value = true
    error.value = null

    try {
      const url = referralCode
        ? `${config.public.apiBase}/api/mlm/tree?referral_code=${referralCode}`
        : `${config.public.apiBase}/api/mlm/tree`

      const response = await useSanctumFetch(url, { method: 'GET' })

      if (response?.success && response?.data) {
        treeData.value = response.data
        currentReferralCode.value = referralCode || null
      } else {
        treeData.value = []
        error.value = response?.message || 'Failed to fetch tree data'
      }
    } catch (err: any) {
      console.error('Failed to fetch MLM tree:', err)
      error.value = err.message || 'Failed to load network data'
      treeData.value = []
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Fetch MLM statistics
   */
  async function fetchStats(): Promise<void> {
    try {
      const response = await useSanctumFetch(`${config.public.apiBase}/api/mlm/stats`, {
        method: 'GET'
      })

      if (response?.success && response?.data) {
        stats.value = response.data
      }
    } catch (err: any) {
      console.error('Failed to fetch MLM stats:', err)
    }
  }

  /**
   * Get total members count
   */
  const totalMembers = computed(() => treeData.value.length)

  /**
   * Get active members (those with children)
   */
  const activeMembers = computed(() =>
    treeData.value.filter(m => m.hasChildren).length
  )

  /**
   * Get max depth in tree
   */
  const maxDepth = computed(() =>
    Math.max(...treeData.value.map(m => m.depth), 0)
  )

  /**
   * Get current viewing user name
   */
  const currentViewingName = computed(() => {
    if (currentReferralCode.value && treeData.value.length > 0) {
      const member = treeData.value.find(m => m.referral_code === currentReferralCode.value)
      return member?.name || 'Unknown'
    }
    return 'You'
  })

  /**
   * Reset to own tree
   */
  async function resetToOwnTree(): Promise<void> {
    currentReferralCode.value = null
    await fetchTree()
  }

  /**
   * View another member's tree
   */
  async function viewMemberTree(referralCode: string): Promise<void> {
    await fetchTree(referralCode)
  }

  return {
    treeData,
    stats,
    isLoading,
    error,
    currentReferralCode,
    totalMembers,
    activeMembers,
    maxDepth,
    currentViewingName,
    fetchTree,
    fetchStats,
    resetToOwnTree,
    viewMemberTree
  }
}
