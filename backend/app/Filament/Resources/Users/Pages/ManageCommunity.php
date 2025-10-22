<?php

namespace App\Filament\Resources\Users\Pages;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\View;
use App\Filament\Resources\Users\UserResource;
use App\Services\UserServices\NetworkServices\NetworkService;
use Filament\Resources\Pages\ViewRecord;

class ManageCommunity extends ViewRecord
{
    protected static string $resource = UserResource::class;


    public function infolist(Schema $schema): Schema
    {
        return parent::infolist($infolist)
            ->components([
                View::make('community-tree')
                    ->viewData(['downline' => NetworkService::make($this->record)->getTree()->getJson()])
                    ->columnSpanFull()
            ]);
    }

}
