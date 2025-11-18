<?php

namespace App\Filament\Resources\Order\OrderResource\Schemas\Traits;


use App\Filament\Resources\Order\OrderResource\Schemas\OrderForm;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Mintreu\LaravelGeokit\Models\Address;

trait HasOrderCreateFilamentPageSupport
{


    use HasWizard;

    public function form(Form $form): Form
    {
        return parent::form($form)->schema($this->wizardOrderCreationSchema());
    }

    protected function wizardOrderCreationSchema():array
    {
        return [
            Wizard::make([
                Wizard\Step::make('Order')
                    ->schema(OrderForm::configure()),
                Wizard\Step::make('Preview')
                    ->schema([

                        Placeholder::make('bill_preview')
                            ->hiddenLabel()
                            ->content(fn (Get $get) => $this->getBillPreviewHtml($get))
                            ->columnSpanFull(),



                    ]),
            ])->columnSpanFull()
                ->skippable()
                ->submitAction(new HtmlString(Blade::render(<<<BLADE
    <x-filament::button
        type="submit"
        color="primary"
        size="sm"
    >
        Save
    </x-filament::button>
BLADE)))
        ];
    }





    protected function getBillPreviewHtml(Get $get): HtmlString
    {
        $customer = $get('cached_customer');
        $meta = $get('cached_meta');

        if (! $customer || ! $meta) {
            return new HtmlString('');
        }

        $customerName = $customer->name ?? 'N/A';
        $billingAddress = $customer->addresses->where('id',$get('billing_address_id'))->first() ;
        if ($billingAddress instanceof Address)
        {
            $billingAddress->loadMissing('state');
        }

        $customerAddress = $billingAddress?->address_1 ?? 'No billing address provided.';
        $customerCity = $billingAddress?->city ?? '';
        $customerState = $billingAddress?->state->name ?? '';
        $customerPostalCode = $billingAddress?->postal_code ?? '';

        $lineItems = '';
        foreach ($meta as $key => $item) {
            if (! is_numeric($key) || ! is_array($item)) {
                continue;
            }

            $productName = $item['product']['name'] ?? 'N/A';
            $quantity = $item['quantity'] ?? 0;
            $unitPrice = $item['product']['price'] ?? '₹0.00';
            $itemTotal = $item['product']['total'] ?? '₹0.00';

            $lineItems .= <<<HTML
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <td class="py-4 px-6 text-sm font-medium text-gray-900 dark:text-white">{$productName}</td>
                    <td class="py-4 px-6 text-sm text-gray-500 dark:text-gray-400 text-center">{$quantity}</td>
                    <td class="py-4 px-6 text-sm text-gray-500 dark:text-gray-400 text-right">{$unitPrice}</td>
                    <td class="py-4 px-6 text-sm font-medium text-gray-900 dark:text-white text-right">{$itemTotal}</td>
                </tr>
            HTML;
        }

        $subtotal = $meta['sub_total'] ?? '₹0.00';
        $total = $meta['total'] ?? '₹0.00';

        return new HtmlString(<<<HTML
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-header px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Invoice Preview
                    </h3>
                </div>
                <div class="fi-section-content p-6">
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div>
                            <h4 class="text-base font-medium text-gray-800 dark:text-gray-200 mb-2">Bill To:</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <strong class="font-semibold text-gray-900 dark:text-white">{$customerName}</strong><br>
                                {$customerAddress}<br>
                                {$customerCity}, {$customerState} {$customerPostalCode}
                            </p>
                        </div>
                        <div class="text-right">
                             <p class="text-sm text-gray-500 dark:text-gray-400">Order Date: <span class="font-medium text-gray-700 dark:text-gray-300">
                                    2025-11-11
                                </span></p>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-t-xl">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="bg-primary-600 text-xs text-white uppercase">
                                <tr>
                                    <th scope="col" class="py-3 px-6">Product</th>
                                    <th scope="col" class="py-3 px-6 text-center">Quantity</th>
                                    <th scope="col" class="py-3 px-6 text-right">Unit Price</th>
                                    <th scope="col" class="py-3 px-6 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {$lineItems}
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <div class="w-full max-w-xs">
                            <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Subtotal:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{$subtotal}</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-base font-semibold text-gray-900 dark:text-white">Total:</span>
                                <span class="text-base font-semibold text-gray-900 dark:text-white">{$total}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        HTML);
    }





}
