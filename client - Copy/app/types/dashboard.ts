/**
 * Dashboard-specific type definitions used across Nuxt components.
 */

export interface DashboardPagination {
  current_page: number
  last_page: number
  per_page: number
  total: number
  has_more: boolean
}

export interface DashboardUserRef {
  uuid: string
  name: string
  type?: string
}

export interface DashboardAppointment {
  uuid: string
  title: string
  agenda: string | null
  meeting_mode: 'online' | 'offline'
  meeting_link: string | null
  start_at: string
  end_at: string | null
  status: string
  advisor?: DashboardUserRef
  mentor?: DashboardUserRef
  attendee?: DashboardUserRef
  created_at: string | null
}

export interface DashboardProgramParticipant {
  uuid: string
  role: string
  status: string
  joined_at: string | null
  inviter?: DashboardUserRef
  user?: DashboardUserRef
}

export interface DashboardProgram {
  uuid: string
  title: string
  description?: string | null
  status: string
  start_date?: string | null
  end_date?: string | null
  creator?: DashboardUserRef
  participants: DashboardProgramParticipant[]
  location?: {
    uuid: string
    title: string
    full_address: string
    city: string
    state?: string | null
    country?: string | null
  } | null
}

export interface DashboardChallenge {
  uuid: string
  title: string
  description?: string | null
  status: string
  start_at?: string | null
  end_at?: string | null
  reward: {
    type: string
    value: number
  }
  goal: {
    type: string
    value: number
  }
  target_user_type?: string
  target_level?: {
    uuid: string
    name: string
  } | null
  target_stage?: {
    uuid: string
    name: string
  } | null
  meta?: Record<string, unknown> | null
}

export interface PaginatedResponse<T> {
  items: T[]
  meta: {
    total: number
    per_page: number
    current_page: number
    last_page: number
  }
}
