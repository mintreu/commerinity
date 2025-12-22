<?php

namespace Mintreu\Toolkit\Traits;

trait HasResourceSupport
{


    private function resourceCollectionWhenLoadedAndNotEmpty($relation, $resourceClass)
    {
        return $this->when(
            $this->relationLoaded($relation) && !is_null($this->$relation),
            function () use ($relation, $resourceClass) {
                $data = $this->$relation;

                // Check if the relation is a Collection or has multiple records
                if ($data instanceof \Illuminate\Support\Collection || $data instanceof \Illuminate\Database\Eloquent\Collection) {
                    return $data->isNotEmpty() ? $resourceClass::collection($data) : null;
                }

                // Handle single model instance
                return $data instanceof \Illuminate\Database\Eloquent\Model ? $resourceClass::make($data) : null;
            }
        );
    }



}
