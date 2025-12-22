
# 🛒 Cart & Order Module

The cart and order module is the backbone of the e-commerce functionality, enabling users to select products, manage their selections, and proceed to checkout. It supports both authenticated users and guest users, providing a seamless experience for all.

## ✨ Key Features

- **Guest & Authenticated User Support:** The cart and checkout process is fully functional for both registered users and guests.
- **Persistent Cart:** User carts are saved in the database, while guest carts are maintained through session/token-based persistence.
- **Coupon & Voucher Support:** Easily apply discounts to the cart using voucher codes.
- **Dynamic Pricing:** The cart recalculates totals instantly when quantities change or coupons are applied.
- **Merge Carts:** Guest carts are automatically merged with a user's cart upon login.
- **Guest Checkout:** Guests can place orders without creating an account.

## 🏛️ Technical Architecture

The cart functionality is primarily built around the `mintreu/laravel-commerinity` package, which provides a robust and flexible `CartService`.

### 📦 Backend Components

- **`CartController` (`backend/app/Http/Controllers/Api/CartController.php`):**
  The main entry point for all cart-related API requests. It acts as a thin layer, delegating the heavy lifting to the `CartService`.
  - **`ensureGuestCartCredential`**: Handles the generation of guest IDs and tokens.
  - **`validateGuestCartCredential`**: Validates the guest ID and token sent from the frontend.
  - **`index`**: Retrieves the current cart contents.
  - **`addProduct`**: Adds a product to the cart.
  - **`updateProduct`**: Updates the quantity of a product in the cart.
  - **`removeProduct`**: Removes a product from the cart.
  - **`clearCart`**: Empties the entire cart.
  - **`mergeGuestCart`**: Merges a guest's cart into an authenticated user's cart upon login.
  - **`applyCoupon`**: Applies a voucher code to the cart.

- **`OrderController` (`backend/app/Http/Controllers/Api/OrderController.php`):**
  Handles the order placement logic.
  - **`placeOrder`**: Processes the cart contents and customer information to create a new order.

- **`CartService` (`backend/packages/mintreu/laravel-commerinity/src/Services/CartService/CartService.php`):**
  The heart of the cart module. This service class handles all the business logic, including adding, updating, and removing items, as well as managing guest carts and coupons.
  - **`capture(Request $request)`**: Initializes the cart service, identifies user type (guest/authenticated), and validates guest tokens.
  - **`items()`**: Retrieves cart items from the database based on user type.
  - **`add(Model|Product $item, int $quantity)`**: Adds a product to the cart, handling quantity limits.
  - **`update(Model|Product $item, int $quantity)`**: Updates a product's quantity in the cart.
  - **`delete(Model $item)`**: Removes a product from the cart.
  - **`empty()`**: Clears all items from the cart.
  - **`getTotalQuantity()`**: Returns the total number of items in the cart.
  - **`setError(?string $msg)` / `getErrors()`**: Manages error messages.
  - **`setCouponCode(string|VoucherCode $voucherCode)` / `getCouponCode()`**: Applies and retrieves coupon codes, including validation.
  - **`howToUse()`**: Provides instructions for guest cart usage.

- **`Cart` Model (`app/Models/Cart.php` - *Note: This might be within the `mintreu/laravel-commerinity` package or a local override*):**
  An Eloquent model that represents a single item in a user's or guest's cart.

- **`Order` Model (`app/Models/Order.php` - *Note: This might be within the `mintreu/laravel-commerinity` package or a local override*):**
  An Eloquent model that represents a customer's order.

- **`CartLineService` (`backend/packages/mintreu/laravel-commerinity/src/Services/CartService/CartLineService.php`):**
  A dedicated service for calculating the price, tax, and discount for each individual line item in the cart. This service is crucial for accurate pricing and will be detailed further below.

- **`CartVoucherValidator` (`backend/packages/mintreu/laravel-commerinity/src/Services/CartService/CartVoucherValidator.php`):**
  A service responsible for validating voucher codes and ensuring they can be applied to the cart based on various rules (e.g., minimum order value, product eligibility, expiry).

- **`LaravelMoney`:** A library used for all monetary calculations to prevent floating-point inaccuracies.

### 🌐 Frontend Components

