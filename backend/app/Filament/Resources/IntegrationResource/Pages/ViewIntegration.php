<?php

namespace App\Filament\Resources\IntegrationResource\Pages;

use App\Filament\Resources\IntegrationResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Forms\Form;
class ViewIntegration extends ViewRecord
{
    protected static string $resource = IntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('configuration')
                ->label('Configuration')
                ->modalHeading('Integration Configuration')
                ->modalDescription('Set or update the credentials and webhook URL for this integration.')
                ->fillForm(fn () => $this->record->toArray())
                ->form([
                    Forms\Components\TextInput::make('key')
                        ->label('API Key')
                        ->maxLength(255)
                        ->placeholder('Enter API key...')
                        ->required()
                        ->hint('Provided by the integration provider.'),

                    Forms\Components\TextInput::make('secret')
                        ->label('API Secret')
                        ->password()
                        ->revealable()
                        ->maxLength(255)
                        ->placeholder('Enter API secret...')
                        ->required()
                        ->hint('Keep this secret safe and never share it.'),

                    Forms\Components\TextInput::make('webhook')
                        ->label('Webhook URL')
                        ->url()
                        ->maxLength(255)
                        ->placeholder('https://yourapp.com/webhook-endpoint')
                        ->helperText('This URL will receive events from the integration.'),
                ])
                ->action(function (array $data) {
                    $this->record->update($data);

                    Notification::make()
                        ->success()
                        ->title('Configuration Saved!')
                        ->body('Your integration credentials and webhook have been updated.')
                        ->send();
                })



        ];
    }
}
