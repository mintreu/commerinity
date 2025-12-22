
# 🛒 Cart & Order Module

The cart and order module is the backbone of the e-commerce functionality, enabling users to select products, manage their selections, and proceed to checkout. It supports both authenticated users and guest users, providing a seamless experience for all.

## Key Features

- **Guest & Authenticated User Support:** The cart and checkout process is fully functional for both registered users and guests.
- **Persistent Cart:** User carts are saved in the database, while guest carts are maintained through session/token-based persistence.
- **Coupon & Voucher Support:** Easily apply discounts to the cart using voucher codes.
- **Dynamic Pricing:** The cart recalculates totals instantly when quantities change or coupons are applied.
- **Merge Carts:** Guest carts are automatically merged with a user's cart upon login.
- **Guest Checkout:** Guests can place orders without creating an account.

## Technical Architecture

The cart functionality is primarily built around the `mintreu/laravel-commerinity` package, which provides a robust and flexible `CartService`.

### Core Components

- **`CartController`:** The main entry point for all cart-related API requests. It acts as a thin layer, delegating the heavy lifting to the `CartService`.
- **`OrderController`:** Handles the order placement logic.
- **`CartService`:** The heart of the cart module. This service class handles all the business logic, including adding, updating, and removing items, as well as managing guest carts and coupons.
- **`Cart` Model:** An Eloquent model that represents a single item in a user's or guest's cart.
- **`Order` Model:** An Eloquent model that represents a customer's order.
- **`CartLineService`:** A dedicated service for calculating the price, tax, and discount for each individual line item in the cart.
- **`CartVoucherValidator`:** A service responsible for validating voucher codes and ensuring they can be applied to the cart.
- **`LaravelMoney`:** A library used for all monetary calculations to prevent floating-point inaccuracies.

### Database Schema

#### `carts` table

- `id`: Primary key.
- `ownerable_id`, `ownerable_type`: Polymorphic relationship to the owner of the cart (usually a `User` model).
- `cartable_id`, `cartable_type`: Polymorphic relationship to the item in the cart (e.g., a `Product` model).
- `quantity`: The number of units of the item.
- `is_guest`: A boolean flag to indicate if the cart belongs to a guest.
- `guest_id`: A unique identifier for the guest user.
- `guest_token`: A secure token to authenticate the guest user.

#### `orders` table

- `id`: Primary key.
- `uuid`: A unique identifier for the order.
- `user_id`: Foreign key to the `users` table (nullable for guest orders).
- `guest_id`: The guest ID for guest orders.
- `total`: The total amount of the order.
- `status`: The status of the order (e.g., pending, processing, shipped, delivered).
- ... and other order-related details.

## Workflow

### Guest User Workflow

1.  **Credential Generation:** When a guest user first interacts with the cart (e.g., adds a product), the frontend calls the `POST /api/cart/guest-credential` endpoint. The `CartService` generates a unique `guest_id` and a secure `guest_token`.
2.  **Token Storage:** The frontend receives the `guest_id` and `guest_token` and stores them in cookies.
3.  **Authenticated Requests:** For all subsequent cart operations, the frontend sends the `guest_id` and `guest_token` in the request headers (`X-Guest-ID` and `X-Guest-Token`).
4.  **Token Validation:** The `CartService` validates the guest token on every request to ensure the integrity of the guest cart.
5.  **Checkout:** When the guest user is ready to checkout, the frontend collects their shipping and payment information using the `GuestCartForm.vue` component.
6.  **Order Placement:** The frontend sends a `POST` request to the `/api/order/place` endpoint with the cart data and the guest's information. The `OrderController` then creates a new order in the `orders` table, associating it with the `guest_id`.
7.  **Cart Merging:** When the guest user logs in or registers, the frontend calls the `POST /api/cart/merge` endpoint. The `CartService` then merges the items from the guest cart into the user's permanent cart.

### Authenticated User Workflow

For authenticated users, the workflow is simpler.

1.  **Cart Management:** The user's cart is directly associated with their user account, and all cart operations are performed in the context of that user.
2.  **Checkout:** The user proceeds to the checkout page, where their saved shipping and payment information is pre-filled.
3.  **Order Placement:** The frontend sends a `POST` request to the `/api/order/place` endpoint. The `OrderController` creates a new order and associates it with the user's `user_id`.

## API Endpoints

All cart-related endpoints are prefixed with `/api/cart`.

| Method  | URI                               | Action                      |
| :------ | :-------------------------------- | :-------------------------- |
| `POST`  | `/guest-credential`               | Generate guest credentials. |
| `POST`  | `/validate/guest-credential`      | Validate guest credentials. |
| `GET`   | `/`                               | Get the cart contents.      |
| `POST`  | `/add/{product:sku}`              | Add a product to the cart.  |
| `POST`  | `/update/{product:sku}`           | Update a product's quantity.|
| `DELETE`| `/remove/{product:sku}`           | Remove a product from the cart.|
| `POST`  | `/coupon/{voucher_code}`          | Apply a coupon to the cart. |
| `POST`  | `/clear`                          | Clear the entire cart.      |
| `POST`  | `/merge`                          | Merge a guest cart.         |

All order-related endpoints are prefixed with `/api/order`.

| Method | URI | Action |
| :--- | :--- | :--- |
| `POST` | `/place` | Place an order. |

## Future Enhancements

- **Multiple Carts:** Allow users to maintain multiple named carts (e.g., "Wishlist," "Christmas Shopping").
- **Saved Carts:** Enable users to save their carts for later and retrieve them.
- **Cart Sharing:** Allow users to share their carts with others via a unique link.
- **Order Tracking:** Provide users with real-time updates on their order status.
- **Multiple Payment Gateways:** Integrate with various payment providers to offer more payment options.
