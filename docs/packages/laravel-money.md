# `mintreu/laravel-money` Package Documentation

## 1. Package Overview

The `mintreu/laravel-money` package is designed to provide accurate and robust handling of monetary values within Laravel applications. It integrates with established PHP money libraries (`moneyphp/money` via `akaunting/laravel-money`) to prevent common floating-point arithmetic errors that can occur when dealing with currency.

A key feature of this package is its custom Eloquent attribute cast, `LaravelMoneyCast`, which ensures that monetary values are stored as integers (representing cents or the smallest currency unit) in the database for precision, while being presented as more readable floats (representing dollars/currency units) in the application.

### Core Features

-   **Precision Monetary Handling:** Leverages `moneyphp/money` to perform all monetary calculations with accuracy, avoiding floating-point issues.
-   **Eloquent Attribute Casting:** Provides `LaravelMoneyCast` to seamlessly convert between integer storage (cents) and float representation (currency units) for model attributes.
-   **Currency Formatting:** (Assumed via underlying libraries) Offers tools for formatting monetary values according to locale and currency.

## 2. Architecture and Data Model

The package primarily consists of:

-   **`LaravelMoney` Class:** This class extends `LaravelMoneyService` (which in turn likely wraps `akaunting/laravel-money` or `moneyphp/money`). It serves as the main interface for performing monetary operations like addition, subtraction, multiplication, division, and formatting.
-   **`LaravelMoneyCast` Class:** An Eloquent custom cast that handles the conversion of monetary values when interacting with the database.
-   **`LaravelMoneyServiceProvider`:** Registers the package and its components with Laravel.

## 3. Installation

This package is a core component of the Commerinity project and is installed as a local path repository. To install it in another project, you would typically run:

```bash
composer require mintreu/laravel-money
```

## 4. Usage

### 4.1. `LaravelMoneyCast`

To use the `LaravelMoneyCast`, simply add it to the `$casts` array of your Eloquent model for the attributes that store monetary values.

```php
use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelMoney\Casts\LaravelMoneyCast;

class Product extends Model
{
    protected $casts = [
        'price' => LaravelMoneyCast::class,
        'cost' => LaravelMoneyCast::class,
    ];

    // ...
}
```

**Important Note:** As of the current analysis, there is a **critical bug** in the `LaravelMoneyCast::set()` method. It currently returns the value directly without converting it to an integer (cents) for storage. This must be fixed for the cast to function correctly and prevent floating-point errors in the database. The intended logic is commented out in the source code.

### 4.2. `LaravelMoney` Class

The `LaravelMoney` class (extending `LaravelMoneyService`) is intended for performing monetary calculations and formatting.

```php
use Mintreu\LaravelMoney\LaravelMoney;

// Create a new money instance
$amount = LaravelMoney::make(100.50); // Represents $100.50

// Perform operations
$newAmount = $amount->add(LaravelMoney::make(20.25)); // $120.75
$subtractedAmount = $newAmount->subtract(LaravelMoney::make(10)); // $110.75
$multipliedAmount = $subtractedAmount->times(2); // $221.50

// Format the amount for display
echo $multipliedAmount->formatted(); // e.g., "$221.50" (depending on locale/currency)
```

## 5. Review and Production Readiness Assessment

### Review

-   **Strengths:**
    -   **Best Practice Approach:** The package correctly adopts the best practice of using dedicated money libraries to handle monetary values, which is fundamental for financial accuracy.
    -   **Eloquent Integration:** The `LaravelMoneyCast` provides a convenient way to integrate this precision into Eloquent models, abstracting the storage and retrieval logic.

-   **Weaknesses:**
    -   **CRITICAL BUG in `LaravelMoneyCast::set()`:** This is the most significant issue. The `set` method in `src/Casts/LaravelMoneyCast.php` currently bypasses the conversion of float values to integer cents for database storage. This means monetary values will be stored as floats, reintroducing the very floating-point precision issues the package is designed to prevent. The commented-out line `return round(floatval($value) * 100);` clearly indicates the intended, but currently inactive, logic.
    -   **Lack of Documentation:** The `README.md` is a template and provides no practical information on how to use the package, its classes, or its custom cast. This makes it extremely difficult for developers to understand and correctly implement.
    -   **Missing Tests:** There is no visible test suite. For a package dealing with money, the absence of comprehensive tests for calculations and casting is a severe risk. Without tests, the accuracy and reliability of the package cannot be guaranteed.
    -   **Obscure `LaravelMoneyService`:** The `LaravelMoney` class extends `LaravelMoneyService`, but the code for `LaravelMoneyService` is not provided, making it difficult to fully assess the capabilities and usage of the main `LaravelMoney` class.

### Production Readiness: **NOT PRODUCTION-READY**

The `mintreu/laravel-money` package is **NOT PRODUCTION-READY** due to a critical bug in its core `LaravelMoneyCast::set()` method, which undermines its primary purpose of ensuring monetary precision. This, combined with the complete lack of documentation and tests, makes it a high-risk dependency for any application, especially those handling financial transactions.

## 6. Recommendations for Improvement

1.  **CRITICAL FIX: Enable Integer Conversion in `LaravelMoneyCast::set()`:**
    -   **Immediately uncomment and activate** the conversion logic in `src/Casts/LaravelMoneyCast.php` to ensure monetary values are correctly stored as integers (cents) in the database:
        ```php
        public function set(Model $model, string $key, mixed $value, array $attributes): mixed
        {
            // Transform the float into an integer for storage.
            return round(floatval($value) * 100);
        }
        ```

2.  **Create Comprehensive Documentation:**
    -   Develop a detailed `README.md` that explains:
        -   The importance of using a money library for precision.
        -   How to install and configure the package.
        -   Clear instructions and examples for using `LaravelMoneyCast` on Eloquent models.
        -   Detailed usage examples for the `LaravelMoney` class (or `LaravelMoneyService`) for performing various monetary operations (addition, subtraction, multiplication, division, comparison, formatting).
        -   Document any configuration options (e.g., default currency, locale).

3.  **Implement a Robust Test Suite:**
    -   **Unit Tests:**
        -   Write extensive unit tests for `LaravelMoneyCast` to verify correct conversion between float and integer (cents) for both `get` and `set` operations. Include tests for edge cases (zero, negative values, values with more than two decimal places).
        -   Write comprehensive unit tests for the `LaravelMoney` class (or `LaravelMoneyService`) to ensure all monetary arithmetic operations (add, subtract, multiply, divide, compare) are accurate and handle different currencies correctly.
    -   **Integration Tests:**
        -   Test the `LaravelMoneyCast` with an actual Eloquent model, saving and retrieving values to confirm that data integrity and precision are maintained throughout the database interaction.

By addressing these critical issues, particularly the bug in the cast and the absence of tests, the `mintreu/laravel-money` package can become a reliable, accurate, and essential component for any e-commerce or financial application within the Commerinity platform.
