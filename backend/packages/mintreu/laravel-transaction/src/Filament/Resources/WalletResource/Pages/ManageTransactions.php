<?php

namespace Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages;


use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\TextSize;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-square-3-stack-3d';
    protected static string | \BackedEnum | null $activeNavigationIcon = 'heroicon-m-square-3-stack-3d';

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

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([


                TextInput::make('amount')
                    ->columnSpanFull()
                    ->required(),

                ToggleButtons::make('type')
                    ->required()
                    ->inlineLabel()
                    ->inline()
                    ->options(array_combine(
                        array_map(fn($type) => $type->value, TransactionTypeCast::cases()),
                        array_map(fn($type) => $type->getLabel(), TransactionTypeCast::cases())
                    )),

                Textarea::make('description')->maxLength(1000)->columnSpanFull(),

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
                TextColumn::make('uuid')->label(__('Receipt')),
                TextColumn::make('amount')->money(LaravelMoney::defaultCurrency(),100),
                TextColumn::make('type')->badge(),
                TextColumn::make('status')->badge(),
                IconColumn::make('verified')->boolean()->default(false),
                TextColumn::make('integration.name')->badge(),
                TextColumn::make('updated_at')->dateTime('md/m/y H:i'),
            ])
            ->filters([
                //
            ])
            ->headerActions(array_merge([
//                Tables\Actions\CreateAction::make(),
//                Tables\Actions\AssociateAction::make()
            ],[]))
            ->recordActions([
                ViewAction::make()->schema($this->getTransactionInfolistSchema()),
//                Tables\Actions\EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
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
                            ->size(TextSize::Large)
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
