<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;

class ViewUserStats extends ViewRecord
{
    protected static string $resource = UserResource::class;
    protected static ?string $navigationLabel = 'View Stats';
    protected static ?string $title = 'View Statistics';

    protected function getHeaderActions(): array
    {
        return [


            Action::make('team')
                ->color('info')
                ->url(fn() => self::$resource::getUrl('members',['record' => $this->record->referral_code]),false),



        ];
    }


    public function infolist(Schema $schema):Schema
    {
        return parent::infolist($infolist)
            ->components([

                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Team Rewards')
                            ->schema([
                                RepeatableEntry::make('children')
                                    ->schema([

                                        TextEntry::make('name'),
                                    ]),
                            ]),



//                        Tabs\Tab::make('Shopping Rewards')
//                            ->schema([]),
//                        Tabs\Tab::make('Team Rewards')
//                            ->schema([]),
//                        Tabs\Tab::make('Business Rewards')
//                            ->schema([]),
//
//                        Tabs\Tab::make('System Rewards')
//                            ->schema([]),


                    ]),


            ]);
    }


}
