<?php

namespace App\Filament\Resources\Order\OrderResource\Pages;

use App\Filament\Resources\Order\OrderResource;
use App\Services\OrderService\OrderCreationService;
use App\Services\OrderService\OrderService;
use Filament\Actions;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
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
                        ]),
                    Toggle::make('forced')
                        ->label('Reset')
                        ->default(false)
                        ->helperText('If Enable transaction record reset if expired already')
                ])->action(function (array $data){
                    $result =  OrderService::make()->payIt(order: $this->record,provider: $data['provider'],hasResource: true,resource: self::$resource,forced: $data['forced']);
                    if ($result['success'])
                    {
                        $redirectUrl = $result['redirect'];
                        $this->redirect($redirectUrl);
                    }else{
                        Notification::make()->title($result['errors'])->warning()->send();
                    }


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
