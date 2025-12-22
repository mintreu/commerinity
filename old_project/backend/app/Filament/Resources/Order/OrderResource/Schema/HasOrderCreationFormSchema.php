<?php

namespace App\Filament\Resources\Order\OrderResource\Schema;

use App\Models\User;
use Filament\Forms\Components;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Mintreu\LaravelGeokit\Casts\AddressTypeCast;
use Mintreu\LaravelIntegration\Casts\IntegrationTypeCast;
use Mintreu\LaravelIntegration\Models\Integration;
use Mintreu\LaravelMoney\LaravelMoney;

trait HasOrderCreationFormSchema
{
    /**
     * Fetch a customer with all relevant relations in one query.
     */
    protected function getCustomerWithCart($customerId)
    {
        if (! $customerId) {
            return null;
        }

        return User::with([
            'addresses' => fn($query) => $query->whereIn('type', [
                AddressTypeCast::HOME->value,
                AddressTypeCast::WORK->value,
                AddressTypeCast::OTHER->value,
            ]),
            'media' => fn($query) => $query->where('collection_name', 'avatarImage'),
            'cart.cartable',
        ])->find($customerId);
    }

    /**
     * Customer selection + address display.
     */
    public function chooseCustomer(): array
    {
        return [
            Select::make('customer_id')
                ->label('Customer')
                ->options(fn() => User::pluck('name', 'id'))
                ->searchable(['name', 'email', 'mobile'])
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    if (! $state) {
                        $set('cached_customer', null);
                        $set('cart', []);
                        return;
                    }

                    $customer = $this->getCustomerWithCart($state);
                    $set('cached_customer', $customer);

                    $cartProducts = $customer->cart->map(fn($item) => [
                        'cartable_id' => $item->cartable_id,
                        'quantity' => $item->quantity,
                    ])->toArray();

                    $set('cart', $cartProducts);
                }),

            Placeholder::make('customer_info')
                ->content(function (Get $get) {
                    $customer = $get('cached_customer');

                    if (! $customer) {
                        return new HtmlString('<p class="text-gray-500 text-sm">Select a customer to view details.</p>');
                    }

                    $name = e($customer->name);
                    $email = e($customer->email);
                    $mobile = $customer->mobile ? e($customer->mobile) : '—';
                    $gender = $customer->gender ? e($customer->gender) : '—';
                    $avatarUrl = $customer->getFirstMediaUrl('avatarImage')
                        ?: 'https://ui-avatars.com/api/?name=' . rawurlencode($customer->name);

                    $alt = $name . "'s avatar";

                    $html = '
                    <div class="flex flex-col md:flex-row items-center gap-3 p-3 border border-gray-200 rounded-md shadow-sm bg-white dark:bg-gray-800">
                        <div class="flex-shrink-0">
                            <img src="' . $avatarUrl . '" alt="' . $alt . '" class="w-10 h-10 rounded-full object-cover border border-gray-300 shadow-sm">
                        </div>
                        <div class="flex flex-col text-sm text-gray-700 dark:text-gray-200 leading-tight">
                            <div class="font-semibold text-base text-gray-900 dark:text-white">' . $name . '</div>
                            <div><span class="font-medium">Email:</span> ' . $email . '</div>
                            <div><span class="font-medium">Mobile:</span> ' . $mobile . '</div>
                            <div><span class="font-medium">Gender:</span> ' . $gender . '</div>
                        </div>
                    </div>';

                    return new HtmlString($html);
                }),

            Select::make('billing_address_id')
                ->label('Billing Address')
                ->visible(fn(Get $get) => filled($get('customer_id')))
                ->reactive()
                ->options(fn(Get $get) =>
                    optional($get('cached_customer'))->addresses?->pluck('title', 'id')->toArray() ?? []
                )
                ->required(),

            Select::make('shipping_address_id')
                ->label('Shipping Address')
                ->visible(fn(Get $get) => filled($get('customer_id')))
                ->reactive()
                ->options(fn(Get $get) =>
                    optional($get('cached_customer'))->addresses?->pluck('title', 'id')->toArray() ?? []
                )
                ->required(),
        ];
    }

    /**
     * Product selection and pricing repeater.
     */
    public function chooseProducts(Get $get): array
    {
        return [
            Toggle::make('has_discount')->default(false),

            Components\TextInput::make('voucher')
                ->label('Enter Coupon Code')
                ->exists('voucher_codes', 'code')
                ->inlineLabel()
                ->nullable(),

            Components\Repeater::make('cart')
                ->columnSpanFull()
                ->columns(3)
                ->schema([
                    Components\Select::make('cartable_id')
                        ->label('Select Product')
                        ->options(fn() => $this->getProducts()?->pluck('name', 'id') ?? [])
                        ->required()
                        ->live(),

                    Components\TextInput::make('quantity')
                        ->lazy()
                        ->hint(function (Get $get) {
                            $productId = $get('cartable_id');
                            if (! $productId) return null;
                            $product = $this->getProducts($productId);
                            return 'Min: ' . $product->min_quantity . '  Max: ' . $product->max_quantity;
                        })
                        ->integer()
                        ->minValue(fn(Get $get) => optional($this->getProducts($get('cartable_id')))->min_quantity ?? 1)
                        ->maxValue(fn(Get $get) => optional($this->getProducts($get('cartable_id')))->max_quantity ?? 1)
                        ->required()
                        ->default(1),

                    Placeholder::make('price')
                        ->content(function (Get $get) {
                            $productId = $get('cartable_id');
                            $quantity = $get('quantity') ?? 1;

                            if (! $productId) {
                                return 'Select Product and set quantity!';
                            }

                            $product = $this->getProducts($productId);
                            $unitPrice = $product?->cheapestTier?->price ?? $product->price;
                            $total = LaravelMoney::make($unitPrice)->multiply($quantity)->formatted();

                            return 'Total = ' . $total . ' (' .
                                LaravelMoney::format($unitPrice) . ' × ' . $quantity . ')';
                        }),
                ]),
        ];
    }






    public function getDeliveryFormSchema()
    {
        return [
            // Future: delivery step schema
            Select::make('integration_id')
                ->label('Payment through')
                ->live()
                ->inlineLabel()
                ->options(Integration::where('type',IntegrationTypeCast::PAYMENT->value)->get()->pluck('name','id'))
                ->required(),


            Toggle::make('is_cod')
                ->label('Cash on Delivery')
                ->visible(function (Get $get) {
                    $integrationId = $get('integration_id');

                    if (! $integrationId) {
                        return false;
                    }

                    $integration = Integration::find($integrationId);
                    if (! $integration || ! $integration->url) {
                        return false;
                    }

                    $url = Str::lower($integration->url);

                    // Must contain 'cod' OR 'cash'
                    $matchesCodOrCash = Str::contains($url, ['cod', 'cash','wallet']);

                    // Must NOT contain 'cash-free' or 'free'
                    $containsExcluded = Str::contains($url, ['cash-free', 'free']);

                    return $matchesCodOrCash && ! $containsExcluded;
                })
                ->reactive()->default(false)

        ];
    }



    public function getInvoiceView()
    {

    }




}
