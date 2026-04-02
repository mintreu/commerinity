<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ecommerce\Products\Pages;

use App\Casts\ProductStatusCast;
use App\Casts\ProductTypeCast;
use App\Filament\Resources\Ecommerce\Products\ProductResource;
use App\Filament\Resources\Ecommerce\Products\Schemas\ProductCreateForm;
use App\Filament\Resources\Ecommerce\Products\Support\ProductFilterOptions;
use App\Services\Ecommerce\ProductManager;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;


    public function form(Schema $schema): Schema
    {
        return ProductCreateForm::configure($schema);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $productData = $data;
        $productData['status'] = $data['status'] ?? ProductStatusCast::DRAFT->value;
        $productData['type'] = $data['type'] ?? ProductTypeCast::SIMPLE->value;
        $productData['filter_options'] = ProductFilterOptions::normalize($data['filter_options'] ?? []);

        $record = ProductManager::create($productData);

        if (! $record) {
            Notification::make()
                ->title('Failed to create product')
                ->danger()
                ->send();

            throw new \RuntimeException('ProductManager::create() returned null');
        }

        if ($parentRecord = $this->getParentRecord()) {
            return $this->associateRecordWithParent($record, $parentRecord);
        }

        return $record;
    }
}
