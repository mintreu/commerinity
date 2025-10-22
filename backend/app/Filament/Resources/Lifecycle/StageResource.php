<?php

namespace App\Filament\Resources\Lifecycle;

use Filament\Pages\Enums\SubNavigationPosition;
use App\Filament\Resources\Lifecycle\StageResource\Pages\ViewStage;
use App\Filament\Resources\Lifecycle\StageResource\Pages\ManageStageLevels;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use App\Filament\Resources\Lifecycle\StageResource\Pages\ListStages;
use App\Filament\Resources\Lifecycle\StageResource\Pages\CreateStage;
use App\Filament\Resources\Lifecycle\StageResource\Pages\EditStage;
use App\Filament\Resources\Lifecycle\StageResource\Pages\ManageSubscription;
use App\Filament\Resources\Lifecycle\StageResource\Pages;
use App\Filament\Resources\Lifecycle\StageResource\RelationManagers;
use App\Models\Lifecycle\Stage;
use Filament\Forms;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;

class StageResource extends Resource
{
    protected static ?string $model = Stage::class;
    protected static ?string $recordRouteKeyName = 'url';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-square-2-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Lifecycle';

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewStage::class,
            ManageStageLevels::class,
           // Pages\ManageSubscription::class,
        ]);
    }


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('url')
                    ->required()
                    ->maxLength(255),
                Textarea::make('desc')
                    ->columnSpanFull(),
                TextInput::make('base_price')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tax_percentage')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tax_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('max_team_members')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('estimated_total_joining_points')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('estimated_total_purchasing_points')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('benefits'),
                TextInput::make('accessibility'),
                Toggle::make('status')
                    ->required(),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStages::route('/'),
            'create' => CreateStage::route('/create'),
            'view' => ViewStage::route('/{record:url}'),
            'edit' => EditStage::route('/{record:url}/edit'),
            'levels' => ManageStageLevels::route('/{record:url}/level'),
            'subscriptions' => ManageSubscription::route('/{record:url}/subscriptions'),
        ];
    }
}
