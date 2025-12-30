# Filament v4 Guide

## Core Patterns

### Form Layout
- **Tabs**: Use `Tabs::make()->tabs([...])` for multi-tab forms.
- **Sections**: Use `Section::make('Title')->schema([...])` for logical groupings.
- **Grids**: Use `Grid::make(3)->schema([...])` for column-based layouts.
- **Collapsible**: Add `->collapsible()` to Sections.

### Tables
- **TextColumn**: Use `->badge()` for Enum-based color coding.
- **IconColumn**: Use `->boolean()` for true/false fields.
- **Toggles**: Use `ToggleColumn` for fast boolean updates.

### Schemas
- Split configuration into `UserForm`, `UserTable`, and `UserInfolist` classes as per current project pattern.

### Best Practices
- **Strict Types**: Always use `declare(strict_types=1);`.
- **Native Components**: Avoid custom Blade views unless absolutely necessary.
- **Persistence**: Use `->persistTab()` and `->persistCollapsed()` for better UX.
- **Conditional Requirements**: Use `->required(fn ($operation) => $operation === 'create')` for passwords.