- **`useCart` Composable (`frontend/composables/useCart.ts`):**
  This is the central piece of the frontend cart logic. It encapsulates all the state management, API interactions, and business logic related to the cart. It provides a set of reactive properties and methods that can be used by any component in the application.
  - **`cartData`**: A reactive object that holds all the cart information, including the items, summary, and customer details.
  - **`loading`**: A boolean flag that indicates when a cart operation is in progress.
  - **`itemCount`**: A computed property that returns the total number of items in the cart.
  - **`items`**: A computed property that returns the array of items in the cart.
  - **`summary`**: A computed property that returns the cart summary, including subtotal, tax, discount, and total.
  - **`fetchCart()`**: Fetches the current cart data from the backend.
  - **`addToCart(sku, quantity)`**: Adds a product to the cart.
  - **`updateCartItem(sku, quantity)`**: Updates a product's quantity.
  - **`removeItem(sku)`**: Removes a product.
  - **`applyCoupon(code)`**: Applies a coupon code.
  - **`clearCart()`**: Clears the cart.
  - **`mergeGuestCart()`**: Merges guest cart on login.
  - **`ensureGuestCredentials()`**: Ensures guest credentials are valid or generates new ones.
  - **`validateGuestCredential()`**: Validates existing guest credentials.

- **`AddToCartButton.vue` (`frontend/components/cart/AddToCartButton.vue`):**
  A reusable component that allows users to add items to the cart. It handles loading states, error feedback, and communicates with the `useCart` composable.

- **`BuyNowButton.vue` (`frontend/components/cart/BuyNowButton.vue`):**
  A component that adds an item to the cart and immediately redirects the user to the checkout page. It uses `updateCartItem` from `useCart`.

- **`CartCounter.vue` (`frontend/components/cart/CartCounter.vue`):**
  A component that displays the number of items in the cart. It's typically placed in the main header of the application and updates in real-time using `itemCount` from `useCart`. It also calls `fetchCart()` on mount.

- **`GuestCartForm.vue` (`frontend/components/cart/GuestCartForm.vue`):**
  A form used to collect shipping and contact information from guest users during the checkout process. While it doesn't directly use `useCart`, it provides the necessary data for the order placement.

## 📊 Database Schema

### `carts` table

- `id`: Primary key.
- `ownerable_id`, `ownerable_type`: Polymorphic relationship to the owner of the cart (usually a `User` model).
- `cartable_id`, `cartable_type`: Polymorphic relationship to the item in the cart (e.g., a `Product` model).
- `quantity`: The number of units of the item.
- `is_guest`: A boolean flag to indicate if the cart belongs to a guest.
- `guest_id`: A unique identifier for the guest user.
- `guest_token`: A secure token to authenticate the guest user.

### `orders` table

- `id`: Primary key.
- `uuid`: A unique identifier for the order.
- `user_id`: Foreign key to the `users` table (nullable for guest orders).
- `guest_id`: The guest ID for guest orders.
- `total`: The total amount of the order.
- `status`: The status of the order (e.g., pending, processing, shipped, delivered).
- `customer_name`, `customer_email`, `customer_mobile`: Customer details for guest orders.
- `address_1`, `landmark`, `state`, `city`, `district`, `postal_code`, `block_id`: Shipping address details.
- ... and other order-related details.

## 🔄 Workflow

### Guest User Workflow

1.  **Initial Interaction & Credential Generation:**
    - When a guest user first interacts with the cart (e.g., adds a product via `AddToCartButton.vue`), the frontend's `useCart` composable checks for existing guest credentials.
    - If none exist, it calls `ensureGuestCredentials()`, which makes a `POST` request to `/api/cart/guest-credential`.
    - The `CartService` on the backend generates a unique `guest_id` and a secure `guest_token` (with a configurable TTL, e.g., 15 days).
    - The frontend receives these credentials and stores them in cookies (`guest_id`, `guest_token`, `guest_token_expires`).
2.  **Authenticated Cart Requests:**
    - For all subsequent cart operations (add, update, remove, fetch, apply coupon), the frontend sends the `guest_id` and `guest_token` in the request headers (`X-Guest-ID` and `X-Guest-Token`).
    - The `CartService` on the backend validates these credentials using `validateGuestToken()` (part of `HasGuestCartSupport` trait) on every request to ensure the integrity and authenticity of the guest cart.
