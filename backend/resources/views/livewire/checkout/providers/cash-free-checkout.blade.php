<div>
    <button id="cashfree-button1" class="text-lg md:text-xl py-1 rounded-lg w-96 shadow-lg border-2 border-green-500">
        <i class="fas fa-money-bill"></i>
        Pay Via Cashfree
    </button>
</div>

@assets
<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
@endassets

@push('scripts')
    <script>
        const isDev = @js($mode); // true/false from backend
        const cashfree = new Cashfree({ mode: isDev ? 'sandbox' : 'production' });

        function getCashfreeCheckout() {
            return {
                paymentSessionId: @js($paymentSessionId), // from DB: provider_gen_session
                returnUrl: @js($successUrl) // already has {order_id} placeholder
            };
        }

        document.getElementById('cashfree-button1').onclick = function(e) {
            e.preventDefault();
            let options = getCashfreeCheckout();

            cashfree.checkout(options).then(function(result) {
                if (result.error) {
                    console.error("Cashfree error:", result.error);
                    window.location.replace(@js($failureUrl));
                }
                if (result.redirect) {
                    console.log("Redirecting...");
                }
            });
        };

        window.addEventListener("load", () => {
            let isPending = @js($payable);
            if (isPending) {
                let options = getCashfreeCheckout();
                cashfree.checkout(options).then(function(result) {
                    if (result.error) {
                        console.error("Cashfree error:", result.error);
                        window.location.replace(@js($failureUrl));
                    }
                });
            }
        });
    </script>
@endpush
