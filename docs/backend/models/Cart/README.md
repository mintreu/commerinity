# Commerinity Cart Module Documentation

This document provides a comprehensive guide to the cart module of the Commerinity e-commerce platform. It is intended for developers who want to understand, use, and extend the cart functionality.

## 1. High-Level Overview

The cart module is designed with a layered architecture to ensure a clear separation of concerns. Here's a high-level overview of how the core components work together:

```
+----------------------+
|   `CartController`   |
+----------------------+
          |
          v
+----------------------+
|      `Cart` Service  |
+----------------------+
          |
          v
+----------------------+
|    `CartService`     |
+----------------------+
          |
          v
+----------------------+
|  `CartLineService`   |
+----------------------+
```

1.  **`CartController`**: The entry point for all HTTP requests related to the cart.
2.  **`Cart` Service**: Responsible for generating structured cart data (metadata) for API responses.
3.  **`CartService`**: The base service that contains the core business logic for cart operations.
4.  **`CartLineService`**: Handles the calculations for each individual line item in the cart.

## 2. Core Components

### 2.1. `CartController`

-   **File**: `app/Http/Controllers/Api/CartController.php`
-   **Role**: This controller is responsible for handling all incoming API requests for the cart. It receives HTTP requests, calls the appropriate methods in the `Cart` service, and returns the JSON responses.

### 2.2. `Cart` Service

-   **File**: `packages/mintreu/laravel-commerinity/src/Services/CartService/Cart.php`
-   **Role**: This service extends `CartService` and is primarily responsible for preparing the cart data for the API. It generates a structured array (`meta`) that includes the cart summary, customer information, and a list of all items in the cart. It uses `CartLineService` to process each item.

### 2.3. `CartService`

-   **File**: `packages/mintreu/laravel-commerinity/src/Services/CartService/CartService.php`
-   **Role**: This is the base service for the cart module. It contains the fundamental business logic for managing the cart, including:
    -   Adding, updating, and deleting items.
    -   Emptying the cart.
    -   Handling guest user carts using the `HasGuestCartSupport` trait.
    -   Managing and validating coupon codes using the `HasVoucherCodeValidator` trait.

### 2.4. `CartLineService`

-   **File**: `packages/mintreu/laravel-commerinity/src/Services/CartService/CartLineService.php`
-   **Role**: This service is responsible for a single line item in the cart. It performs all the calculations for an individual item, such as:
    -   Resolving the product price (considering product tiers and sales).
    -   Calculating the subtotal, discount, tax, and final total for the item.
    -   Validating sales and vouchers for the specific item.

## 3. Database Model (`App\Models\Cart.php`)

The `Cart` model represents a single item in a shopping cart.

-   **File:** `backend/app/Models/Cart.php`
-   **Key Attributes:**
    -   `quantity` (integer)
    -   `discount` (integer) - **Note**: The unit of this field needs clarification.
    -   `cartable_id`, `cartable_type`: Polymorphic relation to the cartable item (e.g., `Product`).
    -   `ownerable_id`, `ownerable_type`: Polymorphic relation to the cart owner (e.g., `User`).
    -   `guest_id`, `guest_token`, `is_guest`: For guest cart functionality.

## 4. Guest Cart Workflow

The cart module provides a seamless experience for guest users. Here's how to implement the guest cart workflow on the frontend:

1.  **Generate Credentials**: On the first interaction with the cart by a guest user, send a POST request to `/api/cart/guest-credential`.
2.  **Store Credentials**: The API will return a `guest_id` and a `guest_token`. Store these on the client-side (e.g., in local storage or cookies).
3.  **Send Credentials**: For all subsequent cart-related API requests, include the stored credentials in the request headers:
    -   `x-guest-id`: The guest ID.
    -   `x-guest-token`: The guest token.
4.  **Merge Carts**: When the guest user logs in or registers, the `CartService` will automatically merge the guest cart into the user's cart. This is handled by the `capture` method in `CartService`.

## 5. Sales and Vouchers

The cart module has built-in support for sales and vouchers (coupons).

-   **Sales**: The `CartLineService` automatically checks for applicable sales for each item in the cart using the `CartSaleValidator`.
-   **Vouchers**: Vouchers can be applied to the cart using the `/api/cart/coupon/{voucher_code}` endpoint. The `CartService` and `CartVoucherValidator` handle the validation of the voucher.

## 6. Extending the Cart

The modular architecture of the cart system makes it easy to extend. Here are some ideas for new features:

### Saved Carts

The current implementation does not support multiple saved carts for a user. To implement this feature, you could:

1.  **Create a `SavedCart` model**: This model would have a relationship with the `User` model and would store a collection of cart items.
2.  **Add new API endpoints**: Create new endpoints for saving, retrieving, and deleting saved carts.
3.  **Extend the `CartService`**: Add new methods to the `CartService` to handle the logic for managing saved carts.

### Custom Discount Types

To implement custom discount types (e.g., buy-one-get-one, free shipping), you could:

1.  **Create a `Discount` model**: This model would store the different types of discounts and their rules.
2.  **Extend the `CartLineService`**: Modify the `CartLineService` to apply the custom discounts to the line items.

## 7. API Reference

| Method | Endpoint | Controller Method | Purpose |
| --- | --- | --- | --- |
| POST | `/api/cart/add/{product}` | `addProduct` | Add a product to the cart. |
| POST | `/api/cart/update/{product}` | `updateProduct` | Update a product's quantity in the cart. |
| DELETE | `/api/cart/remove/{product}` | `removeProduct` | Remove a product from the cart. |
| POST | `/api/cart/clear` | `clearCart` | Clear all items from the cart. |
| POST | `/api/cart/coupon/{voucher_code}` | `applyCoupon` | Apply a coupon to the cart. |
| GET | `/api/cart` | `index` | Get the current cart details. |
| POST | `/api/cart/guest-credential` | `ensureGuestCartCredential` | Get guest cart credentials. |
| POST | `/api/g/cart/validate/guest-credential` | `validateGuestCartCredential` | Validate guest cart credentials. |
| POST | `/api/cart/merge` | `mergeGuestCart` | Merge a guest cart with a user's cart. |
