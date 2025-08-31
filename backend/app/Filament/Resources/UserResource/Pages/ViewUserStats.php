<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Casts\AuthStatusCast;
use App\Casts\AuthTypeCast;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;

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
