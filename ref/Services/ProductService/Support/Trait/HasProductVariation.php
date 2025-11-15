<?php

namespace App\Services\ProductService\Support\Trait;


use App\Models\Store\Attribute\FilterOption;
use App\Models\Store\Product\Product;
use Illuminate\Support\Str;

trait HasProductVariation
{



    private function createMultipleVariants(array $data,Product $product): Product
    {
        // Multiple Case
        $allFilterable = $this->arrayPermutation($data['filter_options']);

        $ids = collect($data['filter_options'])->flatten()->all();
        $records = FilterOption::whereIn('id', $ids)->get();

        $dataBag = [];
        foreach ($allFilterable as $key => $permutation) {

            $matchingPermutation = collect($permutation)->mapWithKeys(function ($id, $attribute) use ($records) {
                $matchedRecord = $records->where('id',$id)->first();
                return [$attribute => $matchedRecord->code]; // Fallback to ID if not found
            })->all();

            $dataBag[$key]['name'] = $product->sku;
            $dataBag[$key]['url'] = Str::slug($product->sku.'-variant-'.implode('-', $matchingPermutation)).'-'.now()->timestamp;
            $dataBag[$key]['sku'] = Str::slug($product->sku.'-variant-'.implode('-', $matchingPermutation));
            $dataBag[$key]['filter_group_id'] = $product->filter_group_id;
            $dataBag[$key]['type'] = 'simple';
//            $dataBag[$key]['vendor_id'] = $product->vendor_id;
            //          $dataBag[$key]['product_id'] = $product->id;
            $dataBag[$key]['filter_attributes'] = $permutation;
        }



        // Create Variants Products
        $variants = $product->variants()->createMany($dataBag);



        $filterOptions = FilterOption::whereIn('id',collect($dataBag)->groupBy('filter_attributes')->keys()->toArray())->get();

        $variants->each(function ($item, $key) use ($dataBag,$filterOptions) {

            if ($dataBag[$key]['sku'] == $item->sku) {

                foreach ($dataBag[$key]['filter_attributes'] as $key => $value) {

                    $filter = $filterOptions->where('id',$value)->first();
                    // attach filterOptions  ('filter_attributes') must hold ids
                    $item->filterOptions()->attach($filter->id);
                }

            }
        });

        return $product;
    }


    /**
     * @param $input
     * @return array
     */
    public function arrayPermutation($input): array
    {
        $results = [];

        foreach ($input as $key => $values) {
            if (empty($values)) {
                continue;
            }

            if (empty($results)) {
                foreach ($values as $value) {
                    $results[] = [$key => $value];
                }
            } else {
                $append = [];

                foreach ($results as &$result) {
                    $result[$key] = array_shift($values);

                    $copy = $result;

                    foreach ($values as $item) {
                        $copy[$key] = $item;
                        $append[] = $copy;
                    }

                    array_unshift($values, $result[$key]);
                }

                $results = array_merge($results, $append);
            }
        }

        return $results;
    }




}
