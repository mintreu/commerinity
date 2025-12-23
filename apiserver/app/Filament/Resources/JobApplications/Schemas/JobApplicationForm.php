<?php

namespace App\Filament\Resources\JobApplications\Schemas;

use App\Casts\JobApplicationStatusCast;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class JobApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                Select::make('recruitment_id')
                    ->relationship('recruitment', 'title')
                    ->required(),
                TextInput::make('applicant_type')
                    ->required(),
                TextInput::make('applicant_id')
                    ->required()
                    ->numeric(),
                TextInput::make('guardian_name')
                    ->required(),
                Select::make('address_id')
                    ->relationship('address', 'title'),
                TextInput::make('educations'),
                TextInput::make('skills'),
                TextInput::make('experiences'),
                TextInput::make('reference_name'),
                TextInput::make('reference_contact'),
                Toggle::make('is_paid')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('transaction_id')
                    ->relationship('transaction', 'id'),
                Select::make('status')
                    ->options(JobApplicationStatusCast::class)
                    ->default('draft')
                    ->required(),
                Textarea::make('status_feedback')
                    ->columnSpanFull(),
                DateTimePicker::make('submitted_at'),
                TextInput::make('import_batch_id'),
                TextInput::make('import_data'),
            ]);
    }
}
