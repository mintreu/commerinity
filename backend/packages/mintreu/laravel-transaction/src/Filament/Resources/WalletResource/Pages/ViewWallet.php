<?php

namespace Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mintreu\LaravelIntegration\LaravelIntegration;
use Mintreu\LaravelMoney\Filament\Forms\Components\MoneyInput;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource;
use Mintreu\LaravelTransaction\Models\Transaction;
use Mintreu\LaravelTransaction\Models\Wallet;
use Mintreu\LaravelTransaction\Services\WalletService\WalletService;

class ViewWallet extends ViewRecord
{
    protected static string $resource = WalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\ActionGroup::make([
                Actions\Action::make('change_pin')
                    ->label('Change PIN')
                    ->icon('heroicon-o-key')
                    ->color('warning'),

                Actions\Action::make('add_money')
                    ->label('Add Money')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        TextInput::make('amount')
                            //->minValue(99)
                            ->type('decimal')
                            ->helperText('Add Fund'),

                    ])
                    ->action(function (array $data){
                        $checkoutUrl = WalletService::make($this->record)
                            ->addFund((int) round(((float) $data['amount']) * 100))
                            ->getCheckoutUrl(
                                redirect_success_url: self::$resource::getUrl('view',['record' => $this->record->getRouteKey()]),
                                redirect_failure_url: self::$resource::getUrl('view',['record' => $this->record->getRouteKey()]),
                            );

                        if($checkoutUrl)
                        {
                            $this->redirect($checkoutUrl);
                        }else{
                            Notification::make()->title('Something Happening!')->warning()->send();
                        }

                    }),

                Actions\Action::make('withdraw_money')
                    ->label('Withdraw Money')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('danger'),

                Actions\Action::make('send_money')
                    ->label('Send Money')
                    ->icon('heroicon-o-paper-airplane')
                    ->form([
                        \Filament\Forms\Components\Grid::make(2)
                            ->schema([

                                TextInput::make('receiver_wallet_uuid')
                                    ->label(__('Receiver Wallet ID'))
                                    ->placeholder('Enter the receiver’s wallet UUID')
                                    ->helperText('This must be a valid existing wallet ID different from your own.')
                                    ->hint('Format: WAL-XXXXXXXX')
                                    ->prefix('WAL-')
                                    ->prefixIcon('heroicon-o-identification')
                                    ->notIn([$this->record->uuid])
                                    ->columnSpanFull()
                                    ->required(),

                                TextInput::make('amount')
                                    ->label(__('Amount'))
                                    ->placeholder('Enter the amount to transfer')
                                    ->helperText('Enter the amount you wish to send (in your wallet currency).')
                                    ->hint('Maximum transferable: ' . number_format($this->record->balance / 100, 2))
                                    ->suffix (LaravelMoney::defaultCurrency())
                                    ->type('number')
                                    ->step('0.01')
                                    ->maxValue($this->record->balance / 100)
                                    ->required(),

                                TextInput::make('pin')
                                    ->label(__('Transaction PIN'))
                                    ->placeholder('Enter your 6-digit wallet PIN')
                                    ->helperText('Used to authorize this transaction securely.')
                                    ->hint('Your personal 6-digit code.')
                                    ->prefixIcon('heroicon-o-lock-closed')
                                    ->password()
                                    ->revealable()
                                    ->maxLength(6)
                                    ->rule(function () {
                                        return function (string $attribute, $value, \Closure $fail) {
                                            // Compare hashed pin with record pin
                                            if (! Hash::check($value, $this->record->pin)) {
                                                $fail(__('The provided PIN is incorrect.'));
                                            }
                                        };
                                    })
                                    ->required(),

                                Textarea::make('purpose')
                                    ->label(__('Notes / Purpose'))
                                    ->placeholder('Optional: add notes or purpose of this transfer...')
                                    ->helperText('Helps track the reason for this transaction.')
                                    ->hint('Max 120 characters.')
                                    ->hintIcon('heroicon-o-pencil-square')
                                    ->maxLength(120)
                                    ->columnSpanFull()
                                    ->nullable(),
                            ])

                    ])
                    ->action(function (array $data){
                        $checkoutUrl = WalletService::make($this->record);
                        $valid = $checkoutUrl->sendFund(
                            receiver: 'WAL-'.$data['receiver_wallet_uuid'],
                            amount: (int) round(((float) $data['amount']) * 100),
                            redirect_success_url: self::$resource::getUrl('view',['record' => $this->record->getRouteKey()]),
                            redirect_failure_url: self::$resource::getUrl('view',['record' => $this->record->getRouteKey()]),
                            purpose: $data['purpose'],
                        );


                        if($valid)
                        {
                            Notification::make()->title('Money Send Successfully!')->success()->send();
                        }else{
                            Notification::make()->title('Something Happening!')->warning()->send();
                        }
                    })
                    ->color('info'),

                Actions\Action::make('manage_beneficiary')
                    ->label('Manage Bank Accounts')
                    ->icon('heroicon-o-banknotes')
                    ->color('primary'),
            ]),





        ];
    }





    public function mount(int|string $record): void
    {
        parent::mount($record); // TODO: Change the autogenerated stub
        $this->record->load('walletable');

    }


    public function infolist(Infolist $infolist): Infolist
    {
        return parent::infolist($infolist)
            ->schema([

                Section::make('General')
                    ->aside()
                    ->description('')
                    ->columns()
                    ->schema([
                        TextEntry::make('uuid')
                            ->label(__("Wallet ID"))
                            ->color('primary')
                            ->copyable()
                            ->copyableState(fn($state) => Str::replace('WAL-','',$state))
                            ->size(TextEntry\TextEntrySize::Large),

                        TextEntry::make('balance')
                            ->size(TextEntry\TextEntrySize::Large)
                            ->color('success')
                            ->money(LaravelMoney::defaultCurrency(),100),

                    ]),



                Section::make('owner')
                    ->aside()
                    ->description('')
                    ->relationship('walletable')
                    ->columns()
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('avatar')
                            ->circular()
                            ->hiddenLabel()
                            ->collection('avatarImage'),
                        TextEntry::make('name')->label(__('Name')),

                    ])


            ]);
    }


}
