<?php

namespace Mintreu\LaravelPenpress\Filament\Resources\PageResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Mintreu\LaravelPenpress\Filament\Resources\PageResource;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;
    protected ?string $maxContentWidth = 'full';
}
