<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\BeneficiaryAccounts\BeneficiaryAccountResource;
use App\Filament\Resources\Users\UserResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ManageUserBeneficiaries extends ManageRelatedRecords
{
    protected static string $resource = UserResource::class;

    protected static string $relationship = 'beneficiaryAccounts';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $title = 'Beneficiaries';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('uuid')
            ->columns([
                TextColumn::make('uuid')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('holder_name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('masked_account_number')
                    ->label('Account')
                    ->placeholder('-'),
                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('viewBeneficiary')
                    ->label('View')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn ($record): string => BeneficiaryAccountResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
