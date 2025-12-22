<?php

namespace App\Filament\Resources\Lifecycle\LevelResource\Pages;

use App\Filament\Resources\Lifecycle\LevelResource;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use Filament\Forms\Form;

class EditLevel extends EditRecord
{
    protected static string $resource = LevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }





    public  function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('validate_years')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\Select::make('stage_id')
                    ->relationship('stage', 'name')
                    ->required(),
                Forms\Components\Toggle::make('status')
                    ->required(),
                Forms\Components\TextInput::make('team_member_limit')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('joining_bonus')
                    ->required()
                    ->numeric()
                    ->default(0.00),


            TableRepeater::make('tasks')
                ->label('Level Tasks')
                ->relationship('tasks')
                ->headers([
                    Header::make('name')->label('Task Name'),
                    Header::make('url')->label('Task URL / Route'),
                    Header::make('description')->label('Description'),
                    Header::make('score')->label('Score Reward'),
                    Header::make('min_eligible_score')->label('Min Eligible Score'),
                    Header::make('min_progress')->label('Min Progress Requirements'),
                    Header::make('game_type')->label('Game Type'),
                ])
                ->schema([

                    Forms\Components\TextInput::make('name')
                        ->label('Task Name')
                        ->placeholder('Enter task title')
                        ->required()
                        ->maxLength(255)
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            // Automatically generate slug-like URL if it's empty
                            $set('url', str($state)->slug()->prepend('/')->toString());
                        })
                        ->helperText('A short descriptive name for the task.'),

                    Forms\Components\TextInput::make('url')
                        ->label('Task URL / Route')
                        ->placeholder('/game/spinner')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->helperText('Provide a unique URL or endpoint for this task.'),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->placeholder('Describe what this task is about...')
                        ->maxLength(1000)
                        ->rows(2)
                        ->helperText('Optional: a short summary or guidance for this task.'),

                    Forms\Components\TextInput::make('score')
                        ->label('Score Reward')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Points awarded when the task is completed.'),

                    Forms\Components\TextInput::make('min_eligible_score')
                        ->label('Minimum Eligible Score')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Minimum score required before this task becomes available.'),

                    Forms\Components\TextInput::make('min_progress')
                        ->label('Minimum Progress Requirements')
                        ->placeholder('e.g., spins:10, coins:100')
                        ->helperText('Define metrics or progress criteria for eligibility.'),

                    Forms\Components\Select::make('game_type')
                        ->label('Game Type (Optional)')
                        ->options([
                            'spinner' => 'Spinner',
                            'village' => 'Village',
                            'quiz' => 'Quiz',
                            'mission' => 'Mission',
                        ])
                        ->searchable()
                        ->placeholder('Select a game type')
                        ->helperText('Optional: categorize this task under a specific module.')
                        ->nullable(),
                ])
                ->columnSpanFull()
                ->reorderable(true)
                ->deletable(true)
                ->addable(true)
                ->collapsible()
                ->default([])
                ->helperText('Manage all level-specific tasks here — including rules, descriptions, and rewards.'),



            ]);
    }









}
