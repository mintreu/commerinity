
# 🛒 Frontend Cart Module

The frontend of the cart module is built with Vue.js and Nuxt.js, providing a reactive and seamless user experience. It leverages a powerful `useCart` composable to manage the cart state and interact with the backend API.

## Key Components

- **`useCart` Composable:** This is the central piece of the frontend cart logic. It encapsulates all the state management, API interactions, and business logic related to the cart. It provides a set of reactive properties and methods that can be used by any component in the application.
- **`AddToCartButton.vue`:** A reusable component that allows users to add items to the cart. It handles loading states, error feedback, and communicates with the `useCart` composable.
- **`BuyNowButton.vue`:** A component that adds an item to the cart and immediately redirects the user to the checkout page.
- **`CartCounter.vue`:** A component that displays the number of items in the cart. It's typically placed in the main header of the application and updates in real-time.
- **`GuestCartForm.vue`:** A form used to collect shipping and contact information from guest users during the checkout process.

## State Management

The `useCart` composable manages the entire state of the cart, including:

- `cartData`: A reactive object that holds all the cart information, including the items, summary, and customer details.
- `loading`: A boolean flag that indicates when a cart operation is in progress.
- `itemCount`: A computed property that returns the total number of items in the cart.
- `items`: A computed property that returns the array of items in the cart.
- `summary`: A computed property that returns the cart summary, including subtotal, tax, discount, and total.

## Workflow

### Adding an Item to the Cart

1.  The user clicks on the `AddToCartButton.vue` component.
2.  The component calls the `addToCart` method from the `useCart` composable, passing the product SKU and quantity.
3.  The `addToCart` method in the composable makes a `POST` request to the `/api/cart/add/{sku}` endpoint.
4.  The backend `CartService` handles the request, adding the item to the cart in the database.
5.  The `useCart` composable updates its `cartData` with the new cart information returned by the API.
6.  The `CartCounter.vue` component automatically updates to reflect the new item count.

### Guest Checkout

1.  The guest user proceeds to the checkout page.
2.  The `GuestCartForm.vue` component is displayed, allowing the user to enter their shipping and contact information.
3.  Upon form submission, the frontend sends a `POST` request to the `/api/order/place` endpoint, including the cart data and the guest's information.
4.  The backend creates the order and returns a success response.
5.  The frontend displays an order confirmation page.

## Visual Enhancements

- **Loading Spinners:** All cart-related buttons and components display loading spinners to provide visual feedback during API requests.
- **Error Toasts:** Error messages are displayed using a toast notification system, providing clear and non-intrusive feedback to the user.
- **Real-time Updates:** The cart counter and other cart components update in real-time without requiring a page reload, thanks to the reactive nature of Vue.js and the `useCart` composable.
