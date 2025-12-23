<?php

namespace App\Filament\Resources\Kycs\Schemas;

use App\Casts\KycStatusCast;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KycForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kycable_type')
                    ->required(),
                TextInput::make('kycable_id')
                    ->required()
                    ->numeric(),
                TextInput::make('kyc_type')
                    ->required()
                    ->default('personal'),
                TextInput::make('company_name'),
                TextInput::make('company_type'),
                TextInput::make('pan_number')
                    ->required(),
                TextInput::make('aadhaar_number'),
                TextInput::make('gst_number'),
                Select::make('status')
                    ->options(KycStatusCast::class)
                    ->default('pending')
                    ->required(),
                Textarea::make('rejection_reason')
                    ->columnSpanFull(),
                DateTimePicker::make('submitted_at'),
                DateTimePicker::make('reviewed_at'),
                TextInput::make('reviewed_by')
                    ->numeric(),
            ]);
    }
}
