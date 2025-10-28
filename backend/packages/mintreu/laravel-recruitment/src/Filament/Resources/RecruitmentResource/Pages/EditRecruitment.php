<?php

namespace Mintreu\LaravelRecruitment\Filament\Resources\RecruitmentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelRecruitment\Filament\Resources\RecruitmentResource;

class EditRecruitment extends EditRecord
{
    protected static string $resource = RecruitmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
