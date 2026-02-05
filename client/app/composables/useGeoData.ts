/**
 * Composable for fetching geo location data (countries, states, blocks)
 * Provides dependent select functionality for address forms
 */

export interface GeoOption {
  value: string | number
  label: string
  isd_code?: number
  district?: string
  coordinates?: { lat: number, lng: number } | null
  states?: GeoOption[]
}

export function useGeoData() {
  const config = useRuntimeConfig()

  const countries = ref<GeoOption[]>([])
  const statesByCountry = ref<Record<string, GeoOption[]>>({})
  const states = ref<GeoOption[]>([])
  const blocks = ref<GeoOption[]>([])
  const districts = ref<GeoOption[]>([])

  const loadingCountries = ref(false)
  const loadingStates = ref(false)
  const loadingBlocks = ref(false)
  const loadingDistricts = ref(false)

  /**
   * Fetch all countries
   */
  const fetchCountries = async () => {
    loadingCountries.value = true
    try {
      const response = await useSanctumFetch<{ data: GeoOption[] }>(
        `${config.public.apiBase}/api/geo/countries?with_states=1`
      )

      const fetchedCountries = response.data || []

      // Extract countries and cache their states
      countries.value = fetchedCountries.map(country => ({
        value: country.value,
        label: country.label,
        isd_code: country.isd_code
      }))

      // Cache states for each country
      statesByCountry.value = {}
      fetchedCountries.forEach((country) => {
        if (country.states && Array.isArray(country.states)) {
          statesByCountry.value[String(country.value)] = country.states
        }
      })

      return countries.value
    } catch (error) {
      console.error('Failed to fetch countries:', error)
      return []
    } finally {
      loadingCountries.value = false
    }
  }

  /**
   * Fetch states for a country
   */
  const fetchStates = async (countryCode: string) => {
    if (!countryCode) {
      states.value = []
      return []
    }

    const cachedStates = statesByCountry.value[countryCode]
    if (cachedStates && cachedStates.length) {
      states.value = cachedStates
      return states.value
    }

    loadingStates.value = true
    try {
      const response = await useSanctumFetch<{ data: GeoOption[] }>(
        `${config.public.apiBase}/api/geo/states?country_code=${countryCode}`
      )
      states.value = response.data || []
      return states.value
    } catch (error) {
      console.error('Failed to fetch states:', error)
      states.value = []
      return []
    } finally {
      loadingStates.value = false
    }
  }

  /**
   * Fetch blocks for a state
   */
  const fetchBlocks = async (stateCode: string) => {
    if (!stateCode) {
      blocks.value = []
      return []
    }

    loadingBlocks.value = true
    try {
      const response = await useSanctumFetch<{ data: GeoOption[] }>(
        `${config.public.apiBase}/api/geo/blocks?state_code=${stateCode}`
      )
      blocks.value = response.data || []
      return blocks.value
    } catch (error) {
      console.error('Failed to fetch blocks:', error)
      blocks.value = []
      return []
    } finally {
      loadingBlocks.value = false
    }
  }

  /**
   * Fetch districts for a state
   */
  const fetchDistricts = async (stateCode: string) => {
    if (!stateCode) {
      districts.value = []
      return []
    }

    loadingDistricts.value = true
    try {
      const response = await useSanctumFetch<{ data: GeoOption[] }>(
        `${config.public.apiBase}/api/geo/districts?state_code=${stateCode}`
      )
      districts.value = response.data || []
      return districts.value
    } catch (error) {
      console.error('Failed to fetch districts:', error)
      districts.value = []
      return []
    } finally {
      loadingDistricts.value = false
    }
  }

  /**
   * Reset dependent fields when parent changes
   */
  const resetStates = () => {
    states.value = []
    resetBlocks()
  }

  const resetBlocks = () => {
    blocks.value = []
    districts.value = []
  }

  return {
    // Data
    countries,
    states,
    blocks,
    districts,

    // Loading states
    loadingCountries,
    loadingStates,
    loadingBlocks,
    loadingDistricts,

    // Methods
    fetchCountries,
    fetchStates,
    fetchBlocks,
    fetchDistricts,
    resetStates,
    resetBlocks
  }
}
