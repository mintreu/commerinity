<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Addresses\AddressResource;
use App\Filament\Resources\Users\UserResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ManageUserAddresses extends ManageRelatedRecords
{
    protected static string $resource = UserResource::class;

    protected static string $relationship = 'addresses';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $title = 'Addresses';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('person_name')
                    ->label('Recipient')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('person_mobile')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('postal_code')
                    ->label('PIN / ZIP')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('default')
                    ->label('Default')
                    ->boolean(),
            ])
            ->recordActions([
                Action::make('viewAddress')
                    ->label('View')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn ($record): string => AddressResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
