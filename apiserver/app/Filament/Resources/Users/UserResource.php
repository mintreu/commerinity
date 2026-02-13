<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ManageUserAddresses;
use App\Filament\Resources\Users\Pages\ManageUserBeneficiaries;
use App\Filament\Resources\Users\Pages\ManageUserChildren;
use App\Filament\Resources\Users\Pages\ManageUserJobApplications;
use App\Filament\Resources\Users\Pages\ManageUserKycs;
use App\Filament\Resources\Users\Pages\ManageUserOrders;
use App\Filament\Resources\Users\Pages\ManageUserSubscriptions;
use App\Filament\Resources\Users\Pages\ManageUserTransactions;
use App\Filament\Resources\Users\Pages\ManageUserWallet;
use App\Filament\Resources\Users\Pages\ViewUserInsights;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|null|\UnitEnum $navigationGroup = 'Account Management';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\ViewUser::class,
            Pages\ViewUserInsights::class,
            Pages\EditUser::class,
            Pages\ManageUserOrders::class,
            Pages\ManageUserJobApplications::class,
            Pages\ManageUserSubscriptions::class,
            Pages\ManageUserChildren::class,
            Pages\ManageUserAddresses::class,
            Pages\ManageUserKycs::class,
            Pages\ManageUserBeneficiaries::class,
            Pages\ManageUserWallet::class,
            Pages\ManageUserTransactions::class,
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'insights' => ViewUserInsights::route('/{record}/insights'),
            'edit' => EditUser::route('/{record}/edit'),
            'orders' => ManageUserOrders::route('/{record}/orders'),
            'job-applications' => ManageUserJobApplications::route('/{record}/job-applications'),
            'subscriptions' => ManageUserSubscriptions::route('/{record}/subscriptions'),
            'children' => ManageUserChildren::route('/{record}/children'),
            'addresses' => ManageUserAddresses::route('/{record}/addresses'),
            'kycs' => ManageUserKycs::route('/{record}/kycs'),
            'beneficiaries' => ManageUserBeneficiaries::route('/{record}/beneficiaries'),
            'wallet' => ManageUserWallet::route('/{record}/wallet'),
            'transactions' => ManageUserTransactions::route('/{record}/transactions'),
        ];
    }
}
