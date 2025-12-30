/**
 * Catalog Type Definitions
 * Complete type safety for API responses
 */

export interface ProductImage {
  url: string
  thumbnail: string
  srcset: string | null
  responsive: Record<string, string> | null
  alt: string
  width: number | null
  height: number | null
}

export interface ProductCategory {
  name: string
  slug: string
}

export interface Product {
  name: string
  slug: string
  sku: string
  price: number
  price_formatted: string
  original_price: number | null
  original_price_formatted: string | null
  discount_percent: number | null
  sale_name: string | null
  sale_ends_at: string | null
  short_description?: string | null
  description?: string | null
  category: ProductCategory | null
  image: ProductImage | null
  gallery?: ProductImage[]
  in_stock: boolean
  stock_quantity: number
  view_count: number
  bv: number
  pv: number
  reward_points: number
}

export interface Pagination {
  current_page: number
  last_page: number
  per_page: number
  total: number
  has_more: boolean
}

export interface ProductsResponse {
  success: boolean
  data: {
    items: Product[]
    pagination: Pagination
  }
}

export interface Category {
  name: string
  slug: string
  description?: string | null
  thumbnail?: string | null
  banner?: string | null
  product_count: number
  total_products?: number
  children?: Category[]
  ancestors?: Category[]
  seo_meta?: {
    title: string
    description: string
    keywords: string[]
  } | null
}

export interface CategoriesResponse {
  success: boolean
  data: Category[]
}
