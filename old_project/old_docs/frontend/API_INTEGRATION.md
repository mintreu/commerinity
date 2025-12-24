# API Integration Guide

This document provides a comprehensive guide to integrating with the Commerinity API.

## Authentication

Authentication is handled via Sanctum. To authenticate, you must first obtain a token by sending a `POST` request to `/api/tokens/create` with the user's email and password. This token must then be included in the `Authorization` header of all subsequent requests as a Bearer token.

Example: `Authorization: Bearer <token>`

### Endpoints

#### `POST /api/register`

Registers a new user.

**Request Body:**

```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "mobile": "1234567890",
  "gender": "male",
  "dob": "1990-01-01",
  "password": "password",
  "type": "email",
  "referral": "optional-referral-code",
  "otp": "optional-otp"
}
```

**Response (200 OK):**

```json
{
  "status": "success",
  "message": "Registration complete",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "mobile": "1234567890",
    "gender": "male",
    "dob": "1990-01-01",
    "referral_code": "ABCDEFG",
    "parent_id": null,
    "created_at": "2023-10-27T10:00:00.000000Z",
    "updated_at": "2023-10-27T10:00:00.000000Z"
  }
}
```

#### `POST /api/login`

Logs a user in and returns a user object.

**Request Body:**

```json
{
  "email": "john.doe@example.com",
  "password": "password",
  "remember": true
}
```

**Response (200 OK):**

```json
{
  "message": "Login successful",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "mobile": "1234567890",
    "gender": "male",
    "dob": "1990-01-01",
    "referral_code": "ABCDEFG",
    "parent_id": null,
    "created_at": "2023-10-27T10:00:00.000000Z",
    "updated_at": "2023-10-27T10:00:00.000000Z"
  }
}
```

#### `POST /api/logout`

Logs a user out.

**Response (200 OK):**

```json
{
  "message": "Logout successful"
}
```

#### `POST /api/tokens/create`

Creates an API token for the user.

**Request Body:**

```json
{
  "email": "john.doe@example.com",
  "password": "password"
}
```

**Response (200 OK):**

```json
{
  "token": "your-api-token"
}
```

#### `POST /api/tokens/delete`

Deletes the current API token.

**Response (204 No Content)**

#### `POST /api/auth/has_contact`

Checks if a user exists with the given email or mobile number.

**Request Body:**

```json
{
  "type": "email",
  "value": "john.doe@example.com"
}
```

**Response (200 OK):**

```json
{
  "data": {
    "exists": true
  }
}
```

#### `POST /api/auth/send-otp`

Sends a One-Time Password (OTP) to the user's email or mobile.

**Request Body:**

```json
{
  "type": "email",
  "value": "john.doe@example.com"
}
```

**Response (200 OK):**

```json
{
  "status": "success",
  "message": "OTP sent successfully",
  "note": "Otp will be expire after 5 minutes from now"
}
```

#### `POST /api/auth/verify-otp`

Verifies a One-Time Password (OTP).

**Request Body:**

```json
{
  "type": "email",
  "value": "john.doe@example.com",
  "otp": "123456"
}
```

**Response (200 OK):**

```json
{
  "data": {
    "valid": true,
    "message": "OTP verified successfully"
  }
}
```

#### `POST /api/reset_password`

Resets the user's password.

**Request Body:**

```json
{
  "email": "john.doe@example.com"
}
```

**Response (200 OK):**

```json
{
  "message": "Password reset email sent."
}
```
## Account

All endpoints in this section require authentication.

### Endpoints

#### `GET /api/account`

Retrieves the authenticated user's account information.

