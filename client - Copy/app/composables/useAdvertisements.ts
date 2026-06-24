export type AdType = 'native' | 'google' | 'facebook' | 'amazon' | 'custom_html' | 'affiliate'

export interface ThirdPartyPayload {
  code?: string | null
  unit_id?: string | null
  script_url?: string | null
  config?: Record<string, any>
}

export interface AdvertisementItem {
  id: number
  slug: string
  type: AdType
  type_label?: string
  placement: string
  placement_label?: string
  block?: string | null
  is_premium?: boolean
  position?: number
  position_type?: string | null
  position_type_label?: string | null
  position_config?: Record<string, any>
  title?: string | null
  description?: string | null
  link_url?: string | null
  link_text?: string | null
  open_in_new_tab?: boolean
  image?: string | null
  image_mobile?: string | null
  ad_code?: string | null
  ad_unit_id?: string | null
  third_party?: ThirdPartyPayload
  affiliate_network?: string | null
  width?: number | null
  height?: number | null
  is_responsive?: boolean
}

interface AdsResponse<T> {
  success: boolean
  data: T
  message?: string
}

interface PlacementRequest {
  placement: string
  block?: string
  position_type?: string
  page_path?: string
}

interface PendingRequest {
  request: PlacementRequest
  cacheKey: string
}

interface PendingBatch {
  timer: ReturnType<typeof setTimeout> | null
  requests: Map<string, PendingRequest>
}

const pendingBatches = new Map<string, PendingBatch>()

export const useAdvertisements = () => {
  const config = useRuntimeConfig()
  const loading = ref(false)
  const error = ref<string | null>(null)
  const cache = useState<Record<string, AdvertisementItem[]>>('ads:placement-cache', () => ({}))
  const inFlight = useState<Record<string, boolean>>('ads:in-flight', () => ({}))


  const normalizePagePath = (path?: string) => {
    if (!path || !path.trim()) return ''
    const normalized = `/${path.trim().replace(/^\/+/, '')}`
    return normalized === '//' ? '/' : normalized
  }

  const buildRequestPrefix = (request: PlacementRequest) => {
    let key = request.placement
    if (request.block) key += `_block_${request.block}`
    if (request.position_type) key += `_position_${request.position_type}`
    return key
  }

  const buildCacheKey = (request: PlacementRequest) => {
    const pagePath = normalizePagePath(request.page_path)
    return `${buildRequestPrefix(request)}|page:${pagePath || 'none'}`
  }

  const resolveBatchAds = (
    payload: Record<string, AdvertisementItem[]>,
    request: PlacementRequest
  ) => {
    const prefix = buildRequestPrefix(request)
    const candidates = Object.keys(payload)
    const matchedKey = candidates.find(key => key === prefix || key.startsWith(`${prefix}_page_`))
    return matchedKey ? (payload[matchedKey] || []) : []
  }

  const flushBatch = async (pageKey: string) => {
    const batch = pendingBatches.get(pageKey)
    if (!batch || !batch.requests.size) return

    const requests = Array.from(batch.requests.values())
    pendingBatches.delete(pageKey)

    try {
      const placements = requests.map(item => item.request)
      const pagePath = pageKey === '__NO_PAGE__' ? undefined : pageKey

      const response = await useSanctumFetch<AdsResponse<Record<string, AdvertisementItem[]>>>(
        `${config.public.apiBase}/api/ads/page`,
        {
          method: 'GET',
          query: { placements, page_path: pagePath }
        }
      )

      const payload = response.data || {}
      for (const item of requests) {
        cache.value[item.cacheKey] = resolveBatchAds(payload, item.request)
        delete inFlight.value[item.cacheKey]
      }
    } catch (err: any) {
      const message = err?.data?.message || err?.message || 'Failed to load advertisements'
      error.value = message
      for (const item of requests) {
        cache.value[item.cacheKey] = []
        delete inFlight.value[item.cacheKey]
      }
    }
  }

  const fetchPlacementAds = async (placement: string, options?: { block?: string, positionType?: string, pagePath?: string }) => {
    loading.value = true
    error.value = null

    const request: PlacementRequest = {
      placement,
      block: options?.block,
      position_type: options?.positionType,
      page_path: normalizePagePath(options?.pagePath) || undefined
    }

    const cacheKey = buildCacheKey(request)
    if (cache.value[cacheKey]) {
      loading.value = false
      return cache.value[cacheKey]
    }

    if (!inFlight.value[cacheKey]) {
      inFlight.value[cacheKey] = true
      const pageKey = request.page_path || '__NO_PAGE__'
      const batch = pendingBatches.get(pageKey) || { timer: null, requests: new Map<string, PendingRequest>() }
      batch.requests.set(cacheKey, { request, cacheKey })
      if (!batch.timer) {
        batch.timer = setTimeout(() => {
          void flushBatch(pageKey)
        }, 0)
      }
      pendingBatches.set(pageKey, batch)
    }

    const maxWaitMs = 15000
    const startedAt = Date.now()
    while (inFlight.value[cacheKey]) {
      if ((Date.now() - startedAt) > maxWaitMs) {
        break
      }
      await new Promise(resolve => setTimeout(resolve, 10))
    }

    loading.value = false
    return cache.value[cacheKey] || []
  }

  const fetchPageAds = async (placements: Array<string | PlacementRequest>, pagePath?: string) => {
    loading.value = true
    error.value = null

    try {
      const response = await useSanctumFetch<AdsResponse<Record<string, AdvertisementItem[]>>>(
        `${config.public.apiBase}/api/ads/page`,
        {
          method: 'GET',
          query: { placements, page_path: pagePath }
        }
      )

      return response.data || {}
    } catch (err: any) {
      error.value = err?.data?.message || err?.message || 'Failed to load advertisements'
      return {}
    } finally {
      loading.value = false
    }
  }

  const recordAdClick = async (advertisementId: number) => {
    try {
      await useSanctumFetch(`${config.public.apiBase}/api/ads/${advertisementId}/click`, {
        method: 'POST'
      })
    } catch {
      // silently ignore click tracking failures
    }
  }

  return {
    loading: readonly(loading),
    error: readonly(error),
    fetchPlacementAds,
    fetchPageAds,
    recordAdClick
  }
}
