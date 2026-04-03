<?php

namespace App\Filament\Resources\JobApplications\Tables;

use App\Casts\JobApplicationStatusCast;
use App\Casts\UserTypeCast;
use App\Filament\Exports\Recruitment\JobApplicationExporter;
use App\Models\Geo\Block;
use App\Models\Geo\District;
use App\Models\Geo\State;
use App\Models\Recruitment\JobApplication;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                SelectFilter::make('state_code')
                    ->label('State')
                    ->options(fn (): array => State::query()
                        ->orderBy('name')
                        ->pluck('name', 'code')
                        ->all())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $stateCode = $data['value'] ?? null;

                        if (! filled($stateCode)) {
                            return $query;
                        }

                        return $query->whereHas('address', fn (Builder $addressQuery) => $addressQuery->where('state_code', $stateCode));
                    }),
                SelectFilter::make('district_id')
                    ->label('District')
                    ->options(fn (): array => District::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $districtId = $data['value'] ?? null;

                        if (! filled($districtId)) {
                            return $query;
                        }

                        return $query->whereHas('address', fn (Builder $addressQuery) => $addressQuery->where('district_id', (int) $districtId));
                    }),
                SelectFilter::make('block_id')
                    ->label('Block')
                    ->options(fn (): array => Block::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $blockId = $data['value'] ?? null;

                        if (! filled($blockId)) {
                            return $query;
                        }

                        return $query->whereHas('address', fn (Builder $addressQuery) => $addressQuery->where('block_id', (int) $blockId));
                    }),
                SelectFilter::make('reference_name')
                    ->label('Referred By')
                    ->options(fn (): array => JobApplication::query()
                        ->whereNotNull('reference_name')
                        ->where('reference_name', '!=', '')
                        ->orderBy('reference_name')
                        ->pluck('reference_name', 'reference_name')
                        ->all())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $referenceName = $data['value'] ?? null;

                        if (! filled($referenceName)) {
                            return $query;
                        }

                        return $query->where('reference_name', $referenceName);
                    }),
                SelectFilter::make('reference_contact')
                    ->label('Reference Phone')
                    ->options(fn (): array => JobApplication::query()
                        ->whereNotNull('reference_contact')
                        ->where('reference_contact', '!=', '')
                        ->orderBy('reference_contact')
                        ->pluck('reference_contact', 'reference_contact')
                        ->all())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $referenceContact = $data['value'] ?? null;

                        if (! filled($referenceContact)) {
                            return $query;
                        }

                        return $query->where('reference_contact', $referenceContact);
                    }),
                TernaryFilter::make('is_paid')
                    ->label('Payment Status'),
                SelectFilter::make('status')
                    ->label('Application Status')
                    ->options(collect(JobApplicationStatusCast::cases())
                        ->mapWithKeys(fn (JobApplicationStatusCast $status) => [$status->value => $status->getLabel()])
                        ->all()),
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
            ->headerActions([
                ExportAction::make()
                    ->exporter(JobApplicationExporter::class)
                    ->enableVisibleTableColumnsByDefault()
                    ->columnMappingColumns(3),
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