**Response (200 OK):**

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "mobile": "1234567890",
    "gender": "male",
    "dob": "1990-01-01",
    "referral_code": "ABCDEFG",
    "parent_id": null,
    "created_at": "2023-10-27T10:00:00.000000Z",
    "updated_at": "2023-10-27T10:00:00.000000Z"
  }
}
```

#### `GET /api/account/profile`

Retrieves the authenticated user's profile information.

**Response (200 OK):**

```json
{
  "data": {
    "name": "John Doe",
    "gender": "male",
    "dob": "1990-01-01",
    "bio": "A short bio about the user."
  }
}
```

#### `PUT /api/account/profile`

Updates the authenticated user's profile.

**Request Body:**

```json
{
  "name": "John Doe",
  "gender": "male",
  "dob": "1990-01-01",
  "bio": "An updated bio."
}
```

**Response (200 OK):**

```json
{
  "message": "Profile updated successfully"
}
```

#### `PUT /api/account/contact`

Updates the authenticated user's email or mobile number.

**Request Body:**

```json
{
  "type": "email",
  "email": "new.email@example.com",
  "otp": "123456"
}
```

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Email updated successfully.",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "new.email@example.com",
    "mobile": "1234567890",
    "gender": "male",
    "dob": "1990-01-01",
    "referral_code": "ABCDEFG",
    "parent_id": null,
    "created_at": "2023-10-27T10:00:00.000000Z",
    "updated_at": "2023-10-27T10:00:00.000000Z"
  }
}
```

#### `PUT /api/account/avatar`

Updates the authenticated user's avatar.

**Request Body:**

This endpoint expects a `multipart/form-data` request with an `avatar` field containing the image file.

**Response (200 OK):**

```json
{
  "message": "Avatar updated successfully",
  "avatar": "https://example.com/path/to/new_avatar.jpg"
}
```

#### `PUT /api/account/password`

Updates the authenticated user's password.

**Request Body:**

```json
{
  "current_password": "old_password",
  "password": "new_password",
  "password_confirmation": "new_password"
}
```

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Password updated successfully.",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "mobile": "1234567890",
    "gender": "male",
    "dob": "1990-01-01",
    "referral_code": "ABCDEFG",
    "parent_id": null,
    "created_at": "2023-10-27T10:00:00.000000Z",
    "updated_at": "2023-10-27T10:00:00.000000Z"
  }
}
```

#### `POST /api/account/export-data`

Initiates a job to export the user's data and send it to their email.

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Data export initiated successfully. You will receive an email with your data within 5-10 minutes."
}
```



## Cart



The cart endpoints allow for managing a shopping cart for both authenticated and guest users. For guest users, a guest ID and token must be provided in the headers (`x-guest-id` and `x-guest-token`).



### Endpoints



#### `POST /api/cart/guest-credential`



Ensures a guest cart credential exists and returns it.



**Response (200 OK):**



```json

{

    "data": {

        "id": "some-guest-id",

        "token": "some-guest-token"

    }

}

```



#### `POST /api/cart/validate/guest-credential`



Validates a guest cart credential.



**Request Headers:**



```

x-guest-id: some-guest-id

x-guest-token: some-guest-token

```



**Response (200 OK):**



```json

{

  "data": {

    "status": true,

    "error": "validate"

  }

}

```



#### `GET /api/cart`



Retrieves the current cart contents.



**Response (200 OK):**



A `CartResource` object. The exact structure depends on the `CartResource` implementation, but it will generally look like this:



```json

{

  "data": {

    "items": [

      {

        "product_sku": "SKU123",

        "quantity": 2,

        "price": 10.00,

        "total": 20.00

      }

    ],

    "subtotal": 20.00,

    "discount": 0.00,

    "total": 20.00

  },

  "suggestions": []

}

```



#### `POST /api/cart/add/{product:sku}`



Adds a product to the cart.



**Request Body:**



```json

{

  "quantity": 1

}

```



**Response (200 OK):**



A `CartResource` object representing the updated cart.



#### `POST /api/cart/update/{product:sku}`



Updates the quantity of a product in the cart.



**Request Body:**



```json

{

  "quantity": 3

}

```



**Response (200 OK):**



A `CartResource` object representing the updated cart.



#### `DELETE /api/cart/remove/{product:sku}`



Removes a product from the cart.



**Response (200 OK):**



A `CartResource` object representing the updated cart.



#### `POST /api/cart/clear`



Clears all items from the cart.



**Response (200 OK):**



```json

{

  "message": "User cart cleared"

}

```



#### `POST /api/cart/merge`



Merges a guest cart into an authenticated user's cart upon login.



**Response (200 OK):**



```json

{

  "message": "Guest cart merged successfully"

}

```



## Categories

The categories endpoints provide access to the product category hierarchy.

### Endpoints

#### `GET /api/categories`

Retrieves a tree of all visible categories.

**Response (200 OK):**

A collection of `CategoryIndexResource` objects.

