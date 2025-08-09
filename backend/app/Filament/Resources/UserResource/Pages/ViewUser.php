<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Casts\AuthStatusCast;
use App\Casts\AuthTypeCast;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('updateStatus')
                ->label('Update Status')
                ->icon('heroicon-o-adjustments-vertical')
                ->color('warning') // Can be primary, success, danger, etc.
                ->modalHeading('Update Status & Feedback')
                ->fillForm([
                    'status' => $this->record->status,
                    'status_feedback' => $this->record->status_feedback,
                ])
                ->form([
                    Radio::make('status')
                        ->label('Status')
                        ->options(fn () => collect(AuthStatusCast::cases())->mapWithKeys(
                            fn ($case) => [$case->value => $case->name]
                        ))
                        ->required(),

                    Textarea::make('status_feedback')
                        ->label('Feedback')
                        ->placeholder('Optional feedback or reason for this status...')
                        ->nullable(),
                ])
                ->action(function (array $data): void {
                    $this->record->update($data);

                    Notification::make()
                        ->title('Status updated successfully')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalSubmitActionLabel('Save')
                ->modalWidth('md'),




            Actions\Action::make('updateType')
                ->label('Update Type')
                ->icon('heroicon-o-adjustments-vertical')
                ->color('info') // Can be primary, success, danger, etc.
                ->modalHeading('Update Auth Type')
                ->fillForm([
                    'type' => $this->record->type,
                ])
                ->form([
                    Radio::make('type')
                        ->label('Type')
                        ->options(fn () => collect(AuthTypeCast::cases())->mapWithKeys(
                            fn ($case) => [$case->value => $case->name]
                        ))
                        ->inline()
                        ->required(),

                ])
                ->action(function (array $data): void {
                    $this->record->update($data);

                    Notification::make()
                        ->title('Status updated successfully')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalSubmitActionLabel('Save')
                ->modalWidth('md')




        ];
    }
}
