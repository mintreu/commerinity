<?php

namespace App\Filament\Resources\Lifecycle\Stages\Pages;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Lifecycle\Stages\StageResource;
use Filament\Forms;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ManageStageLevels extends ManageRelatedRecords
{
    protected static string $resource = StageResource::class;

    protected static string $relationship = 'levels';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'Levels';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->lazy()
                    ->afterStateUpdated(fn ($state,Set $set) => $set('url',Str::slug($state)))
                    ->maxLength(255),


                TextInput::make('url')
                    ->required()
                    ->maxLength(255),

                TextInput::make('team_member_limit')
                    ->required(fn() => ($this->record->max_team_members - $this->record->levels->sum('team_member_limit')) > 0)
                    ->disabled(fn() => ($this->record->max_team_members - $this->record->levels->sum('team_member_limit')) == 0)
                    ->maxValue(function (?Model $record){
                        $available = $this->record->max_team_members - $this->record->levels->sum('team_member_limit');
                        return $available == 0 ? $record->team_member_limit : $available;
                    })
                    ->helperText(fn() => 'available team member limit : ' . $this->record->max_team_members - $this->record->levels->sum('team_member_limit'))
                    ->maxLength(255),

                TextInput::make('joining_bonus')
                    ->required(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->description(fn() => 'Total Levels '.$this->record->levels->count().' Total Member Limit '.$this->record->levels->sum('team_member_limit'))
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label(__('Total Member')),
                TextColumn::make('team_member_limit'),
                TextColumn::make('joining_bonus'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
//                Tables\Actions\Action::make('add_to_cart')
//                    ->color('success')
//                    ->action(function (Model $record){
//
//                        $activeUser = filament()->auth()->user();
//                        $activeUser->cart()->create([
//                            'cartable_id' => $record->id,
//                            'cartable_type' => get_class($record)
//                            // You can add additional fields here if needed
//                        ]);
//                        Notification::make()->title('Added to your cart')
//                            ->success()->send();
//
//
//                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
