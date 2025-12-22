<?php

namespace Mintreu\LaravelRecruitment\Filament\Resources\JobApplicationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Mintreu\LaravelRecruitment\Filament\Resources\JobApplicationResource;

class ListJobApplications extends ListRecords
{
    protected static string $resource = JobApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
