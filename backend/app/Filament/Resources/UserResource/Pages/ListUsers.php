<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


    public  function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Stack::make([

                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\SpatieMediaLibraryImageColumn::make('avatar')
                            ->label('Profile Picture')
                            //->extraAttributes(['class' => 'hidden md:block'])
                            ->extraImgAttributes(['class' => 'mx-auto object-cover'])
                            ->collection('avatarImage')
                            ->circular()
//                        ->alignCenter()
                            ->size('80%'),

                        Tables\Columns\Layout\Stack::make([
                            Tables\Columns\TextColumn::make('name')
                                ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                                ->weight(FontWeight::SemiBold)
                                ->color('primary')
                                ->searchable(),
                            Tables\Columns\TextColumn::make('uuid')->label('Registration'),
                            Tables\Columns\TextColumn::make('email')
                                ->searchable(),
                            Tables\Columns\TextColumn::make('email_verified_at')
                                ->sortable(),
                            Tables\Columns\TextColumn::make('mobile')
                                ->searchable(),
                            Tables\Columns\TextColumn::make('mobile_verified_at')
                                ->dateTime()
                                ->sortable(),
                            Tables\Columns\TextColumn::make('referral_code')
                                ->searchable(),
                            Tables\Columns\TextColumn::make('parent_id')
                                ->numeric()
                                ->sortable(),
                            Tables\Columns\TextColumn::make('originator_type')
                                ->searchable(),
                            Tables\Columns\TextColumn::make('originator_id')
                                ->numeric()
                                ->sortable(),
                            Tables\Columns\TextColumn::make('gender')
                                ->searchable(),
                            Tables\Columns\TextColumn::make('dob')
                                ->date()
                                ->sortable(),
                            Tables\Columns\TextColumn::make('type')
                                ->searchable(),
                            Tables\Columns\TextColumn::make('status')
                                ->searchable(),
                            Tables\Columns\TextColumn::make('created_at')
                                ->dateTime()
                                ->sortable()
                                ->toggleable(isToggledHiddenByDefault: true),
                            Tables\Columns\TextColumn::make('updated_at')
                                ->dateTime()
                                ->sortable()
                                ->toggleable(isToggledHiddenByDefault: true),
                        ]),
                    ])
                ])
            ])
            ->contentGrid([
                'default' => 1,
                'md' => 2
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }



}
