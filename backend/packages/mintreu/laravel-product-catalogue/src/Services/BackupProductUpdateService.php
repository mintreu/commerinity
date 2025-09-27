<?php

namespace Mintreu\LaravelProductCatalogue\Services;

use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\LaravelProductCatalogue\Services\Support\HasProductSupport;

class BackupProductUpdateService
{

    use HasProductSupport;

    protected Model|Product $product;
    protected array $data;
    protected bool $recreateVariants = false;

    /**
     * @param Product|Model $product
     * @param array $data
     */
    public function __construct(Model|Product $product)
    {
        $this->product = $product;

    }


    public static function make(Model|Product $product):static
    {
        return new static($product);
    }


    public function update(array $data):Product|Model
    {
        try {
            $this->data = $data;
            $this->recreateVariants = $this->product->filter_group_id != $this->data['filter_group_id'];

            $type = $this->data['type'] instanceof ProductTypeCast  ? $this->data['type'] : ProductTypeCast::from($this->data['type']);
            return match ($type)
            {
                ProductTypeCast::SIMPLE => $this->updateProduct(ProductTypeCast::SIMPLE),
                ProductTypeCast::WHOLESALE => $this->updateProduct(ProductTypeCast::WHOLESALE),
                ProductTypeCast::CONFIGURABLE => $this->updateConfigurableProduct(),
            };
        }catch (\Throwable $t)
        {
            dd($t->getMessage(),$t->getLine(),$t->getFile(),$t->getTraceAsString());
        }
    }

    private function updateProduct(ProductTypeCast $SIMPLE):Product
    {
        $fillables = $this->data;
        unset($fillables['filter_options']);
        // Update Configurable Product
        $this->product->update($fillables);
        // Update or Sync Filter Options
        $this->updateProductFilterOption();
        return $this->product;
    }

    private function updateConfigurableProduct():Product
    {
        $fillables = $this->data;
        unset($fillables['filter_options']);
        // Update Configurable Product
       // $this->product->update($fillables);
        if ($this->recreateVariants)
        {
            // Remove Old Variants And Create New Variants
            $this->product->variants()->each(fn($product) => $product->delete());
            // Regenerate New Variants
            $this->generateProductVariants($this->product);
            $this->updateProductFilterOption();

        }else{

            $this->smartUpdateVariants();
            // Update or Sync Filter Options
            $this->updateProductFilterOption();
        }

    }


//    protected function updateProductFilterOption(): void
//    {
//        if ($this->product->type == ProductTypeCast::CONFIGURABLE)
//        {
//            $this->updateProductFilterOptionToParent();
//        }else{
//            $filterOptionIds = array_values($this->data['filter_options']);
//            $this->product->filterOptions()->sync($filterOptionIds,['product_id' => $this->product->id]);
//        }
//
//    }


    protected function updateProductFilterOption(): void
    {
        if ($this->product->type == ProductTypeCast::CONFIGURABLE) {
            $this->updateProductFilterOptionToParent();
        } else {
            // Build pivot array with filter_id
            $pivotData = [];
            foreach ($this->data['filter_options'] as $filterId => $filterOptionId) {
                $pivotData[$filterOptionId] = ['filter_id' => $filterId];
            }

            // Sync with extra pivot data
            $this->product->filterOptions()->sync($pivotData);
        }
    }



    private function updateProductFilterOptionToParent(): void
    {
        $ids = [];
        foreach ($this->data['filter_options'] as $filterId => $optionIds) {
            foreach ($optionIds as $optionId) {
                $ids[] = $optionId;
            }
        }
        $filterOptionIds = array_values($ids);
        $this->product->filterOptions()->sync($filterOptionIds);

    }



    protected function smartUpdateVariants(): void
    {

        // 1. Gather new filter option IDs from the form data
        $newOptionIds = collect($this->data['filter_options'])
            ->flatten()
            ->map(fn($v) => (int) $v) // Normalize
            ->sort()
            ->values()
            ->all();

        // 2. Gather all unique filter option IDs used by existing variants
        $existingOptionIds = $this->product->variants()
            ->with('filterOptions')
            ->get()
            ->flatMap(fn($variant) => $variant->filterOptions->pluck('id'))
            ->unique()
            ->sort()
            ->values()
            ->all();

//        dd($newOptionIds === $existingOptionIds);

        // 3. Compare both sets
        if ($newOptionIds === $existingOptionIds) {
            // No change — skip processing
            return;
        }

        // 4. There’s a difference — proceed with diffing variant combinations
        $newVariants = $this->generateVariants($this->data['sku'] ?? $this->product->sku, $this->data['filter_options']);

        $existingVariants = $this->product->variants()->with('filterOptions')->get();

        $existingSignatures = $existingVariants->mapWithKeys(function ($variant) {
            $optionIds = $variant->filterOptions->pluck('id')->sort()->values()->all();
            $signature = implode('-', $optionIds);
            return [$signature => $variant];
        });

        $newSignatures = collect($newVariants)->mapWithKeys(function ($variant) {
            $signature = implode('-', collect($variant['filter_option_ids'])->sort()->values()->all());
            return [$signature => $variant];
        });


        dd($newOptionIds,$existingOptionIds,$this->data['filter_options'],$existingSignatures,$newSignatures);


        // 5. Delete outdated variants
        $toDelete = $existingSignatures->keys()->diff($newSignatures->keys());
        foreach ($toDelete as $signature) {
            $existingSignatures[$signature]->delete();
        }

        // 6. Create only new variants
        $toCreate = $newSignatures->keys()->diff($existingSignatures->keys());
        $variantsToCreate = $toCreate->map(fn($signature) => $newSignatures[$signature])->values()->all();

        if (!empty($variantsToCreate)) {
            $this->createVariants($this->product, $this->data, $variantsToCreate);
        }
    }




}
