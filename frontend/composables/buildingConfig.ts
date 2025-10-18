// buildingConfig.ts - Centralized building management
export interface BuildingConfig {
    id: string
    name: string
    icon: string
    description: string
    route: string
    category: 'commerce' | 'health' | 'service' | 'entertainment' | 'utility'
    color: number
    position: { x: number, z: number }
    status: 'active' | 'disabled' | 'coming_soon'
    level: number
    maxLevel: number
    score: number
    upgradeable: boolean
    requiredUserLevel?: number
    cta: string
    hasModal: boolean
    modalId?: string
    size: { width: number, height: number, depth: number }
}

export const BUILDINGS: BuildingConfig[] = [
    // Active Buildings
    {
        id: 'store',
        name: 'Shopping Mall',
        icon: '🛍️',
        description: 'Browse products and shop',
        route: '/store',
        category: 'commerce',
        color: 0xff6b6b,
        position: { x: -60, z: 40 },
        status: 'active',
        level: 1,
        maxLevel: 5,
        score: 100,
        upgradeable: true,
        cta: 'Shop Now',
        hasModal: false,
        size: { width: 12, height: 15, depth: 12 }
    },
    {
        id: 'wallet',
        name: 'Central Bank',
        icon: '🏦',
        description: 'Manage your wallet',
        route: '/wallet',
        category: 'utility',
        color: 0x4ecdc4,
        position: { x: 60, z: 40 },
        status: 'active',
        level: 1,
        maxLevel: 5,
        score: 80,
        upgradeable: true,
        cta: 'View Wallet',
        hasModal: false,
        size: { width: 10, height: 12, depth: 10 }
    },
    {
        id: 'health',
        name: 'Health Clinic',
        icon: '🏥',
        description: 'Health & wellness products',
        route: '/category/health',
        category: 'health',
        color: 0x95e1d3,
        position: { x: -60, z: -10 },
        status: 'active',
        level: 1,
        maxLevel: 3,
        score: 60,
        upgradeable: true,
        cta: 'Visit Clinic',
        hasModal: false,
        size: { width: 9, height: 11, depth: 9 }
    },
    {
        id: 'grocery',
        name: 'Farm Market',
        icon: '🌾',
        description: 'Fresh groceries',
        route: '/category/grocery',
        category: 'commerce',
        color: 0xf7dc6f,
        position: { x: 60, z: -10 },
        status: 'active',
        level: 1,
        maxLevel: 4,
        score: 70,
        upgradeable: true,
        cta: 'Visit Market',
        hasModal: false,
        size: { width: 9, height: 10, depth: 9 }
    },
    {
        id: 'beauty',
        name: 'Beauty Salon',
        icon: '💄',
        description: 'Makeup & grooming',
        route: '/category/beauty',
        category: 'service',
        color: 0xffa07a,
        position: { x: -60, z: -50 },
        status: 'active',
        level: 1,
        maxLevel: 3,
        score: 50,
        upgradeable: true,
        cta: 'Get Groomed',
        hasModal: false,
        size: { width: 8, height: 10, depth: 8 }
    },
    {
        id: 'support',
        name: 'Help Center',
        icon: '🆘',
        description: 'Get support',
        route: '/helpdesk',
        category: 'service',
        color: 0x87ceeb,
        position: { x: 60, z: -50 },
        status: 'active',
        level: 1,
        maxLevel: 2,
        score: 40,
        upgradeable: false,
        cta: 'Get Help',
        hasModal: true,
        modalId: 'support-modal',
        size: { width: 7, height: 9, depth: 7 }
    },

    // Disabled Buildings (Coming Soon)
    {
        id: 'entertainment',
        name: 'Cinema Hall',
        icon: '🎬',
        description: 'Entertainment center',
        route: '/entertainment',
        category: 'entertainment',
        color: 0x9b59b6,
        position: { x: -30, z: -50 },
        status: 'disabled',
        level: 0,
        maxLevel: 5,
        score: 0,
        upgradeable: true,
        requiredUserLevel: 2,
        cta: 'Coming Soon',
        hasModal: false,
        size: { width: 10, height: 12, depth: 10 }
    },
    {
        id: 'education',
        name: 'Learning Hub',
        icon: '📚',
        description: 'Educational resources',
        route: '/education',
        category: 'service',
        color: 0x3498db,
        position: { x: 30, z: -50 },
        status: 'coming_soon',
        level: 0,
        maxLevel: 4,
        score: 0,
        upgradeable: true,
        requiredUserLevel: 3,
        cta: 'Coming Soon',
        hasModal: false,
        size: { width: 9, height: 11, depth: 9 }
    }
]

// Vehicle configurations
export const VEHICLES = [
    { type: 'car', colors: [0xff0000, 0x00ff00, 0x0000ff, 0xffff00, 0xff00ff], weight: 50 },
    { type: 'bus', colors: [0xffa500, 0x4169e1], weight: 20 },
    { type: 'bike', colors: [0x000000, 0xff0000, 0x00ff00], weight: 30 }
]

// Tree configurations
export const TREE_TYPES = [
    { name: 'oak', trunkColor: 0x78350f, foliageColor: 0x22c55e, foliageRadius: 2.5 },
    { name: 'pine', trunkColor: 0x654321, foliageColor: 0x228b22, foliageRadius: 2 }
]
