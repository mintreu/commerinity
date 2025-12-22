<?php

namespace Mintreu\LaravelRecruitment\Filament\Resources\RecruitmentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelRecruitment\Filament\Resources\RecruitmentResource;

class ViewRecruitment extends ViewRecord
{
    protected static string $resource = RecruitmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
