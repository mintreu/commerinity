# Agent Guidelines for Commerinity Project

## Build/Lint/Test Commands

### Backend (Laravel/PHP)
- **Full development server**: `composer run dev` (runs server, queue, logs, and Vite concurrently)
- **Run all tests**: `composer run test` or `php artisan test`
- **Run single test**: `php artisan test --filter=testName` or `php artisan test tests/Feature/ExampleTest.php`
- **Lint/format code**: `vendor/bin/pint --dirty` (run before finalizing changes)
- **Build assets**: `npm run build` (in backend directory)

### Frontend (Nuxt/Vue/TypeScript)
- **Development server**: `npm run dev`
- **Build for production**: `npm run build`
- **Generate static site**: `npm run generate`
- **Preview build**: `npm run preview`

## Code Style Guidelines

### PHP/Laravel
- **Indentation**: 4 spaces (configured in .editorconfig)
- **Return types**: Always use explicit return type declarations
- **Constructors**: Use PHP 8 constructor property promotion
- **Control structures**: Always use curly braces, even for single lines
- **Imports**: Organize imports following existing patterns in files
- **Naming**: Use descriptive names (e.g., `isRegisteredForDiscounts`, not `discount()`)
- **Enums**: Keys should be TitleCase (e.g., `FavoritePerson`, `BestLake`)
- **Comments**: Prefer PHPDoc blocks over inline comments
- **Validation**: Use Form Request classes instead of inline validation
- **Database**: Prefer Eloquent relationships over raw queries, avoid `DB::` facade
- **Testing**: Use PHPUnit (not Pest), create factories for test models

### Vue/TypeScript (Nuxt 3)
- **Framework**: Nuxt 3 with TypeScript
- **Components**: Follow existing Vue component patterns
- **Styling**: Tailwind CSS v4 (use `@import "tailwindcss"` in CSS files)
- **Dark mode**: Support dark mode using `dark:` prefixes when applicable
- **Spacing**: Use gap utilities instead of margins for flex/grid layouts

### General
- **Formatting**: Run `vendor/bin/pint --dirty` for PHP code before committing
- **Architecture**: Follow existing Laravel/Filament structure, don't create new base folders
- **Dependencies**: Don't change dependencies without approval
- **Testing**: Test happy paths, failure paths, and edge cases
- **Documentation**: Only create documentation files when explicitly requested</content>
<parameter name="filePath">C:/laragon/www/mintreu/server/commerinity/AGENTS.md