3.  **Checkout Process:**
    - When the guest user is ready to checkout, they navigate to the checkout page.
    - The `GuestCartForm.vue` component is displayed, prompting the user to enter their shipping and contact information.
    - The frontend collects this data along with the current cart contents (from `useCart`'s `cartData`).
4.  **Order Placement:**
    - The frontend sends a `POST` request to the `/api/order/place` endpoint. This request includes the `guest_id`, `guest_token`, cart item details, and the collected customer/shipping information.
    - The `OrderController` on the backend processes this request:
        - It retrieves the guest cart items.
        - It creates a new entry in the `orders` table, populating it with the guest's details and the cart contents.
        - The `user_id` column in the `orders` table will be `NULL` for guest orders.
        - The `guest_id` from the request is stored to link the order to the guest session.
        - The cart items are typically moved or copied to an `order_items` table and then cleared from the `carts` table for that `guest_id`.
5.  **Cart Merging (Post-Login/Registration):**
    - If the guest user decides to log in or register *after* having items in their cart, the frontend calls the `POST /api/cart/merge` endpoint.
    - The `CartService` then merges the items from the guest cart (identified by `guest_id` and `guest_token`) into the newly authenticated user's permanent cart.
    - After a successful merge, the guest credentials are cleared from the frontend cookies.

### Authenticated User Workflow

1.  **Cart Management:**
    - For authenticated users, the cart is directly associated with their `user_id`.
    - All cart operations (add, update, remove, fetch, apply coupon) are performed in the context of that user. The `useCart` composable automatically includes the user's authentication token (e.g., Sanctum token) in API requests.
2.  **Checkout Process:**
    - The authenticated user proceeds to the checkout page.
    - Their saved shipping and payment information is typically pre-filled from their user profile.
    - The frontend collects any additional necessary information.
3.  **Order Placement:**
    - The frontend sends a `POST` request to the `/api/order/place` endpoint, including the user's authentication token and the cart item details.
    - The `OrderController` on the backend processes this request:
        - It retrieves the authenticated user's cart items.
        - It creates a new entry in the `orders` table, associating it with the user's `user_id`.
        - The cart items are typically moved or copied to an `order_items` table and then cleared from the `carts` table for that `user_id`.

## 🧮 Calculation Logic

The cart's total calculation is a multi-step process, primarily handled by the `CartService` and `CartLineService` on the backend.

1.  **Item Retrieval:** The `CartService::items()` method retrieves all `Cart` models associated with the current user (authenticated or guest).
2.  **Eager Loading:** In `Cart::getMeta()`, related models like `cartable` (the product itself), `media` (product images), `cheapestTier` (for tiered pricing), and `sales` (for active promotions) are eager-loaded to prevent N+1 query issues.
3.  **Line Item Calculation (`CartLineService`):**
    - For each `Cart` item, a `CartLineService` instance is created.
    - This service calculates:
        - **Base Price:** The product's individual price.
        - **Quantity Price:** Base Price * Quantity.
        - **Discounts:** Applies any product-specific discounts or sales.
        - **Coupon Discount:** If a valid coupon is applied to the cart, the `CartVoucherValidator` determines how the coupon affects this specific line item (e.g., percentage off, fixed amount off, buy-one-get-one).
        - **Tax:** Calculates tax based on the product's tax class and the customer's location.
        - **Subtotal:** Quantity Price - Discounts - Coupon Discount.
        - **Total:** Subtotal + Tax.
    - All monetary calculations use `LaravelMoney` to ensure precision.
4.  **Cart Summary Calculation (`Cart::getSummaryMeta`):**
    - After all individual line items have been processed by `CartLineService`, the `Cart::getSummaryMeta` method aggregates these values.
    - It sums up the `sub_total`, `tax`, `discount`, and `total` from each `CartLineService` instance to provide the overall cart summary.
    - It also includes information about the applied coupon (if any) and the total quantity of items.

## 🚀 Future Enhancements

- **Multiple Carts:** Allow users to maintain multiple named carts (e.g., "Wishlist," "Christmas Shopping").
- **Saved Carts:** Enable users to save their carts for later and retrieve them.
- **Cart Sharing:** Allow users to share their carts with others via a unique link.
- **Order Tracking:** Provide users with real-time updates on their order status.
- **Multiple Payment Gateways:** Integrate with various payment providers to offer more payment options.
- **Visual Enhancements:** Further improve the frontend with more interactive elements, animations, and a richer user experience.
