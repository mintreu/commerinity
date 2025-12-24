# Plan: E-commerce Logic Refactoring

**Task:** Overhaul the stock selection and tax calculation logic. The new system will select stock from the inventory point closest to the customer and apply a First-in-First-Out (FIFO) strategy for ties. Tax calculation will be dynamically based on the customer's state, replacing the current `TaxCode` model with a `GstTaxCast` enum system.

---

## **Phase 1: Backend - Core Logic Overhaul**

### **Step 1: Implement Geo-Aware, FIFO Stock Selection**
*   **Title:** Refactor Stock Selection to "Closest, Oldest First".
*   **Details:**
    1.  **Investigate Address Model:** I will start by examining the `Address` model within the `mintreu/laravel-geokit` package to confirm it can store latitude and longitude. If not, a migration will be created to add these fields.
    2.  **Create `StockLocatorService`:** This new service will be the brain of the new selection logic. It will contain a method like `findClosestTier(Product $product, Address $customerAddress)` which will:
        *   Find all `ProductTier`s for the product with available stock.
        *   Calculate the geographical distance between the customer's address and each stock tier's address.
        *   Return the `ProductTier` that is the closest. If multiple tiers are at the same location, it will return the one that was created first (`created_at` ascending) to ensure older stock is sold first (FIFO).
    3.  **Deprecate `cheapestTier`:** The `cheapestTier` relationship on the `Product` model will be removed. I will then perform a codebase search to find all usages of `cheapestTier` and `cheapestTire` and replace them with the new `StockLocatorService`.
*   **Reference Inspiration:** The provided snippets emphasize creating clear plans and interfaces (`Plan` class, `IPlan` interface). This step establishes a clean `StockLocatorService` interface to handle a complex business rule.

### **Step 2: Implement State-Based GST Calculation via Enum**
*   **Title:** Replace `TaxCode` Model with a `GstTaxSlab` Enum and `GstTaxCast`.
*   **Details:**
    1.  **Create `GstTaxSlab` Enum:** I will create a new PHP Enum at `app/Enums/GstTaxSlab.php`. This enum will define the GST slabs (e.g., `GST_18`, `GST_28`) and contain methods to return the corresponding IGST, CGST, and SGST rates.
    2.  **Add `tax_slab` to Products:** A migration will be run to add a `tax_slab` column to the `products` table and remove the old `tax_code_id`.
    3.  **Create `GstTaxCast`:** A new custom Eloquent cast class will be created. This class will be applied to the `product` model's `tax_slab` attribute.
    4.  **Create `TaxCalculationService`:** This service will determine if a transaction is intra-state or inter-state by comparing the seller's address (from the `ProductTier`) and the buyer's address. It will then use the product's `tax_slab` enum to fetch the correct tax rates (CGST/SGST for intra-state, IGST for inter-state).
    5.  **Remove `TaxCode` Model:** The `TaxCode` model and its related files will be deleted from the project.
*   **Reference Inspiration:** Using a structured plan (`mock_plan`) to define sections and content is directly applicable here, as we are planning the structure of the new tax system.

### **Step 3: Fix and Integrate Stock Consumption**
*   **Title:** Implement Accurate Stock Decrementation on Order Placement.
*   **Details:**
    1.  **Fix Bug:** Correct the `getAvailableStockAttribute` method in `ProductTier.php` to accurately calculate `init_quantity - sold_quantity`.
    2.  **Update `order_products` Table:** Add a `product_tier_id` column to the `order_products` table via a migration. This is crucial for knowing which specific stock point to decrement.
    3.  **Integrate with `OrderService`:** In the service responsible for creating orders, after an order is successfully placed, I will add logic to call the `consumeStock()` method on the specific `ProductTier` that is now linked to the `OrderProduct` record.
*   **Reference Inspiration:** The `updatePlan` function snippet shows a clear pattern of receiving data, processing it, and persisting the final state to the server, which is analogous to how the `OrderService` will process an order and persist the new stock level.

---

## **Phase 2: API & Frontend Integration**

### **Step 4: Refactor Cart API and Services**
*   **Title:** Make the Cart Service Address-Aware.
*   **Details:**
    1.  **Modify `CartController`:** The controller handling the `GET /api/cart` route will be updated to accept an optional `address_id` query parameter.
    2.  **Modify `Cart` Service:** The `getMeta()` method will be updated to accept a nullable `Address` object. When an address is provided, it will be passed to the `StockLocatorService` (for price) and `TaxCalculationService` (for tax). If no address is provided, prices and tax will be returned as zero or based on a default.
    3.  **Modify Add-to-Cart Logic:** The `CartService::add` method will also need to be aware of the customer's selected address to validate stock against the *closest* tier.
*   **Reference Inspiration:** The `PlanAPI` class shows a consistent pattern of passing user/context identifiers (`userId`) in API calls, similar to how we will now pass `address_id` to get context-specific cart data.

### **Step 5: Redesign Frontend Checkout Experience**
*   **Title:** Implement "Address First" Checkout Flow.
*   **Details:**
    1.  **Update UI:** The checkout page will be redesigned to require the user to select a shipping address *before* the final total is displayed.
    2.  **Dynamic Recalculation:** When an address is selected, the `useCart.fetchCart()` method will be called again, this time with the `address_id`.
    3.  **Render Updated Data:** The cart summary will reactively update to display the correct price (which may change if a different stock point is now closer) and the precise, destination-based tax.
*   **Reference Inspiration:** The `updatePlan` frontend function demonstrates the pattern we will use: when user input changes (selecting an address), a PUT/fetch request is made to the backend, and the UI is updated with the response.

