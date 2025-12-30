<?php

namespace App\Filament\Resources\Membership\UserSubscriptions;

use App\Filament\Resources\Membership\UserSubscriptions\Pages\CreateUserSubscription;
use App\Filament\Resources\Membership\UserSubscriptions\Pages\EditUserSubscription;
use App\Filament\Resources\Membership\UserSubscriptions\Pages\ListUserSubscriptions;
use App\Filament\Resources\Membership\UserSubscriptions\Pages\ViewUserSubscription;
use App\Filament\Resources\Membership\UserSubscriptions\Schemas\UserSubscriptionForm;
use App\Filament\Resources\Membership\UserSubscriptions\Schemas\UserSubscriptionInfolist;
use App\Filament\Resources\Membership\UserSubscriptions\Tables\UserSubscriptionsTable;
use App\Models\Membership\UserSubscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserSubscriptionResource extends Resource
{
    protected static ?string $model = UserSubscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|null|\UnitEnum $navigationGroup = 'Subscription';

    public static function form(Schema $schema): Schema
    {
        return UserSubscriptionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserSubscriptionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserSubscriptionsTable::configure($table);
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
