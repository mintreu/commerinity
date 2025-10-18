<?php

namespace Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages;

use App\Models\Admin;
use App\Models\Distributor;
use App\Models\User;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource;
use Mintreu\LaravelTransaction\Services\WalletService\WalletService;

class CreateWallet extends CreateRecord
{
    protected static string $resource = WalletResource::class;

    public function form(Form $form): Form
    {
        return parent::form($form)->schema([
            Radio::make('walletable_type')
                ->label('For:')
                ->options([
                    User::class => 'User (Member)',
                    Distributor::class => 'Distributor',
                    Admin::class => 'Admin',
                ])
                ->default(User::class)
                ->live(),

            Select::make('walletable_id')
                ->label('Select Wallet Owner')
                ->options(function (Get $get) {
                    $selectedType = $get('walletable_type');

                    if (!in_array($selectedType, [User::class, Distributor::class, Admin::class], true)) {
                        return [];
                    }

                    // Efficient retrieval with minimal memory footprint
                    return $selectedType::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->searchable()
                ->reactive()
                ->required(),
        ]);
    }

    public function create(bool $another = false): void
    {
        $data = $this->form->getState();
        $selectedType = $data['walletable_type'] ?? null;
        $ownerId = $data['walletable_id'] ?? null;

        if (! $selectedType || ! $ownerId) {
            $this->notifyError('Invalid data provided.');
            return;
        }

        $owner = $selectedType::with('wallet')->find($ownerId);

        if (! $owner) {
            $this->notifyError('Owner not found.');
            return;
        }

        if ($owner->wallet) {
            $this->notifyError('Wallet already exists!');
            return;
        }

        // Wrap in a transaction for atomicity
        DB::transaction(function () use ($owner) {
            $this->record = WalletService::create(owner: $owner);
        });

        $this->notifySuccess('Wallet created successfully.');

        $this->redirect(
            self::$resource::getUrl('view', ['record' => $this->record])
        );
    }

    /**
     * Helper: Display a success notification.
     */
    protected function notifySuccess(string $message): void
    {
        Notification::make()
            ->success()
            ->title($message)
            ->send();
    }

    /**
     * Helper: Display an error notification.
     */
    protected function notifyError(string $message): void
    {
        Notification::make()
            ->danger()
            ->title($message)
            ->send();
    }
}
