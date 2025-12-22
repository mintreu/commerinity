# Filament v3 to v4 Resource Migration Guide

## 1. Introduction

This document outlines the key differences between Filament v3 and v4 resources, using the `backend` (v3) and `apiserver` (v4) projects as a reference. The goal is to provide a clear guide for migrating existing resources or creating new ones in the `apiserver` project that align with the modern, more organized structure of Filament v4.

## 2. Core Structural Change: Modularity

The most significant change in Filament v4 is the shift from a monolithic resource file to a modular, more organized structure where responsibilities are delegated to dedicated classes.

### Filament v3 (`backend`)

-   **Monolithic Structure:** All definitions for the form, table, actions, and filters are typically located within the single `Resource` class (e.g., `AdminResource.php`).

    ```php
    // backend/app/Filament/Resources/AdminResource.php (v3 Example)
    class AdminResource extends Resource
    {
        public static function form(Form $form): Form
        {
            return $form->schema([...]); // Form fields defined here
        }

        public static function table(Table $table): Table
        {
            return $table
                ->columns([...]) // Table columns defined here
                ->filters([...])
                ->actions([...])
                ->bulkActions([...]);
        }
        // ...
    }
    ```

### Filament v4 (`apiserver`)

-   **Modular Structure:** The main `Resource` class is now much cleaner. It delegates the responsibility of defining forms, tables, and infolists to separate, dedicated classes.

    ```php
    // apiserver/app/Filament/Resources/Admins/AdminResource.php (v4 Example)
    class AdminResource extends Resource
    {
        public static function form(Schema $schema): Schema
        {
            // Delegates to a dedicated Form schema class
            return AdminForm::configure($schema);
        }

        public static function infolist(Schema $schema): Schema
        {
            // Delegates to a dedicated Infolist schema class
            return AdminInfolist::configure($schema);
        }

        public static function table(Table $table): Table
        {
            // Delegates to a dedicated Table class
            return AdminsTable::configure($table);
        }
        // ...
    }
    ```

This new structure promotes better code organization, reusability, and maintainability, especially for complex resources.

## 3. The "Generate, Then Edit" Workflow

A core principle of this migration is to **always use Artisan commands to generate the boilerplate for your v4 components**. Do not manually create the resource files. This ensures they have the correct namespaces, structure, and class imports.

### Key Generation Commands:

-   `php artisan make:filament-resource {ResourceName}`: This is the primary command. It will generate the main `Resource` class and its associated `List`, `Create`, `Edit`, and `View` page classes.
-   `php artisan make:filament-relation-manager {ResourceName} {RelationshipName}`: Use this to create a `RelationManager` for any relationships you need to manage on your resource's edit page.
-   `php artisan make:filament-page {PageName} --resource={ResourceName}`: Use this to create additional custom pages for a resource, similar to the `ManageChildrens` or `ViewUserStats` pages in the v3 `UserResource`.

By generating the files first, you can then focus on porting the specific logic (form fields, table columns) from the v3 resource into the appropriate new v4 classes (`...Form.php`, `...Table.php`, etc.).

## 4. Migration Example: `AdminResource`

The `AdminResource` provides a perfect example of a straightforward migration.

### Step 1: The v3 Implementation (`backend`)

The form and table are defined directly in `backend/app/Filament/Resources/AdminResource.php`.

### Step 2: The v4 Implementation (`apiserver`)

The logic is split into multiple files:

-   **`apiserver/app/Filament/Resources/Admins/AdminResource.php`**: The main resource file that orchestrates the components.
-   **`apiserver/app/Filament/Resources/Admins/Schemas/AdminForm.php`**: Contains the form schema. The code inside its `configure()` method is nearly identical to the code that was in the v3 `form()` method.
-   **`apiserver/app/Filament/Resources/Admins/Tables/AdminsTable.php`**: Contains the table definition. The code inside its `configure()` method is nearly identical to the code from the v3 `table()` method.
-   **`apiserver/app/Filament/Resources/Admins/Schemas/AdminInfolist.php`**: A new addition in v4 for defining the read-only "View" page.

## 5. Complex Resource Analysis: `UserResource`

The `UserResource` highlights the difference in complexity and features between the two projects.

-   **The `backend` (v3) `UserResource` is highly customized:**
    -   Its form contains many fields specific to the detailed `User` model (`referral_code`, `parent_id`, `bio`, etc.).
    -   It defines multiple custom pages for managing a user's downline, community, and stats (`ManageChildrens`, `ManageCommunity`, `ViewUserStats`).
    -   It uses a custom route key (`referral_code`).

-   **The `apiserver` (v4) `UserResource` is a skeleton:**
    -   It currently has a minimal form with only basic fields (`name`, `email`, `password`).
    -   It lacks the custom pages and detailed business logic of the v3 version.

### How to Migrate the `UserResource` to v4:

1.  **Generate Files:** Start by running `php artisan make:filament-resource User`.
2.  **Form:** The entire schema from the `form()` method in the v3 `UserResource` would be moved into the generated `app/Filament/Resources/UserResource/Schemas/UserForm.php` class.
3.  **Table:** A new `app/Filament/Resources/UserResource/Tables/UserTable.php` class would be created to define the columns, filters, and actions for the user list.
4.  **Custom Pages:** The custom pages (`ManageChildrens`, etc.) would be re-created by running `php artisan make:filament-page ...` within the `UserResource`, and the routing would be defined in the `getPages()` method of the v4 `UserResource`.
5.  **Route Key:** The `protected static ?string $recordRouteKeyName = 'referral_code';` property would be added to the v4 `UserResource` to maintain the custom URL structure.

## 6. Summary of Changes & Migration Checklist

When migrating a resource from v3 to v4, follow these steps:

-   [ ] **1. Generate the Resource:** Run `php artisan make:filament-resource {ResourceName}` to create the basic v4 resource structure, including the main resource class and its pages.

-   [ ] **2. Create Schema/Table Classes:**
    -   Manually create the `Schemas` and `Tables` subdirectories inside your new resource directory.
    -   Create a `...Form.php` and `...Infolist.php` inside the `Schemas/` directory.
    -   Create a `...Table.php` inside the `Tables/` directory.

-   [ ] **3. Migrate Logic:**
    -   Copy the schema array from the v3 `form()` method into the `configure()` method of your new `...Form.php` class.
    -   Copy the column, filter, and action definitions from the v3 `table()` method into the `configure()` method of your new `...Table.php` class.

-   [ ] **4. Update Main Resource File:**
    -   Modify the main `Resource.php` file in `apiserver` to delegate its `form()`, `infolist()`, and `table()` methods to your new classes, as shown in the `AdminResource` v4 example.

-   [ ] **5. Update Syntax:**
    -   **Icons:** Change string-based icons to use the `Heroicon` enum (e.g., `'heroicon-o-rectangle-stack'` becomes `Heroicon::OutlinedRectangleStack`).
    -   **Actions:** In the `Table` class, `actions()` becomes `recordActions()` and `bulkActions()` becomes `toolbarActions()`.

-   [ ] **6. Re-create Custom Pages & Relations:**
    -   Use `php artisan make:filament-page` and `php artisan make:filament-relation-manager` to generate any additional pages or relation managers.
    -   Copy the relevant logic from the v3 implementation into these new files.
    -   Update the `getPages()` and `getRelations()` methods in the main resource file accordingly.
