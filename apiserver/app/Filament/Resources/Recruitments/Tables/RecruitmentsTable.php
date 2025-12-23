<?php

declare(strict_types=1);

namespace App\Filament\Resources\Recruitments\Tables;

use App\Casts\RecruitmentRoleCast;
use App\Casts\RecruitmentStatusCast;
use App\Services\MoneyService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RecruitmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('role')
                    ->badge()
                    ->sortable(),

                TextColumn::make('employment_type')
                    ->badge()
                    ->toggleable(),

                TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('vacancy')
                    ->numeric()
                    ->sortable()
                    ->label('Vacancies'),

                TextColumn::make('applications_count')
                    ->counts('applications')
                    ->label('Applications')
                    ->sortable(),

                IconColumn::make('is_payable')
                    ->boolean()
                    ->label('Paid'),

                TextColumn::make('fees')
                    ->formatStateUsing(fn ($state) => $state > 0 ? MoneyService::format($state) : 'Free')
                    ->sortable()
                    ->label('Fee'),

                TextColumn::make('open_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('close_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options(RecruitmentRoleCast::class),

                SelectFilter::make('status')
                    ->options(RecruitmentStatusCast::class),

                TernaryFilter::make('is_payable')
                    ->label('Payment Required'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
