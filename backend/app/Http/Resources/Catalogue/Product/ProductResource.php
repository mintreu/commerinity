<?php

namespace App\Http\Resources\Catalogue\Product;

use App\Http\Resources\Category\CategoryIndexResource;
use App\Http\Resources\Filter\FilterOptionResource;
use App\Http\Resources\Product\ProductEngagementResource;
use App\Http\Resources\Product\ProductIndexResource;
use App\Http\Resources\Product\ProductTireResource;
use App\Http\Resources\Promotion\SaleProductResource;
use Illuminate\Http\Request;

class ProductResource extends ProductIndexResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'banner'           => $this->getMedia('bannerImage')->map->getFullUrl(),
            'short_description'=> $this->short_description,
            'description'      => $this->description,
            'meta'             => $this->meta_data,

            'hasParent'        => filled($this->parent_id),

            'filter_option'    => $this->resourceCollectionWhenLoadedAndNotEmpty('filterOptions', FilterOptionResource::class),
            'categories'       => $this->resourceCollectionWhenLoadedAndNotEmpty('categories', CategoryIndexResource::class),
            'tiers'            => $this->resourceCollectionWhenLoadedAndNotEmpty('tiers', ProductTireResource::class),
            'sales'            => $this->resourceCollectionWhenLoadedAndNotEmpty('sales', SaleProductResource::class),

            'variants'         => $this->resolveVariants(),
            'engagement'       => $this->whenLoaded('engagements', fn () =>
            ProductEngagementResource::collection($this->engagements)
            ),
        ]);
    }

    /**
     * Return a collection for a relation only if loaded and not empty.
     */
    private function resourceCollectionWhenLoadedAndNotEmpty(string $relation, string $resourceClass)
    {
        return $this->when(
            $this->relationLoaded($relation) && filled($this->$relation) && $this->$relation->isNotEmpty(),
            fn () => $resourceClass::collection($this->$relation)
        );
    }

    /**
     * Resolve variants from self or parent.
     */
    private function resolveVariants()
    {
        return ProductIndexResource::collection(
            $this->whenLoaded('variants', fn () =>
            $this->variants->isNotEmpty()
                ? $this->variants
                : optional($this->parent)->variants
            )
        );
    }
}
