<?php

namespace App\Filament\Resources\Ecommerce\Orders\Schemas;

use App\Casts\ProductStatusCast;
use App\Casts\ProductTypeCast;
use App\Casts\UserStatusCast;
use App\Models\Ecommerce\Product;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class OrderCreationForm
{


    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::fields());
    }

    public static function fields(): array
    {
        return [
            Flex::make([
                // LEFT MAIN
                Section::make([
                    Fieldset::make('Customer')
                        ->columns([
                            'default' => 1,
                            'md' => 2,
                        ])
                        ->schema([
                            Select::make('customer_type')
                                ->label('Customer Type')
                                ->placeholder('Select type')
                                ->live()
                                ->options([
                                    User::class => 'Registered User',
                                ])
                                ->columnSpan(1),

                            Select::make('customer_id')
                                ->label('Customer')
                                ->placeholder('Search & select customer')
                                ->live()
                                ->searchable()
                                ->options(function (Get $get) {
                                    $model = $get('customer_type');

                                    if ($model) {
                                        return $model::query()
                                            ->whereNotIn('status', UserStatusCast::notServiceable())
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    }

                                    return [];
                                })
                                ->afterStateUpdated(function ($state, Get $get,Set $set) {
                                    $model = $get('customer_type');

                                    if (!$model || !$state) {
                                        return;
                                    }

                                    $record = $model::with([
                                        'addresses' => fn($query) => $query->where('default', true),
                                        //'cart',
                                    ])->find($state);
                                    // Cached it
                                    $set('cached_customer', $record);
                                    // Set Address (Select Fields)
                                    $defaultAddressId = $record->addresses->first()?->id;
                                    $set('shipping_address_id', $defaultAddressId);
                                    $set('billing_address_id', $defaultAddressId);
                                })
                                ->columnSpan(1),
                        ]),

                    Fieldset::make('Cart Items')
                        ->schema([
                            Repeater::make('cart')
                                ->label('')
                                ->hiddenLabel()
                                ->columnSpanFull()
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->table([
                                    Repeater\TableColumn::make('Product'),
                                    Repeater\TableColumn::make('Qty'),
                                    Repeater\TableColumn::make('Breakdown'),
                                ])
                                ->addActionLabel('Add Item')
                                ->schema([
                                    Select::make('product_id')
                                        ->label('Product')
                                        ->placeholder('Choose product')
                                        ->searchable()
                                        ->options(
                                            Product::query()
                                                ->where('status', ProductStatusCast::PUBLISHED->value)
                                                ->where('type', ProductTypeCast::SIMPLE->value)
                                                ->orderBy('name')
                                                ->pluck('name', 'id')
                                                ->toArray()
                                        )
                                        ->required(),

                                    TextInput::make('quantity')
                                        ->label('Quantity')
                                        ->integer()
                                        ->minValue(1)
                                        ->default(1)
                                        ->required(),

                                    Text::make('breakdown')
                                        ->tooltip('Calculation'),
                                ]),
                        ]),

                    Fieldset::make('Shipping')
                        ->columns([
                            'default' => 1,
                            'md' => 2,
                        ])
                        ->columnSpanFull()
                        ->schema([
                            Select::make('billing_address_id')
                                ->label('Billing Address')
                                ->placeholder('Select billing address')
                                ->searchable()
                                ->options(function (Get $get) {
                                    $customer = $get('cached_customer');
                                    if ($customer) {
                                        return $customer->addresses->pluck('title', 'id')->toArray();
                                    }
                                    return [];
                                }),

                            Select::make('shipping_address_id')
                                ->label('Delivery Address')
                                ->placeholder('Select delivery address')
                                ->searchable()
                                ->options(function (Get $get) {
                                    $customer = $get('cached_customer');
                                    if ($customer) {
                                        return $customer->addresses->pluck('title', 'id')->toArray();
                                    }
                                    return [];
                                }),
                        ]),
                ]),

                // RIGHT SIDEBAR
                Section::make([
                    Fieldset::make('Coupon')
                        ->schema([
                            TextInput::make('voucher')
                                ->label('Voucher Code')
                                ->placeholder('Enter code (optional)')
                                ->columnSpanFull(),

                            Actions::make([
                                Action::make('validateVoucher')
                                    ->label('Validate')
                                    ->size('sm'),
                            ])
                                ->alignRight()
                                ->columnSpanFull(),
                        ]),

                    Fieldset::make('Flags')
                        ->schema([
                            Toggle::make('commission_processed')
                                ->label('Commission Processed')
                                ->default(false)
                                ->required(),
                        ]),

                    Fieldset::make('Notes')
                        ->schema([
                            Textarea::make('notes')
                                ->label('Order Notes')
                                ->placeholder('Internal notes for this order...')
                                ->rows(10)
                                ->columnSpanFull(),
                        ]),
                ])->grow(false),
            ])->from('md')->columnSpanFull(),

            Fieldset::make('Cost Breakdown')
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ])
                ->columnSpanFull()
                ->schema([
                    TextInput::make('subtotal')
                        ->label('Subtotal')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->prefix('$'),

                    TextInput::make('shipping_cost')
                        ->label('Shipping')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->prefix('$'),

                    TextInput::make('tax')
                        ->label('Tax')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->prefix('$'),

                    TextInput::make('discount')
                        ->label('Discount')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->prefix('$'),

                    TextInput::make('total_quantity')
                        ->label('Total Items')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    TextInput::make('total')
                        ->label('Grand Total')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->prefix('$'),
                ]),

            Fieldset::make('Reward Breakdown')
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ])
                ->columnSpanFull()
                ->schema([
                    TextInput::make('total_bv')->label('Total BV')->required()->numeric()->default(0),
                    TextInput::make('total_pv')->label('Total PV')->required()->numeric()->default(0),
                    TextInput::make('total_reward_points')->label('Reward Points')->required()->numeric()->default(0),
                ]),
        ];
    }
}
