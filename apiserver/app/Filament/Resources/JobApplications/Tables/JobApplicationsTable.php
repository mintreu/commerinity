<?php

namespace App\Filament\Resources\JobApplications\Tables;

use App\Casts\UserTypeCast;
use App\Filament\Exports\Recruitment\JobApplicationExporter;
use App\Models\Recruitment\JobApplication;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class JobApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('recruitment.title')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('applicant_type')
                    ->searchable(),
                TextColumn::make('applicant_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('guardian_name')
                    ->searchable(),
                TextColumn::make('address.title')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reference_name')
                    ->searchable(),
                TextColumn::make('reference_contact')
                    ->searchable(),
                IconColumn::make('is_paid')
                    ->boolean(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('transaction.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('import_batch_id')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('makeAdvisor')
                    ->label('Make Advisor')
                    ->icon('heroicon-o-user')
                    ->requiresConfirmation()
                    ->visible(fn (JobApplication $record) => $record->applicant_type === User::class
                        && $record->applicant?->type !== UserTypeCast::ADVISOR)
                    ->action(function (JobApplication $record): void {
                        /** @var User|null $user */
                        $user = $record->applicant;
                        if (! $user) {
                            return;
                        }

                        $user->update(['type' => UserTypeCast::ADVISOR]);
                        $record->accept('Promoted to advisor via admin action.');

                        Notification::make()
                            ->title('User promoted to Advisor')
                            ->success()
                            ->send();
                    }),
                Action::make('makeMentor')
                    ->label('Make Mentor')
                    ->icon('heroicon-o-academic-cap')
                    ->requiresConfirmation()
                    ->visible(fn (JobApplication $record) => $record->applicant_type === User::class
                        && $record->applicant?->type !== UserTypeCast::MENTOR)
                    ->action(function (JobApplication $record): void {
                        /** @var User|null $user */
                        $user = $record->applicant;
                        if (! $user) {
                            return;
                        }

                        $user->update(['type' => UserTypeCast::MENTOR]);
                        $record->accept('Promoted to mentor via admin action.');

                        Notification::make()
                            ->title('User promoted to Mentor')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(JobApplicationExporter::class)
                    ,
                ]),
            ]);
    }
}
