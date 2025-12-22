<?php

namespace App\Filament\Resources\Order\OrderResource\Pages;

use App\Filament\Resources\Order\OrderResource;

use App\Services\OrderService\OrderCreationService;
use App\Services\OrderService\OrderService;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Mintreu\LaravelCommerinity\Services\CartService\Cart;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class CreateOrder extends CreateRecord
{
    use OrderResource\Schemas\Traits\HasOrderCreateFilamentPageSupport;

    protected static string $resource = OrderResource::class;
    protected ?Collection $orderAbleProducts = null;



    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // $record = new ($this->getModel())($data);



        $customer = $this->getCachedForms()['form']->getLivewire()->data['cached_customer'];

        $orderService = OrderService::make();
        $data['shipping_address_id'] = (int)$data['shipping_address_id'][0];
        $data['billing_address_id']  = (int)$data['billing_address_id'][0];

        $shippingAddressId = $data['shipping_address_id'];
        $billingAddressId = $data['billing_address_id'];

        $shippingAddress = $customerAddress = $customer->addresses->where('id',$shippingAddressId)->first();
        $billingAddress = $customerAddress = $customer->addresses->where('id',$billingAddressId)->first();

        $cartService = new Cart($customer);


        $record = $orderService
            ->create(cart: $cartService,billing: $billingAddress,shipping: $shippingAddress,isFilament: true,filamentResource: self::$resource);




        if (
            static::getResource()::isScopedToTenant() &&
            ($tenant = Filament::getTenant())
        ) {
            return $this->associateRecordWithTenant($record, $tenant);
        }

        $record->save();

        return $record;
    }





}