```json
[
  {
    "id": 1,
    "name": "Electronics",
    "url": "electronics",
    "image": "https://example.com/path/to/image.jpg",
    "children": [
      {
        "id": 2,
        "name": "Phones",
        "url": "phones",
        "image": "https://example.com/path/to/image.jpg",
        "children": []
      }
    ]
  }
]
```

#### `GET /api/categories/with-products`

Retrieves parent categories along with their child categories and the starting price of products within those child categories.

**Response (200 OK):**

```json
[
  {
    "url": "electronics",
    "name": "Electronics",
    "thumbnail": "https://example.com/path/to/image.jpg",
    "children": [
      {
        "name": "Smartphones",
        "url": "smartphones",
        "image": "https://example.com/path/to/image.jpg",
        "starting_from_price": "₹10,000.00"
      }
    ]
  }
]
```

## Products

The products endpoints provide access to the product catalog.

### Endpoints

#### `GET /api/products`

Retrieves a paginated list of products. This endpoint supports filtering, sorting, and searching.

**Query Parameters:**

*   `categories[]` (array): Filter by category URLs.
*   `price[min]` (integer): Minimum price.
*   `price[max]` (integer): Maximum price.
*   `search` (string): Search term for product name, SKU, description, etc.
*   `in_stock` (boolean): Filter for products that are in stock.
*   `offer` (boolean): Filter for products that have an active sale.
*   `min_rating` (float): Minimum average rating.
*   `vendor[]` (array): Filter by vendor IDs.
*   `filters[FilterName][]` (array): Filter by product filter options.
*   `sort[column]` (string): Sort by a specific column (e.g., `popularity`, `latest`, `pricelow2high`, `pricehigh2low`, `rating`, `name`).
*   `page` (integer): The page number for pagination.

**Response (200 OK):**

A paginated collection of `ProductIndexResource` objects.

```json
{
  "data": [
    {
      "id": 1,
      "name": "Example Product",
      "url": "example-product",
      "sku": "SKU123",
      "price": "₹1,000.00",
      "image": "https://example.com/path/to/image.jpg",
      "is_wishlisted": false,
      "has_stock": true,
      "sale_price": "₹900.00"
    }
  ],
  "links": {
    "first": "http://localhost/api/products?page=1",
    "last": "http://localhost/api/products?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost/api/products",
    "per_page": 12,
    "to": 1,
    "total": 1
  }
}
```

#### `GET /api/products/filters/get`

Retrieves the available filter options for a given category.

**Query Parameters:**

*   `category` (string): The URL of the category.

**Response (200 OK):**

```json
{
  "Color": {
    "Red": "Red",
    "Blue": "Blue"
  },
  "Size": {
    "S": "S",
    "M": "M",
    "L": "L"
  }
}
```

#### `GET /api/products/sorts/get`

Retrieves the available sorting options.

**Response (200 OK):**

```json
[
  {
    "name": "popularity",
    "value": "view_count",
    "direction": "desc"
  },
  {
    "name": "latest",
    "value": "created_at",
    "direction": "desc"
  },
  {
    "name": "pricelow2high",
    "value": "price",
    "direction": "asc"
  },
  {
    "name": "pricehigh2low",
    "value": "price",
    "direction": "desc"
  }
]
```

#### `GET /api/products/bestSaleProducts`

Retrieves a list of the best-selling products.

**Response (200 OK):**

A collection of `ProductIndexResource` objects.

#### `GET /api/products/trendingProducts`

Retrieves a list of trending products.

**Response (200 OK):**

A collection of `ProductIndexResource` objects.

## Wishlist

The wishlist endpoints allow authenticated users to manage their product wishlist.

### Endpoints

#### `POST /api/product/wishlist/{product:url}`

Adds a product to the authenticated user's wishlist.

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Example Product successfully added in your wishlist"
}
```

#### `DELETE /api/product/wishlist/{product:url}`

Removes a product from the authenticated user's wishlist.

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Example Product successfully removed from in your wishlist"
}
```

## Engagements (Reviews)

The engagements endpoints allow authenticated users to review products.

### Endpoints

#### `GET /api/product/engagements/{product:url}`

Retrieves a paginated list of reviews for a product.

**Query Parameters:**

*   `per_page` (integer): The number of reviews to return per page.
*   `page` (integer): The page number for pagination.

