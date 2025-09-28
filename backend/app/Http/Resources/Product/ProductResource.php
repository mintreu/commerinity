<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Category\CategoryIndexResource;
use App\Http\Resources\Filter\FilterGroupResource;
use App\Http\Resources\Filter\FilterOptionResource;
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
        return array_merge(parent::toArray($request),[

            'type' => $this->type->getLabel(),
            'hasParent' => !is_null($this->parent_id),
            'short_description' => $this->short_description,
            'description' => $this->description,
            'reward_point' => $this->reward_point,
            'thumbnail' => $this->getFirstMediaUrl('displayImage'),
            'banner' => $this->getMedia('bannerImage')->map(fn($media) => $media->getFullUrl()),
            'meta' => $this->meta_data,
            'parent' => new ProductResource($this->whenLoaded('parent')),

            'filterGroup' => new FilterGroupResource($this->whenLoaded('filterGroup')),


            'filter_option' => $this->resourceCollectionWhenLoadedAndNotEmpty('filterOptions', FilterOptionResource::class),
            'variants' => $this->resourceCollectionWhenLoadedAndNotEmpty('variants', ProductIndexResource::class),
            'categories' => $this->resourceCollectionWhenLoadedAndNotEmpty('categories', CategoryIndexResource::class),
            'tiers' => $this->resourceCollectionWhenLoadedAndNotEmpty('tiers', ProductTireResource::class),
            'sales' => $this->resourceCollectionWhenLoadedAndNotEmpty('sales', SaleProductResource::class),

        ]);
    }



    private function resourceCollectionWhenLoadedAndNotEmpty($relation, $resourceClass)
    {
        return $this->when(
            $this->relationLoaded($relation) && $this->$relation->isNotEmpty(),
            fn () => $resourceClass::collection($this->$relation)
        );
    }



}
