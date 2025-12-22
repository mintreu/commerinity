# `mintreu/laravel-category` Package Documentation

## 1. Package Overview

The `mintreu/laravel-category` package provides a comprehensive and flexible categorization system for Laravel applications. It allows any Eloquent model to be associated with one or more categories, supporting both simple and hierarchical (tree-like) category structures.

The package is designed to be highly configurable and extensible, leveraging polymorphic relationships to enable categorization of various types of content, such as products, blog posts, or any other model in your application.

### Core Features

-   **Polymorphic Many-to-Many Relationships:** Allows any model to be associated with multiple categories.
-   **Hierarchical Categories:** Supports nested categories (parent-child relationships) using an adjacency list pattern.
-   **Media Integration:** Built-in support for attaching display and banner images to categories using `spatie/laravel-medialibrary`.
-   **Filament Integration:** Designed to work seamlessly with Filament admin panel, including a tree-like category selector for a better user experience.
-   **Dynamic Relationship Loading:** Provides "magic" methods to load categorized models directly from a category instance (e.g., `$category->products`).

## 2. Installation and Configuration

### Installation

1.  Install the package via Composer:
    ```bash
    composer require mintreu/laravel-category
    ```

2.  Publish and run the migrations:
    ```bash
    php artisan vendor:publish --tag="laravel-category-migrations"
    php artisan migrate
    ```

3.  Publish the configuration file:
    ```bash
    php artisan vendor:publish --tag="laravel-category-config"
    ```
    This will create a `config/laravel-category.php` file.

### Configuration

The `config/laravel-category.php` file allows you to define which models can be categorized.

```php
// config/laravel-category.php
return [
    'categorized' => [
        'models' => [
            \App\Models\Product::class,
            \App\Models\Post::class,
        ],
    ],
];
```

## 3. Usage

### 3.1. The `Category` Model

The core of the package is the `Mintreu\LaravelCategory\Models\Category` model. It has the following key attributes:

-   `name` (string): The name of the category.
-   `url` (string): A unique, URL-friendly slug for the category.
-   `status` (boolean): Whether the category is active or not.
-   `is_visible_on_front` (boolean): Controls visibility on the frontend.
-   `parent_id` (integer): The ID of the parent category for hierarchical structures.
-   `desc` (text): A description of the category.
-   `meta_data` (array): For storing additional custom data.

### 3.2. The `HasCategory` Trait

To make a model categorizable, use the `Mintreu\LaravelCategory\Traits\HasCategory` trait.

```php
use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelCategory\Traits\HasCategory;

class Product extends Model
{
    use HasCategory;

    // ...
}
```

This trait provides two key relationships:

-   **`categories()`**: A `morphToMany` relationship that allows a model to be associated with multiple categories.
-   **`category()`**: A `belongsTo` relationship for models that have a single primary category (requires a `category_id` column on the model's table).

**Example Usage:**

```php
$product = Product::find(1);

// Attach categories
$category1 = Category::find(1);
$category2 = Category::find(2);
$product->categories()->attach([$category1->id, $category2->id]);

// Get all categories for a product
$categories = $product->categories;

// Get the primary category (if using a direct relationship)
$primaryCategory = $product->category;
```

### 3.3. Hierarchical Categories

The package uses `staudenmeir/laravel-adjacency-list` to manage hierarchical data. You can create nested categories by setting the `parent_id`.

**Example:**

```php
$parent = Category::create(['name' => 'Electronics', 'url' => 'electronics']);
$child = Category::create(['name' => 'Smartphones', 'url' => 'smartphones', 'parent_id' => $parent->id]);

// Get children of a category
$children = $parent->children;

// Get the parent of a category
$parent = $child->parent;
```

### 3.4. Dynamic Relationships

The `Category` model provides a "magic" `__call` method to dynamically load related models based on your configuration. The method name should be the plural, snake_case version of the model name.

**Example:**

If you have `\App\Models\Product` in your `laravel-category.php` config, you can do:

```php
$category = Category::where('url', 'smartphones')->first();

// This will dynamically call the morphedByMany relationship for products
$products = $category->products;
```

## 4. Review and Production Readiness Assessment

### Review

-   **Strengths:**
    -   The polymorphic many-to-many relationship provides a highly flexible and scalable categorization system.
    -   Support for hierarchical categories is a powerful feature for complex catalogs.
    -   Integration with `spatie/laravel-medialibrary` for category images is a great addition.
    -   The use of modern packages like `staudenmeir/laravel-adjacency-list` and `codewithdennis/filament-select-tree` indicates a well-thought-out architecture.

-   **Weaknesses:**
    -   **Lack of Documentation:** The package's `README.md` is a template and provides no real information. This is a major barrier to adoption and a significant risk for production use.
    -   **Misleading Facade:** The `LaravelCategory` facade points to an empty class, which is confusing and serves no purpose.
    -   **"Magic" Methods:** The dynamic relationship resolution via `__call` is clever but can be difficult for developers to discover and debug. This kind of "magic" should be used sparingly and be extremely well-documented.
    -   **Incomplete `categorized()` method:** The `categorized()` relationship on the `Category` model appears to be hardcoded to only work with the first model defined in the configuration, which is likely not the intended behavior.

### Production Readiness: **NOT READY**

The package has a solid conceptual foundation, but it is not production-ready in its current state. The lack of documentation, placeholder code, and potential bugs make it too risky to use in a live application without significant improvements.

## 5. Recommendations for Improvement

To make this package more robust, testable, and production-ready, the following actions are recommended:

1.  **Complete the Documentation:**
    -   Create a comprehensive `README.md` that covers all features, including installation, configuration, usage of the `HasCategory` trait, hierarchical categories, and the dynamic relationship loading.
    -   Provide clear code examples for all features.
    -   Remove all placeholder content.

2.  **Refactor the Code:**
    -   **Remove or Implement the Facade:** The `LaravelCategory` facade and its underlying empty class should either be removed or given a meaningful purpose (e.g., a service class with helper methods for managing categories).
    -   **Clarify Dynamic Relationships:** While the `__call` magic is powerful, consider adding explicit, documented methods for retrieving related models to improve clarity and discoverability.
    -   **Fix the `categorized()` method:** The `categorized()` method on the `Category` model should be reviewed and fixed to work with all configured models, not just the first one.

3.  **Improve Testability and Add Tests:**
    -   **Unit Tests:**
        -   Write unit tests for the `Category` model to verify its relationships, scopes, and media collections.
        -   Test the hierarchical features, including creating, moving, and deleting nested categories.
    -   **Feature Tests:**
        -   Create feature tests for the `HasCategory` trait to ensure it works correctly when used on a model.
        -   Test the dynamic relationship loading to ensure it resolves relationships correctly for all configured models.
    -   **Filament Tests (if applicable):** If there is a Filament resource for managing categories, it should have its own suite of tests to cover creation, editing, and deletion.

By addressing these points, the `laravel-category` package can be transformed into a robust, reliable, and production-ready solution for categorization in Laravel.
