# Backend Project (Current Project) Tax Calculation Analysis

This report details the tax calculation logic implemented within the `backend` directory of the current project.

## 1. Database Schema (Migrations)

The following tables and columns are directly involved in tax storage:

*   **`products` table (`2025_07_14_223058_create_products_table.php`):**
    *   `is_tax_inclusive` (boolean): Indicates if the product price already includes tax.
    *   `is_exempted` (boolean): Indicates if the product is exempt from tax.
    *   `tax_slab` (string): Stores a key for the applicable tax rate.
*   **`orders` table (`2025_08_23_130905_create_orders_table.php`):**
    *   `tax` (integer): Stores the total tax amount for the entire order.
    *   `subtotal`, `discount`, `total` (integers): Used for overall order value calculation.
*   **`order_products` table (`2025_08_23_132737_create_order_products_table.php`):**
    *   `tax` (integer): Stores the tax amount for that specific product line item within an order.
    *   `quantity`, `amount`, `discount`, `total` (integers): Used for line item value calculation.
*   **`carts` table (`2025_08_01_101758_create_carts_table.php`):**
    *   No direct tax-related columns. Tax calculation is handled during the cart processing stage, not stored persistently in the cart itself.

## 2. Models

### `App\Models\Product` (Extends `Mintreu\LaravelProductCatalogue\Models\Product`)

*   **Location:** `backend/app/Models/Product.php`
*   **Key Fields (from parent package model):**
    *   `is_tax_inclusive`: Determines if the base price includes tax.
    *   `is_exempted`: Determines if the product is tax-exempt.
    *   `tax_slab`: A string representing the tax rate.
*   **`GstTaxCast` Usage:** The `tax_slab` attribute is cast using `App\Casts\GstTaxCast`. This cast is crucial for interpreting the `tax_slab` value.

### `App\Models\Order\Order`

*   **Location:** `backend/app/Models/Order/Order.php`
*   **Key Fields:** Contains `tax`, `subtotal`, `discount`, and `total` attributes, which are populated during the order creation process. No direct tax calculation logic resides here.

### `App\Models\Cart`

*   **Location:** `backend/app/Models/Cart.php`
*   **Tax Relevance:** No direct tax calculation or storage. It's a container for items before they become an order.

## 3. Casts

### `App\Casts\GstTaxCast`

*   **Location:** `backend/app/Casts/GstTaxCast.php`
*   **Role:** An Enum that defines the available GST (Goods and Services Tax) rates.
    *   `NONE` (0%), `GST_5` (5%), `GST_18` (18%), `GST_40` (40%).
*   **Key Methods:**
    *   `percentage()`: Returns the integer percentage of the selected tax slab.
    *   `getIgstRate()`: Returns the Integrated GST rate based on the slab.
    *   `getCgstRate()`: Returns the Central GST rate based on the slab.
    *   `getSgstRate()`: Returns the State GST rate based on the slab.
    *   `determineTaxType(?string $customerState, ?string $warehouseState)`: A static method that determines if the transaction is `CGST/SGST` (intra-state/UT) or `IGST` (inter-state) based on the provided state codes. It uses a predefined list of `UNION_TERRITORY_STATES`.

## 4. Controllers

### `App\Http\Controllers\Api\CartController`

*   **Location:** `backend/app/Http/Controllers/Api/CartController.php`
*   **Role:** Orchestrates the cart operations.
*   **Tax Relevance:** In its `index()` method, it instantiates `Mintreu\LaravelCommerinity\Services\CartService\Cart` and calls its `getMeta()` method, passing the `$customerAddress`. This `customerAddress` is critical for location-based tax determination.

## 5. Services (Mintreu\LaravelCommerinity Package)

### `Mintreu\LaravelCommerinity\Services\CartService\Cart`

*   **Location:** `backend/vendor/mintreu/laravel-commerinity/src/Services/CartService/Cart.php`
*   **Role:** Manages the overall cart and aggregates data.
*   **Tax Relevance:**
    *   The `getMeta()` method calls `prepareMeta()`, which then processes each cart item individually via `CartLineService`.
    *   The `getSummaryMeta()` method iterates through the processed line items and aggregates their `sub_total`, `discount`, `tax`, and `total` (all handled as `LaravelMoney` objects). This confirms that tax is calculated per line item and then summed for the entire cart/order.

### `Mintreu\LaravelCommerinity\Services\CartService\CartLineService`

*   **Location:** `backend/vendor/mintreu/laravel-commerinity/src/Services/CartService/CartLineService.php`
*   **Role:** Handles calculations for a single cart line item.
*   **Tax Calculation Trigger:** The `calculating()` method is the central point for per-line-item tax calculation.
*   **Address Resolution:**
    *   It determines the `sellerAddress` by utilizing `Mintreu\LaravelProductCatalogue\Services\StockLocatorService`. This service finds the `bestTier` (product tier) for the product based on the `customerAddress`, and the `bestTier` likely contains or provides the seller's address information.
