<?php

namespace App\Filament\Resources\Ecommerce\Products\Pages;

use App\Filament\Resources\Ecommerce\Products\ProductResource;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ManageStock extends ManageRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $relationship = 'stocks';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(1)->schema([

                // =========================
                // Stock Basics
                // =========================
                Section::make('Stock Information')
                    ->description('Initial stock, sold count & priority')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            TextInput::make('init_quantity')
                                ->label('Initial Quantity')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->placeholder('0'),

                            TextInput::make('sold_quantity')
                                ->label('Sold Quantity')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->readOnly(),
                        ]),

                        TextInput::make('priority')
                            ->label('Priority')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Higher value = higher stock selection priority'),
                    ])
                    ->compact()
                    ->collapsible(),

                // =========================
                // Low Stock Settings
                // =========================
                Section::make('Low Stock Alert')
                    ->description('Threshold & notification')
                    ->icon('heroicon-o-bell-alert')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            TextInput::make('low_stock_threshold')
                                ->label('Low Stock Threshold')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(5),

                            Toggle::make('notify_on_low_stock')
                                ->label('Notify on Low Stock')
                                ->default(true),
                        ]),
                    ])
                    ->compact()
                    ->collapsible(),

                // =========================
                // Purchase Details
                // =========================
                Section::make('Purchase Details')
                    ->description('Supplier, invoice & dates')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select supplier')
                            ->getOptionLabelFromRecordUsing(
                                fn ($record) => $record->name ?: ('Supplier #' . $record->getKey())
                            ),

                        TextInput::make('purchase_invoice_number')
                            ->label('Invoice Number')
                            ->maxLength(100)
                            ->placeholder('INV-XXXX'),

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            DatePicker::make('purchase_date')
                                ->label('Purchase Date'),

                            DatePicker::make('expiry_date')
                                ->label('Expiry Date'),
                        ]),

                        TextInput::make('batch_number')
                            ->label('Batch Number')
                            ->maxLength(80)
                            ->placeholder('BATCH-001'),
                    ])
                    ->compact()
                    ->collapsible(),

                // =========================
                // Cost & Location
                // =========================
                Section::make('Cost & Location')
                    ->description('Landing cost & storage address')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        TextInput::make('landing_cost')
                            ->label('Landing Cost (paise)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Stored in paise'),

                        Select::make('address_id')
                            ->label('Storage Location')
                            ->relationship('address', 'title')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select location')
                            ->getOptionLabelFromRecordUsing(
                                fn ($record) => $record->title ?: ('Address #' . $record->getKey())
                            ),
                    ])
                    ->compact()
                    ->collapsible(),

                // =========================
                // Notes
                // =========================
                Section::make('Notes')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('notes')
                            ->rows(3)
                            ->placeholder('Optional internal notes…'),
                    ])
                    ->compact()
                    ->collapsible(),

            ])->columnSpanFull(),
        ]);
    }



    public function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Stock Overview')
                ->icon('heroicon-o-presentation-chart-line')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 4,
                    ])->schema([
                        TextEntry::make('init_quantity')
                            ->label('Initial')
                            ->badge()
                            ->color(fn (int $state): string => match (true) {
                                $state > 100 => 'success',
                                $state > 10 => 'warning',
                                default => 'danger',
                            }),

                        TextEntry::make('sold_quantity')
                            ->label('Sold')
                            ->badge()
                            ->color('danger'),

                        TextEntry::make('priority')
                            ->label('Priority')
                            ->badge()
                            ->color(fn (int $state): string => match (true) {
                                $state > 0 => 'warning',
                                default => 'gray',
                            }),

                        TextEntry::make('low_stock_threshold')
                            ->label('Low Stock Level')
                            ->badge()
                            ->color(fn (int $state): string => match (true) {
                                $state <= 5 => 'danger',
                                $state <= 15 => 'warning',
                                default => 'gray',
                            }),
                    ]),

                    IconEntry::make('notify_on_low_stock')
                        ->label('Low Stock Notify')
                        ->boolean()
                        ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                ])
                ->compact(),

            Section::make('Purchase & Cost')
                ->icon('heroicon-o-receipt-percent')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                    ])->schema([
                        TextEntry::make('landing_cost')
                            ->label('Landing Cost (paise)')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('purchase_invoice_number')
                            ->label('Invoice #')
                            ->badge()
                            ->placeholder('-'),
                    ]),

                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                    ])->schema([
                        TextEntry::make('purchase_date')
                            ->label('Purchase Date')
                            ->date()
                            ->placeholder('-'),

                        TextEntry::make('expiry_date')
                            ->label('Expiry Date')
                            ->date()
                            ->placeholder('-'),
                    ]),

                    TextEntry::make('batch_number')
                        ->label('Batch Number')
                        ->placeholder('-'),
                ])
                ->collapsible()
                ->compact(),

            Section::make('Links & Notes')
                ->icon('heroicon-o-information-circle')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                    ])->schema([
                        TextEntry::make('supplier.name')
                            ->label('Supplier')
                            ->placeholder('-'),

                        TextEntry::make('address.title')
                            ->label('Location')
                            ->placeholder('-'),
                    ]),

                    TextEntry::make('notes')
                        ->label('Notes')
                        ->placeholder('-')
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->compact(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('batch_number')
            ->columns([
                TextColumn::make('batch_number')
                    ->label('Batch')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('-'),

                IconColumn::make('in_stock')
                    ->label('Status')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('in_stock_quantity')
                    ->label('Available')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        (int) $state > 50 => 'success',
                        (int) $state > 5 => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('sold_quantity')
                    ->label('Sold')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('danger'),

                TextColumn::make('priority')
                    ->label('Priority')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                // NOTE: If you don't have "price" in stock table, remove this column.
                TextColumn::make('price')
                    ->label('Price')
                    ->money()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('landing_cost')
                    ->label('Cost')
                    ->money()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),

                TextColumn::make('address.title')
                    ->label('Location')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),

                TextColumn::make('purchase_date')
                    ->label('Purchased')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('expiry_date')
                    ->label('Expiry')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->since() // nice human readable
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Stock')
                    ->icon(Heroicon::OutlinedPlus),
            ])
            ->recordActions([
                ViewAction::make()->icon(Heroicon::OutlinedEye),
                EditAction::make()->icon(Heroicon::OutlinedPencilSquare),
                DeleteAction::make()->icon(Heroicon::OutlinedTrash),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
