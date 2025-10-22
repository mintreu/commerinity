<?php

namespace App\Filament\Resources\Order\OrderResource\Pages;

use App\Filament\Resources\Order\OrderResource\Schema\HasOrderCreationFormSchema;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Resources\Order\OrderResource;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Collection;
use Mintreu\LaravelIntegration\Casts\IntegrationTypeCast;
use Mintreu\LaravelIntegration\Models\Integration;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\Toolkit\Casts\PublishableStatusCast;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class CreateOrder extends CreateRecord
{
    use HasOrderCreationFormSchema, HasWizard;

    protected static string $resource = OrderResource::class;
    protected ?Collection $orderAbleProducts = null;

    public function mount(): void
    {
        parent::mount();
    }

    public function form(Schema $schema): Schema
    {
        return parent::form($schema)
            ->components([
                Wizard::make([
                    Step::make('Customer')
                        ->columns()
                        ->schema($this->chooseCustomer())
                        ->afterValidation(fn(Get $get, callable $set) => $this->afterCustomerSet($get, $set)),

                    Step::make('Order')
                        ->columns()
                        ->schema(fn(Get $get) => $this->chooseProducts($get)),

                    Step::make('Delivery')
                        ->columns()
                        ->schema($this->getDeliveryFormSchema()),

                    Step::make('Billing')
                        ->schema([
                            // Future: billing step schema
                        ]),
                ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Cached product retrieval for efficiency.
     */
    public function getProducts(?int $productId = null, bool $force = false)
    {
        $allProducts = Cache::remember('published_products_with_media', 300, function () {
            return Product::with(['media', 'cheapestTier'])
                ->where('status', PublishableStatusCast::PUBLISHED->value)
                ->get();
        });

        if ($force && $productId) {
            return $allProducts->find($productId) ?? null;
        }

        return $productId ? $allProducts->find($productId) : $allProducts;
    }

    /**
     * Fired after selecting a customer — stores customer & cart in internal state.
     */
    public function afterCustomerSet(Get $get, callable $set): void
    {
        $customerId = $get('customer_id');
        if (! $customerId) {
            $set('cached_customer', null);
            $set('cart', []);
            return;
        }

        // Efficient eager loading
        $user = $this->getCustomerWithCart($customerId);
        if (! $user) {
            $set('cached_customer', null);
            $set('cart', []);
            return;
        }

        $set('cached_customer', $user);

        // Map customer’s cart items into order form structure
        $cartProducts = $user->cart->map(function ($item) {
            return [
                'cartable_id' => $item->cartable_id,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        $set('cart', $cartProducts);
    }
}