*   **`TaxCalculationService` Invocation:** It instantiates `App\Services\TaxCalculationService` and calls its `calculate()` method, passing:
    *   The `Product` model (`$this->cartable`).
    *   The line item's `subTotal` (as a `LaravelMoney` object).
    *   The resolved `sellerAddress`.
    *   The `customerAddress`.
*   It then stores the `total_tax` and `taxDetails` returned by the `TaxCalculationService`.
*   The final line item `total` is calculated as `subTotal - discount + taxAmount`.

## 6. Services (Application Specific)

### `App\Services\TaxCalculationService`

*   **Location:** `backend/app/Services/TaxCalculationService.php`
*   **Role:** Implements the core logic for calculating GST based on product and location.
*   **`calculate` Method Parameters:**
    *   `Product $product`: The product for which tax is being calculated.
    *   `LaravelMoney $lineItemPrice`: The base price (quantity * unit price) for the line item.
    *   `Address $sellerAddress`: The address of the seller/warehouse.
    *   `Address $buyerAddress`: The address of the buyer/customer.
*   **Logic:**
    1.  **Exemption/No Slab Check:** If `product->tax_slab` is null or `product->is_exempted` is true, it immediately returns a zero tax breakdown.
    2.  **Inter-state Determination:** Compares `sellerAddress->state_code` and `buyerAddress->state_code` to determine if it's an inter-state transaction.
    3.  **IGST (Inter-state):** If `isInterState` is true, it retrieves the IGST rate from `$slab->getIgstRate()` (where `$slab` is the `GstTaxCast` instance from the product's `tax_slab`) and calculates the total tax using this rate.
    4.  **CGST + SGST (Intra-state):** If `isInterState` is false, it retrieves `cgstRate` and `sgstRate` from `$slab->getCgstRate()` and `$slab->getSgstRate()` respectively, calculates separate amounts, and sums them for the `total_tax`.
    5.  **Output:** Returns a structured array containing rates and amounts for CGST, SGST, IGST, and the `total_tax`, all using `LaravelMoney` for amounts.

## 7. Key Configuration

*   **`config('laravel-commerinity.cart.guest.header_id')`**: Used in `CartController` for guest identification, though not directly related to tax calculation.
*   **`LaravelMoney`**: The extensive use of `Mintreu\LaravelMoney\LaravelMoney` ensures precision in financial calculations.

## 8. End-to-End Tax Flow (Backend Project)

1.  A user adds products to their cart. The `CartController` receives the request.
2.  The `CartController` initializes the `Cart` service and calls `getMeta()`, passing the `customerAddress` (if available).
3.  The `Cart` service then iterates through each item in the cart. For each item, it creates a `CartLineService` instance.
4.  The `CartLineService`'s `calculating()` method performs the core line-item calculation:
    *   It determines the `sellerAddress` (e.g., origin warehouse) based on the product and customer address using `StockLocatorService`.
    *   It instantiates `TaxCalculationService`.
    *   It calls `TaxCalculationService::calculate()` with the `Product` model (containing `is_exempted`, `is_tax_inclusive`, and `tax_slab`), the line item's base price, the `sellerAddress`, and the `customerAddress`.
5.  `TaxCalculationService::calculate()` then:
    *   Checks if the product is tax-exempt or has no `tax_slab`.
    *   Compares the `state_code` of the `sellerAddress` and `customerAddress` to determine if the transaction is inter-state or intra-state.
    *   Retrieves the appropriate GST rate (IGST, or CGST/SGST) from the product's `tax_slab` (which is an `GstTaxCast` enum instance).
    *   Calculates the tax amount based on the rate and the line item price.
6.  The calculated tax amount and breakdown are returned to `CartLineService`.
7.  `CartLineService` updates the line item's total with the calculated tax.
8.  The `Cart` service aggregates the individual line item taxes to get the total cart tax.
9.  When an order is placed, these calculated `tax` values are stored in the `orders` and `order_products` tables.

## 9. Potential Issues / Areas for Clarification

*   **`is_tax_inclusive` Handling:** The current `TaxCalculationService` assumes the `lineItemPrice` is tax-exclusive and calculates tax to be *added*. If `is_tax_inclusive` is true for a product, the `lineItemPrice` would need to be adjusted (tax extracted) *before* `TaxCalculationService::calculate()` is called, or the service itself would need to incorporate logic to handle this flag.
*   **Warehouse/Seller Address Origin:** While the `StockLocatorService` is used, the exact configuration or sourcing of the "warehouse address" (or `sellerAddress`) for `bestTier` is crucial for correct tax determination and should be thoroughly verified.
