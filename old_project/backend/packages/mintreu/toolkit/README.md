# `mintreu/toolkit`

## 1. Package Overview

The `mintreu/toolkit` package serves as a foundational collection of reusable classes, traits, and utilities designed to enhance and streamline common development tasks within Laravel projects, particularly within the Mintreu ecosystem. It aims to promote code consistency, enforce best practices, and simplify repetitive coding patterns.

### Core Features

-   **Publishable Status Management:** Provides an Enum for standardized handling of content publishing statuses, with built-in Filament UI integration.
-   **Dynamic Model Factory Resolution:** Simplifies the creation and discovery of Eloquent model factories within packages.
-   **Unique Identifier Generation:** Offers a comprehensive suite of methods for generating various types of unique codes (alphanumeric, ULID, UUID, initials-based) for model attributes.
-   **UUID Trait:** (Assumed) Provides functionality for models that utilize UUIDs as primary or secondary keys.
-   **PDF Generation:** (Implied by `barryvdh/laravel-dompdf` dependency) Likely includes utilities or integrations for generating PDF documents.

## 2. Architecture and Key Components

The toolkit is primarily composed of traits and an Enum, designed to be easily integrated into Eloquent models and other parts of a Laravel application.

### `PublishableStatusCast` Enum

-   **Location:** `src/Casts/PublishableStatusCast.php`
-   **Description:** A PHP Enum that defines a set of common publishing statuses for content or records (e.g., `DRAFT`, `PENDING`, `PUBLISHED`, `REJECTED`, `ARCHIVED`).
-   **Filament Integration:** Implements `Filament\Support\Contracts\HasColor`, `HasIcon`, and `HasLabel` interfaces, allowing for direct use in Filament admin panels to display statuses with appropriate colors, icons, and human-readable labels.

### `HasPackageModelFactory` Trait

-   **Location:** `src/Traits/HasPackageModelFactory.php`
-   **Description:** This trait is designed to be used on Eloquent models within a Laravel package. It overrides the default `newFactory()` method to dynamically locate and instantiate the model's factory class based on a conventional naming and directory structure (e.g., `Package\Database\Factories\ModelNameFactory`). This simplifies factory setup for package development.

### `HasUnique` Trait

-   **Location:** `src/Traits/HasUnique.php`
-   **Description:** Provides a collection of methods to generate and assign unique identifiers to model attributes. It ensures that the generated code does not already exist in the database for the specified column.
-   **Methods:**
    -   `setUniqueCode(string $column_name, int $length = 8, ?string $prefix = null, ?string $suffix = null)`: Generates a unique alphanumeric string.
    -   `setUniqueCodeUpper(...)`: Same as `setUniqueCode`, but converts the result to uppercase.
    -   `refreshUniqueCode(string $column_name = 'uuid', int $length = 16, ?string $prefix = null, ?string $suffix = null)`: Generates a unique code based on the current timestamp and random characters, then saves the model.
    -   `setUniqueUlid(string $column_name, int $length = 26)`: Generates a unique ULID (Universally Unique Lexicographically Sortable Identifier).
    -   `setUniqueUlidUpper(...)`: Same as `setUniqueUlid`, but converts the result to uppercase.
    -   `setUniqueUuid(string $column_name, int $length = 36)`: Generates a unique UUID (Universally Unique Identifier).
    -   `setUniqueUuidUpper(...)`: Same as `setUniqueUuid`, but converts the result to uppercase.
    -   `setUniqueInitialsCode(string $column_name, int $length = 6)`: Generates a unique code based on the model's `name` attribute initials and a random string.
    -   `setUniqueInitialsCodeUpper(...)`: Same as `setUniqueInitialsCode`, but converts the result to uppercase.

### `HasUuid` Trait

-   **Location:** (Not provided in snippets, but typically found in `src/Traits/HasUuid.php`)
-   **Description:** (Assumed) This trait would typically handle the automatic generation and assignment of UUIDs to a model's primary key or a designated UUID column upon creation.

## 3. Installation

This package is a core component of the Commerinity project and is installed as a local path repository. To install it in another project, you would typically run:

```bash
composer require mintreu/toolkit
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="toolkit-config"
```

## 4. Usage

### 4.1. Using `PublishableStatusCast`

To use the `PublishableStatusCast` enum, simply cast an attribute in your Eloquent model:

```php
use Illuminate\Database\Eloquent\Model;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class Article extends Model
{
    protected $casts = [
        'status' => PublishableStatusCast::class,
    ];

    // ...
}
```

