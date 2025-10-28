<?php

namespace Mintreu\LaravelRecruitment\Filament\Resources\JobApplicationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelRecruitment\Filament\Resources\JobApplicationResource;

class ViewJobApplication extends ViewRecord
{
    protected static string $resource = JobApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
