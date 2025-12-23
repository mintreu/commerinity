import type { User } from '~/types/user'
import { UserType } from '~/types/user'

export const useUserType = () => {
  const user = useCurrentUser() as Ref<User | null>
  const { refreshUser } = useSanctum()

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

    const baseItems: NavigationItem[] = [
      {
        label: 'Dashboard',
        icon: 'i-lucide-layout-dashboard',
        to: '/dashboard'
      }
    ]

    // Add Subscribe Now for regular users (highlighted)
    if (user.value.type === UserType.REGULAR) {
      baseItems.push({
        label: 'Subscribe Now',
        icon: 'i-lucide-crown',
        to: '/subscription',
        highlight: true,
        badge: 'Upgrade'
      })
    }

    const typeSpecificItems: Record<UserType, NavigationItem[]> = {
      [UserType.REGULAR]: [
        {
          label: 'Shop',
          icon: 'i-lucide-shopping-bag',
          to: '/shop'
        },
        {
          label: 'Orders',
          icon: 'i-lucide-package',
          to: '/orders'
        }
      ],
      [UserType.MEMBER]: [
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
          label: 'My Network',
          icon: 'i-lucide-users',
          to: '/network'
        },
        {
          label: 'Wallet',
          icon: 'i-lucide-wallet',
          to: '/wallet'
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
          label: 'My Network',
          icon: 'i-lucide-users',
          to: '/network'
        },
        {
          label: 'Wallet',
          icon: 'i-lucide-wallet',
          to: '/wallet'
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
          label: 'Marketing',
          icon: 'i-lucide-megaphone',
          to: '/marketing'
        }
      ],
      [UserType.ADVISOR]: [
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
        },
        {
          label: 'Earnings',
          icon: 'i-lucide-indian-rupee',
          to: '/earnings'
        },
        {
          label: 'My Team',
          icon: 'i-lucide-users-round',
          to: '/team'
        },
        {
          label: 'Clients',
          icon: 'i-lucide-users',
          to: '/clients'
        },
        {
          label: 'Reports',
          icon: 'i-lucide-bar-chart',
          to: '/reports'
        },
        {
          label: 'Training',
          icon: 'i-lucide-graduation-cap',
          to: '/training'
        }
      ],
      [UserType.MENTOR]: [
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
          label: 'My Network',
          icon: 'i-lucide-users',
          to: '/network'
        },
        {
          label: 'Wallet',
          icon: 'i-lucide-wallet',
          to: '/wallet'
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
          label: 'Reports',
          icon: 'i-lucide-bar-chart',
          to: '/reports'
        },
        {
          label: 'Training',
          icon: 'i-lucide-graduation-cap',
          to: '/training'
        },
        {
          label: 'Leadership',
          icon: 'i-lucide-star',
          to: '/leadership'
        },
        {
          label: 'Analytics',
          icon: 'i-lucide-line-chart',
          to: '/analytics'
        }
      ]
    }

    return [...baseItems, ...(typeSpecificItems[user.value.type] || [])]
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