You can then access its properties:

```php
$article = Article::find(1);
echo $article->status->getLabel(); // e.g., "Published"
echo $article->status->getColor(); // e.g., "success"
echo $article->status->getIcon();  // e.g., "heroicon-m-check"
```

### 4.2. Using `HasPackageModelFactory`

Apply this trait to your package's Eloquent models to enable dynamic factory resolution:

```php
use Illuminate\Database\Eloquent\Model;
use Mintreu\Toolkit\Traits\HasPackageModelFactory;

class MyPackageModel extends Model
{
    use HasPackageModelFactory;

    // ...
}
```

Ensure your factory is located at `PackageNamespace\Database\Factories\MyPackageModelFactory`.

### 4.3. Using `HasUnique`

Apply this trait to any Eloquent model where you need to generate unique codes:

```php
use Illuminate\Database\Eloquent\Model;
use Mintreu\Toolkit\Traits\HasUnique;

class Order extends Model
{
    use HasUnique;

    protected $fillable = ['order_code', 'uuid'];

    // ...

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->setUniqueCode('order_code', 10, 'ORD-');
            $model->setUniqueUuid('uuid');
        });
    }
}
```

## 5. Review

### Strengths:
-   **High Reusability:** The package provides highly reusable components (traits, enums) that address common cross-project needs in Laravel development.
-   **Promotes Consistency:** `PublishableStatusCast` ensures a standardized approach to content statuses, especially beneficial in Filament-based admin panels.
-   **Filament-Ready:** Direct implementation of Filament contracts in `PublishableStatusCast` makes it immediately usable and visually consistent within Filament.
-   **Developer Convenience:** `HasPackageModelFactory` simplifies package development workflows, and `HasUnique` offers a powerful set of tools for generating various unique identifiers.
-   **Robust Unique ID Generation:** The `HasUnique` trait covers multiple scenarios for generating unique codes (random, ULID, UUID, initials-based).

### Weaknesses:
-   **Missing Automated Tests:** There is no visible test suite for the traits and casts. For a toolkit designed for reusability and data integrity, comprehensive tests are absolutely critical to ensure reliability and prevent regressions.
-   **Empty `Toolkit` Class:** The `src/Toolkit.php` class is currently empty. If it's intended to be a facade or a container for static helper methods, its purpose is unclear and unimplemented.
-   **`HasUnique` Column Validation:** The `validateColumnName` method in `HasUnique` checks if the column is in `$fillable`. This can be overly restrictive if a unique code needs to be set on a guarded attribute that is managed internally by the model.
-   **`HasUnique::refreshUniqueCode` Potential for Collision:** While `Str::random` is used, relying on `now()->format('YmdHisv')` as a primary base for uniqueness, especially with `substr` trimming, might not guarantee absolute uniqueness under extremely high-concurrency scenarios.

## 6. Recommendations for Improvement

1.  **Implement a Robust Test Suite:**
    -   **Unit Tests:**
        -   For `PublishableStatusCast`: Verify correct `getLabel()`, `getColor()`, and `getIcon()` outputs for all enum cases.
        -   For `HasPackageModelFactory`: Test the dynamic factory resolution mechanism to ensure it correctly finds and instantiates factories.
        -   For `HasUnique`: Write extensive unit tests for all unique code generation methods (`setUniqueCode`, `setUniqueUlid`, `setUniqueUuid`, `setUniqueInitialsCode`, and their `Upper` variants). Test for uniqueness guarantees, correct length, prefix/suffix application, and edge cases (e.g., empty input for initials).
    -   **Integration Tests:** Apply the `HasUnique` trait to dummy Eloquent models and perform database operations to verify that unique codes are correctly saved and that uniqueness constraints are respected.

2.  **Refine `Toolkit` Class:**
    -   If the `src/Toolkit.php` class is intended to serve a specific purpose (e.g., a facade for static helper methods, a container for global configurations), implement that functionality. Otherwise, consider removing it to avoid confusion.

3.  **Enhance `HasUnique` Trait:**
    -   **Column Validation:** Modify the `validateColumnName` method to check if the column exists on the model's table (e.g., using `Schema::hasColumn`) rather than relying solely on `$fillable`. This provides more flexibility for guarded attributes.
    -   **`refreshUniqueCode` Robustness:** For scenarios requiring extremely high uniqueness guarantees, consider making `refreshUniqueCode` rely more heavily on cryptographically secure random strings or a full UUID/ULID generation for the core unique part, rather than a timestamp-based approach that might be less unique under rapid calls.