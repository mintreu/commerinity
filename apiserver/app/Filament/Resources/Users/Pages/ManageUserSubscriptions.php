<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Membership\UserSubscriptions\UserSubscriptionResource;
use App\Filament\Resources\Users\UserResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ManageUserSubscriptions extends ManageRelatedRecords
{
    protected static string $resource = UserResource::class;

    protected static string $relationship = 'subscriptions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $title = 'Subscriptions';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('uuid')
            ->columns([
                TextColumn::make('uuid')
                    ->label('Subscription ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stage.name')
                    ->label('Stage')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('currentLevel.name')
                    ->label('Current Level')
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_paid')
                    ->label('Paid')
                    ->boolean(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('viewSubscription')
                    ->label('View')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn ($record): string => UserSubscriptionResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
