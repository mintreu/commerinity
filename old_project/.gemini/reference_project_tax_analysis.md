# Reference Project (references/popkult/apiserver) Tax Calculation Analysis

This report details the tax calculation logic implemented within the `references/popkult/apiserver` directory, treating it as a reference project.

## 1. Database Schema (Migrations)

The following tables and columns are directly involved in tax storage:

*   **`products` table (`2025_08_21_232809_create_products_table.php` and `2025_11_07_000000_add_gst_tax_type_to_products_table.php`):**
    *   `gst_tax_type` (string): Stores a key for the applicable tax rate. This is added by a separate migration.
    *   `price` (unsignedBigInteger): Stores the product price (in paise for precision).
*   **`orders` table (`2025_11_06_100000_create_orders_table.php`):**
    *   `tax` (unsignedBigInteger): Stores the total tax amount for the entire order (in paise).
    *   `subtotal`, `shipping_cost`, `discount`, `total` (unsignedBigInteger): Used for overall order value calculation (in paise).
*   **`order_items` table (`2025_11_06_100001_create_order_items_table.php`):**
    *   **Crucially, this table DOES NOT have a dedicated `tax` column.** It stores `unit_price` and `total_price` for the item, but tax for individual items is calculated dynamically.
*   **`cart_customer` table (`2025_09_05_184503_create_cart_customer_table.php`):**
    *   No direct tax-related columns. This pivot table represents the items in a customer's cart (`customer_id`, `product_id`, `quantity`).

## 2. Models

### `App\Models\Product`

*   **Location:** `references/popkult/apiserver/app/Models/Product.php`
*   **Key Fields:**
    *   `gst_tax_type`: A string representing the tax rate.
*   **`GstTaxCast` Usage:** The `gst_tax_type` attribute is cast using `App\Casts\GstTaxCast`, which is the same Enum used in the `backend` project.
*   **`preferredWarehouseAddress()`:** A method to retrieve a preferred warehouse address associated with the product's available stock. This is a source for the `sellerAddress` in tax calculations.

### `App\Models\Order`

*   **Location:** `references/popkult/apiserver/app/Models/Order.php`
*   **Key Fields:** Contains `tax`, `subtotal`, `shipping_cost`, `discount`, and `total` attributes, which are populated during the order creation process. No direct tax calculation logic resides here.
*   **`items()` Relation:** Relates to `OrderItem` models. As noted, `OrderItem` does not store tax directly.

## 3. Casts

### `App\Casts\GstTaxCast`

*   **Location:** `references/popkult/apiserver/app/Casts/GstTaxCast.php` (Same as `backend` project)
*   **Role:** An Enum that defines the available GST (Goods and Services Tax) rates.
    *   `NONE` (0%), `GST_5` (5%), `GST_18` (18%), `GST_40` (40%).
*   **Key Methods (Relevant for Tax Calculation):**
    *   `percentage()`: Returns the integer percentage of the selected tax slab.
    *   `determineTaxType(?string $customerState, ?string $warehouseState)`: A static method that determines if the transaction is `CGST/SGST` (intra-state/UT) or `IGST` (inter-state) based on the provided state codes. It uses a predefined list of `UNION_TERRITORY_STATES`.

## 4. Controllers

### `App\Http\Controllers\Api\CartController`

*   **Location:** `references/popkult/apiserver/app/Http/Controllers/Api/CartController.php`
*   **Role:** Orchestrates the cart operations.
*   **Tax Relevance:** In its `index()` method, it injects and utilizes `App\Services\CartService`, calling its `getCartTotal()` method. It passes the `customer`, `shippingAddress`, and `shippingStateOverride` to this service, which are crucial inputs for location-based tax determination.

## 5. Services

### `App\Services\CartService`

*   **Location:** `references/popkult/apiserver/app/Services/CartService.php`
*   **Role:** Contains the core logic for managing the cart and calculating its totals, including tax.
*   **`getCartTotal()` Method:**
    *   **Inputs:** `Customer`, optional `shippingAddress`, optional `shippingStateOverride`.
    *   **Money Handling:** Instantiates `App\Services\MoneyService` for all monetary calculations.
    *   **State Resolution:**
        *   `resolveShippingState()`: Determines the customer's shipping state.
        *   `resolveWarehouseState()`: Determines the warehouse's state for each product using `product->availableStocks->first()->address->state`.
    *   **Per-Item Tax Calculation (Loop):** Iterates through each `cartItem` (product):
        *   Determines `gstType` using `GstTaxCast::determineTaxType($shippingState, $warehouseState)`.
        *   Retrieves `gstPercentage` from the product's `gst_tax_type` (`$item->gst_tax_type?->percentage()`).
        *   If `gstPercentage > 0` and both states are resolved, it calculates `unitTax` and then `lineTax` (`unitTax * quantity`).
        *   Aggregates `totalTax` by summing up `lineTax` for all items.
    *   **Output:** Returns a comprehensive array including:
        *   `subtotal`, `tax` (total aggregated tax), `total` (subtotal + tax), `discount`, `shipping_cost`.
        *   `items`: An array containing details for each product, including `item_tax` (the calculated tax for that line item).
        *   `tax_breakdown`: A separate array providing a detailed breakdown of tax per product.

