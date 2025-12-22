<?php

namespace Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages;


use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Casts\TransactionTypeCast;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource;

class ManageTransactions extends ManageRelatedRecords
{

    protected static string $resource = WalletResource::class;

    protected static string $relationship = 'transactions';

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';
    protected static ?string $activeNavigationIcon = 'heroicon-m-square-3-stack-3d';

    public static function getNavigationLabel(): string
    {
        return 'Transactions';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([


                Forms\Components\TextInput::make('amount')
                    ->columnSpanFull()
                    ->required(),

                Forms\Components\ToggleButtons::make('type')
                    ->required()
                    ->inlineLabel()
                    ->inline()
                    ->options(array_combine(
                        array_map(fn($type) => $type->value, TransactionTypeCast::cases()),
                        array_map(fn($type) => $type->getLabel(), TransactionTypeCast::cases())
                    )),

                Forms\Components\Textarea::make('description')->maxLength(1000)->columnSpanFull(),

            ]);
    }


    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            TransactionTypeCast::CREDITED->value => Tab::make()
                ->icon('heroicon-m-document-text')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', TransactionTypeCast::CREDITED)),
            TransactionTypeCast::DEBITED->value => Tab::make()
                ->icon('heroicon-m-eye')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', TransactionTypeCast::DEBITED)),
        ];
    }






    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('uuid')->label(__('Receipt')),
                Tables\Columns\TextColumn::make('amount')->money(LaravelMoney::defaultCurrency(),100),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\IconColumn::make('verified')->boolean()->default(false),
                Tables\Columns\TextColumn::make('integration.name')->badge(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime('md/m/y H:i'),
            ])
            ->filters([
                //
            ])
            ->headerActions(array_merge([
//                Tables\Actions\CreateAction::make(),
//                Tables\Actions\AssociateAction::make()
            ],[]))
            ->actions([
                Tables\Actions\ViewAction::make()->infolist($this->getTransactionInfolistSchema()),
//                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
            ]);
    }










    public function getTransactionInfolistSchema():array
    {
        return [

            Section::make('Transaction Details')
                ->aside()
                ->schema([

                    Grid::make([
                        'md' => 2,
                        'lg' => 3
                    ])->schema([
                        TextEntry::make('receipt')
                            ->size(TextEntry\TextEntrySize::Large)
                            ->weight(FontWeight::SemiBold)->color('primary'),
                        TextEntry::make('type')->badge(),
                        TextEntry::make('status')->badge(),
                    ]),


                    Grid::make([
                        'md' => 3,
                    ])->schema([
                        TextEntry::make('amount')->prefix(LaravelMoney::defaultCurrency()),
                        TextEntry::make('description')->label('Remarks')->alignJustify()->columnSpan(2),
                    ]),


                ]),

            Section::make('payment_details')
                ->heading('Payment Details')
                ->aside()
                ->relationship('payment')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextEntry::make('provider_gen_id')
                        ->default('--not found--')
                        ->label('Generate ID'),

                    TextEntry::make('provider_transaction_id')
                        ->default('--not found--')
                        ->label('Transaction ID'),

                    TextEntry::make('provider.name')
                        ->label('Provider')
                        ->default('--not found--')
                        ->badge(),


                    IconEntry::make('verified')->inlineLabel()->boolean()->default(false),

                ]),


        ];
    }










}
