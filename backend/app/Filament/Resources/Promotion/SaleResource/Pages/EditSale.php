<?php

namespace App\Filament\Resources\Promotion\SaleResource\Pages;

use App\Filament\Resources\Promotion\SaleResource;
use App\Filament\Resources\Promotion\SaleResource\Schema\HasSaleConditionFormSchema;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Collection;
use Mintreu\LaravelCommerinity\Support\SaleManager;

class EditSale extends EditRecord
{
    use HasSaleConditionFormSchema;
    protected static string $resource = SaleResource::class;

    protected ?SaleManager $saleManager = null;

    protected ?Collection $conditions = null;

    public function mount(int|string $record): void
    {

        $this->saleManager = SaleManager::make();
        $this->conditions = $this->saleManager->getCondition();
        parent::mount($record);

    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }






    protected function afterSave()
    {
        $this->saleManager = $this->saleManager ?? SaleManager::make();
        $this->saleManager->reindexSaleableProducts();
    }

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->contained(false)
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Information')
                            ->schema(self::$resource::getCommonFormSchema()),
                        Forms\Components\Tabs\Tab::make('Conditions')
                            ->schema($this->getSaleConditionFormSchema()),
                    ])
            ]);
    }








}
