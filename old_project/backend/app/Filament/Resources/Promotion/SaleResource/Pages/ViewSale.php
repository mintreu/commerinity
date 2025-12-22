<?php

namespace App\Filament\Resources\Promotion\SaleResource\Pages;

use App\Filament\Resources\Promotion\SaleResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
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
            Actions\EditAction::make(),
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


    public function infolist(Infolist $infolist): Infolist
    {
        return parent::infolist($infolist)
            ->schema([

                Infolists\Components\Section::make('General Information')
                    ->aside()
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\IconEntry::make('status')->boolean(),
                        Infolists\Components\TextEntry::make('discount_amount')
                            ->label('Discount Percentage')
                            ->formatStateUsing(fn($state) => $state/100)
                            ->suffix('%'),
                        MoneyEntry::make('discount_amount')
                    ]),

                Infolists\Components\Section::make('Description')
                    ->aside()
                    ->schema([
                        Infolists\Components\TextEntry::make('description')->alignJustify()->hiddenLabel()->columnSpanFull(),
                    ]),


            ]);
    }


}
