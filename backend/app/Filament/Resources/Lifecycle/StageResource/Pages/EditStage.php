<?php

namespace App\Filament\Resources\Lifecycle\StageResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Lifecycle\StageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStage extends EditRecord
{
    protected static string $resource = StageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
