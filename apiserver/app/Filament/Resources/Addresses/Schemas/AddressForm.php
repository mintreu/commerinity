<?php

namespace App\Filament\Resources\Addresses\Schemas;

use App\Casts\AddressTypeCast;
use App\Models\Admin;      // <-- adjust namespace if different
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class AddressForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(1)->schema([

                // =========================
                // Basic Info
                // =========================
                Section::make('Basic Information')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('uuid')
                                ->label('UUID')
                                ->required()
                                ->readOnly(fn (string $operation) => $operation === 'edit'),

                            TextInput::make('title')
                                ->placeholder('Home / Office / Warehouse'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('person_name')
                                ->required(),

                            TextInput::make('person_mobile')
                                ->required(),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('person_email')
                                ->email()
                                ->placeholder('Optional'),

                            TextInput::make('alternate_contact')
                                ->placeholder('Optional'),
                        ]),

                        Select::make('type')
                            ->label('Address Type')
                            ->options(
                                collect(AddressTypeCast::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
                                    ->all()
                            )
                            ->default(AddressTypeCast::HOME->value)
                            ->required(),
                    ])
                    ->compact(),

                // =========================
                // Address
                // =========================
                Section::make('Address')
                    ->schema([
                        Textarea::make('address_1')
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('address_2')
                            ->columnSpanFull()
                            ->placeholder('Optional'),

                        Grid::make(3)->schema([
                            TextInput::make('landmark')
                                ->placeholder('Optional'),

                            TextInput::make('city')
                                ->required(),

                            TextInput::make('postal_code')
                                ->required(),
                        ]),
                    ])
                    ->compact(),

                // =========================
                // Region & Geo
                // =========================
                Section::make('Region & Geo')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('block_id')
                                ->relationship('block', 'name')
                                ->searchable()
                                ->preload()
                                ->placeholder('Optional'),

                            TextInput::make('state_code')
                                ->placeholder('e.g. WB'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('country_code')
                                ->required()
                                ->default('IN'),

                            TextInput::make('pickup_location')
                                ->placeholder('Optional'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('latitude')->numeric(),
                            TextInput::make('longitude')->numeric(),
                        ]),
                    ])
                    ->collapsed()
                    ->collapsible()
                    ->compact(),

                // =========================
                // Owner (addressable)
                // =========================
                Section::make('Owner Mapping')
                    ->description('Attach address to Admin or User')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('addressable_type')
                                ->label('Owner Type')
                                ->options([
                                    Admin::class => 'Admin',
                                    User::class  => 'User',
                                ])
                                ->required()
                                ->live()
                                ->afterStateUpdated(
                                    fn (Set $set) => $set('addressable_id', null)
                                )
                                ->placeholder('Select owner type'),

                            Select::make('addressable_id')
                                ->label('Owner')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->placeholder('Search & select owner')
                                ->options(fn (Get $get): array =>
                                self::initialAddressableOptions($get('addressable_type'))
                                )
                                ->getSearchResultsUsing(fn (string $search, Get $get): array =>
                                self::searchAddressableOptions($get('addressable_type'), $search)
                                )
                                ->getOptionLabelUsing(fn ($value, Get $get): ?string =>
                                self::resolveAddressableLabel($get('addressable_type'), $value)
                                )
                                ->visible(fn (Get $get) => filled($get('addressable_type'))),
                        ]),
                    ])
                    ->collapsible()
                    ->compact(),

                // =========================
                // Settings
                // =========================
                Section::make('Settings')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('default')
                                ->label('Default Address')
                                ->required(),

                            TextInput::make('priority')
                                ->required()
                                ->numeric()
                                ->default(1),
                        ]),
                    ])
                    ->compact(),
            ])->columnSpanFull(),
        ]);
    }

    // =====================================================
    // Helper methods
    // =====================================================

    private static function initialAddressableOptions(?string $type): array
    {
        if (! self::isValidModel($type)) {
            return [];
        }

        return $type::query()
            ->latest('id')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (Model $m) => [
                $m->getKey() => self::labelForModel($m),
            ])
            ->all();
    }

    private static function searchAddressableOptions(?string $type, string $search): array
    {
        if (! self::isValidModel($type)) {
            return [];
        }

        return $type::query()
            ->where(function ($q) use ($search) {
                $q->orWhere('id', 'like', "%{$search}%");

                foreach (['name', 'title', 'email', 'uuid'] as $field) {
                    if (self::hasColumn($q->getModel(), $field)) {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                }
            })
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (Model $m) => [
                $m->getKey() => self::labelForModel($m),
            ])
            ->all();
    }

    private static function resolveAddressableLabel(?string $type, $id): ?string
    {
        if (! self::isValidModel($type)) {
            return null;
        }

        $record = $type::find($id);

        return $record ? self::labelForModel($record) : null;
    }

    private static function labelForModel(Model $model): string
    {
        return $model->getAttribute('name')
            ?? $model->getAttribute('title')
            ?? $model->getAttribute('email')
            ?? $model->getAttribute('uuid')
            ?? ('#' . $model->getKey());
    }

    private static function isValidModel(?string $type): bool
    {
        return filled($type)
            && class_exists($type)
            && is_subclass_of($type, Model::class);
    }

    private static function hasColumn(Model $model, string $column): bool
    {
        try {
            return in_array(
                $column,
                $model->getConnection()
                    ->getSchemaBuilder()
                    ->getColumnListing($model->getTable()),
                true
            );
        } catch (\Throwable) {
            return false;
        }
    }
}
