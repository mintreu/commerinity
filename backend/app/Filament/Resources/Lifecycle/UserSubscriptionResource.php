<?php

namespace App\Filament\Resources\Lifecycle;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Lifecycle\UserSubscriptionResource\Pages\ListUserSubscriptions;
use App\Filament\Resources\Lifecycle\UserSubscriptionResource\Pages\CreateUserSubscription;
use App\Filament\Resources\Lifecycle\UserSubscriptionResource\Pages\ViewUserSubscription;
use App\Filament\Resources\Lifecycle\UserSubscriptionResource\Pages\EditUserSubscription;
use App\Filament\Resources\Lifecycle\UserSubscriptionResource\Pages;
use App\Filament\Resources\Lifecycle\UserSubscriptionResource\RelationManagers;
use App\Models\Lifecycle\UserSubscription;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserSubscriptionResource extends Resource
{
    protected static ?string $model = UserSubscription::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Lifecycle';
    protected static ?string $pluralLabel = 'Member Subscriptions';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required()
                    ->maxLength(36),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_paid')
                    ->required(),
                DateTimePicker::make('expires_at'),
                DateTimePicker::make('checkout_expires_at'),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('level_id')
                    ->relationship('level', 'name')
                    ->required(),
                Select::make('stage_id')
                    ->relationship('stage', 'name')
                    ->required(),
                Select::make('wallet_id')
                    ->relationship('wallet', 'id'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_paid')
                    ->boolean(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('checkout_expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('level.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stage.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('wallet.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index' => ListUserSubscriptions::route('/'),
            'create' => CreateUserSubscription::route('/create'),
            'view' => ViewUserSubscription::route('/{record}'),
            'edit' => EditUserSubscription::route('/{record}/edit'),
        ];
    }
}
