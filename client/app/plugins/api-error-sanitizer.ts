import { defineNuxtPlugin } from '#app'
import { getContextFromUrl, getContextualApiError } from '../utils/api-error'

const enhanceFetchError = (error: unknown, meta: ReturnType<typeof getContextualApiError>) => {
  const normalizedError = error instanceof Error ? error : new Error(meta.message)
  const previousMessage = normalizedError.message
  normalizedError.message = meta.message
  ;(normalizedError as Record<string, unknown>).safeMessage = meta.message
  ;(normalizedError as Record<string, unknown>).friendlyKind = meta.kind
  ;(normalizedError as Record<string, unknown>).originalMessage = previousMessage ?? meta.originalMessage
  if (meta.status) {
    ;(normalizedError as Record<string, unknown>).status = meta.status
  }
  return normalizedError
}

export default defineNuxtPlugin((nuxtApp) => {
  type FetchFunction = typeof $fetch
  const globalAny = globalThis as Record<string, unknown>
  const baseFetch: unknown =
    nuxtApp.$fetch
    ?? globalAny.$fetch
    ?? globalAny.fetch

  if (typeof baseFetch !== 'function') {
    return
  }

  const fetchContext = nuxtApp.$fetch ? nuxtApp : globalAny
  const nativeFetch: FetchFunction = (baseFetch as FetchFunction).bind(fetchContext)

  const patchedFetch: FetchFunction = async (...args) => {
    try {
      return await nativeFetch(...args)
    } catch (error: unknown) {
      const request = args[0]
      const options = args[1] as Record<string, unknown> | undefined
      const url = typeof request === 'string'
        ? request
        : request instanceof URL
          ? request.toString()
          : (request as Request | undefined)?.url
      const context =
        (typeof options?.context === 'string' ? options.context : null)
        ?? getContextFromUrl(url)
      const meta = getContextualApiError(error, context, url)
      const enrichedError = enhanceFetchError(error, meta)

      if (process.client || process.dev) {
        console.error('[API] Request failed', {
          message: meta.message,
          status: meta.status,
          kind: meta.kind,
          originalMessage: meta.originalMessage,
          payload: error
        })
      }

      throw enrichedError
    }
  }

  const baseFetchWithCreate = baseFetch as FetchFunction & { create?: (...args: unknown[]) => FetchFunction }
  if (typeof baseFetchWithCreate.create === 'function') {
    ;(patchedFetch as FetchFunction & { create: (...args: unknown[]) => FetchFunction }).create =
      (...createArgs: unknown[]) => {
        const created = baseFetchWithCreate.create?.(...createArgs)
        if (typeof created !== 'function') {
          return created as FetchFunction
        }
        return (async (...innerArgs: Parameters<FetchFunction>) => {
          try {
            return await created(...innerArgs)
          } catch (error: unknown) {
            const request = innerArgs[0]
            const options = innerArgs[1] as Record<string, unknown> | undefined
            const url = typeof request === 'string'
              ? request
              : request instanceof URL
                ? request.toString()
                : (request as Request | undefined)?.url
            const context =
              (typeof options?.context === 'string' ? options.context : null)
              ?? getContextFromUrl(url)
            const meta = getContextualApiError(error, context, url)
            throw enhanceFetchError(error, meta)
          }
        }) as FetchFunction
      }
  }

  if (nuxtApp.$fetch) {
    nuxtApp.$fetch = patchedFetch
  }
  if (typeof globalAny.$fetch === 'function') {
    globalAny.$fetch = patchedFetch
  }

  return {
    provide: {
      fetch: patchedFetch
    }
  }
})
