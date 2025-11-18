<?php

namespace App\Filament\Resources\Order\OrderResource\Schemas;


use App\Casts\OrderStatusCast;
use App\Filament\Resources\Order\OrderResource\Schemas\Traits\HasOrderFormSupport;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Get;
use Mintreu\LaravelMoney\LaravelMoney;


class OrderForm
{
    use HasOrderFormSupport;

    /**
     * Order Form Schema
     * For Upgrade Filament v4 just do this configure(Schema $schema): Schema
     * and return result like this return $schema->components([]);
     * @return array
     */
    public static function configure():array
    {
        $instance = new static();
        return array_merge(
            $instance->getDefaultHeaderForm(),
            $instance->getDynamicMiddleFormPartAsOperation(),
            $instance->getDefaultFooterForm()
        );
    }








    protected function getDefaultHeaderForm(): array
    {
        return [
            Forms\Components\Section::make('Order Information')
                ->schema([
                    Forms\Components\TextInput::make('uuid')
                        ->required()
                        ->maxLength(255)
                        ->disabled()
                        ->visible(fn (string $operation,Forms\Get $get): bool => $operation != 'create')
                        ->dehydrated(),
                    Forms\Components\Select::make('customerable_id')
                        //->relationship('customer', 'name')
                        ->required()
                        ->searchable()
                        ->columnSpan(fn (string $operation): bool => $operation === 'create' ? 2 : 1)
                        ->options(fn() => User::pluck('name', 'id'))
                        ->searchable(['name', 'email', 'mobile'])
                        ->lazy()
                        ->afterStateUpdated(function ($state,Forms\Set $set,string $operation){
                            // Efficient eager loading
                            if ($state && $operation === 'create') {
                                self::updateCustomerCart($state,$set);
                            }
                            return;
                        })
                        ->preload(),
                    Forms\Components\Select::make('status')
                        ->options(collect(OrderStatusCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                        ->required()
                        ->columnSpan(1)
                        ->default('pending'),
                ])
                ->columns(3),
        ];
    }


    protected function getDefaultFooterForm(): array
    {
        return [
            Forms\Components\Section::make('Pricing')
                ->schema([
                    Forms\Components\TextInput::make('subtotal')
                        ->required()
                        ->numeric()
                        ->suffix('paise')
                        ->helperText('Amount in paise (1 rupee = 100 paise)'),


                    Forms\Components\TextInput::make('shipping_cost')
                        ->required()
                        ->numeric()
                        ->suffix('paise')
                        ->default(0),
                    Forms\Components\TextInput::make('tax')
                        ->required()
                        ->numeric()
                        ->suffix('paise')
                        ->default(0),
                    Forms\Components\TextInput::make('discount')
                        ->required()
                        ->numeric()
                        ->suffix('paise')
                        ->default(0),
                    Forms\Components\TextInput::make('total')
                        ->required()
                        ->numeric()
                        ->suffix('paise'),
                ])
                ->columns(5),

            Forms\Components\Section::make('Addresses')
                ->schema([
                    Forms\Components\Select::make('shipping_address_id')
                        ->relationship('shippingAddress', 'title')
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('billing_address_id')
                        ->relationship('billingAddress', 'title')
                        ->searchable()
                        ->preload(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Notes')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Customer Notes')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Admin Notes')
                        ->columnSpanFull(),
                ]),
        ];
    }


    protected function getDynamicMiddleFormPartAsOperation(): array
    {

        return array_merge($this->getUserHasCartFormSchema(),$this->getOrderItemRepeaterSchema());
    }



    protected function getUserHasCartFormSchema(): array
    {
        return [
            Forms\Components\Repeater::make('cart')
                ->label('Cart Details')
                ->columns(3)
                ->columnSpanFull()
                ->visible(fn (string $operation,Forms\Get $get): bool => $operation === 'create' && $get('customerable_id'))
                ->lazy()
                ->schema([

                    Forms\Components\Select::make('cartable_id')
                        ->label('Select Product')
                        ->options(fn() => self::getProducts()?->pluck('name', 'id') ?? [])
                        ->required()
                        ->visibleOn('create')
                        ->live(),

                    Forms\Components\TextInput::make('quantity')
                        ->default(1)
                        ->lazy()
                        ->hint(function (Get $get) {
                            $productId = $get('cartable_id');
                            if (! $productId) return null;
                            $product = self::getProducts($productId);
                            return 'Min: ' . $product->min_quantity . '  Max: ' . $product->max_quantity;
                        })
                        ->minValue(fn(Get $get) => optional(self::getProducts($get('cartable_id')))->min_quantity ?? 1)
                        ->maxValue(fn(Get $get) => optional(self::getProducts($get('cartable_id')))->max_quantity ?? 1)
                        ->required()
                        ->integer(),

                    Forms\Components\Placeholder::make('price')
                        ->content(function (Get $get,Forms\Set $set) {
                            $productId = $get('cartable_id');
                            $quantity = $get('quantity');

                            if (! $productId) {
                                return 'Select Product and set quantity!';
                            }
                            if (! $quantity) {
                                return 'Set Product quantity!';
                            }

                            $product = self::getProducts($productId);
                            $unitPrice = $product?->price ?? 0;
                            $total = LaravelMoney::make($unitPrice)->multiply($quantity);
                            return 'Total = ' . $total->formatted() . ' (' .
                                LaravelMoney::make($unitPrice)->formatted() . ' × ' . $quantity . ')';
                        }),

                ]),

            Forms\Components\Placeholder::make('silent_cart_updater')
                ->hiddenLabel()
                ->live()
                ->visible(fn (string $operation,Forms\Get $get): bool => $operation === 'create' && $get('customerable_id'))
                ->content(fn (Get $get,Forms\Set $set) => self::resolveLiveCart($get,$set)),
        ];
    }



    protected function getOrderItemRepeaterSchema(): array
    {
        return [
//            Forms\Components\Section::make('Ordered Items')
//                ->visible(fn (string $operation,Forms\Get $get): bool => $operation === 'edit' && $get('customerable_id'))
//                ->collapsible()
//                ->collapsed()
//                ->columnSpanFull()
//                ->schema([
//                    Forms\Components\Repeater::make('items')
//                        ->relationship('orderProducts')
//                        ->label('Ordered Items')
//                        ->hiddenLabel()
//                        ->columns(3)
//                        ->columnSpanFull()
//                        ->lazy()
//                        ->schema([
////                            Forms\Components\TextInput::make('productname'),
////                            Forms\Components\TextInput::make('product_sku'),
//                        ])
//                ])
        ];
    }
















}
