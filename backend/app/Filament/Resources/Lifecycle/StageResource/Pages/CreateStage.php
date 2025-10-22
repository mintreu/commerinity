<?php

namespace App\Filament\Resources\Lifecycle\Stages\Pages;

use App\Filament\Resources\Lifecycle\Stages\StageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStage extends CreateRecord
{
    protected static string $resource = StageResource::class;
}
