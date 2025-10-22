<?php

namespace App\Filament\Resources\Users;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ManageChildrens;
use App\Filament\Resources\Users\Pages\ManageCommunity;
use App\Filament\Resources\Users\Pages\ViewUserStats;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\Users\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $recordRouteKeyName = 'referral_code';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Peoples';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('mobile')
                    ->maxLength(255),
                DateTimePicker::make('mobile_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                TextInput::make('referral_code')
                    ->maxLength(255),
                TextInput::make('parent_id')
                    ->numeric(),
                TextInput::make('originator_type')
                    ->maxLength(255),
                TextInput::make('originator_id')
                    ->numeric(),
                Textarea::make('bio')
                    ->columnSpanFull(),
                TextInput::make('gender')
                    ->required()
                    ->maxLength(255)
                    ->default('other'),
                DatePicker::make('dob'),
                TextInput::make('type')
                    ->required()
                    ->maxLength(255)
                    ->default('regular'),
                TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('draft'),
                Textarea::make('status_feedback')
                    ->columnSpanFull(),
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record:referral_code}'),
            'edit' => EditUser::route('/{record:referral_code}/edit'),
            'members' => ManageChildrens::route('/{record:referral_code}/children'),
            'community' => ManageCommunity::route('/{record:referral_code}/community'),
            'stats' => ViewUserStats::route('/{record:referral_code}/stats'),
        ];
    }
}
