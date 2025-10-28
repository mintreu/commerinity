<?php

namespace Mintreu\LaravelRecruitment\Filament\Resources\RecruitmentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Mintreu\LaravelRecruitment\Filament\Resources\RecruitmentResource;

class ListRecruitments extends ListRecords
{
    protected static string $resource = RecruitmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
