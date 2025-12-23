<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('log_name')
                    ->label('Log')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'user-activity' => 'info',
                        'default' => 'gray',
                        default => 'primary',
                    })
                    ->sortable(),

                TextColumn::make('event')
                    ->label('Event')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'login' => 'success',
                        'logout' => 'warning',
                        'page_view' => 'info',
                        'action' => 'primary',
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('causer.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('M j, Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Log Type')
                    ->options([
                        'user-activity' => 'User Activity',
                        'default' => 'System Default',
                    ]),

                SelectFilter::make('event')
                    ->label('Event Type')
                    ->options([
                        'login' => 'Login',
                        'logout' => 'Logout',
                        'page_view' => 'Page View',
                        'action' => 'Action',
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),

                SelectFilter::make('causer_id')
                    ->label('User')
                    ->searchable()
                    ->preload()
                    ->relationship('causer', 'name'),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->poll('30s');
    }
}