**Response (200 OK):**

A paginated collection of `ProductEngagementResource` objects.

```json
{
  "data": [
    {
      "id": 1,
      "review": "This is a great product!",
      "rating": 5,
      "author": {
        "name": "John Doe",
        "avatar": "https://example.com/path/to/avatar.jpg"
      },
      "created_at": "2023-10-27T10:00:00.000000Z"
    }
  ],
  "links": {
    "first": "http://localhost/api/product/engagements/example-product?page=1",
    "last": "http://localhost/api/product/engagements/example-product?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost/api/product/engagements/example-product",
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```

#### `POST /api/product/engagement/{product:url}`

Adds a review to a product.

**Request Body:**

```json
{
  "review": "This is a great product!",
  "rating": 5
}
```

**Response (200 OK):**

A `ProductEngagementResource` object representing the new review.

#### `PUT /api/product/engagement/{product_engagement}`

Updates an existing review.

**Request Body:**

```json
{
  "review": "This is an updated review.",
  "rating": 4
}
```

**Response (200 OK):**

A `ProductEngagementResource` object representing the updated review.

#### `DELETE /api/product/engagement/{product_engagement}`

Deletes a review.

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Review deleted successfully!"
}
```

## Flash Deals

The flash deals endpoints provide access to current flash deals and related information.

### Endpoints

#### `GET /api/flash-deals`

Retrieves a paginated list of active flash deals.

**Response (200 OK):**

A paginated collection of `SaleResource` objects.

```json
{
  "data": [
    {
      "id": 1,
      "name": "Flash Sale",
      "description": "A limited time flash sale.",
      "starts_from": "2023-10-27T00:00:00.000000Z",
      "ends_till": "2023-10-28T00:00:00.000000Z",
      "products": [
        {
          "id": 1,
          "name": "Example Product",
          "url": "example-product",
          "sku": "SKU123",
          "price": "₹1,000.00",
          "sale_price": "₹900.00",
          "image": "https://example.com/path/to/image.jpg"
        }
      ]
    }
  ],
  "links": {
    "first": "http://localhost/api/flash-deals?page=1",
    "last": "http://localhost/api/flash-deals?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost/api/flash-deals",
    "per_page": 30,
    "to": 1,
    "total": 1
  }
}
```

#### `GET /api/flash-deals/stats`

Retrieves statistics about the current flash deals.

**Response (200 OK):**

```json
{
  "total_deals": 10,
  "avg_discount": 25,
  "customers_saved": 125
}
```

## Orders

The orders endpoints allow for placing and managing orders.

### Endpoints

#### `POST /api/order/place`

Places a new order.

**Request Body:**

The request body for this endpoint is complex and depends on whether the user is authenticated or a guest. See the `PlaceOrderRequest` for full details.

**Response (200 OK):**

```json
{
  "data": {
    "success": true,
    "checkout_url": "https://example.com/checkout/123",
    "message": null
  }
}
```

#### `GET /api/order/insight`

Retrieves order trend data for the authenticated user.

**Query Parameters:**

*   `range` (string): The date range for the trend data (e.g., `today`, `week`, `month`, `year`).
*   `metric` (string): The metric to retrieve (e.g., `count`, `revenue`).
*   `status` (string|array): Filter by order status.

**Response (200 OK):**

```json
{
  "data": {
    "datasets": [
      {
        "label": "Orders",
        "data": [1, 2, 3]
      }
    ],
    "labels": ["Jan", "Feb", "Mar"],
    "meta": {
      "range": "year",
      "metric": "count",
      "status": [],
      "interval": "perMonth",
      "start": "2023-01-01T00:00:00.000000Z",
      "end": "2023-12-31T23:59:59.999999Z"
    }
  }
}
```

#### `GET /api/orders`

Retrieves a paginated list of orders for the authenticated user.

**Query Parameters:**

*   `status` (string): Filter by order status.
*   `from_date` (date): Filter by a start date.
*   `to_date` (date): Filter by an end date.
*   `page` (integer): The page number for pagination.

**Response (200 OK):**

A paginated collection of `OrderIndexResource` objects, with an additional `stats` object.

```json
{
  "data": [
    {
      "id": 1,
      "uuid": "order-uuid",
      "total": "₹1,000.00",
      "status": "completed",
      "created_at": "2023-10-27T10:00:00.000000Z"
    }
  ],
  "links": {
    "first": "http://localhost/api/orders?page=1",
    "last": "http://localhost/api/orders?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost/api/orders",
    "per_page": 10,
    "to": 1,
    "total": 1
  },
  "stats": {
    "total_orders": {
      "label": "Total Orders",
      "value": "1",
      "change": null,
      "trend": "neutral"
    },
    "pending_orders": {
      "label": "Pending Orders",
      "value": "0",
      "change": null,
      "trend": "neutral"
    },
    "confirmed_orders": {
      "label": "Confirmed Orders",
      "value": "0",
      "change": null,
      "trend": "neutral"
    },
    "completed_orders": {
      "label": "Completed Orders",
      "value": "1",
      "change": null,
      "trend": "neutral"
    }
  }
}
```

#### `GET /api/orders/{order:uuid}`

Retrieves a single order by its UUID.

**Response (200 OK):**

An `OrderResource` object.

```json
{
  "data": {
    "id": 1,
    "uuid": "order-uuid",
    "total": "₹1,000.00",
    "status": "completed",
    "created_at": "2023-10-27T10:00:00.000000Z",
    "transaction": {},
    "billing_address": {},
    "shipping_address": {},
    "products": []
  }
}
```

#### `POST /api/orders/{order:uuid}/canceled`

Cancels an order.

**Response (200 OK):**

```json
{
  "message": "Order canceled successfully."
}
```

#### `POST /api/orders/{order:uuid}/return`

Requests a return for an order.

**Response (200 OK):**

```json
{
  "message": "Return requested successfully."
}
```

#### `POST /api/orders/{order:uuid}/refund`

Requests a refund for an order.

**Response (200 OK):**

```json
{
  "message": "Refund requested successfully."
}
```

## Wallet

The wallet endpoints allow authenticated users to manage their wallet, including checking their balance, adding funds, and making transfers.

### Endpoints

#### `GET /api/wallet`

Retrieves the authenticated user's wallet information, including recent transactions and statistics.

**Response (200 OK):**

A `WalletResource` object.

```json
{
  "data": {
    "uuid": "wallet-uuid",
    "balance": "₹1,000.00",
    "points": 100,
    "transactions": [],
    "beneficiary": {},
    "stats": {}
  }
}
```

#### `POST /api/wallet/create`

Creates a new wallet for the authenticated user.

**Request Body:**

```json
{
  "pin": "123456"
}
```

**Response (200 OK):**

A `WalletResource` object representing the new wallet.

#### `GET /api/wallet/qr`

Retrieves a QR code for the authenticated user's wallet.

**Response (200 OK):**

```json
{
  "data": {
    "uuid": "wallet-uuid",
    "qr": "data:image/png;base64,..."
  }
}
```

#### `POST /api/wallet/change-pin`

Changes the PIN for the authenticated user's wallet.

**Request Body:**

```json
{
  "pin": "654321",
  "confirm_pin": "654321",
  "old_pin": "123456"
}
```

**Response (200 OK):**

```json
{
  "success": true,
  "message": "PIN changed successfully."
}
```

#### `POST /api/wallet/add-money`

Adds funds to the authenticated user's wallet.

**Request Body:**

```json
{
  "amount": 100,
  "reference": "Optional reference",
  "pin": "123456"
}
```

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Money added successfully.",
  "redirect": "https://example.com/checkout/123"
}
```

