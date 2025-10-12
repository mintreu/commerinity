<?php

namespace App\Filament\Resources\Lifecycle;

use App\Filament\Resources\Lifecycle\StageResource\Pages;
use App\Filament\Resources\Lifecycle\StageResource\RelationManagers;
use App\Models\Lifecycle\Stage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;

class StageResource extends Resource
{
    protected static ?string $model = Stage::class;
    protected static ?string $recordRouteKeyName = 'url';
    protected static ?string $navigationIcon = 'heroicon-o-square-2-stack';
    protected static ?string $navigationGroup = 'Lifecycle';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\ViewStage::class,
            Pages\ManageStageLevels::class,
           // Pages\ManageSubscription::class,
        ]);
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('desc')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('base_price')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('tax_percentage')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('tax_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                Forms\Components\TextInput::make('max_team_members')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('estimated_total_joining_points')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('estimated_total_purchasing_points')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('benefits'),
                Forms\Components\TextInput::make('accessibility'),
                Forms\Components\Toggle::make('status')
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
            'index' => Pages\ListStages::route('/'),
            'create' => Pages\CreateStage::route('/create'),
            'view' => Pages\ViewStage::route('/{record:url}'),
            'edit' => Pages\EditStage::route('/{record:url}/edit'),
            'levels' => Pages\ManageStageLevels::route('/{record:url}/level'),
            'subscriptions' => Pages\ManageSubscription::route('/{record:url}/subscriptions'),
        ];
    }
}
