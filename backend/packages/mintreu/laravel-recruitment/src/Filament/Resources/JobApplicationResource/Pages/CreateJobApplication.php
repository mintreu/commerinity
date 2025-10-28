<?php

namespace Mintreu\LaravelRecruitment\Filament\Resources\JobApplicationResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Mintreu\LaravelRecruitment\Filament\Resources\JobApplicationResource;

class CreateJobApplication extends CreateRecord
{
    protected static string $resource = JobApplicationResource::class;
}