#### `POST /api/wallet/withdraw`

Initiates a withdrawal from the authenticated user's wallet to their default beneficiary.

**Request Body:**

```json
{
  "amount": 100,
  "pin": "123456"
}
```

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Withdrawal initiated."
}
```

#### `POST /api/wallet/send`

Sends money from the authenticated user's wallet to another user's wallet.

**Request Body:**

```json
{
  "amount": 100,
  "recipient_uuid": "recipient-wallet-uuid",
  "pin": "123456"
}
```

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Transfer successful."
}
```

#### `POST /api/wallet/point-conversion`

Converts loyalty points to wallet balance.

**Request Body:**

```json
{
  "points": 100,
  "pin": "123456"
}
```

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Points converted to balance successfully."
}
```

## Beneficiaries

The beneficiaries endpoints allow authenticated users to manage their beneficiary accounts for withdrawals.

### Endpoints

#### `GET /api/beneficiaries`

Retrieves a paginated list of the authenticated user's beneficiary accounts.

**Response (200 OK):**

A paginated collection of `BeneficiaryAccountResource` objects.

```json
{
  "data": [
    {
      "id": 1,
      "uuid": "beneficiary-uuid",
      "type": "savings",
      "bank_name": "Example Bank",
      "account_name": "John Doe",
      "account_number": "1234567890",
      "ifsc": "EXAM0000001",
      "default": true
    }
  ],
  "links": {
    "first": "http://localhost/api/beneficiaries?page=1",
    "last": "http://localhost/api/beneficiaries?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost/api/beneficiaries",
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```

#### `GET /api/beneficiaries/{account:uuid}`

Retrieves a single beneficiary account by its UUID.

**Response (200 OK):**

A `BeneficiaryAccountResource` object.

#### `POST /api/beneficiaries`

Adds a new beneficiary account.

**Request Body:**

```json
{
  "type": "savings",
  "bank_name": "Example Bank",
  "account_name": "John Doe",
  "account_number": "1234567890",
  "ifsc": "EXAM0000001",
  "default": true
}
```

**Response (201 Created):**

A `BeneficiaryAccountResource` object representing the new beneficiary account.

#### `PUT /api/beneficiaries/{account:uuid}`

Updates an existing beneficiary account.

**Request Body:**

```json
{
  "bank_name": "Updated Bank Name"
}
```

**Response (200 OK):**

A `BeneficiaryAccountResource` object representing the updated beneficiary account.

#### `DELETE /api/beneficiaries/{account:uuid}`

Deletes a beneficiary account.

**Response (200 OK):**

```json
{
  "data": {
    "success": true,
    "message": "Beneficiary Account Deleted!"
  }
}
```

## Geo Location

The geo location endpoints provide access to geographical data such as countries and states.

### Endpoints

#### `GET /api/geo/countries`

Retrieves a list of all active countries.

**Response (200 OK):**

A collection of `CountryIndexResource` objects.

```json
[
  {
    "id": 1,
    "name": "India",
    "iso_code_2": "IN",
    "isd_code": "+91",
    "locale": "en_IN",
    "timezone": "Asia/Kolkata",
    "currency": "INR",
    "flag": "🇮🇳",
    "exchange_rate": 1,
    "multiplier": 100,
    "is_active": true
  }
]
```

#### `GET /api/geo/country/{country:iso_code_2}`

Retrieves a single country by its ISO code, including its states.

**Response (200 OK):**

A `CountryResource` object.

```json
{
  "data": {
    "id": 1,
    "name": "India",
    "iso_code_2": "IN",
    "states": [
      {
        "id": 1,
        "name": "West Bengal",
        "code": "WB"
      }
    ]
  }
}
```

#### `GET /api/geo/states/{country:iso_code_2}`

Retrieves a list of all states for a given country.

**Response (200 OK):**

A collection of `StateResource` objects.

```json
[
  {
    "id": 1,
    "name": "West Bengal",
    "code": "WB"
  }
]
```

## HelpDesk

The helpdesk endpoints allow authenticated users to create and manage support tickets.

### Endpoints

#### `GET /api/helpdesk/topics/ticket`

Retrieves a list of all active ticket topics.

**Response (200 OK):**

A collection of `HelpdeskTopicResource` objects.

```json
[
  {
    "id": 1,
    "name": "Billing",
    "slug": "billing",
    "description": "Issues related to billing."
  }
]
```

#### `GET /api/helpdesk/topics/faq`

Retrieves a list of all active FAQ topics.

**Response (200 OK):**

A collection of `HelpdeskTopicResource` objects.

#### `GET /api/helpdesk/tickets`

Retrieves a list of all tickets for the authenticated user.

**Response (200 OK):**

A collection of `HelpdeskResource` objects.

```json
[
  {
    "id": 1,
    "title": "Billing Issue",
    "description": "I have an issue with my bill.",
    "priority": "high",
    "status": "open",
    "topic": {
      "id": 1,
      "name": "Billing",
      "slug": "billing"
    },
    "created_at": "2023-10-27T10:00:00.000000Z"
  }
]
```

#### `GET /api/helpdesk/tickets/{helpdesk:uuid}`

Retrieves a single ticket by its UUID, including its conversations.

**Response (200 OK):**

```json
{
  "ticket": {
    "id": 1,
    "title": "Billing Issue",
    "description": "I have an issue with my bill.",
    "priority": "high",
    "status": "open",
    "topic": {
      "id": 1,
      "name": "Billing",
      "slug": "billing"
    },
    "created_at": "2023-10-27T10:00:00.000000Z"
  },
  "conversations": [
    {
      "id": 1,
      "message": "This is a reply to the ticket.",
      "author": {
        "name": "Support Team",
        "avatar": "https://example.com/path/to/avatar.jpg"
      },
      "created_at": "2023-10-27T10:05:00.000000Z",
      "attachments": []
    }
  ]
}
```

#### `POST /api/helpdesk/tickets`

Creates a new support ticket.

**Request Body:**

This endpoint expects a `multipart/form-data` request with the following fields:

*   `topic_slug` (string, required): The slug of the ticket topic.
*   `title` (string, required): The title of the ticket.
*   `description` (string, required): The description of the ticket.
*   `priority` (string, required): The priority of the ticket (e.g., `low`, `medium`, `high`).
*   `screenshot` (file, optional): A screenshot of the issue.

**Response (201 Created):**

A `HelpdeskResource` object representing the new ticket.

#### `POST /api/helpdesk/tickets/{helpdesk:uuid}/reply`

Adds a reply to a support ticket.

**Request Body:**

This endpoint expects a `multipart/form-data` request with the following fields:

*   `message` (string, required): The reply message.
*   `attachments[]` (file, optional): An array of file attachments.

**Response (201 Created):**

A `HelpdeskConversationResource` object representing the new reply.

## Inquiry

The inquiry endpoints allow users to submit contact and business inquiries.

### Endpoints

#### `POST /api/contact/user`

Submits a user contact inquiry.

**Request Body:**

```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "message": "This is a contact message."
}
```

**Response (201 Created):**

```json
{
  "success": true,
  "message": "Message received",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "message": "This is a contact message.",
    "created_at": "2023-10-27T10:00:00.000000Z",
    "updated_at": "2023-10-27T10:00:00.000000Z"
  }
}
```

## Integration

The integration endpoints provide access to third-party integrations, such as payment gateways.

### Endpoints

## Lifecycle

The lifecycle endpoints provide access to the user lifecycle stages and levels.

### Endpoints

#### `GET /api/lifecycle/timeline`

Retrieves the entire lifecycle timeline, including all stages and levels.

**Response (200 OK):**

A collection of `StageResource` objects.

```json
[
  {
    "id": 1,
    "name": "Stage 1",
    "url": "stage-1",
    "levels": [
      {
        "id": 1,
        "name": "Level 1",
        "url": "level-1"
      }
    ]
  }
]
```

#### `GET /api/lifecycle/stages`

Retrieves a list of all active lifecycle stages.

**Response (200 OK):**

A collection of `StageResource` objects.

#### `GET /api/lifecycle/stage/{stage:url}`

Retrieves a single lifecycle stage by its URL, including its levels.

**Response (200 OK):**

A `StageResource` object.

#### `GET /api/lifecycle/level/{level:url}`

Retrieves a single lifecycle level by its URL.

**Response (200 OK):**

A `LevelResource` object.

## Pages

The pages endpoints provide access to dynamic page content.

### Endpoints

## Posts (Blogs)

The posts endpoints provide access to blog posts.

### Endpoints

#### `GET /api/blogs`

Retrieves a paginated list of blog posts. This endpoint supports filtering by category, author, date range, and search term.

**Query Parameters:**

*   `category` (string): Filter by category slug.
*   `author` (integer): Filter by author ID.
*   `from_date` (date): Filter by a start date.
*   `to_date` (date): Filter by an end date.
*   `search` (string): Search term for post title and content.
*   `page` (integer): The page number for pagination.

**Response (200 OK):**

A paginated collection of `PostIndexResource` objects.

```json
{
  "data": [
    {
      "id": 1,
      "name": "Example Post",
      "url": "example-post",
      "excerpt": "This is an example post.",
      "image": "https://example.com/path/to/image.jpg",
      "author": {
        "name": "John Doe"
      },
      "category": {
        "name": "General"
      },
      "created_at": "2023-10-27T10:00:00.000000Z"
    }
  ],
  "links": {
    "first": "http://localhost/api/blogs?page=1",
    "last": "http://localhost/api/blogs?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost/api/blogs",
    "per_page": 12,
    "to": 1,
    "total": 1
  }
}
```

## Push Notifications

The push notification endpoints allow for subscribing to and sending push notifications.

### Endpoints

#### `POST /api/push/subscribe`

Subscribes a user to push notifications.

**Request Body:**

```json
{
  "subscription": {
    "endpoint": "https://example.com/push-endpoint",
    "keys": {
      "p256dh": "p256dh-key",
      "auth": "auth-key"
    }
  },
  "email": "john.doe@example.com"
}
```

**Response (201 Created):**

```json
{
  "status": true,
  "message": "Push notifications enabled successfully!",
  "data": {
    "endpoint": "https://example.com/push-endpoint",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john.doe@example.com"
    }
  }
}
```

#### `POST /api/push/unsubscribe`

Unsubscribes a user from push notifications. This endpoint requires authentication.

**Request Body:**

```json
{
  "endpoint": "https://example.com/push-endpoint"
}
```

**Response (200 OK):**

```json
{
  "status": true,
  "message": "Successfully unsubscribed"
}
```

#### `POST /api/push/send-to-user`

Sends a push notification to a specific user. This endpoint requires admin privileges.

**Request Body:**

```json
{
  "user_id": 1,
  "title": "Hello!",
  "body": "This is a push notification.",
  "icon": "/icon-192x192.png",
  "url": "https://example.com"
}
```

**Response (200 OK):**

```json
{
  "status": true,
  "message": "Notification sent successfully!"
}
```

#### `POST /api/push/send-to-all`

Sends a push notification to all subscribed users. This endpoint requires admin privileges.

**Request Body:**

```json
{
  "title": "Hello!",
  "body": "This is a push notification.",
  "icon": "/icon-192x192.png",
  "url": "https://example.com"
}
```

**Response (200 OK):**

```json
{
  "status": true,
  "message": "Notification sent to 10 users!"
}
```

#### `POST /api/push/send-to-level`

Sends a push notification to all subscribed users in a specific lifecycle level. This endpoint requires admin privileges.

**Request Body:**

```json
{
  "level_id": 1,
  "title": "Hello!",
  "body": "This is a push notification.",
  "icon": "/icon-192x192.png",
  "url": "https://example.com"
}
```

**Response (200 OK):**

```json
{
  "status": true,
  "message": "Notification sent to 5 users!"
}
```

## Recruitment

The recruitment endpoints allow users to view and apply for job openings.

### Endpoints

#### `GET /api/recruitment`

Retrieves a list of all active and published job recruitments. If the user is authenticated, it will exclude jobs they have already applied for.

**Response (200 OK):**

A collection of `NaukriIndexResource` objects.

```json
[
  {
    "id": 1,
    "title": "Software Engineer",
    "url": "software-engineer",
    "location": "Remote",
    "type": "Full-time",
    "image": "https://example.com/path/to/image.jpg"
  }
]
```

#### `GET /api/recruitment/{recruitment:url}`

Retrieves a single job recruitment by its URL.

**Response (200 OK):**

A `NaukriResource` object.

```json
{
  "data": {
    "id": 1,
    "title": "Software Engineer",
    "url": "software-engineer",
    "description": "<p>Job description here.</p>",
    "location": "Remote",
    "type": "Full-time",
    "salary": "₹1,000,000 - ₹2,000,000 per year",
    "image": "https://example.com/path/to/image.jpg"
  }
}
```

## Sales

The sales endpoints provide access to sales and promotions.

### Endpoints

#### `GET /api/sales`

Retrieves a list of all active sales. If the user is authenticated, it will return sales targeted to their lifecycle level. Otherwise, it will return non-targeted sales.

**Response (200 OK):**

A collection of `SaleResource` objects.

```json
[
  {
    "id": 1,
    "name": "Summer Sale",
    "description": "A sale for the summer season.",
    "starts_from": "2023-06-01T00:00:00.000000Z",
    "ends_till": "2023-08-31T23:59:59.000000Z"
  }
]
```
