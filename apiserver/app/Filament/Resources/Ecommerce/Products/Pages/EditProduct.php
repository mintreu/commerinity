<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ecommerce\Products\Pages;

use App\Casts\ProductStatusCast;
use App\Casts\ProductTypeCast;
use App\Filament\Resources\Ecommerce\Products\ProductResource;
use App\Filament\Resources\Ecommerce\Products\RelationManagers\VariantsRelationManager;
use App\Filament\Resources\Ecommerce\Products\Support\ProductFilterOptions;
use App\Services\Ecommerce\ProductManager;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    public function getRelationManagers(): array
    {
        $relationManagers = [];
        if ($this->record->type == ProductTypeCast::CONFIGURABLE->value) {
            $relationManagers[] = VariantsRelationManager::class;
        }

        return $relationManagers;
    }


    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->action(function (Model $record): void {
                    ProductManager::delete($record);
                }),
        ];
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Build filter_options from the form data
        $filterOptions = ProductFilterOptions::normalize($data['filter_options'] ?? []);

        // Prepare data for ProductManager (keep all form fields)
        $productData = $data;
        $productData['status'] = $data['status'] ?? ProductStatusCast::DRAFT->value;
        $productData['type'] = $data['type'] ?? ProductTypeCast::SIMPLE->value;
        $productData['filter_options'] = $filterOptions;

        // Use ProductManager to update product (handles variants automatically)
        $product = ProductManager::update($record, $productData);

        if (! $product) {
            Notification::make()
                ->title('Failed to update product')
                ->danger()
                ->send();

            throw new \Exception('ProductManager::update() returned null');
        }

        Notification::make()
            ->title('Product updated')
            ->success()
            ->send();

        return $product;
    }

}
