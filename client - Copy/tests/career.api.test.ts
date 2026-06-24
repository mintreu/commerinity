import { describe, it, expect, beforeAll } from 'vitest'

/**
 * Career API Tests
 *
 * Tests the career/recruitment API endpoints to ensure:
 * - Proper data format from backend
 * - Correct field types and structure
 * - Filter functionality
 * - Real seeded data validation
 *
 * NOTE: Run `php artisan serve` in apiserver before running tests
 */

const apiBase = process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000'

interface Recruitment {
  id: number
  uuid: string
  slug: string
  title: string
  description: string
  role: string
  role_label: string
  location: string
  employment_type: string
  employment_type_label: string
  vacancy: number
  open_date: string
  close_date: string
  open_date_formatted: string
  close_date_formatted: string
  is_payable: boolean
  fees: number
  fees_formatted: string
  fees_in_rupees: number
  requirements: string[]
  benefits: string[]
  eligibility: {
    max_age: number
    min_age: number
  }
  status: string
  status_label: string
  is_open: boolean
  display_image: string
  info_pdf: string
  created_at: string
  updated_at: string
}

interface FilterOption {
  value: string
  label: string
}

interface FiltersData {
  roles: FilterOption[]
  types: FilterOption[]
  counts_by_role: Record<string, number>
}

interface PaginationMeta {
  current_page: number
  from: number
  last_page: number
  per_page: number
  to: number
  total: number
}

