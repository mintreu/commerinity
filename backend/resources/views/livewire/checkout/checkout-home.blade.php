<div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="w-full max-w-6xl grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Billing Details Section (hidden on mobile) --}}
        <div class="hidden md:block md:col-span-1 bg-white shadow-lg rounded-2xl p-6">
            <h2 class="text-xl font-semibold mb-4">Billing Details</h2>

            <div class="space-y-2 text-sm text-gray-700">
                <div class="flex justify-between">
                    {{-- Bill Reference or Receipt --}}
                    <span>Bill ID:</span>
                    <span class="font-medium">#{{ $transaction->uuid }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Amount:</span>
                    <span class="font-medium">₹ {{ \Mintreu\LaravelMoney\LaravelMoney::format($transaction->amount) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Status:</span>
                    <span class="font-medium {{ $transaction->status->value === 'pending' ? 'text-yellow-600' : 'text-green-600' }}">
                        {{ ucfirst($transaction->status->getLabel()) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Type:</span>
                    <span class="font-medium">{{ ucfirst($transaction->type->getLabel()) }}</span>
                </div>
            </div>

            <div class="mt-6">
                <h3 class="text-md font-semibold mb-2">Pay With</h3>
                <div class="flex items-center gap-2 mb-3">
                    @if($integration['logo_url'])
                        <img src="{{ $integration['logo_url'] }}" alt="{{ $integration['name'] }} Logo" class="h-8">
                    @else
                        <div class="h-8 w-8 flex items-center justify-center rounded bg-gray-100 text-gray-500">
                            {{ strtoupper(substr($integration['name'], 0, 1)) }}
                        </div>
                    @endif
                    <span class="font-medium">{{ $integration['name'] }}</span>
                </div>
                <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                    <li>Charge: {{ $integration['charge'] ?? '0.00' }}%</li>
                </ul>
            </div>
        </div>

        {{-- Checkout Section --}}
        <div class="md:col-span-2 bg-white shadow-lg rounded-2xl p-6 flex flex-col justify-center items-center">
            <h2 class="text-xl font-semibold mb-4">Payment Checkout</h2>

            <div class="w-full h-64 border-2 border-dashed border-gray-300 rounded-xl flex flex-col items-center justify-center text-gray-500 gap-4">
                @if($integration['url'] == 'razorpay-payment')
                    @livewire('checkout.providers.razorpay-checkout',['transaction' => $transaction])
                @elseif($integration['url'] == 'cash-free-payment')
                    @livewire('checkout.providers.cash-free-checkout',['transaction' => $transaction])
                @else
                    <h2>Choose Payment Option</h2>
                @endif
            </div>
        </div>
    </div>
</div>
