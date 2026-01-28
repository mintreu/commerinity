<?php

namespace App\Filament\Resources\Ecommerce\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('parent_id')
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug'),
                TextInput::make('url')
                    ->url(),
                Toggle::make('status')
                    ->required(),
                TextInput::make('view_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('desc')
                    ->columnSpanFull(),
                TextInput::make('seo_meta'),
                TextInput::make('meta_data'),
                TextInput::make('banners'),
                SpatieMediaLibraryFileUpload::make('thumbnail')->collection('thumbnail'),
            ]);
    }
}
