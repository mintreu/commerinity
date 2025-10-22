<?php

namespace Mintreu\LaravelPenpress\Filament\Resources\PageResource\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelPenpress\Filament\Resources\PageResource;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;
    protected Width|string|null $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
