<?php

namespace Mintreu\LaravelIntegration\Filament\Resources\IntegrationResource\Pages;

use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelIntegration\Filament\Resources\IntegrationResource;

class ViewIntegration extends ViewRecord
{
    protected static string $resource = IntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('configuration')
                ->label('Configuration')
                ->modalHeading('Integration Configuration')
                ->modalDescription('Set or update the credentials and webhook URL for this integration.')
                ->fillForm(fn () => $this->record->toArray())
                ->schema([
                    TextInput::make('key')
                        ->label('API Key')
                        ->maxLength(255)
                        ->placeholder('Enter API key...')
                        ->required()
                        ->hint('Provided by the integration provider.'),

                    TextInput::make('secret')
                        ->label('API Secret')
                        ->password()
                        ->revealable()
                        ->maxLength(255)
                        ->placeholder('Enter API secret...')
                        ->required()
                        ->hint('Keep this secret safe and never share it.'),

                    TextInput::make('webhook')
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