### `App\Services\MoneyService`

*   **Location:** `references/popkult/apiserver/app/Services/MoneyService.php`
*   **Role:** Provides a robust wrapper around the `MoneyPHP` library for precise monetary calculations.
*   **Key Features:**
    *   All calculations done in "paise" (integers) to avoid floating-point issues.
    *   Supports `add`, `subtract`, `multiply`, `divide` operations.
    *   Ensures immutability (returns new `MoneyService` instances).
    *   Provides formatting methods (`format()`, `formatForApi()`).
    *   Default currency is INR.

## 6. End-to-End Tax Flow (Reference Project)

1.  A user adds products to their cart. The `CartController` receives the request and interacts with `App\Services\CartService`.
2.  The `CartService::getCartTotal()` method is invoked, receiving the `customer` object and relevant `shippingAddress`/`shippingState` information.
3.  Inside `getCartTotal()`, for each product in the customer's cart:
    *   The customer's `shippingState` is resolved.
    *   The product's `warehouseState` is resolved via its `preferredWarehouseAddress()` method (which looks at available stock locations).
    *   Using these two states, `GstTaxCast::determineTaxType()` identifies if the transaction is inter-state (IGST) or intra-state (CGST/SGST/UTGST).
    *   The `gstPercentage` is retrieved from the product's `gst_tax_type` attribute.
    *   The `lineTax` for the product is calculated dynamically by applying the `gstPercentage` to the `unit_price` and multiplying by the `quantity` using `MoneyService` for precision.
4.  These individual `lineTax` amounts are summed up to derive the `totalTax` for the entire cart.
5.  The `getCartTotal()` method returns a detailed data structure, including the total calculated tax and the tax breakdown for each item.
6.  When an order is created from this cart data, the aggregated `totalTax` is stored in the `orders` table. The individual `item_tax` values are *not* persisted in the `order_items` table but are part of the `getCartTotal()` output for display/further processing.

## 7. Key Configuration

*   **Currency**: Hardcoded to 'INR' (Indian Rupee) within `App\Services\MoneyService`.

## 8. Similarities to `backend` Project

*   **`GstTaxCast`**: Both projects use the same `App\Casts\GstTaxCast` enum, providing a consistent definition of GST types and rates.
*   **Location-based Tax Determination**: The mechanism to determine inter-state (IGST) vs. intra-state (CGST/SGST/UTGST) based on customer and seller (warehouse) states is identical.
*   **Product-level Tax Attributes**: Products in both projects carry an attribute (`tax_slab` or `gst_tax_type`) that defines their base tax rate.
*   **Order-level Total Tax Persistence**: The `orders` table in both projects stores the aggregated total tax.

## 9. Differences from `backend` Project

*   **Line Item Tax Persistence**:
    *   `backend`: Explicitly stores the calculated `tax` for each line item in the `order_products` table.
    *   `reference`: *Does not* store `tax` explicitly per line item in the `order_items` table. The tax is calculated dynamically when cart totals are retrieved and is not persisted on a per-item basis for the final order record.
*   **Tax Calculation Service Encapsulation**:
    *   `backend`: Employs a dedicated `App\Services\TaxCalculationService` class with a `calculate()` method.
    *   `reference`: Integrates the detailed tax calculation logic directly within the `App\Services\CartService::getCartTotal()` method.
*   **Money Handling Implementation**:
    *   `backend`: Uses `Mintreu\LaravelMoney\LaravelMoney` from a third-party package.
    *   `reference`: Uses a custom `App\Services\MoneyService` wrapper around the `MoneyPHP` library.
*   **Cart Implementation Details**: The overall structure and specific service classes/database tables for managing the cart itself (`Mintreu\LaravelCommerinity\Services\CartService\Cart` vs. `App\Services\CartService`, and `carts` table vs. `cart_customer` pivot table) differ significantly.
*   **`is_tax_inclusive` Handling**: The `backend` project's Product model has an `is_tax_inclusive` flag, which was noted as potentially unhandled in the `TaxCalculationService`. The `reference` project's Product model does not appear to have this flag explicitly. This suggests the `reference` project likely always treats product prices as tax-exclusive.