describe('Career API Tests', () => {
  let recruitments: Recruitment[] = []
  let filtersData: FiltersData | null = null
  let paginationMeta: PaginationMeta | null = null

  beforeAll(async () => {
    // Fetch careers data
    const careersResponse = await fetch(`${apiBase}/api/careers`)
    const careersJson = await careersResponse.json()
    recruitments = careersJson.data || []
    paginationMeta = careersJson.meta || null

    // Fetch filters data
    const filtersResponse = await fetch(`${apiBase}/api/careers/filters`)
    const filtersJson = await filtersResponse.json()
    filtersData = filtersJson.data || null
  })

  describe('Career Listing Endpoint', () => {
    it('should return successful response', async () => {
      const response = await fetch(`${apiBase}/api/careers`)
      expect(response.ok).toBe(true)
      expect(response.status).toBe(200)
    })

    it('should return data array', () => {
      expect(Array.isArray(recruitments)).toBe(true)
    })

    it('should have seeded recruitment data', () => {
      expect(recruitments.length).toBeGreaterThan(0)
    })

    it('should have pagination metadata', () => {
      expect(paginationMeta).not.toBeNull()
      expect(paginationMeta?.total).toBeGreaterThan(0)
      expect(paginationMeta?.per_page).toBeGreaterThan(0)
    })
  })

  describe('Recruitment Data Structure', () => {
    it('each recruitment should have required identity fields', () => {
      for (const job of recruitments) {
        expect(job.id).toBeDefined()
        expect(typeof job.id).toBe('number')
        expect(job.uuid).toBeDefined()
        expect(typeof job.uuid).toBe('string')
        expect(job.slug).toBeDefined()
        expect(typeof job.slug).toBe('string')
      }
    })

    it('each recruitment should have core fields', () => {
      for (const job of recruitments) {
        expect(job.title).toBeDefined()
        expect(typeof job.title).toBe('string')
        expect(job.title.length).toBeGreaterThan(0)
        expect(job.description).toBeDefined()
        expect(typeof job.description).toBe('string')
      }
    })

    it('each recruitment should have role and type labels', () => {
      for (const job of recruitments) {
        expect(job.role).toBeDefined()
        expect(job.role_label).toBeDefined()
        expect(job.employment_type).toBeDefined()
        expect(job.employment_type_label).toBeDefined()
      }
    })

    it('each recruitment should have vacancy > 0', () => {
      for (const job of recruitments) {
        expect(typeof job.vacancy).toBe('number')
        expect(job.vacancy).toBeGreaterThan(0)
      }
    })

    it('should have properly formatted dates', () => {
      for (const job of recruitments) {
        // Check formatted date looks like "DD Mon YYYY"
        expect(job.open_date_formatted).toMatch(/^\d{2} \w{3} \d{4}$/)
        expect(job.close_date_formatted).toMatch(/^\d{2} \w{3} \d{4}$/)
      }
    })

    it('should have properly formatted fees', () => {
      for (const job of recruitments) {
        // Should start with currency symbol
        expect(job.fees_formatted).toMatch(/^₹[\d,]+\.\d{2}$/)
      }
    })

    it('UUID should be valid format', () => {
      const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i
      for (const job of recruitments) {
        expect(job.uuid).toMatch(uuidRegex)
      }
    })

    it('slug should be URL-safe', () => {
      const slugRegex = /^[a-z0-9-]+$/i
      for (const job of recruitments) {
        expect(job.slug).toMatch(slugRegex)
      }
    })

    it('requirements and benefits should be arrays', () => {
      for (const job of recruitments) {
        expect(Array.isArray(job.requirements)).toBe(true)
        expect(Array.isArray(job.benefits)).toBe(true)
      }
    })

    it('eligibility should have age limits', () => {
      for (const job of recruitments) {
        expect(job.eligibility).toBeDefined()
        expect(typeof job.eligibility.min_age).toBe('number')
        expect(typeof job.eligibility.max_age).toBe('number')
      }
    })
  })

  describe('Filters Endpoint', () => {
    it('should return successful response', async () => {
      const response = await fetch(`${apiBase}/api/careers/filters`)
      expect(response.ok).toBe(true)
      expect(response.status).toBe(200)
    })

    it('should have roles array with value/label format', () => {
      expect(filtersData).not.toBeNull()
      expect(Array.isArray(filtersData?.roles)).toBe(true)
      expect(filtersData!.roles.length).toBeGreaterThan(0)

      for (const role of filtersData!.roles) {
        expect(role.value).toBeDefined()
        expect(typeof role.value).toBe('string')
        expect(role.label).toBeDefined()
        expect(typeof role.label).toBe('string')
      }
    })

    it('should have types array with value/label format', () => {
      expect(Array.isArray(filtersData?.types)).toBe(true)
      expect(filtersData!.types.length).toBeGreaterThan(0)

      for (const type of filtersData!.types) {
        expect(type.value).toBeDefined()
        expect(typeof type.value).toBe('string')
        expect(type.label).toBeDefined()
        expect(typeof type.label).toBe('string')
      }
    })
  })

  describe('Filter Functionality', () => {
    it('should filter by advisor role', async () => {
      const response = await fetch(`${apiBase}/api/careers?role=advisor`)
      const json = await response.json()

      expect(response.ok).toBe(true)
      expect(Array.isArray(json.data)).toBe(true)

      for (const job of json.data) {
        expect(job.role).toBe('advisor')
      }
    })

    it('should filter by intern role', async () => {
      const response = await fetch(`${apiBase}/api/careers?role=intern`)
      const json = await response.json()

      expect(response.ok).toBe(true)
      for (const job of json.data) {
        expect(job.role).toBe('intern')
      }
    })

    it('should filter by full_time type', async () => {
      const response = await fetch(`${apiBase}/api/careers?type=full_time`)
      const json = await response.json()

      expect(response.ok).toBe(true)
      for (const job of json.data) {
        expect(job.employment_type).toBe('full_time')
      }
    })

    it('should filter by internship type', async () => {
      const response = await fetch(`${apiBase}/api/careers?type=internship`)
      const json = await response.json()

      expect(response.ok).toBe(true)
      for (const job of json.data) {
        expect(job.employment_type).toBe('internship')
      }
    })

    it('should filter by both role and type', async () => {
      const response = await fetch(`${apiBase}/api/careers?role=advisor&type=full_time`)
      const json = await response.json()

      expect(response.ok).toBe(true)
      for (const job of json.data) {
        expect(job.role).toBe('advisor')
        expect(job.employment_type).toBe('full_time')
      }
    })
  })

  describe('Single Career Detail', () => {
    it('should return single career by slug', async () => {
      if (recruitments.length === 0) return

      const firstJob = recruitments[0]
      const response = await fetch(`${apiBase}/api/careers/${firstJob.slug}`)

      expect(response.ok).toBe(true)

      const json = await response.json()
      expect(json.data).toBeDefined()
      expect(json.data.slug).toBe(firstJob.slug)
      expect(json.data.uuid).toBe(firstJob.uuid)
    })

    it('should return 404 for non-existent slug', async () => {
      const response = await fetch(`${apiBase}/api/careers/non-existent-job-slug-12345`)
      expect(response.status).toBe(404)
    })
  })

  describe('Seeded Data Validation', () => {
    it('should have multiple roles', () => {
      const roles = new Set(recruitments.map(r => r.role))
      expect(roles.size).toBeGreaterThanOrEqual(3)
    })

    it('should have multiple employment types', () => {
      const types = new Set(recruitments.map(r => r.employment_type))
      expect(types.size).toBeGreaterThanOrEqual(2)
    })

    it('should have multiple locations', () => {
      const locations = new Set(recruitments.map(r => r.location))
      expect(locations.size).toBeGreaterThanOrEqual(3)
    })

    it('should have mix of payable and free positions', () => {
      const payable = recruitments.filter(r => r.is_payable)
      const free = recruitments.filter(r => !r.is_payable)

      expect(payable.length).toBeGreaterThan(0)
      expect(free.length).toBeGreaterThan(0)
    })

    it('all positions should be open', () => {
      for (const job of recruitments) {
        expect(job.is_open).toBe(true)
      }
    })
  })
})
