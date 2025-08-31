<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $recordRouteKeyName = 'referral_code';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('mobile')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('mobile_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('referral_code')
                    ->maxLength(255),
                Forms\Components\TextInput::make('parent_id')
                    ->numeric(),
                Forms\Components\TextInput::make('originator_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('originator_id')
                    ->numeric(),
                Forms\Components\Textarea::make('bio')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('gender')
                    ->required()
                    ->maxLength(255)
                    ->default('other'),
                Forms\Components\DatePicker::make('dob'),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(255)
                    ->default('regular'),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('draft'),
                Forms\Components\Textarea::make('status_feedback')
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record:referral_code}'),
            'edit' => Pages\EditUser::route('/{record:referral_code}/edit'),
            'members' => Pages\ManageChildrens::route('/{record:referral_code}/children'),
            'stats' => Pages\ViewUserStats::route('/{record:referral_code}/stats'),
        ];
    }
}
