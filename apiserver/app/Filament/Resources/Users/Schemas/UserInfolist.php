<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('email_verified_at')
                    ->dateTime(),
                TextEntry::make('mobile'),
                TextEntry::make('mobile_verified_at')
                    ->dateTime(),
                TextEntry::make('referral_code'),
                TextEntry::make('parent.name')
                    ->numeric(),
                TextEntry::make('level_id')
                    ->numeric(),
                TextEntry::make('originator_type'),
                TextEntry::make('originator_id')
                    ->numeric(),
                TextEntry::make('gender'),
                TextEntry::make('dob')
                    ->date(),
                TextEntry::make('type'),
                TextEntry::make('status'),
                IconEntry::make('onboarded')
                    ->boolean(),
                TextEntry::make('subscribed_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
