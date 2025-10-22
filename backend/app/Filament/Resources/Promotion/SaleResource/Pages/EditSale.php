<?php

namespace App\Filament\Resources\Promotion\SaleResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Resources\Promotion\SaleResource;
use App\Filament\Resources\Promotion\SaleResource\Schema\HasSaleConditionFormSchema;
use Filament\Actions;
use Filament\Forms;
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
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }






    protected function afterSave()
    {
        $this->saleManager = $this->saleManager ?? SaleManager::make();
        $this->saleManager->reindexSaleableProducts();
        $this->redirect(self::$resource::getUrl('edit',['record' => $this->record->getRouteKey()]));
    }

    public function form(Schema $schema): Schema
    {
        return parent::form($schema)
            ->components([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->contained(false)
                    ->tabs([
                        Tab::make('Information')
                            ->schema(self::$resource::getCommonFormSchema()),
                        Tab::make('Conditions')
                            ->schema($this->getSaleConditionFormSchema()),
                    ])
            ]);
    }








}
