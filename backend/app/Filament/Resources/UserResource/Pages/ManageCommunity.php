<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Services\UserServices\NetworkServices\NetworkService;
use App\Services\UserServices\NetworkServices\Support\MemberTreeList;
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
                    ->viewData(['downline' => MemberTreeList::make($this->record)
                        ->withDepth(5)        // Maximum 5 levels deep
                        ->withLimit(5)->getJson()])
                    ->columnSpanFull()
            ]);
    }

}
