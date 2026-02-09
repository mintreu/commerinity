<?php

namespace App\Filament\Forms\Components\RichEditor;

use Filament\Forms\Components\RichEditor\RichContentCustomBlock;

class HeroBlock  extends RichContentCustomBlock
{

    public static function getId(): string
    {
        return 'hero';
    }

    public static function getLabel(): string
    {
        return 'Hero section';
    }
}
