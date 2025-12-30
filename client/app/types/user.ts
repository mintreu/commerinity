export enum UserType {
  REGULAR = 'regular',
  MEMBER = 'member',
  PROMOTER = 'promoter',
  ADVISOR = 'advisor',
  MENTOR = 'mentor'
}

export enum UserStatus {
  DRAFT = 'draft',
  ACTIVE = 'active',
  INACTIVE = 'inactive',
  SUSPENDED = 'suspended',
  BANNED = 'banned'
}

export enum KycStatus {
  NOT_SUBMITTED = 'not_submitted',
  PENDING = 'pending',
  VERIFIED = 'verified',
  REJECTED = 'rejected'
}

export interface ParentInfo {
  uuid: string
  name: string
}

export interface TeamSummary {
  direct_count: number
  active_count: number
}

export interface UserPermissions {
  can_withdraw: boolean
  can_refer: boolean
  can_access_affiliate: boolean
  can_access_team: boolean
}

export interface UserFeatures {
  show_wallet: boolean
  show_network: boolean
  show_earnings: boolean
  show_team: boolean
  show_training: boolean
  show_upgrade_prompt: boolean
}

export interface User {
  // Identity
  uuid: string
  name: string
  email: string
  mobile: string

  // Verification Status
  email_verified: boolean
  mobile_verified: boolean
  email_verified_at: string | null
  mobile_verified_at: string | null

  // Affiliate Tree
  referral_code: string
  parent?: ParentInfo | null
  hasParent: boolean

  // Profile
  gender: string | null
  dob: string | null
  bio: string | null
  avatar: string

  // Type & Status (CRITICAL for personalization)
  type: UserType
  status: UserStatus
  onboarded: boolean

  // Membership
  hasLevel: boolean
  level_id: number | null

  // KYC Status
  kyc_status: KycStatus

  // Team Summary (for Affiliate users only)
  team_summary?: TeamSummary | null

  // Permissions (computed based on type/status)
  permissions: UserPermissions

  // Feature Flags (for UI rendering)
  features: UserFeatures
}

export interface AuthResponse {
  success: boolean
  data: {
    user: User
    token: string
  }
  message?: string
}

export interface ApiResponse<T = unknown> {
  success: boolean
  data?: T
  message?: string
}
