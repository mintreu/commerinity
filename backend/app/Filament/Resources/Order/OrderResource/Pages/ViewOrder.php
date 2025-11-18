<?php

namespace App\Filament\Resources\Order\OrderResource\Pages;

use App\Filament\Resources\Order\OrderResource;
use App\Services\OrderService\OrderCreationService;
use Filament\Actions;
use Filament\Forms\Components\Radio;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('pay_now')
                ->form([
                    Radio::make('provider')
                        ->inlineLabel()
                        ->inline()
                        ->options([
                            'wallet' => 'Wallet',
                            'online' => 'Online'
                        ])
                ])->action(function (array $data){

                }),

            Actions\Action::make('ship_it')
                ->form([
                    Radio::make('provider')
                        ->inlineLabel()
                        ->inline()
                        ->options([
                            'native' => 'Native',
                            'online' => 'Online'
                        ])
                ])->action(function (array $data){

                })

        ];
    }
}
