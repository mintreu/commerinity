<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewUserStats extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [


            Actions\Action::make('team')
                ->color('info')
                ->url(fn() => self::$resource::getUrl('members',['record' => $this->record->referral_code]),false),



        ];
    }


    public function infolist(Infolist $infolist):Infolist
    {
        return parent::infolist($infolist)
            ->schema([

                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Shopping Rewards')
                            ->schema([]),
                        Tabs\Tab::make('Team Rewards')
                            ->schema([]),
                        Tabs\Tab::make('Business Rewards')
                            ->schema([]),

                        Tabs\Tab::make('System Rewards')
                            ->schema([]),


                    ]),


            ]);
    }


}
