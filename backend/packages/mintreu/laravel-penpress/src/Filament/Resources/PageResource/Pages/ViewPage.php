<?php

namespace Mintreu\LaravelPenpress\Filament\Resources\PageResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelPenpress\Filament\Resources\PageResource;

class ViewPage extends ViewRecord
{
    protected static string $resource = PageResource::class;
    protected ?string $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
