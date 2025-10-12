<?php

namespace App\Filament\Resources\Lifecycle\StageResource\Pages;

use App\Filament\Resources\Lifecycle\StageResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ManageStageLevels extends ManageRelatedRecords
{
    protected static string $resource = StageResource::class;

    protected static string $relationship = 'levels';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'Levels';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->lazy()
                    ->afterStateUpdated(fn ($state,Forms\Set $set) => $set('url',Str::slug($state)))
                    ->maxLength(255),


                Forms\Components\TextInput::make('url')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('team_member_limit')
                    ->required(fn() => ($this->record->max_team_members - $this->record->levels->sum('team_member_limit')) > 0)
                    ->disabled(fn() => ($this->record->max_team_members - $this->record->levels->sum('team_member_limit')) == 0)
                    ->maxValue(function (?Model $record){
                        $available = $this->record->max_team_members - $this->record->levels->sum('team_member_limit');
                        return $available == 0 ? $record->team_member_limit : $available;
                    })
                    ->helperText(fn() => 'available team member limit : ' . $this->record->max_team_members - $this->record->levels->sum('team_member_limit'))
                    ->maxLength(255),

                Forms\Components\TextInput::make('joining_bonus')
                    ->required(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->description(fn() => 'Total Levels '.$this->record->levels->count().' Total Member Limit '.$this->record->levels->sum('team_member_limit'))
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label(__('Total Member')),
                Tables\Columns\TextColumn::make('team_member_limit'),
                Tables\Columns\TextColumn::make('joining_bonus'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
