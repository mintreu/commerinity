<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Services\UserServices\NetworkServices\NetworkService;
use Filament\Infolists\Components\View;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ManageCommunity extends ViewRecord
{
    protected static string $resource = UserResource::class;


    public function infolist(Infolist $infolist): Infolist
    {
        return parent::infolist($infolist)
            ->schema([
                View::make('community-tree')
                    ->viewData(['downline' => NetworkService::make($this->record)->getTree()->getJson()])
                    ->columnSpanFull()
            ]);
    }

}
