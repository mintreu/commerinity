import type { User } from '~/types/user'
import { UserType } from '~/types/user'

export const useUserType = () => {
  const config = useRuntimeConfig()
  const user = useCurrentUser() as Ref<User | null>
  const { refreshUser, isLoggedIn } = useSanctum()

  const jobApplicationsCount = useState<number | null>('jobApplicationsCount', () => null)
  const jobApplicationsLoaded = useState<boolean>('jobApplicationsLoaded', () => false)
  const jobApplicationsLoading = useState<boolean>('jobApplicationsLoading', () => false)

  const refreshJobApplicationsCount = async () => {
    if (!isLoggedIn.value) return
    if (jobApplicationsLoading.value) return

    jobApplicationsLoading.value = true

    try {
      const response = await useSanctumFetch<{ data: unknown[] }>(
        `${config.public.apiBase}/api/my-applications`
      )

      jobApplicationsCount.value = Array.isArray(response?.data) ? response.data.length : 0
    } catch {
      jobApplicationsCount.value = 0
    } finally {
      jobApplicationsLoading.value = false
      jobApplicationsLoaded.value = true
    }
  }

  watch([user, isLoggedIn], ([nextUser, loggedIn]) => {
    if (!loggedIn || !nextUser) {
      jobApplicationsCount.value = null
      jobApplicationsLoaded.value = false
      jobApplicationsLoading.value = false
      return
    }

    if (!jobApplicationsLoaded.value) {
      refreshJobApplicationsCount()
    }
  }, { immediate: true })

  const userType = computed(() => user.value?.type)

  const isRegular = computed(() => userType.value === UserType.REGULAR)
  const isMember = computed(() => userType.value === UserType.MEMBER)
  const isPromoter = computed(() => userType.value === UserType.PROMOTER)
  const isAdvisor = computed(() => userType.value === UserType.ADVISOR)
  const isMentor = computed(() => userType.value === UserType.MENTOR)

  const getLayoutForUserType = (): string => {
    if (!user.value) return 'guest'

    switch (user.value.type) {
      case UserType.REGULAR:
        return 'regular'
      case UserType.MEMBER:
        return 'member'
      case UserType.PROMOTER:
        return 'promoter'
      case UserType.ADVISOR:
        return 'advisor'
      case UserType.MENTOR:
        return 'mentor'
      default:
        return 'regular'
    }
  }

  const getDashboardRoute = (): string => {
    if (!user.value) return '/auth/login'
    return '/dashboard'
  }

  const getDashboardComponent = (): string => {
    if (!user.value) return 'DashboardRegular'

    const components: Record<UserType, string> = {
      [UserType.REGULAR]: 'DashboardRegular',
      [UserType.MEMBER]: 'DashboardMember',
      [UserType.PROMOTER]: 'DashboardPromoter',
      [UserType.ADVISOR]: 'DashboardAdvisor',
      [UserType.MENTOR]: 'DashboardMentor'
    }

    return components[user.value.type] || 'DashboardRegular'
  }

  interface NavigationItem {
    label: string
    icon: string
    to: string
    highlight?: boolean
    badge?: string
  }

  interface NavigationGroup {
    label: string
    icon: string
    items: NavigationItem[]
  }

  const getNavigationItems = (): NavigationItem[] => {
    if (!user.value) return []

    const generalItems: NavigationItem[] = [
      {
        label: 'Dashboard',
        icon: 'i-lucide-layout-dashboard',
        to: '/dashboard'
      },
      {
        label: 'Shop',
        icon: 'i-lucide-shopping-bag',
        to: '/shop'
      },
      {
        label: 'Orders',
        icon: 'i-lucide-package',
        to: '/orders'
      },
      {
        label: 'Wallet',
        icon: 'i-lucide-wallet',
        to: '/wallet'
      }
    ]

    if ((jobApplicationsCount.value ?? 0) > 0) {
      generalItems.push({
        label: 'My Applications',
        icon: 'i-lucide-briefcase',
        to: '/career/applications'
      })
    }

    const typeSpecificItems: Record<UserType, NavigationItem[]> = {
      [UserType.REGULAR]: [
        {
          label: 'Subscribe Now',
          icon: 'i-lucide-crown',
          to: '/subscription',
          highlight: true,
          badge: 'Upgrade'
        },
        {
          label: 'KYC',
          icon: 'i-lucide-shield-check',
          to: '/profile/kyc'
        }
      ],
      [UserType.MEMBER]: [
        {
          label: 'My Network',
          icon: 'i-lucide-users',
          to: '/network'
        },
        {
          label: 'Earnings',
          icon: 'i-lucide-indian-rupee',
          to: '/earnings'
        },
        {
          label: 'Subscription',
          icon: 'i-lucide-crown',
          to: '/subscription'
        }
      ],
      [UserType.PROMOTER]: [
        {
          label: 'My Network',
          icon: 'i-lucide-users',
          to: '/network'
        },
        {
          label: 'Earnings',
          icon: 'i-lucide-indian-rupee',
          to: '/earnings'
        },
        {
          label: 'Subscription',
          icon: 'i-lucide-crown',
          to: '/subscription'
        },
        {
          label: 'Team',
          icon: 'i-lucide-users-round',
          to: '/team'
        },
        {
          label: 'Challenges',
          icon: 'i-lucide-flame',
          to: '/challenges'
        }
      ],
      [UserType.ADVISOR]: [
        {
          label: 'Appointments',
          icon: 'i-lucide-calendar',
          to: '/appointments'
        },
        {
          label: 'Programs',
          icon: 'i-lucide-book-open',
          to: '/programs'
        },
        {
          label: 'My Team Leaders',
          icon: 'i-lucide-user-check',
          to: '/dashboard/team-leaders/new'
        },
        {
          label: 'Earnings',
          icon: 'i-lucide-indian-rupee',
          to: '/earnings'
        }
      ],
      [UserType.MENTOR]: [
        {
          label: 'New Session',
          icon: 'i-lucide-video',
          to: '/uptime' // placeholder route; adjust when page exists
        },
        {
          label: 'Programs',
          icon: 'i-lucide-book-open',
          to: '/programs'
        },
        {
          label: 'Appointments',
          icon: 'i-lucide-calendar',
          to: '/appointments'
        },
        {
          label: 'Analytics',
          icon: 'i-lucide-activity',
          to: '/analytics'
        },
        {
          label: 'Earnings',
          icon: 'i-lucide-indian-rupee',
          to: '/earnings'
        }
      ]
    }

    return [...generalItems, ...(typeSpecificItems[user.value.type] || [])]
  }

  const getAccountMenuItems = (): NavigationItem[] => {
    return [
      {
        label: 'My Profile',
        icon: 'i-lucide-user',
        to: '/profile'
      },
      {
        label: 'Edit Profile',
        icon: 'i-lucide-user-pen',
        to: '/profile/edit'
      },
      {
        label: 'Change Password',
        icon: 'i-lucide-key-round',
        to: '/profile/change-password'
      },
      {
        label: 'My Addresses',
        icon: 'i-lucide-map-pin',
        to: '/addresses'
      },
      {
        label: 'KYC',
        icon: 'i-lucide-shield-check',
        to: '/profile/kyc'
      },
      {
        label: 'Settings',
        icon: 'i-lucide-settings',
        to: '/profile/settings'
      },
      {
        label: 'Notifications',
        icon: 'i-lucide-bell',
        to: '/notifications'
      }
    ]
  }

  const getUserTypeLabel = (): string => {
    if (!user.value) return 'Guest'

    const labels: Record<UserType, string> = {
      [UserType.REGULAR]: 'Regular Customer',
      [UserType.MEMBER]: 'Member',
      [UserType.PROMOTER]: 'Promoter',
      [UserType.ADVISOR]: 'Advisor',
      [UserType.MENTOR]: 'Mentor'
    }

    return labels[user.value.type] || 'User'
  }

  const getUserTypeBadgeColor = (): string => {
    if (!user.value) return 'gray'

    const colors: Record<UserType, string> = {
      [UserType.REGULAR]: 'gray',
      [UserType.MEMBER]: 'blue',
      [UserType.PROMOTER]: 'green',
      [UserType.ADVISOR]: 'purple',
      [UserType.MENTOR]: 'amber'
    }

    return colors[user.value.type] || 'gray'
  }

  return {
    user,
    userType,
    isRegular,
    isMember,
    isPromoter,
    isAdvisor,
    isMentor,
    getLayoutForUserType,
    getDashboardRoute,
    getDashboardComponent,
    getNavigationItems,
    getAccountMenuItems,
    getUserTypeLabel,
    getUserTypeBadgeColor,
    refreshUser
  }
}
