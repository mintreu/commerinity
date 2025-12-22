<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FilterScope
{

    public function apply($query, Request $request)
    {
        if (! $request->has('filter')) {
            return $query;
        }

        $filters = $request->get('filter', []);

        if (! is_array($filters)) {
            return $query;
        }

        foreach ($filters as $filterName => $values) {
            if ($values) {
                $filterValues = array_map('trim', explode(',', $values));

                // Find base products through their variants' filter options
                $query->where(function ($query) use ($filterName, $filterValues) {
                    $query->whereHas('variants', function (Builder $variantQuery) use ($filterName, $filterValues) {
                        $variantQuery->whereHas('filterOptions', function (Builder $builder) use ($filterName, $filterValues) {
                            $builder->whereHas('filter', function (Builder $query) use ($filterName) {
                                $query->where('name', $filterName);
                            })
                                ->whereIn('value', $filterValues);
                        });
                    })
                        // For simple products that have their own filter options
                        ->orWhereHas('filterOptions', function (Builder $builder) use ($filterName, $filterValues) {
                            $builder->whereHas('filter', function (Builder $query) use ($filterName) {
                                $query->where('name', $filterName);
                            })
                                ->whereIn('value', $filterValues);
                        });
                });
            }
        }

        return $query;
    }

}
