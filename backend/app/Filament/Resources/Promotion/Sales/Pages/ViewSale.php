<?php

namespace App\Filament\Resources\Promotion\Sales\Pages;

use Filament\Actions\EditAction;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use App\Filament\Resources\Promotion\Sales\SaleResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Collection;
use Mintreu\LaravelCommerinity\Support\SaleManager;
use Mintreu\LaravelMoney\Filament\Infolists\Components\MoneyEntry;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    protected SaleManager $saleManager;

    public ?Collection $conditions = null;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

//    public function mount(int|string $record): void
//    {
//        $this->record = $this->resolveRecord($record);
//
//        $sale = $this->record->toArray();
//
//
//        $this->saleManager = SaleManager::make();
//        $this->conditions = $this->saleManager->getCondition();
//        $this->form->fill(array_merge($sale));
//        // $this->fillForm();
//    }


    public function infolist(Schema $schema): Schema
    {
        return parent::infolist($infolist)
            ->components([

                Section::make('General Information')
                    ->aside()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name'),
                        IconEntry::make('status')->boolean(),
                        TextEntry::make('discount_amount')
                            ->label('Discount Percentage')
                            ->formatStateUsing(fn($state) => $state/100)
                            ->suffix('%'),
                        MoneyEntry::make('discount_amount')
                    ]),

                Section::make('Description')
                    ->aside()
                    ->schema([
                        TextEntry::make('description')->alignJustify()->hiddenLabel()->columnSpanFull(),
                    ]),


            ]);
    }


}
