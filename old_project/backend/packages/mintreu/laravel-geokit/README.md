# `mintreu/laravel-geokit`

## 1. Package Overview

The `mintreu/laravel-geokit` package provides a robust and structured solution for managing geographical data and addresses within a Laravel application. It establishes a clear data model for countries, states, and blocks, and offers a flexible system for associating addresses with any Eloquent model.

This package is essential for any application that needs to store and manage physical addresses for users, orders, or any other entity.

### Core Features

-   **Normalized Geographical Data:** Provides separate models for `Country`, `State`, and `Block` to create a well-structured and easily manageable geographical database.
-   **Flexible Address Management:** Uses a polymorphic relationship to allow any model to have one or more associated addresses.
-   **`HasAddress` Trait:** A convenient trait that provides an easy way to add address management capabilities to any model.
-   **Address Types:** Supports different types of addresses (e.g., home, delivery, pickup) through a custom cast.
-   **Eloquent-based:** Built entirely on Laravel's Eloquent ORM, making it easy to use and extend.

## 2. Architecture and Data Model

The package is built around a few key Eloquent models:

-   **`Country`:** Stores country-level information, such as name, ISO codes, currency, and timezone.
-   **`State`:** Represents states or provinces within a country.
-   **`Block`:** A further subdivision of a state, which could represent a district, county, or other administrative area.
-   **`Address`:** The central model that represents a full physical address. It ties together the other geographical models and uses a polymorphic relationship (`addressable`) to link to any other model in your application.

**Relationship Diagram:**

```
+-------------+       +-----------+       +---------+
|   Country   |<>-----|   State   |<>-----|  Block  |
+-------------+       +-----------+       +---------+
      ^                     ^                 ^
      |                     |                 |
      |                     |                 |
+-------------------------------------------------+
|                     Address                     |
+-------------------------------------------------+
      ^
      | (addressable)
      |
+-------------+
|  Your Model |
| (e.g., User)|
+-------------+
```

## 3. Installation

This package is a core component of the Commerinity project and is installed as a local path repository. To install it in another project, you would typically run:

```bash
composer require mintreu/laravel-geokit
```

You would then publish and run the migrations:

```bash
php artisan vendor:publish --tag="laravel-geokit-migrations"
php artisan migrate
```

## 4. Usage

### The `HasAddress` Trait

The easiest way to add address functionality to a model is by using the `HasAddress` trait.

```php
use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelGeokit\Traits\HasAddress;

class User extends Model
{
    use HasAddress;

    // ...
}
```

This trait provides the following relationships:

-   **`addresses()`:** A `morphMany` relationship that allows a model to have multiple addresses.
-   **`address()`:** A `morphOne` relationship, useful for models that have a single primary address.
-   **`homeAddress()`:** A `morphOne` relationship that retrieves the address of type "home".
-   **`deliveryAddresses()`:** A `morphMany` relationship for all "delivery" type addresses.
-   **`pickupAddresses()`:** A `morphMany` relationship for all "pickup" type addresses.

**Example Usage:**

```php
$user = User::find(1);

// Create a new address for the user
$user->addresses()->create([
    'type' => 'home',
    'address_1' => '123 Main St',
    'city' => 'Anytown',
    'postal_code' => '12345',
    'state_code' => 'CA',
    'country_code' => 'US',
]);

// Get all addresses for the user
$addresses = $user->addresses;

// Get the user's home address
$homeAddress = $user->homeAddress;
```

## 5. Review

### Strengths:
-   **Clean Data Model:** The normalized data model for geographical data is a major strength, preventing data duplication and making the system easier to maintain.
-   **High Reusability:** The polymorphic relationship and `HasAddress` trait make the package extremely reusable and easy to integrate with any part of the application.
-   **Good Use of Eloquent:** The package effectively uses core Laravel features like relationships, model booting events, and custom casts.
-   **Developer-Friendly:** The convenience methods provided by the `HasAddress` trait simplify common tasks and improve the developer experience.

### Weaknesses:
-   **Missing Tests:** The lack of a visible test suite is a major risk. The correctness of the relationships and custom logic cannot be verified.
-   **No Geocoding or Distance Calculation:** The package stores coordinates but lacks built-in functionality for geocoding (address to coordinates) or distance calculations, which are common requirements for a geo-location package.

## 6. Recommendations for Improvement

1.  **Implement a Full Test Suite:**
    -   **Unit Tests:** Write unit tests for each model to verify their relationships, scopes, and any custom methods.
    -   **Feature Tests:** Create feature tests for the `HasAddress` trait to ensure it functions correctly when applied to a model. Test the creation and retrieval of addresses through this trait.

2.  **Add Key Features:**
    -   **Geocoding and Reverse Geocoding:** Integrate a third-party geocoding service (e.g., Google Maps, Mapbox) to automatically convert addresses to coordinates and vice versa. This would be a huge value-add.
    -   **Distance Calculation:** Add helper methods or a service to calculate the distance between two `Address` models using their latitude and longitude.
    -   **Address Validation:** Consider integrating an address validation service to standardize and verify user-entered addresses.

3.  **Filament Resources:**
    -   Create Filament resources for the `Country`, `State`, and `Block` models to allow administrators to easily manage the geographical data.