// composables/useWishlist.ts

import { useSanctum, useSanctumFetch, useRuntimeConfig } from '#imports'

export const useWishlist = () => {
    const { isLoggedIn } = useSanctum()
    const config = useRuntimeConfig()

    // Add to wishlist
    const addToWishlist = async (productUrl: string) => {
        if (!isLoggedIn.value) {
            throw new Error('Please login to add items to wishlist')
        }

        try {
            const response = await useSanctumFetch(
                `${config.public.apiBase}/product/wishlist/${productUrl}`,
                {
                    method: 'POST',
                    credentials: 'include'
                }
            )

            return response
        } catch (error: any) {
            console.error('[Wishlist] Add failed:', error)

            // Handle specific errors
            if (error.status === 401) {
                throw new Error('Please login to add items to wishlist')
            } else if (error.status === 404) {
                throw new Error('Product not found')
            } else if (error.status >= 500) {
                throw new Error('Server error. Please try again.')
            }

            throw new Error('Failed to add to wishlist')
        }
    }

    // Remove from wishlist
    const removeFromWishlist = async (productUrl: string) => {
        if (!isLoggedIn.value) {
            throw new Error('Please login to manage wishlist')
        }

        try {
            const response = await useSanctumFetch(
                `${config.public.apiBase}/product/wishlist/${productUrl}`,
                {
                    method: 'DELETE',
                    credentials: 'include'
                }
            )

            return response
        } catch (error: any) {
            console.error('[Wishlist] Remove failed:', error)

            // Handle specific errors
            if (error.status === 401) {
                throw new Error('Please login to manage wishlist')
            } else if (error.status === 404) {
                throw new Error('Product not found')
            } else if (error.status >= 500) {
                throw new Error('Server error. Please try again.')
            }

            throw new Error('Failed to remove from wishlist')
        }
    }

    // Toggle wishlist status
    const toggleWishlist = async (productUrl: string, isCurrentlyWishlisted: boolean) => {
        if (isCurrentlyWishlisted) {
            return await removeFromWishlist(productUrl)
        } else {
            return await addToWishlist(productUrl)
        }
    }

    return {
        addToWishlist,
        removeFromWishlist,
        toggleWishlist,
        isLoggedIn
    }
}
