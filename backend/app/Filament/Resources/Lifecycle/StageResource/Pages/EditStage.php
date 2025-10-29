<?php

namespace App\Filament\Resources\Lifecycle\StageResource\Pages;

use App\Filament\Resources\Lifecycle\StageResource;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Mintreu\LaravelMoney\Filament\Forms\Components\MoneyInput;

class EditStage extends EditRecord
{
    protected static string $resource = StageResource::class;

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

                Forms\Components\Section::make('General Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('url')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('desc')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('status')
                            ->required(),
                    ]),


                Forms\Components\Section::make('Pricing')
                    ->columns(2)
                    ->schema([
                        MoneyInput::make('price')
                            ->inlineLabel()
                            ->required(),

                        Forms\Components\Toggle::make('tax_inclusive')
                            ->required()
                            ->default(0),
                    ]),


                Forms\Components\Section::make('Limits')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('max_team_members')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('estimated_total_joining_points')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('estimated_total_purchasing_points')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ]),







                Forms\Components\Fieldset::make('Conditions')
                    ->schema([
                        TableRepeater::make('benefits')
                            ->headers([
                                Header::make('name'),
                                Header::make('slug'),
                                Header::make('value'),
                            ])
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->afterStateUpdated(fn($state,Forms\Set $set) => $set('slug',Str::slug($state))),
                                Forms\Components\TextInput::make('slug')
                                    ->disabled(),
                                Forms\Components\Toggle::make('value')->default(false)
                            ]),

                        TableRepeater::make('accessibility')
                            ->headers([
                                Header::make('name'),
                                Header::make('slug'),
                                Header::make('value'),
                            ])
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->afterStateUpdated(fn($state,Forms\Set $set) => $set('slug',Str::slug($state))),
                                Forms\Components\TextInput::make('slug')
                                    ->disabled(),
                                Forms\Components\Toggle::make('value')->default(false)
                            ]),
                    ]),



            ]);
    }










}
