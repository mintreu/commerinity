<?php

namespace Mintreu\Toolkit\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

trait HasResourceSupport
{


    private function resourceCollectionWhenLoadedAndNotEmpty($relation, $resourceClass)
    {
        return $this->when(
            $this->relationLoaded($relation) && !is_null($this->$relation),
            function () use ($relation, $resourceClass) {
                $data = $this->$relation;

                // Check if the relation is a Collection or has multiple records
                if ($data instanceof Collection || $data instanceof \Illuminate\Database\Eloquent\Collection) {
                    return $data->isNotEmpty() ? $resourceClass::collection($data) : null;
                }

                // Handle single model instance
                return $data instanceof Model ? $resourceClass::make($data) : null;
            }
        );
    }



}
