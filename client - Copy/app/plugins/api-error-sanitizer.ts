import { defineNuxtPlugin } from '#app'
import { getContextFromUrl, getContextualApiError } from '../utils/api-error'

const enhanceFetchError = (error: unknown, meta: ReturnType<typeof getContextualApiError>) => {
  const originalError = error instanceof Error ? error : null
  const previousMessage = originalError?.message
  const wrappedError = new Error(meta.message)

  if (originalError?.stack) {
    wrappedError.stack = originalError.stack
  }

  const target = wrappedError as Record<string, unknown>
  target.safeMessage = meta.message
  target.friendlyKind = meta.kind
  target.originalMessage = previousMessage ?? meta.originalMessage
  if (meta.status) {
    target.status = meta.status
  }
  if (error && typeof error === 'object') {
    target.cause = error
  }

  return wrappedError
}

export default defineNuxtPlugin((nuxtApp) => {
  type FetchFunction = typeof $fetch
  const globalAny = globalThis as Record<string, unknown>
  const baseFetch: unknown = nuxtApp.$fetch ?? globalAny.$fetch ?? globalAny.fetch

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
      const context = (typeof options?.context === 'string' ? options.context : null) ?? getContextFromUrl(url)
      const meta = getContextualApiError(error, context, url)
      const enrichedError = enhanceFetchError(error, meta)

      if (import.meta.client || import.meta.dev) {
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
    const patchedFetchWithCreate = patchedFetch as FetchFunction & { create: (...args: unknown[]) => FetchFunction }
    patchedFetchWithCreate.create = (...createArgs: unknown[]) => {
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
          const context = (typeof options?.context === 'string' ? options.context : null) ?? getContextFromUrl(url)
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
